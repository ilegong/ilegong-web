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

    private function get_user_info($cond){
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
        return;
    }


    /**
     * 最近评论的信息概要
     */
    public function comment_resume(){
        //query from opt log
        $uid = $this->currentUser['id'];
        $cond = ['obj_creator' => $uid, 'obj_type' => OPT_LOG_SHARE_COMMENT];
        $last_comment_log = $this->get_opt_log($cond);

    }

    /**
     * 私信列表[用户列表] 消息首页显示
     */
    public function faq_msg_list()
    {
        //query from faq
    }

    /**
     * 获取购买列表
     */
    public function get_buy_list()
    {
        $uid = $this->currentUser['id'];

    }

    /**
     * 列表
     */
    public function comment_list()
    {

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
     * 用户私信列表
     */
    public function faq_msg_detail()
    {

    }

}