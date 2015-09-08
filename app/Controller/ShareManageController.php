<?php

/**
 * Class ShareManageController
 * 分享者管理系统
 */
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
