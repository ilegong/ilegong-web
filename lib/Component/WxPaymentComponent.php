<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/9/14
 * Time: 10:44 PM
 */

class WxPaymentComponent extends Component {

    /**
     * @return Notify_pub
     */
    public function createNotify() {
        App::import('Vendor', 'WxPayPubHelper/WxPayPubHelper');
        return new Notify_pub();

    }

    public function createJsApi() {
        App::import('Vendor', 'WxPayPubHelper/WxPayPubHelper');
        return new JsApi_pub();
    }

    public function createNativeApi(){
        App::import('Vendor', 'WxPayPubHelper/WxPayPubHelper');
        return new NativeLink_pub();
    }

    public function out_trade_no($source, $orderId) {
        return $source."-$orderId-".mktime().'-'.rand(0, 100);
    }

    public function logistics_out_trade_no($source, $orderId){
        return $source."-rr"."-$orderId-".mktime().'-'.rand(0, 100);
    }

    public function goToAliPayForm($order_id, $uid) {
        App::import('Vendor', 'ali_direct_pay/AliPay');
        $ali = new AliPay();
        return $this->api_form($order_id, $uid, $ali, ALI_PAY_TYPE_PC);
    }

    public function verify_notify() {
        App::import('Vendor', 'ali_direct_pay/AliPay');
        $ali = new AliPay();
        return $ali->verify_notify();
    }


    public function verify_return() {
        App::import('Vendor', 'ali_direct_pay/AliPay');
        $ali = new AliPay();
        return $ali->verify_return();
    }

    public function wap_goToAliPayForm($order_id, $uid, $type = ALI_PAY_TYPE_WAP) {
        App::import('Vendor', 'ali_wap_pay/AliWapPay');
        $ali = new AliWapPay();
        return $this->api_form($order_id, $uid, $ali, $type);
    }

    public function app_goToAliPayForm($order_id, $uid){
        App::import('Vendor', 'ali_wap_pay/AliWapPay');
        $ali = new AliWapPay();
        return $this->app_pay_params($order_id, $uid, $ali);
    }

    public function wap_logisticsGoToAliPayForm($order_id, $uid, $type = ALI_PAY_TYPE_WAP){
        App::import('Vendor', 'ali_wap_pay/AliWapPay');
        $ali = new AliWapPay();
        return $this->logistics_api_form($order_id, $uid, $ali, $type);
    }

    public function wap_notify() {
        App::import('Vendor', 'ali_wap_pay/AliWapPay');
        $ali = new AliWapPay();
        return $ali->notify();
    }


    public function wap_verify_return() {
        App::import('Vendor', 'ali_wap_pay/AliWapPay');
        $ali = new AliWapPay();
        return $ali->verify_return();
    }

    public function app_verify_return() {
        App::import('Vendor', 'ali_wap_pay/AliWapPay');
        $ali = new AliWapPay();
        $ali->alipay_config['sign_type'] = 'RSA';
        return $ali->verify_return();
    }

    /**
     * @param $orderId
     * @param $out_trade_no
     * @param $body
     * @param $trade_type
     * @param $totalFee
     * @param $prepay_id
     * @param $openid
     * @param $type
     * @return mixed
     * @return mixed true|false, or the results
     */
    public function saveLogisticsPayLog($orderId, $out_trade_no, $body, $trade_type, $totalFee, $prepay_id, $openid, $type){
        $payLog = ClassRegistry::init('PayLog');
        return $payLog->save(array('PayLog' => array(
            'out_trade_no' => $out_trade_no,
            'body' => $body,
            'trade_type' => $trade_type,
            'total_fee' => $totalFee,
            'prepay_id' => $prepay_id,
            'openid' => $openid,
            'order_id' => $orderId,
            'type' => $type
        )));
    }

    /**
     * @param $orderId
     * @param $out_trade_no
     * @param $body
     * @param $trade_type
     * @param $totalFee
     * @param $prepay_id
     * @param $openid
     * @return mixed true/false, or the results
     */
    public function savePayLog($orderId, $out_trade_no, $body, $trade_type, $totalFee, $prepay_id, $openid) {
        $payLog = ClassRegistry::init('PayLog');
        return $payLog->save(array('PayLog' => array(
            'out_trade_no' => $out_trade_no,
            'body' => $body,
            'trade_type' => $trade_type,
            'total_fee' => $totalFee,
            'prepay_id' => $prepay_id,
            'openid' => $openid,
            'order_id' => $orderId,
        )));
    }

    /**
     * @param $out_trade_no
     * @return mixed pay notify the specified out_trade_no
     */
    public function findOneNotify($out_trade_no) {
        $payNotify = ClassRegistry::init('PayNotify');
        return $payNotify->find('first', array('conditions' => array('out_trade_no' => $out_trade_no)));
    }

    /**
     * @param $out_trade_no
     * @param $transaction_id
     * @param $trade_type
     * @param $suc
     * @param string $openid
     * @param string $coupon_fee
     * @param int $total_fee
     * @param int $is_subscribe
     * @param string $bank_type
     * @param string $fee_type
     * @param string $attach
     * @param string $time_end
     * @param int $type
     * @internal param $result_code
     * @internal param $notify
     * @return array order id and order object
     */
    public function saveLogisticsNotifyAndUpdateStatus($out_trade_no, $transaction_id, $trade_type, $suc,
                                                       $openid = '',
                                                       $coupon_fee = '',
                                                       $total_fee = 0,
                                                       $is_subscribe = 0,
                                                       $bank_type = '',
                                                       $fee_type = '',
                                                       $attach = '',
                                                       $time_end = '', $type) {
        $payNotifyModel = ClassRegistry::init('PayNotify');
        $payLogModel = ClassRegistry::init('PayLog');
        $payNotifyModel->id = null;
        $payNotify = $payNotifyModel->save(array(
            'out_trade_no' => $out_trade_no,
            'transaction_id' => $transaction_id,
            'trade_type' => $trade_type,
            'openid' => empty($openid) ? 'unknown' : $openid,
            'coupon_fee' => empty($coupon_fee) ? 0 : $coupon_fee,
            'total_fee' => $total_fee,
            'is_subscribe' => $is_subscribe,
            'bank_type' => $bank_type,
            'fee_type' => empty($fee_type) ? 'CNY' : $fee_type,
            'attach' => empty($attach) ? '' : substr($attach, 0, 511),
            'time_end' => $time_end,
            'status' => PAYNOTIFY_STATUS_NEW,
            'type' => $type
        ));
        $notifyLogId = $payNotify['PayNotify']['id'];
        $payLog = $payLogModel->find('first', array('conditions' => array('out_trade_no' => $out_trade_no)));
        if (empty($payLog)) {
            $status = PAYNOTIFY_ERR_TRADENO;
        } else {
            $payLogModel->updateAll(array('status' => $suc ? PAYLOG_STATUS_SUCCESS : PAYLOG_STATUS_FAIL), array('out_trade_no' => $out_trade_no));
            $status = PAYNOTIFY_STATUS_PAYLOG_UPDATED;

            $orderId = $payLog['PayLog']['order_id'];
            if ($suc) {
                $logisticsOrderM = ClassRegistry::init('LogisticsOrder');
                $order = $logisticsOrderM->find('first', array('conditions' => array('id' => $orderId)));
                if (empty($order)) {
                    $status = PAYNOTIFY_ERR_ORDER_NONE;
                } else if ($order['LogisticsOrder']['status'] != ORDER_STATUS_WAITING_PAY || $order['LogisticsOrder']['deleted'] == 1) {
                    $status = PAYNOTIFY_ERR_ORDER_STATUS_ERR;
                } else {
                    //update logistics order status
                    $updatedResult = $logisticsOrderM->updateAll(array('status' => LOGISTICS_ORDER_PAID_STATUS), array('id' => $orderId));
                    $this->log('set_logistics_order_to_paid:' . $orderId . ', updatedResult=' . $updatedResult);
                    $status = PAYNOTIFY_STATUS_ORDER_UPDATED;
                    if ($updatedResult) {
                        $order['LogisticsOrder']['status'] = LOGISTICS_ORDER_PAID_STATUS;
                    }
                }
            }
        }
        $payNotifyModel->updateAll(array('status' => $status, 'order_id' => $orderId), array('id' => $notifyLogId));
        return array($status, $order);
    }

    /**
     * @param $out_trade_no
     * @param $transaction_id
     * @param $trade_type
     * @param $suc
     * @param string $openid
     * @param string $coupon_fee
     * @param int $total_fee
     * @param int $is_subscribe
     * @param string $bank_type
     * @param string $fee_type
     * @param string $attach
     * @param string $time_end
     * @internal param $result_code
     * @internal param $notify
     * @return array order id and order object
     */
    public function saveNotifyAndUpdateStatus($out_trade_no, $transaction_id, $trade_type, $suc,
                                                 $openid = '',
                                                 $coupon_fee = '',
                                                 $total_fee = 0,
                                                 $is_subscribe = 0,
                                                 $bank_type = '',
                                                 $fee_type = '',
                                                 $attach = '',
                                                 $time_end = '') {
        $payNotifyModel = ClassRegistry::init('PayNotify');
        $payLogModel = ClassRegistry::init('PayLog');
        //array('PayNotify' => )
        $payNotifyModel->id = null;
        $payNotify = $payNotifyModel->save(array(
            'out_trade_no' => $out_trade_no,
            'transaction_id' => $transaction_id,
            'trade_type' => $trade_type,
            'openid' => empty($openid) ? 'unknown' : $openid,
            'coupon_fee' => empty($coupon_fee) ? 0 : $coupon_fee,
            'total_fee' => $total_fee,
            'is_subscribe' => $is_subscribe,
            'bank_type' => $bank_type,
            'fee_type' => empty($fee_type) ? 'CNY' : $fee_type,
            'attach' => empty($attach) ? '' : substr($attach, 0, 511),
            'time_end' => $time_end,
            'status' => PAYNOTIFY_STATUS_NEW
        ));
        $notifyLogId = $payNotify['PayNotify']['id'];
        $payLog = $payLogModel->find('first', array('conditions' => array('out_trade_no' => $out_trade_no)));
        if (empty($payLog)) {
            $status = PAYNOTIFY_ERR_TRADENO;
        } else {
            $payLogModel->updateAll(array('status' => $suc ? PAYLOG_STATUS_SUCCESS : PAYLOG_STATUS_FAIL), array('out_trade_no' => $out_trade_no));
            $status = PAYNOTIFY_STATUS_PAYLOG_UPDATED;

            $orderId = $payLog['PayLog']['order_id'];
            if ($suc) {
                $orderModel = ClassRegistry::init('Order');
                $order = $orderModel->find('first', array('conditions' => array('id' => $orderId)));
                if (empty($order)) {
                    $status = PAYNOTIFY_ERR_ORDER_NONE;
                } else if ($order['Order']['status'] != ORDER_STATUS_WAITING_PAY || $order['Order']['deleted'] == 1) {
                    $status = PAYNOTIFY_ERR_ORDER_STATUS_ERR;
                //} else if ($payLog['PayLog']['total_fee'] != $total_fee) {
                //    $status = PAYNOTIFY_ERR_ORDER_FEE;
                } else {
                    $updatedResult = $orderModel->set_order_to_paid($orderId, $order['Order']['try_id'], $order['Order']['creator'], $order['Order']['type'], $order['Order']['member_id']);
                    $this->log('set_order_to_paid:'.$orderId.', updatedResult='.$updatedResult, LOG_INFO);
                    $status = PAYNOTIFY_STATUS_ORDER_UPDATED;
                    if($updatedResult){
                        $order['Order']['status'] = ORDER_STATUS_PAID;
                    }
                }
            }
        }
        $payNotifyModel->updateAll(array('status' => $status, 'order_id' => $orderId), array('id' => $notifyLogId));
        return array($status, $order);
    }

    /**
     * @param $logistics_order_id
     * @return array(desc body)
     */
    public function getLogisticsDesc($logistics_order_id) {
        $logisticsOrderItemM = ClassRegistry::init('LogisticsOrderItem');
        $desc = '';
        $items = $logisticsOrderItemM->find('all', array(
            'conditions' => array(
                'logistics_order_id' => $logistics_order_id
            ),
            'fields' => array('id', 'goods_name')
        ));
        if (!empty($items)) {
            $itemNames = array_map(function ($val) {
                return $val['LogisticsOrderItem']['goods_name'];
            }, $items);
            $desc .= implode('、', $itemNames);
            $body = mb_substr($desc, 0, 25);
            $end = " 商品的物流费用";
            $desc .= $end;
            $body .= $end;
        } else {
            //display errors
            $this->log('Cannot get logistics items: ' . $logistics_order_id . '/pay?msg=cannot_get_cart_items');
        }
        return array($desc, $body);
    }

    /**
     * @param $orderId
     * @return array (productDesc, body)
     */
    public function getProductDesc($orderId) {
        $cartModel = ClassRegistry::init('Cart');
        $productDesc = '';
        $items = $cartModel->find('all', array(
                'fields' => array('name'),
                'conditions' => array('order_id' => $orderId))
        );
        if (!empty($items)) {
            $cartItemNames = array_map(function ($val) {
                return $val['Cart']['name'];
            }, array_slice($items, 0, 3));
            $productDesc .= implode('、', $cartItemNames);

            $body =  mb_substr($productDesc, 0, 25);

            $end = " 等" . count($items) . "件商品";
            $productDesc .= $end;
            $body .= $end;
        } else {
            //display errors
            $this->log('Cannot get cart items: ' . $orderId . '/pay?msg=cannot_get_cart_items');
        }
//        $body = strlen($productDesc) > 127 ? mb_substr($productDesc, 0, 127, 'UTF-8') : $productDesc;
        return array($productDesc, $body);
    }

    /**
     * @param $orderId
     * @param $uid
     * @return mixed
     * @throw CakeException if status is incorrect or it's not owned by current user
     */
    public function findOrderAndCheckStatus($orderId, $uid) {
        $orderModel = ClassRegistry::init('Order');
        $order = $orderModel->find('first', array('conditions' => array('id' => $orderId, 'creator' => $uid)));
        if (empty($order)) {
            throw new CakeException('wx_pay_order_not_found:'. $orderId);
        } else if ($order['Order']['creator'] !== $uid) {
            throw new CakeException('/?wx_pay_order_id_not_owned='.$order['Order']['creator'].'__uid='. $uid);
        }

        if ($order['Order']['status'] != ORDER_STATUS_WAITING_PAY || $order['Order']['deleted'] == 1) {
            throw new CakeException('/?wx_pay_order_status_incorrect='.$order['Order']['creator'].'__uid='. $uid);
        }

        return $order;
    }

    public function getOrder($orderId, $uid){
        $orderModel = ClassRegistry::init('Order');
        $order = $orderModel->find('first', ['conditions' => ['id' => $orderId, 'creator' => $uid]]);
        return $order;
    }

    /**
     * @param $logistics_order_id
     * @param $uid
     * @return mixed
     * @throw CakeException if status is incorrect or it's not owned by current user
     */
    public function findLogisticsOrderAndCheckStatus($logistics_order_id, $uid) {
        $logisticsOrderM = ClassRegistry::init('LogisticsOrder');
        $orderM = ClassRegistry::init('Order');
        $logistics_order = $logisticsOrderM->find('first', array(
            'conditions' => array(
                'id' => $logistics_order_id,
                'creator' => $uid
            )
        ));
        if (empty($logistics_order)) {
            throw new CakeException('wx_pay_order_not_found:' . $logistics_order_id);
        } else if ($logistics_order['LogisticsOrder']['creator'] !== $uid) {
            throw new CakeException('/?wx_pay_order_id_not_owned=' . $logistics_order['LogisticsOrder']['creator'] . '__uid=' . $uid);
        }
        if ($logistics_order['LogisticsOrder']['status'] != LOGISTICS_ORDER_WAIT_PAY_STATUS || $logistics_order['LogisticsOrder']['deleted'] == DELETED_YES) {
            throw new CakeException('/?wx_pay_order_status_incorrect=' . $logistics_order['LogisticsOrder']['creator'] . '__uid=' . $uid);
        }
        $goods_order = $orderM->find('first', array(
            'conditions' => array(
                'id' => $logistics_order['LogisticsOrder']['order_id'],
                'type' => ORDER_TYPE_WESHARE_BUY
            ),
            'fields' => array('id', 'member_id')
        ));
        $logistics_order['LogisticsOrder']['weshare_id'] = $goods_order['Order']['member_id'];
        return $logistics_order;
    }

    /**
     * @param $order_id
     * @param $uid
     * @param $ali
     * @param $type
     * @return mixed
     */
    protected function api_form($order_id, $uid, $ali, $type) {
        $order = $this->findOrderAndCheckStatus($order_id, $uid);

        $totalFee = $order['Order']['total_all_price'];
        list($subject, $body) = $this->getProductDesc($order_id);

        $out_trade_no = $this->out_trade_no(TRADE_ALI_TYPE, $order_id);
        $this->savePayLog($order_id, $out_trade_no, $body, TRADE_ALI_TYPE, $totalFee * 100, '', '');
        return $ali->api_form($out_trade_no, $order_id, $subject, $totalFee, $body, $type);
    }

    /**
     * @param $order_id
     * @param $uid
     * @param $ali
     * @return mixed
     */
    protected function app_pay_params($order_id, $uid, $ali) {
        $order = $this->findOrderAndCheckStatus($order_id, $uid);
        $totalFee = $order['Order']['total_all_price'];
        list($subject, $body) = $this->getProductDesc($order_id);
        $out_trade_no = $this->out_trade_no(TRADE_ALI_TYPE, $order_id);
        $pay_params = $ali->app_pay_params($out_trade_no, $subject, $body, $totalFee);
        return $pay_params;
    }

    /**
     * @param $order_id
     * @param $uid
     * @param $ali
     * @param $type
     * @return mixed
     * 生成物流订单的支付
     */
    protected function logistics_api_form($order_id, $uid, $ali, $type) {
        $order = $this->findLogisticsOrderAndCheckStatus($order_id, $uid);
        $share_id = $order['LogisticsOrder']['weshare_id'];
        $totalFee = $order['LogisticsOrder']['total_price'];
        list($subject, $body) = $this->getLogisticsDesc($order_id);
        $out_trade_no = $this->logistics_out_trade_no(TRADE_ALI_TYPE, $order_id);
        $this->saveLogisticsPayLog($order_id, $out_trade_no, $body, TRADE_ALI_TYPE, $totalFee * 100, '', '', LOGISTICS_ORDER_PAY_TYPE);
        return $ali->logistics_api_form($out_trade_no, $subject, $totalFee, $type, $share_id);
    }

} 