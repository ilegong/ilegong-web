<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/25/14
 * Time: 5:27 PM
 */

class AliPayController extends AppController {

    public $components = array('WxPayment');

    public function goto_to_alipay($order_id) {
        $this->autoRender = false;
        echo $this->WxPayment->goToAliPayForm($order_id, $this->currentUser['id']);
    }

    public function notify() {
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

                if ($this->WxPayment->notifyCounted($out_trade_no) > 0) {
                    $this->log("Zhifubao: Aready done, so skipped");
                } else {
                    list($status, $order) = $this->WxPayment->saveNotifyAndUpdateStatus($out_trade_no, $trade_no, TRADE_ALI_TYPE, true);
                }

            }
            else if ($trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                //注意：
                //该种交易状态只在一种情况下出现——开通了高级即时到账，买家付款成功后。

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");

                if ($this->WxPayment->notifyCounted($out_trade_no) > 0) {
                    $this->log("Zhifubao: Aready done");
                } else {
                    list($status, $order) = $this->WxPayment->saveNotifyAndUpdateStatus($out_trade_no, $trade_no, TRADE_ALI_TYPE, true);
                }

            }  else {
                $this->log("verify notify failed: for $out_trade_no, $trade_status");
            }

            if (isset($status) && $status == PAYNOTIFY_STATUS_ORDER_UPDATED) {
                //TODO: send notify to Weixin user(if any)
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
            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序
                if ($this->WxPayment->notifyCounted($out_trade_no) > 0) {
                    $this->log("Zhifubao: Aready done, so skipped");
                } else {
                    list($status, $order) = $this->WxPayment->saveNotifyAndUpdateStatus($out_trade_no, $trade_no, TRADE_ALI_TYPE, true);
                    if (isset($status) && $status == PAYNOTIFY_STATUS_ORDER_UPDATED) {
                        //TODO: send notify to Weixin user(if any)
                    }
                }
            }
            else {
                $this->log("verify notify failed: for $out_trade_no, $trade_status");
            }

            echo "验证成功<br />";
        }
        else {
            echo "验证失败";
            $this->log("Zhifubao: fail to verify(return_back): request:".json_encode($_REQUEST));
        }
    }

} 