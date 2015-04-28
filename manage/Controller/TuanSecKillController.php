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

        $query_cond['deleted'] = DELETED_NO;
        if(!empty($start_time)){
            $query_cond['start_time >='] = $start_time;
        }
        if(!empty($end_time)){
            $query_cond['start_time <'] = $end_time;
        }
//        if(!empty($tuan_id)){
//            $query_cond['tuan_id'] = $tuan_id;
//        }
        if(!empty($product_id)){
            $query_cond['product_id'] = $product_id;
        }

        $this->log('query_cond'.json_encode($query_cond));
        $this->loadModel('ProductTuanTry');
        if(empty($tuan_id)){
        $datas = $this->ProductTry->find('all',array(
            'conditions' => $query_cond,
            'order' => 'start_time DESC'

        ));
        }else{
        $tryInfo = $this->ProductTuanTry->find('all',array('conditions' => array('team_id'=> $tuan_id)));
        $tryIds = Hash::extract($tryInfo,'{n}.ProductTuanTry.try_id');
            $query_cond['id'] = $tryIds;
        $datas = $this->ProductTry->find('all',array(
            'conditions' => $query_cond,
            'order' => 'start_time DESC'
        ));
        }

        foreach($datas as &$data){
            $ProductTuanTryInfo = $this->ProductTuanTry->find('all',array('conditions' => array('try_id' => $data['ProductTry']['id'])));
            $TuanTryIds = Hash::extract($ProductTuanTryInfo,'{n}.ProductTuanTry.team_id');
            $TuanTryId=implode(',',$TuanTryIds);
            $data['team_ids'] = $TuanTryId;
        }
        $this->set('datas',$datas);
        $this->set('tuan_id',$tuan_id);
        $this->set('product_id',$product_id);
        $this->set('start_time',$start_time);
        $this->set('end_time',$end_time);
    }

    public function admin_new(){
    }

    public function admin_create(){
        $tuanTeamIds = trim($_REQUEST['team_ids']);
        $this->log('tuanTeamIds'.$tuanTeamIds);
        $tuanTeamIds = $tuanTeamIds == ''?null:explode(',',$tuanTeamIds);
        $ProductTuanTryM = ClassRegistry::init('ProductTuanTry');
        $ProductTryM = ClassRegistry::init('ProductTry');
        if(!empty($this->data)){
            $this->data['ProductTry']['global_show'] = empty($tuanTeamIds)?1:0;
            $ProductTryM->save($this->data);
            if(!empty($tuanTeamIds)){
            $tryId = $ProductTryM->getLastInsertId();
            foreach($tuanTeamIds as $tuanTeamId){
            $this->data['ProductTuanTry']['try_id'] = $tryId;
            $this->data['ProductTuanTry']['team_id'] = $tuanTeamId;
            $ProductTuanTryM->create();
            $ProductTuanTryM->save($this->data);
         };
        }
        } $this->redirect(array('controller'=>'tuanSecKill','action'=>'index'));
    }

    public function admin_edit($id){
        $data_info = $this->ProductTry->find('first',array('conditions' => array('id' => $id)));
        if (empty($data_info)) {
            throw new ForbiddenException(__('该秒杀不存在！'));
        }
        $this->loadModel('ProductTuanTries');
        $teamIds = $this->ProductTuanTries->find('all',array(
            'conditions' =>array('try_id' => $id)
        ));
        $teamIds = implode(',', Hash::extract($teamIds,'{n}.ProductTuanTries.team_id'));
        $this->set('teamIds',$teamIds);
        $this->data = $data_info;
        $this->log('datainfo'.json_encode($data_info));
        $this->set('id',$id);
    }

    public function admin_update($id){
        $this->log('update product_try '.$id.': '.json_encode($this->data));
        $this->autoRender = false;
//        $teamIds = explode(',',$_REQUEST['team_ids']);
//        $this->loadModel('ProductTuanTries');
//        $this->ProductTuanTry->delete($id);
//        $this->ProductTuanTries->save();
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
    public function admin_api_tuan_seckills(){
        $this->autoRender=false;

        $tuan_seckills = $this->ProductTry->find('all',array(
            'conditions' => array(
                'deleted' => DELETED_NO
            )
        ));
        echo json_encode($tuan_seckills);
    }
}