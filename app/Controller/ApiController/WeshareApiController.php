<?php

class WeshareApiController extends BaseApiController
{
    var $components = ['Weshares', 'UserConsignee', 'ShareUtil'];

    //获取首页的标签
    public function get_index_product_tags()
    {
        $tags = ['0' => '新品爆款', '1' => '水果蔬菜', '2' => '肉蛋粮油', '3' => '零食其他'];
        echo json_encode($tags);
        exit();
    }
    //获取首页产品
    public function get_index_products($tag)
    {
        $products = $this->ShareUtil->get_index_product($tag);
        echo json_encode($products);
        exit();
    }

    //获取分享的详情
    public function get_weshare_detail($weshare_id)
    {
        $uid = $this->currentUser['id'];
        $detail = $this->Weshares->get_weshare_detail($weshare_id, $uid);
        echo json_encode($detail);
        exit();
    }

    //获取分享的汇总数据
    public function get_share_summery_data($weshare_id)
    {
        $uid = $this->currentUser['id'];
        $summery = $this->WeshareBuy->get_share_and_all_refer_share_summary($weshare_id, $uid);
        echo json_encode($summery);
        exit();
    }

    //获取某个分享自己的订单
    public function get_current_user_order($weshare_id)
    {
        $uid = $this->currentUser['id'];
        $ordersDetail = $this->WeshareBuy->get_current_user_share_order_data($weshare_id, $uid);
        echo json_encode($ordersDetail);
        exit();
    }

    //获取某个分享分页查询订单
    public function get_share_order_data($weshare_id, $page)
    {
        $uid = $this->currentUser['id'];
        $ordersDetail = $this->WeshareBuy->get_share_detail_view_orders($weshare_id, $page, $uid, 1);
        echo json_encode($ordersDetail);
        exit();
    }

    //获取用户的地址
    public function get_user_consignees()
    {
        $uid = $this->currentUser['id'];
        $result = $this->UserConsignee->get_consignee_list($uid);
        echo $result;
        exit();
    }

    //保存用户收货地址
    public function save_user_consignee()
    {
        $uid = $this->currentUser['id'];
        $post_data = $this->get_post_raw_data();
        $consignee = $this->UserConsignee->save($post_data, $uid);
        echo json_encode(['success' => true, 'consignee' => $consignee['OrderConsignee']]);
        exit();
    }

    //用户选择某个地址
    public function select_user_consignee($consignee_id)
    {
        $uid = $this->currentUser['id'];
        $this->UserConsignee->select_consignee($consignee_id, $uid);
        echo json_encode(['success' => true]);
        exit();
    }
}