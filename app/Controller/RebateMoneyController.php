<?php

class RebateMoneyController extends AppController{

    var $uses = ['RebateLog', 'Order'];

    var $components = ['ShareUtil'];

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    public function rules(){
    }

    public function detail(){
        $this->loadModel('User');
        $uid = $this->currentUser['id'];
        $totalRebateMoney = $this->User->get_rebate_money($uid, true);
        $this->set('totalRebateMoney', get_format_number($totalRebateMoney/100));
    }

    public function rebate_list($page){
        $limit = 10;
        $uid = $this->currentUser['id'];
        $result = $this->ShareUtil->get_rebate_list($uid, $page, $limit);
        echo json_encode($result);
        exit;
    }

}