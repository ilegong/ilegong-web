<?php
/**
 * 开放接口
 * @author arlonzou
 * @2012-11-2下午3:30:18
 */

class ApiController extends AppController {

	var $name = 'Api';
	
	public function beforeFilter(){
		parent::beforeFilter();
		$this->autoRender = false;
	}
	
	/**
	 * 需在51daifan已注册的用户，不需要关注仅在论坛注册的部分用户
	 */
	public function getUserInfo(){
		//echo authcode('34,arlonzou','ENCODE');
		//echo '<br/>'.time();
		$code = $_GET['code'];
		$appkey = 'Mb7a5WQuryf9TXKH'; //踏歌的appkey
		$timestamp = $_GET['t'];
		//echo '<br/>'.md5($appkey.$timestamp);
		if( abs(time()-$timestamp) > 3600 ){
			$ret = array('code' => 1,$msg => 'timestamp error.');
			echo json_encode($ret);
			exit;
		}
		elseif($_GET['sign']!= md5($appkey.$timestamp)){
			$ret = array('code'=>2,$msg => 'sign error.');
			echo json_encode($ret);
			exit;
		}
		list($uid,$username) = explode(',',authcode($code, 'DECODE'));
		$this->loadModel('User');
		$userinfo = $this->User->find('first',array('conditions'=>array('id'=>$uid,'username'=>$username)));
		if(empty($uid) || empty($userinfo)){
			$ret = array('code'=>3,$msg=>'code error or no user exists.');
			//错误的参数。
		}
		else{
			$ret = array();
			$ret['code'] = 0;
			$ret['uid'] = $userinfo['User']['id'];
			$ret['username'] = $userinfo['User']['username'];
			$this->loadModel('OrderConsignee');
			$consignee = $this->OrderConsignee->find('first',array(
					'conditions'=>array('creator'=>$ret['uid']),'order' => 'status desc',
			));
			if(!empty($consignee)){
				$ret['address'] = $consignee['OrderConsignee']['address'];
				$ret['mobilephone'] = $consignee['OrderConsignee']['mobilephone'];
				$ret['name'] = $consignee['OrderConsignee']['name'];
			}
			if(empty($ret['name'])){
				$ret['name'] = $ret['username'];
			}
		}
		echo json_encode($ret);
		exit;
	}
}
?>