<?php


class WeshareBuyComponent extends Component {

    var $name = 'WeshareBuyComponent';


    var $share_order_count = 10;

    var $query_user_fields = array('id', 'nickname', 'image', 'wx_subscribe_status', 'description', 'mobilephone', 'is_proxy');

    var $query_order_fields = array('id', 'creator', 'created', 'consignee_name', 'consignee_mobilephone', 'consignee_address', 'status', 'total_all_price', 'coupon_total', 'ship_mark', 'ship_code', 'ship_type', 'member_id', 'process_prepaid_status', 'price_difference', 'is_prepaid');

    var $query_share_info_order_fields = array('id', 'creator', 'created', 'updated', 'consignee_name', 'consignee_mobilephone', 'consignee_address', 'status', 'total_all_price', 'coupon_total', 'ship_mark', 'ship_code', 'ship_type', 'total_price', 'coupon_total', 'cate_id', 'process_prepaid_status', 'price_difference', 'is_prepaid', 'business_remark');

    var $query_cart_fields = array('id', 'order_id', 'name', 'product_id', 'num');

    var $query_comment_fields = array('id', 'username', 'user_id', 'data_id', 'type', 'body', 'order_id', 'parent_id');

    var $components = array('Session', 'Weixin', 'RedPacket', 'ShareUtil');


    /**
     * @param $weshare_ids
     * @param $sharer_id
     * @return array
     * 加载分享者 所有的评论
     * 个人中心  页面
     */
    public function load_sharer_comment_data($weshare_ids, $sharer_id) {
        $cache_key = SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $sharer_id . '_1';
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
                ),
                'order' => array('created DESC'),
                'limit' => 100
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
            $my_comment_count = $commentM->find('count', array(
                'conditions' => array(
                    'user_id' => $sharer_id,
                    'type' => COMMENT_SHARE_TYPE,
                    'parent_id' => 0,
                    'status' => COMMENT_SHOW_STATUS
                )
            ));
            $reply_percent = 0;
            $comment_count = $comment_count + $my_comment_count;
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
     * @param $uid
     * @return array|mixed
     * 获取用户评论的数据
     */
    public function load_user_share_comments($uid) {
        $key = USER_SHARE_COMMENTS_DATA_CACHE_KEY . '_' . $uid;
        $user_share_comment_data = Cache::read($key);
        if (empty($user_share_comment_data)) {
            $commentM = ClassRegistry::init('Comment');
            $userM = ClassRegistry::init('User');
            $weshareM = ClassRegistry::init('Weshare');
            $comments = $commentM->find('all', array(
                'conditions' => array(
                    'type' => COMMENT_SHARE_TYPE,
                    'user_id' => $uid,
                    'status' => COMMENT_SHOW_STATUS,
                    'parent_id' => 0
                ),
                'order' => array('created DESC'),
                'limit' => 100
            ));
            $share_ids = Hash::extract($comments, '{n}.Comment.data_id');
            $share_info = $weshareM->find('all', array(
                'conditions' => array(
                    'id' => $share_ids
                )
            ));
            $share_creator_ids = Hash::extract($share_info, '{n}.Weshare.creator');
            $this->explode_share_imgs($share_info);
            $share_info = Hash::combine($share_info, '{n}.Weshare.id', '{n}.Weshare');
            $share_creators = $userM->find('all', array(
                'conditions' => array(
                    'id' => $share_creator_ids
                ),
                'fields' => $this->query_user_fields
            ));
            $share_creators = Hash::combine($share_creators, '{n}.User.id', '{n}.User');
            $user_share_comment_data = array('comments' => $comments, 'share_info' => $share_info, 'share_creators' => $share_creators);
            Cache::write($key, json_encode($user_share_comment_data));
            return $user_share_comment_data;
        }
        return json_decode($user_share_comment_data, true);
    }

    /**
     * @param $uid
     * @return array|mixed
     * 准备用户中心数据
     */
    public function prepare_user_share_info($uid) {
        $key = USER_SHARE_INFO_CACHE_KEY . '_' . $uid;
        $user_share_data = Cache::read($key);
        if (empty($user_share_data)) {
            $weshareM = ClassRegistry::init('Weshare');
            $orderM = ClassRegistry::init('Order');
            $commentM = ClassRegistry::init('Comment');
            $userM = ClassRegistry::init('User');
            $myCreateShares = $weshareM->find('all', array(
                'conditions' => array(
                    'creator' => $uid,
                    'status' => array(0, 1)
                ),
                'order' => array('created DESC')
            ));
            $my_create_share_ids = Hash::extract($myCreateShares, '{n}.Weshare.id');
            $orderStatus = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE);
            $joinShareOrder = $orderM->find('all', array(
                'conditions' => array(
                    'creator' => $uid,
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'status' => $orderStatus
                ),
                'fields' => array('member_id', 'id', 'status')
            ));
            $joinShareOrderIds = Hash::extract($joinShareOrder, '{n}.Order.id');
            $joinShareComments = $commentM->find('all', array(
                'conditions' => array(
                    'order_id' => $joinShareOrderIds,
                    'status' => COMMENT_SHOW_STATUS,
                    'type' => COMMENT_SHARE_TYPE,
                    'parent_id' => 0,
                    'user_id' => $uid
                )
            ));
            $joinShareComments = Hash::combine($joinShareComments, '{n}.Comment.order_id', '{n}.Comment');
            $joinShareOrderStatus = Hash::combine($joinShareOrder, '{n}.Order.member_id', '{n}.Order');
            $joinShareIds = Hash::extract($joinShareOrder, '{n}.Order.member_id');
            $joinShareIds = array_unique($joinShareIds);
            $myJoinShares = $weshareM->find('all', array(
                'conditions' => array(
                    'id' => $joinShareIds,
                    'status' => array(0, 1)
                ),
                'order' => array('created DESC')
            ));
            $creatorIds = Hash::extract($myJoinShares, '{n}.Weshare.creator');
            $creatorIds[] = $uid;
            $creators = $userM->find('all', array(
                'conditions' => array(
                    'id' => $creatorIds
                ),
                'fields' => $this->query_user_fileds
            ));
            $creators = Hash::combine($creators, '{n}.User.id', '{n}.User');
            $this->explode_share_imgs($myCreateShares);
            $this->explode_share_imgs($myJoinShares);
            $user_share_data = array('creators' => $creators, 'my_create_share_ids' => $my_create_share_ids, 'joinShareOrderStatus' => $joinShareOrderStatus, 'joinShareComments' => $joinShareComments, 'myJoinShares' => $myJoinShares, 'myCreateShares' => $myCreateShares);
            Cache::write($key, json_encode($user_share_data));
            return $user_share_data;
        }
        return json_decode($user_share_data, true);
    }

    /**
     * @param $shares
     * 获取数据后处理
     * 把图片的url拼接的字符串分隔成每个图片的url
     */
    private function explode_share_imgs(&$shares) {
        foreach ($shares as &$item) {
            $item['Weshare']['images'] = explode('|', $item['Weshare']['images']);
        }
    }

    /**
     * @param $uid
     * @return mixed
     * 获取分享者的爱心评价数量
     */
    public function get_sharer_comments_count($uid) {
        $key = SHARER_COMMENT_COUNT_DATA_CACHE_KEY . '_' . $uid;
        $cacheData = Cache::read($key);
        if (!empty($cacheData)) {
            return $cacheData;
        }
        $weshareM = ClassRegistry::init('Weshare');
        $commentM = ClassRegistry::init('Comment');
        $allShares = $weshareM->find('all', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => array(0, 1)
            ),
            'fields' => array('id')
        ));
        $share_ids = Hash::extract($allShares, '{n}.Weshare.id');
        $sharer_comment_count = $commentM->find('count', array(
            'conditions' => array(
                'type' => COMMENT_SHARE_TYPE,
                'data_id' => $share_ids,
                'status' => COMMENT_SHOW_STATUS,
                'parent_id' => 0,
                'not' => array('order_id' => null)
            )
        ));
        Cache::write($key, $sharer_comment_count);
        return $sharer_comment_count;
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
            //check comment type
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
            //save comment opt log
            $this->ShareUtil->save_comment_opt_log($comment_uid, $share_id, $comment_content);
            if ($comment_uid == $order_info['Order']['creator']) {
                $this->send_comment_notify($order_id, $share_id, $comment_content);
            }
            if (!empty($comment['Comment']['id'])) {
                $this->send_shareed_offer_notify($order_id, $share_id, $comment['Comment']['id']);
            }
            //clean cache
            //$cache_key = SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $sharer_id . '_0';
            //$cache_key = SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $sharer_id . '_1';
            Cache::write(SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $weshare_info['creator'] . '_0', '');
            Cache::write(SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $weshare_info['creator'] . '_1', '');
            //SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId;
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $share_id . '_1_1', '');
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $share_id . '_0_1', '');
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $share_id . '_1_0', '');
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $share_id . '_0_0', '');
        }
        //$key = SHARE_COMMENT_DATA_CACHE_KEY . '_' . $weshare_id;
        Cache::write(SHARE_COMMENT_DATA_CACHE_KEY . '_' . $share_id, '');
        return array('success' => true, 'comment' => $comment['Comment'], 'comment_reply' => $commentReply['CommentReply'], 'order_id' => $order_id);
    }

    /**
     * @param $weshareId
     * @param $limit
     * @param $offset
     * 创建新的分享之后发送模板消息
     */
    public function send_new_share_msg($weshareId, $limit = null, $offset = null) {
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
        $detail_url = WX_HOST . '/weshares/view/' . $weshareId;
        $sharer_name = $sharer_user_info['User']['nickname'];
        $product_name = $weshare['Weshare']['title'];
        $title = '关注的' . $sharer_name . '发起了';
        $remark = '点击详情，赶快加入' . $sharer_name . '的分享！';
        $followers = $this->load_fans_buy_sharer($weshare['Weshare']['creator'], $limit, $offset);
        $openIds = $this->Oauthbind->findWxServiceBindsByUids($followers);
        foreach ($openIds as $openId) {
            $this->process_send_share_msg($openId, $title, $product_name, $detail_url, $sharer_name, $remark);
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
            $title = $order_user_nickname . '你好，' . $share_creator_nickname . '分享的' . $weshare_info['Weshare']['title'] . '寄出了，请注意查收。' . $share_creator_nickname . '电话:' . $users[$share_creator]['mobilephone'];
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
     * @param $share_id
     * @param $refer_share_id
     * @param $msg
     * @param $address
     * 对自提点进行发货
     */
    public function send_group_share_product_arrival_msg($share_id, $refer_share_id, $msg, $address) {
        $weshareM = ClassRegistry::init('Weshare');
        $weshareInfo = $weshareM->find('all', array(
            'conditions' => array(
                'id' => array($share_id, $refer_share_id)
            )
        ));
        $weshareInfo = Hash::combine($weshareInfo, '{n}.Weshare.id', '{n}.Weshare');
        $shareInfo = $weshareInfo[$refer_share_id];
        $share_creator = $shareInfo['creator'];
        $orderShareInfo = $weshareInfo[$share_id];
        $order_share_creator = $orderShareInfo['creator'];
        $user_ids = array($share_creator, $order_share_creator);
        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $user_ids
            ),
            'fields' => array('id', 'nickname')
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        $userOauthBinds = $this->Oauthbind->find('all', array(
            'conditions' => array(
                'user_id' => $user_ids
            ),
            'fields' => array('user_id', 'oauth_openid')
        ));
        $userOauthBinds = Hash::combine($userOauthBinds, '{n}.Oauthbind.user_id', '{n}.Oauthbind.oauth_openid');
        $desc = '感谢大家对' . $users[$share_creator]['nickname'] . '的支持，分享快乐。';
        $detail_url = WX_HOST . '/weshares/view/' . $refer_share_id;
        $order_id = $share_id;
        $order_user_id = $order_share_creator;
        $open_id = $userOauthBinds[$order_user_id];
        $order_user_name = $users[$order_user_id]['nickname'];
        $title = $order_user_name . '你好，' . $msg;
        $conginess_name = $users[$order_user_id]['nickname'];
        $conginess_address = $address;
        $this->Weixin->send_share_product_arrival($open_id, $detail_url, $title, $order_id, $conginess_address, $conginess_name, $desc);
    }

    /**
     * @param $shareInfo
     * @param $msg
     * 到货提醒
     */
    public function send_share_product_arrive_msg($shareInfo, $msg) {
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
                'ship_mark' => array(SHARE_SHIP_SELF_ZITI_TAG, SHARE_SHIP_GROUP_TAG)
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
     * @param $limit
     * @param $offset
     * @return array
     * 加载粉丝数据
     */
    public function load_fans_buy_sharer($sharerId, $limit = null, $offset = null) {
        $userRelationM = ClassRegistry::init('UserRelation');
        $cond = array(
            'conditions' => array(
                'user_id' => $sharerId,
                'deleted' => DELETED_NO
            )
        );
        if ($limit != null && $offset != null) {
            $cond['limit'] = $limit;
            $cond['offset'] = $offset;
        }
        $relations = $userRelationM->find('all', $cond);
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
    public function process_send_share_msg($openId, $title, $productName, $detailUrl, $sharerName, $remark) {
        send_join_tuan_buy_msg(null, $title, $productName, $sharerName, $remark, $detailUrl, $openId);
    }

    /**
     * @param $weshareId
     * @return mixed
     * 计算后结算的款项
     */
    public function get_added_order_repaid_money($weshareId) {
        if (!is_array($weshareId)) {
            $weshareId = array($weshareId);
        }
        $orderM = ClassRegistry::init('Order');
        $addOrderResult = $orderM->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY_ADD,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_REFUND_DONE),
                'member_id' => $weshareId
            ),
            'fields' => array('SUM(total_all_price) as all_repaid_order_money'),
            'limit' => 100
        ));
        return $addOrderResult[0][0]['all_repaid_order_money'];
    }

    /**
     * @param $orderIds
     * @param $weshareId
     * @return mixed
     */
    public function get_group_order_repaid_money($orderIds, $weshareId) {
        $orderM = ClassRegistry::init('Order');
        $addOrderResult = $orderM->find('all', array(
            'conditions' => array(
                'parent_order_id' => $orderIds,
                'type' => ORDER_TYPE_WESHARE_BUY_ADD,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_REFUND_DONE),
                'member_id' => $weshareId
            ),
            'fields' => array('SUM(total_all_price) as all_repaid_order_money'),
        ));
        return $addOrderResult[0][0]['all_repaid_order_money'];
    }


    /**
     * @param $weshareId
     * @return float
     * 退款 金额
     */
    public function get_refund_money_by_weshare($weshareId) {
        if (!is_array($weshareId)) {
            $weshareId = array($weshareId);
        }
        $orderM = ClassRegistry::init('Order');
        $refundLogM = ClassRegistry::init('RefundLog');
        $refund_orders = $orderM->find('all', array(
            'conditions' => array(
                'member_id' => $weshareId,
                'status' => array(ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY),
                'type' => ORDER_TYPE_WESHARE_BUY,
                'deleted' => DELETED_NO
            ),
            'limit' => 1000
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
     * @param $share_id
     * @return array
     * 获取组团分享的信息
     */
    public function get_child_share_items($share_id) {
        $cache_key = SHARE_OFFLINE_ADDRESS_BUY_DATA_CACHE_KEY . '_' . $share_id;
        $child_share_data_json = Cache::read($cache_key);
        if (empty($child_share_data_json)) {
            $OrderM = ClassRegistry::init('Order');
            $UserM = ClassRegistry::init('User');
            $WeshareM = ClassRegistry::init('Weshare');
            $address_data = $this->ShareUtil->get_share_offline_address_detail($share_id);
            $share_ids = array();
            foreach ($address_data as $item_key => $item_address_data) {
                $share_ids[] = $item_key;
            }
            $share_infos = $WeshareM->find('all', array(
                'conditions' => array(
                    'id' => $share_ids
                ),
                'fields' => array('id', 'creator')
            ));
            $share_creators = Hash::extract($share_infos, '{n}.Weshare.creator');
            $share_infos = Hash::combine($share_infos, '{n}.Weshare.id', '{n}.Weshare');
            foreach ($address_data as $item_key => &$item_address_data) {
                $item_share_creator = $share_infos[$item_key]['creator'];
                $item_address_data['creator'] = $item_share_creator;
                $item_address_data['order_status'] = $share_infos[$item_key]['status'];
            }
            $group_share_order = $OrderM->find('all', array(
                'conditions' => array(
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'member_id' => $share_ids,
                    'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY)
                ),
                'fields' => array('id', 'creator', 'member_id')
            ));
            $user_ids = Hash::extract($group_share_order, '{n}.Order.creator');
            $user_ids = array_merge($user_ids, $share_creators);
            $user_ids = array_unique($user_ids);
            $user_infos = $UserM->find('all', array(
                'conditions' => array(
                    'id' => $user_ids
                ),
                'fields' => array('id', 'nickname', 'image', 'is_proxy', 'mobilephone')
            ));
            $user_infos = Hash::combine($user_infos, '{n}.User.id', '{n}.User');
            foreach ($group_share_order as $order_item) {
                $member_id = $order_item['Order']['member_id'];
                $creator = $order_item['Order']['creator'];
                if (!isset($address_data[$member_id]['join_users'])) {
                    $address_data[$member_id]['join_users'] = array();
                }
                if (!in_array($creator, $address_data[$member_id]['join_users'])) {
                    $address_data[$member_id]['join_users'][] = $creator;
                }
            }
            $child_share_data = array('child_share_data' => $address_data, 'child_share_user_infos' => $user_infos, 'child_share_ids' => $share_ids);
            $child_share_data_json = json_encode($child_share_data);
            Cache::write($cache_key, $child_share_data_json);
            return $child_share_data;
        }
        return json_decode($child_share_data_json, true);
    }

    //先不进行缓存
    public function get_product_id_map_by_origin_ids($share_id) {
        $weshareProductM = ClassRegistry::init('WeshareProduct');
        $query_sql = 'select id, origin_product_id from cake_weshare_products where origin_product_id in (select id from cake_weshare_products where weshare_id=' . $share_id . ')';
        $product_id_map = $weshareProductM->query($query_sql);
        $product_id_map = Hash::combine($product_id_map, '{n}.cake_weshare_products.id', '{n}.cake_weshare_products.origin_product_id');
        return $product_id_map;
    }

    /**
     * @param $share_id
     * @param $refer_share_id
     * @return mixed
     * 获取子分享的统计数据
     */
    public function get_child_share_summery($share_id, $refer_share_id) {
        $key = GROUP_SHARE_ORDER_SUMMERY_DATA_CACHE_KEY . '_' . $share_id;
        $share_summery_data_str = Cache::read($key, '');
        if (empty($share_summery_data_str)) {
            $this->Weshare = ClassRegistry::init('Weshare');
            $this->Order = ClassRegistry::init('Order');
            $this->User = ClassRegistry::init('User');
            $this->Cart = ClassRegistry::init('Cart');
            $this->Oauthbind = ClassRegistry::init('Oauthbind');
            $this->WeshareProduct = ClassRegistry::init('WeshareProduct');
            $this->RebateTrackLog = ClassRegistry::init('RebateTrackLog');
            $product_id_map = $this->get_product_id_map_by_origin_ids($refer_share_id);
            $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY);
            $sort = array('created DESC');
            $orders = $this->Order->find('all', array(
                'conditions' => array(
                    'member_id' => $share_id,
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'status' => $order_status,
                    'deleted' => DELETED_NO
                ),
                'fields' => $this->$query_share_info_order_fields,
                'order' => $sort,
                'limit' => 1000
            ));
            $orderIds = Hash::extract($orders, '{n}.Order.id');
            $cateIds = Hash::extract($orders, '{n}.Order.cate_id');
            $orderIds = array_unique($orderIds);
            $cateIds = array_unique($cateIds);
            $rebateLogs = $this->RebateTrackLog->find('all', array(
                'conditions' => array(
                    'id' => $cateIds
                ),
                'fields' => array('id', 'sharer'),
                'limit' => 1000
            ));

            $rebateLogs = Hash::combine($rebateLogs, '{n}.RebateTrackLog.id', '{n}.RebateTrackLog.sharer');
            $orders = Hash::combine($orders, '{n}.Order.id', '{n}.Order');
            $carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $orderIds,
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'not' => array('order_id' => null, 'order_id' => '')
                ),
                'fields' => array('id', 'name', 'order_id', 'num', 'product_id', 'price', 'confirm_price', 'tag_id'),
                'limit' => 2000
            ));
            $realTotalPrice = 0;
            $summeryTotalPrice = 0;
            $couponPrice = 0;
            $shipFee = 0;
            foreach ($orders as $order_item) {
                $realTotalPrice = $realTotalPrice + $order_item['total_all_price'];
                $summeryTotalPrice = $summeryTotalPrice + $order_item['total_price'];
                $couponPrice = $couponPrice + $order_item['coupon_total'];
                $shipFee = $shipFee + $order_item['ship_fee'];
            }
            $product_buy_num = array('details' => array());
            foreach ($carts as $item) {
                $order_id = $item['Cart']['order_id'];
                $product_id = $item['Cart']['product_id'];
                //get product map id
                if (!empty($product_id_map[$product_id])) {
                    $product_id = $product_id_map[$product_id];
                }
                $cart_num = $item['Cart']['num'];
                $cart_price = $item['Cart']['price'];
                $cart_name = $item['Cart']['name'];
                if (!isset($product_buy_num['details'][$product_id])) $product_buy_num['details'][$product_id] = array('num' => 0, 'total_price' => 0, 'name' => $cart_name);
                $product_buy_num['details'][$product_id]['num'] = $product_buy_num['details'][$product_id]['num'] + $cart_num;
                $totalPrice = $cart_num * $cart_price;
                $product_buy_num['details'][$product_id]['total_price'] = $product_buy_num['details'][$product_id]['total_price'] + $totalPrice;
                $order_cart_map[$order_id][] = $item['Cart'];
            }
            $product_buy_num['all_buy_user_count'] = count($orders);
            $product_buy_num['all_total_price'] = $summeryTotalPrice;
            $product_buy_num['real_total_price'] = $realTotalPrice;
            $product_buy_num['all_coupon_price'] = $couponPrice / 100;
            $share_rebate_money = $this->ShareUtil->get_share_rebate_money($share_id);
            $refund_money = $this->get_refund_money_by_weshare($share_id);
            $share_summery_data = array('summery' => $product_buy_num, 'rebate_logs' => $rebateLogs, 'share_rebate_money' => $share_rebate_money, 'refund_money' => $refund_money);
            Cache::write($key, json_encode($share_summery_data));
            return $share_summery_data;
        }
        return json_decode($share_summery_data_str, true);
    }

    /**
     * @param $weshareId
     * @param $uid
     * @return array
     * 获取分享的分页信息
     */
    public function get_share_order_page_info($weshareId, $uid) {
        $order_count = $this->get_share_all_buy_count($weshareId, $uid);
        $page_count = ceil($order_count / $this->share_order_count);
        $page_info = array('order_count' => $order_count, 'page_count' => $page_count);
        return $page_info;
    }

    public function get_share_buy_summery($shareId) {
        $key  = SHARE_BUY_SUMMERY_INFO_CACHE_KEY.'_'.$shareId;
        $cacheData = Cache::read($key);
        if(empty($cacheData)){
            $product_ids = $this->get_share_pids($shareId);
            $sql = 'select sum(num), product_id from cake_carts where type=9 and status in (1,2,3,4,9,14) and product_id in (' . implode(',', $product_ids) . ') group by product_id';
            $cartM = ClassRegistry::init('Cart');
            $result = $cartM->query($sql);
            $summery_result = array();
            foreach($result as $item){
                $item_pid = $item['cake_carts']['product_id'];
                $item_count = $item[0]['sum(num)'];
                if(!isset($summery_result[$item_pid])){
                    $summery_result[$item_pid] = array();
                }
                $summery_result[$item_pid]['num'] = $item_count;
            }
            $summery = array('details' => $summery_result);
            Cache::write($key, json_encode($summery));
            return $summery;
        }
        return json_decode($cacheData, true);
    }

    public function get_share_pids($weshareId){
        $weshareProductM = ClassRegistry::init('WeshareProduct');
        $products = $weshareProductM->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshareId,
                'deleted' => DELETED_NO
            ),
            'fields' => array('id')
        ));
        return Hash::extract($products,'{n}.WeshareProduct.id');
    }

    /**
     * @param $weshareId
     * @param $uid
     * @return array
     * 分享详情页面 用户的数据先行加载
     *
     */
    public function get_current_user_share_order_data($weshareId, $uid) {
        //check $uid
        $key = USER_SHARE_ORDER_INFO_CACHE_KEY . '_' . $weshareId . '_' . $uid;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY);
            $sort = array('status' => 'desc', 'created' => 'desc');
            $query_order_cond = array(
                'conditions' => array(
                    'member_id' => $weshareId,
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'status' => $order_status,
                    'deleted' => DELETED_NO,
                    'creator' => $uid
                ),
                'fields' => $this->query_share_info_order_fields,
                'order' => $sort);
            $data = $this->load_share_order_data($query_order_cond);
            Cache::write($key, json_encode($data));
            return $data;
        }
        return json_decode($cacheData, true);
    }

    /**
     * @param $weshareId
     * @param $page
     * @param $uid 当前用户数据
     * @return array
     */
    public function get_share_detail_view_orders($weshareId, $page, $uid) {
        //todo cache it 只缓存第一页的数据，实时更新第一页的缓存(分段缓存，细粒度缓存)
        $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY);
        $sort = array('created DESC');
        $query_order_cond = array(
            'conditions' => array(
                'member_id' => $weshareId,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => $order_status,
                'deleted' => DELETED_NO,
                'not' => array('creator' => $uid)
            ),
            'fields' => $this->query_share_info_order_fields,
            'limit' => $this->share_order_count,
            'offset' => ($page - 1) * $this->share_order_count,
            'order' => $sort);
        $result = $this->load_share_order_data($query_order_cond);
        if ($page == 1) {
            $result['page_info'] = $this->get_share_order_page_info($weshareId, $uid);
        }
        return $result;
    }

    /**
     * @param $cond
     * @return array
     * 获取分享的数据
     */
    private function load_share_order_data($cond) {
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $userM = ClassRegistry::init('User');
        $orders = $orderM->find('all', $cond);
        $orderIds = Hash::extract($orders, '{n}.Order.id');
        $order_cart_map = array();
        $users = array();
        if ($orders) {
            $userIds = Hash::extract($orders, '{n}.Order.creator');
            $cateIds = Hash::extract($orders, '{n}.Order.cate_id');
            $carts = $cartM->find('all', array(
                'conditions' => array(
                    'order_id' => $orderIds,
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'not' => array('order_id' => null, 'order_id' => '')
                ),
                'fields' => array('id', 'name', 'order_id', 'num', 'product_id', 'price', 'confirm_price', 'tag_id')
            ));
            foreach ($carts as $item) {
                $order_id = $item['Cart']['order_id'];
                $order_cart_map[$order_id][] = $item['Cart'];
            }
            $rebateLogM = ClassRegistry::init('RebateTrackLog');
            $rebateLogs = $rebateLogM->find('all', array(
                'conditions' => array(
                    'id' => $cateIds
                ),
                'fields' => array('id', 'sharer')
            ));
            $rebateSharerIds = Hash::extract($rebateLogs, '{n}.RebateTrackLog.sharer');
            $rebateLogs = Hash::combine($rebateLogs, '{n}.RebateTrackLog.id', '{n}.RebateTrackLog.sharer');
            $userIds = array_merge($userIds, $rebateSharerIds);
            $userIds = array_unique($userIds);
            $users = $userM->find('all', array(
                'conditions' => array(
                    'id' => $userIds
                ),
                'recursive' => 1, //int
                'fields' => $this->query_user_fields,
            ));
            $users = Hash::combine($users, '{n}.User.id', '{n}.User');
            $orders = Hash::combine($orders, '{n}.Order.id', '{n}.Order');
            usort($orders, function ($a, $b) {
                return ($a['id'] < $b['id']) ? -1 : 1;
            });
        }
        $result_data = array('users' => $users, 'orders' => $orders, 'order_cart_map' => $order_cart_map, 'rebate_logs' => $rebateLogs);
        return $result_data;
    }


    /**
     * @param $weshareId
     * @param $is_me
     * @param bool $division
     * @param bool $export
     * @return array
     * 获取分享的订单信息
     *
     * 这个里面的逻辑和上面统计子分享数据逻辑有共同处，修改的时候注意
     */
    public function get_share_order_for_show($weshareId, $is_me, $division = false, $export = false) {
        if ($division) {
            $key = SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId . '_1';
        } else {
            $key = SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId . '_0';
        }
        if ($export) {
            $key = $key . '_0';
        } else {
            $key = $key . '_1';
        }
        $share_order_data = Cache::read($key);
        if (empty($share_order_data)) {
            $this->Weshare = ClassRegistry::init('Weshare');
            $this->Order = ClassRegistry::init('Order');
            $this->User = ClassRegistry::init('User');
            $this->Cart = ClassRegistry::init('Cart');
            $this->Oauthbind = ClassRegistry::init('Oauthbind');
            $this->WeshareProduct = ClassRegistry::init('WeshareProduct');
            $this->RebateTrackLog = ClassRegistry::init('RebateTrackLog');
            $product_buy_num = array('details' => array());
            $order_cart_map = array();
            if ($export) {
                $order_status = array(ORDER_STATUS_PAID);
            } else {
                $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY);
            }
            $sort = array('created DESC');
            $orders = $this->Order->find('all', array(
                'conditions' => array(
                    'member_id' => $weshareId,
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'status' => $order_status,
                    'deleted' => DELETED_NO
                ),
                'fields' => $this->$query_share_info_order_fields,
                'order' => $sort
            ));
            $orderIds = Hash::extract($orders, '{n}.Order.id');
            $cateIds = Hash::extract($orders, '{n}.Order.cate_id');
            $userIds = Hash::extract($orders, '{n}.Order.creator');
            $orderIds = array_unique($orderIds);
            $userIds = array_unique($userIds);
            $cateIds = array_unique($cateIds);
            $rebateLogs = $this->RebateTrackLog->find('all', array(
                'conditions' => array(
                    'id' => $cateIds
                ),
                'fields' => array('id', 'sharer')
            ));
            $rebateSharerIds = Hash::extract($rebateLogs, '{n}.RebateTrackLog.sharer');
            $rebateLogs = Hash::combine($rebateLogs, '{n}.RebateTrackLog.id', '{n}.RebateTrackLog.sharer');
            $userIds = array_merge($userIds, $rebateSharerIds);
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
                'fields' => array('id', 'name', 'order_id', 'num', 'product_id', 'price', 'confirm_price', 'tag_id')
            ));
            $realTotalPrice = 0;
            $summeryTotalPrice = 0;
            $couponPrice = 0;
            $shipFee = 0;
            foreach ($orders as $order_item) {
                $realTotalPrice = $realTotalPrice + $order_item['total_all_price'];
                $summeryTotalPrice = $summeryTotalPrice + $order_item['total_price'];
                $couponPrice = $couponPrice + $order_item['coupon_total'];
                $shipFee = $shipFee + $order_item['ship_fee'];
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
            $product_buy_num['all_buy_user_count'] = count($orders);
            $product_buy_num['all_total_price'] = $summeryTotalPrice;
            $product_buy_num['real_total_price'] = $realTotalPrice;
            $product_buy_num['all_coupon_price'] = $couponPrice / 100;
            $product_buy_num['all_ship_fee'] = $shipFee;
            $users = Hash::combine($users, '{n}.User.id', '{n}.User');
            if ($division) {
                $kuaidi_orders = array_filter($orders, "share_kuaidi_order_filter");
                if (!$export) {
                    if ($kuaidi_orders) {
                        usort($kuaidi_orders, function ($a, $b) {
                            return ($a['status'] < $b['status']) ? -1 : 1;
                        });
                    }
                }
                $self_ziti_orders = array_filter($orders, "share_self_ziti_order_filter");
                $pys_ziti_orders = array_filter($orders, "share_pys_ziti_order_filter");
                $orders = array('origin_orders' => $orders, SHARE_SHIP_KUAIDI_TAG => $kuaidi_orders, SHARE_SHIP_SELF_ZITI_TAG => $self_ziti_orders, SHARE_SHIP_PYS_ZITI_TAG => $pys_ziti_orders);
            }
            //show order ship type name
            $shipTypes = ShipAddress::ship_type_list();
            $share_order_data = array('users' => $users, 'orders' => $orders, 'order_cart_map' => $order_cart_map, 'summery' => $product_buy_num, 'ship_types' => $shipTypes, 'rebate_logs' => $rebateLogs);
            if ($division) {
                $share_rebate_money = $this->ShareUtil->get_share_rebate_money($weshareId);
                $share_order_data['share_rebate_money'] = $share_rebate_money;
            }
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
        $limit_date = date('Y-m-d', strtotime("-7 days"));
        $cond = array(
            'status' => ORDER_STATUS_RECEIVED,
            'type' => ORDER_TYPE_WESHARE_BUY
        );
        if (!empty($weshareId)) {
            $cond['member_id'] = $weshareId;
        } else {
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
    public function change_status_and_send_to_comment_msg($weshareId = null) {
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
     * @param $shareId
     * @return array|mixed
     * load share recommend data
     */
    public function load_share_recommend_data($shareId) {
        $key = SHARE_RECOMMEND_DATA_CACHE_KEY . '_' . $shareId;
        $recommendData = Cache::read($key);
        if (empty($recommendData)) {
            $recommendLogM = ClassRegistry::init('RecommendLog');
            $userM = ClassRegistry::init('User');
            $shareRecommendData = $recommendLogM->find('all', array(
                'conditions' => array(
                    'data_type' => RECOMMEND_SHARE,
                    'data_id' => $shareId
                ),
                'group' => array('user_id')
            ));
            $user_ids = Hash::extract($shareRecommendData, '{n}.RecommendLog.user_id');
            $recommend_users = $userM->find('all', array(
                'conditions' => array(
                    'id' => $user_ids
                ),
                'fields' => array('id', 'nickname', 'image')
            ));
            $recommendData = Hash::extract($recommend_users, '{n}.User');
            Cache::write($key, json_encode($recommendData));
            return $recommendData;
        }
        return json_decode($recommendData, true);
    }


    /**
     * @param $orders
     * 通知下单用户去评论模板消息
     */
    private function process_send_to_comment_msg($orders) {
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
    public function send_comment_mutual_msg($comment_uid, $reply_id, $content, $share_id, $order_id) {
        $uid_name_map = $this->get_users_nickname(array($comment_uid, $reply_id));
        $title = $uid_name_map[$reply_id] . '你好，' . $uid_name_map[$comment_uid] . '对你说：' . $content;
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
        $title = $uid_name_map[$share_creator] . '你好，' . $uid_name_map[$order_creator] . '说，感谢' . $uid_name_map[$share_creator] . '，' . $comment_content . '。';
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
    public function send_comment_notify_buyer($order_id, $weshare_id) {
        $order_info = $this->get_order_info($order_id);
        $order_creator = $order_info['creator'];
        $share_info = $this->get_weshare_info($weshare_id);
        $share_creator = $share_info['creator'];
        $uid_name_map = $this->get_users_nickname(array($order_creator, $share_creator));
        $open_id_map = $this->get_open_ids(array($order_creator));
        $open_id = $open_id_map[$order_creator];
        //分享的XXX
        $sharer_offer_map = $this->sharer_has_offer(array($share_creator));
        $title = $uid_name_map[$order_creator] . '你好，' . $uid_name_map[$share_creator] . '说，分享的' . $share_info['title'] . '收到了吧，给个爱心评价呢！';
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
    public function send_comment_reply_notify($order_id, $weshare_id, $reply_content) {
        $order_info = $this->get_order_info($order_id);
        $order_creator = $order_info['creator'];
        $share_info = $this->get_weshare_info($weshare_id);
        $share_creator = $share_info['creator'];
        $uid_name_map = $this->get_users_nickname(array($order_creator, $share_creator));
        $open_id_map = $this->get_open_ids(array($share_creator, $order_creator));
        $open_id = $open_id_map[$order_creator];
        $title = $uid_name_map[$order_creator] . '你好，' . $uid_name_map[$share_creator] . '说，谢谢你对我的支持，' . $reply_content . '。';
        $order_id = $order_info['id'];
        $order_date = $order_info['created'];
        $desc = '分享，让生活更美。点击查看。';
        $detail_url = $this->get_weshares_detail_url($weshare_id);
        $this->Weixin->send_comment_template_msg($open_id, $detail_url, $title, $order_id, $order_date, $desc);
    }

    /**
     * @param $shareId
     * @param $exclude_uid
     * @return mixed | int
     * 获取分享的总购买份数
     */
    public function get_share_all_buy_count($shareId, $exclude_uid = 0) {
        $orderM = ClassRegistry::init('Order');
        $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY);
        if ($exclude_uid == 0) {
            $key = SHARE_ORDER_COUNT_DATA_CACHE_KEY . '_' . $shareId;
            $cacheData = Cache::read($key);
            if (!empty($cacheData)) {
                return $cacheData;
            }
            $shareOrderCount = $orderM->find('count', array(
                'conditions' => array(
                    'member_id' => $shareId,
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'status' => $order_status,
                    'deleted' => DELETED_NO
                )
            ));
            Cache::write($key, $shareOrderCount);
            return $shareOrderCount;
        }
        $shareOrderCount = $orderM->find('count', array(
            'conditions' => array(
                'member_id' => $shareId,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => $order_status,
                'deleted' => DELETED_NO,
                'not' => array('creator' => $exclude_uid)
            )
        ));
        return $shareOrderCount;
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
            $focus_count = $userRelationM->find('count', array(
                'conditions' => array(
                    'follow_id' => $uid
                )
            ));
            $comments_count = $this->get_sharer_comments_count($uid);
            $summery_data = array('share_count' => count($weshares), 'follower_count' => $fans_count, 'focus_count' => $focus_count, 'comment_count' => $comments_count);
            Cache::write($key, json_encode($summery_data));
            return $summery_data;
        }
        return json_decode($summery_data, true);
    }

    /**
     * @param $uid
     * @param $limit
     * @return mixed
     * 获取用户关注
     */
    public function get_user_focus($uid, $limit = 0) {
        $key = SHARER_FOCUS_DATA_CACHE_KEY . '_' . $uid . '_' . $limit;
        $focus_data = Cache::read($key);
        if (empty($focus_data)) {
            $userRelationM = ClassRegistry::init('UserRelation');
            $userM = ClassRegistry::init('User');
            $queryCond = array(
                'conditions' => array(
                    'follow_id' => $uid,
                    'deleted' => DELETED_NO
                )
            );
            if ($limit > 0) {
                $queryCond['limit'] = $limit;
            }
            $relations = $userRelationM->find('all', $queryCond);
            $focus_id = Hash::extract($relations, '{n}.UserRelation.user_id');
            $focus_data = $userM->find('all', array(
                'conditions' => array(
                    'id' => $focus_id
                ),
                'fields' => $this->query_user_fields
            ));
            $focus_data = Hash::extract($focus_data, '{n}.User');
            Cache::write($key, json_encode($focus_data));
            return $focus_data;
        }
        return json_decode($focus_data, true);
    }

    /**
     * @param $uid
     * @param $limit
     * 获取用户粉丝的信息
     * @return array|mixed
     */
    public function get_user_fans_data($uid, $limit = 0) {
        $key = SHARER_FANS_DATA_CACHE_KEY . '_' . $uid . '_' . $limit;
        $fans_data = Cache::read($key);
        if (empty($fans_data)) {
            $userRelationM = ClassRegistry::init('UserRelation');
            $userM = ClassRegistry::init('User');
            $queryCond = array(
                'conditions' => array(
                    'user_id' => $uid,
                    'deleted' => DELETED_NO
                )
            );
            if ($limit > 0) {
                $queryCond['limit'] = $limit;
            }
            $relations = $userRelationM->find('all', $queryCond);
            $fans_id = Hash::extract($relations, '{n}.UserRelation.follow_id');
            $relation_map = Hash::combine($relations, '{n}.UserRelation.id', '{n}.UserRelation.follow_id');
            $relation_map = array_unique($relation_map);
            usort($relation_map, 'sort_data_by_id');
            $fans_data = $userM->find('all', array(
                'conditions' => array(
                    'id' => $fans_id
                ),
                'fields' => $this->query_user_fields,
                'order' => array('id DESC')
            ));

            $fans_data = Hash::combine($fans_data, '{n}.User.id', '{n}.User');
            $fans_data = array('fans_data' => $fans_data, 'relations' => $relation_map);
            Cache::write($key, json_encode($fans_data));
            return $fans_data;
        }
        return json_decode($fans_data, true);
    }

    /**
     * @param $weshare_info
     * @param $msg_content
     * notify has buy user
     */
    public function send_notify_buy_user_msg($weshare_info, $msg_content) {
        $buy_uids = $this->get_has_buy_user($weshare_info['id']);
        $buy_open_ids = $this->get_open_ids($buy_uids);
        $tuan_leader_name = $weshare_info['creator']['nickname'];
        $remark = '点击查看详情！';
        $deatil_url = $this->get_weshares_detail_url($weshare_info['id']);
        $product_name = $weshare_info['title'];
        foreach ($buy_open_ids as $open_id) {
            $this->Weixin->send_share_buy_complete_msg($open_id, $msg_content, $product_name, $tuan_leader_name, $remark, $deatil_url);
        }
    }

    /**
     * @param $weshare_info
     * @param $msg_content
     * @param $limit
     * @param $offset
     * 发送团购进度消息
     */
    public function send_buy_percent_msg($weshare_info, $msg_content, $limit = null, $offset = null) {
        $share_creator = $weshare_info['creator']['id'];
        $fans_ids = $this->load_fans_buy_sharer($share_creator, $limit, $offset);
        $fans_data_nickname = $this->get_users_nickname($fans_ids);
        $fans_data_ids = $fans_ids;
        $fans_open_ids = $this->get_open_ids($fans_data_ids);
        $product_name = $weshare_info['title'];
        $tuan_leader_name = $weshare_info['creator']['nickname'];
        $remark = '点击详情，赶快加入' . $tuan_leader_name . '的分享！';
        $deatil_url = $this->get_weshares_detail_url($weshare_info['id']);
        $already_buy_uids = $this->get_has_buy_user($weshare_info['id']);
        foreach ($fans_open_ids as $uid => $open_id) {
            if (!in_array($uid, $already_buy_uids)) {
                $title = $fans_data_nickname[$uid] . '你好，' . $msg_content;
                $this->Weixin->send_share_buy_complete_msg($open_id, $title, $product_name, $tuan_leader_name, $remark, $deatil_url);
            }
        }
    }

    /**
     * @param $recommend_user
     * @param $share_id
     * @param $memo
     *
     */
    public function send_recommend_msg($recommend_user, $share_id, $memo) {
        $fansPageInfo = $this->get_user_relation_page_info($recommend_user);
        $pageCount = $fansPageInfo['pageCount'];
        $pageSize = $fansPageInfo['pageSize'];
        $queue = new SaeTaskQueue('share');
        $queue->addTask("/task/process_send_recommend_msg/" . $share_id . '/' . $recommend_user . '/' . $pageCount . '/' . $pageSize, "memo=" . $memo);
        //将任务推入队列
        $ret = $queue->push();
        //任务添加失败时输出错误码和错误信息
        if ($ret === false) {
            $this->log('add task queue error ' . json_encode(array($queue->errno(), $queue->errmsg())));
        }
        return $ret;
    }

    /**
     * @param $weshareId
     * @param $recommend_user
     * @param $memo
     * @param null $limit
     * @param null $offset
     */
    public function send_recommend_msg_task($weshareId, $recommend_user, $memo, $limit = null, $offset = null) {
        $this->Weshare = ClassRegistry::init('Weshare');
        $this->User = ClassRegistry::init('User');
        $this->Oauthbind = ClassRegistry::init('Oauthbind');
        $weshare = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $weshareId
            )
        ));
        $sharer = $weshare['Weshare']['creator'];
        $user_info = $this->User->find('all', array(
            'conditions' => array(
                'id' => array($sharer, $recommend_user)
            ),
            'fields' => array(
                'id', 'nickname'
            )
        ));
        $user_info = Hash::combine($user_info, '{n}.User.id', '{n}.User');
        $sharer_user_info = $user_info[$sharer];
        $detail_url = WX_HOST . '/weshares/view/' . $weshareId . '?recommend=' . $recommend_user;
        $sharer_name = $sharer_user_info['nickname'];
        $recommend_name = $user_info[$recommend_user]['nickname'];
        $product_name = $weshare['Weshare']['title'];
        $title = '关注的' . $recommend_name . '推荐了' . $sharer_name . '的';
        $remark = $memo . '，点击赶快加入' . $sharer_name . '的分享！';
        $followers = $this->load_fans_buy_sharer($recommend_user, $limit, $offset);
        $hasBuyUsers = $this->get_has_buy_user($weshareId);
        $followers = array_diff($followers, $hasBuyUsers);
        //check msg logs filter users
        $followers = $this->check_msg_log_and_filter_user($weshareId, $followers, MSG_LOG_RECOMMEND_TYPE);
        $openIds = $this->Oauthbind->findWxServiceBindsByUids($followers);
        foreach ($openIds as $openId) {
            $this->Weixin->send_recommend_template_msg($openId, $detail_url, $remark, $title, $product_name, $sharer_name);
        }
    }

    /**
     * @param $data_id
     * @param $user_ids
     * @param $type
     * @return array
     * 查找用户消息记录，过滤用户
     */
    public function check_msg_log_and_filter_user($data_id, $user_ids, $type) {
        $msgLogM = ClassRegistry::init('MsgLog');
        //添加更多的过滤条件 (比如每天只收一次)
        //todo 记录过多的情况处理（暂时不会出现这个问题）
        $msgLogs = $msgLogM->find('all', array(
            'conditions' => array(
                'data_id' => $data_id,
                'data_type' => $type
            ),
            'fields' => array('user_id'),
            'limit' => 3000
        ));
        $msgLogUserIds = Hash::extract($msgLogs, '{n}.MsgLog.user_id');
        $user_ids = array_diff($user_ids, $msgLogUserIds);
        $saveMsgLogData = array();
        foreach ($user_ids as $item_uid) {
            $saveMsgLogData[] = array(
                'data_id' => $data_id,
                'data_type' => $type,
                'user_id' => $item_uid,
                'created' => date('Y-m-d H:i:s'),
            );
        }
        $msgLogM->saveAll($saveMsgLogData);
        return $user_ids;
    }

    /**
     * @param $sharer_id
     * @param $follow_id
     * 获取用户关注信息
     */
    public function check_user_subscribe($sharer_id, $follow_id) {
        return $this->ShareUtil->check_user_is_subscribe($sharer_id, $follow_id);
    }

    /**
     * @param $sharer_id
     * @param $follow_id
     * @param $type
     * 关注
     */
    public function subscribe_sharer($sharer_id, $follow_id, $type = 'SUB') {
        if (!$this->ShareUtil->check_user_is_subscribe($sharer_id, $follow_id)) {
            $this->ShareUtil->save_relation($sharer_id, $follow_id, $type);
            Cache::write(SHARE_USER_SUMMERY_CACHE_KEY . '_' . $sharer_id, '');
            Cache::write(SHARER_FOCUS_DATA_CACHE_KEY . '_' . $follow_id . '_100', '');
            Cache::write(SHARER_FANS_DATA_CACHE_KEY . '_' . $sharer_id . '_100', '');
            $this->send_sub_template_msg($sharer_id, $follow_id);
        }
        //$this->ShareUtil->usedUserSubSharerReason($follow_id);;
    }

    public function subscribe_sharer_by_share($share_id, $follow_id, $type = 'SUB') {
        $share_info = $this->get_weshare_info($share_id);
        $sharer_id = $share_info['creator'];
        $this->subscribe_sharer($sharer_id, $follow_id, $type);
    }

    /**
     * @param $sharer_id
     * @param $follow_id
     * 取消关注
     */
    public function unsubscribe_sharer($sharer_id, $follow_id) {
        Cache::write(SHARE_USER_SUMMERY_CACHE_KEY . '_' . $sharer_id, '');
        Cache::write(SHARER_FOCUS_DATA_CACHE_KEY . '_' . $follow_id . '_100', '');
        Cache::write(SHARER_FANS_DATA_CACHE_KEY . '_' . $sharer_id . '_100', '');
        $this->ShareUtil->delete_relation($sharer_id, $follow_id);
    }

    /**
     * @param $sharer_id
     * @param $follow_id
     */
    public function send_sub_template_msg($sharer_id, $follow_id) {
        $openid_map = $this->get_open_ids(array($sharer_id));
        $open_id = $openid_map[$sharer_id];
        $nickname_map = $this->get_users_nickname(array($sharer_id, $follow_id));
        $member_name = $nickname_map[$follow_id];
        $title = $nickname_map[$sharer_id] . '你好，' . $member_name . '刚刚关注了你。';
        $detail_url = $this->get_sharer_detail_url($follow_id);
        $desc = '点击详情，查看我的粉丝！';
        $this->Weixin->send_new_member_tip($open_id, $detail_url, $title, $member_name, $desc);
    }

    /**
     * @param $uid
     * @return array
     * 获取粉丝信息的分页数据
     */
    public function get_user_relation_page_info($uid) {
        $UserRelationM = ClassRegistry::init('UserRelation');
        $totalRecords = $UserRelationM->find('count', array(
            'conditions' => array(
                'user_id' => $uid,
                'deleted' => DELETED_NO
            )
        ));
        $pageSize = 300;
        $pageCount = ($totalRecords + $pageSize - 1) / $pageSize;
        $pageCount = intval($pageCount);
        return array('pageCount' => $pageCount, 'pageSize' => $pageSize);
    }

    /**
     * @param $uid
     * @return array
     * 获取用户分享信息
     */
    public function get_user_weshares($uid) {
        $weshareM = ClassRegistry::init('Weshare');
        $weshares = $weshareM->find('all', array(
            'conditions' => array(
                'creator' => $uid
            ),
            'order' => array('id DESC'),
            'limit' => 500
        ));
        return $weshares;
    }

    /**
     * @param $share_id
     * @return array
     *
     * 获取本次分享已经购买的用户
     */
    public function get_has_buy_user($share_id) {
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => array(ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY),
                'member_id' => $share_id
            ),
            'fields' => $this->query_order_fields,
            'limit' => 300
        ));
        $uids = Hash::extract($orders, '{n}.Order.creator');
        return $uids;
    }

    /**
     * @param $share_ids
     * @return array
     * 分享和已经购买用户的对应关系
     */
    public function get_has_buy_user_map($share_ids) {
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => array(ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY),
                'member_id' => $share_ids
            ),
            'fields' => $this->query_order_fields,
            'limit' => 1000
        ));
        $share_user_map = array();
        $all_user_ids = array();
        foreach ($orders as $order_item) {
            $share_id = $order_item['Order']['member_id'];
            $user_id = $order_item['Order']['creator'];
            if (!isset($share_user_map[$share_id])) {
                $share_user_map[$share_id] = array();
            }
            if (!in_array($user_id, $share_user_map[$share_id])) {
                $share_user_map[$share_id][] = $user_id;
            }
            $all_user_ids[] = $user_id;
        }
        return array('all_user_ids' => $all_user_ids, 'share_user_map' => $share_user_map);
    }

    /**
     * @param $sharer_id
     * @return string
     * 获取个人中心url
     */
    public function get_sharer_detail_url($sharer_id) {
        return WX_HOST . '/weshares/user_share_info/' . $sharer_id;
    }

    /**
     * @param $weshareId
     * @return string
     * 获取分享的地址
     */
    public function get_weshares_detail_url($weshareId) {
        return WX_HOST . '/weshares/view/' . $weshareId;
    }

    public function get_open_id($uid) {
        $oauthBindM = ClassRegistry::init('Oauthbind');
        $uid_openid_map = $oauthBindM->findWxServiceBindMapsByUids(array($uid));
        return $uid_openid_map[$uid];
    }

    public function get_open_ids($uids) {
        $oauthBindM = ClassRegistry::init('Oauthbind');
        $uid_openid_map = $oauthBindM->findWxServiceBindMapsByUids($uids);
        return $uid_openid_map;
    }

    private function findCarts($orderId) {
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

    public function get_users_nickname($uids) {
        $userM = ClassRegistry::init('User');
        return $userM->findNicknamesMap($uids);
    }

    public function get_user_nickname($uid) {
        $userM = ClassRegistry::init('User');
        return $userM->findNicknamesOfUid($uid);
    }

    /**
     * @param $share_id
     * @return mixed
     */
    public function get_weshare_info($share_id) {
        $key = SIMPLE_SHARE_INFO_CACHE_KEY . '_' . $share_id;
        $share_info_str = Cache::read($key);
        if (empty($share_info_str)) {
            $weshareM = ClassRegistry::init('Weshare');
            $share_info = $weshareM->find('first', array(
                'conditions' => array(
                    'id' => $share_id
                )
            ));
            $share_info_str = json_encode($share_info['Weshare']);
            Cache::write($key, $share_info_str);
            return $share_info['Weshare'];
        }
        return json_decode($share_info_str, true);
    }

    /**
     * @param $share_ids
     * @return mixed
     * 根据分享的ID数组获取分享信息
     */
    public function get_all_share_info($share_ids) {
        $weshareM = ClassRegistry::init('Weshare');
        $share_info = $weshareM->find('all', array(
            'conditions' => array(
                'id' => $share_ids
            )
        ));
        return $share_info;
    }

    /**
     * @param $uid
     * @return bool
     * 是否有红包
     */
    public function has_share_offer($uid) {
        $shareOfferM = ClassRegistry::init('ShareOffer');
        $shareOffer = $shareOfferM->find('first', array(
            'conditions' => array('sharer_id' => $uid),
            'order' => array('id desc')
        ));
        return !empty($shareOffer);
    }

    /**
     * @param $uid
     * @return mixed
     * 获取分享者手机号码
     */
    public function get_sharer_mobile($uid) {
        $key = SHARER_MOBILE_PHONE_CACHE_KEY . '_' . $uid;
        $mobile = Cache::read($key);
        if (empty($mobile)) {
            $userM = ClassRegistry::init('User');
            $userInfo = $userM->find('first', array(
                'conditions' => array(
                    'id' => $uid
                ),
                'fields' => array('id', 'mobilephone')
            ));
            $mobile = $userInfo['User']['mobilephone'];
            Cache::write($key, $mobile);
        }
        return $mobile;
    }

    /**
     * @param $sharer_ids
     * @return array
     * 分享者是否有红包
     */
    public function sharer_has_offer($sharer_ids) {
        $shareOfferM = ClassRegistry::init('ShareOffer');
        $shareOffer = $shareOfferM->find('all', array(
            'conditions' => array('sharer_id' => $sharer_ids),
            'order' => array('id desc'),
            'fields' => array('id', 'sharer_id')
        ));
        $shareOffer = Hash::combine($shareOffer, '{n}.ShareOffer.sharer_id', '{n}.ShareOffer.id');
        return $shareOffer;
    }

    /**
     * @param $order_id
     * @return array
     * 获取分享订单商品名称和数量
     */
    public function get_cart_name_and_num($order_id) {
        $carts = $this->findCarts($order_id);
        $num = 0;
        $cart_name = array();
        foreach ($carts as $cart_item) {
            $num += $cart_item['Cart']['num'];
            $cart_name[] = $cart_item['Cart']['name'] . 'X' . $cart_item['Cart']['num'];
        }
        return array('num' => $num, 'cart_name' => implode(',', $cart_name));
    }
}