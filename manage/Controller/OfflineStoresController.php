<?php
class OfflineStoresController extends AppController{

    var $name = 'OfflineStores';

    var $uses = array('OfflineStore', 'Location', 'TuanTeam');

    public function admin_index(){

        $area_id = isset($_REQUEST['area_id'])?$_REQUEST['area_id']:-1;
        $type = isset($_REQUEST['type'])?$_REQUEST['type']:-1;
        $deleted =  isset($_REQUEST['deleted'])?$_REQUEST['deleted']:false;
        $con = array();
        if($area_id != -1){
            $con['area_id'] = $area_id;
        }
        if($type != -1){
            $con['type'] = $type;
        }
        $con['deleted'] = $deleted == false?0:1;
        $offline_stores = $this->OfflineStore->find('all', array(
            'conditions' => $con
        ));
        $tuan_teams_count = $this->TuanTeam->query('select offline_store_id as id, count(offline_store_id) as c from cake_tuan_teams WHERE published = 1 group by offline_store_id;');
        $tuan_teams_count = Hash::combine($tuan_teams_count, '{n}.cake_tuan_teams.id', '{n}.0.c');
        foreach($offline_stores as &$offline_store){
            $tuan_team_count = $tuan_teams_count[$offline_store['OfflineStore']['id']];
            if(!isset($tuan_team_count) || is_null($tuan_team_count)){
                $tuan_team_count = 0;
            }

            $offline_store['OfflineStore']['tuan_team_count'] = $tuan_team_count;
        }

        $beijing_id = 110100;
        $locations = $this->Location->find('all', array(
            'conditions' => array(
                'parent_id' => $beijing_id,
            )
        ));
        $locations = Hash::combine($locations, '{n}.Location.id', '{n}');

        $this->set('offline_stores',$offline_stores);
        $this->set('locations',$locations);
        $this->set('area_id',$area_id);
        $this->set('type',$type);
    }

    public function admin_new(){
    }

    public function admin_create(){
        if($this->data['OfflineStore']['type'] == 0 && $this->data['OfflineStore']['shop_no'] == 0){
            throw new ForbiddenException(__('好邻居自提点请输入店号!'));
        }
        $this->log('shop no: '.$this->data['OfflineStore']['shop_no'].', != 0: '.$this->data['OfflineStore']['shop_no'] != 0);
        if($this->data['OfflineStore']['type'] == 1){
            $this->data['OfflineStore']['shop_no'] = 0;
        }
        $this->log('create offline store: '.json_encode($this->data));

        if($this->OfflineStore->save($this->data)){
            $this->redirect(array('controller' => 'offline_stores','action' => 'index'));
        }
    }

    public function admin_edit($id){
        $offline_store = $this->OfflineStore->find('first',array('conditions' => array('id' => $id)));

        if (empty($offline_store)) {
            throw new ForbiddenException(__('该自提点不存在！'));
        }
        $this->set('offline_store',$offline_store);
    }

    public function admin_update($id){
        if($this->data['OfflineStore']['type'] == 0 && $this->data['OfflineStore']['shop_no'] == 0){
            throw new ForbiddenException(__('好邻居自提点请输入店号!'));
        }
        $this->log('shop no: '.$this->data['OfflineStore']['shop_no']);
        if($this->data['OfflineStore']['type'] == 1 && $this->data['OfflineStore']['shop_no'] != 0){
            $this->data['OfflineStore']['shop_no'] = 0;
        }
        $this->log('update offline store '.$id.': '.json_encode($this->data));
        if($this->data['OfflineStore']['area_id'] != 110114){
            $this->data['OfflineStore']['child_area_id'] = null;
        }
        $this->autoRender = false;
        if($this->OfflineStore->save($this->data)){
            $this->redirect(array('controller' => 'offline_stores','action' => 'index'));
        }
        $this->set('id',$id);
    }

    public function admin_api_offline_stores(){
        $this->autoRender=false;
        $offline_stores = $this->OfflineStore->find('all', array(
            'order' => 'name'
        ));
//        $this->log("offline stores: ".json_encode($offline_stores));
        $offline_stores= Hash::combine($offline_stores , "{n}.OfflineStore.id", "{n}.OfflineStore", "{n}.OfflineStore.type");
        echo json_encode($offline_stores);
    }
}