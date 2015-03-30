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

    var $name = 'Users';
   
    var  $components = array(
		'Email', 
    	'Kcaptcha',
    	//'Securimage',
	);
    
    function admin_add() {
        if (!empty($this->data)) {
            $this->data['User']['activation_key'] = md5(uniqid());
            $this->data['User']['password'] = Security::hash($this->data['User']['password'], null, true);
        }
        parent::admin_add();
        
    	if (empty($this->data)) {
           $this->data['User']['role_id'] = 2; // default Role: Registered
        }
        
        $roles = $this->User->Role->find('list');
        $this->set('roles',$roles);
    }

    function admin_edit($id = null) {
    	if (!$id){
       		$id = $this->_getParamVars('id');
		}		
        if (!$id && empty($this->data)) {
            $this->redirect(array('action' => 'index'));
        }
        if (!empty($this->data)) {
        	$this->autoRender = false;
        	if(empty($this->data['User']['password'])){
        		unset($this->data['User']['password']);
        	}
        	else{
        		$this->data['User']['password'] = Security::hash($this->data['User']['password'], null, true);
        	}
            if ($this->User->save($this->data)) {
                $successinfo = array('success'=>__('Edit success',true));
            }
	        else{
	        	$successinfo = array('error'=>__('Edit error',true));
	        }
	        echo json_encode($successinfo);
        	exit;
        }
        if (empty($this->data)) {
            $this->data = $this->User->read(null, $id);
        }
        $roles = $this->User->Role->find('list',array('conditions'=>array('id >'=>100)));
        $this->set(array('roles'=>$roles,'id'=>$id));
        $this->__viewFileName = 'admin_add';
    }

    function admin_delete($id = null) {
        if (!$id) {
            $this->redirect(array('action' => 'index'));
        }
        if ($this->User->delete($id)) {
            $this->redirect(array('action' => 'index'));
        }
    }

    function admin_to_add_score(){
    }

    function admin_do_add_score(){
        $this->autoRender=false;
        $uid = $_REQUEST['user_id'];
        $score = $_REQUEST['score'];
        if($this->User->updateAll(array('User.score'=>'User.score+'.$score),array('User.id' => $uid))){
            echo json_encode(array('success'=>true,'msg'=>'更新成功'));
        }else{
            echo json_encode(array('success'=>false,'msg'=>'更新失败'));
        }
    }
}
?>