<?php

// 包含：跟分享相关的业务逻辑
// 不包含：订单；产品街；首页产品；团长；用户
class WesharesComponent extends Component
{
    public $components = array('ShareUtil', 'DeliveryTemplate', 'WeshareBuy', 'ShareAuthority');


    public function get_weshare_rebate_setting($weshare_id){
        $proxyRebatePercentM = ClassRegistry::init('ProxyRebatePercent');
        $proxy_share_percent = $proxyRebatePercentM->find('first', array(
            'conditions' => array(
                'share_id' => $weshare_id,
                'deleted' => DELETED_NO,
                'status' => PUBLISH_YES
            )
        ));
        return $proxy_share_percent['ProxyRebatePercent'];
    }

    public function get_app_weshare_detail($weshare_id)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $weshare_detail = $weshareM->find('first', [
            'conditions' => [
                'id' => $weshare_id
            ]
        ]);
        $weshare_detail = $weshare_detail['Weshare'];
        return $weshare_detail;
    }

    public function get_weshare_products($weshare_id)
    {
        $weshareProductM = ClassRegistry::init('WeshareProduct');
        $weshare_products = $weshareProductM->find('all', [
            'conditions' => [
                'weshare_id' => $weshare_id,
                'deleted' => DELETED_NO
            ]
        ]);
        $result = [];
        //商品的统计
        $share_summery = $this->WeshareBuy->get_share_buy_summery($weshare_id);
        $share_summery = $share_summery['details'];
        foreach ($weshare_products as $product_item) {
            $product = $product_item['WeshareProduct'];
            $left = 0;
            if ($product['store'] > 0) {
                $left = $product['store'] - intval($share_summery[$product['id']]['num']);
            }
            $product['left'] = $left < 0 ? 0 : $left;
            $product['price'] = $product['price'] / 100;
            $product['weight'] = $product['weight'] / 1000;
            $result[] = $product;
        }
        return $result;
    }

    public function get_app_share_balance_data($weshare_id ,$uid)
    {
        $weshareInfo = $this->ShareUtil->get_tag_weshare_detail($weshare_id);
        $couponItemM = ClassRegistry::init('CouponItem');
        $share_creator = $weshareInfo['creator']['id'];
        $my_coupon_items = $couponItemM->find_my_valid_share_coupons($uid, $share_creator);
        return [
            'coupons' => $my_coupon_items[0],
            'ship_settings' => $weshareInfo['ship_type'],
            'delivery_template' => $weshareInfo['deliveryTemplate'],
            'proxy_rebate_percent' => $weshareInfo['proxy_rebate_percent'],
            'offline_address' => $weshareInfo['addresses'],
            'consignee' => $consignee = $this->getShareConsignees($uid)
        ];
    }


    /**
     * @param $weshareId
     * @param $uid
     * @return array
     * 获取分享详情的
     */
    public function get_weshare_detail($weshareId, $uid)
    {
        $weshareInfo = $this->ShareUtil->get_tag_weshare_detail($weshareId);
        if (empty($weshareInfo['products'])) {
            return [];
        }
        $is_me = $uid == $weshareInfo['creator']['id'];
        $current_user = [];
        $user_rebate_total = 0;
        if (!empty($uid)) {
            $userM = ClassRegistry::init('User');
            $current_user = $userM->find('first', array(
                'conditions' => array(
                    'id' => $uid
                ),
                'recursive' => 1,
                'fields' => ['id', 'nickname', 'image', 'wx_subscribe_status', 'is_proxy', 'avatar', 'mobilephone', 'payment', 'rebate_money'],
            ));
            $current_user = $current_user['User'];
            //reset user image
            $current_user['image'] = get_user_avatar($current_user);
            $current_user_level_data = $this->ShareUtil->get_user_level($uid);
            $current_user['level'] = $current_user_level_data;
            $current_user['is_proxy'] = $current_user_level_data['data_value'] >= PROXY_USER_LEVEL_VALUE ? 1 : 0;
            $user_rebate_total = $current_user['rebate_money'];
        }
        if (!$is_me) {
            $sub_status = $this->WeshareBuy->check_user_subscribe($weshareInfo['creator']['id'], $uid);
        } else {
            $sub_status = true;
        }
        $creatorId = $weshareInfo['creator']['id'];
        $user_share_summery = $this->WeshareBuy->get_user_share_summary($creatorId);
        $couponItemM = ClassRegistry::init('CouponItem');
        $my_coupon_items = $couponItemM->find_my_valid_share_coupons($uid, $creatorId);
        $recommend_data = $this->WeshareBuy->load_share_recommend_data($weshareId);
        $is_manage_user = $this->ShareAuthority->user_can_view_share_order_list($uid, $weshareId);
        $can_manage_share = $this->ShareAuthority->user_can_manage_share($uid, $weshareId);
        $can_edit_share = $this->ShareAuthority->user_can_edit_share_info($uid, $weshareId);
        $share_summery = $this->WeshareBuy->get_share_buy_summery($weshareId);
        $weshare_ship_settings = $weshareInfo['ship_type'];
        unset($weshareInfo['ship_type']);
        $consignee = $this->getShareConsignees($uid);
        return [
            'weshare' => $weshareInfo,
            'recommendData' => $recommend_data,
            'current_user' => $current_user,
            'weshare_ship_settings' => $weshare_ship_settings,
            'consignee' => $consignee,
            'user_share_summery' => $user_share_summery,
            'my_coupons' => $my_coupon_items[0],
            'sub_status' => $sub_status,
            'is_manage' => $is_manage_user,
            'can_manage_share' => $can_manage_share,
            'can_edit_share' => $can_edit_share,
            'share_summery' => $share_summery,
            'user_rebate_total' => $user_rebate_total
        ];
    }

    /**
     * 获取用户记住的地址
     */
    private function getShareConsignees($uid)
    {
        $orderConsigneeM = ClassRegistry::init('OrderConsignee');
        $offlineStoreM = ClassRegistry::init('OfflineStore');
        $consignees = $orderConsigneeM->find('all', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => PUBLISH_YES,
                'type' => [TYPE_CONSIGNEES_SHARE, TYPE_CONSIGNEES_SHARE_ZITI, TYPE_CONSIGNEE_SHARE_OFFLINE_STORE]
            ),
            'fields' => array('id', 'name', 'mobilephone', 'address', 'ziti_id', 'remark_address', 'province_id', 'city_id', 'county_id', 'type', 'area')
        ));
        //load remember offline store id
        foreach ($consignees as &$consignee) {
            $ziti_id = $consignee['OrderConsignee']['ziti_id'];
            $type = $consignee['OrderConsignee']['type'];
            if (!empty($ziti_id) && $type == TYPE_CONSIGNEE_SHARE_OFFLINE_STORE) {
                $offlineStore = $offlineStoreM->findById($ziti_id);
                if (!empty($offlineStore)) {
                    $consignee['OrderConsignee']['offlineStore'] = $offlineStore['OfflineStore'];
                }
            }
        }
        return Hash::extract($consignees, '{n}.OrderConsignee');
    }

    public function create_weshare($postDataArray, $uid)
    {
        $WeshareM = ClassRegistry::init('Weshare');
        $dataSource = $WeshareM->getDataSource();
        $dataSource->begin();
        $weshareData = array();
        if (empty($postDataArray['id'])) {
            $this->log('Create weshare for user '.$uid, LOG_INFO);
            $weshareData['creator'] = $uid;
        } else {
            $this->log('Update weshare ' . $postDataArray['id'] . ' for user '.$uid , LOG_INFO);
            $weshareData['creator'] = $postDataArray['creator']['id'];
            $this->DeliveryTemplate->clear_share_delivery_template($postDataArray['id']);
        }

        $weshareData['id'] = $postDataArray['id'];
        $weshareData['title'] = trim($postDataArray['title']);
        $weshareData['description'] = trim($postDataArray['description']);
        $weshareData['send_info'] = trim($postDataArray['send_info']);
        if (!empty($postDataArray['status'])) {
            $weshareData['status'] = intval($postDataArray['status']);
        }
        $weshareData['created'] = date('Y-m-d H:i:s');
        $images = $postDataArray['images'];
        $weshareData['images'] = implode('|', $images);
        $weshareData['default_image'] = count($images) > 0 ? $images[0] : '';

        $productsData = $postDataArray['products'];
        $addressesData = $postDataArray['addresses'];
        $shipSetData = $postDataArray['ship_type'];
        $proxyRebatePercent = $postDataArray['proxy_rebate_percent'];
        $deliveryTemplates = $postDataArray['delivery_templates'];
        //merge for child share data
        $saveBuyFlag = $weshare = $WeshareM->save($weshareData);

        if (empty($saveBuyFlag)) {
            if (empty($weshareData['id'])) {
                $this->log('Failed to create weshare for user '.$uid, LOG_WARNING);
            } else {
                $this->log('Failed to update weshare '.$weshareData['id'].' for user '.$uid, LOG_WARNING);
            }
            $dataSource->rollback();
            return array('success' => false, 'uid' => $uid);
        }

        //merge for child share data
        $this->saveWeshareProducts($weshare['Weshare']['id'], $productsData);
        $this->saveWeshareAddresses($weshare['Weshare']['id'], $addressesData);
        $this->saveWeshareShipType($weshare['Weshare']['id'], $shipSetData);
        $this->saveWeshareProxyPercent($weshare['Weshare']['id'], $proxyRebatePercent);
        $this->saveWeshareDeliveryTemplate($weshare['Weshare']['id'], $weshare['Weshare']['creator'], $deliveryTemplates);

        if (empty($weshareData['id'])) {
            $this->on_weshare_created($uid, $weshare);
            $this->log('Create weshare '.$weshare['Weshare']['id'].' for user '.$uid +' successfully: '.json_encode($weshare) , LOG_INFO);
        }
        else{
            $this->on_weshare_updated($uid, $weshare);
            $this->log('Update weshare '.$weshareData['id'].' for user '.$uid +' successfully: '.json_encode($weshare) , LOG_INFO);
        }

        //todo update child share data and product data
        //update product
        //$this->ShareUtil->cascadeSaveShareData($weshareData);
        $dataSource->commit();
        return array('success' => true, 'id' => $weshare['Weshare']['id']);
    }

    public function stop_weshare($uid, $weshare_id)
    {
        $WeshareM = ClassRegistry::init('Weshare');
        $this->log('User ' . $uid . ' stops weshare ' . $weshare_id, LOG_INFO);
        $WeshareM->updateAll(array('status' => WESHARE_STATUS_STOP, 'close_date' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $weshare_id, 'creator' => $uid, 'status' => WESHARE_STATUS_NORMAL));
        $this->on_weshare_stopped($uid, $weshare_id);
    }


    /**
     * @param $uid
     * @param $weshare_id
     * @return int
     * 删除分享
     */
    public function delete_weshare($uid, $weshare_id) {
        $weshareM = ClassRegistry::init('Weshare');
        $weshareM->update(array('status' => WESHARE_STATUS_DELETED), array('id' => $weshare_id, 'creator' => $uid));

        $this->log('Delete weshare '.$weshare_id.' of user '.$uid . ' successfully', LOG_INFO);

        $this->on_weshare_deleted($uid, $weshare_id);
    }

    public function publish_weshare($uid, $weshare_id){
        $weshareM = ClassRegistry::init('Weshare');
        $weshareM->update(array('status' => WESHARE_STATUS_NORMAL), array('id' => $weshare_id, 'creator' => $uid));
        $this->on_weshare_publish($uid, $weshare_id);
    }

    public function get_u_create_share($uid, $limit, $page)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $weshares = $weshareM->find('all', [
            'conditions' => [
                'creator' => $uid,
                'status' => WESHARE_STATUS_NORMAL,
                'type' => [SHARE_TYPE_GROUP, SHARE_TYPE_DEFAULT, SHARE_TYPE_POOL_FOR_PROXY, SHARE_TYPE_POOL]
            ],
            'limit' => $limit,
            'page' => $page,
            'order' => ['Weshare.id DESC'],
            'fields' => ['Weshare.id', 'Weshare.title', 'Weshare.description', 'Weshare.default_image', 'Weshare.creator', 'Weshare.view_count']
        ]);
        $result = [];
        if(!empty($weshares)){
            foreach ($weshares as $weshare_item) {
                $data_item = $weshare_item['Weshare'];
                $data_item['summary'] = $this->ShareUtil->get_index_product_summary($data_item['id']);
                $data_item['summary']['view_count'] = $data_item['view_count'];
                $result[] = $data_item;
            }
        }
        return $result;
    }

    public function get_u_buy_share($uid, $limit, $page)
    {
        $optLogM = ClassRegistry::init('OptLog');
        $logs = $optLogM->find('all', [
            'conditions' => [
                'OptLog.user_id' => $uid,
                'OptLog.obj_type' => OPT_LOG_SHARE_BUY,
                'Weshare.status' => WESHARE_STATUS_NORMAL
            ],
            'joins' => [
                [
                    'table' => 'weshares',
                    'alias' => 'Weshare',
                    'conditions' => ['Weshare.id = OptLog.obj_id'],
                    'type' => 'left'
                ]
            ],
            'fields' => ['Weshare.created','Weshare.id', 'Weshare.title', 'Weshare.description', 'Weshare.default_image', 'Weshare.creator'],
            'limit' => $limit,
            'page' => $page,
            'order' => ['OptLog.id DESC']
        ]);
        $result = [];
        foreach ($logs as $log_item) {
            $data_item = $log_item['Weshare'];
            $data_item['summary'] = $this->ShareUtil->get_index_product_summary($data_item['id']);
            $result[] = $data_item;
        }
        return $result;
    }

    public function get_recommend_weshares($proxy_id, $limit)
    {
        $key = USER_RECOMMEND_WESHARES_CACHE_KEY . '_' . $proxy_id;
        $cacheData = Cache::read($key);
        if (!empty($cacheData)) {
            return $cacheData;
        }

        $weshareM = ClassRegistry::init('Weshare');
        $weshares = $weshareM->find('all', [
            'conditions' => [
                'creator' => $proxy_id,
                'status' => WESHARE_STATUS_NORMAL,
                'type' => [SHARE_TYPE_GROUP, SHARE_TYPE_DEFAULT, SHARE_TYPE_POOL_FOR_PROXY, SHARE_TYPE_POOL]
            ],
            'order' => ['Weshare.id DESC'],
            'fields' => ['Weshare.id', 'Weshare.title', 'Weshare.description', 'Weshare.default_image', 'Weshare.creator', 'Weshare.view_count']
        ]);
        $result = [];
        if(!empty($weshares)){
            foreach ($weshares as $weshare_item) {
                $data_item = $weshare_item['Weshare'];
                $data_item['summary'] = $this->ShareUtil->get_index_product_summary($data_item['id']);
                $data_item['summary']['view_count'] = $data_item['view_count'];
                $result[] = $data_item;
            }
        }
        usort($result, function($a, $b){
            if($a == $b){
                return 0;
            }
            if($b['summary']['view_count'] == 0){
                return -1;
            }
            if($a['summary']['view_count'] == 0){
                return 1;
            }
            return $a['summary']['order_count'] / $a['summary']['view_count'] > $b['summary']['order_count'] / $b['summary']['view_count'] ? -1 : 1;
        });

        $result = array_slice($result, 0, 4);
        Cache::write($key, $result);
        return $result;
    }

    private function on_weshare_stopped($uid, $weshare_id)
    {
        delete_redis_data_by_key('_'.$weshare_id);
        Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $uid, '');
        Cache::write(USER_RECOMMEND_WESHARES_CACHE_KEY . '_' . $uid, '');
    }

    private function on_weshare_publish($uid, $weshare_id)
    {
        delete_redis_data_by_key('_'.$weshare_id);
        Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $uid, '');
    }

    private function on_weshare_created($uid, $weshare){
        $this->clear_cache_for_weshare($uid, $weshare);
        // 消息流
        $images = explode('|', $weshare['Weshare']['images']);
        $thumbnail = null;
        if (count($images) > 0) {
            $thumbnail = $images[0];
        }
        $this->ShareUtil->save_create_share_opt_log($weshare['Weshare']['id'], $thumbnail, $weshare['Weshare']['title'], $uid);
        // check user level and init level data when not
        $this->ShareUtil->check_and_save_default_level($uid);
    }

    private function on_weshare_updated($uid, $weshare){
        $this->clear_cache_for_weshare($uid, $weshare);
    }

    private function on_weshare_deleted($uid, $weshare_id){
        Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $uid, '');
    }

    private function clear_cache_for_weshare($uid, $weshare){
        Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $uid, '');
        delete_redis_data_by_key('_'.$weshare['Weshare']['id']);
    }

    /**
     * @param $weshareId
     * @param $weshareProxyPercent
     * 保存团长比例
     */
    private function saveWeshareProxyPercent($weshareId, $weshareProxyPercent)
    {
        $ProxyRebatePercentM = ClassRegistry::init('ProxyRebatePercent');
        $ProxyRebatePercentM->deleteAll(['share_id' => $weshareId]);
        $weshareProxyPercent['id'] = null;
        $weshareProxyPercent['share_id'] = $weshareId;
        $weshareProxyPercent['percent'] = empty($weshareProxyPercent['percent']) ? 0 : $weshareProxyPercent['percent'];
        return $ProxyRebatePercentM->save($weshareProxyPercent);
    }

    //TODO delete not use product
    /**
     * @param $weshareId
     * @param $weshareProductData
     * 保存分享商品
     */
    private function saveWeshareProducts($weshareId, $weshareProductData)
    {
        $WeshareProductM = ClassRegistry::init('WeshareProduct');
        if (empty($weshareProductData)) {
            return;
        }
        foreach ($weshareProductData as &$product) {
            $product['weshare_id'] = $weshareId;
            $product['price'] = ($product['price'] * 100);
            $store = $product['store'];
            if (empty($store)) {
                $product['store'] = 0;
            }
            $tag_id = $product['tag_id'];
            if (empty($tag_id)) {
                $product['tag_id'] = 0;
            }
            if(empty($product['weight'])){
                $product['weight'] = 0;
            }
            $product['weight'] = $product['weight'] * 1000;
        }
        return $WeshareProductM->saveAll($weshareProductData);
    }

    /**
     * @param $weshareId
     * @param $weshareShipData
     * @return mixed
     * 保存分享的物流方式
     */
    private function saveWeshareShipType($weshareId, $weshareShipData)
    {
        $WeshareSettingM = ClassRegistry::init('WeshareShipSetting');
        foreach ($weshareShipData as &$item) {
            $item['weshare_id'] = $weshareId;
        }
        return $WeshareSettingM->saveAll($weshareShipData);
    }

    /**
     * @param $weshareId
     * @param $weshareAddressData
     * 保存分享的 自有自提点
     */
    private function saveWeshareAddresses($weshareId, $weshareAddressData)
    {
        $WeshareAddressM = ClassRegistry::init('WeshareAddress');
        if (empty($weshareAddressData)) {
            return;
        }
        foreach ($weshareAddressData as &$address) {
            $address['weshare_id'] = $weshareId;
        }
        return $WeshareAddressM->saveAll($weshareAddressData);
    }

    /**
     * @param $weshareId
     * @param $user_id
     * @param $weshareDeliveryTemplateData
     * 保存分享的快递模板
     */
    private function saveWeshareDeliveryTemplate($weshareId, $user_id, $weshareDeliveryTemplateData)
    {
        if (!empty($weshareDeliveryTemplateData)) {
            //filter data
            foreach ($weshareDeliveryTemplateData as &$itemTemplateData) {
                $itemTemplateData['weshare_id'] = $weshareId;
                $itemTemplateData['user_id'] = $user_id;
                $itemTemplateData['add_fee'] = $itemTemplateData['add_fee']*100;
                $itemTemplateData['start_fee'] = $itemTemplateData['start_fee']*100;
                if($itemTemplateData['unit_type'] == DELIVERY_UNIT_WEIGHT_TYPE){
                    //按重量计算运费
                    $itemTemplateData['start_units'] = $itemTemplateData['start_units'] * 1000;
                    $itemTemplateData['add_units'] = $itemTemplateData['add_units'] * 1000;
                }
            }
            $this->DeliveryTemplate->save_all_delivery_template($weshareDeliveryTemplateData);
        }
    }
}