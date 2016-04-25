<?php

// 包含：跟分享相关的业务逻辑
// 不包含：订单；产品街；首页产品；团长；用户
class WesharesComponent extends Component
{
    public $components = array('ShareUtil', 'DeliveryTemplate', 'WeshareBuy', 'ShareAuthority');


    public function get_app_weshare_detail($weshareId, $uid){

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
        if (!empty($uid)) {
            $userM = ClassRegistry::init('User');
            $current_user = $userM->find('first', array(
                'conditions' => array(
                    'id' => $uid
                ),
                'recursive' => 1,
                'fields' => ['id', 'nickname', 'image', 'wx_subscribe_status', 'is_proxy', 'avatar', 'mobilephone', 'payment'],
            ));
            $current_user = $current_user['User'];
            //reset user image
            $current_user['image'] = get_user_avatar($current_user);
            $current_user_level_data = $this->ShareUtil->get_user_level($uid);
            $current_user['level'] = $current_user_level_data;
            $current_user['is_proxy'] = $current_user_level_data['data_value'] >= PROXY_USER_LEVEL_VALUE ? 1 : 0;
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
            'share_summery' => $share_summery
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
        $weshareData['title'] = $postDataArray['title'];
        $weshareData['description'] = $postDataArray['description'];
        $weshareData['send_info'] = $postDataArray['send_info'];

        $weshareData['created'] = date('Y-m-d H:i:s');
        $images = $postDataArray['images'];
        $weshareData['images'] = implode('|', $images);

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

        $WeshareM->updateAll(array('status' => WESHARE_STATUS_STOP), array('id' => $weshare_id, 'creator' => $uid, 'status' => WESHARE_STATUS_NORMAL));
        //stop child share
        $WeshareM->updateAll(array('status' => WESHARE_STATUS_STOP), array('refer_share_id' => $weshare_id, 'status' => WESHARE_STATUS_NORMAL, 'type' => SHARE_TYPE_GROUP));

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


    private function on_weshare_stopped($uid, $weshare_id)
    {
        Cache::write(SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weshare_id, '');
        Cache::write(SHARE_DETAIL_DATA_WITH_TAG_CACHE_KEY . '_' . $weshare_id, '');
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
        Cache::write(SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weshare['Weshare']['id'], '');
        Cache::write(SHARE_DETAIL_DATA_WITH_TAG_CACHE_KEY . '_' . $weshare['Weshare']['id'], '');
        Cache::write(SHARE_SHIP_SETTINGS_CACHE_KEY . '_' . $weshare['Weshare']['id'], '');
        Cache::write(SIMPLE_SHARE_INFO_CACHE_KEY . '_' . $weshare['Weshare']['id'], '');
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