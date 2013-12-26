<?php

/**
 * Users Controller
 *
 * PHP version 5
 *
 * @category Controller
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class UsersController extends AppController {

    /**
     * Controller name
     *
     * @var string
     * @access public
     */
    var $name = 'Users';
    var $components = array(
    	'Auth',
        'Email',
        'Kcaptcha',
    	'Cookie' => array('name' => 'SAECMS', 'time' => '+2 weeks'),
    );
    /**
     * Models used by the Controller
     *
     * @var array
     * @access public
     */
    var $uses = array('User', 'Oauthbind');
    
    public function beforeFilter(){
    	parent::beforeFilter();
    	if(defined('UC_APPID')){
    		$this->Auth->authenticate = array('UCenter');
    	}
    	$this->Auth->allowedActions = array('register','login','forgot','captcha','reset');
    }

    function captcha() {
        error_reporting(0);
        if (0) {
            //Kcaptcha
            $this->Kcaptcha->render();
        }
//		else
//		{ 
//			//Securimage
//			$this->autoRender = false;  
//	        //override variables set in the component - look in component for full list 
////	        $this->captcha->image_height = 75; 
////	        $this->captcha->image_width = 350; 
////	        $this->captcha->image_bg_color = '#ffffff'; 
////	        $this->captcha->line_color = '#cccccc'; 
////	        $this->captcha->arc_line_colors = '#999999,#cccccc'; 
////	        $this->captcha->code_length = 5; 
////	        $this->captcha->font_size = 45; 
//	        $this->captcha->text_color = '#000000'; 
//	        $this->captcha->show(); //dynamically creates an image 
//        
//		
//		}
    }

    public function account() {
        $this->pageTitle = __('Users', true);
    }

    function index() {
        $this->pageTitle = __('Users', true);
        $this->layout = 'user_portlet';
    }

    function layout() {
        $this->pageTitle = __('Users', true);
        $this->layout = 'user_portlet';
    }

    function checkusername() {
        print_r($this->data);
        $user = $this->User->findByUsername($username);
        print_r($user);
        exit;
    }

    function register() {
        $this->pageTitle = lang('user_register');
        if (!empty($this->data)) {
        	$this->User->create();            
            $this->data['User']['role_id'] = Configure::read('User.defaultroler'); // Registered defaultroler
            $this->data['User']['activation_key'] = md5(uniqid());
            $useractivate = Configure::read('User.activate');
            if ($useractivate == 'activate') {
                $this->data['User']['status'] = 1;
            } else {
                $this->data['User']['status'] = 0;
            }
            if(!empty($this->data['User']['password'])){
            	$this->data['User']['password'] = Security::hash($this->data['User']['password'], null, true);
            }
            print_r($this->data);
            if(!empty($this->data['User']['password'])){
            	$this->data['User']['password'] = Security::hash($this->data['User']['password'], null, true);
            }
            print_r($this->data);
            if ($this->data['User']['password'] != Security::hash($this->data['User']['password_confirm'], null, true)) {
                $this->Session->setFlash(lang('two_password_is_not_equare.'));
            } else {
            	$has_error = false;
            	if(defined('UC_APPID')){
            		App::import('Vendor', '',array('file' => 'uc_client'.DS.'client.php'));
            		$uid = uc_user_register( $this->data['User']['username'], $this->data['User']['password'], $this->data['User']['email'],'','', $this->request->clientIp());
            		if($uid<=0){
            			if($uid == -1) {
            				$error_msg = '用户名不合法';
            			} elseif($uid == -2) {
            				$error_msg = '包含不允许注册的词语';
            			} elseif($uid == -3) {
            				$error_msg = '用户名已经存在';
            			} elseif($uid == -4) {
            				$error_msg = 'Email 格式有误';
            			} elseif($uid == -5) {
            				$error_msg = 'Email 不允许注册';
            			} elseif($uid == -6) {
            				$error_msg = '该 Email 已经被注册';
            			} else{
            				$error_msg = '未知错误';
            			}
            			$has_error = true;
            		}
            		else if($uid>0){
            			$this->data['User']['id'] = $uid;
            		}
            	}
                if ($has_error==false && $this->User->save($this->data)) {                	
//	                $this->autoRender = false;
                    if ($useractivate == 'email') {
                        $this->Email->from = Configure::read('Site.title') . ' '
                                . '<SaeCMS@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME'])) . '>';
                        $this->Email->to = $this->data['User']['email'];
                        $this->Email->subject = __('[' . Configure::read('Site.title') . '] Please activate your account', true);
                        $this->Email->template = 'register';

                        $this->data['User']['password'] = null;
                        $this->set('user', $this->data);
                        $this->Email->send();

                        $this->Session->setFlash(__('Please receive email and activate your account.', true));
                    } elseif ($useractivate == 'hand') {
                        $this->Session->setFlash(__('Please wait administrator to activate your account.', true));
                    }
//	                $this->redirect(array('action' => 'login'));
                } else {
                    $this->Session->setFlash(__('Registe error.Username or email exists.', true));
                }
            }
        } else {
            $this->Session->delete('Message.flash');
        }

        // 加载选项，默认值等
        //$this->__loadFormValues('User');
    }

    function activate($username = null, $key = null) {

        if ($username == null || $key == null) {
            $user = $this->Auth->user();
            if (!empty($user['User']) && $user['User']['status'] == 0) {
                //'您的用户没有激活';
                //exit;
            } else {
                $this->redirect(array('action' => 'login'));
            }
        } else {
            if ($this->User->hasAny(array(
                        'User.username' => $username,
                        'User.activation_key' => $key, 'User.status' => 0,)
            )) {
                $user = $this->User->findByUsername($username);
                $this->User->id = $user['User']['id'];
                $this->User->saveField('status', 1);
                $activation_key = md5(uniqid());
                $this->User->saveField('activation_key', $activation_key);

                // 更新cookie与session
                $user['User']['status'] = 1;
                $user['User']['activation_key'] = $activation_key;
                $this->Session->write('Auth.User', $user['User']);
                $this->Cookie->write('User', $user['User']);
            } else {
                //exit;
                $this->__message('激活链接已失效，请重新获取。', array('action' => 'activate'));
                //$this->redirect(array('action' => 'activate'));
            }
        }
    }

    function edit($id=null) {

        $userinfo = $this->Auth->user();
        if ($userinfo['id']) {
            $datainfo = $this->{$this->modelClass}->find('first', array('recursive' => -1, 'conditions' => array('id' => $userinfo['id'])));
            if (empty($datainfo)) {
                throw new ForbiddenException(__('You cannot edit this data'));
            }

            if (!empty($this->data)) {
                if ($this->{$this->modelClass}->save($this->data)) {
                    $this->Session->setFlash(__('The Data has been saved', true));
                    //$this->redirect(array('action'=>'index'));
                } else {
                    $this->Session->setFlash(__('The Data could not be saved. Please, try again.', true));
                }
                $successinfo = array('success' => __('Edit success', true), 'actions' => array('OK' => 'closedialog'));
                //echo json_encode($successinfo);
                //return ;
            }
            if (empty($this->data)) {
                $this->data = $datainfo;
            }
        } else {
            $this->Session->setFlash(__('You are not authorized to access that location.', true));
            $this->redirect(array('action' => 'login'));
        }
    }

    function editpassword() {
        $userinfo = $this->Auth->user();
        if (!$userinfo['id']) {
            $this->Session->setFlash(__('You are not authorized to access that location.', true));
            $this->redirect(array('action' => 'login'));
        }
        if (!empty($this->data) && isset($this->data['User']['password'])) {
            $before_edit = $this->{$this->modelClass}->read(null, $userinfo['id']);

            if ($before_edit['User']['password'] == Security::hash($this->data['User']['password'], null, true)) {

                if (!empty($this->data['User']['new_password']) && $this->data['User']['new_password'] == $this->data['User']['password_confirm']) {
                    $user = array();
                    $user['User']['id'] = $userinfo['id'];
                    $user['User']['password'] = Security::hash($this->data['User']['new_password'], null, true);
                    $user['User']['activation_key'] = md5(uniqid());

                    if ($this->User->save($user['User'])) {
                        $this->Session->setFlash(__('Password is updated success.', true));
                    }
                } else {
                    $this->Session->setFlash(__('Two password is empty or not equare.', true));
                }
            } else {
                $this->Session->setFlash(__('Your password is not right.', true));
            }
        } else {
            $this->Session->delete('Message.flash');
        }
    }

    function forgot() {
        $this->pageTitle = __('Forgot Password', true);
        if (!empty($this->data) && isset($this->data['User']['username'])) {
            $user = $this->User->findByUsername($this->data['User']['username']);
            if (!isset($user['User']['id'])) {
                $this->redirect(array('action' => 'login'));
            }
            $this->User->id = $user['User']['id'];
            $activationKey = md5(uniqid());
            $this->User->saveField('activation_key', $activationKey);
            $this->set(array('user'=>$user, 'activationKey'=>$activationKey));
			
            $this->Email->from = Configure::read('Site.title') . ' '
            . '<' . Configure::read('Site.email') . '>';
            
            //$this->Email->from = Configure::read('Site.title') . ' '
            //        . '<SaeCMS@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME'])) . '>';
            $this->Email->to = $user['User']['email'];
            $this->Email->subject = '[' . Configure::read('Site.title') . '] ' . __('Reset Password', true);
            $this->Email->template = 'forgot_password';
            $this->autoRender = false;
            
//             $this->Email->viewRender('Dzstyle');
            if ($this->Email->send()) {
                $this->redirect(array('action' => 'login'));
            }
            die();
        }
    }

    function reset($username = null, $key = null) {
        $this->pageTitle = __('Reset Password', true);

        if ($username == null || $key == null) {
            $this->redirect(array('action' => 'login'));
        }

        $user = $this->User->find('first', array(
                    'conditions' => array(
                        'User.username' => $username,
                        'User.activation_key' => $key,
                    ),
                ));
        if (!isset($user['User']['id'])) {
            $this->redirect(array('action' => 'login'));
        }

        if (!empty($this->data) && isset($this->data['User']['password'])) {
            $this->User->id = $user['User']['id'];
            $user['User']['password'] = Security::hash($this->data['User']['password'], null, true);
            $user['User']['activation_key'] = md5(uniqid());
            if ($this->User->save($user['User'])) {
                $this->redirect(array('action' => 'login'));
            }
        }

        $this->set(array('user'=>$user, 'username'=>$username, 'key'=>$key));
    }

    function login() {
        $redirect = $this->data['User']['referer'] ? $this->data['User']['referer'] : ($_GET['referer'] ? $_GET['referer'] : $this->Auth->redirect());
        $success = false;
        
        if(empty($this->data) && $this->request->query['data']){ //get 方式传入时,phonegap
        	$this->data = $this->request->query['data'];
        }
        
        if ($id = $this->Auth->user('id')) { //已经登录的
            $this->User->id = $id;
            $this->User->updateAll(array(
                'last_login' => "'".date('Y-m-d H:i:s')."'"
            ),array('id' => $id,));
            $success = true;
        }
        elseif (!empty($this->data['User'])) { // 通过表单登录
            if ($this->Auth->login()) {
                $this->User->id = $this->Auth->user('id');
                $this->User->updateAll(array(
                    'last_login' => "'".date('Y-m-d H:i:s')."'",
                ),array('id' => $this->User->id,));
                
                $this->Session->setFlash(lang('login_success').$this->Session->read('Auth.User.session_flash'));
                $success = true;
            }
        }
        
        if ($success) {
        	$wx_openid = $this->Session->read('wx_openid');
        	if($wx_openid){        		
        		$this->loadModel('Oauthbinds');
        		$oauth = $this->Oauthbinds->find('first', array('conditions' => array('oauth_openid' => $wx_openid,'source'=>'weixin',)));
        		if(!empty($oauth) && !empty($oauth['Oauthbinds']['user_id'])){
        			if($this->User->id != $oauth['Oauthbinds']['user_id']){
        				$this->Oauthbinds->updateAll(array('user_id'=>	$this->User->id),
        					array('oauth_openid' => $wx_openid,'source'=>'weixin')
        				);
        			}
        		}
        		else{
        			$this->Oauthbinds->save(array(
        				'source'=>'weixin',
        				'user_id'=>	$this->User->id,
        				'oauth_openid' => $wx_openid,
        				'created'=>date('Y-m-d H:i:s'),
        				'updated'=>date('Y-m-d H:i:s'),
        			));
        		}
        	}
        	
            $this->Hook->call('loginSuccess');            
            if ($this->RequestHandler->accepts('json') || $this->RequestHandler->isAjax() || isset($_GET['inajax'])) {
                // ajax 操作
                $user = $this->Auth->user();
                $userinfo = array(
                	'id' => $user['id'],
                	'username' => $user['username'],
                );
                
                if(!empty($this->data['User']['remember_me'])){
                	$cookietime = 2592000; // 一月内30*24*60*60
                }
                else{
                	$cookietime = 0;
                }
                $this->Cookie->write('Auth.User',$userinfo, true, $cookietime);
                
                $successinfo = array('success' => __('Login success'), 
                		'userinfo' => $userinfo,
                		'tasks'=>array(array('dotype'=>'reload')));
                
                $this->autoRender = false; // 不显示模板
                
                $content = json_encode($successinfo);
                if($_GET['jsoncallback']){
                	$content = $_GET['jsoncallback'] . '(' . $content . ');';
                }
                $this->response->body($content);
                $this->response->send(); exit;// exit退出时，cookie信息未发出，cookie创建失败。
            } else {
                $this->redirect($redirect);
            }
        } elseif (isset($this->data['User']['username'])) {
            if ($this->RequestHandler->accepts('json') || $this->RequestHandler->isAjax() || isset($_GET['inajax'])) {
                // ajax 操作
                $errorinfo = array('error' => __('user not exists or password not right'), 'tasks' => array(array('dotype' => 'html', 'selector' => '#login_errorinfo', 'content' => __('username or password not right'))));
                $content = json_encode($errorinfo);
                $this->autoRender = false; // 不显示模板
                if($_GET['jsoncallback']){
                	echo $_GET['jsoncallback'] . '(' . $content . ');';
                }
                else{
                	echo $content;
                }
            }
            else {
            	$this->Session->setFlash(__('user not exists or password not right'));
            }
            //$this->redirect(array('action' => 'forgot'), 401);
        } else {
            if ($this->RequestHandler->accepts('json') || $this->RequestHandler->isAjax() || isset($_GET['inajax'])) {
                // ajax 操作
                $this->set('isajax', true);
            }
        }
        $this->data['User']['referer'] = $redirect;
    }

    function logout() {        
        
        $this->Cookie->destroy();
        $this->Session->destroy();
        unset($this->currentUser);
        if(defined('UC_APPID')){
            App::import('Vendor', '',array('file' => 'uc_client'.DS.'client.php'));
            if(function_exists('uc_user_synlogout')){
                $synlogout = uc_user_synlogout();
            }
        }
        $this->Session->setFlash(__('Logout Success', true).$synlogout);
        $this->redirect($this->referer());
    }

    function view($username) {
        $user = $this->User->findByUsername($username);
        if (!isset($user['User']['id'])) {
            $this->redirect('/');
        }

        $this->pageTitle = $user['User']['name'];
        $this->set('user',$user);
    }

    /**
     * 
     */
    function sinalist() {
        $user = $this->User->findByUsername($username);
        if (!isset($user['User']['id'])) {
            $this->redirect('/');
        }

        $this->pageTitle = $user['User']['name'];
        $this->set('user',$user);
    }

    function myquestion($type='', $page=1, $count = 30) {
        $this->loadModel('Question');
        $questionlist = $this->Question->find('all',
                        array('conditions' => array(
                                'creator' => $this->currentUser['User']['sina_uid'],
                                'published' => 1,
                            )
                ));
        $this->set('questionlist', $questionlist);
    }

    function myweibo($type='', $page=1, $count = 30) {
        $this->loadModel('Weibo');
        $weibolist = $this->Weibo->find('all',
                        array(
                            'conditions' => array(
                                'Weibo.creator' => $this->currentUser['User']['sina_uid'],
                                'Weibo.published' => 1,
                            ),
                            'fields' => array('Weibo.*', 'Question.*'),
                            'limit' => 20,
                            'joins' => array(
                                array(
                                    'table' => Inflector::tableize('Question'),
                                    'alias' => 'Question',
                                    'type' => 'left',
                                    'conditions' => array('Weibo.data_id=Question.id', 'Weibo.model' => 'Question'),
                                ),
                            )
                ));
        $this->set('weibolist', $weibolist);
        $this->set('action', 'myweibo');
    }

}

?>