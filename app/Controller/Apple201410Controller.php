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

    public $components = array('Weixin');

    var $AWARD_LIMIT = 100;

    const EXCHANGE_RICE_SOURCE = 'apple_exchange_rice';
    const RICE_201411 = 'rice201411';
    const CHENGZI_1411 = 'chengzi1411';
    const CHOUPG_1411 = 'Choupg1411';

    var $DAY_LIMIT = array(
        self::RICE_201411 => 20,
        self::CHOUPG_1411 => 10,
        self::CHENGZI_1411 => 10,
    );
    var $game_obj_names = array(
        self::CHOUPG_1411 => '苹果',
        self::RICE_201411 => '苹果',
        self::CHENGZI_1411 => '橙子',
    );
    var $treeNames = array(
        self::CHOUPG_1411 => 'apple_shakev1.gif',
        self::RICE_201411 => 'apple_shakev1.gif',
        self::CHENGZI_1411 => 'orange.gif');

    var $treeStaticNames = array(
        self::CHOUPG_1411 => 'apple_tree.gif',
        self::RICE_201411 => 'apple_tree.gif',
        self::CHENGZI_1411 => 'orange_static.gif');

    var $game_least_change = array(
        self::RICE_201411 => 50,
        self::CHENGZI_1411 => 30,
        self::CHOUPG_1411 => 30,
    );

    var $game_ends = array(
        self::RICE_201411 => '2014-11-15 23:59:59',
        self::CHENGZI_1411 => '2014-11-16 23:59:59',
        self::CHOUPG_1411 => '2014-11-17 23:59:59',
    );
    var $title_in_page = array(
        self::CHOUPG_1411 => '摇一摇,最高一箱<a href="/products/20141028/yun_nan_chou_ping_guo.html">云南丑苹果</a>免费送',
        self::CHENGZI_1411 => '摇一摇，最高一箱<a href="/products/20141014/gan_nan_qi_cheng_kai_shi_yu_shou.html">橙子</a>免费送',
        self::RICE_201411 => '摇下50个，大米优惠券免费送',
    );
    var $title_in_window = array(
        self::CHOUPG_1411 => '摇下100个,十斤云南丑苹果免费送',
        self::CHENGZI_1411 => '摇下100个，最高一箱橙子免费送',
        self::RICE_201411 => '摇下50个，大米优惠券免费送',
    );
    var $title_js_func = array(
        self::CHOUPG_1411 => "'摇一摇换云南丑苹果, 我已经摇到'+total+'个啦'",
        self::RICE_201411 => "'摇一摇免费兑稻花香大米券, 我已经有机会兑到'+total*10+'g五常稻花香大米啦 -- 城市里的乡下人腾讯nana分享爸爸种的大米-朋友说'",
        self::CHENGZI_1411 => "'橙妾来啦，摇一摇一箱橙子带回家，我已经...'",
    );

    public function beforeFilter()
    {
        parent::beforeFilter();
        if (empty($this->currentUser['id'])) {
            $ref = Router::url($_SERVER['REQUEST_URI']);
            if ($this->is_weixin()) {
                $this->redirect(redirect_to_wx_oauth($ref, WX_OAUTH_BASE, true));
            } else {
                $this->redirect('/users/login.html?referer='.$ref);
            }
        }
    }

    public function rules()
    {
    }

    public function index()
    {
        $this->set('game_type', self::RICE_201411);
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

    public function hasNewTimes($gameType = KEY_APPLE_201410)
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
                'type' => $gameType,
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

    public function assignWXSubscribeTimes($gameType = KEY_APPLE_201410)
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
            $weixinTimesLog = $wxTimesLogModel->find('first', array('conditions' => array('uid' => $id, 'type' => $gameType)));
            $now = mktime();
            if ($this->gotWxTimesToday($weixinTimesLog, $now)) {
                $result = self::WX_TIMES_ASSIGN_GOT;
                $res['got_time'] = date('H点i分', $weixinTimesLog['AwardWeixinTimeLog']['last_got_time']);
            } else {
                $log = array();
                $log['uid'] = $id;
                $log['last_got_time'] = $now;
                $log['type'] = $gameType;
                if (!empty($weixinTimesLog)) {
                    $wxTimesLogModel->id = $weixinTimesLog['AwardWeixinTimeLog']['id'];
                }
                if ($wxTimesLogModel->save(array('AwardWeixinTimeLog' => $log)) !== false) {
                    $cond = array('uid' => $id, 'type' => $gameType);
                    $this->AwardInfo->updateAll(array('times' => 'times + ' . self::DAILY_TIMES_SUB,), $cond);
                    $awardInfo = $this->AwardInfo->find('first', array('conditions' => array('uid' => $id, 'type' => $gameType)));
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

    public function exchange_coupon($gameType = KEY_APPLE_201410)
    {
        $this->autoRender = false;
        $id = $this->currentUser['id'];
        $result = array();

        $game_end = $this->game_ends[$gameType];
        if ($game_end) {
            $dt = new DateTime($game_end);
            if(mktime() - $dt->getTimestamp() > 0) {
                echo json_encode(array('result' => 'goon', 'msg' => 'game_end'));
                return;
            }
        }

        $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($id, $gameType);
        $apple_count_snapshot = $awardInfo['got'];
        $exChangeSource = $this->getExchangeType($gameType);

        $can_exchange_apple_count = $apple_count_snapshot - $awardInfo['spent'];

        $coupon_count = 0;
        $exchangeCount = 0;
        $store = '';
        $validDesc = '';
        if ($gameType == self::RICE_201411) {
            if ($can_exchange_apple_count >= 50) {
                $coupon_count = intval($can_exchange_apple_count / 50);
                $exchangeCount = 50 * $coupon_count;
                $this->exchangeCouponAndLog($id, $apple_count_snapshot, $exchangeCount, $coupon_count, $exChangeSource, $awardInfo['id'], COUPON_TYPE_RICE_1KG);
                $store = "购买nana家大米时使用";
                $validDesc = "有效期至2014年11月15日";
            }
        } else if($gameType == self::CHENGZI_1411) {
            $couponType = 0;
            if ($can_exchange_apple_count >= 100) {
                $coupon_count = 1;
                $exchangeCount = 100;
                $couponType = COUPON_TYPE_CHZ_100;
            } else if ($can_exchange_apple_count >= 90) {
                $coupon_count = 1;
                $exchangeCount = 90;
                $couponType = COUPON_TYPE_CHZ_90;
            } else if ($can_exchange_apple_count >= 50) {
                $coupon_count = 1;
                $exchangeCount = 50;
                $couponType = COUPON_TYPE_CHZ_50;
            } else if ($can_exchange_apple_count >= 30) {
                $coupon_count = 1;
                $exchangeCount = 30;
                $couponType = COUPON_TYPE_CHZ_30;
            }

            if ($coupon_count > 0 && $couponType) {
                $this->exchangeCouponAndLog($id, $apple_count_snapshot, $exchangeCount, $coupon_count, $exChangeSource, $awardInfo['id'], $couponType);
                $store = "购买赣南脐橙时使用";
                $validDesc = "有效期至2014年11月16日";
            }
        } else if ($gameType == self::CHOUPG_1411) {
            $couponType = 0;
            if ($can_exchange_apple_count >= 100) {
                $coupon_count = 1;
                $exchangeCount = 100;
                $couponType = COUPON_TYPE_CHOUPG_100;
            } else if ($can_exchange_apple_count >= 50) {
                $coupon_count = 1;
                $exchangeCount = 50;
                $couponType = COUPON_TYPE_CHOUPG_50;
            } else if ($can_exchange_apple_count >= 30) {
                $coupon_count = 1;
                $exchangeCount = 30;
                $couponType = COUPON_TYPE_CHOUPG_30;
            }

            if ($coupon_count > 0 && $couponType) {
                $this->exchangeCouponAndLog($id, $apple_count_snapshot, $exchangeCount, $coupon_count, $exChangeSource, $awardInfo['id'], $couponType);
                $store = "购买云南丑苹果时使用";
                $validDesc = "有效期至2014年11月17日";
            }
        }

        if ($coupon_count > 0) {
            $result['exchange_apple_count'] = $exchangeCount;
            $result['coupon_count'] = $coupon_count;
            $result['result'] = "just-got";
            $this->Weixin->send_coupon_received_message($id, $coupon_count, $store, $validDesc);
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

    public function award($gameType = KEY_APPLE_201410)
    {
        $current_uid = $this->currentUser['id'];
        list($friend, $shouldAdd, $gameType) = $this->track_or_redirect($current_uid, $gameType);
        if (!empty($friend)) {
            if ($shouldAdd) {
                $this->AwardInfo->updateAll(array('times' => 'times + 1',), array('uid' => $friend['User']['id'], 'type' => $gameType));
            }
            $this->_addNotify(filter_invalid_name($friend['User']['nickname']), $shouldAdd);
            $this->redirect_for_append_tr_id($current_uid, $gameType);
        }

        $friendsHelpMe = $this->TrackLog->find('all', array(
            'conditions' => array('to' => $current_uid, 'type' => $gameType),
            'fields' => array('from'),
            'limit' => 500
        ));

        $friendsIHelped = $this->TrackLog->find('all', array(
            'conditions' => array('from' => $current_uid, 'type' => $gameType),
            'fields' => array('to'),
            'limit' => 500
        ));

        list($allUids, $nameIdMap) = $this->findNicknames($friendsHelpMe, $friendsIHelped);

        $gots = $this->AwardInfo->find('list', array(
            'conditions' => array('uid' => $allUids, 'type'=> $gameType),
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

        $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($current_uid, $gameType);
        if (empty($awardInfo)) {
            $awardInfo = array('AwardInfo' => array('uid' => $current_uid, 'type' => $gameType, 'times' => 10, 'got' => 0));
            try {
                $this->AwardInfo->save($awardInfo);
            } catch (Exception $e) {
                if ($e && $e->getMessage() && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($current_uid, $gameType);
                    $this->log('save Award Info error:'.$e->getMessage());
                } else {
                    $this->log("error to save awardInfo:" . var_export($awardInfo, true) . ", message:" . $e->getMessage());
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
        $this->set('game_type', $gameType);

        $this->set('game_obj_name', $this->game_obj_names[$gameType]);
        $this->set('title_func', $this->title_js_func[$gameType]);
        $title_in_page = $this->title_in_page[$gameType];
        $this->set('title_in_page', $title_in_page);
        $this->set('game_least_change', $this->game_least_change[$gameType]);
        $this->set('treeName', $this->treeNames[$gameType]);
        $this->set('treeStatic', $this->treeStaticNames[$gameType]);

        $this->pageTitle = $this->title_in_window[$gameType];
        $this->set('hideNav', true);
        $this->set('noFlash', true);

        $this->setTotalVariables($awardInfo);
        $this->set('got_apple', 0);
        $this->_updateLastQueryTime(time());


        $game_end = $this->game_ends[$gameType];
        if ($game_end) {
            $dt = new DateTime($game_end);
            if(mktime() - $dt->getTimestamp() > 0) {
                $this->set('game_end', true);
            }
        }

        $wxTimesLogModel = ClassRegistry::init('AwardWeixinTimeLog');
        $weixinTimesLog = $wxTimesLogModel->find('first', array('conditions' => array('uid' => $current_uid, 'type' => $gameType)));
        $this->set('got_wx_sub_times', $this->gotWxTimesToday($weixinTimesLog, mktime()));
    }

    public function shake($gameType)
    {
        $this->autoRender = false;

        if (!empty($gameType)) {
            $uid = $this->currentUser['id'];

            $game_end = $this->game_ends[$gameType];
            if ($game_end) {
                $dt = new DateTime($game_end);
                if(mktime() - $dt->getTimestamp() > 0) {
                    echo json_encode(array('success' => false, 'msg' => 'game_end'));
                    return;
                }
            }

            $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($uid, $gameType);
            $apple = $this->guessAwardAndUpdate($awardInfo, $gameType);
            $totalAwardTimes = $awardInfo && $awardInfo['times'] ? $awardInfo['times'] : 0;
            $got = !empty($awardInfo) ?  $awardInfo['got'] : 0;
            $total_apple = $got ? ($got - $awardInfo['spent']) : 0;
            $this->_updateLastQueryTime(time());
            $need_login = $this->is_weixin() && $got > 50 && notWeixinAuthUserInfo($uid, $this->currentUser['nickname']);
            echo json_encode(array('success' => true, 'got_apple' => $apple, 'total_apple' => $total_apple, 'total_times' => $totalAwardTimes, 'need_login' => $need_login));
        } else {
            $this->log('incorrect award activity type:'. $gameType);
            echo json_encode(array('success' => false, 'msg' => 'incorrect_type'));
        }
    }

    private function guessAwardAndUpdate(&$awardInfo, $gameType)
    {

        if ($awardInfo['times'] <= 0) {
            return 0;
        };

        $total_got = ($awardInfo && $awardInfo['got']) ? $awardInfo['got'] : 0;
        $curr_got = 0;

        $this->loadModel('AwardResult');
        $model = $this->AwardResult;
        $todayAwarded = $model->todayAwarded(date(FORMAT_DATE), $gameType);
        $uid = $this->currentUser['id'];
        $iAwarded = $model->userIsAwarded($uid, $gameType);

        $dayLimit = $this->DAY_LIMIT[$gameType];
        if (empty($dayLimit)) {
            $dayLimit = 0;
        }
        $curr_got += $this->randGotApple($todayAwarded, $total_got, $dayLimit);
        $curr_got = ($total_got == 0 && $curr_got == 0 ? 3 : $curr_got);

        if (is_array($iAwarded) && empty($iAwarded) && $total_got + $curr_got >= $this->AWARD_LIMIT) {
            $awardResult = array(
                'uid' => $uid,
                'type' => $gameType,
                'finish_time' => date(FORMAT_DATETIME)
            );
            if (!$model->save($awardResult)) {
                $this->log("update AwardResult failed:" . json_encode($awardResult));
            };
        }

        if ($this->AwardInfo->updateAll(array('times' => 'times - 1', 'got' => 'got + ' . $curr_got, 'updated' => '\'' . date(FORMAT_DATETIME) . '\''),
            array('id' => $awardInfo['id'], 'times>0', 'type'=> addslashes($gameType)))) {
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
    private function setTotalVariables($awardInfo)
    {
        $totalAwardTimes = $awardInfo && $awardInfo['times'] ? $awardInfo['times'] : 0;
        $total_apple = $awardInfo && $awardInfo['got'] ? ($awardInfo['got'] - $awardInfo['spent']) : 0;
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

    /**
     * @param $todayAwarded
     * @param $total_got
     * @param $dailyLimit
     * @return int
     */
    private function randGotApple($todayAwarded, $total_got, $dailyLimit) {
        $this_got = 0;
        $ext = 10;
        if (!$this->is_weixin()) {
            $ext = 1000000;
        } else if ($this->shouldLimit($todayAwarded, $dailyLimit)) {
            $left = $this->AWARD_LIMIT - $total_got;
            if ($left > 0) {
                if ($left <= 10) {
                    $ext = 100000;
                }
                /*
                else if ($left <= 20) {
                    $ext = 100;
                } */
            }
        }

        if ($total_got < 80 && $total_got>50) {
            $ext -= 30;
        } else if ($total_got > 150) {
            $ext += 3 * $total_got;
        }

        for ($i = 0; $i < 10; $i++) {
            $mt_rand = mt_rand(0, intval($ext + $total_got));
            $this_got += ($mt_rand >= 1 && $mt_rand <= 5 ? 1 : 0);
        }
        return $this_got;
    }

    /**
     * @param $gameType
     * @return string
     */
    private function getExchangeType($gameType) {
        return $gameType == self::RICE_201411 ? self::EXCHANGE_RICE_SOURCE : $gameType;
    }

    /**
     * @param $id
     * @param $apple_count_snapshot
     * @param $exchangeCount
     * @param $coupon_count
     * @param $exChangeSource
     * @param $awardInfoId
     * @param $couponType
     */
    private function exchangeCouponAndLog($id, $apple_count_snapshot, $exchangeCount, $coupon_count, $exChangeSource, $awardInfoId, $couponType) {
        $latest_exchange_log_id = $this->ExchangeLog->addExchangeLog($id, $apple_count_snapshot,
            $exchangeCount, $coupon_count, $exChangeSource);

        $uid = $this->currentUser['id'];
        if($latest_exchange_log_id) {
            $awardInfoModel = ClassRegistry::init('AwardInfo');
            if ($awardInfoModel->updateAll(array('spent ' => 'spent + '. $exchangeCount, ), array('id' => $awardInfoId))) {
                for ($i = 1; $i <= $coupon_count; $i++) {
                    $this->CouponItem->addCoupon($id, $couponType, $uid, $exChangeSource . "_" . $latest_exchange_log_id . '_' . $i);
                    $this->CouponItem->id = null;
                }
            }
        }
    }

    private function shouldLimit($todayAwarded, $dailyLimit) {
        if($todayAwarded >= $dailyLimit) return true;

        $hour = date('G');
        return ($todayAwarded >= round($dailyLimit * $hour/24, 0, PHP_ROUND_HALF_UP));
    }
}