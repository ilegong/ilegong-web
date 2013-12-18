<?php
//session_start();print_r($_SESSION);
class OauthsController extends AppController {

    var $name = 'Oauths';

    function sina() {
    	
    	if(!empty($this->currentUser)){
    		$this->redirect('/'.$_GET['turl']);
    	}
		$oauthkeys = $this->Session->read('oauthkeys');
    	if(defined( "SAE_MYSQL_DB"))
		{
			$o = new SAETOAuth( WB_AKEY , WB_SKEY , $oauthkeys['oauth_token'] , $oauthkeys['oauth_token_secret']  );
		}
		else
		{
			$o = new WeiboOAuth( WB_AKEY , WB_SKEY , $oauthkeys['oauth_token'] , $oauthkeys['oauth_token_secret']  );
		}
		$login_key = $o->getAccessToken(  $_REQUEST['oauth_verifier'] ) ;
		
//		print_r($login_key);
//		Array
//		(
//		    [oauth_token] => 80d94ce70ec49ec8d735d1c66e209c06
//		    [oauth_token_secret] => d073e65dae7904a3f495c1db1275d47d
//		    [user_id] => 1707725861
//		)
		$this->loadModel('User');
		
		$userinfo = $this->User->find('first',array(
			'conditions'=>array(
				'sina_uid'=> $login_key['user_id'],
			),
		));
		$current_time = date('Y-m-d H:i:s');
    	if(defined( "SAE_MYSQL_DB"))
		{
			$c = new SAETClient( WB_AKEY , WB_SKEY , $login_key['oauth_token'] ,$login_key['oauth_token_secret']);
		}
		else
		{
			$c = new WeiboClient( WB_AKEY , WB_SKEY , $login_key['oauth_token'] ,$login_key['oauth_token_secret']);
		}
		
    	if($login_key['user_id']<1)
		{
			$this->redirect('/'.$_GET['turl']); exit;
		}
		
		$sina_user  = $c->show_user($login_key['user_id']); // done
		// print_r($sina_user);exit;
		if(empty($sina_user['domain'])) $sina_user['domain'] = $login_key['user_id'];
		
    	if($sina_user['gender']=='m')
		{
			$gender = 1; //男
		}
		else
		{
			$gender = 0; //女
		}
		
		if(empty($userinfo))
		{
			$userinfo = array(
				'role_id'=>2,
				'username' => 'sina_'.$sina_user['name'],
				'password' => Security::hash(random_str(12), null, true),
				'nickname'=> $sina_user['name'],
				'screen_name'=> $sina_user['screen_name'],
//				'email'=> $sina_user['profile_image_url'],
				'image'=> $sina_user['profile_image_url'],
				'website'=> $sina_user['url'],
				'sina_domain'=> $sina_user['domain'],
				'sex'=> $gender,
				'location'=> $sina_user['location'],
				'description'=> $sina_user['description'],
				'last_login' => $current_time,
				'created' => $current_time,
				'city'=> $sina_user['city'],
				'province'=> $sina_user['province'],
				'activation_key' => md5(uniqid()),
				'status' => 1,				
				'sina_uid'=> $login_key['user_id'],
				'sina_token'=> $login_key['oauth_token'],
				'sina_token_secret' => $login_key['oauth_token_secret'],
			);
			$this->User->save($userinfo);
			
			$userinfo['id'] = $user_id = $this->User->getLastInsertID();			
			$this->Session->write('User',$userinfo);
			$this->Cookie->write('User',$userinfo,true,0);
//			$me = $c->verify_credentials();
		}
		else
		{
			if($userinfo['User']['image']!=$sina_user['profile_image_url'])
			{
				$this->loadModel('Question');
				$this->Question->updateAll(
					array('user_img'=> $sina_user['profile_image_url']),
					array('creator_id'=> $userinfo['User']['sina_uid'])
				);
				
				$this->loadModel('Weibo');
				$this->Weibo->updateAll(
					array('user_img'=> $sina_user['profile_image_url']),
					array('creator_id'=> $userinfo['User']['sina_uid'])
				);
			}
			
			$updateinfo = array(
				'nickname'=> $sina_user['name'],
				'screen_name'=> $sina_user['screen_name'],
				'image'=> $sina_user['profile_image_url'],
				'website'=> $sina_user['url'],
				'sex'=> $gender,
				'location'=> $sina_user['location'],
				'description'=> $sina_user['description'],
				'city'=> $sina_user['city'],
				'province'=> $sina_user['province'],
				'last_login' => $current_time,
				
				'sina_domain'=> $sina_user['domain'],
				'sina_uid'=> $login_key['user_id'],
				'sina_token'=> $login_key['oauth_token'],
				'sina_token_secret' => $login_key['oauth_token_secret'],
			);
			$userinfo['User'] = array_merge($userinfo['User'],$updateinfo);		
			//$this->User->updateAll($userinfo['User'],array('id'=>$userinfo['User']['id']));
			$this->User->save($userinfo['User']);
			$this->Session->write('User',$userinfo['User']);
			$this->Cookie->write('User',$userinfo['User'],true,0);
		}
		//print_r($userinfo);
		//print_r($_GET);
		$this->redirect('/'.$_GET['turl']);
		exit;
    }  	    
}
?>