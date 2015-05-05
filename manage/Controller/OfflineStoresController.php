<?php
class OfflineStoresController extends AppController{

    var $name = 'OfflineStores';

    var $uses = array('OfflineStore', 'Location', 'TuanTeam');

    public function admin_index(){
        $offline_stores = $this->OfflineStore->find('all', array(
            'conditions' => array(
                'deleted' => 0
            )
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
    }

    public function admin_edit($id){
        $offline_store = $this->OfflineStore->find('first',array('conditions' => array('id' => $id)));

        if (empty($offline_store)) {
            throw new ForbiddenException(__('该自提点不存在！'));
        }
        $this->set('offline_store',$offline_store);
    }

    public function admin_update($id){
        $this->log('update offline store '.$id.': '.json_encode($this->data));
        $this->autoRender = false;
        if($this->OfflineStore->save($this->data)){
            $this->redirect(array('controller' => 'offline_stores','action' => 'index'));
        }
        $this->set('id',$id);
    }
}