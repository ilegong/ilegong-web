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

    /**
     * 获取购买列表
     */
    public function get_buy_list()
    {

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
     * 私信列表[用户列表]
     */
    public function faq_msg_list()
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