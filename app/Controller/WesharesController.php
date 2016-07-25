<?php

class WesharesController extends AppController
{
    var $uses =
        array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User', 'OrderConsignees', 'Oauthbind', 'SharedOffer', 'CouponItem','UserRelation','UserLevel',
            'SharerShipOption', 'WeshareShipSetting', 'OfflineStore', 'Comment', 'RebateTrackLog', 'ProxyRebatePercent', 'ShareUserBind', 'UserSubReason', 'ShareFavourableConfig', 'ShareAuthority');

    var $query_user_fields = array('id', 'nickname', 'image', 'wx_subscribe_status', 'description', 'is_proxy', 'avatar');

    var $components = array('Weixin', 'WeshareBuy', 'Buying', 'RedPacket', 'ShareUtil', 'ShareAuthority', 'OrderExpress', 'PintuanHelper', 'RedisQueue', 'DeliveryTemplate', 'Orders', 'Weshares', 'UserFans');

    var $pay_type = 1;

    const PROCESS_SHIP_MARK_DEFAULT_RESULT = 0;
    const PROCESS_SHIP_MARK_UNFINISHED_RESULT = 1;


    public function __construct($request = null, $response = null)
    {
        $this->helpers[] = 'PhpExcel';
        parent::__construct($request, $response);
    }

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    /**
     * @param $tag
     * 首页
     */
    public function index($tag = 0)
    {
        //add logs
        $log = [
            "index" => "event_share_index",
            "type" => "share_index",
            "user_id" => intval($this->currentUser['id']),
            "referer" => $_SERVER["HTTP_REFERER"],
        ];
        add_logs_to_es($log);

        $index_products = $this->ShareUtil->get_index_product($tag);
        $banners = $this->ShareUtil->get_index_banners();
        $uid = $this->currentUser['id'];
        $this->set('index_products', $index_products);
        $this->set('uid', $uid);
        $this->set('tag', $tag);
        $this->set('banners', $banners);
        $coupon_count = $this->CouponItem->find_my_share_coupons_count($uid);
        $this->set('coupon_count', $coupon_count);
        $hide_nav = $_REQUEST['hide_nav'];
        $this->set('hide_nav', $hide_nav);
    }

    public function read_share_count_api($page)
    {
        $uid = $this->currentUser['id'];
        $this->WxShareStatistics = $this->Components->load('WxShareStatistics');
        $weshares = $this->WxShareStatistics->getWeshareList($uid, $page);
        header('Content-type: application/json');
        echo json_encode($weshares);
        die;
    }
    public function read_count_api($id, $page = 1)
    {
        $this->WxShareStatistics = $this->Components->load('WxShareStatistics');
        $res = $this->WxShareStatistics->getWeshareReadList($id, $page);
        header('Content-type: application/json');
        echo json_encode($res);
        die;
    }
    public function share_count_api($id, $page = 1)
    {
        $this->WxShareStatistics = $this->Components->load('WxShareStatistics');
        $res = $this->WxShareStatistics->getWeshareForwardList($id, $page);
        header('Content-type: application/json');
        echo json_encode($res);
        die;
    }
    public function read_share_count()
    {
        $uid = $this->currentUser['id'];
        $this->WxShareStatistics = $this->Components->load('WxShareStatistics');
        list($share_count, $read_count) = $this->WxShareStatistics->getWeshareSummary($uid);
        $this->set('share_count', $share_count);
        $this->set('read_count', $read_count);
        $this->set('title', '朋友说-转发阅读统计');
    }
    public function share_count($id)
    {
        $this->WxShareStatistics = $this->Components->load('WxShareStatistics');
        list($weshare, $share_count) = $this->WxShareStatistics->getWeshareForwardData($id);
        $this->set('weshare', $weshare);
        $this->set('share_count', $share_count);
    }
    public function read_count($id)
    {
        $this->WxShareStatistics = $this->Components->load('WxShareStatistics');
        list($weshare, $read_count) = $this->WxShareStatistics->getWeshareReadData($id);
        $this->set('weshare', $weshare);
        $this->set('read_count', $read_count);
    }


    public function coupons(){
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $this->redirect('/');
        }
        $coupons = $this->CouponItem->find_my_all_valid_share_coupons($uid);
        $this->set('coupons', $coupons);
    }

    public function index_special($tagId)
    {
        $index_products = $this->ShareUtil->get_index_product($tagId);
        $tags = get_index_tags();
        $tag = null;
        foreach ($tags as $tag_item) {
            if ($tag_item['id'] == $tagId) {
                $tag = $tag_item;
                break;
            }
        }
        $uid = $this->currentUser['id'];
        $this->set('index_products', $index_products);
        $this->set('uid', $uid);
        $this->set('tag', $tagId);
        $this->set('special_banner', $tag['banner']);
        $this->set('hide_nav', true);
        $this->set('name', $tag['name']);
        $this->set('is_try', $tag['try']);
    }


    public function entrance(){
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $this->redirect('/users/login.html?referer=/weshares/entrance');
        }
        $user_level = $this->ShareUtil->get_user_level($uid);
        $this->set('user_level', $user_level);
    }

    private function add_paid_faild_msg_to_es($uid, $weshareId)
    {
        $order = $this->Order->find('first', [
            'conditions' => ['creator' => $uid, 'member_id' => $weshareId, 'status' => ORDER_STATUS_WAITING_PAY]
        ]);
        if (!empty($order)) {
            try {
                add_logs_to_es(["index" => "event_pay_failed", "type" => "pay_failed", "user_id" => $order['Order']['creator'], "order_id" => $order['Order']['id'], "total_price" => $order['Order']['total_all_price'], "weshare_id" => $order['Order']['member_id'], "brand_id" => $order['Order']['brand_id']]);
            } catch (Exception $e) {
                $this->log('add pay failed msg error');
            }
        }
    }

    /**
     * @param string $weshare_id
     * @param int $from 标示从什么地方跳转的访问
     * 跳转到分享的详情页
     */
    public function view($weshare_id, $from = 0)
    {
        //add logs
        $log = [
            "index" => "event_share_view",
            "type" => "share_view",
            "user_id" => intval($this->currentUser['id']),
            "referer" => $_SERVER["HTTP_REFERER"],
            "weshare_id" => intval($weshare_id)
        ];
        add_logs_to_es($log);

        $uid = $this->currentUser['id'];

        $weshare = $this->WeshareBuy->get_weshare_info($weshare_id);

        //渠道价购买链接 判断是否是团长
        if ($weshare['type'] == SHARE_TYPE_POOL_FOR_PROXY) {
            //check share type
            if (!$this->ShareUtil->is_proxy_user($uid)) {
                $this->redirect('/weshares/index');
                return;
            }
        }

        $this->handle_weshare_view_paid($from, $uid, $weshare_id);
        $creator = $this->User->find('first', array('conditions' => array('id' => $weshare['creator'])))['User'];
        $this->set('uid', $uid);
        $this->set('weshare_id', $weshare_id);

        //获取推荐人
        $recommend = $_REQUEST['recommend'];
        //添加推荐日志
        //自己推荐人购买不能加入推荐
        if ($this->ShareUtil->is_proxy_user($recommend) && $recommend != $uid) {
            if (!empty($recommend) && !empty($uid)) {
                $rebateLogId = $this->ShareUtil->save_rebate_log($recommend, $uid, $weshare_id);
                $this->set('recommend_id', $recommend);
                $this->set('rebateLogId', $rebateLogId);
            }
        }
        //用户点击评论模板消息自动弹出评论对话框
        $comment_order_id = $_REQUEST['comment_order_id'];
        $replay_comment_id = $_REQUEST['reply_comment_id'];
        if (!empty($comment_order_id)) {
            $this->set('comment_order_id', $comment_order_id);
        }
        if (!empty($replay_comment_id)) {
            $this->set('reply_comment_id', $replay_comment_id);
        }
        //标记链接从什么地方点击来的
        $view_from = $_REQUEST['from'];
        if (empty($view_from)) {
            //iOS phone 微信不添加参数手工记录
            $share_type = $_REQUEST['share_type'];
            if ($share_type) {
                $view_from = $share_type == 'appMsg' ? 'groupmessage' : 'timeline';
            }
        }
        $shared_offer_id = $this->handle_weshare_view_has_shared_offer($uid, $weshare['creator']);
        $summary = $this->ShareUtil->get_index_product_summary($weshare['id']);
        $ordersDetail = $this->WeshareBuy->get_current_user_share_order_data($weshare['id'], $uid);
        $this->set_weixin_params_for_view($this->currentUser, $creator, $weshare, $recommend, $shared_offer_id, $summary, $ordersDetail);
        $this->set('page_title', $weshare['title']);
        $this->set('click_from', $view_from);
        $this->WeshareBuy->update_share_view_count($weshare_id);
    }

    private function handle_weshare_view_paid($from, $uid, $weshare_id)
    {
        //from paid done
        if ($from == $this->pay_type) {
            $paidMsg = $_REQUEST['msg'];
            if ($paidMsg == 'ok') {
                $this->set('from', $this->pay_type);
            } else {
                $this->add_paid_faild_msg_to_es($uid, $weshare_id);
                if ($paidMsg == 'cancel') {
                    $this->log('Payment of user ' . $uid . ' to weshare ' . $weshare_id . ' failed: canceled', LOG_INFO);
                } else {
                    $this->log('Payment of user ' . $uid . ' to weshare ' . $weshare_id . ' failed: ' . $paidMsg, LOG_ERR);
                }
            }
        }

    }

    //处理分享链接的红包数据
    private function handle_weshare_view_has_shared_offer($uid, $weshare_creator)
    {
        //check has sharer has red packet
        //领取红包
        $shared_offer_id = $_REQUEST['shared_offer_id'];
        //用户抢红包
        if (empty($shared_offer_id)) {
            //process
            $shared_offers = $this->SharedOffer->find_new_offers_by_weshare_creator($uid, $weshare_creator);
            //get first offer
            if (!empty($shared_offers)) {
                $firstSharedOffer = $shared_offers[0]['SharedOffer'];
                $shared_offer_id = $firstSharedOffer['id'];
            }
        }
        if (!empty($shared_offer_id)) {
            $this->set('shared_offer_id', $shared_offer_id);
            $this->set('from', $this->pay_type);
            $this->process_shared_offer($shared_offer_id);
            return $shared_offer_id;
        }
        return null;
    }

    public function pay_result($orderId){
        $uid = $this->currentUser['id'];
        $orderInfo = $this->Order->find('first', [
            'conditions' => ['id' => $orderId, 'creator' => $uid, 'type' => ORDER_TYPE_WESHARE_BUY]
        ]);
        if (empty($orderInfo)) {
            $this->redirect('/');
        }
        if ($orderInfo['Order']['status'] != ORDER_STATUS_PAID) {
            $this->redirect('/weshares/view/' . $orderInfo['Order']['member_id']);
        }
        $detail = $this->ShareUtil->get_tag_weshare_detail($orderInfo['Order']['member_id']);
        list($sharedOffer, $sliceCount) = $this->SharedOffer->find_new_offers_by_order_id($uid, $orderId, $detail['creator']['id']);
        $this->set('totalFee', $orderInfo['Order']['total_all_price']);
        $uid = $this->currentUser['id'];
        $this->set('uid', $uid);
        $this->set('detail', $detail);
        $this->set('offer_count', $sliceCount);
        $this->set('shared_offer', $sharedOffer);
        $isSub = $this->ShareUtil->check_user_is_subscribe($detail['creator']['id'], $uid);
        $this->set('has_sub', $isSub);
        $this->set_weixin_params_for_pay_result($detail['creator'], $detail, $sharedOffer['SharedOffer']['id']);
    }

    /**
     * @param $weshareId
     * 更新 分享信息
     */
    public function update($weshareId)
    {
        $uid = $this->currentUser['id'];
        $weshareInfo = $this->ShareUtil->get_weshare_detail($weshareId);
        $can_edit_share = $this->ShareAuthority->user_can_edit_share_info($uid, $weshareId);
        // 忠立可以操作任何人的分享. 801447是他的UID
        if ($uid != 801447 && $uid != $weshareInfo['creator']['id'] && !$can_edit_share) {
            $this->redirect('/weshares/view/' . $weshareId . '/0');
        }
        $share_ship_set = $this->sharer_can_use_we_ship($uid);
        if ($this->is_new_sharer($uid)) {
            $this->set('is_new_sharer', 1);
        }
        $this->set('ship_type', $share_ship_set);
        $this->set('weshare_id', $weshareId);
        $this->set('user_id', $uid);
    }

    /**
     * @param $weshareId
     * 分享的详细信息
     */
    public function get_share_info($weshareId)
    {
        $this->autoRender = false;
        $shareInfo = $this->ShareUtil->get_edit_share_info($weshareId);
        echo json_encode($shareInfo);
        return;
    }

    /**
     * 添加分享页面
     * 判断用户是否绑定 手机 等信息
     */
    public function add()
    {
        $currentUser = $this->currentUser;
        $uid = $currentUser['id'];
        //check user has bind mobile and payment
        $current_user = $this->User->find('first', array(
            'conditions' => array(
                'id' => $uid
            ),
            'recursive' => 1, //int
            'fields' => array('id', 'mobilephone', 'payment'),
        ));
        if (empty($current_user['User']['mobilephone']) || empty($current_user['User']['payment'])) {
            $this->redirect('/users/tutorial');
            return;
        }

        $share_ship_set = $this->sharer_can_use_we_ship($uid);
        if ($this->is_new_sharer($uid)) {
            $this->set('is_new_sharer', 1);
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
                $image = 'http://static.tongshijia.com/static/img/logo_footer.jpg';
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
    public function save()
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $postStr = file_get_contents('php://input');
        $postDataArray = json_decode($postStr, true);
        $result = $this->Weshares->create_weshare($postDataArray, $uid);
        //add log
        $log = [
            "sharer_id" => intval($result["id"]),
            "user_id" => intval($uid),
            "index" => "event_save_sharer",
            "type" => "save_sharer"
        ];
        add_logs_to_es($log);

        echo json_encode($result);
        return;
    }

    public function load_share_comments($sharer_id)
    {
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
    public function get_share_order_by_page($shareId, $page)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $combineComment = $_REQUEST['combineComment'];
        $ordersDetail = $this->WeshareBuy->get_share_detail_view_orders($shareId, $page, $uid, $combineComment);
        echo json_encode($ordersDetail);
        return;
    }


    /**
     * @param $shareId
     * ajax 获取用户的订单数据
     */
    public function get_share_user_order($shareId)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $ordersDetail = $this->WeshareBuy->get_current_user_share_order_data($shareId, $uid);
        echo json_encode(array('ordersDetail' => $ordersDetail));
        exit();
    }

    /**
     * ajax 获取线下自提点 信息
     */
    public function get_offline_address_detail($share_id)
    {
        $this->autoRender = false;
        $offline_address_data = $this->ShareUtil->get_share_offline_address_detail($share_id);
        echo json_encode($offline_address_data);
        return;
    }

    /**
     * @param $weshareId
     * ajax 获取分享的详细信息
     */
    public function detail($weshareId)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $detail = $this->Weshares->get_weshare_detail($weshareId, $uid);
        if (!empty($detail)) {
            $detail['prepare_comment_data'] = $this->prepare_comment_data();
        }
        echo json_encode($detail);
        exit();
    }

    /**
     * @return mixed
     * 自动弹出评论框的数据
     */
    private function prepare_comment_data()
    {
        $to_comment_order_id = $_REQUEST['comment_order_id'];
        $reply_comment_id = $_REQUEST['reply_comment_id'];
        //准备评论数据
        $prepare_comment_data = $this->WeshareBuy->prepare_to_comment_info($to_comment_order_id, $reply_comment_id);
        return $prepare_comment_data;
    }

    /**
     * @param $weshareId
     * 异步获取分享的评论数据
     */
    public function get_share_comment_data($weshareId, $sharer)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $comment_data = $this->WeshareBuy->load_comment_by_share_id($weshareId, $uid, $sharer);
        echo json_encode(array('comment_data' => $comment_data));
        exit();
    }

    /**
     * @param $uid
     * @param $weshareId
     * @return array|null
     * 把微信分享的一些参数设置好
     */
    private function set_weixin_share_data($uid, $weshareId)
    {
        if (parent::is_weixin()) {
            $weixinJs = prepare_wx_share_log($uid, 'wsid', $weshareId);
            return $weixinJs;
        }
        return null;
    }

    private function get_weixin_share_str($weshareId)
    {
        $uid = empty($this->currentUser['id']) ? 0 : $this->currentUser['id'];
        return prepare_wx_share_string($uid, 'wsid', $weshareId);
    }

    /**
     * @param $orderId
     * @param $type
     * 去支付
     */
    public function pay($orderId, $type)
    {
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
    public function pay_order_add($orderId)
    {
        $this->layout = 'weshare_bootstrap';
        $cart_info = $this->WeshareBuy->get_cart_name_and_num($orderId);
        $order_info = $this->Orders->get_order_info($orderId);
        if ($order_info['status'] == ORDER_STATUS_PAID) {
            $this->redirect('/weshares/view/' . $order_info['member_id']);
            return;
        }
        $this->set('cart_info', $cart_info);
        $this->set('order_info', $order_info);
    }

    /**
     * @param $ship_setting
     * @param $good_num
     * @param $good_weight
     * @param $weshare_id
     * @return mixed
     * 计算订单费用
     */
    private function calculate_order_ship_fee($ship_setting, $good_num, $good_weight, $weshare_id, $province_id)
    {
        if ($ship_setting['WeshareShipSetting']['tag'] != SHARE_SHIP_KUAIDI_TAG) {
            return $ship_setting['WeshareShipSetting']['ship_fee'];
        }
        $shipFee = $this->DeliveryTemplate->calculate_ship_fee($good_num, $good_weight, $province_id, $weshare_id);
        return $shipFee;
    }

    /**
     * 用户下单
     */
    public function makeOrder()
    {
        $this->autoRender = false;
        parent::set_current_user_by_access_token();
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $this->log('Failed to create order: user not logged in', LOG_WARNING);
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $postStr = file_get_contents('php://input');
        $postDataArray = json_decode($postStr, true);
        $products = $postDataArray['products'];
        $weshareId = $postDataArray['weshare_id'];
        $weshareCreator = $postDataArray['weshare_creator'];
        $business_remark = $postDataArray['remark'];
        $rebateLogId = $postDataArray['rebate_log_id'];
        if ($_REQUEST['access_token'] && empty($postDataArray['from'])) {
            $postDataArray['from'] = 'app';
        }
        $order_chat_group_id = empty($postDataArray['chat_group_id']) ? 0 : $postDataArray['chat_group_id'];
        $order_flag = get_order_from_flag($postDataArray['from']);
        //购物车
        $cart = array();
        $dataSource = $this->Order->getDataSource();
        try {
            $weshare_available = $this->WeshareBuy->check_weshare_status($weshareId);
            if (!$weshare_available) {
                $this->log('Failed to create order for ' . $uid . ' with weshare ' . $weshareId . ': weshare is not available.', LOG_WARNING);
                echo json_encode(array('success' => false, 'reason' => '分享已经截团'));
                exit();
            }
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
                $this->log('Failed to create order for ' . $uid . ' with weshare ' . $weshareId . ': product is sold out.', LOG_WARNING);
                echo json_encode($checkProductStoreResult);
                exit();
            }

            $shipInfo = $postDataArray['ship_info'];
            $consigneeId = $shipInfo['consignee_id'];
            $shipType = $shipInfo['ship_type'];
            $shipSetId = $shipInfo['ship_set_id'];
            $shipSetting = $this->get_ship_set($shipSetId, $weshareId);
            if (empty($shipSetting)) {
                $this->log('Failed to create order for ' . $uid . ' with weshare ' . $weshareId . ': ship setting is empty', LOG_WARNING);
                echo json_encode(array('success' => false, 'reason' => '物流方式选择错误'));
                exit();
            }
            //开启事务
            $dataSource->begin();
            //获取下单地址
            $address = $this->get_order_address($weshareId, $shipInfo, $uid);
            $orderData = array('cate_id' => $rebateLogId, 'creator' => $uid,
                'consignee_address' => $address,
                'member_id' => $weshareId,
                'brand_id' => $weshareCreator,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'created' => date('Y-m-d H:i:s'),
                'updated' => date('Y-m-d H:i:s'),
                'consignee_id' => $consigneeId,
                'consignee_name' => $shipInfo['name'],
                'consignee_mobilephone' => $shipInfo['mobilephone'],
                'business_remark' => $business_remark,
                'flag' => $order_flag,
                'chat_group_id' => $order_chat_group_id);
            //设置订单物流方式
            $this->process_order_ship_mark($shipType, $orderData);
            $order = $this->Order->save($orderData);
            $orderId = $order['Order']['id'];
            $totalPrice = 0;
            $cart_good_num = 0;
            $cart_good_weight = 0;
            foreach ($weshareProducts as $p) {
                $item = array();
                $pid = $p['WeshareProduct']['id'];
                $num = $productIdNumMap[$pid];
                $cart_good_num = $cart_good_num + $num;
                $cart_good_weight = $cart_good_weight + $p['WeshareProduct']['weight'];
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
            //产品价格的团长佣金
            $rebate_fee = $this->WeshareBuy->cal_proxy_rebate_fee($totalPrice, $uid, $weshareId);
            $shipFee = $this->calculate_order_ship_fee($shipSetting, $cart_good_num, $cart_good_weight, $weshareId, $shipInfo['provinceId']);
            $totalPrice += $shipFee;
            $update_order_data = array('total_all_price' => $totalPrice / 100, 'total_price' => $totalPrice / 100, 'ship_fee' => $shipFee);
            if ($rebate_fee > 0) {
                //团长已经返利
                //记录返利的钱
                $rebate_log_id = $this->WeshareBuy->log_proxy_rebate_log($weshareId, $uid, 0, 1, $orderId, $rebate_fee * 100);
                if (!empty($rebate_log_id)) {
                    $update_order_data['cate_id'] = $rebate_log_id;
                    $update_order_data['total_all_price'] = $update_order_data['total_all_price'] - $rebate_fee;
                }
            } else {
                //团长没有返利直接设置返利订单号
                //返利
                $this->ShareUtil->update_rebate_log_order_id($rebateLogId, $orderId, $weshareId);
            }
            if ($this->Order->updateAll($update_order_data, array('id' => $orderId))) {
                $coupon_id = $postDataArray['coupon_id'];
                $this->log('use coupon id ' . $coupon_id, LOG_INFO);
                //红包
                if (!empty($coupon_id)) {
                    //菠萝
                    if ($weshareId == 4507) {
                        //use code
                        $this->order_use_coupon_code($coupon_id, $orderId, $uid);
                    } else {
                        $this->order_use_score_and_coupon($coupon_id, $orderId, $uid, $totalPrice / 100);
                    }
                }
                $this->Orders->on_order_created($uid, $weshareId, $orderId);
            }
            $this->log('Create order for ' . $uid . ' with weshare ' . $weshareId . ' successfully, order id ' . $orderId, LOG_INFO);
            $dataSource->commit();
            try {
                add_logs_to_es(["index" => "event_pay_begin", "type" => "pay_begin", "user_id" => $uid, "order_id" => $orderId, "total_price" => get_format_number($totalPrice/100), "weshare_id" => $weshareId, "brand_id" => $weshareCreator]);
            } catch (Exception $e) {
                $this->log('add es log error when make order');
            }
            echo json_encode(array('success' => true, 'orderId' => $orderId));
            exit;
        } catch (Exception $e) {
            $this->log($uid . 'buy share ' . $weshareId . $e);
            $dataSource->rollback();
            echo json_encode(array('success' => false, 'reason' => $e->getMessage()));
            exit;
        }
    }

    /**
     * 保存商品的标签
     */
    public function save_tags()
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $postStr = file_get_contents('php://input');
        $tags = json_decode($postStr, true);
        $tags = $this->ShareUtil->save_tags_return($tags, $uid);
        echo json_encode(array('success' => true, 'tags' => $tags));
        return;
    }

    /**
     * 获取商品的标签
     */
    public function get_tags()
    {
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
    function confirmReceived($order_id)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $result = $this->ShareUtil->confirm_received_order($order_id, $uid);
        echo json_encode($result);
        return;
    }

    /**
     * @param $weshare_id
     * 截止分享
     */
    public function stopShare($weshare_id)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];

        // 判断权限：owner或者超级管理员
        $this->Weshares->stop_weshare($uid, $weshare_id);

        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 重新开团
     */
    public function cloneShare($shareId)
    {
        $uid = $this->currentUser['id'];
        $is_owner = $this->Weshare->hasAny(['id' => $shareId, 'creator' => $uid]);
        if (!$is_owner) {
            echo json_encode(array('success' => false, 'reason' => 'not a proxy user.'));
            exit();
        }

        $this->autoRender = false;
        $this->log('Proxy ' . $uid . ' tries to clone share from share ' . $shareId, LOG_INFO);
        $result = $this->ShareUtil->cloneShare($shareId, null);
        if ($result['success']) {
            $this->log('Proxy ' . $uid . ' clones share ' . $result['shareId'] . ' from share ' . $shareId . ' successfully', LOG_INFO);
        } else {
            $this->log('Proxy ' . $uid . ' failed to clone share from share ' . $shareId, LOG_ERR);
        }

        echo json_encode($result);
        exit();
    }

    /**
     * @param $weshare_id
     */
    public function delete_share($weshare_id)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $this->log('Failed to delete weshare ' . $weshare_id . ': user not logged in');
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }

        $this->Weshares->delete_weshare($uid, $weshare_id);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param null $uid
     * 获取分享用户的个人中心,$uid为空 获取当前用户的
     */
    public function user_share_info($uid = null)
    {
        $current_uid = $this->currentUser['id'];
        if (empty($uid)) {
            $uid = $current_uid;
        }
        $is_me = $uid == $current_uid;
        if ($is_me) {
            redirect('/weshares/get_self_info.html');
            //$this->redirect('/weshares/get_self_info.html');
        } else {
            redirect('/weshares/get_other_info/' . $uid . '.html');
            //$this->redirect('/weshares/get_other_info/' . $uid . '.html');
        }
    }

    public function user_setting()
    {
        $user_info = $this->get_user_info($this->currentUser['id']);
        $this->set('user_info', $user_info['User']);
        $this->set('uid', $this->currentUser['id']);
    }

    /**
     * 获取用户授权的列表
     */
    public function my_auth_shares_list_api($type, $page = 1)
    {
        $limit = 5;
        $uid = $this->currentUser['id'];
        $shares = [];
        if ($uid) {
            $params = $this->__get_query_share_settlement_status_by_type($type);
            $auth_shares_result = $this->WeshareBuy->get_my_auth_shares($uid, $page, $limit, $params['status'], $params['settlement']);
            $share_ids = Hash::extract($auth_shares_result, '{n}.Weshare.id');
            if (!empty($share_ids)) {
                $order_count_result = $this->get_order_count_share_map($share_ids);
                $share_balance_money = $this->get_share_balance_result($share_ids);
                foreach ($auth_shares_result as $result_item) {
                    $share_item = $result_item['Weshare'];
                    $operate_item = $result_item['ShareOperateSetting'];
                    $share_item_id = $share_item['id'];
                    if (!isset($shares[$share_item_id])) {
                        $shares[$share_item_id] = $share_item;
                        $shares[$share_item_id]['order_count'] = empty($order_count_result[$share_item_id]) ? 0 : $order_count_result[$share_item_id];
                        $shares[$share_item_id]['balance_money'] = $share_balance_money[$share_item_id];
                        $shares[$share_item_id]['auth_types'] = [];
                    }
                    $shares[$share_item_id]['auth_types'][] = $operate_item['data_type'];
                }
            }
        }
        echo json_encode(array_values($shares));
        exit();
    }

    /**
     * 获取用户的分享列表
     */
    public function my_shares_list_api($type, $page = 1)
    {
        $share_list = [];
        $uid = $this->currentUser['id'];
        $limit = 5;
        $page = intval($page) ? intval($page) : 1;
        $type = intval($type);
        if ($uid && in_array($type, [1, 2, 3])) {
            $params = $this->__get_query_share_settlement_status_by_type($type);
            $shares = $this->WeshareBuy->get_my_shares($uid, $params['status'], $params['settlement'], $page, $limit);
            $share_list = $this->combine_share_list_data($shares);
        }
        echo json_encode($share_list);
        exit();
    }

    public function search_shares_api($page=1)
    {
        $keyword = $_REQUEST['keyword'];
        $uid = $this->currentUser['id'];
        $shares = $this->WeshareBuy->search_shares($uid, $keyword, $page, 5);
        $share_list = $this->combine_share_list_data($shares);
        echo json_encode($share_list);
        exit();
    }

    private function combine_share_list_data($shares){
        $share_ids = Hash::extract($shares, '{n}.Weshare.id');
        $share_list = [];
        if (!empty($share_ids)) {
            $order_count_result = $this->get_order_count_share_map($share_ids);
            $share_balance_money = $this->get_share_balance_result($share_ids);
            foreach ($shares as $shareItem) {
                $shareItem = $shareItem['Weshare'];
                $shareItem['order_count'] = empty($order_count_result[$shareItem['id']]) ? 0 : intval($order_count_result[$shareItem['id']]);
                $shareItem['balance_money'] = $share_balance_money[$shareItem['id']];
                $share_list[] = $shareItem;
            }
        }
        return $share_list;
    }

    private function  __get_query_share_settlement_status_by_type($type)
    {
        if ($type == 2) {
            $status = WESHARE_STATUS_STOP;
            $settlement = WESHARE_SETTLEMENT_NO;
        } elseif ($type == 3) {
            $status = WESHARE_STATUS_STOP;
            $settlement = WESHARE_SETTLEMENT_YES;
        } else {
            $status = WESHARE_STATUS_NORMAL;
            $settlement = [WESHARE_SETTLEMENT_NO, WESHARE_SETTLEMENT_YES];
        }
        return ['status' => $status, 'settlement' => $settlement];
    }

    private function get_order_count_share_map($share_ids)
    {
        $query_order_sql = 'select count(id), member_id from cake_orders where member_id in (' . implode(',', $share_ids) . ') and status>0 and type=9 group by member_id';
        $orderM = ClassRegistry::init('Order');
        $result = $orderM->query($query_order_sql);
        $result = Hash::combine($result, '{n}.cake_orders.member_id', '{n}.0.count(id)');
        return $result;
    }

    private function  get_share_balance_result($share_ids)
    {
        $balance_result = $this->WeshareBuy->get_shares_balance_money($share_ids);
        $summery_data = $balance_result['weshare_summery'];
        $weshare_repaid_map = $balance_result['weshare_repaid_map'];
        $weshare_rebate_map = $balance_result['weshare_rebate_map'];
        $weshare_refund_map = $balance_result['weshare_refund_map'];
        $result = array();
        foreach ($share_ids as $share_id) {
            $current_share_repaid_money = $weshare_repaid_map[$share_id];
            if ($current_share_repaid_money == 0) {
                $current_share_repaid_money = 0;
            }
            $result[$share_id] = floatval($summery_data[$share_id]['total_price']) - floatval($weshare_refund_map[$share_id]) - floatval($weshare_rebate_map[$share_id]) + $current_share_repaid_money;
        }
        return $result;
    }

    public function my_shares_list($type = 0)
    {
        $uid = $this->currentUser['id'];
        if (!($uid > 0)) {
            $this->redirect('/users/login');
        }

        $this->set('type', $type);
        $title = $type == 0 ? '我的分享' : '授权我的';
        $this->set('title', $title);
    }

    public function search_my_shares(){
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $this->redirect('/users/login');
        }
        $query = $_REQUEST['query'];
        $this->set('title', '查询分享');
        $this->set('query', $query);
    }

    public function my_order_list()
    {
        $uid = $this->currentUser['id'];
        if (!($uid > 0)) {
            $this->redirect('/users/login');
        }
    }

    public function my_order_list_api($page = 1)
    {
        $limit = 5;
        $page = intval($page) > 1 ? intval($page) : 1;
        $uid = $this->currentUser['id'];
        $params = ['user_id' => $uid, 'status' => [ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY], 'limit' => $limit, 'page' => $page];
        $orders = $this->Orders->get_user_order($params);
        $result = [];
        foreach ($orders as $order_item) {
            $result_item = $order_item['Order'];
            $result_item['share_info'] = $order_item['Weshare'];
            $result[] = $result_item;
        }
        echo json_encode($result);
        exit();
    }

    public function subUser($uid)
    {
        if (!$this->currentUser['id']) {
            echo json_encode(array('success' => false));
            die;
        }
        return $this->subscribe_sharer($uid, $this->currentUser['id']);
    }

    public function unSubUser($uid)
    {
        if (!$this->currentUser['id']) {
            echo json_encode(array('success' => false));
            die;
        }
        return $this->unsubscribe_sharer($uid, $this->currentUser['id']);
    }

    private function get_user_info($user_id)
    {
        $datainfo = $this->User->find('first', array('recursive' => -1,
            'conditions' => array('id' => $user_id),
            'fields' => array('nickname', 'image', 'sex', 'mobilephone', 'username', 'id', 'hx_password', 'description', 'payment', 'avatar')));
        return $datainfo;
    }

    public function get_other_info($uid)
    {
        $curr_uid = $this->currentUser['id'];
        $user_summary = $this->WeshareBuy->get_user_share_summary($uid);
        $user_info = $this->get_user_info($uid);
        $user_info['User']['avatar'] = get_user_avatar($user_info['User']);
        $sub_status = 0;
        if ($curr_uid) {
            $sub_status = $this->ShareUtil->check_user_relation($uid, $curr_uid);
        }
        $this->set('user_summary', $user_summary);
        $this->set('user_info', $user_info);
        $this->set('sub_status', !$sub_status);
        $this->set('uid', $uid);
        $this->set_share_user_info_weixin_params($uid, $curr_uid, $user_info['User']);
    }

    public function get_other_shares($uid, $page)
    {
        $page = intval($page) ? intval($page) : 1;
        $limit = 5;
        $result = $this->Weshares->get_u_create_share($uid, $limit, $page);

        foreach ($result as $k => $res) {
            $item_desc = strip_tags($result[$k]['description']);
            $result[$k]['description'] = mb_strlen($item_desc, 'utf8') > 100 ? mb_substr($item_desc, 0, 99, 'utf8') . "..." : $item_desc;
        }

        echo json_encode($result);
        exit();
    }

    public function get_other_attends($uid, $page)
    {
        $page = intval($page) ? intval($page) : 1;
        $limit = 5;
        $result = $this->Weshares->get_u_buy_share($uid, $limit, $page);
        echo json_encode($result);
        exit();
    }

    public function get_self_info()
    {
        $uid = $this->currentUser['id'];
        if (!($uid > 0)) {
            $this->redirect('/users/login');
        }

        //add log
        $log = [
            "user_id" => intval($uid),
            "index" => "event_get_self_info",
            "type" => "get_self_info"
        ];
        
        add_logs_to_es($log);

        $user_summary = $this->WeshareBuy->get_user_share_summary($uid);
        $user_level = $this->ShareUtil->get_user_level($uid);
        $user_info = $this->get_user_info($uid);
        $my_order_count = $this->WeshareBuy->get_user_all_order_count($uid);
        $user_info['User']['avatar'] = get_user_avatar($user_info['User']);
        $this->set_share_user_info_weixin_params($uid, $uid, $user_info['User']);
        $this->set('share_user', $user_info['User']);
        $this->set('user_level', $user_level);
        $this->set('user_summary', $user_summary);
        $this->set('my_order_count', $my_order_count);
        $rebate_money = $this->ShareUtil->get_rebate_money($uid);
        $coupon_count = $this->CouponItem->find_my_share_coupons_count($uid);
        $this->set('coupon_count', $coupon_count);
        $this->set('rebate_money', $rebate_money);
        $this->set('uid', $uid);
    }
    
    //    某个团长推荐的分享
    public function get_recommend_weshares($proxy_id)
    {
        $limit = 5;
        $result = $this->Weshares->get_recommend_weshares($proxy_id, $limit);

        foreach ($result as $k => $res) {
            $item_desc = strip_tags($result[$k]['description']);
            $result[$k]['description'] = mb_strlen($item_desc, 'utf8') > 100 ? mb_substr($item_desc, 0, 99, 'utf8') . "..." : $item_desc;
        }

        echo json_encode($result);
        exit();
    }

    /**
     * 分享订单 快递发货
     */
    public function set_order_ship_code()
    {
        $this->autoRender = false;
        $order_id = $_REQUEST['order_id'];
        $ship_company_id = $_REQUEST['company_id'];
        $weshare_id = $_REQUEST['weshare_id'];
        $ship_code = $_REQUEST['ship_code'];
        $this->ShareUtil->set_order_ship_code($ship_company_id, $weshare_id, $ship_code, $order_id);
        echo json_encode(array('success' => true));
        return;
    }

    public function set_order_shipped()
    {
        $this->autoRender = false;
        $order_id = $_REQUEST['order_id'];
        $weshare_id = $_REQUEST['weshare_id'];
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED, 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $order_id, 'status' => ORDER_STATUS_PAID));
        $this->Cart->updateAll(array('status' => ORDER_STATUS_RECEIVED), array('order_id' => $order_id));
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_0', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_0', '');
        $this->WeshareBuy->clear_user_share_order_data_cache(array($order_id), $weshare_id);
        echo json_encode(array('success' => true));
        return;
    }


    /**
     * 更新快递单号
     */
    public function update_order_ship_code()
    {
        $this->autoRender = false;
        $order_id = $_REQUEST['order_id'];
        $ship_type_name = $_REQUEST['ship_type_name'];
        $ship_code = $_REQUEST['ship_code'];
        $weshare_id = $_REQUEST['weshare_id'];
        $this->ShareUtil->update_order_ship_code($ship_code, $weshare_id, $order_id, null, $ship_type_name);
        echo json_encode(array('success' => true));
    }

    public function batch_set_order_ship_code()
    {
        $this->autoRender = false;
        $post_data = $_REQUEST['data'];
        $order_list = json_decode($post_data, true);
        //批量添加任务
        $task = array();
        $ship_name_id_map = ShipAddress::ship_type_name_id_map();
        $order_ids = array();
        foreach ($order_list as $order) {
            $order_id = $order['order_id'];
            $order_ids[] = $order_id;
            $ship_type_name = $order['ship_type_name'];
            $ship_company_id = $ship_name_id_map[$ship_type_name];
            $ship_code = $order['ship_code'];
            $params = "order_id=" . $order_id . "&company_id=" . $ship_company_id . "&ship_code=" . $ship_code . "&ship_type_name=" . $ship_type_name;
            $task[] = array('url' => "/task/process_set_order_ship_code", "postdata" => $params);
        }
        $ret = $this->RedisQueue->add_tasks('order_ship', $task);
        //任务添加失败时输出错误码和错误信息
        echo json_encode(array('success' => $ret));
        return;
    }

    /**
     * 自有自提点 发货
     */
    public function send_arrival_msg()
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $params = json_decode(file_get_contents('php://input'), true);
        $msg = $params['msg'];
        $weshare_id = $params['share_id'];
        $idsStr = $params['ids'];
        $orderIds = explode(',', $idsStr);
        $this->ShareUtil->send_arrival_msg($orderIds, $weshare_id, $uid, $msg);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 设置子分享发货
     */
    public function set_share_shipped()
    {
        $this->autoRender = false;
        $weshare_id = $_REQUEST['share_id'];
        $this->Weshare->updateAll(array('order_status' => WESHARE_ORDER_STATUS_SHIPPED), array('id' => $weshare_id, 'order_status' => WESHARE_ORDER_STATUS_WAIT_SHIP));
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_0', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_0', '');
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
    public function subscribe_sharer($sharer_id, $user_id, $from_type = 0, $share_id = 0)
    {
        $this->autoRender = false;
        //没有关注服务号
//        if (user_subscribed_pys($user_id) != WX_STATUS_SUBSCRIBED) {
//            //save sub reason
//            $sub_type = $from_type == 0 ? SUB_SHARER_REASON_TYPE_FROM_USER_CENTER : SUB_SHARER_REASON_TYPE_FROM_SHARE_INFO;
//            $data_id = $from_type == 0 ? $sharer_id : $share_id;
//            $url = $from_type == 0 ? WX_HOST . '/weshares/user_share_info/' . $sharer_id : WX_HOST . '/weshares/view/' . $share_id;
//            $nicknames = $this->WeshareBuy->get_users_nickname(array($sharer_id, $user_id));
//            $title = $nicknames[$user_id] . '，你好，您已经关注' . $nicknames[$sharer_id];
//            $this->UserSubReason->save(array('type' => $sub_type, 'url' => $url, 'user_id' => $user_id, 'title' => $title, 'data_id' => $data_id));
//            echo json_encode(array('success' => false, 'reason' => 'not_sub', 'url' => WX_SERVICE_ID_GOTO));
//            return;
//        }

        //add log
        $log = [
            "sharer_id" => intval($sharer_id),
            "user_id" => intval($user_id),
            "index" => "event_user_subscribe",
            "type" => "user_subscribe"
        ];
        add_logs_to_es($log);

        $this->WeshareBuy->subscribe_sharer($sharer_id, $user_id);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $sharer_id
     * @param $user_id
     * 取消关注分享者
     */
    public function unsubscribe_sharer($sharer_id, $user_id)
    {
        //add log
        $log = [
            "sharer_id" => intval($sharer_id),
            "user_id" => intval($user_id),
            "index" => "event_user_unsubscribe",
            "type" => "user_unsubscribe"
        ];
        add_logs_to_es($log);

        $this->autoRender = false;
        $this->WeshareBuy->unsubscribe_sharer($sharer_id, $user_id);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * update order remark
     */
    public function update_order_remark()
    {
        $this->autoRender = false;
        $order_id = $_POST['order_id'];
        $order_remark = $_POST['order_remark'];
        $weshare_id = $_POST['weshare_id'];
        $this->WeshareBuy->update_order_remark($order_id, $order_remark, $weshare_id);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $weshareId
     * 分享者订单统计页面
     */
    public function share_order_list($weshareId)
    {
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
    private function merge_child_share_summery_data(&$parent_summery_data, $child_share_datas)
    {
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
    public function loadComment($weshareId)
    {
        $this->autoRender = false;
        $comment_data = $this->WeshareBuy->load_comment_by_share_id($weshareId);
        echo json_encode($comment_data);
        return;
    }

    /**
     * 提交评论
     */
    public function comment()
    {
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
    public function send_buy_percent_msg($weshare_id)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $share_info = $this->ShareUtil->get_weshare_detail($weshare_id);
        if ($share_info['creator']['id'] != $uid && !$this->ShareAuthority->user_can_manage_share($uid, $weshare_id)) {
            echo json_encode(array('success' => false, 'reason' => 'not_creator'));
            return;
        }
        $params = json_decode(file_get_contents('php://input'), true);
        $content = $params['content'];
        $type = $params['type'];
        $result = $this->ShareUtil->send_buy_percent_msg($type, $uid, $share_info, $content, $weshare_id);
        echo json_encode($result);
        return;
    }

    /**
     * @param $weshare_id
     * 发送建团消息 采用队列
     */
    public function send_new_share_msg($weshare_id)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        if (is_blacklist_user($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'user_bad'));
            return;
        }
        $share_info = $this->ShareUtil->get_weshare_detail($weshare_id);
        if ($share_info['creator']['id'] != $uid && !$this->ShareAuthority->user_can_manage_share($uid, $weshare_id)) {
            echo json_encode(array('success' => false, 'reason' => 'not_creator'));
            return;
        }
        $checkCanSendMsgResult = $this->ShareUtil->checkCanSendMsg($uid, $weshare_id, MSG_LOG_NOTIFY_TYPE);
        if (!$checkCanSendMsgResult['success']) {
            echo json_encode($checkCanSendMsgResult);
            return;
        }
        $send_msg_log_data = array('created' => date('Y-m-d H:i:s'), 'sharer_id' => $uid, 'data_id' => $weshare_id, 'type' => MSG_LOG_NOTIFY_TYPE, 'status' => 1);
        $this->ShareUtil->saveSendMsgLog($send_msg_log_data);
        $this->WeshareBuy->send_new_share_msg_to_share_manager($weshare_id);
        $fansPageInfo = $this->WeshareBuy->get_user_relation_page_info($uid);
        $pageCount = $fansPageInfo['pageCount'];
        $pageSize = $fansPageInfo['pageSize'];
        $task_url = "/weshares/process_send_new_share_msg/" . $weshare_id . '/' . $pageCount . '/' . $pageSize;
        $this->RedisQueue->add_tasks('share', $task_url);
        echo json_encode(array('success' => true, 'msg' => $checkCanSendMsgResult['msg']));
        return;
    }

    /**
     * @param $shareId
     * @param $pageCount
     * @param $pageSize
     * 处理 建团消息 task
     */
    public function process_send_new_share_msg($shareId, $pageCount, $pageSize)
    {
        $this->autoRender = false;
        $tasks = array();
        foreach (range(0, $pageCount) as $page) {
            $offset = $page * $pageSize;
            $tasks[] = array('url' => "/weshares/send_new_share_msg_task/" . $shareId . "/" . $pageSize . "/" . $offset);
        }
        $ret = $this->RedisQueue->add_tasks('tasks', $tasks);
        echo json_encode(array('success' => true, 'ret' => $ret));
        return;
    }

    /**
     * @param $shareId
     * @param $limit
     * @param $offset
     * 处理建团消息子任务
     */
    public function send_new_share_msg_task($shareId, $limit, $offset)
    {
        $this->autoRender = false;
        $this->WeshareBuy->send_new_share_msg($shareId, $limit, $offset);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $weshareId
     * 发送团购通知
     */
    public function process_notify_has_buy_fans($weshareId)
    {
        $this->autoRender = false;
        $msg_content = $_REQUEST['content'];
        $share_info = $this->ShareUtil->get_weshare_detail($weshareId);
        $this->WeshareBuy->send_notify_buy_user_msg($share_info, $msg_content);
        echo json_encode(array('success' => true));
    }

    /**
     * @param $weshare_id
     * @param $pageCount
     * @param $pageSize
     * 发送团购进度消息任务
     */
    public function process_send_buy_percent_msg($weshare_id, $pageCount, $pageSize)
    {
        $this->autoRender = false;
        $tasks = array();
        $msg_content = $_REQUEST['content'];
        foreach (range(0, $pageCount) as $page) {
            $offset = $page * $pageSize;
            $tasks[] = array('url' => "/weshares/send_buy_percent_msg_task/" . $weshare_id . "/" . $pageSize . "/" . $offset, "postdata" => "content=" . $msg_content);
        }
        $ret = $this->RedisQueue->add_tasks('tasks', $tasks);
        echo json_encode(array('success' => true, 'ret' => $ret));
        return;
    }

    /**
     * @param $weshare_id
     * @param $limit
     * @param $offset
     * 发送团购进度消息子任务
     */
    public function send_buy_percent_msg_task($weshare_id, $limit, $offset)
    {
        $this->autoRender = false;
        $share_info = $this->ShareUtil->get_weshare_detail($weshare_id);
        $msg_content = $_REQUEST['content'];
        $this->WeshareBuy->send_buy_percent_msg($share_info, $msg_content, $limit, $offset);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $shareId
     * @param $only_paid
     * export order to excel
     * 是否只导出待发货的
     */
    public function order_export($shareId, $only_paid = 1)
    {
        $this->layout = null;
        if ($only_paid == 1) {
            $export_paid_order = true;
        } else {
            $export_paid_order = false;
        }
        $statics_data = $this->get_weshare_buy_info($shareId, true, true, $export_paid_order);
        $this->set($statics_data);
    }

    public function old_order_export($shareId, $only_paid = 1)
    {
        $this->layout = null;
        if ($only_paid == 1) {
            $export_paid_order = true;
        } else {
            $export_paid_order = false;
        }
        $statics_data = $this->get_weshare_buy_info($shareId, true, true, $export_paid_order);
        $this->set($statics_data);
    }

    /**
     * recommend share
     * 推荐分享
     */
    public function recommend()
    {
        $this->autoRender = false;
        $params = json_decode(file_get_contents('php://input'), true);
        $memo = $params['recommend_content'];
        $userId = $params['recommend_user'];
        $shareId = $params['recommend_share'];
        $result = $this->ShareUtil->saveShareRecommendLog($shareId, $userId, $memo);
        echo json_encode($result);
        return;
    }

    /**
     * sharer refund money
     * 退款
     */
    public function refund_money()
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'no_login'));
        }
        $shareId = $_REQUEST['shareId'];
        $orderId = $_REQUEST['orderId'];
        $refundMoney = $_REQUEST['refundMoney'];
        $refundMark = $_REQUEST['refundMark'];
        $result = $this->ShareUtil->order_refund($shareId, $uid, $orderId, $refundMoney, $refundMark);
        echo json_encode($result);
        return;
    }

    /**
     * sharer confirm price
     * 分享者确认价格
     */
    public function confirm_price()
    {
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


    //    小妹订阅号推荐后，用户点击“阅读原文”，显示本页面
    public function special_list()
    {
        $this->layout = 'weshare_bootstrap';

        $shares = $this->WeshareBuy->get_shares_list([3088, 3101, 3061, 2194, 2617, 3040, 3055, 3169, 3170, 3240]);

        $this->set('shares', $shares);
    }

    public function summaries()
    {
        $this->autoRender = false;

        $shareIds = json_decode($_REQUEST['shareIds']);

        $summaries = [];
        if(!empty($shareIds)){
            foreach ($shareIds as $shareId) {
                $summary = $this->ShareUtil->get_index_product_summary($shareId);
                $summary['share_id'] = $shareId;
                $summaries[] = $summary;
            }
        }

        echo json_encode($summaries);
        return;
    }

    /**
     * @param $weshareId
     * @param $is_me
     * @param bool $division
     * @param bool $export
     * @return mixed
     * 获取分享的订单信息
     */
    private function get_weshare_buy_info($weshareId, $is_me, $division = false, $export = false)
    {
        return $this->WeshareBuy->get_share_order_for_show($weshareId, $is_me, $division, $export);
    }


    /**
     * @param $shipInfo
     * @param $uid
     * 记住用户填写的地址
     */
    private function setShareConsignees($shipInfo, $uid)
    {
        $userInfo = $shipInfo['name'];
        $mobileNum = $shipInfo['mobilephone'];
        $remarkAddress = $shipInfo['patchAddress'];
        $consigneeId = $shipInfo['consignee_id'];
        $type = $shipInfo['ship_type'];
        $consignee = $this->OrderConsignees->find('first', array(
            'conditions' => array(
                'creator' => $uid,
                'type' => $type,
                'status' => PUBLISH_YES
            ),
            'fields' => array('id', 'name', 'mobilephone', 'remark_address')
        ));
        $saveData = ['creator' => $uid, 'type' => $type, 'name' => $userInfo, 'mobilephone' => $mobileNum, 'remark_address' => $remarkAddress, 'status' => 1];
        if (!empty($consignee)) {
            //update
            $saveData['id'] = $consignee['OrderConsignees']['id'];
        }
        if ($type == TYPE_CONSIGNEE_SHARE_OFFLINE_STORE) {
            $saveData['ziti_id'] = $consigneeId;
        }
        //save
        $this->OrderConsignees->save($saveData);
    }


    /**
     * @param $weshare_ids
     * @param $sharer_id
     * @return mixed
     * 获取分享者的评论数据(汇总)
     */
    private function getSharerCommentData($weshare_ids, $sharer_id)
    {
        return $this->WeshareBuy->load_sharer_comment_data($weshare_ids, $sharer_id);
    }


    /**
     * @param $weshareId
     * @param $weshareProduct
     * @param $num
     * @return array
     * 购买前检查库存
     */
    private function check_product_num($weshareId, $weshareProduct, $num)
    {
        $store_num = $weshareProduct['WeshareProduct']['store'];
        if ($store_num == -1) {
            return array('result' => false, 'type' => 0);
        }
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
    private function get_coupon_with_shared_id($share_offer_id)
    {
        $uid = $this->currentUser['id'];
        return $this->RedPacket->process_receive($share_offer_id, $uid, $this->is_weixin());
    }

    public function get_useful_coupons()
    {
        $postStr = file_get_contents('php://input');
        $data = json_decode($postStr, true);
        $couponCode = $data['couponCode'];
        //写死
        $coupon_id = 36580;
        $couponItem = $this->CouponItem->find('first', ['conditions' => ['code' => $couponCode, 'coupon_id' => $coupon_id]]);
        if (empty($couponItem)) {
            echo json_encode(['ok' => 1, 'msg' => '不存在该优惠码', 'num' => 0, 'id' => 0]);
            exit();
        }
        $used_cnt = $this->Order->used_code_paid_cnt($couponCode);
        if ($used_cnt > 0) {
            echo json_encode(['ok' => 1, 'msg' => '该优惠码已被使用', 'num' => 0, 'id' => 0]);
        } else {
            echo json_encode(['ok' => 0, 'msg' => '使用成功', 'num' => 2000, 'useCouponId' => $couponItem['CouponItem']['id']]);
        }
        exit();
    }


    //菠萝优惠码使用
    private function order_use_coupon_code($coupon_id, $order_id)
    {
        $this->log('order use coupon' . $coupon_id, LOG_INFO);
        $reduced = 20;
        $couponItem = $this->CouponItem->findById($coupon_id);
        $coupon_code = $couponItem['CouponItem']['code'];
        $used_cnt = $this->Order->used_code_paid_cnt($coupon_code);
        if ($used_cnt == 0) {
            $toUpdate = array('applied_code' => '\'' . $coupon_code . '\'',
                'coupon_total' => 'coupon_total + 2000',
                'total_all_price' => 'if(total_all_price - ' . $reduced . ' < 0, total_all_price, total_all_price - ' . $reduced . ')');
            $this->Order->updateAll($toUpdate, array('id' => $order_id, 'status' => ORDER_STATUS_WAITING_PAY));
        }
    }

    /**
     * @param $coupon_id
     * @param $order_id
     * @param $uid
     * @param $total_all_price
     * 使用 积分和红包逻辑
     * 积分（没有用）
     */
    private function order_use_score_and_coupon($coupon_id, $order_id, $uid, $total_all_price)
    {
        //use coupon
        App::uses('OrdersController', 'Controller');
        $ordersController = new OrdersController();
        $brand_id = -1;
        $this->Session->write($ordersController::key_balanced_conpons(), json_encode([$brand_id => [$coupon_id]]));
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
    private function process_shared_offer($shared_offer_id)
    {
        //to do check offer status
        $get_coupon_result = $this->get_coupon_with_shared_id($shared_offer_id);
        $this->log('share user get red packet result: ' . json_encode($get_coupon_result), LOG_INFO);
        if (!$get_coupon_result['success']) {
            $this->log('share user get red packet result: failed.', LOG_INFO);
            $this->set('get_coupon_type', 'fail');
            return;
        }
        //no more
        if ($get_coupon_result['noMore']) {
            $this->log('share user get red packet result: no more', LOG_INFO);
            $this->set('get_coupon_type', 'no_more');
            return;
        }
        //accepted
        $this->set('follow_shared_offer_id', $shared_offer_id);
        if ($get_coupon_result['accepted'] && $get_coupon_result['just_accepted'] == 0) {
            $this->log('share user get red packet result: accepted', LOG_INFO);
            $this->set('get_coupon_type', 'accepted');
            return;
        }

        $this->log('share user get red packet result: got ' . $get_coupon_result['couponNum'], LOG_INFO);
        $this->set('get_coupon_type', 'got');
        $this->set('couponNum', $get_coupon_result['couponNum']);
    }

    /**
     * @param $sharer
     * @return int
     * 判断用户 能否使用好邻居
     */
    private function sharer_can_use_we_ship($sharer)
    {
        return $this->ShareUtil->read_share_ship_option_setting($sharer, SHARE_SHIP_OPTION_OFFLINE_STORE);
    }


    /**
     * @param $sharer
     * @return bool
     */
    private function is_new_sharer($sharer)
    {
        $level = $this->ShareUtil->get_user_level($sharer);
        if (empty($level)) {
            return true;
        }
        return false;
    }
    //check order ship type gen order address

    /**
     * @param $weshareId
     * @param $shipInfo
     * @param $uid
     * @return mixed
     * 单独处理订单的地址 根据 自有自提、快递、好邻居
     */
    private function get_order_address($weshareId, $shipInfo, $uid)
    {
        $shipType = $shipInfo['ship_type'];
        $consigneeId = $shipInfo['consignee_id'];
        //快递
        if ($shipType == SHARE_SHIP_KUAIDI) {
            return $this->get_express_address($consigneeId);
        }

        //记住或者更新自提地址
        $this->setShareConsignees($shipInfo, $uid);
        //用户记住地址不能为空
        $patchAddress = $shipInfo['patchAddress'];
        if ($patchAddress == null) {
            $patchAddress = '';
        }
        //自有自提
        if ($shipType == SHARE_SHIP_SELF_ZITI) {
            $tinyAddress = $this->WeshareAddress->find('first', array(
                'conditions' => array(
                    'id' => $consigneeId,
                    'weshare_id' => $weshareId
                )
            ));
            $address = $tinyAddress['WeshareAddress']['address'];
            if (!empty($patchAddress)) {
                $address = $address . '【' . $patchAddress . '】';
            }
            return $address;
        }
        //朋友说自提
        if ($shipType == SHARE_SHIP_PYS_ZITI) {
            $offline_store = $this->OfflineStore->findById($consigneeId);
            $address = $offline_store['OfflineStore']['name'];
            return $address;
        }
    }

    //获取快递地址
    private function get_express_address($consignee_id)
    {
        $express_consignee = $this->OrderConsignees->find('first', [
            'conditions' => ['id' => $consignee_id]
        ]);
        $area = $express_consignee['OrderConsignees']['area'];
        if (empty($area)) {
            $area = get_address_location($express_consignee['OrderConsignees']);
            $this->OrderConsignees->update(['area' => "'" . $area . "'"], ['id' => $consignee_id]);
        }
        return $area . $express_consignee['OrderConsignees']['address'];
    }


    /**
     * @param $id
     * @param $weshareId
     * @return mixed
     * 获取分享的物流设置
     */
    private function get_ship_set($id, $weshareId)
    {
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
    private function check_product_store($weshareProducts, $weshareId, $productIdNumMap)
    {
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
     * @return int
     * 用户下单根据选择的物流类型设置数据
     */
    private function process_order_ship_mark($shipType, &$orderData)
    {
        if ($shipType == SHARE_SHIP_PYS_ZITI) {
            $orderData['ship_mark'] = SHARE_SHIP_PYS_ZITI_TAG;
            return self::PROCESS_SHIP_MARK_DEFAULT_RESULT;
        }
        if ($shipType == SHARE_SHIP_SELF_ZITI) {
            $orderData['ship_mark'] = SHARE_SHIP_SELF_ZITI_TAG;
            return self::PROCESS_SHIP_MARK_DEFAULT_RESULT;
        }
        if ($shipType == SHARE_SHIP_KUAIDI) {
            $orderData['ship_mark'] = SHARE_SHIP_KUAIDI_TAG;
            return self::PROCESS_SHIP_MARK_DEFAULT_RESULT;
        }
    }

    /**
     * @param $shareId
     * 给子分享退款
     */
    public function refund_share($shareId)
    {
        $this->autoRender = false;
        $tasks = array();
        $remark = $_REQUEST['remark'];
        $tasks[] = array('url' => "/task/batch_refund_money/" . $shareId . ".json", "postdata" => "remark=" . $remark);
        $ret = $this->RedisQueue->add_tasks('tasks', $tasks);
        echo json_encode(array('success' => true, 'ret' => $ret));
        return;
    }


    /**
     * @param $orderId
     * 获取订单的发货信息
     */
    public function express_info($orderId)
    {
        $this->layout = 'weshare_bootstrap';
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $orderInfo = $orderM->find('first', array(
            'conditions' => array(
                'id' => $orderId
            ),
            'fields' => array('id', 'ship_type_name', 'ship_type', 'member_id', 'creator', 'ship_code', 'consignee_name', 'consignee_address', 'consignee_mobilephone')
        ));
        $carts = $cartM->find('all', array(
            'conditions' => array(
                'order_id' => $orderId
            ),
            'fields' => array(
                'name', 'num'
            )
        ));
        $carts = Hash::extract($carts, '{n}.Cart');
        if (!empty($orderInfo)) {
            $frameShipDetail = ShipAddress::get_ship_detail($orderInfo);
            if (empty($frameShipDetail)) {
                //fetch json ship info
                $code = $orderInfo['Order']['ship_code'];
                $ip = $this->get_ip();
                $result = $this->OrderExpress->get_express_info($code, $ip);
                $express_info = array();
                if ($result->message == 'ok') {
                    $express_info = $result->data;
                }
                $this->set('express_info', $express_info);
            } else {
                $this->set('shipDetailSrc', $frameShipDetail);
            }
            $this->set('order', $orderInfo);
            $this->set('carts', $carts);
        } else {
            $this->set('no_order', true);
        }
    }

    public function u_comment($uid)
    {
        $this->layout = null;
        $user_share_data = $this->WeshareBuy->prepare_user_share_info($uid);
        $my_create_share_ids = $user_share_data['my_create_share_ids'];
        $shareCommentData = $this->getSharerCommentData($my_create_share_ids, $uid);
        $userCommentData = $this->WeshareBuy->load_user_share_comments($uid);
        $this->set('sharer_comment_data', $shareCommentData);
        $this->set('user_comment_data', $userCommentData);
        $this->set('uid', $uid);
    }

    public function fans_list()
    {
        $uid = $this->currentUser['id'];
        $res_self = $this->UserRelation->query("SELECT count(1) AS total FROM cake_user_relations WHERE is_own = 1 AND deleted = 0 AND user_id = ".$uid);
        $res_comm = $this->UserRelation->query("SELECT count(1) AS total FROM cake_user_relations WHERE is_own = 0 AND deleted = 0 AND user_id = ".$uid);
        $this->set('total_self',$res_self[0][0]['total']);
        $this->set('total_comm',$res_comm[0][0]['total']);
        $this->set('type', 1);
        $this->set('title', '我的粉丝');
        $this->set('userId',$uid);
        $this->render('u_list');
    }

    public function sub_list($uid)
    {
        $currentId = $this->currentUser['id'];
        $me = $uid == $currentId ? 1 : 0;
        $this->set('me', $me);
        $this->set('uid', $uid);
        $this->set('type', 1);
        $this->set('title', '我关注的');
        //$this->render('u_list');
    }

    public function get_fans_data($type = 1,$page = 1)
    {
        $this->autoRender = false;

        $uid = $this->currentUser['id'];
        $page = intval($page) > 0 ? intval($page) : 1;
        $type = intval($type) == 1 ? 1 : 0;
        $nickname = $_REQUEST['query'];
        $condition = "";
        $limit = 30;
        //$uid = 559795;
        if($nickname)
        {
            $condition = " AND u.nickname like '%{$nickname}%' ";
        }

        $sql = "SELECT u.nickname AS nickname ,u.id AS id,u.avatar as avatar, u.label as label FROM cake_user_relations r LEFT JOIN cake_users u ON r.follow_id = u.id WHERE r.deleted = 0 AND r.is_own = {$type} AND r.user_id = {$uid} {$condition} LIMIT ".($page-1)*$limit.",{$limit}";
        $users = $this->UserRelation->query($sql);
        $user_ids = [];

        foreach ($users as $k => $user) {
            $users[$k] = $user['u'];
            $user_ids[] = $user['u']['id'];
        }

        foreach ($users as $index => $user) {
            $users[$index]['avatar'] = get_user_avatar($user);
            $users[$index]['label'] = $user['lable'] ? $user['lable'] : "吃货游客";
        }

        echo json_encode($users);
        die;
    }

    public function mine_fansrule()
    {
        $this->layout = null;
        $this->render('mine_fansrule');
    }

    public function get_u_list_data($type, $uid, $page)
    {
        $this->autoRender = false;
        $query = $_REQUEST['query'];
        if ($type == 0) {
            $data = $this->UserFans->get_fans($uid, $page, $query);
        } else {
            $data = $this->UserFans->get_subs($uid, $page, $query);
        }
        echo json_encode($data);
        return;
    }

    public function new_user_guide()
    {
        $this->layout = null;
    }

    /**
     * @param $uid
     * @param $current_uid
     * @param $shareUser
     * 设置分享用户中心页面 微信分享参数
     */
    private function set_share_user_info_weixin_params($uid, $current_uid, $shareUser)
    {
        if (parent::is_weixin()) {
            $wexin_params = $this->set_weixin_share_data($uid, -1);
            $this->set($wexin_params);
            if ($uid == $current_uid) {
                $title = '这是' . $shareUser['nickname'] . '的微分享，快来关注我吧';
                $image = $shareUser['avatar'];
                $desc = '朋友说是一个有人情味的分享社区，这里你不但可以吃到各地的特产，还能认识有趣的人。';
                $detail_url = WX_HOST . "/weshares/get_other_info/$uid.html";
            } else {
                $current_user = $this->currentUser;
                $title = $current_user['nickname'] . '推荐了' . $shareUser['nickname'] . '的微分享，快来关注ta吧！';
                $image = $shareUser['avatar'];
                $desc = $shareUser['nickname'] . '是我的朋友，很靠谱。朋友说是一个有人情味的分享社区，这里你不但可以吃到各地的特产，还能认识有趣的人。';
                $detail_url = WX_HOST . "/weshares/get_other_info/$uid.html";
            }
            if (!$image) {
                // 这里有问题吧? 为啥是dev?
                $image = 'http://dev.tongshijia.com/img/logo_footer.jpg';
            }
            $this->set('detail_url', $detail_url);
            $this->set('title', $title);
            $this->set('image', $image);
            $this->set('desc', $desc);
        }
    }

    private function set_weixin_params_for_pay_result($creator, $weshare, $shared_offer_id){
        $detail_url = WX_HOST . '/weshares/view/' . $weshare['id'] . '?shared_offer_id=' . $shared_offer_id;
        $weixin_share_str = $this->get_weixin_share_str($weshare['id']);
        $image = empty($weshare['default_image']) ? get_user_avatar($creator) : $weshare['default_image'];
        $title = $creator['nickname'] . '分享:' . $weshare['title'];
        $desc = $creator['nickname'] . '我认识，很靠谱！送你一个爱心礼包，一起来参加。';
        $this->set('share_string', $weixin_share_str);
        $this->set('title', $title);
        $this->set('detail_url', $detail_url);
        $this->set('image', $image);
        $this->set('desc', $desc);
    }

    private function set_weixin_params_for_view($user, $creator, $weshare, $recommend, $shared_offer_id, $summary, $ordersDetail)
    {
        $title = remove_emoji(preg_replace('/\s+/', '', $weshare['title']));
        $image = empty($weshare['default_image']) ? get_user_avatar($creator) : $weshare['default_image'];
        $desc = remove_emoji(mb_substr(strip_tags(preg_replace('/\s+/', '', $weshare['description'])), 0, 30, "UTF8"));
        // 自己转发
        if ($user['id'] == $creator['id']) {
            $title = $creator['nickname'] . '分享:' . $weshare['title'];
            $order_count = $summary['order_count'];
            if ($order_count >= 5) {
                $desc = '已经有' . $order_count . '人报名，' . $desc;
            }
        } else if (!empty($user) && !empty($ordersDetail['orders'])) {
            // 用户已经报名
            $title = $user['nickname'] . '报名了' . $creator['nickname'] . '分享的' . $title;
            $desc = $creator['nickname'] . '我认识，很靠谱。' . $desc;
        } else if (!empty($user)) {
            // 用户未购买
            $title = $user['nickname'] . '推荐' . $creator['nickname'] . '分享的' . $title;
            $desc = $creator['nickname'] . '我认识，很靠谱。' . $desc;
        } else {
            $title = $creator['nickname'] . '分享了' . $title;
            $desc = $creator['nickname'] . '我认识，很靠谱。' . $desc;
        }

        $detail_url = WX_HOST . '/weshares/view/' . $weshare['id'];
        if ($user['is_proxy'] && $user['id'] != $creator['id']) {
            // 团长
            $detail_url = $detail_url . '?recommend=' . $user['id'];
        }
        if (!$user['is_proxy'] && !empty($recommend)) {
            // 继续转发上一个团长的
            $detail_url = $detail_url . '?recommend=' . $recommend;
        }

        if (!empty($shared_offer_id) && !empty($user)) {
            if (strpos($detail_url, '?') !== false) {
                $detail_url = $detail_url . '&shared_offer_id=' . $shared_offer_id;
            } else {
                $detail_url = $detail_url . '?shared_offer_id=' . $shared_offer_id;
            }
            $image = 'http://static.tongshijia.com/static/weshares/images/share_icon.jpg';
            $desc = $creator['nickname'] . '我认识，很靠谱！送你一个爱心礼包，一起来参加。';
        }

        $weixin_share_str = $this->get_weixin_share_str($weshare['id']);
        $this->set('share_string', $weixin_share_str);
        $this->set('title', $title);
        $this->set('detail_url', $detail_url);
        $this->set('image', $image);
        $this->set('desc', $desc);
    }
}
