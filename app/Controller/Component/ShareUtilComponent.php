<?php

class ShareUtilComponent extends Component {

    var $name = 'ShareUtil';

    var $normal_order_status = array(ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY);

    public $components = array('Weixin', 'WeshareBuy');

    /**
     * @param $weshare_id
     * @param $uid
     * 触发建团消息
     */
    public function trigger_send_new_share_msg($weshare_id, $uid) {
        $fansPageInfo = $this->WeshareBuy->get_user_relation_page_info($uid);
        $pageCount = $fansPageInfo['pageCount'];
        $pageSize = $fansPageInfo['pageSize'];
        $queue = new SaeTaskQueue('share');
        $queue->addTask("/weshares/process_send_new_share_msg/" . $weshare_id . '/' . $pageCount . '/' . $pageSize);
        //将任务推入队列
        $ret = $queue->push();
        //任务添加失败时输出错误码和错误信息
        if ($ret === false) {
            $this->log('add task queue error ' . json_encode(array($queue->errno(), $queue->errmsg())));
        }
    }

    /**
     * @param $weshareId
     * @param $sharer_id
     * 迁移粉丝数据
     */
    public function process_weshare_task($weshareId, $sharer_id) {
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

    public function get_all_weshares() {
        $weshareM = ClassRegistry::init('Weshare');
        $allWeshares = $weshareM->find('all', array(
            'limit' => 200
        ));
        return $allWeshares;
    }

    public function check_user_is_subscribe($user_id, $follow_id) {
        $userRelationM = ClassRegistry::init('UserRelation');
        $relation = $userRelationM->find('first', array(
            'conditions' => array(
                'user_id' => $user_id,
                'follow_id' => $follow_id
            )
        ));
        return (!empty($relation) && ($relation['UserRelation']['deleted'] == DELETED_NO));
    }

    public function check_user_relation($user_id, $follow_id) {
        $userRelationM = ClassRegistry::init('UserRelation');
        $relation = $userRelationM->find('all', array(
            'conditions' => array(
                'user_id' => $user_id,
                'follow_id' => $follow_id
            )
        ));
        return empty($relation);
    }

    public function delete_relation($sharer_id, $user_id) {
        $userRelationM = ClassRegistry::init('UserRelation');
        $userRelationM->updateAll(array('deleted' => DELETED_YES), array('user_id' => $sharer_id, 'follow_id' => $user_id));
    }

    public function save_relation($sharer_id, $user_id, $type = 'Buy') {
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
    public function save_rebate_log($recommend, $clicker, $weshare_id) {
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
    public function update_rebate_log($id, $order) {
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
    public function update_rebate_log_order_id($id, $order_id, $share_id) {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $rebateTrackLogM->updateAll(array('order_id' => $order_id, 'share_id' => $share_id), array('id' => $id));
    }

    /**
     * @param $share_id
     * @return int
     */
    public function get_share_rebate_ship_fee($share_id) {
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
    public function get_share_rebate_money($share_id) {
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
    public function get_rebate_money($user_id) {
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
    public function is_proxy_user($uid) {
        $userM = ClassRegistry::init('User');
        $isProxy = $userM->userIsProxy($uid);
        return $isProxy == USER_IS_PROXY;
    }

    /**
     * @param $share_id
     * 获取分享rebate data
     */
    public function get_share_rebate_data($share_id) {
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
    public function cal_rebate_money($orders) {
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

    public function get_user_rebate_info($user_id) {
        $rebate_users = $this->rebate_users();
        return $rebate_users[$user_id];
    }

    /**
     * @param $id
     * @param $order
     * process rebate money
     */
    public function process_order_paid_rebate($id, $order) {
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

    public function read_share_ship_option_setting($sharer, $type) {
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
    public function cloneShare($shareId, $uid = null, $address = null, $address_remarks = null, $type = DEFAULT_SHARE_TYPE, $share_status = WESHARE_DELETE_STATUS) {
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

    private function saveGroupShareOfflineAddress($address, $uid, $remarks) {
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
    private function saveGroupShareAddress($address, $share_id) {
        $WeshareAddressM = ClassRegistry::init('WeshareAddress');
        $shareAddressData = array('address' => $address, 'weshare_id' => $share_id);
        $WeshareAddressM->save($shareAddressData);
    }

    //todo clone share product
    private function cloneSharProductTag($new_share_id, $old_share_id) {

    }

    /**
     * @param $new_share_id
     * @param $old_share_id
     * clone share product
     */
    private function cloneShareProduct($new_share_id, $old_share_id) {
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
    private function cloneShareAddresses($new_share_id, $old_share_id) {
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
    private function cloneShareShipSettings($new_share_id, $old_share_id, $is_set_group = false) {
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
    private function cloneShareRebateSet($new_share_id, $old_share_id, $is_set_group = false) {
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
    public function saveShareRecommendLog($shareId, $userId, $memo) {
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
        $this->WeshareBuy->send_recommend_msg($userId, $shareId, $memo);
        $this->notify_sharer_recommend($userId, $shareId);
    }

    /**
     * @param $recommend
     * @param $shareId
     */
    public function notify_sharer_recommend($recommend, $shareId) {
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
    public function save_create_share_opt_log($share_id, $thumbnail, $memo, $user_id) {
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
    public function get_user_level_by_fans_count($uid) {
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
    public function get_user_level($uid, $type = 0) {
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
    public function get_users_level($user_ids) {
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
    public function check_and_save_default_level($uid) {
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
     * @param $order_id
     * save user buy product opt log
     */
    public function save_buy_opt_log($user_id, $share_id, $order_id) {
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
        $this->saveOptLog($optData);
    }

    /**
     * @param $user_id
     * @param $share_id
     * @param $replay_text
     * save comment opt log
     */
    public function save_comment_opt_log($user_id, $share_id, $replay_text) {
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
    public function saveOptLog($data) {
        $optLogM = ClassRegistry::init('OptLog');
        $optLogM->save($data);
        Cache::write(LAST_OPT_LOG_DATA_CACHE_KEY, '');
    }

    /**
     * @param $shareId
     * @return mixed
     * 根据分享获取订单
     */
    public function get_share_orders($shareId) {
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
    public function batch_refund_order($shareId, $refundMark) {
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
    public function refund($orderId, $refundMoney, $refundMark, $refundStatus) {
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
    public function check_order_is_prepaid_and_update_status($order) {
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
    public function save_tags_return($tags, $uid) {
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
    public function get_tags($user_id, $refer_share_id = 0) {
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
    public function get_tags_list($user_id) {
        $tags = $this->load_tags_data($user_id);
        $tags = Hash::extract($tags, '{n}.WeshareProductTag');
        return $tags;
    }

    /**
     * @param $order
     * @return bool
     * check is start new order share and reset order member id
     */
    public function check_is_start_new_group_share($order) {
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
    public function get_share_refer_id($shareId) {
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
    public function get_group_share($uid, $refer_share_id) {
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
    public function get_share_offline_address_detail($share_id) {
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
    public function set_group_share_available($share_id) {
        $weshareM = ClassRegistry::init('Weshare');
        $weshareM->updateAll(array('status' => WESHARE_NORMAL_STATUS), array('id' => $share_id));
    }

    /**
     * @param $share_id
     * @return mixed
     * 根据分享ID回去商品标签
     * 由于在拼团中，没有复制标签，所以要查找父分享的
     */
    private function load_tags_by_share($share_id) {
        $shareInfo = $this->WeshareBuy->get_weshare_info($share_id);
        $shareCreator = $shareInfo['creator'];
        return $this->load_tags_data($shareCreator);
    }

    /**
     * @param $user_id
     * @return mixed
     * cache tags data
     */
    private function load_tags_data($user_id) {
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
    public function get_share_tags($weshare_id) {
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
    public function summery_order_data_by_tag($orderData, $shareId) {
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
    public function process_paid_order_add($order) {
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
    public function get_product_tag_map($weshare_id) {
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
    public function process_order_prepaid($order_id, $product_price_map) {
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
    public function split_order_by_tag($order) {
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

    private function check_cart_confirm_price($tag_carts) {
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
    public function add_money_for_offline_address($share_id, $order) {
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
            //notify share complete task
            $queue = new SaeTaskQueue('tasks');
            //添加单个任务
            $queue->addTask("/task/notify_group_share_complete/" . $share_id);
            //将任务推入队列
            $ret = $queue->push();
            $this->log('notify share complete ' . $ret);

        }
    }

    /**
     * @param $share_id
     * @param $order_creator
     * @param $order_id
     * 退款后每单5元自提费用减去
     */
    public function remove_money_for_offline_address($share_id, $order_creator, $order_id) {
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
    public function get_recent_group_share() {
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
    public function get_share_group_limit($share_id) {
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
    public function send_group_share_complete($share_id) {
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
    public function get_static_offline_address() {
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
    public function new_static_address_group_shares($origin_share_id) {
        $static_addresses = $this->get_static_offline_address();
        $queue = new SaeTaskQueue('share');
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
        $queue->addTask($tasks);
        //将任务推入队列
        $ret = $queue->push();
        //任务添加失败时输出错误码和错误信息
        if ($ret === false)
            var_dump($queue->errno(), $queue->errmsg());
        return $ret;
    }

    /**
     * @param $weshareData
     * 级联更新数据
     */
    public function cascadeSaveShareData($weshareData) {
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
    public function usedUserSubSharerReason($uid) {
        $SubReasonM = ClassRegistry::init('UserSubReason');
        $SubReasonM->updateAll(array('used' => 1), array('user_id' => $uid, 'type' => array(SUB_SHARER_REASON_TYPE_FROM_USER_CENTER, SUB_SHARER_REASON_TYPE_FROM_SHARE_INFO)));
    }


    /**
     * @param $tag
     * @return array
     * index product
     */
    public function get_share_index_product($tag) {
        $product = array(
            0 => array(
                '1436' => array(
                    'share_id' => 1436,
                    'share_name' => '沙窝萝卜--脆绿嫩',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/91ca3f25e0f_1205.jpg',
                    'share_price' => '27',
                    'share_user_name' => '平凡的世界',
                    'share_vote' => 1000,
                    'share_user_id' => 801447,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/8014471448421852.png'
                ),
                '1484' => array(
                    'share_id' => 1484,
                    'share_name' => '俄罗斯野生淡干小海参',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/38e770bc1b4_1209.jpg',
                    'share_price' => '399',
                    'share_user_name' => '樱花',
                    'share_vote' => 3000,
                    'share_user_id' => 810684,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
                ),
                '1510' => array(
                    'share_id' => 1506,
                    'share_name' => '野生净鱼段带鱼【包邮】',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/c4c9f3c7bb8_1209.jpg',
                    'share_price' => '79.8',
                    'share_user_name' => '小宝妈',
                    'share_vote' => 2000,
                    'share_user_id' => 811917,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
                ),
                '1500' => array(
                    'share_id' => 1500,
                    'share_name' => '小火团贡玉米 甜糯口感 浓郁的玉米原香',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/0129a52fd3d_1209.jpg',
                    'share_price' => '68',
                    'share_user_name' => '杨晓光',
                    'share_vote' => 2000,
                    'share_user_id' => 141,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_f88cfd957b22b112058e340d508423a7.jpg'
                ),
                //无花果干 二团
                '1431' => array(
                    'share_id' => 1431,
                    'share_name' => '无添加威海无花果干，还送无花果叶茶',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/4def05a1d4b_1209.jpg',
                    'share_price' => '35',
                    'share_user_name' => '盛夏',
                    'share_vote' => 235,
                    'share_user_id' => 708029,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
                ),
                '1395' => array(
                    'share_id' => 1395,
                    'share_name' => '好吃的真空低温油浴果蔬套装（黄秋葵+香菇+什锦果蔬）',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/d50b8e8d6ad_1202.jpg',
                    'share_price' => '55',
                    'share_user_name' => '赵宇',
                    'share_vote' => 2000,
                    'share_user_id' => 810688,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_7db41874b00ec17af715b72adf87768a.jpg'
                ),
//                '1268' => array(
//                    'share_id' => 1268,
//                    'share_name' => '麻阳冰糖橙（包邮预售）----不打药，不防腐，不上蜡，守护内心的“橙”实',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/2f2ae8653ee_1123.jpg',
//                    'share_price' => '68',
//                    'share_user_name' => '鲲鲲',
//                    'share_vote' => 560,
//                    'share_user_id' => 806889,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
//                ),
                '1392' => array(
                    'share_id' => 1392,
                    'share_name' => '多油、好吃到停不下来的海鸭蛋',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/ece57e81c34_1027.jpg',
                    'share_price' => '55',
                    'share_user_name' => '樱花',
                    'share_vote' => 3000,
                    'share_user_id' => 810684,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
                ),
                '1439' => array(
                    'share_id' => 1439,
                    'share_name' => '云南秘制油淋牛肝菌，过嘴不忘的味道',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/6eb4a007442_1110.jpg',
                    'share_price' => '70',
                    'share_user_name' => '鲲鲲',
                    'share_vote' => 560,
                    'share_user_id' => 806889,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
                ),
                '1506' => array(
                    'share_id' => 1506,
                    'share_name' => '越南黑虎虾仁 【纯野生虾仁】',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/cc44309c109_1202.jpg',
                    'share_price' => '150',
                    'share_user_name' => '小宝妈',
                    'share_vote' => 1000,
                    'share_user_id' => 811917,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
                ),
                '1256' => array(
                    'share_id' => 1256,
                    'share_name' => '有机翠香猕猴桃来了，你还等什么',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/7905f59b420_1203.jpg',
                    'share_price' => '95',
                    'share_user_name' => '赵静',
                    'share_vote' => 1000,
                    'share_user_id' => 867250,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_2d0cd7f75b366ae4ccb40cc380351574.jpg'
                ),
                //银耳
                '1199' => array(
                    'share_id' => 1199,
                    'share_name' => '鲜活银耳@"防霾佳品"【顺丰包邮】',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/f257f80229f_1110.jpg',
                    'share_price' => '108',
                    'share_user_name' => '片片妈',
                    'share_vote' => 600,
                    'share_user_id' => 878825,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_52d08a7c8bb6e9611307b6c4858ca38c.jpg'
                ),
//                '1181' => array(
//                    'share_id' => 1181,
//                    'share_name' => '多油、好吃到停不下来的海鸭蛋',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/ece57e81c34_1027.jpg',
//                    'share_price' => '55',
//                    'share_user_name' => '李樱花',
//                    'share_vote' => 800,
//                    'share_user_id' => 810684,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
//                ),
//                '1314' => array(
//                    'share_id' => 1314,
//                    'share_name' => '俱乐部团购14—赵姑娘合核枣',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/81f96c841c5_1127.jpg',
//                    'share_price' => '900',
//                    'share_user_name' => 'guru',
//                    'share_vote' => 1333,
//                    'share_user_id' => 859965,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_cb6f01f497f772a9dd58c1655c7770c1.jpg'
//                ),
//                '1270' => array(
//                    'share_id' => 1270,
//                    'share_name' => '俄罗斯艾利客面粉(高筋)【北京包邮】',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/7e79af0be49_1125.jpg',
//                    'share_price' => '160',
//                    'share_user_name' => '小宝妈',
//                    'share_vote' => 1000,
//                    'share_user_id' => 811917,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
//                ),
                '1451' => array(
                    'share_id' => 1451,
                    'share_name' => '德庆贡柑熟了，想找找当皇帝娘娘的感觉吗？',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/3c397b7406b_1125.jpg',
                    'share_price' => '46',
                    'share_user_name' => '李明',
                    'share_vote' => 999,
                    'share_user_id' => 6783,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0e8ff635498de280dd3193826d837ee5.jpg'
                ),
                '438' => array(
                    'share_id' => 438,
                    'share_name' => '河南荥阳河阴软籽大石榴',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/9275047528e_1027.jpg',
                    'share_price' => '68',
                    'share_user_name' => '段赵明',
                    'share_vote' => 300,
                    'share_user_id' => 1199,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/6f5125521e1_0903.jpg'
                ),
//                '1301' => array(
//                    'share_id' => 1301,
//                    'share_name' => '越南原装进口黑虎虾仁儿1000g',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/ee7947974c7_1127.jpg',
//                    'share_price' => '46',
//                    'share_user_name' => '吃好网',
//                    'share_vote' => 1000,
//                    'share_user_id' => 884103,
//                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/9e077f58c06_1125.jpg'
//                ),
                '747' => array(
                    'share_id' => 747,
                    'share_name' => '那那家五常稻花香米',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/dafe5e63fc8_1027.jpg',
                    'share_price' => '166',
                    'share_user_name' => '那那',
                    'share_vote' => 600,
                    'share_user_id' => 812111,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_db53c030cbe19145428f0d5ca58b9562.jpg'
                ),
//                '1304' => array(
//                    'share_id' => 1304,
//                    'share_name' => '越南进口红心火龙果 大果5枚装',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/1657753d5b2_1106.jpg',
//                    'share_price' => '88',
//                    'share_user_name' => '金子',
//                    'share_vote' => 188,
//                    'share_user_id' => 867768,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_6826f42e5a8ed9fc40cc3653e12b1064.jpg'
//                ),
                //山药
                '1483' => array(
                    'share_id' => 1483,
                    'share_name' => '口口相传的艳艳山药第五批团！',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/9865c6196ae_1110.jpg',
                    'share_price' => '85',
                    'share_user_name' => '艳艳',
                    'share_vote' => 800,
                    'share_user_id' => 12376,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/123761447121370.png'
                ),
                //紫皮糖
                '1338' => array(
                    'share_id' => 1338,
                    'share_name' => '俄罗斯经典紫皮糖、鲜奶威化和酸奶威化',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/9d333807aad_1110.jpg',
                    'share_price' => '70',
                    'share_user_name' => '微儿',
                    'share_vote' => 2000,
                    'share_user_id' => 23771,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_d742f4391e472ca6a24c58d96be17aca.jpg'
                ),
//                '1238' => array(
//                    'share_id' => 1238,
//                    'share_name' => '香妃柚',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/9bd5eea0b33_1118.jpg',
//                    'share_price' => '74.8',
//                    'share_user_name' => '朋友说小妹',
//                    'share_vote' => 1000,
//                    'share_user_id' => 711503,
//                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
//                ),
//                '1237' => array(
//                    'share_id' => 1237,
//                    'share_name' => '泰国野生迷你（MINI）小菠萝',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/fc5ce30c960_1118.jpg',
//                    'share_price' => '149',
//                    'share_user_name' => '朋友说小妹',
//                    'share_vote' => 1000,
//                    'share_user_id' => 711503,
//                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
//                ),
//                '1201' => array(
//                    'share_id' => 1201,
//                    'share_name' => '沧源原始森林野生丑木耳',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/0c2ac493287_1118.jpg',
//                    'share_price' => '36',
//                    'share_user_name' => '鲲鲲',
//                    'share_vote' => 560,
//                    'share_user_id' => 806889,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
//                ),
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
//                '1101' => array(
//                    'share_id' => 1101,
//                    'share_name' => '老家农家自产玉米面',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/dd3ad10f3f2_1118.jpg',
//                    'share_price' => '26',
//                    'share_user_name' => '陈彦',
//                    'share_vote' => 300,
//                    'share_user_id' => 848454,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_ddecfe1af5d21c658eed85d84a10ba6c.jpg'
//                ),
                '961' => array(
                    'share_id' => 961,
                    'share_name' => '阳光下的枇杷，蒙自甜甜的小枇杷',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/39dda22d439_1105.jpeg',
                    'share_price' => '108',
                    'share_user_name' => '盛夏',
                    'share_vote' => 235,
                    'share_user_id' => 708029,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
                ),
                '1335' => array(
                    'share_id' => 1335,
                    'share_name' => '六斤枇杷一斤膏----枇杷之乡，农家密炼，汁汁原味，滴滴健康',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/8eff16b947b_1130.jpg',
                    'share_price' => '115',
                    'share_user_name' => '鲲鲲',
                    'share_vote' => 1000,
                    'share_user_id' => 806889,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
                ),
                //地瓜干,牛杆菌
//                '1072' => array(
//                    'share_id' => 1072,
//                    'share_name' => '停不下来的味道～长白山脚下地瓜干',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/249eef44d07_1110.jpg',
//                    'share_price' => '65',
//                    'share_user_name' => '小宝妈',
//                    'share_vote' => 1000,
//                    'share_user_id' => 811917,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
//                ),

                //微微安
//                '1098' => array(
//                    'share_id' => 1098,
//                    'share_name' => '薇薇安千层出新口味咯，提拉米苏千层蛋糕冬日里的暖暖美食',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/49f0af92aa7_1110.jpg',
//                    'share_price' => '160',
//                    'share_user_name' => '盛夏',
//                    'share_vote' => 235,
//                    'share_user_id' => 708029,
//                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
//                ),
                //脐橙
                '1085' => array(
                    'share_id' => 1085,
                    'share_name' => '不打蜡不染色不催熟的赣南脐橙',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/9db396b550e_1110.jpg',
                    'share_price' => '49',
                    'share_user_name' => '盛夏',
                    'share_vote' => 235,
                    'share_user_id' => 708029,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
                ),
//                '1060' => array(
//                    'share_id' => 1060,
//                    'share_name' => '没有美丽的图片，只有美丽的口感，小宝妈水果团',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/869ca52575f_1109.jpg',
//                    'share_price' => '30',
//                    'share_user_name' => '小宝妈',
//                    'share_vote' => 1000,
//                    'share_user_id' => 811917,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
//                ),
//                '953' => array(
//                    'share_id' => 953,
//                    'share_name' => '脆甜好吃的冬雪蜜桃开团喽！',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/980e1682705_1027.jpg',
//                    'share_price' => '120',
//                    'share_user_name' => '何荷',
//                    'share_vote' => 235,
//                    'share_user_id' => 875275,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/8752751445930740.png'
//                ),
                '1009' => array(
                    'share_id' => 1009,
                    'share_name' => '甜甜糯糯的迁西板栗',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/0019710cb43_1104.jpg',
                    'share_price' => '75',
                    'share_user_name' => 'Ruby',
                    'share_vote' => 550,
                    'share_user_id' => 873345,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_03a203d2622e7173f03d9cf3d8b4784d.jpg'
                ),
                '884' => array(
                    'share_id' => 884,
                    'share_name' => '鼎力推荐！云南天然蜜酿玫瑰，有买有赠，两瓶包邮！',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/7ff87aa3340_1027.jpg',
                    'share_price' => '46',
                    'share_user_name' => '鲲鲲',
                    'share_vote' => 560,
                    'share_user_id' => 806889,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
                ),
                //笋干
//                '1057' => array(
//                    'share_id' => 1057,
//                    'share_name' => '鼎力推荐！楠竹笋干，父辈的手艺。宁可居无竹，不可食无笋，分享来自大地的馈赠！！',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/59a37d840ea_1110.jpg',
//                    'share_price' => '86',
//                    'share_user_name' => '鲲鲲',
//                    'share_vote' => 560,
//                    'share_user_id' => 806889,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
//                ),
//                //丑苹果
//                '1015' => array(
//                    'share_id' => 1015,
//                    'share_name' => '云南丑苹果，丑到崩溃，好吃到哭，实力派不靠卖脸，现摘即发。',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/bf3bac9a370_1110.jpg',
//                    'share_price' => '88',
//                    'share_user_name' => '鲲鲲',
//                    'share_vote' => 560,
//                    'share_user_id' => 806889,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
//                ),

                //新疆大枣
//                '1021' => array(
//                    'share_id' => 1021,
//                    'share_name' => '温暖亲情组合装“新疆大枣”@“福建银耳”',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/c91e46cf7ba_1110.jpg',
//                    'share_price' => '72',
//                    'share_user_name' => '李樱花',
//                    'share_vote' => 800,
//                    'share_user_id' => 810684,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
//                ),
//                '446' => array(
//                    'share_id' => 446,
//                    'share_name' => '阳澄湖大闸蟹2015中秋第一波团购启动啦！',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/da7db400b09_0906.jpg',
//                    'share_price' => '150',
//                    'share_user_name' => '博文',
//                    'share_vote' => 300,
//                    'share_user_id' => 815,
//                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/9257b1649ba_0906.jpg'
//                ),
//                '954' => array(
//                    'share_id' => 954,
//                    'share_name' => '好吃到爆的琼中绿橙，来自北纬18°的热带天然水果',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/7c62eeb1412_1027.jpg',
//                    'share_price' => '128',
//                    'share_user_name' => '亮',
//                    'share_vote' => 300,
//                    'share_user_id' => 842862,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_03f346b121de697c8f51b36ba498700d.jpg'
//                ),
//                '659' => array(
//                    'share_id' => 659,
//                    'share_name' => '浙江衢州橘子团（限北京地区）',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/000c732f7c6_1009.jpg',
//                    'share_price' => '60',
//                    'share_user_name' => '小宝妈',
//                    'share_vote' => 900,
//                    'share_user_id' => 811917,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
//                ),
//                '885' => array(
//                    'share_id' => 885,
//                    'share_name' => '云南哀牢山古法手工叶子红糖，特价包邮还有赠品必须分享啊！',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/abcb4094d25_0917.jpg',
//                    'share_price' => '38',
//                    'share_user_name' => '鲲鲲',
//                    'share_vote' => 130,
//                    'share_user_id' => 806889,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg'
//                ),
//                '1061' => array(
//                    'share_id' => 1061,
//                    'share_name' => '父母种的红薯、紫薯、花生开刨啦',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/ff1894c1082_0928.jpg',
//                    'share_price' => '40',
//                    'share_user_name' => '大美',
//                    'share_vote' => 345,
//                    'share_user_id' => 842908,
//                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/319793d271b_0828.jpg'
//                ),
//                '674' => array(
//                    'share_id' => 674,
//                    'share_name' => '新疆阿克苏新温185核桃',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/71dfdf1dca7_1009.jpg',
//                    'share_price' => '108',
//                    'share_user_name' => '李樱花',
//                    'share_vote' => 95,
//                    'share_user_id' => 810684,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
//                ),
//                '664' => array(
//                    'share_id' => 664,
//                    'share_name' => '当天海南空运～薇薇安千层水果蛋糕',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/39e96c45dc2_0930.jpg',
//                    'share_price' => '160',
//                    'share_user_name' => '盛夏',
//                    'share_vote' => 987,
//                    'share_user_id' => 708029,
//                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
//                ),
//                '670' => array(
//                    'share_id' => 670,
//                    'share_name' => ' 树上的糖包子，新鲜树摘威海青皮无花果 ',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/6f41187b776_0901.jpg',
//                    'share_price' => '69',
//                    'share_user_name' => '盛夏',
//                    'share_vote' => 235,
//                    'share_user_id' => 708029,
//                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
//                ),
//                '636' => array(
//                    'share_id' => 636,
//                    'share_name' => '沾化冬枣，真正有机冬枣！',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/3757a24d870_0923.jpg',
//                    'share_price' => '58',
//                    'share_user_name' => '盛夏',
//                    'share_vote' => 235,
//                    'share_user_id' => 708029,
//                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
//                ),
                '717' => array(
                    'share_id' => 717,
                    'share_name' => '正宗韩国辣白菜',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/c68c07c4022_0915.jpg',
                    'share_price' => '35',
                    'share_user_name' => '土豆',
                    'share_vote' => 95,
                    'share_user_id' => 712908,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/885789f1d97_0915.jpg'
                ),
//                '542' => array(
//                    'share_id' => 542,
//                    'share_name' => '喜迎中秋云南云腿月饼团购',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/99b37d6ef0d_0915.jpg',
//                    'share_price' => '35',
//                    'share_user_name' => '白胡子老头',
//                    'share_vote' => 123,
//                    'share_user_id' => 869820,
//                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/368fc9903d0_0908.jpg'
//                ),
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
                '800' => array(
                    'share_id' => 800,
                    'share_name' => '新鲜栖霞红富士，现摘现发，无农药不打蜡',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/ff415812eb4_1110.jpg',
                    'share_price' => '40',
                    'share_user_name' => '盛夏',
                    'share_vote' => 230,
                    'share_user_id' => 708029,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_e92e61d892c2ad72c0e01ec1ac136e71.jpg'
                ),
                '1165' => array(
                    'share_id' => 1165,
                    'share_name' => '刺激你的味蕾，百香果',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/6ebd4d49632_1118.jpg',
                    'share_price' => '48',
                    'share_user_name' => '盛夏',
                    'share_vote' => 700,
                    'share_user_id' => 708029,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
                ),
                //怀北红 梨
                '997' => array(
                    'share_id' => 997,
                    'share_name' => '京郊特色之一----怀北“红肖梨”、“糖梨”',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/0c972b72dca_1110.jpg',
                    'share_price' => '85',
                    'share_user_name' => '李樱花',
                    'share_vote' => 800,
                    'share_user_id' => 810684,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
                ),
                '828' => array(
                    'share_id' => 828,
                    'share_name' => '赣南脐橙',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/da5cb8ee003_1027.jpg',
                    'share_price' => '98',
                    'share_user_name' => '习蛋蛋',
                    'share_vote' => 650,
                    'share_user_id' => 563240,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/26a561eddc9_1021.jpg'
                ),
                //玉米神话
//                '1070' => array(
//                    'share_id' => 1070,
//                    'share_name' => '密云太师屯--传说中的玉米神话',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/26c0eec40d6_0915.jpg',
//                    'share_price' => '68',
//                    'share_user_name' => '李樱花',
//                    'share_vote' => 95,
//                    'share_user_id' => 810684,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
//                ),
                '634' => array(
                    'share_id' => 634,
                    'share_name' => '明明分享自家爸妈种植的正宗阳信鸭梨',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/bd4dde08463_0923.jpg',
                    'share_price' => '66',
                    'share_user_name' => '明明有梨',
                    'share_vote' => 235,
                    'share_user_id' => 999,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/2dca381fd51_0923.jpg'
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
                '635' => array(
                    'share_id' => 635,
                    'share_name' => '朝鲜族特色美食-深海纯野生明太鱼',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/329b4b275d5_0923.jpg',
                    'share_price' => '79',
                    'share_user_name' => '明明有梨',
                    'share_vote' => 235,
                    'share_user_id' => 999,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/2dca381fd51_0923.jpg'
                ),
                '410' => array(
                    'share_id' => 410,
                    'share_name' => '陕西眉县猴吃桃三色猕猴桃',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/653d1bbe04e_0901.jpg',
                    'share_price' => '149',
                    'share_user_name' => '张慧敏',
                    'share_vote' => 230,
                    'share_user_id' => 23711,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/4c91fb9380d_0901.jpg'
                ),
                '419' => array(
                    'share_id' => 419,
                    'share_name' => '山西古县新核桃',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/f6a605eebcb_0915.jpg',
                    'share_price' => '16.5',
                    'share_user_name' => '苏打饼干',
                    'share_vote' => 230,
                    'share_user_id' => 9228,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_a17d0cc7ba98f4ccc8207b225f4fa549.jpg'
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
                //寒之乡 稻花香
                '1065' => array(
                    'share_id' => 1065,
                    'share_name' => '寒之乡稻花香2号大米----2015年新米上市啦！',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/b34f11172dc_1110.jpg',
                    'share_price' => '110',
                    'share_user_name' => '李樱花',
                    'share_vote' => 300,
                    'share_user_id' => 810684,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
                ),
                '503' => array(
                    'share_id' => 503,
                    'share_name' => '旱地杂粮之清水河小香米',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/302470b0709_0915.jpg',
                    'share_price' => '6.18',
                    'share_user_name' => 'Mr.White',
                    'share_vote' => 187,
                    'share_user_id' => 832279,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_624ed608a37ca10baec387ffb2d1fd89.jpg'
                ),
//                '791' => array(
//                    'share_id' => 791,
//                    'share_name' => '东北五常稻花香大米五常当地米厂专供',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/965ba48b2d2_0818.jpg',
//                    'share_price' => '160',
//                    'share_user_name' => '王谷丹',
//                    'share_vote' => 187,
//                    'share_user_id' => 1388,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_928e6bec43ee9674c9abbcf7ce7eae61.jpg'
//                ),
                '424' => array(
                    'share_id' => 424,
                    'share_name' => '东北野生臻蘑（2015年刚采摘的哦）',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/22e7b36d290_0912.jpg',
                    'share_price' => '48',
                    'share_user_name' => '那那',
                    'share_vote' => 165,
                    'share_user_id' => 812111,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_db53c030cbe19145428f0d5ca58b9562.jpg'
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
                '1007' => array(
                    'share_id' => 1007,
                    'share_name' => '积累了7000位老顾客的土鸡蛋 朋友说特供40枚/60元',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/15157459186_1110.jpg',
                    'share_price' => '60',
                    'share_user_name' => '小赵-水源绿色食品',
                    'share_vote' => 203,
                    'share_user_id' => 876460,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_deb52342ad0b28deb859ef2b01835ca8.jpg'
                ),
                '328' => array(
                    'share_id' => 328,
                    'share_name' => '天福号山地散养有机柴鸡蛋(限五环内)',
                    'share_img' => '/img/share_index/jidang.jpg',
                    'share_price' => '24.8',
                    'share_user_name' => '朋友说小妹',
                    'share_vote' => 203,
                    'share_user_id' => 711503,
                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
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
                //泉林的纸
                '1512' => array(
                    'share_id' => 1512,
                    'share_name' => '泉林本色纸品【限北京】',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/a8d8a9adf87_1209.jpg',
                    'share_price' => '132',
                    'share_user_name' => '馨聪',
                    'share_vote' => 2000,
                    'share_user_id' => 872024,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_bb0fe3108e30628b6766530390b7f1e8.jpg'
                ),
                '442' => array(
                    'share_id' => 442,
                    'share_name' => '烟台特产海鲜干货：鱿鱼丝/烤鱼片/金钩海米/干贝/瑶柱',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/fb0a5367f93_0915.jpg',
                    'share_price' => '30',
                    'share_user_name' => '路',
                    'share_vote' => 123,
                    'share_user_id' => 1495,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_8c9bd6b81a7f53ebf9194404bf2f3bea.jpg'
                ),
//                '460' => array(
//                    'share_id' => 460,
//                    'share_name' => '云南九叶玫鲜花饼店',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/d3c9dfd7efd_0908.jpg',
//                    'share_price' => '5',
//                    'share_user_name' => '白胡子老头',
//                    'share_vote' => 123,
//                    'share_user_id' => 869820,
//                    'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/368fc9903d0_0908.jpg'
//                ),
//                '56' => array(
//                    'share_id' => 56,
//                    'share_name' => '新疆天山脚下的骏枣',
//                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/babee077c75_0908.jpg',
//                    'share_price' => '100',
//                    'share_user_name' => '樱花',
//                    'share_vote' => 197,
//                    'share_user_id' => 810684,
//                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
//                ),
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
                '724' => array(
                    'share_id' => 724,
                    'share_name' => '密云山谷中纯正自家产荆花蜜',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/7167382dca7_0826.jpg',
                    'share_price' => '38-78',
                    'share_user_name' => '樱花',
                    'share_vote' => 197,
                    'share_user_id' => 810684,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
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
                '766' => array(
                    'share_id' => 766,
                    'share_name' => '可以吃的润唇膏',
                    'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/cd1a35d9e5a_0915.jpg',
                    'share_price' => '30',
                    'share_user_name' => '庄梓铭',
                    'share_vote' => 132,
                    'share_user_id' => 823656,
                    'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_6f6b33c00c71819c1eb74f29f3f1503d.jpg'
                ),

            )
        );
        return $product[$tag];
    }
}