<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/12/14
 * Time: 3:57 PM
 */
class Apple201410Controller extends AppController {

    const DAILY_TIMES_SUB = 8;

    var $name = "Apple201410";

    var $uses = array('User', 'AppleAward', 'AwardInfo', 'TrackLog');

    var $DAY_LIMIT = 8;
    var $AWARD_LIMIT = 100;


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
            $this->Session->write($key, 0);
        } else {
            $notified = true;
            echo json_encode(array("notified" => $notified));
        }
        $this->autoRender = false;
    }

    var $companies = array(
        1156 => '搜狗',
        1914 => '搜狗',
        1334 => '搜狗',

        1308 => '北京联合大学',
        940 => '爱大厨',
        988 => '原道智业',


        8 => '朋友说',
        578 => '朋友说',

        1608 => '去哪儿',
        1488 => '去哪儿',
        1968 => '去哪儿',
        3468 => '凤凰网',

        4639 => '去哪儿',

        2073 => '',
        926 => '去哪儿',
        2727 => '去哪儿',
        1290 => '对外经贸大学',
    );

    var $in_pys = array(8, 578, 818, 819);
    
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

    const WX_TIMES_ASSIGN_NOT_SUB = "not-sub";
    const WX_TIMES_ASSIGN_RETRY = "retry";
    const WX_TIMES_ASSIGN_GOT = "got";
    const WX_TIMES_ASSIGN_JUST_GOT = "just-got";
    public function assignWXSubscribeTimes() {
        $this->autoRender = false;
        $subscribe_status = $this->currentUser['wx_subscribe_status'];

        $id = $this->currentUser['id'];
        $res = array();
        if ($subscribe_status == WX_STATUS_UNKNOWN) {
            $this->loadModel('Oauthbind');
            $oauth = $this->Oauthbind->findWxServiceBindByUid($id);
            if (!empty($oauth)) {
                $this->loadModel('WxOauth');
                $uinfo = $this->WxOauth->get_user_info_by_base_token($oauth['oauth_openid']);
                if (!empty($uinfo)) {
                    $subscribe_status = ($uinfo['subscribe'] != 0 ? WX_STATUS_SUBSCRIBED : WX_STATUS_UNSUBSCRIBED);
                    $this->loadModel('User');
                    $this->User->updateAll(array('wx_subscribe_status' => $subscribe_status), array('id' => $id));
                }
            }
        }

        if (WX_STATUS_SUBSCRIBED == $subscribe_status) {
            $wxTimesLogModel = ClassRegistry::init('AwardWeixinTimeLog');
            $weixinTimesLog = $wxTimesLogModel->findById($id);
            $now = mktime();
            if (!empty($weixinTimesLog) && same_day($weixinTimesLog['AwardWeixinTimeLog']['last_got_time'], $now)) {
                $result = self::WX_TIMES_ASSIGN_GOT;
                $res['got_time'] = date('H点i分', $weixinTimesLog['AwardWeixinTimeLog']['last_got_time']);
            }else {
                $log = array();
                $log['id'] = $id;
                $log['last_got_time'] = $now;
                if ($wxTimesLogModel->save($log) !== false){
                    $this->AwardInfo->updateAll(array('times' => 'times + '.self::DAILY_TIMES_SUB,), array('uid' => $id, ''));
                    $awardInfo = $this->AwardInfo->findByUid($id);
                    $res['total_times'] = $awardInfo['AwardInfo']['times'];
                    $result = self::WX_TIMES_ASSIGN_JUST_GOT;
                } else {
                    $result = self::WX_TIMES_ASSIGN_RETRY;
                }
            }
        }  else {
            $result = $subscribe_status == WX_STATUS_UNSUBSCRIBED ? self::WX_TIMES_ASSIGN_NOT_SUB : self::WX_TIMES_ASSIGN_RETRY;
        }
        $res['result'] = $result;
        echo json_encode($res);
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
            'limit' => 500
        ));

        $friendsIHelped = $this->TrackLog->find('all', array(
            'conditions' => array('from' => $this->currentUser['id']),
            'fields' => array('to'),
            'limit' => 500
        ));

        list($allUids, $nameIdMap) = $this->findNicknames($friendsHelpMe, $friendsIHelped);

        $gots = $this->AwardInfo->find('list', array(
            'conditions' => array('uid' => $allUids),
            'fields' => array('uid', 'got')
        ));

        $helpMeItems = array();
        foreach($friendsHelpMe as $item) {
            $uid = $item['TrackLog']['from'];
            $helpMeItems[] = array('nickname' => $this->filter_invalid_name($nameIdMap[$uid]), 'got' => $gots[$uid]? $gots[$uid] : 0);
        }

        $meHelpItems = array();
        foreach($friendsIHelped as $item) {
            $uid = $item['TrackLog']['to'];
            $meHelpItems[] = array('nickname' => $this->filter_invalid_name($nameIdMap[$uid]), 'got' => $gots[$uid]? $gots[$uid] : 0);
        }


        function cmp($a, $b) {
            $sortby = 'got'; //define here the field by which you want to sort
            return $a[$sortby] < $b[$sortby];
        }
        uasort($helpMeItems, 'cmp');
        uasort($meHelpItems, 'cmp');

        $this->set('helpMe', $helpMeItems);
        $this->set('meHelp', $meHelpItems);

        $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($this->currentUser['id'], KEY_APPLE_201410);
        if (empty($awardInfo)) {
            $awardInfo = array('AwardInfo' => array('uid' => $this->currentUser['id'], 'type' => KEY_APPLE_201410, 'times' => 10, 'got' => 0));
            $this->AwardInfo->save($awardInfo);
            $awardInfo = $awardInfo['AwardInfo'];
        }


        //TODO: need cache
        $awardItems = array();
        $awardInfos = $this->AwardInfo->find('list', array(
            'conditions' => array('got >=' => $this->AWARD_LIMIT),
            'fields' => array('uid', 'got')
        ));
        if (!empty($awardInfos)) {
            $nicknamesMap = $this->User->findNicknamesMap(array_keys($awardInfos));
            foreach ($awardInfos as $uid => $got) {
                if (array_search($uid, $this->in_pys) === false) {
                    $awardItems[] = array('nickname' => $this->filter_invalid_name($nicknamesMap[$uid]), 'got' => $got, 'company' => $this->companies[$uid]);
                }
            }
        }

        $this->set('awarded', $awardItems);
        $this->setTotalVariables($awardInfo);
        $this->set('got_apple', 0);
        $this->_updateLastQueryTime(time());
//        $this->set('subscribe_status', $this->currentUser['wx_subscribe_status'] != WX_STATUS_SUBSCRIBED);
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

        $this->loadModel('AwardResult');
        $model = $this->AwardResult;
        $todayAwarded =  $model->todayAwarded(date(FORMAT_DATE));
        $iAwarded =  $model->userIsAwarded($this->currentUser['id']);

        $ext = 10;
        if (!$this->is_weixin()){
            $ext = 1000000;
        } else if ($todayAwarded > $this->DAY_LIMIT) {
            $left = $this->AWARD_LIMIT - $total_got;
            if ($left > 0) {
                if ($left <= 10) {
                    $ext = 100000;
                } else if ($left <= 20) {
                    $ext = 100;
                }
            }
        }

        for ($i = 0; $i < 10; $i++) {
            $mt_rand = mt_rand(0, intval($ext + $total_got));
            $curr_got += ($mt_rand >= 1 && $mt_rand <= 5 ? 1 : 0);
        }

        $curr_got = ($total_got == 0 && $curr_got == 0 ? 3 : $curr_got);

        if (is_array($iAwarded) && empty($iAwarded) && $total_got + $curr_got >= $this->AWARD_LIMIT) {
            $awardResult = array(
                'uid' => $this->currentUser['id'],
                'type' => KEY_APPLE_201410,
                'finish_time' => date(FORMAT_DATETIME)
            );
            if(!$model->save($awardResult)){
                $this->log("update AwardResult failed:". json_encode($awardResult));
            };
        }

        if($this->AwardInfo->updateAll(array('times' => 'times - 1', 'got' => 'got + '. $curr_got, 'updated' => '\''.date(FORMAT_DATETIME).'\'' ), array('id' => $awardInfo['id'], 'times>0'))){
            $awardInfo['times'] -= 1;
            $awardInfo['got'] += $curr_got;
        } else {
            $curr_got = 0;
        }


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

    private function filter_invalid_name($name) {
        if (!$name || $name == 'null') {
            $name = '神秘人';
        } else if (strpos($name, '微信用户') === 0) {
            $name = mb_substr($name, 0, 8, 'UTF-8');
        }
        return $name;
    }

    /**
     * @param $friendsHelpMe
     * @param $friendsIHelped
     * @return array
     */
    protected function findNicknames($friendsHelpMe, $friendsIHelped) {
        $allUids = array_map(function ($val) {
            return $val['TrackLog']['from'];
        }, $friendsHelpMe);

        $allUids += array_map(function ($val) {
            return $val['TrackLog']['to'];
        }, $friendsIHelped);

        $allUids = array_unique($allUids);
        $nameIdMap = $this->User->findNicknamesMap($allUids);
        return array($allUids, $nameIdMap);
    }

}