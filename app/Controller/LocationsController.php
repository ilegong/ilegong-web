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
            $params = array('conditions' => array('parent_id' => 1), 'fields' => 'name');
            $provinces = $this->Location->find('list',$params);
            echo json_encode($provinces);
        }
    }
    public function get_city(){
        $this->autoRender = false;
        if($this->request->is('post')){
            $province_id = $_POST['provinceId'];
            $params = array('conditions' => array('parent_id' => $province_id ));
            $cities = $this->Location->find('list',$params);
            echo json_encode($cities);
        }
    }
    public function get_county(){
        $this->autoRender = false;
        if($this->request->is('post')){
            $city_id = $_POST['cityId'];
            $params = array('conditions' => array('parent_id' => $city_id ));
            $counties = $this->Location->find('list',$params);
            echo json_encode($counties);
        }
    }
    public function get_town(){
        $this->autoRender = false;
        if($this->request->is('post')){
            $county_id = $_POST['countyId'];
            $params = array('conditions' => array('parent_id' => $county_id ));
            $counties = $this->Location->find('list',$params);;
            echo json_encode($counties);
        }
    }
    public function get_address(){
        $this->autoRender = false;
        if($this->request->is('get')){
            $group_id = array();
            $group_id[] = $_REQUEST['province_id'];
            $group_id[] = $_REQUEST['city_id'];
            $group_id[] = $_REQUEST['county_id'];
            $group_id[] = $_REQUEST['town_id'];
            $a = $this->Location->find('list', array('conditions' => array('id' => $group_id)));
            $b = $this->Location->find('list', array('conditions' => array('parent_id' => $group_id[0])));
            $c = $this->Location->find('list', array('conditions' => array('parent_id' => $group_id[1])));
            $d = $this->Location->find('list', array('conditions' => array('parent_id' => $group_id[2])));
            $successinfo = array('histories'=>$a, 'city_list' => $b, 'county_list' => $c, 'town_list' => $d);
            echo json_encode($successinfo);
        }
    }
}