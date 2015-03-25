<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 3/19/15
 * Time: 20:26
 */

class TuanController extends AppController{

    var $name = 'Tuan';

    var $uses = array('TuanTeam','TuanBuying','Order','Cart');

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

            if($order_type!=-1){
                $order_query_cond['Order.status']=$order_type;
            }
            $orders = $this->Order->find('all',array(
                'conditions' => $order_query_cond,
                'joins' => $join_conditions,
                'fields' => array('Order.*', 'Pay.trade_type'),
            ));
            if(!empty($orders)){
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
        $this->set('conn_name',$con_name);
        $this->set('con_phone',$con_phone);
        $this->set('post_time',$post_time);
        $this->set('order_type',$order_type);
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

    /**
     * show all tuan_buyings
     */
     public function admin_tuan_buyings(){

         $team_id = $_REQUEST['team_id'];
         $product_id = $_REQUEST['product_id'];
         $time_type = $_REQUEST['time_type'];
         $tuan_type = $_REQUEST['tuan_type'];
         $post_time = $_REQUEST['post_time'];
         $con = array();
         if(!empty($team_id)&&$team_id!='-1'){
             $con['tuan_id']=$team_id;
         }
         if($time_type==0){
             $con['end_time']=$post_time;
         }else if($time_type==1){
             $con['consign_time']=$post_time;
         }
         if(!empty($product_id)&&$product_id!=-1){
             $con['pid'] = $product_id;
         }
         if($tuan_type!=-1){
             $con['status'] = $tuan_type;
         }
         $this->log('con'.json_encode($con));
         if(!empty($con)){
         $tuan_buyings = $this->TuanBuying->find('all',array(
             'conditions' => $con
         ));}else{
         $tuan_buyings = $this->TuanBuying->find('all',array('conditions' => array('pid !=' => null)));
         }
         $tuan_ids = Hash::extract($tuan_buyings,'{n}.TuanBuying.id');
         $tuan_team_info = array();
         foreach($tuan_ids as $id){
             $tuan_team_info[$id] = $this->TuanBuying->find('first',array('conditions' => array('id' => $id)));
             $tuan_info = $this->TuanTeam->find('first',array('conditions' => array('id' => $tuan_team_info[$id]['TuanBuying']['tuan_id']),'fields' => array('tuan_name','id')));
             $tuan_team_info[$id]['name'] = $tuan_info['TuanTeam']['tuan_name'];
             $tuan_team_info[$id]['tuan_id'] = $tuan_info['TuanTeam']['id'];
         }
         $this->log('tuan_info'.json_encode($tuan_team_info));
         $this->set('tuan_ids',$tuan_ids);
         $this->set('tuan_team_info',$tuan_team_info);

     }

    /**
     * set tuan_buying status
     */
     public function admin_tuan_buying_set(){
         $this->autoRender = false;
         if($this->request->is('post')){
             $id = $_REQUEST['id'];
             $val = $_REQUEST['val'];
             $res = array();
//             foreach($id as $tuan_buying_id){
                 $this->TuanBuying->updateAll(array('status' => $val),array('id' => $id));
                 $res [$tuan_buying_id] = array('success' => __('团购状态修改成功.', true));
//             }
             $this->log('status'.json_encode($res));
             echo json_encode($res);
         }
     }

    /**
     * edit tuan_buying info
     */
     public function admin_tuan_buying_edit($id){
         $data_info = $this->TuanBuying->find('first',array('conditions' => array('id' => $id)));
         $this->log('data_info'.json_encode($data_info));
         if (empty($data_info)) {
             throw new ForbiddenException(__('该团不存在！'));
         }
         if(!empty($this->data)){
             $this->data['TuanBuying']['id'] = $id;
             $this->autoRender = false;
             if($this->TuanBuying->save($this->data)){
                 $this->Session->setFlash(__('团购状态修改成功',true));
                 $this->redirect(array('controller' => 'tuan','action' => 'admin_tuan_buyings'));
             }
            $this->Session->setFlash(__('The Data could not be saved. Please, try again.'));
         }else{
             $this->data = $data_info;
         }
         $this->set('id',$id);
     }

    /**
     * create new tuan_buying
     */
     public function admin_tuan_buying_create(){
         if(!empty($this->data)){
             $this->data['TuanBuying']['join_num'] = 0;
             $this->data['TuanBuying']['sold_num'] = 0;
             $this->data['TuanBuying']['status'] = 0;
             if($this->TuanBuying->save($this->data)){
                 $this->Session->setFlash(__('创建团购成功',true));
                 $this->redirect(array('controller' => 'tuan','action' => 'admin_tuan_buyings'));
             }else{
                $this->Session->setFlash(__('The Data could not be saved. Please, try again.'));
             }
         }else{
               $this->Session->setFlash(__('The Data could not be null. Please, try again.'));
         }
     }

    /**
     * create new tuan_team
     */
     public function admin_tuan_team_create(){

     }

}