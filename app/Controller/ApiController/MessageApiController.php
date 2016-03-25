<?php

class MessageApiController extends AppController
{

    public $components = array('OAuth.OAuth', 'Session');

    public $uses = array('OptLog');

    public function beforeFilter()
    {
        $allow_action = [];
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }

    /**
     * 获取最近购买的信息概要
     */
    public function buy_resume()
    {
        //query from opt log
        $uid = $this->currentUser['id'];
        $last_buy_log = $this->OptLog->find('first', [
            'conditions' => [
                'obj_creator' => $uid,
                'obj_type' => OPT_LOG_SHARE_BUY
            ],
            'order' => ['id DESC']
        ]);
        
    }

    /**
     * 最近评论的信息概要
     */
    public function comment_resume(){
        //query from opt log
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