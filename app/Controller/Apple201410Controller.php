<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/12/14
 * Time: 3:57 PM
 */
class Apple201410Controller extends AppController {

    var $name = "Apple201410";

    var $uses = array('User', 'AppleAward', 'AwardInfo', 'TrackLog');

    public function beforeFilter() {
        parent::beforeFilter();
        if(empty($this->currentUser['id'])){
            $this->redirect('/users/login?referer='.Router::url($_SERVER['REQUEST_URI']));
        }
        $this->pageTitle = __('摇一摇免费得红富士苹果');
        $this->set('hideNav', true);
        $this->set('noFlash', true);
    }

    public function rules() {}

    public function award() {
        $tr_id = $_GET['trid'];
        list($friendUid, $isSelf) = $this->check_tr_id($tr_id, 'award');
        if (!$isSelf) {

            $friend = $this->User->findById($friendUid);
            if (!empty($friend)) {
                $this->set('friend', $friend);

                $trackLogs = $this->TrackLog->find('first', array(
                    'conditions' => array('type' => KEY_APPLE_201410, 'from' => $this->currentUser['id'], 'to' => $friendUid),
                    'fields' => array('id',)
                ));
                if (empty($trackLogs)) {
                    $this->AwardInfo->updateAll(array('times' => 'times + 1',), array('uid' => $friendUid));
                    $this->TrackLog->save(array('TrackLog' => array('type' => KEY_APPLE_201410, 'from' => $this->currentUser['id'], 'to' => $friendUid, 'award_time' => date('Y-m-d H:i:s') )));
                    $this->set('addedNotify', true);
                }
            } else {
                //treat as self
                $this->redirect_for_append_tr_id('award');
            }
        }
//            $friendsHelpMe = $this->AppleAward->find('all', array(
//                'conditions' => array('award_to' => $friendUid),
//                'fields' => array('award_to', 'sum(apple_got) as apple_got'),
//                'order' => ' award_time desc',
//                'limit' => 500
//            ));
//            $friendsIHelped = $this->AppleAward->find('all', array(
//                'conditions' => array('award_from' => $friendUid),
//                'fields' => array('award_from', 'sum(apple_got) as apple_got'),
//                'group' => ' award_time ',
//                'limit' => 500
//            ));
//
//            $allUids = array_map(function($val){
//                return $val['award_from'];
//            }, $friendsHelpMe);
//
//            $allUids += array_map(function($val){
//                return $val['award_to'];
//            }, $friendsIHelped);


//            $allUids = array_unique($allUids);

//            $users = $this->User->find('list', array('conditions' => array('id' => $allUids), 'field' => array('id', 'nickname')));

//            $this->set('helpMe', $friendsHelpMe);
//            $this->set('iHelp', $friendsIHelped);
//            $this->set('userIdNames', $users);
            $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($this->currentUser['id'], KEY_APPLE_201410);
            if (empty($awardInfo)) {
                $awardInfo = array('AwardInfo' => array('uid' => $this->currentUser['id'], 'type' => KEY_APPLE_201410, 'times' => 10, 'got' => 0));
                $this->AwardInfo->save($awardInfo);
                $awardInfo = $awardInfo['AwardInfo'];
            }
            $this->setTotalVariables($awardInfo);
            $this->set('got_apple', 0);
    }

    public function shake() {
        $this->autoRender = false;
        $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($this->currentUser['id'], KEY_APPLE_201410);
        $apple = $this->guessAwardAndUpdate($awardInfo);
        $totalAwardTimes = $awardInfo && $awardInfo['times'] ? $awardInfo['times'] : 0;
        $total_apple = $awardInfo && $awardInfo['got'] ? $awardInfo['got'] : 0;
        echo json_encode(array('got_apple' => $apple, 'total_apple' => $total_apple, 'total_times' => $totalAwardTimes));
    }

    private function guessAwardAndUpdate(&$awardInfo) {

        if ($awardInfo['times'] <= 0) { return 0; };

        $total_got = ($awardInfo && $awardInfo['got']) ? $awardInfo['got'] : 0;
        $curr_got = 0;
        for($i = 0; $i < 10; $i++) {
            $mt_rand = mt_rand(0, intval(10 * (1 + $this->_actualApple($total_got))));
            $curr_got += ($mt_rand >=1 && $mt_rand <=3  ? 1 : 0);
        }
        $curr_got = ($total_got == 0 && $curr_got == 0 ? 3 : $curr_got);

        $this->AwardInfo->updateAll(array('times' => 'times - 1', 'got' => 'got + '. $curr_got, ), array('id' => $awardInfo['id']));

        $awardInfo['times'] -= 1;
        $awardInfo['got'] += $curr_got;

        return $curr_got;
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
                return array(false, false);
            }
        }
    }

    /**
     * @param $action
     */
    private function redirect_for_append_tr_id($action) {
        $encodedTrid = $this->encode_apple_tr_id($this->currentUser['id']);
        $this->redirect("/apple_201410/$action.html?trid=".$encodedTrid);
    }

    private function encode_apple_tr_id($id) {
        $code = authcode($id, 'ENCODE', $this->getTrackKey());
        return $code;
    }

    private function decode_apple_tr_id($tr_id) {
        return authcode($tr_id, 'DECODE', $this->getTrackKey());
    }

    private function getTrackKey() {
        return KEY_APPLE_201410 . ':';
    }

    /**
     * @param $awardInfo
     */
    private function setTotalVariables($awardInfo) {
        $totalAwardTimes = $awardInfo && $awardInfo['times'] ? $awardInfo['times'] : 0;
        $total_apple = $awardInfo && $awardInfo['got'] ? $awardInfo['got'] : 0;
        $this->set('total_apple', $total_apple);
        $this->set('total_times', $totalAwardTimes);
    }

    /**
     * @param $got
     * @return float
     */
    private function _actualApple($got) {
        return (int)($got / 10);
    }

}