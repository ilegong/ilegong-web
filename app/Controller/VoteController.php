<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/27/15
 * Time: 10:05
 */
class VoteController extends AppController {


    var $uses = array('VoteEvent', 'Candidate', 'Vote', 'CandidateEvent');
    var $components = array('Paginator');
    var $paginate = array(
        'Candidate' => array(
            'order' => 'Candidate.created DESC',
            'limit' => 1,
        )
    );

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
        $this->pageTitle = '萌宝';
        $event_info = $this->VoteEvent->find('first',array(
            'conditions' => array(
                'id'=>$eventId
            ),
            'fields' => array(
                'start_time','end_time'
            )
        ));
        $candidators = $this->CandidateEvent->find('all',array(
            'conditions' => array(
                'event_id' => $eventId
            )
        ));
        $candidator_ids = Hash::extract($candidators,'{n}.CandidateEvent.candidate_id');
        $this->Paginator->settings = $this->paginate;
        $candidators_info = $this->Paginator->paginate('Candidate',array('Candidate.id' => $candidator_ids));

        if(!empty($candidators_info)){
            foreach($candidators_info as &$candidator){
                $conditions = array();
                $conditions['candidate_id'] = $candidator['Candidate']['id'];
                $conditions['Vote.created >= '] = $event_info['VoteEvent']['start_time'];
                $conditions['Vote.created <= '] = $event_info['VoteEvent']['end_time'];
                $candidator_vote= $this->Vote->find('count',array('conditions' => $conditions));
                $candidator['vote_num'] = $candidator_vote;
                list($uvote,$is_vote) = $this->is_already_vote($candidator['Candidate']['id'],$eventId);
                unset($uvote);
                $candidator['is_vote'] = $is_vote;
            }
        }
        $this->set('candidators',$candidators);
        $this->set('candidators_info',$candidators_info);
        $this->set('event_id',$eventId);
    }

    /**
     * @param $candidateId
     * @param $eventId
     * 给特定人和特定投票活动 进行投票
     */
    public function vote($candidateId, $eventId) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if(empty($uid)){
            echo json_encode(array('success' => false, 'reason' => 'Not logged'));
            return;
        }
        if(user_subscribed_pys($uid) != WX_STATUS_SUBSCRIBED ){
            echo json_encode(array('success' => false, 'reason' => 'Not subscribed'));
            return;
        }
        $uvote = $this->today_vote_count($eventId,$uid);
        $already_vote_candidate = Hash::extract($uvote, '{n}.Vote.candidate_id');
        if(count($uvote)>= 5){
            echo json_encode(array('success' => false, 'reason' => 'more than five'));
            return;
        }
        //has vote for this baby
        if(in_array($candidateId,$already_vote_candidate)){
            echo json_encode(array('success' => false, 'reason' => 'already vote'));
            return;
        }
        $vote = $this->Vote->save(array('candidate_id' => $candidateId, 'user_id'=>$uid, 'event_id'=>$eventId));
        if(empty($vote)) {
            echo json_encode(array('success' => false, 'reason' => 'save wrong'));
            return;
        }
        echo json_encode(array('success' => true));
    }

    /**
     * @param $candidateId
     * @param $eventId
     * 暂时不用
     * 特定的人加入到特定的投票事件中 加入成功跳转到 vote_event_view
     */
    public function join_event($candidateId, $eventId) {

    }

    public function sign_up($eventId){
        //check login
        $this->pageTitle='报名';
        $this->set('event_id',$eventId);
        $uid = $this->currentUser['id'];
        if(empty($uid)){
            $this->set('not_login',true);
        }
        if(!$this->is_weixin()||user_subscribed_pys($uid) != WX_STATUS_SUBSCRIBED){
            $this->set('not_weixin',true);
        }
    }

    /**
     * 添加候选人
     * 主要是图片的处理 类似评论的上传图片(使用微信的js上传)
     */
    public function upload_candidate($eventId) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not login'));
            return;
        }
        if(user_subscribed_pys($uid) != WX_STATUS_SUBSCRIBED){
            echo json_encode(array('success' => false, 'reason' => 'not subscribed'));
            return;
        }
        $title = $_POST['title'];
        $mobileNum = $_POST['mobileNum'];
        $description = $_POST['description'];
        $images = $_POST['images'];
        $saveData = array(
            'mobile_num' => $mobileNum,
            'description' => $description,
            'images' => $images,
            'title' => $title,
            'created' => date('Y-m-d H:i:s'),
            'user_id' => $uid
        );
        if ($this->Candidate->save($saveData)) {
            $candidate_id = $this->Candidate->id;
            $eventCandidateData = array('event_id' => $eventId, 'candidate_id' => $candidate_id);
            $this->CandidateEvent->save($eventCandidateData);
            echo json_encode(array('success' => true));
            return;
        }
        echo json_encode(array('success' => false, 'reason' => 'server error'));
        return;
    }

    }

    /**
     * 萌宝详情
     */
    public function candidate_detail($candidateId,$eventId) {

       $vote_num = $_GET['vote_num']? $_GET['vote_num']:0;
       $is_vote = $_GET['is_vote'];
       $this->pageTitle = '萌宝详情';
       $candidate_info = $this->Candidate->find('first',array(
          'conditions' => array(
              'id' => $candidateId
          )
       ));
       $images = explode('|',$candidate_info['Candidate']['images']);
       $this->set('vote_num',$vote_num);
       $this->set('candidate_id',$candidateId);
       $this->set('event_id',$eventId);
       $this->set('images',$images);
       $this->set('candidate_info',$candidate_info);
       $this->set('is_vote',$is_vote);


    }

    public function is_already_vote($candidateId,$eventId){

        $uid = $this->currentUser['id'];
        $uvote = $this->Vote->find('all', array(
            'conditions' => array(
                'user_id' => $uid,
                'event_id' => $eventId,
                'created >'=> date('Y-m-d', time()),
                'created <'=> date('Y-m-d', strtotime('+1 day')),
            )
        ));
        $already_vote_candidate = Hash::extract($uvote, '{n}.Vote.candidate_id');
        $is_vote = in_array($candidateId,$already_vote_candidate);
        return array($uvote,$is_vote);
    private function today_vote_count($eventId,$userId){
        $votes = $this->Vote->find('all', array(
            'conditions' => array(
                'user_id' => $userId,
                'event_id' => $eventId,
                'created >'=> date('Y-m-d', time()),
                'created <'=> date('Y-m-d', strtotime('+1 day')),
            )
        ));
        return $votes;
    }

}