<?php

class MessageApiController extends Controller
{

    public $components = array('OAuth.OAuth', 'Session', 'WeshareBuy', 'ShareFaqUtil');

    var $currentUser = null;


    public function beforeFilter()
    {
        $allow_action = [];
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }

    private function get_opt_log($cond)
    {
        $this->loadModel('OptLog');
        $last_buy_log = $this->OptLog->find('first', [
            'conditions' => $cond,
            'fields' => ['id', 'created', 'user_id'],
            'order' => ['id DESC']
        ]);
        return $last_buy_log;
    }

    private function get_user_info($cond)
    {
        $this->loadModel('User');
        $user_info = $this->User->find('first', [
            'conditions' => $cond,
            'fields' => ['id', 'nickname']
        ]);
        return $user_info;
    }

    /**
     * 获取最近购买的信息概要
     */
    public function buy_resume()
    {
        //query from opt log
        $uid = $this->currentUser['id'];
        $cond = ['obj_creator' => $uid, 'obj_type' => OPT_LOG_SHARE_BUY];
        $last_buy_log = $this->get_opt_log($cond);
        $user_id = $last_buy_log['OptLog']['user_id'];
        $u_cond = ['id' => $user_id];
        $user_info = $this->get_user_info($u_cond);
        echo json_encode(array('user_id' => $user_id, 'nickname' => $user_info['User']['nickname'], 'datetime' => $last_buy_log['OptLog']['created']));
        exit();
    }


    /**
     * 最近评论的信息概要
     */
    public function comment_resume()
    {
        //query from opt log
        $uid = $this->currentUser['id'];
        $cond = ['obj_creator' => $uid, 'obj_type' => OPT_LOG_SHARE_COMMENT];
        $last_comment_log = $this->get_opt_log($cond);
        $user_id = $last_comment_log['OptLog']['user_id'];
        $u_cond = ['id' => $user_id];
        $user_info = $this->get_user_info($u_cond);
        echo json_encode(array('user_id' => $user_id, 'nickname' => $user_info['User']['nickname'], 'datetime' => $last_comment_log['OptLog']['created']));
        exit();
    }

    /**
     * @param $page
     * @param $limit
     * 私信列表[用户列表] 消息首页显示
     */
    public function faq_msg_list($page, $limit)
    {
        $uid = $this->currentUser['id'];
        $this->loadModel('ShareFaq');
        $this->loadModel('User');
        $share_faqs = $this->ShareFaq->find('all', [
            'conditions' => [
                'receiver' => $uid
            ],
            'group' => ['sender'],
            'order' => ['id DESC'],
            'limit' => $limit,
            'page' => $page
        ]);
        $sender_ids = Hash::extract($share_faqs, '{n}.ShareFaq.sender');
        $senders = $this->User->find('all', [
            'conditions' => [
                'id' => $sender_ids
            ],
            'fields' => ['id', 'nickname', 'image', 'avatar']
        ]);
        $senders = array_map('map_user_avatar2', $senders);
        $senders = Hash::combine($senders, '{n}.User.id', '{n}.User');
        $result = [];
        foreach ($share_faqs as $faq_item) {
            $r_item = $faq_item['ShareFaq'];
            $r_item['sender'] = $senders[$r_item['sender']];
            $result[] = $r_item;
        }
        echo json_encode($result);
        exit();
    }

    private function get_msg($type, $page, $limit)
    {
        $uid = $this->currentUser['id'];
        $cond = [
            'conditions' => ['obj_creator' => $uid, 'obj_type' => $type],
            'group' => ['user_id'],
            'limit' => $limit,
            'page' => $page,
            'order' => ['id DESC']
        ];
        return $this->query_opt_msg($cond);
    }

    private function get_u_msg_list($type, $page, $limit, $user)
    {
        $uid = $this->currentUser['id'];
        $cond = [
            'conditions' => ['obj_creator' => $uid, 'obj_type' => $type, 'user_id' => $user],
            'limit' => $limit,
            'page' => $page,
            'order' => ['id DESC']
        ];
        return $this->query_opt_msg($cond);
    }

    private function query_opt_msg($cond)
    {
        $this->loadModel('OptLog');
        $opt_logs = $this->OptLog->find('all', $cond);
        $user_ids = Hash::extract($opt_logs, '{n}.OptLog.user_id');
        $this->loadModel('User');
        $user_infos = $this->User->find('all', [
            'conditions' => ['id' => $user_ids],
            'fields' => ['id', 'nickname', 'image', 'avatar'],
        ]);
        $user_infos = array_map('map_user_avatar2', $user_infos);
        $user_infos = Hash::combine($user_infos, '{n}.User.id', '{n}.User');
        return ['user_infos' => $user_infos, 'msg' => $opt_logs];
    }

    /**
     * @param $page
     * @param $limit
     * 获取购买列表
     */
    public function get_u_buy_list($page, $limit)
    {
        echo json_encode($this->get_msg(OPT_LOG_SHARE_BUY, $page, $limit));
        exit();
    }

    /**
     * @param $page
     * @param $limit
     * 获取评论列表
     */
    public function get_u_comment_list($page, $limit)
    {
        echo json_encode($this->get_msg(OPT_LOG_SHARE_COMMENT, $page, $limit));
        exit();
    }

    /**
     * @param user
     * @param page
     * @param limit
     * 获取购买列表
     */
    public function get_buy_list($user,$page, $limit)
    {
        echo json_encode($this->get_u_msg_list(OPT_LOG_SHARE_BUY, $page, $limit, $user));
        exit();
    }

    /**
     * @param user
     * @param $page
     * @param $limit
     * 列表
     */
    public function comment_list($user, $page, $limit)
    {
        echo json_encode($this->get_u_msg_list(OPT_LOG_SHARE_COMMENT, $page, $limit, $user));
        exit();
    }

    /**
     * @param $user_id
     * @param $page
     * @param $limit
     * 用户私信列表
     */
    public function faq_list($user_id, $page, $limit)
    {
        $current_uid = $this->currentUser['id'];
        $this->loadModel('ShareFaq');
        $result = $this->ShareFaq->find('all', [
            'conditions' => [
                'receiver' => $current_uid,
                'sender' => $user_id
            ],
            'group' => ['share_id'],
            'order' => ['id DESC'],
            'page' => $page,
            'limit' => $limit
        ]);
        $faqs = [];
        $share_ids = [];
        foreach ($result as $item) {
            $faqs[] = $item['ShareFaq'];
            $share_ids[] = $item['ShareFaq']['share_id'];
        }
        $this->loadModel('Weshare');
        $weshares = $this->Weshare->find('all', [
            'conditions' => ['id' => $share_ids],
            'fields' => ['title', 'id']
        ]);
        $weshares = Hash::combine($weshares, '{n}.Weshare.id', '{n}.Weshare');
        echo(json_encode(['messages' => $faqs, 'weshares' => $weshares]));
        exit();
    }

    /**
     * 提交评论
     */
    public function comment()
    {
        $postStr = file_get_contents('php://input');
        $post_data = json_decode($postStr, true);
        $result = $this->WeshareBuy->create_share_comment($post_data['order_id'], $post_data['comment_content'], $post_data['reply_comment_id'], $post_data['comment_uid'], $post_data['share_id']);
        echo json_encode($result);
        exit();
    }

    private function get_shares($share_ids)
    {
        $this->loadModel('Weshare');
        $shares = $this->Weshare->find('all', ['conditions' => ['id' => $share_ids], 'fields' => ['id', 'title']]);
        return Hash::combine($shares, '{n}.Weshare.id', '{n}.Weshare');
    }

    /**
     * @param $sharer_id
     * @param $user_id
     * 评论的详情
     */
    public function comment_detail($sharer_id, $user_id)
    {
        $this->loadModel('OptLog');
        $opt_logs = $this->OptLog->find('all', ['conditions' => ['user_id' => $user_id, 'obj_creator' => $sharer_id, 'obj_type' => OPT_LOG_SHARE_COMMENT], 'order' => ['id DESC'], 'limit' => 50]);
        $result = [];
        $result['comments'] = [];
        $obj_ids = [];
        foreach ($opt_logs as $log_item) {
            $obj_ids[] = $log_item['OptLog']['obj_id'];
            $result['comments'][] = $log_item['OptLog'];
        }
        $result['weshares'] = $this->get_shares($obj_ids);
        echo json_encode($result);
        exit();
    }

    /**
     * @param $share_id
     * @param $user_id
     * 获取单条分享的详细数据
     */
    public function load_single_comment_detail($share_id, $user_id)
    {
        //根据评论的时间定位到具体的一条评论，[可能存在问题]
        $comment_date = $_REQUEST['comment_date'];
        $query_cond = ['type' => COMMENT_SHARE_TYPE, 'status' => COMMENT_SHOW_STATUS, 'user_id' => $user_id, 'data_id' => $share_id, 'date(created)' => $comment_date];
        $result = $this->WeshareBuy->query_comment($query_cond);
        echo json_encode($result);
        exit();
    }

    /**
     * 提交私信
     */
    public function faq_msg()
    {
        $this->loadModel('ShareFaq');
        $postStr = file_get_contents('php://input');
        $post_data = json_decode($postStr, true);
        $this->ShareFaq->save($post_data);
        $share_id = $post_data['share_id'];
        $msg = $post_data['msg'];
        $sender = $post_data['sender'];
        $receiver = $post_data['receiver'];
        $weshareInfo = $this->WeshareBuy->get_weshare_info($share_id);
        $share_title = $weshareInfo['title'];
        $this->ShareFaqUtil->send_notify_template_msg($sender, $receiver, $msg, $share_id, $share_title);
        echo json_encode(['success' => true]);
        exit();
    }

    /**
     * @param $page
     * @param $limit
     * @param $share_id
     * @param $user_id
     * 用户私信列表
     */
    public function user_faq_msg_list($share_id, $user_id, $page, $limit)
    {
        $uid = $this->currentUser['id'];
        $this->loadModel('ShareFaq');
        $share_faqs = $this->ShareFaq->find('all', [
            'conditions' => ['share_id' => $share_id, 'sender' => [$uid, $user_id], 'receiver' => [$uid, $user_id]],
            'limit' => $limit,
            'page' => $page,
            'order' => ['id DESC']
        ]);
        echo json_encode($share_faqs);
    }

    /**
     * @param $uids
     * 获取用户信息
     */
    public function get_users($uids)
    {
        $uids = explode(',', $uids);
        $this->loadModel('User');
        $users = $this->User->find('all', [
            'conditions' => ['id' => $uids]
        ]);
        $users = array_map('map_user_avatar2', $users);
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        echo json_encode($users);
        exit();
    }

}