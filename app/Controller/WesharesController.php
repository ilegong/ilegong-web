<?php

class WesharesController extends AppController {

    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User', 'OrderConsignees', 'Oauthbind', 'SharedOffer', 'CouponItem', 'SharerShipOption', 'WeshareShipSetting', 'OfflineStore');

    var $query_user_fileds = array('id', 'nickname', 'image', 'wx_subscribe_status', 'description');

    var $components = array('Weixin', 'WeshareBuy', 'Buying', 'RedPacket');

    var $share_ship_type = array('self_ziti', 'kuaidi', 'pys_ziti');

    var $pay_type = 1;

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    public function index() {
        $this->layout = null;
        $uid = $this->currentUser['id'];
        $this->set('uid', $uid);
    }

    public function view($weshare_id, $from = 0) {
        $uid = $this->currentUser['id'];
        $this->set('weshare_id', $weshare_id);
        //form paid done
        $this->log('weshare view mark ' . $_REQUEST['mark']);
        //check has sharer has red packet
        //领取红包
        $shared_offer_id = $_REQUEST['shared_offer_id'];
        //has share offer id user open share
        //用户抢红包
        if (!empty($shared_offer_id)) {
            //process
            $this->process_shared_offer($shared_offer_id);
        } else {
            $weshare = $this->Weshare->find('first', array('conditions' => array('id' => $weshare_id)));
            $weshare_creator = $weshare['Weshare']['creator'];
            $shared_offers = $this->SharedOffer->find_new_offers_by_weshare_creator($uid, $weshare_creator);
            //get first
            if (!empty($shared_offers)) {
                $this->set('shared_offer_id', $shared_offers[0]['SharedOffer']['id']);
                $this->set('from', $this->pay_type);
            }
            if ($from == 1) {
                $this->set('from', $this->pay_type);
            }
        }
    }

    public function update($weshareId) {
        $uid = $this->currentUser['id'];
        $weshareInfo = $this->get_weshare_detail($weshareId);
        if ($uid != $weshareInfo['creator']['id']) {
            $this->redirect('/weshares/view/' . $weshareId . '/0');
        }
        $share_ship_set = $this->sharer_can_use_we_ship($uid);
        $this->set('ship_type', $share_ship_set);
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
        //check user has bind mobile and payment
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
        $share_ship_set = $this->sharer_can_use_we_ship($uid);
        $this->set('ship_type', $share_ship_set);
        //设置微信分享参数
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
        $shipSetData = $postDataArray['ship_type'];
        $weshareData['creator'] = $uid;
        $saveBuyFlag = $weshare = $this->Weshare->save($weshareData);
        $this->saveWeshareProducts($weshare['Weshare']['id'], $productsData);
        $this->saveWeshareAddresses($weshare['Weshare']['id'], $addressesData);
        $this->saevWeshareShipType($weshare['Weshare']['id'], $shipSetData);
        if ($saveBuyFlag) {
            if (empty($weshareData['id'])) {
                $this->WeshareBuy->send_new_share_msg($weshare['Weshare']['id']);
            }
            echo json_encode(array('success' => true, 'id' => $weshare['Weshare']['id']));
            return;
        } else {
            echo json_encode(array('success' => false, 'uid' => $uid));
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
        $share_ship_set = $this->sharer_can_use_we_ship($weshareInfo['creator']['id']);
        $my_coupon_items = $this->get_can_used_coupons($uid, $creatorId);
        $weshare_ship_settings = $this->getWeshareShipSettings($weshareId);
        $comment_data = $this->WeshareBuy->load_comment_by_share_id($weshareId);
        echo json_encode(array('support_pys_ziti' => $share_ship_set,
            'weshare' => $weshareInfo,
            'ordersDetail' => $ordersDetail,
            'current_user' => $current_user['User'],
            'weixininfo' => $weixinInfo,
            'weshare_ship_settings' => $weshare_ship_settings,
            'consignee' => $consignee,
            'user_share_summery' => $user_share_summery,
            'my_coupons' => $my_coupon_items[0],
            'comment_data' => $comment_data
        ));
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
        $buyerData = $postDataArray['buyer'];
        $cart = array();
        try {
            $weshareProductIds = Hash::extract($products, '{n}.id');
            $productIdNumMap = Hash::combine($products, '{n}.id', '{n}.num');

            $weshareProducts = $this->WeshareProduct->find('all', array(
                'conditions' => array(
                    'id' => $weshareProductIds,
                    'weshare_id' => $weshareId
                )
            ));
            $checkProductStoreResult = $this->check_product_store($weshareProducts, $weshareId, $productIdNumMap);
            if (!empty($checkProductStoreResult)) {
                echo json_encode($checkProductStoreResult);
                return;
            }
            $shipInfo = $postDataArray['ship_info'];
            $addressId = $shipInfo['address_id'];
            $shipType = $shipInfo['ship_type'];
            $shipSetId = $shipInfo['ship_set_id'];
            $shipSetting = $this->get_ship_set($shipSetId, $weshareId);
            if (empty($shipSetting)) {
                echo json_encode(array('success' => false, 'msg' => '物流方式选择错误'));
                return;
            }
            $shipFee = $shipSetting['WeshareShipSetting']['ship_fee'];
            $address = $this->get_order_address($weshareId, $shipInfo, $buyerData, $uid);
            $orderData = array('creator' => $uid, 'consignee_address' => $address, 'member_id' => $weshareId, 'type' => ORDER_TYPE_WESHARE_BUY, 'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'), 'consignee_id' => $addressId, 'consignee_name' => $buyerData['name'], 'consignee_mobilephone' => $buyerData['mobilephone']);
            if ($shipType == SHARE_SHIP_PYS_ZITI) {
                $orderData['ship_mark'] = SHARE_SHIP_PYS_ZITI_TAG;
            }
            if ($shipType == SHARE_SHIP_SELF_ZITI) {
                $orderData['ship_mark'] = SHARE_SHIP_SELF_ZITI_TAG;
            }
            if ($shipType == SHARE_SHIP_KUAIDI) {
                $orderData['ship_mark'] = SHARE_SHIP_KUAIDI_TAG;
            }
            $order = $this->Order->save($orderData);
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
            $totalPrice += $shipFee;
            if ($this->Order->updateAll(array('total_all_price' => $totalPrice / 100, 'total_price' => $totalPrice / 100, 'ship_fee' => $shipFee), array('id' => $orderId))) {
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
        $result = $this->Order->updateAll(array('status' => ORDER_STATUS_RECEIVED, 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $order['Order']['id']));
        $this->Cart->updateAll(array('status' => ORDER_STATUS_RECEIVED), array('order_id' => $order['Order']['id']));
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
        $my_create_share_ids = Hash::extract($myCreateShares, '{n}.Weshare.id');
        $orderStatus = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE);
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
        $joinShareOrderStatus = Hash::combine($joinShareOrder, '{n}.Order.member_id', '{n}.Order.status');
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
        $shareCommentData = $this->getSharerCommentData($my_create_share_ids, $uid);
        $this->explode_share_imgs($myCreateShares);
        $this->explode_share_imgs($myJoinShares);
        $this->set($userShareSummery);
        $this->set('is_me', $uid == $current_uid);
        $this->set('share_user', $shareUser);
        $this->set('creators', $creators);
        $this->set('my_create_shares', $myCreateShares);
        $this->set('my_join_shares', $myJoinShares);
        $this->set('sharer_comment_data', $shareCommentData);
        $this->set('is_verify_user', $this->is_verify_sharer($uid));
        $canSupportOfflineStore = $this->sharer_can_use_we_ship($uid);
        $this->set('is_support_offline_store', $canSupportOfflineStore > 0);
        $this->set('join_share_order_status', $joinShareOrderStatus);
    }

    public function set_order_ship_code() {
        $this->autoRender = false;
        $order_id = $_REQUEST['order_id'];
        $ship_company_id = $_REQUEST['company_id'];
        $weshare_id = $_REQUEST['weshare_id'];
        $ship_code = $_REQUEST['ship_code'];
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED, 'ship_type' => $ship_company_id, 'ship_code' => "'" . $ship_code . "'", 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $order_id, 'status' => ORDER_STATUS_PAID));
        $this->Cart->updateAll(array('status' => ORDER_STATUS_RECEIVED), array('order_id' => $order_id));
        $this->WeshareBuy->send_share_product_ship_msg($order_id, $weshare_id);
        echo json_encode(array('success' => true));
        return;
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
        //update order status
        $prepare_update_orders = $this->Order->find('all', array(
            'conditions' => array('status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED), 'type' => ORDER_TYPE_WESHARE_BUY, 'ship_mark' => SHARE_SHIP_SELF_ZITI_TAG, 'member_id' => $weshare_id),
            'fields' => array('id')
        ));
        $prepare_update_order_ids = Hash::extract($prepare_update_orders, '{n}.Order.id');
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED, 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $prepare_update_order_ids));
        $this->Cart->updateAll(array('status' => ORDER_STATUS_SHIPPED), array('order_id' => $prepare_update_order_ids));
        $this->process_send_msg($share_info, $msg);
        echo json_encode(array('success' => true));
        return;
    }

    public function share_order_list($weshareId) {
        $this->layout = 'weshare_bootstrap';
        $user_id = $this->currentUser['id'];
        $weshare = $this->Weshare->find('first', array(
            'conditions' => array('id' => $weshareId, 'creator' => $user_id)
        ));
        if (empty($weshare)) {
            $this->redirect("/weshares/view/" . $weshareId);
        }
        $statics_data = $this->get_weshare_buy_info($weshareId, true, true);
        $this->set($statics_data);
        $this->set('ship_type_list', ShipAddress::ship_type_list());
        $this->set('hide_footer', true);
        $this->set('user_id', $user_id);
        $this->set('weshareId', $weshareId);
    }

    public function loadComment($weshareId) {
        $this->autoRender = false;
        $comment_data = $this->WeshareBuy->load_comment_by_share_id($weshareId);
        echo json_encode($comment_data);
        return;
    }

    public function comment() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $params = json_decode(file_get_contents('php://input'), true);
        $order_id = $params['order_id'];
        $comment_content = $params['comment_content'];
        $reply_comment_id = $params['reply_comment_id'];
        $share_id = $params['share_id'];
        $result = $this->WeshareBuy->create_share_comment($order_id, $comment_content, $reply_comment_id, $uid, $share_id);
        echo json_encode($result);
        return;
    }

    //TODO delete not use product
    private function saveWeshareProducts($weshareId, $weshareProductData) {
        if (empty($weshareProductData)) {
            return;
        }
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


    private function saevWeshareShipType($weshareId, $weshareShipData) {
        foreach ($weshareShipData as &$item) {
            $item['weshare_id'] = $weshareId;
        }
        return $this->WeshareShipSetting->saveAll($weshareShipData);
    }

    //TODO delete not use address
    private function saveWeshareAddresses($weshareId, $weshareAddressData) {
        if (empty($weshareAddressData)) {
            return;
        }
        foreach ($weshareAddressData as &$address) {
            $address['weshare_id'] = $weshareId;
        }
        return $this->WeshareAddress->saveAll($weshareAddressData);
    }

    private function get_weshare_buy_info($weshareId, $is_me, $division = false) {
        return $this->WeshareBuy->get_share_order_for_show($weshareId, $is_me, $division);
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
        $weshareShipSettings = $this->WeshareShipSetting->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshareId
            )
        ));
        $weshareShipSettings = Hash::combine($weshareShipSettings, '{n}.WeshareShipSetting.tag', '{n}.WeshareShipSetting');
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
        $weshareInfo['ship_type'] = $weshareShipSettings;
        $weshareInfo['images'] = array_filter(explode('|', $weshareInfo['images']));
        return $weshareInfo;

    }

    private function setShareConsignees($userInfo, $mobileNum, $address, $uid, $offlineStoreId = 0) {
        $consignee = $this->OrderConsignees->find('first', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => STATUS_CONSIGNEES_SHARE
            ),
            'fields' => array('id', 'name', 'mobilephone')
        ));
        if (!empty($consignee)) {
            //update
            $saveData = array('name' => "'" . $userInfo . "'", 'mobilephone' => "'" . $mobileNum . "'", 'address' => "'" . $address . "'");
            if ($offlineStoreId != 0) {
                $saveData['ziti_id'] = $offlineStoreId;
            }
            $this->OrderConsignees->updateAll($saveData, array('id' => $consignee['OrderConsignees']['id']));
            return;
        }
        //save
        $this->OrderConsignees->save(array('creator' => $uid, 'status' => STATUS_CONSIGNEES_SHARE, 'name' => $userInfo, 'mobilephone' => $mobileNum, 'address' => $address, 'ziti_id' => $offlineStoreId));
    }

    private function getShareConsignees($uid) {
        $consignee = $this->OrderConsignees->find('first', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => STATUS_CONSIGNEES_SHARE
            ),
            'fields' => array('name', 'mobilephone', 'address', 'ziti_id')
        ));
        //load remember offline store id
        $ziti_id = $consignee['OrderConsignees']['ziti_id'];
        if ($ziti_id) {
            $offlineStore = $this->OfflineStore->findById($ziti_id);
            if (!empty($offlineStore)) {
                $consignee['OrderConsignees']['offlineStore'] = $offlineStore['OfflineStore'];
            }
        }
        return $consignee['OrderConsignees'];
    }

    private function getSharerCommentData($weshare_ids, $sharer_id) {
        return $this->WeshareBuy->load_sharer_comment_data($weshare_ids, $sharer_id);
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
            'fields' => array('DISTINCT creator')
        ));
        return array('share_count' => count($weshares), 'follower_count' => $follower_count);
    }

    private function getWeshareShipSettings($weshareId) {
        $shareShipSettings = $this->WeshareShipSetting->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshareId
            )
        ));
        $shareShipSettings = Hash::combine($shareShipSettings, '{n}.WeshareShipSetting.tag', '{n}.WeshareShipSetting');
        return $shareShipSettings;
    }

    private function explode_share_imgs(&$shares) {
        foreach ($shares as &$item) {
            $item['Weshare']['images'] = explode('|', $item['Weshare']['images']);
        }
    }

    private function process_send_msg($shareInfo, $msg) {
        $this->WeshareBuy->send_share_product_arrive_msg($shareInfo, $msg);
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
        return $this->RedPacket->process_receive($share_offer_id, $uid, $this->is_weixin());
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
        if ($get_coupon_result['accepted'] && $get_coupon_result['just_accepted'] == 0) {
            $this->set('get_coupon_type', 'accepted');
            return;
        }
        $this->set('get_coupon_type', 'got');
        $this->set('couponNum', $get_coupon_result['couponNum']);
    }

    private function sharer_can_use_we_ship($sharer) {
        $ship_setting = $this->SharerShipOption->find('first', array(
            'conditions' => array(
                'sharer_id' => $sharer
            )
        ));
        if (empty($ship_setting)) {
            return 0;
        }
        $ship_set_type = $ship_setting['SharerShipOption']['type'];
        return $ship_set_type;
    }

    //check order ship type gen order address
    private function get_order_address($weshareId, $shipInfo, $buyerData, $uid) {
        $shipType = $shipInfo['ship_type'];
        $addressId = $shipInfo['address_id'];
        $customAddress = $buyerData['address'];
        if ($shipType == SHARE_SHIP_PYS_ZITI) {
            $offline_store_id = $addressId;
        }
        $this->setShareConsignees($buyerData['name'], $buyerData['mobilephone'], $buyerData['address'], $uid, $offline_store_id);
        if ($shipType == SHARE_SHIP_KUAIDI) {
            return $customAddress;
        }
        if ($shipType == SHARE_SHIP_SELF_ZITI) {
            $tinyAddress = $this->WeshareAddress->find('first', array(
                'conditions' => array(
                    'id' => $addressId,
                    'weshare_id' => $weshareId
                )
            ));
            $address = $tinyAddress['WeshareAddress']['address'];
            if ($customAddress) {
                $address = $address;
            }
            return $address;
        }
        if ($shipType == SHARE_SHIP_PYS_ZITI) {
            $offline_store = $this->OfflineStore->findById($addressId);
            $address = $offline_store['OfflineStore']['name'];
            if ($customAddress) {
                $address = $address;
            }
            return $address;
        }
    }

    private function get_ship_set($id, $weshareId) {
        return $this->WeshareShipSetting->find('first', array(
            'conditions' => array(
                'id' => $id,
                'weshare_id' => $weshareId
            )
        ));
    }

    private function check_product_store($weshareProducts, $weshareId, $productIdNumMap) {
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
            return array('success' => false, 'reason' => $reason);
        }
        return null;
    }

    private function is_verify_sharer($uid){
        $uids = array(633345,802852,544307,811917);
        return in_array($uid,$uids);
    }
}