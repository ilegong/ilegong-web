<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/25/14
 * Time: 5:27 PM
 */

class AliPayController extends AppController {

    public $components = array('WxPayment', 'Weixin');

    public function beforeFilter() {
        parent::beforeFilter();
    }

    public function goto_to_alipay($order_id) {
        if(empty($this->currentUser['id'])){
            $this->redirect('/users/login?referer='.Router::url('/orders/detail/'.$order_id));
        }
        $form = $this->WxPayment->goToAliPayForm($order_id, $this->currentUser['id']);
        $this->set('form', $form);
        $this->pageTitle = '支付宝支付';
    }

    public function wap_to_alipay($order_id) {

        $from = $_GET['from'];
        if ("app" == $from){
            $uid = $_GET['uid'];
            $type=ALI_PAY_TYPE_WAPAPP;
        } else {
            $uid = $this->currentUser['id'];
            if(empty($uid)){
                $this->redirect('/users/login?referer='.Router::url('/orders/detail/'.$order_id));
            }
            $type=ALI_PAY_TYPE_WAP;
        }

        $form = $this->WxPayment->wap_goToAliPayForm($order_id, $uid, $type);
        $this->set('form', $form);
        $this->pageTitle = '支付宝支付';
    }

    public function wap_notify() {
        $this->autoRender = false;
        $wap_notify = $this->WxPayment->wap_notify();
        if($wap_notify !== false) {

            $out_trade_no = $wap_notify['out_trade_no'];
            $trade_no = $wap_notify['trade_no'];
            $trade_status = $wap_notify['trade_status'];

            $arr = array("buyer_id" => $wap_notify['buyer_id'], "exterface" => $wap_notify['exterface'], "is_success" => $wap_notify["is_success"],
                "payment_type" => $wap_notify['payment_type'], "trade_status" => $wap_notify['trade_status']);
            $attach = json_encode($arr);

            if($trade_status == 'TRADE_FINISHED') {
                //注意：
                //该种交易状态只在两种情况下出现
                //1、开通了普通即时到账，买家付款成功后。
                //2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
                $this->saveNotifyIfNotSaved($out_trade_no, $trade_no, $wap_notify['total_fee'], $wap_notify['buyer_email'], $attach);
            }
            else if ($trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                $this->saveNotifyIfNotSaved($out_trade_no, $trade_no, $wap_notify['total_fee'], $wap_notify['buyer_email'], $attach);
            }  else {
                $this->log("verify wap_notify not handling: for $out_trade_no, $trade_status");
            }
            echo "success";
        } else {
            echo "fail";
        }
    }

    public function wap_return_back_app() {
        $this->wap_return_back();
    }

    public function wap_return_back() {
        $verify = $this->WxPayment->wap_verify_return();
        $out_trade_no = $_GET['out_trade_no'];
        $trade_no = $_GET['trade_no'];
        $trade_status = $_GET['result'];


        $this->handle_return_back($verify, $trade_status, $out_trade_no, $trade_no, $_GET['result'] == 'success', '');
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

            $arr = array("buyer_id" => $_REQUEST['buyer_id'], "exterface" => $_REQUEST['exterface'], "is_success" => $_REQUEST["is_success"],
                "payment_type" => $_REQUEST['payment_type'], "trade_status" => $_REQUEST['trade_status']);
            $attach = json_encode($arr);

            if($trade_status == 'TRADE_FINISHED') {
                //注意：
                //该种交易状态只在两种情况下出现
                //1、开通了普通即时到账，买家付款成功后。
                //2、开通了高级即时到账，从该笔交易成功时间算起，过了签约时的可退款时限（如：三个月以内可退款、一年以内可退款等）后。
                $this->saveNotifyIfNotSaved($out_trade_no, $trade_no, $_POST['total_fee'], $_POST['buyer_email'], $attach);
            }
            else if ($trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                $this->saveNotifyIfNotSaved($out_trade_no, $trade_no, $_POST['total_fee'], $_POST['buyer_email'], $attach);

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
        $verify = $this->WxPayment->verify_return();
        $out_trade_no = $_GET['out_trade_no'];
        $trade_no = $_GET['trade_no'];
        $trade_status = $_GET['trade_status'];
        $isSuccess = $trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS';

        $arr = array("buyer_id" => $_REQUEST['buyer_id'], "exterface" => $_REQUEST['exterface'], "is_success" => $_REQUEST["is_success"],
            "payment_type" => $_REQUEST['payment_type'], "trade_status" => $_REQUEST['trade_status']);
        $attach = json_encode($arr);

        $this->handle_return_back($verify, $trade_status, $out_trade_no, $trade_no, $isSuccess, $attach);
    }

    /**
     * @param $out_trade_no
     * @param $trade_no
     * @param $total_fee
     * @param $buyer_email
     * @param $attach
     * @internal param $total_fee_in_cent
     * @return array status, Order object, notifyRecord (if skipped)
     */
    protected function saveNotifyIfNotSaved($out_trade_no, $trade_no, $total_fee, $buyer_email, $attach) {
        $total_fee_in_cent = $total_fee * 100;

        $notifyRecord = $this->WxPayment->findOneNotify($out_trade_no);
        if (!empty($notifyRecord)) {
            $this->log("Zhifubao: Aready done, so skipped: ".$out_trade_no);
            return array(PAYNOTIFY_STATUS_SKIPPED, null, $notifyRecord);
        } else {
            list($status, $order) = $this->WxPayment->saveNotifyAndUpdateStatus($out_trade_no, $trade_no, TRADE_ALI_TYPE, true, $buyer_email, 0,
                $total_fee_in_cent, false, '', '', $attach, '');

            if ($status == PAYNOTIFY_STATUS_ORDER_UPDATED) {
                $this->Weixin->notifyPaidDone($order);
            }

            return array($status, $order);
        }
    }

    /**
     * @param $verify
     * @param $trade_status
     * @param $out_trade_no
     * @param $trade_no
     * @param $isSuccess
     * @param $attach
     */
    protected function handle_return_back($verify, $trade_status, $out_trade_no, $trade_no, $isSuccess, $attach) {
        if ($verify) {

            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
            $this->log("Zhifubao: return_back: request:" . json_encode($_REQUEST));

            $order_id = $display_status = $msg = '';
            $order_type = ORDER_TYPE_DEF;
            $order_member_id = 0;

            if ($isSuccess) {

                list($status, $order, $notifyRecord) = $this->saveNotifyIfNotSaved($out_trade_no, $trade_no, $_GET['total_fee'], $_GET['buyer_email'], $attach);
                if ($status == PAYNOTIFY_STATUS_SKIPPED && !empty($notifyRecord)) {
                    $status = $notifyRecord['PayNotify']['status'];
                }

                if (isset($status) && $status == PAYNOTIFY_STATUS_ORDER_UPDATED) {
                    $msg = "支付成功，请留意发货通知。";
                    $display_status = PAID_DISPLAY_SUCCESS;
                } else {
                    $msg = "您的订单支付在处理中，立即将要返回我的订单列表查看状态。";
                    $display_status = PAID_DISPLAY_PENDING;
                }

                if (!empty($order)) {
                    $order_id = $order['Order']['id'];
                    $order_type = $order['Order']['type'];
                    $order_member_id = $order['Order']['member_id'];
                }
            } else {
                $this->log("return back failed for incorrect trade_status: for $out_trade_no, $trade_status");
                $display_status = 'zfb_said_error';
                $msg = '支付失败，请重新支付。 有任何疑问请致电我们。';
            }
        } else {
            $this->log("Zhifubao: fail to verify(return_back): request:" . json_encode($_REQUEST));
            $display_status = 'system_error';
            $msg = '接收支付信息失败，请进入订单页面检查状态。';
        }

        if (empty($order_id) && !empty($out_trade_no)) {
            $payLog = ClassRegistry::init('PayLog')->find('first', array('conditions' => array('out_trade_no' => $out_trade_no)));
            if (!empty($payLog)) {
                $order_id = $payLog['PayLog']['order_id'];
            }
        }

        if ($order_id && $this->request->params['action'] != 'wap_return_back_app') {

            if ($order_type == ORDER_TYPE_GROUP || $order_type == ORDER_TYPE_GROUP_FILL) {
                if($isSuccess){
                    $group_url = '/groupons/my_join/' . $order_member_id.'?msg=ok';
                }else{
                    $group_url = '/groupons/my_join/' . $order_member_id;
                }
                $this->redirect($group_url);
            } else {
                if($isSuccess){
                    $this->redirect(array('controller' => 'Orders', 'action' => 'detail', $order_id, 'pay', '?' => array('paid_msg' => $msg, 'display_status' => $display_status, 'msg' => 'ok')));

                }else{
                    $this->redirect(array('controller' => 'Orders', 'action' => 'detail', $order_id, 'pay', '?' => array('paid_msg' => $msg, 'display_status' => $display_status)));
                }
            }

            $this->autoRender = false;
        } else {
            $this->set('paid_msg', $msg);
            $this->set('display_status', $display_status);
        }
    }

} 