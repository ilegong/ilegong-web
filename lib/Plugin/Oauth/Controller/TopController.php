<?php

App::uses('TopClient','TaobaoSDK');

class TopController extends OauthAppController {

    public $name = 'Top';
    
    public $client = null;
    
    public $WeiboUtil = null;
    
 	public function beforeFilter()
    {
    	$Oauthbinds = $this->Session->read('Auth.Oauthbind');
    	$qq_oauth=array();
    	if(!empty($Oauthbinds)){
	    	foreach($Oauthbinds as $oauth){
	    		if($oauth['Oauthbind']['source']=='top'){
	    			$qq_oauth=$oauth;
	    			break;
	    		}
	    	}
    	}
    	if(empty($qq_oauth) && !in_array($this->action,array('login','loginCallback'))){
			$this->__message(__('need login'),'/',20);
    	}
    	parent::beforeFilter();
    }
    
    public function login() {
   		$c = new TopClient();
   		$aurl = $c->authorize();
		header('location:'.$aurl);	
		exit;
    }
    
    /**
     * Server-side flow
     * http://open.taobao.com/doc/detail.htm?spm=0.0.0.37.c26f7c&id=118#s2
     **/
    public function loginCallback()
    {
    	$code = $this->request->query['code'];
    	$c = new TopClient();
    	$token = $c->getToken($code);
    	print_r($token);
    	
    	$top_user_nick = $token['taobao_user_nick'];
    	$top_user_id = $token['taobao_user_id'];
    	$access_token = $token['access_token'];
    	$refresh_token = $token['refresh_token'];
    	
    	$this->loadModel('User');
		$dbconfig = new DATABASE_CONFIG();
		$userinfo = $this->User->find('first',array(
			'conditions'=>array(),
			'recursive' => -1,
			'joins'=> array(					
						array(
							'table' => Inflector::tableize('Oauthbind'),
							'alias' => 'Oauthbind',  
							'type' => 'inner',
							'conditions' => array(
								"Oauthbind.user_id = User.id",
								"source" => 'top',
								"Oauthbind.oauth_uid" => $top_user_id,
							),
						),
			),
			'fields' => array('User.*','Oauthbind.*'),
		));
		
		$c = new TopClient();		 
		$req_array = array(
			"method"          => "taobao.user.get",
			"session"        => $access_token,
			"fields"	=> "user_id,uid,nick,sex,location,birthday,avatar,email",
		);
		$topUser = $c->execute($req_array);
		print_r($topUser);exit;
		
    	if($topUser['user']['sex']=='m'){
			$gender = 1; //男
		}
		else{
			$gender = 0; //女
		}
		
		$current_time = date('Y-m-d H:i:s');
		
		
    	if(empty($userinfo)){
			$userinfo = array(
				'role_id'=>2,
				'username' => 'top_'.$top_user_nick,
				'password' => Security::hash(random_str(12), null, true),
				'nickname' => $top_user_nick,
				'screen_name' => $top_user_nick,
				'image' => $topUser['user']['avatar'],				
				'sex' => $gender,
				'location' => $topUser['user']['location']['state'].' '.$topUser['user']['location']['city'].' '.$topUser['user']['location']['address'],				
				'last_login' => $current_time,
				'created' => $current_time,				
				'activation_key' => md5(uniqid()),
				'status' => 1,
			);
			$this->User->save($userinfo);			
			$userinfo['id'] = $user_id = $this->User->getLastInsertID();			
			$this->Session->write('Auth.User',$userinfo);
			$this->Cookie->write('Auth.User',$userinfo,true,0);
		}
		else{
			$updateinfo = array(
				'nickname'=> $top_user_nick,
				'screen_name'=> $top_user_nick,
				'image'=> $topUser['user']['avatar'],
				'sex'=> $gender,
				'location'=> $topUser['user']['location']['state'].' '.$topUser['user']['location']['city'].' '.$topUser['user']['location']['address'],
				'last_login' => $current_time,
			);
			$userinfo['User'] = array_merge($userinfo['User'],$updateinfo);
			$user_id = $userinfo['User']['id'];
			$this->User->save($userinfo['User']);
			$this->Session->write('Auth.User',$userinfo['User']);
			$this->Cookie->write('Auth.User',$userinfo['User'],true,0);
		}
		$this->loadModel('Oauthbind');
		$oauth_bind = $this->Oauthbind->find('first',array(
			'conditions'=>array(
				'oauth_uid'=> $topUser['user']['user_id'],
				'user_id' => $user_id,
				'source' => 'top',
			),
		));
		
		//echo $topUser['user']['user_id'];exit;
		
		if(empty($oauth_bind)){
			$oauth_bind = array(
				'user_id' => $user_id,
				'oauth_uid' => $topUser['user']['user_id'],
				'oauth_token' =>  $access_token,
				//'oauth_token_secret' => $login_key['oauth_token_secret'],
				'source' => 'top',
			);
			$this->Oauthbind->save($oauth_bind);
		}
		else{
			$oauth_bind['Oauthbind']['updated'] = date('Y-m-d H:i:s');
			$oauth_bind['Oauthbind']['oauth_token'] = $access_token;
			//$oauth_bind['Oauthbind']['oauth_token_secret'] = $login_key['oauth_token_secret'];
			$this->Oauthbind->save($oauth_bind);
		}
		
		$user_oauths = $this->Oauthbind->find('all',array(
			'conditions'=>array(
				'user_id' => $user_id,
			),
		));
		$this->Session->write('Auth.Oauthbind',$user_oauths);
		//print_r($oauth_bind);exit;
		if(empty($_GET['turl']))
			$_GET['turl'] = '/';
		header('location:'.$_GET['turl']);	
		//$this->redirect($_GET['turl']);
		exit;
    }

	
}
?>