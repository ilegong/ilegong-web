<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 4/18/15
 * Time: 17:03
 */
class TuanSecKillController extends AppController{

    var $name = 'TuanSecKill';

    var $uses = array('ProductTry','Product');

    public function admin_index(){
        $query_cond = array();
        $tuan_id = $_REQUEST['tuan_id'];
        $product_id = $_REQUEST['product_id'];
        $start_time = $_REQUEST['start_time'];
        $end_time = $_REQUEST['end_time'];
        if(empty($start_time)){
            $start_time = date('Y-m-d H:i',strtotime('-7 days'));
        }
        if(empty($end_time)){
            $end_time = date('Y-m-d H:i',strtotime('+1 hours'));
        }
        if(!empty($tuan_id)){
            $query_cond['tuan_id'] = $tuan_id;
        }
        if(!empty($product_id)){
            $query_cond['product_id'] = $product_id;
        }

        $query_cond['start_time >='] = $start_time;

        $query_cond['start_time <'] = $end_time;

        $datas = $this->ProductTry->find('all',array(
            'conditions' => $query_cond,
            'order' => 'start_time DESC',
        ));
        $this->set('datas',$datas);
        $this->set('tuan_id',$tuan_id);
        $this->set('product_id',$product_id);
        $this->set('start_time',$start_time);
        $this->set('end_time',$end_time);
    }

    public function admin_new(){
    }

    public function admin_add(){
        if($this->ProductTry->save($this->data)){
            $this->redirect(array('controller'=>'tuanSecKill','action'=>'index'));
        }
    }

    public function admin_edit($id){
        $data_info = $this->ProductTry->find('first',array('conditions' => array('id' => $id)));
        if (empty($data_info)) {
            throw new ForbiddenException(__('该秒杀不存在！'));
        }
        $this->data = $data_info;
        $this->set('id',$id);
    }

    public function admin_update($id){
        $this->autoRender = false;
        if($this->ProductTry->save($this->data)){
            $this->redirect(array('controller' => 'tuanSecKill','action' => 'index'));
        }
        $this->set('id',$id);
    }

    public function admin_delete($id){
        if($this->ProductTry->updateAll(array('status'=>0),array('id'=>$id))){
            $this->redirect(array('controller' => 'tuanSecKill','action' => 'index'));
        }
    }

}