<?php

class SharerController extends AppController{

    public $components = array('WeshareBuy');

    public function beforeFilter() {
        parent::beforeFilter();
    }

    public function index(){
        $this->pageTitle = '导出分享订单';
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $this->__message('您需要先登录才能操作', '/users/login?referer=' . urlencode($_SERVER['REQUEST_URI']));
            return false;
        }
        $weshares = $this->WeshareBuy->get_user_weshares($uid);
        $this->set('weshares', $weshares);
    }

}