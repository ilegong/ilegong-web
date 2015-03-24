<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 3/19/15
 * Time: 20:26
 */

class TuanController extends AppController{

    var $name = 'Tuan';

    var $uses = array('TuanTeam','TuanBuying','Order');

    /**
     * index view
     */
    public function admin_view(){

    }
    /**
     * query tuan orders
     */
    public function admin_tuan_orders(){
        $team_id = $_REQUEST['team_id'];
        $product_id = $_REQUEST['product_id'];
        $time_type = $_REQUEST['time_type'];
        $con_name = $_REQUEST['conn_name'];
        $con_address = $_REQUEST['con_address'];
        $con_phone = $_REQUEST['con_phone'];
        $post_time = $_REQUEST['post_time'];
        $order_type = $_REQUEST['order_type'];
        $query_tb = array();
        if(!empty($team_id)&&$team_id!='-1'){
            $query_tb['tuan_id']=$team_id;
        }
        if($time_type==0){
            $query_tb['end_time']=$post_time;
        }else if($time_type==1){
            $query_tb['consign_time']=$post_time;
        }
        if(!empty($product_id)){
            $query_tb['pid'] = $product_id;
        }
        $tuan_buys = $this->TuanBuying->find('all',array(
            'conditions' => $query_tb
        ));


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
            if($order_type!=-1){
                $orders = $this->Order->find('all',array(
                    'conditions' => array(
                        'type' => ORDER_TYPE_TUAN,
                        'member_id' => $tb_ids,
                        'status' => $order_type
                    ),
                    'joins' => $join_conditions,
                    'fields' => array('Order.*', 'Pay.trade_type'),
                ));
            }else{
                $orders = $this->Order->find('all',array(
                    'conditions' => array(
                        'type' => ORDER_TYPE_TUAN,
                        'member_id' => $tb_ids
                    ),
                    'joins' => $join_conditions,
                    'fields' => array('Order.*', 'Pay.trade_type'),
                ));
            }
            $order_ids = Hash::extract($orders,'{n}.Order.id');
            $carts = $this->Cart->find('all',array(
                'conditions'=>array(
                    'order_id' => $order_ids,
                )));

            $order_carts = array();
            foreach($carts as $c){
                $c_order_id = $c['Cart']['order_id'];
                if (!isset($order_carts[$c_order_id])) {
                    $order_carts[$c_order_id] = array();
                }
                $order_carts[$c_order_id][] = $c;
            }
            $orders = Hash::combine($orders,'{n}.Order.id','{n}.Order');
            $this->set('orders',$orders);
            $this->set('order_carts',$order_carts);
            $tuan_ids = Hash::extract($tuan_buys,'{n}.TuanBuying.tuan_id');
            $tuanTeamM = ClassRegistry::init('TuanTeam');
            $tuans = $this->$tuanTeamM->find('all',array(
                'conditions' => array(
                    'id' => $tuan_ids
                )
            ));
            $tuans = Hash::combine($tuans,'{n}.TuanTeam.id','{n}.TuanTeam');
            $this->set('tuans',$tuans);
            $tuan_buys = Hash::combine($tuan_buys,'{n}.TuanBuying.id','{n}.TuanBuying');
            $this->set('tuan_buys',$tuan_buys);
        }
        $this->set('team_id',$team_id);
        $this->set('product_id',$product_id);
        $this->set('time_type',$time_type);
        $this->set('conn_name',$con_name);
        $this->set('con_address',$con_address);
        $this->set('con_phone',$con_phone);
        $this->set('post_time',$post_time);
    }
    /**
     * ajax delete tuan
     * when delete casde tuan buying
     */
    public function admin_delete_tuan(){

    }
    /**
     * ajax delete tuan buying
     */
    public function admin_delete_tuan_buying(){

    }

    /**
     *  ajax save tuan
     */
    public function admin_save_tuan(){

    }

    /**
     * ajax save tuan buying
     */
    public function admin_save_tuan_buying(){

    }
    /**
     * ajax get tuan products
     */
    public function admin_tuan_products(){
        $this->autoRender=false;
        $results = array('838'=>'草莓');
        echo json_encode($results);
    }

    /**
     * ajax get teams
     */
    public function admin_tuan_teams(){
        $this->autoRender=false;
        $teams = $this->TuanTeam->find('all');
        $teams = Hash::extract($teams,'{n}.TuanTeam');
        echo json_encode($teams);
    }

}