<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/27/14
 * Time: 9:37 AM
 */

require_once("lib/alipay_notify.class.php");
require_once("lib/alipay_submit.class.php");

class AliPay extends Object {

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
        $this->alipay_config = $alipay_config;
    }

    public function verify_notify() {
        $alipayNotify = new AlipayNotify($this->alipay_config);
        return $alipayNotify->verifyNotify();
    }


    public function verify_return() {
        $alipayNotify = new AlipayNotify($this->alipay_config);
        return $alipayNotify->verifyReturn();
    }

    public function & api_form($out_trade_no, $order_id, $subject, $total_fee, $body) {
        //支付类型
        $payment_type = "1";
        //卖家支付宝帐户
        $seller_email = ALI_ACCOUNT;
        //必填

        //商品展示地址
        $show_url = ALI_HOST."/orders/detail_n/$order_id.html";
        //需以http://开头的完整路径，例如：http://www.xxx.com/myorder.html

        //FIXIME: todo 怎么做？ 需要处理
        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数

        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1


        //需http://格式的完整路径，不能加?id=123这类自定义参数
        $notify_url = 'http://'.ALI_HOST.'/ali_pay/notify.html';
        $return_url = 'http://'.ALI_HOST.'/ali_pay/return_back.html';


        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($this->alipay_config['partner']),
            "payment_type"	=> $payment_type,
            "notify_url"	=> $notify_url,
            "return_url"	=> $return_url,
            "seller_email"	=> $seller_email,
            "out_trade_no"	=> $out_trade_no,
            "subject"	=> $subject,
            "total_fee"	=> $total_fee,
            "body"	=> $body,
            "show_url"	=> $show_url,
            "anti_phishing_key"	=> $anti_phishing_key,
            "exter_invoke_ip"	=> $exter_invoke_ip,
            "_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
        );

        $alipaySubmit = new AlipaySubmit($this->alipay_config);
        return $alipaySubmit->buildRequestForm($parameter, "get", "正在跳转支付宝支付...");
    }

} 