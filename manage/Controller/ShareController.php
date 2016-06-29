<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 7/20/15
 * Time: 17:11
 */
class ShareController extends AppController
{

    var $name = 'Share';

    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User',
        'WeshareShipSetting', 'OfflineStore', 'Oauthbind', 'Comment', 'RefundLog', 'PayNotify', 'RebateTrackLog', 'PayLog', 'PintuanTag');

    var $components = array('Weixin', 'Paginator');


    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = 'bootstrap_layout';
    }

    public function admin_utils()
    {

    }


    public function admin_set_user_proxy($userId)
    {
        $this->autoRender = false;
        $this->User->updateAll(array('User.is_proxy' => 1), array('User.id' => $userId));
        echo json_encode(array('success' => true));
        return;
    }

    public function admin_clear_user_cache($userId)
    {
        $this->autoRender = false;
        Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $userId, '');
        echo json_encode(array('success' => true));
        return;
    }

    public function admin_clear_share_cache($shareId)
    {
        $this->autoRender = false;
        Cache::write(SHARE_DETAIL_DATA_CACHE_KEY . '_' . $shareId, '');
        Cache::write(SHARE_DETAIL_DATA_WITH_TAG_CACHE_KEY . '_' . $shareId, '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $shareId . '_1_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $shareId . '_0_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $shareId . '_1_0', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $shareId . '_0_0', '');
        Cache::write(SIMPLE_SHARE_INFO_CACHE_KEY . '_' . $shareId, '');
        echo json_encode(array('success' => true));
        return;
    }

    public function admin_delete($shareId)
    {
        $this->autoRender = false;
        $shareInfo = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $shareId
            )
        ));
        try {
            if (!empty($shareInfo)) {
                $uid = $shareInfo['Weshare']['creator'];
                $this->Weshare->updateAll(array('status' => 2), array('id' => $shareId));
                //$this->WeshareProduct->deleteAll(array('weshare_id' => $shareId));
                //$this->WeshareAddress->deleteAll(array('weshare_id' => $shareId));
                Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $uid, '');
                Cache::write(SHARE_USER_SUMMERY_CACHE_KEY . '_' . $uid, '');
            }
        } catch (Exception $e) {
            echo json_encode(array('msg' => $e->getMessage(), 'str' => $e->getTraceAsString()));
        }
        echo json_encode(array('success' => true));
        return;
    }

    public function admin_set_offline_store_code()
    {
        $this->autoRender = false;
        $order_id = $_REQUEST['order_id'];
        $code = $_REQUEST['ship_code'];
        //select order paid to send msg
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'id' => $order_id,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => array(ORDER_STATUS_PAID),
                'ship_mark' => 'pys_ziti'
            ),
            'fields' => array(
                'id', 'consignee_name', 'consignee_address', 'creator', 'member_id', 'consignee_id'
            )
        ));
        $weshare_id = $order['Order']['member_id'];
        $weshare = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $weshare_id
            )
        ));
        $order_user_ids = Hash::extract($order, 'Order.creator');
        $share_creator = $weshare['Weshare']['creator'];
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
        $cart_info = $this->get_cart_name_and_num($order_id);
        $userOauthBinds = Hash::combine($userOauthBinds, '{n}.Oauthbind.user_id', '{n}.Oauthbind.oauth_openid');
        $desc = '感谢大家对' . $users[$share_creator]['nickname'] . '的支持，分享快乐。';
        $detail_url = WX_HOST . '/weshares/view/' . $weshare_id;
        $order_id = $order['Order']['id'];
        $order_user_id = $order['Order']['creator'];
        $open_id = $userOauthBinds[$order_user_id];
        $order_user_name = $users[$order_user_id]['nickname'];
        $title = $order_user_name . '你好，您订购的' . $cart_info['cart_name'] . '已经到达自提点[好邻居便利店]，提货码：' . $code . '，生鲜娇贵，请尽快取货哈。';
        $offlineStore = $this->get_offline_store($order['Order']['consignee_id']);
        $this->Weixin->send_share_product_arrival($open_id, $detail_url, $title, $order_id, $offlineStore['OfflineStore']['alias'], $offlineStore['OfflineStore']['name'], $desc);
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED, 'ship_code' => "'" . $code . "'"), array('id' => $order_id));
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_1_0', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id . '_0_0', '');
        foreach ($order_user_ids as $uid) {
            Cache::write(USER_SHARE_ORDER_INFO_CACHE_KEY . '_' . $weshare_id . '_' . $uid, '');
        }
        echo json_encode(array('success' => true));
    }

    public function admin_set_share_paid($shareId)
    {
        $this->Weshare->updateAll(array('settlement' => 1), array('id' => $shareId));
        $this->send_share_paid_msg($shareId);
        $this->redirect(array('action' => 'admin_share_for_pay'));
    }

    public function set_share_paid($shareId, $fee)
    {
        $this->Weshare->updateAll(array('settlement' => 1), array('id' => $shareId));
        if ($fee > 0) {
            $OauthbindM = ClassRegistry::init('Oauthbind');
            $weshareM = ClassRegistry::init('Weshare');
            $weshare = $weshareM->find('first', array(
                'conditions' => array(
                    'id' => $shareId
                ),
                'fields' => array('id', 'creator', 'title')
            ));
            $userOauthBinds = $OauthbindM->find('first', array(
                'conditions' => array(
                    'user_id' => $weshare['Weshare']['creator']
                ),
                'fields' => array('user_id', 'oauth_openid')
            ));
            $user_open_id = $userOauthBinds['Oauthbind']['oauth_openid'];
            $detail_url = WX_HOST . '/weshares/view/' . $shareId;
            $title = '您的编号为' . $shareId . '的分享已经结款';
            $desc = '一共结款' . $fee . '元';
            $this->Weixin->send_share_paid_msg($user_open_id, $detail_url, $title, $desc);
        }
    }

    private function send_share_paid_msg($shareId)
    {
        $fee = $_REQUEST['fee'];
        if ($fee > 0) {
            $OauthbindM = ClassRegistry::init('Oauthbind');
            $weshareM = ClassRegistry::init('Weshare');
            $weshare = $weshareM->find('first', array(
                'conditions' => array(
                    'id' => $shareId
                ),
                'fields' => array('id', 'creator', 'title')
            ));
            $userOauthBinds = $OauthbindM->find('first', array(
                'conditions' => array(
                    'user_id' => $weshare['Weshare']['creator']
                ),
                'fields' => array('user_id', 'oauth_openid')
            ));
            $user_open_id = $userOauthBinds['Oauthbind']['oauth_openid'];
            $detail_url = WX_HOST . '/weshares/view/' . $shareId;
            $title = '您的编号为' . $shareId . '的分享已经结款';
            $desc = '一共结款' . $fee . '元';
            $this->Weixin->send_share_paid_msg($user_open_id, $detail_url, $title, $desc);
        }
    }

    /**
     * @param $weshares
     * reduce weshares 把子分享和父分享关联起来
     */
    private function reduce_weshares($weshares)
    {
        $remove_keys = array();
        foreach ($weshares as $share_id => $share_item) {
            if ($share_item['type'] == 1) {
                $refer_share_id = $share_item['refer_share_id'];
                $parent_share = $weshares[$refer_share_id];
                if (!empty($parent_share)) {
                    if (!isset($parent_share['child_share'])) {
                        $parent_share['child_share'] = array();
                    }
                    $parent_share['child_share'][$share_id] = $share_item;
                    $remove_keys[] = $share_id;
                }
            }
        }
        //remove single child share
        foreach ($weshares as $share_id => $share_item) {
            if ($share_item['type'] == 1) {
                if (!empty($share_item['refer_share_id'])) {
                    $remove_keys[] = $share_id;
                }
            }
        }
        foreach ($remove_keys as $key) {
            unset($weshares[$key]);
        }
        return $weshares;
    }

    public function reduce_share_summery($weshares, &$summery_data, &$repaid_money_result, &$weshare_rebate_map, &$weshare_refund_money_map)
    {
        foreach ($weshares as $share_item_key => $share_item) {
            $share_summery_data = $summery_data[$share_item_key];
            $child_shares = $share_item['child_share'];
            if (!empty($child_shares)) {
                $share_summery_data['child_ship_fee'] = 0;
                foreach ($child_shares as $child_item_id => $child_share_item) {
                    $child_share_summery_data = $summery_data[$child_item_id];
                    $share_summery_data['total_price'] = $share_summery_data['total_price'] + $child_share_summery_data['total_price'];
                    $share_summery_data['child_ship_fee'] = $share_summery_data['child_ship_fee'] + $child_share_summery_data['ship_fee'];
                    $share_summery_data['coupon_total'] = $share_summery_data['coupon_total'] + $child_share_summery_data['coupon_total'];
                    $share_summery_data['product_total_price'] = $share_summery_data['product_total_price'] + $child_share_summery_data['product_total_price'];
                    $repaid_money_result[$share_item_key] = $repaid_money_result[$share_item_key] + $repaid_money_result[$child_item_id];
                    $weshare_rebate_map[$share_item_key] = $weshare_rebate_map[$share_item_key] + $weshare_rebate_map[$child_item_id];
                    $weshare_refund_money_map[$share_item_key] = $weshare_refund_money_map[$share_item_key] + $weshare_refund_money_map[$child_item_id];
                }
            }

        }
    }

    private function process_share_data($cond)
    {
        $this->Paginator->settings = $cond;
        $this->Paginator->settings['paramType'] = 'querystring';
        $weshares = $this->Paginator->paginate('Weshare', $cond['Weshare']['conditions']);
        //$weshares = $this->Weshare->find('all', $cond);
        $weshare_ids = Hash::extract($weshares, '{n}.Weshare.id');
        $weshare_creator_ids = Hash::extract($weshares, '{n}.Weshare.creator');
        $creators = $this->User->find('all', array(
            'conditions' => array(
                'id' => $weshare_creator_ids
            ),
            'fields' => array(
                'id', 'nickname', 'image', 'wx_subscribe_status', 'description', 'mobilephone', 'payment'
            )
        ));
        $creators = Hash::combine($creators, '{n}.User.id', '{n}.User');
        $weshares = Hash::combine($weshares, '{n}.Weshare.id', '{n}.Weshare');
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY,
                'member_id' => $weshare_ids,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY)
            )
        ));
        $refund_orders = array();
        $refund_order_ids = array();
        $summery_data = array();
        foreach ($orders as $item) {
            $member_id = $item['Order']['member_id'];
            $order_total_price = $item['Order']['total_all_price'];
            $order_ship_fee = $item['Order']['ship_fee'];
            $order_coupon_total = $item['Order']['coupon_total'];
            $order_product_price = $item['Order']['total_price'];
            if ($item['Order']['status'] == ORDER_STATUS_RETURN_MONEY || $item['Order']['status'] == ORDER_STATUS_RETURNING_MONEY) {
                $refund_order_ids[] = $item['Order']['id'];
                if (!isset($refund_orders[$member_id])) {
                    $refund_orders[$member_id] = array();
                }
                $refund_orders[$member_id][] = $item;
            }
            if (!isset($summery_data[$member_id])) {
                $summery_data[$member_id] = array('total_price' => 0, 'ship_fee' => 0, 'coupon_total' => 0);
            }
            $summery_data[$member_id]['total_price'] = $summery_data[$member_id]['total_price'] + $order_total_price;
            $summery_data[$member_id]['ship_fee'] = $summery_data[$member_id]['ship_fee'] + $order_ship_fee;
            $summery_data[$member_id]['coupon_total'] = $summery_data[$member_id]['coupon_total'] + $order_coupon_total;
            $summery_data[$member_id]['product_total_price'] = $summery_data[$member_id]['product_total_price'] + $order_product_price;
        }
        $refund_logs = $this->RefundLog->find('all', array(
            'order_id' => $refund_order_ids
        ));
        $refund_logs = Hash::combine($refund_logs, '{n}.RefundLog.order_id', '{n}.RefundLog.refund_fee');
        $weshare_refund_money_map = array();
        foreach ($refund_orders as $item_share_id => $item_orders) {
            $share_refund_money = 0;
            $weshare_refund_money_map[$item_share_id] = 0;
            foreach ($item_orders as $refund_order_item) {
                $order_id = $refund_order_item['Order']['id'];
                $share_refund_money = $share_refund_money + $refund_logs[$order_id];
            }
            $weshare_refund_money_map[$item_share_id] = $share_refund_money / 100;
        }
        $weshare_rebate_map = $this->get_share_rebate_money($weshare_ids);
        $repaid_money_result = $this->get_share_repaid_money($weshare_ids);
        //$weshares = $this->reduce_weshares($weshares);
        $this->reduce_share_summery($weshares, $summery_data, $repaid_money_result, $weshare_rebate_map, $weshare_refund_money_map);
        return ['repaid_money_result' => $repaid_money_result, 'weshare_rebate_map' => $weshare_rebate_map, 'weshare_refund_map' => $weshare_refund_money_map, 'weshares' => $weshares, 'weshare_summery' => $summery_data, 'creators' => $creators];
    }


    private function set_query_share_cond($cond)
    {
        $share_id = $_REQUEST['share_id'];
        if (!empty($share_id)) {
            $cond['id'] = $share_id;
        }
        $share_title = $_REQUEST['share_name'];
        if (!empty($share_title)) {
            $cond['title like '] = '%' . $share_title . '%';
        }
        $sharer_id = $_REQUEST['sharer_id'];
        if (!empty($sharer_id)) {
            $cond['creator'] = $sharer_id;
        }
        $this->set('share_id', $share_id);
        $this->set('share_name', $share_title);
        $this->set('sharer_id', $sharer_id);
        return $cond;
    }

    public function admin_share_paid()
    {
        $cond = array(
            'status' => array(1, 2),
            'settlement' => 1,
        );
        $cond = $this->set_query_share_cond($cond);
        $q_c = array(
            'Weshare' => array(
                'conditions' => $cond,
                'limit' => 50,
                'order' => 'Weshare.id DESC'
            )
        );
        $result = $this->process_share_data($q_c);
        $this->set('repaid_money_result', $result['repaid_money_result']);
        $this->set('weshare_rebate_map', $result['weshare_rebate_map']);
        $this->set('weshare_refund_map', $result['weshare_refund_map']);
        $this->set('weshares', $result['weshares']);
        $this->set('weshare_summery', $result['weshare_summery']);
        $this->set('creators', $result['creators']);
    }

    public function admin_share_for_pay()
    {
        $cond = array(
            'status' => array(1, 2, -1),
            'settlement' => 0,
        );
        $cond = $this->set_query_share_cond($cond);
        $q_c = array(
            'Weshare' => array(
                'conditions' => $cond,
                'limit' => 50,
                'order' => ['Weshare.close_date DESC', 'Weshare.id DESC']
            )
        );
        $result = $this->process_share_data($q_c);
        //['repaid_money_result' => $repaid_money_result, 'weshare_rebate_map' => $weshare_rebate_map, 'weshare_refund_map' => $weshare_refund_money_map, 'weshares' => $weshares, 'weshare_summery' => $summery_data, 'creators' => $creators]
        /**
         * $this->set('repaid_money_result', $repaid_money_result);
         * $this->set('weshare_rebate_map', $weshare_rebate_map);
         * $this->set('weshare_refund_map', $weshare_refund_money_map);
         * $this->set('weshares', $weshares);
         * $this->set('weshare_summery', $summery_data);
         * $this->set('creators', $creators);
         */
        $this->set('repaid_money_result', $result['repaid_money_result']);
        $this->set('weshare_rebate_map', $result['weshare_rebate_map']);
        $this->set('weshare_refund_map', $result['weshare_refund_map']);
        $this->set('weshares', $result['weshares']);
        $this->set('weshare_summery', $result['weshare_summery']);
        $this->set('creators', $result['creators']);
    }

    public function admin_gen_stop_share_balance_logs()
    {
        $cond = array(
            'Weshare.status' => array(1, 2, -1),
            'BalanceLog.id' => null
        );
        if ($_REQUEST['share_id']) {
            $cond['Weshare.id'] = $_REQUEST['share_id'];
        }
        $q_c = array(
            'Weshare' => array(
                'conditions' => $cond,
                'limit' => 50,
                'order' => ['Weshare.id DESC'],
                'joins' => [
                    [
                        'type' => 'left',
                        'table' => 'cake_balance_logs',
                        'alias' => 'BalanceLog',
                        'conditions' => 'BalanceLog.share_id = Weshare.id'

                    ]
                ],
                'fields' => ['Weshare.*']
            )
        );

        $result = $this->process_share_data($q_c);
        $repaid_money_result = $result['repaid_money_result'];
        $weshare_rebate_map = $result['weshare_rebate_map'];
        $weshare_refund_map = $result['weshare_refund_map'];
        $weshares = $result['weshares'];
        $weshare_summery = $result['weshare_summery'];
        $save_data = [];
        foreach ($weshares as $item) {
            $share_id = $item['id'];
            $creator = $item['creator'];
            $refund_fee = floatval($weshare_refund_map[$share_id]);
            $coupon_fee = round(floatval($weshare_summery[$share_id]['coupon_total'] / 100), 2);
            $product_fee = floatval($weshare_summery[$share_id]['product_total_price']);
            $rebate_fee = floatval($weshare_rebate_map[$share_id]) - floatval($weshare_summery[$share_id]['child_ship_fee'] / 100);
            $total_fee = floatval($weshare_summery[$share_id]['total_price']);
            $ship_fee = round($weshare_summery[$share_id]['ship_fee'] / 100, 2);
            $current_share_repaid_money = $repaid_money_result[$share_id];
            if ($current_share_repaid_money == 0) {
                $current_share_repaid_money = 0;
            }
            $status = $item['settlement'] == 1 ? 1 : 0;
            $transaction_fee = floatval($weshare_summery[$share_id]['total_price']) - floatval($weshare_refund_map[$share_id]) - floatval($weshare_rebate_map[$share_id]) + $current_share_repaid_money;
            $weshare_type = $item['type'];
            $type = 1;
            if ($weshare_type == 6) {
                $type = 2;
            }
            $begin_datetime = $item['created'];
            $end_datetime = empty($item['close_date']) ? date('Y-m-d H:i:s') : $item['close_date'];
            $save_data[] = [
                'share_id' => $share_id,
                'user_id' => $creator,
                'ship_fee' => $ship_fee,//快递费用
                'refund_fee' => $refund_fee,//退款费用
                'coupon_fee' => $coupon_fee,//优惠券费用
                'origin_total_fee' => $product_fee,//产品费用
                'rebate_fee' => $rebate_fee,//返利费用
                'total_fee' => $total_fee,//总费用
                'transaction_fee' => $transaction_fee,//交易费用
                'brokerage' => 0,//佣金
                'trade_fee' => $transaction_fee,//打款费
                'status' => $status,
                'type' => $type,
                'created' => date('Y-m-d H:i:s'),
                'updated' => date('Y-m-d H:i:s'),
                'begin_datetime' => $begin_datetime,
                'end_datetime' => $end_datetime,
                'remark' => '系统自动生成'
            ];
        }
        $this->loadModel('BalanceLog');
        $this->BalanceLog->saveAll($save_data);
        echo 'success';
        exit;
    }

    public function admin_merge_ship_setting_data()
    {
        $this->autoRender = false;
        $shares = $this->Weshare->find('all', array(
            'limit' => 200
        ));
        $saveData = array();
        foreach ($shares as $share) {
            $share_id = $share['Weshare']['id'];
            $historyData = $this->WeshareShipSetting->find('first', array(
                'conditions' => array(
                    'weshare_id' => $share_id
                )
            ));
            if (empty($historyData)) {
                $saveData[] = array('weshare_id' => $share_id, 'status' => 1, 'ship_fee' => 0, 'tag' => 'kuai_di');
                $saveData[] = array('weshare_id' => $share_id, 'status' => 1, 'ship_fee' => 0, 'tag' => 'self_ziti');
                $saveData[] = array('weshare_id' => $share_id, 'status' => -1, 'ship_fee' => 0, 'tag' => 'pys_ziti');
            }
        }
        $this->WeshareShipSetting->saveAll($saveData);
        echo json_encode(array('success' => true));
        return;
    }

    function rand_date($min_date, $max_date)
    {
        /* Gets 2 dates as string, earlier and later date.
           Returns date in between them.
        */
        $is_valid = true;
        $gen_date = '';
        while ($is_valid) {
            $rand_epoch = mt_rand($min_date, $max_date);
            $gen_date = date('Y-m-d H:i:s', $rand_epoch);
            $yh = getdate($gen_date);
            if ($yh['hours'] >= 6 && $yh['hours'] < 12) {
                $is_valid = false;
            }
        }
        return $gen_date;
    }

    public function admin_make_comment($num, $product_id, $weshare_id)
    {
        $this->autoRender = false;
        //$old_comment_distinct_comment = $this->Comment->query("select id , DISTINCT(user_id) from cake_comments where type='Product' and data_id=".$product_id." and order_id is not null limit 0,".$num);
        $old_comment_distinct_comment = $this->Comment->find('all', array(
            'conditions' => array(
                'data_id' => $product_id,
                'type' => 'Product',
                'not' => array('order_id' => null)
            ),
            'group' => 'user_id',
            'limit' => $num,
        ));
        $old_comment_ids = Hash::extract($old_comment_distinct_comment, '{n}.Comment.id');
        $old_product_comments = $this->Comment->find('all', array(
            'conditions' => array(
                'id' => $old_comment_ids
            ),
        ));
        $order_ids = Hash::extract($old_product_comments, '{n}.Comment.order_id');
        $user_ids = Hash::extract($old_product_comments, '{n}.Comment.user_id');
        $user_infos = $this->User->find('all', array(
            'conditions' => array(
                'id' => $user_ids
            )
        ));
        $order_infos = $this->Order->find('all', array(
            'conditions' => array(
                'id' => $order_ids
            )
        ));
        $user_infos = Hash::combine($user_infos, '{n}.User.id', '{n}.User');
        $order_infos = Hash::combine($order_infos, '{n}.Order.id', '{n}.Order');
        $weshare_products = $this->WeshareProduct->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id
            )
        ));
        foreach ($old_product_comments as $comment_item) {
            $comment_info = $comment_item['Comment'];
            $item_order_id = $comment_info['order_id'];
            $item_user_id = $comment_info['user_id'];
            $user_info = $user_infos[$item_user_id];
            $order_info = $order_infos[$item_order_id];
            $this->gen_order_has_comment($comment_info, $order_info, $weshare_products, $user_info, $weshare_id);
        }
        echo json_encode(array('success' => true));
    }

    public function admin_make_order($num = 1, $weshare_id)
    {
        $this->autoRender = false;
        /**
         * SELECT name FROM random AS r1 JOIN (SELECT CEIL(RAND() * (SELECT MAX(id) FROM random)) AS id) AS r2 WHERE r1.id >= r2.id ORDER BY r1.id ASC LIMIT 1
         *
         * select id, nickname, status, username from cake_users where status=9 limit 0,10
         */
        $users = $this->User->query('SELECT user.id, user.nickname, user.username FROM cake_users  AS user JOIN (SELECT CEIL(RAND() * (SELECT MAX(id) FROM cake_users)) AS id) AS r2 WHERE user.id >= r2.id and user.nickname not like "微信用户%" ORDER BY user.id ASC LIMIT ' . $num);
        $weshare = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $weshare_id
            )
        ));
        $weshare_products = $this->WeshareProduct->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id
            )
        ));
        $weshare_addresses = $this->WeshareAddress->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id
            )
        ));
        $current_date = date('Y-m-d H:i:s');
        $rand_start = strtotime($current_date . ' -3 day');
        $rand_end = strtotime($current_date);
        foreach ($users as $user) {
            $order_date = $this->rand_date($rand_start, $rand_end);
            $this->gen_order($weshare, $user, $weshare_products, $weshare_addresses, $order_date);
        }
        echo json_encode(array('success' => true));
        return;
    }

    public function admin_index()
    {
        $current_date = date('Y-m-d H:i:s');
        $weshare_count = $this->Weshare->find('count', array(
            'limit' => 5000
        ));

        $weshare_creator_count = $this->Weshare->find('count', array(
            'limit' => 5000,
            'fields' => 'DISTINCT creator'
        ));

        $join_weshare_count = $this->Order->find('count', array(
            'conditions' => array(
                'type' => 9,
                'DATE(created)' => date('Y-m-d'),
            ),
            'limit' => 15000,
            'fields' => array('DISTINCT creator')
        ));

        $order_count = $this->Order->find('count', array(
            'conditions' => array(
                'type' => 9,
                'DATE(created)' => date('Y-m-d'),
            )
        ));
        $share_pay_count = $this->Weshare->find('count', array(
            'conditions' => array(
                'status' => array(1, 2),
                'settlement' => 0
            )
        ));
        $share_paid_count = $this->Weshare->find('count', array(
            'conditions' => array(
                'status' => array(1, 2),
                'settlement' => 1
            )
        ));
        $proxy_count = $this->RebateTrackLog->find('count', array(
            'conditions' => array(
                'DATE(updated) >= ' => getMonthRange($current_date),
                'DATE(updated) <= ' => getMonthRange($current_date, false)
            ),
            'limit' => 100,
            'group' => array('sharer')
        ));
        $this->set('proxy_count', $proxy_count);
        $this->set('share_pay_count', $share_pay_count);
        $this->set('share_count', $weshare_count);
        $this->set('share_creator_count', $weshare_creator_count);
        $this->set('join_share_count', $join_weshare_count);
        $this->set('today_order_count', $order_count);
        $this->set('share_paid_count', $share_paid_count);
    }

    public function admin_all_shares()
    {
        $shares = $this->Weshare->find('all', array(
            'order' => array('created DESC'),
            'limit' => 300
        ));
        $shareIds = Hash::extract($shares, '{n}.Weshare.id');
        $products = $this->WeshareProduct->find('all', array(
            'conditions' => array(
                'weshare_id' => $shareIds
            )
        ));
        $share_product_map = array();
        foreach ($products as $item) {
            if (!isset($share_product_map[$item['WeshareProduct']['weshare_id']])) {
                $share_product_map[$item['WeshareProduct']['weshare_id']] = array();
            }
            $share_product_map[$item['WeshareProduct']['weshare_id']][] = $item['WeshareProduct'];
        }
        $this->set('shares', $shares);
        $this->set('share_product_map', $share_product_map);
    }

    private function handle_query_orders($order_query_condition)
    {
        $orders = $this->Order->find('all', $order_query_condition);
        $total_price = 0;
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $total_price += $order['Order']['total_all_price'];
            }
            $order_group_ids = array_unique(Hash::extract($orders, '{n}.Order.group_id'));
            $order_ids = Hash::extract($orders, '{n}.Order.id');
            $parent_order_ids = Hash::extract($orders, '{n}.Order.parent_order_id');
            $member_ids = Hash::extract($orders, '{n}.Order.member_id');
            $cate_ids = Hash::extract($orders, '{n}.Order.cate_id');
            $cateIds = array_unique($cate_ids);
            $rebateLogs = $this->RebateTrackLog->find('all', array(
                'conditions' => array(
                    'id' => $cateIds
                ),
                'fields' => array('id', 'sharer', 'rebate_money')
            ));
            $refundLogs = $this->RefundLog->find('all',
                array(
                    'conditions' => array(
                        'order_id' => $order_ids
                    ),
                    'fields' => array('order_id', 'id', 'refund_fee')
                ));
            $refundLogs = Hash::combine($refundLogs, '{n}.RefundLog.order_id', '{n}.RefundLog.refund_fee');
            $allRebateMoney = 0;
            foreach ($rebateLogs as $rebate_item) {
                $allRebateMoney = $allRebateMoney + $rebate_item['RebateTrackLog']['rebate_money'];
            }
            $allRebateMoney = number_format(round($allRebateMoney / 100, 2), 2);
            $rebateSharerIds = Hash::extract($rebateLogs, '{n}.RebateTrackLog.sharer');
            $rebateLogs = Hash::combine($rebateLogs, '{n}.RebateTrackLog.id', '{n}.RebateTrackLog.sharer');
            $pay_notify_order_ids = array_merge($order_ids, $parent_order_ids);
            $pay_notify_order_ids = array_unique($pay_notify_order_ids);
            $pay_notifies = $this->PayNotify->find('all', array(
                'conditions' => array(
                    'order_id' => $pay_notify_order_ids,
                    'type' => GOOD_ORDER_PAY_TYPE
                ),
            ));
            $pay_notifies = Hash::combine($pay_notifies, '{n}.PayNotify.order_id', '{n}.PayNotify.out_trade_no');
            $weshares = $this->Weshare->find('all', array(
                'conditions' => array(
                    'id' => $member_ids,
                )
            ));
            $creatorIds = [];
            $poolShareIds = [];
            foreach ($weshares as $weshare_item) {
                $creatorIds[] = $weshare_item['Weshare']['creator'];
                if ($weshare_item['Weshare']['type'] == 6) {
                    $poolShareIds[] = $weshare_item['Weshare']['refer_share_id'];
                }
            }
            if (!empty($poolShareIds)) {
                $poolShares = $this->Weshare->find('all', [
                    'conditions' => ['id' => $poolShareIds],
                ]);
                foreach ($poolShares as $poolShareItem) {
                    $creatorIds[] = $poolShareItem['Weshare']['creator'];
                    $weshares[] = $poolShareItem;
                }
            }
            $allUserIds = array_merge($creatorIds, $rebateSharerIds);
            $order_user_ids = Hash::extract($orders, '{n}.Order.creator');
            $allUserIds = array_merge($allUserIds, $order_user_ids);
            $allUserIds = array_unique($allUserIds);
            $all_users = $this->User->find('all', array(
                'conditions' => array(
                    'id' => $allUserIds
                ),
                'fields' => array('id', 'nickname', 'mobilephone')
            ));
            $all_users = Hash::combine($all_users, '{n}.User.id', '{n}.User');
            $weshares = Hash::combine($weshares, '{n}.Weshare.id', '{n}.Weshare');
            $carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $order_ids
                )
            ));
            $order_cart_map = array();
            foreach ($carts as $item) {
                $order_id = $item['Cart']['order_id'];
                if (!isset($order_cart_map[$order_id])) {
                    $order_cart_map[$order_id] = array();
                }
                $order_cart_map[$order_id][] = $item['Cart'];
            }
            $summery_result = array('order_count' => count($orders), 'total_all_price' => $total_price);
            $pintuan_tags = $this->PintuanTag->find('all', array(
                'conditions' => array(
                    'id' => $order_group_ids
                )
            ));
            $pintuan_tags = Hash::combine($pintuan_tags, '{n}.PintuanTag.id', '{n}.PintuanTag');
            $this->set('pintuan_tags', $pintuan_tags);
            $this->set('summery', $summery_result);
            $this->set('refund_logs', $refundLogs);
            $this->set('orders', $orders);
            $this->set('weshares', $weshares);
            $this->set('pay_notifies', $pay_notifies);
            $this->set('all_users', $all_users);
            $this->set('order_cart_map', $order_cart_map);
            $this->set('all_rebate_money', $allRebateMoney);
            $this->set('rebate_logs', $rebateLogs);
        }
    }

    private function handle_query_orders_by_sql($sql)
    {
        $orders = $this->Order->query($sql);
        $total_price = 0;
        if (!empty($orders)) {
            $order_ids = [];
            $parent_order_ids = [];
            $member_ids = [];
            $cate_ids = [];
            $allUserIds = [];
            $allPoolShareIds = [];
            foreach ($orders as $order) {
                $total_price += $order['Order']['total_all_price'];
                $order_ids[] = $order['o']['id'];
                $parent_order_ids[] = $order['o']['parent_order_id'];
                $member_ids[] = $order['o']['member_id'];
                $cate_ids[] = $order['o']['cate_id'];
                if (!in_array($order['o']['creator'], $allUserIds)) {
                    $allUserIds[] = $order['o']['creator'];
                }
                if (!in_array($order['s']['creator'], $allUserIds)) {
                    $allUserIds[] = $order['s']['creator'];
                }
                if ($order['s']['type'] == 6) {
                    $allPoolShareIds[] = $order['s']['refer_share_id'];
                }
            }

            if (!empty($allPoolShareIds)) {
                $pool_shares = $this->Weshare->find('all', [
                    'conditions' => ['id' => $allPoolShareIds],
                    'fields' => ['id', 'creator', 'title']
                ]);
                $map_pool_shares = [];
                foreach ($pool_shares as $pool_share_item) {
                    $map_pool_shares[$pool_share_item['Weshare']['id']] = $pool_share_item['Weshare'];
                    $allUserIds[] = $pool_share_item['Weshare']['creator'];
                }
                $this->set('pool_shares', $map_pool_shares);
            }

            $cateIds = array_unique($cate_ids);
            $rebateLogs = $this->RebateTrackLog->find('all', array(
                'conditions' => array(
                    'id' => $cateIds
                ),
                'fields' => array('id', 'sharer', 'rebate_money')
            ));
            $refundLogs = $this->RefundLog->find('all',
                array(
                    'conditions' => array(
                        'order_id' => $order_ids
                    ),
                    'fields' => array('order_id', 'id', 'refund_fee')
                ));
            $refundLogs = Hash::combine($refundLogs, '{n}.RefundLog.order_id', '{n}.RefundLog.refund_fee');
            $rebateLogs = Hash::combine($rebateLogs, '{n}.RebateTrackLog.id', '{n}.RebateTrackLog.sharer');
            $pay_notify_order_ids = array_merge($order_ids, $parent_order_ids);
            $pay_notify_order_ids = array_unique($pay_notify_order_ids);
            $pay_notifies = $this->PayNotify->find('all', array(
                'conditions' => array(
                    'order_id' => $pay_notify_order_ids,
                    'type' => GOOD_ORDER_PAY_TYPE
                ),
            ));

            $pay_notifies = Hash::combine($pay_notifies, '{n}.PayNotify.order_id', '{n}.PayNotify.out_trade_no');

            $all_users = $this->User->find('all', array(
                'conditions' => array(
                    'id' => $allUserIds
                ),
                'fields' => array('id', 'nickname', 'mobilephone')
            ));

            $all_users = Hash::combine($all_users, '{n}.User.id', '{n}.User');
            $carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $order_ids
                )
            ));

            $order_cart_map = array();
            foreach ($carts as $item) {
                $order_id = $item['Cart']['order_id'];
                if (!isset($order_cart_map[$order_id])) {
                    $order_cart_map[$order_id] = array();
                }
                $order_cart_map[$order_id][] = $item['Cart'];
            }
            $summery_result = array('order_count' => count($orders), 'total_all_price' => $total_price);

            $this->set('summery', $summery_result);
            $this->set('refund_logs', $refundLogs);
            $this->set('orders', $orders);
            $this->set('pay_notifies', $pay_notifies);
            $this->set('all_users', $all_users);
            $this->set('order_cart_map', $order_cart_map);
            $this->set('rebate_logs', $rebateLogs);
        }
    }

    public function admin_batch_update_orders()
    {
        $this->autoRender = false;
        $orders = $_REQUEST['orders'];

        foreach ($orders as $id) {
            $order = [];
            $order['id'] = $id;
            $order['status'] = 2;
            $this->Order->save($order);
        }

        echo json_encode([
            'result' => true,
        ]);
    }

    public function admin_warn_orders()
    {
        if (!$_REQUEST['start_date']) {
            $start_date = date('Y-m-d', strtotime('-15 day'));
        } else {
            $start_date = $_REQUEST['start_date'];
        }
        if (!$_REQUEST['end_date']) {
            $end_date = date('Y-m-d');
        } else {
            $end_date = $_REQUEST['end_date'];
        }

        $con1 = '';
        if ($_REQUEST['share_name']) {
            $con1 .= " AND s.title LIKE '%{$_REQUEST['share_name']}%'";
        }

        if (($_REQUEST['share_type'] === '0') or ($_REQUEST['share_type'] === '6')) {
            $con1 .= " AND s.type = {$_REQUEST['share_type']}";
        }

        if (($_REQUEST['share_status'] === '0') or ($_REQUEST['share_status'] === '1')) {
            $con1 .= " AND s.status = {$_REQUEST['share_status']}";
        }

        $page = intval($_REQUEST['page']) > 0 ? intval($_REQUEST['page']) : 1;
        $flow = ($page - 1) * 10;

        $countSql = "SELECT count(1) FROM cake_orders o LEFT JOIN cake_weshares s ON o.member_id = s.id WHERE (o.created BETWEEN '{$start_date} 00:00:00' AND '{$end_date} 23:59:59') AND o.status = " . ORDER_STATUS_PAID . $con1;

        $count = $this->Order->query($countSql);

        $sql = "SELECT * FROM cake_orders o LEFT JOIN cake_weshares s ON o.member_id = s.id WHERE (o.created BETWEEN '{$start_date} 00:00:00' AND '{$end_date} 23:59:59') AND o.status = " . ORDER_STATUS_PAID . "{$con1} ORDER BY o.created DESC LIMIT {$flow} , 10";
        $this->handle_query_orders_by_sql($sql);

        require_once(APPLIBS . 'MyPaginator.php');

        $url = "/manage/admin/share/warn_orders?share_name={$_REQUEST['share_name']}&start_date={$_REQUEST['start_date']}&end_date={$_REQUEST['end_date']}&share_status={$_REQUEST['share_status']}&share_type={$_REQUEST['share_type']}&page=(:num)";
        $pager = new MyPaginator($count[0][0]['count(1)'], 10, $page, $url);;

        $this->set('pager', $pager);
        $this->set('count', $count[0][0]['count']);
        $this->set('share_type', $_REQUEST['share_type']);
        $this->set('share_status', $_REQUEST['share_status']);
        $this->set('start_date', $start_date);
        $this->set('end_date', $end_date);
        $this->set('share_name', $_REQUEST['share_name']);
    }

    public function admin_share_orders_export()
    {
        $share_id = $_REQUEST['share_id'];
        if (!empty($share_id)) {
            $conditions = array('Order.member_id' => $share_id, 'Order.type' => ORDER_TYPE_WESHARE_BUY, 'Order.status' => ORDER_STATUS_PAID);
            $this->_query_orders($conditions, 'Order.created DESC');
            $this->set('share_id', $share_id);
        }
    }

    public function _query_orders($conditions, $order_by, $limit = null)
    {
        $this->PayNotify->query("update cake_pay_notifies set order_id =  substring_index(substring_index(out_trade_no,'-',2),'-',-1) where status = 6 and order_id is NULL and type=0");
        $join_conditions = array(
            array(
                'table' => 'pay_notifies',
                'alias' => 'Pay',
                'conditions' => array(
                    'Pay.order_id = Order.id'
                ),
                'type' => 'LEFT',
            ),
            array(
                'table' => 'carts',
                'alias' => 'Cart',
                'conditions' => array(
                    'Cart.order_id = Order.id'
                ),
                'type' => 'INNER'
            )
        );
        $all_orders = array();
        if (!empty($conditions)) {
            $params = array(
                'conditions' => $conditions,
                'joins' => $join_conditions,
                'fields' => array('Order.*', 'Pay.trade_type', 'Pay.out_trade_no', 'Cart.id', 'Cart.product_id', 'Cart.send_date'),
                'order' => $order_by
            );
            if (!empty($limit)) {
                $params['limit'] = $limit;
            }
            $this->log('query order conditions: ' . json_encode($params));
            $all_orders = $this->Order->find('all', $params);
        } else {
            $this->log('order condition is empty: ' . json_encode($conditions));
        }
        $order_ids = array_unique(Hash::extract($all_orders, "{n}.Order.id"));
        $cart_ids = array_unique(Hash::extract($all_orders, "{n}.Cart.id"));
        $orders = array();
        foreach ($all_orders as $order) {
            if (!isset($orders[$order['Order']['id']])) {
                $orders[$order['Order']['id']] = $order;
            }
        }
        $carts = array();
        if (!empty($cart_ids)) {
            $carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $order_ids
                ),
            ));
        }
        $p_ids = Hash::extract($carts, '{n}.Cart.product_id');
        $products = array();
        if (!empty($p_ids)) {
            $products = $this->WeshareProduct->find('all', array(
                'conditions' => array(
                    'id' => $p_ids
                )
            ));
            $products = Hash::combine($products, '{n}.WeshareProduct.id', '{n}');
        }
        $order_carts = array();
        $product_detail = array();
        foreach ($carts as &$cart) {
            $order_id = $cart['Cart']['order_id'];
            $cart['Cart']['matched'] = in_array($cart['Cart']['id'], $cart_ids);
            if (!isset($order_carts[$order_id])) {
                $order_carts[$order_id] = array();
            }
            if ($cart['Cart']['matched']) {
                array_unshift($order_carts[$order_id], $cart);
                if (isset($product_detail[$cart['Cart']['product_id']])) {
                    $product_detail[$cart['Cart']['product_id']] += $cart['Cart']['num'];
                } else {
                    $product_detail[$cart['Cart']['product_id']] = $cart['Cart']['num'];
                }
            } else {
                $order_carts[$order_id][] = $cart;
            }
        }
        // product count
        $product_count = 0;
        if (!empty($order_ids)) {
            $order_id_strs = '(' . join(',', $order_ids) . ')';
            $result = $this->Cart->query('select sum(num) from cake_carts where order_id in ' . $order_id_strs);
            $product_count = $result[0][0]['sum(num)'];
        }
        //total_money
        $total_money = 0;
        if (!empty($orders)) {
            foreach ($orders as $o) {
                $o_status = $o['Order']['status'];
                if ($o_status == 1 || $o_status == 2 || $o_status == 3) {
                    $total_money = $total_money + $o['Order']['total_all_price'];
                }
            }
            $this->set('total_order_money', $total_money);
        }

        $ship_mark_enum = array(SHARE_SHIP_KUAIDI_TAG => array('name' => '快递', 'style' => 'primary'), SHARE_SHIP_PYS_ZITI_TAG => array('name' => '好邻居自提', 'style' => 'warning'), SHARE_SHIP_SELF_ZITI_TAG => array('name' => '自提', 'style' => 'danger'), 'none' => array('name' => '没有标注', 'style' => 'info'));
        $this->set('ship_mark_enum', $ship_mark_enum);
        $ziti_orders = array_filter($orders, 'share_self_ziti_order_filter');
        $pys_ziti_orders = array_filter($orders, 'share_pys_ziti_order_filter');
        $kuaidi_orders = array_filter($orders, 'share_kuai_di_order_filter');
        $none_orders = array_filter($orders, 'share_none_order_filter');
        $map_other_orders = array(SHARE_SHIP_SELF_ZITI_TAG => $ziti_orders, SHARE_SHIP_KUAIDI_TAG => $kuaidi_orders, 'none' => $none_orders, SHARE_SHIP_PYS_ZITI_TAG => $pys_ziti_orders);
        $map_self_ziti_orders = array();
        foreach ($ziti_orders as $item) {
            $consignee_id = $item['Order']['consignee_id'];
            if ($consignee_id == null) {
                $consignee_id = 0;
            }
            if (!array_key_exists($consignee_id, $map_self_ziti_orders)) {
                $map_self_ziti_orders[$consignee_id] = array();
            }
            $map_self_ziti_orders[$consignee_id][] = $item;
        }
        $map_ziti_orders = array();
        foreach ($pys_ziti_orders as $item) {
            $consignee_id = $item['Order']['consignee_id'];
            if ($consignee_id == null) {
                $consignee_id = 0;
            }
            if (!array_key_exists($consignee_id, $map_ziti_orders)) {
                $map_ziti_orders[$consignee_id] = array();
            }
            $map_ziti_orders[$consignee_id][] = $item;
        }

        $offline_stores = array();
        $offline_store_ids = array_filter(array_unique(Hash::extract($pys_ziti_orders, "{n}.Order.consignee_id")));
        if (!empty($offline_store_ids)) {
            $offline_stores = $this->OfflineStore->find('all', array(
                'conditions' => array(
                    'id' => $offline_store_ids
                )
            ));
            $offline_stores = Hash::combine($offline_stores, "{n}.OfflineStore.id", "{n}");
        }

        $weshare_addresses = array();
        $weshare_address_ids = array_filter(array_unique(Hash::extract($ziti_orders, "{n}.Order.consignee_id")));
        if (!empty($weshare_address_ids)) {
            $weshare_addresses = $this->WeshareAddress->find('all', array(
                'conditions' => array(
                    'id' => $weshare_address_ids
                )
            ));
            $weshare_addresses = Hash::combine($weshare_addresses, '{n}.WeshareAdresss.id', '{n}');
        }
        $pys_ziti_point = array_filter($offline_stores, 'pys_ziti_filter');
        $hlj_ziti_point = array_filter($offline_stores, 'hlj_ziti_filter');

        $this->set('pys_ziti_point', $pys_ziti_point);
        $this->set('hlj_ziti_point', $hlj_ziti_point);
        $this->set('weshare_addresses', $weshare_addresses);

        $this->set('map_ziti_orders', $map_ziti_orders);
        $this->set('map_other_orders', $map_other_orders);
        $this->set('map_self_ziti_orders', $map_self_ziti_orders);

        $this->set('product_count', $product_count);
        $this->set('orders', $orders);
        $this->set('offline_stores', $offline_stores);
        $this->set('order_carts', $order_carts);
        $this->set('product_detail', $product_detail);
        $this->set('products', $products);
    }


    public function admin_share_orders()
    {
        $query_date = date('Y-m-d');
        $start_date = $query_date;
        $end_date = $query_date;
        if ($_REQUEST['start_date']) {
            $start_date = $_REQUEST['start_date'];
        }
        if ($_REQUEST['end_date']) {
            $end_date = $_REQUEST['end_date'];
        }
        $cond = array();
        if ($_REQUEST['order_type'] == 0) {
            $cond['type'] = array(9, 12);
        } else {
            $cond['type'] = $_REQUEST['order_type'];
        }
        $request_order_id = $_REQUEST['order_id'];
        if ($_REQUEST['share_id']) {
            $query_share_id = $_REQUEST['share_id'];
        }
        if ($_REQUEST['mobile_no']) {
            $query_mobile_num = $_REQUEST['mobile_no'];
        }
        if ($request_order_id) {
            $cond['id'] = $request_order_id;
        } elseif ($query_share_id) {
            $cond['member_id'] = $query_share_id;
        } elseif ($query_mobile_num) {
            $cond['consignee_mobilephone'] = $query_mobile_num;
        } else {
            if ($start_date == $end_date) {
                $cond['DATE(created)'] = $query_date;
            } else {
                $cond['DATE(created) >='] = $start_date;
                $cond['DATE(created) <='] = $end_date;
            }
        }
        $order_status = $_REQUEST['order_status'];
        if ($order_status != 0) {
            $cond['status'] = array($order_status);
        } else {
            $cond['status'] = array(ORDER_STATUS_PAID, ORDER_STATUS_RECEIVED, ORDER_STATUS_SHIPPED, ORDER_STATUS_DONE, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_PREPAID, ORDER_STATUS_PREPAID_TODO, ORDER_STATUS_REFUND);
        }
        $order_flag = $_REQUEST['order_from_flag'];
        if ($order_flag != -1) {
            $cond['flag'] = $order_flag;
        }
        $order_repaid_status = $_REQUEST['order_prepaid_status'];
        if ($order_repaid_status != 0) {
            $cond['process_prepaid_status'] = array($order_repaid_status);
        } else {
            $cond['process_prepaid_status'] = array(0, ORDER_STATUS_PREPAID, ORDER_STATUS_PREPAID_TODO, ORDER_STATUS_PREPAID_DONE, ORDER_STATUS_REFUND_TODO, ORDER_STATUS_REFUND_DONE);
        }
        $order_query_condition = array(
            'conditions' => $cond,
            'order' => array('created DESC'));
        if ($query_mobile_num) {
            $order_query_condition['limit'] = 200;
        }
        $this->handle_query_orders($order_query_condition);
        $this->set('start_date', $_REQUEST['start_date']);
        $this->set('end_date', $_REQUEST['end_date']);
        $this->set('order_prepaid_status', $order_repaid_status);
        $this->set('share_id', $query_share_id);
        $this->set('order_status', $order_status);
        $this->set('order_id', $request_order_id);
        $this->set('order_type', $_REQUEST['order_type']);
        $this->set('order_from_flag', $order_flag);
    }

    private function get_random_item($items)
    {
        return $items[array_rand($items)];
    }

    private function gen_order_has_comment($comment_info, $order_info, $weshare_products, $user_info, $weshare_id)
    {
        $this->Order->id = null;
        $order_info['id'] = null;
        $order_info['member_id'] = $weshare_id;
        $order_info['creator'] = $user_info['id'];
        $order_info['type'] = ORDER_TYPE_WESHARE_BUY;
        $order_info['status'] = ORDER_STATUS_DONE;
        $order = $this->Order->save($order_info);
        $weshareProducts[] = $this->get_random_item($weshare_products);
        $order_date = $order['Order']['created'];
        $creator = $order['Order']['creator'];
        $weshare_id = $order['Order']['member_id'];
        $orderId = $order['Order']['id'];
        if (!empty($orderId)) {
            $totalPrice = 0;
            foreach ($weshareProducts as $p) {
                $item = array();
                $num = 1;
                $price = $p['WeshareProduct']['price'];
                $item['name'] = $p['WeshareProduct']['name'];
                $item['num'] = $num;
                $item['price'] = $price;
                $item['type'] = ORDER_TYPE_WESHARE_BUY;
                $item['product_id'] = $p['WeshareProduct']['id'];
                $item['created'] = $order_date;
                $item['updated'] = $order_date;
                $item['creator'] = $creator;
                $item['order_id'] = $orderId;
                $item['tuan_buy_id'] = $weshare_id;
                $cart[] = $item;
                $totalPrice += $num * $price;
            }
            $this->Cart->id = null;
            $this->Cart->saveAll($cart);
            $this->Order->updateAll(array('total_all_price' => $totalPrice / 100, 'total_price' => $totalPrice / 100, 'ship_fee' => 0, 'status' => ORDER_STATUS_DONE), array('id' => $orderId));
            $comment_info['user_id'] = $creator;
            $comment_info['username'] = $user_info['nickname'];
            $comment_info['data_id'] = $weshare_id;
            $comment_info['type'] = 'Share';
            $comment_info['order_id'] = $orderId;
            $comment_info['id'] = null;
            $this->Comment->id = null;
            $this->Comment->save($comment_info);
            return array('success' => true, 'orderId' => $orderId);
        }
    }

    private function gen_order($weshare, $user, $weshare_products, $weshare_address, $order_date, $address = null)
    {
        $weshareProducts = array();
        $weshareProducts[] = $this->get_random_item($weshare_products);
        $tinyAddress = $this->get_random_item($weshare_address);
        $cart = array();
        try {
            $mobile_phone = $this->randMobile(1);
            $addressId = 0;
            if ($address) {
                $order_consignee_address = $address;
            } else {
                $order_consignee_address = '虚拟订单';
            }
            if (!empty($tinyAddress)) {
                $addressId = $tinyAddress['WeshareAddress']['id'];
                $order_consignee_address = $tinyAddress['WeshareAddress']['address'];
            }
            $weshare_id = $weshare['Weshare']['id'];
            $user = $user['user'];
            $user_name = $user['nickname'];
            $this->Order->id = null;
            $order = $this->Order->save(array('creator' => $user['id'], 'consignee_address' => $order_consignee_address, 'member_id' => $weshare['Weshare']['id'], 'type' => ORDER_TYPE_WESHARE_BUY, 'created' => $order_date, 'updated' => $order_date, 'consignee_id' => $addressId, 'consignee_name' => $user_name, 'consignee_mobilephone' => $mobile_phone[0]));
            $orderId = $order['Order']['id'];
            if (!empty($orderId)) {
                $totalPrice = 0;
                foreach ($weshareProducts as $p) {
                    $item = array();
                    $num = 1;
                    $price = $p['WeshareProduct']['price'];
                    $item['name'] = $p['WeshareProduct']['name'];
                    $item['num'] = $num;
                    $item['price'] = $price;
                    $item['type'] = ORDER_TYPE_WESHARE_BUY;
                    $item['product_id'] = $p['WeshareProduct']['id'];
                    $item['created'] = $order_date;
                    $item['updated'] = $order_date;
                    $item['creator'] = $user['id'];
                    $item['order_id'] = $orderId;
                    $item['tuan_buy_id'] = $weshare_id;
                    $cart[] = $item;
                    $totalPrice += $num * $price;
                }
                $this->Cart->id = null;
                $this->Cart->saveAll($cart);
                $this->Order->updateAll(array('total_all_price' => $totalPrice / 100, 'total_price' => $totalPrice / 100, 'ship_fee' => 0, 'status' => ORDER_STATUS_VIRTUAL), array('id' => $orderId));
                //echo json_encode(array('success' => true, 'orderId' => $orderId));
                return array('success' => true, 'orderId' => $orderId);
            }
            return array('success' => false, 'msg' => 'order empty');
        } catch (Exception $e) {
            $this->log($user['id'] . 'buy share ' . $weshare_id . $e);
            //echo json_encode(array('success' => false, 'msg' => $e->getMessage()));
            return array('success' => false, 'msg' => $e->getMessage());
        }
    }

    private function findCarts($orderId)
    {
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $orderId
            ),
            'fields' => array('id', 'order_id', 'name', 'product_id', 'num')
        ));
        return $carts;
    }

    private function get_cart_name_and_num($orderId)
    {
        $carts = $this->findCarts($orderId);
        $num = 0;
        $cart_name = array();
        foreach ($carts as $cart_item) {
            $num += $cart_item['Cart']['num'];
            $cart_name[] = $cart_item['Cart']['name'] . 'X' . $cart_item['Cart']['num'];
        }
        return array('num' => $num, 'cart_name' => implode(',', $cart_name));
    }

    private function get_offline_store($offlineStoreId)
    {
        $offlineStore = $this->OfflineStore->find('first', array(
            'conditions' => array(
                'id' => $offlineStoreId
            )
        ));
        return $offlineStore;
    }

    function get_share_repaid_money($share_ids)
    {
        $orderM = ClassRegistry::init('Order');
        $addOrderResult = $orderM->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY_ADD,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_REFUND_DONE),
                'member_id' => $share_ids
            ),
            'fields' => array('total_all_price', 'id', 'member_id'),
            'group' => array('member_id')
        ));
        $repaid_money_result = array();
        foreach ($addOrderResult as $item) {
            $member_id = $item['Order']['member_id'];
            if (!isset($repaid_money_result[$member_id])) {
                $repaid_money_result[$member_id] = 0;
            }
            $repaid_money_result[$member_id] = $repaid_money_result[$member_id] + $item['Order']['total_all_price'];
        }
        return $repaid_money_result;
    }

    function get_share_rebate_money($share_ids)
    {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $rebateLogs = $rebateTrackLogM->find('all', array(
            'conditions' => array(
                'share_id' => $share_ids,
                'not' => array('order_id' => 0, 'is_paid' => 0)
            ),
            'limit' => 500
        ));
        $share_rebate_map = array();
        foreach ($rebateLogs as $log) {
            $share_id = $log['RebateTrackLog']['share_id'];
            if (!isset($share_rebate_map[$share_id])) {
                $share_rebate_map[$share_id] = array('rebate_money' => 0);
            }
            $share_rebate_map[$share_id]['rebate_money'] = $log['RebateTrackLog']['rebate_money'];
        }
        foreach ($share_rebate_map as &$rebate_item) {
            $rebate_item['rebate_money'] = number_format(round($rebate_item['rebate_money'] / 100, 2), 2);
        }
        return $share_rebate_map;
    }


    public function admin_balance_log_form($id = null)
    {
        $this->loadModel('BalanceLog');
        if (!empty($id)) {
            $cond = [];
            $cond['BalanceLog.id'] = $id;
        } else {
            $share_id = $_REQUEST['share_id'];
            if (!empty($share_id)) {
                $cond['BalanceLog.share_id'] = $share_id;
            }
        }
        if (!empty($cond)) {
            $data = $this->BalanceLog->find('first', [
                'conditions' => $cond,
                'joins' => [
                    [
                        'type' => 'left',
                        'table' => 'cake_weshares',
                        'alias' => 'Weshare',
                        'conditions' => 'Weshare.id=BalanceLog.share_id'
                    ]
                ],
                'fields' => ['BalanceLog.*', 'Weshare.title', 'Weshare.type']
            ]);
        }
        $user_id = $_REQUEST['user_id'];
        $total_fee = $_REQUEST['total_fee'];
        if ($share_id && $data) {
            $data['BalanceLog']['share_id'] = $share_id;
        }
        if ($user_id && $data) {
            $data['BalanceLog']['user_id'] = $user_id;
        }
        if ($total_fee && $data) {
            $data['BalanceLog']['total_fee'] = $total_fee;
        }
        $this->set('data', $data);
    }

    public function admin_save_balance_log()
    {
        $this->loadModel('BalanceLog');
        $balanceLog = $this->request->data;
        $balanceLog['BalanceLog']['updated'] = date('Y-m-d H:i:s');
        if (!$balanceLog['BalanceLog']['id']) {
            $balanceLog['BalanceLog']['created'] = date('Y-m-d H:i:s');
        }
        $log = $this->BalanceLog->save($balanceLog);
        $share_id = $log['BalanceLog']['share_id'];
        $fee = $log['BalanceLog']['trade_fee'];
        if ($log['BalanceLog']['status'] == 2) {
            $this->set_share_paid($share_id, $fee);
        }
        $this->redirect('/admin/share/balance_logs.html');
    }

    public function admin_balance_logs()
    {
        require_once(APPLIBS . 'MyPaginator.php');
        $cond = [];
        if ($_REQUEST['shareId']) {
            $cond['BalanceLog.share_id'] = $_REQUEST['shareId'];
        }
        if ($_REQUEST['shareName']) {
            $cond['Weshare.title like '] = '%' . $_REQUEST['shareName'] . '%';
        }
        $filter_type = $_REQUEST['shareType'];
        if ($filter_type == 1) {
            $cond['Weshare.type'] = SHARE_TYPE_DEFAULT;
        } elseif ($filter_type == 2) {
            $cond['Weshare.type'] = SHARE_TYPE_POOL;
        } else {
            $cond['Weshare.type'] = [SHARE_TYPE_POOL, SHARE_TYPE_DEFAULT, SHARE_TYPE_POOL_SELF];
        }
        if ($_REQUEST['beginDate']) {
            $cond['Weshare.close_date > '] = $_REQUEST['beginDate'];
        }
        if ($_REQUEST['endDate']) {
            $cond['Weshare.close_date < '] = $_REQUEST['endDate'];
        }
        $filter_status = empty($_REQUEST['balanceStatus']) ? 1 : $_REQUEST['balanceStatus'];
        if($filter_status != '-1'){
            $cond['BalanceLog.status'] = $filter_status;
        }
        $filter_balance_type = $_REQUEST['balanceType'];
        if ($filter_balance_type != '-1') {
            $cond['BalanceLog.type'] = $filter_balance_type;
        }
        $joins = [
            [
                'type' => 'left',
                'table' => 'cake_weshares',
                'alias' => 'Weshare',
                'conditions' => ['Weshare.id = BalanceLog.share_id']
            ],
            [
                'type' => 'left',
                'table' => 'cake_users',
                'alias' => 'User',
                'conditions' => ['User.id = BalanceLog.user_id']
            ]
        ];
        $this->loadModel('BalanceLog');
        $count = $this->BalanceLog->find('count', [
            'conditions' => $cond,
            'joins' => $joins
        ]);
        $page = intval($_REQUEST['page']) > 0 ? intval($_REQUEST['page']) : 1;
        $logs = $this->BalanceLog->find('all', [
            'conditions' => $cond,
            'page' => $page,
            'limit' => 50,
            'joins' => $joins,
            'fields' => ['BalanceLog.*', 'User.nickname', 'User.payment', 'Weshare.title']
        ]);
        $url = "/manage/admin/share/balance_logs?page=(:num)";
        $pager = new MyPaginator($count, 50, $page, $url);
        $this->set('pager', $pager);
        $this->set('logs', $logs);
        $this->set('shareId', $_REQUEST['shareId']);
        $this->set('shareType', $filter_type);
        $this->set('shareName', $_REQUEST['shareName']);
        $this->set('beginDate', $_REQUEST['beginDate']);
        $this->set('endDate', $_REQUEST['endDate']);
        $this->set('balanceType', $filter_balance_type);
        $this->set('balanceStatus', $filter_status);
    }


    /**
     * @desc 生成n个随机手机号
     * @param int $num 生成的手机号数
     * @author niujiazhu
     * @return array
     */
    function randMobile($num = 1)
    {
        //手机号2-3为数组
        $numberPlace = array(30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 50, 51, 58, 59, 89);
        for ($i = 0; $i < $num; $i++) {
            $mobile = 1;
            $mobile .= $numberPlace[rand(0, count($numberPlace) - 1)];
            $mobile .= str_pad(rand(0, 99999999), 8, 0, STR_PAD_LEFT);
            $result[] = $mobile;
        }
        return $result;
    }

    public function getName($type = 0)
    {
        switch ($type) {
            case 1: //2字
                $name = $this->getXing() . $this->getMing();
                break;
            case 2: //随机2、3个字
                $name = $this->getXing() . $this->getMing();
                if (mt_rand(0, 100) > 50) $name .= $this->getMing();
                break;
            case 3: //只取姓
                $name = $this->getXing();
                break;
            case 4: //只取名
                $name = $this->getMing();
                break;
            case 0:
            default: //默认情况 1姓+2名
                $name = $this->getXing() . $this->getMing() . $this->getMing();
        }
        return $name;
    }

    private function getXing()
    {
        $arrXing = $this->getXingList();
        return $arrXing[mt_rand(0, count($arrXing))];
    }

    private function getMing()
    {
        $arrMing = $this->getMingList();
        return $arrMing[mt_rand(0, count($arrMing))];
    }

    private function getXingList()
    {
        $arrXing = array('赵', '钱', '孙', '李', '周', '吴', '郑', '王', '冯', '陈', '褚', '卫', '蒋', '沈', '韩', '杨', '朱', '秦', '尤', '许', '何', '吕', '施', '张', '孔', '曹', '严', '华', '金', '魏', '陶', '姜', '戚', '谢', '邹', '喻', '柏', '水', '窦', '章', '云', '苏', '潘', '葛', '奚', '范', '彭', '郎', '鲁', '韦', '昌', '马', '苗', '凤', '花', '方', '任', '袁', '柳', '鲍', '史', '唐', '费', '薛', '雷', '贺', '倪', '汤', '滕', '殷', '罗', '毕', '郝', '安', '常', '傅', '卞', '齐', '元', '顾', '孟', '平', '黄', '穆', '萧', '尹', '姚', '邵', '湛', '汪', '祁', '毛', '狄', '米', '伏', '成', '戴', '谈', '宋', '茅', '庞', '熊', '纪', '舒', '屈', '项', '祝', '董', '梁', '杜', '阮', '蓝', '闵', '季', '贾', '路', '娄', '江', '童', '颜', '郭', '梅', '盛', '林', '钟', '徐', '邱', '骆', '高', '夏', '蔡', '田', '樊', '胡', '凌', '霍', '虞', '万', '支', '柯', '管', '卢', '莫', '柯', '房', '裘', '缪', '解', '应', '宗', '丁', '宣', '邓', '单', '杭', '洪', '包', '诸', '左', '石', '崔', '吉', '龚', '程', '嵇', '邢', '裴', '陆', '荣', '翁', '荀', '于', '惠', '甄', '曲', '封', '储', '仲', '伊', '宁', '仇', '甘', '武', '符', '刘', '景', '詹', '龙', '叶', '幸', '司', '黎', '溥', '印', '怀', '蒲', '邰', '从', '索', '赖', '卓', '屠', '池', '乔', '胥', '闻', '莘', '党', '翟', '谭', '贡', '劳', '逄', '姬', '申', '扶', '堵', '冉', '宰', '雍', '桑', '寿', '通', '燕', '浦', '尚', '农', '温', '别', '庄', '晏', '柴', '瞿', '阎', '连', '习', '容', '向', '古', '易', '廖', '庾', '终', '步', '都', '耿', '满', '弘', '匡', '国', '文', '寇', '广', '禄', '阙', '东', '欧', '利', '师', '巩', '聂', '关', '荆', '司马', '上官', '欧阳', '夏侯', '诸葛', '闻人', '东方', '赫连', '皇甫', '尉迟', '公羊', '澹台', '公冶', '宗政', '濮阳', '淳于', '单于', '太叔', '申屠', '公孙', '仲孙', '轩辕', '令狐', '徐离', '宇文', '长孙', '慕容', '司徒', '司空');
        return $arrXing;
    }

    /*
      获取名列表
    */
    private function getMingList()
    {
        $arrMing = array('伟', '刚', '勇', '毅', '俊', '峰', '强', '军', '平', '保', '东', '文', '辉', '力', '明', '永', '健', '世', '广', '志', '义', '兴', '良', '海', '山', '仁', '波', '宁', '贵', '福', '生', '龙', '元', '全', '国', '胜', '学', '祥', '才', '发', '武', '新', '利', '清', '飞', '彬', '富', '顺', '信', '子', '杰', '涛', '昌', '成', '康', '星', '光', '天', '达', '安', '岩', '中', '茂', '进', '林', '有', '坚', '和', '彪', '博', '诚', '先', '敬', '震', '振', '壮', '会', '思', '群', '豪', '心', '邦', '承', '乐', '绍', '功', '松', '善', '厚', '庆', '磊', '民', '友', '裕', '河', '哲', '江', '超', '浩', '亮', '政', '谦', '亨', '奇', '固', '之', '轮', '翰', '朗', '伯', '宏', '言', '若', '鸣', '朋', '斌', '梁', '栋', '维', '启', '克', '伦', '翔', '旭', '鹏', '泽', '晨', '辰', '士', '以', '建', '家', '致', '树', '炎', '德', '行', '时', '泰', '盛', '雄', '琛', '钧', '冠', '策', '腾', '楠', '榕', '风', '航', '弘', '秀', '娟', '英', '华', '慧', '巧', '美', '娜', '静', '淑', '惠', '珠', '翠', '雅', '芝', '玉', '萍', '红', '娥', '玲', '芬', '芳', '燕', '彩', '春', '菊', '兰', '凤', '洁', '梅', '琳', '素', '云', '莲', '真', '环', '雪', '荣', '爱', '妹', '霞', '香', '月', '莺', '媛', '艳', '瑞', '凡', '佳', '嘉', '琼', '勤', '珍', '贞', '莉', '桂', '娣', '叶', '璧', '璐', '娅', '琦', '晶', '妍', '茜', '秋', '珊', '莎', '锦', '黛', '青', '倩', '婷', '姣', '婉', '娴', '瑾', '颖', '露', '瑶', '怡', '婵', '雁', '蓓', '纨', '仪', '荷', '丹', '蓉', '眉', '君', '琴', '蕊', '薇', '菁', '梦', '岚', '苑', '婕', '馨', '瑗', '琰', '韵', '融', '园', '艺', '咏', '卿', '聪', '澜', '纯', '毓', '悦', '昭', '冰', '爽', '琬', '茗', '羽', '希', '欣', '飘', '育', '滢', '馥', '筠', '柔', '竹', '霭', '凝', '晓', '欢', '霄', '枫', '芸', '菲', '寒', '伊', '亚', '宜', '可', '姬', '舒', '影', '荔', '枝', '丽', '阳', '妮', '宝', '贝', '初', '程', '梵', '罡', '恒', '鸿', '桦', '骅', '剑', '娇', '纪', '宽', '苛', '灵', '玛', '媚', '琪', '晴', '容', '睿', '烁', '堂', '唯', '威', '韦', '雯', '苇', '萱', '阅', '彦', '宇', '雨', '洋', '忠', '宗', '曼', '紫', '逸', '贤', '蝶', '菡', '绿', '蓝', '儿', '翠', '烟');
        return $arrMing;
    }

}
