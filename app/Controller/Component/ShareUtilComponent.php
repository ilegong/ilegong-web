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

    public function save_rebate_log($recommend, $clicker) {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $history_log = $rebateTrackLogM->find('first', array(
            'conditions' => array(
                'sharer' => $recommend,
                'clicker' => $clicker,
                'order_id' => 0
            )
        ));
        if (!empty($history_log)) {
            return $history_log['RebateTrackLog']['id'];
        }
        $rebate_log = array('sharer' => $recommend, 'clicker' => $clicker, 'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'));
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
            'id' => $id
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
        $user_nicknames = $this->WeshareBuy->get_users_nickname($user_ids);
        $recommend_open_ids = $this->WeshareBuy->get_open_ids(array($recommend));
        $title = $user_nicknames[$recommend] . '，' . $user_nicknames[$order_creator] . '购买了你推荐的' . $user_nicknames[$share_creator] . $weshareInfo['title'] . '，获得返利回馈。';
        $detail_url = $this->WeshareBuy->get_sharer_detail_url($recommend);
        $order_id = $order['Order']['id'];
        $order_money = $rebateData['order_price'];
        $rebate_money = $rebateData['rebate_money'];
        $pay_time = $order['Order']['created'];
        $this->Weixin->send_rebate_template_msg($recommend_open_ids[$recommend], $detail_url, $order_id, $order_money, $pay_time, $rebate_money, $title);
    }

    /**
     * @return array
     * index product
     */
    public function get_share_index_product() {
        $product = array(
            '413' => array(
                'share_id' => 413,
                'share_name' => '澳洲芦柑',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/c436e3c5c8b_0829.jpg',
                'share_price' => '139',
                'share_user_name' => '小宝妈',
                'share_vote' => 150,
                'share_user_id' => 811917,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
            ),
            '422' => array(
                'share_id' => 422,
                'share_name' => '天津茶淀玫瑰香葡萄,8小时极致新鲜',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/f16a8e0a80e_0824.jpg',
                'share_price' => '79.9',
                'share_user_name' => '刘忠立',
                'share_vote' => 53,
                'share_user_id' => 801447,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_s/c0a73f0653b_0822.jpg'
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
//            '414'=>array(
//                'share_id' => 414,
//                'share_name' => '平谷后北宫村当季精品桃',
//                'share_img' => '/img/share_index/datao.jpg',
//                'share_price' => '80-100',
//                'share_user_name' => '小宝妈',
//                'share_vote' => 150,
//                'share_user_id' => 811917,
//                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
//            ),
            '357' => array(
                'share_id' => 357,
                'share_name' => '像太阳一样的猕猴桃～浦江红心猕猴桃',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/594cea5464a_0821.jpg',
                'share_price' => '77',
                'share_user_name' => '盛夏',
                'share_vote' => 95,
                'share_user_id' => 708029,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
            ),
            '385' => array(
                'share_id' => 385,
                'share_name' => '[内蒙古土默特]大红李子，天然纯绿色，顺丰包邮',
                'share_img' => '/img/share_index/lizi.jpg',
                'share_price' => '78',
                'share_user_name' => '忠义',
                'share_vote' => 99,
                'share_user_id' => 816006,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/1ef4b665fec_0807.jpg'
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
            '389' => array(
                'share_id' => 389,
                'share_name' => '佳沛奇异果',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/5380268e014_0826.jpg',
                'share_price' => '66',
                'share_user_name' => '朋友说小妹',
                'share_vote' => 203,
                'share_user_id' => 711503,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
            ),
            '390' => array(
                'share_id' => 390,
                'share_name' => '佳沛金果',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/c0982711918_0826.jpg',
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