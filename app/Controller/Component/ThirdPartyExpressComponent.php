<?php

class ThirdPartyExpressComponent extends Component {


    /**
     *  $header = array("Content-Type: application/json;charset=UTF-8");
     */
    /**
     * @param $params
     * @return array|string
     * 计算快递费用
     */
    public function calculate_rr_logistics_cost($params) {
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
//$params ["$params"]; op
//$params ["sign"];  strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $startingAddress . $consigneeAddress ) ) ) )

//$result ["isSuccess"]; String
//$result ["errMsg"]; String
//$result ["warn"]; String
//$result ["price"]; Double

        $url = "http://101.251.194.3:8091/delivery/3rd/getFastPrice";
        try {
            $result = $this->curlPost($url, json_encode($params));
            return $result;
        } catch (Exception $e) {
            $this->log('curl post error ' . $e->getMessage());
            return array();
        }
    }

    /**
     * @param $params
     * @return array|string
     * 确认订单
     */
    public function confirm_rr_order($params) {
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
        $url = 'http://101.251.194.3:8091/delivery/3rd/confrmOrder';
        try {
            $result = $this->curlPost($url, json_encode($params));
            return $result;
        } catch (Exception $e) {
            $this->log('curl post error ' . $e->getMessage());
            return array();
        }
    }

    /**
     * @param $params
     * @return array|string
     * 取消人人订单
     */
    public function cancel_rr_order($params) {
//$params ["userName"];
//$params ["businessNo"];
//$params ["reason"];
//$params ["sign"]; strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $businessNo ) ) ) )
        $url = 'http://101.251.194.3:8091/delivery/3rd/cancelOrder';
        try {
            $result = $this->curlPost($url, json_encode($params));
            return $result;
        } catch (Exception $e) {
            $this->log('curl post error ' . $e->getMessage());
            return array();
        }
    }

    /**
     * CURL POST数据
     *
     * @param string $url 发送地址
     * @param array $post_data 发送数组
     * @param integer $timeout 超时秒
     * @param array $header 头信息
     * @return string $result 响应结果
     */
    public function curlPost($url, $post_data = array(), $timeout = 5, $header = array()) {
        $header = empty ($header) ? array() : $header;
        $post_string = $post_data;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); // 模拟的header头
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


}