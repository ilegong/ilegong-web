<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 12/8/15
 * Time: 14:16
 *
 * 第三方物流
 */
class LogisticsController extends AppController {

    public $name = 'logistics';

    public $components = array('ThirdPartyExpress', 'Logistics');

    public function cal_rr_ship_fee() {
//$params ["userName"];
//$params ["goodsWeight"];
//$params ["goodsWorth"];
//$params ["mapFrom"];  op
//$params ["startingPhone"];
//$params ["startingProvince"]; op 仅直辖市和特别行政区可以为空
//$params ["startingCity"];
//$params ["startingAddress"];
//$params ["startingLng"]; op
//$params ["startingLat"]; op
//$params ["consigneeProvince"]; op 仅直辖市和特别行政区可以为空
//$params ["consigneeCity"];
//$params ["consigneeAddress"];
//$params ["consigneeLng"]; op
//$params ["consigneeLat"]; op
//$params ["pickupTime"]; op
//$params ["sign"];  strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $startingAddress . $consigneeAddress ) ) ) )

//$result ["isSuccess"]; String
//$result ["errMsg"]; String
//$result ["warn"]; String
//$result ["price"]; Double

        $json_params = $_REQUEST['params'];
        $params = json_encode($json_params, true);
        $sign_keyword = $params ["userName"] . $params ["startingAddress"] . $params ["consigneeAddress"];
        $params['sign'] = $this->Logistics->get_sign($sign_keyword);
        $result = $this->ThirdPartyExpress->calculate_rr_logistics_cost($params);
        echo $result;
        return;
    }

    /**
     * 确认生成订单
     */
    public function confirm_logistics_order(){
        $json_params = $_REQUEST['params'];
        $params = json_decode($json_params, true);
        $result = $this->ThirdPartyExpress->create_logistics_order_from_rr($params);
        echo json_encode($result);
        return;
    }

    /**
     * @param $type
     * 支付物流订单
     */
    public function pay_logistics_order($type){

    }

    public function confirm_rr_order(){
        $json_params = $_REQUEST['params'];
        $params = json_decode($json_params, true);
        $sign_key_word = $params['userName'] . $params ["startingAddress"] . $params ["consigneeAddress"];
        $params['sign'] = $this->Logistics->get_sign($sign_key_word);
        $result = $this->ThirdPartyExpress->confirm_rr_order($params);
        echo $result;
        return;
    }

    public function rr_logistics_callback(){
        $this->autoRender = false;
        $msgType = $_POST['msgType'];
        $orderNo = $_POST['orderNo'];
        $businessNo = $_POST['businessNo'];
        $createTime = $_POST['createTime'];
        $sign = $_POST['sign'];
        if($this->valid_rr_sign($sign, $orderNo, $businessNo)){

        }
        echo json_encode(array('success' => false));
        return;
    }

    private function valid_rr_sign($sign, $orderNo, $businessNo) {
        $sign_keyword = RR_LOGISTICS_USERNAME . $orderNo . $businessNo;
        $valid_sign = $this->Logistics->get_sign($sign_keyword);
        return $valid_sign == $sign;
    }


    private function handle_rr_receive() {
        //人人快递接单 1
    }

    private function handle_rr_take() {
        //人人快递取件 2
    }


    private function handle_rr_sign() {
        //人人快递签收 3
    }

    private function handle_rr_timeout_cancel() {
        //人人快递超时取消 4
    }

    private function handle_rr_cancel_by_rr() {
        //取消订单 5
    }

    private function handle_rr_cancel_receive_by_rr() {
        //取消接单 6
    }

    private function handle_rr_receive_by_rr() {
        //接单 7
    }

    private function handle_rr_take_by_rr() {
        //取件 8
    }

    private function handle_rr_sign_by_rr() {
        //签收 9
    }

}