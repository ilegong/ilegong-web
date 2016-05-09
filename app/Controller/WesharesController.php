<?php

class WesharesController extends AppController
{
    var $uses =
        array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User', 'OrderConsignees', 'Oauthbind', 'SharedOffer', 'CouponItem',
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
        $this->layout = null;
        $index_products = $this->ShareUtil->get_index_product($tag);
        $uid = $this->currentUser['id'];
        $this->set('index_products', $index_products);
        //$this->set('weshare_ids', Hash::extract($index_products, '{n}.Weshare.id'));
        $this->set('uid', $uid);
        $this->set('tag', $tag);
        //$this->render();
    }

    /**
     * @param string $weshare_id
     * @param int $from 标示从什么地方跳转的访问
     * 跳转到分享的详情页
     */
    public function view($weshare_id, $from = 0)
    {
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
            $weshare = $this->WeshareBuy->get_weshare_info($weshare_id);
            if ($weshare['type'] == SHARE_TYPE_POOL_FOR_PROXY) {
                //check share type
                if (!$this->ShareUtil->is_proxy_user($uid)) {
                    $user_can_manage_share = $this->ShareAuthority->user_can_manage_share($uid, $weshare_id);
                    if (!$user_can_manage_share) {
                        //not proxy or manage redirect index
                        $this->redirect('/weshares/index');
                        return;
                    }
                }
            }
            $weshare_creator = $weshare['creator'];
            $shared_offers = $this->SharedOffer->find_new_offers_by_weshare_creator($uid, $weshare_creator);
            //get first offer
            if (!empty($shared_offers)) {
                $this->set('shared_offer_id', $shared_offers[0]['SharedOffer']['id']);
                $this->set('from', $this->pay_type);
            }
            //from paid done
            if ($from == 1) {
                $paidMsg = $_REQUEST['msg'];
                if ($paidMsg == 'ok') {
                    $this->set('from', $this->pay_type);
                } else if ($paidMsg == 'cancel') {
                    $this->log('Payment of user ' . $uid . ' to weshare ' . $weshare_id . ' failed: canceled', LOG_INFO);
                } else {
                    $this->log('Payment of user ' . $uid . ' to weshare ' . $weshare_id . ' failed: ' . $paidMsg, LOG_ERR);
                }
            }
        }
        $this->set('weshare_id', $weshare_id);
        //form paid done
        //$this->log('weshare view mark ' . $_REQUEST['mark']);
        //获取推荐人
        $recommend = $_REQUEST['recommend'];
        //add rebate log
        //自己推荐人购买不能加入推荐
        if ($this->ShareUtil->is_proxy_user($recommend) && $recommend != $uid) {
            if (!empty($recommend) && !empty($uid)) {
                $rebateLogId = $this->ShareUtil->save_rebate_log($recommend, $uid, $weshare_id);
                $this->set('recommend_id', $recommend);
                $this->set('rebateLogId', $rebateLogId);
            }
        }
        //mark this form comment template msg and auto pop comment dialog
        $comment_order_id = $_REQUEST['comment_order_id'];
        $replay_comment_id = $_REQUEST['reply_comment_id'];
        if (!empty($comment_order_id)) {
            $this->set('comment_order_id', $comment_order_id);
        }
        if (!empty($replay_comment_id)) {
            $this->set('reply_comment_id', $replay_comment_id);
        }
        $this->WeshareBuy->update_share_view_count($weshare_id);
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

    //获取分享的汇总数据
    public function get_share_summery_data($shareId, $uid)
    {
        $this->autoRender = false;
        try {
            $summery = $this->WeshareBuy->get_share_and_all_refer_share_summary($shareId, $uid);
        } catch (Exception $e) {
            $this->log("Failed to get share and all refer share count for share " . $shareId . ": " . $e->getMessage(), LOG_ERR);
            $summery = ['order' => 0, 'comment' => 0];
        }
        echo json_encode($summery);
        exit();
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
            $detail['weixininfo'] = $this->set_weixin_share_data($uid, $weshareId);
            //$detail['my_coupons'] = [];//CouponItem.id Coupon.reduced_price
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
    public function get_share_comment_data($weshareId)
    {
        $this->autoRender = false;
        $comment_data = $this->WeshareBuy->load_comment_by_share_id($weshareId);
        echo json_encode(array('comment_data' => $comment_data));
        exit();
    }

    /**
     * @param $uid
     * @param $weshareId
     * @return array|null
     * 把微信分享的一些参数设置好
     */
    public function set_weixin_share_data($uid, $weshareId)
    {
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
                'business_remark' => $business_remark);
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
                //记录返利的钱
                $rebate_log_id = $this->WeshareBuy->log_proxy_rebate_log($weshareId, $uid, 0, 1, $orderId, $rebate_fee * 100);
                if (!empty($rebate_log_id)) {
                    $update_order_data['cate_id'] = $rebate_log_id;
                    $update_order_data['total_all_price'] = $update_order_data['total_all_price'] - $rebate_fee;
                }
            }
            if ($this->Order->updateAll($update_order_data, array('id' => $orderId))) {
                $coupon_id = $postDataArray['coupon_id'];
                $this->log('use coupon id '.$coupon_id, LOG_DEBUG);
                //红包
                if (!empty($coupon_id)) {
                    //菠萝
                    if($weshareId == 4507){
                        //use code
                        $this->order_use_coupon_code($coupon_id, $orderId, $uid);
                    }else{
                        $this->order_use_score_and_coupon($orderId, $uid, 0, $totalPrice / 100);
                    }
                }
                //返利
                $this->ShareUtil->update_rebate_log_order_id($rebateLogId, $orderId, $weshareId);
                $this->Orders->on_order_created($uid, $weshareId, $orderId);
            }
            $this->log('Create order for ' . $uid . ' with weshare ' . $weshareId . ' successfully, order id ' . $orderId, LOG_INFO);
            $dataSource->commit();
            echo json_encode(array('success' => true, 'orderId' => $orderId));
            exit();
        } catch (Exception $e) {
            $this->log($uid . 'buy share ' . $weshareId . $e);
            $dataSource->rollback();
            echo json_encode(array('success' => false, 'reason' => $e->getMessage()));
            exit();
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
        $this->layout = 'weshare_bootstrap';
        $current_uid = $this->currentUser['id'];
        if (empty($uid)) {
            $uid = $current_uid;
        }
        $user_share_data = $this->WeshareBuy->prepare_user_share_info($uid);
        $creators = $user_share_data['creators'];
        $joinShareOrderStatus = $user_share_data['joinShareOrderStatus'];
        $myCreateShares = $user_share_data['myCreateShares'];
        $myJoinShares = $user_share_data['myJoinShares'];
        $shareOperateMap = $user_share_data['authority_share_map'];
        //$joinShareComments = $user_share_data['joinShareComments'];
        $shareUser = $creators[$uid];
        $this->set_share_user_info_weixin_params($uid, $current_uid, $shareUser);
        $userShareSummery = $this->getUserShareSummery($uid);
        if ($uid != $current_uid) {
            $sub_status = $this->WeshareBuy->check_user_subscribe($uid, $current_uid);
            $this->set('sub_status', $sub_status);
        }
        $user_is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if ($user_is_proxy) {
            $this->set('is_proxy', true);
        }
        //get user level
        $user_level = $this->ShareUtil->get_user_level($uid);
        $this->set('user_level', $user_level);
        if ($uid == $current_uid) {
            $rebate_money = $this->ShareUtil->get_rebate_money($current_uid);
            $this->set('rebate_money', $rebate_money);
            $this->set('show_rebate_money', $rebate_money > 0);
        }
        $u_comment_count = $this->WeshareBuy->get_user_comment_count($uid);
        $this->set('u_comment_count', $u_comment_count);
        $this->set($userShareSummery);
        $this->set('is_me', $uid == $current_uid);
        $this->set('current_uid', $current_uid);
        $this->set('visitor', $current_uid);
        $this->set('share_user', $shareUser);
        $this->set('creators', $creators);
        $this->set('my_create_shares', $myCreateShares);
        $this->set('my_join_shares', $myJoinShares);
        $this->set('authority_shares', $user_share_data['authority_shares']);
        $this->set('join_share_order_status', $joinShareOrderStatus);
        $this->set('authority_share_map', $shareOperateMap);
        $pintuan_data = $this->PintuanHelper->get_user_pintuan_data($uid);
        $this->set('pintuan_data', $pintuan_data);
        if ($uid == $current_uid && !empty($user_level)) {
            $userMonthOrderCount = $this->WeshareBuy->get_month_total_count($uid);
            $this->set('order_count', $userMonthOrderCount);
        }
        //$this->set('joinShareComments', $joinShareComments);
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
    public function unsubscribe_sharer($sharer_id, $user_id)
    {
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
        $checkCanSendMsgResult = $this->ShareUtil->checkCanSendMsg($uid);
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
        //$refund_money = $this->WeshareBuy->get_refund_money_by_weshare($shareId);
        //$rebate_money = $this->ShareUtil->get_share_rebate_money($shareId);
        $this->set($statics_data);
        //$this->set('refund_money', $refund_money);
        //$this->set('rebate_money', $rebate_money);
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
        //$refund_money = $this->WeshareBuy->get_refund_money_by_weshare($shareId);
        //$rebate_money = $this->ShareUtil->get_share_rebate_money($shareId);
        $this->set($statics_data);
        //$this->set('refund_money', $refund_money);
        //$this->set('rebate_money', $rebate_money);
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
     * @param $uid
     * @return array
     * 获取分享者的一些统计数据(粉丝、分享次数)
     */
    private function getUserShareSummery($uid)
    {
        return $this->WeshareBuy->get_user_share_summary($uid);
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
        $this->log('order use coupon'.$coupon_id, LOG_DEBUG);
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
     * @param $order_id
     * @param $uid
     * @param $brand_id
     * @param $total_all_price
     * 使用 积分和红包逻辑
     * 积分（没有用）
     */
    private function order_use_score_and_coupon($order_id, $uid, $brand_id, $total_all_price)
    {
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
    private function process_shared_offer($shared_offer_id)
    {
        //to do check offer status
        $get_coupon_result = $this->get_coupon_with_shared_id($shared_offer_id);
        $this->log('share user get red packet result: ' . json_encode($get_coupon_result), LOG_DEBUG);
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

    public function fans_list($uid)
    {
        $this->layout = null;
        $currentId = $this->currentUser['id'];
        $me = $uid == $currentId ? 1 : 0;
        $this->set('uid', $uid);
        $this->set('me', $me);
        $this->set('type', 0);
        $title = $me == 1 ? '我的粉丝' : 'TA的粉丝';
        $this->set('title', $title);
        $this->render('u_list');
    }

    public function sub_list($uid)
    {
        $this->layout = null;
        $currentId = $this->currentUser['id'];
        $me = $uid == $currentId ? 1 : 0;
        $this->set('me', $me);
        $this->set('uid', $uid);
        $this->set('type', 1);
        $title = $me == 1 ? '我关注的' : 'TA关注的';
        $this->set('title', $title);
        $this->render('u_list');
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
                $image = $shareUser['image'];
                $desc = '朋友说是一个有人情味的分享社区，这里你不但可以吃到各地的特产，还能认识有趣的人。';
            } else {
                $current_user = $this->currentUser;
                $title = $current_user['nickname'] . '推荐了' . $shareUser['nickname'] . '的微分享，快来关注ta吧！';
                $image = $shareUser['image'];
                $desc = $shareUser['nickname'] . '是我的朋友，很靠谱。朋友说是一个有人情味的分享社区，这里你不但可以吃到各地的特产，还能认识有趣的人。';
            }
            if (!$image) {
                // 这里有问题吧? 为啥是dev?
                $image = 'http://dev.tongshijia.com/img/logo_footer.jpg';
            }
            $detail_url = WX_HOST . '/weshares/user_share_info/' . $uid;
            $this->set('detail_url', $detail_url);
            $this->set('title', $title);
            $this->set('image', $image);
            $this->set('desc', $desc);
        }
    }
}
