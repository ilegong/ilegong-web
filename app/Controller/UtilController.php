<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 8/23/15
 * Time: 20:16
 */
class UtilController extends AppController {

    public $name = 'util';

    public $uses = array('UserRelation', 'Order', 'Cart', 'User', 'Oauthbind', 'Weshare', 'CandidateEvent');

    public $components = array('ShareUtil', 'Weixin');

    /**
     * @param $user_id
     * @return array
     * 获取用户粉丝
     */
    public function load_user_fans($user_id) {
        $this->autoRender = false;
        $shares = $this->Weshare->find('all', array(
            'conditions' => array(
                'creator' => $user_id
            ),
            'limit' => 100
        ));
        $share_ids = Hash::extract($shares, '{n}.Weshare.id');

        $orders_group_by_user = $this->Order->find('all', array(
            'conditions' => array(
                'member_id' => $share_ids,
                'ship_mark' => 'self_ziti'
            ),
            'limit' => 1000,
            'group' => array('Order.creator')
        ));

        $join_users = $this->CandidateEvent->find('all', array(
            'conditions' => array(
                'event_id' => 6,
            ),
            'limit' => 500
        ));
        $order_user_ids = Hash::extract($orders_group_by_user, '{n}.Order.creator');
        $join_user_ids = Hash::extract($join_users, '{n}.CandidateEvent.user_id');
        $diff_user_ids = array_diff($order_user_ids, $join_user_ids);
        //echo json_encode(array('count' => count($diff_user_ids), 'user_ids' => $diff_user_ids));
        $this->set('user_ids', $diff_user_ids);
        return $diff_user_ids;
    }

    /**
     * @param $openId
     * @param $title
     * @param $productName
     * @param $detailUrl
     * @param $sharerName
     * @param $remark
     * 处理发起分享通知
     */
    public function process_send_share_msg($openId, $title, $productName, $detailUrl, $sharerName, $remark) {
        send_join_tuan_buy_msg(null, $title, $productName, $sharerName, $remark, $detailUrl, $openId);
    }

    /**
     * @param $user_id
     * 发送投票通知
     */
    public function send_notify_vote($user_id) {
        $this->autoRender = false;
        $title = '关注的小宝妈发起了';
        $remark = ' 晒萌宝吃海鲜啦，一等奖三文鱼、二等奖北极甜虾、三等奖黄花鱼、还有88元的礼券包，感谢亲们对小宝妈的支持，有你们在一起真好！点击详情，赶快来参加！';
        $followers = $this->load_user_fans($user_id);
        $followers[] = '633345';
        $followers[] = '544307';
        $openIds = $this->Oauthbind->findWxServiceBindsByUids($followers);
        foreach ($openIds as $openId) {
            $this->process_send_share_msg($openId, $title, '晒萌宝小宝妈请吃海鲜啦！', 'www.tongshijia.com/vote/vote_event_view/6', '小宝妈', $remark);
        }
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * 获取微信的token
     */
    public function get_base_token() {
        $this->autoRender = false;
        try {
            if ($this->is_admin($this->currentUser['id'])) {
                $this->loadModel('WxOauth');
                $o = $this->WxOauth->get_base_access_token();
                $log = "get_base_access_token:" . $o . ", in json:" . json_encode($o);
                $this->log($log);
                echo $log;
            }
        } catch (Exception $e) {
            echo "exception: $e";
        }
    }

    /**
     * @param $shareId
     * @param $user_id
     * 根据分享迁移数据
     */
    public function transferFansByShareId($shareId, $user_id) {
        $this->autoRender = false;
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'member_id' => $shareId,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_DONE, ORDER_STATUS_SHIPPED, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RECEIVED),
                'type' => ORDER_TYPE_WESHARE_BUY
            ),
            'group' => array('Order.creator'),
            'limit' => 1000
        ));
        $save_data = array();
        $temp_data = array();
        foreach ($orders as $order) {
            $order_creator = $order['Order']['creator'];
            if ($this->ShareUtil->check_user_relation($user_id, $order_creator)) {
                if (!in_array($order_creator, $temp_data)) {
                    $temp_data[] = $order_creator;
                    $save_data[] = array('user_id' => $user_id, 'follow_id' => $order_creator, 'type' => 'Transfer', 'created' => date('Y-m-d H:i:s'));
                }
            }
        }
        $this->UserRelation->saveAll($save_data);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $product_id
     * @param $user_id
     * @param $offset
     * 迁移粉丝数据
     */
    public function transferFansData($product_id, $user_id, $offset = 0) {
        $this->autoRender = false;
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'product_id' => $product_id,
                'not' => array('order_id' => null, 'order_id' => 0, 'type' => ORDER_TYPE_WESHARE_BUY, 'creator' => 0, 'creator' => null),
            ),
            'group' => array('creator'),
            'limit' => 500,
            'offset' => $offset,
            'order' => array('created DESC')
        ));
        $save_data = array();
        $temp_data = array();
        foreach ($carts as $cart_item) {
            $cart_creator = $cart_item['Cart']['creator'];
            if ($this->ShareUtil->check_user_relation($user_id, $cart_creator)) {
                if (!in_array($cart_creator, $temp_data)) {
                    $temp_data[] = $cart_creator;
                    $save_data[] = array('user_id' => $user_id, 'follow_id' => $cart_creator, 'type' => 'Transfer', 'created' => date('Y-m-d H:i:s'));
                }
            }
        }
        $this->UserRelation->saveAll($save_data);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $user_id
     * @param $page_num
     * @param $page_size
     * 从微信里面更新粉丝信息
     */
    public function updateFansProfile($user_id, $page_num, $page_size) {
        $this->autoRender = false;
        $tasks = array();
        foreach (range(0, $page_num) as $index) {
            $offset = $index * $page_size;
            $url = '/util/processUpdateFansProfile/' . $user_id . '/' . $page_size . '/' . $offset;
            $tasks[] = array('url' => $url);
        }
        $result = $this->addTaskQueue($tasks);
        echo json_encode(array('result' => $result, 'tasks' => $tasks));
        return;
    }

    /**
     * @param $user_id
     * @param $limit
     * @param $offset
     * task queue
     */
    public function processUpdateFansProfile($user_id, $limit, $offset) {
        $this->autoRender = false;
        $user_relations = $this->UserRelation->find('all', array(
            'conditions' => array(
                'user_id' => $user_id,
                'type' => 'Transfer'
            ),
            'limit' => $limit,
            'offset' => $offset,
            'order' => array('created DESC')
        ));
        $follow_ids = Hash::extract($user_relations, '{n}.UserRelation.follow_id');
        $oauthBinds = $this->Oauthbind->find('all', array(
            'conditions' => array(
                'user_id' => $follow_ids
            ),
            'limit' => $limit
        ));
        if (!empty($oauthBinds)) {
            foreach ($oauthBinds as $item) {
                $follow_user_id = $item['Oauthbind']['user_id'];
                $open_id = $item['Oauthbind']['oauth_openid'];
                $wx_user = get_user_info_from_wx($open_id);
                $this->log('download wx user info ' . json_encode($wx_user));
                $photo = $wx_user['headimgurl'];
                $nickname = $wx_user['nickname'];
                if (empty($nickname)) {
                    //user not have wexin info
                    $this->UserRelation->updateAll(array('deleted' => 1), array('user_id' => $user_id, 'follow_id' => $follow_user_id, 'type' => 'Transfer'));
                    continue;
                }
                //when user many oauth bind
                $this->UserRelation->updateAll(array('deleted' => 0), array('user_id' => $user_id, 'follow_id' => $follow_user_id, 'type' => 'Transfer'));
                //default header
                $download_url = 'http://51daifan.sinaapp.com/img/default_user_icon.jpg';
                if (!empty($photo)) {
                    $this->log('download wx user photo ' . $photo);
                    $download_url = download_photo_from_wx($photo);
                }
                $this->User->updateAll(array('nickname' => "'" . $nickname . "'", 'image' => "'" . $download_url . "'"), array('id' => $follow_user_id));
            }
        }
        //update status
        $this->UserRelation->updateAll(array('is_update' => 1), array('user_id' => $user_id, 'follow_id' => $follow_ids, 'type' => 'Transfer'));
        echo json_encode(array('success' => true, 'oauth-bind' => $oauthBinds, 'follow-id' => $follow_ids));
        return;
    }

    /**
     * @param $tasks
     * @return array
     * 添加队列任务
     */
    private function addTaskQueue($tasks) {
        $queue = new SaeTaskQueue('cqueue');
        $queue->addTask($tasks);
        //将任务推入队列
        $ret = $queue->push();
        //任务添加失败时输出错误码和错误信息
        if ($ret === false) {
            return array('success' => false, 'errno' => $queue->errno(), 'errmsg' => $queue->errmsg());
        }
        return array('success' => true, 'errno' => $queue->errno(), 'errmsg' => $queue->errmsg());
    }

    /**
     * @param $shareId
     * 手动的开启常用自提点子分享
     */
    public function start_static_shares($shareId) {
        $this->autoRender = false;
        $this->ShareUtil->new_static_address_group_shares($shareId);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $shareOfferId
     * @param $uid
     * @param $totalNum
     * 手工给分享者发一些红包
     */
    public function gen_share_offer_for_sharer($shareOfferId, $uid, $totalNum) {
        $this->autoRender = false;
        $shareOfferM = ClassRegistry::init('ShareOffer');
        $genShareOfferResult = $shareOfferM->add_shared_slices($uid, $shareOfferId, $totalNum, true);
        echo json_encode($genShareOfferResult);
        return;
    }
}