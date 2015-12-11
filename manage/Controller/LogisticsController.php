<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 12/11/15
 * Time: 08:56
 */

class LogisticsController extends AppController{

    var $name = 'Share';

    var $components = array('Weixin');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'bootstrap_layout';
    }

    public function admin_query_logistics_order() {
        $logisticsOrderM = ClassRegistry::init('LogisticsOrder');
        $logisticsOrderItemM = ClassRegistry::init('LogisticsOrderItem');
        $uid = $_REQUEST['user_id'];
        $q_cond = array();
        if (!empty($uid)) {
            $q_cond['creator'] = $uid;
        }
        $share_id = $_REQUEST['share_id'];
        if (!empty($share_id)) {
            $order_ids = $this->get_order_ids_by_share($share_id);
            $q_cond['order_id'] = $order_ids;
        }
        $id = $_REQUEST['id'];
        if (!empty($id)) {
            $q_cond['id'] = $id;
        }
        $date = $_REQUEST['date'];
        if (!empty($date)) {
            $q_cond['DATE(created)'] = $date;
        }
        $order_status = $_REQUEST['order_status'];
        if ($order_status != -1) {
            $q_cond['status'] = $order_status;
        }
        if (empty($q_cond)) {
            $q_cond['DATE(created)'] = date('Y-m-d');
        }
        $logistics_orders = $logisticsOrderM->find('all', array(
            'conditions' => $q_cond,
            'limit' => 500
        ));
        $logistics_order_ids = Hash::extract($logistics_orders, '{n}.LogisticsOrder.id');
        $logistics_order_items = $logisticsOrderItemM->find('all', array(
            'conditions' => array(
                'logistics_order_id' => $logistics_order_ids
            )
        ));
        $logistics_order_item_map = array();
        foreach ($logistics_order_items as $logistics_order_item) {
            $logistics_order_id = $logistics_order_item['LogisticsOrderItem']['logistics_order_id'];
            if (!isset($logistics_order_item_map[$logistics_order_id])) {
                $logistics_order_item_map[$logistics_order_id] = array();
            }
            $logistics_order_item_map[$logistics_order_id][] = $logistics_order_item['LogisticsOrderItem'];
        }
        $this->set('logistics_orders', $logistics_orders);
        $this->set('logistics_order_item_map', $logistics_order_item_map);
    }


    private function get_order_ids_by_share($share_id) {
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'member_id' => $share_id,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'ship_mark' => array(SHARE_SHIP_PYS_ZITI_TAG),
                'not' => array('status' => array(ORDER_STATUS_WAITING_PAY)),
            ),
            'fields' => array('id')
        ));
        $order_ids = Hash::extract($orders, '{n}.Order.id');
        return $order_ids;
    }


}