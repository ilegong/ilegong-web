<?php

class BrandsController extends AppController {

    var $name = 'Brands';
    var $uses = array('Brand');

    public function admin_index() {
        $brands = $this->Brand->find('all',
            array('conditions'=>array(
            ))
        );
        $this->set('brands', $brands);
    }
}
?>