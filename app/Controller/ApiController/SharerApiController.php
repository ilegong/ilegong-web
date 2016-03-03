<?php

class SharerApiController extends AppController{

    public $components = array('OAuth.OAuth', 'Session', 'WeshareBuy', 'ShareUtil');

    public function beforeFilter(){
        $allow_action = array('test', 'update_share');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }

    /**
     * @param $shareId
     * 更新分享
     */
    public function update_share($shareId){
        $weshareInfo = $this->ShareUtil->get_weshare_detail($shareId);
        echo json_encode($weshareInfo);
        return;
    }

    /**
     * 保存分享
     */
    public function save_share(){
        $uid = $this->currentUser['id'];
        $postDataArray = $this->get_post_raw_data();
        $result = $this->ShareUtil->create_share($postDataArray, $uid);
        echo json_encode($result);
        return;
    }

    /**
     * 创建分享
     */
    public function create_share(){
        $uid = $this->currentUser['id'];
        $postDataArray = $this->get_post_raw_data();
        $result = $this->ShareUtil->create_share($postDataArray, $uid);
        echo json_encode($result);
        return;
    }

    /**
     * @param $shareId
     * 获取分享订单
     */
    public function get_my_share_orders($shareId){
        $result = $this->WeshareBuy->get_share_order_for_show($shareId, true, $division = false, $export = false);
        unset($result['ship_types']);
        unset($result['rebate_logs']);
        echo json_encode($result);
    }

    /**
     * 获取我的分享
     */
    public function get_my_shares(){
        $uid = $this->currentUser['id'];
        $createShares = $this->WeshareBuy->get_my_create_shares($uid);
        $share_ids = Hash::extract($createShares, '{n}.Weshare.id');
        if(!empty($share_ids)){
            $query_order_sql = 'select count(id), member_id from cake_orders where member_id in (' . implode(',', $share_ids) . ') and status=1 group by member_id';
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
        return;
    }

    /**
     * @param $shareId
     * 重新开团
     */
    public function open_new_share($shareId){
        $result = $this->ShareUtil->cloneShare($shareId);
        if($result['success']){
            $shareId = $result['shareId'];
            $weshareM = ClassRegistry::init('Weshare');
            $shareInfo = $weshareM->find('first', array(
                'conditions' => array(
                    'id' => $shareId
                ),
                'fields' => array('id', 'title', 'images', 'status', 'created', 'description')
            ));
            $shareInfo = $shareInfo['Weshare'];
            $shareInfo['images'] = explode(',', $shareInfo['images']);
            echo json_encode(array('success' => true, 'shareInfo' => $shareInfo));
            return;
        }
        echo json_encode(array('success' => false));
        return;
    }

    /**
     * @param $shareId
     * 截团
     */
    public function stop_share($shareId){
        $uid = $this->currentUser['id'];
        $this->ShareUtil->stop_share($shareId, $uid);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * remark order
     */
    public function remark_order(){
        $postData = $this->get_post_raw_data();
        $this->WeshareBuy->update_order_remark($postData['order_id'], $postData['order_remark'], $postData['share_id']);
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $postData['share_id'] . '_0_1', "");
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 发送团购通知
     */
    public function send_notify_msg(){
        $postData = $this->get_post_raw_data();
        $weshare_id = $postData['share_id'];
        $share_info = $this->ShareUtil->get_weshare_detail();
        $result = $this->ShareUtil->send_buy_percent_msg($postData['type'], $postData['user_id'], $share_info, $postData['content'], $weshare_id);
        echo json_decode($result);
        return;
    }

    /**
     * 填写订单快递单号
     */
    public function set_order_ship_code(){
        $postData = $this->get_post_raw_data();
        $ship_company_id = $postData['company_id'];
        $weshare_id = $postData['share_id'];
        $ship_code = $postData['ship_code'];
        $order_id = $postData['order_id'];
        $this->ShareUtil->set_order_ship_code($ship_company_id, $weshare_id, $ship_code, $order_id);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 更新快递单号
     */
    public function update_order_ship_code(){
        $postData = $this->get_post_raw_data();
        $ship_code = $postData['ship_code'];
        $weshare_id = $postData['share_id'];
        $order_id = $postData['order_id'];
        $company_id = $postData['company_id'];
        $ship_type_name = $postData['ship_type_name'];
        $this->ShareUtil->update_order_ship_code($ship_code, $weshare_id, $order_id, $company_id, $ship_type_name);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 发送到货提醒
     */
    public function send_pickup_notify(){
        $uid = $this->currentUser['id'];
        $postData = $this->get_post_raw_data();
        $order_ids = $postData['order_ids'];
        $weshare_id = $postData['share_id'];
        $content = $postData['content'];
        $this->ShareUtil->send_arrival_msg($order_ids, $weshare_id, $uid, $content);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 订单退款
     */
    public function order_refund(){
        $uid = $this->currentUser['id'];
        $postData  = $this->get_post_raw_data();
        $shareId = $postData['shareId'];
        $orderId = $postData['orderId'];
        $refundMoney = $postData['refundMoney'];
        $refundMark = $postData['refundMark'];
        $result = $this->ShareUtil->order_refund($shareId, $uid, $orderId, $refundMoney, $refundMark);
        echo json_encode($result);
        return;
    }

    /**
     * @param $order_id
     * 确认取货
     */
    public function confirm_received($order_id){
        $uid = $this->currentUser['id'];
        $result = $this->ShareUtil->confirm_received_order($order_id, $uid);
        echo json_encode($result);
        return;
    }

    /**
     * @param $createShares
     * @return array
     * 过滤分类订单
     */
    private function classify_shares_by_status($createShares){
        //normal => 进行中 stop => 截团 settlement => 已结款
        $result = array('normal' => array(), 'stop' => array(), 'settlement' => array());
        foreach ($createShares as $shareItem) {
            $settlement = $shareItem['settlement'];
            $status = $shareItem['status'];
            if ($settlement == WESHARE_SETTLEMENT_STATUS) {
                $result['settlement'][] = $shareItem;
            } else {
                if ($status == WESHARE_NORMAL_STATUS) {
                    $result['normal'][] = $shareItem;
                }
                if ($status == WESHARE_STOP_STATUS) {
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
    private function  get_share_balance_result($share_ids){
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
}