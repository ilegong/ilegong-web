<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/12/14
 * Time: 3:57 PM
 */
class Apple201410Controller extends AppController {

    const TRACK_KEY_APPLE = 'apple:';

    var $name = "Apple201410";

    var $uses = array('User', 'AppleAward');

    public function beforeFilter() {
        parent::beforeFilter();
        if(empty($this->currentUser['id'])){
            $this->redirect('/users/login?referer='.Router::url($_SERVER['REQUEST_URI']));
        }
        $this->set('pageTitle', __('摇一摇免费得红富士苹果'));
    }

    public function award($tr_id) {
        list($uid, $isSelf) = $this->check_tr_id($tr_id, 'award');
        if (!$isSelf) {
            $friend = $this->User->get($uid);
            if (!empty($friend)) {
                $this->set('friend', $friend);
            } else {
                //treat as self
                $this->redirect_for_append_tr_id('award');
            }
        } else {
            $friendsHelpMe = $this->AppleAward->find('all', array(
                'conditions' => array('award_to' => $uid),
                'fields' => array('award_to', 'sum(apple_got) as apple_got'),
                'order' => ' award_time desc',
                'limit' => 500
            ));
            $friendsIHelped = $this->AppleAward->find('all', array(
                'conditions' => array('award_from' => $uid),
                'fields' => array('award_from', 'sum(apple_got) as apple_got'),
                'group' => ' award_time ',
                'limit' => 500
            ));

            $allUids = array_map(function($val){
                return $val['award_from'];
            }, $friendsHelpMe);

            $allUids += array_map(function($val){
                return $val['award_to'];
            }, $friendsIHelped);


            $allUids = array_unique($allUids);

            $users = $this->User->find('list', array('conditions' => array('id' => $allUids), 'field' => array('id', 'nickname')));

            $this->set('helpMe', $friendsHelpMe);
            $this->set('iHelp', $friendsIHelped);
            $this->set('userIdNames', $users);
        }
    }

    /**
     * if $tr_id is empty, redirect with current user's track id;
     * else return user id and whether is self.
     * Throw an exception if the $tr_id cannot be decoded to a valid $uid
     * @param $tr_id
     * @param $action
     * @return array
     */
    private function check_tr_id($tr_id, $action) {
        if (empty($tr_id)) {
            $this->redirect_for_append_tr_id($action);
        }
        if (!empty($tr_id)) {
            $uid = $this->decode_apple_tr_id($tr_id);
            if ($uid && is_numeric($uid)) {
                return array($uid, $uid === $this->currentUser['id']);
            } else {
                throw new CacheException("invalid track id");
            }
        }
    }

    /**
     * @param $action
     */
    private function redirect_for_append_tr_id($action) {
        $this->redirect("/apple_201410/$action/" . urlencode($this->encode_apple_tr_id($this->currentUser['id'])).'.html');
    }

    private function encode_apple_tr_id($id) {
        $code = authcode($id, 'ENCODE', Apple201410Controller::TRACK_KEY_APPLE);
        return $code;
    }

    private function decode_apple_tr_id($tr_id) {
        return authcode($tr_id, 'DECODE', Apple201410Controller::TRACK_KEY_APPLE);
    }

} 