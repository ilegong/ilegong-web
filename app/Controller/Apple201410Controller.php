<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/12/14
 * Time: 3:57 PM
 */
class Apple201410Controller extends AppController
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

    /*
     * INSERT INTO `cake_game_configs` (`game_type`, `day_limit`, `created`, `modified`, `game_obj_name`, `game_end`, `game_start`) VALUES
    ('chengzi1411', 10, NULL, NULL, '橙子', '2014-11-17 23:59:59', ''),
    ('rice201411', 20, NULL, NULL, '苹果', '2014-11-15 23:59:59', ''),
    ('Choupg1411', 10, NULL, NULL, '苹果', '2014-11-17 23:59:59', ''),
    ('mihoutao1411', 0, NULL, NULL, '猕猴桃', '2014-12-03 23:59:59', '2014-11-26 00:00:00')
    INSERT INTO `cake_game_configs` (`game_type`, `day_limit`, `created`, `modified`, `game_obj_name`, `game_end`, `game_start`)
VALUES
	('xirui1412', 50, NULL, NULL, '大米', '2014-12-30 23:59:59', '2014-12-24 00:00:00'),
	('normal1', 50, NULL, NULL, '橙子', '2014-12-30 23:59:59', '2015-12-24 00:00:00')
    ;
    ;
     */

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
        self::MIHOUTAO1411 => '摇下100个，最高1箱猕猴桃免费领',
        self::RICE_201411 => '摇下50个，大米优惠券免费送',
        self::BTC1412 => '摇下100个，1箱万橙免费领',
        self::XIRUI1412 => '摇下20粒，西瑞东北珍珠米免费抢',
        self::NORMAL_1 => '摇下20个，奖品优惠免费送',
    );
    var $title_in_window = array(
        self::MIHOUTAO1411 => '摇下100个，最高一箱猕猴桃免费领',
        self::RICE_201411 => '摇下50个，大米优惠券免费送',
        self::BTC1412 => '摇下100个，1箱万橙免费领',
        self::XIRUI1412 => '摇下20粒，西瑞东北珍珠米免费抢',
        self::NORMAL_1 => '摇下20个，奖品优惠免费送',
    );
    var $title_js_func = array(
        self::RICE_201411 => "'摇一摇免费兑稻花香大米券, 我已经有机会兑到'+total*10+'g五常稻花香大米啦 -- 城市里的乡下人腾讯nana分享爸爸种的大米-朋友说'",
//        self::MIHOUTAO1411 => "'摇一摇一起免费兑有机猕猴桃红包，我已经摇下'+total+'个猕猴桃，兑到'+ game_mihoutao_hongbao(total) +'元红包啦 -- 城市里的乡下人张慧敏分享有机种植眉县猕猴桃 -- 朋友说'",
        self::MIHOUTAO1411 => "'摇一摇一起免费兑有机猕猴桃红包，我已经摇下'+total+'个猕猴桃，兑到XX元红包啦 -- 城市里的乡下人张慧敏分享有机种植眉县猕猴桃 -- 朋友说'",
    );
    var $customized_view_files = array(
        self::XIRUI1412 => 'xirui_rice',
        self::NORMAL_1 => 'normal1',
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
        $id = $this->currentUser['id'];

        $res = array();

        $key = key_cache_sub($id);
        $subscribe_status = Cache::read($key);
        if (empty($result) || $subscribe_status == WX_STATUS_UNKNOWN ) {
            $this->loadModel('Oauthbind');
            $oauth = $this->Oauthbind->findWxServiceBindByUid($id);
            if (!empty($oauth)) {
                $this->loadModel('WxOauth');
                $uinfo = $this->WxOauth->get_user_info_by_base_token($oauth['oauth_openid']);
                if (!empty($uinfo)) {
                    $subscribe_status = ($uinfo['subscribe'] != 0 ? WX_STATUS_SUBSCRIBED : WX_STATUS_UNSUBSCRIBED);
                    Cache::write($key, $subscribe_status);
//                        $this->loadModel('User');
//                        $this->User->updateAll(array('wx_subscribe_status' => $subscribe_status), array('id' => $id));
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


    public function hours_limit() {
        $hour = date('G');
        $limits = array(
            8 => 10,
            9 => 10,
            10 => 5,
            11 => 5,
            12 => 5,
            13 => 10,
            14 => 5,
            15 => 5,
            16 => 5,
            17 => 5,
            18 => 10,
            19 => 5,
            20 => 5,
            21 => 10,
            22 => 5,
        );
        return empty($limits[$hour]) ? 0 : $limits[$hour];
    }

    public function xirui_times() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $key_followed = key_follow_brand_time('xirui', $uid);
        $success = false;
        if (Cache::read($key_followed) > 1) {
            $key_assigned_times = key_assigned_times('xirui', $uid);
            if (Cache::read($key_assigned_times) > 1) {
                $reason = 'already_assigned';
            } else {
                $success = true;
                Cache::write($key_assigned_times, time());
                $this->loadModel('AwardInfo');
                $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($uid, self::XIRUI1412);
                if (!empty($awardInfo)) {
                    $this->AwardInfo->updateAll(array('times' => 'times + 2',),  array('uid' => $uid, 'type' => self::XIRUI1412));
                }

                $total_times = $awardInfo['times'] + 2;
            }

        } else {
            $reason = 'not_follow';
        }
        $rtn = array('success' => $success, 'reason' => $reason, 'total_times' => $total_times);
        echo json_encode($rtn);
    }

    public function jp($gameType) {
        $this->autoRender = false;

        $uid = $this->currentUser['id'];

        $award_type = 0;
        $last = $this->Session->read('last_chou_jiang');
        $msg = '';
        if (time() - $last > 10 && ($gameType == self::NORMAL_1)
            && $this->is_weixin()
        ) {
            $this->Session->write('last_chou_jiang', time());
//
//            $hour_limit = $this->hours_limit();
//            if ($hour_limit > 0) {

                try {

                    $gameCfg = $this->GameConfig->findByGameType($gameType);
                    if (!empty($gameCfg) && $gameCfg['GameConfig']['game_end']) {
                        $dt = new DateTime($gameCfg['GameConfig']['game_end']);
                        if (mktime() - $dt->getTimestamp() > 0) {
                            $msg = 'game_end';
                        } else {
                            $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($uid, $gameType);
                            $not_spent = ($awardInfo && $awardInfo['got']) ? $awardInfo['got'] - $awardInfo['spent'] : 0;

                            $per_spent = 20;
                            if ($not_spent >= $per_spent) {
                                $this->loadModel('AwardResult');
                                $day = date(FORMAT_DATE);
                                $exchangeCount = 20;
                                $mt_rand = mt_rand(0, 20);

                                /** Xirui Code
                                $this->loadModel('GameXiruiCode');

                                $iAwarded = $this->GameXiruiCode->userIsAwarded($uid);
                                if (empty($iAwarded)  && $mt_rand < 10 ) {
                                    $first_type_award = array(1 => 13, 2 => 50);
                                    foreach ($first_type_award as $type => $limit) {
                                        $today_awarded = $this->GameXiruiCode->today_awarded($day, $type);
                                        if ($this->shouldLimit($today_awarded, $limit)) {
                                            continue;
                                        }

                                        $code = $this->GameXiruiCode->find('first', array('conditions' => array(
                                            'type' => $type,
                                            'uid' => 0
                                        )));
                                        if (empty($code)) {
                                            continue;
                                        }

                                        if ($this->GameXiruiCode->updateAll(array(
                                            'uid' => $uid,
                                            'type' => $type,
                                            'created' => '\''.date(FORMAT_DATETIME).'\''
                                        ), array('code' => $code['GameXiruiCode']['code'], 'uid' => 0))) {
                                            $this->exchangeCouponAndLog($uid, $not_spent, $exchangeCount, 1, $gameType, $awardInfo['id'],
                                                function ($uid, $operator, $source_log_id)  {}
                                            );
                                            $award_type = $type;
                                            $award_code = $code['GameXiruiCode']['code'];
                                            break;
                                        }
                                    }
                                }
                                */

                                $today_awarded = 0;
                                $iAwarded = 0;

                                if ($award_type == 0) {

                                    //award_limit: 1=>50, 2=>30, 3=>3000/5000, 4=>unlimited
                                    //18278  500
                                    //18279  300
                                    //18280  100
                                    $coupon_id_list = array_keys($this->coupon_steps);
                                    $coupon_name_list = array(
                                        '生活速递联盟',
                                        '天下农夫-朱晓宇',
                                        '腾讯rabbi',
                                        '阿莲妈妈',
                                        '铁棍山药-艳艳',
                                        '德庆贡柑－林玲',
                                    );
                                    $coupon_count = 1;

                                    $award_idx = 0;
                                    while(true) {
                                        $award_idx = $mt_rand % count($coupon_id_list);
                                        $award_type = $coupon_id_list[$award_idx];
                                        if ($award_type == 18705) {
                                            $count = $this->CouponItem->couponCount(18705);
                                            if ($count < 5) {
                                                break;
                                            }
                                        } else {
                                            break;
                                        }
                                    }
                                    $rotate = $this->coupon_steps[$award_type];
                                    $award_brand_name = $coupon_name_list[$award_idx];
                                    $so = $this->CouponItem;
                                    $weixin = $this->Weixin;
                                    $this->exchangeCouponAndLog($uid, $not_spent, $exchangeCount, $coupon_count, $gameType, $awardInfo['id'],
                                        function ($uid, $operator, $source_log_id) use ($so, $weixin, $award_type, $award_brand_name) {
                                            $so->addCoupon($uid, $award_type, $operator, $source_log_id);
                                            $so->id = null;
                                            $store = "在朋友说".$award_brand_name."店购买时使用";
                                            $validDesc = "有效期至2014年12月31日";
                                            $weixin->send_coupon_received_message($uid, 1, $store, $validDesc);
                                        }
                                    );
                                }
                                $not_spent -= $exchangeCount;
                                $logstr = "Choujian $uid : todayAwarded=$today_awarded, iAwarded=$iAwarded, award_type = $award_type, msg=" . $msg;
                                $this->log($logstr);
                            } else {
                                $msg = 'not_enough_100';
                                $logstr = "total_got=$not_spent, award_limit=" . $this->AWARD_LIMIT;
                            }
                        }
                    }
                }catch(Exception $e) {
                    $msg = '';
                    $this->log('error_jp: '.$e);
                }
//            } else {
//                $msg = 'time_error';
//            }
        } else {
            $logstr = 'too frequently';
        }

        echo json_encode(array('success' => true, 'award_type' => $award_type, 'brand_name' => $award_brand_name, 'rotate' => $rotate, 'logstr' => '', 'msg' => $msg, 'total' => $not_spent));
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

        $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($id, $gameType);
        $apple_count_snapshot = $awardInfo['got'];
        $exChangeSource = $this->getExchangeType($gameType);

        $can_exchange_apple_count = $apple_count_snapshot - $awardInfo['spent'];

        $sold_out = false;
        $coupon_count = 0;
        $ex_count_per_Item = 0;
        if ($gameType == self::RICE_201411) {
            if ($can_exchange_apple_count >= 50) {
                $coupon_count = intval($can_exchange_apple_count / 50);
                $ex_count_per_Item = 50 * $coupon_count;
                $this->exchangeCouponAndLog($id, $apple_count_snapshot, $ex_count_per_Item, $coupon_count, $exChangeSource, $awardInfo['id'],
                    function($uid, $operator, $source_log_id){
                        $this->CouponItem->addCoupon($uid, COUPON_TYPE_RICE_1KG, $operator, $source_log_id);
                        $this->CouponItem->id = null;
                    }
                );
                if ($coupon_count > 0) {
                    $store = "购买nana家大米时使用";
                    $validDesc = "有效期至2014年11月15日";
                    $this->Weixin->send_coupon_received_message($id, $coupon_count, $store, $validDesc);
                }
            }
        } else if ($gameType == self::BTC1412) {

            if ($can_exchange_apple_count >= 100) {
                $coupon_count = 2;
                $ex_count_per_Item = 50; //100也是扣除50，因为只给一张
                $total_ex_count = 2 * $ex_count_per_Item;
                $sharingPref = array(17725, 30);
            } else if ($can_exchange_apple_count >= 50) {
                $total_ex_count = $ex_count_per_Item = 50;
                $sharingPref = array(18095, 20);
                    $coupon_count = 1;
            } else if ($can_exchange_apple_count >= 30) {
                $total_ex_count = $ex_count_per_Item = 30;
                $sharingPref = array(17724, 15);
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
                                $store = "在黔阳冰糖橙店购买时使用(" . $toShareNum . '元)';
                                $validDesc = "有效期至2014年12月18日";
                                $weixin->send_coupon_received_message($uid, 1, $store, $validDesc);
//                            list($shareOfferId, $toShareNum) = $sharingPref;
//                            $added = $so->add_shared_slices($uid, $shareOfferId, $toShareNum);
//                            $so->log('add_shared_slices:uid='. $uid . ', shareOfferId='. $shareOfferId . ', toShareNum='. $toShareNum .', result='. $added);
//                            if (!empty($added))  {
//                                App::uses('CakeNumber', 'Utility');
//                            }
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

//            $this->loadModel('AwardResult');
//            $iAwared = $this->AwardResult->userIsAwarded($current_uid, $gameType);
//            $this->set('awarded', !empty($iAwared));

//            $this->loadModel('GameXiruiCode');
//            if ($this->GameXiruiCode->userIsAwarded($current_uid)) {
//                $gotCode = $this->GameXiruiCode->find('first', array('conditions' => array('uid' => $current_uid), 'order' => 'created asc'));
//                $this->set('awarded', $gotCode);
//            }

            $this->loadModel('Order');

            $start = $gameCfg['GameConfig']['game_start'];
            $oppu_log = $this->Order->query('select max(created) as latest from cake_game_btc_order_exchanges');
            if (!empty($oppu_log)) {
                if($oppu_log[0][0]['latest']){
                    $start = $oppu_log[0][0]['latest'];
                }
            }
//
            $cond = array('creator' => $current_uid, 'published' => PUBLISH_YES, 'deleted' => DELETED_NO, 'created >= ' => $start);
            $cond['status'] = array(ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_RECEIVED, ORDER_STATUS_SHIPPED);
            $order_ids = $this->Order->find('all', array(
                'conditions' => $cond,
                'fields' => array('id'),
            ));
            if (!empty($order_ids)) {
                $order_ids = Hash::extract($order_ids, '{n}.Order.id');
                $this->loadModel('Cart');
                $carts = $this->Cart->find('all', array(
                    'conditions' => array('order_id' => $order_ids, 'status' => CART_ITEM_STATUS_BALANCED),
                    'fields' => array('num')
                ));
                $total = 0;
                foreach($carts as $cart) {
                    $total += $cart['Cart']['num'];
                }
                if ($total > 0) {
                    $this->Order->query('insert into cake_game_btc_order_exchanges(times, uid, created) values('.$total.', '.$current_uid.', \''.date(FORMAT_DATETIME).'\')');
                    $rrr = $this->AwardInfo->updateAll(array('times' => ' times + '. ($total * 2)), array('type' => $gameType, 'uid' => $current_uid));
                }
            }

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

        if ($awardInfo['got'] >= 20) {
            $this->loadModel('CouponItem');
            $coupons = $this->CouponItem->find_coupon_item_by_type($current_uid, array_keys($this->coupon_steps));
            $this->set('coupons', $coupons);
        }


        $this->set('game_end', $this->is_game_end($gameCfg));
        if ($gameType == self::BTC1412) {
            $result = array();
            $this->fill_top_lists($gameType, $result, $current_uid);
            $this->set('top_list', json_encode($result));

            $result = array();
            $this->fill_today_award($gameType, $result);
            $this->set('award_list', json_encode($result));
        }
        $wxTimesLogModel = ClassRegistry::init('AwardWeixinTimeLog');
        $weixinTimesLog = $wxTimesLogModel->find('first', array('conditions' => array('uid' => $current_uid, 'type' => $gameType)));
        $pys_got = $this->gotWxTimesToday($weixinTimesLog, mktime());

        $this->set('today_got_wx', $pys_got);

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
//
//        if ($gameType != self::MIHOUTAO1411
//            && $gameType != self::BTC1412
//            && $gameType != self::XIRUI1412
//            && (is_array($iAwarded) && empty($iAwarded) && $total_got + $curr_got >= $this->AWARD_LIMIT)) {
//            $awardResult = array(
//                'uid' => $uid,
//                'type' => $gameType,
//                'finish_time' => date(FORMAT_DATETIME)
//            );
//            if (!$model->save($awardResult)) {
//                $this->log("update AwardResult failed:" . json_encode($awardResult));
//            };
//        }

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
        if (!$this->is_weixin()) {
            return 0;
        } else if (false/*$this->shouldLimit($todayAwarded, $dailyLimit)*/) {
            $left = $this->AWARD_LIMIT - $total_got;
            $limit = true;
            if ($left > 0) {
                if ($left <= 3) {
                    $ext = 100000;
                } else if ($left < 10){
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

        $times =10;

        for ($i = 0; $i < $times; $i++) {
            $mt_rand = mt_rand(0, intval($ext + $total_got));
            $this_got += ( $mt_rand >= 1 && $mt_rand <= 5 ? 1 : 0);
        }

        if ( $limit && ($total_got + $this_got) > $this->AWARD_LIMIT) {
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
     * @param array|int $include_uid_pos
     */
    private function fill_top_lists($gameType, &$result, $include_uid_pos = array()) {

        $listR = $this->AwardInfo->top_list($gameType);
        $updateTime = friendlyDate($listR[0], 'full');

        $user_pos = array();
        $user_total = array();
        if($include_uid_pos && !is_array($include_uid_pos)) {
            $include_uid_pos = array($include_uid_pos);
        }
        foreach($include_uid_pos as $uid) {
            $searched = array_search($uid, array_keys($listR[1]));
            $user_pos[$uid] = $searched === false ? -1 :  1 + $searched;
            $user_total[$uid] = $listR[1][$uid];
        }

        $cache_key = 'v_top_list_' . $listR[0];
        $top_list_cache = Cache::read($cache_key);
        if (empty($top_list_cache)) {
            $top_list = array();
            $count = 0;
            $uids = array();
            foreach ($listR[1] as $uid => $got) {
                if ($count++ >= 30) {
                    break;
                }
                $top_list[] = array($uid, $got);
                $uids[] = $uid;
            }
            $nameIdMap = $this->User->findNicknamesMap($uids);
            foreach ($top_list as &$list) {
                $list[0] = mb_substr(filter_invalid_name($nameIdMap[$list[0]]), 0, 7);
            }
            Cache::write($cache_key, json_encode($top_list));
        } else {
            $top_list = json_decode($top_list_cache);
        }

        $tt_list = array('list' => $top_list, 'update_time' => $updateTime, 'user_pos' => $user_pos, 'user_total' => $user_total);

        $result['top_list'] = $tt_list;
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
        $listR = $this->AwardResult->list_day_award($day, $gameType);
        $updateTime = friendlyDate($listR[0], 'full');

        $cache_key = 'v_today_award_list_' .$gameType . '_'. $day . '_' .$listR[0];
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
}