<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 4/9/15
 * Time: 11:23
 */

class TuanBuyingsController extends AppController{

    var $name = 'TuanBuyings';

    var $uses = array('TuanTeam','TuanBuying','Order','Cart','TuanBuyingMessages','TuanProduct');

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
        $tuanBuyingId = $_REQUEST['id'];
        $this->log('update tuan buying ' + $tuanBuyingId + ' status to ' + 11);
        $this->TuanBuying->updateAll(array('status' => 11), array('id' => $tuanBuyingId));
    }
    public function admin_api_tuan_buying_refunded(){
        $tuanBuyingId = $_REQUEST['id'];
        $this->log('update tuan buying ' + $tuanBuyingId + ' status to ' + 21);
        $this->TuanBuying->updateAll(array('status' => 21), array('id' => $tuanBuyingId));
    }
    /**
     * show all tuan_buyings
     */
    public function admin_index(){
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
        $tuan_teams = $this->TuanTeam->find('all', array('conditions' => array('id' => $tuan_ids), 'fields' => array('id', 'tuan_name','type','address')));
        $tuan_teams = Hash::combine($tuan_teams, '{n}.TuanTeam.id', '{n}.TuanTeam');
        $this->log('tuan_team'.json_encode($tuan_teams));
        $tuan_products = getTuanProducts();
        $tuan_products = Hash::combine($tuan_products,'{n}.TuanProduct.product_id','{n}.TuanProduct');
        foreach($tuan_buyings as &$tuan_buying){
            $tuanBuying = $tuan_buying['TuanBuying'];
            $tb_id = $tuanBuying['id'];
            $tuan_id = $tuanBuying['tuan_id'];
            $tuan_buying['create_msg_status'] = $this->get_tb_msg_status(TUAN_CREATE_MSG,$tb_id);
            $tuan_buying['cancel_msg_status'] = $this->get_tb_msg_status(TUAN_CANCEL_MSG,$tb_id);
            $tuan_buying['complete_msg_status'] = $this->get_tb_msg_status(TUAN_COMPLETE_MSG,$tb_id);
            $tuan_buying['tip_msg_status'] = $this->get_tb_msg_status(TUAN_TIP_MSG,$tb_id);
            $tuan_buying['start_deliver_msg_status'] = $this->get_tb_msg_status(TUAN_STARTDELIVER_MSG,$tb_id);
            $tuan_buying['notify_deliver_msg_status'] = $this->get_tb_msg_status(TUAN_NOTIFYDELIVER_MSG,$tb_id);
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

    /**
     * tuan msg has send
     * @param $type
     * @param $tb_id
     * @return string
     */
    private function get_tb_msg_status($type,$tb_id){
        $cond = array(
            'flag' => $tb_id,
            'type' => $type
        );
        if($type==TUAN_CREATE_MSG){
            $tml = $this->TuanBuyingMessages->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '已发建团消息';
            }else{
                return 'true';
            }
        }
        if($type==TUAN_CANCEL_MSG){
            $tml = $this->TuanBuyingMessages->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '已发团取消消息';
            }else{
                return 'true';
            }

        }
        if($type==TUAN_COMPLETE_MSG){
            $tml = $this->TuanBuyingMessages->find('first',array(
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
            $tml = $this->TuanBuyingMessages->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '今天已发提示消息';
            }else{
                return 'true';
            }
        }
        if($type==TUAN_STARTDELIVER_MSG){
            $tml = $this->TuanBuyingMessages->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '已发配送消息';
            }else{
                return 'true';
            }
        }
        if($type==TUAN_NOTIFYDELIVER_MSG){
            $tml = $this->TuanBuyingMessages->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '已发到货消息';
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
            $this->TuanBuying->updateAll(array('status' => $val),array('id' => $id));
            $this->log('status'.json_encode($res));
            echo json_encode($res);
        }
    }

    public function admin_edit($id){
        $data_info = $this->TuanBuying->find('first',array('conditions' => array('id' => $id)));
        $this->log('data_info'.json_encode($data_info));
        if (empty($data_info)) {
            throw new ForbiddenException(__('该团购不存在！'));
        }
        $this->data = $data_info;
        $this->set('id',$id);
    }

    public function admin_update($id){
        $this->log('update tuan buying '.$id.': '.json_encode($this->data));
        $this->autoRender = false;
        if($this->TuanBuying->save($this->data)){
            $this->redirect(array('controller' => 'tuan_buyings','action' => 'index'));
        }
        $this->set('id',$id);
    }

    public function admin_new(){
    }

    public function admin_create(){
        if(!empty($this->data)){
            $this->data['TuanBuying']['join_num'] = 0;
            $this->data['TuanBuying']['sold_num'] = 0;
            $this->data['TuanBuying']['status'] = 0;
            if($this->TuanBuying->save($this->data)){
                $this->redirect(array('controller' => 'tuan_buyings','action' => 'index'));
            }
        }
    }
}