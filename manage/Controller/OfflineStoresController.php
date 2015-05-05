<?php
class OfflineStoresController extends AppController{

    var $name = 'OfflineStores';

    var $uses = array('OfflineStore');

    public function admin_index(){
        $offline_stores = $this->OfflineScore->find('all', array(
            'conditions' => array(
                'published' => PUBLISH_YES
            )
        ));

        $this->set('offline_stores',$offline_stores);
    }
}