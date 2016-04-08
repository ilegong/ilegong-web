<?php

// 包含：跟分享相关的业务逻辑
// 不包含：订单；产品街；首页产品；团长；用户
class WesharesComponent extends Component
{
    public $components = array('ShareUtil', 'DeliveryTemplate');

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
        $this->saveWeshareShipType($weshare['Weshare']['id'], $weshare['Weshare']['creator'], $shipSetData);
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

        $WeshareM->updateAll(array('status' => WESHARE_STOP_STATUS), array('id' => $weshare_id, 'creator' => $uid, 'status' => WESHARE_NORMAL_STATUS));
        //stop child share
        $WeshareM->updateAll(array('status' => WESHARE_STOP_STATUS), array('refer_share_id' => $weshare_id, 'status' => WESHARE_NORMAL_STATUS, 'type' => GROUP_SHARE_TYPE));

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
        $weshareM->update(array('status' => WESHARE_DELETE_STATUS), array('id' => $weshare_id, 'creator' => $uid));

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
     * @param $userId
     * @param $weshareShipData
     * @return mixed
     * 保存分享的物流方式
     */
    private function saveWeshareShipType($weshareId, $userId, $weshareShipData)
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