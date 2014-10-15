<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/9/14
 * Time: 10:42 PM
 */
class WxPayController extends AppController {

    public $components = array('WxPayment');

    var $uses = array('Order', 'PayLog', 'PayNotify');

    function beforeFilter(){
        parent::beforeFilter();
        if(empty($this->currentUser['id']) && !$this->_is_action_by_wx_callback()){
            $this->redirect('/users/login?referer='.Router::url('/orders/mine'));
        }
        if (!$this->_is_action_by_wx_callback() && !$this->is_weixin()) {
            throw new CakeException('/?wx_pay_only_in_WX');
        }
    }

    public function jsApiPay($orderId) {

        $order = $this->Order->find('first', array('conditions' => array('id' => $orderId)));
        if (empty($order)) {
            throw new CakeException('wx_pay_order_not_found:'. $orderId);
        } else if ($order['Order']['creator'] !== $this->currentUser['id']) {
            throw new CakeException('/?wx_pay_order_id_now_owned='.$order['Order']['creator'].'__uid='.$this->currentUser['id']);
        }

        if ($order['Order']['status'] != ORDER_STATUS_WAITING_PAY || $order['Order']['deleted'] == 1) {
            throw new CakeException('/?wx_pay_order_status_incorrect='.$order['Order']['creator'].'__uid='.$this->currentUser['id']);
        }

        $this->loadModel('Cart');
        $productDesc = '';
        $items = $this->Cart->find('all', array(
                'fields' => array('name'),
            'conditions' => array('order_id' => $orderId))
        );
        if (!empty($items)) {
            $cartItemNames = array_map(function ($val) {
                return $val['Cart']['name'];
            }, array_slice($items, 0, 3));
            $productDesc .= implode('、', $cartItemNames);
            $productDesc .= " 等".count($items)."件商品";
        } else {
            //display errors
            $this->redirect('/orders/detail/'.$orderId.'/pay?msg=cannot_get_cart_items');
        }

        //使用jsapi接口
        $jsApi = $this->WxPayment->createJsApi();

        $oauth = ClassRegistry::init('Oauthbind')->findWxServiceBindByUid($this->currentUser['id']);

        if ($oauth) {
            $openid = $oauth['oauth_openid'];
        }  else {
            //通过code获得openid
            if (!isset($_GET['code'])) {
                //触发微信返回code码
                $url = $jsApi->createOauthUrlForCode(WxPayConf_pub::JS_API_CALL_URL . '/' . $orderId . '?showwxpaytitle=1');
                Header("Location: $url");
            } else {
                //获取code码，以获取openid
                $code = $_GET['code'];
                $jsApi->setCode($code);
                $openid = $jsApi->getOpenId();
            }
        }

        //自定义订单号，此处仅作举例
        $timeStamp = time();
        $out_trade_no = WX_APPID_SOURCE."-$orderId-$timeStamp";
        $trade_type = "JSAPI";
        $body = mb_strlen($productDesc, 'UTF-8') > 127 ? mb_substr($productDesc, 0, 127, 'UTF-8') : $productDesc;
        $totalFee = intval(intval($order['Order']['total_all_price'] * 1000)/10);

        //=========步骤2：使用统一支付接口，获取prepay_id============
        //使用统一支付接口
        $unifiedOrder = new UnifiedOrder_pub();

        //设置统一支付接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //spbill_create_ip已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $unifiedOrder->setParameter("openid","$openid");//商品描述
        $unifiedOrder->setParameter("body", $productDesc);//商品描述
        $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
        $unifiedOrder->setParameter("total_fee", $totalFee);//总金额
        $unifiedOrder->setParameter("notify_url",WxPayConf_pub::NOTIFY_URL);//通知地址
        $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
        //$unifiedOrder->setParameter("device_info","XXXX");//设备号
        //$unifiedOrder->setParameter("attach","XXXX");//附加数据
        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
        //$unifiedOrder->setParameter("openid","XXXX");//用户标识
        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID

        $prepay_id = $unifiedOrder->getPrepayId();

        $this->PayLog->save(array('PayLog' => array(
            'out_trade_no'=> $out_trade_no,
            'body'=> $body,
            'trade_type' => $trade_type,
            'total_fee' => $totalFee,
            'prepay_id' => $prepay_id,
            'openid' => $openid,
            'order_id' => $orderId
        )));

        //=========步骤3：使用jsapi调起支付============
        $jsApi->setPrepayId($prepay_id);
        $this->set('jsApiParameters', $jsApi->getParameters());
        $this->set('totalFee', $order['Order']['total_all_price']);
        $this->set('tradeNo', $out_trade_no);
        $this->set('productDesc', $productDesc);
        $this->set('orderId', $orderId);
        $this->pageTitle = '微信支付';
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
                $notifyExists = $this->PayNotify->find('count', array('conditions' => array('out_trade_no' => $out_trade_no)));
                if ($notifyExists > 0) {
                    $this->log('[WEIXIN_PAY_NOTIFY] duplicated notify:' . $xml);
                } else {
                    $this->PayNotify->save(array('PayNotify' => array(
                        'out_trade_no' => $notify->data['out_trade_no'],
                        'transaction_id' => $notify->data['transaction_id'],
                        'trade_type' => $notify->data['trade_type'],
                        'openid' => $notify->data['openid'],
                        'coupon_fee' => empty($notify->data['coupon_fee']) ? 0 : $notify->data['coupon_fee'],
                        'total_fee' => $notify->data['total_fee'],
                        'is_subscribe' => $notify->data['is_subscribe'],
                        'bank_type' => $notify->data['bank_type'],
                        'fee_type' => empty($notify->data['fee_type']) ? 'CNY' : $notify->data['fee_type'],
                        'attach' => empty($notify->data['attach']) ? '' : $notify->data['attach'],
                        'time_end' => $notify->data['time_end'],
                        'status' => PAYNOTIFY_STATUS_NEW
                    )));
                    $notifyLogId = $this->PayNotify->getLastInsertId();
                    $payLog = $this->PayLog->find('first', array('conditions' => array('out_trade_no' => $out_trade_no)));
                    if (empty($payLog)) {
                        $status = PAYNOTIFY_ERR_TRADENO;
                    } else {
                        $suc = $notify->data['result_code'] == "SUCCESS";
                        $this->PayLog->updateAll(array('status' => $suc ? PAYLOG_STATUS_FAIL : PAYLOG_STATUS_SUCCESS), array('out_trade_no' => $out_trade_no));
                        $status = PAYNOTIFY_STATUS_PAYLOG_UPDATED;

                        $orderId = $payLog['PayLog']['order_id'];
                        if ($suc) {
                            $order = $this->Order->find('first', array('conditions' => array('id' => $orderId)));
                            if (empty($order)) {
                                $status = PAYNOTIFY_ERR_ORDER_NONE;
                            } else if ($order['Order']['status'] != ORDER_STATUS_WAITING_PAY || $order['Order']['deleted'] == 1) {
                                $status = PAYNOTIFY_ERR_ORDER_STATUS_ERR;
                            } else if ($payLog['PayLog']['total_fee'] != $notify->data['total_fee']) {
                                $status = PAYNOTIFY_ERR_ORDER_FEE;
                            } else {
                                $this->Order->updateAll(array('status' => ORDER_STATUS_PAID), array('id' => $orderId, 'status' => ORDER_STATUS_WAITING_PAY));
                                $status = PAYNOTIFY_STATUS_ORDER_UPDATED;
                            }
                        }
                    }
                    $this->PayNotify->updateAll(array('status' => $status), array('id' => $notifyLogId));
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
                //此处应该更新一下订单状态，商户自行增删操作
                $this->log("【通信出错】:\n" . $xml . "\n");
            } elseif ($notify->data["result_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
                $this->log("【业务出错】:\n" . $xml . "\n");
            } else {
                //此处应该更新一下订单状态，商户自行增删操作
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

} 