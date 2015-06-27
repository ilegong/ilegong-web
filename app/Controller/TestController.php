<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/1/15
 * Time: 11:27
 */


class TestController extends AppController{

    public $components = array('Weixin');
    public $uses = array('Order');
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

    public function test_wx_location(){
        App::import('Vendor', 'Location', array('file' => 'Location/Coordinate.php'));
        App::import('Vendor', 'Location', array('file' => 'Location/Distance/Vincenty.php'));
        $coordinate1 = new Location\Coordinate(19.820664, -155.468066); // Mauna Kea Summit
        $coordinate2 = new Location\Coordinate(20.709722, -156.253333); // Haleakala Summit
        $calculator = new Location\Distance\Vincenty();
        $this->set('distance',$calculator->getDistance($coordinate1, $coordinate2));
        //echo $calculator->getDistance($coordinate1, $coordinate2);
        // returns 128130.850 (meters; ≈128 kilometers)
    }

}