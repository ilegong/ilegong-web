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
		$this->layout = false;
	}
	
	public function getUserInfo(){
		$code = $_GET['code'];
		list($uid,$username) = explode(',',authcode($code, 'DECODE'));
		$this->loadModel('User');
		$userinfo = $this->User->find('first',array('conditions'=>array('id'=>$uid,'username'=>$username)));
		if(empty($uid) || empty($userinfo)){
			$ret = array('code'=>0,$msg=>'code error. no user exists.');
		}
		else{
			$ret = array();
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
		}
		echo json_encode($ret);
		exit;
	}
}
?>