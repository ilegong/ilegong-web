<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/27/14
 * Time: 9:37 AM
 */

require_once("lib/alipay_notify.class.php");
require_once("lib/alipay_submit.class.php");

class AliWapPay extends Object {

    var $alipay_config;

    function __construct(){
        $alipay_config['partner']		= '2088611419020192';
        $alipay_config['key']			= 'h42yd9hy8trfvmghsnbyrcnoqpj5srih';
        $alipay_config['sign_type']    = strtoupper('MD5');
        //字符编码格式 目前支持 gbk 或 utf-8
        $alipay_config['input_charset']= strtolower('utf-8');
        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        $alipay_config['cacert']    = getcwd().'\\cacert.pem';
        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        $alipay_config['transport']    = 'http';

        //如果签名方式设置为“0001”时，请设置该参数
        $alipay_config['private_key_path'] = CAKE_CORE_INCLUDE_PATH . DS . 'Vendor' . DS . 'ali_wap_pay' . DS . 'key' . DS . 'rsa_private_key.pem';
        $alipay_config['ali_public_key_path'] = CAKE_CORE_INCLUDE_PATH . DS . 'Vendor' . DS . 'ali_wap_pay' . DS . 'key' . DS . 'rsa_public_key.pem';
        //签名方式 不需修改
        //$alipay_config['sign_type']    = '0001';

        $this->alipay_config = $alipay_config;
    }

    /**
     * @return array|bool if verify failed, return false; otherwise return a array
     */
    public function notify() {
        $alipayNotify = new AlipayNotify($this->alipay_config);
        if ($alipayNotify->verifyNotify()) {
            //解析notify_data
            //注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
            $doc = new DOMDocument();
            $notify_data = $_POST['notify_data'];
            if ($this->alipay_config['sign_type'] == 'MD5') {
                $doc->loadXML($notify_data);
            }

            if ($this->alipay_config['sign_type'] == '0001') {
                $doc->loadXML($alipayNotify->decrypt($notify_data));
            }

            $this->log('get notify resp xml:'. $notify_data, LOG_INFO);

            if(!empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
                $out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
                $trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
                $trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
                $rtn = array('out_trade_no' => $out_trade_no, 'trade_no' => $trade_no, 'trade_status' => $trade_status);
                $rtn['total_fee'] = $doc->getElementsByTagName( "total_fee" )->item(0)->nodeValue;
                $rtn['buyer_email'] = $doc->getElementsByTagName( "buyer_email" )->item(0)->nodeValue;

                $arr = array("buyer_id" => $doc->getElementsByTagName( "buyer_id")->item(0),
                    "payment_type" => $doc->getElementsByTagName( "payment_type")->item(0),
                    "trade_status" => $trade_status);
                $rtn['attach'] = json_encode($arr);

                return $rtn;
            }  else {
                return array();
            }
        } else {
            return false;
        }
    }


    public function verify_return() {
        $alipayNotify = new AlipayNotify($this->alipay_config);
        return $alipayNotify->verifyReturn();
    }

    public function & logistics_api_form($out_trade_no, $subject, $total_fee, $type = ALI_PAY_TYPE_WAP, $share_id) {
        $format = "xml";
        $v = "2.0";
        //请求号，需要保证每次都是唯一
        $req_id = date('Ymdhis').'-'.$out_trade_no;
        $seller_email = ALI_ACCOUNT;

        $merchant_url = $this->alipay_config['transport'].'://'. ALI_HOST."/weshares/view/$share_id.html?from=zhifubaopay";
        //需http://格式的完整路径，不能加?id=123这类自定义参数
        $notify_url = 'http://'.ALI_HOST.'/ali_pay/logistics_wap_notify.html';
        $call_back_url = 'http://'.ALI_HOST.'/ali_pay/logistics_return_back'.($type == ALI_PAY_TYPE_WAPAPP?'_app':'').'.html';

        //请求业务参数详细
        $req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><merchant_url>' . $merchant_url . '</merchant_url></direct_trade_create_req>';

        /************************************************************/
        $para_token = array(
            "service" => "alipay.wap.trade.create.direct",
            "partner" => trim($this->alipay_config['partner']),
            "sec_id" => trim($this->alipay_config['sign_type']),
            "format"	=> $format,
            "v"	=> $v,
            "req_id"	=> $req_id,
            "req_data"	=> $req_data,
            "_input_charset" => trim(strtolower($this->alipay_config['input_charset']))
        );

        $alipaySubmit = new AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestHttp($para_token);
        $html_text = urldecode($html_text);

        $para_html_text = $alipaySubmit->parseResponse($html_text);

        $request_token = $para_html_text['request_token'];

        /**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/
        $req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
        $parameter = array(
            "service" => "alipay.wap.auth.authAndExecute",
            "partner" => trim($this->alipay_config['partner']),
            "sec_id" => trim($this->alipay_config['sign_type']),
            "format"	=> $format,
            "v"	=> $v,
            "req_id"	=> $req_id,
            "req_data"	=> $req_data,
            "_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
        );

        $alipaySubmit = new AlipaySubmit($this->alipay_config);
        return $alipaySubmit->buildRequestForm($parameter, 'get', __('正在跳转到支付宝...'));
    }

    public function & api_form($out_trade_no, $order_id, $subject, $total_fee, $body, $type = ALI_PAY_TYPE_WAP) {
        $format = "xml";
        $v = "2.0";
        //请求号，需要保证每次都是唯一
        $req_id = date('Ymdhis').'-'.$out_trade_no;
        $seller_email = ALI_ACCOUNT;

        //$merchant_url = $this->alipay_config['transport'].'://'. ALI_HOST."/orders/detail/$order_id/pay.html?from=zhifubaopay";
        $merchant_url = $this->alipay_config['transport'].'://'. ALI_HOST."/weshares/pay/$order_id/1.html";
        //需http://格式的完整路径，不能加?id=123这类自定义参数
        $notify_url = 'http://'.ALI_HOST.'/ali_pay/wap_notify.html';
        $call_back_url = 'http://'.ALI_HOST.'/ali_pay/wap_return_back'.($type == ALI_PAY_TYPE_WAPAPP?'_app':'').'.html';

        //请求业务参数详细
        $req_data = '<direct_trade_create_req><notify_url>' . $notify_url . '</notify_url><call_back_url>' . $call_back_url . '</call_back_url><seller_account_name>' . $seller_email . '</seller_account_name><out_trade_no>' . $out_trade_no . '</out_trade_no><subject>' . $subject . '</subject><total_fee>' . $total_fee . '</total_fee><merchant_url>' . $merchant_url . '</merchant_url></direct_trade_create_req>';

        /************************************************************/
        $para_token = array(
            "service" => "alipay.wap.trade.create.direct",
            "partner" => trim($this->alipay_config['partner']),
            "sec_id" => trim($this->alipay_config['sign_type']),
            "format"	=> $format,
            "v"	=> $v,
            "req_id"	=> $req_id,
            "req_data"	=> $req_data,
            "_input_charset" => trim(strtolower($this->alipay_config['input_charset']))
        );

        $alipaySubmit = new AlipaySubmit($this->alipay_config);
        $html_text = $alipaySubmit->buildRequestHttp($para_token);
        $html_text = urldecode($html_text);

        $para_html_text = $alipaySubmit->parseResponse($html_text);

        $request_token = $para_html_text['request_token'];

        /**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/
        $req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
        $parameter = array(
            "service" => "alipay.wap.auth.authAndExecute",
            "partner" => trim($this->alipay_config['partner']),
            "sec_id" => trim($this->alipay_config['sign_type']),
            "format"	=> $format,
            "v"	=> $v,
            "req_id"	=> $req_id,
            "req_data"	=> $req_data,
            "_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
        );

        $alipaySubmit = new AlipaySubmit($this->alipay_config);
        return $alipaySubmit->buildRequestForm($parameter, 'get', __('正在跳转到支付宝...'));
    }

    public function & app_pay_params($out_trade_no, $subject, $body, $total_fee){
        /************************************************************/
        $this->alipay_config['sign_type'] = strtoupper('RSA');
        $para_token = array(
            "service" => "mobile.securitypay.pay",
            "partner" => trim($this->alipay_config['partner']),
            "_input_charset" => trim(strtolower($this->alipay_config['input_charset'])),
            "notify_url" => "http://".ALI_HOST."/aliPay/wap_return_back_app",
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "payment_type" => 1,
            "seller_id" => ALI_ACCOUNT,
            "total_fee" => $total_fee,
            "app_pay_params" => "30m",
            "body" => $body
        );
        $alipaySubmit = new AlipaySubmit($this->alipay_config);
        $result =  $alipaySubmit->buildRequestPara($para_token);
        $arg  = '';
        while (list ($key, $val) = each ($result)) {
            $arg.=$key.'="'.urlencode($val).'"&';
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        return $arg;
    }

} 