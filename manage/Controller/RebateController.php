<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 9/2/15
 * Time: 14:24
 */
class RebateController extends AppController{
    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User',
         'WeshareShipSetting', 'OfflineStore', 'Oauthbind', 'Comment', 'RefundLog', 'PayNotify', 'RebateTrackLog');

    var $components = array('Weixin');

    public function beforeFilter(){
        parent::beforeFilter();
        $this->layout='bootstrap_layout';
    }

    public function admin_pay_rebate() {
        $this->autoRender = false;
        $sharer_id = $_REQUEST['sharer_id'];
        $start_date = $_REQUEST['start_date'];
        $end_date = $_REQUEST['end_date'];
        $this->RebateTrackLog->updateAll(array('is_rebate' => 1), array('DATE(updated) >= ' => $start_date, 'DATE(updated) <= ' => $end_date, 'sharer' => $sharer_id, 'is_paid' => 1));
        echo json_encode(array('success' => true));
        return;
    }

    public function admin_index(){
        $currentDate = date('Y-m-d H:i:s');
        if ($_REQUEST['start_date']) {
            $start_date = $_REQUEST['start_date'];
        } else {
            $start_date = getMonthRange($currentDate);
        }

        if ($_REQUEST['end_date']) {
            $end_date = $_REQUEST['end_date'];
        } else {
            $end_date = getMonthRange($currentDate, false);
        }
        $queryCond = array();
        $queryCond['DATE(updated) >= '] = $start_date;
        $queryCond['DATE(updated) <= '] = $end_date;
        if ($_REQUEST['proxy_id']) {
            $queryCond['sharer'] = $_REQUEST['proxy_id'];
        }
        $queryCond['is_rebate'] = 0;
        $queryCond['not'] = array('order_id' => 0, 'is_paid' => 0);

        $rebateLogs = $this->RebateTrackLog->find('all', array(
            'conditions' => $queryCond,
            'limit' => 3000
        ));
        $rebate_order_ids = Hash::extract($rebateLogs, '{n}.RebateTrackLog.order_id');
        $rebate_user_ids = Hash::extract($rebateLogs, '{n}.RebateTrackLog.sharer');
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'id' => $rebate_order_ids
            ),
            'fields' => array('id', 'total_all_price', 'cate_id')
        ));
        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $rebate_user_ids
            ),
            'fields' => array('id', 'nickname', 'mobilephone', 'payment')
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        $orders = Hash::combine($orders, '{n}.Order.id', '{n}.Order');
        $rebate_data = array();
        foreach ($rebateLogs as $log) {
            $sharer = $log['RebateTrackLog']['sharer'];
            $order = $orders[$log['RebateTrackLog']['order_id']];
            if (!isset($rebate_data[$sharer])) {
                $rebate_data[$sharer] = array('sharer' => $sharer, 'total_order_money' => 0, 'rebate_money' => 0, 'order_count' => 0);
            }
            $rebate_data[$sharer]['order_count'] = $rebate_data[$sharer]['order_count'] + 1;
            $rebate_data[$sharer]['total_order_money'] = $rebate_data[$sharer]['total_order_money'] + $order['total_all_price'];
            $rebate_data[$sharer]['rebate_money'] = $rebate_data[$sharer]['rebate_money'] + $log['RebateTrackLog']['rebate_money'];
        }
        $this->set('users', $users);
        $this->set('rebate_data', $rebate_data);
        $this->set('sharer_id', $_REQUEST['sharer_id']);
        $this->set('start_date', $start_date);
        $this->set('end_date', $end_date);
    }


}