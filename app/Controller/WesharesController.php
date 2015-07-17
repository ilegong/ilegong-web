<?php

class WesharesController extends AppController {

    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User', 'OrderConsignees');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    public function index($weshareId=null) {
        if($weshareId){
            $this->redirect('/weshares/view/'.$weshareId);
        }
    }

    public function add(){}

    public function view($weshare_id){
        $this->set('weshare_id', $weshare_id);
    }

    public function create() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if(empty($uid)){
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $postStr = file_get_contents('php://input');
        $postDataArray = json_decode($postStr, true);
        $weshareData = array();
        $weshareData['title'] = $postDataArray['title'];
        $weshareData['description'] = $postDataArray['description'];
        $weshareData['send_info'] = $postDataArray['send_info'];
        $weshareData['creator'] = $uid;
        $weshareData['created'] = date('Y-m-d H:i:s');
        $images = $postDataArray['images'];
        $images  = Hash::extract($images,'{n}.url');
        $weshareData['images'] = implode('|',$images);
        $productsData = $postDataArray['products'];
        $addressesData = $postDataArray['addresses'];
        $weshareData['creator'] = $uid;
        $saveBuyFlag = $weshare = $this->Weshare->save($weshareData);
        $saveProductFlag = $this->saveWeshareProducts($weshare['Weshare']['id'], $productsData);
        $saveAddressFlag = $this->saveWeshareAddresses($weshare['Weshare']['id'], $addressesData);
        if ($saveBuyFlag && $saveProductFlag && $saveAddressFlag) {
            echo json_encode(array('success' => true, 'id' => $weshare['Weshare']['id']));
            return;
        } else {
            echo json_encode(array('success' => false));
            return;
        }
    }

    public function detail($weshareId) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $weshareInfo = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $weshareId
            )
        ));
        $weshareProducts = $this->WeshareProduct->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshareId
            )
        ));
        $weshareAddresses = $this->WeshareAddress->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshareId
            )
        ));
        $creatorInfo = $this->User->find('first', array(
            'conditions' => array(
                'id' => $weshareInfo['Weshare']['creator']
            ),
            'recursive' => 1, //int
            'fields' => array('id', 'nickname', 'image', 'wx_subscribe_status'),
        ));
        $weshareInfo = $weshareInfo['Weshare'];
        $weshareInfo['addresses'] = Hash::extract($weshareAddresses, '{n}.WeshareAddress');
        $weshareInfo['products'] = Hash::extract($weshareProducts, '{n}.WeshareProduct');
        $weshareInfo['creator'] = $creatorInfo['User'];
        $ordersDetail = $this->get_weshare_buy_info($weshareId);
        $weshareInfo['images'] = array_filter(explode('|',$weshareInfo['images']));
        $weixinInfo = $this->set_weixin_share_data($uid,$weshareId);
        $current_user = $this->User->find('first', array(
            'conditions' => array(
                'id' => $uid
            ),
            'recursive' => 1, //int
            'fields' => array('id', 'nickname', 'image', 'wx_subscribe_status'),
        ));
        $consignee = $this->getShareConsignees($uid);
        $user_share_summery = $this->getUserShareSummery($creatorInfo['User']['id']);
        echo json_encode(array('weshare' => $weshareInfo, 'ordersDetail' => $ordersDetail, 'current_user' => $current_user['User'], 'weixininfo' => $weixinInfo, 'consignee' => $consignee, 'user_share_summery' => $user_share_summery));
        return;
    }

    public function set_weixin_share_data($uid,$weshareId){
        if(parent::is_weixin()){
            $weixinJs = prepare_wx_share_log($uid, 'wsid', $weshareId);
            return $weixinJs;
        }
        return null;
    }

    public function pay($orderId,$type) {
        if($type==0){
            $this->redirect('/wxPay/jsApiPay/'.$orderId.'?from=share');
            return;
        }
        if($type==1){
            $this->redirect('/ali_pay/wap_to_alipay/'.$orderId.'?from=share');
            return;
        }
    }

    /**
     * {weshare_id: 1, address_id: 1, products: [{id: 1, num:2}, {id: 2, num: 10}], buyer: {name: 'Zhang San', mobilephone: 13521112222}}
     */
    public function makeOrder() {
        $this->autoRender=false;
        $uid = $this->currentUser['id'];
        if(empty($uid)){
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $postStr = file_get_contents('php://input');
        $postDataArray = json_decode($postStr, true);
        $products = $postDataArray['products'];
        $weshareId = $postDataArray['weshare_id'];
        $addressId = $postDataArray['address_id'];
        $buyerData = $postDataArray['buyer'];
        $cart = array();
        try {
            $weshareProductIds = Hash::extract($products, '{n}.id');
            $productIdNumMap = Hash::combine($products, '{n}.id', '{n}.num');
            $tinyAddress = $this->WeshareAddress->find('first', array(
                'conditions' => array(
                    'id' => $addressId,
                    'weshare_id' => $weshareId
                )
            ));
            $weshareProducts = $this->WeshareProduct->find('all', array(
                'conditions' => array(
                    'id' => $weshareProductIds,
                    'weshare_id' => $weshareId
                )
            ));
            $this->setShareConsignees($buyerData['name'], $buyerData['mobilephone'], $uid);
            $order = $this->Order->save(array('creator' => $uid, 'consignee_address' => $tinyAddress['WeshareAddress']['address'] ,'member_id' => $weshareId, 'type' => ORDER_TYPE_WESHARE_BUY, 'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'), 'consignee_id' => $addressId, 'consignee_name' => $buyerData['name'], 'consignee_mobilephone' => $buyerData['mobilephone']));
            $orderId = $order['Order']['id'];
            $totalPrice = 0;
            foreach ($weshareProducts as $p) {
                $item = array();
                $pid = $p['WeshareProduct']['id'];
                $num = $productIdNumMap[$pid];
                $price = $p['WeshareProduct']['price'];
                $item['name'] = $p['WeshareProduct']['name'];
                $item['num'] = $num;
                $item['price'] = $price;
                $item['type'] = ORDER_TYPE_WESHARE_BUY;
                $item['product_id'] = $p['WeshareProduct']['id'];
                $item['created'] = date('Y-m-d H:i:s');
                $item['updated'] = date('Y-m-d H:i:s');
                $item['creator'] = $uid;
                $item['order_id'] = $orderId;
                $item['tuan_buy_id'] = $weshareId;
                $cart[] = $item;
                $totalPrice += $num * $price;
            }
            $this->Cart->saveAll($cart);
            $this->Order->updateAll(array('total_all_price' => $totalPrice / 100, 'total_price' => $totalPrice / 100, 'ship_fee' => 0), array('id' => $orderId));
            echo json_encode(array('success' => true, 'orderId' => $orderId));
            return;
        } catch (Exception $e) {
            echo json_encode(array('success' => false, 'msg' => $e->getMessage()));
            return;
        }
    }

    function confirmReceived($order_id){
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if(empty($uid)){
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }

        $order = $this->Order->findById($order_id);
        if(empty($order)){
            echo json_encode(array(success => false, reason => 'order does not exist'));
        }
        if($order['Order']['type'] != ORDER_TYPE_WESHARE_BUY){
            echo json_encode(array(success => false, reason => 'invalid order'));
        }
        $weshare_id = $order['Order']['member_id'];
        $weshare = $this->Weshare->findById($weshare_id);
        if(empty($weshare)){
            echo json_encode(array(success => false, reason => 'invalid weshare'));
        }
        $is_owner = $uid == $order['Order']['creator'];
        $is_creator = $uid == $weshare['Weshare']['creator'];
        if(!$is_owner && !$is_creator){
            echo json_encode(array(success => false, reason => 'only owner or creator '));
        }

        $result = $this->Order->updateAll(array('status' => 2), array('id' => $order['Order']['id']));
        $this->Cart->updateAll(array('status' => 2), array('order_id' => $order['Order']['id']));
        if(!$result){
            echo json_encode(array(success => false, reason=>"failed to update order status"));
        }

        echo json_encode(array(success => true));
    }

    public function stopShare($weShareId){
        $this->autoRender = false;
        $this->Weshare->updateAll(array('status' => WESHARE_STOP_STATUS),array('id' => $weShareId));
        echo json_encode(array('success' => true));
    }

    public function user_share_info($uid=null){
        $this->layout = null;
        $current_uid = $this->currentUser['id'];
        if(empty($uid)){
            $uid = $current_uid;
        }
        $myCreateShares = $this->Weshare->find('all', array(
            'conditions' => array(
                'creator' => $uid
            )
        ));
        $joinShareOrder = $this->Order->find('all',array(
            'conditions' => array(
                'creator' => $uid,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED)
            ),
            'fields' => array('member_id', 'id')
        ));
        $joinShareIds = Hash::extract($joinShareOrder,'{n}.Order.member_id');
        $joinShareIds = array_unique($joinShareIds);
        $myJoinShares = $this->Weshare->find('all', array(
            'conditions' => array(
                'id' => $joinShareIds
            )
        ));
        $creatorIds = Hash::extract($myJoinShares, '{n}.Weshare.creator');
        $creatorIds[] = $uid;
        $creators = $this->User->find('all',array(
            'conditions' => array(
                'id' => $creatorIds
            ),
            'fields' => array('id', 'nickname', 'image', 'wx_subscribe_status')
        ));
        $creators = Hash::combine($creators,'{n}.User.id','{n}.User');
        $userShareSummery = $this->getUserShareSummery($uid);
        $this->set($userShareSummery);
        $currentUser = $creators[$uid];
        $this->set('is_me',$uid==$current_uid);
        $this->set('current_user',$currentUser);
        $this->set('creators',$creators);
        $this->set('my_create_shares',$myCreateShares);
        $this->set('my_join_shares',$myJoinShares);
    }

    private function saveWeshareProducts($weshareId, $weshareProductData) {
        foreach ($weshareProductData as &$product) {
            $product['weshare_id'] = $weshareId;
            $product['price'] = ($product['price']*100);
        }
        return $this->WeshareProduct->saveAll($weshareProductData);
    }

    private function saveWeshareAddresses($weshareId, $weshareAddressData) {
        foreach ($weshareAddressData as &$address) {
            $address['weshare_id'] = $weshareId;
        }
        return $this->WeshareAddress->saveAll($weshareAddressData);
    }

    private function get_weshare_buy_info($weshareId) {
        $product_buy_num = array('details'=> array());
        $order_cart_map = array();
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'member_id' => $weshareId,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED)
            ),
            'fields' => array('id', 'creator', 'created', 'consignee_name', 'consignee_mobilephone', 'consignee_address', 'status'),
            'order' => array('created DESC')
        ));
        $orderIds = Hash::extract($orders, '{n}.Order.id');
        $userIds = Hash::extract($orders, '{n}.Order.creator');
        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $userIds
            ),
            'recursive' => 1, //int
            'fields' => array('id', 'nickname', 'image', 'wx_subscribe_status'),
        ));
        $orders = Hash::combine($orders, '{n}.Order.id', '{n}.Order');
        if($orders){
            usort($orders, function ($a, $b) {
                return ($a['id'] < $b['id']) ? -1 : 1;
            });
        }
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $orderIds,
                'type' => ORDER_TYPE_WESHARE_BUY
            ),
            'fields' => array('id', 'name', 'order_id', 'num', 'product_id', 'price')
        ));
        $summeryTotalPrice = 0;
        foreach ($carts as $item) {
            $order_id = $item['Cart']['order_id'];
            $product_id = $item['Cart']['product_id'];
            $cart_num = $item['Cart']['num'];
            $cart_price = $item['Cart']['price'];
            $cart_name = $item['Cart']['name'];
            if (!isset($product_buy_num['details'][$product_id])) $product_buy_num['details'][$product_id] = array('num' => 0, 'total_price' => 0, 'name' => $cart_name);
            if (!isset($orders[$order_id]['carts'])) $order_cart_map[$order_id] = array();
            $product_buy_num['details'][$product_id]['num'] = $product_buy_num['details'][$product_id]['num'] + $cart_num;
            $totalPrice = $cart_num * $cart_price;
            $summeryTotalPrice += $totalPrice;
            $product_buy_num['details'][$product_id]['total_price'] = $product_buy_num['details'][$product_id]['total_price'] + $totalPrice;
            $order_cart_map[$order_id][] = $item['Cart'];
        }
        $product_buy_num['all_buy_user_count'] = count($users);
        $product_buy_num['all_total_price'] = $summeryTotalPrice;
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        return array('users' => $users, 'orders' => $orders, 'order_cart_map' => $order_cart_map, 'summery' => $product_buy_num);
    }

    private function setShareConsignees($userInfo, $mobileNum, $uid) {
        $consignee = $this->OrderConsignees->find('first', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => STATUS_CONSIGNEES_SHARE
            ),
            'fields' => array('name', 'mobilephone')
        ));
        //update
        if(!empty($consignee)){
            $this->OrderConsignees->updateAll(array('name' => $userInfo, 'mobilephone' => $mobileNum),array('id' => $consignee['OrderConsignees']['id']));
            return;
        }
        //save
        $this->OrderConsignees->save(array('creator' => $uid, 'status' => STATUS_CONSIGNEES_SHARE, 'name' => $userInfo, 'mobilephone' => $mobileNum));
    }

    private function getShareConsignees($uid){
        $consignee = $this->OrderConsignees->find('first', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => STATUS_CONSIGNEES_SHARE
            ),
            'fields' => array('name', 'mobilephone')
        ));
        return $consignee;
    }

    private function getUserShareSummery($uid){
        $weshares = $this->Weshare->find('all',array(
            'conditions' => array(
                'creator' => $uid
            ),
            'fields' => array('id')
        ));
        $weshare_ids = Hash::extract($weshares, '{n}.Weshare.id');
        $follower_count = $this->Order->find('count',array(
            'conditions' => array(
                'member_id' => $weshare_ids,
                'status' => array(ORDER_STATUS_SHIPPED,ORDER_STATUS_PAID),
                'type' => ORDER_TYPE_WESHARE_BUY
            ),
            'fileds' => array('DISTINCT creator')
        ));
        return array('share_count' => count($weshares), 'follower_count' => $follower_count);
    }
}