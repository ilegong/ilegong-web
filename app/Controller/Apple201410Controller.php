<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/12/14
 * Time: 3:57 PM
 */
class Apple201410Controller extends AppController
{

    const DAILY_TIMES_SUB = 5;

    var $name = "Apple201410";

    var $uses = array('User', 'AppleAward', 'AwardInfo', 'TrackLog', 'CouponItem', 'ExchangeLog');

    var $DAY_LIMIT = 8;
    var $AWARD_LIMIT = 100;

    const EXCHANGE_RICE_SOURCE = 'apple_exchange_rice';


    public function beforeFilter()
    {
        parent::beforeFilter();
        if (empty($this->currentUser['id'])) {
            $this->redirect(redirect_to_wx_oauth(Router::url($_SERVER['REQUEST_URI']), WX_OAUTH_BASE, true));
        }
        $this->pageTitle = __('摇一摇免费兑稻花香大米');
        $this->set('hideNav', true);
        $this->set('noFlash', true);
    }

    public function rules()
    {
    }

    public function index()
    {
    }

    public function notifiedToMe()
    {
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

    public function hasNewTimes()
    {
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
                'award_time > \'' . date(FORMAT_DATETIME, $r) . '\''
            ), 'fields' => array('from')
            ));

            $nicknames = '';
            if (!empty($logsToMe)) {

                $uids = array_map(function ($log) {
                    return $log['TrackLog']['from'];
                }, $logsToMe);
                $maxShow = 3;
                $nicknames = implode("、", array_map(function ($n) {
                    return filter_invalid_name($n);
                }, $this->User->findNicknamesMap(array_slice($uids, 0, $maxShow))));
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

    public function assignWXSubscribeTimes()
    {
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
            $weixinTimesLog = $wxTimesLogModel->find('first', array('conditions' => array('uid' => $id, 'type' => KEY_APPLE_201410)));
            $now = mktime();
            if ($this->gotWxTimesToday($weixinTimesLog, $now)) {
                $result = self::WX_TIMES_ASSIGN_GOT;
                $res['got_time'] = date('H点i分', $weixinTimesLog['AwardWeixinTimeLog']['last_got_time']);
            } else {
                $log = array();
                $log['uid'] = $id;
                $log['last_got_time'] = $now;
                $log['type'] = KEY_APPLE_201410;
                if ($wxTimesLogModel->save(array('AwardWeixinTimeLog' => $log)) !== false) {
                    $this->AwardInfo->updateAll(array('times' => 'times + ' . self::DAILY_TIMES_SUB,), array('uid' => $id, 'type' => KEY_APPLE_201410));
                    $awardInfo = $this->AwardInfo->findByUid($id);
                    $res['total_times'] = $awardInfo['AwardInfo']['times'];
                    $result = self::WX_TIMES_ASSIGN_JUST_GOT;
                } else {
                    $result = self::WX_TIMES_ASSIGN_RETRY;
                }
            }
        } else {
            $result = $subscribe_status == WX_STATUS_UNSUBSCRIBED ? self::WX_TIMES_ASSIGN_NOT_SUB : self::WX_TIMES_ASSIGN_RETRY;
        }
        $res['result'] = $result;
        echo json_encode($res);
    }

    public function exchange_coupon()
    {
        $this->autoRender = false;
        $id = $this->currentUser['id'];
        $result = array();

        $awardInfo = $this->AwardInfo->findByUid($id);
        $apple_count_snapshot = $awardInfo['AwardInfo']['got'];
        $can_exchange_apple_count = $apple_count_snapshot;

        $exchange_log = $this->ExchangeLog->getLatestExchangeLogByUidAndSource($id, self::EXCHANGE_RICE_SOURCE);
        if ($exchange_log != false) {
            $can_exchange_apple_count = $apple_count_snapshot - intval($exchange_log['apple_count_snapshot'] / 50) * 50;
        }

        if ($can_exchange_apple_count >= 50) {
            $coupon_count = intval($can_exchange_apple_count / 50);
            $latest_exchange_log_id = $this->ExchangeLog->addExchangeLog($id, $apple_count_snapshot,
                50 * $coupon_count, $coupon_count, self::EXCHANGE_RICE_SOURCE);

            for ($i = 1; $i <= $coupon_count; $i++) {
                $this->CouponItem->addCoupon($id, self::EXCHANGE_RICE_SOURCE . "_" . $latest_exchange_log_id);
                $this->CouponItem->id = null;
            }
            $result['exchange_apple_count'] = 50 * $coupon_count;
            $result['coupon_count'] = $coupon_count;
            $result['result'] = "just-got";
        }else{
            $result['result'] = "goon";
        }
        echo json_encode($result);
    }

    private function _updateLastQueryTime($curr)
    {
        $this->Session->write($this->time_last_query_key, $curr);
    }

    private function _addNotify($uname, $added)
    {
        $this->Session->write($this->sess_award_notified, array('name' => $uname, 'got' => $added));
    }

    public function award()
    {
        $uri = "/apple_201410/award.html";
        $current_uid = $this->currentUser['id'];
        list($friend, $shouldAdd) = $this->track_or_redirect($uri, $current_uid, KEY_APPLE_201410);
        if ($shouldAdd) {
            $this->AwardInfo->updateAll(array('times' => 'times + 1',), array('uid' => $friend['User']['id']));
            $this->_addNotify(filter_invalid_name($friend['User']['nickname']), $shouldAdd);
        }

        $friendsHelpMe = $this->TrackLog->find('all', array(
            'conditions' => array('to' => $current_uid, 'type' => KEY_APPLE_201410),
            'fields' => array('from'),
            'limit' => 500
        ));

        $friendsIHelped = $this->TrackLog->find('all', array(
            'conditions' => array('from' => $current_uid, 'type' => KEY_APPLE_201410),
            'fields' => array('to'),
            'limit' => 500
        ));

        list($allUids, $nameIdMap) = $this->findNicknames($friendsHelpMe, $friendsIHelped);

        $gots = $this->AwardInfo->find('list', array(
            'conditions' => array('uid' => $allUids, 'type'=> KEY_APPLE_201410),
            'fields' => array('uid', 'got')
        ));

        $helpMeItems = array();
        foreach ($friendsHelpMe as $item) {
            $uid = $item['TrackLog']['from'];
            $helpMeItems[] = array('nickname' => filter_invalid_name($nameIdMap[$uid]), 'got' => $gots[$uid] ? $gots[$uid] : 0);
        }

        $meHelpItems = array();
        foreach ($friendsIHelped as $item) {
            $uid = $item['TrackLog']['to'];
            $meHelpItems[] = array('nickname' => filter_invalid_name($nameIdMap[$uid]), 'got' => $gots[$uid] ? $gots[$uid] : 0);
        }


        function cmp($a, $b)
        {
            $sortby = 'got'; //define here the field by which you want to sort
            return $a[$sortby] < $b[$sortby];
        }

        uasort($helpMeItems, 'cmp');
        uasort($meHelpItems, 'cmp');

        $this->set('helpMe', $helpMeItems);
        $this->set('meHelp', $meHelpItems);

        $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($current_uid, KEY_APPLE_201410);
        if (empty($awardInfo)) {
            $awardInfo = array('AwardInfo' => array('uid' => $current_uid, 'type' => KEY_APPLE_201410, 'times' => 10, 'got' => 0));
            try {
                $this->AwardInfo->save($awardInfo);
            } catch (Exception $e) {
                $this->log("error to save awardInfo:" . var_export($awardInfo, true) . ", message:" . $e->getMessage());
                if ($e && $e->getMessage() && preg_match('/^\d+: Duplicate entry \'(.*)\' for key \d+$/i', $e->getMessage(), $matches)) {
                    $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($current_uid, KEY_APPLE_201410);
                } else {
                    throw $e;
                }
            }
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
                    $awardItems[] = array('nickname' => filter_invalid_name($nicknamesMap[$uid]), 'got' => $got, 'company' => $this->companies[$uid]);
                }
            }
        }

        $this->set('awarded', $awardItems);

        $exchange_log = $this->ExchangeLog->getLatestExchangeLogByUidAndSource($current_uid, self::EXCHANGE_RICE_SOURCE);
        $this->setTotalVariables($awardInfo, $exchange_log);
        $this->set('got_apple', 0);
        $this->_updateLastQueryTime(time());

        $wxTimesLogModel = ClassRegistry::init('AwardWeixinTimeLog');
        $weixinTimesLog = $wxTimesLogModel->find('first', array('conditions' => array('uid' => $current_uid, 'type' => KEY_APPLE_201410)));
        $this->set('got_wx_sub_times', $this->gotWxTimesToday($weixinTimesLog, mktime()));

        $this->pageTitle = "摇一摇免费兑稻花香大米, 我已经兑到" . $awardInfo['got']*10 . "g五常稻花香大米啦 -- 城市里的乡下人腾讯nana分享爸爸种的大米";
    }

    public function shake()
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($uid, KEY_APPLE_201410);
        $exchange_log = $this->ExchangeLog->getLatestExchangeLogByUidAndSource($uid, self::EXCHANGE_RICE_SOURCE);

        $apple = $this->guessAwardAndUpdate($awardInfo);
        $totalAwardTimes = $awardInfo && $awardInfo['times'] ? $awardInfo['times'] : 0;
        $total_apple = $awardInfo && $awardInfo['got'] ? $awardInfo['got'] : 0;
        if($total_apple>0 && $exchange_log!=false){
            $total_apple = $total_apple - intval($exchange_log['apple_count_snapshot'] / 50) * 50;
        }
        $this->_updateLastQueryTime(time());
        echo json_encode(array('got_apple' => $apple, 'total_apple' => $total_apple, 'total_times' => $totalAwardTimes));
    }

    private function guessAwardAndUpdate(&$awardInfo)
    {

        if ($awardInfo['times'] <= 0) {
            return 0;
        };

        $total_got = ($awardInfo && $awardInfo['got']) ? $awardInfo['got'] : 0;
        $curr_got = 0;

        $this->loadModel('AwardResult');
        $model = $this->AwardResult;
        $todayAwarded = $model->todayAwarded(date(FORMAT_DATE), KEY_APPLE_201410);
        $uid = $this->currentUser['id'];
        $iAwarded = $model->userIsAwarded($uid, KEY_APPLE_201410);

        $ext = 10;
        if (!$this->is_weixin()) {
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
                'uid' => $uid,
                'type' => KEY_APPLE_201410,
                'finish_time' => date(FORMAT_DATETIME)
            );
            if (!$model->save($awardResult)) {
                $this->log("update AwardResult failed:" . json_encode($awardResult));
            };
        }

        if ($this->AwardInfo->updateAll(array('times' => 'times - 1', 'got' => 'got + ' . $curr_got, 'updated' => '\'' . date(FORMAT_DATETIME) . '\''),
            array('id' => $awardInfo['id'], 'times>0', 'type'=>KEY_APPLE_201410))) {
            $awardInfo['times'] -= 1;
            $awardInfo['got'] += $curr_got;
        } else {
            $curr_got = 0;
        }


        return $curr_got;
    }

    /**
     * @param $awardInfo
     */
    private function setTotalVariables($awardInfo, $exchangeLog)
    {
        $totalAwardTimes = $awardInfo && $awardInfo['times'] ? $awardInfo['times'] : 0;
        $total_apple = $awardInfo && $awardInfo['got'] ? $awardInfo['got'] : 0;
        if($total_apple>0 && $exchangeLog!=false){
            $total_apple = $total_apple - intval($exchangeLog['apple_count_snapshot'] / 50) * 50;
        }
        $this->set('total_apple', $total_apple);
        $this->set('total_times', $totalAwardTimes);
    }

    /**
     * @param $friendsHelpMe
     * @param $friendsIHelped
     * @return array
     */
    protected function findNicknames($friendsHelpMe, $friendsIHelped)
    {
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

    /**
     * @param $weixinTimesLog
     * @param $now
     * @return bool
     */
    protected function gotWxTimesToday($weixinTimesLog, $now)
    {
        return !empty($weixinTimesLog) && same_day($weixinTimesLog['AwardWeixinTimeLog']['last_got_time'], $now);
    }

    protected function getTrackType()
    {
        return KEY_APPLE_201410;
    }
}