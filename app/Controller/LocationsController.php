<?php

/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 14/11/4
 * Time: 下午1:31
 */
class LocationsController extends AppController
{
    public $name = 'Locations';

    public function get_provinces()
    {
        $this->autoRender = false;
        $params = array('conditions' => array('parent_id between ? and ?'=>array(1, 10)), 'fields' => array('id', 'name', 'parent_id'));
        $provinces = $this->Location->find('list', $params);
        echo json_encode($provinces);
    }

    public function get_city()
    {
        $this->autoRender = false;
        if ($this->request->is('post') || $this->request->is('get')) {
            $province_id = intval($_REQUEST['provinceId']);
            $params = array('conditions' => array('parent_id' => $province_id), 'fields' => array('id', 'name'));
            $cities = $this->Location->find('list', $params);
            echo json_encode($cities);
        }
    }

    public function get_county()
    {
        $this->autoRender = false;
        if ($this->request->is('post') || $this->request->is('get')) {
            $city_id = intval($_REQUEST['cityId']);
            $params = array('conditions' => array('parent_id' => $city_id), 'fields' => array('id', 'name'));
            $counties = $this->Location->find('list', $params);
            echo json_encode($counties);
        }
    }

    public function get_town()
    {
        $this->autoRender = false;
        if ($this->request->is('post') || $this->request->is('get')) {
            $county_id = intval($_REQUEST['countyId']);
            $params = array('conditions' => array('parent_id' => $county_id), 'fields' => array('id', 'name'));
            $counties = $this->Location->find('list', $params);
            echo json_encode($counties);
        }
    }

    public function get_address()
    {
        $this->autoRender = false;
        $inputData = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
        $province_id = intval($inputData['province_id']);
        $city_id = intval($inputData['city_id']);
        $history_params = array($province_id, $city_id);
        $connection_params = array($province_id, $city_id);
        if (!empty($inputData['county_id'])) {
            $county_id = intval($inputData['county_id']);
            $history_params[] = $county_id;
            if (!$this->RequestHandler->isMobile()) {
                $connection_params[] = $county_id;
            }
        }
        if (!empty($inputData['town_id'])) {
            $town_id = intval($inputData['town_id']);
            $history_params[] = $town_id;
        }
        $histories = $this->Location->find('list', array('conditions' => array('id' => $history_params), 'fields' => array('id', 'name')));
        $connection_address = $this->Location->find('list', array('conditions' => array('parent_id' => $connection_params), 'fields' => array('id', 'name', 'parent_id')));
        $successinfo = array('histories' => $histories, 'city_list' => $connection_address[$province_id], 'county_list' => $connection_address[$city_id], 'town_list' => $connection_address[$county_id]);
        echo json_encode($successinfo);
    }
}