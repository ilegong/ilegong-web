<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/1/15
 * Time: 11:27
 */


App::import('Vendor', 'LocationHelper', array('file' => 'LocationHelper/Coordinate.php'));
App::import('Vendor', 'LocationHelper', array('file' => 'LocationHelper/Distance/Vincenty.php'));
App::import('Vendor', 'LocationHelper', array('file' => 'LocationHelper/Distance/Haversine.php'));
class TestController extends AppController
{

    public $components = array('Weixin', 'WeshareBuy', 'OrderExpress', 'Logistics', 'PintuanHelper', 'ShareUtil', 'RedisQueue', 'DeliveryTemplate', 'JPush', 'SharePush', 'Auth', 'Balance');
    public $uses = array('Order', 'Oauthbind');


//    public function save_user_relation()
//    {
//        $this->autoRender = false;
//        $uids = array(1156, 1412, 1495, 9134, 12376, 23771, 24086, 68832, 433224, 506391, 544307, 559795, 633345, 711503, 660240, 701166, 710486, 801447, 801818, 806889, 807492, 810684,  813896, 815328, 816006, 842908);
//        $saveData = array();
//        foreach($uids as $uid){
//            $saveData[] = array('user_id' => 895096, 'follow_id' => $uid, 'type' => 'SUB', 'created' => date('Y-m-d H:i:s'));
//        }
//        $userRelationM = ClassRegistry::init('UserRelation');
//        $userRelationM->saveAll($saveData);
//        echo json_encode(array('success' => true));
//        return;
//    }


//    public function test_delivery_template(){
//        $this->autoRender = false;
//        $ship_fee = $this->DeliveryTemplate->calculate_ship_fee(1, 0, 53);
//        echo json_encode(array('testCase' => 1, 'ship_fee' => $ship_fee));
//        echo '<br>';
//        $ship_fee = $this->DeliveryTemplate->calculate_ship_fee(2, 0, 53);
//        echo json_encode(array('testCase' => 2, 'ship_fee' => $ship_fee));
//        echo '<br>';
//        $ship_fee = $this->DeliveryTemplate->calculate_ship_fee(1, 0, 59);
//        echo json_encode(array('testCase' => 3, 'ship_fee' => $ship_fee));
//        echo '<br>';
//        $ship_fee = $this->DeliveryTemplate->calculate_ship_fee(5, 0, 59);
//        echo json_encode(array('testCase' => 4, 'ship_fee' => $ship_fee));
//        echo '<br>';
//        $ship_fee = $this->DeliveryTemplate->calculate_ship_fee(1, 310000, 60);
//        echo json_encode(array('testCase' => 5, 'ship_fee' => $ship_fee));
//        echo '<br>';
//        $ship_fee = $this->DeliveryTemplate->calculate_ship_fee(2, 540000, 60);
//        echo json_encode(array('testCase' => 6, 'ship_fee' => $ship_fee));
//        echo '<br>';
//        $ship_fee = $this->DeliveryTemplate->calculate_ship_fee(2, 110000, 60);
//        echo json_encode(array('testCase' => 7, 'ship_fee' => $ship_fee));
//        return;
//    }


//    public function test_push_buy_msg()
//    {
//        $this->autoRender = false;
//        $order = $this->Order->find('first', array(
//            'conditions' => array(
//                'id' => 75394
//            )
//        ));
//        $this->Weixin->notifyPaidDone($order);
//        echo '1';
//        exit();
//    }
//
//    public function test_push_comment_msg()
//    {
//        $this->autoRender = false;
//
//        echo '1';
//        exit();
//    }


//    public function test_get_view_count(){
//        $weshareM = ClassRegistry::init('Weshare');
//        $share_view_count = $weshareM->query("select sum(view_count) from cake_weshares where creator = 633345");
//        echo json_encode($share_view_count[0][0]['sum(view_count)']);
//        exit;
//    }
//
//    public function test_get_sharer_order_summary(){
//        $uid = 633345;
//        $start_date = '2016-05-01 00:00:00';
//        $end_date = '2016-06-17 00:00:00';
//        $result = $this->WeshareBuy->get_sharer_order_summary($uid, $start_date, $end_date);
//        echo json_encode($result);
//        exit;
//    }

//    public function test_get_sharer_order_summary(){
//        $uid = 810684;
//        $start_date = '2016-05-01 00:00:00';
//        $end_date = '2016-06-17 00:00:00';
//        $result = $this->WeshareBuy->get_days_order_summary($uid, $start_date, $end_date);
//        echo json_encode($result);
//        exit;
//    }

//    public function test_get_fans_info(){
//        $data = $this->ShareUtil->get_fans_info_list_by_sql(10, 1, '', 141);
//        echo json_encode($data);
//        exit;
//    }
//
//    public function test_get_fans_info_list(){
//        $result = $this->ShareUtil->get_fans_info_list(10, 1, null, 141);
//        echo json_encode($result);
//        exit;
//    }

//    public function test_get_payment_info(){
//        $pay_info = '{"full_name":"peng","account":"6666666","card_name":"","type":"0"}';
//        $pay = get_user_payment_info($pay_info);
//        echo $pay;
//        exit;
//    }


//    public function get_going_balance_list(){
//        $result = $this->Balance->get_going_share_list(141, 1, 10);
//        echo json_encode($result);
//        exit;
//    }


//    public function set_user_money(){
//        $sql = 'SELECT user_id, sum(money) FROM `cake_rebate_logs` group by user_id';
//        $this->loadModel('User');
//        $data = $this->User->query($sql);
//        foreach($data as $item){
//            $uid = $item['cake_rebate_logs']['user_id'];
//            $money = $item['0']['sum(money)'];
//            $this->User->updateAll(['rebate_money' => $money], ['id' => $uid]);
//        }
//        echo json_encode($data);
//        exit;
//    }

public function test_get_user_rebate_money(){
    $this->loadModel('User');
    $a = $this->User->get_rebate_money(141);
    $u = $this->User->find('first',['conditions' => ['id' => 141], 'fields' => ['id', 'rebate_money', 'nickname']]);
    $c = $u['User']['rebate_money'];
    echo json_encode(['a' => $a, 'c' => $c, 'u' => $u['User']]);
    exit;
}


//    public function migrate_user_rebate_money($limit){
//        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
//        $trackLogs = $rebateTrackLogM->find('all', [
//            'conditions' => ['order_id >' => 0, 'is_paid' => 1, 'rebate_money >' => 0, 'is_rebate' => 0],
//            'limit' => $limit
//        ]);
//        $log_ids = [];
//        foreach ($trackLogs as $trackLogItem) {
//            $log_ids[] = $trackLogItem['RebateTrackLog']['id'];
//            $uid = $trackLogItem['RebateTrackLog']['sharer'];
//            $money = $trackLogItem['RebateTrackLog']['rebate_money'];
//            $reason = USER_REBATE_MONEY_GOT;
//            $order_id = $trackLogItem['RebateTrackLog']['order_id'];
//            $this->ShareUtil->add_rebate_log($uid, $money, $reason, $order_id);
//        }
//        $rebateTrackLogM->updateAll(['is_rebate' => 1], ['id' => $log_ids]);
//        echo json_encode(['success' => true, 'count' => count($trackLogs), 'track_log_ids' => $log_ids]);
//        exit;
//    }

    public function add_oauth_client(){
        $this->OAuth = $this->Components->load('OAuth.OAuth');
        $client = $this->OAuth->Client->add('http://www.cmlejia.com/');
        echo json_encode($client);
        exit;
    }

    public function test_gen_password(){
        echo $this->Auth->password($_REQUEST['password']);
        exit;
    }


    public function test_load_fans_by_uid(){
        $this->autoRender = false;
        $result = $this->WeshareBuy->load_fans_buy_sharer(633345, $limit = 10, $offset = 0);
        echo json_encode($result);
        exit;
    }

    public function set_root_id($offset,$limit)
    {
        $this->autoRender = false;
        $weshareM = ClassRegistry::init('Weshare');
        $weshares = $weshareM->find('all', [
            'conditions' => [
                'type' => 0,
                'not' => ['refer_share_id' => 0]
            ],
            'offset' => $offset,
            'limit' => $limit,
            'order' => ['id DESC']
        ]);
        $save_data = [];
        foreach ($weshares as $share_item) {
            $share_item_id = $share_item['Weshare']['id'];
            $root_share_id = $weshareM->get_root_share_id($share_item_id);
            if (empty($root_share_id)) {
                $root_share_id = 0;
            }
            $save_data[] = ['id' => $share_item_id, 'root_share_id' => $root_share_id];
        }
        $weshareM->saveAll($save_data);
        echo 'page' . $limit;
        exit();
    }



    public function test_push(){
        $this->autoRender=false;
        $result = $this->JPush->push('893376');
        echo json_encode($result);
        return;
    }


    public function add_queue(){
        $this->autoRender = false;
        $this->RedisQueue->add_curl_task();
        echo json_encode(array('success' => true));
        return;
    }

    public function update_old_avatar()
    {
        $this->autoRender = false;
        $userM = ClassRegistry::init('User');
        $users = $userM->find('all', array(
            'conditions' => array(
                'avatar LIKE' => 'avatar/http://51daifan%'
            ),
            'limit' => 100
        ));
        $save_data = array();
        foreach ($users as $user) {
            $uid = $user['User']['id'];
            $image = $user['User']['image'];
            $avatar = create_avatar_in_aliyun($image);
            $save_data[] = array('id' => $uid, 'avatar' => $avatar);
        }
        $userM->saveAll($save_data);
        echo json_encode(array('success' => true));
        return;
    }

    public function test_query_share_ids($share_id, $uid){
        $this->autoRender = false;
        $weshareM = ClassRegistry::init('Weshare');
        $result = $weshareM->get_relate_share($share_id, $uid);
        $share_buy_result = $this->WeshareBuy->get_share_and_all_refer_share_count($share_id, $uid);
        echo json_encode(array('success' => true, 'result' => $result, 'share_buy_result' => $share_buy_result));
        return;
    }

    public function send_tmp_msg()
    {
        $this->autoRender = false;
        $userId = 697674;
        $openId = $this->Oauthbind->findWxServiceBindByUid($userId);
        $title = 'hi,你在我们平台订了猕猴桃和无花果，现在您留的电话不正确，请迅速和我们联系：13651031953，';
        $order_id = 37377;
        $order_date = '2015-09-10 21:14:32';
        $desc = '生鲜不能存放，谢谢理解！';
        $detail_url = '';
        $this->Weixin->send_comment_template_msg($openId, $detail_url, $title, $order_id, $order_date, $desc);
        echo json_encode(array('success' => true));
    }

    public function test_send_tuan_buy_msg($orderId)
    {
        $this->autoRender = false;
        $order = $this->Order->find('first', array(
            'conditions' => array('id' => $orderId)));
        $this->Weixin->notifyPaidDone($order);

        echo 'success';
        exit;
    }


    public function test_send_msg_to_hx($shareId, $gid, $creator)
    {
        $result = $this->ShareUtil->send_buy_msg_to_hx($shareId, $gid, $creator);
        echo json_encode($result);
        exit;
    }

    public function test_set_order_paid_done($orderId)
    {
        $this->Order->set_order_to_paid($orderId, 0, 633345, 1, $memberId = 0);
    }

    public function test_order_paid_done($orderId)
    {
        $this->autoRender = false;
        $this->loadModel('Order');
        $this->Order->set_order_to_paid($orderId, 0, 633345, 5, $memberId = 0);
        echo json_encode(array('success' => true));
        return;
    }

    public function test_get_option_date()
    {
        $this->autoRender = false;
        $date = get_consignment_date('3', '2,4,6', '17,30');
        echo json_encode(array('success' => true, 'date' => $date));
        return;
    }

    public function test_get_send_date()
    {
        $this->autoRender = false;
        $date = get_send_date('2', '19:00:00', '2,4,6');
        echo json_encode(array('success' => true, 'data' => $date));
        return;
    }

    public function test_send_ship_code()
    {
        $this->autoRender = false;
        $this->WeshareBuy->send_share_product_ship_msg(32, 22);
        echo json_encode(array('success' => true));
    }

    public function test_get_match_location()
    {
        $this->autoRender = false;
        $this->loadModel('OfflineStore');
        //116.336402,40.06276
        //116.336145,40.062573
        $coordinate = new LocationHelper\Coordinate(40.062573, 116.336145);
        $squrePoint = $coordinate->getSquarePoint($coordinate);

        $offlineStore = $this->OfflineStore->find('all', array(
            'conditions' => array(
                'location_lat >=' => $squrePoint['right-bottom']['lat'],
                'location_lat <=' => $squrePoint['left-top']['lat'],
                'location_long >=' => $squrePoint['left-top']['lng'],
                'location_long <=' => $squrePoint['right-bottom']['lng'],
                'not' => array(
                    'location_lat' => 0,
                    'location_long' => 0
                )
            )
        ));
        $offlineStore = Hash::combine($offlineStore, '{n}.OfflineStore.id', '{n}.OfflineStore.name');
        echo json_encode($offlineStore);
    }

    public function test_wx_location()
    {
        $this->autoRender = false;
        $coordinate1 = new LocationHelper\Coordinate(19.820664, -155.468066); // Mauna Kea Summit
        $coordinate2 = new LocationHelper\Coordinate(20.709722, -156.253333); // Haleakala Summit
        //$this->set('distance', $this->cal_tow_point_distance($coordinate1, $coordinate2));
        echo json_encode(array('distance' => $this->cal_tow_point_distance($coordinate1, $coordinate2)));
        return;
    }

    private function cal_tow_point_distance($p1, $p2)
    {
        $calculator = new LocationHelper\Distance\Vincenty();
        return $calculator->getDistance($p1, $p2);
    }

    public function test_send_msg_for_creator($orderId)
    {
        $this->autoRender = false;
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'id' => $orderId
            )
        ));
        $this->Weixin->notifyPaidDone($order);
        echo json_encode(array('success' => true));
        return;
    }

    public function test_send_weshare_new_msg($weshareId)
    {
        $this->autoRender = false;
        $this->WeshareBuy->send_new_share_msg($weshareId);
        echo json_encode(array('success' => true));
    }

    public function test_product_id_map()
    {
        $this->autoRender = false;
        $result = $this->WeshareBuy->get_product_id_map_by_origin_ids(53);
        echo json_encode(array('success' => true, 'result' => $result));
    }

    public function get_ship_code_result()
    {
        $this->autoRender = false;
        $code = $_REQUEST['code'];
        $ip = $this->get_ip();
        $result = $this->OrderExpress->get_express_info($code, $ip);
        echo json_encode(array('success' => true, 'result' => $result));
    }

    public function logistics_test($id)
    {
        $this->autoRender = false;
        $result = $this->Logistics->notifyPaidDone($id);
        echo json_encode(array('success' => true, 'result' => $result));
    }

    public function test_logistics_paid($logistics_order_id)
    {
        $this->autoRender = false;
        $this->Logistics->send_logistics_order_paid_msg($logistics_order_id);
        echo json_encode(array('success' => true));
    }

    public function test_logistics_notify_receive($logistics_no)
    {
        $this->autoRender = false;
        $title = '快递已接单，请您耐心等待。';
        $remark = '点击查看详情！';
        $this->Logistics->send_logistics_order_notify_msg($title, $remark, $logistics_no);
        echo json_encode(array('success' => true));
    }

    public function test_logistics_notify_cancel($logistics_no)
    {
        $this->autoRender = false;
        $title = '快递呼叫超时，您可再次呼叫。';
        $remark = '再次呼叫快递小伙儿～～';
        $this->Logistics->send_logistics_order_notify_msg($title, $remark, $logistics_no);
        echo json_encode(array('success' => true));
    }

    public function test_pintuan_order_paid($orderId)
    {
        $this->autoRender = false;
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'id' => $orderId
            )
        ));
        $this->Weixin->notifyPaidDone($order);
        echo json_encode(array('success' => true));
    }

    public function test_send_pintuan_msg()
    {
        $this->autoRender = false;
        $this->PintuanHelper->send_pintuan_success_msg(1941, 2, 802852);
        echo json_encode(array('success' => true));
    }

    public function test_push_msg(){
        $this->autoRender = false;
        $this->SharePush->push_buy_msg(['user_id' => 633345, 'thumbnail' => 'www.baidu.com', 'reply_content' => 'hello world'], ['title' => 'test', 'creator' => 811917]);
        echo 'true';
        exit;
    }

}