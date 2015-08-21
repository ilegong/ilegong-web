<?php


class WeshareBuyComponent extends Component {
    //TODO 重构 weshare controller
    var $name = 'WeshareBuyComponent';

    var $query_user_fields = array('id', 'nickname', 'image', 'wx_subscribe_status', 'description', 'mobilephone');

    var $query_order_fields = array('id', 'creator', 'created', 'consignee_name', 'consignee_mobilephone', 'consignee_address', 'status', 'total_all_price', 'coupon_total', 'ship_mark', 'ship_code', 'ship_type');

    var $query_cart_fields = array('id', 'order_id', 'name', 'product_id', 'num');

    var $query_comment_fields = array('id', 'username', 'user_id', 'data_id', 'type', 'body', 'order_id', 'parent_id');

    var $components = array('Session', 'Weixin', 'RedPacket');

    /**
     * @param $weshare_ids
     * @param $sharer_id
     * @return array
     * 加载分享者 所有的评论
     * 个人中心  页面
     */
    public function load_sharer_comment_data($weshare_ids, $sharer_id) {
        $cache_key = SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $sharer_id.'_1';
        $sharer_comment_data = Cache::read($cache_key);
        if (empty($sharer_comment_data)) {
            $commentM = ClassRegistry::init('Comment');
            $userM = ClassRegistry::init('User');
            $comments = $commentM->find('all', array(
                'conditions' => array(
                    'data_id' => $weshare_ids,
                    'type' => COMMENT_SHARE_TYPE,
                    'parent_id' => 0,
                    'status' => COMMENT_SHOW_STATUS
                )
            ));
            $comment_ids = Hash::extract($comments, '{n}.Comment.id');
            $comment_uids = Hash::extract($comments, '{n}.Comment.user_id');
            $comment_users = $userM->find('all', array(
                'conditions' => array(
                    'id' => $comment_uids
                ),
                'fields' => $this->query_user_fields
            ));
            $comment_users = Hash::combine($comment_users, '{n}.User.id', '{n}.User');
            $replay_count = $commentM->find('count', array(
                'fields' => 'DISTINCT parent_id',
                'conditions' => array(
                    'parent_id' => $comment_ids,
                    'status' => COMMENT_SHOW_STATUS,
                    'user_id' => $sharer_id,
                    'data_id' => $weshare_ids,
                    'type' => COMMENT_SHARE_TYPE,
                )
            ));
            $comment_count = count($comments);
            $reply_percent = 0;
            if ($comment_count > 0) {
                $reply_percent = $replay_count / $comment_count * 100;
            }
            $sharer_comment_data = array('comment_count' => $comment_count, 'comments' => $comments, 'comment_users' => $comment_users, 'reply_percent' => $reply_percent);
            Cache::write($cache_key, json_encode($sharer_comment_data));
            return $sharer_comment_data;
        }
        return json_decode($sharer_comment_data, true);
    }

    /**
     * @param $sharer_id
     * @return array
     * 分享页面 获取分享者的所有评论数据
     */
    public function load_sharer_comments($sharer_id) {
        $cache_key = SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $sharer_id . '_0';
        $sharer_comment_data = Cache::read($cache_key);
        if (empty($sharer_comment_data)) {
            $weshareM = ClassRegistry::init('Weshare');
            $commentM = ClassRegistry::init('Comment');
            $userM = ClassRegistry::init('User');
            $allShares = $weshareM->find('all', array(
                'conditions' => array(
                    'creator' => $sharer_id,
                    'status' => array(0, 1)
                )
            ));
            $share_ids = Hash::extract($allShares, '{n}.Weshare.id');
            $share_all_comments = $commentM->find('all', array(
                'conditions' => array(
                    'type' => COMMENT_SHARE_TYPE,
                    'data_id' => $share_ids,
                    'status' => COMMENT_SHOW_STATUS,
                    'parent_id' => 0,
                    'not' => array('order_id' => null)
                )
            ));
            $comment_user_ids = Hash::extract($share_all_comments, '{n}.Comment.user_id');
            $share_all_comments = Hash::extract($share_all_comments, '{n}.Comment');
            $all_users = $userM->find('all', array(
                'conditions' => array(
                    'id' => $comment_user_ids
                ),
                'fields' => $this->query_user_fields
            ));
            $all_users = Hash::combine($all_users, '{n}.User.id', '{n}.User');
            $sharer_comment_data = array('share_all_comments' => $share_all_comments, 'share_comment_all_users' => $all_users);
            Cache::write($cache_key, json_encode($sharer_comment_data));
            return $sharer_comment_data;
        }
        return json_decode($sharer_comment_data, true);
    }

    /**
     * @param $weshare_id
     * @return array
     * 加载本次分享的数据
     */
    public function load_comment_by_share_id($weshare_id) {
        $key = SHARE_COMMENT_DATA_CACHE_KEY . '_' . $weshare_id;
        $share_comment_data = Cache::read($key);
        if (empty($share_comment_data)) {
            $commentM = ClassRegistry::init('Comment');
            $commentReplyM = ClassRegistry::init('CommentReply');
            $comments = $commentM->find('all', array(
                'conditions' => array(
                    'type' => COMMENT_SHARE_TYPE,
                    'data_id' => $weshare_id,
                    'status' => COMMENT_SHOW_STATUS
                ),
                'fields' => $this->$query_comment_fields
            ));
            //$comments = Hash::combine($comments,'{n}.Comment.id', '{n}.Comment', '{n}.Comment.order_id');
            $order_comments = array_filter($comments, 'order_comment_filter');
            $order_comments = Hash::combine($order_comments, '{n}.Comment.order_id', '{n}.Comment');
            $reply_comments = array_filter($comments, 'order_reply_comment_filter');
            $reply_comments = Hash::combine($reply_comments, '{n}.Comment.id', '{n}.Comment');
            $commentReplies = $commentReplyM->find('all', array(
                'conditions' => array(
                    'data_id' => $weshare_id,
                    'data_type' => COMMENT_SHARE_TYPE
                ),
                'order' => array('id ASC')
            ));
            $comment_reply_relation = array();
            foreach ($commentReplies as $commentReply) {
                $comment_id = $commentReply['CommentReply']['comment_id'];
                $reply_id = $commentReply['CommentReply']['reply_id'];
                if (!isset($comment_reply_relation[$comment_id])) {
                    $comment_reply_relation[$comment_id] = array();
                }
                $comment_reply_relation[$comment_id][] = $reply_id;
            }
            $comment_replies = $this->recursionReply($order_comments, $reply_comments, $comment_reply_relation);
            $share_comment_data = array('order_comments' => $order_comments, 'comment_replies' => $comment_replies);
            Cache::write($key, json_encode($share_comment_data));
            return $share_comment_data;
        }
        return json_decode($share_comment_data, true);
    }

    /**
     * @param $order_comments
     * @param $reply_comments
     * @param $comment_replay_relation
     * @return array
     * 处理评论回复数据
     */
    private function recursionReply($order_comments, $reply_comments, $comment_replay_relation) {
        $comment_reply_format_result = array();
        foreach ($order_comments as $comment) {
            $comment_id = $comment['id'];
            $comment_reply_result = array();
            $this->processRecursionReply($reply_comments, $comment_reply_result, $comment_replay_relation, $comment_id, 0);
            $comment_reply_format_result[$comment_id] = $comment_reply_result;
        }
        return $comment_reply_format_result;
    }

    /**
     * @param $reply_comments
     * @param $comment_replay_format_result
     * @param $comment_replay_relation
     * @param $comment_id
     * @param int $level
     * 递归处理评论回复数据
     */
    private function processRecursionReply($reply_comments, &$comment_replay_format_result, $comment_replay_relation, $comment_id, $level = 0) {
        $current_comment_replay_relation = $comment_replay_relation[$comment_id];
        if (!empty($current_comment_replay_relation)) {
            foreach ($current_comment_replay_relation as $reply_id) {
                $reply = $reply_comments[$reply_id];
                $username = $reply['username'];
                $user_id = $reply['user_id'];
                $item_reply_data = array('id' => $reply['id'], 'body' => $reply['body'], 'plain_username' => $username);
                $item_reply_data['username'] = $username;
                $item_reply_data['user_id'] = $user_id;
                $item_reply_data['is_reply'] = 0;
                //todo check code issue
                if ($level == 1) {
                    $parent_comment = $reply_comments[$comment_id];
                    $reply_user_id = $parent_comment['user_id'];
                    if ($user_id != $reply_user_id) {
                        $item_reply_data['reply_username'] = $parent_comment['username'];
                        $item_reply_data['reply_user_id'] = $reply_user_id;
                        $item_reply_data['is_reply'] = 1;
                    }
                }
                $comment_replay_format_result[] = $item_reply_data;
                $reply_reply_relation = $comment_replay_relation[$reply_id];
                if (!empty($reply_reply_relation)) {
                    $this->processRecursionReply($reply_comments, $comment_replay_format_result, $comment_replay_relation, $reply_id, 1);
                }
            }
        }
    }

    /**
     * @param $order_id
     * @param $comment_content
     * @param $reply_comment_id
     * @param $comment_uid
     * @param $share_id
     * @return array
     * 提交评论
     */
    public function create_share_comment($order_id, $comment_content, $reply_comment_id, $comment_uid, $share_id) {
        $commentM = ClassRegistry::init('Comment');
        $userM = ClassRegistry::init('User');
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $weshare_info = $this->get_weshare_info($share_id);
        if (($weshare_info['creator'] == $comment_uid) && $reply_comment_id == 0 && empty($comment_content)) {
            //seller send to buyer
            $this->send_comment_notify_buyer($order_id, $share_id, $comment_content);
            return array('success' => true, 'type' => 'notify');
        }
        $user_nickname = $userM->findNicknamesOfUid($comment_uid);
        $order_info = $orderM->findOrderByConditionsAndFields(array('id' => $order_id), array('created', 'creator'));
        $date_time = date('Y-m-d H:i:s');
        $buy_date_time = $order_info['Order']['created'];
        $commentData = array('parent_id' => $reply_comment_id, 'user_id' => $comment_uid, 'username' => $user_nickname, 'body' => $comment_content, 'data_id' => $share_id, 'type' => COMMENT_SHARE_TYPE, 'publish_time' => $date_time, 'created' => $date_time, 'updated' => $date_time, 'buy_time' => $buy_date_time, 'order_id' => $order_id, 'status' => COMMENT_SHOW_STATUS);
        $comment = $commentM->save($commentData);
        if (empty($comment)) {
            $this->log('save comment fail order id ' . $order_id . ' uid ' . $comment_uid . ' share id ' . $share_id);
            return array('success' => false);
        }
        if ($reply_comment_id != 0) {
            //save replay relation
            $commentReplyM = ClassRegistry::init('CommentReply');
            $commentReplyData = array('comment_id' => $reply_comment_id, 'reply_id' => $comment['Comment']['id'], 'data_id' => $share_id, 'data_type' => COMMENT_SHARE_TYPE);
            $commentReply = $commentReplyM->save($commentReplyData);
            if (empty($commentReply)) {
                $this->log('save comment reply fail order id ' . $order_id . ' uid ' . $comment_uid . ' share id ' . $share_id . ' comment id ' . $comment['Comment']['id']);
                return array('success' => false);
            }
            //send share creator reply msg
            $reply_comment = $commentM->find('first', array(
                'conditions' => array(
                    'id' => $reply_comment_id
                )
            ));
            $reply_comment_uid = $reply_comment['Comment']['user_id'];
            $order_uid = $order_info['Order']['creator'];
            if ($comment_uid == $weshare_info['creator'] && $reply_comment_uid == $order_uid) {
                $this->send_comment_reply_notify($order_id, $share_id, $comment_content);
            } elseif ($reply_comment_id == $weshare_info['creator'] && $order_uid == $comment_uid) {
                $this->send_comment_notify($order_id, $share_id, $comment_content);
            } elseif ($comment_uid != $reply_comment_uid) {
                $this->send_comment_mutual_msg($comment_uid, $reply_comment_uid, $comment_content, $share_id, $order_id);
            }
        } else {
            //update order status
            $orderM->updateAll(array('status' => ORDER_STATUS_DONE, 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $order_id));
            $cartM->updateAll(array('status' => ORDER_STATUS_DONE), array('order_id' => $order_id));
            if ($comment_uid == $order_info['Order']['creator']) {
                $this->send_comment_notify($order_id, $share_id, $comment_content);
            }
            if (!empty($comment['Comment']['id'])) {
                $this->send_shareed_offer_notify($order_id, $share_id, $comment['Comment']['id']);
            }
            //clean cache
            //$cache_key = SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $sharer_id . '_0';
            //$cache_key = SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $sharer_id . '_1';
            Cache::write(SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $weshare_info['creator'] . '_0','');
            Cache::write(SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $weshare_info['creator'] . '_1','');
            //SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId;
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $share_id, '');
        }
        //$key = SHARE_COMMENT_DATA_CACHE_KEY . '_' . $weshare_id;
        Cache::write(SHARE_COMMENT_DATA_CACHE_KEY . '_' . $share_id, '');
        return array('success' => true, 'comment' => $comment['Comment'], 'comment_reply' => $commentReply['CommentReply'], 'order_id' => $order_id);
    }

    /**
     * @param $weshareId
     * 创建新的分享之后发送模板消息
     */
    public function send_new_share_msg($weshareId) {
        $this->Weshare = ClassRegistry::init('Weshare');
        $this->User = ClassRegistry::init('User');
        $this->Oauthbind = ClassRegistry::init('Oauthbind');
        $this->WeshareProduct = ClassRegistry::init('WeshareProduct');

        $weshare = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $weshareId
            )
        ));

        $sharer_user_info = $this->User->find('first', array(
            'conditions' => array(
                'id' => $weshare['Weshare']['creator']
            ),
            'fields' => array(
                'id', 'nickname'
            )
        ));
        $detail_url = WX_HOST.'/weshares/view/'.$weshareId;
        $sharer_name = $sharer_user_info['User']['nickname'];
        $product_name = $weshare['Weshare']['title'];
        $title = '关注的'.$sharer_name.'发起了';
        $remark = '点击详情，赶快加入'.$sharer_name.'的分享！';
        $followers = $this->load_fans_buy_sharer($weshare['Weshare']['creator'],$weshareId);
        $openIds = $this->Oauthbind->findWxServiceBindsByUids($followers);
        foreach($openIds as $openId){
            $this->process_send_share_msg($openId,$title,$product_name,$detail_url,$sharer_name,$remark);
        }
    }

    /**
     * @param $order_id
     * @param $weshare_id
     * 快递寄出的模板消息
     */
    public function send_share_product_ship_msg($order_id, $weshare_id) {
        $this->Weshare = ClassRegistry::init('Weshare');
        $this->Order = ClassRegistry::init('Order');
        $this->User = ClassRegistry::init('User');
        $order_info = $this->Order->find('first', array(
            'conditions' => array(
                'id' => $order_id,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'member_id' => $weshare_id
            ),
            'fields' => $this->query_order_fields
        ));
        if (!empty($order_info)) {
            $order_user_id = $order_info['Order']['creator'];
            $weshare_info = $this->Weshare->find('first', array(
                'conditions' => array(
                    'id' => $weshare_id
                )
            ));
            $share_creator = $weshare_info['Weshare']['creator'];
            $users = $this->User->find('all', array(
                'conditions' => array(
                    'id' => array($share_creator, $order_user_id)
                ),
                'fields' => $this->query_user_fields
            ));
            $users = Hash::combine($users, '{n}.User.id', '{n}.User');
            //$nick_name_map = $this->User->findNicknamesMap(array($share_creator, $order_user_id));
            $order_user_nickname = $users[$order_user_id]['nickname'];
            $share_creator_nickname = $users[$share_creator]['nickname'];
            $title = $order_user_nickname . '你好，' . $share_creator_nickname . '分享的' . $weshare_info['Weshare']['title'] . '寄出了，请注意查收。'.$share_creator_nickname.'电话:'.$users[$share_creator]['mobilephone'];
            $shipTypesList = ShipAddress::ship_type_list();
            $ship_company_name = $shipTypesList[$order_info['Order']['ship_type']];
            $ship_code = $order_info['Order']['ship_code'];
            $desc = '感谢您对' . $share_creator_nickname . '的支持，分享快乐！';
            $cart_info = $this->get_cart_name_and_num($order_id);
            $deatail_url = WX_HOST . '/weshares/view/' . $weshare_id;
            $this->Weixin->send_order_ship_info_msg($order_user_id, null, $ship_code, $ship_company_name, $cart_info['cart_name'], null, $title, $cart_info['num'], $desc, $deatail_url);
            //send_order_ship_info_msg
        }
    }

    /**
     * @param $shareInfo
     * @param $msg
     * 到货提醒
     */
    public function send_share_product_arrive_msg($shareInfo, $msg){
        $this->Order = ClassRegistry::init('Order');
        $this->User = ClassRegistry::init('User');
        $this->Oauthbind = ClassRegistry::init('Oauthbind');
        $share_id = $shareInfo['Weshare']['id'];
        $share_creator = $shareInfo['Weshare']['creator'];
        //select order paid to send msg
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY,
                'member_id' => $share_id,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED),
                'ship_mark' => SHARE_SHIP_SELF_ZITI_TAG
            ),
            'fields' => array(
                'id', 'consignee_name', 'consignee_address', 'creator'
            )
        ));
        $order_user_ids = Hash::extract($orders, '{n}.Order.creator');
        $order_user_ids[] = $share_creator;
        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $order_user_ids
            ),
            'fields' => array('id', 'nickname')
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        $userOauthBinds = $this->Oauthbind->find('all', array(
            'conditions' => array(
                'user_id' => $order_user_ids
            ),
            'fields' => array('user_id', 'oauth_openid')
        ));
        $userOauthBinds = Hash::combine($userOauthBinds, '{n}.Oauthbind.user_id', '{n}.Oauthbind.oauth_openid');
        $desc = '感谢大家对' . $users[$share_creator]['nickname'] . '的支持，分享快乐。';
        $detail_url = WX_HOST . '/weshares/view/' . $share_id;
        foreach ($orders as $order) {
            $order_id = $order['Order']['id'];
            $order_user_id = $order['Order']['creator'];
            $open_id = $userOauthBinds[$order_user_id];
            $order_user_name = $users[$order_user_id]['nickname'];
            $title = $order_user_name . '你好，' . $msg;
            $conginess_name = $order['Order']['consignee_name'];
            $conginess_address = $order['Order']['consignee_address'];
            $this->Weixin->send_share_product_arrival($open_id, $detail_url, $title, $order_id, $conginess_address, $conginess_name, $desc);
        }
    }

    /**
     * @param $sharerId
     * @param $weshareId
     * @return array
     * 加载粉丝数据
     */
    public function load_fans_buy_sharer($sharerId, $weshareId=null) {
        $userRelationM = ClassRegistry::init('UserRelation');
        $relations = $userRelationM->find('all', array(
            'conditions' => array(
                'user_id' => $sharerId,
                'deleted' => DELETED_NO
            )
        ));
        $follower_ids = Hash::extract($relations, '{n}.UserRelation.follow_id');
        return $follower_ids;
    }

    /**
     * @param $openId
     * @param $title
     * @param $productName
     * @param $detailUrl
     * @param $sharerName
     * @param $remark
     * 处理发送 参团信息
     */
    public function process_send_share_msg($openId, $title, $productName, $detailUrl,$sharerName,$remark) {
        send_join_tuan_buy_msg(null,$title,$productName,$sharerName,$remark,$detailUrl,$openId);
    }

    /**
     * @param $weshareId
     * @return float
     * 退款 金额
     */
    public function get_refund_money_by_weshare($weshareId) {
        $orderM = ClassRegistry::init('Order');
        $refundLogM = ClassRegistry::init('RefundLog');
        $refund_orders = $orderM->find('all', array(
            'conditions' => array(
                'member_id' => $weshareId,
                'status' => array(ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY),
                'type' => ORDER_TYPE_WESHARE_BUY,
                'deleted' => DELETED_NO
            )
        ));
        $refund_order_ids = Hash::extract($refund_orders, '{n}.Order.id');
        $refund_logs = $refundLogM->find('all', array(
            'conditions' => array(
                'order_id' => $refund_order_ids
            )
        ));
        $refund_money = 0;
        foreach ($refund_logs as $item_log) {
            $refund_money = $refund_money + $item_log['RefundLog']['refund_fee'];
        }
        return $refund_money / 100;
    }

    /**
     * @param $weshareId
     * @param $is_me
     * @param bool $division
     * @return array
     * 获取分享的订单信息
     */
    public function get_share_order_for_show($weshareId, $is_me, $division = false){
        $key = SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId;
        $share_order_data = Cache::read($key);
        if (empty($share_order_data)) {
            $this->Weshare = ClassRegistry::init('Weshare');
            $this->Order = ClassRegistry::init('Order');
            $this->User = ClassRegistry::init('User');
            $this->Cart = ClassRegistry::init('Cart');
            $this->Oauthbind = ClassRegistry::init('Oauthbind');
            $this->WeshareProduct = ClassRegistry::init('WeshareProduct');
            $product_buy_num = array('details' => array());
            $order_cart_map = array();
            $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY);
            $sort = array('created DESC');
            $orders = $this->Order->find('all', array(
                'conditions' => array(
                    'member_id' => $weshareId,
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'status' => $order_status,
                    'deleted' => DELETED_NO
                ),
                'fields' => array('id', 'creator', 'created', 'updated', 'consignee_name', 'consignee_mobilephone', 'consignee_address', 'status', 'total_all_price', 'coupon_total', 'ship_mark', 'ship_code', 'ship_type'),
                'order' => $sort
            ));
            $orderIds = Hash::extract($orders, '{n}.Order.id');
            $userIds = Hash::extract($orders, '{n}.Order.creator');
            $users = $this->User->find('all', array(
                'conditions' => array(
                    'id' => $userIds
                ),
                'recursive' => 1, //int
                'fields' => $this->query_user_fields,
            ));
            $orders = Hash::combine($orders, '{n}.Order.id', '{n}.Order');
            if ($orders) {
                if ($is_me) {
                    usort($orders, function ($a, $b) {
                        $a_update_date = $a['updated'];
                        $a_update_date_time = strtotime($a_update_date);
                        $b_update_date = $b['updated'];
                        $b_update_date_time = strtotime($b_update_date);
                        return ($a_update_date_time < $b_update_date_time) ? 1 : -1;
                    });
                } else {
                    usort($orders, function ($a, $b) {
                        return ($a['id'] < $b['id']) ? -1 : 1;
                    });
                }
            }
            $carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $orderIds,
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'not' => array('order_id' => null, 'order_id' => '')
                ),
                'fields' => array('id', 'name', 'order_id', 'num', 'product_id', 'price')
            ));
            $realTotalPrice = 0;
            $summeryTotalPrice = 0;
            foreach ($orders as $order_item) {
                $realTotalPrice = $realTotalPrice + $order_item['total_all_price'];
                $summeryTotalPrice = $summeryTotalPrice + $order_item['total_price'];
            }
            foreach ($carts as $item) {
                $order_id = $item['Cart']['order_id'];
                $product_id = $item['Cart']['product_id'];
                $cart_num = $item['Cart']['num'];
                $cart_price = $item['Cart']['price'];
                $cart_name = $item['Cart']['name'];
                if (!isset($product_buy_num['details'][$product_id])) $product_buy_num['details'][$product_id] = array('num' => 0, 'total_price' => 0, 'name' => $cart_name);
                if (!isset($order_cart_map[$order_id])) $order_cart_map[$order_id] = array();
                $product_buy_num['details'][$product_id]['num'] = $product_buy_num['details'][$product_id]['num'] + $cart_num;
                $totalPrice = $cart_num * $cart_price;
                $product_buy_num['details'][$product_id]['total_price'] = $product_buy_num['details'][$product_id]['total_price'] + $totalPrice;
                $order_cart_map[$order_id][] = $item['Cart'];
            }
            $product_buy_num['all_buy_user_count'] = count($users);
            $product_buy_num['all_total_price'] = $summeryTotalPrice;
            $product_buy_num['real_total_price'] = $realTotalPrice;
            $users = Hash::combine($users, '{n}.User.id', '{n}.User');
            if ($division) {
                $kuaidi_orders = array_filter($orders, "share_kuaidi_order_filter");
                if ($kuaidi_orders) {
                    usort($kuaidi_orders, function ($a, $b) {
                        return ($a['status'] < $b['status']) ? -1 : 1;
                    });
                }
                $self_ziti_orders = array_filter($orders, "share_self_ziti_order_filter");
                $pys_ziti_orders = array_filter($orders, "share_pys_ziti_order_filter");
                $orders = array(SHARE_SHIP_KUAIDI_TAG => $kuaidi_orders, SHARE_SHIP_SELF_ZITI_TAG => $self_ziti_orders, SHARE_SHIP_PYS_ZITI_TAG => $pys_ziti_orders);
            }
            //show order ship type name
            $shipTypes = ShipAddress::ship_type_list();
            $share_order_data = array('users' => $users, 'orders' => $orders, 'order_cart_map' => $order_cart_map, 'summery' => $product_buy_num, 'ship_types' => $shipTypes);
            Cache::write($key, json_encode($share_order_data));
            return $share_order_data;
        }
        return json_decode($share_order_data, true);
    }

    /**
     * @param $weshareId
     * 批量更新订单数据
     */
    public function batch_update_order_status($weshareId) {
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $cond = array(
            'status' => array(ORDER_STATUS_RECEIVED, ORDER_STATUS_SHIPPED, ORDER_STATUS_PAID),
            'type' => ORDER_TYPE_WESHARE_BUY
        );
        if (!empty($weshareId)) {
            $cond['member_id'] = $weshareId;
        }
        $orders = $orderM->find('all', array(
            'conditions' => $cond
        ));
        $order_ids = Hash::extract($orders, '{n}.Order.id');
        //update order status
        $orderM->updateAll(array('status' => ORDER_STATUS_RECEIVED, 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $order_ids));
        $cartM->updateAll(array('status' => ORDER_STATUS_RECEIVED), array('order_id' => $order_ids));
        $this->process_send_to_comment_msg($orders);
    }

    /**
     * @param null $weshareId
     * 发送评论通知模板消息
     */
    public function send_to_comment_msg($weshareId = null) {
        $orderM = ClassRegistry::init('Order');
        $limit_date = date('Y-m-d', strtotime("-4 days"));
        $cond = array(
            'status' => ORDER_STATUS_RECEIVED,
            'type' => ORDER_TYPE_WESHARE_BUY
        );
        if (!empty($weshareId)) {
            $cond['member_id'] = $weshareId;
        }else{
            $cond['DATE(updated)'] = $limit_date;
        }
        $orders = $orderM->find('all', array(
            'conditions' => $cond
        ));
        $this->process_send_to_comment_msg($orders);
    }

    /**
     * @param null $weshareId
     * 更新订单状态且发送评论通知信息
     */
    public function chage_status_and_send_to_comment_msg($weshareId=null) {
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $limit_date = date('Y-m-d', strtotime("-7 days"));
        $cond = array(
            'status' => ORDER_STATUS_SHIPPED,
            'type' => ORDER_TYPE_WESHARE_BUY
        );
        if (!empty($weshareId)) {
            $cond['member_id'] = $weshareId;
        } else {
            $cond['DATE(updated) <= '] = $limit_date;
        }
        $orders = $orderM->find('all', array(
            'conditions' => $cond
        ));
        $order_ids = Hash::extract($orders, '{n}.Order.id');
        //update order status to received
        $orderM->updateAll(array('status' => ORDER_STATUS_RECEIVED, 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $order_ids));
        $cartM->updateAll(array('status' => ORDER_STATUS_RECEIVED), array('order_id' => $order_ids));
        $this->process_send_to_comment_msg($orders);
    }

    /**
     * @param $orders
     * 通知下单用户去评论模板消息
     */
    private function process_send_to_comment_msg($orders){
        $oauthBindM = ClassRegistry::init('Oauthbind');
        $order_uids = Hash::extract($orders, '{n}.Order.creator');
        $order_member_ids = Hash::extract($orders, '{n}.Order.member_id');
        $uid_openid_map = $oauthBindM->findWxServiceBindMapsByUids($order_uids);
        $share_list = $this->get_all_share_info($order_member_ids);
        $sharer_ids = Hash::extract($share_list, '{n}.Weshare.creator');
        $share_list = Hash::combine($share_list, '{n}.Weshare.id', '{n}.Weshare');
        $all_uids = array_merge($order_uids, $sharer_ids);
        $nick_name_map = $this->get_users_nickname($all_uids);
        $sharer_offer_map = $this->sharer_has_offer($sharer_ids);
        foreach ($orders as $order_info) {
            $member_id = $order_info['Order']['member_id'];
            $share_info = $share_list[$member_id];
            $sharer_name = $nick_name_map[$share_info['creator']];
            $creator_name = $nick_name_map[$order_info['Order']['creator']];
            $msg_title = $creator_name . '你好，' . $sharer_name . '分享的' . $share_info['title'] . '收到了吧，做个爱心评价呢。';
            $desc = '分享，让生活更美。点击评价。';
            if (!empty($sharer_offer_map[$share_info['creator']])) {
                $desc = $desc . $sharer_name . '的红包等着你呢：）';
            }
            $detail_url = $this->get_weshares_detail_url($member_id);
            $order_date = $order_info['Order']['created'];
            $open_id = $uid_openid_map[$order_info['Order']['creator']];
            $order_id = $order_info['Order']['id'];
            $this->Weixin->send_comment_template_msg($open_id, $detail_url, $msg_title, $order_id, $order_date, $desc);
        }
    }

    /**
     * @param $comment_uid
     * @param $reply_id
     * @param $content
     * @param $share_id
     * @param $order_id
     * 用户之间互相评论
     */
    public function send_comment_mutual_msg($comment_uid,$reply_id,$content, $share_id,$order_id){
        $uid_name_map = $this->get_users_nickname(array($comment_uid, $reply_id));
        $title = $uid_name_map[$reply_id].'你好，'.$uid_name_map[$comment_uid].'对你说：'.$content;
        $desc = '分享，让生活更美。点击查看。';
        $detail_url = $this->get_weshares_detail_url($share_id);
        $order_info = $this->get_order_info($order_id);
        $order_id = $order_info['id'];
        $order_date = $order_info['created'];
        $open_id_map = $this->get_open_ids(array($reply_id));
        $open_id = $open_id_map[$reply_id];
        $this->Weixin->send_comment_template_msg($open_id, $detail_url, $title, $order_id, $order_date, $desc);
    }

    /**
     * @param $order_id
     * @param $weshare_id
     * @param $comment_id
     * 评论分享礼包
     */
    public function send_shareed_offer_notify($order_id, $weshare_id, $comment_id) {
        //send to seller
        $order_info = $this->get_order_info($order_id);
        $order_creator = $order_info['creator'];
        $share_info = $this->get_weshare_info($weshare_id);
        $share_creator = $share_info['creator'];
        $uid_name_map = $this->get_users_nickname(array($order_creator, $share_creator));
        $open_id_map = $this->get_open_ids(array($order_creator));
        $open_id = $open_id_map[$order_creator];
        $detail_url = $this->get_weshares_detail_url($weshare_id);
        $title = $uid_name_map[$order_creator] . '，你好，恭喜你获得' . $uid_name_map[$share_creator] . '分享礼包！报名可以抵现！';
        $desc = '分享，让生活更美。点击查看。';
        $keyword1 = $uid_name_map[$share_creator] . '分享礼包';
        $offer = $this->Weixin->send_share_offer_msg($open_id, $order_id, $title, $detail_url, $keyword1, $desc, $comment_id);
        $share_offer_id = $offer['id'];
        $this->RedPacket->process_receive($share_offer_id, $order_creator, true, false);
    }

    /**
     * @param $order_id
     * @param $weshare_id
     * @param $comment_content
     * 通知下单用户 收到了评论
     */
    public function send_comment_notify($order_id, $weshare_id, $comment_content) {
        $order_info = $this->get_order_info($order_id);
        $order_creator = $order_info['creator'];
        $share_info = $this->get_weshare_info($weshare_id);
        $share_creator = $share_info['creator'];
        $uid_name_map = $this->get_users_nickname(array($order_creator, $share_creator));
        $open_id_map = $this->get_open_ids(array($share_creator));
        $open_id = $open_id_map[$share_creator];
        $title = $uid_name_map[$share_creator].'你好，'.$uid_name_map[$order_creator].'说，感谢' . $uid_name_map[$share_creator] . '，' . $comment_content . '。';
        $order_id = $order_info['id'];
        $order_date = $order_info['created'];
        $desc = '分享，让生活更美。点击回复' . $uid_name_map[$order_creator] . '。';
        $detail_url = $this->get_weshares_detail_url($weshare_id);
        $this->Weixin->send_comment_template_msg($open_id, $detail_url, $title, $order_id, $order_date, $desc);
    }

    /**
     * @param $order_id
     * @param $weshare_id
     * 通知分享者收到了评论
     */
    public function send_comment_notify_buyer($order_id, $weshare_id){
        $order_info = $this->get_order_info($order_id);
        $order_creator = $order_info['creator'];
        $share_info = $this->get_weshare_info($weshare_id);
        $share_creator = $share_info['creator'];
        $uid_name_map = $this->get_users_nickname(array($order_creator, $share_creator));
        $open_id_map = $this->get_open_ids(array($order_creator));
        $open_id = $open_id_map[$order_creator];
        //分享的XXX
        $sharer_offer_map = $this->sharer_has_offer(array($share_creator));
        $title = $uid_name_map[$order_creator].'你好，'.$uid_name_map[$share_creator].'说，分享的'.$share_info['title'].'收到了吧，给个爱心评价呢！';
        if (!empty($sharer_offer_map[$share_info['creator']])) {
            $title = $title . $uid_name_map[$share_creator] . '的红包等着你呢：）';
        }
        $order_id = $order_info['id'];
        $order_date = $order_info['created'];
        $desc = '分享，让生活更美。点击回复' . $uid_name_map[$share_creator] . '。';
        $detail_url = $this->get_weshares_detail_url($weshare_id);
        $this->Weixin->send_comment_template_msg($open_id, $detail_url, $title, $order_id, $order_date, $desc);
    }

    /**
     * @param $order_id
     * @param $weshare_id
     * @param $reply_content
     * 收到评论回复通知
     */
    public function send_comment_reply_notify($order_id,$weshare_id,$reply_content){
        $order_info = $this->get_order_info($order_id);
        $order_creator = $order_info['creator'];
        $share_info = $this->get_weshare_info($weshare_id);
        $share_creator = $share_info['creator'];
        $uid_name_map = $this->get_users_nickname(array($order_creator, $share_creator));
        $open_id_map = $this->get_open_ids(array($share_creator,$order_creator));
        $open_id = $open_id_map[$order_creator];
        $title = $uid_name_map[$order_creator].'你好，'.$uid_name_map[$share_creator].'说，谢谢你对我的支持，'.$reply_content.'。';
        $order_id = $order_info['id'];
        $order_date = $order_info['created'];
        $desc = '分享，让生活更美。点击查看。';
        $detail_url = $this->get_weshares_detail_url($weshare_id);
        $this->Weixin->send_comment_template_msg($open_id, $detail_url, $title, $order_id, $order_date, $desc);
    }

    /**
     * @param $uid
     * @return array|mixed
     *
     */
    public function get_user_share_summary($uid) {
        $key = SHARE_USER_SUMMERY_CACHE_KEY . '_' . $uid;
        $summery_data = Cache::read($key);
        if (empty($summery_data)) {
            $weshareM = ClassRegistry::init('Weshare');
            $userRelationM = ClassRegistry::init('UserRelation');
            $weshares = $weshareM->find('all', array(
                'conditions' => array(
                    'creator' => $uid
                ),
                'fields' => array('id')
            ));
            $fans_count = $userRelationM->find('count', array(
                'conditions' => array(
                    'user_id' => $uid
                )
            ));
            $summery_data = array('share_count' => count($weshares), 'follower_count' => $fans_count);
            Cache::write($key, json_encode($summery_data));
            return $summery_data;
        }
        return json_decode($summery_data, true);
    }

    /**
     * @param $uid
     * 获取用户粉丝的信息
     * @return array|mixed
     */
    public function get_user_fans_data($uid) {
        $key = SHARER_FANS_DATA_CACHE_KEY . '_' . $uid;
        $fans_data = Cache::read($key);
        if (empty($fans_data)) {
            $userRelationM = ClassRegistry::init('UserRelation');
            $userM = ClassRegistry::init('User');
            $relations = $userRelationM->find('all', array(
                'conditions' => array(
                    'user_id' => $uid,
                    'deleted' => DELETED_NO
                )
            ));
            $fans_id = Hash::extract($relations, '{n}.UserRelation.follow_id');
            $fans_data = $userM->find('all', array(
                'conditions' => array(
                    'id' => $fans_id
                ),
                'fields' => $this->query_user_fields
            ));
            $fans_data = Hash::extract($fans_data, '{n}.User');
            Cache::write($key, json_encode($fans_data));
            return $fans_data;
        }
        return json_decode($fans_data, true);
    }

    /**
     * @param $weshareId
     * @return string
     * 获取分享的地址
     */
    private function get_weshares_detail_url($weshareId){
        return  WX_HOST . '/weshares/view/' . $weshareId;
    }

    private function get_open_ids($uids){
        $oauthBindM = ClassRegistry::init('Oauthbind');
        $uid_openid_map = $oauthBindM->findWxServiceBindMapsByUids($uids);
        return $uid_openid_map;
    }

    private function findCarts($orderId){
        $this->Cart = ClassRegistry::init('Cart');
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $orderId
            ),
            'fields' => $this->query_cart_fields
        ));
        return $carts;
    }

    public function get_order_info($order_id) {
        $orderM = ClassRegistry::init('Order');
        $order = $orderM->find('first', array(
            'conditions' => array(
                'id' => $order_id
            ),
            'fields' => $this->query_order_fields
        ));
        return $order['Order'];
    }

    private function get_users_nickname($uids){
        $userM = ClassRegistry::init('User');
        return $userM->findNicknamesMap($uids);
    }

    private function get_user_nickname($uid){
        $userM = ClassRegistry::init('User');
        return $userM->findNicknamesOfUid($uid);
    }

    private function get_weshare_info($share_id){
        $weshareM = ClassRegistry::init('Weshare');
        $share_info = $weshareM->find('first',array(
            'conditions' => array(
                'id' => $share_id
            )
        ));
        return $share_info['Weshare'];
    }

    private function get_all_share_info($share_ids){
        $weshareM = ClassRegistry::init('Weshare');
        $share_info = $weshareM->find('all',array(
            'conditions' => array(
                'id' => $share_ids
            )
        ));
        return $share_info;
    }

    private function has_share_offer($uid){
        $shareOfferM = ClassRegistry::init('ShareOffer');
        $shareOffer = $shareOfferM->find('first',array(
            'conditions' => array('sharer_id' => $uid),
            'order' => array('id desc')
        ));
        return !empty($shareOffer);
    }

    /**
     * @param $sharer_ids
     * @return array
     * 分享者是否有红包
     */
    private function sharer_has_offer($sharer_ids){
        $shareOfferM = ClassRegistry::init('ShareOffer');
        $shareOffer = $shareOfferM->find('all',array(
            'conditions' => array('sharer_id' => $sharer_ids),
            'order' => array('id desc'),
            'fields' => array('id', 'sharer_id')
        ));
        $shareOffer = Hash::combine($shareOffer, '{n}.ShareOffer.sharer_id', '{n}.ShareOffer.id');
        return $shareOffer;
    }

    /**
     * @param $orderId
     * @return array
     * 获取分享订单商品名称和数量
     */
    private function get_cart_name_and_num($orderId) {
        $carts = $this->findCarts($orderId);
        $num = 0;
        $cart_name = array();
        foreach ($carts as $cart_item) {
            $num += $cart_item['Cart']['num'];
            $cart_name[] = $cart_item['Cart']['name'] . 'X' . $cart_item['Cart']['num'];
        }
        return array('num' => $num, 'cart_name' => implode(',', $cart_name));
    }
}