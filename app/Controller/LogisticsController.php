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
     * @param $logistics_order_id
     * 取消人人订单
     */
    public function cancel_rr_logistics_order($logistics_order_id) {
        $this->autoRender = false;
        $reason = $_REQUEST['reason'];
        $reason = empty($reason) ? '没货了' : $reason;
        $result = $this->Logistics->cancel_logistics_order($logistics_order_id, $reason);
        echo $result;
        return;
    }

    /**
     * @param $logistics_order_id
     * 查询人人订单的操作日志
     */
    public function get_rr_logistics_order_log($logistics_order_id){
        $this->autoRender = false;
        $result = $this->Logistics->get_logistics_order_log($logistics_order_id);
        echo $result;
        return;
    }

    /**
     * @param $logistics_order_id
     * 查询人人订单配送人的信息
     */
    public function get_rr_order_deliverer($logistics_order_id){

    }

    /**
     * 计算人人快递费用
     */
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
        $json_params = $_REQUEST['params'];
        $params = json_decode($json_params, true);
        $order_info = $this->get_order_info($order_id);
        $result = $this->get_rr_ship_fee($order_info, $params);
        echo $result;
        return;
    }

    /**
     * @param $order_info
     * @param $params
     * @return mixed
     *
     * 获取人人的费用
     */
    private function get_rr_ship_fee($order_info, $params) {
        $startingAddress = $order_info['Order']['consignee_address'];
        $startingAddress = str_replace('，', '', $startingAddress);
        $sign_keyword = RR_LOGISTICS_USERNAME . $startingAddress . $params ["consigneeAddress"];
        $params['sign'] = $this->Logistics->get_sign($sign_keyword);
        $params['goodsWeight'] = 1; //set default goods weight
        if ($order_info['Order']['total_all_price'] > 1) {
            $params['goodsWorth'] = intval($order_info['Order']['total_all_price']);
        } else {
            $params['goodsWorth'] = 1; //set default goods worth
        }
        $params['startingAddress'] = $startingAddress;
        $params['startingCity'] = '北京市';
        $params['consigneeCity'] = '北京市';
        $params['startingPhone'] = SERVICE_LINE_PHONE;
        $params['userName'] = RR_LOGISTICS_USERNAME;
        $result = $this->ThirdPartyExpress->calculate_rr_logistics_cost($params);
        return $result;
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
        if (empty($order_info)) {
            echo json_encode(array('success' => false, 'reason' => 'not_order'));
            return;
        }
        //todo check order has call rr logistics
        $starting_address = $order_info['Order']['consignee_address'];
        $starting_address = str_replace('，', '', $starting_address);
        $json_params = $_REQUEST['params'];
        $params = json_decode($json_params, true);
        $params['creator'] = $uid;
        $params['goodsName'] = $this->get_order_cart($order_id);
        $params['goodsWeight'] = 1;
        if ($order_info['Order']['total_all_price'] > 1) {
            $params['goodsWorth'] = intval($order_info['Order']['total_all_price']);
        } else {
            $params['goodsWorth'] = 1;
        }
        $params['startingPhone'] = SERVICE_LINE_PHONE;
        $params['startingCity'] = '北京市';
        $params['startingAddress'] = $starting_address;
        $params['consigneeCity'] = '北京市';
        $params['pickup_code'] = $order_info['Order']['ship_code'];
        $params['remark'] = $this->get_logistics_order_remark($params['goodsName'], $order_info['Order']['ship_code'], $starting_address, $params['remark']);
        //check total price
        $ship_fee_result = $this->get_rr_ship_fee($order_info, array('consigneeAddress' => $params['consigneeAddress'], 'consigneePhone' => $params['consigneePhone']));
        $ship_fee_result = json_decode($ship_fee_result, true);
        if ($ship_fee_result['status'] == 1) {
            $params['total_price'] = $ship_fee_result['price'];
        }
        $result = $this->Logistics->create_logistics_order_from_rr($params);
        echo json_encode($result);
        return;
    }

    /**
     * @param $logistics_order_id
     * 订单取消之后重新呼叫
     */
    public function re_confirm_rr_logistics_order($logistics_order_id) {
        $this->autoRender = false;
        $result = $this->Logistics->re_confirm_rr_order($logistics_order_id);
        echo json_encode($result);
        return;
    }

    /**
     * @param $type
     * @param $orderId
     * 支付物流订单
     */
    public function pay_logistics_order($type, $orderId) {
        if ($type == 0) {
            //微信支付订单
            $this->redirect('/wxPay/jsApiPay/' . $orderId . '?action=logistics');
            return;
        }
        if ($type == 1) {
            //支付宝支付
            $this->redirect('/ali_pay/wap_to_alipay/' . $orderId . '?action=logistics');
            return;
        }
    }

    private function get_order_info($orderId) {
        $order_info = $this->Order->find('first', array(
            'conditions' => array('id' => $orderId, 'status' => ORDER_STATUS_SHIPPED, 'type' => ORDER_TYPE_WESHARE_BUY, 'ship_mark' => SHARE_SHIP_PYS_ZITI_TAG),
            'fields' => array('id', 'status', 'consignee_name', 'consignee_id', 'consignee_address', 'consignee_mobilephone', 'member_id', 'total_all_price', 'ship_code')
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
        $this->log('rr logistics call back result ' . $postStr);
        $msgType = $result['msgType'];
        $orderNo = $result['orderNo'];
        $businessNo = $result['businessNo'];
        $sign = $result['sign'];
        $this->log('rr call back msg type ' . $msgType . ' rr order No ' . $orderNo . ' rr business no ' . $businessNo);
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
        echo 'success';
        return;
    }

    private function valid_rr_sign($sign, $orderNo, $businessNo) {
        $sign_keyword = RR_LOGISTICS_USERNAME . $orderNo . $businessNo;
        $valid_sign = $this->Logistics->get_call_back_sign($sign_keyword);
        $this->log('call back sign ' . $sign . ' valid sign ' . $valid_sign);
        return $valid_sign == $sign;
    }


    private function handle_rr_receive($business_no, $business_order_id) {
        //人人快递接单 1
        if($this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_RECEIVE, $business_no, $business_order_id)){
            $title = '快递已接单，请您耐心等待。';
            $remark = '点击查看详情！';
            $this->Logistics->send_logistics_order_notify_msg($title, $remark, $business_order_id);
        }
    }

    private function handle_rr_take($business_no, $business_order_id) {
        //人人快递取件 2
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_TAKE, $business_no, $business_order_id);
    }


    private function handle_rr_sign($business_no, $business_order_id) {
        //人人快递签收 3
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_SIGN, $business_no, $business_order_id);
        //更新系统订单状态
        $this->Logistics->update_user_order_status($business_order_id);
    }

    private function handle_rr_timeout_cancel($business_no, $business_order_id) {
        //人人快递超时取消 4
        if($this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_CANCEL, $business_no, $business_order_id)){
            $title = '快递呼叫超时，您可再次呼叫。';
            $remark = '再次呼叫快递小伙儿～～';
            $this->Logistics->send_logistics_order_notify_msg($title, $remark, $business_order_id);
        }
    }

    private function handle_rr_cancel_by_rr($business_no, $business_order_id) {
        //取消订单 5
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_CANCEL, $business_no, $business_order_id);
    }

    private function handle_rr_cancel_receive_by_rr($business_no, $business_order_id) {
        //取消接单 6
        if($this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_CANCEL, $business_no, $business_order_id)){
            $title = '快递已接单，请您耐心等待。';
            $remark = '点击查看详情！';
            $this->Logistics->send_logistics_order_notify_msg($title, $remark, $business_order_id);
        }
    }

    private function handle_rr_receive_by_rr($business_no, $business_order_id) {
        //接单 7
        if($this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_RECEIVE, $business_no, $business_order_id)){
            $title = '快递已接单，请您耐心等待。';
            $remark = '点击查看详情！';
            $this->Logistics->send_logistics_order_notify_msg($title, $remark, $business_order_id);
        }
    }

    private function handle_rr_take_by_rr($business_no, $business_order_id) {
        //取件 8
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_TAKE, $business_no, $business_order_id);
    }

    private function handle_rr_sign_by_rr($business_no, $business_order_id) {
        //签收 9
        $this->Logistics->update_logistics_order_status(LOGISTICS_ORDER_SIGN, $business_no, $business_order_id);
        //更新系统订单状态
        $this->Logistics->update_user_order_status($business_order_id);
    }

    /**
     * @param $product_info
     * @param $ship_code
     * @param $pick_address
     * @param $remark
     * @return string
     * get logistics order remark
     */
    private function get_logistics_order_remark($product_info, $ship_code = '', $pick_address, $remark = '') {
        $remark_msg = "亲爱的跑腿哥，" . $product_info . "，共1件商品，取货码：" . $ship_code . "，取货地址：" . $pick_address . "【好邻居便利】。" . $remark . " 咨询取货问题请联系010-56245991【朋友说】";
        return $remark_msg;
    }

}