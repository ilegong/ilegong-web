<?php

class ShareManageController extends AppController{

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'sharer';
    }

    public function index(){

    }

    public function login(){
        $this->layout = null;
    }
}
