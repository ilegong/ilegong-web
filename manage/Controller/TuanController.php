<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 3/19/15
 * Time: 20:26
 */

class TuanController extends AppController{

    var $name = 'Tuan';

    var $uses = array('TuanTeam','TuanBuying','Order','Cart','TemplateMsgLog','TuanProduct');

    /**
     * query tuan orders
     */
    public function admin_tuan_orders(){
        $this->loadModel('ProductSpecGroup');
        $team_id = $_REQUEST['team_id'];
        $product_id = $_REQUEST['product_id'];
        $time_type = $_REQUEST['time_type'];
        $con_name = $_REQUEST['con_name'];
        $con_phone = $_REQUEST['con_phone'];
        $post_time = $_REQUEST['post_time'];
        $order_type = $_REQUEST['order_type'];
        $order_id = $_REQUEST['order_id'];
        $query_tb = array();
        if(!empty($team_id)&&$team_id!='-1'){
            $query_tb['tuan_id']=$team_id;
        }
        if($time_type==0){
            $query_tb['end_time > ']=$post_time;
        }else if($time_type==1){
            $query_tb['consign_time > ']=$post_time;
        }
        if(!empty($product_id)){
            $query_tb['pid'] = $product_id;
        }
        $tuan_buys = $this->TuanBuying->find('all',array(
            'conditions' => $query_tb
        ));

        $p_ids = Hash::extract($tuan_buys,'{n}.TuanBuying.pid');

        $spec_groups = $this->ProductSpecGroup->find('all',array(
            'conditions' => array(
                'product_id' => $p_ids
            )
        ));

        $spec_groups = Hash::combine($spec_groups,'{n}.ProductSpecGroup.id','{n}.ProductSpecGroup.spec_names');


        if(!empty($tuan_buys)){
            $tb_ids = Hash::extract($tuan_buys,'{n}.TuanBuying.id');
            $payNotifyModel = ClassRegistry::init('PayNotify');
            $payNotifyModel->query("update cake_pay_notifies set order_id =  substring_index(substring_index(out_trade_no,'-',2),'-',-1) where status = 6");
            $join_conditions = array(
                array(
                    'table' => 'pay_notifies',
                    'alias' => 'Pay',
                    'conditions' => array(
                        'Pay.order_id = Order.id'
                    ),
                    'type' => 'LEFT',
                )
            );
            $order_query_cond = array(
                'Order.type' => ORDER_TYPE_TUAN,
                'Order.member_id' => $tb_ids,
            );

            if(!empty($con_name)){
                $order_query_cond['Order.consignee_name LIKE'] = '%'.$con_name.'%';
            }

            if(!empty($con_phone)){
                $order_query_cond['Order.consignee_mobilephone LIKE'] = '%'.$con_phone.'%';
            }
            if(!empty($order_id)){
                $order_query_cond['Order.id']=$order_id;
            }

            if($order_type!=-1){
                $order_query_cond['Order.status']=$order_type;
            }else{
                $order_query_cond['Order.status'] = array(ORDER_STATUS_CANCEL, ORDER_STATUS_WAITING_PAY);
                $orders_invalid = $this->Order->find('count', array(
                    'conditions' => $order_query_cond,
                ));
                $this->set('orders_invalid', $orders_invalid);
                if ($orders_invalid > 0) {
                    $r = $this->Order->find('all', array(
                        'fields' => array('sum(Order.total_all_price)   AS total'),
                        'conditions' => $order_query_cond,
                    ));
                    if (!empty($r)) {
                        $this->set('total_unpaid', $r[0][0]['total']);
                    }
                }
                unset($order_query_cond['Order.status']);
            }

            $orders = $this->Order->find('all',array(
                'conditions' => $order_query_cond,
                'joins' => $join_conditions,
                'fields' => array('Order.*', 'Pay.trade_type'),
            ));

            if(!empty($orders)){
                $total_money = 0;
                foreach($orders as $o){
                    $ids[] = $o['Order']['id'];
                    $o_status = $o['Order']['status'];
                    if($o_status == 1 || $o_status == 2 || $o_status == 3 ){
                        $total_money = $total_money + $o['Order']['total_all_price'];
                    }
                }
                $this->set('total_money',$total_money);
                $order_ids = Hash::extract($orders,'{n}.Order.id');
                $carts = $this->Cart->find('all',array(
                    'conditions'=>array(
                        'order_id' => $order_ids,
                    )));

                $order_carts = array();
                foreach($carts as &$c){
                    $c_order_id = $c['Cart']['order_id'];
                    $specId = $c['Cart']['specId'];
                    $c['Cart']['spec_name'] = $spec_groups[$specId];
                    if (!isset($order_carts[$c_order_id])) {
                        $order_carts[$c_order_id] = array();
                    }
                    $order_carts[$c_order_id][] = $c;
                }
                $this->set('orders',$orders);
                $this->set('order_carts',$order_carts);
                $tuan_ids = Hash::extract($tuan_buys,'{n}.TuanBuying.tuan_id');
                $tuans = $this->TuanTeam->find('all',array(
                    'conditions' => array(
                        'id' => $tuan_ids
                    )
                ));
                $tuans = Hash::combine($tuans,'{n}.TuanTeam.id','{n}.TuanTeam');
                $this->set('tuans',$tuans);
                $tuan_buys = Hash::combine($tuan_buys,'{n}.TuanBuying.id','{n}.TuanBuying');
                $this->set('tuan_buys',$tuan_buys);
            }
        }
        $this->set('team_id',$team_id);
        $this->set('product_id',$product_id);
        $this->set('time_type',$time_type);
        $this->set('con_name',$con_name);
        $this->set('con_phone',$con_phone);
        $this->set('post_time',$post_time);
        $this->set('order_type',$order_type);
        $this->set('order_id',$order_id);
    }


    /**
     * 团购功能列表
     */
    public function admin_tuan_func_list(){

    }

}