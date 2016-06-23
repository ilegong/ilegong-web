<?php

class BalanceApiController extends Controller
{

    public $components = array('OAuth.OAuth');

    public function beforeFilter()
    {
        $allow_action = array('test', 'order_export');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }

    /**
     * 获取单个分享的订单详情
     */
    public function get_share_fee_detail()
    {
        $uid = $this->currentUser['id'];
    }

    /**
     * 待结算的list
     */
    public function wait_balance_list()
    {

    }

    /**
     * 已经结算list
     */
    public function already_balance_list()
    {

    }

    /**
     * 进行中的分享list
     */
    public function going_balance_list()
    {

    }

    /**
     * 自己分享的结算详情
     */
    public function self_share_balance_detail()
    {

    }

    /**
     * 产品街分享的结算详情
     */
    public function pool_share_balance_detail()
    {

    }

    /**
     * 产品街商家结算详情
     */
    public function brand_share_balance_detail()
    {

    }

}