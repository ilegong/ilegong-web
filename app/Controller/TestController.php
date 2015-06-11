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

    public function test_get_option_date(){
        $this->autoRender = false;
        $date = get_consignment_date('3','2,4,6','17,30');
        echo json_encode(array('success' => true,'date' => $date));
        return;
    }

}