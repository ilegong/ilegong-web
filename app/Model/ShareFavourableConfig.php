<?php

class ShareFavourableConfig extends AppModel {

    public $useTable = false;

    /**
     * @var array
     *
     * ('discount' => 0)
     */
    var $favourableConfig = array();

    public function get_favourable_config($share_id) {
        return $this->favourableConfig[$share_id];
    }

}