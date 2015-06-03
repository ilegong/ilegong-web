<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 5/10/15
 * Time: 21:24
 */
class ShipSettingController extends AppController {

    var $name = 'ShipSetting';

    var $uses = array('ProductShipSetting');

    public function admin_view($data_id, $data_type){
        $shipSettings = $this->ProductShipSetting->find('all', array(
            'conditions' => array(
                'data_id' => $data_id,
                'data_type' => $data_type
            )
        ));
        $shipSettings = Hash::combine($shipSettings,'{n}.ProductShipSetting.id','{n}.ProductShipSetting');
        if ($data_type == 'Product') {
            $this->set('type', '团购');
        }
        if ($data_type == 'Try') {
            $this->set('type', '秒杀');
        }
        $this->set('name', $_REQUEST['name']);
        $this->set('datas', json_encode($shipSettings));
        $this->set('data_id', $data_id);
        $this->set('data_type', $data_type);
    }

    public function admin_save(){
        $this->autoRender=false;
        $dataId = $_POST['dataId'];
        $dataType = $_POST['dataType'];
        $postData = $_POST['data'];
        $this->ProductShipSetting->deleteAll(array('data_id'=>$dataId,'data_type'=>$dataType));

        if($this->ProductShipSetting->saveAll($postData)){
            echo json_encode(array('success'=>true));
        }else{
            echo json_encode(array('success'=>false));
        }
    }
}