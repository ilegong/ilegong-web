<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/27/15
 * Time: 10:05
 */
class VoteController extends AppController {


    var $uses = array('VoteEvent', 'Candidate', 'Vote', 'CandidateEvent');

    public function beforeFilter(){
        parent::beforeFilter();
    }

    public function beforeRender(){
        parent::beforeRender();
        $this->set('hideNav',true);
        $this->set('hideFooter',true);
    }


    /**
     * @param $eventId
     * 根据投票的事件ID到特定的投票页面
     */
    public function vote_event_view($eventId) {

    }

    /**
     * @param $candidateId
     * @param $eventId
     * 给特定人和特定投票活动 进行投票
     */
    public function vote($candidateId, $eventId) {

    }

    /**
     * @param $candidateId
     * @param $eventId
     * 特定的人加入到特定的投票事件中 加入成功跳转到 vote_event_view
     */
    public function join_event($candidateId, $eventId) {

    }

    public function sign_up($eventId){
        $this->pageTitle='报名';
        $this->set('event_id',$eventId);
    }

    /**
     * 添加候选人
     * 主要是图片的处理 类似评论的上传图片(使用微信的js上传)
     */
    public function upload_candidate() {

    }

}