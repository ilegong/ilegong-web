<?php

class PromotorsController extends AppController
{
    var $name = 'Promotors';

    public function summary($tuan_team_id){

        $this->loadModel('TuanTeam');
        $tuan_team = $this->TuanTeam->findById($tuan_team_id);
        if(empty($tuan_team)){
            throw new Exception('tuan team does not exist');
        }

        $this->loadModel('Order');
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'Order.status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED),
                'Order.type' => 5
            ),
            'joins' => array(
                array(
                    'table' => 'tuan_buyings',
                    'alias' => 'TuanBuying',
                    'conditions' => array(
                        'TuanBuying.tuan_id = ' . $tuan_team_id
                    ),
                    'type' => 'LEFT',
                )
            ),
            'fields' => array('Order.*'),
            'group' => 'Order.id',
            'order' => 'Order.pay_time ASC'
        ));

        $distinct_orders = array();
        foreach ($orders as &$order) {
            if (!empty($distinct_orders[$order['Order']['creator']])) {
                continue;
            }
            $distinct_orders[$order['Order']['creator']] = $order;
        }

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
}