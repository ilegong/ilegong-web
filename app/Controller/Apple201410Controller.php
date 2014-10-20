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
    public function index() {}

    public function notifiedToMe() {
        $key = $this->sess_award_notified;
        $r = $this->Session->read($key);
        if (!empty($r)) {
            $r['notified'] = false;
            echo json_encode($r);
        } else {
            $notified = true;
            $this->Session->write($key, 0);
            echo json_encode(array("notified" => $notified));
        }
        $this->autoRender = false;
    }
    var $sess_award_notified = "award-notified";
    var $time_last_query_key = 'award-new-times-last';
    public function hasNewTimes() {
        $this->autoRender = false;
        $r = $this->Session->read($this->time_last_query_key);
//        if (!$r) {
//            $notifyHis = $this->UserNotifyLog->find('first', array('conditions' => array(
//                'uid' => $this->currentUser['id'],
//                'type' => KEY_APPLE_201410
//            )));

            /**
             * create table cake_user_notify_logs (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `type` char(12) NOT NULL,
            `uid` bigint(20) NOT NULL,
            `last_notify` timestamp NOT NULL,
            primary key(`id`),
            key(`uid`, `type`)
            );
             */
//            if (!empty($notifyHis)) {
//                $r = $notifyHis['UserNotifyLog']['last_notify'];
//            }
//        }
        if ($r && $r > 1413724118 /*2014-10-19 21:00*/) {
            if (time() - $r < 5) {
                return json_encode(array('success' => false));
            }
            $logsToMe = $this->TrackLog->find('all', array('conditions' => array(
                'type' => KEY_APPLE_201410,
                'to' => $this->currentUser['id'],
                'award_time > \''.date(FORMAT_DATETIME, $r).'\''
            ), 'fields' => array('from')
            ));

            $nicknames = '';
            if (!empty($logsToMe)) {

                $uids = array_map(function ($log) {
                    return $log['TrackLog']['from'];
                }, $logsToMe);
                $maxShow = 3;
                $nicknames = implode("、", array_map(function($n){ return $this->filter_invalid_name($n); }, $this->User->findNicknamesMap(array_slice($uids, 0, $maxShow))));
                if (count($logsToMe) > $maxShow) {
                    $nicknames .= __('等');
                }
            }
//        if (count($logsToMe) > 0) {
//            $this->UserNotifyLog->updateAll(array('last_notify' => time()), array(
//                'uid' => $this->currentUser['id'],
//                'type' => KEY_APPLE_201410
//            ));
//        }
            $this->_updateLastQueryTime(time());
            return json_encode(array('success' => true, 'new_times' => count($logsToMe), 'nicknames' => $nicknames));
        } else {
            $this->_updateLastQueryTime(time());
            return json_encode(array('success' => false));
        }

    }

    private function _updateLastQueryTime($curr) {
        $this->Session->write($this->time_last_query_key, $curr);
    }

    private function _addNotify($uname, $added) {
        $this->Session->write($this->sess_award_notified, array('name' => $uname, 'got' => $added));
    }

    public function award() {
        $tr_id = $_GET['trid'];
        list($friendUid, $isSelf) = $this->check_tr_id($tr_id, 'award');
        if (!$isSelf) {

            $friend = $this->User->findById($friendUid);
            if (!empty($friend)) {
                $trackLogs = $this->TrackLog->find('first', array(
                    'conditions' => array('type' => KEY_APPLE_201410, 'from' => $this->currentUser['id'], 'to' => $friendUid),
                    'fields' => array('id',)
                ));
                $shouldAdd = empty($trackLogs);
                if ($shouldAdd) {
                    $this->AwardInfo->updateAll(array('times' => 'times + 1',), array('uid' => $friendUid));
                    $this->TrackLog->save(array('TrackLog' => array('type' => KEY_APPLE_201410, 'from' => $this->currentUser['id'], 'to' => $friendUid, 'award_time' => date(FORMAT_DATETIME) )));
                }
                $this->_addNotify($this->filter_invalid_name($friend['User']['nickname']), $shouldAdd);
            }
            //treat as self
            $this->redirect_for_append_tr_id('award');
        }

        $friendsHelpMe = $this->TrackLog->find('all', array(
            'conditions' => array('to' => $this->currentUser['id']),
            'fields' => array('from'),
            'order' => ' award_time desc',
            'limit' => 500
        ));
//            $friendsIHelped = $this->AppleAward->find('all', array(
//                'conditions' => array('award_from' => $friendUid),
//                'fields' => array('award_from', 'sum(apple_got) as apple_got'),
//                'group' => ' award_time ',
//                'limit' => 500
//            ));
//
            $allUids = array_map(function($val){
                return $val['TrackLog']['from'];
            }, $friendsHelpMe);
//
//            $allUids += array_map(function($val){
//                return $val['award_to'];
//            }, $friendsIHelped);


            $allUids = array_unique($allUids);
            $nameIdMap = $this->User->findNicknamesMap($allUids);

        $friends = $this->AwardInfo->find('list', array(
            'conditions' => array('uid' => $allUids),
            'fields' => array('uid', 'got')
        ));

        $helpItems = array();
        foreach($friendsHelpMe as $item) {
            $uid = $item['TrackLog']['from'];
            $helpItems[] = array('nickname' => $this->filter_invalid_name($nameIdMap[$uid]), 'got' => $friends[$uid]);
        }

            $this->set('helpMe', $helpItems);
//            $this->set('iHelp', $friendsIHelped);
//            $this->set('userIdNames', $users);
        $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($this->currentUser['id'], KEY_APPLE_201410);
        if (empty($awardInfo)) {
            $awardInfo = array('AwardInfo' => array('uid' => $this->currentUser['id'], 'type' => KEY_APPLE_201410, 'times' => 10, 'got' => 0));
            $this->AwardInfo->save($awardInfo);
            $awardInfo = $awardInfo['AwardInfo'];
        }
        $this->set('awarded', array());
        $this->setTotalVariables($awardInfo);
        $this->set('got_apple', 0);
        $this->_updateLastQueryTime(time());
        $this->pageTitle = "摇一摇免费得红富士苹果, 我已经摇到了".$awardInfo['got']."个苹果 -- 城市里的乡下人电科院QA小娟分享家乡的苹果";
    }

    public function shake() {
        $this->autoRender = false;
        $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($this->currentUser['id'], KEY_APPLE_201410);
        $apple = $this->guessAwardAndUpdate($awardInfo);
        $totalAwardTimes = $awardInfo && $awardInfo['times'] ? $awardInfo['times'] : 0;
        $total_apple = $awardInfo && $awardInfo['got'] ? $awardInfo['got'] : 0;
        $this->_updateLastQueryTime(time());
        echo json_encode(array('got_apple' => $apple, 'total_apple' => $total_apple, 'total_times' => $totalAwardTimes));
    }

    private function guessAwardAndUpdate(&$awardInfo) {

        if ($awardInfo['times'] <= 0) { return 0; };

        $total_got = ($awardInfo && $awardInfo['got']) ? $awardInfo['got'] : 0;
        $curr_got = 0;
        for($i = 0; $i < 10; $i++) {
            $mt_rand = mt_rand(0, intval(10 * (1 + $this->_actualApple($total_got))));
            $curr_got += ($mt_rand >=1 && $mt_rand <=5 ? 1 : 0);
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

    private function filter_invalid_name($name) {
        if (!$name || $name == 'null') {
            $name = '神秘人';
        } else if (strpos($name, '微信用户') === 0) {
            $name = mb_substr($name, 0, 8, 'UTF-8');
        }
        return $name;
    }

}