<?php

class PayApiController extends Controller
{

    public $components = array('OAuth.OAuth', 'WxPayment');

    public function beforeFilter()
    {
        $allow_action = array('test');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }

    public function save_ali_pay_log($order_id)
    {
        $uid = $this->currentUser['id'];
        $order = $this->WxPayment->findOrderAndCheckStatus($order_id, $uid);
        $totalFee = $order['Order']['total_all_price'];
        list($subject, $body) = $this->WxPayment->getProductDesc($order_id);
        $out_trade_no = $this->WxPayment->out_trade_no(TRADE_ALI_TYPE, $order_id);
        $this->WxPayment->savePayLog($order_id, $out_trade_no, $body, TRADE_ALI_TYPE, $totalFee * 100, '', '');
        echo json_encode(['success' => true, 'out_trade_no' => $out_trade_no, 'subject' => $subject, 'body' => $body, 'total_price' => $totalFee]);
    }

}