<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 8/20/15
 * Time: 15:04
 */
class ShareUtilComponent extends Component {

    var $name = 'ShareUtil';

    public $components = array('Weixin', 'WeshareBuy');

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
        $order_id = $order['Order']['id'];
        $ship_fee = $order['Order']['ship_fee'];
        $share_id = $order['Order']['member_id'];
        $total_price = $order['Order']['total_all_price'];
        $rebate_money = 0;
        $ship_fee = round($ship_fee / 100, 2);
        $canRebateMoney = $total_price - $ship_fee;
        $rebatePercentData = $this->get_share_rebate_data($share_id);
        if (!empty($rebatePercentData)) {
            $percent = $rebatePercentData['ProxyRebatePercent']['percent'];
            $rebate_money = ($canRebateMoney * $percent) / 100;
            $rebate_money = round($rebate_money, 2);
            $rebate_money = $rebate_money * 100;
        }
        $rebateTrackLogM->updateAll(array('is_paid' => 1, 'updated' => '\'' . date('Y-m-d H:i:s') . '\'', 'rebate_money' => $rebate_money), array('id' => $id, 'order_id' => $order_id));
        $rebateTrackLog = $rebateTrackLogM->find('first', array(
            'conditions' => array(
                'id' => $id
            )
        ));
        return array('rebate_money' => $rebate_money, 'order_price' => $canRebateMoney, 'recommend' => $rebateTrackLog['RebateTrackLog']['sharer']);
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
    public function get_share_rebate_money($share_id){
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $allRebateMoney = 0;
        $rebateLogs = $rebateTrackLogM->find('all', array(
            'conditions' => array(
                'share_id' => $share_id,
                'not' => array('order_id' => 0, 'is_paid' => 0)
            ),
            'limit' => 500
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
        $rebate_money =  round($rebate_money / 100, 2);
        $rebate_money = number_format($rebate_money, 2);
        $this->Weixin->send_rebate_template_msg($recommend_open_ids[$recommend], $detail_url, $order_id, $order_money, $pay_time, $rebate_money, $title);
    }

    /**
     * @param $shareId
     * @return array
     * clone一份
     */
    public function cloneShare($shareId) {
        $WeshareM = ClassRegistry::init('Weshare');
        $WeshareProductM = ClassRegistry::init('WeshareProduct');
        $WeshareAddressM = ClassRegistry::init('WeshareAddress');
        $WeshareShipSettingM = ClassRegistry::init('WeshareShipSetting');
        $proxyRebatePercentM = ClassRegistry::init('ProxyRebatePercent');
        $shareInfo = $WeshareM->find('first', array(
            'conditions' => array(
                'id' => $shareId
            )
        ));
        $shareInfo = $shareInfo['Weshare'];
        $shareInfo['id'] = null;
        $shareInfo['created'] = date('Y-m-d H:i:s');
        $shareInfo['status'] = 0;
        $uid = $shareInfo['creator'];
        $WeshareM->id = null;
        $newShareInfo = $WeshareM->save($shareInfo);
        if ($newShareInfo) {
            $newShareId = $newShareInfo['Weshare']['id'];
            $shareProducts = $WeshareProductM->find('all', array(
                'conditions' => array(
                    'weshare_id' => $shareId
                )
            ));
            $newProducts = array();
            foreach ($shareProducts as $itemShareProduct) {
                $itemShareProduct = $itemShareProduct['WeshareProduct'];
                $itemShareProduct['id'] = null;
                $itemShareProduct['weshare_id'] = $newShareId;
                $newProducts[] = $itemShareProduct;
            }
            $WeshareProductM->id = null;
            $WeshareProductM->saveAll($newProducts);

            $shareAddresses = $WeshareAddressM->find('all', array(
                'conditions' => array(
                    'weshare_id' => $shareId
                )
            ));
            $newAddresses = array();
            foreach ($shareAddresses as $itemShareAddress) {
                $itemShareAddress = $itemShareAddress['WeshareAddress'];
                $itemShareAddress['id'] = null;
                $itemShareAddress['weshare_id'] = $newShareId;
                $newAddresses[] = $itemShareAddress;
            }
            $WeshareAddressM->id = null;
            $WeshareAddressM->saveAll($newAddresses);

            $shareShipSettings = $WeshareShipSettingM->find('all', array(
                'conditions' => array(
                    'weshare_id' => $shareId
                )
            ));
            $newShareShipSettings = array();
            foreach ($shareShipSettings as $itemShareShipSetting) {
                $itemShareShipSetting = $itemShareShipSetting['WeshareShipSetting'];
                $itemShareShipSetting['id'] = null;
                $itemShareShipSetting['weshare_id'] = $newShareId;
                $newShareShipSettings[] = $itemShareShipSetting;
            }
            $WeshareShipSettingM->id = null;
            $WeshareShipSettingM->saveAll($newShareShipSettings);

            $oldShareRebateSet = $proxyRebatePercentM->find('first', array(
                'conditions' => array('share_id' => $shareId)
            ));
            if (!empty($oldShareRebateSet)) {
                $newShareRebateSet = $oldShareRebateSet['ProxyRebatePercent'];
                $newShareRebateSet['id'] = null;
                $newShareRebateSet['share_id'] = $newShareId;
                $proxyRebatePercentM->save($newShareRebateSet);
            } else {
                $proxyRebatePercentM->save(array('share_id' => $newShareId, 'percent' => 0));
            }
            Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $uid, '');
            return array('shareId' => $newShareId, 'success' => true);
        }
        return array('success' => false);
    }

    /**
     * @return array
     * index product
     */
    public function get_share_index_product() {
        $product = array(
            '446' => array(
                'share_id' => 446,
                'share_name' => '阳澄湖大闸蟹2015中秋第一波团购启动啦！',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/da7db400b09_0906.jpg',
                'share_price' => '150',
                'share_user_name' => '博文',
                'share_vote' => 300,
                'share_user_id' => 815,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/9257b1649ba_0906.jpg'
            ),
            '438' => array(
                'share_id' => 438,
                'share_name' => '河南荥阳河阴软籽大石榴，9月20号抢鲜',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/a7c2d7a2a0d_0906.jpg',
                'share_price' => '68',
                'share_user_name' => '段赵明',
                'share_vote' => 300,
                'share_user_id' => 1199,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/6f5125521e1_0903.jpg'
            ),
            '481' => array(
                'share_id' => 481,
                'share_name' => '这个季节最好吃的橘子和橙子',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/5ab4e3ec7fd_0908.jpg',
                'share_price' => '139',
                'share_user_name' => '小宝妈',
                'share_vote' => 50,
                'share_user_id' => 811917,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
            ),
            '406' => array(
                'share_id' => 406,
                'share_name' => ' 树上的糖包子，新鲜树摘威海青皮无花果 ',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/6f41187b776_0901.jpg',
                'share_price' => '69',
                'share_user_name' => '盛夏',
                'share_vote' => 95,
                'share_user_id' => 708029,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
            ),
            '484' => array(
                'share_id' => 484,
                'share_name' => '口感清甜、平谷经典晚24号大桃',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/c40b45adec8_0908.jpg',
                'share_price' => '100',
                'share_user_name' => '小宝妈',
                'share_vote' => 150,
                'share_user_id' => 811917,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
            ),
            '489' => array(
                'share_id' => 489,
                'share_name' => '天津茶淀玫瑰香葡萄,8小时极致新鲜',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/f16a8e0a80e_0824.jpg',
                'share_price' => '79.9',
                'share_user_name' => '刘忠立',
                'share_vote' => 53,
                'share_user_id' => 801447,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_s/c0a73f0653b_0822.jpg'
            ),
            '460' => array(
                'share_id' => 460,
                'share_name' => '云南九叶玫鲜花饼店',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/f16a8e0a80e_0824.jpg',
                'share_price' => '5.5',
                'share_user_name' => '白胡子老头',
                'share_vote' => 123,
                'share_user_id' => 869820,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_40bab950248dce61adbc14cc327ba77c.jpg'
            ),
            '56' => array(
                'share_id' => 56,
                'share_name' => '新疆天山脚下的骏枣',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/babee077c75_0908.jpg',
                'share_price' => '100',
                'share_user_name' => '樱花',
                'share_vote' => 197,
                'share_user_id' => 810684,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
            ),
            '410' => array(
                'share_id' => 410,
                'share_name' => ' 陕西眉县猴吃桃三色猕猴桃',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/653d1bbe04e_0901.jpg',
                'share_price' => '149',
                'share_user_name' => '张慧敏',
                'share_vote' => 230,
                'share_user_id' => 23711,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/4c91fb9380d_0901.jpg'
            ),
            '481-1' => array(
                'share_id' => 481,
                'share_name' => '迷人香甜、泰国金枕头榴',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/24723e0b6d9_0908.jpg',
                'share_price' => '139',
                'share_user_name' => '小宝妈',
                'share_vote' => 50,
                'share_user_id' => 811917,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
            ),
            '481-2' => array(
                'share_id' => 481,
                'share_name' => '宝宝最爱、墨西哥进口',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/ddcdc890a8f_0908.jpg',
                'share_price' => '139',
                'share_user_name' => '小宝妈',
                'share_vote' => 50,
                'share_user_id' => 811917,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
            ),
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
//            '386' => array(
//                'share_id' => 386,
//                'share_name' => '翠香猕猴桃  甜蜜蜜，陕西周至翠香绿心猕猴桃',
//                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/dd0ddeb9bc9_0901.jpg',
//                'share_price' => '77',
//                'share_user_name' => '盛夏',
//                'share_vote' => 95,
//                'share_user_id' => 708029,
//                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
//            ),

            '419' => array(
                'share_id' => 419,
                'share_name' => '山西古县新核桃',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/4dff0b6ff92_0901.jpg',
                'share_price' => '16.5',
                'share_user_name' => '苏打饼干',
                'share_vote' => 230,
                'share_user_id' => 9228,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_a17d0cc7ba98f4ccc8207b225f4fa549.jpg'
            ),
            '353' => array(
                'share_id' => 353,
                'share_name' => '房山窦店有机黄金梨',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/7753d4bf5e4_0901.jpg',
                'share_price' => '88',
                'share_user_name' => '大美',
                'share_vote' => 230,
                'share_user_id' => 842908,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/319793d271b_0828.jpg'
            ),
            '394' => array(
                'share_id' => 394,
                'share_name' => '广州南沙一点红番薯',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/2459fbbbcd3_0901.jpg',
                'share_price' => '78',
                'share_user_name' => '筠子花树',
                'share_vote' => 125,
                'share_user_id' => 664258,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_27f6e242fbb54bea79ef3550e060f856.jpg'
            ),
            '388' => array(
                'share_id' => 388,
                'share_name' => '泰国空运新鲜椰青',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/2d117ce8806_0826.jpg',
                'share_price' => '59',
                'share_user_name' => '朋友说小妹',
                'share_vote' => 203,
                'share_user_id' => 711503,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
            ),
            '325' => array(
                'share_id' => 325,
                'share_name' => '来自广西十万大山的原生态巢蜜',
                'share_img' => '/img/share_index/fengchao.jpg',
                'share_price' => '69',
                'share_user_name' => '陈玉燕',
                'share_vote' => 145,
                'share_user_id' => 807492,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0797b2ef26b_0812.jpg'
            ),
            '354' => array(
                'share_id' => 354,
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
//            '297'=>array(
//                'share_id' => 297,
//                'share_name' => '瑞士卷——每一道甜品都是有故事的',
//                'share_img' => '/img/share_index/danggao.jpg',
//                'share_price' => '128',
//                'share_user_name' => '甜欣',
//                'share_vote' => 132,
//                'share_user_id' => 813896,
//                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_s/060eced7063_0807.jpg'
//            ),
            '326' => array(
                'share_id' => 326,
                'share_name' => '东北五常稻花香大米五常当地米厂专供',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/965ba48b2d2_0818.jpg',
                'share_price' => '158',
                'share_user_name' => '王谷丹',
                'share_vote' => 187,
                'share_user_id' => 1388,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_928e6bec43ee9674c9abbcf7ce7eae61.jpg'
            ),
            '350' => array(
                'share_id' => 350,
                'share_name' => '生态农庄之康陵村农户家自产小米@棒渣',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/55d76e0b1e3_0818.jpg',
                'share_price' => '26-36',
                'share_user_name' => '樱花',
                'share_vote' => 197,
                'share_user_id' => 810684,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
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
            '328' => array(
                'share_id' => 328,
                'share_name' => '天福号山地散养有机柴鸡蛋',
                'share_img' => '/img/share_index/jidang.jpg',
                'share_price' => '24.8',
                'share_user_name' => '朋友说小妹',
                'share_vote' => 203,
                'share_user_id' => 711503,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
            ),
            '329' => array(
                'share_id' => 329,
                'share_name' => '天福号山地散养油鸡',
                'share_img' => '/img/share_index/jirou.jpg',
                'share_price' => '118',
                'share_user_name' => '朋友说小妹',
                'share_vote' => 203,
                'share_user_id' => 711503,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
            ),
        );
        return $product;
    }
}