<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 3/19/15
 * Time: 20:26
 */

class TuanController extends AppController{

    var $name = 'Tuan';

    var $uses = array('TuanTeam','TuanBuying','Order','Cart','TemplateMsgLog');

    /**
     * query tuan orders
     */
    public function admin_tuan_orders(){
        $this->loadModel('ProductSpecGroup');
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
        $this->set('conn_name',$con_name);
        $this->set('con_phone',$con_phone);
        $this->set('post_time',$post_time);
        $this->set('order_type',$order_type);
    }


    /**
     * 团购功能列表
     */
    public function admin_tuan_func_list(){

    }

    public function admin_api_tuan_products(){
        $this->autoRender=false;
        $results = array('838'=>'草莓', '851' => '芒果', '862'=>'好好蛋糕', '863' => '草莓863', '230' => '蛋糕230','381'=>'牛肉干','868'=>'建平小米','873'=>'好好蛋糕大团','874'=>'海南出口金菠萝','876'=>'蔬菜单次试吃','879'=>'烟台苹果');
        echo json_encode($results);
    }

     public function admin_api_tuan_teams(){
        $this->autoRender=false;
        $teams = $this->TuanTeam->find('all');
        $teams = Hash::extract($teams,'{n}.TuanTeam');
        echo json_encode($teams);
    }

    public function admin_api_tuan_buying_due(){
        $tuanBuyingId = $_REQUEST['id'];
        $this->log('update tuan buying ' + $tuanBuyingId + ' status to ' + 1);
        $this->TuanBuying->updateAll(array('status' => 1), array('id' => $tuanBuyingId));
    }
    public function admin_api_tuan_buying_canceled(){
        $tuanBuyingId = $_REQUEST['id'];
        $this->log('update tuan buying ' + $tuanBuyingId + ' status to ' + 2);
        $this->TuanBuying->updateAll(array('status' => 2), array('id' => $tuanBuyingId));
    }
    public function admin_api_tuan_buying_finished(){
//        $this->autoRender = false;
        $tuanBuyingId = $_REQUEST['id'];
        $this->log('update tuan buying ' + $tuanBuyingId + ' status to ' + 11);
        $this->TuanBuying->updateAll(array('status' => 11), array('id' => $tuanBuyingId));

//        $this->loadModel('Order');
//        $this->Order->updateAll(array('status' => 2),array('member_id' => $tuanBuyingId,'status' => 1));
//        $successinfo = array('success'=>__('团购订单状态已修改为已发货',true));
//        $this->log('successinfo'.json_encode($successinfo));
//        echo json_encode($successinfo);
    }
    public function admin_api_tuan_buying_refunded(){
        $tuanBuyingId = $_REQUEST['id'];
        $this->log('update tuan buying ' + $tuanBuyingId + ' status to ' + 21);
        $this->TuanBuying->updateAll(array('status' => 21), array('id' => $tuanBuyingId));
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
         if(!empty($time_type)&&$time_type!=-1){
         if($time_type==0){
             $con['end_time']=$post_time;
         }else if($time_type==1){
             $con['consign_time']=$post_time;
         }
         }
         if(!empty($product_id)&&$product_id!=-1){
             $con['pid'] = $product_id;
         }
         if($tuan_type!=-1){
             $con['status'] = $tuan_type==null?0:$tuan_type;
         }
         $this->log('con'.json_encode($con));
         if(!empty($con)){
            $tuan_buyings = $this->TuanBuying->find('all',array(
                'conditions' => $con
            ));
         }else{
            $tuan_buyings = $this->TuanBuying->find('all',array('conditions' => array('pid !=' => null,'status' => array(0,1,2))));
         }
         $this->log('tuan_buyings'.json_encode($tuan_buyings));
         $tuan_ids = Hash::extract($tuan_buyings,'{n}.TuanBuying.tuan_id');
         $tuan_teams = $this->TuanTeam->find('all', array('conditions' => array('id' => $tuan_ids), 'fields' => array('id', 'tuan_name')));
         $tuan_teams = Hash::combine($tuan_teams, '{n}.TuanTeam.id', '{n}.TuanTeam');
         $this->log('tuan_team'.json_encode($tuan_teams));
         $tuan_products = array('838'=>'草莓', '851' => '芒果', '862'=>'好好蛋糕', '863' => '草莓863', '230' => '蛋糕230','381'=>'牛肉干','868'=>'建平小米');
         foreach($tuan_buyings as &$tuan_buying){
             $tuanBuying = $tuan_buying['TuanBuying'];
             $tb_id = $tuanBuying['id'];
             $tuan_id = $tuanBuying['tuan_id'];
             $tuan_buying['create_msg_status'] = $this->get_tb_msg_status(TUAN_CREATE_MSG,$tb_id);
             $tuan_buying['cancel_msg_status'] = $this->get_tb_msg_status(TUAN_CANCEL_MSG,$tb_id);
             $tuan_buying['complete_msg_status'] = $this->get_tb_msg_status(TUAN_COMPLETE_MSG,$tb_id);
             $tuan_buying['tip_msg_status'] = $this->get_tb_msg_status(TUAN_TIP_MSG,$tb_id);
             $tuan_buying['tuan_team'] = $tuan_teams[$tuan_id];
             $tuan_buying['tuan_product'] = $tuan_products[$tuanBuying['pid']];
         }
         $this->set('tuan_buyings', $tuan_buyings);
         $this->set('team_id',$team_id);
         $this->set('product_id',$product_id);
         $this->set('time_type',$time_type);
         $this->set('tuan_type',$tuan_type);
         $this->set('post_time',$post_time);

     }

    private function get_tb_msg_status($type,$tb_id){
        $cond = array(
            'flag' => $tb_id,
            'type' => $type
        );
        if($type==TUAN_CREATE_MSG){
            $tml = $this->TemplateMsgLog->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '已发建团消息';
            }else{
                return 'true';
            }
        }
        if($type==TUAN_CANCEL_MSG){
            $tml = $this->TemplateMsgLog->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '已发团取消消息';
            }else{
                return 'true';
            }

        }
        if($type==TUAN_COMPLETE_MSG){
            $tml = $this->TemplateMsgLog->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '已发团完成消息';
            }else{
                return 'true';
            }

        }
        if($type==TUAN_TIP_MSG){
            $cond['date(send_date)']=date(FORMAT_DATE);
            $tml = $this->TemplateMsgLog->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '今天已发提示消息';
            }else{
                return 'true';
            }
        }
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
//                 $res [$tuan_buying_id] = array('success' => __('团购状态修改成功.', true));
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
                 $successinfo = array('success'=>__('Edit success',true));
                 $this->redirect(array('controller' => 'tuan','action' => 'admin_tuan_buyings'));
             }else{
             $successinfo = array('error'=>__('Edit error',true));
             }
         }else{
             $this->data = $data_info;
             $this->log('data'.json_encode($this->data));
         }
         $this->set('id',$id);
//         echo json_encode($successinfo);
     }

    /**
     * show new tuan_buying
     */
     public function admin_tuan_buying_new(){

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
                 $successinfo = array('success'=>__('create success',true));
                 $this->redirect(array('controller' => 'tuan','action' => 'admin_tuan_buyings'));
             }else{
                 $successinfo = array('error'=>__('create error',true));
             }
         }else{
             $successinfo = array('success'=>__('the data can not be null',true));
         }
//         echo json_encode($successinfo);
     }
    /**
     * show all tuan_team
     */
    public function admin_tuan_teams(){
        $team_id = $_REQUEST['team_id'];
        $con = array();
        if(!empty($team_id)&&$team_id!='-1'){
            $con['id']=$team_id;
        }
        $this->log('con'.json_encode($con));
        if(!empty($con)){
            $tuan_teams = $this->TuanTeam->find('all',array(
                'conditions' => $con
            ));}else{
            $tuan_teams = $this->TuanTeam->find('all');
        }
        $this->set('tuan_teams',$tuan_teams);
        $this->set('team_id',$team_id);
    }
    /**
     * create a new tuan_team
     */
     public function admin_tuan_team_create(){

         if(!empty($this->data)){
             if($this->TuanTeam->save($this->data)){
                 $successinfo = array('success'=>__('create success',true));
                 $this->redirect(array('controller' => 'tuan','action' => 'admin_tuan_teams'));
             }else{
                 $successinfo = array('error'=>__('create error',true));
             }
         }else{
             $successinfo = array('success'=>__('the data can not be null',true));
         }
//         echo json_encode($successinfo);
     }

    /**
     * edit a tuan_team
     */
     public function admin_tuan_team_edit($id){

         $data_Info = $this->TuanTeam->find('first',array('conditions' => array('id' => $id)));
         $this->log('data_info'.json_encode($data_Info));
         if (empty($data_Info)) {
             throw new ForbiddenException(__('该团队不存在！'));
         }
         if(!empty($this->data)){
             $this->data['TuanTeam']['id'] = $id;
             $this->autoRender = false;
             if($this->TuanTeam->save($this->data)){
                 $successinfo = array('success'=>__('edit success',true));
                 $this->redirect(array('controller' => 'tuan','action' => 'admin_tuan_teams'));
             }
             $successinfo = array('error'=>__('edit error',true));
         }else{
             $this->data = $data_Info;
         }
         $this->set('id',$id);
//         echo json_encode($successinfo);
     }

}