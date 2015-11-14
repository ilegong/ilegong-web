<?php

class SpreadConf extends AppModel {

    public $useTable = false;

    var $sharerConf = array();

    public function get_sharer_conf($sharer_id) {
        return $this->sharerConf[$sharer_id];
    }

}