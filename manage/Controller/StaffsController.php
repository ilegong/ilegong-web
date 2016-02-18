<?php

/**
 * Staffs Controller
 *
 * PHP version 5
 *
 * @category Controller
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class StaffsController extends AppController {

    /**
     * Controller name
     *
     * @var string
     * @access public
     */
    var $name = 'Staffs';
    var $components = array(
        'Email',
        'Kcaptcha',
    	'Cookie' => array('name' => 'SAECMS', 'time' => '+2 weeks'),
        'Acl',
        'Auth'=>array(
            'authenticate' => array(
                'Form'=>array(
                    'userModel' => 'Staff','recursive' => 1,
                    'fields'=>array(
                        'username'=>'name',
                        'password'=>'password'
                    )
                ),
            ),
//         	'authorize' => 'Controller',
        ),
        'AclFilter',
    );

    function beforeFilter() {
        parent::beforeFilter();
    }

    function admin_captcha() {
        error_reporting(0);
        $this->Kcaptcha->render();
    }

    function admin_add() {
        if (!empty($this->data[$this->modelClass]['password'])) {
            $this->data[$this->modelClass]['password'] = AuthComponent::password($this->data[$this->modelClass]['password']);
        }
        parent::admin_add();
    }

    function admin_edit($id = null) {
        if (!$id) {
            $id = $this->_getParamVars('id');
        }

        if (empty($_POST['data'][$this->modelClass]['password'])) {
            // 当post提交空密码时，不修改密码。修改用户资料，输入了新密码时，修改密码；无输入密码时不修改密码。
            unset($this->data[$this->modelClass]['password']);
        } else {
            $this->data[$this->modelClass]['password'] = AuthComponent::password($this->data[$this->modelClass]['password']);
        }
        parent::admin_edit($id);
        unset($this->data[$this->modelClass]['password']);
    }

    function admin_reset_password($id = null) {
        if (!$id && empty($this->data)) {
            $this->redirect(array('action' => 'index'));
        }
        if (!empty($this->data)) {
            if ($this->User->save($this->data)) {
                $this->redirect(array('action' => 'index'));
            }
        }
        if (empty($this->data)) {
            $this->data = $this->User->read(null, $id);
        }
    }

    function admin_login() {
        $this->pageTitle = __('Admin Login', true);
        if(preg_match('/MSIE 6.0/i',$_SERVER['HTTP_USER_AGENT'])){
        	$this->layout = 'ie6';
        }
        else{
        	$this->layout = 'admin_login';
        }
        if ($id = $this->Auth->user('id')) { // 已登录的直接跳转。
            $userinfo = $this->Auth->user();
            if(!is_array($userinfo['role_id'])){
            	$this->data['role_id'] = explode(',',$userinfo['role_id']);
            }
            else{
            	$this->data['role_id'] = $userinfo['role_id'];
            }
            $this->Staff->id = $id;
            $this->Staff->updateAll(array(
                'last_login' => "'".date('Y-m-d H:i:s')."'"
            ),array('id' => $id));
            $redirect = $this->Auth->redirect();
            if (strpos($redirect, 'login') !== false || strpos($redirect, 'admin/staffs') !== false) {
                $redirect = '/';
            }
            $this->redirect($redirect);
        }
        else{
        	$userinfo = $this->Cookie->read('Auth.Staff');
        	if (is_array($userinfo) && !empty($userinfo['id'])) {
        		$this->Session->write('Auth.Staff', $userinfo);
        		
        		$this->Staff->updateAll(array(
        				'last_login' => "'".date('Y-m-d H:i:s')."'"
        		),array('id' => $userinfo['id']));
        		$redirect = $this->Auth->redirect();
        		if (strpos($redirect, 'login') !== false || strpos($redirect, 'admin/staffs') !== false) {
        			$redirect = '/';
        		}
        		$this->redirect($redirect);
        	}
        	
        }
                

        if (!empty($this->data['Staff'])) { // 有提交时，验证登录
            
            if ($this->Auth->login()) {
            	$userinfo = $this->Auth->user();
                $id = $this->Staff->id = $userinfo['id'];
                $userinfo['role_id'] = explode(',',$userinfo['role_id']);
                $this->Session->write('Auth.Staff.role_id',$userinfo['role_id']);
                
                $this->Staff->updateAll(array(
                    'last_login' => "'".date('Y-m-d H:i:s')."'",
                ),array('id' => $id));
                if (!empty($this->data['User']['remember_me'])) {
                	$this->Cookie->write('Auth.Staff', $this->Session->read('Auth.Staff'), true, 31536000);
                }
                $redirect = $this->Auth->redirect();
                $this->redirect($redirect);
            } else {
                unset($this->data['Staff']['password']);
                $this->Session->setFlash(__('username or password not right'));
            }
        }
    }

    function admin_logout() {
        $this->Cookie->destroy();
        $this->Session->destroy();
        $this->redirect($this->Auth->logout());
    }

    function admin_editpassword() {
        $userinfo = $this->Auth->user();
        if (!$userinfo['id']) {
            $this->Session->setFlash(__('You are not authorized to access that location.', true));
            $this->redirect(array('action' => 'login'));
        }
        if (!empty($this->data) && isset($this->data['Staff']['password'])) {
            $before_edit = $this->{$this->modelClass}->read(null, $userinfo['Staff']['id']);

            if ($before_edit['Staff']['password'] == Security::hash($this->data['Staff']['password'], null, true)) {

                if (!empty($this->data['Staff']['new_password']) && $this->data['Staff']['new_password'] == $this->data['Staff']['password_confirm']) {
                    $user = array();
                    $user['Staff']['id'] = $userinfo['Staff']['id'];
                    $user['Staff']['password'] = Security::hash($this->data['Staff']['new_password'], null, true);
                    $user['Staff']['activation_key'] = md5(uniqid());

                    if ($this->Staff->save($user['Staff'])) {
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

}

?>