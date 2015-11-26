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
    //如果自由人已经取件则不能取消
    public function cancel_rr_order($params) {
//$params ["userName"];
//$params ["businessNo"];
//$params ["reason"];
//$params ["sign"]; strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $businessNo ) ) ) )

//$result ["isSuccess"];
//$result ["errMsg"];
//$result ["warn"];
//$result ["orderNo"];
//$result ["businessNo"];
        $url = 'http://101.251.194.3:8091/delivery/3rd/cancelOrder';
        try {
            $result = $this->curlPost($url, json_encode($params));
            return $result;
        } catch (Exception $e) {
            $this->log('curl post error ' . $e->getMessage());
            return array();
        }
    }

    public function query_rr_order($params) {
//$pkg ["userName"];
//$pkg ["businessNo"];
//$pkg ["sign"]; strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $businessNo ) ) ) )

//$result ["isSuccess"]
//$result ["errMsg"]
//$result ["warn"]
//$result ["orderNo"]
//$result ["businessNo"]
//$result ["orderState"]
//$result ["orderLogs"] => array()  item => array('type', 'operator', 'description', 'createTime')
        $url = 'http://101.251.194.3:8091/delivery/3rd/queryOrder';
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
     * 重新下单
     */
    public function re_confirm_rr_order($params) {
//$params ["userName"];
//$params ["businessNo"];
//$params ["goodsName"];
//$params ["goodsWeight"];
//$params ["goodsWorth"];
//$params ["mapFrom"];
//$params ["startingName"];
//$params ["startingPhone"];
//$params ["startingProvince"];
//$params ["startingCity"];
//$params ["startingAddress"];
//$params ["startingLng"];
//$params ["startingLat"];
//$params ["consigneeName"];
//$params ["consigneePhone"];
//$params ["consigneeProvince"];
//$params ["consigneeCity"];
//$params ["consigneeAddress"];
//$params ["consigneeLng"];
//$params ["consigneeLat"];
//$params ["serviceFees"];
//$params ["remark"];
//$params ["callbackUrl"];
//$params ["sign"]; strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $startingAddress . $consigneeAddress ) ) ) )

//$result ["isSuccess"]
//$result ["errMsg"]
//$result ["warn"]
//$result ["price"]
//$result ["orderNo"]
//$result ["businessNo"]
        $url = 'http://101.251.194.3:8091/delivery/3rd/againOrder';
        try {
            $result = $this->curlPost($url, json_encode($params));
            //可能遇到乱码问题
            //mb_convert_encoding($result, "gb2312", "utf-8");
            return $result;
        } catch (Exception $e) {
            $this->log('curl post error ' . $e->getMessage());
            return array();
        }
    }

    /**
     * @param $params
     * @return array|string
     * 人人快递进行拼单
     */
    public function confirm_rr_multi_order($params) {
//$pkg ["userName"];
//$pkg ["parentBusinessNo"];
//$pkg ["mapFrom"];
//$pkg ["startingName"];
//$pkg ["startingPhone"];
//$pkg ["startingProvince"];
//$pkg ["startingCity"];
//$pkg ["startingAddress"];
//$pkg ["startingLng"];
//$pkg ["startingLat"];
//$pkg ["childOrders"] = array(
        //array(
        //"businessNo" => $businessNo1,
        //"goodsName"	=> $goodsName1,
        //"goodsWeight" => $goodsWeight1,
        //"goodsWorth" => $goodsWorth1,
        //"consigneeName" => $consigneeName1,
        //"consigneePhone" => $consigneePhone1,
        //"consigneeProvince" => $consigneeProvince1,
        //"consigneeCity" => $consigneeCity1,
        //"consigneeAddress" => $consigneeAddress1,
        //"consigneeLng" => $consigneeLng1,
        //"consigneeLat" => $consigneeLat1
        //"remark" => $remark1),
        //array(
        //"businessNo" => $businessNo2,
        //"goodsName"	=> $goodsName2,
        //"goodsWeight" => $goodsWeight2,
        //"goodsWorth" => $goodsWorth2,
        //"consigneeName" => $consigneeName2,
        //"consigneePhone" => $consigneePhone2,
        //"consigneeProvince" => $consigneeProvince2,
        //"consigneeCity" => $consigneeCity2,
        //"consigneeAddress" => $consigneeAddress2,
        //"consigneeLng" => $consigneeLng2,
        //"consigneeLat" => $consigneeLat2
        ////"remark" => $remark2)
        //);
//$pkg ["callbackUrl"] = $callbackUrl;
//$pkg ["sign"]; strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $startingAddress ) ) ) )

//$result ["isSuccess"]
//$result ["errMsg"]
//$result ["warn"]
//$result ["parentBusinessNo"] //自己生成的一个汇总订单
//$result ["parentOrderNo"] //人人快递生成的总订单号
//$result ["totalPrice"]
//$result ["priceDetails"] => Array<OrderPriceResult> OrderPriceResult => array("businessNo", "orderNo", "price")
        $url = 'http://101.251.194.3:8091/delivery/3rd/confirmMultiOrder';
        try {
            $result = $this->curlPost($url, json_encode($params));
            //可能遇到乱码问题
            //mb_convert_encoding($result, "gb2312", "utf-8");
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

    var $rr_state_map = array(0 => '尚无人接单', 1 => '等待取件', 2 => '取消发件', 3 => '已取件', 4 => '问题件', 5 => '已签收', 6 => '送货中', 7 => '预授权');
}