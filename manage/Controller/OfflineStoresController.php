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
}