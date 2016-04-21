<?php

class WeshareApiController extends BaseApiController
{
    var $components = ['Weshares'];

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

    public function get_current_user_order($weshare_id)
    {

    }

}