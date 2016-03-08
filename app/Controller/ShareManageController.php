<?php

/**
 * Class ShareManageController
 * 分享者管理系统
 */
class ShareManageController extends AppController
{


    public $components = array('Auth', 'ShareUtil', 'WeshareBuy', 'ShareManage', 'Cookie', 'Session', 'Paginator', 'WeshareBuy', 'ShareAuthority');

    public $uses = array('User', 'Weshare', 'Order', 'Cart', 'WeshareProduct', 'UserLevel', 'ShareOperateSetting', 'WeshareProductTag');

    var $operateDataTypeNameMap = array(SHARE_ORDER_OPERATE_TYPE => '查看订单权限', SHARE_TAG_ORDER_OPERATE_TYPE => '查看分组订单权限');

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
    public function search_users()
    {
        $u_mobile = $_REQUEST['mobile'];
        $u_nickname = $_REQUEST['nick_name'];
        $u_id = $_REQUEST['uid'];
        if (!empty($u_mobile) || !empty($u_nickname)|| !empty($u_id)) {
            $userM = ClassRegistry::init('User');
            $cond = array();
            if (!empty($u_nickname)) {
                $cond['User.nickname LIKE'] = '%' . $u_nickname . '%';
            }
            if (!empty($u_mobile)) {
                $cond['User.mobilephone'] = $u_mobile;
            }
            if (!empty($u_id)) {
                $cond['User.id'] = $u_id;
            }
            $users = $userM->find('all', array(
                'conditions' => $cond,
                'recursive' => 1,
                'fields' => array('User.id', 'User.nickname', 'User.image', 'User.mobilephone', 'User.avatar'),
                'limit' => 100
            ));
            if ($this->request->is('ajax')) {
                //print_r($users);
                echo json_encode($users[0]['User']); exit();
            } else {
                $this->set('users', $users);
            }
        }
    }

    /**
     * 查询分享
     */
    public function search_shares()
    {
        $s_id = $_REQUEST['id'];
        if (!empty($s_id)) {
            $WeshareM = ClassRegistry::init('Weshare');
            $cond = array('id' => $s_id);
            $weshares = $WeshareM->find('all', array(
                'conditions' => $cond,
                'limit' => 300
            ));
            $this->set('weshares', $weshares);
        }
        $s_title = $_REQUEST['title'];
        if (!empty($s_title)) {
            $WeshareM = ClassRegistry::init('Weshare');
            $UserM = ClassRegistry::init('User');
            $cond = array('title LIKE' => '%' . $s_title . '%');
            $share_status = $_REQUEST['share_status'];
            if ($share_status != "all") {
                $cond['status'] = $share_status;
            }
            $weshares = $WeshareM->find('all', array(
                'conditions' => $cond,
                'limit' => 300
            ));
            $share_creators = Hash::extract($weshares, '{n}.Weshare.creator');
            $users = $UserM->find('all', array(
                'conditions' => array(
                    'id' => $share_creators
                ),
                'fields' => array('id', 'nickname')
            ));
            $users = Hash::combine($users, '{n}.User.id', '{n}.User');
            $this->set('users', $users);
            $this->set('weshares', $weshares);
        }
    }

    /*
     * 为用户分配团长级别
     */
    public function search_level()
    {
        $user_levels = get_user_levels();
        $this->set('user_levels', $user_levels);
    }

    public function do_set_level()
    {
        $this->autoRender = false;
        $para = array();
        $para['data_id'] = $_POST['data_id'];
        $para['data_value'] = $_POST['data_value'];
        if (empty($para['data_id'])) {
            echo json_encode(array('code' => '1001', 'msg' => 'error'));
            return;
        }
        $para['type'] = 0;
        $old_data = $this->UserLevel->find('first', array(
            'conditions' => array('data_id' => $para['data_id'])
        ));
        if (!empty($old_data)) {
            //set id for update
            $para['id'] = $old_data['UserLevel']['id'];
        }
        $res = $this->UserLevel->save($para);
        if ($res) {
            echo json_encode(array('code' => '1000', 'msg' => 'succ'));
        } else {
            echo json_encode(array('code' => '1001', 'msg' => 'error'));
        }
        return;
    }

    /**
     * 更新分享信息
     */
    public function update_share()
    {
        $this->autoRender = false;
        $json_data = $_REQUEST['data'];
        $share_data = json_decode($json_data, true);
        $this->Weshare->save($share_data);
        $this->clear_share_cache();
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 更新返利设置
     */
    public function update_share_rebate_setting()
    {
        $this->autoRender = false;
        $json_data = $_REQUEST['data'];
        $data = json_decode($json_data, true);
        $proxyRebatePercent = ClassRegistry::init('ProxyRebatePercent');
        $proxyRebatePercent->save($data);
        $this->clear_share_cache();
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 更新分享的快递设置
     */
    public function update_share_ship_setting()
    {
        $this->autoRender = false;
        $json_data = $_POST['data'];
        $data = json_decode($json_data, true);
        $weshareShipSettingM = ClassRegistry::init('WeshareShipSetting');
        $weshareAddressM = ClassRegistry::init('WeshareAddress');
        $weshareShipSettingM->saveAll($data['ship_setting']);
        $weshareAddressM->saveAll($data['weshare_address']);
        $this->clear_share_cache();
        echo json_encode(array('success' => true));
        return;
    }

    public function update_share_product()
    {
        $this->autoRender = false;
        $json_data = $_REQUEST['data'];
        $share_product_data = json_decode($json_data, true);
        $this->WeshareProduct->saveAll($share_product_data);
        $this->clear_share_cache();
        echo json_encode(array('success' => true));
        return;
    }

    public function delete_share($shareId)
    {
        $this->Weshare->delete($shareId);
        if ($_REQUEST['from'] == 'search') {
            $this->redirect(array('action' => 'search_shares'));
            return;
        }
        $this->redirect(array('action' => 'shares'));
    }

    /**
     * 获取分享者的分享
     */
    public function shares()
    {
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

    private function get_weshare_by_id($sid)
    {
        $model = ClassRegistry::init('Weshare');
        $data = $model->find('first', [
            'conditions' => [
                'id' => $sid,
            ],
        ]);

        return $data['Weshare'];
    }

    public function pool_product_item_ban($id, $sid)
    {
        // 在cake_pool_products表里面删除数据
        $model = ClassRegistry::init('WeshareProduct');
        $model->update([
            'deleted' => 1
        ], [
            'id' => $id,
        ]);

        $this->redirect("/shareManage/pool_product_edit/$sid.html");
    }

    public function pool_product_add()
    {
    }

    public function pool_product_delete($id)
    {
        // 在cake_pool_products表里面删除数据
        $model = ClassRegistry::init('PoolProduct');
        $model->update([
            'deleted' => 1
        ], [
            'id' => $id,
        ]);

        $this->redirect('/shareManage/pool_products');
    }

    public function pool_product_ban($id)
    {
        // 在cake_pool_products表里面状态设置为下架
        $model = ClassRegistry::init('PoolProduct');
        $model->update([
            'status' => 0
        ], [
            'id' => $id,
        ]);

        $this->redirect('/shareManage/pool_products');
    }

    public function pool_share_copy($share_id)
    {
        $uid = $this->currentUser['id'];
        // 先克隆初来一份Wesahres表行
        $nshare = $this->ShareUtil->cloneShare($share_id, null, null, null, POOL_SHARE_TYPE, WESHARE_DELETE_STATUS, 0);
        $nshare = $this->get_weshare_by_id($nshare['shareId']);
        // 手动填充cake_pool_products表.
        $data = [];
        $data['weshare_id'] = $nshare['id'];
        $data['share_name'] = $nshare['title'];
        $data['created'] = date('Y-m-d H:i:s');
        // 0 下架
        // 1 上架
        // 2 刚刚复制完, 新建的状态
        $data['status'] = 2;
        $data['deleted'] = 0;
        $model = ClassRegistry::init('PoolProduct');
        $res = $model->save($data);
        $id = $model->getLastInsertId();
        $this->redirect("/shareManage/pool_product_edit/$id.html");
    }

    public function share_edit($share_id)
    {
        $uid = $this->currentUser['id'];
        $weshareData = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $share_id
            )
        ));
        if ($weshareData['Weshare']['creator'] != $uid) {
            if (!$this->ShareAuthority->user_can_edit_share_info($uid, $share_id) && !is_super_share_manager($uid)) {
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

    public function authorize_shares()
    {
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

    public function beforeFilter()
    {
        $this->Auth->authenticate = array('WeinxinOAuth', 'Form', 'Pys', 'Mobile');
        $this->Auth->allowedActions = array('login', 'forgot', 'reset', 'do_login');
        $this->layout = 'sharer';
        parent::beforeFilter();
    }


    public function index()
    {
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $this->redirect(array('action' => 'login'));
            return;
        }
        $collect_data = $this->ShareManage->set_dashboard_collect_data($uid);
        $this->set('collect_data', $collect_data);
    }

    public function logout()
    {
        $this->logoutCurrUser();
        $this->redirect(array('action' => 'login'));
    }

    public function do_login()
    {
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

    public function login()
    {
        $this->layout = null;
    }

    private function handle_query_order($q_cond)
    {
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
            $user_ids = Hash::extract($orders, '{n}.Order.creator');
            foreach ($order_carts as $cart_item) {
                $order_id = $cart_item['Cart']['order_id'];
                if (!isset($order_cart_map[$order_id])) {
                    $order_cart_map[$order_id] = array();
                }
                $order_cart_map[$order_id][] = $cart_item['Cart'];
            }
            $users = $this->User->find('all', array(
                'conditions' => array(
                    'id' => $user_ids
                ),
                'fields' => array('id', 'nickname')
            ));
            $user_data = Hash::combine($users, '{n}.User.id', '{n}.User');
        }
        $this->set('user_data', $user_data);
        $this->set('order_cart_map', $order_cart_map);
        $this->set('orders_count', $orders_count);
        $this->set('orders', $orders);
    }

    public function get_my_paid_order()
    {
        $uid = $this->currentUser['id'];
        $create_share = $this->Weshare->find('all', array(
            'conditions' => array(
                'creator' => $uid,
            ),
            'fields' => array('id'),
            'limit' => 100,
            'order' => array('id DESC')
        ));
        $create_share_ids = Hash::extract($create_share, '{n}.Weshare.id');
        $auth_share_ids = $this->ShareAuthority->get_my_auth_share_ids($uid);
        $share_ids = array_merge($create_share_ids, $auth_share_ids);
        if (!empty($share_ids)) {
            $q_cond = array(
                'member_id' => $share_ids,
                'status' => ORDER_STATUS_PAID
            );
            $share_infos = $this->Weshare->find('all', array(
                'conditions' => array(
                    'id' => $share_ids
                ),
                'fields' => array('id', 'title')
            ));
            $share_infos = Hash::combine($share_infos, '{n}.Weshare.id', '{n}.Weshare');
            $this->set('share_infos', $share_infos);
            $this->handle_query_order($q_cond);
        }

    }

    public function order_manage($share_id)
    {
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
        $this->handle_query_order($q_cond);
        $this->set('share_info', $share_info);
    }

    public function share_order()
    {
        $share_id = $_REQUEST['share_id'];
        if (!empty($share_id)) {
            $this->set_share_order_data($share_id);
        }
    }

    /**
     * 产品池中产品的订单
     */
    public function pool_product_order()
    {
        $share_id = $_REQUEST['share_id'];
        $sharePoolProductM = ClassRegistry::init('SharePoolProduct');
        $all_pool_products = $sharePoolProductM->get_all_products();
        $this->set('all_pool_products', $all_pool_products);
        if (!empty($share_id)) {
            $sharePoolProductM = ClassRegistry::init('SharePoolProduct');
            $all_fork_shares = $sharePoolProductM->get_fork_share_ids($share_id);
            if (!empty($all_fork_shares)) {
                $all_fork_shares = Hash::combine($all_fork_shares, '{n}.Weshare.id', '{n}.Weshare');
                $q_share_id = $_REQUEST['q_share_id'] ? $_REQUEST['q_share_id'] : key($all_fork_shares);
                $fork_share_creators = Hash::extract($all_fork_shares, '{n}.creator');
                $this->set_share_order_data($q_share_id, $fork_share_creators);
                $this->set('child_shares', $all_fork_shares);
                $this->set('q_share_id', $q_share_id);
                $this->set('current_share', $all_fork_shares[$q_share_id]);
                $this->set('share_id', $share_id);
            }
        }
    }

    /**
     * @param $share_id
     * @param array $patch_uids
     * 公用的设置订单数据
     */
    private function set_share_order_data($share_id, $patch_uids = array())
    {
        $orders = $this->ShareManage->get_share_orders($share_id);
        $user_ids = Hash::extract($orders, '{n}.Order.creator');
        $user_ids = array_merge($user_ids, $patch_uids);
        $user_data = $this->ShareManage->get_users_data($user_ids);
        $user_data = Hash::combine($user_data, '{n}.User.id', '{n}.User');
        $share_data = $this->WeshareBuy->get_weshare_info($share_id);
        $this->set('orders', $orders);
        $this->set('user_data', $user_data);
        $this->set('share_data', $share_data);
        $this->set('share_id', $share_id);
    }

    public function batch_set_order_ship_code()
    {

    }

    public function clear_index_product_cache()
    {
        $this->autoRender = false;
        $this->ShareManage->clear_cache_for_index_products_of_type(0);
        $this->ShareManage->clear_cache_for_index_products_of_type(1);
        $this->ShareManage->clear_cache_for_index_products_of_type(2);
        $this->ShareManage->clear_cache_for_index_products_of_type(3);
        echo json_encode(array('success' => true));
        return;
    }

    public function clear_app_cache()
    {
        $this->autoRender = false;
        Cache::clear(false, 'default');
        echo json_encode(array('success' => true));
        return;
    }

    public function index_products()
    {
        $tags = get_index_tags();
        $tag_id = empty($_REQUEST['tag_id']) ? $tags[0]['id'] : $_REQUEST['tag_id'];
        $this->set('tags', $tags);
        $index_products = $this->ShareManage->get_index_products($tag_id);
        $this->set('index_products', $index_products);
        $this->set('tag_id', $tag_id);
    }

    public function pool_products()
    {
        $index_products = $this->ShareManage->get_pool_products();
        $this->set('index_products', $index_products);
    }

    public function index_product_add()
    {
        $tags = get_index_tags();
        $this->set('tags', $tags);
    }

    public function index_product_delete($id)
    {
        $indexProductM = ClassRegistry::init('IndexProduct');
        $this->log('delete index product ' . $id, LOG_INFO);
        $indexProductM->update(array('deleted' => DELETED_YES), array('id' => $id));

        $indexProduct = $indexProductM->findById($id);
        $this->ShareManage->on_index_product_deleted($indexProduct);

        $this->redirect(array('action' => 'index_products'));
    }

    public function index_product_edit($id)
    {
        $indexProductM = ClassRegistry::init('IndexProduct');
        $index_product = $indexProductM->find('first', array(
            'conditions' => array(
                'id' => $id
            )
        ));
        $this->set('index_product', $index_product);
        $tags = get_index_tags();
        $this->set('tags', $tags);
    }

    public function pool_product_edit($id)
    {
        $product = $this->ShareManage->get_pool_product($id);
        $this->set('index_product', $product);
    }

    public function pool_product_save()
    {
        $error = false;
        $data = $this->data;
        if (!$data['Weshares']['images']) {
            $error = "分享链接BANNER图不能为空.";
        }
        foreach ($data['WeshareProduct'] as $key => $value) {
            if ($value['channel_price'] == "") {
                $error = "渠道价不能为空";
                break;
            }
        }
        if ($error) {
            echo $error;
        } else {
            // 此处清空缓存, Weshare
            $key = 'pool_product_info_cache_key_' . $data['Weshares']['id'];
            $cacheData = Cache::delete($key);

            $data['PoolProduct']['status'] = 1;
            $this->ShareManage->save_pool_product($data);
            $this->redirect(array('action' => 'pool_products'));
        }
    }

    public function index_product_save()
    {
        $data = $this->data;
        $this->ShareManage->save_index_product($data);
        $this->redirect(array('action' => 'index_products'));
    }

    /**
     * 清除分享的缓存
     */
    private function clear_share_cache()
    {
        $shareId = $_REQUEST['shareId'];
        //SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weshareId
        Cache::write(SHARE_DETAIL_DATA_CACHE_KEY . '_' . $shareId, '');
        Cache::write(SHARE_DETAIL_DATA_WITH_TAG_CACHE_KEY . '_' . $shareId, '');
        //SHARE_SHIP_SETTINGS_CACHE_KEY . '_' . $weshareId;
        Cache::write(SHARE_SHIP_SETTINGS_CACHE_KEY . '_' . $shareId, '');
        //SIMPLE_SHARE_INFO_CACHE_KEY . '_' . $share_id
        Cache::write(SIMPLE_SHARE_INFO_CACHE_KEY . '_' . $shareId, '');
    }


    public function cache_tool()
    {
    }

    public function clear_share_info_cache()
    {
        $this->autoRender = false;
        $this->clear_share_cache();
        echo json_encode(array('success' => true));
        return;
    }

    public function clear_pool_product_cache()
    {
        $this->autoRender = false;
        $share_id = $_REQUEST['shareId'];
        $key = 'pool_product_info_cache_key_' . $share_id;
        Cache::write($key, '');
        echo json_encode(array('success' => true));
        return;
    }

    public function clear_share_order_cache()
    {
        $this->autoRender = false;
        $share_id = $_REQUEST['shareId'];
        Cache::write(SHARE_BUY_SUMMERY_INFO_CACHE_KEY . '_' . $share_id, '');
        Cache::write(SHARE_ORDER_COUNT_DATA_CACHE_KEY . '_' . $share_id, '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $share_id . '_1_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $share_id . '_0_1', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $share_id . '_1_0', '');
        Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $share_id . '_0_0', '');
        Cache::write(SHARE_OFFLINE_ADDRESS_SUMMERY_DATA_CACHE_KEY . '_' . $share_id, '');
        Cache::write(SHARE_OFFLINE_ADDRESS_BUY_DATA_CACHE_KEY . '_' . $share_id, '');
        Cache::write(SHARE_ORDER_COUNT_DATA_CACHE_KEY . '_' . $share_id, '');
        Cache::write(SHARE_BUY_SUMMERY_INFO_CACHE_KEY . '_' . $share_id, '');
        echo json_encode(array('success' => true));
        return;
    }

    public function clear_user_info_cache()
    {
        $this->autoRender = false;
        $user_id = $_REQUEST['user_id'];
        Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $user_id, '');
        Cache::write(SHARER_LEVEL_CACHE_KEY . '_' . $user_id . '_' . 0, '');
        clearMemcacheCacheByKeyword($user_id);
        echo json_encode(array('success' => true));
        return;
    }

    public function clear_share_auth_cache()
    {
        $this->autoRender = false;
        $share_id = $_REQUEST['shareId'];
        Cache::write(SHARE_ORDER_OPERATE_CACHE_KEY . '_' . $share_id, '');
        echo json_encode(array('success' => true));
        return;
    }

    //统计商品的份数
    /**
     * SELECT name, SUM( num )
     * FROM cake_carts
     * WHERE order_id
     * IN (
     *
     * SELECT id
     * FROM cake_orders
     * WHERE member_id =2078
     * AND ship_mark =  'kuai_di'
     * )
     * GROUP BY product_id
     *
     */

    /**
     * @param $refer_share_id
     * @return array
     * 获取可能授权的用户
     */
    private function load_refer_share_authority_user($refer_share_id)
    {
        if (!empty($refer_share_id)) {
            $shareOperateSettings = $this->ShareOperateSetting->find('all', array(
                'conditions' => array(
                    'scope_id' => $refer_share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE
                ),
                'order' => array('user DESC')
            ));
            $history_operate_setting_uids = Hash::extract($shareOperateSettings, '{n}.ShareOperateSetting.user');
            return $history_operate_setting_uids;
        }
        return array();
    }

    /**
     * 跳转到一个分享权限设置的页面
     */
    public function share_operate_set_view()
    {
        $shareId = $_REQUEST['share_id'];
        if (!empty($shareId)) {
            $shareInfo = $this->Weshare->find('first', array(
                'conditions' => array(
                    'id' => $shareId
                )
            ));
            $share_creator = $shareInfo['Weshare']['creator'];
            $productTags = $this->WeshareProductTag->find('all', array(
                'conditions' => array(
                    'user_id' => $shareInfo['Weshare']['creator'],
                    'deleted' => DELETED_NO
                )
            ));
            $shareOperateSettings = $this->ShareOperateSetting->find('all', array(
                'conditions' => array(
                    'scope_id' => $shareId,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE
                ),
                'order' => array('user DESC')
            ));
            $history_operate_setting_uids = $this->load_refer_share_authority_user($shareInfo['Weshare']['refer_share_id']);
            $shareOperateUserIds = Hash::extract($shareOperateSettings, '{n}.ShareOperateSetting.user');
            $shareOperateUserIds[] = $share_creator;
            $shareOperateUserIds = array_merge($shareOperateUserIds, $history_operate_setting_uids);
            $shareOperateUserIds = array_unique($shareOperateUserIds);
            $usersData = $this->User->find('all', array(
                'conditions' => array(
                    'id' => $shareOperateUserIds
                ),
                'fields' => array('nickname', 'id', 'mobilephone')
            ));
            $usersData = Hash::combine($usersData, '{n}.User.id', '{n}.User');
            $productTags = Hash::combine($productTags, '{n}.WeshareProductTag.id', '{n}.WeshareProductTag');
            $this->set('history_operate_setting_uids', $history_operate_setting_uids);
            $this->set('operate_settings', $shareOperateSettings);
            $this->set('user_data', $usersData);
            $this->set('operate_name_map', $this->operateDataTypeNameMap);
            $this->set('share_info', $shareInfo);
            $this->set('product_tags', $productTags);
        }
    }

    /**
     * 保存分享权限设置
     */
    public function save_share_operate_setting()
    {
        $user_id = $_REQUEST['user_id'];
        $share_id = $_REQUEST['share_id'];
        $tag_id = $_REQUEST['tag_id'];
        $this->save_share_operate($user_id, $share_id);
        $this->save_share_tag_operate($user_id, $tag_id, $share_id);
        $this->redirect(array('action' => 'share_operate_set_view', '?' => array('share_id' => $share_id)));
    }

    public function save_share_edit_operate_setting()
    {
        $user_id = $_REQUEST['user_id'];
        $share_id = $_REQUEST['share_id'];
        $this->process_save_share_operate_setting($user_id, $share_id, SHARE_INFO_OPERATE_TYPE);
        $this->redirect(array('action' => 'share_operate_set_view', '?' => array('share_id' => $share_id)));
    }

    public function save_share_manage_operate_setting()
    {
        $user_id = $_REQUEST['user_id'];
        $share_id = $_REQUEST['share_id'];
        $this->save_share_operate_setting($user_id, $share_id, SHARE_MANAGE_OPERATE_TYPE);
        $this->redirect(array('action' => 'share_operate_set_view', '?' => array('share_id' => $share_id)));
    }

    //保存编辑分享的权限
    private function process_save_share_operate_setting($user_id, $share_id, $type)
    {
        if (!empty($user_id) && !empty($share_id) && !empty($type)) {
            $oldData = $this->ShareOperateSetting->find('first', array(
                'conditions' => array(
                    'user' => $user_id,
                    'data_type' => $type,
                    'data_id' => $share_id,
                    'scope_id' => $share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE
                )
            ));
            if (empty($oldData)) {
                $saveData = array('user' => $user_id,
                    'data_type' => $type,
                    'data_id' => $share_id,
                    'scope_id' => $share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE);
                $this->ShareOperateSetting->save($saveData);
                if ($type == SHARE_INFO_OPERATE_TYPE) {
                    Cache::write(SHARE_INFO_OPERATE_CACHE_KEY . '_' . $share_id, '');
                    Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $user_id, '');
                }
                if ($type == SHARE_MANAGE_OPERATE_TYPE) {
                    Cache::write(SHARE_MANAGE_OPERATE_CACHE_KEY . '_' . $share_id, '');
                    Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $user_id, '');
                }
            }
        }
    }


    private function save_share_tag_operate($user_id, $tag_id, $share_id)
    {
        if (!empty($user_id) && !empty($share_id) && !empty($tag_id)) {
            $oldData = $this->ShareOperateSetting->find('first', array(
                'conditions' => array(
                    'user' => $user_id,
                    'data_type' => SHARE_TAG_ORDER_OPERATE_TYPE,
                    'data_id' => $tag_id,
                    'scope_id' => $share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE
                )
            ));
            if (empty($oldData)) {
                $saveData = array('user' => $user_id,
                    'data_type' => SHARE_TAG_ORDER_OPERATE_TYPE,
                    'data_id' => $tag_id,
                    'scope_id' => $share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE);
                $this->ShareOperateSetting->save($saveData);
                Cache::write(SHARE_ORDER_TAG_OPERATE_CACHE_KEY . '_' . $share_id, '');
                Cache::write(SHARE_ORDER_TAG_OPERATE_CACHE_KEY . '_' . $share_id . '_' . $tag_id, '');
                Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $user_id, '');
            }
        }
    }

    private function save_share_operate($user_id, $share_id)
    {
        if (!empty($user_id) && !empty($share_id)) {
            $oldData = $this->ShareOperateSetting->find('first', array(
                'conditions' => array(
                    'user' => $user_id,
                    'data_type' => SHARE_ORDER_OPERATE_TYPE,
                    'data_id' => $share_id,
                    'scope_id' => $share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE
                )
            ));
            if (empty($oldData)) {
                $saveData = array('user' => $user_id,
                    'data_type' => SHARE_ORDER_OPERATE_TYPE,
                    'data_id' => $share_id,
                    'scope_id' => $share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE);
                $this->ShareOperateSetting->save($saveData);
                Cache::write(SHARE_ORDER_OPERATE_CACHE_KEY . '_' . $share_id, '');
                Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $user_id, '');
            }
        }
    }


    /**
     * @param $id
     * @param $share_id
     * @param $data_id
     * 删除分享权限
     */
    public function delete_share_operate_setting($id, $share_id, $data_id)
    {
        $data = $this->ShareOperateSetting->find('first', array('conditions' => array('id' => $id)));
        if (!empty($data)) {
            $this->ShareOperateSetting->delete($id);
            if ($data['ShareOperateSetting']['data_type'] == SHARE_ORDER_OPERATE_TYPE) {
                Cache::write(SHARE_ORDER_OPERATE_CACHE_KEY . '_' . $share_id, '');
            } else {
                Cache::write(SHARE_ORDER_TAG_OPERATE_CACHE_KEY . '_' . $share_id, '');
                Cache::write(SHARE_ORDER_TAG_OPERATE_CACHE_KEY . '_' . $share_id . '_' . $data_id, '');
            }
        }
        $this->redirect(array('action' => 'share_operate_set_view', '?' => array('share_id' => $share_id)));
    }

    public function share_utils()
    {

    }

    public function copy_share_to_user($shareId, $userId)
    {
        $this->autoRender = false;
        $result = $this->ShareUtil->cloneShare($shareId, $userId);
        echo json_encode(array('success' => true, 'result' => $result));
        return;
    }

}
