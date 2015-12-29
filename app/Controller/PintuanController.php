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

    /**
     * 用户下单
     */
    public function make_order() {
        $this->autoRender = false;
        $share_id = $_REQUEST['share_id'];

    }


    private function get_product_price() {
        if ($_REQUEST['start'] || $_REQUEST['normal']) {
    
        } else {
            $tag_id = $_REQUEST['tag_id'];
        }
    }


    /**
     * @param $share_id 分享的id
     * 结算页面
     */
    public function balance($share_id) {
        $tag_id = $_REQUEST['tag_id'];
        if (empty($tag_id)) {
            //没有拼团的tag
            $start_pintuan = $_REQUEST['create'];
            if (!empty($start_pintuan)) {
                //发起拼团
                $this->set('start', true);
            } else {
                //原价购买
                $this->set('normal', true);
            }
        } else {
            //加入拼团
            $this->set('tag_id', $tag_id);
        }
        $this->set('share_id', $share_id);
    }

    private function pintuan_tag($tag_id) {
        $pinTuanTagM = ClassRegistry::init('PintuanTag');
        $tag = $pinTuanTagM->find('first', array(
            'conditions' => array('id' => $tag_id)
        ));
        return $tag;
    }

    private function get_pintuan_conf($share_id) {
        $pintuanConfigM = ClassRegistry::init('PintuanConfig');
        $conf = $pintuanConfigM->get_conf_data($share_id);
        return $conf;
    }
}