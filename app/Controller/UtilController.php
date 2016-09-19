<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 8/23/15
 * Time: 20:16
 */
class UtilController extends AppController {

    public $name = 'util';

    public $uses = array('UserRelation', 'Order', 'Cart', 'User', 'Oauthbind', 'Weshare', 'CandidateEvent', 'IndexProduct');

    public $components = array('ShareUtil', 'Weixin', 'WeshareBuy', 'RedisQueue', 'RedPacket');

    public function log_view_position_click(){
        $this->autoRender = false;
        $postData = $this->get_post_raw_data();
        $pageName = $postData['page'];
        $pagePosition = $postData['position'];
        $positionValue = $postData['value'];
        $log = [
            "index" => "event_page_click",
            "type" => $pageName,
            "position" => $pagePosition,
            "positionVal" => $positionValue,
            "user_id" => intval($this->currentUser['id'])
        ];
        add_logs_to_es($log);
        echo json_encode(['success' => true]);
        exit;
    }

    public function log_js_error() {
        $msg = $_GET['msg'];
        $url = $_GET['error_url'];
        $ln = $_GET['ln'];
        $ip = $_SERVER['REMOTE_HOST'];
        $uid = $this->currentUser['id'] ? $this->currentUser['id'] : 0;
        $this->log("js error $uid : $url : $ln msg=$msg : ip=$ip");
        echo "logged";
        $this->autoRender = false;
    }

    public function log_trace() {
        $this->log('tracekit:'.var_export($_POST, true));
        echo "logged";
        $this->autoRender = false;
    }



    public function gen_qr_code(){
        App::import('Vendor', 'php_qrcode/phpqrcode');
        $value = $_REQUEST['content']; //二维码内容
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 6;//生成图片大小
        QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize);
        exit();
    }

    public function create_qr_code_by_scene_id($sceneId){
        $this->loadModel('WxOauth');
        $result = $this->WxOauth->create_qrcode_by_sceneid($sceneId);
        echo json_encode($result);
        exit;
    }

    /**
     * @param $user_id
     * @return array
     * 获取用户粉丝
     */
//    public function load_user_fans($user_id) {
//        $this->autoRender = false;
//        $shares = $this->Weshare->find('all', array(
//            'conditions' => array(
//                'creator' => $user_id
//            ),
//            'limit' => 100
//        ));
//        $share_ids = Hash::extract($shares, '{n}.Weshare.id');
//
//        $orders_group_by_user = $this->Order->find('all', array(
//            'conditions' => array(
//                'member_id' => $share_ids,
//                'ship_mark' => 'self_ziti'
//            ),
//            'limit' => 1000,
//            'group' => array('Order.creator')
//        ));
//
//        $join_users = $this->CandidateEvent->find('all', array(
//            'conditions' => array(
//                'event_id' => 6,
//            ),
//            'limit' => 500
//        ));
//        $order_user_ids = Hash::extract($orders_group_by_user, '{n}.Order.creator');
//        $join_user_ids = Hash::extract($join_users, '{n}.CandidateEvent.user_id');
//        $diff_user_ids = array_diff($order_user_ids, $join_user_ids);
//        //echo json_encode(array('count' => count($diff_user_ids), 'user_ids' => $diff_user_ids));
//        $this->set('user_ids', $diff_user_ids);
//        return $diff_user_ids;
//    }

    /**
     * @param $openId
     * @param $title
     * @param $productName
     * @param $detailUrl
     * @param $sharerName
     * @param $remark
     * 处理发起分享通知
     */
    public function process_send_share_msg($openId, $title, $productName, $detailUrl, $sharerName, $remark) {
        send_join_tuan_buy_msg(null, $title, $productName, $sharerName, $remark, $detailUrl, $openId);
    }
    
    /**
     * 获取微信的token
     */
    public function get_base_token() {
        $this->autoRender = false;
        try {
            if (is_super_share_manager($this->currentUser['id'])) {
                $this->loadModel('WxOauth');
                $o = $this->WxOauth->get_base_access_token();
                $log = "get_base_access_token:" . $o . ", in json:" . json_encode($o);
                $this->log($log);
                echo $log;
            }
        } catch (Exception $e) {
            echo "exception: $e";
        }
    }

    /**
     * @param $shareId
     * @param $user_id
     * 根据分享迁移数据
     */
//    public function transferFansByShareId($shareId, $user_id) {
//        $this->autoRender = false;
//        $orders = $this->Order->find('all', array(
//            'conditions' => array(
//                'member_id' => $shareId,
//                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_DONE, ORDER_STATUS_SHIPPED, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RECEIVED),
//                'type' => ORDER_TYPE_WESHARE_BUY
//            ),
//            'group' => array('Order.creator'),
//            'limit' => 1000
//        ));
//        $save_data = array();
//        $temp_data = array();
//        foreach ($orders as $order) {
//            $order_creator = $order['Order']['creator'];
//            if ($this->ShareUtil->check_user_relation($user_id, $order_creator)) {
//                if (!in_array($order_creator, $temp_data)) {
//                    $temp_data[] = $order_creator;
//                    $save_data[] = array('user_id' => $user_id, 'follow_id' => $order_creator, 'type' => 'Transfer', 'created' => date('Y-m-d H:i:s'));
//                }
//            }
//        }
//        $this->UserRelation->saveAll($save_data);
//        echo json_encode(array('success' => true));
//        return;
//    }

    /**
     * @param $product_id
     * @param $user_id
     * @param $offset
     * 迁移粉丝数据
     */
//    public function transferFansData($product_id, $user_id, $offset = 0) {
//        $this->autoRender = false;
//        $carts = $this->Cart->find('all', array(
//            'conditions' => array(
//                'product_id' => $product_id,
//                'not' => array('order_id' => null, 'order_id' => 0, 'type' => ORDER_TYPE_WESHARE_BUY, 'creator' => 0, 'creator' => null),
//            ),
//            'group' => array('creator'),
//            'limit' => 500,
//            'offset' => $offset,
//            'order' => array('created DESC')
//        ));
//        $save_data = array();
//        $temp_data = array();
//        foreach ($carts as $cart_item) {
//            $cart_creator = $cart_item['Cart']['creator'];
//            if ($this->ShareUtil->check_user_relation($user_id, $cart_creator)) {
//                if (!in_array($cart_creator, $temp_data)) {
//                    $temp_data[] = $cart_creator;
//                    $save_data[] = array('user_id' => $user_id, 'follow_id' => $cart_creator, 'type' => 'Transfer', 'created' => date('Y-m-d H:i:s'));
//                }
//            }
//        }
//        $this->UserRelation->saveAll($save_data);
//        echo json_encode(array('success' => true));
//        return;
//    }

    /**
     * @param $user_id
     * @param $page_num
     * @param $page_size
     * 从微信里面更新粉丝信息
     */
    public function updateFansProfile($user_id, $page_num, $page_size) {
        $this->autoRender = false;
        $tasks = array();
        foreach (range(0, $page_num) as $index) {
            $offset = $index * $page_size;
            $url = '/util/processUpdateFansProfile/' . $user_id . '/' . $page_size . '/' . $offset;
            $tasks[] = array('url' => $url);
        }
        $result = $this->addTaskQueue($tasks);
        echo json_encode(array('result' => $result, 'tasks' => $tasks));
        return;
    }

    /**
     * @param $user_id
     * @param $limit
     * @param $offset
     * task queue
     */
    public function processUpdateFansProfile($user_id, $limit, $offset) {
        $this->autoRender = false;
        $user_relations = $this->UserRelation->find('all', array(
            'conditions' => array(
                'user_id' => $user_id,
                'type' => 'Transfer'
            ),
            'limit' => $limit,
            'offset' => $offset,
            'order' => array('created DESC')
        ));
        $follow_ids = Hash::extract($user_relations, '{n}.UserRelation.follow_id');
        $oauthBinds = $this->Oauthbind->find('all', array(
            'conditions' => array(
                'user_id' => $follow_ids
            ),
            'limit' => $limit
        ));
        if (!empty($oauthBinds)) {
            foreach ($oauthBinds as $item) {
                $follow_user_id = $item['Oauthbind']['user_id'];
                $open_id = $item['Oauthbind']['oauth_openid'];
                $wx_user = get_user_info_from_wx($open_id);
                $this->log('download wx user info ' . json_encode($wx_user));
                $photo = $wx_user['headimgurl'];
                $nickname = $wx_user['nickname'];
                if (empty($nickname)) {
                    //user not have wexin info
                    $this->UserRelation->updateAll(array('deleted' => 1), array('user_id' => $user_id, 'follow_id' => $follow_user_id, 'type' => 'Transfer'));
                    continue;
                }
                //when user many oauth bind
                $this->UserRelation->updateAll(array('deleted' => 0), array('user_id' => $user_id, 'follow_id' => $follow_user_id, 'type' => 'Transfer'));
                //default header
                $download_url = 'http://static.tongshijia.com/avatar/default.jpg';
                if (!empty($photo)) {
                    $this->log('download wx user photo ' . $photo);
                    $download_url = download_photo_from_wx($photo);
                }
                $this->User->updateAll(array('nickname' => "'" . $nickname . "'", 'image' => "'" . $download_url . "'", 'avatar' => "'".$download_url."'"), array('id' => $follow_user_id));
            }
        }
        //update status
        $this->UserRelation->updateAll(array('is_update' => 1), array('user_id' => $user_id, 'follow_id' => $follow_ids, 'type' => 'Transfer'));
        echo json_encode(array('success' => true, 'oauth-bind' => $oauthBinds, 'follow-id' => $follow_ids));
        return;
    }

    /**
     * @param $tasks
     * @return array
     * 添加队列任务
     */
    private function addTaskQueue($tasks) {
        $this->RedisQueue->add_tasks('cqueue', $tasks);
        return array('success' => true);
    }

    /**
     * @param $shareId
     * 手动的开启常用自提点子分享
     */
    public function start_static_shares($shareId) {
        $this->autoRender = false;
        $this->ShareUtil->new_static_address_group_shares($shareId);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $shareOfferId
     * @param $uid
     * @param $totalNum
     * 手工给分享者发一些红包
     */
    public function gen_share_offer_for_sharer($shareOfferId, $uid, $totalNum) {
        $this->autoRender = false;
        $shareOfferM = ClassRegistry::init('ShareOffer');
        $genShareOfferResult = $shareOfferM->add_shared_slices($uid, $shareOfferId, $totalNum, true);
        echo json_encode($genShareOfferResult);
        return;
    }

    /**
     * @param $shareOfferId
     * @param $share_id
     * 手工发红包
     */
    public function manual_send_coupon($shareOfferId, $share_id){
        $this->autoRender=false;
        $uids = $this->WeshareBuy->get_has_buy_user($share_id);
        foreach($uids as $uid){
            $this->RedPacket->process_receive($shareOfferId, $uid, $is_weixin = true, $send_msg = true);
        }
        echo json_encode($uids);
        exit;
    }

    /**
     * @param $offerId
     * @param $uid
     * 手工生成红包分享ID
     */
    public function gen_shared_offer_by_offer($offerId, $uid)
    {
        $this->autoRender = false;
        $this->loadModel('SharedOffer');
        $this->SharedOffer->create();
        $saveData = [
            'share_offer_id' => $offerId,
            'total_number' => 0,
            'start' => date(FORMAT_DATETIME, mktime()),
            'uid' => $uid,
            'order_id' => -1,
            'status' => SHARED_OFFER_STATUS_OUT
        ];
        $result = $this->SharedOffer->save($saveData);
        echo json_encode($result);
        exit;
    }

    /**
     *
     * 迁移写死的一些 用户权限配置数据
     */
//    public function transfer_share_operate_settings() {
//        $this->autoRender = false;
//        $shareUserBindM = ClassRegistry::init('ShareUserBind');
//        $shareOperateSettingM = ClassRegistry::init('ShareOperateSetting');
//        $allShareUserBindDatas = $shareUserBindM->getAllShareUserBind();
//        $saveData = array();
//        foreach ($allShareUserBindDatas as $shareId => $userIds) {
//            foreach ($userIds as $uid) {
//                $saveData[] = array('data_type' => SHARE_ORDER_OPERATE_TYPE, 'data_id' => $shareId, 'scope_type' => SHARE_OPERATE_SCOPE_TYPE, 'scope_id' => $shareId, 'user' => $uid);
//            }
//        }
//        $shareOperateSettingM->saveAll($saveData);
//        echo json_encode(array('success' => true));
//        return;
//    }



//    public function gen_password(){
//        $password = $_REQUEST['password'];
//    }



    //二维码快捷支付
    public function scan_qr_code_make_order($shareId){
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            if ($this->is_weixin()) {
                if (empty($this->currentUser) && $this->is_weixin() && !in_array($this->request->params['controller'], array('users', 'check'))) {
                    $this->redirect($this->login_link());
                }
            }else{
                $this->redirect('/weshares/view/' . $shareId . '.html');
            }
        }
        $this->loadModel('WeshareProduct');
        $this->loadModel('Weshare');
        $this->loadModel('WeshareAddress');
        $this->loadModel('Cart');
        $this->loadModel('Order');
        $dataSource = $this->Order->getDataSource();
        $dataSource->begin();
        try {
            $weshare = $this->Weshare->find('first', [
                'conditions' => [
                    'id' => $shareId
                ]
            ]);
            $product = $this->WeshareProduct->find('first', [
                'conditions' => [
                    'weshare_id' => $shareId,
                    'deleted' => DELETED_NO
                ]
            ]);
            $address = $this->WeshareAddress->find('first', [
                'conditions' => [
                    'weshare_id' => $shareId,
                    'deleted' => DELETED_NO
                ]
            ]);
            $date_now = date('Y-m-d H:i:s');
            $order_price = number_format($product['WeshareProduct']['price'] / 100, 2, '.', '');
            $order = [
                'creator' => $uid,
                'consignee_address' => $address['WeshareAddress']['address'],
                'member_id' => $shareId,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'created' => $date_now,
                'updated' => $date_now,
                'consignee_id' => $address['WeshareAddress']['id'],
                'consignee_name' => $this->currentUser['nickname'],
                'consignee_mobilephone' => $address['WeshareAddress']['phone'],
                'total_all_price' => $order_price,
                'total_price' => $order_price,
                'brand_id' => $weshare['Weshare']['creator'],
                'status' => ORDER_STATUS_WAITING_PAY,
                'ship_mark' => SHARE_SHIP_SELF_ZITI_TAG
            ];
            $order = $this->Order->save($order);
            $cart = [
                'name' => $product['WeshareProduct']['name'],
                'num' => 1,
                'price' => $product['WeshareProduct']['price'],
                'type' => ORDER_TYPE_WESHARE_BUY,
                'product_id' => $product['WeshareProduct']['id'],
                'created' => $date_now,
                'updated' => $date_now,
                'creator' => $uid,
                'order_id' => $order['Order']['id'],
                'tuan_buy_id' => $shareId
            ];
            $this->Cart->save($cart);
            $dataSource->commit();
            $this->redirect('/weshares/pay/' . $order['Order']['id'] . '/0.html');
            return;
        } catch (Exception $e) {
            $this->log('scan qr code gen order exception '.$e->getMessage());
            $dataSource->rollback();
        }
        $this->redirect('/weshares/view/' . $shareId . '.html');
    }
}