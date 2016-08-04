<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/27/15
 * Time: 10:05
 */
App::uses('CakeEvent', 'Event');

class VoteController extends AppController
{

    var $eventId = 0;
    var $uses = array('VoteEvent', 'Candidate', 'Vote', 'CandidateEvent', 'UserSubReason', 'User', 'VoteSetting');
    var $components = array('Paginator', 'WeshareBuy');
    var $paginate = array(
        'Candidate' => array(
            'order' => 'Candidate.created DESC',
            'limit' => 10,
        )
    );

    var $sortPaginate = array(
        'Candidate' => array(
            'order' => 'Candidate.vote_num DESC',
            'limit' => 10,
        )
    );

    public function beforeFilter()
    {
        parent::beforeFilter();
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->set('hideNav', true);
        $this->set('hideFooter', true);
        $currentVoteConfig = $this->VoteSetting->getVoteConfig($this->eventId);
        $this->set('voteConfig', $currentVoteConfig);
    }

    /**
     * @param $eventId
     * @param $sort
     * 可以排序
     * 根据投票的事件ID到特定的投票页面
     */
    public function vote_event_view($eventId, $sort = 0)
    {
        $this->layout = 'vote';
        $this->eventId = $eventId;
        $this->pageTitle = $this->VoteSetting->getVoteTitle($eventId);
        $uid = $this->currentUser['id'];
        $event_info = $this->get_event_info($eventId);
        $candidators = $this->CandidateEvent->find('all', array(
            'conditions' => array(
                'event_id' => $eventId
            )
        ));

        $candidator_ids = Hash::extract($candidators, '{n}.CandidateEvent.candidate_id');
        $this->set('total_count', count($candidator_ids));
        if ($sort == 1) {
            $this->Paginator->settings = $this->sortPaginate;
            $this->set('op_cate', 'sort');
        } else {
            $this->Paginator->settings = $this->paginate;
            $this->set('op_cate', 'index');
        }
        $candidators_info = $this->Paginator->paginate('Candidate', array('Candidate.id' => $candidator_ids, 'Candidate.deleted' => DELETED_NO));
        $candidators_info_user_ids = Hash::extract($candidators_info, '{n}.Candidate.user_id');
        $candidators_users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $candidators_info_user_ids
            ),
            'limit' => 200,
            'fields' => array('User.id', 'User.nickname', 'User.image', 'User.avatar')
        ));
        $candidators_users = Hash::combine($candidators_users, '{n}.User.id', '{n}.User');
        if (!empty($candidators_info)) {
            foreach ($candidators_info as &$candidator) {
                list($uvote, $is_vote) = $this->is_already_vote($candidator['Candidate']['id'], $eventId, $uid);
                unset($uvote);
                $candidator['is_vote'] = $is_vote;
            }
        }
        $candidate_event = $this->has_sign_up($eventId, $uid);
        $this->set('is_sign_up', !empty($candidate_event));
        $this->set('candidator_ids', $candidator_ids);
        $this->set('event_info', $event_info);
        $this->set('candidators', $candidators);
        $this->set('candidators_info', $candidators_info);
        $this->set('event_id', $eventId);
        $this->set('candidators_users', $candidators_users);
        $this->set('event_available', $this->check_event_is_available($event_info));
        $this->set_wx_data($uid, $eventId);

        if (!empty($candidate_event)) {
            $candidate_info = $this->Candidate->find('first', array(
                'conditions' => array(
                    'id' => $candidate_event['CandidateEvent']['candidate_id'],
                )
            ));
        }
        if (!empty($candidate_info)) {
            $this->set('weixin_share_title', '我是第' . $candidate_info['Candidate']['id'] . '号' . $candidate_info['Candidate']['title'] . '，叔叔阿姨快来支持我一票啦');
            $this->set('weixin_share_desc', '我正在参加亲子联盟@新西兰之旅分享会，快来帮我点赞助力吧！！！');
        }
        else {
            $this->set('weixin_share_title', '我正在参加亲子联盟@新西兰之旅分享会，叔叔阿姨快来支持我一票啦');
            $this->set('weixin_share_desc', '快来帮我点赞助力吧！！！');
        }
        $currentVoteConfig = $this->VoteSetting->getVoteConfig($this->eventId);
        $this->set('weixin_share_image', $currentVoteConfig['common_params']['banner']);
    }

    private function save_sub_reason($candidateId, $eventId, $uid)
    {
        $this->eventId = $eventId;
        $candidate_info = $this->Candidate->find('first', array(
            'conditions' => array(
                'id' => $candidateId
            )
        ));
        $title = '我是第' . $candidateId . '号' . $candidate_info['Candidate']['title'];
        $type = $this->VoteSetting->getSubVoteType($eventId);
        $this->UserSubReason->save(array('type' => $type, 'url' => WX_HOST . '/vote/candidate_detail/' . $candidateId . '/' . $eventId, 'user_id' => $uid, 'title' => $title, 'data_id' => $eventId, 'created' => date('Y-m-d H:i:s')));
    }

    public function to_sub($candidateId, $eventId)
    {
        $uid = $this->currentUser['id'];
        $this->save_sub_reason($candidateId, $eventId, $uid);
        $url = $this->VoteSetting->getSubVoteUrl($eventId);
        $this->redirect($url);
    }

    /**
     * @param $candidateId
     * @param $eventId
     * 给特定人和特定投票活动 进行投票
     */
    public function vote($candidateId, $eventId)
    {
        $this->autoRender = false;
        $this->eventId = $eventId;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'Not logged'));
            return;
        }
        // vote user don't sub pys
        if (user_subscribed_pys($uid) != WX_STATUS_SUBSCRIBED) {
            $this->save_sub_reason($candidateId, $eventId, $uid);
            echo json_encode(array('success' => false, 'reason' => 'Not subscribed'));
            return;
        }
        list($uvote, $is_vote) = $this->is_already_vote($candidateId, $eventId, $uid);
        if (count($uvote) >= 5) {
            echo json_encode(array('success' => false, 'reason' => 'more than five'));
            return;
        }
        //has vote for this baby
        if ($is_vote) {
            echo json_encode(array('success' => false, 'reason' => 'already vote'));
            return;
        }
        $vote = $this->Vote->save(array('candidate_id' => $candidateId, 'user_id' => $uid, 'event_id' => $eventId));
        if (empty($vote)) {
            echo json_encode(array('success' => false, 'reason' => 'save wrong'));
            return;
        }
        //update vote num
        $this->update_candidate_vote_num($candidateId);
        //$this->save_user_relation($uid, $eventId);
        echo json_encode(array('success' => true));
    }

    /**
     * @param $candidateId
     * @param $eventId
     * 暂时不用
     * 特定的人加入到特定的投票事件中 加入成功跳转到 vote_event_view
     */
    public function join_event($candidateId, $eventId)
    {
        $this->autoRender = false;
        $this->eventId = $eventId;
        $uid = $this->currentUser['id'];
        $CandidateUploadEvent = new CakeEvent('Vote.Candidate.created', $this->Candidate, array(
            'id' => $candidateId,
            'candidateData' => array('eventId' => $eventId, 'userId' => $uid)
        ));
        $this->getEventManager()->dispatch($CandidateUploadEvent);
        $this->log('upload candidate event ' . json_encode($CandidateUploadEvent));
        echo json_encode(array('success' => true));
        return;
    }

    public function sign_up($eventId)
    {
        $this->layout = 'vote';
        $this->eventId = $eventId;
        $this->pageTitle = $this->VoteSetting->getVoteTitle($eventId);
        //check login
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $ref = Router::url($_SERVER['REQUEST_URI']);
            $this->redirect('/users/login.html?force_login=1&auto_weixin=' . $this->is_weixin() . '&referer=' . urlencode($ref));
            return;
        }
//        $sign_up_info = $this->has_sign_up($eventId, $uid);
//        if (!empty($sign_up_info)) {
//            $this->redirect('/vote/candidate_detail/' . $sign_up_info['CandidateEvent']['candidate_id'] . '/' . $eventId);
//            return;
//        }
        $event_info = $this->get_event_info($eventId);
        $this->set('event_info', $event_info);
        $this->set('event_id', $eventId);
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $this->set('not_login', true);
        }
        if (!$this->is_weixin() || user_subscribed_pys($uid) != WX_STATUS_SUBSCRIBED) {
//            $this->set('not_weixin', true);
        }
        $this->set_wx_data($uid, $eventId);
        $this->set('op_cate', 'sign_up');
        $this->pageDesc = $this->VoteSetting->getVoteTitle($eventId);
        $this->set('weixin_share_title', '我正在参加亲子联盟@新西兰之旅分享会，你也来参加吧！');
        $this->set('weixin_share_desc', '这里有好看的好玩的哦！');
        $currentVoteConfig = $this->VoteSetting->getVoteConfig($this->eventId);
        $this->set('weixin_share_image', $currentVoteConfig['common_params']['banner']);
    }

    /**
     * 添加候选人
     * 主要是图片的处理 类似评论的上传图片(使用微信的js上传)
     */
    public function upload_candidate($eventId)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not login'));
            return;
        }
        if (user_subscribed_pys($uid) != WX_STATUS_SUBSCRIBED) {
            echo json_encode(array('success' => false, 'reason' => 'not subscribed'));
            return;
        }
        $signUpRecord = $this->has_sign_up($eventId, $uid);
        if (!empty($signUpRecord)) {
            echo json_encode(array('success' => false, 'reason' => 'has sign', 'candidate_id' => $signUpRecord['CandidateEvent']['candidate_id']));
            return;
        }

        $result = $this->get_post_raw_data();
        $title = $result['title'];
        $mobileNum = $result['mobileNum'];
        $description = $result['description'];
        $images = $result['images'];
        $saveData = array(
            'mobile_num' => $mobileNum,
            'description' => $description,
            'images' => $images,
            'title' => $title,
            'created' => date('Y-m-d H:i:s'),
            'user_id' => $uid,
            'event_id' => $eventId
        );
        if ($this->Candidate->save($saveData)) {
            $candidate_id = $this->Candidate->id;
            $eventCandidateData = array('event_id' => $eventId, 'candidate_id' => $candidate_id, 'user_id' => $uid);
            $this->CandidateEvent->save($eventCandidateData);
            //$this->save_user_relation($uid,$eventId);
            echo json_encode(array('success' => true));
            return;
        }
        echo json_encode(array('success' => false, 'reason' => 'server error'));
        return;
    }

    /**
     * 萌宝详情
     */
    public function candidate_detail($candidateId, $eventId)
    {
        $this->layout = 'vote';
        $this->eventId = $eventId;
        $this->pageTitle = $this->VoteSetting->getVoteTitle($eventId);
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $ref = Router::url($_SERVER['REQUEST_URI']);
            $this->redirect('/users/login.html?force_login=1&auto_weixin=' . $this->is_weixin() . '&referer=' . urlencode($ref));
            return;
        }
        if (user_subscribed_pys($uid) != WX_STATUS_SUBSCRIBED) {
            $this->set('not_sub', true);
        }
        $candidate_data = $this->set_candidate_data($candidateId, $eventId, $uid);
        $this->set($candidate_data);
        $candidate_info = $this->Candidate->find('first', array(
            'conditions' => array(
                'id' => $candidateId
            )
        ));
        $candidate_info_user_id = $candidate_info['Candidate']['user_id'];
        $candidate_info_user = $this->User->find('first', array(
            'conditions' => array(
                'id' => $candidate_info_user_id
            ),
            'fields' => array('User.id', 'User.nickname', 'User.image', 'User.avatar')
        ));
        $this->set('candidate_info_user', $candidate_info_user);
        $rank_data = $this->get_candidate_rank($candidateId, $eventId);
        $images = array_filter(explode('|', $candidate_info['Candidate']['images']));
        $is_sign_up = $this->has_sign_up($eventId, $uid);
        $this->set('is_sign_up', $is_sign_up);
        $this->set('rank', $rank_data[0][0]['Rank']);
        $event_info = $this->get_event_info($eventId);
        $this->set('event_info', $event_info);
        $this->set('candidate_id', $candidateId);
        $this->set('event_id', $eventId);
        $this->set('images', $images);
        $this->set('candidate_info', $candidate_info);
        $this->set('share_baby_info', true);
        $this->set('weixin_share_title', '我是第' . $candidateId . '号' . $candidate_info['Candidate']['title'] . '，叔叔阿姨快来支持我一票啦');
        $this->set('weixin_share_desc', '我正在参加亲子联盟@新西兰之旅分享会，快来帮我点赞助力吧！！！');
        if (count($images) > 0) {
            $this->set('weixin_share_image', $images[0]);
        }
        $this->set_wx_data($this->currentUser['id'], $eventId);
        $this->pageTitle = '我是' . $candidateId . '号,' . $candidate_info['Candidate']['title'];
        $this->set('event_available', $this->check_event_is_available($event_info));
        $this->set('op_cate', 'detail');
    }

    private function has_sign_up($eventId, $userId)
    {
        $record = $this->CandidateEvent->find('first', array(
            'conditions' => array(
                'user_id' => $userId,
                'event_id' => $eventId
            ),
            'order' => array('id DESC')
        ));
        return $record;
    }

    private function is_already_vote($candidateId, $eventId, $uid)
    {

        $uvote = $this->Vote->find('all', array(
            'conditions' => array(
                'user_id' => $uid,
                'event_id' => $eventId,
                'created >' => date('Y-m-d', time()),
                'created <' => date('Y-m-d', strtotime('+1 day')),
            )
        ));
        $already_vote_candidate = Hash::extract($uvote, '{n}.Vote.candidate_id');
        $is_vote = in_array($candidateId, $already_vote_candidate);
        return array($uvote, $is_vote);
    }

    private function get_event_info($eventId)
    {

        $event_info = $this->VoteEvent->find('first', array(
            'conditions' => array(
                'id' => $eventId
            ),
            'fields' => array(
                'start_time', 'end_time', 'deleted', 'published', 'place'
            )
        ));
        return $event_info;
    }

    private function set_wx_data($uid, $eventId)
    {
        $weixinJs = prepare_wx_share_log($uid, 'voteEventId', $eventId);
        $this->set($weixinJs);
    }

    private function set_candidate_data($candaidateId, $eventId, $uid)
    {
        $allCount = $this->Vote->find('count', array(
            'conditions' => array(
                'event_id' => $eventId,
                'candidate_id' => $candaidateId
            )
        ));
        $hasVote = $this->Vote->find('count', array(
            'conditions' => array(
                'user_id' => $uid,
                'event_id' => $eventId,
                'candidate_id' => $candaidateId,
                'created >' => date('Y-m-d', time()),
                'created <' => date('Y-m-d', strtotime('+1 day'))
            )
        ));

        return array('all_count' => $allCount, 'has_vote' => $hasVote);
    }

    private function update_candidate_vote_num($candidateId)
    {
        $this->Candidate->updateAll(array('vote_num' => 'vote_num + 1'), array('id' => $candidateId));
    }

    private function check_event_is_available($eventInfo)
    {
        if (empty($eventInfo)) {
            return false;
        }
        if ($eventInfo['VoteEvent']['deleted'] == 1 || $eventInfo['VoteEvent']['published'] == 0) {
            return false;
        }
        if (time() < strtotime($eventInfo['VoteEvent']['start_time']) || time() > strtotime($eventInfo['VoteEvent']['end_time'])) {
            return false;
        }
        return true;
    }

    private function save_user_relation($uid, $eventId)
    {
        $initiator = $this->VoteSetting->getVoteInitiator($eventId);
        $this->WeshareBuy->subscribe_sharer($initiator, $uid);
    }

    private function get_candidate_rank($candidate_id, $event_id)
    {
        $query_sql = 'SELECT (SELECT COUNT(rank.id) FROM cake_candidates rank WHERE rank.id IN (SELECT candidate_id from cake_candidate_events WHERE event_id=' . $event_id . ') AND c.vote_num <= rank.vote_num) AS Rank, c.id AS Id, c.vote_num AS Roll FROM cake_candidates c WHERE c.id=' . $candidate_id . ' ORDER BY c.vote_num ASC';
        $rank_data = $this->Candidate->query($query_sql);
        return $rank_data;
    }

    private function get_all_candidate_rank_data($event_id)
    {
        $query_sql = 'SELECT (SELECT COUNT(rank.id) FROM cake_candidates rank WHERE rank.id IN (SELECT candidate_id from cake_candidate_events WHERE event_id=' . $event_id . ') AND c.vote_num <= rank.vote_num) AS Rank, c.id AS Id, c.vote_num AS Roll, c.title AS Title FROM cake_candidates c WHERE c.id IN (SELECT candidate_id from cake_candidate_events WHERE event_id=' . $event_id . ') ORDER BY c.vote_num ASC';
        $all_rank_data = $this->Candidate->query($query_sql);
        return $all_rank_data;
    }


}