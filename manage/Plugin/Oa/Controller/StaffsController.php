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
            //'Securimage',
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
        $this->layout = "admin_login";
        
        if ($id = $this->Auth->user('id')) {
            if (!empty($this->data['User']['remember_me'])) {
                $this->Cookie->write('Auth.Staff', $this->Session->read('Auth.Staff'), true, 31536000);
            }
            $this->Staff->id = $id;
            $this->Staff->save(array(
                'id' => $id,
                'username' => $this->Auth->user('username'),
                'email' => $this->Auth->user('email'),
                'last_login' => date('Y-m-d H:i:s')
            ));
            $redirect = $this->Auth->redirect();
            if (strpos($redirect, 'login') !== false || strpos($redirect, 'admin/staffs') !== false || $redirect == '/') {
                $redirect = '/';
            }
            $this->redirect($redirect);
        }

        if (!empty($this->data['Staff'])) {
            
            if ($this->Auth->login()) {
                $id = $this->Staff->id = $this->Auth->user('id');
                $this->Staff->save(array(
                    'id' => $id,
                    'username' => $this->Auth->user('username'),
                    'email' => $this->Auth->user('email'),
                    'last_login' => date('Y-m-d H:i:s'),
                    'tmp_pass' => null,
                ));
                $this->Cookie->write('Auth.Staff', $this->Session->read('Auth.Staff'), true, 31536000);
                $redirect = $this->Auth->redirect();
                $this->redirect($redirect);
            } else {
                unset($this->data['Staff']['password']);
                $this->Session->setFlash(__('username or password not right'));
            }
        }
        else{
            $this->Auth->redirect($_SERVER['HTTP_REFERER']);
        }
    }

    function admin_logout() {
        $this->Cookie->destroy('Staff');
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