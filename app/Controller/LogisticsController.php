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

    /**
     * 取消人人订单
     */
    public function cancel_rr_logistics_order() {
        $this->autoRender = false;
//$params ["userName"];
//$params ["businessNo"];
//$params ["reason"];
//$params ["sign"]; strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $businessNo ) ) ) )

//$result ["isSuccess"];
//$result ["errMsg"];
//$result ["warn"];
//$result ["orderNo"];
//$result ["businessNo"];
        $params = array();
        $reason = $_REQUEST['reason'];
        $businessNo = $_REQUEST['businessNo'];
        $params['reason'] = $reason;
        $params['userName'] = RR_LOGISTICS_USERNAME;
        $params['businessNo'] = $businessNo;
        $sign_keyword = RR_LOGISTICS_USERNAME . $businessNo;
        $sign = $this->Logistics->get_sign($sign_keyword);
        $params['sign'] = $sign;
        $result = $this->ThirdPartyExpress->cancel_rr_order($params);
        echo $result;
        return;
    }

    public function cal_rr_ship_fee() {
        $this->autoRender = false;
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
    public function confirm_logistics_order() {
        $this->autoRender = false;
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
        //支付订单
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

    /**
     * 人人快递回调接口
     */
    public function rr_logistics_callback() {
        $this->autoRender = false;
        $msgType = $_POST['msgType'];
        $orderNo = $_POST['orderNo'];
        $businessNo = $_POST['businessNo'];
        $sign = $_POST['sign'];
        if ($this->valid_rr_sign($sign, $orderNo, $businessNo)) {
            switch ($msgType) {
                case 1: //接单
                    $this->handle_rr_receive($businessNo, $orderNo);
                    break;
                case 2: //取件
                    $this->handle_rr_take($businessNo, $orderNo);
                    break;
                case 3: //签收
                    $this->handle_rr_sign($businessNo, $orderNo);
                    break;
                case 4: //超时
                    $this->handle_rr_timeout_cancel($businessNo, $orderNo);
                    break;
                case 5: //客服取消订单
                    $this->handle_rr_cancel_by_rr($businessNo, $orderNo);
                    break;
                case 6: //客服取消接单
                    $this->handle_rr_cancel_receive_by_rr($businessNo, $orderNo);
                    break;
                case 7: //客服协助接单
                    $this->handle_rr_receive_by_rr($businessNo, $orderNo);
                    break;
                case 8: //客服协助取件
                    $this->handle_rr_take_by_rr($businessNo, $orderNo);
                    break;
                case 9: //客服协助签收
                    $this->handle_rr_sign_by_rr($businessNo, $orderNo);
                    break;
            }
        }
        echo json_encode(array('success' => false));
        return;
    }

    private function valid_rr_sign($sign, $orderNo, $businessNo) {
        $sign_keyword = RR_LOGISTICS_USERNAME . $orderNo . $businessNo;
        $valid_sign = $this->Logistics->get_sign($sign_keyword);
        return $valid_sign == $sign;
    }


    private function handle_rr_receive($business_no, $business_order_id) {
        //人人快递接单 1
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_RECEIVE, $business_no, $business_order_id);
    }

    private function handle_rr_take($business_no, $business_order_id) {
        //人人快递取件 2
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_TAKE, $business_no, $business_order_id);
    }


    private function handle_rr_sign($business_no, $business_order_id) {
        //人人快递签收 3
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_SIGN, $business_no, $business_order_id);
    }

    private function handle_rr_timeout_cancel($business_no, $business_order_id) {
        //人人快递超时取消 4
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_CANCEL, $business_no, $business_order_id);
    }

    private function handle_rr_cancel_by_rr($business_no, $business_order_id) {
        //取消订单 5
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_CANCEL, $business_no, $business_order_id);
    }

    private function handle_rr_cancel_receive_by_rr($business_no, $business_order_id) {
        //取消接单 6
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_CANCEL, $business_no, $business_order_id);
    }

    private function handle_rr_receive_by_rr($business_no, $business_order_id) {
        //接单 7
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_RECEIVE, $business_no, $business_order_id);
    }

    private function handle_rr_take_by_rr($business_no, $business_order_id) {
        //取件 8
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_TAKE, $business_no, $business_order_id);
    }

    private function handle_rr_sign_by_rr($business_no, $business_order_id) {
        //签收 9
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_SIGN, $business_no, $business_order_id);
    }

}