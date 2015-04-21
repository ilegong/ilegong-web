<?php

class BrandsController extends AppController {

    var $name = 'Brands';
    var $uses = array('Brand');

    public function admin_index() {
        $brands = $this->Brand->find('all',
            array('conditions'=>array(
                'deleted'=> DELETED_NO,
            ))
        );
        $this->set('brands', $brands);
    }
}
?>