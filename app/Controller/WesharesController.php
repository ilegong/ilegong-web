<?php
class WesharesController extends AppController {

    var $uses = array();

    public function beforeFilter(){
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    public function add() {
    }

    public function create(){
    }
}