<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/9/14
 * Time: 10:42 PM
 */
class WxPayController extends AppController {

    public $components = array('WxPayment','Weixin');

    var $uses = array('Order', 'PayLog', 'PayNotify');

    function beforeFilter(){
        parent::beforeFilter();
        if(empty($this->currentUser['id']) && !$this->_is_action_by_wx_callback()){
            $this->redirect('/users/login?referer='.Router::url('/orders/mine'));
        }
    }

    public function group_pay($memberId) {

        $uid = $this->currentUser['id'];
        $type = $_REQUEST['type'];

        $this->loadModel('GrouponMember');
        $gm = $this->GrouponMember->findById($memberId);

        if (empty($gm)) {
            setFlashError($this->Session, '参数不正确');
            $this->redirect('/');
        }

        $error_text = '';
        if ($gm['GrouponMember']['user_id'] != $uid) {
            $error_text = '不是您的参团记录，您不能支付';
        } else if ($gm['GroupMember']['status'] == STATUS_GROUP_MEM_PAID && $type != 'done') {
            $error_text = '已经支付过了';
        }
        $team_id = $gm['GrouponMember']['team_id'];
        $groupon_id = $gm['GrouponMember']['groupon_id'];
        $group_url = '/groupons/join/' . $groupon_id;
        if (!empty($error_text)) {
            $this->__message($error_text, $group_url);
            return;
        }

        $this->loadModel('Team');
        $team = $this->Team->findById($team_id);
        if (empty($team)) {
            $this->__message('内部错误', '/');
            return;
        }
        $begin = $team['Team']['begin_time'];
        $end = $team['Team']['end_time'];
        $fee = $team['Team']['unit_pay'];
        $now = time();
        if ($now < $begin || $now > $end) {
            $this->__message('团购已过期', $group_url);
            return;
        }


        if ($type == 'done') {
            $this->loadModel("Groupon");
            $groupon = $this->Groupon->findById($groupon_id);
            if ($groupon['Groupon']['user_id'] == $uid) {
                $balance = $this->Groupon->calculate_balance($groupon_id, $team, $groupon);
                $fee = $balance > $team['Team']['unit_val'] ? $balance : $team['Team']['unit_pay'];
                $area = $groupon['Groupon']['area'];
                $address = $groupon['Groupon']['address'];
                $mobile = $groupon['Groupon']['mobile'];
                $name = $groupon['Groupon']['name'];
            } else {
                $this->__message('您不是团购的发起人，不能提前结束团购', $group_url);
            }
        }

        $order_type = ($type == 'done' ? ORDER_TYPE_GROUP_FILL : ORDER_TYPE_GROUP);
        $order = $this->Order->createOrFindGrouponOrder($memberId, $uid, $fee/100, $team['Team']['product_id'], $order_type, $area, $address, $mobile, $name);
        if ($order['Order']['status'] != ORDER_STATUS_WAITING_PAY) {
            $this->__message('您已经支付过了', $group_url);
            return;
        }
        $orderId = $order['Order']['id'];

        $error_pay_redirect = $group_url;
        $this->pageTitle = '安全支付';
        $order = $this->WxPayment->findOrderAndCheckStatus($orderId, $uid);

        $isWeixin = $this->is_weixin();
        if ($isWeixin) {
            list($jsapi_param, $out_trade_no, $productDesc) = $this->__prepareWXPay($error_pay_redirect, $orderId, $uid, $order);
            $this->set('jsApiParameters', $jsapi_param);
            $this->set('totalFee', $order['Order']['total_all_price']);
            $this->set('tradeNo', $out_trade_no);
        }

        $this->set('team', $team);
        $this->set('weixin', $isWeixin);
        $this->set('productDesc', $productDesc);
        $this->set('orderId', $orderId);
        $this->set('group_url', $group_url);
        $this->set('isMobile', $this->RequestHandler->isMobile());
        $this->set('hideNav', true);
        $this->set('fee', $fee);
    }

    public function jsApiPay($orderId) {

        if ($_GET['action'] == 'group_pay') {
            $this->group_pay($_GET['memberId']);
            $this->__viewFileName = 'group_pay';
            return;
        }

        $uid = $this->currentUser['id'];
        $error_pay_redirect = '/orders/detail/' . $orderId . '/pay';
        $this->pageTitle = '微信支付';

        $order = $this->WxPayment->findOrderAndCheckStatus($orderId, $uid);

        list($jsapi_param, $out_trade_no, $productDesc) = $this->__prepareWXPay($error_pay_redirect, $orderId, $uid, $order);
        $this->set('jsApiParameters', $jsapi_param);
        $this->set('totalFee', $order['Order']['total_all_price']);
        $this->set('tradeNo', $out_trade_no);
        $this->set('productDesc', $productDesc);
        $this->set('orderId', $orderId);
    }

    /**
     * @param $error_pay_redirect
     * @param $orderId
     * @param $uid
     * @param $order
     * @throws CakeException
     * @return array js api parameters, out trade no, product description
     */
    private function __prepareWXPay($error_pay_redirect, $orderId, $uid, $order) {
        if (!$this->is_weixin()) {
            throw new CakeException("您只能在微信中使用微信支付。");
        }

        //使用jsapi接口
        $jsApi = $this->WxPayment->createJsApi();

        $oauth = ClassRegistry::init('Oauthbind')->findWxServiceBindByUid($uid);

        if ($oauth && $oauth['oauth_openid']) {
            $openid = $oauth['oauth_openid'];
        }  else {
            //通过code获得openid
            if (!isset($_GET['code'])) {
                //触发微信返回code码
                $url = $jsApi->createOauthUrlForCode(WxPayConf_pub::JS_API_CALL_URL . '/' . $orderId . '?showwxpaytitle=1');
                Header("Location: $url");
                exit();   //cannot use return!!!
            } else {
                //获取code码，以获取openid
                $code = $_GET['code'];
                $jsApi->setCode($code);
                $openid = $jsApi->getOpenId();
            }
        }

        list($productDesc, $body) = $this->WxPayment->getProductDesc($orderId);
        $trade_type = TRADE_WX_API_TYPE;
        $totalFee = intval(intval($order['Order']['total_all_price'] * 1000)/10);
        $out_trade_no = $this->WxPayment->out_trade_no(WX_APPID_SOURCE, $orderId);

        //=========步骤2：使用统一支付接口，获取prepay_id============
        $prepay_id = $this->getPrePayIdFromWx($openid, $body, $out_trade_no, $totalFee);
        if (!$prepay_id) {
            $this->log("Re generate prepay id for order:".$orderId);
            $out_trade_no = $this->WxPayment->out_trade_no(WX_APPID_SOURCE, $orderId);
            $prepay_id = $this->getPrePayIdFromWx($openid, $body, $out_trade_no, $totalFee);
        }

        if ($prepay_id) {

            $this->WxPayment->savePayLog($orderId, $out_trade_no, $body, $trade_type, $totalFee, $prepay_id, $openid);

            //=========步骤3：使用jsapi调起支付============
            $jsApi->setPrepayId($prepay_id);
            $jsapi_param = $jsApi->getParameters();
            $this->log("wxpay:" . $jsapi_param);
            return array($jsapi_param, $out_trade_no, $productDesc);
        }  else {
            $this->log('wx_prepare_error');
            $this->__message('支付服务忙死了，请您稍后重试', $error_pay_redirect, 5);
            exit();
        }
    }


    public function notify() {

        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];

        $notify = $this->WxPayment->createNotify();
        if(empty($xml)) {
            $notify->setReturnParameter("return_code", "FAIL"); //返回状态码
            $notify->setReturnParameter("return_msg", "内容为空"); //返回信息
        } else {

            $notify->saveData($xml);

            //验证签名，并回应微信。
            //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
            //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
            //尽可能提高通知的成功率，但微信不保证通知最终能成功。
            if ($notify->checkSign() == FALSE) {
                $notify->setReturnParameter("return_code", "FAIL"); //返回状态码
                $notify->setReturnParameter("return_msg", "签名失败"); //返回信息
            } else {
                $notify->setReturnParameter("return_code", "SUCCESS"); //设置返回码

                $out_trade_no = $notify->data['out_trade_no'];
                $notifyRecord = $this->WxPayment->findOneNotify($out_trade_no);
                if (!empty($notifyRecord)) {
                    $this->log('[WEIXIN_PAY_NOTIFY] duplicated notify:' . $xml);
                } else {
                    $trade_type = $notify->data['trade_type'];
                    $transaction_id = $notify->data['transaction_id'];
                    $openid = $notify->data['openid'];
                    $coupon_fee = $notify->data['coupon_fee'];

                    $total_fee = $notify->data['total_fee'];
                    $is_subscribe = $notify->data['is_subscribe'];
                    $bank_type = $notify->data['bank_type'];
                    $fee_type = $notify->data['fee_type'];

                    $attach = $notify->data['attach'];
                    $time_end = $notify->data['time_end'];
                    $result_code = $notify->data['result_code'];

                    $suc = $result_code == "SUCCESS";
                    list($status, $order) = $this->WxPayment->saveNotifyAndUpdateStatus($out_trade_no, $transaction_id, $trade_type, $suc, $openid, $coupon_fee,
                        $total_fee, $is_subscribe, $bank_type, $fee_type, $attach, $time_end);

                    if ($status == PAYNOTIFY_STATUS_ORDER_UPDATED) {
                        $this->Weixin->notifyPaidDone($order);
                    }
                }
            }
        }

        $returnXml = $notify->returnXml();
        echo $returnXml;

        //==商户根据实际情况设置相应的处理流程，此处仅作举例=======

        //以log文件形式记录回调信息
        $this->log("【接收到的notify通知】:\n" . $xml . "\n");

        if ($notify->checkSign() == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {
                $this->log("【通信出错】:\n" . $xml . "\n");
            } elseif ($notify->data["result_code"] == "FAIL") {
                $this->log("【业务出错】:\n" . $xml . "\n");
            } else {
                $this->log("【支付成功】:\n" . $xml . "\n");
            }
        }

        $this->autoRender = false;
    }

    public function warning() {
        $this->log('WARNING FROM WEIXIN at '. time());
    }

    /**
     * @return bool
     */
    private function _is_action_by_wx_callback() {
        return array_search($this->request->params['action'], array('notify', 'warning')) !== false;
    }

    /**
     * @param $openid
     * @param $body
     * @param $out_trade_no
     * @param $totalFee
     * @return bool
     */
    protected function getPrePayIdFromWx($openid, $body, $out_trade_no, $totalFee) {
        //使用统一支付接口
        $unifiedOrder = new UnifiedOrder_pub();

        //设置统一支付接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //spbill_create_ip已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $unifiedOrder->setParameter("openid", "$openid"); //商品描述
        $unifiedOrder->setParameter("body", $body); //商品描述
        $unifiedOrder->setParameter("out_trade_no", "$out_trade_no"); //商户订单号
        $unifiedOrder->setParameter("total_fee", $totalFee); //总金额
        $unifiedOrder->setParameter("notify_url", WxPayConf_pub::NOTIFY_URL); //通知地址
        $unifiedOrder->setParameter("trade_type", "JSAPI"); //交易类型
        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
        //$unifiedOrder->setParameter("device_info","XXXX");//设备号
        //$unifiedOrder->setParameter("attach","XXXX");//附加数据
        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID

        $prepay_id = $unifiedOrder->getPrepayId();
        return $prepay_id;
    }
}