<?php

/**
 * Translate Controller
 *
 * PHP version 5
 *
 * @category Controller
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
if (defined("SAE_MYSQL_DB")) {
    include_once( 'saetv2.ex.class.php' );
} else {
    App::uses('SaeTOAuthV2','Lib');
}
App::uses('WeiboUtil','Lib');

class SinaController extends OauthAppController {

    public $name = 'Oauth';
    public $WeiboUtil = null;

    public function beforeFilter() {
        $Oauthbinds = $this->Session->read('Auth.Oauthbind');
        $sina_oauth = array();
        if (!empty($Oauthbinds)) {
            foreach ($Oauthbinds as $oauth) {
                if ($oauth['Oauthbind']['source'] == 'sina') {
                    $sina_oauth = $oauth;
                    break;
                }
            }
        }
        if (empty($sina_oauth) && !in_array($this->action, array('login', 'loginCallback','register'))) {
            $this->__message(__('need login'), '/', 20);
        }
//        $this->WeiboUtil = new WeiboUtil($sina_oauth['Oauthbind']['oauth_token'], $sina_oauth['Oauthbind']['oauth_token_secret']);
        parent::beforeFilter();
        $this->Auth->loginAction = array(
			'controller' => 'sina',
			'action' => 'login',
			'plugin' => null
		);
    }

    public function batchdelete($confirm = false) {
        if ($confirm) {
            $page = 1;
            $count = 100;
            $weibolist = $this->WeiboUtil->user_timeline($page, $count);
            foreach ($weibolist as $weibo) {
                $this->WeiboUtil->delete_weibo($weibo['id']);
            }
            $this->__message(__('delete success'), array('action' => 'batchdelete'), 2);
        }
        $this->set('type', 'batchdelete');
    }

    public function sina($type='index', $page=1, $count = 30, $output_type='') {
        if ($type == 'atme') {
            $weibolist = $this->WeiboUtil->mentions($page, $count);
        } elseif ($type == 'comments') {
            $weibolist = $this->WeiboUtil->comments_timeline($page, $count);
        } elseif ($type == 'favs') {
            $weibolist = $this->WeiboUtil->get_favorites($page);
        } elseif ($type == 'friends') {
            if ($_POST['bigest_weiboid']) {
                $weibolist = $this->WeiboUtil->friends_timeline($page, $count, $_POST['bigest_weiboid']);
            } else {
                $weibolist = $this->WeiboUtil->friends_timeline($page, $count);
            }
        } else {
            $weibolist = $this->WeiboUtil->user_timeline($page, $count);
        }
        $alltext = '';
        foreach ($weibolist as $key => $value) {
            $alltext .=$value['text'];
            if (!empty($value['retweeted_status'])) {
                $alltext .=' ' . $value['retweeted_status']['text'];
            }
        }
        if (preg_match_all('/http:\/\/sinaurl.cn\/(\w+)/i', $alltext, $matches)) {
            /*
              $append = Array(
              'http://sinaurl.cn/hbMlyJ',
              'http://sinaurl.cn/hbyEZ0',
              'http://sinaurl.cn/hbxfWs',
              );$matches[0]=array_merge($matches[0],$append); */
            $matches[0] = array_unique($matches[0]);
            //print_r($matches);
            //[url_short] => http://sinaurl.cn/hbMjMv
            //[url_long] => http://blog.sina.com.cn/s/blog_49236e0f0100mtxd.html
            //<a href="http://sinaurl.cn/hbAcR8" target="_blank" mt="url" >http://sinaurl.cn/hbAcR8</a>
            //<a href="http://sinaurl.cn/5bt3p" target="_blank" mt="video">http://sinaurl.cn/5bt3p<img class="small_icon videoicon" alt="" dynamic-src="http://timg.sjs.sinajs.cn/miniblog2style/images/common/transparent.gif"/></a>
            //http://video.sina.com.cn/v/b/35888462-1667588574.html
            $short_urls = $this->WeiboUtil->shorturl($matches[0]);
            $url_short = $url_long = array();
            foreach ($short_urls as $key => $url) {
                if (preg_match('/^http[s]?\:\/\/(www\.)?(tudou\.com)\/programs\/view\/([a-z0-9-_]{3,})/i', $url['url_long'], $matches)) {
                    //土豆
                } elseif (preg_match('/^http[s]?:\/\/v\.youku\.com\/v_show\/id_(\w{13}?)\.html/i', $url['url_long'], $matches)) {
                    //优酷
                } elseif (preg_match('/^http(s)?:\/\/video\.sina\.com\.cn\/v\/b\/(\d+\-\d+)\.html/i', $url['url_long'], $matches)) {
                    //新浪 http://video.sina.com.cn/v/b/35888462-1667588574.html
                } elseif (preg_match('/^http[s]?:\/\/video\.sina\.com\.cn\/playlist\/^\s+?\.html#(\d+)/i', $url['url_long'], $matches)) {
                    //新浪 http://video.sina.com.cn/playlist/4028801-1645691133-2.html#35888462 井号后面为视频vid
                } else {
                    //print_r($url);
                }

                $url_short[] = $url['url_short'];
                $url_target[] = '<a href="' . $url['url_short'] . '" target="_blank">' . $url['url_short'] . '</a>';
                $url_long[] = $url['url_long'];
//				print_r($matches);
            }
            foreach ($weibolist as $key => $value) {
                $weibolist[$key]['text'] = str_replace($url_short, $url_target, $value['text']);
            }
        }
//		print_r($weibolist);exit;
        if ($output_type == 'json') {
            $this->autoRender = false;
            echo json_encode($weibolist);
            return false;
        }
        $this->set('weibolist', $weibolist);
        $this->set('type', $type);
    }

    public function login() {
        $this->Auth->redirect($_SERVER['HTTP_REFERER']);        
        $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
        $url = $o->getAuthorizeURL( WB_CALLBACK_URL );
        header('location:' . $url);
        exit;
    }

    public function loginCallback() {
        $oauthkeys = $this->Session->read('SinaOauthKeys');
        $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );

        if (isset($_REQUEST['code'])) {
            $keys = array();
            $keys['code'] = $_REQUEST['code'];
            $keys['redirect_uri'] = WB_CALLBACK_URL;
            try {
                    $login_token = $o->getAccessToken( 'code', $keys ) ;
            } catch (OAuthException $e) {
            }
        }
        if (empty($login_token)) {
        	$this->redirect('/oauth/sina/login');
        }
       
        $c = new SaeTClientV2(WB_AKEY, WB_SKEY, $login_token['access_token'], '');
        $sina_user = $c->account_profile_basic();
        //print_R($sina_user);
        if($sina_user['id'] < 1){
	        $uid_get = $c->get_uid();	        
			$login_token['uid'] = $uid = $uid_get['uid'];
			$sina_user = $c->show_user_by_id( $uid);
			if ($sina_user['id'] < 1) {
	            $this->Session->setFlash(__('Login Error'));
	            $this->redirect($this->Auth->redirect());
	            exit;
	        }
        }
        else{
        	$login_token['uid'] = $sina_user['id'];
        }
        
        $login_token['oauth_token'] =  $login_token['access_token'];
        
        $this->loadModel('User');
        $userinfo = $this->User->find('first', array(
                    'conditions' => array(),
                    'recursive' => -1,
                    'joins' => array(
                        array(
                            'table' => Inflector::tableize('Oauthbind'),
                            'alias' => 'Oauthbind',
                            'type' => 'inner',
                            'conditions' => array(
                                "Oauthbind.user_id = User.id",
                                "source" => 'sina',
                                "Oauthbind.oauth_uid" => $sina_user['id'],
                            ),
                        ),
                    ),
                    'fields' => array('User.*', 'Oauthbind.*'),
        ));
        
                
        
        if (empty($sina_user['domain']))
            $sina_user['domain'] = $sina_user['id'];

        
        if (empty($userinfo)) {
            // 第一次使用第三方帐号登录，用户不存在的，要求输入用户名，密码和邮箱。
            /**
             * Todo. 注册新用户或者绑定已注册用户
             */
            $this->Session->write('sina_auth_user', $sina_user);
            $this->Session->write('sina_auth_login_key', $login_token);
            $this->data['User']['username'] = $sina_user['real_name'];
            $this->data['User']['email'] = $sina_user['email'];
            
        } else {
        	$current_time = date('Y-m-d H:i:s');
            // 登录成功 
            $updateinfo = array(
                'nickname' => $sina_user['name'],
                'screen_name' => $sina_user['screen_name'],
                'image' => $sina_user['profile_image_url'],
                'website' => $sina_user['url'],
                'sex' => $gender,
                'location' => $sina_user['location'],
                'description' => $sina_user['description'],
                'city' => $sina_user['city'],
                'province' => $sina_user['province'],
                'last_login' => $current_time,
                'sina_domain' => $sina_user['domain'],
            );
            $userinfo['User'] = array_merge($userinfo['User'], $updateinfo);
            $user_id = $userinfo['User']['id'];
            $this->User->save($userinfo['User']);
            $this->Session->write('Auth.User', $userinfo['User']);
            $this->Cookie->write('Auth.User', $userinfo['User'], true, 0);
            
			$this->_getOauthBinds($user_id,$login_token,'sina');
			
            $this->redirect($this->Auth->redirect());
        }
    }
    
    /**
     * 无本站用户，注册本站用户，并绑定
     */
    public function register(){
    	$this->autoRender = false;
        $sina_user = $this->Session->read('sina_auth_user');
        $login_token = $this->Session->read('sina_auth_login_key');
		if ($sina_user['gender'] == 'm') {
            $gender = 1; //男
        } else {
            $gender = 0; //女
        }
        $current_time = date('Y-m-d H:i:s');
        if(!empty($_POST['password'])){
        	if($_POST['data']['User']['password']!=$_POST['data']['User']['password_confirm']){
        		$this->Session->setFlash(__('Two password is empty or not equare.', true));
        		$this->redirect('/Oauth/sina/login');
        	}
        	$password = Security::hash($_POST['data']['User']['password'], null, true);
        }
        else{
        	$password = Security::hash(random_str(12), null, true);
        }
        $userinfo = array(
            'username' => 'sina_' . $sina_user['name'],
            'password' => $password,
            'nickname' => $sina_user['name'],
            'screen_name' => $sina_user['screen_name'],
			'email'=> $sina_user['email'],
            'image' => $sina_user['profile_image_url'],
            'website' => $sina_user['url'],
            'sina_domain' => $sina_user['domain'],
            'sex' => $gender,
            'location' => $sina_user['location'],
            'description' => $sina_user['description'],
            'last_login' => $current_time,
            'created' => $current_time,
            'city' => $sina_user['city'],
            'province' => $sina_user['province'],
            'activation_key' => md5(uniqid()),
            'status' => 1,
        );
        

        if(defined('UC_API')){
        	App::import('Vendor', '', array('file' => 'uc_client' . DS . 'client.php'));
        	$uc_user = uc_user_register('sina_' . $sina_user['name'], $password, $sina_user['email']) ;
        }
//         print_r($uc_user);exit;
//            uc_user_register($username, $password, $email, $questionid = '', $answer = '', $regip = '') ;
        $this->User->save($userinfo);

        $userinfo['id'] = $user_id = $this->User->getLastInsertID();
        $this->Session->write('Auth.User', $userinfo);
        $this->Cookie->write('Auth.User', $userinfo, true, 0);
        
        $this->_getOauthBinds($user_id,$login_token,'sina');
        
        $this->Session->setFlash(__('Login Success', true).$this->Session->read('Auth.User.session_flash'));
        
        $this->redirect($this->Auth->redirect());
    }

    /**
     * 有本站用户，绑定本站用户
     */
    public function bind(){
    	if($this->Auth->login()){
    		
    	}
    	else{
    		
    	}
    }
}

?>