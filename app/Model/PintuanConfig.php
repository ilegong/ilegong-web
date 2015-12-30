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
            'banner_img' => '/static/pintuan/images/banner.jpg',
            'sharer_id' => '633345',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/6333451445516765.png',
            'share_title' => '越南红心火龙果 4个装',
            'share_label' => '来自［愣愣］的分享',
            'limit_time' => 24,
            'product' => array(
                'id' => 3845,
                'normal_price' => 0.2,
                'pintuan_price' => 0.1,
                'name' => '越南红心火龙果 4个装'
            )
        )
    );

    public function get_conf_data($share_id) {
        return $this->conf_data[$share_id];
    }

}