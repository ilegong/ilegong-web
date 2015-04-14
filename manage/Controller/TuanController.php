<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 3/19/15
 * Time: 20:26
 */

class TuanController extends AppController{

    var $name = 'Tuan';

    var $uses = array('TuanTeam','TuanBuying','Order','Cart','TuanBuyingMessages','TuanProduct');

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
        $con_address = $_REQUEST['con_address'];
        $query_tb = array();
        $should_count_nums = false;
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
            $should_count_nums = true;
            $this->set('should_count_nums',$should_count_nums);
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

        $order_query_cond = array(
            'Order.type' => ORDER_TYPE_TUAN
        );

        if(!empty($tuan_buys)){
            $tb_ids = Hash::extract($tuan_buys,'{n}.TuanBuying.id');
            $order_query_cond['Order.member_id'] = $tb_ids;
        }

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

        if(!empty($con_address)){
            $order_query_cond['Order.consignee_address LIKE'] = '%'.$con_address.'%';
        }

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
            if($should_count_nums){
                $order_id_strs = '('.join(',',$order_ids).')';
                $product_count = $this->Cart->query('select sum(num) from cake_carts where order_id in '.$order_id_strs);
                $this->set('product_count',$product_count[0][0]['sum(num)']);
                $product_spec_map = $this->Cart->query('select specId,sum(num) from cake_carts where order_id in '.$order_id_strs.' group by specId');
                $this->set('product_spec_map',$product_spec_map);
            }
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
            //排期
            $consign_ids = array_unique(Hash::extract($carts,'{n}.Cart.consignment_date'));
            if(count($consign_ids)!=1 || !empty($consign_ids[0])){
                $this->loadModel('ConsignmentDate');
                $consign_dates = $this->ConsignmentDate->find('all',array(
                    'conditions' => array(
                        'id' => $consign_ids
                    )
                ));
                $consign_dates = Hash::combine($consign_dates,'{n}.ConsignmentDate.id','{n}.ConsignmentDate.send_date');
                $this->set('consign_dates', $consign_dates);
            }
        }

        $this->set('spec_groups',$spec_groups);
        $this->set('team_id',$team_id);
        $this->set('product_id',$product_id);
        $this->set('time_type',$time_type);
        $this->set('con_name',$con_name);
        $this->set('con_phone',$con_phone);
        $this->set('post_time',$post_time);
        $this->set('order_type',$order_type);
        $this->set('order_id',$order_id);
        $this->set('con_address',$con_address);
    }


    /**
     * 团购功能列表
     */
    public function admin_tuan_func_list(){
        $tuan_team_count = $this->TuanTeam->query('select count(*) as c from cake_tuan_teams');
        $this->set('tuan_team_count', $tuan_team_count[0][0]['c']);
        $tuan_product_count = $this->TuanProduct->query('select count(*) as c from cake_tuan_products where deleted = 0');
        $this->set('tuan_product_count', $tuan_product_count[0][0]['c']);
    }
}