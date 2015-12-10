<?php

class ThirdPartyExpressComponent extends Component {


    /**
     * @param $params
     * @return array|string
     * 计算快递费用
     */
    public function calculate_rr_logistics_cost($params) {
        //http://code.rrkd.cn/v2
        $url = RR_LOGISTICS_URL."/getfastprice";
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
        $url = 'http://openapi.rrkd.cn/v2/addorderfortdd';
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
        $url = 'http://openapi.rrkd.cn/v2/cancelorder';
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
        $url = 'http://openapi.rrkd.cn/v2/queryorder';
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
     * 计算拼单的价钱
     */
    public function calculate_rr_multi_logistics_cost($params) {
//$params ["userName"];
//$params ["mapFrom"];
//$params ["startingPhone"];
//$params ["startingProvince"];
//$params ["startingCity"];
//$params ["startingAddress"];
//$params ["startingLng"];
//$params ["startingLat"];
//$params ["childOrders"] = array(
//array(
//"goodsWeight" => $goodsWeight1,
//"goodsWorth" => $goodsWorth1,
//"consigneeProvince" => $consigneeProvince1,
//"consigneeCity" => $consigneeCity1,
//"consigneeAddress" => $consigneeAddress1,
//"consigneeLng" => $consigneeLng1,
//"consigneeLat" => $consigneeLat1),
//array(
//"goodsWeight" => $goodsWeight2,
//"goodsWorth" => $goodsWorth2,
//"consigneeProvince" => $consigneeProvince2,
//"consigneeCity" => $consigneeCity2,
//"consigneeAddress" => $consigneeAddress2,
//"consigneeLng" => $consigneeLng2,
//"consigneeLat" => $consigneeLat2)
//);
//$params ["sign"]; strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $startingAddress ) ) ) );

//$result ["isSuccess"]
//$result ["errMsg"]
//$result ["warn"]
//$result ["totalPrice"]
        $url = 'http://openapi.rrkd.cn/v2/getMultiPrice';
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
        $url = 'http://openapi.rrkd.cn/v2/againorder';
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
//$params ["userName"];
//$params ["parentBusinessNo"];
//$params ["mapFrom"];
//$params ["startingName"];
//$params ["startingPhone"];
//$params ["startingProvince"];
//$params ["startingCity"];
//$params ["startingAddress"];
//$params ["startingLng"];
//$params ["startingLat"];
//$params ["childOrders"] = array(
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
//$params ["callbackUrl"] = $callbackUrl;
//$params ["sign"]; strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $startingAddress ) ) ) )

//$result ["isSuccess"]
//$result ["errMsg"]
//$result ["warn"]
//$result ["parentBusinessNo"] //自己生成的一个汇总订单
//$result ["parentOrderNo"] //人人快递生成的总订单号
//$result ["totalPrice"]
//$result ["priceDetails"] => Array<OrderPriceResult> OrderPriceResult => array("businessNo", "orderNo", "price")
        $url = 'http://openapi.rrkd.cn/v2/confirmMultiOrder';
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
     * 取消订单
     */
    public function cancel_rr_multi_order($params) {
//$params ["userName"];
//$params ["parentOrderNo"];
//$params ["parentBusinessNo"];
//$params ["reason"];
//$params ["sign"]; strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $parentBusinessNo ) ) ) )

//$result ["isSuccess"]
//$result ["errMsg"]
//$result ["warn"]
//$result ["parentOrderNo"]
//$result ["parentBusinessNo"]
//$result ["childOrders"] Array<ChildOrder> => array('businessNo','orderNo')
        $url = 'http://openapi.rrkd.cn/v2/cancelMultiOrder';
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
     * 查询拼单状态
     */
    public function query_rr_multi_order($params) {
//$params ["userName"];
//$params ["parentBusinessNo"];
//$params ["sign"]; strtolower ( MD5 ( $appKey . strtolower ( MD5 ( $userName . $parentBusinessNo ) ) ) );

//$result ["isSuccess"]
//$result ["errMsg"]
//$result ["warn"]
//$result ["parentOrderNo"]
//$result ["parentBusinessNo"]
//$result ["parentOrderState"]
//$result ["childOrders"]   Array<ChildOrder> item = array('orderNo', 'businessNo', 'orderState', 'orderLogs' => array('type', 'operator', 'description', 'createTime'))

        $url = 'http://openapi.rrkd.cn/v2/queryMultiOrder';
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
     * 获取接单人的信息
     */
    public function get_rr_deliverer($params){
        /*
        $params['userName']	        String	是	人人快递分配的第三方平台账号	15100000000
        $params['orderNo']	        String	否	人人快递产生的订单号	123
        $params['businessNo']	    String	是	第三方生成的订单号	XXXXXX
        $params['customerPhone']	String	是	商户手机号	15100000000
        $params['sign']	            String	是	验证串
         */
        $url = 'http://code.rrkd.cn/v2/getorderdeliverer';
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
    public function curlPost($url, $post_data = array(), $timeout = 10, $header = array()) {
        $header = empty ($header) ? array() : $header;
        $header [] = "Content-Type: application/json"; // 指定请求头为application/json 【非常重要】
        $header [] = "timestamp:" . date('YmdH:i:s'); // 【非常重要】
        if (is_array($post_data)) {
            $post_data['version'] = 2.0;
            $post_string = http_build_query($post_data);
        } else {
            $post_string = $post_data;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); // 模拟的header头
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    var $rr_state_map = array(0 => '尚无人接单', 1 => '等待取件', 2 => '取消发件', 3 => '已取件', 5 => '问题件', 6 => '已签收', 7 => '送货中', 8 => '预授权');

    var $rr_callback_state_map = array(1 => '接单', 2 => '取件', 3 => '签收', 4 => '超时', 5 => '客服取消订单', 6 => '客服取消接单', 7 => '客服协助接单', 8 => '客服协助取件', 9 => '客服协助签收');
}