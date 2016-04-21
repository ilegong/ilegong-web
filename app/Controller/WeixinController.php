<?php
/**
 * 腾讯问问自助问答
 * @author arlonzou
 * @2012-11-2下午3:30:18
 */
define("WEIXIN_TOKEN", "sUrjPDH8xus2d4JT");
define('FROM_WX_SERVICE', 1);
define('FROM_WX_SUB', 2);

class WeixinController extends AppController {

	var $name = 'Weixin';

    var $uses = array('Oauthbind', 'User', 'UserSubReason', 'Candidate', 'CandidateEvent', 'VoteSetting');

    var $components = array('WeixinUtil');
	
	public function beforeFilter(){
		parent::beforeFilter();
		$this->layout = false;
	}
	
	public function msg(){
		if(!empty($_GET["echostr"])){
			$this->valid();
		}
		else{
			$this->responseMsg(FROM_WX_SUB);
		}
		exit;
	}

	public function msg_service(){
		if(!empty($_GET["echostr"])){
			$this->valid();
		}
		else{
			$this->responseMsg(FROM_WX_SERVICE);
		}
		exit;
	}
	
	public function valid()
	{
		$echoStr = $_GET["echostr"];
		if($this->checkSignature()){
			echo $echoStr;
		}else{
			echo "invalid request: echo=$echoStr";
			CakeLog::error("invalid request: echo=$echoStr");
		}
	}

	private function responseMsg($from = '')
	{
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr))
		{
			$ret = xml_to_array($postStr);
			$req = $ret["xml"];

			$me = $req["ToUserName"];
			$user = $req["FromUserName"];
			//$type = $req["MsgType"];

            $this->log($req, LOG_DEBUG);

            $host3g = (WX_HOST);

            $openId = trim($req['FromUserName']);
            if (!empty($openId)) {
                if ($from == FROM_WX_SERVICE) {
                    $uid = $this->Oauthbind->findUidByWx($openId);
                }
            }

			$input = "";
			if(!empty($req['Event'])){
				if($req['Event']=='subscribe'){ //订阅
                    $this->log('On wechat event subscribe: '.$from.trim($req['FromUserName']), LOG_INFO);
                    $process_result = $this->WeixinUtil->process_user_sub_weixin($from, $uid, $openId);
                    $replay_type = $process_result['replay_type'];
                    $content = $process_result['content'];
                    if ($replay_type == 0) {
                        echo $this->newArticleMsg($user, $me, $content);
                    }
                    if ($replay_type == 1) {
                        $url = $process_result['url'];
                        echo $this->newTextMsg($user, $me, $content.'，<a href="'.$url.'">点击查看详情</a>');
                        //echo $this->newTextMsg($user, $me, $content.'<a href=\"' + $url + '\" >点击查看详情<\/a>');
                    }
                    if($from == FROM_WX_SERVICE){
                        $key = key_cache_sub($uid,'kfinfo');
                        $subscribe_array = json_decode(Cache::read($key),true);
                        $ticket = $req['Ticket'];
                        if (!empty($ticket) && $ticket == SCAN_TICKET_QRCODE_PAY) {
                            $this->log('扫码支付跳转关注-1', LOG_INFO);
                        }
                        if(!empty($subscribe_array)){
                            $this->loadModel('WxOauth');
                            $body=array();
                            if(array_key_exists('groupId',$subscribe_array)){
                                $body=array(
                                    'touser'=>$user,
                                    "msgtype"=>"text",
                                    "text"=>array(
                                        "content"=>'您在［朋友说］参加的［组团］活动成功<a href=\"'.$this->loginServiceIfNeed($from, $user, "http://".WX_HOST."/groupons/join/".$subscribe_array['groupId']).'\">查看详情<\/a>'
                                    )
                                );

                            }elseif(array_key_exists('orderId',$subscribe_array)){
                                $body=array(
                                    'touser'=>$user,
                                    "msgtype"=>"text",
                                    "text"=>array(
                                        "content"=>'您在［朋友说］购买的商品已支付成功，<a href=\"'.$this->loginServiceIfNeed($from, $user, "http://".WX_HOST."/orders/detail/".$subscribe_array['orderId']).'\">查看详情<\/a>'
                                    )
                                );
                            }elseif(array_key_exists('follow',$subscribe_array)){
                                $body=array(
                                    'touser'=>$user,
                                    "msgtype"=>"text",
                                    "text"=>array(
                                        "content"=>'关注成功！当您的订单状态有变化时系统将通过微信消息通知您。 <a href=\"'.$this->loginServiceIfNeed($from, $user, oauth_wx_goto('CLICK_URL_MINE', WX_HOST)).'\">查看您的订单<\/a>'
                                    )
                                );
                            }else if(array_key_exists('pid',$subscribe_array)){
                                $key = key_cache_sub($uid,'spring');
                                $data = $subscribe_array['pid'];
                                Cache::write($key, $data);
                                $body=array(
                                    'touser'=>$user,
                                    "msgtype"=>"text",
                                    "text"=>array(
                                        "content"=>'关注成功！领取优惠券成功。 <a href=\"'.$this->loginServiceIfNeed($from, $user, "http://".WX_HOST."/categories/spring").'\">去年货专场<\/a>'
                                    )
                                );
                            }
                            if(!empty($body)){
                                foreach ( $body['text'] as $key => $value ) {
                                    $body['text'][$key] = urlencode($value);
                                }
                                $body = urldecode(json_encode($body));
                                $this->WxOauth->send_kefu($body);
                            }
                        }
                    }
					exit;
				} else if ($req['Event'] == 'CLICK') {
                    $input = $req['EventKey'];
                } else if ($req['Event'] == 'unsubscribe') {
                    $this->log('On wechat event unsubscribe: '.$from.trim($req['FromUserName']), LOG_INFO);
                    if ($from == FROM_WX_SERVICE) {
                        $uid = $this->Oauthbind->findUidByWx(trim($req['FromUserName']));
                        if ($uid) {
                            Cache::write(key_cache_sub($uid), WX_STATUS_UNSUBSCRIBED);
                        }
                    }
                } else if ( strtoupper($req['Event']) == 'SCAN') {
                    if ($from == FROM_WX_SERVICE) {
                        $ticket = $req['Ticket'];
                        if ($ticket == SCAN_TICKET_CAOMEI) {
                            echo $this->newTextMsg($user, $me, '点击<a href="http://'.$host3g.'/products/20150120/you_ji_hong_yan_cao_mei_tuan_gou.html">查看草莓详情</a>');
                            return;
                        }
                        if ($ticket == SCAN_TICKET_QRCODE_PAY) {
                            $this->log('扫码支付跳转关注-2', LOG_INFO);
                        }
                    }
                }
			}


			$msg = "";


			if( isset($req["Content"])) { //text message
				$input = $req["Content"];
				$msg = $msg.$req["Content"];
			}

			if( isset($req["PicUrl"])) { //image message
				$msg = $msg."*图片消息*";
				$picUrl = $req["PicUrl"];
			}

			if( isset($req["Url"])){ //link message
				$msg = $msg."*超链接信息：{$req['Title']}.({$req['Description']})*";
				$url = $req["Url"];
			}

			if( isset($req["Location_X"])){ //location message
				$msg = $msg."*位置信息,我在{$req["Label"]}({$req["Location_X"]}, {$req["Location_Y"]})*";
			}

			if( isset($req["Recognition"])){ //location message
				$msg = $msg."*语音信息：{$req['Recognition']}*";
			}

            $host3g = (WX_HOST);

            $special = $this->getSpecialTitle($from, $input);
            if (!empty($special)) {
                echo $this->newArticleMsg($user, $me, array(array('url' => $special['url'], 'title' => $special['title'], 'picUrl' => $special['pic'], 'description' => '点击查看详情，获得你的前世吃货身份')));
                return;
            }

            if (!empty($uid) && is_admin_uid($uid)) {
                $parameters = explode(' ', $input);
                if (count($parameters >= 1)) {
                    $upper_input = trim(mb_strtoupper($parameters[0]));
                    switch($upper_input) {
                        case '5152':
                        case 'ADMIN_COUPON':
                            $this->handle_admin_coupon($parameters, $user, $me);
                            return;
                    }
                }
            }


			$user_code = urlencode(authcode($user,'ENCODE'));
			//判断输入内容
			switch($input)
			{
				case "下单":
				case "预订":
				case "预定":
				case "1":
                case "１":
                case "CLICK_URL_TECHAN":
					echo $this->newTextMsg($user, $me, '点击进入<a href="'.$this->loginServiceIfNeed($from, $user, oauth_wx_goto('CLICK_URL_TECHAN', $host3g)).'">预定</a>');
					break;
				case "查看订单":
				case "订单":
                case "CLICK_URL_MINE":
				case "2":
					echo $this->newTextMsg($user, $me, '点击进入<a href="'.$this->loginServiceIfNeed($from, $user, oauth_wx_goto('CLICK_URL_MINE', $host3g)).'">个人中心</a>');
					break;
                case "3":
					echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://wx.wsq.qq.com/177650290\" >51daifan微社区</a>");
					break;
                case "CLICK_URL_SALE_AFTER_SAIL":
                    echo $this->newTextMsg($user, $me, '点击进入<a href="'.$this->loginServiceIfNeed($from, $user, oauth_wx_goto('CLICK_URL_SALE_AFTER_SAIL', $host3g)).'">售后服务</a>');
                    break;
                case "CLICK_URL_SHICHITUAN":
                    echo $this->newTextMsg($user, $me, '点击进入<a href="'.$this->loginServiceIfNeed($from, $user, oauth_wx_goto('CLICK_URL_SHICHITUAN', $host3g)).'">试吃评价</a>');
                    break;
                case '6':
                case '６':
                case 'CLICK_GUANZHU_FUWUHAO':
                    if($from == FROM_WX_SERVICE) {
                        echo "您正在使用我们的服务号！谢谢！(::)";
                    } else {
                        echo $this->newTextMsg($user, $me, '安卓系统请点击关注<a href="' . $this->loginServiceIfNeed($from, $user, "weixin://contacts/profile/gh_b860367e62a5")
                            . '">朋友说服务号</a>；苹果系统请点击<a href="'.WX_SERVICE_ID_GOTO.'">查看如何关注</a>。');
                    }
                    break;
                case "CLICK_URL_BINDING":
                    if ($from == FROM_WX_SUB) {
                        list($oauth, $hasAccountWithSubOpenId) = $this->hasAccountWithSubOpenId($user);
                        if (!$hasAccountWithSubOpenId) {
                            echo $this->newTextMsg($user, $me, '您没有历史账号信息');
                        } else if ($this->whetherBinded($oauth['Oauthbind']['user_id'])) {
                            echo $this->newTextMsg($user, $me, '您的历史账号信息已经合并');
                        } else {
                            echo $this->newTextMsg($user, $me, '您有历史账号信息未绑定，点击<a href="' . $this->loginServiceIfNeed($from, $user, "http://$host3g/users/after_bind_relogin.html?wx_openid=$user_code", true) . '">绑定账号</a>');
                        }
                    }
                    break;
                case "5151":
                case "ordersadmin":
                case "Ordersadmin":
                    echo $this->newTextMsg($user, $me, '点击进入<a href="'.$this->loginServiceIfNeed($from, $user, "http://$host3g/brands/brands_admin?wx_openid=$user_code").'">商家订单管理</a>');
                    break;
                case '5152':
                    echo $this->newTextMsg($user, $me, '点击进入<a href="'.$this->loginServiceIfNeed($from, $user, "http://$host3g/apple_201410/index.html").'">苹果游戏Demo</a>');
                    break;
                case "e100":
                case "E100":
                    if ($from == FROM_WX_SERVICE) {
                        $openId =  trim($user);
                        $uid = $this->Oauthbind->findUidByWx($openId);
                        if ($uid) {
                            $this->loadModel('User');
                            $u = $this->User->findById($uid);
                            $this->log('Found user: id='. $uid .', ' . json_encode($u));
                            if (!empty($u)) {
                                $dt2 = DateTime::createFromFormat(FORMAT_DATETIME, $u['User']['created']);
                                $ts2 = $dt2->getTimestamp();
                                if (time() - $ts2 > 3 * 24 * 3600) {
                                    echo $this->newTextMsg($user, $me, '亲，只有从e袋洗过来的新用户才能领取此优惠哦');
                                } else {
                                    $weixinC = $this->Components->load('Weixin');
                                    if (!add_coupon_for_new($uid, $weixinC,
                                        array(18753, 18754, 18755, 18756, 18757),
                                        "满50元减5元；满100元减10元；满150元减15元；满200元减20元；满400元减50元")
                                    ) {
                                        echo $this->newTextMsg($user, $me, '欢迎关注朋友说，您已经领过啦');
                                    }
                                }
                            }
                        } else {
                            $this->log("notfound:".$openId);
                        }
                    }
                    break;
                case '19':
                case 'uid':
                    echo $this->newTextMsg($user, $me,  "您的用户id为".$uid);
                    echo $this->newTextMsg($user, $me,  "您的用户id为(test2):".$uid);
                    break;
                case '樱花':
                    $voteConfig = $this->VoteSetting->getVoteConfig(7);
                    $detail_url = $voteConfig['common_params']['server_reply_url'];
                    $pic_url = $voteConfig['common_params']['server_reply_img'];
                    $reply_title = $voteConfig['title'];
                    if ($uid) {
                        $event_candidate = $this->CandidateEvent->find('first', array(
                            'conditions' => array('user_id' => $uid, 'event_id' => 7)
                        ));
                        $this->log('event candidate ' . json_encode($event_candidate));
                        if (!empty($event_candidate)) {
                            $candidate_id = $event_candidate['CandidateEvent']['candidate_id'];
                            $candidate = $this->Candidate->find('first', array(
                                'conditions' => array(
                                    'id' => $candidate_id
                                )
                            ));
                            if (!empty($candidate)) {
                                if ($candidate['Candidate']['deleted'] == DELETED_NO) {
                                    $detail_url = 'http://www.tongshijia.com/vote/candidate_detail/' . $candidate_id . '/7';
                                }
                            }
                        }
                    }
                    $content = array(
                        array('title' => $reply_title, 'description' => '',
                            'picUrl' => $pic_url,
                            'url' => $detail_url),
                    );
                    echo $this->newArticleMsg($user, $me, $content);
                    break;
                case '万圣节':
                    $voteConfig = $this->VoteSetting->getVoteConfig(8);
                    $detail_url = $voteConfig['common_params']['server_reply_url'];
                    $pic_url = $voteConfig['common_params']['server_reply_img'];
                    $reply_title = $voteConfig['title'];
                    if ($uid) {
                        $event_candidate = $this->CandidateEvent->find('first', array(
                            'conditions' => array('user_id' => $uid, 'event_id' => 8)
                        ));
                        $this->log('event candidate ' . json_encode($event_candidate));
                        if (!empty($event_candidate)) {
                            $candidate_id = $event_candidate['CandidateEvent']['candidate_id'];
                            $candidate = $this->Candidate->find('first', array(
                                'conditions' => array(
                                    'id' => $candidate_id
                                )
                            ));
                            if (!empty($candidate)) {
                                if ($candidate['Candidate']['deleted'] == DELETED_NO) {
                                    $detail_url = 'http://www.tongshijia.com/vote/candidate_detail/' . $candidate_id . '/8';
                                }
                            }
                        }
                    }
                    $content = array(
                        array('title' => $reply_title, 'description' => '这一天的装扮是多么的“可爱”！看看大家的装扮，我们快来比一比，谁的装扮最Cool! 最棒？',
                            'picUrl' => $pic_url,
                            'url' => $detail_url),
                    );
                    echo $this->newArticleMsg($user, $me, $content);
                    break;
                case '报名':
                case '宝宝':
                case '投票':
                    $voteConfig = $this->VoteSetting->getVoteConfig(6);
                    $detail_url = $voteConfig['common_params']['server_reply_url'];
                    $pic_url = $voteConfig['common_params']['server_reply_img'];
                    $reply_title = $voteConfig['title'];
                    if($uid){
                        $event_candidate = $this->CandidateEvent->find('first',array(
                            'conditions' => array('user_id' => $uid, 'event_id' => 6)
                        ));
                        $this->log('event candidate '.json_encode($event_candidate));
                        if(!empty($event_candidate)){
                            $candidate_id = $event_candidate['CandidateEvent']['candidate_id'];
                            $candidate = $this->Candidate->find('first',array(
                                'conditions' => array(
                                    'id' => $candidate_id
                                )
                            ));
                            if(!empty($candidate)){
                                if($candidate['Candidate']['deleted']==DELETED_NO){
                                    $detail_url = 'http://www.tongshijia.com/vote/candidate_detail/'.$candidate_id.'/6';
                                }
                            }
                        }
                    }
                    $content = array(
                        array('title' => $reply_title, 'description' => '',
                            'picUrl' => $pic_url,
                            'url' => $detail_url),
                    );
                    echo $this->newArticleMsg($user, $me, $content);
                    break;
                case '8888':
                    $voteConfig = $this->VoteSetting->getVoteConfig(9);
                    $detail_url = $voteConfig['common_params']['server_reply_url'];
                    $pic_url = $voteConfig['common_params']['server_reply_img'];
                    $reply_title = $voteConfig['title'];
                    if($uid){
                        $event_candidate = $this->CandidateEvent->find('first',array(
                            'conditions' => array('user_id' => $uid, 'event_id' => 9),
                            'order' => array('id DESC')
                        ));
                        $this->log('event candidate '.json_encode($event_candidate));
                        if(!empty($event_candidate)){
                            $candidate_id = $event_candidate['CandidateEvent']['candidate_id'];
                            $candidate = $this->Candidate->find('first',array(
                                'conditions' => array(
                                    'id' => $candidate_id
                                )
                            ));
                            if(!empty($candidate)){
                                if($candidate['Candidate']['deleted']==DELETED_NO){
                                    $detail_url = 'http://www.tongshijia.com/vote/candidate_detail/'.$candidate_id.'/9';
                                }
                            }
                        }
                    }
                    $content = array(
                        array('title' => $reply_title, 'description' => '',
                            'picUrl' => $pic_url,
                            'url' => $detail_url),
                    );
                    echo $this->newArticleMsg($user, $me, $content);
                    break;
				default:
                    $hour = date('G');
                    if($hour>=9&&$hour<21){
                        echo $this->newTextMsg($user, $me,  "回复“2”查看我的订单\n其他问题加微信MM客服：pyshuo2015，我们将尽快给您回复");
                    }else{
                        echo $this->newTextMsg($user, $me, "回复“2”查看我的订单\n微信MM客服：pyshuo2015 \n工作时间9:00-21:00。\n紧急情况请电话联系\n".CSAD_PHONE);
                    }
			}
		}
	}

    private function loginServiceIfNeed($from, $subOpenId, $url, $do_bind = false) {
        if ($do_bind) {
            $return_uri = urlencode('http://'.WX_HOST.'/users/wx_auth');
            $state = base64_encode(authcode($subOpenId, 'ENCODE') . "redirect_url_" . $url);
            return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . WX_APPID . '&redirect_uri=' . $return_uri . '&response_type=code&scope=snsapi_base&state=' . $state . '#wechat_redirect';
        }  else {
            return $url;
        }
    }

	private function newTextMsg($toUser, $sender, $cont){
    	$time = time();
    	return "<xml>"
				."<ToUserName><![CDATA[$toUser]]></ToUserName>"
				."<FromUserName><![CDATA[$sender]]></FromUserName>"
				."<CreateTime>$time</CreateTime>"
				."<MsgType><![CDATA[text]]></MsgType>"
				."<Content><![CDATA[$cont]]></Content>"
				."</xml>";
    }

    // array = title => '', description => '', picUrl => '', url => ''
    private function newArticleMsg($toUser, $sender, $array){
    	$time = time();
    	$len = count($array);
    	$items = "";

    	foreach($array as $it){
    		$items =$items."<item>"
				."<Title><![CDATA[{$it['title']}]]></Title> "
				."<Description><![CDATA[{$it['description']}]]></Description>"
				."<PicUrl><![CDATA[{$it['picUrl']}]]></PicUrl>"
				."<Url><![CDATA[{$it['url']}]]></Url>"
				."</item>";

    	}

    	return "<xml>"
				."<ToUserName><![CDATA[$toUser]]></ToUserName>"
				."<FromUserName><![CDATA[$sender]]></FromUserName>"
				."<CreateTime>$time</CreateTime>"
				."<MsgType><![CDATA[news]]></MsgType>"
				."<ArticleCount>$len</ArticleCount>"
				."<Articles>"
				.$items
				."</Articles>"
				."</xml>";
    }


	private function checkSignature()
	{
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	
	        		
		$token = WEIXIN_TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

    /**
     * @param $user_id
     * @return bool  whether is bind (and if user has never been created for the specified openid, return false).
     */
    private function whetherBinded($user_id) {
        if ($user_id) {
            $r = $this->Oauthbind->find('first', array('conditions' => array('user_id' => $user_id, 'source' => oauth_wx_source(),)));
            if (!empty($r)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $subOpenId
     * @return array :  list($oauth, $hasAccountWithSubOpenId)
     */
    private function hasAccountWithSubOpenId($subOpenId) {
        $oauth = $this->Oauthbind->find('first', array('conditions' => array('oauth_openid' => $subOpenId, 'source' => 'weixin',)));
        $hasAccountWithSubOpenId = !empty($oauth) && !empty($oauth['Oauthbind']['user_id']);
        return array($oauth, $hasAccountWithSubOpenId);
    }


    private function getSpecialTitle($type, $val) {
        $elements = array(FROM_WX_SERVICE =>
          array(
                '打' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5NzQ3NTkxNA==&mid=204166400&idx=1&sn=6f250ac6c2e5c677af859317ac2842a5#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia357phWhKGa8jUUC94zLOEXE4ND3PDMx4sWe0yPY7jdltD6Riasa0SceWA/0', 'title'=>'【你前世的吃货身份：武大郎】'),
                '死' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5NzQ3NTkxNA==&mid=204166086&idx=1&sn=fc8251ab2d5cda2af825895b9e0e228d#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia35xd3hZfWYK2kEmCRtqZiaUzATl1XVdpKZh9HfE113oJTuqqk0G0y6PVQ/0', 'title'=>'【你前世的吃货身份：武则天】'),
                '都' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5NzQ3NTkxNA==&mid=204165678&idx=1&sn=b7ed9f3d1263675456d7431a912305f4#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia35bz312xCVICquYMn9UY5K5ibf8d540CQicImR9TxcAic9Kfk12Dj5dYN4Q/0', 'title'=>'【你前世的吃货身份：唐伯虎】'),
                '不' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5NzQ3NTkxNA==&mid=204165330&idx=1&sn=c11bcc24f5083550082c30991f14233d#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia35kvYSljfzYjhuYicjOAfapMib3eCnDY19CVaIkY3qEpBbjJFvc5EANYicQ/0', 'title'=>'【你前世的吃货身份：如花】'),
                '是' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5NzQ3NTkxNA==&mid=204165084&idx=1&sn=09fb2503326549242af067b269df40b4#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia357vzbHyBriamXdwsQq7SAXpc5p93cHSk9icEATWzxkyO6nYqSvLp1FdHg/0', 'title'=>'【你前世的吃货身份：包租婆】'),
                '猪' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5NzQ3NTkxNA==&mid=204164747&idx=1&sn=9b9478886ec864dda9e71fe4fc8d1ede#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia350WwImJ0QEBk45RnjIqq8mqem4KdgicGVaSRWmOY8tJ1PHdGvgZIgFdg/0', 'title'=>'【你前世的吃货身份：猪八戒】'),
                '八' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5NzQ3NTkxNA==&mid=204164184&idx=1&sn=16188fdda3594bdeee968fd84928cb96#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia35bgswj4J7wCt0amx6s6vpUZLgJX2Nq0NvWfV2xEggFqBEmIE8kBIvdQ/0', 'title'=>'【你前世的吃货身份：林黛玉】'),
                '戒' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5NzQ3NTkxNA==&mid=204162688&idx=1&sn=959c8132480eabd4cdb6bb3a14be649b#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia35KENKL8pQcdolSp59YgeHfqyt7YWBMfvarXkmHsrz3eCicpI7xZX6qag/0', 'title'=>'【你前世的吃货身份：肥仔】')
          )
        , FROM_WX_SUB =>
      array(
            '打' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=202247008&idx=1&sn=62fc1d3cdc6fc03dc0455edae5e5ccbe#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia357phWhKGa8jUUC94zLOEXE4ND3PDMx4sWe0yPY7jdltD6Riasa0SceWA/0', 'title' => '【你前世的吃货身份：武大郎】'),
            '死' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=202247055&idx=1&sn=891b24b7d1c57199096cc135290f34f3#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia35xd3hZfWYK2kEmCRtqZiaUzATl1XVdpKZh9HfE113oJTuqqk0G0y6PVQ/0', 'title' => '【你前世的吃货身份：武则天】'),
            '都' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=202247108&idx=1&sn=538860cfe732e97e7f5472df1720e831#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia35bz312xCVICquYMn9UY5K5ibf8d540CQicImR9TxcAic9Kfk12Dj5dYN4Q/0', 'title' => '【你前世的吃货身份：唐伯虎】'),
            '不' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=202247151&idx=1&sn=6591069bc0d7618c7840065012431dfe#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia35kvYSljfzYjhuYicjOAfapMib3eCnDY19CVaIkY3qEpBbjJFvc5EANYicQ/0', 'title' => '【你前世的吃货身份：如花】'),
            '是' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=202247205&idx=1&sn=3a81fab29a02f03b8c06ccf5ca591bbc#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia357vzbHyBriamXdwsQq7SAXpc5p93cHSk9icEATWzxkyO6nYqSvLp1FdHg/0', 'title' => '【你前世的吃货身份：包租婆】'),
            '猪' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=202247235&idx=1&sn=cb4024d719503c8ba7def775d7a789f4#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia350WwImJ0QEBk45RnjIqq8mqem4KdgicGVaSRWmOY8tJ1PHdGvgZIgFdg/0', 'title' => '【你前世的吃货身份：猪八戒】'),
            '八' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=202247289&idx=1&sn=140e36756bf287074ec206d90b856921#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia35bgswj4J7wCt0amx6s6vpUZLgJX2Nq0NvWfV2xEggFqBEmIE8kBIvdQ/0', 'title' => '【你前世的吃货身份：林黛玉】'),
            '戒' => array('url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=202247377&idx=1&sn=67296ad1022e3d9afeb61944e4fed480#rd', 'pic' => 'https://mmbiz.qlogo.cn/mmbiz/UuGM2hE8WNGMCRo9uNFuJAluvfc1iaia35KENKL8pQcdolSp59YgeHfqyt7YWBMfvarXkmHsrz3eCicpI7xZX6qag/0', 'title' => '【你前世的吃货身份：肥仔】'),
      )
        );

        return $elements[$type][$val];
    }
    public function save_subscribe_info(){
        $uid = $this->currentUser['id'];
        if($uid){
            $data =array();
            $key = key_cache_sub($uid,'kfinfo');
            if($_GET['groupId']){
                $data = json_encode(array('groupId'=> intval($_GET['groupId'])));
            }elseif($_GET['orderId']){
                $data = json_encode(array('orderId'=> intval($_GET['orderId'])));
            }elseif($_GET['type'] == 'follow') {
                $data = json_encode(array('follow'=> 1));
            }else if($_GET['spring']){
                $data = json_encode(array('pid'=> intval($_GET['spring'])));
            } else if ($_GET['type'] == 'more_score') {
                $data = json_encode(array('type'  => 'more_score'));
            }
            Cache::write($key, $data);
        }
        echo 1;
    }

    /**
     * @param $parameters
     * @param $user
     * @param $me
     */
    private function handle_admin_coupon($parameters, $user, $me) {
        $_coupon_could_distribute = array(18483 => '新用户50返10元券', 18482 => '新用户100返20元券');
        $msg = "";
        if (count($parameters) < 2) {
            $msg = "格式错误， 发优惠券的格式为 admin_coupon {{uid或者手机号}} {{COUPON_ID}}";
        } else {
            $identity = $parameters[1];
            $identity_len = strlen($identity);
            $identity_nick = '';
            if ($identity_len > 8) {
                if ($identity_len != 11) {
                    $msg = '手机号应该为11位';
                } else {
                    $this->loadModel('User');
                    $u = $this->User->findByMobilephone($identity);
                    if (!empty($u)) {
                        $uid = $u['User']['id'];
                        $identity_nick = $u['User']['nickname'];
                    } else {
                        $msg = '手机号不存在';
                    }
                }
            } else if($identity > 0) {
                $u = $this->User->findById($identity);
                if (!empty($u)) {
                    $identity_nick = $u['User']['nickname'];
                    $uid = $identity;
                } else {
                    $msg = '用户 id 不存在';
                }
            } else {
                $msg = '用户Id错误';
            }
            if (!empty($uid)) {
                if (count($parameters) == 2 || empty($parameters[2]) || array_search($parameters[2], array_keys($_coupon_could_distribute)) === false) {
                    $msg = '可用优惠券：';
                    foreach($_coupon_could_distribute as $cid => $label) {
                        $msg .= $cid .'=>'.$label.'; ';
                    }
                } else {
                    $weixinC = $this->Components->load('Weixin');
                    $coupon_desc = $_coupon_could_distribute[$parameters[2]];
                    if (!add_coupon_for_new($uid, $weixinC,
                        array($parameters[2]), $coupon_desc)) {
                        $msg = '给用户'.$identity_nick.'发优惠券('.$coupon_desc.')失败， 该用户已经拥有此优惠券';
                    } else {
                        $msg = '给用户'.$identity_nick.'发优惠券('.$coupon_desc.')成功';
                    }
                }
            }

        }
        echo $this->newTextMsg($user, $me, $msg);
    }
}
?>