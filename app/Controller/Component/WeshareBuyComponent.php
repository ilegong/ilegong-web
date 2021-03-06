<?php


class WeshareBuyComponent extends Component
{

    var $name = 'WeshareBuyComponent';

    var $share_order_count = 10;

    var $query_user_fields = array('id', 'nickname', 'image', 'wx_subscribe_status', 'description', 'mobilephone', 'is_proxy', 'avatar');

    var $query_order_user_fields = array('id', 'nickname', 'image', 'avatar');

    var $query_user_simple_fields = array('id', 'nickname', 'image', 'wx_subscribe_status', 'mobilephone', 'is_proxy', 'avatar');

    var $query_share_info_order_fields = array('id', 'creator', 'created', 'updated', 'consignee_name', 'consignee_mobilephone', 'consignee_address', 'status', 'total_all_price', 'coupon_total', 'ship_mark', 'ship_code', 'ship_type', 'member_id', 'ship_type_name', 'total_price', 'coupon_total', 'cate_id', 'process_prepaid_status', 'price_difference', 'is_prepaid', 'business_remark', 'flag', 'applied_rebate');

    var $query_cart_fields = array('id', 'order_id', 'name', 'product_id', 'num');

    var $query_comment_fields = array('id', 'username', 'user_id', 'data_id', 'type', 'body', 'order_id', 'parent_id');

    var $components = array('Session', 'Weixin', 'RedPacket', 'ShareUtil', 'ShareAuthority', 'RedisQueue', 'Orders', 'WeshareFaq', 'SharePush');

    var $query_share_fields = array('id', 'title', 'images', 'status', 'creator', 'created', 'settlement', 'type', 'description');

    var $query_list_share_fields = array('id', 'title', 'default_image', 'status', 'creator', 'created', 'settlement', 'type', 'description');

    /**
     * @param $weshare_ids
     * @param $sharer_id
     * @return array
     * 加载分享者 所有的评论
     * 个人中心  页面
     */
    public function load_sharer_comment_data($weshare_ids, $sharer_id)
    {
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

    public function get_user_comment_count($sharer_id)
    {
        $commentM = ClassRegistry::init('Comment');
        $count = $commentM->find('count', [
            'conditions' => [
                'type' => COMMENT_SHARE_TYPE,
                'status' => COMMENT_SHOW_STATUS,
                'parent_id' => 0,
                'data_creator' => $sharer_id
            ]
        ]);
        return $count;
    }

    /**
     * @param $uid
     * @return array|mixed
     * 获取用户评论的数据
     */
    public function load_user_share_comments($uid)
    {
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

    //待评论准备数据
    public function prepare_to_comment_info($orderId, $replyCommentId)
    {
        if (empty($orderId)) {
            return array();
        }
        $result = array();
        $orderM = ClassRegistry::init('Order');
        $commentOrderInfo = $orderM->find('first', array(
            'conditions' => array(
                'id' => $orderId
            ),
            'fields' => array('id', 'creator', 'status', 'created')
        ));
        //user has comment it
        if ($commentOrderInfo['Order']['status'] == ORDER_STATUS_DONE && empty($replyCommentId)) {
            return array();
        }
        $orderNickName = $this->get_user_nickname($commentOrderInfo['Order']['creator']);
        $commentInfo['Order']['creator_nickname'] = $orderNickName;
        $result['comment_order_info'] = $commentOrderInfo['Order'];
        if (!empty($replyCommentId)) {
            $commentM = ClassRegistry::init('Comment');
            $commentInfo = $commentM->find('first', array(
                'conditions' => array(
                    'id' => $replyCommentId
                ),
                'fields' => array('id', 'username', 'order_id')
            ));
            $result['comment_info'] = $commentInfo['Comment'];
        }
        return $result;
    }

    /**
     * @param $uid
     * @return mixed
     * query shares
     */
    public function get_my_create_shares($uid)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $query_share_type = array(SHARE_TYPE_GROUP, SHARE_TYPE_DEFAULT, SHARE_TYPE_POOL_FOR_PROXY, SHARE_TYPE_POOL);
        $myCreateShares = $weshareM->find('all', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => array(WESHARE_STATUS_STOP, WESHARE_STATUS_NORMAL),
                'type' => $query_share_type
            ),
            'fields' => $this->query_share_fields,
            'order' => array('created DESC'),
            'limit' => 100
        ));
        $this->explode_share_imgs($myCreateShares);
        return $myCreateShares;
    }

    public function get_my_shares($uid, $status, $settlement, $page, $limit)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $query_share_type = [SHARE_TYPE_GROUP, SHARE_TYPE_DEFAULT, SHARE_TYPE_POOL_FOR_PROXY, SHARE_TYPE_POOL];
        $q_cond = [
            'conditions' => [
                'creator' => $uid,
                'status' => $status,
                'type' => $query_share_type,
                'settlement' => $settlement
            ],
            'fields' => $this->query_list_share_fields,
            'order' => ['id DESC'],
            'limit' => $limit,
            'page' => $page
        ];
        if ($status == WESHARE_STATUS_STOP && $settlement != WESHARE_SETTLEMENT_YES) {
            $q_cond['order'] = ['close_date DESC'];
        }
        $shares = $weshareM->find('all', $q_cond);
        return $shares;
    }

//    public function get()
//    {
//
//    }

    public function get_user_provide_shares($uid, $keyword = null){
        $poolProductM = ClassRegistry::init('PoolProduct');
        $queryCond = [
            'PoolProduct.user_id' => $uid,
            'PoolProduct.status' => [POOL_PRODUCT_PUBLISH, POOL_PRODUCT_UN_PUBLISH, POOL_PRODUCT_DRAFT],
        ];
        if ($keyword != null) {
            $queryCond['PoolProduct.share_name LIKE '] = '%' . $keyword . '%';
        }
        $poolProducts = $poolProductM->find('all', [
            'conditions' => $queryCond,
            'order' => ['PoolProduct.status DESC', 'PoolProduct.created DESC']
        ]);
        $result = [];
        $shareIds = [];
        foreach ($poolProducts as $productItem) {
            $shareIds[] = $productItem['PoolProduct']['weshare_id'];
            $result[] = $productItem['PoolProduct'];
        }
        $paid_order_count = $this->ShareUtil->get_pool_share_wait_ship_order_count($shareIds);
        foreach ($result as &$item) {
            $item['wait_ship_order_count'] = empty($paid_order_count[$item['weshare_id']]) ? 0 : $paid_order_count[$item['weshare_id']];
        }
        return $result;
    }

    public function search_shares($uid, $keyword, $page, $limit)
    {
        $query_share_type = array(SHARE_TYPE_GROUP, SHARE_TYPE_DEFAULT, SHARE_TYPE_POOL_FOR_PROXY, SHARE_TYPE_POOL);
        $weshareM = ClassRegistry::init('Weshare');
        $shares = $weshareM->find('all', [
            'conditions' => [
                'creator' => $uid,
                'type' => $query_share_type,
                'title like' => '%' . $keyword . '%'
            ],
            'fields' => $this->query_list_share_fields,
            'order' => array('created DESC'),
            'limit' => $limit,
            'page' => $page
        ]);
        return $shares;
    }

    public function get_my_auth_shares($uid, $page, $limit, $status, $settlement, $without_pool_share = false)
    {
        $shareOperateSettingM = ClassRegistry::init('ShareOperateSetting');
        $weshareM = ClassRegistry::init('Weshare');
        if ($status != WESHARE_STATUS_NORMAL) {
            $status = [$status, WESHARE_STATUS_DELETED];
        }
        $cond = [
            'Weshare.status' => $status,
            'ShareOperateSetting.user' => $uid,
            'Weshare.settlement' => $settlement
        ];
        if ($without_pool_share) {
            $cond['Weshare.type'] = SHARE_TYPE_DEFAULT;
        }

        $weshareIds = $shareOperateSettingM->find('all', [
            'conditions' => $cond,
            'joins' => [
                [
                    'table' => 'cake_weshares',
                    'alias' => 'Weshare',
                    'conditions' => [
                        'Weshare.id = ShareOperateSetting.data_id'
                    ],
                ],
            ],
            'fields' => ['Weshare.id'],
            'order' => ['Weshare.created DESC'],
            'limit' => $limit,
            'page' => $page,
            'group' => 'ShareOperateSetting.data_id'
        ]);

        $weshareIds = Hash::extract($weshareIds, '{n}.Weshare.id');

        if(empty($weshareIds)){
            return [];
        }

        $cond['Weshare.id'] = $weshareIds;

        $result = $weshareM->find('all', [
            'conditions' => $cond,
            'joins' => [
                [
                    'table' => 'cake_share_operate_settings',
                    'alias' => 'ShareOperateSetting',
                    'conditions' => [
                        'ShareOperateSetting.data_id = Weshare.id'
                    ],
                ],
            ],
            'fields' => ['ShareOperateSetting.id', 'ShareOperateSetting.data_id', 'ShareOperateSetting.data_type', 'Weshare.id', 'Weshare.title', 'Weshare.default_image', 'Weshare.status', 'Weshare.creator', 'Weshare.created', 'Weshare.settlement', 'Weshare.type'],
            'order' => ['Weshare.created DESC']
        ]);
        return $result;
    }

    /**
     * @param $uid
     * @return array|mixed
     * 准备用户中心数据
     */
    public function prepare_user_share_info($uid)
    {
        $key = USER_SHARE_INFO_CACHE_KEY . '_' . $uid;
        $user_share_data = Cache::read($key);
        if (empty($user_share_data)) {
            $weshareM = ClassRegistry::init('Weshare');
            $orderM = ClassRegistry::init('Order');
            $userM = ClassRegistry::init('User');
            $query_share_type = array(SHARE_TYPE_GROUP, SHARE_TYPE_DEFAULT, SHARE_TYPE_POOL_FOR_PROXY, SHARE_TYPE_POOL);
            $myCreateShares = $this->get_my_create_shares($uid);
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
//           加入分享的评论不显示了
//            $joinShareOrderIds = Hash::extract($joinShareOrder, '{n}.Order.id');
//            $joinShareComments = $commentM->find('all', array(
//                'conditions' => array(
//                    'order_id' => $joinShareOrderIds,
//                    'status' => COMMENT_SHOW_STATUS,
//                    'type' => COMMENT_SHARE_TYPE,
//                    'parent_id' => 0,
//                    'user_id' => $uid
//                )
//            ));
//            $joinShareComments = Hash::combine($joinShareComments, '{n}.Comment.order_id', '{n}.Comment');
            $joinShareOrderStatus = Hash::combine($joinShareOrder, '{n}.Order.member_id', '{n}.Order');
            $joinShareIds = Hash::extract($joinShareOrder, '{n}.Order.member_id');
            $joinShareIds = array_unique($joinShareIds);
            $myJoinShares = $weshareM->find('all', array(
                'conditions' => array(
                    'id' => $joinShareIds,
                    'status' => array(0, 1, -1),
                    'type' => $query_share_type
                ),
                'fields' => $this->query_share_fields,
                'order' => array('created DESC')
            ));
            $creatorIds = Hash::extract($myJoinShares, '{n}.Weshare.creator');
            $creatorIds[] = $uid;
            //$this->explode_share_imgs($myCreateShares);
            $this->explode_share_imgs($myJoinShares);
            //authority shares
            $authority_shares = array();
            $q_authority_share_cond = array(
                'user' => $uid,
                'scope_type' => SHARE_OPERATE_SCOPE_TYPE,
                'deleted' => DELETED_NO
            );
            $shareOperateSettingM = ClassRegistry::init('ShareOperateSetting');
            //获取授权的分享ID
            $share_operate_settings = $shareOperateSettingM->find('all', array(
                'conditions' => $q_authority_share_cond,
                'order' => array('id' => 'desc'),
                'limit' => 300
            ));
            $authority_share_ids = [];
            $authority_share_map = [];
            foreach ($share_operate_settings as $operate_setting_item) {
                $operate_share_id = $operate_setting_item['ShareOperateSetting']['data_id'];
                $authority_share_ids[] = $operate_share_id;
                if (!isset($authority_share_map[$operate_share_id])) {
                    $authority_share_map[$operate_share_id] = [];
                }
                $authority_share_map[$operate_share_id][] = $operate_setting_item['ShareOperateSetting']['data_type'];
            }
            $authority_share_ids = array_unique($authority_share_ids);
            if (count($authority_share_ids) > 0) {
                $authority_shares = $weshareM->find('all', array(
                    'conditions' => array(
                        'id' => $authority_share_ids,
                        'type' => $query_share_type
                    ),
                    'fields' => $this->query_share_fields,
                    'order' => array('id' => 'desc')
                ));
                $authority_shares_creators = Hash::extract($authority_shares, '{n}.Weshare.creator');
                $creatorIds = array_unique(array_merge($creatorIds, $authority_shares_creators));
            }

            $this->explode_share_imgs($authority_shares);
            $creators = $userM->find('all', array(
                'conditions' => array(
                    'id' => $creatorIds
                ),
                'fields' => $this->query_user_fields
            ));
            $creators = Hash::combine($creators, '{n}.User.id', '{n}.User');
            $user_share_data = array('authority_shares' => $authority_shares, 'authority_share_map' => $authority_share_map, 'creators' => $creators, 'my_create_share_ids' => $my_create_share_ids, 'joinShareOrderStatus' => $joinShareOrderStatus, 'myJoinShares' => $myJoinShares, 'myCreateShares' => $myCreateShares);
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
    private function explode_share_imgs(&$shares)
    {
        foreach ($shares as &$item) {
            $item['Weshare']['images'] = explode('|', $item['Weshare']['images']);
        }
    }

    /**
     * @param $uid
     * @return mixed
     * 获取分享者的爱心评价数量
     */
    public function get_sharer_comments_count($uid)
    {
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

//    /**
//     * @param $sharer_id
//     * @return array
//     * 分享页面 获取分享者的所有评论数据
//     */
//    public function load_sharer_comments($sharer_id)
//    {
//        $cache_key = SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $sharer_id . '_0';
//        $sharer_comment_data = Cache::read($cache_key);
//        if (empty($sharer_comment_data)) {
//            $weshareM = ClassRegistry::init('Weshare');
//            $commentM = ClassRegistry::init('Comment');
//            $userM = ClassRegistry::init('User');
//            $allShares = $weshareM->find('all', array(
//                'conditions' => array(
//                    'creator' => $sharer_id,
//                    'status' => array(0, 1)
//                )
//            ));
//            $share_ids = Hash::extract($allShares, '{n}.Weshare.id');
//            $share_all_comments = $commentM->find('all', array(
//                'conditions' => array(
//                    'type' => COMMENT_SHARE_TYPE,
//                    'data_id' => $share_ids,
//                    'status' => COMMENT_SHOW_STATUS,
//                    'parent_id' => 0,
//                    'not' => array('order_id' => null)
//                ),
//                'limit' => 500,
//                'order' => array('id' => 'desc')
//            ));
//            $comment_user_ids = Hash::extract($share_all_comments, '{n}.Comment.user_id');
//            $share_all_comments = Hash::extract($share_all_comments, '{n}.Comment');
//            $all_users = $userM->find('all', array(
//                'conditions' => array(
//                    'id' => $comment_user_ids
//                ),
//                'fields' => $this->query_user_fields
//            ));
//            $all_users = Hash::combine($all_users, '{n}.User.id', '{n}.User');
//            $sharer_comment_data = array('share_all_comments' => $share_all_comments, 'share_comment_all_users' => $all_users);
//            Cache::write($cache_key, json_encode($sharer_comment_data));
//            return $sharer_comment_data;
//        }
//        return json_decode($sharer_comment_data, true);
//    }

    private function combine_comment($comments)
    {
        if (count($comments) > 0) {
            $commentReplyM = ClassRegistry::init('CommentReply');
            //$comments = Hash::combine($comments,'{n}.Comment.id', '{n}.Comment', '{n}.Comment.order_id');
            $comment_ids = Hash::extract($comments, '{n}.Comment.id');
            $order_comments = array_filter($comments, 'order_comment_filter');
            $order_comments = Hash::combine($order_comments, '{n}.Comment.order_id', '{n}.Comment');
            $reply_comments = array_filter($comments, 'order_reply_comment_filter');
            $reply_comments = Hash::combine($reply_comments, '{n}.Comment.id', '{n}.Comment');
            $commentReplies = $commentReplyM->find('all', array(
                'conditions' => array(
                    'data_id' => $comments[0]['Comment']['data_id'],
                    'data_type' => COMMENT_SHARE_TYPE,
                    'OR' => array(
                        'comment_id' => $comment_ids,
                        'reply_id' => $comment_ids
                    )
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
            return $share_comment_data;
        }
        return array('order_comments' => array(), 'comment_replies' => array());
    }

    public function query_comment2($cond)
    {
        $commentM = ClassRegistry::init('Comment');
        $comments = $commentM->find('all', $cond);
        //$users = Hash::combine($comments, '{n}.User.id', '{n}.User');
        $users = [];
        $order_ids = [];
        foreach ($comments as $comment_item) {
            $user_id = $comment_item['User']['id'];
            $users[$user_id] = $comment_item['User'];
            $order_ids[] = $comment_item['Comment']['order_id'];
        }
        $order_data = $this->get_order_data($order_ids);
        $users = array_map('map_user_avatar', $users);
        $result = $this->combine_comment($comments);
        $result['users'] = $users;
        $result['order_data'] = $order_data;
        return $result;
    }

    public function get_order_data($order_ids)
    {
        $order_ids = array_unique($order_ids);
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', [
            'conditions' => [
                'Order.id' => $order_ids
            ],
            'joins' => [
                [
                    'table' => 'carts',
                    'alias' => 'Cart',
                    'type' => 'RIGHT',
                    'conditions' => [
                        'Cart.order_id = Order.id',
                    ],
                ],
            ],
            'fields' => ['Order.id', 'Cart.name', 'Cart.num']
        ]);
        $result = [];
        foreach ($orders as $order_item) {
            $order_id = $order_item['Order']['id'];
            if (!isset($result[$order_id])) {
                $result[$order_id] = $order_item['Cart']['name'] . 'X' . $order_item['Cart']['num'];
            } else {
                $result[$order_id] = $result[$order_id] . ', ' . $order_item['Cart']['name'] . 'X' . $order_item['Cart']['num'];
            }
        }
        return $result;
    }


    /**
     * @param $conds
     * @return array
     */
    public function query_comment($conds)
    {
        $commentM = ClassRegistry::init('Comment');

        $comments = $commentM->find('all', array(
            'conditions' => $conds,
            'fields' => $this->$query_comment_fields
        ));
        return $this->combine_comment($comments);
    }


    /**
     * @param $order_ids
     * @return array()
     * 获取评论数据
     */
    public function load_comment_by_order_id($order_ids)
    {
        $conds = array(
            'type' => COMMENT_SHARE_TYPE,
            'order_id' => $order_ids,
            'status' => COMMENT_SHOW_STATUS
        );
        $share_comment_data = $this->query_comment($conds);
        return $share_comment_data;
    }

    /**
     * @param $weshare_id
     * @return array
     * 加载本次分享的数据
     */
    public function load_comment_by_share_id($weshare_id, $uid = null, $sharer = null)
    {
        $conds = array(
            'type' => COMMENT_SHARE_TYPE,
            'data_id' => $weshare_id,
            'status' => COMMENT_SHOW_STATUS
        );
        $user_ids = [];
        if (!empty($uid)) {
            $user_ids[] = $uid;
        }
        if (!empty($sharer)) {
            $user_ids[] = $sharer;
        }
        if (!empty($user_ids)) {
            $conds['user_id'] = $user_ids;
        }
        $share_comment_data = $this->query_comment($conds);
        return $share_comment_data;
    }

    /**
     * @param $order_comments
     * @param $reply_comments
     * @param $comment_replay_relation
     * @return array
     * 处理评论回复数据
     */
    private function recursionReply($order_comments, $reply_comments, $comment_replay_relation)
    {
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
    private function processRecursionReply($reply_comments, &$comment_replay_format_result, $comment_replay_relation, $comment_id, $level = 0)
    {
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
    public function create_share_comment($order_id, $comment_content, $reply_comment_id, $comment_uid, $share_id)
    {
        $commentM = ClassRegistry::init('Comment');
        $userM = ClassRegistry::init('User');
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $weshare_info = $this->get_weshare_info($share_id);

        //分享者通知购买者 去评价
        if (($weshare_info['creator'] == $comment_uid) && $reply_comment_id == 0 && empty($comment_content)) {
            //seller send to buyer
            $this->send_comment_notify_buyer($order_id, $share_id, $comment_content);
            return array('success' => true, 'type' => 'notify');
        }
        $share_managers = $this->ShareAuthority->get_share_manage_auth_users($share_id);
        //回复分享
        if ($reply_comment_id != 0) {
            //判断$comment_uid  是不是分享的管理员
            if (!empty($share_managers)) {
                //如果是分享的管理员，替换当前用户为分享的管理者
                if (in_array($comment_uid, $share_managers)) {
                    $comment_uid = $weshare_info['creator'];
                }
            }
        }
        $user_nickname = $userM->findNicknamesOfUid($comment_uid);
        $order_info = $orderM->findOrderByConditionsAndFields(array('id' => $order_id), array('created', 'creator'));
        $date_time = date('Y-m-d H:i:s');
        $buy_date_time = $order_info['Order']['created'];
        $commentData = array('parent_id' => $reply_comment_id, 'user_id' => $comment_uid, 'username' => $user_nickname, 'body' => $comment_content, 'data_id' => $share_id, 'data_creator' => $weshare_info['creator'], 'type' => COMMENT_SHARE_TYPE, 'publish_time' => $date_time, 'created' => $date_time, 'updated' => $date_time, 'buy_time' => $buy_date_time, 'order_id' => $order_id, 'status' => COMMENT_SHOW_STATUS);
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
                //回复给订单用户
                $this->send_comment_reply_notify($order_id, $share_id, $comment_content, $comment['Comment']['id']);
            } elseif ($reply_comment_id == $weshare_info['creator'] && $order_uid == $comment_uid) {
                //回复给分享者
                $this->send_comment_notify($order_id, $share_id, $comment_content, $comment['Comment']['id']);
            } elseif ($comment_uid != $reply_comment_uid) {
                //用户之间交互
                $this->send_comment_mutual_msg($comment_uid, $reply_comment_uid, $comment_content, $share_id, $order_id, $comment['Comment']['id']);
            }
        } else {
            //update order status
            $orderM->updateAll(array('status' => ORDER_STATUS_DONE, 'updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $order_id));
            $cartM->updateAll(array('status' => ORDER_STATUS_DONE), array('order_id' => $order_id));
            //save comment opt log
            $this->ShareUtil->save_comment_opt_log($comment_uid, $share_id, $comment['Comment']['id'], $comment_content);
            if ($comment_uid == $order_info['Order']['creator']) {
                //发送给分享者
                $this->send_comment_notify($order_id, $share_id, $comment_content, $comment['Comment']['id']);
            }
//            if (!empty($comment['Comment']['id'])) {
//                $this->send_shareed_offer_notify($order_id, $share_id, $comment['Comment']['id']);
//            }
            //clean cache
            //$cache_key = SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $sharer_id . '_0';
            //$cache_key = SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $sharer_id . '_1';
            delete_redis_data_by_key(SHARER_ALL_COMMENT_DATA_CACHE_KEY . '_' . $weshare_info['creator']);
            delete_redis_data_by_key(SHARE_ORDER_DATA_CACHE_KEY . '_' . $share_id);
            //SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId;
            $this->clear_user_share_order_data_cache(array($order_id), $share_id);
        }
        return array('success' => true, 'comment' => $comment['Comment'], 'comment_reply' => $commentReply['CommentReply'], 'order_id' => $order_id);
    }

//    /**
//     * @param $shareId
//     * 发送建团消息给分享的创建者
//     */
//    public function send_new_share_msg_to_share_manager($shareId)
//    {
//        $share_manager = $this->ShareAuthority->get_share_manage_auth_users($shareId);
//        $weshareM = ClassRegistry::init('Weshare');
//        $weshare = $weshareM->find('first', array(
//            'conditions' => array(
//                'id' => $shareId
//            )
//        ));
//        if (empty($share_manager)) {
//            $share_manager = array();
//        }
//        $share_manager[] = $weshare['Weshare']['creator'];
//        $this->do_send_new_share_msg($weshare, $share_manager);
//    }

//    /**
//     * @param $weshareId
//     * @param $limit
//     * @param $offset
//     * 创建新的分享之后发送模板消息
//     */
//    public function send_new_share_msg($weshareId, $limit = null, $offset = null)
//    {
//        $this->Weshare = ClassRegistry::init('Weshare');
//        $weshare = $this->Weshare->find('first', array(
//            'conditions' => array(
//                'id' => $weshareId
//            )
//        ));
//        $followers = $this->load_fans_buy_sharer($weshare['Weshare']['creator'], $limit, $offset);
//        $this->do_send_new_share_msg($weshare, $followers);
//    }

    /**
     * @param $share_id
     * @param null $limit
     * @param null $offset
     * 发送拼团消息
     */
    public function send_pintuan_share_msg($share_id, $tag_id, $limit = null, $offset = null)
    {
        $PintuanConfigM = ClassRegistry::init('PintuanConfig');
        $data = $PintuanConfigM->get_conf_data($share_id);
        $followers = $this->load_fans_by_sharer($data['sharer_id'], $limit, $offset);
        $this->do_send_new_pintuan_msg($data, $tag_id, $followers);
    }

    /**
     * @param $pintuan_data
     * @param $tag_id
     * @param $uids
     * 处理发送拼团消息
     */
    private function do_send_new_pintuan_msg($pintuan_data, $tag_id, $uids)
    {
        //add filter
        $uids = $this->check_msg_log_and_filter_user($pintuan_data['pid'], $uids, MSG_LOG_PINTUAN_TYPE);
        $this->save_msg_logs($pintuan_data['pid'], $uids, MSG_LOG_PINTUAN_TYPE);
        $OauthbindM = ClassRegistry::init('Oauthbind');
        $detail_url = WX_HOST . '/pintuan/detail/' . $pintuan_data['share_id'] . '?from=template_msg';
        if ($tag_id != 0) {
            $detail_url = $detail_url . '&tag_id=' . $tag_id;
        }
        $sharer_name = $pintuan_data['sharer_nickname'];
        $product_name = $pintuan_data['share_title'];
        $title = '关注的' . $sharer_name . '发起了';
        $remark = '点击详情，赶快加入' . $sharer_name . '的拼团！';
        $openIds = $OauthbindM->findWxServiceBindsByUids($uids);
        if ($openIds) {
            $openIds = array_unique($openIds);
            foreach ($openIds as $openId) {
                $this->process_send_share_msg($openId, $title, $product_name, $detail_url, $sharer_name, $remark);
            }
        }
    }

//    private function do_send_new_share_msg($weshare, $uids)
//    {
//        //add filter
//        $uids = $this->check_msg_log_and_filter_user($weshare['Weshare']['id'], $uids, MSG_LOG_NOTIFY_TYPE);
//        $uids = $this->check_msg_log_and_filter_user($weshare['Weshare']['refer_share_id'], $uids, MSG_LOG_NOTIFY_TYPE);
//        $uids = $this->check_msg_log_and_filter_user($weshare['Weshare']['id'], $uids, MSG_LOG_RECOMMEND_TYPE);
//        $uids = $this->check_msg_log_and_filter_user($weshare['Weshare']['refer_share_id'], $uids, MSG_LOG_RECOMMEND_TYPE);
//        $this->save_msg_logs($weshare['Weshare']['id'], $uids, MSG_LOG_NOTIFY_TYPE);
//        $userM = ClassRegistry::init('User');
//        $OauthbindM = ClassRegistry::init('Oauthbind');
//        $sharer_user_info = $userM->find('first', array(
//            'conditions' => array(
//                'id' => $weshare['Weshare']['creator']
//            ),
//            'fields' => array(
//                'id', 'nickname'
//            )
//        ));
//        $detail_url = WX_HOST . '/weshares/view/' . $weshare['Weshare']['id'].'?from=template_msg';
//        $sharer_name = $sharer_user_info['User']['nickname'];
//        $product_name = $weshare['Weshare']['title'];
//        $title = '关注的' . $sharer_name . '发起了';
//        $remark = '点击详情，赶快加入' . $sharer_name . '的分享！';
//        $openIds = $OauthbindM->findWxServiceBindsByUids($uids);
//        if ($openIds) {
//            $openIds = array_unique($openIds);
//            foreach ($openIds as $openId) {
//                $this->process_send_share_msg($openId, $title, $product_name, $detail_url, $sharer_name, $remark);
//            }
//        }
//    }

    /**
     * @param $order_id
     * @param $weshare_id
     * 快递寄出的模板消息
     */
    public function send_share_product_ship_msg($order_id, $weshare_id)
    {
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
            $ship_company_name = $order_info['Order']['ship_type_name'];
            if (empty($ship_company_name)) {
                $ship_company_name = $shipTypesList[$order_info['Order']['ship_type']];
            }
            $ship_code = $order_info['Order']['ship_code'];
            $desc = '感谢您对' . $share_creator_nickname . '的支持，分享快乐！';
            $cart_info = $this->get_cart_name_and_num($order_id);
            $deatail_url = WX_HOST . '/weshares/view/' . $weshare_id;
            //send_order_ship_info_msg
            $this->Weixin->send_order_ship_info_msg($order_user_id, null, $ship_code, $ship_company_name, $cart_info['cart_name'], null, $title, $cart_info['num'], $desc, $deatail_url);
            //push msg to app
            $jpush_title = "您好，你的订单已经发货，订单号:$order_id，快递公司:$ship_company_name，快递单号:$ship_code";
            $this->SharePush->push_order_shipped_msg($order_user_id, $jpush_title, $ship_company_name . ' : ' . $ship_code, $order_id);
        }
    }

    /**
     * @param $share_id
     * @param $refer_share_id
     * @param $msg
     * @param $address
     * 对自提点进行发货
     */
    public function send_group_share_product_arrival_msg($share_id, $refer_share_id, $msg, $address)
    {
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
     * @param $order_ids
     * 到货提醒
     */
    public function send_share_product_arrive_msg($shareInfo, $msg, $order_ids)
    {
        $this->Order = ClassRegistry::init('Order');
        $this->User = ClassRegistry::init('User');
        $this->Oauthbind = ClassRegistry::init('Oauthbind');
        $share_id = $shareInfo['Weshare']['id'];
        $share_creator = $shareInfo['Weshare']['creator'];
        //select order paid to send msg
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'id' => $order_ids,
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
        foreach ($orders as $order) {
            $order_id = $order['Order']['id'];
            $order_user_id = $order['Order']['creator'];
            $conginess_address = $order['Order']['consignee_address'];
            $jpush_title = "您好，你的订单商品已经到自提点，地址:$conginess_address";
            $this->SharePush->push_pick_up_msg($order_user_id, $jpush_title, $msg, $order_id);
        }
    }

    /**
     * @param $sharerId
     * @param $limit
     * @param $offset
     * @return array
     * 加载粉丝数据
     */
    public function load_fans_by_sharer($sharerId, $limit = null, $offset = null)
    {
        $userRelationM = ClassRegistry::init('UserRelation');
        $cond = array(
            'conditions' => [
                'UserRelation.user_id' => $sharerId,
                'UserRelation.deleted' => DELETED_NO,
            ],
            'fields' => ['UserRelation.follow_id'],
            'order' => ['UserRelation.id ASC']
        );
        if ($limit != null && $offset != null) {
            $cond['limit'] = $limit;
            $cond['offset'] = $offset;
        }
        $relations = $userRelationM->find('all', $cond);
        $follower_ids = Hash::extract($relations, '{n}.UserRelation.follow_id');
        if (!empty($follower_ids)) {
            $userM = ClassRegistry::init('User');
            $users = $userM->find('all', [
                'conditions' => [
                    'id' => $follower_ids,
                    'wx_subscribe_status' => 1
                ],
                'fields' => ['id']
            ]);
            $follower_ids = Hash::extract($users, '{n}.User.id');
        }
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
    public function process_send_share_msg($openId, $title, $productName, $detailUrl, $sharerName, $remark)
    {
        send_join_tuan_buy_msg(null, $title, $productName, $sharerName, $remark, $detailUrl, $openId);
    }

    /**
     * @param $weshareId
     * @return mixed
     * 计算后结算的款项
     */
    public function get_added_order_repaid_money($weshareId)
    {
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
    public function get_group_order_repaid_money($orderIds, $weshareId)
    {
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
    public function get_refund_money_by_weshare($weshareId)
    {
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
    public function get_child_share_items($share_id)
    {
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
            $level_data = $this->ShareUtil->get_users_level($user_ids);
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
            $child_share_data = array('child_share_data' => $address_data, 'child_share_user_infos' => $user_infos, 'child_share_level_data' => $level_data, 'child_share_ids' => $share_ids);
            $child_share_data_json = json_encode($child_share_data);
            Cache::write($cache_key, $child_share_data_json);
            return $child_share_data;
        }
        return json_decode($child_share_data_json, true);
    }

    //先不进行缓存
    public function get_product_id_map_by_origin_ids($share_id)
    {
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
    public function get_child_share_summery($share_id, $refer_share_id)
    {
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
    public function get_share_order_page_info($weshareId, $uid)
    {
        $order_count = $this->get_share_all_buy_count($weshareId, $uid);
        $page_count = ceil($order_count / $this->share_order_count);
        $page_info = array('order_count' => $order_count, 'page_count' => $page_count);
        return $page_info;
    }

    /**
     * @param $referShareId
     * @param $uid
     * @return int
     * 过滤父分享的Id
     */
    private function filter_refer_share_id($referShareId, $uid)
    {
        $refer_share_info = $this->get_weshare_info($referShareId);
        if (!empty($refer_share_info) && $refer_share_info['type'] == SHARE_TYPE_DEFAULT && $refer_share_info['creator'] == $uid) {
            return $referShareId;
        }
        return 0;
    }

    /**
     * @param $weshareId
     * @param $uid
     * @return array
     */
    public function get_refer_share_ids($weshareId, $uid)
    {
        $sql = 'SELECT id FROM cake_weshares as share where (share.id=' . $weshareId . ' or share.id=(select refer_share_id from cake_weshares where id=' . $weshareId . ' and creator=' . $uid . ')) and type=0 and creator=' . $uid;
        $weshareM = ClassRegistry::init('Weshare');
        $result = $weshareM->query($sql);
        $refer_share_ids = Hash::extract($result, '{n}.share.id');
        return $refer_share_ids;
    }


    /**
     * @param $shareId
     * @return array|mixed
     * 分享购买的统计汇总
     */
    public function get_share_buy_summery($shareId)
    {
        $key = SHARE_BUY_SUMMERY_INFO_CACHE_KEY . '_' . $shareId;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $product_ids = $this->get_share_pids($shareId);
            $sql = 'select sum(num), product_id from cake_carts where type=9 and product_id in (' . implode(',', $product_ids) . ') and order_id in (select id from cake_orders where type=9 and status in (1,2,3,4,9,14) and member_id=' . $shareId . ') group by product_id';
            $cartM = ClassRegistry::init('Cart');
            $result = $cartM->query($sql);
            $summery_result = array();
            foreach ($result as $item) {
                $item_pid = $item['cake_carts']['product_id'];
                $item_count = $item[0]['sum(num)'];
                if (!isset($summery_result[$item_pid])) {
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

    public function get_share_pids($weshareId)
    {
        $weshareProductM = ClassRegistry::init('WeshareProduct');
        $products = $weshareProductM->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshareId,
                'deleted' => DELETED_NO
            ),
            'fields' => array('id')
        ));
        return Hash::extract($products, '{n}.WeshareProduct.id');
    }

    /**
     * @param $weshareId
     * @param $uid
     * @return array
     * 分享详情页面 用户的数据先行加载
     *
     */
    public function get_current_user_share_order_data($weshareId, $uid)
    {
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
     * @param $uid
     * @return array
     * 获取个人订单 呼叫人人和闪送的订单
     */
    public function get_current_user_logistics_order_data($weshareId, $uid)
    {
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'member_id' => $weshareId,
                'creator' => $uid,
                'ship_mark' => SHARE_SHIP_PYS_ZITI_TAG
            ),
            'fields' => array('id')
        ));
        if (!empty($orders)) {
            $order_ids = Hash::extract($orders, '{n}.Order.id');
            $logisticsOrderM = ClassRegistry::init('LogisticsOrder');
            //排除掉待支付的
            $logistics_orders = $logisticsOrderM->find('all', array(
                'conditions' => array(
                    'order_id' => $order_ids,
                    'deleted' => DELETED_NO,
                    'not' => array('status' => array(0))
                ),
                'fields' => array('id', 'order_id', 'status', 'business_order_id')
            ));
            $logistics_orders = Hash::combine($logistics_orders, '{n}.LogisticsOrder.order_id', '{n}.LogisticsOrder');
            return $logistics_orders;
        }
        return array();
    }

    /**
     * @param $orderIds
     * @param $shareId
     * 清除分享用户缓存
     */
    public function clear_user_share_order_data_cache($orderIds, $shareId = 0)
    {
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'id' => $orderIds
            ),
            'fields' => array('id', 'creator', 'member_id')
        ));
        if ($shareId == 0) {
            $shareId = $orders[0]['Order']['member_id'];
        }
        $order_creators = Hash::extract($orders, '{n}.Order.creator');
        foreach ($order_creators as $uid) {
            $this->do_clear_user_share_order_data_cache($uid, $shareId);
        }

    }

    /**
     * @param $uid
     * @param $shareId
     * 微分享订单页面 缓存
     */
    public function do_clear_user_share_order_data_cache($uid, $shareId)
    {
        Cache::write(USER_SHARE_ORDER_INFO_CACHE_KEY . '_' . $shareId . '_' . $uid, '');
    }


    public function get_app_detail_orders($q_cond)
    {
        $result = $this->load_share_order_data($q_cond);
        unset($result['order_ids']);
        return $result;
    }

    /**
     * @param $weshareId
     * @param $page
     * @param $uid 当前用户数据
     * @param $combineComment
     * @return array
     */
    public function get_share_detail_view_orders($weshareId, $page, $uid, $combineComment = 0)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $related_share_ids = $weshareM->get_relate_share($weshareId);
        $order_status = array(ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY);
        //'FIELD(status, ' . join(',', $order_status) . ')'
        $sort = ['created DESC'];
        $current_user_order = $this->get_current_user_share_order_data($weshareId, $uid);
        $query_order_cond = [
            'conditions' => [
                'member_id' => $related_share_ids,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => $order_status,
                'deleted' => DELETED_NO,
            ],
            'fields' => $this->query_share_info_order_fields,
            'limit' => $this->share_order_count,
            'offset' => ($page - 1) * $this->share_order_count,
            'order' => $sort
        ];
        if (!empty($current_user_order['order_ids'])) {
            $query_order_cond['conditions']['not'] = ['id' => $current_user_order['order_ids']];
        }
        $result = $this->load_share_order_data($query_order_cond);
        if ($page == 1) {
            //第一页的话保存分页信息
            $result['page_info'] = $this->get_share_order_page_info($weshareId, $uid);
        }
        if ($combineComment == 1) {
            $order_ids = $result['order_ids'];
            $commentData = $this->load_comment_by_order_id($order_ids);
            $result['comment_data'] = $commentData;
        }
        unset($result['order_ids']);
        return $result;
    }

    /**
     * @param $cond
     * @return array
     * 获取分享的数据
     */
    private function load_share_order_data($cond)
    {
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $userM = ClassRegistry::init('User');
        $orders = $orderM->find('all', $cond);
        $orderIds = [];
        $userIds = [];
        $cateIds = [];
        $order_result = [];
        $order_cart_map = array();
        $users = array();
        if ($orders) {
            foreach ($orders as $order_item) {
                $order_result[] = $order_item['Order'];
                $userIds[] = $order_item['Order']['creator'];
                $cateIds[] = $order_item['Order']['cate_id'];
                $orderIds[] = $order_item['Order']['id'];
            }
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
                'fields' => $this->query_user_simple_fields,
            ));
            $level_data = $this->ShareUtil->get_users_level($userIds);
            //reset user image
            $users = array_map('map_user_avatar2', $users);
            $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        }
        $result_data = array('users' => $users, 'level_data' => $level_data, 'orders' => $order_result, 'order_cart_map' => $order_cart_map, 'rebate_logs' => $rebateLogs, 'order_ids' => $orderIds);
        return $result_data;
    }

    /**
     * @param $orderId
     * @param $orderRemark
     * @param $weshareId
     */
    public function update_order_remark($orderId, $orderRemark, $weshareId)
    {
        $orderM = ClassRegistry::init('Order');
        $orderM->update(array('business_remark' => "'" . $orderRemark . "'"), array('id' => $orderId));
        delete_redis_data_by_key(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId);
    }

    /**
     * @param $weshareId
     * @param $is_me
     * @param bool $division 根据发货方式 分类订单
     * @return array
     * 获取分享的订单信息
     *
     * 这个里面的逻辑和上面统计子分享数据逻辑有共同处，修改的时候注意
     */
    public function get_share_order_for_show($weshareId, $is_me, $division = false, $only_paid=false, $only_real_orders=false)
    {
        if ($division) {
            $key = SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId . '_1';
        } else {
            $key = SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId . '_0';
        }
        if ($only_paid) {
            $key = $key . '_1';
        } else {
            $key = $key . '_0';
        }
        if ($only_real_orders) {
            $key = $key . '_1';
        } else {
            $key = $key . '_0';
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
            $conditions = array(
                'member_id' => $weshareId,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'deleted' => DELETED_NO,
            );
            if($only_paid){
                $conditions['status'] = array(ORDER_STATUS_PAID);
            }
            else{
                $conditions['status'] = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY);
            }

            if ($only_real_orders) {
                $conditions['not'] = array('flag' => ORDER_FLAG_VIRTUAL_FLAG);
            }

            $sort = array('created DESC');
            $orders = $this->Order->find('all', array(
                'conditions' => $conditions,
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
                'fields' => $this->query_order_user_fields,
            ));
            $orders = Hash::combine($orders, '{n}.Order.id', '{n}.Order');
            $carts = [];
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
                $carts = $this->Cart->find('all', array(
                    'conditions' => array(
                        'order_id' => $orderIds,
                        'type' => ORDER_TYPE_WESHARE_BUY
                    ),
                    'fields' => array('id', 'name', 'order_id', 'num', 'product_id', 'price', 'confirm_price', 'tag_id')
                ));
            }
            $realTotalPrice = 0;
            $summeryTotalPrice = 0;
            $couponPrice = 0;
            $shipFee = 0;
            $useRebateFee = 0;
            foreach ($orders as $order_item) {
                $realTotalPrice = $realTotalPrice + $order_item['total_all_price'];
                $summeryTotalPrice = $summeryTotalPrice + $order_item['total_price'];
                $couponPrice = $couponPrice + $order_item['coupon_total'];
                $shipFee = $shipFee + $order_item['ship_fee'];
                $useRebateFee = $useRebateFee + $order_item['applied_rebate'];
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
            $product_buy_num['all_use_rebate_fee'] = $useRebateFee / 100;
            $product_buy_num['all_ship_fee'] = $shipFee;
            if ($division) {
                usort($orders, function ($a, $b) {
                    return ($a['id'] < $b['id']) ? 1 : -1;
                });
                $kuaidi_orders = array_filter($orders, "share_kuaidi_order_filter");
                $self_ziti_orders = array_filter($orders, "share_self_ziti_order_filter");
                $pys_ziti_orders = array_filter($orders, "share_pys_ziti_order_filter");
                $orders = array('origin_orders' => $orders, SHARE_SHIP_KUAIDI_TAG => $kuaidi_orders, SHARE_SHIP_SELF_ZITI_TAG => $self_ziti_orders, SHARE_SHIP_PYS_ZITI_TAG => $pys_ziti_orders);
            }
            //show order ship type name
            $shipTypes = ShipAddress::ship_type_list();
            $user_list = [];
            foreach($users as $user_item){
                $user_list[$user_item['User']['id']] = [
                    'id' => $user_item['User']['id'],
                    'nickname' => $user_item['User']['nickname'],
                    'image' => get_user_avatar($user_item)
                ];
            }
            $share_order_data = array('users' => $user_list, 'orders' => $orders, 'order_cart_map' => $order_cart_map, 'summery' => $product_buy_num, 'ship_types' => $shipTypes, 'rebate_logs' => $rebateLogs);
            if ($division) {
                $share_rebate_money = $this->ShareUtil->get_share_rebate_money($weshareId);
                $share_order_data['share_rebate_money'] = $share_rebate_money;
            }
            $share_order_data = json_encode($share_order_data);
            Cache::write($key, $share_order_data);
        }

        return json_decode($share_order_data, true);
    }

    /**
     * @param $weshareId
     * 批量更新订单数据
     */
    public function batch_update_order_status($weshareId)
    {
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
    public function send_to_comment_msg($weshareId = null)
    {
        $orderM = ClassRegistry::init('Order');
        $limit_date = date('Y-m-d', strtotime("-14 days"));
        $cond = array(
            'status' => ORDER_STATUS_RECEIVED,
            'type' => ORDER_TYPE_WESHARE_BUY,
            'not' => ['flag' => ORDER_FLAG_VIRTUAL_FLAG]
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
    public function change_status_and_send_to_comment_msg($weshareId = null)
    {
        $orderM = ClassRegistry::init('Order');
        $cartM = ClassRegistry::init('Cart');
        $limit_date = date('Y-m-d', strtotime("-15 days"));
        $cond = array(
            'status' => ORDER_STATUS_SHIPPED,
            'type' => ORDER_TYPE_WESHARE_BUY,
            'not' => ['flag' => ORDER_FLAG_VIRTUAL_FLAG]
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
    public function load_share_recommend_data($shareId)
    {
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
                'group' => array('user_id'),
                'order' => ['id DESC'],
                'limit' => 20
            ));
            $user_ids = Hash::extract($shareRecommendData, '{n}.RecommendLog.user_id');
            $recommends = Hash::combine($shareRecommendData, '{n}.RecommendLog.user_id', '{n}.RecommendLog');
            $recommend_users = $userM->find('all', array(
                'conditions' => array(
                    'id' => $user_ids
                ),
                'fields' => array('id', 'nickname', 'image', 'avatar', 'created')
            ));
            $recommendData = Hash::extract($recommend_users, '{n}.User');
            //reset user image
            $recommendData = array_map('map_user_avatar', $recommendData);
            foreach ($recommendData as &$data_item) {
                $data_item['recommend_reason'] = $recommends[$data_item['id']]['memo'];
            }
            Cache::write($key, json_encode($recommendData));
            return $recommendData;
        }
        return json_decode($recommendData, true);
    }


    /**
     * @param $orders
     * 通知下单用户去评论模板消息
     */
    private function process_send_to_comment_msg($orders)
    {
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
            $order_date = $order_info['Order']['created'];
            $open_id = $uid_openid_map[$order_info['Order']['creator']];
            $order_id = $order_info['Order']['id'];
            $detail_url = $this->get_weshares_detail_url($member_id) . '&comment_order_id=' . $order_id;
            $this->Weixin->send_comment_template_msg($open_id, $detail_url, $msg_title, $order_id, $order_date, $desc);
        }
    }

    /**
     * @param $comment_uid
     * @param $reply_id
     * @param $content
     * @param $share_id
     * @param $order_id
     * @param $comment_id
     * 用户之间互相评论
     */
    public function send_comment_mutual_msg($comment_uid, $reply_id, $content, $share_id, $order_id, $comment_id = 0)
    {
        $uid_name_map = $this->get_users_nickname(array($comment_uid, $reply_id));
        $title = $uid_name_map[$reply_id] . '你好，' . $uid_name_map[$comment_uid] . '对你说：' . $content;
        $desc = '分享，让生活更美。点击查看。';
        $detail_url = $this->get_weshares_detail_url($share_id) . '&comment_order_id=' . $order_id . '&reply_comment_id=' . $comment_id;
        $order_info = $this->Orders->get_order_info($order_id);
        $order_id = $order_info['Order']['id'];
        $order_date = $order_info['Order']['created'];
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
    public function send_shareed_offer_notify($order_id, $weshare_id, $comment_id)
    {
        //send to seller
        $order_info = $this->Orders->get_order_info($order_id);
        $order_creator = $order_info['Order']['creator'];
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
     * @param $comment_id
     * 通知下单用户 收到了评论
     * 标记评论
     */
    public function send_comment_notify($order_id, $weshare_id, $comment_content, $comment_id = 0)
    {
        $order_info = $this->Orders->get_order_info($order_id);
        $order_creator = $order_info['Order']['creator'];
        $share_info = $this->get_weshare_info($weshare_id);
        $share_creator = $share_info['creator'];
        $uid_name_map = $this->get_users_nickname(array($order_creator, $share_creator));
        $open_id_map = $this->get_open_ids(array($share_creator));
        $open_id = $open_id_map[$share_creator];
        $title = $uid_name_map[$share_creator] . '你好，' . $uid_name_map[$order_creator] . '说，感谢' . $uid_name_map[$share_creator] . '，' . $comment_content . '。';
        $order_id = $order_info['Order']['id'];
        $order_date = $order_info['Order']['created'];
        $desc = '分享，让生活更美。点击回复' . $uid_name_map[$order_creator] . '。';
        $detail_url = $this->get_weshares_detail_url($weshare_id) . '&comment_order_id=' . $order_id . '&reply_comment_id=' . $comment_id;
        $this->Weixin->send_comment_template_msg($open_id, $detail_url, $title, $order_id, $order_date, $desc);
        //send comment notify msg to share manager
        $share_manager_open_ids = $this->ShareAuthority->get_share_manage_auth_user_open_ids($weshare_id);
        if (!empty($share_manager_open_ids)) {
            foreach ($share_manager_open_ids as $manager_open_id_item) {
                $this->Weixin->send_comment_template_msg($manager_open_id_item, $detail_url, $title, $order_id, $order_date, $desc);
            }
        }
    }

    /**
     * @param $order_id
     * @param $weshare_id
     * 通知分享者收到了评论
     */
    public function send_comment_notify_buyer($order_id, $weshare_id)
    {
        $order_info = $this->Orders->get_order_info($order_id);
        $order_creator = $order_info['Order']['creator'];
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
        $order_id = $order_info['Order']['id'];
        $order_date = $order_info['Order']['created'];
        $desc = '分享，让生活更美。点击回复' . $uid_name_map[$share_creator] . '。';
        $detail_url = $this->get_weshares_detail_url($weshare_id) . '&comment_order_id=' . $order_id;
        $this->Weixin->send_comment_template_msg($open_id, $detail_url, $title, $order_id, $order_date, $desc);
    }

    /**
     * @param $order_id
     * @param $weshare_id
     * @param $reply_content
     * @param $comment_id
     * 收到评论回复通知
     */
    public function send_comment_reply_notify($order_id, $weshare_id, $reply_content, $comment_id = 0)
    {
        $order_info = $this->Orders->get_order_info($order_id);
        $order_creator = $order_info['Order']['creator'];
        $share_info = $this->get_weshare_info($weshare_id);
        $share_creator = $share_info['Order']['creator'];
        $uid_name_map = $this->get_users_nickname(array($order_creator, $share_creator));
        $open_id_map = $this->get_open_ids(array($share_creator, $order_creator));
        $open_id = $open_id_map[$order_creator];
        $title = $uid_name_map[$order_creator] . '你好，' . $uid_name_map[$share_creator] . '说，谢谢你对我的支持，' . $reply_content . '。';
        $order_id = $order_info['Order']['id'];
        $order_date = $order_info['Order']['created'];
        $desc = '分享，让生活更美。点击查看。';
        $detail_url = $this->get_weshares_detail_url($weshare_id) . '&comment_order_id=' . $order_id . '&reply_comment_id=' . $comment_id;
        $this->Weixin->send_comment_template_msg($open_id, $detail_url, $title, $order_id, $order_date, $desc);
    }

    /**
     * @param $shareId
     * @param $share_creator
     * @return int
     */
//    TODO: Remove this method and replace with ShareUtil.get_index_product_summary()
    public function get_share_and_all_refer_share_summary($shareId, $share_creator)
    {
        $key = SHARE_COMMENT_COUNT_SUM_CACHE_KEY . '_' . $shareId . '_' . $share_creator;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $weshareM = ClassRegistry::init('Weshare');
            $commentM = ClassRegistry::init('Comment');
            $orderM = ClassRegistry::init('Order');
            $related_share_ids = $weshareM->get_relate_share($shareId);
            $comment_count = $commentM->find('count', array(
                'conditions' => array(
                    'data_id' => $related_share_ids,
                    'parent_id' => 0,
                    'not' => array('status' => [ORDER_STATUS_WAITING_PAY, ORDER_STATUS_CANCEL], 'order_id' => 0)
                )
            ));
            $order_count = $orderM->find('count', array(
                'conditions' => array(
                    'member_id' => $related_share_ids,
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'not' => array('status' => ORDER_STATUS_WAITING_PAY)
                )
            ));
            $result = ['order' => $order_count, 'comment' => $comment_count];
            Cache::write($key, json_encode($result));
            return $result;
        }
        return json_decode($cacheData);
    }

    /**
     * @param $shareId
     * @param $share_creator
     * @return int
     */
    public function get_share_and_all_refer_share_count($shareId, $share_creator)
    {
        $key = SHARE_ORDER_COUNT_SUM_CACHE_KEY . '_' . $shareId . '_' . $share_creator;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $weshareM = ClassRegistry::init('Weshare');
            $orderM = ClassRegistry::init('Order');
            $related_share_ids = $weshareM->get_relate_share($shareId);
            $order_count = $orderM->find('count', array(
                'conditions' => array(
                    'member_id' => $related_share_ids,
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'not' => array('status' => ORDER_STATUS_WAITING_PAY)
                )
            ));
            Cache::write($key, $order_count);
            return $order_count;
        }
        return intval($cacheData);
    }

    /**
     * @param $shareId
     * @param $exclude_uid
     * @return mixed | int
     * 获取分享的总购买份数
     */
    public function get_share_all_buy_count($shareId, $exclude_uid = 0)
    {
        $orderM = ClassRegistry::init('Order');
        $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY);
        $weshareM = ClassRegistry::init('Weshare');
        $relate_share_ids = $weshareM->get_relate_share($shareId);
        $key = SHARE_ORDER_COUNT_DATA_CACHE_KEY . '_' . $shareId;
        $cacheData = Cache::read($key);
        if (!empty($cacheData)) {
            return $cacheData;
        }
        $shareOrderCount = $orderM->find('count', array(
            'conditions' => array(
                'member_id' => $relate_share_ids,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => $order_status,
                'deleted' => DELETED_NO
            )
        ));
        Cache::write($key, $shareOrderCount);
        return $shareOrderCount;
    }

    public function get_sharer_summary($uid)
    {
        $orderM = ClassRegistry::init('Order');
        $weshareM = ClassRegistry::init('Weshare');
        $shareOperateSettingM = ClassRegistry::init('ShareOperateSetting');
        //$authorize_count = $shareOperateSettingM->query('SELECT count(distinct data_id) as a_count FROM cake_share_operate_settings where user=' . $uid);
        $authorize_count = $shareOperateSettingM->query('SELECT count(distinct cs.data_id) as a_count FROM cake_share_operate_settings cs join cake_weshares cw on cw.id=cs.data_id where cw.status!=-1 and cs.user=' . $uid);
        $wait_ship_order_count = $orderM->find('count', [
            'conditions' => [
                'status' => ORDER_STATUS_PAID,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'brand_id' => $uid
            ]
        ]);
        $share_count = $weshareM->find('count', [
            'conditions' => [
                'creator' => $uid,
                'status' => [WESHARE_STATUS_NORMAL, WESHARE_STATUS_STOP],
                'settlement' => [WESHARE_SETTLEMENT_NO, WESHARE_SETTLEMENT_YES],
                'type' => [SHARE_TYPE_GROUP, SHARE_TYPE_DEFAULT, SHARE_TYPE_POOL_FOR_PROXY, SHARE_TYPE_POOL]
            ]
        ]);
        $this_month_order_count = $this->get_month_total_count($uid);
        $date = date('Y-m') . '-01 00:00:00';
        $this_month_trade_money = $orderM->query("SELECT sum(total_price) as trade_money  FROM cake_orders where status > ".ORDER_STATUS_WAITING_PAY." and status != ".ORDER_STATUS_CANCEL." and creator = " . $uid . " and created > '" . $date . "' and type=9");
        $this_month_trade_money = empty($this_month_trade_money[0][0]['trade_money']) ? 0 : $this_month_trade_money[0][0]['trade_money'];
        $authorize_count = intval($authorize_count[0][0]['a_count']);
        return ['wait_ship_order_count' => $wait_ship_order_count, 'month_order_count' => $this_month_order_count, 'month_trade_money' => $this_month_trade_money, 'share_count' => $share_count, 'authorize_count' => $authorize_count];
    }

    //获取用户的订单汇总
    public function get_user_order_summary($uid)
    {
        $orderM = ClassRegistry::init('Order');
        $result = $orderM->query('SELECT count(id) as s_count, status FROM cake_orders where creator = ' . $uid . ' and type=9 group by status;');
        $result = Hash::combine($result, '{n}.cake_orders.status', '{n}.0.s_count');
        return $result;
    }

    public function get_user_all_order_count($uid)
    {
        $orderM = ClassRegistry::init('Order');
        $count = $orderM->find('count', [
            'conditions' => [
                'creator' => $uid,
                'status' => [ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY],
                'type' => ORDER_TYPE_WESHARE_BUY,
            ]
        ]);
        return $count;
    }

    public function get_days_order_summary($uid, $start_date, $end_date)
    {
        $orderM = ClassRegistry::init('Order');
        $sql = "SELECT date(created) as day_date, count(id) as order_count, format(sum(total_all_price),2) as total_fee FROM cake_orders WHERE brand_id = $uid AND status > ".ORDER_STATUS_WAITING_PAY." AND status != ".ORDER_STATUS_CANCEL." AND created > '$start_date' AND created < '$end_date' AND type = 9 GROUP BY date(created) order by day_date desc";
        $data = $orderM->query($sql);
        $result = [];
        foreach ($data as $data_item) {
            $result[] = $data_item[0];
        }
        return $result;
    }

    public function get_days_order_detail($uid, $start_date, $end_date)
    {
        $orderM = ClassRegistry::init('Order');
        $weshareM = ClassRegistry::init('Weshare');
        $sql = "select member_id, count(id) as order_count, format(sum(total_all_price),2) as total_fee from cake_orders where brand_id = $uid and type = 9 and status > ".ORDER_STATUS_WAITING_PAY." and status != ".ORDER_STATUS_CANCEL." and created > '$start_date' and created < '$end_date' group by member_id order by member_id desc";
        $data = $orderM->query($sql);
        $member_id = [];
        $summary = [];
        foreach ($data as $item) {
            $tmp = $item[0];
            $share_id = $item['cake_orders']['member_id'];
            $member_id[] = $share_id;
            $tmp['share_id'] = $share_id;
            $summary[] = $tmp;
        }
        $weshares = $weshareM->find('all', [
            'conditions' => [
                'id' => $member_id
            ],
            'fields' => ['id', 'title', 'default_image']
        ]);
        $weshares = Hash::combine($weshares, '{n}.Weshare.id', '{n}.Weshare');
        return ['summary' => $summary, 'weshares' => $weshares];
    }

    /**
     * @param $uid
     * @return array|mixed
     *
     */
    public function get_user_share_summary($uid)
    {
        $key = SHARE_USER_SUMMERY_CACHE_KEY . '_' . $uid;
        $summery_data = Cache::read($key);
        if (empty($summery_data)) {
            $weshareM = ClassRegistry::init('Weshare');
            $weshare_count = $weshareM->find('count', array(
                'conditions' => [
                    'creator' => $uid,
                    'status' => [WESHARE_STATUS_NORMAL, WESHARE_STATUS_STOP],
                    'settlement' => [WESHARE_SETTLEMENT_NO, WESHARE_SETTLEMENT_YES],
                    'type' => [SHARE_TYPE_GROUP, SHARE_TYPE_DEFAULT, SHARE_TYPE_POOL_FOR_PROXY, SHARE_TYPE_POOL]
                ]
            ));
            $fans_count = $this->get_sharer_fans_count($uid);
            $focus_count = $this->get_sharer_focus_count($uid);
            $comments_count = $this->get_sharer_comments_count($uid);
            $summery_data = array('share_count' => $weshare_count, 'follower_count' => $fans_count, 'focus_count' => $focus_count, 'comment_count' => $comments_count);
            Cache::write($key, json_encode($summery_data));
            return $summery_data;
        }
        return json_decode($summery_data, true);
    }

    public function get_sharer_fans_count($uid){
        $userRelationM = ClassRegistry::init('UserRelation');
        $fans_count = $userRelationM->find('count', array(
            'conditions' => array(
                'user_id' => $uid,
                'deleted' => DELETED_NO
            )
        ));
        return $fans_count;
    }

    public function get_sharer_focus_count($uid){
        $userRelationM = ClassRegistry::init('UserRelation');
        $focus_count = $userRelationM->find('count', array(
            'conditions' => array(
                'follow_id' => $uid,
                'deleted' => DELETED_NO
            )
        ));
        return $focus_count;
    }

    /**
     * @param $uid
     * @param $limit
     * @return mixed
     * 获取用户关注
     */
    public function get_user_focus($uid, $limit = 0)
    {
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
    public function get_user_fans_data($uid, $limit = 0)
    {
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
            //usort($relation_map, 'sort_data_by_id');
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
    public function send_notify_buy_user_msg($weshare_info, $msg_content)
    {
        $buy_uids = $this->get_has_buy_user($weshare_info['id']);
        $buy_open_ids = $this->get_open_ids($buy_uids);
        $tuan_leader_name = $weshare_info['creator']['nickname'];
        $remark = '点击查看详情！';
        $deatil_url = $this->get_weshares_detail_url($weshare_info['id']);
        $product_name = $weshare_info['title'];
        if (!empty($buy_open_ids)) {
            $buy_open_ids = array_unique($buy_open_ids);
            foreach ($buy_open_ids as $open_id) {
                $this->Weixin->send_share_buy_complete_msg($open_id, $msg_content, $product_name, $tuan_leader_name, $remark, $deatil_url);
            }
        }
    }

    /**
     * @param $weshare_info
     * @param $msg_content
     * 发送给管理员团购结果通知
     */
    public function send_notify_user_msg_to_share_manager($weshare_info, $msg_content)
    {
        $share_id = $weshare_info['id'];
        $share_manager = $this->ShareAuthority->get_share_manage_auth_users($share_id);
        if (empty($share_manager)) {
            $share_manager = array();
        }
        $share_manager[] = $weshare_info['creator']['id'];
        $open_ids = $this->get_open_ids($share_manager);
        $tuan_leader_name = $weshare_info['creator']['nickname'];
        $remark = '点击查看详情！';
        $deatil_url = $this->get_weshares_detail_url($weshare_info['id']);
        $product_name = $weshare_info['title'];
        if(!empty($open_ids)){
            foreach ($open_ids as $open_id) {
                $this->Weixin->send_share_buy_complete_msg($open_id, $msg_content, $product_name, $tuan_leader_name, $remark, $deatil_url);
            }
        }
    }

    /**
     * @param $weshare_info
     * @param $msg_content
     * 发送团购提醒给分享管理员
     */
    public function send_buy_percent_msg_to_share_manager($weshare_info, $msg_content)
    {
        $share_id = $weshare_info['id'];
        $share_manager = $this->ShareAuthority->get_share_manage_auth_users($share_id);
        if (empty($share_manager)) {
            $share_manager = array();
        }
        $share_manager[] = $weshare_info['creator']['id'];
        $this->do_send_buy_percent_msg($weshare_info, $share_manager, $msg_content);
    }

    /**
     * @param $weshare_info
     * @param $msg_content
     * @param $limit
     * @param $offset
     * 发送团购进度消息
     */
    public function send_buy_percent_msg($weshare_info, $msg_content, $limit = null, $offset = null)
    {
        $share_creator = $weshare_info['creator']['id'];
        $fans_ids = $this->load_fans_by_sharer($share_creator, $limit, $offset);
        //filter user
        $fans_ids = $this->check_msg_log_and_filter_user($weshare_info['id'], $fans_ids, MSG_LOG_NOTIFY_TYPE);
        $fans_ids = $this->check_msg_log_and_filter_user($weshare_info['refer_share_id'], $fans_ids, MSG_LOG_NOTIFY_TYPE);
        $fans_ids = $this->check_msg_log_and_filter_user($weshare_info['id'], $fans_ids, MSG_LOG_RECOMMEND_TYPE);
        $fans_ids = $this->check_msg_log_and_filter_user($weshare_info['refer_share_id'], $fans_ids, MSG_LOG_RECOMMEND_TYPE);
        $this->save_msg_logs($weshare_info['id'], $fans_ids, MSG_LOG_NOTIFY_TYPE);
        $this->do_send_buy_percent_msg($weshare_info, $fans_ids, $msg_content);
    }

    /**
     * @param $sharer_id
     * @param $title
     * @param $shop_name
     * @param null $limit
     * @param null $offset
     * @return bool
     * 店铺通知
     */
    public function send_shop_notify($sharer_id, $title, $shop_name, $limit = null, $offset = null)
    {
        $fans_ids = $this->load_fans_by_sharer($sharer_id, $limit, $offset);
        if(empty($fans_ids)){
            return false;
        }
        $fans_open_ids = $this->get_open_ids($fans_ids);
        if(empty($fans_open_ids)){
            return false;
        }
        $fans_data_nickname = $this->get_users_nickname($fans_ids);
        $fans_open_ids = array_unique($fans_open_ids);
        foreach($fans_open_ids as $uid => $open_id){
            $nTitle = $fans_data_nickname[$uid] . '你好，' . $title;
            $this->Weixin->send_shop_notify_msg($open_id, $sharer_id, $shop_name, $nTitle);
        }
    }

    /**
     * @param $weshare_info
     * @param $uids
     * @param $msg_content
     * 触发发送团购进度的通知
     */
    public function do_send_buy_percent_msg($weshare_info, $uids, $msg_content)
    {
        $fans_data_nickname = $this->get_users_nickname($uids);
        $fans_data_ids = $uids;
        $fans_open_ids = $this->get_open_ids($fans_data_ids);
        if (!empty($fans_data_ids)) {
            $product_name = $weshare_info['title'];
            $tuan_leader_name = $weshare_info['creator']['nickname'];
            $remark = '点击详情，赶快加入' . $tuan_leader_name . '的分享！';
            $deatil_url = $this->get_weshares_detail_url($weshare_info['id']);
            $already_buy_uids = $this->get_has_buy_user($weshare_info['id']);
            $no_buy_uids = [];
            if(!empty($fans_open_ids)){
                $fans_open_ids = array_unique($fans_open_ids);
                foreach ($fans_open_ids as $uid => $open_id) {
                    if (!in_array($uid, $already_buy_uids)) {
                        $no_buy_uids[] = $uid;
                        $title = $fans_data_nickname[$uid] . '你好，' . $msg_content;
                        $this->Weixin->send_share_buy_complete_msg($open_id, $title, $product_name, $tuan_leader_name, $remark, $deatil_url);
                    }
                }
            }
//            $jpush_title = "您好，{$tuan_leader_name}发起一个分享!";
//            foreach ($no_buy_uids as $item) {
//                $this->SharePush->push_share_offered_msg($item, $jpush_title, $remark, $weshare_info['id']);
//            }
        }
    }

    /**
     * @param $recommend_user
     * @param $share_id
     * @param $memo
     *
     */
    public function send_recommend_msg($recommend_user, $share_id, $memo)
    {
        $checkSendMsgResult = $this->ShareUtil->checkCanSendMsg($recommend_user, $share_id, MSG_LOG_RECOMMEND_TYPE);
        if (!$checkSendMsgResult['success']) {
            return $checkSendMsgResult;
        }
        $fansPageInfo = $this->get_user_relation_page_info($recommend_user);
        $pageCount = $fansPageInfo['pageCount'];
        $pageSize = $fansPageInfo['pageSize'];
        $this->RedisQueue->add_tasks('share', "/task/process_send_recommend_msg/" . $share_id . '/' . $recommend_user . '/' . $pageCount . '/' . $pageSize, "memo=" . $memo);
        return $checkSendMsgResult;
    }

    /**
     * @param $weshareId
     * @param $recommend_user
     * @param $memo
     * @param null $limit
     * @param null $offset
     */
    public function send_recommend_msg_task($weshareId, $recommend_user, $memo, $limit = null, $offset = null)
    {
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
        $followers = $this->load_fans_by_sharer($recommend_user, $limit, $offset);
        $hasBuyUsers = $this->get_has_buy_user($weshareId);
        $followers = array_diff($followers, $hasBuyUsers);
        //过滤模板消息
        $followers = $this->check_msg_log_and_filter_user($weshareId, $followers, MSG_LOG_NOTIFY_TYPE);
        //check msg logs filter users
        $followers = $this->check_msg_log_and_filter_user($weshareId, $followers, MSG_LOG_RECOMMEND_TYPE);
        //filter refer share id
        $followers = $this->check_msg_log_and_filter_user($weshare['Weshare']['refer_share_id'], $followers, MSG_LOG_NOTIFY_TYPE);
        $followers = $this->check_msg_log_and_filter_user($weshare['Weshare']['refer_share_id'], $followers, MSG_LOG_RECOMMEND_TYPE);
        $this->save_msg_logs($weshareId, $followers, MSG_LOG_RECOMMEND_TYPE);
        $openIds = $this->Oauthbind->findWxServiceBindsByUids($followers);
        if (!empty($openIds)) {
            foreach ($openIds as $openId) {
                $this->Weixin->send_recommend_template_msg($openId, $detail_url, $remark, $title, $product_name, $sharer_name);
            }
        }
    }

    /**
     * @param $data_id
     * @param $user_ids
     * @param $type
     * @return array
     * 查找用户消息记录，过滤用户
     */
    private function check_msg_log_and_filter_user($data_id, $user_ids, $type)
    {
        if ($data_id == 0) {
            return $user_ids;
        }
        $msgLogM = ClassRegistry::init('MsgLog');
        $q_date = date('Y-m-d');
        $msgLogs = $msgLogM->find('all', array(
            'conditions' => array(
                'data_id' => $data_id,
                'data_type' => $type,
                'created > ' => $q_date,
                'user_id' => $user_ids
            ),
            'fields' => array('user_id'),
            'limit' => 500,
            'order' => array('id desc')
        ));
        $msgLogUserIds = Hash::extract($msgLogs, '{n}.MsgLog.user_id');
        $user_ids = array_diff($user_ids, $msgLogUserIds);
        $user_ids = array_filter($user_ids);
        return $user_ids;
    }

    private function save_msg_logs($data_id, $user_ids, $type)
    {
        $saveMsgLogData = array();
        foreach ($user_ids as $item_uid) {
            $saveMsgLogData[] = array(
                'data_id' => $data_id,
                'data_type' => $type,
                'user_id' => $item_uid,
                'created' => date('Y-m-d H:i:s'),
            );
        }
        if (!empty($saveMsgLogData)) {
            $msgLogM = ClassRegistry::init('MsgLog');
            $msgLogM->saveAll($saveMsgLogData);
        }
    }

    /**
     * @param $sharer_id
     * @param $follow_id
     * 获取用户关注信息
     */
    public function check_user_subscribe($sharer_id, $follow_id)
    {
        return $this->ShareUtil->check_user_is_subscribe($sharer_id, $follow_id);
    }

    /**
     * @param $sharer_id
     * @param $follow_id
     * @param $type
     * 关注
     */
    public function subscribe_sharer($sharer_id, $follow_id, $type = 'SUB')
    {
        if (!$this->ShareUtil->check_user_is_subscribe($sharer_id, $follow_id)) {
            $this->ShareUtil->save_relation($sharer_id, $follow_id, $type);
            delete_redis_data_by_key('_' . $follow_id);
            delete_redis_data_by_key('_' . $sharer_id);
            $this->send_sub_template_msg($sharer_id, $follow_id);
        }
        //$this->ShareUtil->usedUserSubSharerReason($follow_id);;
    }

    /**
     * @param $share_id
     * @param $follow_id
     * @param string $type
     * 关注分享者 通过分享
     */
    public function subscribe_sharer_by_share($share_id, $follow_id, $type = 'SUB')
    {
        $share_info = $this->get_weshare_info($share_id);
        $sharer_id = $share_info['creator'];
        $this->subscribe_sharer($sharer_id, $follow_id, $type);
    }

    /**
     * @param $sharer_id
     * @param $follow_id
     * 取消关注
     */
    public function unsubscribe_sharer($sharer_id, $follow_id)
    {
        $this->ShareUtil->delete_relation($sharer_id, $follow_id);
        delete_redis_data_by_key('_' . $follow_id);
        delete_redis_data_by_key('_' . $sharer_id);
    }

    /**
     * @param $sharer_id
     * @param $follow_id
     */
    public function send_sub_template_msg($sharer_id, $follow_id)
    {
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
    public function get_user_relation_page_info($uid)
    {
        $UserRelationM = ClassRegistry::init('UserRelation');
        $totalRecords = $UserRelationM->find('count', array(
            'conditions' => array(
                'user_id' => $uid,
                'deleted' => DELETED_NO
            )
        ));
        $pageSize = 50;
        $pageCount = ceil($totalRecords / $pageSize);
        return array('pageCount' => $pageCount, 'pageSize' => $pageSize);
    }

    /**
     * @param $uid
     * @return array
     * 获取用户分享信息
     */
    public function get_user_weshares($uid)
    {
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
     * @param $status
     * @return array
     *
     * 获取本次分享已经购买的用户
     */
    public function get_has_buy_user($share_id, $status = array(ORDER_STATUS_PAID))
    {
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => $status,
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
    public function get_has_buy_user_map($share_ids)
    {
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => array(ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY),
                'member_id' => $share_ids
            ),
            'fields' => ['id', 'member_id', 'creator'],
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
    public function get_sharer_detail_url($sharer_id)
    {
        return WX_HOST . '/weshares/user_share_info/' . $sharer_id;
    }

    /**
     * @param $weshareId
     * @return string
     * 获取分享的地址
     */
    public function get_weshares_detail_url($weshareId)
    {
        return WX_HOST . '/weshares/view/' . $weshareId . '?from=template_msg';
    }

    public function get_open_id($uid)
    {
        $oauthBindM = ClassRegistry::init('Oauthbind');
        $uid_openid_map = $oauthBindM->findWxServiceBindMapsByUids(array($uid));
        return $uid_openid_map[$uid];
    }

    public function get_open_ids($uids)
    {
        $oauthBindM = ClassRegistry::init('Oauthbind');
        $uid_openid_map = $oauthBindM->findWxServiceBindMapsByUids($uids);
        return $uid_openid_map;
    }

    private function findCarts($orderId)
    {
        $this->Cart = ClassRegistry::init('Cart');
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $orderId
            ),
            'fields' => $this->query_cart_fields
        ));
        return $carts;
    }

    public function get_users_nickname($uids)
    {
        $userM = ClassRegistry::init('User');
        return $userM->findNicknamesMap($uids);
    }

    public function get_user_nickname($uid)
    {
        $userM = ClassRegistry::init('User');
        return $userM->findNicknamesOfUid($uid);
    }

    /**
     * @param $share_id
     * @return mixed
     */
    public function get_weshare_info($share_id)
    {
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
    public function get_all_share_info($share_ids)
    {
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
    public function has_share_offer($uid)
    {
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
    public function get_sharer_mobile($uid)
    {
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
    public function sharer_has_offer($sharer_ids)
    {
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
    public function get_cart_name_and_num($order_id)
    {
        $carts = $this->findCarts($order_id);
        $num = 0;
        $cart_name = array();
        foreach ($carts as $cart_item) {
            $num += $cart_item['Cart']['num'];
            $cart_name[] = $cart_item['Cart']['name'] . 'X' . $cart_item['Cart']['num'];
        }
        return array('num' => $num, 'cart_name' => implode(',', $cart_name));
    }

    /**
     * @param $shareId
     * @return bool
     * 检测分享是否截团
     */
    public function check_weshare_status($shareId)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $weshare = $weshareM->find('first', array(
            'conditions' => array(
                'id' => $shareId
            ),
            'fields' => array('id', 'status')
        ));
        if (empty($weshare)) {
            return false;
        }
        return $weshare['Weshare']['status'] == WESHARE_STATUS_NORMAL;
    }

    /**
     * @param $share_id
     * @param $user
     * @param $is_paid
     * @param $is_rebate
     * @param $order_id
     * @param $rebate_money
     * 团长购买有佣金的东西进行返利
     */
    public function log_proxy_rebate_log($share_id, $user, $is_paid, $is_rebate, $order_id, $rebate_money)
    {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $data = array(
            'share_id' => $share_id,
            'sharer' => $user,
            'clicker' => $user,
            'is_paid' => $is_paid,
            'is_rebate' => $is_rebate,
            'order_id' => $order_id,
            'rebate_money' => $rebate_money,
            'type' => PROXY_USER_PAID_REBATE_TYPE
        );
        $rebateLog = $rebateTrackLogM->save($data);
        return $rebateLog['RebateTrackLog']['id'];
    }

    /**
     * @param $total_price
     * @param $uid
     * @param $shareId
     * @return float|int
     * 计算团长返利
     * 团长下单的时候自动扣除返利钱
     */
    public function cal_proxy_rebate_fee($total_price, $uid, $shareId)
    {
        $data_val = $this->ShareUtil->get_user_level($uid);
        $data_val = $data_val['data_value'];
        if ($data_val >= PROXY_USER_LEVEL_VALUE) {
            $rebate_setting = $this->ShareUtil->get_share_rebate_data($shareId);
            if (!empty($rebate_setting)) {
                $rebate_money = round((floatval($rebate_setting['ProxyRebatePercent']['percent']) * $total_price) / (100 * 100), 2);
                return $rebate_money;
            }
        }
        return 0;
    }

    /**
     * @param $uid
     * @return mixed
     * 获取团长当月订单
     */
    public function get_month_total_count($uid)
    {
        $orderM = ClassRegistry::init('Order');
        $first_day = date('Y-m-01', strtotime(date('Y-m-d')));
        $last_day = date('Y-m-d', strtotime("$first_day +1 month -1 day"));
        $order_count = $orderM->find('count', array(
            'conditions' => array(
                'brand_id' => $uid,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'not' => array('status' => ORDER_STATUS_WAITING_PAY),
                'date(created) BETWEEN ? AND ?' => array($first_day, $last_day)
            )
        ));
        return $order_count;
    }

    public function get_sharer_order_summary($uid, $start_date, $end_date)
    {
        $sql = "select count(id) as order_count, format(sum(total_all_price),2) as total_fee from cake_orders where type=9 and status > ".ORDER_STATUS_WAITING_PAY." and status != ".ORDER_STATUS_CANCEL." and brand_id=$uid and created > '$start_date' and created < '$end_date'";
        $orderM = ClassRegistry::init('Order');
        $result = $orderM->query($sql);
        $order_count = $result[0][0]['order_count'];
        $total_fee = empty($result[0][0]['total_fee']) ? 0 : $result[0][0]['total_fee'];
        return ['order_count' => $order_count, 'total_fee' => $total_fee];
    }

    /**
     * @param $share_ids
     * @return array
     * 获取所有分享的结算金额
     */
    public function get_shares_balance_money($share_ids)
    {
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY,
                'member_id' => $share_ids,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY),
                'not' => array('flag' => ORDER_FLAG_VIRTUAL_FLAG)
            )
        ));
        $refund_orders = array();
        $refund_order_ids = array();
        $summery_data = array();
        foreach ($orders as $item) {
            $member_id = $item['Order']['member_id'];
            $order_total_price = $item['Order']['total_all_price'];
            $order_ship_fee = $item['Order']['ship_fee'];
            $order_coupon_total = $item['Order']['coupon_total'];
            $order_product_price = $item['Order']['total_price'];
            if ($item['Order']['status'] == ORDER_STATUS_RETURN_MONEY || $item['Order']['status'] == ORDER_STATUS_RETURNING_MONEY) {
                $refund_order_ids[] = $item['Order']['id'];
                if (!isset($refund_orders[$member_id])) {
                    $refund_orders[$member_id] = array();
                }
                $refund_orders[$member_id][] = $item;
            }
            if (!isset($summery_data[$member_id])) {
                $summery_data[$member_id] = array('total_price' => 0, 'ship_fee' => 0, 'coupon_total' => 0);
            }
            $summery_data[$member_id]['total_price'] = $summery_data[$member_id]['total_price'] + $order_total_price;
            $summery_data[$member_id]['ship_fee'] = $summery_data[$member_id]['ship_fee'] + $order_ship_fee;
            $summery_data[$member_id]['coupon_total'] = $summery_data[$member_id]['coupon_total'] + $order_coupon_total;
            $summery_data[$member_id]['product_total_price'] = $summery_data[$member_id]['product_total_price'] + $order_product_price;
        }
        $RefundLogM = ClassRegistry::init('RefundLog');
        $refund_logs = $RefundLogM->find('all', array(
            'order_id' => $refund_order_ids
        ));
        $refund_logs = Hash::combine($refund_logs, '{n}.RefundLog.order_id', '{n}.RefundLog.refund_fee');
        $weshare_refund_money_map = array();
        foreach ($refund_orders as $item_share_id => $item_orders) {
            $share_refund_money = 0;
            $weshare_refund_money_map[$item_share_id] = 0;
            foreach ($item_orders as $refund_order_item) {
                $order_id = $refund_order_item['Order']['id'];
                $share_refund_money = $share_refund_money + $refund_logs[$order_id];
            }
            $weshare_refund_money_map[$item_share_id] = $share_refund_money / 100;
        }
        $weshare_rebate_map = $this->get_shares_rebate_money($share_ids);
        $weshare_repaid_map = $this->get_share_repaid_money($share_ids);
        return array('weshare_repaid_map' => $weshare_repaid_map, 'weshare_rebate_map' => $weshare_rebate_map, 'weshare_refund_map' => $weshare_refund_money_map, 'weshare_summery' => $summery_data);
    }

    /**
     * @param $share_ids
     * @return array
     * 获取分享的返利金额
     */
    public function get_shares_rebate_money($share_ids)
    {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $rebateLogs = $rebateTrackLogM->find('all', array(
            'conditions' => array(
                'share_id' => $share_ids,
                'not' => array('order_id' => 0, 'is_paid' => 0)
            ),
            'limit' => 500
        ));
        $share_rebate_map = array();
        $result = [];
        foreach ($rebateLogs as $log) {
            $share_id = $log['RebateTrackLog']['share_id'];
            if (!isset($share_rebate_map[$share_id])) {
                $share_rebate_map[$share_id] = 0;
            }
            $share_rebate_map[$share_id] = $share_rebate_map[$share_id] + $log['RebateTrackLog']['rebate_money'];
        }
        foreach ($share_rebate_map as $key => $rebate_item) {
            $result[$key] = number_format(round($rebate_item / 100, 2), 2);
        }
        return $result;
    }

    /**
     * @param $share_ids
     * @return array
     * 获取分享的补退差价
     */
    public function get_share_repaid_money($share_ids)
    {
        $orderM = ClassRegistry::init('Order');
        $addOrderResult = $orderM->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY_ADD,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_REFUND_DONE),
                'member_id' => $share_ids
            ),
            'fields' => array('total_all_price', 'id', 'member_id'),
            'group' => array('member_id')
        ));
        $repaid_money_result = array();
        foreach ($addOrderResult as $item) {
            $member_id = $item['Order']['member_id'];
            if (!isset($repaid_money_result[$member_id])) {
                $repaid_money_result[$member_id] = 0;
            }
            $repaid_money_result[$member_id] = $repaid_money_result[$member_id] + $item['Order']['total_all_price'];
        }
        return $repaid_money_result;
    }

    public function update_share_view_count($id)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $weshareM->update(['view_count' => 'view_count + 1'], ['id' => $id]);
    }

    public function get_shares_list($share_ids)
    {
        $weshareM = ClassRegistry::init('Weshare');

        $shares = $weshareM->find('all', array(
            'conditions' => array(
                'id' => $share_ids,
                'status' => WESHARE_STATUS_NORMAL
            ),
            'fields' => $this->query_share_fields,
            'order' => array('created DESC'),
            'limit' => 100
        ));
        $this->explode_share_imgs($shares);

        return $shares;
    }

    public function get_sharer_info_with_sub_status($sharer_id, $uid)
    {
        $userM = ClassRegistry::init('User');
        $sharer_info = $userM->find('first', [
            'conditions' => ['User.id' => $sharer_id],
            'joins' => [
                [
                    'table' => 'user_relations',
                    'alias' => 'UserRelation',
                    'type' => 'LEFT',
                    'conditions' => [
                        'UserRelation.user_id = User.id',
                        'UserRelation.follow_id' => $uid,
                        'UserRelation.deleted' => DELETED_NO,
                    ],
                ], [
                    'table' => 'user_levels',
                    'alias' => 'UserLevel',
                    'type' => 'LEFT',
                    'conditions' => [
                        'UserLevel.data_id = User.id',
                    ],
                ],
            ],
            'fields' => ['User.id', 'User.nickname', 'User.avatar', 'User.image', 'UserLevel.data_value', 'UserRelation.id']
        ]);
        return $sharer_info;
    }

    /**
     * @param $only_paid
     * @param $share_id
     * @param $limit
     * @param $page
     * @param string $keyword
     * @return array
     * 我上了产品街的订单
     */
    public function get_provide_share_order($only_paid, $share_id, $limit, $page, $keyword = '')
    {
        $orderM = ClassRegistry::init('Order');
        $cond = [
            'Order.type' => ORDER_TYPE_WESHARE_BUY,
            'not' => ['Order.status' => ORDER_STATUS_CANCEL, 'Order.flag' => ORDER_FLAG_VIRTUAL_FLAG],
        ];
        if (!empty($keyword)) {
            $cond['OR'] = [
                ['Order.consignee_name like ' => '%' . $keyword . '%'],
                ['Order.consignee_address like ' => '%' . $keyword . '%'],
                ['Order.consignee_mobilephone' => '%' . $keyword . '%']
            ];
        }
        if ($only_paid == 1) {
            $cond['Order.status'] = ORDER_STATUS_PAID;
        } else {
            $cond['Order.status > '] = ORDER_STATUS_WAITING_PAY;
        }
        $cond['Weshare.refer_share_id'] = $share_id;
        $cond['Weshare.type'] = SHARE_TYPE_POOL;
        $orders = $orderM->find('all', [
            'conditions' => $cond,
            'limit' => $limit,
            'page' => $page,
            'joins' => [
                [
                    'type' => 'left',
                    'table' => 'cake_weshares',
                    'alias' => 'Weshare',
                    'conditions' => 'Weshare.id = Order.member_id'
                ]
            ],
            'recursive' => 1,
            'fields' => ['Order.id', 'Order.member_id', 'Order.total_all_price', 'Order.consignee_name', 'Order.consignee_address', 'Order.consignee_mobilephone', 'Order.ship_type_name', 'Order.ship_type', 'Order.ship_code', 'Order.status', 'Order.created', 'Order.pay_time', 'Order.creator', 'Order.business_remark']
        ]);
        $result = [];
        foreach ($orders as $order_item) {
            $carts = $order_item['Cart'];
            $cart_info = [];
            foreach ($carts as $cart_item) {
                $cart_info[] = $cart_item['name'] . 'X' . $cart_item['num'];
            }
            $order = $order_item['Order'];
            $order['carts'] = $cart_info;
            $result[] = $order;
        }
        return $result;
    }
}