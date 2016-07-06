<?php

class SharerApiController extends Controller
{

    public $components = array('OAuth.OAuth', 'Session', 'WeshareBuy', 'ShareUtil', 'Weshares');
    public $uses = array('Weshare', 'UserRelation');

    public function beforeFilter()
    {
        $allow_action = array('test', 'order_export');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }


    /**
     * 获取粉丝备注
     */
    public function get_remark()
    {
        $user_id = $this->currentUser['id'];
        $id = $_REQUEST['id'];
        $res = $this->UserRelation->find('first', array(
            'conditions' => array(
                'follow_id' => $id,
                'user_id' => $user_id,
                'deleted' => DELETED_NO
            ),
            'fields' => 'remark'
        ));
        if(!empty($res)){
            echo json_encode($res['UserRelation']);
        }else{
            echo '{}';
        }
        exit;
    }

    /**
     * 更新粉丝备注
     */
    public function update_remark()
    {
        $user_id = $this->currentUser['id'];
        $id = $_REQUEST['id'];
        $remark = $_REQUEST['remark'];
        $this->UserRelation->update(['remark' => "'" . $remark . "'"], ['follow_id' => $id, 'user_id' => $user_id]);
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * 分享着统计汇总页面
     */
    public function dashboard()
    {
        $uid = $this->currentUser['id'];
        $subData = $this->ShareUtil->get_yesterday_fans_incremental($uid);
        $viewData = $this->ShareUtil->get_yesterday_view_count($uid);
        $month_ini = new DateTime("first day of this month");
        $month_end = new DateTime("last day of this month");
        $start_date = $month_ini->format('Y-m-d') . ' 00:00:00';
        $end_date = $month_end->format('Y-m-d') . ' 23:59:59';
        $orderData = $this->WeshareBuy->get_sharer_order_summary($uid, $start_date, $end_date);
        $fans_count = $this->WeshareBuy->get_sharer_fans_count($uid);
        echo json_encode(['view_count' => strval($viewData), 'sub_count' => strval($subData['sub_count'] - $subData['un_sub_count']), 'order_count' => strval($orderData['order_count']), 'total_fee' => strval($orderData['total_fee']), 'fans_count' => strval($fans_count)]);
        exit;
    }

    /**
     * @param $limit
     * @param $page
     * 获取粉丝列表
     */
    public function get_fans_info_list($limit, $page)
    {
        $uid = $this->currentUser['id'];
        $keyword = $_REQUEST['keyword'];
        $users = $this->ShareUtil->get_fans_info_list_by_sql($limit, $page, $keyword, $uid);
        echo json_encode($users);
        exit;
    }

    /**
     * @param $fan_id
     * @param $limit
     * @param $page
     * 获取粉丝详情
     */
    public function get_fans_detail($fan_id, $limit, $page)
    {
        $uid = $this->currentUser['id'];
        $user_detail = $this->ShareUtil->get_fans_detail($limit, $page, $fan_id, $uid);
        echo json_encode($user_detail);
        exit;
    }

    /**
     * api 每天订单的汇总
     */
    public function get_days_order_summary()
    {
        $uid = $this->currentUser['id'];
        $month_ini = new DateTime("first day of this month");
        $month_end = new DateTime("last day of this month");
        $start_date = $month_ini->format('Y-m-d') . ' 00:00:00';
        $end_date = $month_end->format('Y-m-d') . ' 23:59:59';
        $result = $this->WeshareBuy->get_days_order_summary($uid, $start_date, $end_date);
        echo json_encode($result);
        exit;
    }

    /**
     * @param $date
     * 获取某一天订单数据
     */
    public function get_days_order_detail($date)
    {
        $uid = $this->currentUser['id'];
        $start_date = $date . ' 00:00:00';
        $end_date = $date . ' 23:59:59';
        $result = $this->WeshareBuy->get_days_order_detail($uid, $start_date, $end_date);
        echo json_encode($result);
        exit;
    }

    /**
     * @param $shareId
     * 更新分享
     */
    public function update_share($shareId)
    {
        $weshareInfo = $this->ShareUtil->get_edit_share_info($shareId);
        echo json_encode($weshareInfo);
        exit;
    }

    /**
     * 保存分享
     */
    public function save_share()
    {
        $uid = $this->currentUser['id'];
        $postDataArray = $this->get_post_raw_data();
        $result = $this->Weshares->create_weshare($postDataArray, $uid);
        echo json_encode($result);
        exit;
    }

    /**
     * 创建分享
     */
    public function create_share()
    {
        $uid = $this->currentUser['id'];
        $postDataArray = $this->get_post_raw_data();
        $result = $this->Weshares->create_weshare($postDataArray, $uid);
        echo json_encode($result);
        exit;
    }

    /**
     * 删除分享
     * @param $weshare_id
     */
    public function delete_share($weshare_id)
    {
        $uid = $this->currentUser['id'];
        $this->Weshares->delete_weshare($uid, $weshare_id);
        echo json_encode(array('success' => true));
        exit;
    }

    public function publish_share($weshare_id)
    {
        $uid = $this->currentUser['id'];
        $this->Weshares->publish_weshare($uid, $weshare_id);
        echo json_encode(array('success' => true));
        exit;
    }

    /**
     * @param $shareId
     * 获取分享订单
     */
    public function get_my_share_orders($shareId)
    {
        $result = $this->WeshareBuy->get_share_order_for_show($shareId, true, $division = false, $export = false);
        usort($result['orders'], function ($a, $b) {
            return ($a['id'] < $b['id']) ? 1 : -1;
        });
        unset($result['ship_types']);
        unset($result['rebate_logs']);
        echo json_encode($result);
    }

    public function get_sharer_month_order_count()
    {
        $uid = $this->currentUser['id'];
        $userMonthOrderCount = $this->WeshareBuy->get_month_total_count($uid);
        echo json_encode(['count' => $userMonthOrderCount]);
        exit();
    }

    private function get_order_count_share_map($share_ids)
    {
        $query_order_sql = 'select count(id), member_id from cake_orders where member_id in (' . implode(',', $share_ids) . ') and status=1 and type=9 group by member_id';
        $orderM = ClassRegistry::init('Order');
        $result = $orderM->query($query_order_sql);
        $result = Hash::combine($result, '{n}.cake_orders.member_id', '{n}.0.count(id)');
        return $result;
    }


    public function get_share_list($status, $settlement, $page, $limit)
    {
        $uid = $this->currentUser['id'];
        $shares = $this->WeshareBuy->get_my_shares($uid, $status, $settlement, $page, $limit);
        echo json_encode($this->map_shares($shares));
        exit();
    }

    public function search_share_list($page, $limit)
    {
        $uid = $this->currentUser['id'];
        $keyword = $_REQUEST['keyword'];
        $shares = $this->WeshareBuy->search_shares($uid, $keyword, $page, $limit);
        echo json_encode($this->map_shares($shares));
        exit();
    }

    private function map_shares($shares)
    {
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

    public function get_auth_share_list($status, $settlement, $page, $limit)
    {
        $uid = $this->currentUser['id'];
        $auth_shares_result = $this->WeshareBuy->get_my_auth_shares($uid, $page, $limit, $status, $settlement);
        $shares = $this->map_auth_share_data($auth_shares_result);
        echo json_encode(array_values($shares));
        exit;
    }

    public function get_provide_share_list($status, $settlement, $page, $limit)
    {
        $uid = $this->currentUser['id'];
        $auth_shares_result = $this->WeshareBuy->get_my_auth_shares($uid, $page, $limit, $status, $settlement, true);
        $shares = $this->map_auth_share_data($auth_shares_result);
        echo json_encode(array_values($shares));
        exit;
    }

    private function map_auth_share_data($auth_shares_result)
    {
        $share_ids = Hash::extract($auth_shares_result, '{n}.Weshare.id');
        $shares = [];
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
        return $shares;
    }

    /**
     * 获取我的分享
     */
    public function get_my_shares()
    {
        $uid = $this->currentUser['id'];
        $createShares = $this->WeshareBuy->get_my_create_shares($uid);
        $share_ids = Hash::extract($createShares, '{n}.Weshare.id');
        if (!empty($share_ids)) {
            $query_order_sql = 'select count(id), member_id from cake_orders where member_id in (' . implode(',', $share_ids) . ') and status=1 and type=9 group by member_id';
            $orderM = ClassRegistry::init('Order');
            $result = $orderM->query($query_order_sql);
            $result = Hash::combine($result, '{n}.cake_orders.member_id', '{n}.count(id)');
            $share_balacne_money = $this->get_share_balance_result($share_ids);
            $createShares = Hash::extract($createShares, '{n}.Weshare');
            foreach ($createShares as &$shareItem) {
                $shareItem['order_count'] = empty($result[$shareItem['id']]) ? 0 : $result[$shareItem['id']];
                $shareItem['balance_money'] = $share_balacne_money[$shareItem['id']];
            }
        }
        usort($createShares, 'sort_data_by_id');
        $shareResult = $this->classify_shares_by_status($createShares);
        $userMonthOrderCount = $this->WeshareBuy->get_month_total_count($uid);
        $shareResult['monthOrderCount'] = $userMonthOrderCount;
        echo json_encode($shareResult);
        exit;
    }

    /**
     * @param $shareId
     * 重新开团, app开团
     */
    public function open_new_share($shareId)
    {
        $uid = $this->currentUser['id'];
        $is_owner = $this->Weshare->hasAny(['id' => $shareId, 'creator' => $uid]);
        if (!$is_owner) {
            echo json_encode(array('success' => false, 'reason' => 'not a proxy user.'));
            exit();
        }

        $this->log('Proxy ' . $uid . ' tries to clone share from share ' . $shareId, LOG_INFO);
        $result = $this->ShareUtil->cloneShare($shareId, null);
        if (!$result['success']) {
            $this->log('Proxy ' . $uid . ' failed to clone share from share ' . $shareId, LOG_ERR);
            echo json_encode($result);
            exit();
        }

        $weshareM = ClassRegistry::init('Weshare');
        $shareInfo = $weshareM->find('first', array(
            'conditions' => array(
                'id' => $result['shareId']
            ),
            'fields' => array('id', 'title', 'images', 'status', 'created', 'description')
        ));
        $shareInfo = $shareInfo['Weshare'];
        $shareInfo['images'] = explode('|', $shareInfo['images']);

        $this->log('Proxy ' . $uid . ' clones share ' . $result['shareId'] . '  from share ' . $shareId . ' successfully', LOG_INFO);
        echo json_encode(array('success' => true, 'shareInfo' => $shareInfo));
        exit();
    }

    /**
     * @param $shareId
     * 截团
     */
    public function stop_share($shareId)
    {
        $uid = $this->currentUser['id'];
        $this->Weshares->stop_weshare($uid, $shareId);
        echo json_encode(array('success' => true));
        exit;
    }

    /**
     * remark order
     */
    public function remark_order()
    {
        $postData = $this->get_post_raw_data();
        $this->WeshareBuy->update_order_remark($postData['order_id'], $postData['order_remark'], $postData['share_id']);
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $postData['share_id'] . '_0_1', "");
        echo json_encode(array('success' => true));
        exit;
    }

    /**
     * 发送团购通知
     */
    public function send_notify_msg()
    {
        $postData = $this->get_post_raw_data();
        $weshare_id = $postData['share_id'];
        $share_info = $this->ShareUtil->get_weshare_detail($weshare_id);
        $result = $this->ShareUtil->send_buy_percent_msg($postData['type'], $postData['user_id'], $share_info, $postData['content'], $weshare_id);
        echo json_encode($result);
        exit;
    }

    /**
     * 发送推荐通知
     */
    public function send_recommend()
    {
        $params = json_decode(file_get_contents('php://input'), true);
        $memo = $params['recommend_content'];
        $userId = $params['recommend_user'];
        $shareId = $params['recommend_share'];
        $result = $this->ShareUtil->saveShareRecommendLog($shareId, $userId, $memo);
        echo json_encode($result);
        return;
    }

    /**
     * 填写订单快递单号
     */
    public function set_order_ship_code()
    {
        $postData = $this->get_post_raw_data();
        $ship_company_id = $postData['company_id'];
        $weshare_id = $postData['share_id'];
        $ship_code = $postData['ship_code'];
        $order_id = $postData['order_id'];
        $this->ShareUtil->set_order_ship_code($ship_company_id, $weshare_id, $ship_code, $order_id);
        echo json_encode(array('success' => true));
        exit;
    }

    /**
     * 更新快递单号
     */
    public function update_order_ship_code()
    {
        $postData = $this->get_post_raw_data();
        $ship_code = $postData['ship_code'];
        $weshare_id = $postData['share_id'];
        $order_id = $postData['order_id'];
        $company_id = $postData['company_id'];
        $ship_type_name = $postData['ship_type_name'];
        $this->ShareUtil->update_order_ship_code($ship_code, $weshare_id, $order_id, $company_id, $ship_type_name);
        echo json_encode(array('success' => true));
        exit;
    }

    /**
     * 发送到货提醒
     */
    public function send_pickup_notify()
    {
        $uid = $this->currentUser['id'];
        $postData = $this->get_post_raw_data();
        $order_ids = $postData['order_ids'];
        $weshare_id = $postData['share_id'];
        $content = $postData['content'];
        $this->ShareUtil->send_arrival_msg($order_ids, $weshare_id, $uid, $content);
        echo json_encode(array('success' => true));
        exit;
    }

    /**
     * 订单退款
     */
    public function order_refund()
    {
        $uid = $this->currentUser['id'];
        $postData = $this->get_post_raw_data();
        $shareId = $postData['share_id'];
        $orderId = $postData['order_id'];
        $refundMoney = $postData['refundMoney'];
        $refundMark = $postData['refundMark'];
        $result = $this->ShareUtil->order_refund($shareId, $uid, $orderId, $refundMoney, $refundMark);
        echo json_encode($result);
        exit;
    }

    /**
     * @param $order_id
     * 确认取货
     */
    public function confirm_received($order_id)
    {
        $uid = $this->currentUser['id'];
        $result = $this->ShareUtil->confirm_received_order($order_id, $uid);
        echo json_encode($result);
        exit;
    }

    /**
     * @param $shareId
     * @param int $only_paid
     * 订单导出
     */
    public function order_export($shareId, $only_paid = 1)
    {
        $this->autoRender = true;
        $this->layout = null;
        if ($only_paid == 1) {
            $export_paid_order = true;
        } else {
            $export_paid_order = false;
        }
        $statics_data = $this->WeshareBuy->get_share_order_for_show($shareId, true, true, $export_paid_order);
        $this->set($statics_data);
    }

    /**
     * @param $createShares
     * @return array
     * 过滤分类订单
     */
    private function classify_shares_by_status($createShares)
    {
        //normal => 进行中 stop => 截团 settlement => 已结款
        $result = array('normal' => array(), 'stop' => array(), 'settlement' => array());
        foreach ($createShares as $shareItem) {
            $settlement = $shareItem['settlement'];
            $status = $shareItem['status'];
            if ($settlement == WESHARE_SETTLEMENT_YES) {
                $result['settlement'][] = $shareItem;
            } else {
                if ($status == WESHARE_STATUS_NORMAL) {
                    $result['normal'][] = $shareItem;
                }
                if ($status == WESHARE_STATUS_STOP) {
                    $result['stop'][] = $shareItem;
                }
            }
        }
        return $result;
    }

    /**
     * @param $share_ids
     * @return array
     * 获取结算结果
     */
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

    protected function get_post_raw_data()
    {
        $postStr = file_get_contents('php://input');
        $postData = json_decode($postStr, true);
        return $postData;
    }
}