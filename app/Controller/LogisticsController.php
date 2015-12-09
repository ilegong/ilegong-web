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

    public $uses = array('Order', 'Cart');

    /**
     * @param $orderId
     * 呼叫人人快递
     */
    public function rr_logistics($orderId) {
        $this->layout = null;
        $order_info = $this->get_order_info($orderId);
        if (empty($order_info)) {
            $this->redirect('/weshares/index.html');
            return;
        }
        $this->set('order_info', $order_info);
    }

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
        $order_id = $_REQUEST['order_id'];
        $order_info = $this->get_order_info($order_id);
        $startingAddress = $order_info['Order']['consignee_address'];
        $startingAddress = str_replace('，', '', $startingAddress);
        $json_params = $_REQUEST['params'];
        $params = json_decode($json_params, true);
        $sign_keyword = RR_LOGISTICS_USERNAME . $startingAddress . $params ["consigneeAddress"];
        $params['sign'] = $this->Logistics->get_sign($sign_keyword);
        $params['goodsWeight'] = 1;
        $params['goodsWorth'] = intval($order_info['Order']['total_all_price']);
        $params['startingAddress'] = $startingAddress;
        $params['startingCity'] = '北京市';
        $params['consigneeCity'] = '北京市';
        $params['startingPhone'] = SERVICE_LINE_PHONE;
        $params['userName'] = RR_LOGISTICS_USERNAME;
        $result = $this->ThirdPartyExpress->calculate_rr_logistics_cost($params);
        echo $result;
        return;
    }

    /**
     * 确认生成系统订单
     */
    public function confirm_logistics_order() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $order_id = $_REQUEST['orderId'];
        $order_info = $this->get_order_info($order_id);
        if(empty($order_info)){
            echo json_encode(array('success' => false, 'reason' => 'not_order'));
            return;
        }
        $starting_address = $order_info['Order']['consignee_address'];
        $starting_address = str_replace('，', '', $starting_address);
        $json_params = $_REQUEST['params'];
        $params = json_decode($json_params, true);
        $params['creator'] = $uid;
        $params['goodsName'] = $this->get_order_cart($order_id);
        $params['goodsWeight'] = 1;
        $params['goodsWorth'] = intval($order_info['Order']['total_all_price']);
        $params['startingPhone'] = SERVICE_LINE_PHONE;
        $params['startingCity'] = '北京';
        $params['startingAddress'] = $starting_address;
        $params['consigneeCity'] = '北京';
        //todo check total price
        $result = $this->Logistics->create_logistics_order_from_rr($params);
        echo json_encode($result);
        return;
    }

    /**
     * @param $type
     * 支付物流订单
     */
    public function pay_logistics_order($type) {
        //支付订单
    }

    private function get_order_info($orderId) {
        $order_info = $this->Order->find('first', array(
            'conditions' => array('id' => $orderId, 'status' => ORDER_STATUS_SHIPPED, 'type' => ORDER_TYPE_WESHARE_BUY),
            'fields' => array('id', 'status', 'consignee_name', 'consignee_id', 'consignee_address', 'consignee_mobilephone', 'member_id', 'total_all_price')
        ));
        return $order_info;
    }

    private function get_order_cart($orderId) {
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $orderId
            ),
            'fields' => array('id', 'name', 'num')
        ));
        $cart_info = array();
        foreach ($carts as $cart_item) {
            $cart_info[] = $cart_item['Cart']['name'] . 'X' . $cart_item['Cart']['num'];
        }
        return implode(',', $cart_info);
    }

    /**
     * 人人快递回调接口
     */
    public function rr_logistics_callback() {
        $this->autoRender = false;
        $postStr = file_get_contents('php://input');
        $result = json_decode($postStr, true);
        $msgType = $result['msgType'];
        $orderNo = $result['orderNo'];
        $businessNo = $result['businessNo'];
        $sign = $result['sign'];
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