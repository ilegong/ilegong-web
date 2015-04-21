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

        $query_cond['deleted = '] = DELETED_NO;
        if(!empty($start_time)){
            $query_cond['start_time >='] = $start_time;
        }
        if(!empty($end_time)){
            $query_cond['start_time <'] = $end_time;
        }
        if(!empty($tuan_id)){
            $query_cond['tuan_id'] = $tuan_id;
        }
        if(!empty($product_id)){
            $query_cond['product_id'] = $product_id;
        }

        $this->log('query_cond'.json_encode($query_cond));
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

    public function admin_create(){
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
        $this->log('datainfo'.json_encode($data_info));
        $this->set('id',$id);
    }

    public function admin_update($id){
        $this->log('update product_try '.$id.': '.json_encode($this->data));
        $this->autoRender = false;
        $this->data['ProductTry']['id'] = $id;
        if($this->ProductTry->save($this->data)){
            $this->redirect(array('controller' => 'tuanSecKill','action' => 'index'));
        }
        $this->set('id',$id);
    }

    public function admin_delete($id){
        if($this->ProductTry->updateAll(array('deleted'=>1),array('id'=>$id))){
            $this->redirect(array('controller' => 'tuanSecKill','action' => 'index'));
        }
    }
}