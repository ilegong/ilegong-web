<?php

/**
 * Class ShareFavourableConfig
 * 分享打折处理
 */
class ShareFavourableConfig extends AppModel {

    public $useTable = false;

    /**
     * @var array
     *
     * ('discount' => 0)
     */
    var $favourableConfig = array(
//        71 => array(
//            'discount' => 0.8
//        )
        1216 => array(
            'discount' => 0.8
        )
    );

    public function get_favourable_config($share_id) {
        return $this->favourableConfig[$share_id];
    }

}