<?php

class RebateMoneyController extends AppController{

    var $uses = ['RebateLog', 'Order'];

    public function beforeFilter()
    {
        parent::beforeFilter();
        if (empty($this->currentUser['id']) || ($this->is_weixin() && name_empty_or_weixin($this->currentUser['nickname']))) {
            $ref = Router::url($_SERVER['REQUEST_URI']);
            $this->redirect('/users/login.html?force_login=1&auto_weixin=' . $this->is_weixin() . '&referer=' . urlencode($ref));
        }
        $this->layout = 'weshare';
    }

    public function rules(){
        $this->set('title', '余额常见问题');
    }

    public function detail(){

    }

}