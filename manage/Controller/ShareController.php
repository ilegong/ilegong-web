<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 7/20/15
 * Time: 17:11
 */

class ShareController extends AppController{

    var $name = 'Share';

    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User', 'OrderConsignees', 'WeshareShipSetting', 'OfflineStore', 'Oauthbind', 'Comment', 'RefundLog');

    var $components = array('Weixin');

    public function beforeFilter(){
        parent::beforeFilter();
        $this->layout='bootstrap_layout';
    }

    public function admin_set_offline_store_code() {
        $this->autoRender = false;
        $order_id = $_REQUEST['order_id'];
        $code = $_REQUEST['ship_code'];
        //select order paid to send msg
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'id' => $order_id,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => array(ORDER_STATUS_PAID),
                'ship_mark' => 'pys_zi_ti'
            ),
            'fields' => array(
                'id', 'consignee_name', 'consignee_address', 'creator', 'member_id', 'consignee_id'
            )
        ));
        $weshare_id = $order['Order']['member_id'];
        $weshare = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $weshare_id
            )
        ));
        $order_user_ids = Hash::extract($order, 'Order.creator');
        $share_creator = $weshare['Weshare']['creator'];
        $order_user_ids[] = $share_creator;
        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $order_user_ids
            ),
            'fields' => array('id', 'nickname')
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        $userOauthBinds = $this->Oauthbind->find('all', array(
            'conditions' => array(
                'user_id' => $order_user_ids
            ),
            'fields' => array('user_id', 'oauth_openid')
        ));
        $cart_info = $this->get_cart_name_and_num($order_id);
        $userOauthBinds = Hash::combine($userOauthBinds, '{n}.Oauthbind.user_id', '{n}.Oauthbind.oauth_openid');
        $desc = '感谢大家对' . $users[$share_creator]['nickname'] . '的支持，分享快乐。';
        $detail_url = WX_HOST . '/weshares/view/' . $weshare_id;
        $order_id = $order['Order']['id'];
        $order_user_id = $order['Order']['creator'];
        $open_id = $userOauthBinds[$order_user_id];
        $order_user_name = $users[$order_user_id]['nickname'];
        $title = $order_user_name . '你好，您订购的' . $cart_info['cart_name'] . '已经到达自提点，提货码：' . $code . '，生鲜娇贵，请尽快取货哈。';
        $offlineStore = $this->get_offline_store($order['Order']['consignee_id']);
        $this->Weixin->send_share_product_arrival($open_id, $detail_url, $title, $order_id, $offlineStore['OfflineStore']['alias'], $offlineStore['OfflineStore']['name'], $desc);
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED, 'ship_code' => "'" . $code . "'"), array('id' => $order_id));
        echo json_encode(array('success' => true));
    }

    public function admin_set_share_paid($shareId) {
        $this->Weshare->updateAll(array('settlement' => 1), array('id' => $shareId, 'status' => array(1, 2)));
        $this->redirect(array('action' => 'admin_share_for_pay'));
    }

    public function admin_share_for_pay(){
        $weshares = $this->Weshare->find('all', array(
            'conditions' => array(
                'status' => array(1, 2),
                'settlement' => 0
            )
        ));
        $weshare_ids = Hash::extract($weshares, '{n}.Weshare.id');
        $weshare_creator_ids = Hash::extract($weshares, '{n}.Weshare.creator');
        $creators = $this->User->find('all', array(
            'conditions' => array(
                'id' => $weshare_creator_ids
            ),
            'fields' => array(
                'id', 'nickname', 'image', 'wx_subscribe_status', 'description', 'mobilephone', 'payment'
            )
        ));
        $creators = Hash::combine($creators, '{n}.User.id', '{n}.User');
        $weshares = Hash::combine($weshares, '{n}.Weshare.id', '{n}.Weshare');
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY,
                'member_id' => $weshare_ids,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY)
            )
        ));
        $refund_orders = array();
        $refund_order_ids = array();
        $summery_data = array();
        foreach ($orders as $item) {
            $member_id = $item['Order']['member_id'];
            $order_total_price = $item['Order']['total_all_price'];
            if ($item['Order']['status'] == ORDER_STATUS_RETURN_MONEY || $item['Order']['status'] == ORDER_STATUS_RETURNING_MONEY) {
                $refund_order_ids[] = $item['Order']['id'];
                if (!isset($refund_orders[$member_id])) {
                    $refund_orders[$member_id] = array();
                }
                $refund_orders[$member_id][] = $item;
            }
            if (!isset($summery_data[$member_id])) {
                $summery_data[$member_id] = array('total_price' => 0);
            }
            $summery_data[$member_id]['total_price'] = $summery_data[$member_id]['total_price'] + $order_total_price;
        }
        $refund_logs = $this->RefundLog->find('all', array(
            'order_id' => $refund_order_ids
        ));
        $refund_logs = Hash::combine($refund_logs,'{n}.RefundLog.order_id', '{n}.RefundLog.refund_fee');
        $weshare_refund_money_map = array();
        foreach ($refund_orders as $item_share_id => $item_orders) {
            $share_refund_money = 0;
            foreach ($item_orders as $refund_order_item) {
                $order_id = $refund_order_item['Order']['id'];
                $share_refund_money = $share_refund_money + $refund_logs[$order_id];
            }
            $weshare_refund_money_map[$item_share_id] = $share_refund_money/100;
        }
        $this->set('weshare_refund_map',$weshare_refund_money_map);
        $this->set('weshares', $weshares);
        $this->set('weshare_summery', $summery_data);
        $this->set('creators', $creators);
    }

    public function admin_merge_ship_setting_data() {
        $this->autoRender = false;
        $shares = $this->Weshare->find('all', array(
            'limit' => 200
        ));
        $saveData = array();
        foreach ($shares as $share) {
            $share_id = $share['Weshare']['id'];
            $historyData = $this->WeshareShipSetting->find('first', array(
                'conditions' => array(
                    'weshare_id' => $share_id
                )
            ));
            if (empty($historyData)) {
                $saveData[] = array('weshare_id' => $share_id, 'status' => 1, 'ship_fee' => 0, 'tag' => 'kuai_di');
                $saveData[] = array('weshare_id' => $share_id, 'status' => 1, 'ship_fee' => 0, 'tag' => 'self_ziti');
                $saveData[] = array('weshare_id' => $share_id, 'status' => -1, 'ship_fee' => 0, 'tag' => 'pys_ziti');
            }
        }
        $this->WeshareShipSetting->saveAll($saveData);
        echo json_encode(array('success' => true));
        return;
    }

    function rand_date($min_date, $max_date) {
        /* Gets 2 dates as string, earlier and later date.
           Returns date in between them.
        */
        $is_valid = true;
        $gen_date = '';
        while ($is_valid) {
            $rand_epoch = mt_rand($min_date, $max_date);
            $gen_date = date('Y-m-d H:i:s', $rand_epoch);
            $yh = getdate($gen_date);
            if ($yh['hours'] >= 6 && $yh['hours'] < 12) {
                $is_valid = false;
            }
        }
        return $gen_date;
    }

    public function admin_make_comment($num, $product_id, $weshare_id) {
        $this->autoRender = false;
        //$old_comment_distinct_comment = $this->Comment->query("select id , DISTINCT(user_id) from cake_comments where type='Product' and data_id=".$product_id." and order_id is not null limit 0,".$num);
        $old_comment_distinct_comment = $this->Comment->find('all', array(
            'conditions' => array(
                'data_id' => $product_id,
                'type' => 'Product',
                'not' => array('order_id' => null)
            ),
            'group' => 'user_id',
            'limit' => $num,
        ));
        $old_comment_ids = Hash::extract($old_comment_distinct_comment, '{n}.Comment.id');
        $old_product_comments = $this->Comment->find('all', array(
            'conditions' => array(
                'id' => $old_comment_ids
            ),
        ));
        $order_ids = Hash::extract($old_product_comments, '{n}.Comment.order_id');
        $user_ids = Hash::extract($old_product_comments, '{n}.Comment.user_id');
        $user_infos = $this->User->find('all', array(
            'conditions' => array(
                'id' => $user_ids
            )
        ));
        $order_infos = $this->Order->find('all', array(
            'conditions' => array(
                'id' => $order_ids
            )
        ));
        $user_infos = Hash::combine($user_infos, '{n}.User.id', '{n}.User');
        $order_infos = Hash::combine($order_infos, '{n}.Order.id', '{n}.Order');
        $weshare_products = $this->WeshareProduct->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id
            )
        ));
        foreach ($old_product_comments as $comment_item) {
            $comment_info = $comment_item['Comment'];
            $item_order_id = $comment_info['order_id'];
            $item_user_id = $comment_info['user_id'];
            $user_info = $user_infos[$item_user_id];
            $order_info = $order_infos[$item_order_id];
            $this->gen_order_has_comment($comment_info, $order_info, $weshare_products, $user_info, $weshare_id);
        }
        echo json_encode(array('success' => true));
    }

    public function admin_make_order($num = 1, $weshare_id) {
        $this->autoRender=false;
        /**
         * SELECT name FROM random AS r1 JOIN (SELECT CEIL(RAND() * (SELECT MAX(id) FROM random)) AS id) AS r2 WHERE r1.id >= r2.id ORDER BY r1.id ASC LIMIT 1
         *
         * select id, nickname, status, username from cake_users where status=9 limit 0,10
         */
        $users = $this->User->query('SELECT user.id, user.nickname, user.username FROM cake_users  AS user JOIN (SELECT CEIL(RAND() * (SELECT MAX(id) FROM cake_users)) AS id) AS r2 WHERE user.id >= r2.id and user.nickname not like "微信用户%" ORDER BY user.id ASC LIMIT '.$num);
        $weshare = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $weshare_id
            )
        ));
        $weshare_products = $this->WeshareProduct->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id
            )
        ));
        $weshare_addresses = $this->WeshareAddress->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id
            )
        ));
        $current_date = date('Y-m-d H:i:s');
        $rand_start = strtotime($current_date . ' -3 day');
        $rand_end = strtotime($current_date);
        foreach($users as $user){
            $order_date = $this->rand_date($rand_start,$rand_end);
            $this->gen_order($weshare, $user, $weshare_products, $weshare_addresses, $order_date);
        }
        echo json_encode(array('success' => true));
        return;
    }

    public function admin_index(){
        $weshare_count = $this->Weshare->find('count', array(
            'limit' => 5000
        ));

        $weshare_creator_count = $this->Weshare->find('count', array(
            'limit' => 5000,
            'fields' => 'DISTINCT creator'
        ));

        $join_weshare_count = $this->Order->find('count', array(
            'conditions' => array(
                'type' => 9,
                'DATE(created)' => date('Y-m-d'),
            ),
            'limit' => 15000,
            'fields' => array('DISTINCT creator')
        ));

        $order_count = $this->Order->find('count', array(
            'conditions' => array(
                'type' => 9,
                'DATE(created)' => date('Y-m-d'),
            )
        ));
        $share_pay_count = $this->Weshare->find('count',array(
            'conditions' => array(
                'status' => array(1,2),
                'settlement' => 0
            )
        ));
        $this->set('share_pay_count', $share_pay_count);
        $this->set('share_count', $weshare_count);
        $this->set('share_creator_count', $weshare_creator_count);
        $this->set('join_share_count', $join_weshare_count);
        $this->set('today_order_count', $order_count);
    }

    public function admin_all_shares() {
        $shares = $this->Weshare->find('all', array(
            'order' => array('created DESC'),
            'limit' => 200
        ));
        $shareIds = Hash::extract($shares, '{n}.Weshare.id');
        $products = $this->WeshareProduct->find('all',array(
            'conditions' => array(
                'weshare_id' => $shareIds
            )
        ));
        $share_product_map = array();
        foreach($products as $item){
            if(!isset($share_product_map[$item['WeshareProduct']['weshare_id']])){
                $share_product_map[$item['WeshareProduct']['weshare_id']] = array();
            }
            $share_product_map[$item['WeshareProduct']['weshare_id']][] = $item['WeshareProduct'];
        }
        $this->set('shares',$shares);
        $this->set('share_product_map',$share_product_map);
    }

    public function admin_share_orders(){
        $query_date = date('Y-m-d');
        $start_date  = $query_date;
        $end_date = $query_date;
        if($_REQUEST['start_date']){
            $start_date = $_REQUEST['start_date'];
        }
        if($_REQUEST['end_date']){
            $end_date = $_REQUEST['end_date'];
        }
        $cond = array(
            'type' => 9,
        );
        $request_order_id = $_REQUEST['order_id'];
        if($request_order_id){
                $cond['id'] = $request_order_id;
        }else{
            if($start_date==$end_date){
                $cond['DATE(created)'] = $query_date;
            }else{
                $cond['DATE(created) >='] = $start_date;
                $cond['DATE(created) <='] = $end_date;
            }
        }
        if($_REQUEST['weshare_id']){
            $query_share_id = $_REQUEST['weshare_id'];
        }
        $order_status = $_REQUEST['order_status'];
        if($order_status){
            $cond['status'] = array($order_status);
        }else{
            $cond['status'] = array(ORDER_STATUS_PAID,ORDER_STATUS_RECEIVED,ORDER_STATUS_SHIPPED);
        }
        if($query_share_id){
           $cond['member_id'] = $query_share_id;
        }
        $orders = $this->Order->find('all',array(
            'conditions' => $cond,
            'order' => array('created DESC')
        ));
        $total_price = 0;
        if(!empty($orders)){
            foreach($orders as $order){
                $total_price += $order['Order']['total_all_price'];
            }
            $order_ids = Hash::extract($orders, '{n}.Order.id');
            $member_ids = Hash::extract($orders, '{n}.Order.member_id');
            $weshares = $this->Weshare->find('all', array(
                'conditions' => array(
                    'id' => $member_ids
                )
            ));
            $creatorIds = Hash::extract($weshares,'{n}.Weshare.creator');
            $creators = $this->User->find('all',array(
                'conditions' => array(
                    'id' => $creatorIds
                ),
                'fields' => array('id', 'nickname', 'mobilephone')
            ));
            $creators = Hash::combine($creators,'{n}.User.id','{n}.User');
            $weshares = Hash::combine($weshares, '{n}.Weshare.id', '{n}.Weshare');
            $carts = $this->Cart->find('all',array(
                'conditions' => array(
                    'order_id' => $order_ids
                )
            ));
            $order_cart_map = array();
            foreach($carts as $item){
                $order_id = $item['Cart']['order_id'];
                if(!isset($order_cart_map[$order_id])){
                    $order_cart_map[$order_id] = array();
                }
                $order_cart_map[$order_id][] = $item['Cart'];
            }
            $summery_result = array('order_count' => count($orders), 'total_all_price' => $total_price);
            $this->set('summery',$summery_result);
            $this->set('start_date', $_REQUEST['start_date']);
            $this->set('end_date', $_REQUEST['end_date']);
            $this->set('orders',$orders);
            $this->set('order_cart_map',$order_cart_map);
            $this->set('weshares',$weshares);
            $this->set('weshare_creators',$creators);
        }
        $this->set('order_status',$order_status);
        $this->set('order_id', $request_order_id);
    }

    private function get_random_item($items){
        return $items[array_rand($items)];
    }

    private function gen_order_has_comment($comment_info, $order_info, $weshare_products, $user_info, $weshare_id) {
        $this->Order->id = null;
        $order_info['id'] = null;
        $order_info['member_id'] = $weshare_id;
        $order_info['creator'] = $user_info['id'];
        $order_info['type'] = ORDER_TYPE_WESHARE_BUY;
        $order_info['status'] = ORDER_STATUS_DONE;
        $order = $this->Order->save($order_info);
        $weshareProducts[] = $this->get_random_item($weshare_products);
        $order_date = $order['Order']['created'];
        $creator = $order['Order']['creator'];
        $weshare_id = $order['Order']['member_id'];
        $orderId = $order['Order']['id'];
        if (!empty($orderId)) {
            $totalPrice = 0;
            foreach ($weshareProducts as $p) {
                $item = array();
                $num = 1;
                $price = $p['WeshareProduct']['price'];
                $item['name'] = $p['WeshareProduct']['name'];
                $item['num'] = $num;
                $item['price'] = $price;
                $item['type'] = ORDER_TYPE_WESHARE_BUY;
                $item['product_id'] = $p['WeshareProduct']['id'];
                $item['created'] = $order_date;
                $item['updated'] = $order_date;
                $item['creator'] = $creator;
                $item['order_id'] = $orderId;
                $item['tuan_buy_id'] = $weshare_id;
                $cart[] = $item;
                $totalPrice += $num * $price;
            }
            $this->Cart->id = null;
            $this->Cart->saveAll($cart);
            $this->Order->updateAll(array('total_all_price' => $totalPrice / 100, 'total_price' => $totalPrice / 100, 'ship_fee' => 0, 'status' => ORDER_STATUS_DONE), array('id' => $orderId));
            $comment_info['user_id'] = $creator;
            $comment_info['username'] = $user_info['nickname'];
            $comment_info['data_id'] = $weshare_id;
            $comment_info['type'] = 'Share';
            $comment_info['order_id'] = $orderId;
            $comment_info['id'] = null;
            $this->Comment->id = null;
            $this->Comment->save($comment_info);
            return array('success' => true, 'orderId' => $orderId);
        }
    }

    private function gen_order($weshare, $user, $weshare_products, $weshare_address, $order_date, $address=null) {
        $weshareProducts = array();
        $weshareProducts[] = $this->get_random_item($weshare_products);
        $tinyAddress = $this->get_random_item($weshare_address);
        $cart = array();
        try {
            $mobile_phone = $this->randMobile(1);
            $addressId = 0;
            if($address){
                $order_consignee_address = $address;
            }else{
                $order_consignee_address = '虚拟订单';
            }
            if(!empty($tinyAddress)){
                $addressId = $tinyAddress['WeshareAddress']['id'];
                $order_consignee_address = $tinyAddress['WeshareAddress']['address'];
            }
            $weshare_id = $weshare['Weshare']['id'];
            $user = $user['user'];
            $user_name = $user['nickname'];
            $this->Order->id = null;
            $order = $this->Order->save(array('creator' => $user['id'], 'consignee_address' => $order_consignee_address, 'member_id' => $weshare['Weshare']['id'], 'type' => ORDER_TYPE_WESHARE_BUY, 'created' => $order_date, 'updated' => $order_date, 'consignee_id' => $addressId, 'consignee_name' => $user_name, 'consignee_mobilephone' => $mobile_phone[0]));
            $orderId = $order['Order']['id'];
            if(!empty($orderId)){
                $totalPrice = 0;
                foreach ($weshareProducts as $p) {
                    $item = array();
                    $num = 1;
                    $price = $p['WeshareProduct']['price'];
                    $item['name'] = $p['WeshareProduct']['name'];
                    $item['num'] = $num;
                    $item['price'] = $price;
                    $item['type'] = ORDER_TYPE_WESHARE_BUY;
                    $item['product_id'] = $p['WeshareProduct']['id'];
                    $item['created'] =$order_date;
                    $item['updated'] = $order_date;
                    $item['creator'] = $user['id'];
                    $item['order_id'] = $orderId;
                    $item['tuan_buy_id'] = $weshare_id;
                    $cart[] = $item;
                    $totalPrice += $num * $price;
                }
                $this->Cart->id = null;
                $this->Cart->saveAll($cart);
                $this->Order->updateAll(array('total_all_price' => $totalPrice / 100, 'total_price' => $totalPrice / 100, 'ship_fee' => 0, 'status' => ORDER_STATUS_VIRTUAL), array('id' => $orderId));
                //echo json_encode(array('success' => true, 'orderId' => $orderId));
                return array('success' => true, 'orderId' => $orderId);
            }
            return array('success' => false, 'msg' => 'order empty');
        } catch (Exception $e) {
            $this->log($user['id'] . 'buy share ' . $weshare_id . $e);
            //echo json_encode(array('success' => false, 'msg' => $e->getMessage()));
            return array('success' => false, 'msg' => $e->getMessage());
        }
    }

    private function findCarts($orderId){
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $orderId
            ),
            'fields' => array('id', 'order_id', 'name', 'product_id', 'num')
        ));
        return $carts;
    }

    private function get_cart_name_and_num($orderId) {
        $carts = $this->findCarts($orderId);
        $num = 0;
        $cart_name = array();
        foreach ($carts as $cart_item) {
            $num += $cart_item['Cart']['num'];
            $cart_name[] = $cart_item['Cart']['name'] . 'X' . $cart_item['Cart']['num'];
        }
        return array('num' => $num, 'cart_name' => implode(',', $cart_name));
    }

    private function get_offline_store($offlineStoreId){
        $offlineStore = $this->OfflineStore->find('first',array(
            'conditions' => array(
                'id' => $offlineStoreId
            )
        ));
        return $offlineStore;
    }

    /**
     * @desc 生成n个随机手机号
     * @param int $num 生成的手机号数
     * @author niujiazhu
     * @return array
     */
    function randMobile($num = 1){
        //手机号2-3为数组
        $numberPlace = array(30,31,32,33,34,35,36,37,38,39,50,51,58,59,89);
        for ($i = 0; $i < $num; $i++){
            $mobile = 1;
            $mobile .= $numberPlace[rand(0,count($numberPlace)-1)];
            $mobile .= str_pad(rand(0,99999999),8,0,STR_PAD_LEFT);
            $result[] = $mobile;
        }
        return $result;
    }

    public function getName($type = 0) {
        switch ($type) {
            case 1: //2字
                $name = $this->getXing() . $this->getMing();
                break;
            case 2: //随机2、3个字
                $name = $this->getXing() . $this->getMing();
                if (mt_rand(0, 100) > 50) $name .= $this->getMing();
                break;
            case 3: //只取姓
                $name = $this->getXing();
                break;
            case 4: //只取名
                $name = $this->getMing();
                break;
            case 0:
            default: //默认情况 1姓+2名
                $name = $this->getXing() . $this->getMing() . $this->getMing();
        }
        return $name;
    }

    private function getXing() {
        $arrXing = $this->getXingList();
        return $arrXing[mt_rand(0, count($arrXing))];
    }

    private function getMing() {
        $arrMing = $this->getMingList();
        return $arrMing[mt_rand(0, count($arrMing))];
    }

    private function getXingList() {
        $arrXing = array('赵', '钱', '孙', '李', '周', '吴', '郑', '王', '冯', '陈', '褚', '卫', '蒋', '沈', '韩', '杨', '朱', '秦', '尤', '许', '何', '吕', '施', '张', '孔', '曹', '严', '华', '金', '魏', '陶', '姜', '戚', '谢', '邹', '喻', '柏', '水', '窦', '章', '云', '苏', '潘', '葛', '奚', '范', '彭', '郎', '鲁', '韦', '昌', '马', '苗', '凤', '花', '方', '任', '袁', '柳', '鲍', '史', '唐', '费', '薛', '雷', '贺', '倪', '汤', '滕', '殷', '罗', '毕', '郝', '安', '常', '傅', '卞', '齐', '元', '顾', '孟', '平', '黄', '穆', '萧', '尹', '姚', '邵', '湛', '汪', '祁', '毛', '狄', '米', '伏', '成', '戴', '谈', '宋', '茅', '庞', '熊', '纪', '舒', '屈', '项', '祝', '董', '梁', '杜', '阮', '蓝', '闵', '季', '贾', '路', '娄', '江', '童', '颜', '郭', '梅', '盛', '林', '钟', '徐', '邱', '骆', '高', '夏', '蔡', '田', '樊', '胡', '凌', '霍', '虞', '万', '支', '柯', '管', '卢', '莫', '柯', '房', '裘', '缪', '解', '应', '宗', '丁', '宣', '邓', '单', '杭', '洪', '包', '诸', '左', '石', '崔', '吉', '龚', '程', '嵇', '邢', '裴', '陆', '荣', '翁', '荀', '于', '惠', '甄', '曲', '封', '储', '仲', '伊', '宁', '仇', '甘', '武', '符', '刘', '景', '詹', '龙', '叶', '幸', '司', '黎', '溥', '印', '怀', '蒲', '邰', '从', '索', '赖', '卓', '屠', '池', '乔', '胥', '闻', '莘', '党', '翟', '谭', '贡', '劳', '逄', '姬', '申', '扶', '堵', '冉', '宰', '雍', '桑', '寿', '通', '燕', '浦', '尚', '农', '温', '别', '庄', '晏', '柴', '瞿', '阎', '连', '习', '容', '向', '古', '易', '廖', '庾', '终', '步', '都', '耿', '满', '弘', '匡', '国', '文', '寇', '广', '禄', '阙', '东', '欧', '利', '师', '巩', '聂', '关', '荆', '司马', '上官', '欧阳', '夏侯', '诸葛', '闻人', '东方', '赫连', '皇甫', '尉迟', '公羊', '澹台', '公冶', '宗政', '濮阳', '淳于', '单于', '太叔', '申屠', '公孙', '仲孙', '轩辕', '令狐', '徐离', '宇文', '长孙', '慕容', '司徒', '司空');
        return $arrXing;
    }

    /*
      获取名列表
    */
    private function getMingList() {
        $arrMing = array('伟', '刚', '勇', '毅', '俊', '峰', '强', '军', '平', '保', '东', '文', '辉', '力', '明', '永', '健', '世', '广', '志', '义', '兴', '良', '海', '山', '仁', '波', '宁', '贵', '福', '生', '龙', '元', '全', '国', '胜', '学', '祥', '才', '发', '武', '新', '利', '清', '飞', '彬', '富', '顺', '信', '子', '杰', '涛', '昌', '成', '康', '星', '光', '天', '达', '安', '岩', '中', '茂', '进', '林', '有', '坚', '和', '彪', '博', '诚', '先', '敬', '震', '振', '壮', '会', '思', '群', '豪', '心', '邦', '承', '乐', '绍', '功', '松', '善', '厚', '庆', '磊', '民', '友', '裕', '河', '哲', '江', '超', '浩', '亮', '政', '谦', '亨', '奇', '固', '之', '轮', '翰', '朗', '伯', '宏', '言', '若', '鸣', '朋', '斌', '梁', '栋', '维', '启', '克', '伦', '翔', '旭', '鹏', '泽', '晨', '辰', '士', '以', '建', '家', '致', '树', '炎', '德', '行', '时', '泰', '盛', '雄', '琛', '钧', '冠', '策', '腾', '楠', '榕', '风', '航', '弘', '秀', '娟', '英', '华', '慧', '巧', '美', '娜', '静', '淑', '惠', '珠', '翠', '雅', '芝', '玉', '萍', '红', '娥', '玲', '芬', '芳', '燕', '彩', '春', '菊', '兰', '凤', '洁', '梅', '琳', '素', '云', '莲', '真', '环', '雪', '荣', '爱', '妹', '霞', '香', '月', '莺', '媛', '艳', '瑞', '凡', '佳', '嘉', '琼', '勤', '珍', '贞', '莉', '桂', '娣', '叶', '璧', '璐', '娅', '琦', '晶', '妍', '茜', '秋', '珊', '莎', '锦', '黛', '青', '倩', '婷', '姣', '婉', '娴', '瑾', '颖', '露', '瑶', '怡', '婵', '雁', '蓓', '纨', '仪', '荷', '丹', '蓉', '眉', '君', '琴', '蕊', '薇', '菁', '梦', '岚', '苑', '婕', '馨', '瑗', '琰', '韵', '融', '园', '艺', '咏', '卿', '聪', '澜', '纯', '毓', '悦', '昭', '冰', '爽', '琬', '茗', '羽', '希', '欣', '飘', '育', '滢', '馥', '筠', '柔', '竹', '霭', '凝', '晓', '欢', '霄', '枫', '芸', '菲', '寒', '伊', '亚', '宜', '可', '姬', '舒', '影', '荔', '枝', '丽', '阳', '妮', '宝', '贝', '初', '程', '梵', '罡', '恒', '鸿', '桦', '骅', '剑', '娇', '纪', '宽', '苛', '灵', '玛', '媚', '琪', '晴', '容', '睿', '烁', '堂', '唯', '威', '韦', '雯', '苇', '萱', '阅', '彦', '宇', '雨', '洋', '忠', '宗', '曼', '紫', '逸', '贤', '蝶', '菡', '绿', '蓝', '儿', '翠', '烟');
        return $arrMing;
    }

}