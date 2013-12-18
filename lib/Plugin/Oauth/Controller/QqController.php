<?php

require_once COMMON_PATH . DS . 'QQweibo' . DS . 'Oauth.php';
// oauth.php中类太多，用app::uses实现自动加载不好,使用require_once
App::uses('MBOpenTOAuth', 'QQweibo');
App::uses('MBApiClient', 'QQweibo');

class QqController extends OauthAppController {

    public $name = 'Oauth';
    public $client = null;
    public $WeiboUtil = null;

    public function beforeFilter() {
        $Oauthbinds = $this->Session->read('Auth.Oauthbind');
        $qq_oauth = array();
        if (!empty($Oauthbinds)) {
            foreach ($Oauthbinds as $oauth) {
                if ($oauth['Oauthbind']['source'] == 'qq') {
                    $qq_oauth = $oauth;
                    break;
                }
            }
        }
        if (empty($qq_oauth) && !in_array($this->action, array('login','register','binduser', 'loginCallback'))) {
            $this->__message(__('need login'), '/', 20);
        }
        parent::beforeFilter();
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

    public function login() {
        $this->Auth->redirect($_SERVER['HTTP_REFERER']);
        $o = new MBOpenTOAuth(MB_AKEY, MB_SKEY);
        $oauthkeys = $o->getRequestToken(MB_CALLBACK_URL); 
        $this->Session->write('Auth.QQOauthKeys', $oauthkeys);
        $aurl = $o->getAuthorizeURL($oauthkeys['oauth_token'], false, MB_CALLBACK_URL);
        //header('location:' . $aurl);
        $this->redirect($aurl);
        exit;
    }

    public function loginCallback() {
        $oauthkeys = $this->Session->read('Auth.QQOauthKeys');
        $o = new MBOpenTOAuth(MB_AKEY, MB_SKEY, $oauthkeys['oauth_token'], $oauthkeys['oauth_token_secret']);
        $login_token = $o->getAccessToken($_REQUEST['oauth_verifier']); //获取ACCESSTOKEN
        if (empty($login_token['name'])) {
            $this->__message('error user.');
        }
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
                                "source" => 'qq',
                                "Oauthbind.oauth_name" => $login_token['name'],
                            ),
                        ),
                    ),
                    'fields' => array('User.*', 'Oauthbind.*'),
                ));
        //print_r($userinfo);exit;
        $current_time = date('Y-m-d H:i:s');
        $this->client = new MBApiClient(MB_AKEY, MB_SKEY, $login_token['oauth_token'], $login_token['oauth_token_secret']);
        $qq_user = $this->client->getUserInfo();
//         print_r($qq_user);print_r($login_token);
        if ($qq_user['ret'] == 0 && $qq_user['data']) {
            $qq_user = $qq_user['data'];
        }
        //print_r($qq_user);exit;
        if ($qq_user['sex'] == '1') {
            $gender = 1; //男
        } else {
            $gender = 0; //女
        }
        if (empty($userinfo)) {
            // 第一次使用第三方帐号登录，用户不存在的，要求输入用户名，密码和邮箱。
            /**
             * Todo. 注册新用户或者绑定以注册用户
             */
            $this->set('username',$login_token['name']);
            $this->Session->write('qq_auth_user', $qq_user);
            $this->Session->write('qq_auth_login_token', $login_token);
            
        } else {
            // 登录成功 
            $updateinfo = array(
                'nickname' => $qq_user['nick'],
                'screen_name' => $qq_user['nick'],
                'image' => $qq_user['head'],
                'sex' => $gender,
                'location' => $qq_user['location'],
                'last_login' => $current_time,
            );
            $userinfo['User'] = array_merge($userinfo['User'], $updateinfo);
            $user_id = $userinfo['User']['id'];
            $this->User->save($userinfo['User']);
            $this->Session->write('Auth.User', $userinfo['User']);
            $this->Cookie->write('Auth.User', $userinfo['User'], true, 0);

            $this->loadModel('Oauthbind');
            $oauth_bind = $this->Oauthbind->find('first', array(
                        'conditions' => array(
                            'oauth_uid' => $login_token['uid'],
                            'oauth_name' => $login_token['name'],
                            'user_id' => $user_id,
                            'source' => 'qq',
                        ),
                    ));

            if (empty($oauth_bind)) {
                $oauth_bind = array(
                    'user_id' => $user_id,
                    'oauth_uid' => $login_token['uid'],
                    'oauth_name' => $login_token['name'],
                    'oauth_token' => $login_token['oauth_token'],
                    'oauth_token_secret' => $login_token['oauth_token_secret'],
                    'source' => 'qq',
                );
                $this->Oauthbind->save($oauth_bind);
            } else {
                $oauth_bind['Oauthbind']['updated'] = date('Y-m-d H:i:s');
                $oauth_bind['Oauthbind']['oauth_token'] = $login_token['oauth_token'];
                $oauth_bind['Oauthbind']['oauth_token_secret'] = $login_token['oauth_token_secret'];
                $this->Oauthbind->save($oauth_bind);
            }

            $user_oauths = $this->Oauthbind->find('all', array(
                        'conditions' => array(
                            'user_id' => $user_id,
                        ),
                    ));
            $this->Session->write('Auth.Oauthbind', $user_oauths);
            //print_r($oauth_bind);exit;
            //header('location:' . $this->Auth->redirect());
            $this->redirect($this->Auth->redirect());
        }
    }

    public function register(){
        $this->autoRender = false;
        $qq_user = $this->Session->read('qq_auth_user');
        $login_token = $this->Session->read('qq_auth_login_token');

        $userinfo = array(
            'username' => $_POST['username'],
            'password' => Security::hash($_POST['password'], null, true),
            'nickname' => $qq_user['nick'],
            'screen_name' => $qq_user['nick'],
//				'email'=> $qq_user['profile_image_url'],
            'image' => $qq_user['head'],
            //'website' => $qq_user['url'],
            //'sina_domain' => $qq_user['domain'],
            'sex' => $gender,
            'location' => $qq_user['location'],
            'description' => $qq_user['introduction'],
            'last_login' => $current_time,
            'created' => $current_time,
            'city' => $qq_user['city_code'],
            'province' => $qq_user['province_code'],
            'activation_key' => md5(uniqid()),
            'status' => 1,
        );
        $this->loadModel('User');
        $this->loadModel('Oauthbind');
//            uc_user_register($username, $password, $email, $questionid = '', $answer = '', $regip = '') ;
        $this->User->save($userinfo);
        print_r($userinfo);

        

        $userinfo['id'] = $user_id = $this->User->getLastInsertID();
        $this->Session->write('Auth.User', $userinfo);
        $this->Cookie->write('Auth.User', $userinfo, true, 0);

        $oauth_bind = array(
            'user_id' => $user_id,
            'oauth_uid' => $login_token['uid'],
            'oauth_name' => $login_token['name'],
            'oauth_token' => $login_token['oauth_token'],
            'oauth_token_secret' => $login_token['oauth_token_secret'],
            'source' => 'qq',
        );
        $this->Oauthbind->save($oauth_bind);
        $this->redirect($this->Auth->redirect());
    }

}

?>