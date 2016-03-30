<?php

class MessageApiController extends AppController
{

    public $components = array('OAuth.OAuth', 'Session');


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
        echo json_encode(array('senders' => $senders, 'msg' => $share_faqs));
        exit();
    }

    private function get_msg($type, $page, $limit)
    {
        $uid = $this->currentUser['id'];
        $this->loadModel('OptLog');
        $opt_logs = $this->OptLog->find('all', [
            'conditions' => ['obj_creator' => $uid, 'obj_type' => $type],
            'group' => ['user_id'],
            'limit' => $limit,
            'page' => $page,
            'order' => ['id DESC']
        ]);
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
     * @param page
     * @param limit
     * 获取购买列表
     */
    public function get_buy_list($page, $limit)
    {
        echo json_encode($this->get_msg(OPT_LOG_SHARE_BUY, $page, $limit));
        exit();
    }

    /**
     * @param $page
     * @param $limit
     * 列表
     */
    public function comment_list($page, $limit)
    {
        echo json_encode($this->get_msg(OPT_LOG_SHARE_COMMENT, $page, $limit));
        exit();
    }

    /**
     * 提交评论
     */
    public function comment()
    {

    }

    /**
     * 评论的详情
     */
    public function comment_detail()
    {

    }

    /**
     * 提交私信
     */
    public function faq_msg()
    {

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