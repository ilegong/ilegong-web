<?php

class CommonApiController extends Controller
{

    static  $OFFLINE_STORE_DATA_CACHE_KEY = 'offline_store_data_cache_key';

    var $components = ['ShareUtil'];

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
        echo json_encode([
            0 => '工商银行',
            1 => '建设银行',
            2 => '农业银行',
            3 => '邮政储蓄',
            4 => '招商银行',
            5 => '北京银行',
            6 => '交通银行'
        ]);
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
        $banners = $this->ShareUtil->get_index_banners();
        $banner = [];
        foreach($banners as $banner_item){
            $type = strpos($banner_item['link'], 'weshares/view') == false ? '0' : '1';
            $b = ['banner_img' => $banner_item['banner'], 'type' => $type, 'data' => $banner_item['link']];
            if ($b['type'] == '1') {
                $b['data'] = preg_replace('/[^\-\d]*(\-?\d*).*/', '$1', $banner_item['link']);
            }
            $banner[] = $b;
        }
        echo json_encode($banner);
        exit;
    }

    public function get_app_has_new_version()
    {
        echo json_encode(['version' => strval('1.5.0'), 'force' => true, 'msg' => '解决已知问题']);
        exit;
    }
}