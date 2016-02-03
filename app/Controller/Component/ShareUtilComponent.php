<?php

class ShareUtilComponent extends Component
{

    var $name = 'ShareUtil';

    var $normal_order_status = array(ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY);

    public $components = array('Weixin', 'WeshareBuy', 'RedisQueue');

    /**
     * @param $weshare_id
     * @param $uid
     * 触发建团消息
     */
    public function trigger_send_new_share_msg($weshare_id, $uid)
    {
        $fansPageInfo = $this->WeshareBuy->get_user_relation_page_info($uid);
        $pageCount = $fansPageInfo['pageCount'];
        $pageSize = $fansPageInfo['pageSize'];
        $this->RedisQueue->add_tasks('share',"/weshares/process_send_new_share_msg/" . $weshare_id . '/' . $pageCount . '/' . $pageSize);
    }

    /**
     * @param $weshareId
     * @param $sharer_id
     * 迁移粉丝数据
     */
    public function process_weshare_task($weshareId, $sharer_id)
    {
        $userRelationM = ClassRegistry::init('UserRelation');
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'member_id' => $weshareId,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => array(ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY)
            ),
            'fields' => array('id', 'creator'),
            'limit' => 500
        ));
        $saveDatas = array();
        foreach ($orders as $order) {
            if ($this->check_user_relation($sharer_id, $order['Order']['creator'])) {
                $itemData = array('user_id' => $sharer_id, 'follow_id' => $order['Order']['creator'], 'type' => 'Buy', 'created' => date('Y-m-d H:i:s'));
                $saveDatas[] = $itemData;
            }
        }
        $userRelationM->saveAll($saveDatas);
    }

    public function get_all_weshares()
    {
        $weshareM = ClassRegistry::init('Weshare');
        $allWeshares = $weshareM->find('all', array(
            'limit' => 200
        ));
        return $allWeshares;
    }

    public function check_user_is_subscribe($user_id, $follow_id)
    {
        $userRelationM = ClassRegistry::init('UserRelation');
        $relation = $userRelationM->find('first', array(
            'conditions' => array(
                'user_id' => $user_id,
                'follow_id' => $follow_id
            )
        ));
        return (!empty($relation) && ($relation['UserRelation']['deleted'] == DELETED_NO));
    }

    public function check_user_relation($user_id, $follow_id)
    {
        $userRelationM = ClassRegistry::init('UserRelation');
        $relation = $userRelationM->find('all', array(
            'conditions' => array(
                'user_id' => $user_id,
                'follow_id' => $follow_id
            )
        ));
        return empty($relation);
    }

    public function delete_relation($sharer_id, $user_id)
    {
        $userRelationM = ClassRegistry::init('UserRelation');
        $userRelationM->updateAll(array('deleted' => DELETED_YES), array('user_id' => $sharer_id, 'follow_id' => $user_id));
    }

    public function save_relation($sharer_id, $user_id, $type = 'Buy')
    {
        $userRelationM = ClassRegistry::init('UserRelation');
        if ($this->check_user_relation($sharer_id, $user_id)) {
            $userRelationM->saveAll(array('user_id' => $sharer_id, 'follow_id' => $user_id, 'type' => $type, 'created' => date('Y-m-d H:i:s')));
        } else {
            $userRelationM->updateAll(array('deleted' => DELETED_NO), array('user_id' => $sharer_id, 'follow_id' => $user_id));
        }
    }

    /**
     * @param $recommend
     * @param $clicker
     * @param $weshare_id
     * @return mixed
     * 保存返利记录
     */
    public function save_rebate_log($recommend, $clicker, $weshare_id)
    {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $history_log = $rebateTrackLogM->find('first', array(
            'conditions' => array(
                'sharer' => $recommend,
                'clicker' => $clicker,
                'order_id' => 0,
                'share_id' => $weshare_id
            )
        ));
        if (!empty($history_log)) {
            return $history_log['RebateTrackLog']['id'];
        }
        $rebate_log = array('sharer' => $recommend, 'share_id' => $weshare_id, 'clicker' => $clicker, 'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'));
        $rebateTrackLogM->save($rebate_log);
        $rebateLogId = $rebateTrackLogM->id;
        return $rebateLogId;
    }

    /**
     * @param $id
     * @param $order
     * @return array()
     * 更新 rebate log
     */
    public function update_rebate_log($id, $order)
    {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $share_id = $order['Order']['member_id'];
        $order_id = $order['Order']['id'];
        $rebatePercentData = $this->get_share_rebate_data($share_id);
        if (!empty($rebatePercentData)) {
            $rebateTrackLog = $rebateTrackLogM->find('first', array(
                'conditions' => array(
                    'id' => $id
                )
            ));
            //proxy user buy
            if ($rebateTrackLog['RebateTrackLog']['type'] == PROXY_USER_PAID_REBATE_TYPE) {
                $rebateTrackLogM->updateAll(array('is_paid' => 1, 'updated' => '\'' . date('Y-m-d H:i:s') . '\''), array('id' => $id, 'order_id' => $order_id));
                return array('rebate_money' => $rebateTrackLog['RebateTrackLog']['rebate_money'], 'order_price' => $order['Order']['total_all_price'], 'recommend' => $rebateTrackLog['RebateTrackLog']['sharer']);
            }
            $ship_fee = $order['Order']['ship_fee'];
            $total_price = $order['Order']['total_all_price'];
            $ship_fee = round($ship_fee / 100, 2);
            $canRebateMoney = $total_price - $ship_fee;
            $percent = $rebatePercentData['ProxyRebatePercent']['percent'];
            $rebate_money = ($canRebateMoney * $percent) / 100;
            $rebate_money = round($rebate_money, 2);
            $rebate_money = $rebate_money * 100;
            $rebateTrackLogM->updateAll(array('is_paid' => 1, 'updated' => '\'' . date('Y-m-d H:i:s') . '\'', 'rebate_money' => $rebate_money), array('id' => $id, 'order_id' => $order_id));
            return array('rebate_money' => $rebate_money, 'order_price' => $total_price, 'recommend' => $rebateTrackLog['RebateTrackLog']['sharer']);
        }

    }

    /**
     * @param $id
     * @param $order_id
     * @param $share_id
     * 用户下单后更新返利日志
     */
    public function update_rebate_log_order_id($id, $order_id, $share_id)
    {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $rebateTrackLogM->updateAll(array('order_id' => $order_id, 'share_id' => $share_id), array('id' => $id));
    }

    /**
     * @param $share_id
     * @return int
     */
    public function get_share_rebate_ship_fee($share_id)
    {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $allRebateMoney = 0;
        $rebateLogs = $rebateTrackLogM->find('all', array(
            'conditions' => array(
                'share_id' => $share_id,
                'type' => GROUP_SHARE_BUY_REBATE_TYPE,
                'not' => array('order_id' => 0, 'is_paid' => 0)
            ),
            'limit' => 1000
        ));
        foreach ($rebateLogs as $log) {
            $allRebateMoney = $allRebateMoney + $log['RebateTrackLog']['rebate_money'];
        }
        $allRebateMoney = $allRebateMoney / 100;
        return $allRebateMoney;
    }

    /**
     * @param $share_id
     * @return int
     */
    public function get_share_rebate_money($share_id)
    {
        if (!is_array($share_id)) {
            $share_id = array($share_id);
        }
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $allRebateMoney = 0;
        $rebateLogs = $rebateTrackLogM->find('all', array(
            'conditions' => array(
                'share_id' => $share_id,
                'not' => array('order_id' => 0, 'is_paid' => 0)
            ),
            'limit' => 1000
        ));
        foreach ($rebateLogs as $log) {
            $allRebateMoney = $allRebateMoney + $log['RebateTrackLog']['rebate_money'];
        }
        $allRebateMoney = $allRebateMoney / 100;
        return $allRebateMoney;
    }

    /**
     * @param $user_id
     * @return int
     * 获取用户 返利的金钱
     */
    public function get_rebate_money($user_id)
    {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $allRebateMoney = 0;
        $rebateLogs = $rebateTrackLogM->find('all', array(
            'conditions' => array(
                'sharer' => $user_id,
                'is_rebate' => 0,
                'not' => array('order_id' => 0, 'is_paid' => 0)
            )
        ));
        foreach ($rebateLogs as $log) {
            $allRebateMoney = $allRebateMoney + $log['RebateTrackLog']['rebate_money'];
        }
        $allRebateMoney = $allRebateMoney / 100;
        return $allRebateMoney;
    }

    /**
     * @param $uid
     * @return bool
     * check user is proxy
     */
    public function is_proxy_user($uid)
    {
        $userM = ClassRegistry::init('User');
        $isProxy = $userM->userIsProxy($uid);
        return $isProxy == USER_IS_PROXY;
    }

    /**
     * @param $share_id
     * 获取分享rebate data
     */
    public function get_share_rebate_data($share_id)
    {
        $proxyRebatePercentM = ClassRegistry::init('ProxyRebatePercent');
        $proxyPercent = $proxyRebatePercentM->find('first', array(
            'conditions' => array(
                'share_id' => $share_id,
                'deleted' => DELETED_NO,
                'status' => PUBLISH_YES
            )
        ));
        return $proxyPercent;
    }

    /**
     * @param $orders
     * @return int
     * cal rebate money
     */
    public function cal_rebate_money($orders)
    {
        $proxyRebatePercentM = ClassRegistry::init('ProxyRebatePercent');
        $rebateMoney = 0;
        $share_ids = Hash::extract($orders, '{n}.Order.member_id');
        $proxyPercents = $proxyRebatePercentM->find('all', array(
            'conditions' => array(
                'share_id' => $share_ids,
                'deleted' => DELETED_NO,
                'status' => PUBLISH_YES
            )
        ));
        $proxyPercents = Hash::combine($proxyPercents, '{n}.ProxyRebatePercent.share_id', '{n}.ProxyRebatePercent');
        foreach ($orders as $order) {
            $member_id = $order['Order']['member_id'];
            $percent = $proxyPercents[$member_id]['percent'];
            if (!empty($percent)) {
                $ship_fee = $order['Order']['ship_fee'];
                $order_total_price = $order['Order']['total_all_price'];
                $rebate_price = $order_total_price - $ship_fee;
                $orderRebateMoney = ($rebate_price * $percent) / 100;
                $rebateMoney = $rebateMoney + $orderRebateMoney;
            }
        }
        return round($rebateMoney, 2);
    }

    public function get_user_rebate_info($user_id)
    {
        $rebate_users = $this->rebate_users();
        return $rebate_users[$user_id];
    }

    /**
     * @param $id
     * @param $order
     * process rebate money
     */
    public function process_order_paid_rebate($id, $order)
    {
        $rebateData = $this->update_rebate_log($id, $order);
        $member_id = $order['Order']['member_id'];
        $weshareInfo = $this->WeshareBuy->get_weshare_info($member_id);
        $order_creator = $order['Order']['creator'];
        $share_creator = $weshareInfo['creator'];
        $recommend = $rebateData['recommend'];
        $user_ids = array($order_creator, $share_creator, $recommend);
        $this->WeshareBuy->subscribe_sharer($recommend, $order_creator, 'RECOMMEND');
        $this->WeshareBuy->subscribe_sharer($share_creator, $order_creator, 'BUY');
        $user_nicknames = $this->WeshareBuy->get_users_nickname($user_ids);
        $recommend_open_ids = $this->WeshareBuy->get_open_ids(array($recommend));
        $title = $user_nicknames[$recommend] . '，' . $user_nicknames[$order_creator] . '购买了你推荐的' . $user_nicknames[$share_creator] . $weshareInfo['title'] . '，获得返利回馈。';
        $detail_url = $this->WeshareBuy->get_weshares_detail_url($member_id);
        $order_id = $order['Order']['id'];
        $order_money = $rebateData['order_price'];
        $rebate_money = $rebateData['rebate_money'];
        $pay_time = $order['Order']['created'];
        $rebate_money = round($rebate_money / 100, 2);
        $rebate_money = number_format($rebate_money, 2);
        //rebate money gt 0 send msg
        if ($rebate_money > 0) {
            $this->Weixin->send_rebate_template_msg($recommend_open_ids[$recommend], $detail_url, $order_id, $order_money, $pay_time, $rebate_money, $title);
        }
    }

    public function read_share_ship_option_setting($sharer, $type)
    {
        $SharerShipOptionM = ClassRegistry::init('SharerShipOption');
        $key = SHARER_CAN_USE_OFFLINE_STORE_CACHE_KEY . '_' . $sharer . '_' . $type;
        $ship_set_type = Cache::read($key);
        if (empty($ship_set_type)) {
            $ship_setting = $SharerShipOptionM->find('first', array(
                'conditions' => array(
                    'sharer_id' => $sharer,
                    'ship_option' => $type
                )
            ));
            if (empty($ship_setting)) {
                return 0;
            }
            $ship_set_type = $ship_setting['SharerShipOption']['status'];
            Cache::write($key, $ship_set_type);
            return $ship_set_type;
        }
        return $ship_set_type;
    }


    /**
     * @param $shareId
     * @param $uid
     * @param $address
     * @param $address_remarks
     * @param $type
     * @param $share_status
     * @return array
     * clone一份， 指定用户ID， 指定的地址， 类型， 状态
     */
    public function cloneShare($shareId, $uid = null, $address = null, $address_remarks = null, $type = DEFAULT_SHARE_TYPE, $share_status = WESHARE_DELETE_STATUS)
    {
        $WeshareM = ClassRegistry::init('Weshare');
        $shareInfo = $WeshareM->find('first', array(
            'conditions' => array(
                'id' => $shareId
            )
        ));
        $shareInfo = $shareInfo['Weshare'];
        $shareInfo['id'] = null;
        $shareInfo['created'] = date('Y-m-d H:i:s');
        $shareInfo['status'] = 0;
        $shareInfo['settlement'] = 0;
        $shareInfo['type'] = 0;
        //order status offline address id
        if ($type == GROUP_SHARE_TYPE) {
            $origin_sharer_nickname = $this->WeshareBuy->get_user_nickname($shareInfo['creator']);
            $shareInfo['title'] = '大家一起拼团' . $origin_sharer_nickname . '分享的' . $shareInfo['title'];
            //default share status is not available
            $shareInfo['status'] = $share_status;
        }
        if (!empty($type)) {
            $shareInfo['type'] = $type;
        }
        //set refer share id
        $shareInfo['refer_share_id'] = $shareId;
        if (!empty($uid)) {
            $shareInfo['creator'] = $uid;
        }
        $uid = $shareInfo['creator'];
        $WeshareM->id = null;
        if ($type == GROUP_SHARE_TYPE) {
            $offlineAddress = $this->saveGroupShareOfflineAddress($address, $uid, $address_remarks);
            $shareInfo['offline_address_id'] = $offlineAddress['WeshareOfflineAddress']['id'];
        }
        $newShareInfo = $WeshareM->save($shareInfo);
        if ($newShareInfo) {
            //clone product
            $newShareId = $newShareInfo['Weshare']['id'];
            $this->cloneShareProduct($newShareId, $shareId);
            if ($type == DEFAULT_SHARE_TYPE) {
                //clone address
                $this->cloneShareAddresses($newShareId, $shareId);
                //clone ship setting
                $this->cloneShareShipSettings($newShareId, $shareId);
                //clone rebate set
                $this->cloneShareRebateSet($newShareId, $shareId);
            }
            if ($type == GROUP_SHARE_TYPE) {
                $this->saveGroupShareAddress($address, $newShareId);
                $this->cloneShareShipSettings($newShareId, $shareId, true);
                $this->cloneShareRebateSet($newShareId, $shareId, true);
            }
            Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $uid, '');
            //when clone share status normal save opt log
            if ($share_status == WESHARE_NORMAL_STATUS) {
                $now = date('Y-m-d H:i:s');
                $shareImg = explode('|', $shareInfo['images']);
                $title = '大家一起拼团' . $origin_sharer_nickname . '分享的' . $shareInfo['title'];
                $optLogData = array('user_id' => $uid, 'obj_type' => OPT_LOG_START_GROUP_SHARE, 'obj_id' => $newShareId, 'created' => $now, 'memo' => $title, 'thumbnail' => $shareImg[0]);
                $this->saveOptLog($optLogData);
            }
            return array('shareId' => $newShareId, 'success' => true);
        }
        return array('success' => false);
    }

    private function saveGroupShareOfflineAddress($address, $uid, $remarks)
    {
        $WeshareOfflineAddressM = ClassRegistry::init('WeshareOfflineAddress');
        $weshareOfflineAddress = array('creator' => $uid, 'address' => $address, 'created' => date('Y-m-d H:i:s'), 'remarks' => $remarks);
        $offlineAddress = $WeshareOfflineAddressM->save($weshareOfflineAddress);
        return $offlineAddress;
    }

    /**
     * @param $address
     * @param $share_id
     * @return mixed
     */
    private function saveGroupShareAddress($address, $share_id)
    {
        $WeshareAddressM = ClassRegistry::init('WeshareAddress');
        $shareAddressData = array('address' => $address, 'weshare_id' => $share_id);
        $WeshareAddressM->save($shareAddressData);
    }

    //todo clone share product
    private function cloneSharProductTag($new_share_id, $old_share_id)
    {

    }

    /**
     * @param $new_share_id
     * @param $old_share_id
     * clone share product
     */
    private function cloneShareProduct($new_share_id, $old_share_id)
    {
        $WeshareProductM = ClassRegistry::init('WeshareProduct');
        $shareProducts = $WeshareProductM->find('all', array(
            'conditions' => array(
                'weshare_id' => $old_share_id
            )
        ));
        $newProducts = array();
        foreach ($shareProducts as $itemShareProduct) {
            $itemShareProduct = $itemShareProduct['WeshareProduct'];
            $itemShareProduct['origin_product_id'] = $itemShareProduct['id'];
            $itemShareProduct['id'] = null;
            $itemShareProduct['weshare_id'] = $new_share_id;
            $newProducts[] = $itemShareProduct;
        }
        $WeshareProductM->id = null;
        $WeshareProductM->saveAll($newProducts);
        return;
    }

    /**
     * @param $new_share_id
     * @param $old_share_id
     * clone share addresses
     */
    private function cloneShareAddresses($new_share_id, $old_share_id)
    {
        $WeshareAddressM = ClassRegistry::init('WeshareAddress');
        $shareAddresses = $WeshareAddressM->find('all', array(
            'conditions' => array(
                'weshare_id' => $old_share_id
            )
        ));
        $newAddresses = array();
        foreach ($shareAddresses as $itemShareAddress) {
            $itemShareAddress = $itemShareAddress['WeshareAddress'];
            $itemShareAddress['id'] = null;
            $itemShareAddress['weshare_id'] = $new_share_id;
            $newAddresses[] = $itemShareAddress;
        }
        $WeshareAddressM->id = null;
        $WeshareAddressM->saveAll($newAddresses);
    }

    /**
     * @param $new_share_id
     * @param $old_share_id
     * @param $is_set_group
     * clone share ship setting
     */
    private function cloneShareShipSettings($new_share_id, $old_share_id, $is_set_group = false)
    {
        $WeshareShipSettingM = ClassRegistry::init('WeshareShipSetting');
        $shareShipSettings = $WeshareShipSettingM->find('all', array(
            'conditions' => array(
                'weshare_id' => $old_share_id
            )
        ));
        $newShareShipSettings = array();
        foreach ($shareShipSettings as $itemShareShipSetting) {
            $itemShareShipSetting = $itemShareShipSetting['WeshareShipSetting'];
            $itemShareShipSetting['id'] = null;
            $itemShareShipSetting['weshare_id'] = $new_share_id;
            $newShareShipSettings[] = $itemShareShipSetting;
        }
        $WeshareShipSettingM->id = null;
        if ($is_set_group) {
            //only set self ziti
            $saveData = null;
            $groupShareLimit = 0;
            foreach ($newShareShipSettings as &$itemNewShareShipSetting) {
                if ($itemNewShareShipSetting['tag'] == SHARE_SHIP_SELF_ZITI_TAG) {
                    $itemNewShareShipSetting['status'] = 1;
                    $itemNewShareShipSetting['ship_fee'] = SHARE_OFFLINE_ADDRESS_SHIP_FEE;
                    $saveData = $itemNewShareShipSetting;
                }
                if ($itemNewShareShipSetting['tag'] == SHARE_SHIP_GROUP_TAG) {
                    $groupShareLimit = $itemNewShareShipSetting['limit'];
                }
            }
            $saveData['limit'] = $groupShareLimit;
            $WeshareShipSettingM->saveAll(array($saveData));
            return;
        } else {
            $WeshareShipSettingM->saveAll($newShareShipSettings);
            return;
        }
    }

    /**
     * @param $new_share_id
     * @param $old_share_id
     * @param $is_set_group
     * clone share rebate set
     */
    private function cloneShareRebateSet($new_share_id, $old_share_id, $is_set_group = false)
    {
        $proxyRebatePercentM = ClassRegistry::init('ProxyRebatePercent');
        $oldShareRebateSet = $proxyRebatePercentM->find('first', array(
            'conditions' => array('share_id' => $old_share_id)
        ));
        if (empty($oldShareRebateSet) || $is_set_group) {
            //子分享返利设置为0
            $proxyRebatePercentM->save(array('share_id' => $new_share_id, 'percent' => 0));
            return;
        }
        if (!empty($oldShareRebateSet)) {
            $newShareRebateSet = $oldShareRebateSet['ProxyRebatePercent'];
            $newShareRebateSet['id'] = null;
            $newShareRebateSet['share_id'] = $new_share_id;
            $proxyRebatePercentM->save($newShareRebateSet);
            return;
        }
    }

    /**
     * @param $shareId
     * @param $userId
     * @param $memo
     */
    public function saveShareRecommendLog($shareId, $userId, $memo)
    {
        $recommendLogM = ClassRegistry::init('RecommendLog');
        $now = date('Y-m-d H:i:s');
        $recommendData = array(
            'data_id' => $shareId,
            'data_type' => RECOMMEND_SHARE,
            'user_id' => $userId,
            'memo' => $memo,
            'created' => $now
        );
        $recommendLogM->save($recommendData);
        $thisUserRecommendCount = $recommendLogM->find('count', array(
            'conditions' => array(
                'data_id' => $shareId,
                'data_type' => RECOMMEND_SHARE,
                'user_id' => $userId
            )
        ));
        //clear recommend cache
        if ($thisUserRecommendCount == 1) {
            Cache::write(SHARE_RECOMMEND_DATA_CACHE_KEY . '_' . $shareId, '');
        }
        $share_info = $this->WeshareBuy->get_weshare_info($shareId);
        $shareImg = explode('|', $share_info['images']);
        $title = $share_info['title'];
        $sharer_name = $this->WeshareBuy->get_user_nickname($share_info['creator']);
        $title = $sharer_name . '分享的' . $title;
        $optLogData = array('user_id' => $userId, 'obj_type' => OPT_LOG_SHARE_RECOMMEND, 'obj_id' => $shareId, 'created' => $now, 'memo' => $title, 'reply_content' => $memo, 'thumbnail' => $shareImg[0]);
        $this->saveOptLog($optLogData);
        $sendResult = $this->WeshareBuy->send_recommend_msg($userId, $shareId, $memo);
        $this->notify_sharer_recommend($userId, $shareId);
        return $sendResult;
    }

    /**
     * @param $recommend
     * @param $shareId
     */
    public function notify_sharer_recommend($recommend, $shareId)
    {
        $share_info = $this->WeshareBuy->get_weshare_info($shareId);
        $share_title = $share_info['title'];
        $sharer = $share_info['creator'];
        $share_open_id = $this->WeshareBuy->get_open_ids(array($sharer));
        $share_open_id = $share_open_id[$sharer];
        $user_nicknames = $this->WeshareBuy->get_users_nickname(array($sharer, $recommend));
        $recommend_name = $user_nicknames[$recommend];
        $title = $recommend_name . '推荐了您分享的' . $share_title;
        $remark = '分享快乐，点击详情，看看' . $recommend_name . '是谁？';
        $detail_url = $this->WeshareBuy->get_sharer_detail_url($recommend);
        $this->Weixin->send_recommend_notify_template_msg($share_open_id, $recommend_name, $title, $remark, $detail_url);
    }

    /**
     * @param $share_id
     * @param $thumbnail
     * @param $memo
     * @param $user_id
     */
    public function save_create_share_opt_log($share_id, $thumbnail, $memo, $user_id)
    {
        $optData = array(
            'user_id' => $user_id,
            'obj_type' => OPT_LOG_CREATE_SHARE,
            'obj_id' => $share_id,
            'created' => date('Y-m-d H:i:s'),
            'memo' => $memo,
            'thumbnail' => $thumbnail
        );
        //黑名单用户不显示 或者 粉丝小于50
        if (is_blacklist_user($user_id) || $this->get_user_level_by_fans_count($user_id) == 0) {
            $optData['deleted'] = DELETED_YES;
        }
        $this->saveOptLog($optData);
    }

    /**
     * @param $uid
     * @return int
     * 根据用户的粉丝数 判断能否出现在信息流中
     */
    public function get_user_level_by_fans_count($uid)
    {
        $userRelationM = ClassRegistry::init('UserRelation');
        $fans_count = $userRelationM->find('count', array(
            'conditions' => array(
                'user_id' => $uid
            )
        ));
        if ($fans_count < 50) {
            return 0;
        }
        if ($fans_count > 50) {
            return 1;
        }
    }

    /**
     * @param $uid
     * @param $type
     * @return array
     * 获取用户等级
     */
    public function get_user_level($uid, $type = 0)
    {
        $key = SHARER_LEVEL_CACHE_KEY . '_' . $uid . '_' . $type;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $userLevelM = ClassRegistry::init('UserLevel');
            $user_level = $userLevelM->find('first', array(
                'conditions' => array(
                    'type' => $type,
                    'data_id' => $uid,
                    'deleted' => DELETED_NO
                ),
                'fields' => array('data_id', 'data_value', 'type')
            ));
            if (empty($user_level)) {
                return null;
            }
            $user_level = $user_level['UserLevel'];
            $level_name = get_user_level_text($user_level['data_value']);
            $user_level['level_name'] = $level_name;
            Cache::write($key, json_encode($user_level));
            return $user_level;
        }
        return json_decode($cacheData, true);
    }

    /**
     * @param $user_ids
     * @return array
     */
    public function get_users_level($user_ids)
    {
        $userLevelM = ClassRegistry::init('UserLevel');
        $levels = $userLevelM->find('all', array(
            'conditions' => array(
                'type' => 0,
                'data_id' => $user_ids,
                'deleted' => DELETED_NO
            ),
            'fields' => array('data_id', 'data_value', 'type')
        ));
        $levels = Hash::combine($levels, '{n}.UserLevel.data_id', '{n}.UserLevel');
        foreach ($levels as &$level_item) {
            $level_item_name = get_user_level_text($level_item['data_value']);
            $level_item['level_name'] = $level_item_name;
        }
        return $levels;
    }

    /**
     * @param $uid
     * 检查用户是否有level ， 没有初始化一个
     */
    public function check_and_save_default_level($uid)
    {
        $userLevelM = ClassRegistry::init('UserLevel');
        $level = $userLevelM->find('first', array(
            'conditions' => array(
                'data_id' => $uid,
                'type' => 0
            )
        ));
        if (empty($level)) {
            $date = date('Y-m-d H:i:s');
            $init_level_data = array(
                'data_id' => $uid,
                'data_value' => 0,
                'type' => 0,
                'created' => $date,
                'updated' => $date,
                'deleted' => DELETED_NO
            );
            $userLevelM->save($init_level_data);
        }
    }

    /**
     * @param $user_id
     * @param $share_id
     * @param $tag_id
     * 保存拼团成功的日志
     */
    public function save_pintuan_success_opt_log($user_id, $share_id, $tag_id)
    {
        $pintuanConfigM = ClassRegistry::init('PintuanConfig');
        $conf_data = $pintuanConfigM->get_conf_data($share_id);
        $memo = $conf_data['share_title'];
        $thumbnail = $conf_data['banner_img'];
        $optData = array(
            'user_id' => $user_id,
            'obj_type' => OPT_LOG_PINTUAN_SUCCESS,
            'obj_id' => $tag_id,
            'created' => date('Y-m-d H:i:s'),
            'memo' => $memo,
            'thumbnail' => $thumbnail
        );
        $this->saveOptLog($optData);
    }

    /**
     * @param $user_id
     * @param $share_id
     * @param $order_id
     * save user buy product opt log
     */
    public function save_buy_opt_log($user_id, $share_id, $order_id)
    {
        $share_info = $this->WeshareBuy->get_weshare_info($share_id);
        $memo = $share_info['title'];
        $sharer_name = $this->WeshareBuy->get_user_nickname($share_info['creator']);
        $memo = $sharer_name . '分享的' . $memo;
        $thumbnail = explode('|', $share_info['images']);
        $thumbnail = $thumbnail[0];
        $order_info = $this->WeshareBuy->get_cart_name_and_num($order_id);
        $order_info = $order_info['cart_name'];
        $optData = array(
            'user_id' => $user_id,
            'obj_type' => OPT_LOG_SHARE_BUY,
            'obj_id' => $share_id,
            'created' => date('Y-m-d H:i:s'),
            'memo' => $memo,
            'reply_content' => $order_info,
            'thumbnail' => $thumbnail
        );
        //me test account don't show
        if ($share_info['creator'] == 802852) {
            $optData['deleted'] = DELETED_YES;
        }
        $this->saveOptLog($optData);
    }

    /**
     * @param $user_id
     * @param $share_id
     * @param $replay_text
     * save comment opt log
     */
    public function save_comment_opt_log($user_id, $share_id, $replay_text)
    {
        $share_info = $this->WeshareBuy->get_weshare_info($share_id);
        $memo = $share_info['title'];
        $sharer_name = $this->WeshareBuy->get_user_nickname($share_info['creator']);
        $memo = $sharer_name . '分享的' . $memo;
        $thumbnail = explode('|', $share_info['images']);
        $thumbnail = $thumbnail[0];
        $optData = array(
            'user_id' => $user_id,
            'obj_type' => OPT_LOG_SHARE_COMMENT,
            'obj_id' => $share_id,
            'created' => date('Y-m-d H:i:s'),
            'memo' => $memo,
            'thumbnail' => $thumbnail,
            'reply_content' => $replay_text
        );
        $this->saveOptLog($optData);
    }

    /**
     * @param $data
     *
     */
    public function saveOptLog($data)
    {
        $optLogM = ClassRegistry::init('OptLog');
        $optLogM->save($data);
        Cache::write(LAST_OPT_LOG_DATA_CACHE_KEY, '');
    }

    /**
     * @param $shareId
     * @return mixed
     * 根据分享获取订单
     */
    public function get_share_orders($shareId)
    {
        $orderM = ClassRegistry::init('Order');
        $share_orders = $orderM->find('all', array(
            'conditions' => array(
                'member_id' => $shareId,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => $this->normal_order_status
            ),
            'fields' => array('id', 'creator', 'total_all_price', 'status')
        ));
        return $share_orders;
    }

    /**
     * @param $shareId
     * @param $refundMark
     * 批量处理订单退款
     */
    public function batch_refund_order($shareId, $refundMark)
    {
        $orders = $this->get_share_orders($shareId);
        foreach ($orders as $order_item) {
            $refundMoney = $order_item['Order']['total_all_price'];
            $order_id = $order_item['Order']['id'];
            $this->refund($order_id, $refundMoney, $refundMark, 0);
        }
    }


    /**
     * @param $orderId
     * @param $refundMoney
     * @param $refundMark
     * @param $refundStatus
     * @return array
     *
     */
    public function refund($orderId, $refundMoney, $refundMark, $refundStatus)
    {
        $userM = ClassRegistry::init('User');
        $weshareM = ClassRegistry::init('Weshare');
        $refundLogM = ClassRegistry::init('RefundLog');
        $payLogM = ClassRegistry::init('PayLog');
        $orderM = ClassRegistry::init('Order');
        $refundMoney = intval($refundMoney * 1000 / 10);
        App::uses('CakeNumber', 'Utility');
        $showRefundMoney = CakeNumber::precision($refundMoney / 100, 2);
        $refundLog = $refundLogM->find('first', array(
            'conditions' => array(
                'order_id' => $orderId
            )
        ));
        if (empty($refundLog)) {
            $PayLogInfo = $payLogM->find('first', array(
                'conditions' => array(
                    'order_id' => $orderId,
                    'status' => 2,
                    'type' => GOOD_ORDER_PAY_TYPE
                )
            ));
            $trade_type = $PayLogInfo['PayLog']['trade_type'];
            if (empty($trade_type)) {
                $trade_type = 'JSAPI';
            }
            $saveRefundLogData = array(
                'order_id' => $orderId,
                'refund_fee' => $refundMoney,
                'created' => date('Y-m-d H:i:s'),
                'trade_type' => $trade_type,
                'remark' => $refundMark
            );
            $refundLogM->save($saveRefundLogData);
        } else {
            $refundLogId = $refundLog['RefundLog']['id'];
            $refundLogM->updateAll(array('refund_fee' => $refundMoney, 'remark' => "'" . $refundMark . "'"), array('id' => $refundLogId));
        }
        $orderInfo = $orderM->find('first', array(
            'conditions' => array('id' => $orderId)
        ));
        $weshareId = $orderInfo['Order']['member_id'];
        //refund processing
        $weshareInfo = $weshareM->find('first', array(
            'conditions' => array('id' => $weshareId)
        ));
        $order_creator_id = $orderInfo['Order']['creator'];
        $order_creator_info = $userM->find('first', array(
            'conditions' => array(
                'User.id' => $order_creator_id
            ),
            'recursive' => 0, //int
            'fields' => array('User.id', 'User.nickname')
        ));
        $weshareTitle = $weshareInfo['Weshare']['title'];
        $remark = '点击查看详情';
        $detail_url = WX_HOST . '/weshares/view/' . $weshareId;
        if ($refundStatus == 0) {
            $orderM->updateAll(array('status' => ORDER_STATUS_RETURNING_MONEY), array('id' => $orderId));
            $title = $order_creator_info['User']['nickname'] . '，你好，我们已经为你申请退款，会在3-5个工作日内完成退款。';
            $this->Weixin->send_refunding_order_notify($order_creator_id, $title, $weshareTitle, $showRefundMoney, $detail_url, $orderId, $remark);
            //如果是拼团订单 退款减去余额
            if ($orderInfo['Order']['ship_mark'] == SHARE_SHIP_GROUP_TAG) {
                $this->remove_money_for_offline_address($weshareId, $order_creator_id, $orderId);
            }
        }
        return array('success' => true, 'order_id' => $orderId);
    }

    /**
     * @param $order
     * check order is repaid and update order status
     */
    public function check_order_is_prepaid_and_update_status($order)
    {
        $order_is_prepaid = $order['Order']['is_prepaid'];
        if ($order_is_prepaid == 1) {
            $order_id = $order['Order']['id'];
            $orderM = ClassRegistry::init('Order');
            $orderM->updateAll(array('status' => ORDER_STATUS_PREPAID), array('id' => $order_id));
        }
    }

    /**
     * @param $tags
     * @param $uid
     * @return array
     *
     * save user share product tag and return
     */
    public function save_tags_return($tags, $uid)
    {
        $shareProductTagM = ClassRegistry::init('WeshareProductTag');
        foreach ($tags as &$tag_item) {
            if (!isset($tag_item['created'])) {
                $tag_item['created'] = date('Y-m-d H:i:s');
            }
            if (!isset($tag_item['user_id'])) {
                $tag_item['user_id'] = $uid;
            }
        }
        $shareProductTagM->saveAll($tags);
        Cache::write(SHARER_TAGS_DATA_CACHE_KEY . '_' . $uid, '');
        $tags = $this->get_tags_list($uid);
        return $tags;
    }

    /**
     * @param $user_id
     * @param $refer_share_id
     * @return array
     * get user tags
     */
    public function get_tags($user_id, $refer_share_id = 0)
    {
        if ($refer_share_id == 0) {
            $tags = $this->load_tags_data($user_id);
        } else {
            $tags = $this->load_tags_by_share($refer_share_id);
        }
        $tags = Hash::combine($tags, '{n}.WeshareProductTag.id', '{n}.WeshareProductTag');
        return $tags;
    }


    /**
     * @param $user_id
     * @return array|mixed
     * get user tags list
     */
    public function get_tags_list($user_id)
    {
        $tags = $this->load_tags_data($user_id);
        $tags = Hash::extract($tags, '{n}.WeshareProductTag');
        return $tags;
    }

    /**
     * @param $order
     * @return bool
     * check is start new order share and reset order member id
     */
    public function check_is_start_new_group_share($order)
    {
        if ($order['Order']['relate_type'] == ORDER_TRIGGER_GROUP_SHARE_TYPE) {
            $order_id = $order['Order']['id'];
            $order_creator = $order['Order']['creator'];
            $order_member_id = $order['Order']['member_id'];
            $orderM = ClassRegistry::init('Order');
            $group_share = $this->get_group_share($order_creator, $order_member_id);
            //重复执行之后可能出现问题，订单的member_id已经修改
            if (!empty($group_share)) {
                $group_share_id = $group_share['id'];
                $orderM->updateAll(array('member_id' => $group_share_id), array('id' => $order_id));
                $this->set_group_share_available($group_share_id);
                //save opt log
                $now = date('Y-m-d H:i:s');
                $shareImg = explode('|', $group_share['images']);
                $title = $group_share['title'];
                $optLogData = array('user_id' => $order_creator, 'obj_type' => OPT_LOG_START_GROUP_SHARE, 'obj_id' => $group_share_id, 'created' => $now, 'memo' => $title, 'thumbnail' => $shareImg[0]);
                $this->saveOptLog($optLogData);
                //send msg
                $this->trigger_send_new_share_msg($group_share_id, $order_creator);
                return $group_share_id;
            }
        }
        return $order['Order']['member_id'];
    }

    /**
     * @param $shareId
     * @return mixed
     * get share refer_share_id
     */
    public function get_share_refer_id($shareId)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $weshare_info = $weshareM->find('first', array(
            'conditions' => array(
                'id' => $shareId
            ),
            'fields' => array('id', 'refer_share_id')
        ));
        return $weshare_info['Weshare']['refer_share_id'];
    }


    /**
     * @param $uid
     * @param $refer_share_id
     * @return mixed
     */
    public function get_group_share($uid, $refer_share_id)
    {
        //发起多次拼团有问题
        $WeshareM = ClassRegistry::init('Weshare');
        $weshare = $WeshareM->find('first', array(
            'conditions' => array(
                'type' => GROUP_SHARE_TYPE,
                'creator' => $uid,
                'refer_share_id' => $refer_share_id
            )
        ));
        return $weshare['Weshare'];
    }

    /**
     * @param $share_id
     * @return array
     * get share offline address detail
     */
    public function get_share_offline_address_detail($share_id)
    {
        $cache_key = SHARE_OFFLINE_ADDRESS_SUMMERY_DATA_CACHE_KEY . '_' . $share_id;
        $json_address_data = Cache::read($cache_key);
        if (empty($json_address_data)) {
            $WeshareM = ClassRegistry::init('Weshare');
            //todo should check share status
            $query_address_sql = 'select * from cake_weshare_addresses where weshare_id in (select id from cake_weshares where refer_share_id=' . $share_id . ' and type=' . GROUP_SHARE_TYPE . ')';
            $address_result = $WeshareM->query($query_address_sql);
            $query_order_summery_sql = 'select count(id),member_id from cake_orders where type=' . ORDER_TYPE_WESHARE_BUY . ' and status !=' . ORDER_STATUS_WAITING_PAY . ' and member_id in (select id from cake_weshares where refer_share_id=' . $share_id . ' and type=' . GROUP_SHARE_TYPE . ') group by member_id';
            $order_summery_result = $WeshareM->query($query_order_summery_sql);
            $address_data = Hash::combine($address_result, '{n}.cake_weshare_addresses.weshare_id', '{n}.cake_weshare_addresses');
            $address_order_summery = Hash::combine($order_summery_result, '{n}.cake_orders.member_id', '{n}.0.count(id)');
            foreach ($address_data as $item_share_id => &$address) {
                $address['order_count'] = $address_order_summery[$item_share_id];
            }
            $json_address_data = json_encode($address_data);
            Cache::write($cache_key, $json_address_data);
            return $address_data;
        }
        return json_decode($json_address_data, true);
    }


    /**
     * @param $share_id
     */
    public function set_group_share_available($share_id)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $weshareM->updateAll(array('status' => WESHARE_NORMAL_STATUS), array('id' => $share_id));
    }

    /**
     * @param $share_id
     * @return mixed
     * 根据分享ID回去商品标签
     * 由于在拼团中，没有复制标签，所以要查找父分享的
     */
    private function load_tags_by_share($share_id)
    {
        $shareInfo = $this->WeshareBuy->get_weshare_info($share_id);
        $shareCreator = $shareInfo['creator'];
        return $this->load_tags_data($shareCreator);
    }

    /**
     * @param $user_id
     * @return mixed
     * cache tags data
     */
    private function load_tags_data($user_id)
    {
        $cache_key = SHARER_TAGS_DATA_CACHE_KEY . '_' . $user_id;
        $cache_data = Cache::read($cache_key);
        if (empty($cache_data)) {
            $shareProductTagM = ClassRegistry::init('WeshareProductTag');
            $tags = $shareProductTagM->find('all', array(
                'conditions' => array(
                    'user_id' => $user_id,
                    'deleted' => DELETED_NO
                )
            ));
            $cache_data = json_encode($tags);
            Cache::write($cache_key, $cache_data);
            return $tags;
        }
        return json_decode($cache_data, true);
    }

    /**
     * @param $weshare_id
     * @return mixed
     * 获取一次分享的分组
     */
    public function get_share_tags($weshare_id)
    {
        //cache it
        $shareProductM = ClassRegistry::init('WeshareProduct');
        $shareProductTagM = ClassRegistry::init('WeshareProductTag');
        $shareProducts = $shareProductM->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id
            )
        ));
        $product_tag_map = Hash::combine($shareProducts, '{n}.WeshareProduct.id', '{n}.WeshareProduct.tag_id');
        $productTagIds = Hash::extract($shareProducts, '{n}.WeshareProduct.tag_id');
        $productTags = $shareProductTagM->find('all', array(
            'conditions' => array(
                'id' => $productTagIds
            )
        ));
        return array('tags' => $productTags, 'product_tag_map' => $product_tag_map);
    }

    /**
     * @param $orderData
     * @param $shareId
     * @return array
     * 分类统计订单
     */
    public function summery_order_data_by_tag($orderData, $shareId)
    {
        $orderCartMap = $orderData['order_cart_map'];
        $orders = $orderData['orders']['origin_orders'];
        $orders = Hash::combine($orders, '{n}.id', '{n}');
        $tagOrderSummery = array();
        $tagOrderIds = array();
        foreach ($orderCartMap as $orderId => $carts) {
            $firstCart = $carts[0];
            $item_tagId = $firstCart['tag_id'];
            if (!isset($tagOrderIds[$item_tagId])) {
                $tagOrderIds[$item_tagId] = array();
            }
            $tagOrderIds[$item_tagId][] = $orderId;
        }
        foreach ($tagOrderIds as $tagId => $orderIds) {
            if (!isset($tagOrderSummery[$tagId])) {
                $tagOrderSummery[$tagId] = array();
            }
            $tagItemTotalPrice = 0;
            foreach ($orderIds as $orderId) {
                $item_order = $orders[$orderId];
                $tagItemTotalPrice = $tagItemTotalPrice + $item_order['total_all_price'];
            }
            $tagRepaidMoney = $this->WeshareBuy->get_group_order_repaid_money($orderIds, $shareId);
            if ($tagRepaidMoney == null) {
                $tagRepaidMoney = 0;
            }
            $tagOrderSummery[$tagId]['total_price'] = $tagItemTotalPrice;
            $tagOrderSummery[$tagId]['buy_count'] = count($orderIds);
            $tagOrderSummery[$tagId]['repaid_money'] = $tagRepaidMoney;
        }
        return $tagOrderSummery;
    }

    /**
     * @param $order
     * 支付尾款
     */
    public function process_paid_order_add($order)
    {
        $order_id = $order['Order']['id'];
        $this->log('order origin parent order  id' . $order['Order']['parent_order_id']);
        $orderM = ClassRegistry::init('Order');
        $this_order = $orderM->find('first', array(
            'conditions' => array(
                'id' => $order_id
            ),
            'fields' => array('id', 'parent_order_id')
        ));
        $parent_order_id = $this_order['Order']['parent_order_id'];
        //process
        $this->log('add order paid ' . $parent_order_id);
        $orderM->updateAll(array('process_prepaid_status' => ORDER_STATUS_PREPAID_DONE), array('id' => $parent_order_id));
    }

    /**
     * @param $weshare_id
     * @return array
     * 获取产品和分组的组合
     */
    public function get_product_tag_map($weshare_id)
    {
        $weshareProductM = ClassRegistry::init('WeshareProduct');
        $weshareProducts = $weshareProductM->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id,
                'deleted' => DELETED_NO
            )
        ));
        $result = array();
        foreach ($weshareProducts as $product) {
            $tag_id = $product['WeshareProduct']['tag_id'];
            if (!isset($result[$tag_id])) {
                $result[$tag_id] = array();
            }
            $result[$tag_id][] = $product['WeshareProduct'];
        }
        return $result;
    }

    /**
     * @param $order_id
     * @param $product_price_map
     * @return int
     */
    public function process_order_prepaid($order_id, $product_price_map)
    {
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $order = $orderM->find('first', array(
            'conditions' => array(
                'id' => $order_id,
                'is_prepaid' => 1,
                'process_prepaid_status' => ORDER_STATUS_PREPAID
            )
        ));
        if (empty($order)) {
            return;
        }
        $order_carts = $cartM->find('all', array(
            'conditions' => array(
                'order_id' => $order_id
            )
        ));
        $temp_order_carts = Hash::combine($order_carts, '{n}.Cart.product_id', '{n}.Cart');
        $cart_item_difference_price = array();
        $total_difference_price = 0;
        foreach ($product_price_map as $pid => $price) {
            $order_cart = $temp_order_carts[$pid];
            $cart_all_price = round($order_cart['num'] * $order_cart['price'] / 100, 2);
            $cart_difference_price = $price - $cart_all_price;
            $total_difference_price = $total_difference_price + $cart_difference_price;
            $cart_item_difference_price[$pid] = array('name' => $order_cart['name'], 'difference_price' => $cart_difference_price, 'product_id' => $pid, 'num' => $order_cart['num'], 'origin_price' => $cart_all_price, 'confirm_price' => $price);
        }
        //gen virtual log order
        if ($total_difference_price != 0) {
            //should add pay order mark
            $savePrice = $total_difference_price * 100;
            $new_order_data = $order['Order'];
            $new_order_data['id'] = null;
            $new_order_data['type'] = ORDER_TYPE_WESHARE_BUY_ADD;
            $new_order_data['parent_order_id'] = $order_id;
            $new_order_data['total_all_price'] = $total_difference_price;
            $new_order_data['total_price'] = $total_difference_price;
            $new_order_data['difference_price'] = $savePrice;
            $new_order_data['process_prepaid_status'] = 0;
            if ($total_difference_price > 0) {
                $new_order_data['status'] = ORDER_STATUS_WAITING_PAY;
            } else {
                $new_order_data['status'] = ORDER_STATUS_REFUND;
            }
            $orderM->id = null;
            $new_order = $orderM->save($new_order_data);
            $new_order_cart_data = array();
            $product_array_map = array();
            foreach ($order_carts as $cart_item) {
                $new_cart = $cart_item['Cart'];
                $product_id = $new_cart['product_id'];
                $product_all_price = $product_price_map[$product_id];
                if ($product_all_price > 0) {
                    $product_num = $new_cart['num'];
                    $product_price = round($product_all_price / $product_num, 2);
                    $product_price = $product_price * 100;
                    $new_cart['price'] = $product_price;
                }
                $new_cart['id'] = null;
                $new_cart['order_id'] = $new_order['Order']['id'];
                $new_order_cart_data[] = $new_cart;
                $product_array_map[] = array(
                    $product_id => array(
                        'name' => $new_cart['name'],
                        'num' => $new_cart['num']
                    )
                );
            }
            $cartM->id = null;
            $cartM->saveAll($new_order_cart_data);
            $orderM->id = null;
            if ($total_difference_price > 0) {
                $orderM->updateAll(array('process_prepaid_status' => ORDER_STATUS_PREPAID_TODO, 'price_difference' => $savePrice), array('id' => $order_id));
            } else {
                $orderM->updateAll(array('process_prepaid_status' => ORDER_STATUS_REFUND_TODO, 'price_difference' => $savePrice), array('id' => $order_id));
            }
            //send msg
            $order_creator = $order['Order']['creator'];
            $weshare_id = $order['Order']['member_id'];
            $share_info = $this->WeshareBuy->get_weshare_info($weshare_id);
            $sharer_id = $share_info['creator'];
            $nicknames = $this->WeshareBuy->get_users_nickname(array($sharer_id, $order_creator));
            $open_ids = $this->WeshareBuy->get_open_ids(array($order_creator));
            $order_creator_open_id = $open_ids[$order_creator];
            $title = $nicknames[$order_creator] . '，你报名' . $nicknames[$sharer_id] . '分享的';
            $product_info_str_array = array();
            foreach ($cart_item_difference_price as $cart_different) {
                $product_info_str_array[] = $cart_different['name'] . 'X' . $cart_different['num'] . '，实际价格是' . $cart_different['confirm_price'] . '，你预付了' . $cart_different['origin_price'];
            }
            $title = $title . implode('、', $product_info_str_array);
            if ($total_difference_price > 0) {
                $title = $title . '，合计你还需要补余款' . $total_difference_price . '元，谢谢你的支持！';
                //to pay
                $detail_url = 'http://www.tongshijia.com/weshares/pay_order_add/' . $new_order['Order']['id'];
            } else {
                $title = $title . '我们将会在3-5个工作日给你退款' . abs($total_difference_price) . '元，谢谢你的支持！';
                $detail_url = $this->WeshareBuy->get_weshares_detail_url($weshare_id);
            }
            $share_mobile = $this->WeshareBuy->get_sharer_mobile($sharer_id);
            $remark = '分享快乐，信任无价，点击支付余款。';
            $this->Weixin->send_remedial_order_msg($order_creator_open_id, $title, $detail_url, abs($total_difference_price), $share_mobile, $remark);
            //clear cache
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $order['Order']['member_id'] . '_1_1', '');
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $order['Order']['member_id'] . '_0_1', '');
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $order['Order']['member_id'] . '_1_0', '');
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $order['Order']['member_id'] . '_0_0', '');
            return $total_difference_price;
        }
        return 0;
    }

    // check split order by tag
    /**
     * @param $order
     * 拆分订单根据分组
     */
    public function split_order_by_tag($order)
    {
        // check cal ship fee
        // check cal red packet fee
        // check is prepaid
        // check cal proxy fee
        // check cal refund money (confirm)
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $order_id = $order['Order']['id'];
        $carts = $cartM->find('all', array(
            'conditions' => array(
                'order_id' => $order_id
            )
        ));
        $cart_tag_ids = Hash::extract($carts, '{n}.Cart.tag_id');
        $cart_tag_ids = array_unique($cart_tag_ids);
        if (count($cart_tag_ids) <= 1) {
            return;
        }
        $tag_cart_map = array();
        foreach ($carts as $cart) {
            $tag_id = $cart['Cart']['tag_id'];
            if (!isset($tag_cart_map[$tag_id])) {
                $tag_cart_map[$tag_id] = array('carts' => array(), 'total_price' => 0);
            }
            $tag_cart_map[$tag_id]['carts'][] = $cart['Cart'];
            $cart_price = $cart['Cart']['num'] * $cart['Cart']['price'] / 100;
            $tag_cart_map[$tag_id]['total_price'] = $tag_cart_map[$tag_id]['total_price'] + $cart_price;
        }
        $origin_order_info = $orderM->find('first', array('conditions' => array('id' => $order_id)));
        $result_carts = array();
        $is_set_ship_fee = false;
        $is_set_coupon = false;
        foreach ($tag_cart_map as $tag => $tag_carts) {
            $orderM->id = null;
            $temp_order_price = $tag_carts['total_price'];
            $temp_order_info = $origin_order_info['Order'];
            $temp_order_info['id'] = null;
            $order_prepaid_result = $this->check_cart_confirm_price($tag_carts['carts']);
            //set order is repaid
            if ($order_prepaid_result == 0) {
                $temp_order_info['is_prepaid'] = 0;
                $temp_order_info['process_prepaid_status'] = 0;
            } else {
                $temp_order_info['is_prepaid'] = 1;
                $temp_order_info['process_prepaid_status'] = ORDER_STATUS_PREPAID;
            }
            $temp_order_info['parent_order_id'] = $order_id;
            $temp_order_info['total_price'] = $temp_order_price;
            $temp_order_info['total_all_price'] = $temp_order_price;
            //set ship fee to first order
            if (!$is_set_ship_fee) {
                $is_set_ship_fee = true;
                $ship_fee = round($temp_order_info['ship_fee'] / 100, 2);
                $temp_order_info['total_all_price'] = $temp_order_price + $ship_fee;
            } else {
                $temp_order_info['ship_fee'] = 0;
            }
            //set coupon for first order
            if (!$is_set_coupon) {
                $is_set_coupon = true;
                $coupon_money = round($temp_order_info['coupon_total'] / 100, 2);
                $temp_order_info['total_all_price'] = $temp_order_info['total_all_price'] - $coupon_money;
            } else {
                $temp_order_info['coupon_total'] = 0;
            }
            $temp_order_info = $orderM->save($temp_order_info);
            $new_order_id = $temp_order_info['Order']['id'];
            $tag_carts = $tag_carts['carts'];
            foreach ($tag_carts as &$item_cart) {
                $item_cart['order_id'] = $new_order_id;
                $item_cart['id'] = null;
                $result_carts[] = $item_cart;
            }
        }
        $cartM->saveAll($result_carts);
        $orderM->updateAll(array('type' => ORDER_TYPE_SPLIT), array('id' => $order_id));
    }

    private function check_cart_confirm_price($tag_carts)
    {
        $result = 0;
        foreach ($tag_carts as $item) {
            if ($item['confirm_price'] == 0) {
                $result = 1;
                break;
            }
        }
        return $result;
    }

    /**
     * @param $share_id
     * @param $order
     * 把每单5元的自提费用添加的线下自提点用户余额里面
     */
    public function add_money_for_offline_address($share_id, $order)
    {
        $order_creator = $order['Order']['creator'];
        $order_id = $order['Order']['id'];
        $WeshareM = ClassRegistry::init('Weshare');
        $weshare = $WeshareM->find('first', array(
            'conditions' => array(
                'id' => $share_id,
                'type' => GROUP_SHARE_TYPE
            )
        ));
        if (!empty($weshare)) {
            $share_creator = $weshare['Weshare']['creator'];
            $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
            $rebate_log = array('sharer' => $share_creator, 'share_id' => $share_id, 'clicker' => $order_creator, 'order_id' => $order_id, 'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'), 'rebate_money' => SHARE_GROUP_REBATE_MONEY, 'is_paid' => 1, 'type' => GROUP_SHARE_BUY_REBATE_TYPE);
            $rebateTrackLogM->save($rebate_log);
            $order_username = $this->WeshareBuy->get_user_nickname($order_creator);
            $user_open_id = $this->WeshareBuy->get_open_id($share_creator);
            $detail_url = $this->WeshareBuy->get_weshares_detail_url($share_id);
            $title = $order_username . '参加了，你发起的' . $weshare['Weshare']['title'];
            $this->Weixin->send_rebate_template_msg($user_open_id, $detail_url, $order_id, $order['Order']['total_all_price'], $order['Order']['pay_time'], SHARE_GROUP_REBATE_MONEY, $title);
            $ret = $this->RedisQueue->add_tasks('tasks', "/task/notify_group_share_complete/" . $share_id);
            $this->log('notify share complete ' . $ret);

        }
    }

    /**
     * @param $share_id
     * @param $order_creator
     * @param $order_id
     * 退款后每单5元自提费用减去
     */
    public function remove_money_for_offline_address($share_id, $order_creator, $order_id)
    {
        $WeshareM = ClassRegistry::init('Weshare');
        $weshare = $WeshareM->find('first', array(
            'conditions' => array(
                'id' => $share_id,
                'type' => GROUP_SHARE_TYPE
            )
        ));
        if (!empty($weshare)) {
            //update is paid
            $share_creator = $weshare['Weshare']['creator'];
            $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
            $rebateTrackLogM->updateAll(array('is_paid' => 0), array('sharer' => $share_creator, 'share_id' => $share_id, 'clicker' => $order_creator, 'order_id' => $order_id, 'is_paid' => 1, 'type' => GROUP_SHARE_BUY_REBATE_TYPE));
        }
    }

    /**
     * @return mixed
     * 获取最新的子分享，用来推送模板消息
     */
    public function get_recent_group_share()
    {
        $WeshareM = ClassRegistry::init('Weshare');
        $shares = $WeshareM->find('all', array(
            'conditions' => array(
                'type' => GROUP_SHARE_TYPE,
                'status' => WESHARE_NORMAL_STATUS
            ),
            'order' => array('id DESC'),
            'limit' => 500
        ));
        return $shares;
    }

    /**
     * @param $share_id
     * 获取分享拼团需要人数
     */
    public function get_share_group_limit($share_id)
    {
        $shipSettingM = ClassRegistry::init('WeshareShipSetting');
        $groupShareShipSettings = $shipSettingM->find('first', array(
            'conditions' => array(
                'weshare_id' => $share_id,
                'tag' => SHARE_SHIP_GROUP_TAG,
                'status' => PUBLISH_YES
            )
        ));
        return $groupShareShipSettings['WeshareShipSetting']['limit'];
    }

    /**
     * 拼团成功通知
     */
    public function send_group_share_complete($share_id)
    {
        $share_info = $this->WeshareBuy->get_weshare_info($share_id);
        $share_orders = $this->get_share_orders($share_id);
        $group_share_limit = $this->get_share_group_limit($share_info['refer_share_id']);
        if (count($share_orders) >= $group_share_limit) {
            $share_order_user_ids = Hash::extract($share_orders, '{n}.Order.creator');
            $share_order_user_ids[] = $share_info['creator'];
            $share_order_user_ids = array_unique($share_order_user_ids);
            $share_title = $share_info['title'];
            $user_open_ids = $this->WeshareBuy->get_open_ids($share_order_user_ids);
            $user_nicknames = $this->WeshareBuy->get_users_nickname($share_order_user_ids);
            $tuan_leader_name = $user_nicknames[$share_info['creator']];
            $detail_url = $this->WeshareBuy->get_weshares_detail_url($share_id);
            $title = '你好，您报名的' . $share_title . '，现在已经成团。吼，吼！';
            $remark = '发货信息：' . $share_info['send_info'] . '请留意后续消息！';
            foreach ($user_open_ids as $user_id => $user_open_id) {
                $this->Weixin->send_share_buy_complete_msg($user_open_id, $title, $share_title, $tuan_leader_name, $remark, $detail_url);
            }
        }
    }

    /**
     * @return mixed
     * 获取常用自提点
     */
    public function get_static_offline_address()
    {
        $WeshareOfflineAddressM = ClassRegistry::init('WeshareOfflineAddress');
        $staticOfflineAddress = $WeshareOfflineAddressM->find('all', array(
            'conditions' => array(
                'static' => 1,
                'deleted' => DELETED_NO
            ),
            'limit' => 100,
            'order' => array('weight DESC')
        ));
        return $staticOfflineAddress;
    }

    /**
     * @param $origin_share_id
     * 一次分享建成之后 触发建立以常用自提点为地址的分享
     */
    public function new_static_address_group_shares($origin_share_id)
    {
        $static_addresses = $this->get_static_offline_address();
        //批量添加任务
        $tasks = array();
        foreach ($static_addresses as $static_address) {
            $address = $static_address['WeshareOfflineAddress']['address'];
            $addressRemark = $static_address['WeshareOfflineAddress']['remarks'];
            $addressCreator = $static_address['WeshareOfflineAddress']['creator'];
            $url = "/task/process_start_group_share/" . $origin_share_id . "/" . $addressCreator;
            $params = "address=" . $address . "&business_remark=" . $addressRemark;
            $tasks[] = array('url' => $url, "postdata" => $params);
        }
        $ret = $this->RedisQueue->add_tasks('share', $tasks);
        return $ret;
    }

    /**
     * @param $weshareData
     * 级联更新数据
     */
    public function cascadeSaveShareData($weshareData)
    {
        $shareId = $weshareData['id'];
        if (!empty($shareId)) {
            $weshareM = ClassRegistry::init('Weshare');
            $childShares = $weshareM->find('all', array(
                'conditions' => array(
                    'refer_share_id' => $shareId,
                    'type' => GROUP_SHARE_TYPE
                )
            ));
            $childShareIds = Hash::extract($childShares, '{n}.Weshare.id');
            //update child share data
            unset($weshareData['id']);
            $weshareM->updateAll($weshareData, array('id' => $childShareIds));
        }
    }

    /**
     * @param $uid
     * 把用户关注分享者的原因使用掉
     */
    public function usedUserSubSharerReason($uid)
    {
        $SubReasonM = ClassRegistry::init('UserSubReason');
        $SubReasonM->updateAll(array('used' => 1), array('user_id' => $uid, 'type' => array(SUB_SHARER_REASON_TYPE_FROM_USER_CENTER, SUB_SHARER_REASON_TYPE_FROM_SHARE_INFO)));
    }

    /**
     * @param $data
     * 保存团长发送消息的日志
     */
    public function saveSendMsgLog($data)
    {
        $sendMsgLogM = ClassRegistry::init('SendMsgLog');
        $sendMsgLogM->save($data);
    }

    /**
     * @param $uid
     * @return array
     * 检查团长是否可以发送消息
     */
    public function checkCanSendMsg($uid)
    {
        if($uid==633345||$uid==802852){
            return array('success' => true, 'msg' => '还可以发送很多条消息');
        }
        $limit_count = $this->getSharerMsgLimit($uid);
        $limit_count = $limit_count['limit'];
        if ($limit_count == 0) {
            return array('success' => false, 'msg' => '团长才能发送模板消息');
        }
        $sendMsgLogM = ClassRegistry::init('SendMsgLog');
        $sendMsgCount = $sendMsgLogM->find('count', array(
            'conditions' => array(
                'sharer_id' => $uid,
                'status' => 1,
                'deleted' => DELETED_NO,
                'DATE(created)' => date('Y-m-d')
            )
        ));
        if ($sendMsgCount >= $limit_count) {
            return array('success' => false, 'msg' => '每天限发' . $limit_count . '条消息');
        }
        return array('success' => true, 'msg' => '还可以发送' . ($limit_count - $sendMsgCount) . '条消息');
    }

    /**
     * @param $uid
     * 获取对应级别用户发送消息的限制
     */
    public function getSharerMsgLimit($uid)
    {
        $userLevelM = ClassRegistry::init('UserLevel');
        $userLevel = $userLevelM->find('first', array(
            'conditions' => array(
                'type' => 0,
                'data_id' => $uid,
            )
        ));
        $user_val = 0;
        if (!empty($userLevel)) {
            $user_val = $userLevel['UserLevel']['data_value'];
        }
        return get_user_level_msg_count($user_val);
    }


    public function get_index_product($tag_id){
        $key = INDEX_VIEW_PRODUCT_CACHE_KEY.'_'.$tag_id;
        $cache_data = Cache::read($key);
        if(empty($cache_data)){
            $indexProductM = ClassRegistry::init('IndexProduct');
            $index_products = $indexProductM->find('all', array(
                'conditions' => array(
                    'tag_id' => $tag_id,
                    'deleted' => DELETED_NO,
                ),
                'order' => array('sort_val ASC')
            ));
            $result = array();
            foreach($index_products as $product){
                $result[] = $product['IndexProduct'];
            }
            Cache::write($key, json_encode($result));
            return $result;
        }

        return json_decode($cache_data, true);

    }

    /**
     * @param $tag
     * @return array
     * index product
     */
    public function get_share_index_product($tag)
    {
        $product = array(
            0 => array(
                '1305' => array(
                    'share_id' => 1305,
                    'share_name' => '新一季的芝麻家诚实的草莓来啦',
                    'share_img' => 'http://static.tongshijia.com/images/bf0f1990-c4e0-11e5-a821-00163e1600b6.jpg',
                    'share_price' => '238',
                    'share_user_name' => '小芝麻',
                    'share_vote' => 2687,
                    'share_user_id' => 710617,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/7106171449285451.png'
                ),
                '2005' => array(
                    'share_id' => 2005,
                    'share_name' => '有机纯正红薯粉条 无任何添加剂',
                    'share_img' => 'http://static.tongshijia.com/images/bf0f45a0-c4e0-11e5-a821-00163e1600b6.jpg',
                    'share_price' => '80',
                    'share_user_name' => '片片妈',
                    'share_vote' => 2568,
                    'share_user_id' => 878825,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_52d08a7c8bb6e9611307b6c4858ca38c.jpg'
                ),
                '2131' => array(
                    'share_id' => 2131,
                    'share_name' => '巨好吃的鲜8纯芝麻酱【全国包邮】',
                    'share_img' => 'http://static.tongshijia.com/images/bf0f2e3a-c4e0-11e5-a821-00163e1600b6.jpg',
                    'share_price' => '29',
                    'share_user_name' => '平凡的世界',
                    'share_vote' => 1358,
                    'share_user_id' => 801447,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/8014471448421852.png'
                ),
                '2178' => array(
                    'share_id' => 2178,
                    'share_name' => '年货精选之群鱼荟萃',
                    'share_img' => 'http://static.tongshijia.com/images/bf0ef820-c4e0-11e5-a821-00163e1600b6.jpg',
                    'share_price' => '47',
                    'share_user_name' => '小宝妈',
                    'share_vote' => 2135,
                    'share_user_id' => 811917,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
                ),
                '2135' => array(
                    'share_id' => 2135,
                    'share_name' => '年货之群虾荟萃',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201601/thumb_m/63858bc47e3_0118.jpg',
                    'share_price' => '125',
                    'share_user_name' => '小宝妈',
                    'share_vote' => 2135,
                    'share_user_id' => 811917,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
                ),
                '2189' => array(
                    'share_id' => 2189,
                    'share_name' => '就想那一口旧时酥糖--真味花生酥',
                    'share_img' => 'http://static.tongshijia.com/images/bf0f5ae0-c4e0-11e5-a821-00163e1600b6.jpg',
                    'share_price' => '36',
                    'share_user_name' => '郭佳',
                    'share_vote' => 352,
                    'share_user_id' => 849084,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/8490841445849325.png'
                ),
                //紫皮糖
                '1915' => array(
                    'share_id' => 1915,
                    'share_name' => '俄罗斯经典紫皮糖、鲜奶威化和酸奶威化',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/9d333807aad_1110.jpg',
                    'share_price' => '70',
                    'share_user_name' => '微儿',
                    'share_vote' => 2000,
                    'share_user_id' => 23771,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_d742f4391e472ca6a24c58d96be17aca.jpg'
                ),

                '1981' => array(
                    'share_id' => 1981,
                    'share_name' => '农家土猪自制腊肠----肠常想念，才下舌尖，又上心间',
                    'share_img' => 'http://static.tongshijia.com/images/78014244-b9e1-11e5-a8c5-00163e001f59.jpg ',
                    'share_price' => '58',
                    'share_user_name' => '鲲鲲',
                    'share_vote' => 7568,
                    'share_user_id' => 806889,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg',
                ),
                '2053' => array(
                    'share_id' => 2053,
                    'share_name' => '三代传承传统手工技艺-零添加、回购率超高的广东腊肠',
                    'share_img' => 'http://static.tongshijia.com/images/fd66a6f2-bddf-11e5-b1f0-00163e1600b6.jpg',
                    'share_price' => '70',
                    'share_user_name' => '三朵',
                    'share_vote' => 7568,
                    'share_user_id' => 882638,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/8826381448681480.png',
                ),
                '669' => array(
                    'share_id' => 669,
                    'share_name' => '忆味蕾，富平霜降柿饼重磅归来！',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/57a0c994217_1229.jpg',
                    'share_price' => '28',
                    'share_user_name' => 'glcfarm',
                    'share_vote' => 1000,
                    'share_user_id' => 433224,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_e370e2e71308d0c78c1f22ff4f6aabca.jpg'
                ),
                '1500' => array(
                    'share_id' => 1500,
                    'share_name' => '小火团贡玉米 甜糯口感 浓郁的玉米原香',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/0129a52fd3d_1209.jpg',
                    'share_price' => '68',
                    'share_user_name' => '杨晓光',
                    'share_vote' => 2000,
                    'share_user_id' => 141,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_f88cfd957b22b112058e340d508423a7.jpg'
                ),
                '2033' => array(
                    'share_id' => 2033,
                    'share_name' => '追团甘甜多汁澳洲空运车厘子',
                    'share_img' => 'http://static.tongshijia.com/images/ed34c504-b9e1-11e5-a8c5-00163e001f59.jpg',
                    'share_price' => '385',
                    'share_user_name' => '小宝妈',
                    'share_vote' => 1024,
                    'share_user_id' => 811917,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
                ),
                '2056' => array(
                    'share_id' => 2056,
                    'share_name' => '年习喜·年货礼盒-----味道是想念最好的慰藉',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201601/thumb_m/2622d548862_0115.jpg',
                    'share_price' => '318',
                    'share_user_name' => '鲲鲲',
                    'share_vote' => 656,
                    'share_user_id' => 806889,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg',
                ),

                '1929' => array(
                    'share_id' => 1929,
                    'share_name' => '用一年的时光等待一颗冬笋----来自山林的味道，只有一季，你舍得错过吗？',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201601/thumb_m/6564d6d8a60_0108.jpg',
                    'share_price' => '56',
                    'share_user_name' => '鲲鲲',
                    'share_vote' => 2300,
                    'share_user_id' => 806889,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg',
                ),
                '1900' => array(
                    'share_id' => 1900,
                    'share_name' => '精品天水花牛苹果 宝宝和老人特别喜欢',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201601/thumb_m/67ea4a46011_0107.jpg',
                    'share_price' => '68',
                    'share_user_name' => '杨晓光',
                    'share_vote' => 800,
                    'share_user_id' => 141,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_f88cfd957b22b112058e340d508423a7.jpg'
                ),

                '1877' => array(
                    'share_id' => 1877,
                    'share_name' => '陕西眉县黄心巨无霸黄金果猕猴桃6个装',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201601/thumb_m/fa93e7d2783_0104.jpg',
                    'share_price' => '29.9',
                    'share_user_name' => '张慧敏',
                    'share_vote' => 654,
                    'share_user_id' => 23711,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/4c91fb9380d_0901.jpg'
                ),
                '747' => array(
                    'share_id' => 747,
                    'share_name' => '那那家五常稻花香米',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/dafe5e63fc8_1027.jpg',
                    'share_price' => '166',
                    'share_user_name' => '那那',
                    'share_vote' => 600,
                    'share_user_id' => 812111,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_db53c030cbe19145428f0d5ca58b9562.jpg'
                ),
                //银耳
                '1834' => array(
                    'share_id' => 1834,
                    'share_name' => '鲜活银耳@"防霾佳品"【顺丰包邮】',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201601/thumb_m/268f5b0354f_0111.jpg',
                    'share_price' => '108',
                    'share_user_name' => '片片妈',
                    'share_vote' => 600,
                    'share_user_id' => 878825,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_52d08a7c8bb6e9611307b6c4858ca38c.jpg'
                ),
                '1498' => array(
                    'share_id' => 1498,
                    'share_name' => '不含添加剂的薯条',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/852e6870f74_1224.jpg',
                    'share_price' => '11.8',
                    'share_user_name' => '海煜',
                    'share_vote' => 500,
                    'share_user_id' => 883095,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_173d81362e0b516ec73870fdf83c8a26.jpg'
                ),
//                '1875' => array(
//                    'share_id' => 1875,
//                    'share_name' => '好吃的真空低温油浴果蔬套装（黄秋葵+香菇+什锦果蔬）',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/d50b8e8d6ad_1202.jpg',
//                    'share_price' => '55',
//                    'share_user_name' => '赵宇',
//                    'share_vote' => 2000,
//                    'share_user_id' => 810688,
//                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_7db41874b00ec17af715b72adf87768a.jpg'
//                ),
                '1906' => array(
                    'share_id' => 1906,
                    'share_name' => '老舅家的沙窝萝卜--脆绿嫩',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/8d917876cee_1216.jpg',
                    'share_price' => '27',
                    'share_user_name' => '平凡的世界',
                    'share_vote' => 1000,
                    'share_user_id' => 801447,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/8014471448421852.png'
                ),
                '1529' => array(
                    'share_id' => 1529,
                    'share_name' => '烟台干货海带 补充各种维生素',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/e96c63a7651_1218.jpg',
                    'share_price' => '46',
                    'share_user_name' => '南彩',
                    'share_vote' => 1000,
                    'share_user_id' => 886905,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_eb6105d6e125a8e05a94b6f7608642db.jpg'
                ),
//                '1758' => array(
//                    'share_id' => 1758,
//                    'share_name' => '“万岁子”、“长寿果”--新疆阿克苏核桃',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/592d0fecd5c_1225.jpg',
//                    'share_price' => '24.99',
//                    'share_user_name' => '西域美农',
//                    'share_vote' => 600,
//                    'share_user_id' => 897,
//                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201501/thumb_m/fd70e550e19_0122.jpg'
//                ),
                '2041' => array(
                    'share_id' => 2041,
                    'share_name' => '云南乌骨鸡礼盒 新年好礼 1000g 顺丰包邮',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201601/thumb_m/3801c445b40_0118.jpg',
                    'share_price' => '98',
                    'share_user_name' => '小牛村 南彩',
                    'share_vote' => 248,
                    'share_user_id' => 886905,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_eb6105d6e125a8e05a94b6f7608642db.jpg'
                ),
            ),
            1 => array(
                '699' => array(
                    'share_id' => 699,
                    'share_name' => '【劲爆特价】陕西眉县农家自产徐香猕猴桃家庭装包邮',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/6573df95802_0928.jpg',
                    'share_price' => '25',
                    'share_user_name' => '张慧敏',
                    'share_vote' => 654,
                    'share_user_id' => 23711,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/4c91fb9380d_0901.jpg'
                ),
                '1840' => array(
                    'share_id' => 1840,
                    'share_name' => '富硒砂糖橘真的来啦',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/5c234cd3f0d_1223.jpg',
                    'share_price' => '88',
                    'share_user_name' => '盛夏',
                    'share_vote' => 1435,
                    'share_user_id' => 708029,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
                ),
                '2187' => array(
                    'share_id' => 2187,
                    'share_name' => '德庆贡柑熟了，想找找当皇帝娘娘的感觉吗？',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/3c397b7406b_1125.jpg',
                    'share_price' => '46',
                    'share_user_name' => '李明',
                    'share_vote' => 999,
                    'share_user_id' => 6783,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_0e8ff635498de280dd3193826d837ee5.jpg'
                ),
                //山药
                '2168' => array(
                    'share_id' => 2168,
                    'share_name' => '艳艳山药，自食&送礼，各取所需',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/9865c6196ae_1110.jpg',
                    'share_price' => '85',
                    'share_user_name' => '艳艳',
                    'share_vote' => 800,
                    'share_user_id' => 12376,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/123761447121370.png'
                ),
                '1530' => array(
                    'share_id' => 1530,
                    'share_name' => '烟台长岛金钩海米 天然晾晒 原味干虾米 味鲜劲道',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/02e7ae6f62c_1218.jpg',
                    'share_price' => '47',
                    'share_user_name' => '南彩',
                    'share_vote' => 1000,
                    'share_user_id' => 886905,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_eb6105d6e125a8e05a94b6f7608642db.jpg'
                ),
                '1744' => array(
                    'share_id' => 1744,
                    'share_name' => '正宗韩国辣白菜',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/c68c07c4022_0915.jpg',
                    'share_price' => '35',
                    'share_user_name' => '土豆',
                    'share_vote' => 95,
                    'share_user_id' => 712908,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/885789f1d97_0915.jpg'
                ),
                '969' => array(
                    'share_id' => 969,
                    'share_name' => '姑妈家的桂圆肉',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/54fb984e461_1015.jpg',
                    'share_price' => '38',
                    'share_user_name' => 'Amy',
                    'share_vote' => 123,
                    'share_user_id' => 810719,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/5421fc6174d_0911.jpg'
                ),
                '419' => array(
                    'share_id' => 419,
                    'share_name' => '山西古县新核桃',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/f6a605eebcb_0915.jpg',
                    'share_price' => '16.5',
                    'share_user_name' => '苏打饼干',
                    'share_vote' => 230,
                    'share_user_id' => 9228,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_a17d0cc7ba98f4ccc8207b225f4fa549.jpg'
                ),
                '798' => array(
                    'share_id' => 798,
                    'share_name' => '沱沱工社有机蔬菜限量团购',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/c47ca5d399d_0914.jpg',
                    'share_price' => '12',
                    'share_user_name' => '沱沱工社',
                    'share_vote' => 315,
                    'share_user_id' => 867587,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/5e530f3a08e_0914.jpg'
                ),
            ),
            2 => array(
                '1923' => array(
                    'share_id' => 1923,
                    'share_name' => '天然、无添加、低胆固醇的抗癌之王—乌鸡蛋',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/a8f7f789c13_1231.jpg',
                    'share_price' => '60',
                    'share_user_name' => '三朵',
                    'share_vote' => 1000,
                    'share_user_id' => 882638,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/8826381448681480.png'
                ),
                '2209' => array(
                    'share_id' => 2209,
                    'share_name' => '越南黑虎虾仁 【纯野生虾仁】',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/cc44309c109_1202.jpg',
                    'share_price' => '150',
                    'share_user_name' => '小宝妈',
                    'share_vote' => 1000,
                    'share_user_id' => 811917,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
                ),
                '503' => array(
                    'share_id' => 503,
                    'share_name' => '旱地杂粮之清水河小香米',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/302470b0709_0915.jpg',
                    'share_price' => '6.18',
                    'share_user_name' => 'Mr.White',
                    'share_vote' => 187,
                    'share_user_id' => 832279,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_624ed608a37ca10baec387ffb2d1fd89.jpg'
                ),
                '424' => array(
                    'share_id' => 424,
                    'share_name' => '东北野生臻蘑（2015年刚采摘的哦）',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/22e7b36d290_0912.jpg',
                    'share_price' => '48',
                    'share_user_name' => '那那',
                    'share_vote' => 165,
                    'share_user_id' => 812111,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_db53c030cbe19145428f0d5ca58b9562.jpg'
                ),
                '330' => array(
                    'share_id' => 330,
                    'share_name' => '怀柔散养老杨家黑猪肉',
                    'share_img' => '/img/share_index/zhurou.jpg',
                    'share_price' => '26.6-93.1',
                    'share_user_name' => '朋友说小妹',
                    'share_vote' => 203,
                    'share_user_id' => 711503,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
                ),
                '1777' => array(
                    'share_id' => 1777,
                    'share_name' => '积累了7000位老顾客的土鸡蛋 朋友说特供40枚/60元',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/15157459186_1110.jpg',
                    'share_price' => '60',
                    'share_user_name' => '小赵-水源绿色食品',
                    'share_vote' => 203,
                    'share_user_id' => 876460,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_deb52342ad0b28deb859ef2b01835ca8.jpg'
                ),
                '329' => array(
                    'share_id' => 329,
                    'share_name' => '天福号山地散养油鸡(限五环内)',
                    'share_img' => '/img/share_index/jirou.jpg',
                    'share_price' => '118',
                    'share_user_name' => '朋友说小妹',
                    'share_vote' => 203,
                    'share_user_id' => 711503,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
                ),
            ),
            3 => array(
                '327' => array(
                    'share_id' => 327,
                    'share_name' => '纯天然0添加雾岭山山楂条',
                    'share_img' => '/img/share_index/shangzhatiao.jpg',
                    'share_price' => '12.8',
                    'share_user_name' => '朋友说小妹',
                    'share_vote' => 123,
                    'share_user_id' => 711503,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
                ),
                '1647' => array(
                    'share_id' => 1647,
                    'share_name' => '正宗妈妈味·湘西剁椒----辣些年',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/db34c2a011e_1224.jpg',
                    'share_price' => '32',
                    'share_user_name' => '鲲鲲',
                    'share_vote' => 2654,
                    'share_user_id' => 806889,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
                ),
                '1563' => array(
                    'share_id' => 1563,
                    'share_name' => '宁夏头茬枸杞王，不负你的信赖！',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/6ada3790272_1224.jpg',
                    'share_price' => '70',
                    'share_user_name' => '鲲鲲',
                    'share_vote' => 1562,
                    'share_user_id' => 806889,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
                ),
                '1552' => array(
                    'share_id' => 1552,
                    'share_name' => '云南哀牢山古法手工叶子红糖',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/abcb4094d25_0917.jpg',
                    'share_price' => '38',
                    'share_user_name' => '鲲鲲',
                    'share_vote' => 130,
                    'share_user_id' => 806889,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
                ),
                '1646' => array(
                    'share_id' => 1646,
                    'share_name' => '鼎力推荐！云南天然蜜酿玫瑰，有买有赠，两瓶包邮！',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/7ff87aa3340_1027.jpg',
                    'share_price' => '46',
                    'share_user_name' => '鲲鲲',
                    'share_vote' => 560,
                    'share_user_id' => 806889,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
                ),
                '1566' => array(
                    'share_id' => 1566,
                    'share_name' => '油淋鸡枞菌----邂逅山珍美味的快感',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/41bb355613e_1217.jpg',
                    'share_price' => '48',
                    'share_user_name' => '鲲鲲',
                    'share_vote' => 2000,
                    'share_user_id' => 806889,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
                ),
                //泉林的纸
                '1977' => array(
                    'share_id' => 1977,
                    'share_name' => '福建尤溪红糯米酒（俗称黄酒、月子酒）',
                    'share_img' => 'http://static.tongshijia.com/images/6a62770e-bb51-11e5-b111-00163e001f59.jpg',
                    'share_price' => '88',
                    'share_user_name' => '春之花',
                    'share_vote' => 165,
                    'share_user_id' => 726457,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/7264571452829828.png'
                ),
                '1512' => array(
                    'share_id' => 1512,
                    'share_name' => '泉林本色 180g卷筒纸 5提共50卷套装',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/ff99631a365_1209.jpg',
                    'share_price' => '150',
                    'share_user_name' => '馨聪',
                    'share_vote' => 2000,
                    'share_user_id' => 872024,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_bb0fe3108e30628b6766530390b7f1e8.jpg'
                ),
                '442' => array(
                    'share_id' => 442,
                    'share_name' => '烟台特产海鲜干货：鱿鱼丝/烤鱼片/金钩海米/干贝/瑶柱',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/fb0a5367f93_0915.jpg',
                    'share_price' => '30',
                    'share_user_name' => '路',
                    'share_vote' => 123,
                    'share_user_id' => 1495,
                    'share_user_img' => 'http://static.tongshijia.com/avatar/wx_head_8c9bd6b81a7f53ebf9194404bf2f3bea.jpg'
                ),
                '858' => array(
                    'share_id' => 858,
                    'share_name' => '来自广西十万大山的原生态巢蜜',
                    'share_img' => '/img/share_index/fengchao.jpg',
                    'share_price' => '69',
                    'share_user_name' => '陈玉燕',
                    'share_vote' => 145,
                    'share_user_id' => 807492,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0797b2ef26b_0812.jpg'
                ),
                '342' => array(
                    'share_id' => 342,
                    'share_name' => '中式酥皮点心之Queen——蛋黄酥',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/f1a425d856d_0818.jpg',
                    'share_price' => '60',
                    'share_user_name' => '甜欣',
                    'share_vote' => 132,
                    'share_user_id' => 813896,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_s/060eced7063_0807.jpg'
                ),
            )
        );
        return $product[$tag];
    }
}