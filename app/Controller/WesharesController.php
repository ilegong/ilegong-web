<?php

class WesharesController extends AppController {

    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User', 'OrderConsignees', 'Oauthbind', 'SharedOffer', 'CouponItem');

    var $query_user_fileds = array('id', 'nickname', 'image', 'wx_subscribe_status', 'description');

    public $components = array('Weixin', 'WeshareBuy', 'Buying', 'RedPacket');

    var $pay_type = 1;

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    public function index($weshareId = null) {
        if ($weshareId) {
            $this->redirect('/weshares/view/' . $weshareId);
        }
    }

    public function view($weshare_id, $from = 0) {
        $uid = $this->currentUser['id'];
        $this->set('weshare_id', $weshare_id);
        //form paid done
        $this->log('weshare view mark '.$_REQUEST['mark']);
        if ($from == $this->pay_type||$_REQUEST['mark'] == 'template_msg') {
            //check has sharer has red packet
            //领取红包
            $weshare = $this->Weshare->find('first', array('conditions' => array('id' => $weshare_id)));
            $weshare_creator = $weshare['Weshare']['creator'];
            $shared_offers = $this->SharedOffer->find_new_offers_by_weshare_creator($uid, $weshare_creator);
            //get first
            if (!empty($shared_offers)) {
                $this->set('shared_offer_id', $shared_offers[0]['SharedOffer']['id']);
                //bind user default get coupon
                $this->get_coupon_with_shared_id($shared_offers[0]['SharedOffer']['id']);
            }
            $this->set('from', $this->pay_type);
        }
        //has share offer id user open share
        //用户抢红包
        $shared_offer_id = $_REQUEST['shared_offer_id'];
        if (!empty($shared_offer_id)) {
            //process
            $this->process_shared_offer($shared_offer_id);
        }
    }

    public function update($weshareId) {
        $uid = $this->currentUser['id'];
        $weshareInfo = $this->get_weshare_detail($weshareId);
        if ($uid != $weshareInfo['creator']['id']) {
            $this->redirect('/weshares/view/' . $weshareId . '/0');
        }
        $this->set('weshare_id', $weshareId);
    }

    public function get_share_info($weshareId) {
        $this->autoRender = false;
        $shareInfo = $this->get_weshare_detail($weshareId);
        $products = &$shareInfo['products'];
        foreach ($products as &$p) {
            $p['price'] = $p['price'] / 100;
        }
        echo json_encode($shareInfo);
        return;
    }

    public function add() {
        $currentUser = $this->currentUser;
        $uid = $currentUser['id'];
        $user_fields = $this->query_user_fileds;
        $user_fields[] = 'mobilephone';
        $user_fields[] = 'payment';
        $current_user = $this->User->find('first', array(
            'conditions' => array(
                'id' => $uid
            ),
            'recursive' => 1, //int
            'fields' => $user_fields,
        ));
        if (empty($current_user['User']['mobilephone'])) {
            $this->redirect('/users/to_bind_mobile?from=share');
            return;
        }
        if (empty($current_user['User']['payment'])) {
            $this->redirect('/users/complete_user_info?from=share');
            return;
        }
        if (parent::is_weixin()) {
            $wexin_params = $this->set_weixin_share_data($currentUser['id'], 0);
            $this->set($wexin_params);
            $title = $currentUser['nickname'] . '邀请你一起来加入分享';
            $image = $currentUser['image'];
            if (!$image) {
                $image = 'http://dev.tongshijia.com/img/logo_footer.jpg';
            }
            $desc = '朋友说是一个有人情味的分享社区，这里你不但可以吃到各地的特产，还能认识有趣的人。';
            $this->set('title', $title);
            $this->set('image', $image);
            $this->set('desc', $desc);
            $this->set('add_view', true);
        }
    }

    public function save() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $postStr = file_get_contents('php://input');
        $postDataArray = json_decode($postStr, true);
        $weshareData = array();
        $weshareData['id'] = $postDataArray['id'];
        $weshareData['title'] = $postDataArray['title'];
        $weshareData['description'] = $postDataArray['description'];
        $weshareData['send_info'] = $postDataArray['send_info'];
        $weshareData['creator'] = $uid;
        $weshareData['created'] = date('Y-m-d H:i:s');
        $images = $postDataArray['images'];
        $weshareData['images'] = implode('|', $images);
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
        $weshareInfo = $this->get_weshare_detail($weshareId);
        $is_me = $uid == $weshareInfo['creator']['id'];
        $ordersDetail = $this->get_weshare_buy_info($weshareId, $is_me);
        $weixinInfo = $this->set_weixin_share_data($uid, $weshareId);
        $user_fields = $this->query_user_fileds;
        $user_fields[] = 'mobilephone';
        $user_fields[] = 'payment';
        $current_user = $this->User->find('first', array(
            'conditions' => array(
                'id' => $uid
            ),
            'recursive' => 1, //int
            'fields' => $user_fields,
        ));
        $consignee = $this->getShareConsignees($uid);
        $creatorId = $weshareInfo['creator']['id'];
        $user_share_summery = $this->getUserShareSummery($creatorId, $uid == $creatorId);
        //TODO return coupon
        $my_coupon_items = $this->get_can_used_coupons($uid, $creatorId);

        echo json_encode(array('weshare' => $weshareInfo, 'ordersDetail' => $ordersDetail, 'current_user' => $current_user['User'], 'weixininfo' => $weixinInfo, 'consignee' => $consignee, 'user_share_summery' => $user_share_summery, 'my_coupons' => $my_coupon_items[0]));
        return;
    }

    public function set_weixin_share_data($uid, $weshareId) {
        if (parent::is_weixin()) {
            $weixinJs = prepare_wx_share_log($uid, 'wsid', $weshareId);
            return $weixinJs;
        }
        return null;
    }

    public function pay($orderId, $type) {
        if ($type == 0) {
            $this->redirect('/wxPay/jsApiPay/' . $orderId . '?from=share');
            return;
        }
        if ($type == 1) {
            $this->redirect('/ali_pay/wap_to_alipay/' . $orderId . '?from=share');
            return;
        }
    }

    public function makeOrder() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
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
            $reason = '';
            foreach ($weshareProducts as $p) {
                $product_id = $p['WeshareProduct']['id'];
                $cart_num = $productIdNumMap[$product_id];
                $check_num_result = $this->check_product_num($weshareId, $p, $cart_num);
                if (!$check_num_result['result']) {
                    if ($check_num_result['type'] == 0) {
                        $reason = $reason . ' ' . $p['WeshareProduct']['name'] . '已经售罄';
                    }
                    if ($check_num_result['type'] == 1) {
                        $reason = $reason . ' ' . $p['WeshareProduct']['name'] . '超量' . $check_num_result['num'] . '件';
                    }
                }
            }
            if (strlen($reason) > 0) {
                echo json_encode(array('success' => false, 'reason' => $reason));
                return;
            }
            $address = $tinyAddress['WeshareAddress']['address'];
            if ($buyerData['address']) {
                $address = $address . '--' . $buyerData['address'];
            }
            $this->setShareConsignees($buyerData['name'], $buyerData['mobilephone'], $buyerData['address'], $uid);
            $order = $this->Order->save(array('creator' => $uid, 'consignee_address' => $address, 'member_id' => $weshareId, 'type' => ORDER_TYPE_WESHARE_BUY, 'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'), 'consignee_id' => $addressId, 'consignee_name' => $buyerData['name'], 'consignee_mobilephone' => $buyerData['mobilephone']));
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
            if ($this->Order->updateAll(array('total_all_price' => $totalPrice / 100, 'total_price' => $totalPrice / 100, 'ship_fee' => 0), array('id' => $orderId))) {
                $coupon_id = $postDataArray['coupon_id'];
                //check coupon
                if (!empty($coupon_id)) {
                    App::uses('OrdersController', 'Controller');
                    $this->Session->write(OrdersController::key_balanced_conpons(), json_encode(array(0 => array($coupon_id))));
                    $this->order_use_score_and_coupon($orderId, $uid, 0, $totalPrice / 100);
                }
                echo json_encode(array('success' => true, 'orderId' => $orderId));
                return;
            }
            echo json_encode(array('success' => false, 'orderId' => $orderId));
            return;
        } catch (Exception $e) {
            $this->log($uid . 'buy share ' . $weshareId . $e);
            echo json_encode(array('success' => false, 'msg' => $e->getMessage()));
            return;
        }
    }

    function confirmReceived($order_id) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }

        $order = $this->Order->findById($order_id);
        if (empty($order)) {
            echo json_encode(array(success => false, reason => 'order does not exist'));
            return;
        }
        if ($order['Order']['type'] != ORDER_TYPE_WESHARE_BUY) {
            echo json_encode(array(success => false, reason => 'invalid order'));
            return;
        }
        $weshare_id = $order['Order']['member_id'];
        $weshare = $this->Weshare->findById($weshare_id);
        if (empty($weshare)) {
            echo json_encode(array(success => false, reason => 'invalid weshare'));
            return;
        }
        $is_owner = $uid == $order['Order']['creator'];
        $is_creator = $uid == $weshare['Weshare']['creator'];
        if (!$is_owner && !$is_creator) {
            echo json_encode(array(success => false, reason => 'only owner or creator '));
            return;
        }

        $result = $this->Order->updateAll(array('status' => 2), array('id' => $order['Order']['id']));
        $this->Cart->updateAll(array('status' => 2), array('order_id' => $order['Order']['id']));
        if (!$result) {
            echo json_encode(array(success => false, reason => "failed to update order status"));
            return;
        }

        echo json_encode(array(success => true));
    }

    public function stopShare($weShareId) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $this->Weshare->updateAll(array('status' => WESHARE_STOP_STATUS), array('id' => $weShareId, 'creator' => $uid, 'status' => WESHARE_NORMAL_STATUS));
        echo json_encode(array('success' => true));
    }

    public function share_list() {
        $this->layout = 'weshare_bootstrap';
        $weshares = $this->Weshare->find('all', array(
            'fields' => array('DISTINCT creator'),
            'limit' => 100
        ));
        $weshares_creator = Hash::extract($weshares, '{n}.Weshare.creator');
        $share_users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $weshares_creator
            ),
            'fields' => $this->query_user_fileds
        ));
    }

    public function user_share_info($uid = null) {
        $this->layout = 'weshare_bootstrap';
        $current_uid = $this->currentUser['id'];
        if (empty($uid)) {
            $uid = $current_uid;
        }
        $myCreateShares = $this->Weshare->find('all', array(
            'conditions' => array(
                'creator' => $uid
            ),
            'order' => array('created DESC')
        ));
        $orderStatus = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED);
        if ($uid != $current_uid) {
            $orderStatus[] = ORDER_STATUS_VIRTUAL;
        }
        $joinShareOrder = $this->Order->find('all', array(
            'conditions' => array(
                'creator' => $uid,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => $orderStatus
            ),
            'fields' => array('member_id', 'id')
        ));
        $joinShareIds = Hash::extract($joinShareOrder, '{n}.Order.member_id');
        $joinShareIds = array_unique($joinShareIds);
        $myJoinShares = $this->Weshare->find('all', array(
            'conditions' => array(
                'id' => $joinShareIds
            ),
            'order' => array('created DESC')
        ));
        $creatorIds = Hash::extract($myJoinShares, '{n}.Weshare.creator');
        $creatorIds[] = $uid;
        $creators = $this->User->find('all', array(
            'conditions' => array(
                'id' => $creatorIds
            ),
            'fields' => $this->query_user_fileds
        ));
        $creators = Hash::combine($creators, '{n}.User.id', '{n}.User');
        $shareUser = $creators[$uid];
        if (parent::is_weixin()) {
            $wexin_params = $this->set_weixin_share_data($uid, -1);
            $this->set($wexin_params);
            if ($uid == $current_uid) {
                $title = $shareUser['nickname'] . '的微分享，和我一起来分享吧';
                $image = $shareUser['image'];
                $desc = '朋友说是一个有人情味的分享社区，这里你不但可以吃到各地的特产，还能认识有趣的人。';
            } else {
                $current_user = $this->currentUser;
                $title = $current_user['nickname'] . '推荐了' . $shareUser['nickname'] . '的微分享，和我一起来分享吧';
                $image = $shareUser['image'];
                $desc = $shareUser['nickname'] . '是我的朋友，很靠谱。朋友说是一个有人情味的分享社区，这里你不但可以吃到各地的特产，还能认识有趣的人。';
            }
            if (!$image) {
                $image = 'http://dev.tongshijia.com/img/logo_footer.jpg';
            }
            $this->set('title', $title);
            $this->set('image', $image);
            $this->set('desc', $desc);
            $this->set('add_view', true);
        }
        $userShareSummery = $this->getUserShareSummery($uid, $uid == $current_uid);
        $this->explode_share_imgs($myCreateShares);
        $this->explode_share_imgs($myJoinShares);
        $this->set($userShareSummery);
        $this->set('is_me', $uid == $current_uid);
        $this->set('share_user', $shareUser);
        $this->set('creators', $creators);
        $this->set('my_create_shares', $myCreateShares);
        $this->set('my_join_shares', $myJoinShares);
    }

    public function send_arrival_msg() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $params = json_decode(file_get_contents('php://input'), true);
        $msg = $params['msg'];
        $weshare_id = $params['share_id'];
        $share_info = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $weshare_id
            )
        ));
        if ($uid != $share_info['Weshare']['creator']) {
            echo json_encode(array('success' => false, 'reason' => 'invalid'));
            return;
        }
        $this->process_send_msg($share_info, $msg);
        echo json_encode(array('success' => true));
        return;
    }

    public function share_order_list($weshareId) {
        $this->layout = 'weshare_bootstrap';
        $user_id = $this->currentUser['id'];
        $statics_data = $this->get_weshare_buy_info($weshareId, true);
        $this->set($statics_data);
        $this->set('hide_footer', true);
        $this->set('user_id', $user_id);
        $this->set('weshareId', $weshareId);
    }

    private function saveWeshareProducts($weshareId, $weshareProductData) {
        foreach ($weshareProductData as &$product) {
            $product['weshare_id'] = $weshareId;
            $product['price'] = ($product['price'] * 100);
            $store = $product['store'];
            if (empty($store)) {
                $product['store'] = 0;
            }
        }
        return $this->WeshareProduct->saveAll($weshareProductData);
    }

    private function saveWeshareAddresses($weshareId, $weshareAddressData) {
        foreach ($weshareAddressData as &$address) {
            $address['weshare_id'] = $weshareId;
        }
        return $this->WeshareAddress->saveAll($weshareAddressData);
    }

    private function get_weshare_buy_info($weshareId, $is_me) {
        $product_buy_num = array('details' => array());
        $order_cart_map = array();
        $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED);
        if (!$is_me) {
            $order_status[] = ORDER_STATUS_VIRTUAL;
        }
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'member_id' => $weshareId,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => $order_status,
                'deleted' => DELETED_NO
            ),
            'fields' => array('id', 'creator', 'created', 'consignee_name', 'consignee_mobilephone', 'consignee_address', 'status', 'total_all_price'),
            'order' => array('created DESC')
        ));
        $orderIds = Hash::extract($orders, '{n}.Order.id');
        $userIds = Hash::extract($orders, '{n}.Order.creator');
        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $userIds
            ),
            'recursive' => 1, //int
            'fields' => $this->query_user_fileds,
        ));
        $orders = Hash::combine($orders, '{n}.Order.id', '{n}.Order');
        if ($orders) {
            usort($orders, function ($a, $b) {
                return ($a['id'] < $b['id']) ? -1 : 1;
            });
        }
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $orderIds,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'not' => array('order_id' => null, 'order_id' => '')
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
            if (!isset($order_cart_map[$order_id])) $order_cart_map[$order_id] = array();
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

    private function get_weshare_detail($weshareId) {
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
            'fields' => $this->query_user_fileds,
        ));
        $weshareInfo = $weshareInfo['Weshare'];
        $weshareInfo['addresses'] = Hash::extract($weshareAddresses, '{n}.WeshareAddress');
        $weshareInfo['products'] = Hash::extract($weshareProducts, '{n}.WeshareProduct');
        $weshareInfo['creator'] = $creatorInfo['User'];
        $weshareInfo['images'] = array_filter(explode('|', $weshareInfo['images']));
        return $weshareInfo;

    }

    private function setShareConsignees($userInfo, $mobileNum, $address, $uid) {
        $consignee = $this->OrderConsignees->find('first', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => STATUS_CONSIGNEES_SHARE
            ),
            'fields' => array('id', 'name', 'mobilephone')
        ));
        //update
        if (!empty($consignee)) {
            $this->OrderConsignees->updateAll(array('name' => "'" . $userInfo . "'", 'mobilephone' => "'" . $mobileNum . "'", 'address' => "'" . $address . "'"), array('id' => $consignee['OrderConsignees']['id']));
            return;
        }
        //save
        $this->OrderConsignees->save(array('creator' => $uid, 'status' => STATUS_CONSIGNEES_SHARE, 'name' => $userInfo, 'mobilephone' => $mobileNum, 'address' => $address));
    }

    private function getShareConsignees($uid) {
        $consignee = $this->OrderConsignees->find('first', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => STATUS_CONSIGNEES_SHARE
            ),
            'fields' => array('name', 'mobilephone', 'address')
        ));
        return $consignee['OrderConsignees'];
    }

    private function getUserShareSummery($uid, $is_me = false) {
        $weshares = $this->Weshare->find('all', array(
            'conditions' => array(
                'creator' => $uid
            ),
            'fields' => array('id')
        ));
        $weshare_ids = Hash::extract($weshares, '{n}.Weshare.id');
        $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED);
        if (!$is_me) {
            $order_status[] = ORDER_STATUS_VIRTUAL;
        }
        $follower_count = $this->Order->find('count', array(
            'conditions' => array(
                'member_id' => $weshare_ids,
                'status' => $order_status,
                'type' => ORDER_TYPE_WESHARE_BUY
            ),
            'fileds' => array('DISTINCT creator')
        ));
        return array('share_count' => count($weshares), 'follower_count' => $follower_count);
    }

    private function explode_share_imgs(&$shares) {
        foreach ($shares as &$item) {
            $item['Weshare']['images'] = explode('|', $item['Weshare']['images']);
        }
    }

    private function process_send_msg($shareInfo, $msg) {
        $share_id = $shareInfo['Weshare']['id'];
        $share_creator = $shareInfo['Weshare']['creator'];
        //select order paid to send msg
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY,
                'member_id' => $share_id,
                'status' => array(ORDER_STATUS_PAID)
            ),
            'fields' => array(
                'id', 'consignee_name', 'consignee_address', 'creator'
            )
        ));
        $order_user_ids = Hash::extract($orders, '{n}.Order.creator');
        $order_user_ids[] = $share_creator;
        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $order_user_ids
            ),
            'fields' => array('id', 'nickname')
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        $userOauthBinds = $this->Oauthbind->find('all', array(
            'conditions' => array(
                'user_id' => $order_user_ids
            ),
            'fields' => array('user_id', 'oauth_openid')
        ));
        $userOauthBinds = Hash::combine($userOauthBinds, '{n}.Oauthbind.user_id', '{n}.Oauthbind.oauth_openid');
        $desc = '感谢大家对' . $users[$share_creator]['nickname'] . '的支持，分享快乐。';
        $detail_url = WX_HOST . '/weshares/view/' . $share_id;
        foreach ($orders as $order) {
            $order_id = $order['Order']['id'];
            $order_user_id = $order['Order']['creator'];
            $open_id = $userOauthBinds[$order_user_id];
            $order_user_name = $users[$order_user_id]['nickname'];
            $title = $order_user_name . '你好，' . $msg;
            $conginess_name = $order['Order']['consignee_name'];
            $conginess_address = $order['Order']['consignee_address'];
            $this->Weixin->send_share_product_arrival($open_id, $detail_url, $title, $order_id, $conginess_address, $conginess_name, $desc);
        }
    }

    private function check_product_num($weshareId, $weshareProduct, $num) {
        $store_num = $weshareProduct['WeshareProduct']['store'];
        if ($store_num == 0) {
            return array('result' => true);
        }
        if ($num > $store_num) {
            $over_num = $num - $store_num;
            return array('result' => false, 'type' => 1, 'num' => $over_num);
        }
        $orders = $this->Order->find('all', array(
            'conditions' => array('type' => ORDER_TYPE_WESHARE_BUY, 'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED), 'member_id' => $weshareId),
            'fields' => array('id')
        ));
        //$total = $this->RequestedItem->find('all', array(array('fields' => array('sum(Model.cost * Model.quantity)   AS ctotal'), 'conditions'=>array('RequestedItem.purchase_request_id'=>$this->params['named']['po_id']));
        $order_ids = Hash::extract($orders, '{n}.Order.id');
        $product_id = $weshareProduct['WeshareProduct']['id'];
        if (!empty($order_ids)) {
            $sum_data = $this->Cart->find('all', array(
                'fields' => array('sum(num) AS total'),
                'conditions' => array(
                    'product_id' => $product_id,
                    'order_id' => $order_ids
                )
            ));
            $buy_num = $sum_data[0][0]['total'];
            if (($buy_num + $num) > $store_num) {
                //买完了
                if ($buy_num >= $store_num) {
                    return array('result' => false, 'type' => 0);
                }
                $over_num = $buy_num + $buy_num - $store_num;
                return array('result' => false, 'type' => 1, 'num' => $over_num);
            }
        }
        return array('result' => true);
    }

    private function get_coupon_with_shared_id($share_offer_id) {
        $uid = $this->currentUser['id'];
        return $this->RedPacket->process_receive($share_offer_id, $uid,$this->is_weixin());
    }

    private function get_can_used_coupons($uid, $sharer) {
        return $this->CouponItem->find_my_valid_share_coupons($uid, $sharer);
    }

    private function order_use_score_and_coupon($order_id, $uid, $brand_id, $total_all_price) {
        //use coupon
        App::uses('OrdersController', 'Controller');
        $ordersController = new OrdersController();
        $ordersController->Session = $this->Session;
        $order_results = array();
        $order_results[$brand_id] = array($order_id, $total_all_price);
        foreach ($order_results as $brand_id => $order_val) {
            $order_id = $order_val[0];
            $ordersController->apply_coupons_to_order($brand_id, $uid, $order_id, $order_results);
        }
        $ordersController->clean_score_and_coupon();
    }

    private function process_shared_offer($shared_offer_id) {
        //to do check offer status
        $get_coupon_result = $this->get_coupon_with_shared_id($shared_offer_id);
        $this->log('share user get red packet result ' . json_encode($get_coupon_result));
        if (!$get_coupon_result['success']) {
            $this->set('get_coupon_type', 'fail');
            return;
        }
        //no more
        if ($get_coupon_result['noMore']) {
            $this->set('get_coupon_type', 'no_more');
            return;
        }
        //accepted
        $this->set('follow_shared_offer_id', $shared_offer_id);
        if ($get_coupon_result['accepted']) {
            $this->set('get_coupon_type', 'accepted');
            return;
        }
        $this->set('get_coupon_type', 'got');
        $this->set('couponNum', $get_coupon_result['couponNum']);
    }
}