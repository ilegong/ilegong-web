<?php

/**
 * Class ShareManageController
 * 分享者管理系统
 */
class ShareManageController extends AppController {


    public $components = array('Auth', 'ShareUtil', 'WeshareBuy', 'ShareManage', 'Cookie', 'Session', 'Paginator', 'WeshareBuy');

    public $uses = array('User', 'Weshare', 'Order', 'Cart');

    var $sortSharePaginate = array(
        'Weshare' => array(
            'order' => 'Weshare.id DESC',
            'limit' => 20,
        )
    );

    var $sortShareOrderPaginate = array(
        'Order' => array(
            'order' => 'Order.id DESC',
            'limit' => 100
        )
    );

    /**
     * 获取分享者的分享
     */
    public function shares() {
        $uid = $this->currentUser['id'];
        $this->Paginator->settings = $this->sortSharePaginate;
        $q_cond = array(
            'Weshare.creator' => $uid
        );
        if ($_REQUEST['key_word']) {
            $q_cond['Weshare.title LIKE'] = '%' . $_REQUEST['key_word'] . '%';
        }
        $shares = $this->Paginator->paginate('Weshare', $q_cond);
        $shares_count = $this->Weshare->find('count', array(
            'conditions' => $q_cond
        ));
        $this->set('shares_count', $shares_count);
        $this->set('shares', $shares);
    }

    public function beforeFilter() {
        $this->Auth->authenticate = array('WeinxinOAuth', 'Form', 'Pys', 'Mobile');
        $this->Auth->allowedActions = array('login', 'forgot', 'reset', 'do_login');
        $this->layout = 'sharer';
        parent::beforeFilter();
    }

    public function index() {
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $this->redirect(array('action' => 'login'));
            return;
        }
        $collect_data = $this->ShareManage->set_dashboard_collect_data($uid);
        $this->set('collect_data', $collect_data);
    }

    public function logout() {
        $this->logoutCurrUser();
        $this->redirect(array('action' => 'login'));
    }

    public function do_login() {
        if ($this->Auth->login()) {
            $this->User->id = $this->Auth->user('id');
            $this->User->updateAll(array(
                'last_login' => "'" . date('Y-m-d H:i:s') . "'",
                'last_ip' => "'" . $this->request->clientIp(false) . "'"
            ), array('id' => $this->User->id,));
            if (!empty($this->data['User']['remember_me'])) {
                $cookietime = 2592000; // 一月内30*24*60*60
            } else {
                $cookietime = 3600 * 24 * 7;
            }
            $user = $this->Auth->user();
            $userinfo = array(
                'id' => $user['id'],
                'username' => $user['username'],
                'nickname' => $user['nickname']
            );
            $this->Cookie->write('Auth.User', $userinfo, true, $cookietime);
            $this->Session->setFlash('登录成功' . $this->Session->read('Auth.User.session_flash'));
            $this->redirect('/share_manage/index');
            return;
        }
        $this->Session->setFlash('登录失败,手机号或者密码错误');
        $this->redirect(array('action' => 'login'));
    }

    public function login() {
        $this->layout = null;
    }

    public function order_manage($share_id) {
        $share_info = $this->WeshareBuy->get_weshare_info($share_id);
        $this->Paginator->settings = $this->sortShareOrderPaginate;
        $q_cond = array(
            'Order.member_id' => $share_id,
            'Order.type' => ORDER_TYPE_WESHARE_BUY,
            'NOT' => array(
                'Order.status' => array(ORDER_STATUS_WAITING_PAY)
            )
        );
        //set other query cond
        $orders = $this->Paginator->paginate('Order', $q_cond);
        $order_ids = Hash::extract($orders, '{n}.Order.id');
        $order_carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $order_ids
            )
        ));
        $order_cart_map = array();
        foreach($order_carts as $cart_item){
            $order_id = $cart_item['Cart']['order_id'];
            if(!isset($order_cart_map[$order_id])){
                $order_cart_map[$order_id] = array();
            }
            $order_cart_map[$order_id][] = $cart_item['Cart'];
        }
        $orders_count = $this->Order->find('count', array(
            'conditions' => $q_cond
        ));
        $this->set('order_cart_map', $order_cart_map);
        $this->set('orders_count', $orders_count);
        $this->set('orders', $orders);
        $this->set('share_info', $share_info);
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

    public function batch_set_order_ship_code() {

    }
}
