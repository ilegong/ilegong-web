<?php

/**
 * 拼团
 * User: shichaopeng
 * Date: 12/29/15
 * Time: 09:24
 */
class PintuanController extends AppController {

    var $name = 'pintuan';

    var $pin_tuan_config = array();

    public function beforeFilter() {
        parent::beforeFilter();
        //暂时不使用angular
        $this->layout = null;
    }

    public function detail($share_id) {
        $conf = $this->get_pintuan_conf($share_id);
        $this->set('conf', $conf);
    }

    public function rule() {

    }

    public function balance() {

    }

    private function get_pintuan_conf($share_id) {
        $pintuanConfigM = ClassRegistry::init('PintuanConfig');
        $conf = $pintuanConfigM->get_conf_data($share_id);
        return $conf;
    }
}