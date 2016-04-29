<?php

class CommonApiController extends Controller
{

    static  $OFFLINE_STORE_DATA_CACHE_KEY = 'offline_store_data_cache_key';

    public function beforeFilter()
    {
        $this->autoRender = false;
    }

    public function get_ship_type_list()
    {
        $list = ShipAddress::ship_type_list();
        echo json_encode($list);
        exit();
    }

    public function get_bank_types()
    {
        echo json_encode(get_bank_types());
        exit();
    }

    public function get_offline_store($area_id)
    {
        $cache_data = Cache::read(self::$OFFLINE_STORE_DATA_CACHE_KEY . '_' . $area_id);
        if (!empty($cache_data)) {
            echo $cache_data;
            exit;
        }
        $this->loadModel('OfflineStore');
        $stores = $this->OfflineStore->find('all', [
            'conditions' => [
                'area_id' => $area_id
            ]
        ]);
        $stores = Hash::combine($stores, '{n}.OfflineStore.id', '{n}.OfflineStore');
        $cache_data = json_encode($stores);
        Cache::write(self::$OFFLINE_STORE_DATA_CACHE_KEY . '_' . $area_id, $cache_data);
        echo $cache_data;
        exit();
    }
}