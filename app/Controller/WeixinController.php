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
                case "１":
                case "CLICK_URL_TECHAN":
					echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://51daifan.sinaapp.com/techan.html?wx_openid=$user_code\" >预定</a>");
					break;
				case "查看订单":
				case "订单":
                case "CLICK_URL_MINE":
				case "2":
					echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://51daifan.sinaapp.com/orders/mine.html?wx_openid=$user_code\" >我的订单</a>");
					break;
                case "3":
					echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://wx.wsq.qq.com/177650290\" >51daifan微社区</a>");
					break;
				case "4":
					echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://51daifan.sinaapp.com/share.html?wx_openid=$user_code\" >分享同事列表页</a>");
					break;
                case "5":
                    echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://www.mikecrm.com/f.php?t=3DGEyQ\" >朋友说试吃团报名</a>");
                    break;
                case "CLICK_URL_SALE_AFTER_SAIL":
                    echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://51daifan.sinaapp.com/articles/view/377?wx_openid=$user_code\" >售后服务</a>");
                    break;
				case "大米":
				case "9":
                    echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://51daifan.sinaapp.com/users/goTage?wx_openid=$user_code\" >天天踏歌购买娜娜家的大米</a>");
                    break;
                case "CLICK_URL_SHICHITUAN":
                    echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://51daifan.sinaapp.com/shichituan.html?wx_openid=$user_code\">试吃评价</a>");
                    break;
                case "5151":
                case "ordersadmin":
                case "Ordersadmin":
                    echo $this->newTextMsg($user, $me, "点击进入<a href=\"http://51daifan.sinaapp.com/brands/brands_admin?wx_openid=$user_code\">商家订单管理</a>");
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



// ArangoDB REST function.
// Connection are created demand and closed by PHP on exit.
    function adb_rest($method,$uri,$querry=NULL,$json=NULL,$options=NULL){
        global $adb_url,$adb_handle,$adb_option_defaults;

        // Connect
        if(!isset($adb_handle)) $adb_handle = curl_init();

        echo "DB operation: $method $uri $querry $json\n";

        // Compose querry
        $options = array(
            CURLOPT_URL => $adb_url.$uri."?".$querry,
            CURLOPT_CUSTOMREQUEST => $method, // GET POST PUT PATCH DELETE HEAD OPTIONS
            CURLOPT_POSTFIELDS => $json,
        );
        curl_setopt_array($adb_handle,($options + $adb_option_defaults));

        // send request and wait for responce
        $responce =  json_decode(curl_exec($adb_handle),true);

        echo "Responce from DB: \n";
        print_r($responce);

        return($responce);
    }



    public function login() {
        $this->log("got weixin login code=".$_REQUEST['code'].", state=".$_REQUEST['code']);

        $adb_url="https://api.weixin.qq.com";
        $adb_option_defaults = array(
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2
        );

        $curl = curl_init();
        $options = array(
            CURLOPT_URL => $adb_url.'/sns/oauth2/access_token?appid=wxca7838dcade4709c&secret=79b787ec8f463eeb769540464c9277b2&code='.$_REQUEST['code'].'&grant_type=authorization_code',
            CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS
            CURLOPT_POSTFIELDS => '',
        );
        curl_setopt_array($curl,($options + $adb_option_defaults));
        // send request and wait for responce
        $responce =  json_decode(curl_exec($curl),true);
        print_r($responce);
        echo $_SERVER['QUERY_STRING'];
        exit;
    }
}
?>