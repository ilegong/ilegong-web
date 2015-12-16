<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/1/15
 * Time: 11:27
 */

App::import('Vendor', 'Location', array('file' => 'Location/Coordinate.php'));
App::import('Vendor', 'Location', array('file' => 'Location/Distance/Vincenty.php'));
App::import('Vendor', 'Location', array('file' => 'Location/Distance/Haversine.php'));

class TestController extends AppController{

    public $components = array('Weixin', 'WeshareBuy', 'OrderExpress', 'Logistics');
    public $uses = array('Order', 'Oauthbind');


    public function send_tmp_msg() {
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

    public function test_send_tuan_buy_msg($orderId){
        $order = $this->Order->find('first',array(
            'conditions'=>array('id' => $orderId)));
        $this->Weixin->notifyPaidDone($order);
    }

    public function test_set_order_paid_done($orderId){
        $this->Order->set_order_to_paid($orderId, 0, 633345, 1, $memberId=0);
    }

    public function test_order_paid_done($orderId){
        $this->autoRender = false;
        $this->loadModel('Order');
        $this->Order->set_order_to_paid($orderId, 0, 633345, 5, $memberId=0);
        echo json_encode(array('success' => true));
        return;
    }

    public function test_get_option_date(){
        $this->autoRender = false;
        $date = get_consignment_date('3','2,4,6','17,30');
        echo json_encode(array('success' => true,'date' => $date));
        return;
    }

    public function test_get_send_date(){
        $this->autoRender = false;
        $date = get_send_date('2', '19:00:00', '2,4,6');
        echo json_encode(array('success' => true,'data' => $date));
        return;
    }

    public function test_send_ship_code(){
        $this->autoRender = false;
        $this->WeshareBuy->send_share_product_ship_msg(32,22);
        echo json_encode(array('success' => true));
    }

    public function test_get_match_location(){
        $this->autoRender=false;
        $this->loadModel('OfflineStore');
        //116.336402,40.06276
        //116.336145,40.062573
        $coordinate = new Location\Coordinate(40.062573,116.336145);
        $squrePoint = $coordinate->getSquarePoint($coordinate);

        $offlineStore = $this->OfflineStore->find('all',array(
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
        $offlineStore = Hash::combine($offlineStore,'{n}.OfflineStore.id','{n}.OfflineStore.name');
        echo json_encode($offlineStore);
    }

    public function test_wx_location() {
        $coordinate1 = new Location\Coordinate(19.820664, -155.468066); // Mauna Kea Summit
        $coordinate2 = new Location\Coordinate(20.709722, -156.253333); // Haleakala Summit
        $this->set('distance', $this->cal_tow_point_distance($coordinate1, $coordinate2));
    }

    private function cal_tow_point_distance($p1, $p2) {
        $calculator = new Location\Distance\Vincenty();
        return $calculator->getDistance($p1, $p2);
    }

    public function test_send_msg_for_creator($orderId){
        $this->autoRender = false;
        $order = $this->Order->find('first',array(
            'conditions' => array(
                'id' => $orderId
            )
        ));
        $this->Weixin->notifyPaidDone($order);
        echo json_encode(array('success' => true));
        return;
    }

    public function test_send_weshare_new_msg($weshareId){
        $this->autoRender = false;
        $this->WeshareBuy->send_new_share_msg($weshareId);
        echo json_encode(array('success' => true));
    }

    public function test_product_id_map(){
        $this->autoRender = false;
        $result = $this->WeshareBuy->get_product_id_map_by_origin_ids(53);
        echo json_encode(array('success' => true, 'result' => $result));
    }

    public function get_ship_code_result(){
        $this->autoRender = false;
        $code = $_REQUEST['code'];
        $ip = $this->get_ip();
        $result = $this->OrderExpress->get_express_info($code, $ip);
        echo json_encode(array('success' => true, 'result' => $result));
    }

    public function logistics_test($id){
        $this->autoRender = false;
        $result = $this->Logistics->notifyPaidDone($id);
        echo json_encode(array('success' => true, 'result' => $result));
    }

    public function test_logistics_paid($logistics_order_id){
        $this->autoRender = false;
        $this->Logistics->send_logistics_order_paid_msg($logistics_order_id);
        echo json_encode(array('success' => true));
    }

}