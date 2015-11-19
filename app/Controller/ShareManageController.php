<?php

/**
 * Class ShareManageController
 * 分享者管理系统
 */
class ShareManageController extends AppController {


    public $components = array('Auth', 'ShareUtil', 'WeshareBuy', 'ShareManage', 'Cookie', 'Session', 'Paginator', 'WeshareBuy', 'ShareAuthority');

    public $uses = array('User', 'Weshare', 'Order', 'Cart', 'WeshareProduct');

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
     * 查询用户
     */
    public function search_users() {
        $u_mobile = $_REQUEST['mobile'];
        $u_nickname = $_REQUEST['nick_name'];
        if (!empty($u_mobile) || !empty($u_nickname)) {
            $userM = ClassRegistry::init('User');
            $cond = array();
            if (!empty($u_nickname)) {
                $cond['User.nickname LIKE'] = '%' . $u_nickname . '%';
            }
            if (!empty($u_mobile)) {
                $cond['User.mobilephone'] = $u_mobile;
            }
            $users = $userM->find('all', array(
                'conditions' => $cond,
                'recursive' => 1,
                'fields' => array('User.id', 'User.nickname', 'User.image'),
                'limit' => 100
            ));
            $this->set('users', $users);
        }
    }

    /**
     * 查询分享
     */
    public function search_shares() {
        $s_title = $_REQUEST['title'];
        if (!empty($s_title)) {
            $WeshareM = ClassRegistry::init('Weshare');
            $cond = array('title LIKE' => '%' . $s_title . '%');
            $weshares = $WeshareM->find('all', array(
                'conditions' => $cond,
                'limit' => 100
            ));
            $this->set('weshares', $weshares);
        }
    }

    /**
     * 更新分享信息
     */
    public function update_share() {
        $this->autoRender = false;
        $json_data = $_REQUEST['data'];
        $share_data = json_decode($json_data, true);
        $this->Weshare->save($share_data);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 更新返利设置
     */
    public function update_share_rebate_setting() {
        $this->autoRender = false;
        $json_data = $_REQUEST['data'];
        $data = json_decode($json_data, true);
        $proxyRebatePercent = ClassRegistry::init('ProxyRebatePercent');
        $proxyRebatePercent->save($data);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 更新分享的快递设置
     */
    public function update_share_ship_setting() {
        $this->autoRender = false;
        $json_data = $_POST['data'];
        $data = json_decode($json_data, true);
        $weshareShipSettingM = ClassRegistry::init('WeshareShipSetting');
        $weshareAddressM = ClassRegistry::init('WeshareAddress');
        $weshareShipSettingM->saveAll($data['ship_setting']);
        $weshareAddressM->saveAll($data['weshare_address']);
        echo json_encode(array('success' => true));
        return;
    }

    public function update_share_product() {
        $this->autoRender = false;
        $json_data = $_REQUEST['data'];
        $share_product_data = json_decode($json_data, true);
        $this->WeshareProduct->saveAll($share_product_data);
        echo json_encode(array('success' => true));
        return;
    }

    public function delete_share($shareId) {
        $this->Weshare->delete($shareId);
        $this->redirect(array('action' => 'shares'));
    }

    /**
     * 获取分享者的分享
     */
    public function shares() {
        $uid = $this->currentUser['id'];
        $this->Paginator->settings = $this->sortSharePaginate;
        $q_cond = array(
            'Weshare.creator' => $uid,
            'Weshare.status' => array(0, 1)
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

    public function share_edit($share_id) {
        $uid = $this->currentUser['id'];
        $weshareData = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $share_id
            )
        ));
        if ($weshareData['Weshare']['creator'] != $uid) {
            if (!$this->ShareAuthority->user_can_edit_share_info($uid, $share_id)) {
                $this->redirect(array('action' => 'shares'));
                return;
            }
        }
        $weshare_products = $this->ShareManage->get_weshare_products($share_id);
        $this->set('weshare_products', $weshare_products);
        $share_tags = $this->ShareManage->get_share_product_tags($weshareData['Weshare']['creator']);
        $this->set('weshare_tags', $share_tags);
        $weshare_ship_settings = $this->ShareManage->get_weshare_ship_settings($share_id);
        $weshare_addresses = $this->ShareManage->get_weshare_addresses($share_id);
        //拼团
        $offline_address_ship_set = $this->ShareUtil->read_share_ship_option_setting($weshareData['Weshare']['creator'], SHARE_SHIP_OPTION_OFFLINE_ADDRESS);
        if ($offline_address_ship_set == PUBLISH_YES) {
            $this->set('can_use_offline_address', true);
        }
        //好邻居
        $offline_store_ship_set = $this->ShareUtil->read_share_ship_option_setting($weshareData['Weshare']['creator'], SHARE_SHIP_OPTION_OFFLINE_STORE);
        $this->set('offline_store_ship_set', $offline_store_ship_set);
        $this->set('weshare_ship_settings', $weshare_ship_settings);
        $this->set('weshare_addresses', $weshare_addresses);
        //rebate set
        $share_rebate_set = $this->ShareManage->get_weshare_rebate_setting($share_id);
        $this->set('share_rebate_set', $share_rebate_set);
        $this->data = $weshareData;
    }

    public function authorize_shares() {
        $uid = $this->currentUser['id'];
        $q_cond = array(
            'user' => $uid,
            'scope_type' => SHARE_OPERATE_SCOPE_TYPE,
            'deleted' => DELETED_NO
        );
        $shareOperateSettingM = ClassRegistry::init('ShareOperateSetting');
        $share_operate_settings = $shareOperateSettingM->find('all', array(
            'conditions' => $q_cond,
            'order' => array('id' => 'desc'),
            'limit' => 300
        ));
        $share_ids = Hash::extract($share_operate_settings, '{n}.ShareOperateSetting.data_id');
        $share_ids = array_unique($share_ids);
        if (count($share_ids) > 0) {
            $weshareM = ClassRegistry::init('Weshare');
            $shares = $weshareM->find('all', array(
                'conditions' => array(
                    'id' => $share_ids
                ),
                'order' => array('id' => 'desc')
            ));
            $this->set('shares', $shares);
            $share_operate_settings_result = array();
            foreach ($share_operate_settings as $share_operate_setting) {
                $share_operate_settings_result[] = $share_operate_setting['ShareOperateSetting']['data_id'] . '-' . $share_operate_setting['ShareOperateSetting']['data_type'];
            }
            $this->set('share_operate_settings', $share_operate_settings_result);
        }
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
        if (!empty($_REQUEST['order_ship_type']) && $_REQUEST['order_ship_type'] != 0) {
            $q_cond['Order.ship_mark'] = $_REQUEST['order_ship_type'];
        }
        if (!empty($_REQUEST['order_status']) && $_REQUEST['order_status'] != 0) {
            $q_cond['Order.status'] = $_REQUEST['order_status'];
        }
        if (!empty($_REQUEST['consignee_name'])) {
            $q_cond['Order.consignee_name LIKE'] = '%' . $_REQUEST['consignee_name'] . '%';
        }
        if (!empty($_REQUEST['consignee_mobilephone'])) {
            $q_cond['Order.consignee_mobilephone'] = $_REQUEST['consignee_mobilephone'];
        }
        $orders_count = $this->Order->find('count', array(
            'conditions' => $q_cond
        ));
        $order_cart_map = array();
        $orders = array();
        if ($orders_count > 0) {
            $orders = $this->Paginator->paginate('Order', $q_cond);
            $order_ids = Hash::extract($orders, '{n}.Order.id');
            $order_carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $order_ids
                )
            ));
            foreach ($order_carts as $cart_item) {
                $order_id = $cart_item['Cart']['order_id'];
                if (!isset($order_cart_map[$order_id])) {
                    $order_cart_map[$order_id] = array();
                }
                $order_cart_map[$order_id][] = $cart_item['Cart'];
            }
        }
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
