<?php

class BalanceApiController extends Controller
{

    public $components = array('OAuth.OAuth', 'Balance');

    public function beforeFilter()
    {
        $allow_action = array('test', 'get_share_fee_detail');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }

    public function get_balance_dashboard()
    {
        $uid = $this->currentUser['id'];
        $data = $this->Balance->get_user_share_summary($uid);
        echo json_encode($data);
        exit;
    }

    /**
     * 获取单个分享的订单详情
     */
    public function get_share_fee_detail()
    {
        $uid = $this->currentUser['id'];
        $data = $this->BalanceComponent->get_user_share_summary($uid);
        echo json_encode($data);
        exit;
    }

    /**
     * 待审核
     */
    public function wait_confirm($page, $limit)
    {
        $uid = $this->currentUser['id'];
        $data = $this->Balance->get_wait_confirm_share_list($uid, $page, $limit);
        echo json_encode($data);
        exit;
    }

    /**
     * 待结算的list
     */
    public function wait_balance_list($page, $limit)
    {
        $uid = $this->currentUser['id'];
        $data = $this->Balance->get_wait_balance_share_list($uid, $page, $limit);
        echo json_encode($data);
        exit;
    }

    /**
     * 已经结算list
     */
    public function already_balance_list($page, $limit)
    {
        $uid = $this->currentUser['id'];
        $data = $this->Balance->get_already_balance_share_list($uid, $page, $limit);
        echo json_encode($data);
        exit;
    }

    /**
     * 进行中的分享list
     */
    public function going_balance_list($page, $limit)
    {
        $uid = $this->currentUser['id'];
        $data = $this->Balance->get_going_share_list($uid, $page, $limit);
        echo json_encode($data);
        exit;
    }

    /**
     * 自己分享的结算详情
     */
    public function self_share_balance_detail($balanceId)
    {
        list($orders, $balanceLog) = $this->Balance->get_balance_detail_orders($balanceId);
        echo json_encode(['orders' => $orders, 'balanceLog' => $balanceLog]);
        exit;
    }

    /**
     * 产品街分享的结算详情
     */
    public function pool_share_balance_detail($balanceId)
    {
        list($orders, $balanceLog) = $this->Balance->get_balance_detail_orders($balanceId);
        echo json_encode(['orders' => $orders, 'balanceLog' => $balanceLog]);
        exit;
    }

    /**
     * 产品街商家结算详情
     */
    public function brand_share_balance_detail($balanceId)
    {
        list($orders, $balanceLog) = $this->Balance->get_balance_detail_orders($balanceId);
        echo json_encode(['orders' => $orders, 'balanceLog' => $balanceLog]);
        exit;
    }

}