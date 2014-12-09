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

    var $uses = array('User', 'AppleAward', 'AwardInfo', 'TrackLog', 'CouponItem', 'ExchangeLog', 'GameConfig');

    public $components = array('Weixin');

    var $AWARD_LIMIT = 100;

    const EXCHANGE_RICE_SOURCE = 'apple_exchange_rice';
    const RICE_201411 = 'rice201411';
    const MIHOUTAO1411 = 'mihoutao1411';
    const BTC1412 = 'qinyBTC1412';

    /*
     * INSERT INTO `cake_game_configs` (`game_type`, `day_limit`, `created`, `modified`, `game_obj_name`, `game_end`, `game_start`) VALUES
    ('chengzi1411', 10, NULL, NULL, '橙子', '2014-11-17 23:59:59', ''),
    ('rice201411', 20, NULL, NULL, '苹果', '2014-11-15 23:59:59', ''),
    ('Choupg1411', 10, NULL, NULL, '苹果', '2014-11-17 23:59:59', ''),
    ('mihoutao1411', 0, NULL, NULL, '猕猴桃', '2014-12-03 23:59:59', '2014-11-26 00:00:00')
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
    );

    var $title_in_page = array(
        self::MIHOUTAO1411 => '摇下100个，最高1箱猕猴桃免费领',
        self::RICE_201411 => '摇下50个，大米优惠券免费送',
        self::BTC1412 => '摇下100个，1箱万橙免费领',
    );
    var $title_in_window = array(
        self::MIHOUTAO1411 => '摇下100个，最高一箱猕猴桃免费领',
        self::RICE_201411 => '摇下50个，大米优惠券免费送',
        self::BTC1412 => '摇下100个，1箱万橙免费领',
    );
    var $title_js_func = array(
        self::RICE_201411 => "'摇一摇免费兑稻花香大米券, 我已经有机会兑到'+total*10+'g五常稻花香大米啦 -- 城市里的乡下人腾讯nana分享爸爸种的大米-朋友说'",
        self::MIHOUTAO1411 => "'摇一摇一起免费兑有机猕猴桃红包，我已经摇下'+total+'个猕猴桃，兑到'+ game_mihoutao_hongbao(total) +'元红包啦 -- 城市里的乡下人张慧敏分享有机种植眉县猕猴桃 -- 朋友说'",
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

    var $in_pys = array(8, 578, 818, 819);

    var $sess_award_notified = "award-notified";
    var $time_last_query_key = 'award-new-times-last';

    public function hasNewTimes($gameType = KEY_APPLE_201410)
    {
        $this->autoRender = false;
        $r = $this->Session->read($this->time_last_query_key);

        $result = array();
        if ($gameType == self::BTC1412) {
            $listR = $this->AwardInfo->top_list($gameType);
            $result['update_time'] = friendlyDate($listR[0], 'full');
            $result['top_list'] = array();


            $count = 0;
            $uids = array();
            foreach($listR[1] as $uid => $got) {
                $result['top_list'][] = array($uid, $got);
                $uids[] = $uid;
                if ($count++ >= 30) {
                    break;
                }
            }
            $nameIdMap = $this->User->findNicknamesMap($uids);
            foreach($result['top_list'] as &$list) {
                $list[0] = mb_substr(filter_invalid_name($nameIdMap[$list[0]]), 0, 8);
            }
        }

        if ($r && $r > 1413724118 /*2014-10-19 21:00*/) {
            if (time() - $r < 5) {
                return json_encode(array('success' => false));
            }
            $logsToMe = $this->TrackLog->find('all', array('conditions' => array(
                'type' => $gameType,
                'to' => $this->currentUser['id'],
                'got' => ' > 0',
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

    public function top_list($gameType) {
        $this->autoRender = false;
        if ($gameType == self::BTC1412) {
            $r = $this->AwardInfo->top_list($gameType);
            echo json_encode($r);
        }
    }

    public function jp($gameType) {
        $this->autoRender = false;

        $uid = $this->currentUser['id'];

        $award_type = 0;
        $last = $this->Session->read('last_chou_jiang');
        $msg = '';
        if (time() - $last > 30 && ($gameType == self::BTC1412)) {
            $this->Session->write('last_chou_jiang', time());

            $gameCfg = $this->GameConfig->findByGameType($gameType);
            if (!empty($gameCfg) && $gameCfg['GameConfig']['game_end']) {
                $dt = new DateTime($gameCfg['GameConfig']['game_end']);
                if(mktime() - $dt->getTimestamp() > 0) {
                    $msg = 'game_end';
                }
            }  else {
                $awardInfo = $this->AwardInfo->getAwardInfoByUidAndType($uid, $gameType);
                $not_spent = ($awardInfo && $awardInfo['got']) ? $awardInfo['got'] - $awardInfo['spent'] : 0;

                if ($not_spent >= $this->AWARD_LIMIT) {
                    $this->loadModel('AwardResult');
                    $model = $this->AwardResult;
                    $todayAwarded = $model->todayAwarded(date(FORMAT_DATE), $gameType);
                    $iAwarded = $model->userIsAwarded($uid, $gameType);
                    $shouldLimit = $this->shouldLimit($todayAwarded, 6);
                    if (!$iAwarded && !$shouldLimit) {
                        $awardResult = array(
                            'uid' => $uid,
                            'type' => $gameType,
                            'finish_time' => date(FORMAT_DATETIME)
                        );
                        if (!$model->save($awardResult)) {
                            $this->log("Save AwardResult failed:" . json_encode($awardResult));
                        };

                        $award_type = 58;
                        $this->CouponItem->addCoupon($uid, 17652, $uid, 'game_' . $gameType . '_' . mktime());
                        $store = "购买黔阳冰糖橙时使用";
                        $validDesc = "有效期至2014年12月25日";
                        $this->Weixin->send_coupon_received_message($uid, 1, $store, $validDesc);

                    }
                    $logstr = "Choujian $uid : todayAwarded=$todayAwarded, iAwarded=$iAwarded, shouldLimit=$shouldLimit";
                    $this->log($logstr);
                } else {
                    $msg = 'not_enough_100';
                    $logstr = "total_got=$not_spent, award_limit=" . $this->AWARD_LIMIT;
                }
            }
        } else {
            $logstr = 'too frequently';
        }

        echo json_encode(array('success' => true, 'award_type' => $award_type, 'logstr' => '', 'msg' => $msg, 'total' => $not_spent));
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
        $exchangeCount = 0;
        if ($gameType == self::RICE_201411) {
            if ($can_exchange_apple_count >= 50) {
                $coupon_count = intval($can_exchange_apple_count / 50);
                $exchangeCount = 50 * $coupon_count;
                $this->exchangeCouponAndLog($id, $apple_count_snapshot, $exchangeCount, $coupon_count, $exChangeSource, $awardInfo['id'],
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
        } else if ($gameType == self::MIHOUTAO1411) {

            //9,10,11,12

            $sharing = array(
                100 => array(12, 2000 * 20),
                80 => array(11, 1500 *  15),
                60 => array(10, 1000 * 10),
                30 => array(9, 500 * 5),
            );
            if ($can_exchange_apple_count >= 100) {
                $exchangeCount = 100;
            } else if ($can_exchange_apple_count >= 80) {
                $exchangeCount = 80;
            } else if ($can_exchange_apple_count >= 60) {
                $exchangeCount = 60;
            } else if ($can_exchange_apple_count >= 30) {
                $exchangeCount = 30;
            }

            if ($exchangeCount > 0) {
                $sharingPref = $sharing[$exchangeCount];
                if (!empty($sharingPref)) {
                    $coupon_count = 1;
                    $so = ClassRegistry::init('ShareOffer');
                    $weixin = $this->Weixin;
                    $this->exchangeCouponAndLog($id, $apple_count_snapshot, $exchangeCount, $coupon_count, $exChangeSource, $awardInfo['id'],
                        function ($uid, $operator, $source_log_id) use ($sharingPref, $so, $weixin) {
                            list($shareOfferId, $toShareNum) = $sharingPref;
                            $added = $so->add_shared_slices($uid, $shareOfferId, $toShareNum);
                            $so->log('add_shared_slices:uid='. $uid . ', shareOfferId='. $shareOfferId . ', toShareNum='. $toShareNum .', result='. $added);
                            if (!empty($added))  {
                                App::uses('CakeNumber', 'Utility');
                                $weixin->send_packet_received_message($uid, CakeNumber::precision($toShareNum/100), "眉县有机猕猴桃红包");
                            }
                        }
                    );
                }
            }
        }

        if ($exchangeCount > 0) {
            $result['exchange_apple_count'] = $exchangeCount;
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

    private function _addNotify($uname, $added)
    {
        $this->Session->write($this->sess_award_notified, array('name' => $uname, 'got' => $added));
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
            $this->_addNotify(filter_invalid_name($friend['User']['nickname']), $shouldAdd);
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

        if ($gameType != self::BTC1412) {

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

        } else {
            $this->set('helpMe', $friendsHelpMe);

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
                    $rrr = $this->AwardInfo->updateAll(array('times' => ' times + '. ($total * 10)), array('type' => $gameType, 'uid' => $current_uid));
                }
            }
        }

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
//        $awardItems = array();
//        $awardInfos = $this->AwardInfo->find('list', array(
//            'conditions' => array('got >=' => $this->AWARD_LIMIT),
//            'fields' => array('uid', 'got')
//        ));
//        if (!empty($awardInfos)) {
//            $nicknamesMap = $this->User->findNicknamesMap(array_keys($awardInfos));
//            foreach ($awardInfos as $uid => $got) {
//                if (array_search($uid, $this->in_pys) === false) {
//                    $awardItems[] = array('nickname' => filter_invalid_name($nicknamesMap[$uid]), 'got' => $got, 'company' => $this->companies[$uid]);
//                }
//            }
//        }
//        $this->set('awarded', $awardItems);

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
        $this->set('got_apple', 0);
        $this->_updateLastQueryTime(time());


        $this->set('game_end', $this->is_game_end($gameCfg));

        $wxTimesLogModel = ClassRegistry::init('AwardWeixinTimeLog');
        $weixinTimesLog = $wxTimesLogModel->find('first', array('conditions' => array('uid' => $current_uid, 'type' => $gameType)));
        $this->set('got_wx_sub_times', $this->gotWxTimesToday($weixinTimesLog, mktime()));
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

        if ($gameType != self::MIHOUTAO1411
            && $gameType != self::BTC1412
            && (is_array($iAwarded) && empty($iAwarded) && $total_got + $curr_got >= $this->AWARD_LIMIT)) {
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
     * @param $gameType
     * @return int
     */
    private function randGotApple($todayAwarded, $total_got, $dailyLimit, $gameType) {
        $this_got = 0;
        $ext = 0;
        $limit = false;
        if (!$this->is_weixin()) {
            return 0;
        } else if ($this->shouldLimit($todayAwarded, $dailyLimit)) {
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

        if ($total_got < 90 && $total_got>30) {
            $ext -= 40;
        } else if ($total_got > 150) {
            $ext += 3 * $total_got;
        }

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
     * @param $id
     * @param $apple_count_snapshot
     * @param $exchangeCount
     * @param $coupon_count
     * @param $exChangeSource
     * @param $awardInfoId
     * @param $couponFunc
     */
    private function exchangeCouponAndLog($id, $apple_count_snapshot, $exchangeCount, $coupon_count, $exChangeSource, $awardInfoId, $couponFunc) {
        $latest_exchange_log_id = $this->ExchangeLog->addExchangeLog($id, $apple_count_snapshot,
            $exchangeCount, $coupon_count, $exChangeSource);

        $operator = $this->currentUser['id'];
        if($latest_exchange_log_id) {
            $awardInfoModel = ClassRegistry::init('AwardInfo');
            if ($awardInfoModel->updateAll(array('spent ' => 'spent + '. $exchangeCount, ), array('id' => $awardInfoId))) {
                for ($i = 1; $i <= $coupon_count; $i++) {
                    $couponFunc($id, $operator, $exChangeSource . "_" . $latest_exchange_log_id . '_' . $i);
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
}