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
        79 => array(
            'share_id' => '79',
            'banner_img' => '/static/pintuan/images/banner.jpg',
            'sharer_id' => '802852',
            'sharer_avatar' => '/static/pintuan/images/head.png',
            'share_title' => '越南红心火龙果 4个装',
            'share_label' => '来自［小宝妈］分享',
            'limit_time' => 24,
            'product' => array(
                'id' => 139,
                'normal_price' => 2,
                'pintuan_price' => 1,
                'name' => '越南红心火龙果 4个装'
            )
        )
    );

    public function get_conf_data($share_id) {
        return $this->conf_data[$share_id];
    }

}