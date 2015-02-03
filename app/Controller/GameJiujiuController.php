<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/12/14
 * Time: 3:57 PM
 */
class GameJiujiuController extends AppController
{

    const DAILY_TIMES_SUB = 2;
    const TIMES_INITIAL = 2;

    var $name = "Apple201410";

    var $uses = array('User', 'AppleAward', 'AwardInfo', 'TrackLog', 'CouponItem', 'ExchangeLog', 'GameConfig');

    public $components = array('Weixin');

    var $AWARD_LIMIT = 200;

    const EXCHANGE_RICE_SOURCE = 'apple_exchange_rice';
    const RICE_201411 = 'rice201411';
    const MIHOUTAO1411 = 'mihoutao1411';
    const BTC1412 = 'qinyBTC1412';
    const XIRUI1412 = 'xirui1412';
    const NORMAL_1 = 'normal1';
    const GAME_JIUJIU = 'jiujiu';

    var $treeNames = array(
        self::RICE_201411 => 'apple_shakev1.gif',
        self::MIHOUTAO1411 => 'tree_mihoutao_shake.gif',
        self::BTC1412 => 'orange.gif',
    );

    var $treeStaticNames = array(
        self::RICE_201411 => 'apple_tree.gif',
        self::MIHOUTAO1411 => 'tree_mihoutao_static.gif',
        self::BTC1412 => 'orange_static.gif',
    );

    var $game_least_change = array(
        self::RICE_201411 => 50,
        self::MIHOUTAO1411 => 30,
        self::BTC1412 => 30,
        self::XIRUI1412 => 20,
    );

    var $title_in_page = array(
        self::GAME_JIUJIU => '摇下50个，3斤丹东玖玖农场草莓免费送',
    );
    var $title_in_window = array(
        self::GAME_JIUJIU => '摇下50个，3斤丹东玖玖农场草莓免费送',
    );
    var $title_js_func = array(
    );

    //切忌不能动，位置多意境对应上了
    var $coupon_steps = array(
        18704 => 300,
        18706 => 180,
        18707 => 240,
        18708 => 60,
        18709 => 120,
        18705 => 0,
    );

    const BTC_DAILY_AWARD_LIMIT = 20;
    const COUPON_JIUJIU_FIRST = 19037;
    const COUPON_JIUJIU_SEC = 19036;
    const COUPON_JIUJIU_THIRD = 19035;
    const NEED_MOBILE_LEAST = 40;
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

    public function story()
    {
        $this->set('hideNav', true);
        $this->pageTitle = '玖玖草莓';
    }

    public function notifiedToMe($gameType = KEY_APPLE_201410) {
        $key = $this->sess_award_notified;
        $r = $this->Session->read($key);
        if (!empty($r)) {
            $r['notified'] = false;
            $friendUid = $r['friendUid'];
            if ($friendUid) {
                $trackLogs = $this->TrackLog->find_track_log($gameType, $this->currentUser['id'], $friendUid);
                if (empty($trackLogs)){
                    $notify_type = 0; //Never helped
                } else if ($trackLogs['TrackLog']['award_time'] == $trackLogs['TrackLog']['latest_click_time']){
                    // Just Helped !    and Got should be >= 1
                    $notify_type = $trackLogs['TrackLog']['got'];
                } else if ($trackLogs['TrackLog']['got'] == 0) {
                    //refused:
                    $notify_type = -1;
                } else {
                    //already helped
                    $notify_type = -2;
                }
                $r['notify_type'] = $notify_type;
            }
            echo json_encode($r);
            $this->Session->write($key, 0);
        } else {
            $notified = true;
            echo json_encode(array("notified" => $notified));
        }
        $this->autoRender = false;
    }

    var $in_pys = array(8, 578, 818, 819);

    var $sess_award_notified = "award-notified";
    var $time_last_query_key = 'award-new-times-last';

    public function hasNewTimes($gameType = KEY_APPLE_201410)
    {
        $this->autoRender = false;
        $r = $this->Session->read($this->time_last_query_key);

        $current_uid = $this->currentUser['id'];
        $result = array();
        if ($gameType == self::BTC1412) {
//            $this->fill_top_lists($gameType, $result, $current_uid);
            $total_help_me = $this->TrackLog->find('count', array(
                'conditions' => array('to' => $this->currentUser['id'], 'type' => $gameType, 'got' > 0),
            ));
            $result['total_help_me'] = $total_help_me;
//            $this->fill_today_award($gameType, $result);
        }

        if ($r && $r > 1413724118 /*2014-10-19 21:00*/) {
            if (time() - $r < 5) {
                return json_encode(array('success' => false));
            }
            $logsToMe = $this->TrackLog->find('all', array('conditions' => array(
                'type' => $gameType,
                'to' => $this->currentUser['id'],
                'got >' => '0',
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

            $this->_updateLastQueryTime(time());
            $result['success'] = true;
            $result['new_times'] =  count($logsToMe);
            $result['nicknames']  = $nicknames;
        } else {
            $this->_updateLastQueryTime(time());
            $result['success'] = false;
        }
        return json_encode($result);
    }

    const WX_TIMES_ASSIGN_NOT_SUB = "not-sub";
    const WX_TIMES_ASSIGN_RETRY = "retry";
    const WX_TIMES_ASSIGN_GOT = "got";
    const WX_TIMES_ASSIGN_JUST_GOT = "just-got";

    public function assignWXSubscribeTimes($gameType = KEY_APPLE_201410)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];

        $res = array();

        $subscribe_status = user_subscribed_pys($uid);

        if (WX_STATUS_SUBSCRIBED == $subscribe_status) {
            $wxTimesLogModel = ClassRegistry::init('AwardWeixinTimeLog');
            $weixinTimesLog = $wxTimesLogModel->find('first', array('conditions' => array('uid' => $uid, 'type' => $gameType)));
            $now = mktime();
            if ($this->gotWxTimesToday($weixinTimesLog, $now)) {
                $result = self::WX_TIMES_ASSIGN_GOT;
                $res['got_time'] = date('H点i分', $weixinTimesLog['AwardWeixinTimeLog']['last_got_time']);
            } else {
                $log = array();
                $log['uid'] = $uid;
                $log['last_got_time'] = $now;
                $log['type'] = $gameType;
                if (!empty($weixinTimesLog)) {
                    $wxTimesLogModel->id = $weixinTimesLog['AwardWeixinTimeLog']['id'];
                }
                if ($wxTimesLogModel->save(array('AwardWeixinTimeLog' => $log)) !== false) {
                    $cond = array('uid' => $uid, 'type' => $gameType);
                    $this->AwardInfo->updateAll(array('times' => 'times + ' . self::DAILY_TIMES_SUB,), $cond);
                    $awardInfo = $this->AwardInfo->find('first', array('conditions' => array('uid' => $uid, 'type' => $gameType)));
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


    public function hours_limit() {
        $hour = date('G');
        $limits = array(
            10 => 2,
            11 => 2,
            12 => 2,
            13 => 2,
            14 => 2,
            15 => 2,
            16 => 2,
            17 => 2,
            18 => 2,
            19 => 2,
        );
        return empty($limits[$hour]) ? 0 : $limits[$hour];
    }

    public function jp($gameType) {
        $this->autoRender = false;
    }

    public function exchange_coupon($gameType = KEY_APPLE_201410)
    {
        $this->autoRender = false;
        $id = $this->currentUser['id'];
        $result = array();

        $gameCfg = $this->GameConfig->findByGameType($gameType);
        if (!empty($gameCfg) && $gameCfg['GameConfig']['game_end']) {
            $dt = new DateTime($gameCfg['GameConfig']['game_end']);
            if(mktime() - $dt->getTimestamp() > 0) {
                echo json_encode(array('result' => 'game_end'));
                return;
            }
        }

        $expect = $_REQUEST['expect'];

        $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($id, $gameType);
        $apple_count_snapshot = $awardInfo['got'];
        $exChangeSource = $this->getExchangeType($gameType);

        $can_exchange_apple_count = $apple_count_snapshot - $awardInfo['spent'];

        $sold_out = false;
        $coupon_count = 0;
        $ex_count_per_Item = 0;
        if ($gameType == self::GAME_JIUJIU) {
            if ((empty($expect) || $expect == 'first') && $can_exchange_apple_count >= 50) {
                $in_special_city = $this->in_special_city();
                if ($in_special_city) {
                    $rnd = mt_rand(0, 17);
                    $hourlyCnt = $this->CouponItem->couponCountHourly(self::COUPON_JIUJIU_FIRST, time());
                    if ($hourlyCnt < $this->hours_limit() && $rnd == 10) {
                        $coupon_count = 1;
                        $ex_count_per_Item = 50;
                        $total_ex_count = $ex_count_per_Item;
                        $sharingPref = array(self::COUPON_JIUJIU_FIRST, 148);
                    }
                } else {
                    $sold_out = true;
                }
            } else if ( (empty($expect) || $expect == 'sec') && $can_exchange_apple_count >= 30) {
                $total_ex_count = $ex_count_per_Item = 30;
                $sharingPref = array(self::COUPON_JIUJIU_SEC, 74);
                $coupon_count = 1;
            }

            if ($ex_count_per_Item > 0) {
                if (!empty($sharingPref)) {
                    $so = $this->CouponItem;
                    $weixin = $this->Weixin;
                    for($i = 0; $i < $coupon_count; $i++) {
                        $this->exchangeCouponAndLog($id, $apple_count_snapshot, $ex_count_per_Item, $coupon_count, $exChangeSource, $awardInfo['id'],
                            function ($uid, $operator, $source_log_id) use ($sharingPref, $so, $weixin) {
                                list($couponId, $toShareNum) = $sharingPref;
                                $so->addCoupon($uid, $couponId, $operator, $source_log_id);
                                $so->id = null;
                                $store = "在丹东玖玖农场店购买时使用(" . $toShareNum . '元)';
                                $validDesc = "建议一小时内使用";
                                $weixin->send_coupon_received_message($uid, 1, $store, $validDesc);
                            }
                        );
                    }
                }
            }
        }

        if ($total_ex_count > 0) {
            $result['exchange_apple_count'] = $total_ex_count;
            $result['coupon_count'] = $coupon_count;
            $result['result'] = "just-got";
        }else{
            $result['result'] = $sold_out ? 'sold_out' : "goon";
        }

        echo json_encode($result);
    }

    private function _updateLastQueryTime($curr)
    {
        $this->Session->write($this->time_last_query_key, $curr);
    }

    /**
     * @param $uname
     * @param $friendUid int notify user id
     */
    private function _addNotify($uname, $friendUid)
    {
        $this->Session->write($this->sess_award_notified, array('name' => $uname, 'friendUid' => $friendUid));
    }

    public function award($gameType = KEY_APPLE_201410)
    {

        $gameCfg = $this->GameConfig->findByGameType($gameType);
        if (empty($gameCfg) ) {
            throw new CakeException("Not found Game Config");
        }

        $dailyHelpLimit = ($gameType == self::BTC1412 ? 5 : 0);

        $current_uid = $this->currentUser['id'];
        list($friend, $shouldAdd, $gameType) = $this->track_or_redirect($current_uid, $gameType, $dailyHelpLimit);
        if (!empty($friend)) {
            if ($shouldAdd) {
                $this->AwardInfo->updateAll(array('times' => 'times + 1',), array('uid' => $friend['User']['id'], 'type' => $gameType));
            }
            $this->_addNotify(filter_invalid_name($friend['User']['nickname']), $friend['User']['id']);
            $this->redirect_for_append_tr_id($current_uid, $gameType);
        }

        $trid = $_GET['trid'];
        if ($trid) {
            $this->set('trid', $trid);
        }

        $friendsHelpMe = $this->TrackLog->find('all', array(
            'conditions' => array('to' => $current_uid, 'type' => $gameType, 'got' > 0),
            'fields' => array('from'),
            'limit' => 500
        ));

            $friendsIHelped = $this->TrackLog->find('all', array(
                'conditions' => array('from' => $current_uid, 'type' => $gameType, 'got' > 0),
                'fields' => array('to'),
                'limit' => 500
            ));

            list($allUids, $nameIdMap) = $this->findNicknames($friendsHelpMe, $friendsIHelped);

            $gots = $this->AwardInfo->find('list', array(
                'conditions' => array('uid' => $allUids, 'type' => $gameType, 'got' > 0),
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


            function cmp($a, $b) {
                $sortby = 'got'; //define here the field by which you want to sort
                return $a[$sortby] < $b[$sortby];
            }

            uasort($helpMeItems, 'cmp');
            uasort($meHelpItems, 'cmp');

            $this->set('helpMe', $helpMeItems);
            $this->set('meHelp', $meHelpItems);

            $this->loadModel('Order');

        $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($current_uid, $gameType);
        if (empty($awardInfo)) {
            $awardInfo = array('AwardInfo' => array('uid' => $current_uid, 'type' => $gameType, 'times' => self::TIMES_INITIAL, 'got' => 0));
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

        list($left_98, $left_40) = $this->calculate_left($gameType);
        $this->set(compact('left_98', 'left_40'));

        $this->set('game_type', $gameType);

        $this->set('game_obj_name', $gameCfg['GameConfig']['game_obj_name']);

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
        $this->set('game_user_total', $awardInfo['got']);
        $this->_updateLastQueryTime(time());

        if ($awardInfo['got'] >= 30) {
            $this->loadModel('CouponItem');
            $coupons = $this->CouponItem->find_coupon_item_by_type_no_join($current_uid,
                array(self::COUPON_JIUJIU_FIRST, self::COUPON_JIUJIU_SEC, self::COUPON_JIUJIU_THIRD));
            $couponIds = Hash::combine($coupons, '{n}.CouponItem.coupon_id', '{n}.CouponItem.created');
            $this->set('had_coupon_first', $couponIds[self::COUPON_JIUJIU_FIRST]);
            $this->set('had_coupon_sec', $couponIds[self::COUPON_JIUJIU_SEC]);
            $this->set('had_coupon_third', $couponIds[self::COUPON_JIUJIU_THIRD]);
        }

        $this->set('game_end', $this->is_game_end($gameCfg));
        if ($gameType == self::GAME_JIUJIU) {
            $result = array();
            $this->fill_latest_awards($gameType, $result);
            $this->set('award_list', $result['latest_awards']);
        }

        $wxTimesLogModel = ClassRegistry::init('AwardWeixinTimeLog');
        $weixinTimesLog = $wxTimesLogModel->find('first', array('conditions' => array('uid' => $current_uid, 'type' => $gameType)));
        $pys_got = $this->gotWxTimesToday($weixinTimesLog, mktime());

        $this->set('today_got_wx', $pys_got);
        $subscribe_status = user_subscribed_pys($current_uid);
        $this->set('user_subscribed', $subscribe_status == WX_STATUS_SUBSCRIBED);

        $customized_game = $this->customized_view_files[$gameType];
        if (!empty($customized_game)) {
            $this->__viewFileName = $customized_game;
        }
    }

    public function shake($gameType)
    {
        $this->autoRender = false;

        if (!empty($gameType)) {
            $uid = $this->currentUser['id'];

            $gameCfg = $this->GameConfig->findByGameType($gameType);
            if (empty($gameCfg) ) {
                throw new CakeException("Not found Game Config");
            }
            $game_end = $this->is_game_end($gameCfg);
            if ($game_end) {
                echo json_encode(array('success' => false, 'msg' => 'game_end'));
                return;
            }

            $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($uid, $gameType);
            $apple = $this->guessAwardAndUpdate($awardInfo, $gameType);
            $totalAwardTimes = $awardInfo && $awardInfo['times'] ? $awardInfo['times'] : 0;
            $got = !empty($awardInfo) ?  $awardInfo['got'] : 0;
            $total_apple = $got ? ($got - $awardInfo['spent']) : 0;
            $this->_updateLastQueryTime(time());
            $need_login = $this->is_weixin() && $got > 20 && notWeixinAuthUserInfo($uid, $this->currentUser['nickname']);

            $mobile = $this->Session->read('Auth.User.mobilephone');
            $need_mobile = $gameType == self::GAME_JIUJIU && $got >= self::NEED_MOBILE_LEAST && empty($mobile);
            echo json_encode(array('success' => true, 'got_apple' => $apple, 'total_apple' => $total_apple, 'total_times' => $totalAwardTimes, 'need_login' => $need_login, 'need_mobile' => $need_mobile));
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

        $uid = $this->currentUser['id'];
        $total_got = ($awardInfo && $awardInfo['got']) ? $awardInfo['got'] : 0;
        $curr_got = 0;

//        $this->loadModel('AwardResult');
//        $model = $this->AwardResult;
        $todayAwarded = 0 ; // $model->todayAwarded(date(FORMAT_DATE), $gameType);
//        $iAwarded = $model->userIsAwarded($uid, $gameType);

        $gameCfg = $this->GameConfig->findByGameType($gameType);
        if (empty($gameCfg) ) {
            throw new CakeException("Not found Game Config");
        }

        $dayLimit = $gameCfg['GameConfig']['game_end'];
        if (empty($dayLimit)) {
            $dayLimit = 0;
        }
        $curr_got += $this->randGotApple($todayAwarded, $total_got, $dayLimit, $gameType);
        $curr_got = ($total_got == 0 && $curr_got == 0 ? 3 : $curr_got);

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
     * @param $gameType
     * @return int
     */
    private function randGotApple($todayAwarded, $total_got, $dailyLimit, $gameType) {
        $this_got = 0;
        $ext = 0;
        $limit = false;
        $mobileNum = $this->Session->read('Auth.User.mobilephone');
        if (!$this->is_weixin() || (empty($mobileNum) && $total_got > self::NEED_MOBILE_LEAST)) {
            return 0;
        } else if (false/*$this->shouldLimit($todayAwarded, $dailyLimit)*/) {
            $left = $this->AWARD_LIMIT - $total_got;
            $limit = true;
            if ($left > 0) {
                if ($left <= 3) {
                    $ext = 100000;
                } else if ($left < 10) {
                    $ext = $left * 100;
                }
                /*
                else if ($left <= 20) {
                    $ext = 100;
                } */
            }
        }
//
//        if ($total_got < 90 && $total_got>30) {
////            $ext -= 40;
//        } else if ($total_got > 150) {
//            $ext += 3 * $total_got;
//        }

        if ($ext < 0) {
            $ext = 5;
        }

        $times = 10;

        if ($total_got >= 30) {
            $ext = 50;
        } else if($total_got >= 40) {
            $in_special_city = $this->in_special_city();
            $ext = $in_special_city ? 100 : 200;
        }

        for ($i = 0; $i < $times; $i++) {
            $mt_rand = mt_rand(0, intval($ext + $total_got));
            $this_got += ($mt_rand >= 1 && $mt_rand <= 5 ? 1 : 0);
        }

        if (($total_got + $this_got) > 90 /*$this->AWARD_LIMIT*/) {
            $this_got = 0;
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
     * @param $uid
     * @param $apple_count_snapshot
     * @param $exchangeCount
     * @param $coupon_count
     * @param $exChangeSource
     * @param $awardInfoId
     * @param $couponFunc
     */
    private function exchangeCouponAndLog($uid, $apple_count_snapshot, $exchangeCount, $coupon_count, $exChangeSource, $awardInfoId, $couponFunc) {
        $latest_exchange_log_id = $this->ExchangeLog->addExchangeLog($uid, $apple_count_snapshot,
            $exchangeCount, $coupon_count, $exChangeSource);

        $operator = $this->currentUser['id'];
        if($latest_exchange_log_id) {
            $awardInfoModel = ClassRegistry::init('AwardInfo');
            if ($awardInfoModel->updateAll(array('spent ' => 'spent + '. $exchangeCount, ), array('id' => $awardInfoId))) {
                for ($i = 1; $i <= $coupon_count; $i++) {
                    $couponFunc($uid, $operator, $exChangeSource . "_" . $latest_exchange_log_id . '_' . $i);
                }
            }
        }
    }

    private function shouldLimit($todayAwarded, $dailyLimit) {
        if($todayAwarded >= $dailyLimit) return true;

        $hour = date('G');
        return ($todayAwarded >= round($dailyLimit * $hour/24, 0, PHP_ROUND_HALF_UP));
    }

    /**
     * @param $gameType
     * @return array
     */
    private function calculate_left($gameType) {
        $left_40 = $left_98 = 0;
//        if ($gameType == self::CHENGZI_1411) {
//            $left_98 = 30 - $this->CouponItem->couponCount(COUPON_TYPE_CHZ_100);
//            $left_40 = 1200 - $this->CouponItem->couponCount(COUPON_TYPE_CHZ_90);
//            return array($left_98 >= 0? $left_98 : 0, $left_40 >= 0 ? $left_40 : 0);
//        }
        return array($left_98, $left_40);
    }

    /**
     * @param $gameCfg
     * @return bool
     */
    private function is_game_end($gameCfg) {
        $game_end = $gameCfg['GameConfig']['game_end'];
        if ($game_end) {
            $dt = new DateTime($game_end);
            return (mktime() - $dt->getTimestamp() > 0);
        }
        return true;
    }

    /**
     * Fill top list elements to the specified result array
     * @param $gameType
     * @param $result
     */
    private function fill_latest_awards($gameType, &$result) {

        $listR = $this->CouponItem->find_latest_coupon_item_by_type_no_join(array(self::COUPON_JIUJIU_FIRST, self::COUPON_JIUJIU_SEC), 100);
        $now_time = time();
        $updateTime = friendlyDate($now_time, 'full');

        $cache_key = 'v_latest_list_' . $gameType . '_' . date('Y-m-d Hi', $now_time);
        $top_list_cache = Cache::read($cache_key);
        if (empty($top_list_cache)) {
            $award_list = array();
            $uids = array();
            foreach ($listR as $res) {
                $bind_user = $res['CouponItem']['bind_user'];
                $coupon_id = $res['CouponItem']['coupon_id'];
                $created = friendlyDateFromStr($res['CouponItem']['created']);
                $award_list[] = array($bind_user, $coupon_id == self::COUPON_JIUJIU_FIRST ? 'first' : 'sec', $created);
                $uids[] = $bind_user;
            }
            $nameIdMap = $this->User->findNicknamesMap($uids);
            foreach ($award_list as &$list) {
                $list[0] = mb_substr(filter_invalid_name($nameIdMap[$list[0]]), 0, 7);
            }
            Cache::write($cache_key, json_encode($award_list));
        } else {
            $award_list = json_decode($top_list_cache);
        }

        $tt_list = array('list' => $award_list, 'update_time' => $updateTime);

        $result['latest_awards'] = $tt_list;
    }

    /**
     * Fill today award user names/updated time to the specified result array
     * @param $gameType
     * @param $result
     * @param int $limit
     */
    private function fill_today_award($gameType, &$result, $limit = 60) {

        $this->loadModel('AwardResult');
        $day = date(FORMAT_DATE);
        $listR = $this->CouponItem->find_latest_coupon_item_by_type_no_join(array(self::COUPON_JIUJIU_FIRST, self::COUPON_JIUJIU_SEC));
        $updateTime = friendlyDate(time(), 'full');

        $cache_key = 'v_list_' .$gameType . '_'. $day . '_' .$listR[0];
        $today_award_list_cache = Cache::read($cache_key);
        if (empty($today_award_list_cache)) {
            $count = 0;
            $uids = array();
            foreach ($listR[1] as $awardResult) {
                if ($count++ >= $limit) {
                    break;
                }
                $uid = $awardResult['AwardResult']['uid'];
                $uids[] = $uid;
            }

            $names = array();
            $nameIdMap = $this->User->findNicknamesMap($uids);
            foreach ($uids as $uid) {
                $names[] = mb_substr(filter_invalid_name($nameIdMap[$uid]), 0, 8);
            }
            Cache::write($cache_key, json_encode($names));
        } else {
            $names = json_decode($today_award_list_cache);
        }

        $tt_list = array('list' => $names, 'update_time' => $updateTime, 'today_awarded' => $this->AwardResult->todayAwarded($day, $gameType));

        $result['award_list'] = $tt_list;
    }

    /**
     * @return bool
     */
    private function in_special_city() {
        $mobileNum = $this->Session->read('Auth.User.mobilephone');
        $this->loadModel('MobileInfo');
        $info = $this->MobileInfo->get_province($mobileNum);
        return $info == '天津' || $info == '北京';
    }
}