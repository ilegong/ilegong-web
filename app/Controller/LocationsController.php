<?php

class LocationsController extends Controller
{
    public $name = 'Locations';

    public static $PROVINCES_CACHE_KEY = 'location_province_cache';

    public static $PROVINCES_LIST_CACHE_KEY = 'location_province_list_cache';

    public static $CITY_CACHE_KEY = 'location_city_cache';

    public static $COUNTRY_CACHE_KEY = 'location_country_cache';

    //var $uses = array('Location');

    public function get_province_list()
    {
        $this->autoRender = false;
        $cacheData = Cache::read(self::$PROVINCES_CACHE_KEY);
        if (empty($cacheData)) {
            $params = array('conditions' => array('parent_id between ? and ?' => array(1, 10)), 'fields' => array('id', 'name', 'parent_id'));
            $provinces = $this->Location->find('all', $params);
            $provinces = Hash::extract($provinces, '{n}.Location');
            usort($provinces, 'sort_data_by_id_desc');
            $cacheData = json_encode($provinces);
            Cache::write(self::$PROVINCES_CACHE_KEY, $cacheData);
        }
        echo $cacheData;
        exit();
    }

    public function get_provinces()
    {
        $this->autoRender = false;
        $cacheData = Cache::read(self::$PROVINCES_CACHE_KEY);
        if (empty($cacheData)) {
            $params = array('conditions' => array('parent_id between ? and ?' => array(1, 10)), 'fields' => array('id', 'name', 'parent_id'), 'order' => array('parent_id ASC'));
            $provinces = $this->Location->find('list', $params);
            $cacheData = json_encode($provinces);
            Cache::write(self::$PROVINCES_CACHE_KEY, $cacheData);
        }
        echo $cacheData;
        exit();
    }

    public function get_city()
    {
        $this->autoRender = false;
        if ($this->request->is('post') || $this->request->is('get')) {
            $province_id = intval($_REQUEST['provinceId']);
            $cache_key = self::$CITY_CACHE_KEY . '_' . $province_id;
            $cacheData = Cache::read($cache_key);
            if (empty($cacheData)) {
                $params = array('conditions' => array('parent_id' => $province_id), 'fields' => array('id', 'name'));
                $cities = $this->Location->find('list', $params);
                $cacheData = json_encode($cities);
                Cache::write($cache_key, $cacheData);
            }

            echo $cacheData;
            exit();
        }
    }

    public function get_county()
    {
        $this->autoRender = false;
        if ($this->request->is('post') || $this->request->is('get')) {
            $city_id = intval($_REQUEST['cityId']);
            $cache_key = self::$COUNTRY_CACHE_KEY . '_' . $city_id;
            $cache_data = Cache::read($cache_key);
            if (!empty($cache_data)) {
                echo $cache_data;
                exit();
            }
            $params = array('conditions' => array('parent_id' => $city_id), 'fields' => array('id', 'name'));
            $counties = $this->Location->find('list', $params);
            $result = json_encode($counties);
            Cache::write($cache_key, $result);
            echo $result;
            exit();
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
            exit();
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
        exit();
    }
}