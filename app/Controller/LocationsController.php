<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 14/11/4
 * Time: 下午1:31
 */

class LocationsController extends AppController{
    public $name = 'Locations';

    public function get_province(){
        $this->autoRender = false;
        if($this->request->is('post')||$this->request->is('get')){
            $params = array('conditions' => array('parent_id' => 1), 'fields' => array('id', 'name'));
            $provinces = $this->Location->find('list',$params);
            echo json_encode($provinces);
        }
    }
    public function get_city(){
        $this->autoRender = false;
        if($this->request->is('post')||$this->request->is('get')){
            $province_id = intval($_REQUEST['provinceId']);
            $params = array('conditions' => array('parent_id' => $province_id ), 'fields' => array('id', 'name'));
            $cities = $this->Location->find('list',$params);
            echo json_encode($cities);
        }
    }
    public function get_county(){
        $this->autoRender = false;
        if($this->request->is('post')||$this->request->is('get')){
            $city_id = intval($_REQUEST['cityId']);
            $params = array('conditions' => array('parent_id' => $city_id ), 'fields' => array('id', 'name'));
            $counties = $this->Location->find('list',$params);
            echo json_encode($counties);
        }
    }
    public function get_town(){
        $this->autoRender = false;
        if($this->request->is('post')||$this->request->is('get')){
            $county_id = intval($_REQUEST['countyId']);
            $params = array('conditions' => array('parent_id' => $county_id ), 'fields' => array('id', 'name'));
            $counties = $this->Location->find('list',$params);
            echo json_encode($counties);
        }
    }
    public function get_address(){
        $this->autoRender = false;
        if($this->request->is('get')){
            $province_id = intval($_REQUEST['province_id']);
            $city_id = intval($_REQUEST['city_id']);
            $county_id = intval($_REQUEST['county_id']);
            $town_id = intval($_REQUEST['town_id']);
            $params = array($province_id, $city_id, $county_id, $town_id);
            $group_id = array($province_id, $city_id, $county_id);
            $histories = $this->Location->find('list', array('conditions' => array('id' => $params), 'fields' => array('id', 'name') ));
            $address_group = $this->Location->find('list', array('conditions' => array('parent_id' => $group_id), 'fields' => array('id', 'name', 'parent_id') ));
            $successinfo = array('histories'=> $histories, 'city_list' => $address_group[$province_id], 'county_list' => $address_group[$city_id ], 'town_list' => $address_group[$county_id]);
            echo json_encode($successinfo);
        }
    }
}