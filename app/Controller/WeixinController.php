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
	}
	
	private function valid()
	{
		$echoStr = $_GET["echostr"];
		if($this->checkSignature()){
			echo $echoStr;
			exit;
		}
	}

	private function responseMsg()
	{
		
		
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
		$ret = xml_to_array($postStr);
		
		if (!empty($postStr))
		{
			$ret = xml_to_array($postStr);			
			$fromUsername = $ret['xml']['FromUserName'];
			$toUsername = $ret['xml']['ToUserName'];
			$keyword = trim($ret['xml']['Content']);
			$time = time();
			$textTpl = "<xml>
			<ToUserName><![CDATA[%s]]></ToUserName>
			<FromUserName><![CDATA[%s]]></FromUserName>
			<CreateTime>%s</CreateTime>
			<MsgType><![CDATA[%s]]></MsgType>
			<Content><![CDATA[%s]]></Content>
			<FuncFlag>0</FuncFlag>
			</xml>";
			if(!empty( $keyword ))
			{
				$this->loadModel('GameWord');
				$this->loadModel('GameRoom');
				// 房间号小于6位数
				// 游戏建立后，若3分钟内没有成功开始游戏，则游戏房间作废.若一人连续发送时，删除前一个发送的游戏房间好
				// 法官获得词后，可让人加入房间号游戏，或者线下写小纸条让人抽签
				if(strpos($keyword,'捉鬼')!==false){
					$tmp = preg_split('/\s+/',$keyword);
					$contentStr = '请发送各角色的如 4 3 2表示4个平民，3个鬼，2个啥子';
				}
				elseif(strpos($keyword,'谁是间谍')!==false){
					$tmp = preg_split('/\s+/',$keyword);
					if(count($tmp)!=2 || intval($tmp[1])<3){
						$contentStr = "  发送“谁是间谍 游戏人数”建立游戏，如“谁是间谍 6”，表示6个人参加的谁是间谍游戏。游戏人数要大于3。\n  游戏玩法：游戏会选定两个字数相同且意义相近的词语作为描述对象。参与游戏的人员每人会收到一个词语，间谍拿到的词与其他人不同，每局仅有一个间谍。\n游戏开始时，轮流对词语进行描述，然后大家同时对最可能是间谍的人投票，投票最多的人出局。若间谍被投死则游戏结束，如果间谍没有死则继续进行下一轮。直到剩最后两人时，如果间谍还没有死，则间谍胜利。\n  当有法官时，法官判断游戏是否结束；没有法官时，被投死的人对词判断是否结束，若词有不同的则表示间谍被投死了";
					}
					else{
						$item=$this->GameWord->find('first',array(
								'conditions'=>array('published'=>1,'deleted'=>0),
								'order'=>'rand()',
						));
						$contentStr = $item['GameWord']['name'];
						$this->GameRoom->save(array(
							'name' => $keyword,
							'word' => $item['GameWord']['name'],
							'creator'=> $fromUsername,
						));
						
						$roomid = $this->GameRoom->getLastInsertID();
						$contentStr = "房间号为:$roomid,游戏词:{$item['GameWord']['name']}。您为法官，其他人发送房间号可获取词参与游戏。";
					}
				}
				elseif(intval($keyword)==$keyword && intval($keyword)>0){ //为房间号时，加入游戏
					$roomid = intval($keyword);
					$item=$this->GameRoom->find('first',array(
							'conditions'=>array('id'=>$roomid),
					));
					if(empty($item)){
						$contentStr = '您发的房间号不存在或已过期，下次快点加入游戏吧，小伙伴们都等不急了。';
					}
					$users = unserialize($item['GameRoom']['content']);					
				}
				else{
					$contentStr = "  发送“捉鬼 平民人数 鬼人数 傻子人数”创建一个捉鬼游戏,各数字与词之间用空格隔开，如“捉鬼 4 3 2”。\n  发送“谁是间谍 间谍人数”创建一个谁是间谍的游戏,各数字与词之间用空格隔开，如“谁是间谍  6”。\n  游戏开始前，请先提醒参加游戏的各位小伙伴先关注好微信账号，并开启手机上网准备发送微信消息!";
				}
				
				$msgType = "text";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
				echo $resultStr;
			}else{
				echo "Input something...";
			}
		}
		else {
			$this->loadModel('GameWord');
			$item=$this->GameWord->find('first',array(
					'conditions'=>array('published'=>1,'deleted'=>0),
					'order'=>'rand()',
			));
			print_r($item['GameWord']['name']);
			echo "---";
			exit;
		}
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