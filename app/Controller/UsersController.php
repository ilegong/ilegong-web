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
class UsersController extends AppController
{
    const WX_BIND_REDI_PREFIX = 'redirect_url_';

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
        'FileUpload',
        'UserConsignee',
        'Cookie' => array('name' => 'SAECMS', 'time' => '+2 weeks'),
    );
    /**
     * Models used by the Controller
     *
     * @var array
     * @access public
     */
    var $uses = array('User', 'Oauthbinds', 'WxOauth');

    public function beforeFilter()
    {
        parent::beforeFilter();

        $this->Auth->authenticate = array('WeinxinOAuth', 'Form', 'Pys', 'Mobile');

        $this->Auth->allowedActions = array('register', 'login', 'forgot', 'captcha', 'reset', 'wx_login', 'wx_auth', 'wx_menu_point', 'login_state', 'get_spring_coupon', 'get_proxies', 'get_id_and_proxies');
        $this->set('op_cate', 'me');
    }

    function captcha()
    {
        error_reporting(0);
        if (0) {
            //Kcaptcha
            $this->Kcaptcha->render();
        }
    }

    public function account()
    {
        $this->pageTitle = __('Users', true);
    }

    function index()
    {
        $this->pageTitle = __('Users', true);
        $this->layout = 'user_portlet';
    }

    function goTage()
    {
        $code = authcode($this->currentUser['id'] . ',' . $this->currentUser['username'], 'ENCODE');
        $this->set('code', rawurlencode($code));
        $this->layout = false;
    }

    function layout()
    {
        $this->pageTitle = __('Users', true);
        $this->layout = 'user_portlet';
    }

    function checkusername()
    {
        print_r($this->data);
//        $user = $this->User->findByUsername($username);
        exit;
    }

    function register()
    {
        $this->pageTitle = lang('user_register');
        if (!empty($this->data)) {
            $readCode = $this->data['User']['code'];
            $msgCode = $this->Session->read('messageCode');
            $current_post_num = $this->Session->read('current_register_phone');
            if ($msgCode) {
                $codeLog = json_decode($msgCode, true);
                if ($codeLog && is_array($codeLog) && $codeLog['code'] == $readCode && (time() - $codeLog['time'] < 30 * 60)) {
                    $this->User->create();
                    $this->data['User']['role_id'] = Configure::read('User.defaultroler'); // Registered defaultroler
                    $this->data['User']['activation_key'] = md5(uniqid());
                    $useractivate = Configure::read('User.activate');
                    if ($useractivate == 'activate') {
                        $this->data['User']['status'] = 1;
                    } else {
                        $this->data['User']['status'] = 0;
                    }

                    /*对密码加密*/
                    $src_password = $this->data['User']['password'];

                    $this->data['User']['nickname'] = trim($this->data['User']['nickname']);
                    $this->data['User']['username'] = NULL;
                    $this->data['User']['mobilephone'] = trim($this->data['User']['mobilephone']);

                    if (mb_strlen($this->data['User']['nickname'], 'UTF-8') < 2) {
                        $this->Session->setFlash('用户名长度不能小于2个字符');
                    } else if ($this->data['User']['mobilephone'] != $current_post_num) {
                        $this->Session->setFlash('请重新验证您的手机号码');
                    } else if ($this->data['User']['password'] != $this->data['User']['password_confirm']) {
                        $this->Session->setFlash('两次密码不相等');
                    } else if (is_null($this->data['User']['password']) || trim($this->data['User']['password']) == '') {
                        $this->Session->setFlash(__('Password should be longer than 6 characters'));
                    } else if ($this->User->hasAny(array('User.mobilephone' => $this->data['User']['mobilephone']))) {
                        $this->Session->setFlash(__('Mobilephone is taken by others.'));
                    } else if ($this->User->hasAny(array('User.username' => $this->data['User']['mobilephone']))) {
                        $this->Session->setFlash(__('你的账号已被注册'));
                    } else {
                        $this->data['User']['password'] = Security::hash($this->data['User']['password'], null, true);
                        $has_error = false;
                        $this->data['User']['uc_id'] = 0;
                        if ($has_error == false && $this->User->save($this->data)) {
                            //$this->autoRender = false;
                            $this->data['User']['id'] = $this->User->getLastInsertID();
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
                            } else {
                                $this->Session->setFlash('注册成功!');
                            }
                            $this->after_create_user($this->data['User']['id']);
                            $data = $this->User->find('first', array('conditions' => array('id' => $this->data['User']['id'])));
                            $this->Session->write('Auth.User', $data['User']);
                            $this->redirect('/');
                        } else {
                            $this->Session->setFlash('注册失败，用户名或手机号已存在');
                        }
                    }
                } else {
                    $this->Session->setFlash('短信验证码错误');
                }

            } else {
                $this->Session->setFlash('短信验证未成功，请重新获取');
            }
        } else {
            $this->Session->delete('Message.flash');
        }

        // 加载选项，默认值等
        //$this->__loadFormValues('User');
    }

    function activate($username = null, $key = null)
    {

        $this->pageTitle = __('激活');

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
            )
            ) {
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

    function edit($id = null)
    {

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

    function my_profile()
    {
        $userinfo = $this->Auth->user();
        if ($userinfo['id']) {
            $datainfo = $this->{$this->modelClass}->find('first', array('recursive' => -1, 'conditions' => array('id' => $userinfo['id'])));
            if (empty($datainfo)) {
                throw new ForbiddenException(__('You cannot view this data'));
            }
            $this->set('profile', $datainfo['User']);
        } else {
            $this->Session->setFlash(__('You are not authorized to access that location.', true));
            $this->redirect(array('action' => 'login'));
        }
        $this->pageTitle = __('个人信息');
        $this->set('hideNav', true);
    }


    function editusername()
    {
        $userinfo = $this->Auth->user();
        if (!$userinfo['id']) {
            $this->Session->setFlash(__('You are not authorized to access that location.', true));
            $this->redirect(array('action' => 'login'));
        }

        $this->data['User']['username'] = trim($this->data['User']['username']);
        if (!empty($this->data) && !empty($this->data['User']['username']) && !empty($this->data['User']['password'])) {

            if ($this->User->hasAny(array('User.username' => $this->data['User']['username']))) {
                $this->Session->setFlash(__('Username is taken by others.'));
            }

            $user = array();
            $user['User']['id'] = $userinfo['id'];
            $user['User']['username'] = $this->data['User']['username'];
            $user['User']['password'] = Security::hash(trim($this->data['User']['password']), null, true);
            $user['User']['activation_key'] = md5(uniqid());

            if ($this->User->save($user['User'])) {
                $this->Session->setFlash(__('成功设置用户名与密码'));
            } else {
                $this->Session->setFlash(__('设置用户名与密码失败', true));
            }
        } else {
            $this->Session->delete('Message.flash');
        }
    }

    function wxBindToAccount($defUsername = '')
    {
        $this->pageTitle = __('绑定帐号');
        $userinfo = $this->Auth->user();
        $uid = $userinfo['id'];
        if (!$uid) {
            $this->Session->setFlash(__('You are not authorized to access that location.', true));
            $this->redirect(array('action' => 'login'));
        }

        $this->loadModel('Oauthbind');
        $wxBind = $this->Oauthbind->findWxServiceBindByUid($uid);
        if (empty($wxBind)) {
            $this->set('error', 'not_wx_user');
            return;
        }

        $this->data['User']['username'] = trim($this->data['User']['username']);

        $oauth_openid = $wxBind['oauth_openid'];
        if (!empty($wxBind) && $userinfo['username'] != $oauth_openid) {
            $this->__message(__('您的微信已经与帐号' . $userinfo['username'] . '绑定，不能再绑定其他帐号'), '/', 60);
            return;
        }

        if (!empty($this->data) && !empty($this->data['User']['username'])) {

            if (!empty($wxBind) && $this->data['User']['username'] == $userinfo['username']) {
                $this->Session->setFlash(__('绑定成功！'));
                return;
            }

            if (!empty($this->data['User']['username']) && !empty($this->data['User']['password'])) {

                //TODO: 防止一个用户名绑定了多个微信
                $newUser = $this->User->find('first', array('conditions' => array(

                    'OR' => array(
                        'username' => $this->data['User']['username'],
                        'mobilephone' => $this->data['User']['username'],
                    ),
                    'password' => Security::hash(trim($this->data['User']['password']), null, true),
                )));

                if (!empty($newUser)) {
                    $newUserId = $newUser['User']['id'];
                    if ($uid != $newUserId) {
                        $this->transferUserInfo($uid, $newUserId);
                        $this->Oauthbind->update_wx_bind_uid($oauth_openid, $uid, $newUserId);
                        $this->logoutCurrUser();
                        $this->Auth->login();
                    }
                    $this->__message('绑定成功! 自动跳转到您的个人中心', '/users/me.html', 5);
                } else {
                    $this->Session->setFlash(__('用户名或者密码不正确', true));
                }
            } else {
                $this->Session->setFlash(__('请输入用户名、密码', true));
            }
        }
    }

    function edit_nick_name()
    {
        $userinfo = $this->Auth->user();
        if (!$userinfo['id']) {
            $err = __('You are not authorized to access that location.', true);
        } else {
            $newNickname = htmlspecialchars(trim($_POST['nickname']));
            if (mb_strlen($newNickname) < PROFILE_NICK_MIN_LEN) {
                $err = '昵称至少需要' . PROFILE_NICK_MIN_LEN . '个字符！';
            } else if ($this->nick_should_edited($newNickname)) {
                $err = '昵称不能使用微信用户XXX!';
            } else if ($this->User->hasAny(array('User.nickname' => "$newNickname"))) {
                $err = __('Username is taken by others.');
            } else if (!$this->User->updateAll(array('User.nickname' => "'$newNickname'"), array('User.id' => $userinfo['id']))) {
                $err = __('系统提交保存时失败，请重试');
            } else {
                $this->Session->write('Auth.User.nickname', $newNickname);
                $err = 'ok';
            }
        }

        echo $err;
        $this->autoRender = false;
        return;
    }

    function my_coupons()
    {
        $this->loadModel('CouponItem');
        $coupons = $this->CouponItem->find_my_all_coupons($this->currentUser['id']);
        $this->set(compact('coupons'));
        $this->pageTitle = __('我的优惠劵');
        $this->set('hideNav', true);
    }

    function my_offers()
    {
        $this->loadModel('SharedOffer');
        $sharedOffers = $this->SharedOffer->find_my_all_offers($this->currentUser['id']);
        $expiredIds = array();
        foreach ($sharedOffers as $o) {
            $expired = is_past($o['SharedOffer']['start'], $o['ShareOffer']['valid_days']);
            if ($expired) {
                $expiredIds[] = $o['SharedOffer']['id'];
            } else if (SharedOffer::slicesSharedOut($o['SharedOffer']['id'], $o['SharedOffer']['status'])) {
                $soldOuts[] = $o['SharedOffer']['id'];
            }
        }
        $this->set(compact('sharedOffers', 'expiredIds', 'soldOuts'));
        $this->pageTitle = __('我的红包');
    }

    function setpassword()
    {
        $this->autoRender = false;
        $userinfo = $this->Auth->user();
        if (empty($userinfo['id'])) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $new_password = $_REQUEST['password'];
        if (empty($new_password)) {
            echo json_encode(array('success' => false, 'reason' => 'password_empty'));
            return;
        }
        $user = array();
        $user['id'] = $userinfo['id'];
        $user['password'] = Security::hash($new_password, null, true);
        $user['activation_key'] = md5(uniqid());
        if ($this->User->save($user)) {
            echo json_encode(array('success' => true));
            return;
        }
        echo json_encode(array('success' => false, 'reason' => 'server_error'));
        return;
    }

    function editpassword()
    {
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

    function forgot()
    {
        $this->pageTitle = __('Forgot Password', true);
        if (!empty($this->data) && isset($this->data['User']['mobilephone'])) {
            $msgCode = $this->Session->read('messageCode');
            $readCode = $this->data['User']['code'];
            if (empty($readCode)) {
                $this->Session->setFlash('短信验证码不能为空');
                return;
            } else {
                if (!$msgCode) {
                    $this->Session->setFlash('短信验证失败');
                    return;
                } else {
                    $codeLog = json_decode($msgCode, true);
                    if (!($codeLog && is_array($codeLog) && $codeLog['code'] == $readCode && (time() - $codeLog['time'] < 30 * 60))) {
                        $this->Session->setFlash('短信验证未成功，请重新获取');
                        return;
                    }
                }
            }
            if (empty($this->data['User']['password'])) {
                $this->Session->setFlash('密码不为空');
                return;
            }
            $user = $this->User->findByMobilephone($this->data['User']['mobilephone']);
            if (!isset($user['User']['id'])) {
                $this->Session->setFlash('没有与该手机对应的用户');
                $this->redirect(array('action' => 'register'));
            }
            $this->User->id = $user['User']['id'];
            $activationKey = md5(uniqid());
            $this->User->saveField('activation_key', $activationKey);
            $this->set(array('user' => $user, 'activationKey' => $activationKey));

            $user['User']['password'] = Security::hash($this->data['User']['password'], null, true);
            if ($this->User->save($user)) {
                $this->Session->setFlash(__('Password is updated success.', true));
                $this->redirect(array('action' => 'login'));
            }
            die();
        }
    }

    function update_user_intro()
    {
        $this->autoRender = false;
        $user_intro = $_REQUEST['user_intro'];
        $user_nickname = $_REQUEST['user_nickname'];
        $user_id = $_REQUEST['user_id'];
        $this->User->id = $user_id;
        //$this->User->saveField('description', $user_intro);
        $this->User->updateAll(array('description' => "'" . $user_intro . "'", 'nickname' => "'" . $user_nickname . "'"), array('id' => $user_id));
        Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $user_id, '');
        echo json_encode(array('success' => true));
        return;
    }

    function reset($username = null, $key = null)
    {
        $this->pageTitle = __('Reset Password', true);

        if ($username == null || $key == null) {
            $this->redirect(array('action' => 'login'));
        }

        if ($username == '') {
            $this->Session->setFlash(__('您没有设置用户名，找回密码请联系技术客服。'));
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

        $this->set(array('user' => $user, 'username' => $username, 'key' => $key));
    }

    function login_state()
    {
        $login = true;
        if (!$this->currentUser['id']) {
            if (!$this->Auth->login()) {
                $login = false;
            }
        }
        echo json_encode(array('login' => $login));
        $this->autoRender = false;
    }

    function login()
    {
        $this->pageTitle = __('登录');
        $redirect = $this->data['User']['referer'] ? $this->data['User']['referer'] : ($_REQUEST['referer'] ? $_REQUEST['referer'] : $this->Auth->redirect());
        $success = false;
        if (empty($this->data) && $this->request->query['data']) { //get 方式传入时,phonegap
            $this->data = $this->request->query['data'];
        }
        if (!empty($_GET['force_login'])) {
            $this->logoutCurrUser();
        }
        if ($id = $this->Auth->user('id')) { //已经登录的
            $this->User->id = $id;
            $this->User->updateAll(array(
                'last_login' => "'" . date('Y-m-d H:i:s') . "'",
                'last_ip' => "'" . $this->request->clientIp(false) . "'"
            ), array('id' => $id,));
            $success = true;
        } else { // 通过表单登录
            $sid = $this->Session->id();
            if ($this->Auth->login()) {
                //可能通过微信授权登陆成功
                $newSid = $this->Session->id();
                $this->User->id = $this->Auth->user('id');
                $this->User->updateAll(array(
                    'last_login' => "'" . date('Y-m-d H:i:s') . "'",
                    'last_ip' => "'" . $this->request->clientIp(false) . "'"
                ), array('id' => $this->User->id,));
                //remove old b2c merge cart
                //$this->loadModel('Cart');
                //$this->Cart->merge_user_carts_after_login($this->User->id, $sid);
                $this->Session->setFlash('登录成功' . $this->Session->read('Auth.User.session_flash'));
                $success = true;
            }
        }

        $login_by_account = isset($this->data['User']['username']);
        $login_by_phone = isset($this->data['User']['mobilephone']);
        if ($success) {
            $this->Hook->call('loginSuccess');
            $user = $this->Auth->user();
            $userinfo = array(
                'id' => $user['id'],
                'username' => $user['username'],
            );
            if (!empty($this->data['User']['remember_me'])) {
                $cookietime = 2592000; // 一月内30*24*60*60
            } else {
                $cookietime = 3600 * 24 * 7;
            }
            $this->Cookie->write('Auth.User', $userinfo, true, $cookietime);
            //ajax login
            if ($this->RequestHandler->accepts('json') || $this->RequestHandler->isAjax() || isset($_GET['inajax'])) {
                $successinfo = array('success' => '登录成功',
                    'userinfo' => $userinfo,
                    'tasks' => array(array('dotype' => 'reload')));
                $this->autoRender = false; // 不显示模板
                $content = json_encode($successinfo);
                if ($_GET['jsoncallback']) {
                    $content = $_GET['jsoncallback'] . '(' . $content . ');';
                }
                $this->response->body($content);
                $this->response->send();
                exit;// exit退出时，cookie信息未发出，cookie创建失败。
            } else {
                //from page login
                if (empty($redirect)) {
                    $redirect = '/weshares/index.html';
                }
                //$this->log('login success redirect ' . $redirect);
                $this->redirect($redirect);
                return;
            }
        } elseif ($login_by_account || $login_by_phone) {
            $loginFailMsg = __('username or password not right');
            if ($this->RequestHandler->accepts('json') || $this->RequestHandler->isAjax() || isset($_GET['inajax'])) {
                // ajax 操作
                if ($login_by_account) {
                    $errorinfo = array('error' => '用户名或密码错误', 'tasks' => array(array('dotype' => 'html', 'selector' => '#login_errorinfo', 'content' => $loginFailMsg)));
                } else {
                    $errorinfo = array('error' => '手机号码或密码错误', 'tasks' => array(array('dotype' => 'html', 'selector' => '#login_errorinfo', 'content' => $loginFailMsg)));
                }
                $content = json_encode($errorinfo);
                $this->autoRender = false; // 不显示模板
                if ($_GET['jsoncallback']) {
                    echo $_GET['jsoncallback'] . '(' . $content . ');';
                } else {
                    echo $content;
                }
            } else {
                $this->Session->setFlash($loginFailMsg);
                $this->set('fail_msg', $loginFailMsg);
            }
            //$this->redirect(array('action' => 'forgot'), 401);
        } else {
            if ($this->RequestHandler->accepts('json') || $this->RequestHandler->isAjax() || isset($_GET['inajax'])) {
                // ajax 操作
                $this->set('isajax', true);
            }
        }
        $this->set('supportWeixin', $this->is_weixin());
        $this->data['User']['referer'] = $redirect;
        $this->set('referer', $redirect);
        $this->set('errorType', $_REQUEST['type']);
        $this->Session->write('coupon-id', $_REQUEST['coupon-id']);//领取优惠券
        $this->set('login_by_account', $login_by_account);
    }

    function logout()
    {

        $this->logoutCurrUser();
        $this->Session->setFlash(__('Logout Success', true) . $synlogout);
        $referer = $this->referer();
        $this->log("Refer:" . $referer);
        $this->redirect('/users/login.html');
    }

    function view($username)
    {
        $user = $this->User->findByUsername($username);
        if (!isset($user['User']['id'])) {
            $this->redirect('/');
        }

        $this->pageTitle = $user['User']['name'];
        $this->set('user', $user);
    }

    function bindWxSub()
    {
        $this->Session->delete('Message.flash');
    }

    function after_bind_relogin()
    {
        $this->logoutCurrUser();
        $this->redirect('/users/wx_login?referer=/users/bindWxSub');
    }

    function me()
    {
        $this->pageTitle = __('个人中心');

        $this->loadModel('Order');
        $uid = $this->currentUser['id'];
        $count = $this->Order->count_by_status($uid);
        if (!is_array($count)) {
            $count = array();
        }
        $left_to_comment = $this->Order->count_to_comments($uid);
        $count[ORDER_STATUS_COMMENT] = $left_to_comment;

        $this->set('order_count', $count);

        $this->set('total_score', $this->User->get_score($uid));

        $mOrder = ClassRegistry::init('Order');
        $received_cnt = $mOrder->count_received_order($this->currentUser['id']);
        $this->set('received_cnt', $received_cnt);
    }


    function wx_menu_point()
    {
        $redirect = '/';
        if (!empty($_GET['referer_key'])) {
            $redirect = oauth_wx_goto($_GET['referer_key'], WX_HOST);
        }

        if ($_GET['do_wx_auth_if_not_log_in'] && (empty($this->currentUser) || !$this->currentUser['id'])) {
            $this->redirect(redirect_to_wx_oauth($redirect));
        } else {
            $this->redirect($redirect);
        }
    }

    function wx_login()
    {

        $ref = '';
        if (!empty($_GET['referer'])) {
            $ref = $_GET['referer'];
        } else if (!empty($_GET['referer_key'])) {
            $ref = oauth_wx_goto($_GET['referer_key'], WX_HOST);
        }
        $this->redirect(redirect_to_wx_oauth($ref, WX_OAUTH_USERINFO));
    }

    function get_consignee_list()
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $result = $this->UserConsignee->get_consignee_list($uid);
        echo $result;
        exit();
    }

    function save_consignee()
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(['success' => false, 'reason' => 'not_login']);
            exit();
        }
        $postStr = file_get_contents('php://input');
        $postDataArray = json_decode($postStr, true);
        $consignee = $this->UserConsignee->save_consignee($postDataArray, $uid);
        echo json_encode(['success' => true, 'consignee' => $consignee['OrderConsignee']]);
        exit();
    }

    function select_consignee($consignee_id)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(['success' => false, 'reason' => 'not_login']);
            exit();
        }
        $this->UserConsignee->select_consignee($consignee_id, $uid);
        echo json_encode(['success' => true]);
        exit();
    }

    function change_avatar()
    {
        $this->layout = null;
        $refer_url = $_REQUEST['ref'];
        $uid = $this->currentUser['id'];
        $this->set('refer_url', $refer_url);
        $this->set('uid', $uid);
    }


    function update_avatar()
    {
        $this->autoRender = false;
        $uid = $_POST['uid'];
        $url = $_POST['url'];
        if (!empty($url)) {
            $this->log('update avatar of user ' . $uid . ' to ' . $url, LOG_INFO);
            $this->User->id = $uid;
            $this->User->update(array('image' => "'" . $url . "'", 'avatar' => "'" . $url . "'"), array('id' => $uid));
            Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $uid, '');
        }
        echo json_encode(array('success' => true));
        exit();
    }

    function wx_auth()
    {
        $param_referer = $_GET['referer'];
        $oauth_wx_source = oauth_wx_source();
        if (!empty($_REQUEST['code'])) {
            $rtn = $this->WxOauth->find('all', array(
                'method' => 'get_access_token',
                'code' => $_REQUEST['code']
            ));

            if (!empty($rtn) && $rtn['WxOauth']['errcode'] == 0) {
                $res = $rtn['WxOauth'];
                $access_token = $res['access_token'];
                $openid = $res['openid'];
                $refresh_token = $res['refresh_token'];
                if (!empty($access_token) && !empty($res['openid']) && is_string($access_token) && is_string($openid)) {
                    $oauth = $this->Oauthbinds->find('first', array('conditions' => array('source' => $oauth_wx_source,
                        'oauth_openid' => $res['openid']
                    )));

                    $not_bind_yet = empty($oauth);
                    if (empty($oauth)) {
                        $oauth['Oauthbinds']['oauth_openid'] = $openid;
                        $oauth['Oauthbinds']['created'] = date(FORMAT_DATETIME);
                        $oauth['Oauthbinds']['source'] = $oauth_wx_source;
                        $oauth['Oauthbinds']['domain'] = $oauth_wx_source;
                    } else {
                        $old_serviceAccount_binded_uid = $oauth['Oauthbinds']['user_id'];
                    }
                    $oauth['Oauthbinds']['unionId'] = $res['unionid'];
                    $oauth['Oauthbinds']['oauth_token'] = $access_token;
                    $oauth['Oauthbinds']['oauth_token_secret'] = empty($refresh_token) ? '' : $refresh_token;
                    $oauth['Oauthbinds']['updated'] = date(FORMAT_DATETIME);
                    $oauth['Oauthbinds']['extra_param'] = json_encode(array('scope' => $res['scope'], 'expires_in' => $res['expires_in']));

                    $need_transfer = false;
                    $should_require_user_info = !$_GET['nru'];
                    $refer_by_state = '';
                    if (!empty($_REQUEST['state'])) {
                        $str = base64_decode($_REQUEST['state']);
                        $this->log("got state(after base64 decode):" . $str, LOG_INFO);
                        if (($idx = strpos($str, self::WX_BIND_REDI_PREFIX)) !== false) {
                            $should_require_user_info = false;
                            //TODO: handle decrypt risk
                            $old_openid = authcode(substr($str, 0, $idx), 'DECODE');
                            if (!empty($old_openid)) {
                                $r = $this->Oauthbinds->find('first', array('conditions' => array('oauth_openid' => $old_openid, 'source' => 'weixin',)));
                                if (!empty($r) && !empty($r['Oauthbinds']['user_id'])) {

                                    if (isset($old_serviceAccount_binded_uid) && ($old_serviceAccount_binded_uid > 0
                                            && $old_serviceAccount_binded_uid != $r['Oauthbinds']['user_id'])
                                    ) {
                                        $need_transfer = true;
                                    }

                                    $oauth['Oauthbinds']['user_id'] = $r['Oauthbinds']['user_id'];
                                }
                            }
                            $url = substr($str, $idx + strlen(self::WX_BIND_REDI_PREFIX));
                            //TODO: handle risk for external links!!!
                            if (
                                strpos($url, "http://" . WX_HOST . "/") === 0 ||
                                strpos($url, 'http://www.pyshuo.com/') === 0 ||
                                strpos($url, 'http://www.pengyoushuo.com.cn/') === 0 ||
                                strpos($url, 'http://www.tongshijia.com/') === 0
                            ) {
                                $refer_by_state = $url;
                            }
                        }
                    }

                    $ref = '';
                    if (!empty($refer_by_state)) {
                        $ref = $refer_by_state;
                    } else if (!empty($param_referer)) {
                        $ref = $param_referer;
                    }
                    $new_serviceAccount_binded_uid = $oauth['Oauthbinds']['user_id'];

                    //Do check Require user's authorization to get profile
                    if ($should_require_user_info && $res['scope'] == WX_OAUTH_BASE) {
                        $redi = false;
                        if ($not_bind_yet) {
                            $redi = true;
                        } else {
                            $name = $this->User->findNicknamesOfUid($new_serviceAccount_binded_uid);
                            if ($name == null || $name == '' || notWeixinAuthUserInfo($new_serviceAccount_binded_uid, $name)) {
                                $redi = true;
                            }
                        }
                        if ($redi) {
                            $this->redirect(redirect_to_wx_oauth($ref, WX_OAUTH_USERINFO));
                        }
                    }


                    //Update User profile with WX profile
                    $wxUserInfo = $res['scope'] == WX_OAUTH_USERINFO ? $this->getWxUserInfo($openid, $access_token) : array();
                    if (!empty($wxUserInfo['unionid'])) {
                        $oauth['Oauthbinds']['unionId'] = $wxUserInfo['unionid'];
                    }

                    $new_created = false;
                    if ($new_serviceAccount_binded_uid > 0) {
                        $this->updateUserProfileByWeixin($new_serviceAccount_binded_uid, $wxUserInfo);
                    } else {
                        $this->User->create();
                        if (!empty($wxUserInfo)) {
                            $oauth['Oauthbinds']['user_id'] = $this->createNewUserByWeixin($wxUserInfo);
                        } else {
                            $uu = array(
                                'username' => $oauth['Oauthbinds']['oauth_openid'],
                                'nickname' => '微信用户' . mb_substr($oauth['Oauthbinds']['oauth_openid'], 0, PROFILE_NICK_LEN - 4, 'UTF-8'),
                                'password' => '',
                                'uc_id' => 0
                            );
                            if (!$this->User->save($uu)) {
                                $this->log('Failed to save new user:' . $uu);
                            }
                            $oauth['Oauthbinds']['user_id'] = $this->User->getLastInsertID();
                        }
                        $new_serviceAccount_binded_uid = $oauth['Oauthbinds']['user_id'];
                        $new_created = true;

                        if (!$new_serviceAccount_binded_uid) {
                            $this->log("login failed for cannot got create new user with the current WX info: res=" . json_encode($res) . ", wxUserInfo=" . json_encode($wxUserInfo));
                            $this->wxFailAndGotoLogin($ref);
                            return;
                        }
                    }

                    $this->Oauthbinds->save($oauth['Oauthbinds']);
                    if ($need_transfer && isset($old_serviceAccount_binded_uid) && $old_serviceAccount_binded_uid != $new_serviceAccount_binded_uid) {
                        $this->transferUserInfo($old_serviceAccount_binded_uid, $new_serviceAccount_binded_uid);
                    }
                    if ($new_serviceAccount_binded_uid) {
                        $this->after_create_user($new_serviceAccount_binded_uid);
                    }

                    //TODO: fix risk
                    $redirectUrl = '/users/login?source=' . $oauth['Oauthbinds']['source'] . '&openid=' . $oauth['Oauthbinds']['oauth_openid'];
                    $this->log('wx oauth after redirect login url ' . $redirectUrl, LOG_INFO);
                    if (!empty($refer_by_state)) {
                        $this->redirect($redirectUrl . '&referer=' . urlencode($refer_by_state));
                    } else if (!empty($param_referer)) {
                        $this->redirect($redirectUrl . '&referer=' . urlencode($param_referer));
                    } else {
                        $this->redirect($redirectUrl);
                    }
                }
            } else {
                $this->log("error to get_access_token: code=" . $_REQUEST['code'] . ", return:" . var_export($rtn, true));
                //用户授权了，但是读取过程中失败
                $this->wxFailAndGotoLogin();
            }
        } else {
            //show error msgs
            //用户没有授权
            $this->log("cannot get auth code:" . $_SERVER['QUERY_STRING']);
            $this->wxFailAndGotoLogin();
        }
    }

    private function after_create_user($uid)
    {
        $weixinC = $this->Components->load('Weixin');
        //add_coupon_for_new($uid, $weixinC);
    }

    /**
     * @param $old_serviceAccount_bind_uid
     * @param $new_serviceAccount_bind_uid
     */
    private function transferUserInfo($old_serviceAccount_bind_uid, $new_serviceAccount_bind_uid)
    {
        if ($old_serviceAccount_bind_uid == 0 || $new_serviceAccount_bind_uid == 0) {
            return;
        }

        $this->log("Merge WX Account from  $old_serviceAccount_bind_uid to " . $new_serviceAccount_bind_uid);
        //copy orders && address info
        $this->loadModel('Order');
        $this->loadModel('OrderConsignee');
        $this->loadModel('Cart');
        $orderUpdated = $this->Order->updateAll(array('creator' => $new_serviceAccount_bind_uid), array('creator' => $old_serviceAccount_bind_uid));
        $consigneeUpdated = $this->OrderConsignee->updateAll(array('creator' => $new_serviceAccount_bind_uid), array('creator' => $old_serviceAccount_bind_uid));
        $cartsUpdated = $this->Cart->updateAll(array('creator' => $new_serviceAccount_bind_uid), array('creator' => $old_serviceAccount_bind_uid));

        $this->log("Merge WX Account from  $old_serviceAccount_bind_uid to " . $new_serviceAccount_bind_uid . ": orderUpdated=" . $orderUpdated . ", consigneeUpdated=" . $consigneeUpdated . ", cartsUpdated=" . $cartsUpdated);

        $this->loadModel('Brand');
        $this->Brand->updateAll(array('creator' => $new_serviceAccount_bind_uid), array('creator' => $old_serviceAccount_bind_uid));

        $this->loadModel('TrackLog');
        $this->TrackLog->updateAll(array('from' => $new_serviceAccount_bind_uid), array('from' => $old_serviceAccount_bind_uid));
        $this->TrackLog->updateAll(array('to' => $new_serviceAccount_bind_uid), array('to' => $old_serviceAccount_bind_uid));

        $this->loadModel('AwardResult');
        $this->AwardResult->updateAll(array('uid' => $new_serviceAccount_bind_uid), array('uid' => $old_serviceAccount_bind_uid));

        $this->loadModel('SharedOffer');
        $this->SharedOffer->updateAll(array('uid' => $new_serviceAccount_bind_uid), array('uid' => $old_serviceAccount_bind_uid));

        $this->loadModel('SharedSlice');
        $this->SharedSlice->updateAll(array('accept_user' => $new_serviceAccount_bind_uid), array('accept_user' => $old_serviceAccount_bind_uid));

        $this->loadModel('ExchangeLog');
        $this->ExchangeLog->updateAll(array('user_id' => $new_serviceAccount_bind_uid), array('user_id' => $old_serviceAccount_bind_uid));

        $this->loadModel('CouponItem');
        $this->CouponItem->updateAll(array('bind_user' => $new_serviceAccount_bind_uid), array('bind_user' => $old_serviceAccount_bind_uid));

        $this->loadModel('Comment');
        $this->Comment->updateAll(array('user_id' => $new_serviceAccount_bind_uid), array('user_id' => $old_serviceAccount_bind_uid));

        $this->loadModel('OrderComment');
        $this->OrderComment->updateAll(array('user_id' => $new_serviceAccount_bind_uid), array('user_id' => $old_serviceAccount_bind_uid));

        $this->loadModel('Weshare');
        $this->Weshare->updateAll(array('creator' => $new_serviceAccount_bind_uid), array('creator' => $old_serviceAccount_bind_uid));

        $this->loadModel('UserRelation');
        $this->UserRelation->updateAll(array('user_id' => $new_serviceAccount_bind_uid), array('user_id' => $old_serviceAccount_bind_uid));


        //团购的
        //积分的
        //在后台给1000个
        //优惠券兑换的功能
        //返利
        //分享
        //粉丝数据
    }

    /**
     * @param $new_serviceAccount_binded_uid
     * @param $userInfo
     */
    public function updateUserProfileByWeixin($new_serviceAccount_binded_uid, $userInfo)
    {
        if (empty($userInfo)) {
            return;
        }
        $user = $this->User->findById($new_serviceAccount_binded_uid);
        if (!empty($user)) {
            $changed = false;
            $user = $user['User'];
            if (!$user['nickname'] || notWeixinAuthUserInfo($new_serviceAccount_binded_uid, $user['nickname'])) {
                $user['nickname'] = filter_weixin_username(convertWxName($userInfo['nickname']));
                $changed = true;
            }
            if ($user['sex'] !== 0 && $user['sex'] != 1) {
                $user['sex'] = $userInfo['sex'];
                $changed = true;
            }
            if (!$user['image']) {
                if (!empty($userInfo['headimgurl'])) {
                    $download_url = download_photo_from_wx($userInfo['headimgurl']);
                    if (!empty($download_url)) {
                        $user['image'] = $download_url;
                        $user['avatar'] = $download_url;
                    } else {
                        $user['image'] = $userInfo['headimgurl'];
                    }
                    $changed = true;
                }
            }
            if (!$user['city']) {
                $user['city'] = $userInfo['city'];
                $changed = true;
            }
            if (!$user['province']) {
                $user['province'] = $userInfo['province'];
                $changed = true;
            }
            if (!$user['country']) {
                $user['country'] = $userInfo['country'];
                $changed = true;
            }
            if (!$user['language']) {
                $user['language'] = $userInfo['language'];
                $changed = true;
            }
            if ($changed) {
                $rtn = $this->User->save($user);
                if (!$rtn) {
                    $this->log('error to save user:' . $user['id']);
                }
            }
        }
    }

    /**
     * @param $userInfo
     * @return int new created user id
     */
    public function createNewUserByWeixin($userInfo)
    {
        $union_id = $userInfo['unionid'];
        if (!empty($union_id)) {
            $uid = $this->get_user_id_by_unionid($union_id);
            if (!$uid != 0) {
                return $uid;
            }
        }
        $uid = createNewUserByWeixin($userInfo, $this->User);
        if (empty($uid)) {
            $this->log("error to save createNewUserByWeixin: with " . json_encode($userInfo), LOG_ERR);
            return 0;
        } else {
            return $uid;
        }
        //return $this->User->getLastInsertID();
    }

    private function get_user_id_by_unionid($union_id)
    {
        $oauthbindM = ClassRegistry::init('Oauthbind');
        $uid = $oauthbindM->findUidByUnionId($union_id);
        return $uid;
    }

    /**
     * @param $openid
     * @param $access_token
     * @return mixed
     */
    public function getWxUserInfo($openid, $access_token)
    {
        $userInfo = $this->WxOauth->getUserInfo($openid, $access_token);
        if (!empty($userInfo)) {
            $userInfo = $userInfo['WxOauth'];
            return $userInfo['openid'] ? $userInfo : false;
        }
        return false;
    }

    protected function wxFailAndGotoLogin($ref = '')
    {
        $loginError = __('获取微信授权信息失败，请您重试！');
        $redirect = array('action' => 'login');
        $params = array();
        if (!empty($ref)) {
            $params['referer'] = $ref;
        }
        if ($loginError) {
            $params['login_error'] = $loginError;
        }
        $redirect['?'] = $params;
        $this->redirect($redirect);
    }


    public function mobile_bind()
    {
        $this->autoRender = false;
        $readCode = $_POST['code'];
        $mobile_num = $_POST['mobile'];
        $msgCode = $this->Session->read('messageCode');
        $current_post_num = $this->Session->read('current_register_phone');
        $bind_from = $_POST['from'];
        $codeLog = json_decode($msgCode, true);
        $user_info = array();
        $res = array();
        if ($codeLog && is_array($codeLog) && $codeLog['code'] == $readCode && (time() - $codeLog['time'] < 30 * 60)) {
            $user_info['User']['mobilephone'] = $mobile_num;
            $user_info['User']['id'] = $this->currentUser['id'];
            $user_info['User']['uc_id'] = 5;
            if (empty($this->currentUser['id'])) {
                $res = array('success' => false, 'msg' => 'please login');
            } else if ($mobile_num != $current_post_num) {
                $res = array('success' => false, 'msg' => '请重新验证您的手机号码');
            } else if ($this->User->hasAny(array('User.mobilephone' => $mobile_num))) {
                $tempUser = $this->getUserNamebyMobile($mobile_num);
                $res = array('success' => false, 'msg' => '你的手机号已注册过，无法绑定，请用手机号登录', 'code' => 2, 'username' => $tempUser['User']['nickname']);
            } else if ($this->User->hasAny(array('User.username' => $mobile_num))) {
                if ($this->currentUser['username'] == $mobile_num) {
                    if ($this->User->save($user_info)) {
                        $res = array('success' => true, 'msg' => '你的账号和手机号绑定成功');
                    };
                } else {
                    $tempUser = $this->getUserNamebyMobile($mobile_num);
                    $res = array('success' => false, 'msg' => '你的手机号已注册过，无法绑定，请用手机号登录', 'code' => 2, 'username' => $tempUser['User']['nickname']);
                }
            } else {
                if ($this->User->save($user_info)) {
                    $this->Session->write('Auth.User.mobilephone', $mobile_num);
                    $result_msg = '你的账号和手机号绑定成功';
                    if ($bind_from == 'refer') {
                        $result_msg = $result_msg . ',并获得1000积分';
                    }
                    $res = array('success' => true, 'msg' => $result_msg);
                } else {
                    $res = array('success' => false, 'msg' => '绑定失败，数据库忙');
                }
            }
        } else {
            $res = array('success' => false, 'msg' => '短信验证码错误');
        }
        echo json_encode($res);
    }

    /**
     * @param $text
     * @return mixed|string
     */
    protected function convertWxName($text)
    {
        $nickname = remove_emoji($text);
        return ($nickname == '' ? '用户_' . mt_rand(10, 1000) : $nickname);
    }

    function complete_user_info()
    {
        $this->pageTitle = "完善用户信息";
        $from = $_REQUEST['from'];
        $this->set('from', $from);
        $this->set('hideNav', true);
        $uid = $this->currentUser['id'];
        $current_user = $this->User->find('first', array(
            'conditions' => array(
                'id' => $uid
            ),
            'fields' => array('payment', 'description'),
            'recursive' => 1, //int
        ));
        $this->set('user', $current_user);
    }

    function complete()
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $payment = $_REQUEST['payment'];
        $description = $_REQUEST['description'];
        $user_info = array('payment' => $payment, 'description' => $description, 'id' => $uid);
        $this->User->saveAll($user_info);
        echo json_encode(array('success' => true));
        return;
    }

    function to_bind_mobile()
    {
        $userId = $this->Session->read('Auth.User.id');
        $userNickName = $this->Session->read('Auth.User.nickname');
        $orderId = $_REQUEST['order_id'];
        $from = $_REQUEST['from'];
        $this->set('userId', $userId);
        $this->set('nickname', $userNickName);
        $this->set('orderId', $orderId);
        $this->set('from', $from);
        $short_intro = $_REQUEST['reason'];
        $ref_url = $_REQUEST['ref'];

        $this->set('short_intro', $short_intro);
        $this->set('ref_url', $ref_url);
        $this->set('hideNav', true);

        $this->pageTitle = "绑定手机号";
    }

    function merge_data()
    {
        $this->autoRender = false;
        $userId = $this->Session->read('Auth.User.id');
        //no login user must to login
        if (!$userId) {
            $this->Session->setFlash(__('You are not authorized to access that location.', true));
            $this->redirect(array('action' => 'login'));
        }
        $this->loadModel('Oauthbind');
        $wxBind = $this->Oauthbind->findWxServiceBindByUid($userId);
        $oauth_openid = $wxBind['oauth_openid'];
        $mobile = $_REQUEST['mobile'];
        $mobileCode = $_REQUEST['mobileCode'];
        $msgCode = $this->Session->read('messageCode');
        $codeLog = json_decode($msgCode, true);
        if (is_array($codeLog) && $codeLog['code'] == $mobileCode) {
            $newUser = $this->getUserByMobile($mobile);
            $newUserId = $newUser['User']['id'];
            if (!empty($wxBind)) {
                try {
                    $this->Oauthbind->update_wx_bind_uid($oauth_openid, $userId, $newUserId);
                } catch (Exception $e) {
                    $this->log("merge user data has exception user id " . $userId . " new user id " . $newUserId) . ($e->getMessage());
                    $result = array('success' => 'fale', 'msg' => '信息合并失败');
                }
                //do wx bind
//                $wxNewBind = $this->Oauthbind->findWxServiceBindByUid($newUserId);
//                if(empty($wxNewBind)){
//                    $this->Oauthbind->update_wx_bind_uid($oauth_openid, $userId,$newUserId);
//                }else{
//                    //$new_oauth_openid = $wxNewBind['oauth_openid'];
//
//                }
            }
            $this->transferUserInfo($userId, $newUserId);
            $this->logoutCurrUser();
            $this->data['checkMobileCode'] = true;
            $this->data['User'] = $newUser['User'];
            $this->Auth->login();
            $result = array('success' => 'true', 'msg' => '信息合并成功');
        } else {
            $result = array('success' => 'false', 'msg' => '手机验证码不正确');
        }
        echo json_encode($result);
    }

    function getUserByMobile($mobile)
    {
        $user = $this->User->find('first',
            array('conditions' => array('mobilephone' => $mobile, 'published' => 1))
        );
        return $user;
    }

    function getUserNamebyMobile($mobile)
    {
        $username = $this->User->find('first', array(
            'conditions' => array('mobilephone' => $mobile, 'published' => 1),
            'fields' => array('username', 'nickname')
        ));
        return $username;
    }

    function get_spring_coupon($pid)
    {
        $this->autoRender = false;
        $got = false;
        try {
            $cM = ClassRegistry::init('CouponItem');
            $reason = '';
            if (!empty($this->currentUser['id'])) {
                $got = $cM->add_spring_festival_coupon($this->currentUser['id'], $pid);
            } else {
                $reason = 'not_login';
            }
        } catch (Exception $e) {
            $this->log("exception:" . $e);
            $reason = 'unknown';
        }
        echo json_encode(array('success' => $got, 'reason' => $reason));
    }

    public function get_proxies()
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];

        $proxies = [];
        if (!empty($uid)) {
            $proxies = $this->User->get_my_proxys($uid);
        }

        echo json_encode($proxies);
        exit();
    }

    public function get_id_and_proxies()
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];

        $proxies = [];
        if (!empty($uid)) {
            $proxies = $this->User->get_my_proxys($uid);
        }

        echo json_encode(array('uid'=> $uid, 'proxies'=>$proxies));
        exit();
    }
}

?>