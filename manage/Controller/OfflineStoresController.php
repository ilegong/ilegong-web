<?php
class OfflineStoresController extends AppController{

    var $name = 'OfflineStores';

    var $uses = array('OfflineStore', 'Location');

    public function admin_index(){
        $offline_stores = $this->OfflineStore->find('all', array(
            'conditions' => array(
                'deleted' => 0
            )
        ));

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