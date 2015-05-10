<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 5/10/15
 * Time: 21:24
 */
class ShipSettingController extends AppController {

    var $name = 'ShipSetting';

    var $uses = array('ProductShipSetting', 'TuanProduct', 'ProductTry');

    public function admin_list($data_id, $data_type) {
        $shipSettings = $this->ProductShipSetting->find('all', array(
            'conditions' => array(
                'data_id' => $data_id,
                'data_type' => $data_type
            )
        ));
        if ($data_type == 'Product') {
            $this->set('type', '团购');
        }
        if ($data_type == 'Try') {
            $this->set('type', '秒杀');
        }
        $shipTypes = TuanShip::get_all_tuan_ships();
        $this->set('types', $shipTypes);
        $this->set('name', $_REQUEST['name']);
        $this->set('datas', $shipSettings);
        $this->set('data_id', $data_id);
        $this->set('data_type', $data_type);
    }

    public function admin_new($data_id, $data_type) {
        $shipTypes = TuanShip::get_all_tuan_ships();
        $this->set('types', $shipTypes);
        $this->set('data_id', $data_id);
        $this->set('data_type', $data_type);
    }

    public function admin_create() {
        if ($this->ProductShipSetting->save($this->data)) {
            $data_id = $this->data['ProductShipSetting']['data_id'];
            $data_type = $this->data['ProductShipSetting']['data_type'];
            $this->redirect(array('action' => 'admin_list', $data_id,$data_type));
        }
    }

    public function admin_edit($id) {
        $shipTypes = TuanShip::get_all_tuan_ships();
        $this->set('types', $shipTypes);
        $data = $this->ProductShipSetting->find('first',array(
            'conditions' => array(
                'id' => $id
            )
        ));
        if (empty($data)) {
            throw new ForbiddenException(__('该团队不存在！'));
        }
        $this->set('data',$data);
    }

    public function admin_update() {
        if($this->ProductShipSetting->save($this->data)){
            $data_id = $this->data['ProductShipSetting']['data_id'];
            $data_type = $this->data['ProductShipSetting']['data_type'];
            $this->redirect(array('action' => 'admin_list', $data_id,$data_type));
        }
    }

    public function admin_delete($id) {

    }

}