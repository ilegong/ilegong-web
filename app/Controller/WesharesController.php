<?php

class WesharesController extends AppController {

    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User', 'OrderConsignees', 'Oauthbind', 'SharedOffer', 'CouponItem',
        'SharerShipOption', 'WeshareShipSetting', 'OfflineStore', 'UserRelation', 'Comment', 'RebateTrackLog', 'ProxyRebatePercent', 'ShareUserBind', 'UserSubReason');

    var $query_user_fileds = array('id', 'nickname', 'image', 'wx_subscribe_status', 'description', 'is_proxy');

    var $components = array('Weixin', 'WeshareBuy', 'Buying', 'RedPacket', 'ShareUtil', 'ShareAuthority');

    var $share_ship_type = array('self_ziti', 'kuaidi', 'pys_ziti');

    var $pay_type = 1;

    const PROCESS_SHIP_MARK_DEFAULT_RESULT = 0;
    const PROCESS_SHIP_MARK_UNFINISHED_RESULT = 1;

    public function __construct($request = null, $response = null) {
        $this->helpers[] = 'PhpExcel';
        parent::__construct($request, $response);
    }

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    /**
     * @param $tag
     * 首页
     */
    public function index($tag = 0) {
        $this->layout = null;
        $products = $this->ShareUtil->get_share_index_product($tag);
        $uid = $this->currentUser['id'];
        $this->set('products', $products);
        $this->set('uid', $uid);
        $this->set('tag', $tag);
    }

    /**
     * @param string $weshare_id
     * @param int $from 标示从什么地方跳转的访问
     * 跳转到分享的详情页
     */
    public function view($weshare_id, $from = 0) {
        $uid = $this->currentUser['id'];
        //check has sharer has red packet
        //领取红包
        $shared_offer_id = $_REQUEST['shared_offer_id'];
        //has share offer id user open share
        //用户抢红包
        if (!empty($shared_offer_id)) {
            //process
            $this->process_shared_offer($shared_offer_id);
        } else {
            //use cache
            //$weshare = $this->Weshare->find('first', array('conditions' => array('id' => $weshare_id)));
            $weshare = $this->WeshareBuy->get_weshare_info($weshare_id);
            $weshare_creator = $weshare['creator'];
            $shared_offers = $this->SharedOffer->find_new_offers_by_weshare_creator($uid, $weshare_creator);
            //get first offer
            if (!empty($shared_offers)) {
                $this->set('shared_offer_id', $shared_offers[0]['SharedOffer']['id']);
                $this->set('from', $this->pay_type);
            }
            if ($from == 1) {
                $paidMsg = $_REQUEST['msg'];
                if (!empty($paidMsg) && $paidMsg == 'ok') {
                    $this->set('from', $this->pay_type);
                } else {
                    //TODO check pay fail issue
                    $this->log('paid fail msg ' . $paidMsg);
                }
            }
        }
        $this->set('weshare_id', $weshare_id);
        //form paid done
        $this->log('weshare view mark ' . $_REQUEST['mark']);
        //获取推荐人
        $recommend = $_REQUEST['recommend'];
        //add rebate log
        if ($this->ShareUtil->is_proxy_user($recommend)) {
            if (!empty($recommend) && !empty($uid)) {
                $rebateLogId = $this->ShareUtil->save_rebate_log($recommend, $uid, $weshare_id);
                $this->set('recommend_id', $recommend);
                $this->set('rebateLogId', $rebateLogId);
            }
        }
    }

    /**
     * @param $weshareId
     * 更新 分享信息
     */
    public function update($weshareId) {
        $uid = $this->currentUser['id'];
        $weshareInfo = $this->get_weshare_detail($weshareId);
        $can_edit_share = $this->ShareAuthority->user_can_edit_share_info($uid, $weshareId);
        if ($uid != $weshareInfo['creator']['id']&&!$can_edit_share) {
            $this->redirect('/weshares/view/' . $weshareId . '/0');
        }
        $share_ship_set = $this->sharer_can_use_we_ship($uid);
        if ($this->sharer_can_use_offline_address($uid)) {
            $this->set('can_use_offline_address', 1);
        }
        $this->set('ship_type', $share_ship_set);
        $this->set('weshare_id', $weshareId);
        $this->set('user_id', $uid);
    }

    /**
     * @param $weshareId
     * 分享的详细信息
     */
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

    /**
     * 添加分享页面
     * 判断用户是否绑定 手机 等信息
     */
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
            $ref_url = WX_HOST . '/weshares/add';
            $this->redirect('/users/to_bind_mobile?from=share&ref=' . $ref_url);
            return;
        }
        if (empty($current_user['User']['payment'])) {
            $this->redirect('/users/complete_user_info?from=share');
            return;
        }
        $share_ship_set = $this->sharer_can_use_we_ship($uid);
        if ($this->sharer_can_use_offline_address($uid)) {
            $this->set('can_use_offline_address', 1);
        }
        $this->set('ship_type', $share_ship_set);
        $this->set('user_id', $uid);
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

    /**
     * 保存分享数据
     */
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
        //create save creator
        if(empty($postDataArray['id'])){
            $weshareData['creator'] = $uid;
        }
        $weshareData['created'] = date('Y-m-d H:i:s');
        $images = $postDataArray['images'];
        $weshareData['images'] = implode('|', $images);
        $productsData = $postDataArray['products'];
        $addressesData = $postDataArray['addresses'];
        $shipSetData = $postDataArray['ship_type'];
        $proxyRebatePercent = $postDataArray['proxy_rebate_percent'];
        $weshareData['creator'] = $uid;
        //merge for child share data
        $saveBuyFlag = $weshare = $this->Weshare->save($weshareData);
        //merge for child share data
        $this->saveWeshareProducts($weshare['Weshare']['id'], $productsData);
        $this->saveWeshareAddresses($weshare['Weshare']['id'], $addressesData);
        $this->saveWeshareShipType($weshare['Weshare']['id'], $shipSetData);
        $this->saveWeshareProxyPercent($weshare['Weshare']['id'], $proxyRebatePercent);
        //clear cache
        //SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weshareId
        Cache::write(SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weshare['Weshare']['id'] . '_0', '');
        Cache::write(SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weshare['Weshare']['id'] . '_1', '');
        //SHARE_SHIP_SETTINGS_CACHE_KEY . '_' . $weshareId;
        Cache::write(SHARE_SHIP_SETTINGS_CACHE_KEY . '_' . $weshare['Weshare']['id'], '');
        //SIMPLE_SHARE_INFO_CACHE_KEY . '_' . $share_id
        Cache::write(SIMPLE_SHARE_INFO_CACHE_KEY . '_' . $weshare['Weshare']['id'], '');
        //SHARE_USER_SUMMERY_CACHE_KEY . '_' . $uid;
        if ($saveBuyFlag) {
            if (empty($weshareData['id'])) {
                //create
                Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $uid, '');
                $thumbnail = null;
                if (count($images) > 0) {
                    $thumbnail = $images[0];
                }
                $this->ShareUtil->save_create_share_opt_log($weshare['Weshare']['id'], $thumbnail, $weshareData['title'], $uid);
                //  $this->check_share_and_trigger_new_share($weshare['Weshare']['id'], $shipSetData);
            }
            //todo update child share data and product data
            //$this->ShareUtil->cascadeSaveShareData($weshareData);
            echo json_encode(array('success' => true, 'id' => $weshare['Weshare']['id']));
            return;
        } else {
            echo json_encode(array('success' => false, 'uid' => $uid));
            return;
        }
    }

    /**
     * @param $shareId
     * @param $shipSetData
     * 触发生成拼团的分享
     */
    private function check_share_and_trigger_new_share($shareId, $shipSetData) {
        foreach ($shipSetData as $item) {
            if ($item['tag'] == SHARE_SHIP_GROUP_TAG) {
                if ($item['status'] == PUBLISH_YES && $item['limit'] > 0) {
                    $this->ShareUtil->new_static_address_group_shares($shareId);
                }
                break;
            }
        }
    }

    public function load_share_comments($sharer_id) {
        $this->autoRender = false;
        $share_all_comments = $this->WeshareBuy->load_sharer_comments($sharer_id);
        echo json_encode($share_all_comments);
        return;
    }

    /**
     * @param $shareId
     * @param $page
     * 获取分享订单信息 根据分享ID和页码
     */
    public function get_share_order_by_page($shareId, $page){
        $this->autoRender=false;
        $uid = $this->currentUser['id'];
        $ordersDetail = $this->WeshareBuy->get_share_detail_view_orders($shareId, $page, $uid);
        echo json_encode($ordersDetail);
        return;
    }

    /**
     * @param $shareId
     * ajax 获取购买信息 拆分优化加载
     * 暂时不使用了
     */
    public function get_share_order_detail($shareId) {
        $this->autoRender = false;
        $child_share_data = $this->WeshareBuy->get_child_share_items($shareId);
        $ordersDetail = $this->get_weshare_buy_info($shareId, true);
        echo json_encode(array('ordersDetail' => $ordersDetail, 'childShareData' => $child_share_data));
        return;
    }

    /**
     * @param $shareId
     * ajax 获取用户的订单和子分享数据
     */
    public function get_share_user_order_and_child_share($shareId){
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $child_share_data = $this->WeshareBuy->get_child_share_items($shareId);
        $ordersDetail = $this->WeshareBuy->get_current_user_share_order_data($shareId, $uid);
        $share_summery = $this->WeshareBuy->get_share_buy_summery($shareId);
        $ordersDetail['summery'] = $share_summery;
        echo json_encode(array('ordersDetail' => $ordersDetail, 'childShareData' => $child_share_data));
        return;
    }

    /**
     * @param $share_id
     * ajax 获取线下自提点 信息
     */
    public function get_offline_address_detail($share_id) {
        $this->autoRender = false;
        $offline_address_data = $this->ShareUtil->get_share_offline_address_detail($share_id);
        echo json_encode($offline_address_data);
        return;
    }

    /**
     * @param $weshareId
     * ajax 获取分享的详细信息
     */
    public function detail($weshareId) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $weshareInfo = $this->get_weshare_detail($weshareId, true);
        $is_me = $uid == $weshareInfo['creator']['id'];
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
        if (!$is_me) {
            $sub_status = $this->WeshareBuy->check_user_subscribe($weshareInfo['creator']['id'], $uid);
        } else {
            $sub_status = true;
        }
        $consignee = $this->getShareConsignees($uid);
        $creatorId = $weshareInfo['creator']['id'];
        $user_share_summery = $this->getUserShareSummery($creatorId);
        $share_ship_set = $this->sharer_can_use_we_ship($weshareInfo['creator']['id']);
        $my_coupon_items = $this->get_can_used_coupons($uid, $creatorId);
        $weshare_ship_settings = $this->getWeshareShipSettings($weshareId);
        $comment_data = $this->WeshareBuy->load_comment_by_share_id($weshareId);
        $recommend_data = $this->WeshareBuy->load_share_recommend_data($weshareId);
        $is_manage_user = $this->ShareAuthority->user_can_view_share_order_list($uid, $weshareId);
        $can_edit_share = $this->ShareAuthority->user_can_edit_share_info($uid, $weshareId);
        $share_order_count = $this->WeshareBuy->get_share_all_buy_count($weshareId);
        echo json_encode(array('support_pys_ziti' => $share_ship_set,
            'weshare' => $weshareInfo,
            'recommendData' => $recommend_data,
            'current_user' => $current_user['User'],
            'weixininfo' => $weixinInfo,
            'weshare_ship_settings' => $weshare_ship_settings,
            'consignee' => $consignee,
            'user_share_summery' => $user_share_summery,
            'my_coupons' => $my_coupon_items[0],
            'comment_data' => $comment_data,
            'sub_status' => $sub_status,
            'is_manage' => $is_manage_user,
            'can_edit_share' => $can_edit_share,
            'share_order_count' => $share_order_count
        ));
        return;
    }

    /**
     * @param $uid
     * @param $weshareId
     * @return array|null
     * 把微信分享的一些参数设置好
     */
    public function set_weixin_share_data($uid, $weshareId) {
        if (parent::is_weixin()) {
            $weixinJs = prepare_wx_share_log($uid, 'wsid', $weshareId);
            return $weixinJs;
        }
        return null;
    }

    /**
     * @param $orderId
     * @param $type
     * 去支付
     */
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

    /**
     * @param $orderId
     * 支付 尾款
     */
    public function pay_order_add($orderId) {
        $this->layout = 'weshare_bootstrap';
        $cart_info = $this->WeshareBuy->get_cart_name_and_num($orderId);
        $order_info = $this->WeshareBuy->get_order_info($orderId);
        if ($order_info['status'] == ORDER_STATUS_PAID) {
            $this->redirect('/weshares/view/' . $order_info['member_id']);
            return;
        }
        $this->set('cart_info', $cart_info);
        $this->set('order_info', $order_info);
    }

    /**
     * 不支付直接开启新的分享
     */
    public function start_new_group_share() {
        //不需要支付直接开团
        //check user has
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $postStr = file_get_contents('php://input');
        $postDataArray = json_decode($postStr, true);
        $weshareId = $postDataArray['weshare_id'];
        $address = $postDataArray['address'];
        $business_remark = $postDataArray['business_remark'];
        $result = $this->ShareUtil->cloneShare($weshareId, $uid, $address, $business_remark, GROUP_SHARE_TYPE, WESHARE_NORMAL_STATUS);
        //send template msg and clear cache
        if ($result['success']) {
            Cache::write(SHARE_OFFLINE_ADDRESS_BUY_DATA_CACHE_KEY . '_' . $weshareId, '');
            Cache::write(SHARE_OFFLINE_ADDRESS_SUMMERY_DATA_CACHE_KEY . '_' . $weshareId, '');
            $this->ShareUtil->trigger_send_new_share_msg($result['shareId'], $uid);
        }
        echo json_encode($result);
        return;
    }

    /**
     * 下单
     */
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
        $business_remark = $postDataArray['remark'];
        $buyerData = $postDataArray['buyer'];
        $rebateLogId = $postDataArray['rebate_log_id'];
        $is_start_new_group_share = $postDataArray['start_new_group_share'];
        $is_group_share_type = $postDataArray['is_group_share'];
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
            $orderData = array('cate_id' => $rebateLogId, 'creator' => $uid, 'consignee_address' => $address, 'member_id' => $weshareId, 'type' => ORDER_TYPE_WESHARE_BUY, 'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'), 'consignee_id' => $addressId, 'consignee_name' => $buyerData['name'], 'consignee_mobilephone' => $buyerData['mobilephone'], 'business_remark' => $business_remark);
            $process_shi_mark_result = $this->process_order_ship_mark($shipType, $orderData, $is_group_share_type);
            if ($process_shi_mark_result == self::PROCESS_SHIP_MARK_UNFINISHED_RESULT) {
                $this->process_ship_group($orderData, $shipInfo, $is_start_new_group_share, $weshareId, $uid, $address, $business_remark);
            }
            $order = $this->Order->save($orderData);
            $orderId = $order['Order']['id'];
            $totalPrice = 0;
            $is_prepaid = 0;
            foreach ($weshareProducts as $p) {
                $item = array();
                //check product is tbd to set order prepaid
                $tbd = $p['WeshareProduct']['tbd'];
                //商品价格待定
                if ($tbd == 1) {
                    //预付
                    $is_prepaid = 1;
                    $item['confirm_price'] = 0;
                }
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
                //set tag id
                $item['tag_id'] = $p['WeshareProduct']['tag_id'];
                $cart[] = $item;
                $totalPrice += $num * $price;
            }
            $this->Cart->saveAll($cart);
            $totalPrice += $shipFee;
            $update_order_data = array('total_all_price' => $totalPrice / 100, 'total_price' => $totalPrice / 100, 'ship_fee' => $shipFee, 'is_prepaid' => $is_prepaid);
            if ($is_prepaid) {
                $update_order_data['process_prepaid_status'] = ORDER_STATUS_PREPAID;
            }
            if ($this->Order->updateAll($update_order_data, array('id' => $orderId))) {
                $coupon_id = $postDataArray['coupon_id'];
                //check coupon
                if (!empty($coupon_id)) {
                    App::uses('OrdersController', 'Controller');
                    $this->Session->write(OrdersController::key_balanced_conpons(), json_encode(array(0 => array($coupon_id))));
                    $this->order_use_score_and_coupon($orderId, $uid, 0, $totalPrice / 100);
                }
                $this->ShareUtil->update_rebate_log_order_id($rebateLogId, $orderId, $weshareId);
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

    public function save_tags() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $postStr = file_get_contents('php://input');
        $tags = json_decode($postStr, true);
        $tags = $this->ShareUtil->save_tags_return($tags, $uid);
        echo json_encode(array('success' => true, 'tags' => $tags));
        return;
    }

    public function get_tags() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $tags = $this->ShareUtil->get_tags_list($uid);
        echo json_encode(array('tags' => $tags));
        return;
    }

    /**
     * @param $order_id
     * 确认收货
     */
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
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_0', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_0', '');
        if (!$result) {
            echo json_encode(array(success => false, reason => "failed to update order status"));
            return;
        }

        echo json_encode(array(success => true));
    }

    /**
     * @param $weShareId
     * 截止分享
     */
    public function stopShare($weShareId) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $this->Weshare->updateAll(array('status' => WESHARE_STOP_STATUS), array('id' => $weShareId, 'creator' => $uid, 'status' => WESHARE_NORMAL_STATUS));
        //stop child share
        $this->Weshare->updateAll(array('status' => WESHARE_STOP_STATUS), array('refer_share_id' => $weShareId, 'status' => WESHARE_NORMAL_STATUS, 'type' => GROUP_SHARE_TYPE));
        //SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weshareId . '_1'
        //SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weshareId . '_1'
        Cache::write(SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weShareId . '_0', '');
        Cache::write(SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weShareId . '_1', '');
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $shareId
     */
    public function cloneShare($shareId) {
        $this->autoRender = false;
        $result = $this->ShareUtil->cloneShare($shareId);
        echo json_encode($result);
        return;
    }


    /**
     * @param null $uid
     * 获取分享用户的个人中心,$uid为空 获取当前用户的
     */
    public function user_share_info($uid = null) {
        $this->layout = 'weshare_bootstrap';
        $current_uid = $this->currentUser['id'];
        if (empty($uid)) {
            $uid = $current_uid;
        }
        $user_share_data = $this->WeshareBuy->prepare_user_share_info($uid);
        $creators = $user_share_data['creators'];
        $my_create_share_ids = $user_share_data['my_create_share_ids'];
        $joinShareOrderStatus = $user_share_data['joinShareOrderStatus'];
        $myCreateShares = $user_share_data['myCreateShares'];
        $myJoinShares = $user_share_data['myJoinShares'];
        $joinShareComments = $user_share_data['joinShareComments'];
        $shareUser = $creators[$uid];
        $this->set_share_user_info_weixin_params($uid, $current_uid, $shareUser);
        $userShareSummery = $this->getUserShareSummery($uid);
        $shareCommentData = $this->getSharerCommentData($my_create_share_ids, $uid);
        $userCommentData = $this->WeshareBuy->load_user_share_comments($uid);
        $userFansData = $this->WeshareBuy->get_user_fans_data($uid, 100);
        $userFocusData = $this->WeshareBuy->get_user_focus($uid, 100);
        if ($uid != $current_uid) {
            $sub_status = $this->WeshareBuy->check_user_subscribe($uid, $current_uid);
            $this->set('sub_status', $sub_status);
        }
        $user_is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if ($user_is_proxy) {
            $this->set('is_proxy', true);
        }
        if ($uid == $current_uid) {
            $rebate_money = $this->ShareUtil->get_rebate_money($current_uid);
            $this->set('rebate_money', $rebate_money);
            $this->set('show_rebate_money', $rebate_money > 0);
        }
        $this->set($userShareSummery);
        $this->set('is_me', $uid == $current_uid);
        $this->set('current_uid', $current_uid);
        $this->set('visitor', $current_uid);
        $this->set('share_user', $shareUser);
        $this->set('creators', $creators);
        $this->set('my_create_shares', $myCreateShares);
        $this->set('my_join_shares', $myJoinShares);
        $this->set('sharer_comment_data', $shareCommentData);
        $this->set('user_comment_data', $userCommentData);
        $this->set('is_verify_user', $this->is_verify_sharer($uid));
        $canSupportOfflineStore = $this->sharer_can_use_we_ship($uid);
        $this->set('is_support_offline_store', $canSupportOfflineStore > 0);
        $this->set('join_share_order_status', $joinShareOrderStatus);
        $this->set('fans_data', $userFansData);
        $this->set('focus_data', $userFocusData);
        $this->set('joinShareComments', $joinShareComments);
    }

    /**
     * 分享订单 快递发货
     */
    public function set_order_ship_code() {
        $this->autoRender = false;
        $order_id = $_REQUEST['order_id'];
        $ship_company_id = $_REQUEST['company_id'];
        $weshare_id = $_REQUEST['weshare_id'];
        $ship_code = $_REQUEST['ship_code'];
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED, 'ship_type' => $ship_company_id, 'ship_code' => "'" . $ship_code . "'", 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $order_id, 'status' => ORDER_STATUS_PAID));
        $this->Cart->updateAll(array('status' => ORDER_STATUS_RECEIVED), array('order_id' => $order_id));
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_0', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_0', '');
        $this->WeshareBuy->send_share_product_ship_msg($order_id, $weshare_id);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 自有自提点 发货
     */
    public function send_arrival_msg() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $params = json_decode(file_get_contents('php://input'), true);
        $msg = $params['msg'];
        $weshare_id = $params['share_id'];
        $idsStr = $params['ids'];
        $orderIds = explode(',', $idsStr);
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
            'conditions' => array('status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED), 'type' => ORDER_TYPE_WESHARE_BUY, 'ship_mark' => SHARE_SHIP_SELF_ZITI_TAG, 'member_id' => $weshare_id, 'id' => $orderIds),
            'fields' => array('id')
        ));
        $prepare_update_order_ids = Hash::extract($prepare_update_orders, '{n}.Order.id');
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED, 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $prepare_update_order_ids));
        $this->Cart->updateAll(array('status' => ORDER_STATUS_SHIPPED), array('order_id' => $prepare_update_order_ids));
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_0', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_0', '');
        $this->process_send_msg($share_info, $msg);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 设置子分享发货
     */
    public function set_share_shipped() {
        $this->autoRender = false;
        $weshare_id = $_REQUEST['share_id'];
        $refer_share_id = $_REQUEST['refer_share_id'];
        $address = $_REQUEST['address'];
        $msg = $_REQUEST['msg'];
        //update share status
        $this->Weshare->updateAll(array('order_status' => WESHARE_ORDER_STATUS_SHIPPED), array('id' => $weshare_id, 'order_status' => WESHARE_ORDER_STATUS_WAIT_SHIP));
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_0', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_0', '');
        $this->WeshareBuy->send_group_share_product_arrival_msg($weshare_id, $refer_share_id, $msg, $address);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $sharer_id
     * @param $user_id
     * @param $from_type
     * @param $share_id
     * 关注分享者
     */
    public function subscribe_sharer($sharer_id, $user_id, $from_type = 0, $share_id = 0) {
        $this->autoRender = false;
        //没有关注服务号
        if (user_subscribed_pys($user_id) != WX_STATUS_SUBSCRIBED) {
            //save sub reason
            $sub_type = $from_type == 0 ? SUB_SHARER_REASON_TYPE_FROM_USER_CENTER : SUB_SHARER_REASON_TYPE_FROM_SHARE_INFO;
            $data_id = $from_type == 0 ? $sharer_id : $share_id;
            $url = $from_type == 0 ? WX_HOST . '/weshares/user_share_info/' . $sharer_id : WX_HOST . '/weshares/view/' . $share_id;
            $nicknames = $this->WeshareBuy->get_users_nickname(array($sharer_id, $user_id));
            $title = $nicknames[$user_id] . '，你好，您已经关注' . $nicknames[$sharer_id];
            $this->UserSubReason->save(array('type' => $sub_type, 'url' => $url, 'user_id' => $user_id, 'title' => $title, 'data_id' => $data_id));
            echo json_encode(array('success' => false, 'reason' => 'not_sub'));
            return;
        }
        $this->WeshareBuy->subscribe_sharer($sharer_id, $user_id);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $sharer_id
     * @param $user_id
     * 取消关注分享者
     */
    public function unsubscribe_sharer($sharer_id, $user_id) {
        $this->autoRender = false;
        $this->WeshareBuy->unsubscribe_sharer($sharer_id, $user_id);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $weshareId
     * 分享者订单统计页面
     */
    public function share_order_list($weshareId) {
        $this->layout = 'weshare_bootstrap';
        $user_id = $this->currentUser['id'];
        $weshare = $this->Weshare->find('first', array(
            'conditions' => array('id' => $weshareId)
        ));
        if (empty($weshare)) {
            $this->redirect("/weshares/view/" . $weshareId);
        }
        $is_manage = $this->ShareAuthority->user_can_view_share_order_list($user_id, $weshareId);
        if ($weshare['Weshare']['creator'] != $user_id && !$is_manage) {
            $this->redirect("/weshares/view/" . $weshareId);
        }
        //获取子分享数据
        $child_share_data = $this->WeshareBuy->get_child_share_items($weshareId);
        //获取产品标签数据
        $share_tags = $this->ShareUtil->get_share_tags($weshareId);
        $all_tag_ids = Hash::extract($share_tags['tags'], '{n}.WeshareProductTag.id');
        //should show all tag
        if ($weshare['Weshare']['creator'] == $user_id || count($share_tags['tags']) <= 1) {
            $this->set('show_tag_all', true);
        }
        //存在多个商品标签不是管理员进来管理
        if (count($share_tags) > 0 && $weshare['Weshare']['creator'] != $user_id) {
            $all_tag_ids = $this->ShareAuthority->get_user_can_view_order_tags($user_id, $weshareId);
        }
        //获取订单统计数据
        $statics_data = $this->get_weshare_buy_info($weshareId, true, true);
        if (count($share_tags['tags']) > 0) {
            $tag_order_summery = $this->ShareUtil->summery_order_data_by_tag($statics_data, $weshareId);
            $this->set('tag_order_summery', $tag_order_summery);
        }
        $child_share_summery_datas = array();
        foreach ($child_share_data['child_share_ids'] as $child_share_id) {
            $child_share_summery_datas[$child_share_id] = $this->WeshareBuy->get_child_share_summery($child_share_id, $weshareId);
        }

        //$this->set('child_share_summery_datas', $child_share_summery_datas);
        $this->merge_child_share_summery_data($statics_data, $child_share_summery_datas);
        $share_ids = $child_share_data['child_share_ids'];
        $share_ids[] = $weshareId;

        $refund_money = $this->WeshareBuy->get_refund_money_by_weshare($share_ids);
        $rebate_money = $this->ShareUtil->get_share_rebate_money($share_ids);
        $repaid_order_money = $this->WeshareBuy->get_added_order_repaid_money($share_ids);

        $this->set($child_share_data);
        $this->set($statics_data);
        $this->set('weshare_info', $weshare);
        $this->set('tags', $share_tags['tags']);
        $this->set('show_tag_ids', $all_tag_ids);
        $this->set('product_tag_map', $share_tags['product_tag_map']);
        $this->set('refund_money', $refund_money);
        $this->set('rebate_money', $rebate_money);
        $this->set('repaid_order_money', $repaid_order_money);
        $this->set('ship_type_list', ShipAddress::ship_type_list());
        $this->set('hide_footer', true);
        $this->set('user_id', $user_id);
        $this->set('weshareId', $weshareId);
    }

    /**
     * @param $parent_summery_data
     * @param $child_share_datas
     * 自分享汇总数据合并
     */
    private function merge_child_share_summery_data(&$parent_summery_data, $child_share_datas) {
        $parent_summery_data['child_share_order_count'] = 0;
        foreach ($child_share_datas as $child_share_data_item) {
            $child_share_summery_details = $child_share_data_item['summery']['details'];
            foreach ($child_share_summery_details as $pid => $summery_detail_item) {
                if (empty($parent_summery_data['summery']['details'][$pid]['name'])) {
                    $parent_summery_data['summery']['details'][$pid]['name'] = $summery_detail_item['name'];
                }
                $parent_summery_data['summery']['details'][$pid]['num'] = $parent_summery_data['summery']['details'][$pid]['num'] + $summery_detail_item['num'];
                $parent_summery_data['summery']['details'][$pid]['total_price'] = $parent_summery_data['summery']['details'][$pid]['total_price'] + $summery_detail_item['total_price'];
            }
            $parent_summery_data['summery']['all_buy_user_count'] = $child_share_data_item['summery']['all_buy_user_count'] + $parent_summery_data['summery']['all_buy_user_count'];
            $parent_summery_data['summery']['all_total_price'] = $child_share_data_item['summery']['all_total_price'] + $parent_summery_data['summery']['all_total_price'];
            $parent_summery_data['summery']['real_total_price'] = $child_share_data_item['summery']['real_total_price'] + $parent_summery_data['summery']['real_total_price'];
            $parent_summery_data['summery']['all_coupon_price'] = $child_share_data_item['summery']['all_coupon_price'] + $parent_summery_data['summery']['all_coupon_price'];

            $parent_summery_data['child_share_order_count'] = $parent_summery_data['child_share_order_count'] + $child_share_data_item['summery']['all_buy_user_count'];

            $rebate_logs = $child_share_data_item['rebate_logs'];
            $parent_summery_data['rebate_logs'] = array_merge($parent_summery_data['rebate_logs'], $rebate_logs);
            $share_rebate_money = $child_share_data_item['share_rebate_money'];
            $parent_summery_data['share_rebate_money'] = $share_rebate_money;
            $refund_money = $child_share_data_item['refund_money'];
            $parent_summery_data['refund_money'] = $parent_summery_data['refund_money'] + $refund_money;
        }
    }

    /**
     * @param $weshareId
     * 客户端 重新load评论
     */
    public function loadComment($weshareId) {
        $this->autoRender = false;
        $comment_data = $this->WeshareBuy->load_comment_by_share_id($weshareId);
        echo json_encode($comment_data);
        return;
    }

    /**
     * 提交评论
     */
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

    /**
     * @param $weshare_id
     * 客户端调用发送购买进度消息
     * 可能执行任务比较长
     * 采用队列
     */
    public function send_buy_percent_msg($weshare_id) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $share_info = $this->get_weshare_detail($weshare_id);
        if ($share_info['creator']['id'] != $uid) {
            echo json_encode(array('success' => false, 'reason' => 'not_creator'));
            return;
        }
        $params = json_decode(file_get_contents('php://input'), true);
        $content = $params['content'];
        $type = $params['type'];
        if ($type == 0 || $type == '0') {
            $fansPageInfo = $this->WeshareBuy->get_user_relation_page_info($uid);
            $pageCount = $fansPageInfo['pageCount'];
            $pageSize = $fansPageInfo['pageSize'];
            $queue = new SaeTaskQueue('share');
            $queue->addTask("/weshares/process_send_buy_percent_msg/" . $weshare_id . "/" . $pageCount . "/" . $pageSize, "content=" . $content, true);
            //将任务推入队列
            $ret = $queue->push();
            //任务添加失败时输出错误码和错误信息
            if ($ret === false) {
                $this->log('add task queue error ' . json_encode(array($queue->errno(), $queue->errmsg())));
            }
            echo json_encode(array('success' => true));
            return;
        } else {
            $queue = new SaeTaskQueue('share');
            $queue->addTask("/weshares/process_notify_has_buy_fans/" . $weshare_id, "content=" . $content, true);
            //将任务推入队列
            $ret = $queue->push();
            //任务添加失败时输出错误码和错误信息
            if ($ret === false) {
                $this->log('add task queue error ' . json_encode(array($queue->errno(), $queue->errmsg())));
            }
            echo json_encode(array('success' => true));
            return;
        }
    }

    /**
     * @param $weshare_id
     * 发送建团消息 采用队列
     */
    public function send_new_share_msg($weshare_id) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $share_info = $this->get_weshare_detail($weshare_id);
        if ($share_info['creator']['id'] != $uid) {
            echo json_encode(array('success' => false, 'reason' => 'not_creator'));
            return;
        }
        $fansPageInfo = $this->WeshareBuy->get_user_relation_page_info($uid);
        $pageCount = $fansPageInfo['pageCount'];
        $pageSize = $fansPageInfo['pageSize'];
        $queue = new SaeTaskQueue('share');
        $queue->addTask("/weshares/process_send_new_share_msg/" . $weshare_id . '/' . $pageCount . '/' . $pageSize);
        //将任务推入队列
        $ret = $queue->push();
        //任务添加失败时输出错误码和错误信息
        if ($ret === false) {
            $this->log('add task queue error ' . json_encode(array($queue->errno(), $queue->errmsg())));
        }
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $shareId
     * @param $pageCount
     * @param $pageSize
     * 处理 建团消息 task
     */
    public function process_send_new_share_msg($shareId, $pageCount, $pageSize) {
        $this->autoRender = false;
        $queue = new SaeTaskQueue('tasks');
        $tasks = array();
        foreach (range(0, $pageCount) as $page) {
            $offset = $page * $pageSize;
            $tasks[] = array('url' => "/weshares/send_new_share_msg_task/" . $shareId . "/" . $pageSize . "/" . $offset);
        }
        $queue->addTask($tasks);
        $ret = $queue->push();
        //$this->WeshareBuy->send_new_share_msg($shareId);
        echo json_encode(array('success' => true, 'ret' => $ret));
        return;
    }

    /**
     * @param $shareId
     * @param $limit
     * @param $offset
     * 处理建团消息子任务
     */
    public function send_new_share_msg_task($shareId, $limit, $offset) {
        $this->autoRender = false;
        $this->WeshareBuy->send_new_share_msg($shareId, $limit, $offset);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $weshare_id
     * @param $pageCount
     * @param $pageSize
     * 发送团购进度消息任务
     */
    public function process_send_buy_percent_msg($weshare_id, $pageCount, $pageSize) {
        $this->autoRender = false;
        $queue = new SaeTaskQueue('tasks');
        $tasks = array();
        $msg_content = $_REQUEST['content'];
        foreach (range(0, $pageCount) as $page) {
            $offset = $page * $pageSize;
            $tasks[] = array('url' => "/weshares/send_buy_percent_msg_task/" . $weshare_id . "/" . $pageSize . "/" . $offset, "postdata" => "content=" . $msg_content);
        }
        $queue->addTask($tasks);
        $ret = $queue->push();
        echo json_encode(array('success' => true, 'ret' => $ret));
        return;
    }

    /**
     * @param $weshareId
     */
    public function process_notify_has_buy_fans($weshareId) {
        $this->autoRender = false;
        $msg_content = $_REQUEST['content'];
        $share_info = $this->get_weshare_detail($weshareId);
        $this->WeshareBuy->send_notify_buy_user_msg($share_info, $msg_content);
        echo json_encode(array('success' => true));
    }

    /**
     * @param $weshare_id
     * @param $limit
     * @param $offset
     * 发送团购进度消息子任务
     */
    public function send_buy_percent_msg_task($weshare_id, $limit, $offset) {
        $this->autoRender = false;
        $share_info = $this->get_weshare_detail($weshare_id);
        $msg_content = $_REQUEST['content'];
        $this->WeshareBuy->send_buy_percent_msg($share_info, $msg_content, $limit, $offset);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $shareId
     * @param $only_paid
     * export order to excel
     */
    public function order_export($shareId, $only_paid = 1) {
        $this->layout = null;
        if ($only_paid == 1) {
            $export_paid_order = true;
        } else {
            $export_paid_order = false;
        }
        $statics_data = $this->get_weshare_buy_info($shareId, true, false, $export_paid_order);
        //$refund_money = $this->WeshareBuy->get_refund_money_by_weshare($shareId);
        //$rebate_money = $this->ShareUtil->get_share_rebate_money($shareId);
        $this->set($statics_data);
        //$this->set('refund_money', $refund_money);
        //$this->set('rebate_money', $rebate_money);
    }

    /**
     * recommend share
     */
    public function recommend() {
        $this->autoRender = false;
        $params = json_decode(file_get_contents('php://input'), true);
        $memo = $params['recommend_content'];
        $userId = $params['recommend_user'];
        $shareId = $params['recommend_share'];
        $this->ShareUtil->saveShareRecommendLog($shareId, $userId, $memo);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * sharer refund money
     */
    public function refund_money() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'no_login'));
        }
        $shareId = $_REQUEST['shareId'];
        $share_info = $this->get_weshare_detail($shareId);
        if ($share_info['creator']['id'] != $uid) {
            echo json_encode(array('success' => false, 'reason' => 'not_creator'));
            return;
        }
        $orderId = $_REQUEST['orderId'];
        $refundMoney = $_REQUEST['refundMoney'];
        $refundMark = $_REQUEST['refundMark'];
        $result = $this->ShareUtil->refund($orderId, $refundMoney, $refundMark, 0);
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $shareId . '_1_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $shareId . '_0_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $shareId . '_1_0', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $shareId . '_0_0', '');
        echo json_encode($result);
        return;
    }

    /**
     * sharer confirm price
     */
    public function confirm_price() {
        $this->autoRender = false;
        $postData = $_REQUEST['data'];
        $postDataJson = json_decode($postData, true);
        $orderId = $postDataJson['order_id'];
        $orderCartMap = $postDataJson['cart_map'];
        $orderCartMap = Hash::combine($orderCartMap, '{n}.product_id', '{n}.price');
        $difference_price = $this->ShareUtil->process_order_prepaid($orderId, $orderCartMap);
        echo json_encode(array('success' => true, 'order_id' => $orderId, 'difference_price' => $difference_price));
        return;
    }

    /**
     * @param $weshareId
     * @param $weshareProxyPercent
     * 保存团长比例
     */
    private function saveWeshareProxyPercent($weshareId, $weshareProxyPercent) {
        $weshareProxyPercent['share_id'] = $weshareId;
        return $this->ProxyRebatePercent->save($weshareProxyPercent);
    }

    //TODO delete not use product
    /**
     * @param $weshareId
     * @param $weshareProductData
     * 保存分享商品
     */
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
            $tag_id = $product['tag_id'];
            if (empty($tag_id)) {
                $product['tag_id'] = 0;
            }
        }
        return $this->WeshareProduct->saveAll($weshareProductData);
    }

    /**
     * @param $weshareId
     * @param $weshareShipData
     * @return mixed
     * 保存分享的物流方式
     */
    private function saveWeshareShipType($weshareId, $weshareShipData) {
        foreach ($weshareShipData as &$item) {
            $item['weshare_id'] = $weshareId;
        }
        return $this->WeshareShipSetting->saveAll($weshareShipData);
    }

    /**
     * @param $weshareId
     * @param $weshareAddressData
     * 保存分享的 自有自提点
     */
    private function saveWeshareAddresses($weshareId, $weshareAddressData) {
        if (empty($weshareAddressData)) {
            return;
        }
        foreach ($weshareAddressData as &$address) {
            $address['weshare_id'] = $weshareId;
        }
        return $this->WeshareAddress->saveAll($weshareAddressData);
    }

    /**
     * @param $weshareId
     * @param $is_me
     * @param bool $division
     * @param bool $export
     * @return mixed
     * 获取分享的订单信息
     */
    private function get_weshare_buy_info($weshareId, $is_me, $division = false, $export = false) {
        return $this->WeshareBuy->get_share_order_for_show($weshareId, $is_me, $division, $export);
    }

    /**
     * @param $weshareId
     * @param $all
     */
    private function get_weshare_view_order_info($weshareId, $all = 0) {
        //todo load share detail view order data

    }

    /**
     * @param $weshareId
     * @param $product_to_map
     * @return mixed
     * 获取分享的详情
     */
    private function get_weshare_detail($weshareId, $product_to_map = false) {
        if ($product_to_map) {
            $key = SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weshareId . '_1';
        } else {
            $key = SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weshareId . '_0';
        }
        $share_detail = Cache::read($key);
        if (empty($share_detail)) {
            $weshareInfo = $this->Weshare->find('first', array(
                'conditions' => array(
                    'id' => $weshareId
                )
            ));

            $weshareAddresses = $this->WeshareAddress->find('all', array(
                'conditions' => array(
                    'weshare_id' => $weshareId,
                    'deleted' => DELETED_NO
                )
            ));
            $weshareShipSettings = $this->WeshareShipSetting->find('all', array(
                'conditions' => array(
                    'weshare_id' => $weshareId
                )
            ));
            $proxy_share_percent = $this->ProxyRebatePercent->find('first', array(
                'conditions' => array(
                    'share_id' => $weshareId
                )
            ));
            $sharer_tags = $this->ShareUtil->get_tags($weshareInfo['Weshare']['creator'], $weshareInfo['Weshare']['refer_share_id']);
            $sharer_tags_list = $this->ShareUtil->get_tags_list($weshareInfo['Weshare']['creator']);
            $weshareShipSettings = Hash::combine($weshareShipSettings, '{n}.WeshareShipSetting.tag', '{n}.WeshareShipSetting');
            $creatorInfo = $this->User->find('first', array(
                'conditions' => array(
                    'id' => $weshareInfo['Weshare']['creator']
                ),
                'recursive' => 1, //int
                'fields' => $this->query_user_fileds,
            ));
            if ($product_to_map) {
                $weshareProducts = $this->ShareUtil->get_product_tag_map($weshareId);
            } else {
                $weshareProducts = $this->WeshareProduct->find('all', array(
                    'conditions' => array(
                        'weshare_id' => $weshareId,
                        'deleted' => DELETED_NO
                    )
                ));
                $weshareProducts = Hash::extract($weshareProducts, '{n}.WeshareProduct');
            }
            $weshareInfo = $weshareInfo['Weshare'];
            $weshareInfo['tags'] = $sharer_tags;
            $weshareInfo['tags_list'] = $sharer_tags_list;
            $weshareInfo['addresses'] = Hash::extract($weshareAddresses, '{n}.WeshareAddress');
            $weshareInfo['products'] = $weshareProducts;
            $weshareInfo['creator'] = $creatorInfo['User'];
            $weshareInfo['ship_type'] = $weshareShipSettings;
            $weshareInfo['images'] = array_filter(explode('|', $weshareInfo['images']));
            $weshareInfo['proxy_rebate_percent'] = $proxy_share_percent['ProxyRebatePercent'];
            Cache::write($key, json_encode($weshareInfo));
            return $weshareInfo;
        }
        return json_decode($share_detail, true);
    }

    /**
     * @param $userInfo
     * @param $mobileNum
     * @param $address
     * @param $uid
     * @param $patchAddress
     * @param int $offlineStoreId
     * 记住用户填写的地址
     */
    private function setShareConsignees($userInfo, $mobileNum, $address, $uid, $patchAddress, $offlineStoreId = 0) {
        $consignee = $this->OrderConsignees->find('first', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => STATUS_CONSIGNEES_SHARE
            ),
            'fields' => array('id', 'name', 'mobilephone', 'remark_address')
        ));
        if (!empty($consignee)) {
            //update
            $saveData = array('name' => "'" . $userInfo . "'", 'mobilephone' => "'" . $mobileNum . "'", 'address' => "'" . $address . "'", 'remark_address' => "'" . $patchAddress . "'");
            if ($offlineStoreId != 0) {
                $saveData['ziti_id'] = $offlineStoreId;
            }
            $this->OrderConsignees->updateAll($saveData, array('id' => $consignee['OrderConsignees']['id']));
            return;
        }
        //save
        $this->OrderConsignees->save(array('creator' => $uid, 'status' => STATUS_CONSIGNEES_SHARE, 'name' => $userInfo, 'mobilephone' => $mobileNum, 'address' => $address, 'ziti_id' => $offlineStoreId, 'remark_address' => $patchAddress));
    }

    /**
     * @param $uid
     * @return mixed
     * 获取用户记住的地址
     */
    private function getShareConsignees($uid) {
        $consignee = $this->OrderConsignees->find('first', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => STATUS_CONSIGNEES_SHARE
            ),
            'fields' => array('name', 'mobilephone', 'address', 'ziti_id', 'remark_address')
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

    /**
     * @param $weshare_ids
     * @param $sharer_id
     * @return mixed
     * 获取分享者的评论数据(汇总)
     */
    private function getSharerCommentData($weshare_ids, $sharer_id) {
        return $this->WeshareBuy->load_sharer_comment_data($weshare_ids, $sharer_id);
    }

    /**
     * @param $uid
     * @return array
     * 获取分享者的一些统计数据(粉丝、分享次数)
     */
    private function getUserShareSummery($uid) {
        return $this->WeshareBuy->get_user_share_summary($uid);
    }

    /**
     * @param $weshareId
     * @return array
     * 获取分享的物流设置
     */
    private function getWeshareShipSettings($weshareId) {
        $key = SHARE_SHIP_SETTINGS_CACHE_KEY . '_' . $weshareId;
        $ship_setting_data = Cache::read($key);
        if (empty($ship_setting_data)) {
            $shareShipSettings = $this->WeshareShipSetting->find('all', array(
                'conditions' => array(
                    'weshare_id' => $weshareId
                )
            ));
            $shareShipSettings = Hash::combine($shareShipSettings, '{n}.WeshareShipSetting.tag', '{n}.WeshareShipSetting');
            Cache::write($key, json_encode($shareShipSettings));
            return $shareShipSettings;
        }
        return json_decode($ship_setting_data, true);
    }


    /**
     * @param $shareInfo
     * @param $msg
     * 处理发送消息
     */
    private function process_send_msg($shareInfo, $msg) {
        $this->WeshareBuy->send_share_product_arrive_msg($shareInfo, $msg);
    }

    /**
     * @param $weshareId
     * @param $weshareProduct
     * @param $num
     * @return array
     * 购买前检查库存
     */
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

    /**
     * @param $share_offer_id
     * @return mixed
     * 处理 抢红包事件
     */
    private function get_coupon_with_shared_id($share_offer_id) {
        $uid = $this->currentUser['id'];
        return $this->RedPacket->process_receive($share_offer_id, $uid, $this->is_weixin());
    }

    /**
     * @param $uid
     * @param $sharer
     * @return mixed
     * 获取可用红包
     */
    private function get_can_used_coupons($uid, $sharer) {
        return $this->CouponItem->find_my_valid_share_coupons($uid, $sharer);
    }

    /**
     * @param $order_id
     * @param $uid
     * @param $brand_id
     * @param $total_all_price
     * 使用 积分和红包逻辑
     * 积分（没有用）
     */
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

    /**
     * @param $shared_offer_id
     * 获取抢红包的数据
     * 用户从红包链接 点击进来 要把抢红包的数据带回去
     */
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

    /**
     * @param $sharer
     * @return int
     * 判断用户 能否使用好邻居
     */
    private function sharer_can_use_we_ship($sharer) {
        return $this->ShareUtil->read_share_ship_option_setting($sharer, SHARE_SHIP_OPTION_OFFLINE_STORE);
    }

    /**
     * @param $sharer
     * 数据库读取 能否使用拼团地址
     * @return bool
     */
    private function sharer_can_user_offline_address($sharer) {
        $setting = $this->ShareUtil->read_share_ship_option_setting($sharer, SHARE_SHIP_OPTION_OFFLINE_ADDRESS);
        return $setting == PUBLISH_YES;
    }

    /**
     * @param $sharer
     * @return bool
     */
    private function sharer_can_use_offline_address($sharer) {
        if ($this->sharer_can_user_offline_address($sharer)) {
            return true;
        }
        return $this->ShareUtil->is_proxy_user($sharer);
    }

    //check order ship type gen order address
    /**
     * @param $weshareId
     * @param $shipInfo
     * @param $buyerData
     * @param $uid
     * @return mixed
     * 单独处理订单的地址 根据 自有自提、快递、好邻居
     */
    private function get_order_address($weshareId, $shipInfo, $buyerData, $uid) {
        $shipType = $shipInfo['ship_type'];
        $addressId = $shipInfo['address_id'];
        $customAddress = $buyerData['address'];
        $patchAddress = $buyerData['patchAddress'];
        if ($patchAddress == null) {
            $patchAddress = '';
        }
        if ($shipType == SHARE_SHIP_PYS_ZITI) {
            $offline_store_id = $addressId;
        }
        $this->setShareConsignees($buyerData['name'], $buyerData['mobilephone'], $buyerData['address'], $uid, $patchAddress, $offline_store_id);
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
            if (!empty($patchAddress)) {
                $address = $address . '【' . $patchAddress . '】';
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
        if ($shipType == SHARE_SHIP_GROUP) {
            return $customAddress;
        }
    }

    /**
     * @param $id
     * @param $weshareId
     * @return mixed
     * 获取分享的物流设置
     */
    private function get_ship_set($id, $weshareId) {
        return $this->WeshareShipSetting->find('first', array(
            'conditions' => array(
                'id' => $id,
                'weshare_id' => $weshareId
            )
        ));
    }

    /**
     * @param $weshareProducts
     * @param $weshareId
     * @param $productIdNumMap
     * @return array|null
     * 检查库存
     */
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

    /**
     * @param $shipType
     * @param $orderData
     * @param $is_group_share_type
     * @return int
     * 用户下单根据选择的物流类型设置数据
     */
    private function process_order_ship_mark($shipType, &$orderData, $is_group_share_type) {
        if ($shipType == SHARE_SHIP_PYS_ZITI) {
            $orderData['ship_mark'] = SHARE_SHIP_PYS_ZITI_TAG;
            return self::PROCESS_SHIP_MARK_DEFAULT_RESULT;
        }
        if ($shipType == SHARE_SHIP_SELF_ZITI) {
            //check is group share
            //拼团标示另一种状态
            if ($is_group_share_type) {
                //mark group share
                $orderData['ship_mark'] = SHARE_SHIP_GROUP_TAG;
            } else {
                $orderData['ship_mark'] = SHARE_SHIP_SELF_ZITI_TAG;
            }
            return self::PROCESS_SHIP_MARK_DEFAULT_RESULT;
        }
        if ($shipType == SHARE_SHIP_KUAIDI) {
            $orderData['ship_mark'] = SHARE_SHIP_KUAIDI_TAG;
            return self::PROCESS_SHIP_MARK_DEFAULT_RESULT;
        }
        //remark share ship group or create
        if ($shipType == SHARE_SHIP_GROUP) {
            //check is start share or order in offline address
            $orderData['ship_mark'] = SHARE_SHIP_GROUP_TAG;
            return self::PROCESS_SHIP_MARK_UNFINISHED_RESULT;
        }
    }

    /**
     * @param $orderData
     * @param $shipInfo
     * @param $is_start_new_group_share
     * @param $weshareId
     * @param $uid
     * @param $address
     * @param $business_remark
     * 处理用户邻里拼下单
     */
    private function process_ship_group(&$orderData, $shipInfo, $is_start_new_group_share, $weshareId, $uid, $address, $business_remark) {
        if ($is_start_new_group_share) {
            //标示这是一个邻里拼 触发 clone 一个分享
            $orderData['relate_type'] = ORDER_TRIGGER_GROUP_SHARE_TYPE;
            //clone share
            $this->ShareUtil->cloneShare($weshareId, $uid, $address, $business_remark, GROUP_SHARE_TYPE);
        } else {
            //reset share id
            $shipInfoWeshareId = $shipInfo['weshare_id'];
            $orderData['member_id'] = $shipInfoWeshareId;
        }
    }

    /**
     * @param $uid
     * @return bool
     * 是否是认证分享者 写死
     */
    private function is_verify_sharer($uid) {
        $uids = array(633345, 802852, 544307, 811917, 801447);
        return in_array($uid, $uids);
    }

    /**
     * @param $shareId
     * 给子分享退款
     */
    public function refund_share($shareId) {
        $this->autoRender = false;
        $queue = new SaeTaskQueue('tasks');
        $tasks = array();
        $remark = $_REQUEST['remark'];
        $tasks[] = array('url' => "/task/batch_refund_money/" . $shareId . ".json", "postdata" => "remark=" . $remark);
        $queue->addTask($tasks);
        $ret = $queue->push();
        echo json_encode(array('success' => true, 'ret' => $ret));
        return;
    }

    public function shipped_share($shareId) {

    }

    /**
     * @param $uid
     * @param $current_uid
     * @param $shareUser
     * 设置分享用户中心页面 微信分享参数
     */
    private function set_share_user_info_weixin_params($uid, $current_uid, $shareUser) {
        if (parent::is_weixin()) {
            $wexin_params = $this->set_weixin_share_data($uid, -1);
            $this->set($wexin_params);
            if ($uid == $current_uid) {
                $title = '这是' . $shareUser['nickname'] . '的微分享，快来关注我吧';
                $image = $shareUser['image'];
                $desc = '朋友说是一个有人情味的分享社区，这里你不但可以吃到各地的特产，还能认识有趣的人。';
            } else {
                $current_user = $this->currentUser;
                $title = $current_user['nickname'] . '推荐了' . $shareUser['nickname'] . '的微分享，快来关注ta吧！';
                $image = $shareUser['image'];
                $desc = $shareUser['nickname'] . '是我的朋友，很靠谱。朋友说是一个有人情味的分享社区，这里你不但可以吃到各地的特产，还能认识有趣的人。';
            }
            if (!$image) {
                $image = 'http://dev.tongshijia.com/img/logo_footer.jpg';
            }
            $detail_url = WX_HOST . '/weshares/user_share_info/' . $uid;
            $this->set('detail_url', $detail_url);
            $this->set('title', $title);
            $this->set('image', $image);
            $this->set('desc', $desc);
            $this->set('add_view', true);
        }
    }
}