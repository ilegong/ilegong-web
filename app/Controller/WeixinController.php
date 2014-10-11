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

    var $uses = array('Oauthbinds');
	
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

            $this->log($req);

			$input = "";
			if(!empty($req['Event'])){
				if($req['Event']=='subscribe'){ //订阅
					echo $this->newTextMsg($user, $me, "欢迎关注【朋友说】，【朋友说】是朋友、同事间互相分享推荐自己家特产的平台，欢迎加入我们分享。");
					exit;
				} else if ($req['Event'] == 'CLICK') {
                    $input = $req['EventKey'];
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

            $host3g = ($from == FROM_WX_SERVICE? 'www.pengyoushuo.com.cn' : 'www.pyshuo.com');

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
					echo $this->newTextMsg($user, $me, '点击进入<a href="'.$this->loginServiceIfNeed($from, $user, oauth_wx_goto('CLICK_URL_MINE', $host3g)).'">我的订单</a>');
					break;
                case "3":
					echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://wx.wsq.qq.com/177650290\" >51daifan微社区</a>");
					break;
				case "4":
					echo $this->newTextMsg($user, $me, '点击进入<a href="'.$this->loginServiceIfNeed($from, $user, "http://$host3g/share.html?wx_openid=$user_code").'">分享同事列表页</a>');
					break;
                case "5":
                    echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://www.mikecrm.com/f.php?t=3DGEyQ\">朋友说试吃团报名</a>");
                    break;
                case "CLICK_URL_SALE_AFTER_SAIL":
                    echo $this->newTextMsg($user, $me, '点击进入<a href="'.$this->loginServiceIfNeed($from, $user, oauth_wx_goto('CLICK_URL_SALE_AFTER_SAIL', $host3g)).'">售后服务</a>');
                    break;
				case "大米":
				case "9":
                    echo $this->newTextMsg($user, $me, '点击进入<a href="'.$this->loginServiceIfNeed($from, $user, "http://$host3g/users/goTage?wx_openid=$user_code").'" >天天踏歌购买娜娜家的大米</a>');
                    break;
                case "CLICK_URL_SHICHITUAN":
                    echo $this->newTextMsg($user, $me, '点击进入<a href="'.$this->loginServiceIfNeed($from, $user, oauth_wx_goto('CLICK_URL_SHICHITUAN', $host3g)).'">试吃评价</a>');
                    break;
                case "CLICK_URL_BINDING":
                    list($oauth, $hasAccountWithSubOpenId) = $this->hasAccountWithSubOpenId($user);
                    if (!$hasAccountWithSubOpenId || $this->whetherBinded($oauth['Oauthbinds']['user_id'])){
                        echo $this->newTextMsg($user, $me, '您的历史账号信息已经合并');
                    } else {
                        echo $this->newTextMsg($user, $me, '您有历史账号信息未绑定，点击进入<a href="' . $this->loginServiceIfNeed($from, $user, "http://$host3g/users/bindWxSub.html?wx_openid=$user_code") . '">绑定账号</a>');
                    }
                    break;
                case "5151":
                case "ordersadmin":
                case "Ordersadmin":
                    echo $this->newTextMsg($user, $me, '点击进入<a href="'.$this->loginServiceIfNeed($from, $user, "http://$host3g/brands/brands_admin?wx_openid=$user_code").'">商家订单管理</a>');
                    break;
				//default :
				//	echo $this->newTextMsg($user, $me, "回复“预定”进入预定页\n回复“订单”查看我的订单");
			}
		}
	}

    private function loginServiceIfNeed($from, $subOpenId, $url) {
        if ($from == FROM_WX_SUB) {
            $return_uri = urlencode('http://www.pyshuo.com/users/wx_auth');
            $state = base64_encode(authcode($subOpenId, 'ENCODE') . "redirect_url_" . $url);
            return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . WX_APPID . '&redirect_uri=' . $return_uri . '&response_type=code&scope=snsapi_base&state=' . $state . '#wechat_redirect';
        }
        return $url;
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
            $r = $this->Oauthbinds->find('first', array('conditions' => array('user_id' => $user_id, 'source' => oauth_wx_source(),)));
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
        $oauth = $this->Oauthbinds->find('first', array('conditions' => array('oauth_openid' => $subOpenId, 'source' => 'weixin',)));
        $hasAccountWithSubOpenId = !empty($oauth) && !empty($oauth['Oauthbinds']['user_id']);
        return array($oauth, $hasAccountWithSubOpenId);
    }
}
?>