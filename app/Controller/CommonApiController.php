<?php

class CommonApiController extends AppController{

    public function beforeFilter(){
        parent::beforeFilter();
        $this->autoRender = false;
    }

    public function get_ship_type_list(){
        $list = ShipAddress::ship_type_list();
        echo json_encode($list);
        return;
    }

}