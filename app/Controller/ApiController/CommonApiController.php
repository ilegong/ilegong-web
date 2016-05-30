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

    public function get_banner(){
        $banner = [
            [
                'banner_img' => 'http://static.tongshijia.com/images/index/2016/05/18/7c014ae6-1ca2-11e6-88d7-00163e1600b6.jpg',
                'type' => '0',
                'data' => 'https://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=506221350&idx=1&sn=96cb426c8a1eed785cd86e85f6d9c9e5&scene=1'
            ],
            [
                'banner_img' => 'http://static.tongshijia.com/images/index/2016/05/18/7c0161c0-1ca2-11e6-88d7-00163e1600b6.jpg',
                'type' => '1',
                'data' => '4675'
            ]
        ];
        echo json_encode($banner);
        exit;
    }

    public function get_app_has_new_version()
    {
        echo json_encode(['version' => strval('1.5.0'), 'force' => true, 'msg' => '解决已知问题']);
        exit;
    }
}