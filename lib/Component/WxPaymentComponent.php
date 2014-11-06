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

    public function out_trade_no($source, $orderId) {
        return $source."-$orderId-".mktime().'-'.rand(0, 100);
    }

    public function goToAliPayForm($order_id, $uid) {
        App::import('Vendor', 'ali_direct_pay/AliPay');

        $order = $this->findOrderAndCheckStatus($order_id, $uid);

        $totalFee = $order['Order']['total_all_price'];
        list($subject, $body) = $this->getProductDesc($order_id);

        $out_trade_no = $this->out_trade_no(TRADE_ALI_TYPE, $order_id);
        $this->savePayLog($order_id, $out_trade_no, $body, TRADE_ALI_TYPE, $totalFee  * 100, '', '');
        $ali = new AliPay();
        return $ali->api_form($out_trade_no, $order_id, $subject, $totalFee, $body);
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
            'order_id' => $orderId
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
        $payNotifyModel->save(array('PayNotify' => array(
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
        )));
        $notifyLogId = $payNotifyModel->getLastInsertId();
        $payLog = $payLogModel->find('first', array('conditions' => array('out_trade_no' => $out_trade_no)));
        if (empty($payLog)) {
            $status = PAYNOTIFY_ERR_TRADENO;
        } else {
            $payLogModel->updateAll(array('status' => $suc ? PAYLOG_STATUS_FAIL : PAYLOG_STATUS_SUCCESS), array('out_trade_no' => $out_trade_no));
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
                    $orderModel->updateAll(array('status' => ORDER_STATUS_PAID, 'pay_time' => "'" . date(FORMAT_DATETIME) . "'"), array('id' => $orderId, 'status' => ORDER_STATUS_WAITING_PAY));
                    $status = PAYNOTIFY_STATUS_ORDER_UPDATED;
                }
            }
        }
        $payNotifyModel->updateAll(array('status' => $status), array('id' => $notifyLogId));
        return array($status, $order);
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
            $productDesc .= " 等" . count($items) . "件商品";
        } else {
            //display errors
            $this->log('Cannot get cart items: ' . $orderId . '/pay?msg=cannot_get_cart_items');
        }
        $body = mb_strlen($productDesc, 'UTF-8') > 127 ? mb_substr($productDesc, 0, 127, 'UTF-8') : $productDesc;
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
        $order = $orderModel->find('first', array('conditions' => array('id' => $orderId)));
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

} 