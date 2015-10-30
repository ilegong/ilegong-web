<?php

/**
 * Class ShareManageController
 * 分享者管理系统
 */
class ShareManageController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'sharer';
    }

    public function index() {

    }

    public function login() {
        $this->layout = null;
    }

    public function share_order() {
        $share_id = $_REQUEST['share_id'];
    }
}
