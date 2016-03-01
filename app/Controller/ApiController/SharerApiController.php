<?php

class SharerApiController extends AppController{

    public $components = array('OAuth.OAuth', 'Session', 'WeshareBuy', 'ShareUtil');

    public function beforeFilter(){
        $allow_action = array('test');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }


    /**
     * 创建分享
     */
    public function create_share(){
        $uid = $this->currentUser['id'];
        $postStr = file_get_contents('php://input');
        $this->log('app post str ' . $postStr, LOG_DEBUG);
        $postDataArray = json_decode($postStr, true);
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
                'fields' => array('id', 'title', 'images', 'status', 'created')
            ));
            $shareInfo = $shareInfo['Weshare'];
            $shareInfo['images'] = explode(',', $shareInfo['images']);
            echo json_encode(array('success' => true, 'shareInfo' => $shareInfo));
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
        $postStr = file_get_contents('php://input');
        $postData = json_decode($postStr, true);
        $this->WeshareBuy->update_order_remark($postData['order_id'], $postData['order_remark'], $postData['share_id']);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 发送团购通知
     */
    public function send_notify_msg(){

    }

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