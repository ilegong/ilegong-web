<?php

class PromotorsController extends AppController
{
    var $name = 'Promotors';

    public function summary($tuan_team_id)
    {
        if(!in_array($tuan_team_id, array(170, 171, 172, 173, 174))){
            throw new Exception('permission forbidden');
        }
        $this->loadModel('TuanTeam');
        $tuan_team = $this->TuanTeam->findById($tuan_team_id);
        if (empty($tuan_team)) {
            throw new Exception('tuan team does not exist');
        }

        $distinct_orders = $this->_orders_of_tuan_teams($tuan_team['TuanTeam']['id']);

        $date_orders = array();
        foreach ($distinct_orders as &$order) {
            $pay_date = date_format(date_create($order['Order']['pay_time']), 'Y-m-d');
            if (empty($date_orders[$pay_date])) {
                $date_orders[$pay_date] = array();
            }
            $date_orders[$pay_date][] = $order;
        }

        $this->set('tuan_team', $tuan_team);
        $this->set('date_orders', $date_orders);
        $this->set('orders_count', count($distinct_orders));
    }

    public function summary_offlinestore($offline_store_id)
    {
//        $this->loadModel('TuanTeam');
//        $tuan_teams = $this->TuanTeam->find('all', array(
//            'conditions' => array(
//                'offline_store_id' => $offline_store_id
//            )
//        ));
//
//        $distinct_orders = $this->_orders_of_tuan_teams(Hash::extract($tuan_teams, 'TuanTeam.id'));
//
//        $date_orders = array();
//        foreach ($distinct_orders as &$order) {
//            $pay_date = date_format(date_create($order['Order']['pay_time']), 'Y-m-d');
//            if (empty($date_orders[$pay_date])) {
//                $date_orders[$pay_date] = array();
//            }
//            $date_orders[$pay_date][] = $order;
//        }
//
//        $this->set('date_orders_count', $date_orders_count);
//        $this->set('orders_count', count($distinct_orders));
    }

    private function _orders_of_tuan_teams($tuan_team_ids){
        $this->loadModel('Order');
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'Order.status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED),
                'Order.type' => 5,
                'TuanTeam.id' => $tuan_team_ids
            ),
            'joins' => array(
                array(
                    'table' => 'tuan_buyings',
                    'alias' => 'TuanBuying',
                    'conditions' => array(
                        'TuanBuying.id = Order.member_id'
                    ),
                    'type' => 'INNER',
                ),
                array(
                    'table' => 'tuan_teams',
                    'alias' => 'TuanTeam',
                    'conditions' => array(
                        'TuanTeam.id = TuanBuying.tuan_id'
                    ),
                    'type' => 'INNER',
                )
            ),
            'fields' => array('Order.id', 'Order.pay_time', 'Order.creator', 'Order.total_all_price', 'Order.consignee_name', 'Order.consignee_mobilephone', 'TuanBuying.tuan_id', 'TuanTeam.id', 'TuanTeam.tuan_name'),
            'order' => 'Order.pay_time DESC'
        ));

        $distinct_orders = array();
        foreach ($orders as &$order) {
            if (!empty($distinct_orders[$order['Order']['creator']])) {
                continue;
            }
            $distinct_orders[$order['Order']['creator']] = $order;
        }

        return $distinct_orders;
    }
}