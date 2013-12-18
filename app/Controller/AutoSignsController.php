<?php
/**
 * 自动签到，金山快盘签到
 * @author arlonzou
 * @2012-11-2下午3:30:18
 */
App::uses('RequestFacade', 'Network');

class AutoSignsController extends AppController {

	var $name = 'AutoSign';
	
	var $helpers = array('Combinator',);
	
	var $components = array('Auth','TaskQueue');
	
	public function beforeFilter(){
		parent::beforeFilter();
		if($this->currentUser['id'] || defined('IN_CLI')){
			$this->Auth->allowedActions = array('*','signall','sign','');
		}
	}
	
	
	public function signall(){
		if(!defined('IN_CLI')){
			exit('deny');//排除通过其它方式访问
		}
		
		$this->autoRender = false;
		$page = $this->request->query['page'] ? $this->request->query['page'] : 1;
		$limit = 500;
		do{
			$signs = $this->AutoSign->find('list', array(
					'conditions' => array('AutoSign.published' => 1, 'AutoSign.deleted' => 0,),
					'page' => $page,
					'fields' => 'id',
					'limit' => $limit,
			));
			foreach($signs as $id){
				$this->TaskQueue->add('/auto_signs/sign/'.$id);
			}
			if(count($signs)<$limit){
				break;
			}
			$page++;
		}while(true);
		echo 'add all sign to task queue.';
		exit;
	}
	
	public function sign($id){
// 		if(!defined('IN_CLI')){
// 			exit('deny');//排除通过其它方式访问
// 		}
		$this->autoRender = false;
		$modelClass = $this->modelClass;
		$data = $this->AutoSign->find('first', array(
                    'conditions' => array('AutoSign.published' => 1, 'AutoSign.deleted' => 0, 'AutoSign.id' => $id),
            ));
        if($data['AutoSign']['signsite']){
		//$this->Hook->call('autosign', $data['AutoSign']);
			$this->_kuaipan($data['AutoSign']);
		}	
	}
	
	private function _kuaipan($params){
		$loginurl = 'https://www.kuaipan.cn/account_login.htm';
		$request = array(
				'header' => array(
						'Referer' => $loginurl,
						'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
				),
		);
		/**
		 * 默认生成的cookie_file，为parse_url的host信息，包含二级域名。
		 * 无法在各二级域名之间公用cookie信息，
		 * 需要调用setCookieFileName设置使用统一的cookie_file
		 * 放访问的域名变化后，在重新设置其他的cookie_file.
		 */
		RequestFacade::getHttpRequest()->setCookieFileName('kuaipan.cn');
		//打开登录页，初始化种入一些cookie。
		$response = RequestFacade::get($loginurl, array(), $request); 
		
		$loginurl = 'https://www.kuaipan.cn/index.php?ac=account&op=login';
		// 提交登录
		$response = RequestFacade::post($loginurl, array(
				'isajax'=> 'yes',
				'username'=> $params['name'],
				'userpwd'=> $params['password'],
		), $request);
		$ret = @json_decode($response,true);
		if($ret['state']==1){
			echo 'login '.$ret['errcode'].'<br>';
		}
		else{
			echo 'login error.<br>';
			return false;
		}
		// 签到
		$signurl = 'http://www.kuaipan.cn/index.php?ac=common&op=usersign';
		$response = RequestFacade::get($signurl, array(), $request);
		$ret = @json_decode($response,true);
		if($ret['state']==-102){
			echo 'has sign today<br>';
		}
		else{
			echo 'get size:'.$ret['rewardsize'].';get score:'.$ret['increase'];
		}
// 		echo $response.'<br>';
		$request['header']['Host'] = 'huodong.kuaipan.cn';
		$request['header']['Referer'] = 'http://www.kuaipan.cn/home.htm?m=kp';
		$url = 'http://huodong.kuaipan.cn/turnplate/';	
		$response = RequestFacade::get($url, array(), $request);
		
		
		$request['header']['Referer'] = 'http://huodong.kuaipan.cn/turnplate/';	
		/* 刷新抽奖次数 */
		$url = 'http://huodong.kuaipan.cn/ajaxTurnplate/freshLottery/';
		$response = RequestFacade::get($url, array(), $request);
		/* 抽奖 */
		$url = 'http://huodong.kuaipan.cn/ajaxTurnplate/lottery/';
		$response = RequestFacade::get($url, array(), $request);
		//{"status":"ok","data":"80M"},{"status":"nochance","data":""}
		$ret = @json_decode($response,true);
		if($ret['status']!='ok'){
			echo 'status:'.$ret['status'].'<br>';
		}
		else{
			echo 'get lottery size:'.$ret['data']['awardType'].'<br>';
		}
// 		echo $response.'<br>';
	}
	
	public function lists($cateid='') {
		$page = $this->_getParamVars('page',1);
	
		$rows = 5;
		$conditions = array(
			$this->modelClass.'.deleted' => 0,
			'creator' => $this->currentUser['id'],				
		);
		
		$datalist = $this->{$this->modelClass}->find('all', array(
				'conditions' => $conditions,
				'limit' => $rows,
				'page' => $page,
			)
		);
	
		$total = $this->{$this->modelClass}->find('count',
				array(
					'conditions' => $conditions,
				)
		);
		$this->set('modelClass', $this->modelClass);
		$this->set('region_control_name', Inflector::tableize($this->modelClass));
		$this->set('datalist', $datalist);
		$this->set('user_cates', $user_cates);
		$this->set('active_cateid', $cateid);
		$this->set('total', $total);
	
		$page_navi = getPageLinks($total, $rows, $this->request, $page);
		$this->set('list_page_navi', $page_navi); // page_navi 在region调用中有使用，防止被覆盖，此处使用 list_page_navi
	}
}
?>