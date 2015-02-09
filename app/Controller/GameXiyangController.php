<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/12/14
 * Time: 3:57 PM
 */
class GameXiyangController extends AppController
{

    const DAILY_TIMES_SUB = 2;
    const TIMES_INITIAL = 2;

    var $name = "Xiyang";

    var $uses = array('User', 'AwardResult', 'AwardInfo', 'TrackLog', 'CouponItem', 'ExchangeLog', 'GameConfig');

    public $components = array('Weixin');

    var $AWARD_LIMIT = 200;

    const EXCHANGE_RICE_SOURCE = 'apple_exchange_rice';
    const GAME_XIYANG = 'xiyang';

    const COUPON_YTL_FIRST = 29037;
    const COUPON_YTL_SEC = 29036;
    const NEED_MOBILE_LEAST = 33;
    const AWARD_SECOND_LEAST = 30;

    const AW_BAOJIA_1 = 'baojia-1';
    const AW_FQSM_1 = 'fqsm-1';
    const AW_YTL_1 = 'ytv-1';
    const AW_WGWG_1 = 'wgwg-1';

    const AW_BAOJIA_2 = 'baojia-2';
    const AW_FQSM_2 = 'fqsm-2';
    const AW_YTL_2 = 'ytv-2';
    const AW_WGWG_2 = 'wgwg-2';

    var $coupon_info = array(
        self::AW_BAOJIA_1 => array(
            'intro' => '',
            'store' => '', //适用范围
            'rule' => '',
            'click_intro' => '',
            'coupon_url' => '',
        )
    );

    var $wx_accounts = array('wgwg','fuqiaoshangmen','yuantailv');

    var $wx_accounts_map = array('wgwg'=>'万国万购','fuqiaoshangmen'=>'富侨上门','yuantailv'=>'原态绿');

    var $wx_accounts_init_map = array('init-wgwg'=>'wgwg','init-fqsm'=>'fuqiaoshangmen','init-ytl'=>'yuantailv','init-bjzc'=>'baojiazuche','init-pys'=>'pyshuo');

    var $xw_accounts_map_page = array('wgwg' => 'http://mp.weixin.qq.com/s?__biz=MjM5ODgyMjQ1Nw==&mid=203560202&idx=1&sn=bcc16db724964a9ad66ad9b265dc9570#rd',
        'fuqiaoshangmen' => 'http://mp.weixin.qq.com/s?__biz=MjM5Njc3Mzg5NQ==&mid=205483700&idx=1&sn=289d1921688989587189c2fca0e7434b',
        'yuantailv' => 'http://shop984425.koudaitong.com/v2/showcase/mpnews?alias=9dlutzi1&spm=m1423443892620694414615768.scan.1380097986&from=groupmessage&isappinstalled=0',
        'award_bjzc_sec' => 'http://m.baojia.com/compaign/coupon/offline/?utm_source=moka_zuke',
        'award_fqsm_sec' => 'http://mp.weixin.qq.com/s?__biz=MjM5Njc3Mzg5NQ==&mid=205483700&idx=1&sn=289d1921688989587189c2fca0e7434b',
);

    var $page_titles = array('wgwg'=>'羊年赢大奖,马油面霜免费领','fuqiaoshangmen'=>'羊年赢大奖,免费上门按摩','yuantailv'=>'羊年赢大奖,Love柚免费领','pyshuo'=>'羊年赢大奖,悍马免费开','default'=>'羊年赢大奖,悍马免费开');

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
        $this->pageTitle = '朋友说联合商家-宝驾-富侨上门-原态绿-万国万购';
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

    var $sess_award_notified = "award-notified";
    var $time_last_query_key = 'award-new-times-last';

    public function hasNewTimes($gameType = KEY_APPLE_201410)
    {
        $this->autoRender = false;
        $r = $this->Session->read($this->time_last_query_key);

        $current_uid = $this->currentUser['id'];
        $result = array();
//            $this->fill_top_lists($gameType, $result, $current_uid);
            $total_help_me = $this->TrackLog->find('count', array(
                'conditions' => array('to' => $this->currentUser['id'], 'type' => $gameType, 'got' > 0),
            ));
            $result['total_help_me'] = $total_help_me;
//            $this->fill_today_award($gameType, $result);

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

            $result['left_sec'] = $this->left_sec_coupon();
            $result['first_waiting'] = $this->get_first_waiting($gameType);

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

    //订阅其他号 每天加机会
    public function assignOterWXSubscribeTimes($gameType = KEY_APPLE_201410){
        $this->autoRender=false;
        $uid=$this->currentUser['id'];
        $res = array();
        $from = $_REQUEST['from'];
        if($from!=null){
            $result = $this->add_follow_other_account_times($from,$uid,$gameType,$res);
        }else{
            $result = self::WX_TIMES_ASSIGN_RETRY;
        }
        $res['result'] = $result;
        echo json_encode($res);
    }

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

    public function jp($gameType) {
        $this->autoRender = false;
    }

    public function exchange_coupon($gameType = KEY_APPLE_201410)
    {
        $this->autoRender = false;
        $id = $this->currentUser['id'];
        $result = array();

        if (empty($id)) {
            echo json_encode(array('result' => 'need_login'));
            return;
        }

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
        if ($id == 632 || $this->is_weixin()) {
            if (($expect == self::AW_BAOJIA_1 || $expect == self::AW_FQSM_1 || $expect == self::AW_YTL_1 || $expect == self::AW_WGWG_1)
                && $can_exchange_apple_count >= 50) {
                $can_go = true;
                if ($expect == self::AW_BAOJIA_1 || $expect == self::AW_FQSM_1 ) {
                    $can_go = ('北京' == $this->get_user_province());
                } else if ($expect == self::AW_YTL_1) {
                    $rnd = mt_rand(0, 7);
                    $user_province = $this->get_user_province();
                    $can_go = ('北京' == $user_province || $rnd < 2);
                }
                if ($can_go) {
                    $this->loadModel('Seckilling');
                    $killed = $this->Seckilling->sec_kill($id, self::GAME_XIYANG, $expect, time());
                    if (!empty($killed)) {
                        $this->log("exchange_coupon_first_do_coupon: uid=" . $id . ', killed='. json_encode($killed));
                        $coupon_count = 1;
                        $ex_count_per_Item = 50;
                        $total_ex_count = $ex_count_per_Item;
                        $award_data = $killed['Seckilling']['code'];

                        if ($expect == self::AW_YTL_1) {
                            $awarded_coupon_id = self::COUPON_YTL_FIRST;
                        }
                    }
                }

                if ($coupon_count < 1) {
                    $sold_out = true;
                }
            } else if ( ($expect == self::AW_BAOJIA_2
                    || $expect == self::AW_FQSM_2
                    || $expect == self::AW_WGWG_2
                    || $expect == self::AW_YTL_2)
                    && $can_exchange_apple_count >= self::AWARD_SECOND_LEAST) {

                $success = true;
                if ($expect == self::AW_YTL_2) {
                    $awarded_coupon_id = self::COUPON_YTL_SEC;
                } else if ($expect == self::AW_FQSM_2) {
                    $mobile = $this->Session->read('Auth.User.mobilephone');
                    if (empty($mobile)) {
                        $success = false;
                        $fail_reason = 'need_mobile';
                    } else {
//                        $curl = curl_init();
//                        $options = array(
//                            CURLOPT_URL => 'http://w.iyishengyuan.com/index/getredpaper.html?mobilephone=' . $mobile . '&sign=' . md5($mobile . 'ppsiyishengyuan'),
//                            CURLOPT_CUSTOMREQUEST => 'GET',
//                            CURLOPT_HEADER => false,
//                            CURLOPT_RETURNTRANSFER => true,
//                            CURLOPT_TIMEOUT => 30
//                        );
//                        curl_setopt_array($curl, ($options));
//                        $json = curl_exec($curl);
//                        curl_close($curl);
//
//                        $resu = json_decode($json, true);
//                        if (!empty($resu)) {
//                            if ($resu['state'] != 0) {
//                                $success = false;
//                                $fail_reason = $resu['msg'];
//                            }
//                        } else {
//                            $success = false;
//                            $fail_reason = '获取富侨上门发奖接口结果失败';
//                        }

                        $this->log("querying FQSM second award of mobile:" . $mobile . ', result=' . $json);
                    }
                } else if ($expect == self::AW_WGWG_2) {
                    $this->loadModel('Seckilling');
                    $killed = $this->Seckilling->sec_kill($id, self::GAME_XIYANG, $expect, time());
                    if (!empty($killed)) {
                        $this->log("exchange_coupon_second_do_coupon: uid=" . $id . ', killed='. json_encode($killed));
                        $coupon_count = 1;
                        $ex_count_per_Item = 30;
                        $total_ex_count = $ex_count_per_Item;
                        $award_data = $killed['Seckilling']['code'];
                    }
                }

                if ($success) {
                    $total_ex_count = $ex_count_per_Item = 30;
                    $coupon_count = 1;
                }
            }

            if ($coupon_count > 0) {
                $so = $this->CouponItem;
                $weixin = $this->Weixin;
                $this->exchangeCouponAndLog($id, $apple_count_snapshot, $ex_count_per_Item, $coupon_count, $exChangeSource, $awardInfo['id'],
                    function ($uid, $operator, $source_log_id) use ($awarded_coupon_id, $so, $weixin) {
                        if (!empty($awarded_coupon_id)) {
                            $so->addCoupon($uid, $awarded_coupon_id, $operator, $source_log_id);
                        }
                        $so->id = null;
                    }
                );
                $this->loadModel('AwardResult');
                $awardResult = array(
                    'uid' => $id,
                    'type' => $gameType,
                    'award_type' => $expect,
                    'award_data' => empty($award_data)?'':$award_data,
                    'finish_time' => date(FORMAT_DATETIME),
                );
                if (!$this->AwardResult->save($awardResult)) {
                    $this->log("update AwardResult failed:" . json_encode($awardResult));
                } else {
                    $array = $this->coupon_info[$expect];
                    $first_intro = $array['intro'];
                    $store = $array['store'];
                    $rule = $array['rule'];
                    $click_intro = $array['click_intro'];
                    $coupon_url = $array['coupon_url'];
                    $weixin->send_coupon_message_on_received($id, $store, $rule, $coupon_url, $first_intro, $click_intro);
                }
            }
        }

        if ($coupon_count > 0) {
            $result['exchange_apple_count'] = $total_ex_count;
            $result['coupon_count'] = $coupon_count;
            $result['result'] = "just-got";
        }else{
            $result['result'] = $sold_out ? 'sold_out' : "goon";
            $result['reason'] = $fail_reason;
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
        $dailyHelpLimit = 0; //($gameType == self::GAME_JIUJIU ? 5 : 0);
        $current_uid = $this->currentUser['id'];
        //add follow other account log
        //prevent wx from param
        $f = $_REQUEST['f'];
        if($f==null){
            $from = $_REQUEST['from'];
            $token=$_REQUEST['token'];
            if($from!=null){
                if(strpos($from,'init')===false){
                    $this->Session->write('game_from',$from);
                    if($this->add_follow_other_account_log($from,$current_uid,$token)){
                        $result = $this->add_follow_other_account_times($from,$current_uid,$gameType);
                        if($result == self::WX_TIMES_ASSIGN_JUST_GOT){
                            $follow_tip_info = '您关注'.$this->wx_accounts_map[$from].'成功，增加'.self::DAILY_TIMES_SUB.'机会。';
                            $this->Session->write('follow_tip_info',$follow_tip_info);
                        }
                    }
                }else{
                    $this->Session->write('game_from',$this->wx_accounts_init_map[$from]);
                }
            }
        }else{
            $this->Session->write('game_from',$this->wx_accounts_init_map[$f]);
        }
        $from = $this->Session->read('game_from');
        if($this->page_titles[$from]!=null){
            $this->pageTitle = $this->page_titles[$from];
        }else{
            $this->pageTitle = $this->page_titles['default'];
        }
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

        $this->set('game_type', $gameType);

        $this->set('game_obj_name', $gameCfg['GameConfig']['game_obj_name']);

        $this->set('title_func', $this->title_js_func[$gameType]);
        $title_in_page = $this->title_in_page[$gameType];
        $this->set('title_in_page', $title_in_page);
        $this->set('game_least_change', $this->game_least_change[$gameType]);
        $this->set('treeName', $this->treeNames[$gameType]);
        $this->set('treeStatic', $this->treeStaticNames[$gameType]);

        $this->set('hideNav', true);
        $this->set('noFlash', true);

        $this->setTotalVariables($awardInfo);
        $this->set('game_user_total', $awardInfo['got']);
        $this->_updateLastQueryTime(time());

        if ($awardInfo['got'] >= self::AWARD_SECOND_LEAST) {
            $awardResults = $this->AwardResult->find_my_award_results($current_uid, $gameType);
            if (!empty($awardResults)) {
                $awards_by_type = Hash::combine($awardResults, '{n}.AwardResult.award_type', '{n}.AwardResult');
                $this->set('award_by_type', $awards_by_type);
            }
        }

        $this->set('game_end', $this->is_game_end($gameCfg));
        $result = array();
        $this->fill_latest_awards($gameType, $result);
        $this->set('award_list', $result['latest_awards']);

        $wxTimesLogModel = ClassRegistry::init('AwardWeixinTimeLog');
        $weixinTimesLog = $wxTimesLogModel->find('first', array('conditions' => array('uid' => $current_uid, 'type' => $gameType)));
        $pys_got = $this->gotWxTimesToday($weixinTimesLog, mktime());

        $this->set('today_got_wx', $pys_got);
        $subscribe_status = user_subscribed_pys($current_uid);
        $this->set('user_subscribed', $subscribe_status == WX_STATUS_SUBSCRIBED);

        global $order_after_paid_status;

        $this->loadModel('Order');
        $found_order = $this->Order->find('first', array(
            'conditions' => array('status' => $order_after_paid_status)
        ));

        $this->set('has_no_orders', empty($found_order));

        $this->set('left_sec', $this->left_sec_coupon());
        $this->set('first_waiting', $this->get_first_waiting($gameType));

        $followLogs = $this->get_user_follow_other_account_info($current_uid);
        $followLogs = Hash::combine($followLogs,'{n}.FollowOtherAccountLog.from','{n}.FollowOtherAccountLog');
        $this->set('follow_infos',$followLogs);

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
            $need_mobile = $gameType == self::GAME_XIYANG && $got >= self::NEED_MOBILE_LEAST && empty($mobile);
            echo json_encode(array('success' => true, 'got_apple' => $apple,
                'total_apple' => $total_apple, 'total_times' => $totalAwardTimes,
                'need_login' => $need_login, 'need_mobile' => $need_mobile));
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
        $mobileNum = $this->Session->read('Auth.User.mobilephone');
        if (!$this->is_weixin() || (empty($mobileNum) && $total_got > self::NEED_MOBILE_LEAST)) {
            return 0;
        }

        $times = 10;
        $ext =  ($total_got >= self::AWARD_SECOND_LEAST ? 100 : 20);

        for ($i = 0; $i < $times; $i++) {
            $mt_rand = mt_rand(0, intval($ext + $total_got));
            $this_got += ($mt_rand >= 1 && $mt_rand <= 5 ? 1 : 0);
        }

        $new_got = $total_got + $this_got;

        //avoid get twice big award
        if ($new_got >= 90) {
            $this_got = 0;
        }

        //give more to new user
        if ($new_got  < 5) {
            $this_got = max($this_got, 2);
        }


        return $this_got;
    }

    /**
     * @param $gameType
     * @return string
     */
    private function getExchangeType($gameType) {
        return $gameType;
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

        $now_time = time();

        $cache_key = 'v_latest_list_' . $gameType . '_' . date('Y-m-d Hi', $now_time);
        $top_list_cache = Cache::read($cache_key);
        if (empty($top_list_cache)) {
            $updateTime = friendlyDate($now_time, 'full');
            $award_list = array();
            $listR = $this->AwardResult->find_latest_award_results($gameType, 100);
            $uids = array();
            foreach ($listR as $res) {
                $bind_user = $res['CouponItem']['bind_user'];
                $coupon_id = $res['CouponItem']['coupon_id'];
                $created = friendlyDateFromStr($res['CouponItem']['created']);
                $award_list[] = array($bind_user, $coupon_id == self::COUPON_YTL_FIRST ? 'first' : 'sec', $created);
                $uids[] = $bind_user;
            }
            $nameIdMap = $this->User->findNicknamesMap($uids);
            foreach ($award_list as &$list) {
                $list[0] = mb_substr(filter_invalid_name($nameIdMap[$list[0]]), 0, 7);
            }
            Cache::write($cache_key, json_encode(array('list' => $award_list, 'update' => $updateTime)));
        } else {
            $arr = json_decode($top_list_cache, true);
            $award_list = $arr['list'];
            $updateTime = $arr['update'];
        }

        $tt_list = array('list' => $award_list, 'update_time' => $updateTime);

        $result['latest_awards'] = $tt_list;
    }

    /**
     * @return bool
     */
    private function get_user_province() {
        $mobileNum = $this->Session->read('Auth.User.mobilephone');
        $this->loadModel('MobileInfo');
        $info = $this->MobileInfo->get_province($mobileNum);
        $this->log('city_by_phone:'.$mobileNum.", info=".$info);
        return $info;
    }

    /**
     * @return int
     */
    private function left_sec_coupon() {
        return 300 - $this->CouponItem->couponCountDaily(self::COUPON_YTL_SEC, time());
    }

    /**
     * @param $gameType
     * @return mixed
     */
    private function get_first_waiting($gameType) {
        return $this->AwardInfo->count_ge_no_spent_50($gameType);
    }


    public function redirect_to_account_follow_page(){
        $to = $_REQUEST['to'];
        if(in_array($to,$this->wx_accounts)){
            $this->redirect($this->xw_accounts_map_page[$to]);
        }
    }

    public function rd($page) {
        $url = $this->xw_accounts_map_page[$page];
        $this->redirect(empty($url) ? '/' : $url);
    }

    /**
     * @param $from
     * @param $uid
     * @param null $token
     * @param null $wx_account
     * @return null
     */
    private function add_follow_other_account_log($from,$uid,$token=null,$wx_account=null){
        if(in_array($from,$this->wx_accounts)){
            $followOtherAccountLog = ClassRegistry::init('FollowOtherAccountLog');
            $followLog = $followOtherAccountLog->find('first',array(
                'conditions'=>array(
                    'uid'=>$uid,
                    'from'=>$from,
                )
            ));
            $now = date('Y-m-d H:i:s');
            if(!$followLog){
                //add
               return $followOtherAccountLog->save(array('from'=>$from,'uid'=>$uid,'follow_token'=>$token,'wx_account'=>$wx_account,'created'=>$now));
            }

            return null;
// update token
//            else{
//                //update
//                $id=$followLog['FolllowOtherAccountLog']['id'];
//                $followOtherAccountLog->updateAll(array(),array('id'=>$id));
//            }
        }
    }

    private function is_follow_other_account($from,$uid,$token=null,$wx_account=null){
        $followOtherAccountLog = ClassRegistry::init('FollowOtherAccountLog');
        $followLog = $followOtherAccountLog->find('first',array(
            'conditions'=>array(
                'uid'=>$uid,
                'from'=>$from,
                'follow_token'=>$token,
                'wx_account'=>$wx_account
            )
        ));
        return $followLog;
    }

    private function get_user_follow_other_account_info($uid){
        $followOtherAccountLog = ClassRegistry::init('FollowOtherAccountLog');
        $followLogs = $followOtherAccountLog->find('all',array(
            'conditions'=>array(
                'uid'=>$uid,
                'from'=>$this->wx_accounts,
            )
        ));
        return $followLogs;
    }

    private function add_follow_other_account_times($from,$uid,$gameType,&$res=null){
        $follow_log=$this->is_follow_other_account($from,$uid);
        if($follow_log!=null){
            $wxTimesLogModel = ClassRegistry::init('AwardWeixinTimeLog');
            $weixinTimesLog = $wxTimesLogModel->find('first', array('conditions' => array('uid' => $uid, 'type' => $gameType,'from'=>$from)));
            $now = mktime();
            if ($this->gotWxTimesToday($weixinTimesLog, $now)) {
                $result = self::WX_TIMES_ASSIGN_GOT;
                if(is_array($res)){
                    $res['got_time'] = date('H点i分', $weixinTimesLog['AwardWeixinTimeLog']['last_got_time']);
                }
            } else {
                $log = array();
                $log['uid'] = $uid;
                $log['last_got_time'] = $now;
                $log['type'] = $gameType;
                $log['from']=$from;
                if (!empty($weixinTimesLog)) {
                    $wxTimesLogModel->id = $weixinTimesLog['AwardWeixinTimeLog']['id'];
                }
                if ($wxTimesLogModel->save(array('AwardWeixinTimeLog' => $log)) !== false) {
                    $cond = array('uid' => $uid, 'type' => $gameType);
                    $this->AwardInfo->updateAll(array('times' => 'times + ' . self::DAILY_TIMES_SUB,), $cond);
                    if(is_array($res)){
                        $awardInfo = $this->AwardInfo->find('first', array('conditions' => array('uid' => $uid, 'type' => $gameType)));
                        $res['total_times'] = $awardInfo['AwardInfo']['times'];
                    }
                    $result = self::WX_TIMES_ASSIGN_JUST_GOT;
                } else {
                    $result = self::WX_TIMES_ASSIGN_RETRY;
                }
            }
        }else{
            $result = self::WX_TIMES_ASSIGN_NOT_SUB;
        }
        return $result;
    }
}