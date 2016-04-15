<?php

class MessageApiController extends Controller
{

    public $components = array('OAuth.OAuth', 'Session', 'WeshareBuy', 'ShareFaqUtil', 'WeshareFaq');

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
        //先查询最近数据
        $cond = [
            'conditions' => [
                'receiver' => $uid
            ],
            'group' => ['sender'],
            'fields' => ['MAX(id) as id'],
            'order' => ['id DESC'],
            'limit' => $limit,
            'page' => $page
        ];
        //获取目标ID
        $faq_ids = $this->get_faq_ids($cond);
        $share_faqs = $this->ShareFaq->find('all', ['conditions' => ['id' => $faq_ids], 'order' => ['id DESC']]);
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
        $opt_logs = array_map(function ($item) {
            // 找到, 是不是已经有评论了. 这里属于N+1循环查询, 有性能问题
            // 将来优化
            $item['OptLog']['has_comment'] = $this->check_has_comment($item['OptLog']['obj_id'], $item['OptLog']['user_id']);
            return $item['OptLog'];
        }, $opt_logs);
        return ['user_infos' => $user_infos, 'msg' => $opt_logs];
    }

    /**
     * check_has_comment
     *
     * @param mixed $shareId 分享ID
     * @param mixed $userId  用户ID? 不知道是个啥用户, 凑的.
     * @access public
     * @return bool 评论有回复返回true, 反之false
     */
    public function check_has_comment($shareId, $userId)
    {
        $res = $this->get_comment($shareId, $userId);
        $cid = 0;
        foreach ($res['order_comments'] as $v) {
            if ($v['data_id'] == $shareId && $v['user_id'] == $userId) {
                $cid = $v['id'];
                break;
            }
        }

        if ($cid) {
            return !empty($res['comment_replies'][$cid]);
        } else {
            return false;
        }
    }

    public function get_comment($share_id, $user_id)
    {
        $uid = $this->currentUser['id'];
        //根据评论的时间定位到具体的一条评论，[可能存在问题]
        $query_cond = [
            'type' => COMMENT_SHARE_TYPE,
            'status' => COMMENT_SHOW_STATUS,
            'user_id' => [$user_id, $uid],
            'data_id' => $share_id
        ];
        $comment_date = $_REQUEST['comment_date'];
        if (!empty($comment_date)) {
            $query_cond['date(created)'] = $comment_date;
        }
        $result = $this->WeshareBuy->query_comment($query_cond);

        return $result;
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
        header("Content-Type: application/json");
        echo json_encode($this->get_msg(OPT_LOG_SHARE_COMMENT, $page, $limit));
        exit();
    }

    /**
     * @param user
     * @param page
     * @param limit
     * 获取购买列表
     */
    public function get_buy_list($user, $page, $limit)
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
     * 用户私信列表[单个用户点击进入的列表]
     */
    public function faq_list($user_id, $page, $limit)
    {
        $current_uid = $this->currentUser['id'];
        $this->loadModel('ShareFaq');
        $cond = [
            'conditions' => [
                'receiver' => $current_uid,
                'sender' => $user_id
            ],
            'fields' => ['MAX(id) as id'],
            'group' => ['share_id'],
            'order' => ['id DESC'],
            'page' => $page,
            'limit' => $limit
        ];
        $faq_ids = $this->get_faq_ids($cond);
        $result = $this->ShareFaq->find('all', ['conditions' => ['id' => $faq_ids], 'order' => ['id DESC']]);
        $faqs = [];
        $share_ids = [];
        foreach ($result as $item) {
            $faqs[] = $item['ShareFaq'];
            $share_ids[] = $item['ShareFaq']['share_id'];
        }
        $this->loadModel('Weshare');
        $weshares = $this->Weshare->find('all', [
            'conditions' => ['id' => $share_ids],
            'fields' => ['title', 'id', 'images']
        ]);
        $weshares = array_map('map_share_images', $weshares);
        $weshares = Hash::combine($weshares, '{n}.Weshare.id', '{n}.Weshare');
        echo(json_encode(['messages' => $faqs, 'weshares' => $weshares]));
        exit();
    }

    private function get_faq_ids($cond){
        $this->loadModel('ShareFaq');
        $faq_ids = $this->ShareFaq->find('all',$cond);
        $faq_ids = Hash::extract($faq_ids, '{n}.0.id');
        return $faq_ids;
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
     * [获取分享的评论]
     */
    public function load_single_comment_detail($share_id, $user_id)
    {
        header("Content-Type: application/json");
        $result = $this->get_comment($share_id, $user_id);

        echo json_encode($result);
        exit();
    }


    /**
     * @param $page
     * @param $limit
     * @param $user_id
     * 用户私信列表[针对特定分享的私信列表]
     */
    public function user_faq_msg_list($user_id, $page, $limit)
    {
        $uid = $this->currentUser['id'];
        $this->loadModel('ShareFaq');
        $share_faqs = $this->ShareFaq->find('all', [
            'conditions' => [
                'ShareFaq.sender' => [$uid, $user_id],
                'ShareFaq.receiver' => [$uid, $user_id]
            ],
            'limit' => $limit,
            'page' => $page,
            'order' => ['ShareFaq.id DESC'],
            'fields' => ['Weshare.*', 'ShareFaq.*'],
            'joins' => [
                [
                    'table' => 'weshares',
                    'alias' => 'Weshare',
                    'conditions' => "ShareFaq.share_id = Weshare.id",
                ],
            ],
        ]);

        usort($share_faqs, function ($a, $b) {
            return $a['ShareFaq']['id'] - $b['ShareFaq']['id'];
        });
        $data = [];
	$shares = [];
        foreach ($share_faqs as $key => $a) {
            $tmp = [];
            $share_faqs[$key]['Weshare']['images'] = explode('|', $a['Weshare']['images'])[0];
            $tmp = $share_faqs[$key]['ShareFaq'];
            $shares[$share_faqs[$key]['Weshare']['id']] = $share_faqs[$key]['Weshare'];
            $data[] = $tmp;
        }
        header('Content-type: text/json');
        echo json_encode(['share_faq' => $data, 'weshares' => $shares]);
        exit();
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

    /**
     * 获取评论ID根据
     */
    public function get_comment_by_relate_data($user_id, $share_id)
    {
        $postStr = file_get_contents('php://input');
        $postData = json_decode($postStr, true);
        $date = $postData['date'];
        $content = $postData['content'];
        $this->loadModel('Comment');
        $comment = $this->Comment->find('first', [
            'conditions' => ['body' => $content, 'user_id' => $user_id, 'data_id' => $share_id, 'type' => 'Share', 'date(created)' => $date],
            'fields' => ['id', 'order_id'],
            'order' => ['id DESC']
        ]);
        echo json_encode(['comment_id' => $comment['Comment']['id'], 'order_id' => $comment['Comment']['order_id']]);
        exit();
    }

    /**
     * 提交私信
     *
     * 请求的数据格式:
     *
     * ```json
     * {
     *     "share_id": "", //分享ID
     *      "msg": "", //消息
     *      "sender": "", //发送者
     *      "receiver": "", //接受者
     * }
     * ```
     *
     * @access public
     * @return void
     */
    public function faq_msg()
    {
        $this->loadModel('ShareFaq');
        $postStr = file_get_contents('php://input');
        $postData = json_decode($postStr, true);
        $this->WeshareFaq->create_faq($postData);

        echo json_encode(['success' => true]);
        exit();
    }

}
