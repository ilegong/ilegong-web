<?php

/**
 * 拼团数据的配置
 * User: shichaopeng
 * Date: 12/29/15
 * Time: 09:31
 */
class PintuanConfig extends AppModel {

    public $useTable = false;

    var $conf_data = array(
        1814 => array(
            'share_id' => '1814',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner.jpg',
            'sharer_id' => '633345',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/6333451445516765.png',
            'share_title' => '越南红心火龙果 4个装 4.5斤左右',
            'wx_title' => '[和你一起立省5元] 越南红心火龙果 4个49元，愣愣邀你吃',
            'wx_desc' => '品质棒棒嗒，好吃的要分享给大家一起吃--朋友说',
            'share_label' => '来自［愣愣］的分享',
            'limit_time' => 24,
            'product' => array(
                'id' => 3845,
                'normal_price' => 0.02,
                'pintuan_price' => 0.01,
                'name' => '越南红心火龙果 4个装'
            )
        ),
        1825 => array(
            'share_id' => '1825',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner.jpg',
            'sharer_id' => '141',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_f88cfd957b22b112058e340d508423a7.jpg',
            'share_title' => '【限北京】越南红心火龙果 4个装 4.5斤左右',
            'wx_title' => '[和你一起立省5元] 越南红心火龙果 4个49元，杨晓光邀你吃',
            'wx_desc' => '品质棒棒嗒，好吃的要分享给大家一起吃--朋友说',
            'share_label' => '来自［杨晓光］的分享',
            'limit_time' => 24,
            'product' => array(
                'id' => 3867,
                'normal_price' => 54,
                'pintuan_price' => 49,
                'name' => '越南红心火龙果 4个装'
            )
        )
    );

    public function get_conf_data($share_id) {
        return $this->conf_data[$share_id];
    }

}