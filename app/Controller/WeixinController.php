<?php
/**
 * 腾讯问问自助问答
 * @author arlonzou
 * @2012-11-2下午3:30:18
 */
define("WEIXIN_TOKEN", "sUrjPDH8xus2d4JT");

class WeixinController extends AppController {

	var $name = 'Weixin';
	
	public function beforeFilter(){
		parent::beforeFilter();
		$this->layout = false;
	}
	
	public function msg(){
		if(!empty($_GET["echostr"])){
			$this->valid();
		}
		else{
			$this->responseMsg();
		}
		exit;
	}
	
	public function valid()
	{
		$echoStr = $_GET["echostr"];
		if($this->checkSignature()){
			echo $echoStr;
		}else{
			echo "invalid request: echo=$echostr";
			CakeLog::error("invalid request: echo=$echostr");
		}
	}

	private function responseMsg()
	{
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (!empty($postStr))
		{
			$ret = xml_to_array($postStr);
			$req = $ret["xml"];

			$me = $req["ToUserName"];
			$user = $req["FromUserName"];
			//$type = $req["MsgType"];
			
			if(!empty($req['Event'])){
				if($req['Event']=='subscribe'){ //订阅
					echo $this->newTextMsg($user, $me, "欢迎关注51daifan，51daifan是同事间互相分享自己家特产的公益平台，评论回复1即可查看同事最热分享。51daifan，让一部分人先安全起来！");
					exit;
				}
			}
			
			$input = "";

			$url = "http://www.51daifan.com";
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
			$user_code = urlencode(authcode($user,'ENCODE'));
			//判断输入内容
			switch($input)
			{
				case "下单":
				case "预订":
				case "预定":
				case "1":
					echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://51daifan.sinaapp.com/techan.html?wx_openid=$user_code\" >51带饭预定页</a>");
					break;
				case "查看订单":
				case "订单":
				case "2":
					echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://51daifan.sinaapp.com/orders/mine.html?wx_openid=$user_code\" >查看我的订单</a>");
					break;
                case "3":
					echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://wx.wsq.qq.com/177650290\" >51daifan微社区</a>");
					break;
				case "4":
					echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://51daifan.sinaapp.com/share.html?wx_openid=$user_code\" >分享同事列表页</a>");
					break;
                case "5":
                    echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://www.mikecrm.com/f.php?t=3DGEyQ\" >51带饭试吃团报名</a>");
                    break;
				case "大米":
				case "9":
						echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://51daifan.sinaapp.com/users/goTage?wx_openid=$user_code\" >天天踏歌购买娜娜家的大米</a>");
						break;
                case "ordersadmin":
                    echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://www.pyshuo.com/brands/brands_admin\">商家订单管理</a>");
                    break;
				//default :
				//	echo $this->newTextMsg($user, $me, "回复“预定”进入预定页\n回复“订单”查看我的订单");
			}
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
}
?>