<?php

/**
 * Class ShareManageController
 * 分享者管理系统
 */
class ShareManageController extends AppController {


    public $components = array('ShareUti', 'WeshareBuy', 'ShareManage');

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
        if (!empty($share_id)) {
            $orders = $this->ShareManage->get_share_orders($share_id);
            $user_ids = Hash::extract($orders, '{n}.Order.creator');
            $user_data = $this->ShareManage->get_users_data($user_ids);
            $user_data = Hash::combine($user_data, '{n}.User.id', '{n}.User');
            $share_data = $this->WeshareBuy->get_weshare_info($share_id);
            $this->set('orders', $orders);
            $this->set('user_data', $user_data);
            $this->set('share_data', $share_data);
        }
    }


}
