<?php

/**
 * Class ShareManageController
 * 分享者管理系统
 */
class ShareManageController extends AppController {


    public $components = array('ShareUtil', 'WeshareBuy', 'ShareManage');

    public $uses = array('User');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->authenticate = array('Mobile');
        $this->Auth->allowedActions = array('login', 'forgot', 'captcha', 'reset');
        $this->set('op_cate', 'me');
        $this->layout = 'sharer';
    }

    public function index() {

    }

    public function logout(){
        $this->logoutCurrUser();
        $this->redirect(array('action' => 'login'));
    }

    public function do_login(){
        if ($this->Auth->login()) {
            $this->User->id = $this->Auth->user('id');
            $this->User->updateAll(array(
                'last_login' => "'" . date('Y-m-d H:i:s') . "'",
                'last_ip' => "'" . $this->request->clientIp(false) . "'"
            ), array('id' => $this->User->id,));
            $this->Session->setFlash('登录成功' . $this->Session->read('Auth.User.session_flash'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash('登录失败,手机号或者密码错误');
        $this->redirect(array('action' => 'login'));
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
            $this->set('share_id', $share_id);
        }
    }

    public function batch_set_order_ship_code(){

    }
}
