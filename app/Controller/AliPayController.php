<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/25/14
 * Time: 5:27 PM
 */

class AliPayController extends AppController {

    public $components = array('WxPayment', 'Weixin');

    public function goto_to_alipay($order_id) {
        if(empty($this->currentUser['id'])){
            $this->redirect('/users/login?referer='.Router::url('/orders/detail/'.$order_id));
        }
        $form = $this->WxPayment->goToAliPayForm($order_id, $this->currentUser['id']);
        $this->set('form', $form);
        $this->pageTitle = '支付宝支付';
    }

    public function notify() {
        $this->autoRender = false;
        if($this->WxPayment->verify_notify()) {
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];

            $this->log("Zhifubao: $out_trade_no, $trade_no, $trade_status, request:".json_encode($_REQUEST));

            if($trade_status == 'TRADE_FINISHED') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //该种交易状态只在两种情况下出现
                //1、开通了普通即时到账，买家付款成功后。
                //2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
                $this->saveNotifyIfNotSaved($out_trade_no, $trade_no, $_POST);
            }
            else if ($trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
                $this->saveNotifyIfNotSaved($out_trade_no, $trade_no, $_GET);

            }  else {
                $this->log("verify notify not handling: for $out_trade_no, $trade_status");
            }
            echo "success";
        } else {
            echo "fail";

            $this->log("fail to verify(notify): request:".json_encode($_REQUEST));
        }
    }

    public function return_back() {
        if($this->WxPayment->verify_return()) {

            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
            $this->log("Zhifubao: return_back: request:".json_encode($_REQUEST));

            $out_trade_no = $_GET['out_trade_no'];
            $trade_no = $_GET['trade_no'];
            $trade_status = $_GET['trade_status'];
            if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                if ($this->WxPayment->notifyCounted($out_trade_no) > 0) {
                    $this->log("Zhifubao: Aready done, so skipped");

                    $this->redirect('/orders/mine');
                    //TODO: 需要显示信息。
                    echo "您的订单支付在处理中，立即将要返回我的订单列表查看状态";
                    //TODO: 拿到订单，给出订单支付完成界面

                } else {
                    list($status, $order) = $this->WxPayment->saveNotifyAndUpdateStatus($out_trade_no, $trade_no, TRADE_ALI_TYPE, true);
                    if(!empty($order)) {
                        $this->redirect('/orders/detail/'.$order['Order']['id']);
                    }
                }
            }
            else {
                $this->log("return back failed for incorrect trade_status: for $out_trade_no, $trade_status");
            }
        }
        else {
            $this->log("Zhifubao: fail to verify(return_back): request:".json_encode($_REQUEST));
            //TODO: handling error
            $this->redirect('/orders/mine');
        }
    }

    /**
     * @param $out_trade_no
     * @param $trade_no
     * @param $paras array parameters array
     * @return array status and Order object
     */
    protected function saveNotifyIfNotSaved($out_trade_no, $trade_no, &$paras) {
        $buyer_id = $paras['buyer_id'];
        $total_fee = $paras['total_fee'] * 100;
        $buyer_email = $paras['buyer_email'];
        $arr = array("buyer_id" => $buyer_id, "exterface" => $paras['exterface'], "is_success" => $paras["is_success"], "payment_type" => $paras['payment_type'], "trade_status" => $paras['trade_status']);
        $attach = json_encode($arr);

        if ($this->WxPayment->notifyCounted($out_trade_no) > 0) {
            $this->log("Zhifubao: Aready done, so skipped");
            return PAYNOTIFY_STATUS_SKIPPED;
        } else {
            list($status, $order) = $this->WxPayment->saveNotifyAndUpdateStatus($out_trade_no, $trade_no, TRADE_ALI_TYPE, true, $buyer_email, 0,
                $total_fee, false, '', '', $attach, '');

            if ($status == PAYNOTIFY_STATUS_ORDER_UPDATED) {
                $this->loadModel('Oauthbind');
                $user_weixin = $this->Oauthbind->findWxServiceBindByUid($order['Order']['creator']);
                if ($user_weixin != false) {
                    $good = $this->get_order_good_info($order);
                    $this->log("good info:" . $good['good_info'] . " ship info:" . $good['ship_info'], LOG_DEBUG);
                    $this->Weixin->send_order_paid_message($user_weixin['oauth_openid'], $order['Order']['total_all_price'],
                        $good['good_info'], $good['ship_info'], $order['Order']['id']);
                }
            }

            return array($status, $order);
        }
    }

    function get_order_good_info($order_info){
        $good_info ='';
        $ship_info = $order_info['Order']['consignee_name'].','.$order_info['Order']['consignee_address'].','.$order_info['Order']['consignee_mobilephone'];
        $this->loadModel('Cart');
        $carts = $this->Cart->find('all',array(
            'conditions'=>array('order_id' => $order_info['Order']['id'])));
        foreach($carts as $cart){
            $good_info = $good_info.$cart['Cart']['name'].' x '.$cart['Cart']['num'].';';
        }
        return array("good_info"=>$good_info,"ship_info"=>$ship_info);
    }

} 