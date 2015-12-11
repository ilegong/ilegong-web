<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 12/8/15
 * Time: 14:41
 * 第三方 物流组件
 */
class LogisticsComponent extends Component {


    public $components = array('ThirdPartyExpress');

    /**
     * @param $data
     * @return array
     * 根据人人信息
     * 生成物流订单
     * 不是拼单的订单
     */
    public function create_logistics_order_from_rr($data) {
        $logisticsOrderM = ClassRegistry::init('LogisticsOrder');
        $logisticsOrderItemM = ClassRegistry::init('LogisticsOrderItem');
        $date_time = date('Y-m-d H:i:s');
        $logistics_order = array(
            'creator' => $data['creator'],
            'status' => LOGISTICS_ORDER_WAIT_PAY_STATUS,
            'order_id' => $data['order_id'],
            'created' => $date_time,
            'update' => $date_time,
            'starting_name' => PYS_PROXY_NAME,
            'starting_phone' => $data['startingPhone'],
            'starting_province' => $data['startingProvince'],
            'starting_city' => $data['startingCity'],
            'starting_address' => $data['startingAddress'],
            'total_price' => $data['total_price'],
            'type' => RR_SINGLE_LOGISTICS_ORDER_TYPE,
            'pickup_code' => $data['pickup_code']
        );
        $logistics_order = $logisticsOrderM->save($logistics_order);
        if ($logistics_order && !empty($logistics_order)) {
            $logistics_order_item = array(
                'logistics_order_id' => $logistics_order['LogisticsOrder']['id'],
                'goods_name' => $data['goodsName'],
                'goods_weight' => $data['goodsWeight'],
                'goods_worth' => $data['goodsWorth'],
                'consignee_name' => $data['consigneeName'],
                'consignee_phone' => $data['consigneePhone'],
                'consignee_province' => $data['consigneeProvince'],
                'consignee_city' => $data['consigneeCity'],
                'consignee_address' => $data['consigneeAddress'],
                'remark' => $data['remark'],
                'total_price' => $data['total_price'],
            );
            $logistics_order_item = $logisticsOrderItemM->save($logistics_order_item);
            if ($logistics_order_item && !empty($logistics_order_item)) {
                return array('success' => true, 'logistics_order_id' => $logistics_order['LogisticsOrder']['id']);
            }
        }
        return array('success' => false);
    }

    /**
     * @param $logistics_order_id
     * @return array
     * 准备人人快递提交的数据
     */
    private function prepare_rr_order_params($logistics_order_id){
        $logisticsOrderM = ClassRegistry::init('LogisticsOrder');
        $logisticsOrderItemM = ClassRegistry::init('LogisticsOrderItem');
        $logistics_order = $logisticsOrderM->find('first', array(
            'conditions' => array(
                'id' => $logistics_order_id
            )
        ));
        $logistics_order_item = $logisticsOrderItemM->find('first', array(
            'conditions' => array(
                'logistics_order_id' => $logistics_order_id
            )
        ));
        $sign_keyword = RR_LOGISTICS_USERNAME . $logistics_order['LogisticsOrder']['starting_address'] . $logistics_order_item['LogisticsOrderItem']['consignee_address'];
        $sign = $this->get_sign($sign_keyword);
        $business_no = $logistics_order['LogisticsOrder']['business_no'];
        if(empty($business_no)){
            $business_no = $this->get_rr_business_no($logistics_order['LogisticsOrder']['id']);
        }
        $post_params = array(
            'userName' => RR_LOGISTICS_USERNAME,
            'businessNo' => $business_no,
            'goodsName' => $logistics_order_item['LogisticsOrderItem']['goods_name'],
            'goodsWeight' => $logistics_order_item['LogisticsOrderItem']['goods_weight'],
            'goodsWorth' => $logistics_order_item['LogisticsOrderItem']['goods_worth'],
            'startingName' => $logistics_order['LogisticsOrder']['starting_name'],
            'startingPhone' => $logistics_order['LogisticsOrder']['starting_phone'],
            'startingCity' => $logistics_order['LogisticsOrder']['starting_city'],
            'startingAddress' => $logistics_order['LogisticsOrder']['starting_address'],
            'consigneeName' => $logistics_order_item['LogisticsOrderItem']['consignee_name'],
            'consigneePhone' => $logistics_order_item['LogisticsOrderItem']['consignee_phone'],
            'consigneeCity' => $logistics_order_item['LogisticsOrderItem']['consignee_city'],
            'consigneeAddress' => $logistics_order_item['LogisticsOrderItem']['consignee_address'],
            'remark' => $logistics_order_item['LogisticsOrderItem']['remark'],
            'callbackUrl' => RR_LOGISTICS_CALLBACK,
            'sign' => $sign,
        );
        return $post_params;
    }

    /**
     * @param $logistics_order_id
     * @return mixed
     * re confirm order
     */
    public function re_confirm_rr_order($logistics_order_id) {
        $post_params = $this->prepare_rr_order_params($logistics_order_id);
        $result = $this->ThirdPartyExpress->re_confirm_rr_order($post_params);
        $result = json_decode($result, true);
        if ($result['status'] == 1) {
            //success
            $logisticsOrderM = ClassRegistry::init('LogisticsOrder');
            $orderNo = $result['orderNo'];
            $logisticsOrderM->updateAll(array('status' => LOGISTICS_ORDER_PAID_STATUS, 'business_order_id' => "'" . $orderNo . "'"), array('id' => $logistics_order_id, 'status' => LOGISTICS_ORDER_CANCEL));
        }
        return $result;
    }

    public function trigger_confirm_rr_order($logistics_order_id) {
//$params ["userName"];
//$params ["businessNo"]; 订单号
//$params ["goodsName"];
//$params ["goodsWeight"];
//$params ["goodsWorth"];
//$params ["mapFrom"]; op
//$params ["startingName"];
//$params ["startingPhone"];
//$params ["startingProvince"]; op 仅直辖市和特别行政区可以为空
//$params ["startingCity"];
//$params ["startingAddress"];
//$params ["startingLng"]; op
//$params ["startingLat"]; op
//$params ["consigneeName"];
//$params ["consigneePhone"];
//$params ["consigneeProvince"]; op 仅直辖市和特别行政区可以为空
//$params ["consigneeCity"];
//$params ["consigneeAddress"];
//$params ["consigneeLng"];
//$params ["consigneeLat"];
//$params ["serviceFees"];
//$params ["pickupTime"]; op
//$params ["remark"]; op
//$params ["callbackUrl"];
//$params ["sign"]; strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $startingAddress . $consigneeAddress ) ) ) );

//$result ["isSuccess"];
//$result ["errMsg"];
//$result ["warn"];
//$result ["price"];
//$result ["orderNo"];
//$result ["businessNo"];
        $post_params = $this->prepare_rr_order_params($logistics_order_id);
        $result = $this->ThirdPartyExpress->confirm_rr_order($post_params);
        return $result;
    }

    /**
     * @param $keyword
     * @return string
     * 获取签名
     */
    public function get_sign($keyword) {
        return strtolower(MD5(RR_LOGISTICS_APP_KEY . MD5(date('YmdH:i:s')) . strtolower(MD5($keyword))));
    }

    /**
     * @param $logistics_order_id
     * @return string
     * 生成人人快递的订单号
     */
    public function get_rr_business_no($logistics_order_id) {
        return 'rr-' . $logistics_order_id . '-' . mktime() . '-' . rand(0, 100);
    }

    /**
     * @param $status
     * @param $business_no
     * @param $business_order_id
     * 更新物流订单状态
     */
    public function update_logistics_order_status($status, $business_no, $business_order_id) {
        $logisticsOrderM = ClassRegistry::init('LogisticsOrder');
        $logisticsOrderM->update(array('status' => $status, 'update' => "'" . date('Y-m-d H:i:s') . "'"), array('business_no' => $business_no, 'business_order_id' => $business_order_id));
    }

    /**
     * @param $logistics_order_id
     * @return array
     * 物流订单支付成功之后回调
     */
    public function notifyPaidDone($logistics_order_id) {
        $this->log('logistics order paid notify paid done');
        //send template msg
        $logisticsOrderM = ClassRegistry::init('LogisticsOrder');
        $updateData = array('status' => LOGISTICS_ORDER_PAID_STATUS, 'update' => "'" . date('Y-m-d H:i:s') . "'");
        //trigger call rr logistics api
        $result_str = $this->trigger_confirm_rr_order($logistics_order_id);
        $result = json_decode($result_str, true);
        if ($result['status'] == 1) {
            $business_no = $result['businessNo'];
            $order_no = $result['orderNo'];
            $updateData['business_no'] = "'" . $business_no . "'";
            $updateData['business_order_id'] = "'" . $order_no . "'";
        } else {
            $this->log('auto call rr logistics fail reason ' . $result_str);
        }
        $logisticsOrderM->updateAll($updateData, array('id' => $logistics_order_id));
        return $result;
    }
}