<?php

class WesharesController extends AppController {

    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    public function index($weshareId=null) {
        if($weshareId){
            $this->redirect('/weshares/index/#!/view/'.$weshareId);
        }
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
        echo json_encode(array('weshare' => $weshareInfo, 'ordersDetail' => $ordersDetail, 'current_user' => $current_user['User'], 'weixininfo' => $weixinInfo));
        return;
    }

    public function set_weixin_share_data($uid,$weshareId){
        if(parent::is_weixin()){
            $weixinJs = prepare_wx_share_log($uid, 'wsid', $weshareId);
            return $weixinJs;
        }
        return null;
    }

    public function buy() {

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
                'status' => ORDER_STATUS_PAID
            ),
            'fields' => array('id', 'creator', 'created', 'consignee_name', 'consignee_address'),
            'order' => array('created ASC')
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
            if (!isset($product_buy_num[$product_id])) $product_buy_num['details'][$product_id] = array('num' => 0, 'total_price' => 0, 'name' => $cart_name);
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
}