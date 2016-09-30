<?php

// 跟分享相关的功能，请挪至WesharesComponent
class ShareUtilComponent extends Component
{

    var $name = 'ShareUtil';

    var $normal_order_status = array(ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY);

    public $components = array('Weixin', 'WeshareBuy', 'RedisQueue', 'DeliveryTemplate', 'ShareAuthority', 'SharePush', 'ChatUtil');

    var $query_user_fields = array('id', 'nickname', 'image', 'wx_subscribe_status', 'description', 'is_proxy', 'avatar', 'label');


    public function get_fans_detail($limit, $page, $uid, $sharer_id)
    {
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', [
            'conditions' => [
                'Order.creator' => $uid,
                'Order.brand_id' => $sharer_id,
                'Order.type' => ORDER_TYPE_WESHARE_BUY,
                'Order.status >' => ORDER_STATUS_WAITING_PAY
            ],
            'joins' => [
                [
                    'table' => 'weshares',
                    'alias' => 'Weshare',
                    'type' => 'left',
                    'conditions' => [
                        'Weshare.id = Order.member_id',
                    ],
                ]
            ],
            'fields' => ['Order.id', 'Order.status', 'Order.total_all_price', 'Order.created', 'Weshare.id', 'Weshare.title', 'Weshare.default_image'],
            'limit' => $limit,
            'page' => $page,
            'order' => 'Order.id DESC'
        ]);
        $result = [];
        foreach ($orders as $order_item) {
            $result[] = [
                'order_id' => $order_item['Order']['id'],
                'order_status' => $order_item['Order']['status'],
                'order_total_all_price' => $order_item['Order']['total_all_price'],
                'order_created' => $order_item['Order']['created'],
                'weshare_id' => $order_item['Weshare']['id'],
                'weshare_title' => $order_item['Weshare']['title'],
                'weshare_default_image' => $order_item['Weshare']['default_image']
            ];
        }
        return $result;
    }


    public function get_fans_info_list_by_sql($limit, $page, $keyword, $sharer_id)
    {
        $offset = ($page - 1) * $limit;
        $sql = "select cu.id, cu.nickname, cu.image, cu.avatar, ifnull(order_summary.o_count,0) as order_count, format(ifnull(total_fee, 0),2) as order_total_fee from cake_user_relations as cur ";
        $sql .= "left join (select creator as o_creator, count(id) as o_count, sum(total_all_price) as total_fee from cake_orders where brand_id=$sharer_id and type=9 and status > ".ORDER_STATUS_WAITING_PAY." and status !=".ORDER_STATUS_CANCEL." group by creator) as order_summary on order_summary.o_creator = cur.follow_id ";
        $sql .= "left join cake_users as cu on cu.id=cur.follow_id ";
        $sql .= "where cur.user_id=$sharer_id and cur.deleted=0 ";
        if (!empty($keyword)) {
            $sql .= "and cu.nickname like '%$keyword%'";
        }
        $sql .= " group by cur.follow_id order by order_summary.o_count desc limit $offset,$limit";
        $userRelationM = ClassRegistry::init('UserRelation');
        $data = $userRelationM->query($sql);
        $result = [];
        foreach ($data as $data_item) {
            $result[] = [
                'id' => $data_item['cu']['id'],
                'nickname' => $data_item['cu']['nickname'],
                'image' => get_user_avatar($data_item['cu']),
                'order_count' => $data_item[0]['order_count'],
                'total_fee' => $data_item[0]['order_total_fee']
            ];
        }
        return $result;
    }

//    /**
//     * @param $limit
//     * @param $page
//     * @param $keyword
//     * @param $sharer_id
//     * @return array
//     * 团长获取粉丝数据
//     */
//    public function get_fans_info_list($limit, $page, $keyword, $sharer_id)
//    {
//        $userRelationM = ClassRegistry::init('UserRelation');
//        $orderM = ClassRegistry::init('Order');
//        $cond = [
//            'UserRelation.user_id' => $sharer_id,
//            'UserRelation.deleted' => DELETED_NO
//        ];
//        if (!empty($keyword)) {
//            $cond['User.nickname like '] = '%' . $keyword . '%';
//        }
//        $users = $userRelationM->find('all', [
//            'conditions' => $cond,
//            'joins' => [
//                [
//                    'table' => 'users',
//                    'alias' => 'User',
//                    'type' => 'inner',
//                    'conditions' => [
//                        'User.id = UserRelation.follow_id',
//                    ],
//                ],
//            ],
//            'order' => ['UserRelation.id DESC'],
//            'limit' => $limit,
//            'page' => $page,
//            'fields' => ['User.id', 'User.nickname', 'User.image', 'User.avatar', 'UserRelation.id']
//        ]);
//        $user_list = [];
//        $user_ids = [];
//        foreach ($users as $user_item) {
//            $tmp_item = $user_item['User'];
//            $user_ids[] = $tmp_item['id'];
//            $tmp_item['relation_id'] = $user_item['UserRelation']['id'];
//            $user_list[] = $tmp_item;
//        }
//        $orderSummary = $orderM->find('all', [
//            'conditions' => [
//                'brand_id' => $sharer_id,
//                'creator' => $user_ids,
//                'status > ' => ORDER_STATUS_WAITING_PAY,
//                'type' => ORDER_TYPE_WESHARE_BUY
//            ],
//            'group' => 'creator',
//            'fields' => ['count(id) as order_count', 'format(sum(total_all_price),2) as total_fee', 'creator']
//        ]);
//        $orderSummary = Hash::combine($orderSummary, '{n}.Order.creator', '{n}.0');
//        foreach ($user_list as &$item) {
//            $order_count = empty($orderSummary[$item['id']]['order_count']) ? 0 : $orderSummary[$item['id']]['order_count'];
//            $total_fee = empty($orderSummary[$item['id']]['total_fee']) ? 0 : $orderSummary[$item['id']]['total_fee'];
//            $item['order_count'] = strval($order_count);
//            $item['total_fee'] = strval($total_fee);
//            $item['image'] = get_user_avatar($item);
//        }
//        return $user_list;
//    }

    /**
     * @param $uid
     * 获取用户昨日的浏览量
     */
    public function get_yesterday_view_count($uid)
    {
        $sharerStaticsDataM = ClassRegistry::init('SharerStaticsData');
        $data = $sharerStaticsDataM->find('all', [
            'conditions' => [
                'sharer_id' => $uid
            ],
            'order' => ['id DESC'],
            'limit' => 2
        ]);
        return $data[0]['SharerStaticsData']['view_count'] - $data[1]['SharerStaticsData']['view_count'];
    }

    /**
     * @param $uid
     * @return array
     * 获取粉丝的增量
     */
    public function get_yesterday_fans_incremental($uid)
    {
        $userSubLogM = ClassRegistry::init('UserSubLog');
        $today = date('Y-m-d') . ' 00:00:00';
        $yesterday = date("Y-m-d H:i:s", strtotime($today . " -1 day"));
        $queryResult = $userSubLogM->find('all', [
            'conditions' => [
                'user_id' => $uid,
                'created >' => $today,
                'created <' => $yesterday
            ],
            'group' => 'type',
            'fields' => ['type', 'count(id) as `log_count`']
        ]);
        $sub_count = 0;
        $un_sub_count = 0;
        foreach ($queryResult as $item) {
            $type = $item['UserSubLog']['type'];
            if ($type == USER_SUB_LOG_TYPE) {
                $sub_count = $item['UserSubLog']['log_count'];
            }
            if ($type == USER_UN_SUB_LOG_TYPE) {
                $un_sub_count = $item['UserSubLog']['log_count'];
            }
        }
        return ['sub_count' => $sub_count, 'un_sub_count' => $un_sub_count];
    }

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
        $this->RedisQueue->add_tasks('share', "/weshares/process_send_new_share_msg/" . $weshare_id . '/' . $pageCount . '/' . $pageSize);
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

    /**
     * check_user_relation
     * 检测用户之间的关系, 没有关注的时候, 返回的是true
     *
     * @param mixed $userId 被关注人ID
     * @param mixed $followId 粉丝身份的用户ID
     * @access public
     * @return boolean 没关注返回真, 否则假
     */
    public function check_user_relation($userId, $followId)
    {
        if ($userId == $followId) {
            return false;
        }
        $userRelationM = ClassRegistry::init('UserRelation');
        $relation = $userRelationM->hasAny([
            'user_id' => $userId,
            'follow_id' => $followId,
            'deleted' => DELETED_NO
        ]);
        return !$relation;
    }

    /**
     * check_user_relations
     * 检测用户有没有关注人
     *
     * @param mixed $followId 粉丝身份的用户ID
     * @access public
     * @return boolean 没关注返回真, 否则假
     */
    public function check_user_relations($followId)
    {
        $userRelationM = ClassRegistry::init('UserRelation');
        return $userRelationM->hasAny(['follow_id' => $followId]);
    }

    public function delete_relation($sharer_id, $user_id)
    {
        $userRelationM = ClassRegistry::init('UserRelation');
        $userRelationM->updateAll(array('deleted' => DELETED_YES), array('user_id' => $sharer_id, 'follow_id' => $user_id));
        $userSubLog = ClassRegistry::init('UserSubLog');
        $userSubLog->save(['user_id' => $sharer_id, 'follow_id' => $user_id, 'type' => USER_UN_SUB_LOG_TYPE, 'created' => date('Y-m-d H:i:s')]);
    }

    /**
     * 新版本的购买之后关注逻辑
     *
     * 1. 新用户第一次购买后自动关注团长, 界面无提醒信息.
     * 2. 非新用户购买后, 在未关注该团长的情况下, 提醒用户是否关注该团长, 点击
     *    [关注]则关注团长, 点击[取消]则不关注
     *
     * @param mixed $sharer 出售人ID
     * @param mixed $consumer 消费者ID
     * @access public
     * @return array 返回数组, type字段表示上述两种情况, msg是辅助消息.
     */
    public function save_relation_new($sharer, $consumer)
    {
        if (!$this->check_user_relations($consumer)) {
            // 1. 没有关注任何人, 默认关注
            $this->log("User " . $consumer . ' does not follow anyone, now follows ' . $sharer . ' in default', LOG_INFO);
            $this->save_relation($sharer, $consumer);
            $ret = [
                'type' => 1,
                'msg' => 'Followed the saler by default.',
            ];
        } else {
            // 2. 提醒用户是否关注该团长
            $ret = [
                'type' => 2,
                'msg' => 'Do you want to follow this saler?',
            ];
        }

        return $ret;
    }

    public function save_relation($sharer_id, $user_id, $type = 'Buy')
    {

        if (empty($sharer_id) || empty($user_id)) {
            return 0;
        }
        $userRelationM = ClassRegistry::init('UserRelation');
        $userSubLog = ClassRegistry::init('UserSubLog');
        $has_relation = $userRelationM->hasAny(['user_id' => $sharer_id, 'follow_id' => $user_id]);
        if (!$has_relation) {
            $userRelationM->saveAll(array('user_id' => $sharer_id, 'follow_id' => $user_id, 'type' => $type,'remark' => '', 'created' => date('Y-m-d H:i:s')));
        } else {
            $userRelationM->updateAll(array('deleted' => DELETED_NO), array('user_id' => $sharer_id, 'follow_id' => $user_id));
        }
        $userSubLog->save(['user_id' => $sharer_id, 'follow_id' => $user_id, 'type' => USER_SUB_LOG_TYPE, 'created' => date('Y-m-d H:i:s')]);
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

            $ship_fee = $order['Order']['ship_fee'];
            $total_price = $order['Order']['total_all_price'];
            $ship_fee = round($ship_fee / 100, 2);
            $canRebateMoney = $total_price - $ship_fee;
            $percent = $rebatePercentData['ProxyRebatePercent']['percent'];
            $rebate_money = ($canRebateMoney * $percent) / 100;
            $rebate_money = round($rebate_money, 2);
            $rebate_money = $rebate_money * 100;
            $rebateTrackLogM->updateAll(array('is_paid' => 1, 'updated' => '\'' . date('Y-m-d H:i:s') . '\'', 'rebate_money' => $rebate_money), array('id' => $id, 'order_id' => $order_id));
            $rebate_track_log_has_rebate = $rebateTrackLog['RebateTrackLog']['is_rebate'] == 1;
            if (!$rebate_track_log_has_rebate) {
                $add_rebate_log_result = $this->add_rebate_log($rebateTrackLog['RebateTrackLog']['sharer'], $rebate_money, USER_REBATE_MONEY_GOT, $order_id);
                if ($add_rebate_log_result) {
                    $rebateTrackLogM->updateAll(['is_rebate' => 1], ['id' => $id, 'order_id' => $order_id]);
                }
            }
            return array('rebate_money' => $rebate_money, 'order_price' => $total_price, 'recommend' => $rebateTrackLog['RebateTrackLog']['sharer']);
        }
    }

    public function add_rebate_log($uid, $money, $reason, $order_id)
    {
        if ($money == 0) {
            return true;
        }
        $rebateLogM = ClassRegistry::init('RebateLog');
        $userM = ClassRegistry::init('User');
        $rebateLogM->id = null;
        $result = $rebateLogM->save_rebate_log($uid, $money, $order_id, $reason);
        if ($result) {
            return $userM->add_rebate_money($uid, $money);
        }
        return false;
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
        $userM = ClassRegistry::init('User');
        $allRebateMoney = $userM->get_rebate_money($user_id);
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
        $userLevel = $this->get_user_level($uid);
        return $userLevel['data_value'] >= PROXY_USER_LEVEL_VALUE;
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
        //$this->WeshareBuy->subscribe_sharer($recommend, $order_creator, 'RECOMMEND');
        //$this->WeshareBuy->subscribe_sharer($share_creator, $order_creator, 'BUY');
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

    /**
     * @param $order_id
     * save user use rebate log
     */
    public function add_use_rebate_log($order_id)
    {
        $orderM = ClassRegistry::init('Order');
        $order = $orderM->find('first', [
            'conditions' => [
                'id' => $order_id
            ],
            'fields' => ['id', 'creator', 'applied_rebate']
        ]);
        $rebate = $order['Order']['applied_rebate'];
        $uid = $order['Order']['creator'];
        $order_id = $order['Order']['id'];
        $rebateLogM = ClassRegistry::init('RebateLog');
        $hasBind = $rebateLogM->has_bind_rebate_log($uid, $order_id, USER_REBATE_MONEY_USE);
        if ($rebate > 0 && !$hasBind) {
            $userM = ClassRegistry::init('User');
            $rebateLogM->save_rebate_log($uid, -$rebate, $order_id, USER_REBATE_MONEY_USE);
            $userM->add_rebate_money($uid, -$rebate);
        }
    }

    /**
     * @param $order_id
     * @param $desc
     * save user use score log
     */
    public function add_use_score_log($order_id, $desc)
    {
        $desc = remove_emoji($desc);
        $orderM = ClassRegistry::init('Order');
        $order = $orderM->find('first', [
            'conditions' => [
                'id' => $order_id
            ],
            'fields' => ['id', 'creator', 'applied_score']
        ]);
        $score = $order['Order']['applied_score'];
        $uid = $order['Order']['creator'];
        $order_id = $order['Order']['id'];
        $scoreM = ClassRegistry::init('Score');
        $hasBind = $scoreM->has_bind_score($uid, $order_id, SCORE_ORDER_SPENT);
        if ($score > 0 && !$hasBind) {
            $userM = ClassRegistry::init('User');
            $scoreM->spent_score_by_single_order($uid, $score, $order_id, $desc);
            $userM->add_score($uid, -$score);
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
     * 从普通分享上产品街; 从产品街开团；产品街的分享重新开团；手工复制；普通分享重新开团;
     * @return array clone一份， 指定用户ID， 指定的地址， 类型， 状态
     * clone一份， 指定用户ID， 指定的地址， 类型， 状态
     * @internal param 拼团地址 $address
     * @internal param $address_remarks
     */
    public function cloneShare($shareId, $uid = null, $type = null, $share_status = 0, $share_limit = null)
    {
        $WeshareM = ClassRegistry::init('Weshare');
        $dataSource = $WeshareM->getDataSource();
        $dataSource->begin();
        $shareInfo = $WeshareM->find('first', array(
            'conditions' => array(
                'id' => $shareId
            )
        ));
        $shareInfo = $shareInfo['Weshare'];
        $new_share_type = $shareInfo['type'];
        if (!empty($type)) {
            $new_share_type = $type;
        }

        if ($new_share_type == SHARE_TYPE_POOL_SELF) {
            // 从普通分享(或者复制的一个分享)上产品街
            $root_share_id = 0;
        } else {
            // 默认的分享（重新开团；或者手工复制, 从产品街开团）
            if ($shareInfo['root_share_id'] == 0) {
                $root_share_id = $shareId;
            } else {
                $root_share_id = $shareInfo['root_share_id'];
            }
        }

        try {
            if (!$this->check_delivery($shareId)) {
                throw new Exception("Failed to clone weshare " . $shareId . ": delivery data error");
            }

            $WeshareM->id = null;
            $shareInfo['id'] = null;
            $shareInfo['created'] = date('Y-m-d H:i:s');
            $shareInfo['status'] = $share_status; //分享状态
            $shareInfo['settlement'] = WESHARE_SETTLEMENT_NO; //打款状态为未打款
            $shareInfo['type'] = $new_share_type;

            $shareInfo['refer_share_id'] = $shareId;
            $shareInfo['root_share_id'] = $root_share_id;

            if (!empty($uid)) {
                $shareInfo['creator'] = $uid;
                //检查并设置用户团长
                $this->check_and_save_default_level($uid);
            }
            //order status offline address id
            $newShareInfo = $WeshareM->save($shareInfo);
            if (!$newShareInfo) {
                throw new Exception("Failed to clone weshare " . $shareId . ": db error");
            }

            $newShareId = $newShareInfo['Weshare']['id'];
            //clone product
            $this->cloneShareProduct($newShareId, $newShareInfo['Weshare']['refer_share_id'], $share_limit);
            //clone address
            $this->cloneShareAddresses($newShareId, $newShareInfo['Weshare']['refer_share_id']);
            //clone ship setting
            $this->cloneShareShipSettings($newShareId, $newShareInfo['Weshare']['refer_share_id']);
            //clone rebate set
            $this->cloneShareRebateSet($newShareId, $newShareInfo['Weshare']['refer_share_id']);
            //clone share delivery template
            $this->cloneDeliveryTemplate($newShareId, $newShareInfo['Weshare']['refer_share_id'], $newShareInfo['Weshare']['creator']);

            $this->authorize_weshare_after_cloning($newShareInfo);

            Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $newShareInfo['Weshare']['creator'], '');
            $dataSource->commit();
            return array('shareId' => $newShareId, 'success' => true);
        } catch (Exception $e) {
            $this->log($e->getMessage(), LOG_ERR);
            $dataSource->rollback();
            return array('success' => false);
        }
    }

    private function cloneShareProduct($new_share_id, $old_share_id, $share_limit)
    {
        $WeshareProductM = ClassRegistry::init('WeshareProduct');
        $shareProducts = $WeshareProductM->find('all', array(
            'conditions' => array(
                'weshare_id' => $old_share_id
            )
        ));
        if (empty($shareProducts)) {
            throw new Exception("No share products found for refer share id " . $old_share_id);
        }

        $newProducts = array();
        foreach ($shareProducts as $itemShareProduct) {
            $itemShareProduct = $itemShareProduct['WeshareProduct'];
            $itemShareProduct['origin_product_id'] = $itemShareProduct['id'];
            $itemShareProduct['id'] = null;
            $itemShareProduct['weshare_id'] = $new_share_id;
            if ($share_limit !== null) {
                $itemShareProduct['limit'] = $share_limit;
            }
            $newProducts[] = $itemShareProduct;
        }
        $WeshareProductM->id = null;
        $WeshareProductM->saveAll($newProducts);
        return true;
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
     * @param $uid
     */
    private function cloneDeliveryTemplate($new_share_id, $old_share_id, $uid)
    {
        $WeshareDeliveryTemplateM = ClassRegistry::init('WeshareDeliveryTemplate');
        $WeshareTemplateRegionM = ClassRegistry::init('WeshareTemplateRegion');
        $deliveryTemplates = $WeshareDeliveryTemplateM->find('all', array(
            'conditions' => array(
                'weshare_id' => $old_share_id
            )
        ));
        if (!empty($deliveryTemplates)) {
            $newDeliveryTemplates = array();
            foreach ($deliveryTemplates as $deliveryTemplate) {
                $itemDeliveryTemplate = $deliveryTemplate['WeshareDeliveryTemplate'];
                $itemDeliveryTemplate['id'] = null;
                $itemDeliveryTemplate['weshare_id'] = $new_share_id;
                $itemDeliveryTemplate['user_id'] = $uid;
                $newDeliveryTemplates[] = $itemDeliveryTemplate;
            }
            $WeshareDeliveryTemplateM->saveAll($newDeliveryTemplates);
            $templateRegions = $WeshareTemplateRegionM->find('all', array(
                'conditions' => array(
                    'weshare_id' => $old_share_id
                )
            ));
            $newTemplateRegions = array();
            foreach ($templateRegions as $templateRegion) {
                $itemTemplateRegion = $templateRegion['WeshareTemplateRegion'];
                $itemTemplateRegion['id'] = null;
                $itemTemplateRegion['weshare_id'] = $new_share_id;
                $itemTemplateRegion['creator'] = $uid;
                $newTemplateRegions[] = $itemTemplateRegion;
            }
            $WeshareTemplateRegionM->saveAll($newTemplateRegions);
        }
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
        $optLogData = array('obj_creator' => $share_info['creator'], 'user_id' => $userId, 'obj_type' => OPT_LOG_SHARE_RECOMMEND, 'obj_id' => $shareId, 'event_id' => $shareId, 'created' => $now, 'memo' => $title, 'reply_content' => $memo, 'thumbnail' => $shareImg[0]);
        $this->saveOptLog($optLogData);
        $sendResult = $this->WeshareBuy->send_recommend_msg($userId, $shareId, $memo);
        if ($sendResult['success']) {
            $this->notify_sharer_recommend($userId, $shareId);
        }
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
            'obj_creator' => $user_id,
            'obj_type' => OPT_LOG_CREATE_SHARE,
            'obj_id' => $share_id,
            'event_id' => $share_id,
            'created' => date('Y-m-d H:i:s'),
            'memo' => $memo,
            'thumbnail' => $thumbnail
        );
        //黑名单用户不显示 或者 粉丝小于50
        if (is_blacklist_user($user_id) || $this->get_user_level_by_fans_count($user_id) == 0 || is_test_user($user_id)) {
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
            //$user_level['data_value'] = $user_level['data_value'] + 1;
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
            'event_id' => $tag_id,
            'created' => date('Y-m-d H:i:s'),
            'memo' => $memo,
            'thumbnail' => $thumbnail,
            'obj_creator' => $user_id,
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
            'event_id' => $order_id,
            'memo' => $memo,
            'reply_content' => $order_info,
            'thumbnail' => $thumbnail,
            'obj_creator' => $share_info['creator']
        );
        //me test account don't show
        if (is_test_user($share_info['creator'])) {
            $optData['deleted'] = DELETED_YES;
        }
        //clear order count cache
        $this->clear_share_cache($share_id, $share_info['creator']);
        $this->saveOptLog($optData);
        //推送支付消息
        try {
            $this->SharePush->push_buy_msg($optData, $share_info);
        } catch (Exception $e) {
            $this->log('push buy msg error data ' . json_encode($optData) . 'msg ' . $e->getMessage());
        }
    }

    /**
     * @param $shareId
     * @param $groupId
     * @param $order_creator
     * @return bool
     * 通知环信
     */
    public function send_buy_msg_to_hx($shareId, $groupId, $order_creator)
    {
        //none group
        if ($groupId == 0) {
            return false;
        }
        try {
            $weshareM = ClassRegistry::init('Weshare');
            $orderM = ClassRegistry::init('Order');
            $chatGroupM = ClassRegistry::init('ChatGroup');
            $weshare = $weshareM->find('first', [
                'conditions' => ['id' => $shareId],
                'fields' => ['id', 'title', 'description', 'status']
            ]);
            $weshare = $weshare['Weshare'];
            $carts = $orderM->find('all', [
                'conditions' => [
                    'Order.member_id' => $shareId,
                    'Order.type' => ORDER_TYPE_WESHARE_BUY,
                    'not' => ['Order.status' => ORDER_STATUS_WAITING_PAY]
                ],
                'recursive' => 1,
                'joins' => [
                    [
                        'alias' => 'User',
                        'table' => 'cake_users',
                        'type' => 'left',
                        'conditions' => 'User.id = Order.creator'
                    ]
                ],
                'order' => 'Order.id ASC',
                'fields' => ['Order.id', 'User.nickname', 'User.image', 'User.avatar', 'User.id']
            ]);
            $weshare['description'] = remove_emoji(mb_substr(strip_tags(preg_replace('/\s+/', '', $weshare['description'])), 0, 60, "UTF8")) . '...';
            $weshare['title'] = remove_emoji(preg_replace('/\s+/', '', $weshare['title']));
            $sender = [];
            $list = '';
            $i = 0;
            $len = count($carts);
            foreach ($carts as $item) {
                $orderCarts = $item['Cart'];
                $user = $item['User'];
                if ($user['id'] == $order_creator) {
                    $sender = ['avatar' => get_user_avatar($user), 'userId' => $user['id'], 'nickname' => $user['nickname']];
                }
                $ca = [];
                foreach ($orderCarts as $ci) {
                    $ca[] = $ci['name'] . 'X' . $ci['num'];
                }
                $list = $list . ($i + 1) . '、' . $user['nickname'] . ' ' . implode(',', $ca);
                if ($i != $len - 1) {
                    $list = $list . '$$';
                }
                $i++;
            }

            $cg = $chatGroupM->findById($groupId);
            $hx_group_id = $cg['ChatGroup']['hx_group_id'];
            $ext = array_merge([], $sender);
            $ext['ownerId'] = $order_creator;
            $ext['customType'] = '1';
            $ext['shareId'] = $shareId;
            $ext['orderList'] = $list;
            $ext['shareTitle'] = $weshare['title'];
            $ext['shareDesc'] = $weshare['description'];
            $result = $this->ChatUtil->send_msg(HX_CHAT_GROUP_TARGET_TYPE, [$hx_group_id], $weshare['title'], $order_creator, $ext);
            $this->log('paid success send to hx msg result ' . json_encode($result));
        } catch (Exception $e) {
            $this->log('send pay success msg to hx error shareId ' . $shareId . ' groupId ' . $groupId . ' creator ' . $order_creator . ' error ' . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * @param $share_id
     * @param $share_creator
     * 清除分享相关的缓存
     */
    private function clear_share_cache($share_id, $share_creator)
    {
        delete_redis_data_by_key('_' . $share_id);
        delete_redis_data_by_key(USER_RECOMMEND_WESHARES_CACHE_KEY . '_' . $share_creator);
//        //check should clear child share cache
        //param  $is_pin_tuan = false
//        if ($is_pin_tuan) {
//            $refer_share_id = $this->ShareUtil->get_share_refer_id($share_id);
//            if ($refer_share_id != $share_id) {
//                Cache::write(SHARE_OFFLINE_ADDRESS_SUMMERY_DATA_CACHE_KEY . '_' . $refer_share_id, '');
//                Cache::write(SHARE_OFFLINE_ADDRESS_BUY_DATA_CACHE_KEY . '_' . $refer_share_id, '');
//                Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $refer_share_id . '_1_1', '');
//                Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $refer_share_id . '_0_1', '');
//                Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $refer_share_id . '_1_0', '');
//                Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $refer_share_id . '_0_0', '');
//            }
//        }
    }

    /**
     * @param $user_id
     * @param $share_id
     * @param $replay_text
     * save comment opt log
     */
    public function save_comment_opt_log($user_id, $share_id, $comment_id, $replay_text)
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
            'event_id' => $comment_id,
            'created' => date('Y-m-d H:i:s'),
            'memo' => $memo,
            'thumbnail' => $thumbnail,
            'reply_content' => $replay_text,
            'obj_creator' => $share_info['creator']
        );
        $this->saveOptLog($optData);
        //推送支付消息
        try {
            $this->SharePush->push_comment_msg($optData, $share_info);
        } catch (Exception $e) {
            $this->log('push comment msg error data ' . json_encode($optData) . 'msg ' . $e->getMessage());
        }
    }

    /**
     * @param $data
     *
     */
    public function saveOptLog($data)
    {
        $optLogM = ClassRegistry::init('OptLog');
        $newOptLogM = ClassRegistry::init('NewOptLog');
        $optLogM->save($data);
        if ($newOptLogM->hasAny(['share_id' => $data['obj_id']])) {
            //update
            $newOptLogM->update(['customer_id' => $data['user_id'], 'data_type_tag' => $data['obj_type'], 'time' => "'" . $data['created'] . "'"], ['share_id' => $data['obj_id']]);
        } else {
            //create new opt log
            $newOptLogData = ['share_id' => $data['obj_id'], 'proxy_id' => $data['obj_creator'], 'customer_id' => $data['user_id'], 'data_type_tag' => $data['obj_type'], 'time' => $data['created'], 'deleted' => 0];
            $newOptLogM->save($newOptLogData);
        }
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
        $orderInfo = $orderM->find('first', array(
            'conditions' => array('id' => $orderId)
        ));
        $weshareId = $orderInfo['Order']['member_id'];
        $refundLog = $refundLogM->find('first', array(
            'conditions' => array(
                'order_id' => $orderId
            )
        ));
        if (empty($refundLog)) {
            $PayLogInfo = $payLogM->find('first', array(
                'conditions' => array(
                    'order_id' => $orderId,
                    'status' => PAYLOG_STATUS_SUCCESS,
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
                'remark' => $refundMark,
                'data_id' => $weshareId
            );
            $refundLogM->save($saveRefundLogData);
        } else {
            $refundLogId = $refundLog['RefundLog']['id'];
            $refundLogM->updateAll(array('refund_fee' => $refundMoney, 'remark' => "'" . $refundMark . "'"), array('id' => $refundLogId));
        }
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
                $optLogData = array('obj_creator' => $group_share['creator'], 'user_id' => $order_creator, 'obj_type' => OPT_LOG_START_GROUP_SHARE, 'obj_id' => $group_share_id, 'created' => $now, 'memo' => $title, 'thumbnail' => $shareImg[0]);
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
                'type' => SHARE_TYPE_GROUP,
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
            $query_address_sql = 'select * from cake_weshare_addresses where weshare_id in (select id from cake_weshares where refer_share_id=' . $share_id . ' and type=' . SHARE_TYPE_GROUP . ')';
            $address_result = $WeshareM->query($query_address_sql);
            $query_order_summery_sql = 'select count(id),member_id from cake_orders where type=' . ORDER_TYPE_WESHARE_BUY . ' and status !=' . ORDER_STATUS_WAITING_PAY . ' and member_id in (select id from cake_weshares where refer_share_id=' . $share_id . ' and type=' . SHARE_TYPE_GROUP . ') group by member_id';
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
        $weshareM->updateAll(array('status' => WESHARE_STATUS_NORMAL), array('id' => $share_id));
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

    public function get_all_share_products($weshare_id)
    {
        $weshareProductM = ClassRegistry::init('WeshareProduct');
        $weshareProducts = $weshareProductM->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id,
                'deleted' => DELETED_NO
            )
        ));
        $weshareProducts = Hash::extract($weshareProducts, '{n}.WeshareProduct');
        return $weshareProducts;
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
            delete_redis_data_by_key(SHARE_ORDER_DATA_CACHE_KEY . '_' . $order['Order']['member_id']);
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
                'type' => SHARE_TYPE_GROUP
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
                'type' => SHARE_TYPE_GROUP
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
                'type' => SHARE_TYPE_GROUP,
                'status' => WESHARE_STATUS_NORMAL
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
            if (!empty($user_open_ids)) {
                foreach ($user_open_ids as $user_id => $user_open_id) {
                    $this->Weixin->send_share_buy_complete_msg($user_open_id, $title, $share_title, $tuan_leader_name, $remark, $detail_url);
                }
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
                    'type' => SHARE_TYPE_GROUP
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
    public function checkCanSendMsg($uid, $share_id, $type)
    {
        $sendMsgLogM = ClassRegistry::init('SendMsgLog');
        if ($sendMsgLogM->hasAny(['sharer_id' => $uid,
            'deleted' => DELETED_NO,
            'data_id' => $share_id,
            'type' => $type,
            'created > ' => date('Y-m-d')])
        ) {
            return array('success' => false, 'msg' => '今天已经发送过该消息');
        }
        if (is_pys_signed_user($uid)) {
            return array('success' => true, 'msg' => '还可以发送很多条消息');
        }
        $limit_count = $this->getSharerMsgLimit($uid);
        $limit_count = $limit_count['limit'];
        if ($limit_count == 0) {
            return array('success' => false, 'msg' => '团长才能发送模板消息');
        }
        $sendMsgCount = $sendMsgLogM->find('count', array(
            'conditions' => array(
                'sharer_id' => $uid,
                'deleted' => DELETED_NO,
                'created > ' => date('Y-m-d')
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

    /**
     * @param $category
     * @return array
     * 根据分类获取产品
     */
    public function get_product_by_category($category)
    {
        $indexProductM = ClassRegistry::init('IndexProduct');
        $data = $indexProductM->find('all', [
            'conditions' => [
                'IndexProduct.tag_id' => $category,
                'IndexProduct.deleted' => DELETED_NO,
            ],
            'fields' => [
                'IndexProduct.*',
                'User.*',
                'UserLevel.*',
                'Weshare.*',
            ],
            'joins' => [
                [
                    'table' => 'users',
                    'alias' => 'User',
                    'conditions' => [
                        'User.id = IndexProduct.share_user_id',
                    ],
                ], [
                    'table' => 'user_levels',
                    'alias' => 'UserLevel',
                    'conditions' => [
                        'UserLevel.data_id = IndexProduct.share_user_id',
                    ],
                ], [
                    'table' => 'weshares',
                    'alias' => 'Weshare',
                    'conditions' => [
                        'Weshare.id = IndexProduct.share_id',
                    ],
                ],
            ],
            'order' => ['sort_val ASC']
        ]);

        $userModel = ClassRegistry::init('User');
        $uid = $_SESSION['Auth']['User']['id'];
        $my_proxy = $userModel->get_my_proxys($uid);

        $level_pool = [
            0 => '分享达人',
            1 => '实习团长',
            2 => '正式团长',
            3 => '优秀团长',
            4 => '高级团长',
            5 => '资深团长',
            6 => '首席团长'
        ];

        $res = [];
        foreach ($data as $v) {
            $level = $v['UserLevel']['data_value'];
            $tmp = $v['IndexProduct'];

            $nickname = $v['User']['nickname'];
            if (mb_strlen($nickname) > 4) {
                $tmp['share_user_nickname'] = mb_substr($nickname, 0, 4) . '...';
            } else {
                $tmp['share_user_nickname'] = $nickname;
            }

            $tmp['share_user_level'] = "L{$level}{$level_pool[$level]}";

            $description = str_replace('<br />', '', $v['Weshare']['description']);
            if (mb_strlen($description) > 110) {
                $tmp['share_description'] = mb_substr($description, 0, 110) . "...";
                $tmp['description_more'] = true;
            } else {
                $tmp['share_description'] = $description;
                $tmp['description_more'] = false;
            }

            $tmp['check_user_relation'] = in_array($v['User']['id'], $my_proxy);
            // 缺少浏览量
            // 1. 报名数
            $tmp['baoming'] = $this->WeshareBuy->get_share_and_all_refer_share_count($v['Weshare']['id'], $v['User']['id']);
            // 2. 浏览数
            $tmp['liulan'] = $v['Weshare']['view_count'];

            $tmp['my_id'] = $uid;

            $res[] = $tmp;
        }

        return $res;
    }

    /**
     * @return mixed
     * 获取banner
     */
    public function get_index_banners()
    {
        $key = INDEX_VIEW_BANNER_CACHE_KEY;
        $cache_data = Cache::read($key);
        if (empty($cache_data)) {
            $carousel = ClassRegistry::init('NewFind')->get_all_carousel();
            Cache::write($key, json_encode($carousel));
            return $carousel;
        }
        return json_decode($cache_data, true);
    }

    /**
     * @return array
     * 首页促销信息
     */
    public function get_index_promotions(){
        $promotions = [
            [
                'banner_img' => 'http://static.tongshijia.com/static/img/index/dzx.png',
                'data' => '6951',
                'type' => "1"
            ],
            [
                'banner_img' => 'http://static.tongshijia.com/static/img/index/yuebing.png',
                'data' => 'http://www.tongshijia.com/articles/moon_cake.html',
                'type' => "0"
            ]
        ];
        return $promotions;
    }

    public function get_index_product($tag_id)
    {
        $key = INDEX_VIEW_PRODUCT_CACHE_KEY . '_' . $tag_id;
        $cache_data = Cache::read($key);
        if (empty($cache_data)) {
            $indexProductM = ClassRegistry::init('IndexProduct');
            $index_products = $indexProductM->find('all', [
                'conditions' => [
                    'IndexProduct.tag_id' => $tag_id,
                    'IndexProduct.deleted' => DELETED_NO,
                    'Weshare.status' => WESHARE_STATUS_NORMAL
                ],
                'fields' => [
                    'IndexProduct.*',
                    'User.*',
                    'UserLevel.*'
                ],
                'joins' => [
                    [
                        'type' => 'left',
                        'table' => 'users',
                        'alias' => 'User',
                        'conditions' => [
                            'User.id = IndexProduct.share_user_id',
                        ],
                    ], [
                        'type' => 'left',
                        'table' => 'user_levels',
                        'alias' => 'UserLevel',
                        'conditions' => [
                            'User.id = UserLevel.data_id',
                        ]
                    ], [
                        'type' => 'left',
                        'table' => 'cake_weshares',
                        'alias' => 'Weshare',
                        'conditions' => [
                            'Weshare.id = IndexProduct.share_id'
                        ]
                    ]
                ],
                'order' => array('sort_val ASC')
            ]);
            Cache::write($key, json_encode($index_products));
            return $index_products;
        }

        return json_decode($cache_data, true);
    }

    // api: return a list of index products(id, share_id...)
    public function index_products($tag_id)
    {
        $key = INDEX_PRODUCTS_BY_TAG_CACHE_KEY . '_' . $tag_id;
        $cache_data = Cache::read($key);
        if (!empty($cache_data)) {
            return json_decode($cache_data, true);
        }

        $indexProductM = ClassRegistry::init('IndexProduct');
        $index_products = $indexProductM->find('all', [
            'conditions' => [
                'IndexProduct.tag_id' => $tag_id,
                'IndexProduct.deleted' => DELETED_NO,
            ],
            'fields' => [
                'IndexProduct.id', 'IndexProduct.share_id'
            ]
        ]);
        Cache::write($key, json_encode($index_products));
        return $index_products;
    }

    // api: summary(order count, view count, recent orders and creators)
    //分享的一些汇总数据
    public function get_index_product_summary($share_id)
    {
        $key = INDEX_PRODUCT_SUMMARY_CACHE_KEY . '_' . $share_id;
        $cache_data = Cache::read($key);
        if (!empty($cache_data)) {
            return json_decode($cache_data, true);
        }

        $OrderM = ClassRegistry::init('Order');
        $WeshareM = ClassRegistry::init('Weshare');
        $commentM = ClassRegistry::init('Comment');
        $related_share_ids = $WeshareM->get_relate_share($share_id);

        $order_count = $OrderM->find('count', array(
            'conditions' => array('status' => [ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY], 'member_id' => $related_share_ids, 'type' => ORDER_TYPE_WESHARE_BUY),
        ));
        $view_count = $WeshareM->find('first', array(
            'fields' => array('Weshare.view_count'),
            'conditions' => array('id' => $share_id),
        ));
        $comment_count = $commentM->find('count', array(
            'conditions' => array(
                'data_id' => $related_share_ids,
                'parent_id' => 0,
                'order_id >' => 0,
                'type' => COMMENT_SHARE_TYPE
            )
        ));
        $orders_and_creators = $OrderM->find('all', [
            'conditions' => [
                'Order.member_id' => $related_share_ids,
                'Order.status' => [ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY],
            ],
            'fields' => array('Order.id', 'User.id', 'User.nickname', 'User.avatar', 'User.image'),
            'joins' => [
                [
                    'table' => 'users',
                    'alias' => 'User',
                    'conditions' => [
                        'User.id = Order.creator',
                    ],
                ]
            ],
            'order' => ['Order.id DESC'],
            'limit' => 5
        ]);
        $orders_and_creators = Hash::extract($orders_and_creators, '{n}.User');
        $orders_and_creators = array_map('map_user_avatar3', $orders_and_creators);
        $summary = array('view_count' => $view_count['Weshare']['view_count'], 'order_count' => strval($order_count), 'comment_count' => $comment_count, 'orders_and_creators' => $orders_and_creators);
        Cache::write($key, json_encode($summary));

        return $summary;
    }

    private function query_share_detail($weshare_id)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $weshareAddressM = ClassRegistry::init('WeshareAddress');
        $weshareShipSettingM = ClassRegistry::init('WeshareShipSetting');
        $proxyRebatePercentM = ClassRegistry::init('ProxyRebatePercent');
        $userM = ClassRegistry::init('User');
        $weshareInfo = $weshareM->find('first', array(
            'conditions' => array(
                'id' => $weshare_id
            )
        ));
        $weshareAddresses = $weshareAddressM->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id,
                'deleted' => DELETED_NO
            )
        ));
        $weshareShipSettings = $weshareShipSettingM->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id
            )
        ));
        $proxy_share_percent = $proxyRebatePercentM->find('first', array(
            'conditions' => array(
                'share_id' => $weshare_id,
                'deleted' => DELETED_NO,
                'status' => PUBLISH_YES
            )
        ));
        //$sharer_tags = $this->get_tags($weshareInfo['Weshare']['creator'], $weshareInfo['Weshare']['refer_share_id']);
        //$sharer_tags_list = $this->get_tags_list($weshareInfo['Weshare']['creator']);
        $weshareShipSettings = Hash::combine($weshareShipSettings, '{n}.WeshareShipSetting.tag', '{n}.WeshareShipSetting');
        $creatorInfo = $userM->find('first', array(
            'conditions' => array(
                'id' => $weshareInfo['Weshare']['creator']
            ),
            'recursive' => 1, //int
            'fields' => $this->query_user_fields,
        ));
        $creatorInfo = $creatorInfo['User'];
        //reset user image
        $creatorInfo['image'] = get_user_avatar($creatorInfo);
        $creatorLevel = $this->get_user_level($weshareInfo['Weshare']['creator']);
        $creatorInfo['level'] = $creatorLevel;
        $weshareProducts = $this->get_all_share_products($weshare_id);
        //show break line
        if (check_weshare_detail_is_not_html($weshareInfo['Weshare']['description'])) {
            $weshareInfo['Weshare']['description'] = str_replace(array("\r\n", "\n", "\r"), '<br />', $weshareInfo['Weshare']['description']);
        }
        $weshareInfo = $weshareInfo['Weshare'];
        //$weshareInfo['tags'] = $sharer_tags;
        //$weshareInfo['tags_list'] = $sharer_tags_list;
        $weshareInfo['addresses'] = Hash::extract($weshareAddresses, '{n}.WeshareAddress');
        $weshareInfo['products'] = $weshareProducts;
        $weshareInfo['creator'] = $creatorInfo;
        $weshareInfo['ship_type'] = $weshareShipSettings;
        $weshareInfo['images'] = array_map('map_share_img', array_filter(explode('|', $weshareInfo['images'])));
        $weshareInfo['proxy_rebate_percent'] = $proxy_share_percent['ProxyRebatePercent'];
        $weshareInfo['deliveryTemplate'] = $this->DeliveryTemplate->get_edit_delivery_templates($weshare_id);
        return $weshareInfo;
    }

    public function get_tag_weshare_detail($weshare_id)
    {
        $key = SHARE_DETAIL_DATA_WITH_TAG_CACHE_KEY . '_' . $weshare_id;
        $share_detail = Cache::read($key);
        if (empty($share_detail)) {
            $share_detail = $this->query_share_detail($weshare_id);
            Cache::write($key, json_encode($share_detail));
            return $share_detail;
        }
        return json_decode($share_detail, true);
    }

    /**
     * @param $weshare_id
     * @return mixed
     * 获取分享的详情
     */
    public function get_weshare_detail($weshare_id)
    {
        $key = SHARE_DETAIL_DATA_CACHE_KEY . '_' . $weshare_id;
        $share_detail = Cache::read($key);
        if (empty($share_detail)) {
            $share_detail = $this->query_share_detail($weshare_id);
            Cache::write($key, json_encode($share_detail));
            return $share_detail;
        }
        return json_decode($share_detail, true);
    }

    /**
     * @param $type
     * @param $uid
     * @param $share_info
     * @param $content
     * @param $weshare_id
     * @return array
     * 发送团购进度提醒
     */
    public function send_buy_percent_msg_job($type, $uid, $share_info, $content, $weshare_id)
    {
        if ($type == 0) {
            //发送给分享的管理者
            //发送给没有购买的粉丝
            return ['success' => false];
//            $checkSendMsgResult = $this->checkCanSendMsg($uid, $weshare_id, MSG_LOG_NOTIFY_TYPE);
//            if (!$checkSendMsgResult['success']) {
//                return $checkSendMsgResult;
//            }
//            $send_msg_log_data = array('created' => date('Y-m-d H:i:s'), 'sharer_id' => $uid, 'data_id' => $weshare_id, 'type' => MSG_LOG_NOTIFY_TYPE, 'status' => SEND_TEMPLATE_MSG_ACTIVE_STATUS);
//            $this->saveSendMsgLog($send_msg_log_data);
//            //$this->WeshareBuy->send_buy_percent_msg_to_share_manager($share_info, $content);
//            $fansPageInfo = $this->WeshareBuy->get_user_relation_page_info($uid);
//            $pageCount = $fansPageInfo['pageCount'];
//            $pageSize = $fansPageInfo['pageSize'];
//            $this->RedisQueue->add_tasks('share', "/weshares/process_send_buy_percent_msg/" . $weshare_id . "/" . $pageCount . "/" . $pageSize, "content=" . $content, true);
//            return array('success' => true, 'msg' => $checkSendMsgResult['msg']);
        }
        if ($type == 1) {
            //发送给已购买的用户
            //$this->WeshareBuy->send_notify_user_msg_to_share_manager($share_info, $content);
            $this->RedisQueue->add_tasks('share', "/weshares/process_notify_has_buy_fans/" . $weshare_id, "content=" . $content, true);
            return array('success' => true);
        }
        if ($type == 2) {
            //发送给全部用户

        }
    }

    /**
     * @param $sharer_id
     * @param $title
     * @return array
     * 发送店铺提醒
     */
    public function send_shop_notify_msg_job($sharer_id, $title)
    {
        $userM = ClassRegistry::init('User');
        $sharer = $userM->find('first', ['conditions' => ['id' => $sharer_id], 'fields' => ['id', 'nickname']]);
        $shop_name = $sharer['User']['nickname'] . '的小铺';
        $fansPageInfo = $this->WeshareBuy->get_user_relation_page_info($sharer_id);
        $pageCount = $fansPageInfo['pageCount'];
        $pageSize = $fansPageInfo['pageSize'];
        if ($pageCount <= 0) {
            return ['success' => false];
        }
        $tasks = [];
        foreach (range(0, $pageCount) as $page) {
            $offset = $page * $pageSize;
            $tasks[] = ['url' => "/weshares/shop_notify_task/" . $sharer_id . "/" . $pageSize . "/" . $offset, "postdata" => "title=" . $title . "&shop_name=" . $shop_name];
        }
        $ret = $this->RedisQueue->add_tasks('tasks', $tasks);
        return ['success' => true, 'ret' => $ret];
    }

    /**
     * @param $ship_company_id
     * @param $weshare_id
     * @param $ship_code
     * @param $order_id
     * 设置快递单号
     */
    public function set_order_ship_code($ship_company_id, $weshare_id, $ship_code, $order_id)
    {
        $ship_type_list = ShipAddress::ship_type_list();
        $ship_type_name = $ship_type_list[$ship_company_id];
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $orderM->updateAll(array('status' => ORDER_STATUS_SHIPPED, 'ship_type_name' => "'" . $ship_type_name . "'", 'ship_type' => $ship_company_id, 'ship_code' => "'" . $ship_code . "'", 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $order_id, 'status' => ORDER_STATUS_PAID));
        $cartM->updateAll(array('status' => ORDER_STATUS_RECEIVED), array('order_id' => $order_id));
        delete_redis_data_by_key(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id);
        $this->WeshareBuy->clear_user_share_order_data_cache(array($order_id), $weshare_id);
        $this->WeshareBuy->send_share_product_ship_msg($order_id, $weshare_id);
    }

    /**
     * @param $ship_code
     * @param $weshare_id
     * @param $order_id
     * @param $company_id
     * @param $ship_type_name
     * 更新快递单号
     */
    public function update_order_ship_code($ship_code, $weshare_id, $order_id, $company_id, $ship_type_name)
    {
        if (empty($company_id)) {
            $ship_name_id_map = ShipAddress::ship_type_name_id_map();
            $ship_company_id = $ship_name_id_map[$ship_type_name];
        } else {
            $ship_company_id = $company_id;
        }
        $orderM = ClassRegistry::init('Order');
        $orderM->updateAll(array('ship_type_name' => "'" . $ship_type_name . "'", 'ship_type' => $ship_company_id, 'ship_code' => "'" . $ship_code . "'"), array('id' => $order_id));
        delete_redis_data_by_key(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id);
        $this->WeshareBuy->clear_user_share_order_data_cache(array($order_id), $weshare_id);
    }

    /**
     * @param $order_id
     * @param $uid
     * @return array
     * 确认收货
     */
    public function confirm_received_order($order_id, $uid)
    {
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $weshareM = ClassRegistry::init('Weshare');
        $order = $orderM->findById($order_id);
        if (empty($order)) {
            return array('success' => false, 'reason' => 'order does not exist');
        }
        if ($order['Order']['type'] != ORDER_TYPE_WESHARE_BUY) {
            return array('success' => false, 'reason' => 'invalid order');
        }
        $weshare_id = $order['Order']['member_id'];
        $weshare = $weshareM->findById($weshare_id);
        if (empty($weshare)) {
            return array('success' => false, 'reason' => 'invalid weshare');
        }
        $is_owner = $uid == $order['Order']['creator'];
        $is_creator = $uid == $weshare['Weshare']['creator'];
        if (!$is_owner && !$is_creator) {
            $is_manage = $this->ShareAuthority->user_can_view_share_order_list($uid, $weshare_id);
            if (!$is_manage) {
                return array('success' => false, 'reason' => 'only owner or creator ');
            }
        }
        $result = $orderM->updateAll(array('status' => ORDER_STATUS_RECEIVED, 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $order['Order']['id']));
        $cartM->updateAll(array('status' => ORDER_STATUS_RECEIVED), array('order_id' => $order['Order']['id']));
        delete_redis_data_by_key(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshare_id);
        $this->WeshareBuy->clear_user_share_order_data_cache(array($order_id), $weshare_id);
        if (!$result) {
            return array("success" => false, "reason" => "failed to update order status");
        }
        return array("success" => true);
    }

    /**
     * @param $order_ids
     * @param $share_id
     * @param $uid
     * @param $content
     * @return array
     * 发送到货提醒
     */
    public function send_arrival_msg($order_ids, $share_id, $uid, $content)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $share_info = $weshareM->find('first', array(
            'conditions' => array(
                'id' => $share_id
            )
        ));
        if ($uid != $share_info['Weshare']['creator'] && !$this->ShareAuthority->user_can_view_share_order_list($uid, $share_id)) {
            return array('success' => false, 'reason' => 'invalid');
        }
        //update order status
        $prepare_update_orders = $orderM->find('all', array(
            'conditions' => array('status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED), 'type' => ORDER_TYPE_WESHARE_BUY, 'ship_mark' => SHARE_SHIP_SELF_ZITI_TAG, 'member_id' => $share_id, 'id' => $order_ids),
            'fields' => array('id')
        ));
        $prepare_update_order_ids = Hash::extract($prepare_update_orders, '{n}.Order.id');
        $orderM->updateAll(array('status' => ORDER_STATUS_SHIPPED, 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $prepare_update_order_ids));
        $cartM->updateAll(array('status' => ORDER_STATUS_SHIPPED), array('order_id' => $prepare_update_order_ids));
        delete_redis_data_by_key(SHARE_ORDER_DATA_CACHE_KEY . '_' . $share_id);
        $this->WeshareBuy->clear_user_share_order_data_cache($prepare_update_order_ids, $share_id);
        $this->WeshareBuy->send_share_product_arrive_msg($share_info, $content, $order_ids);
    }

    /**
     * @param $shareId
     * @param $uid
     * @param $orderId
     * @param $refundMoney
     * @param $refundMark
     * @return array
     * 订单退款
     */
    public function order_refund($shareId, $uid, $orderId, $refundMoney, $refundMark)
    {
        $share_info = $this->get_weshare_detail($shareId);
        //check user can manage share order
        $can_manage_order = $this->ShareAuthority->user_can_view_share_order_list($uid, $shareId);
        if ($share_info['creator']['id'] != $uid && !$can_manage_order) {
            return array('success' => false, 'reason' => 'not_creator');
        }
        $result = $this->refund($orderId, $refundMoney, $refundMark, 0);
        return $result;
    }

    /**
     * @param $weshare_id
     * @return array
     * 获取编辑分享的内容
     */
    public function get_edit_share_info($weshare_id)
    {
        $shareInfo = $this->get_weshare_detail($weshare_id);
        //change product price
        //change ship fee
        $products = &$shareInfo['products'];
        foreach ($products as &$p) {
            $p['price'] = $p['price'] / 100;
            $p['weight'] = $p['weight'] / 1000;
        }
        $defaultDeliveryTemplate = &$shareInfo['deliveryTemplate']['default_delivery_template'];
        $defaultDeliveryTemplate['add_fee'] = $defaultDeliveryTemplate['add_fee'] / 100;
        $defaultDeliveryTemplate['start_fee'] = $defaultDeliveryTemplate['start_fee'] / 100;
        if ($defaultDeliveryTemplate['unit_type'] == DELIVERY_UNIT_WEIGHT_TYPE) {
            $defaultDeliveryTemplate['start_units'] = strval($defaultDeliveryTemplate['start_units'] / 1000);
            $defaultDeliveryTemplate['add_units'] = strval($defaultDeliveryTemplate['add_units'] / 1000);
        }
        $deliveryTemplates = &$shareInfo['deliveryTemplate']['delivery_templates'];
        foreach ($deliveryTemplates as &$deliveryTemplateItem) {
            $deliveryTemplateItem['add_fee'] = $deliveryTemplateItem['add_fee'] / 100;
            $deliveryTemplateItem['start_fee'] = $deliveryTemplateItem['start_fee'] / 100;
            if ($deliveryTemplateItem['unit_type'] == DELIVERY_UNIT_WEIGHT_TYPE) {
                $deliveryTemplateItem['start_units'] = strval($deliveryTemplateItem['start_units'] / 1000);
                $deliveryTemplateItem['add_units'] = strval($deliveryTemplateItem['add_units'] / 1000);
            }
        }
        return $shareInfo;
    }

    /**
     * check_delivery
     * 检测一个分享是不是有有效的物流信息.
     *
     * @param mixed $sid
     * @access public
     * @return boolean
     */
    public function check_delivery($sid)
    {
        $shipSettingModel = ClassRegistry::init('WeshareShipSetting');
        $shipSettings = $shipSettingModel->find('all', [
            'conditions' => [
                'weshare_id' => $sid,
                'status' => 1,
            ]
        ]);

        if (!$shipSettings) {
            $this->log('Failed to create pool product with weshare ' . $sid . ': ship setting is empty', LOG_WARNING);
            return false;
        }

        $self_ziti = false;
        $kuai_di = false;

        $methods = [];
        foreach ($shipSettings as $item) {
            $ship = $item['WeshareShipSetting'];
            switch ($ship['tag']) {
                case 'self_ziti':
                    $self_ziti = $this->check_ziti($sid);
                    $methods[] = 'self_ziti';
                    break;
                case 'kuai_di':
                    $kuai_di = $this->check_kuaidi($sid);
                    $methods[] = 'kuai_di';
                    break;
            }
        }

        foreach (['self_ziti', 'kuai_di'] as $method) {
            if (!in_array($method, $methods)) {
                $$method = true;
            }
        }

        return $self_ziti && $kuai_di;
    }


    private function check_ziti($sid)
    {
        $weshareAddresseModel = ClassRegistry::init('WeshareAddresse');
        $weshareAddresse = $weshareAddresseModel->find('all', [
            'conditions' => [
                'weshare_id' => $sid
            ]
        ]);

        return $weshareAddresse;
    }


    private function check_kuaidi($sid)
    {
        $weshareDeliveryModel = ClassRegistry::init('WeshareDeliveryTemplate');
        $weshareDeliveryTemplates = $weshareDeliveryModel->find('all', [
            'conditions' => [
                'weshare_id' => $sid
            ]
        ]);

        return $weshareDeliveryTemplates;
    }


    public function get_pool_product_info($share_id)
    {
        $key = 'pool_product_info_cache_key_' . $share_id;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $weshareM = ClassRegistry::init('Weshare');
            //get share basic info
            $weshare_info = $weshareM->find('first', array(
                'conditions' => array('id' => $share_id)
            ));
            $weshare_info = $weshare_info['Weshare'];
            $weshare_info['description'] = str_replace(array("\r\n", "\n", "\r"), '<br />', $weshare_info['description']);
            $weshare_info['images'] = array_filter(explode('|', $weshare_info['images']));
            $weshare_products = $this->get_product_tag_map($share_id);
            //$sharer_tags = $this->ShareUtil->get_tags($weshare_info['creator'], $weshare_info['refer_share_id']);
            $weshare_info['products'] = $weshare_products;
            //$weshare_info['tags'] = $sharer_tags;
            $WeshareShipSettingM = ClassRegistry::init('WeshareShipSetting');
            $shipSettings = $WeshareShipSettingM->find('all', array('conditions' => array('weshare_id' => $share_id)));
            $ship_info = $this->get_pool_product_ship_info($shipSettings);
            $weshare_info['ship_info'] = $ship_info;
            Cache::write($key, json_encode($weshare_info));
            return $weshare_info;
        }
        return json_decode($cacheData, true);
    }

    /**
     * @param $shipSettings
     * @return string
     * 获取快递信息
     */
    private function get_pool_product_ship_info($shipSettings)
    {
        $ship_info = array();
        foreach ($shipSettings as $shipSettingItem) {
            if ($shipSettingItem['WeshareShipSetting']['tag'] == SHARE_SHIP_KUAIDI_TAG && $shipSettingItem['WeshareShipSetting']['status'] == 1) {
                $ship_fee = $shipSettingItem['WeshareShipSetting']['ship_fee'];
                if ($ship_fee == 0) {
                    $ship_info_item = '快递包邮';
                } else {
                    $ship_fee = $ship_fee / 100;
                    $ship_fee = number_format($ship_fee, 2);
                    $ship_info_item = '快递费用' . $ship_fee . '元';
                }
                $ship_info[] = $ship_info_item;
            }
            if ($shipSettingItem['WeshareShipSetting']['tag'] == SHARE_SHIP_PYS_ZITI_TAG && $shipSettingItem['WeshareShipSetting']['status'] == 1) {
                $ship_info[] = '好邻居自提';
            }
        }
        return implode(',', $ship_info);
    }

    /**
     * @param $newShareInfo
     * @internal param $WeshareM
     */
    public function authorize_weshare_after_cloning($newShareInfo)
    {
        $WeshareM = ClassRegistry::init('Weshare');
        $refer_weshare = $WeshareM->find('first', array(
            'conditions' => array(
                'id' => $newShareInfo['Weshare']['refer_share_id']
            ),
            'fields' => array('id', 'creator')
        ));
        if (!empty($refer_weshare) && $newShareInfo['Weshare']['creator'] != $refer_weshare['Weshare']['creator']) {
            $this->log('authorize share ' . $newShareInfo['Weshare']['id'] . ' of user ' . $newShareInfo['Weshare']['creator'] . ' to referred share ' . $newShareInfo['Weshare']['refer_share_id'] . ' of user ' . $refer_weshare['Weshare']['creator'] . ' for pool product share', LOG_INFO);
            $this->ShareAuthority->init_clone_share_from_pool_operate_config($newShareInfo['Weshare']['id'], $newShareInfo['Weshare']['creator'], $refer_weshare['Weshare']['creator']);
        }
    }

    public function get_pool_share_wait_ship_order_count($ids)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $orderM = ClassRegistry::init('Order');
        $fork_shares = $weshareM->find('list', [
            'conditions' => [
                'refer_share_id' => $ids
            ],
            'fields' => ['refer_share_id']
        ]);
        $share_ids = array_keys($fork_shares);
        $orderWaitShip = $orderM->find('all', [
            'conditions' => [
                'Order.member_id' => $share_ids,
                'Order.type' => ORDER_TYPE_WESHARE_BUY,
                'Order.status' => ORDER_STATUS_PAID,
                'not' => ['Order.flag' => ORDER_FLAG_VIRTUAL_FLAG]
            ],
            'group' => 'Order.member_id',
            'fields' => ['Order.member_id', 'COUNT(Order.id) as order_count']
        ]);
        $orderWaitShip = Hash::combine($orderWaitShip, '{n}.Order.member_id', '{n}.0.order_count');
        $result = [];
        foreach ($fork_shares as $share_id => $pool_share_id) {
            if (!isset($result[$pool_share_id])) {
                $result[$pool_share_id] = 0;
            }
            if ($orderWaitShip[$share_id] > 0) {
                $result[$pool_share_id] = $result[$pool_share_id] + $orderWaitShip[$share_id];
            }
        }
        return $result;
    }

    /**
     * @param $uid
     * @param $page
     * @param $limit
     * @return array
     * 获取返利提醒
     */
    public function get_rebate_list($uid, $page, $limit){
        $rebateLogM = ClassRegistry::init('RebateLog');
        $logs = $rebateLogM->find('all', [
            'conditions' => [
                'RebateLog.user_id' => $uid
            ],
            'joins' => [
                [
                    'table' => 'cake_orders',
                    'type' => 'left',
                    'alias' => 'Order',
                    'conditions' => 'Order.id = RebateLog.order_id'
                ],
                [
                    'table' => 'cake_users',
                    'type' => 'left',
                    'alias' => 'User',
                    'conditions' => 'User.id = Order.creator'
                ],
                [
                    'table' => 'cake_weshares',
                    'type' => 'left',
                    'alias' => 'Weshare',
                    'conditions' => 'Weshare.id = Order.member_id'
                ]
            ],
            'fields' => ['RebateLog.reason', 'RebateLog.money', 'RebateLog.description', 'Weshare.title', 'Weshare.default_image', 'User.nickname', 'Order.created', 'Order.member_id', 'Order.id'],
            'limit' => $limit,
            'page' => $page,
            'order' => 'RebateLog.id DESC'
        ]);
        $result = [];
        foreach ($logs as $logItem) {
            $item = array_merge([], $logItem['RebateLog'], $logItem['Weshare'], $logItem['User'], $logItem['Order']);
            $item['money'] = get_format_number(abs($item['money']) / 100);
            $result[] = $item;
        }
        return $result;
    }

}
