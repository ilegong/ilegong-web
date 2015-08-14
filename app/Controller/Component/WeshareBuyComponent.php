<?php


class WeshareBuyComponent extends Component {
    //TODO 重构 weshare controller
    var $name = 'WeshareBuyComponent';

    var $query_user_fields = array('id', 'nickname', 'image', 'wx_subscribe_status', 'description');

    var $query_order_fields = array('id', 'creator', 'created', 'consignee_name', 'consignee_mobilephone', 'consignee_address', 'status', 'total_all_price', 'coupon_total', 'ship_mark', 'ship_code', 'ship_type');

    var $query_cart_fields = array('id', 'order_id', 'name', 'product_id', 'num');

    var $query_comment_fields = array('id', 'username', 'user_id', 'data_id', 'type', 'body', 'order_id', 'parent_id');

    var $components = array('Session', 'Weixin');


    public function load_comment_by_share_id($weshare_id) {
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
        usort($reply_comments, function ($a, $b) {
            return ($a['id'] > $b['id']) ? 1 : -1;
        });
        $commentReplies = $commentReplyM->find('all', array(
            'conditions' => array(
                'data_id' => $weshare_id,
                'data_type' => COMMENT_SHARE_TYPE
            ),
            'order' => array('id desc')
        ));
        $comment_reply_relation = array();
        foreach($commentReplies as $commentReply){
            $comment_id = $commentReply['CommentReply']['comment_id'];
            $replay_id = $commentReply['CommentReply']['replay_id'];
            if(!isset($comment_reply_relation[$comment_id])){
                $comment_reply_relation[$comment_id] = array();
            }
            $comment_reply_relation[] = $replay_id;
        }
        $comment_replies = $this->recursionReply($order_comments, $reply_comments, $comment_reply_relation);
        return array('order_comments' => $order_comments, 'comment_replies' => $comment_replies);
    }

    private function recursionReply($order_comments, $reply_comments, $comment_replay_relation) {
        $comment_reply_format_result = array();
        foreach($order_comments as $comment){
            $comment_id = $comment['id'];
            $comment_reply_result = array();
            $this->processRecursionReply($reply_comments, $comment_reply_result, $comment_replay_relation, $comment_id, $level = 0);
            $comment_reply_format_result[$comment_id] = $comment_reply_result;
        }
        return $comment_reply_format_result;
    }

    private function processRecursionReply($reply_comments, &$comment_replay_format_result, $comment_replay_relation, $comment_id, $level = 1) {
        $comment_replay_relation = $comment_replay_relation[$comment_id];
        if (!empty($comment_replay_relation)) {
            foreach ($comment_replay_relation as $comment_id => $reply_id) {
                $reply = $reply_comments[$reply_id];
                $username = $reply['username'];
                if($level == 0){
                    $parent_comment = $reply_comments[$comment_id];
                    $username = $reply['username'].'回复'.$parent_comment['username'];
                }
                $comment_replay_format_result[] = array('username' => $username, 'id' => $reply['id'], 'body' => $reply['body']);
                $reply_reply_relation = $comment_replay_relation[$reply_id];
                if(!empty($reply_reply_relation)){
                    $this->processRecursionReply($reply_comments, $comment_replay_format_result, $comment_replay_relation, $comment_id, $level = 1);
                }
            }
        }
        return $comment_replay_format_result;
    }

    public function create_share_comment($order_id, $comment_content, $reply_comment_id, $comment_uid, $share_id) {
        $commentM = ClassRegistry::init('Comment');
        $userM = ClassRegistry::init('User');
        $orderM = ClassRegistry::init('Order');
        $user_nickname = $userM->findNicknamesOfUid($comment_uid);
        $order_info = $orderM->findOrderByConditionsAndFields(array('id' => $order_id), array('created'));
        $date_time = date('Y-m-d H:i:s');
        $buy_date_time = $order_info['Order']['created'];
        $commentData = array('parent_id' => $reply_comment_id, 'user_id' => $comment_uid, 'username' => $user_nickname, 'body' => $comment_content, 'data_id' => $share_id, 'type' => COMMENT_SHARE_TYPE, 'created' => $date_time, 'updated' => $date_time, 'buy_time' => $buy_date_time, 'order_id' => $order_id, 'status' => COMMENT_SHOW_STATUS);
        $comment = $commentM->save($commentData);
        if (empty($comment)) {
            $this->log('save comment fail order id ' . $order_id . ' uid ' . $comment_uid . ' share id ' . $share_id);
            return array('success' => false);
        }
        if ($reply_comment_id != 0) {
            //save replay relation
            $commentReplyM = ClassRegistry::init('CommentReply');
            $commentReplyData = array('comment_id' => $reply_comment_id, 'reply_id' => $comment['Comment']['id']);
            $commentReply = $commentReplyM->save($commentReplyData);
            if (empty($commentReply)) {
                $this->log('save comment reply fail order id ' . $order_id . ' uid ' . $comment_uid . ' share id ' . $share_id . ' comment id ' . $comment['Comment']['id']);
                return array('success' => false);
            }
        }
        //update order status
        $orderM->updateAll(array('status' => ORDER_STATUS_DONE), array('id' => $order_id));
        return array('success' => true, 'comment' => $comment['Comment'], 'comment_reply' => $commentReply['CommentReply']);
    }

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
            $nick_name_map = $this->User->findNicknamesMap(array($share_creator, $order_user_id));
            $order_user_nickname = $nick_name_map[$order_user_id];
            $share_creator_nickname = $nick_name_map[$share_creator];
            $title = $order_user_nickname . '你好，' . $share_creator_nickname . '分享的' . $weshare_info['Weshare']['title'] . '寄出了，请注意查收。';
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

    public function load_fans_buy_sharer($sharerId, $weshareId) {
        $this->Weshare = ClassRegistry::init('Weshare');
        $this->Order = ClassRegistry::init('Order');
        $this->User = ClassRegistry::init('User');
        $weshares = $this->Weshare->find('all', array(
            'conditions' => array(
                'creator' => $sharerId,
                'not' => array(
                    'id' => array($weshareId)
                )
            ),
            'fields' => array('id')
        ));
        $weshare_ids = Hash::extract($weshares, '{n}.Weshare.id');
        $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED);
        //query fans limit 1000
        $follower = $this->Order->find('all', array(
            'conditions' => array(
                'member_id' => $weshare_ids,
                'status' => $order_status,
                'type' => ORDER_TYPE_WESHARE_BUY,
            ),
            'fields' => array('DISTINCT creator'),
            'limit' => 2000
        ));
        $follower_ids = Hash::extract($follower, '{n}.Order.creator');
        return $follower_ids;
    }

    public function process_send_share_msg($openId, $title, $productName, $detailUrl,$sharerName,$remark) {
        send_join_tuan_buy_msg(null,$title,$productName,$sharerName,$remark,$detailUrl,$openId);
    }

    public function get_share_order_for_show($weshareId, $is_me, $division = false){
        $this->Weshare = ClassRegistry::init('Weshare');
        $this->Order = ClassRegistry::init('Order');
        $this->User = ClassRegistry::init('User');
        $this->Cart = ClassRegistry::init('Cart');
        $this->Oauthbind = ClassRegistry::init('Oauthbind');
        $this->WeshareProduct = ClassRegistry::init('WeshareProduct');
        $product_buy_num = array('details' => array());
        $order_cart_map = array();
        $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE);
        if (!$is_me) {
            $order_status[] = ORDER_STATUS_VIRTUAL;
        }
        $sort =  array('created DESC');
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'member_id' => $weshareId,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => $order_status,
                'deleted' => DELETED_NO
            ),
            'fields' => array('id', 'creator', 'created', 'consignee_name', 'consignee_mobilephone', 'consignee_address', 'status', 'total_all_price', 'coupon_total', 'ship_mark', 'ship_code', 'ship_type'),
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
            usort($orders, function ($a, $b) {
                return ($a['id'] < $b['id']) ? -1 : 1;
            });
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
        foreach ($orders as $order_item) {
            $realTotalPrice = $realTotalPrice + $order_item['total_all_price'];
        }
        $summeryTotalPrice = 0;
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
            $summeryTotalPrice += $totalPrice;
            $product_buy_num['details'][$product_id]['total_price'] = $product_buy_num['details'][$product_id]['total_price'] + $totalPrice;
            $order_cart_map[$order_id][] = $item['Cart'];
        }
        $product_buy_num['all_buy_user_count'] = count($users);
        $product_buy_num['all_total_price'] = $summeryTotalPrice;
        $product_buy_num['real_total_price'] = $realTotalPrice;
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        if($division){
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
        return array('users' => $users, 'orders' => $orders, 'order_cart_map' => $order_cart_map, 'summery' => $product_buy_num, 'ship_types' => $shipTypes);
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