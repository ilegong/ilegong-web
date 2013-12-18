<?php
/**
 * AclFilter Component
 *
 * PHP version 5
 *
 * @category Component
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class AclFilterComponent extends Component {
	
	public $components = array ('Session', 'Auth' );
	
	private $controller_name;
	/**
	 * @param object $controller controller
	 * @param array  $settings   settings
	 */
	function initialize(&$controller, $settings = array()) {
		$this->controller = $controller;
		$this->controller_name = Inflector::camelize($controller->name);
	}
	
	private function _getStaffRoles() {
		$userinfo = $this->Auth->user ();
		$roleIds = array();
		if(!empty($userinfo['Role'])){
			foreach($userinfo['Role'] as $role){
				$roleIds[] = $role['id'];
			}
		}
		else{
			$roleIds = $this->Auth->user ( 'role_id' );
			if (! is_array ( $roleIds )) {
				$roleIds = explode ( ',', $roleIds );
				$this->Session->write ( 'Auth.Staff.role_id', $roleIds );
			}
		}
		return $roleIds;
	}
	
	function check($controller_name,$action){
		$allowedActions = $this->getControllerAclActions($controller_name);
		if(in_array($action,$allowedActions) || in_array('*',$allowedActions)){
			return true;
		}
		else{
			return false;
		}
	}
	/** 
	 * 取得用户的aro_id;包括所在用户组(Role)的aro_id 和用户表(Staff)中记录所在的aro_id
	 */
	function getUserAroIds(){
		$user_id = $this->Auth->user ( 'id' );
		if(empty($user_id)){
			return array();
		}
		$aro_ids = Cache::read ( $user_id ."_admin_allow_aro_ids" );
		if($aro_ids===false){
			$aro_ids = array();
			try {
				$this->controller->loadModel ( 'Staff' );
				$nodes = $this->controller->Staff->node ( array ('Staff' => $user_id ) );
				foreach ( $nodes as $aro ) {
					$aro_ids [] = $aro ['Aro'] ['id'];
				}
				$this->controller->loadModel ( 'Role' );
			
				$this->controller->Role->id = $this->_getStaffRoles ();;
				$nodes = $this->controller->Role->node ();
				foreach ( $nodes as $aro ) {
					$aro_ids [] = $aro ['Aro'] ['id'];
				}
			}
			catch(Exception $e)
			{
				echo print_r($e);
				echo "catch exception";
			}
			Cache::write( $user_id  . "_admin_allow_aro_ids",$aro_ids);
		}
		return $aro_ids;
	}
	
	function getUserAcoIds(){
		$user_id = $this->Auth->user ( 'id' );
		$aco_ids = Cache::read ( $user_id ."_admin_allow_aco_ids" );
		if($aco_ids===false){
			$aro_ids = $this->getUserAroIds();
			$aco_ids = array();
			$allowed_aco_ids = ClassRegistry::init ( 'Acl.AclArosAco' )->find ( 'all',
					array (
							'recursive' => '-1',
							'fields' => 'aco_id',
							'conditions' => array(
									'AclArosAco.aro_id' => $aro_ids, 'AclArosAco._read' => 1
							) ) );
			foreach($allowed_aco_ids as $aco){
				$aco_ids[] = $aco['AclArosAco']['aco_id'];
			}
			// print_r($aco_ids);
			Cache::write($user_id."_admin_allow_aco_ids",$aco_ids);
		}
		return $aco_ids;
	}
	
	function getControllerAclActions($controller_name){
		$aro_ids = $this->getUserAroIds();
		if(in_array(1,$aro_ids)){
			return array('*');
		}
		$controller_name = Inflector::camelize($controller_name);
		
		$user_id = $this->Auth->user ( 'id' );
		$acoPath = 'controllers/'.$controller_name;
		// 当前控制器在aco中的节点，如：controllers/systems
		$allow = Cache::read($user_id.$acoPath.'_allow_acl_actions');
		if($allow===false){
			$thisControllerNode = $this->controller->Acl->Aco->node ( $acoPath );
			if ($thisControllerNode && $aro_ids) {
				$thisControllerNode = $thisControllerNode ['0'];
				// 获取aco表中，当前控制器中所有的action操作
				$thisControllerActions = $this->controller->Acl->Aco->find ( 'list', array ('conditions' => array ('Aco.parent_id' => $thisControllerNode ['Aco'] ['id'] ), 'fields' => array ('Aco.id', 'Aco.alias' ) ) );
			
				$thisControllerActionsIds = array_keys ( $thisControllerActions );
			
				$conditions = array ('AclArosAco.aro_id' => $aro_ids, 'AclArosAco.aco_id' => $thisControllerActionsIds, 'AclArosAco._create' => 1, 'AclArosAco._read' => 1, 'AclArosAco._update' => 1, 'AclArosAco._delete' => 1 );
			
				$allowedActions = ClassRegistry::init ( 'Acl.AclArosAco' )->find ( 'all', array ('recursive' => '-1', 'conditions' => $conditions ) );
				// 	                print_r($thisControllerActions);
				//print_r($allowedActions);exit;
				$allowedActionsIds = Set::extract ( '/AclArosAco/aco_id', $allowedActions );
			}
			
			$allow = array ();
			if (isset ( $allowedActionsIds ) && is_array ( $allowedActionsIds ) && count ( $allowedActionsIds ) > 0) {
				foreach ( $allowedActionsIds as $i => $aId ) {
					$allow [] = $thisControllerActions [$aId];
				}
			}
			Cache::write($user_id.$acoPath.'_allow_acl_actions',$allow);
		}
// 		print_r($allow);
		return $allow;
	}
	
	
	/**
	 * acl and auth
	 *
	 * @return void
	 */
	function authAdmin() {
		//Configure AuthComponent
		//$this->Auth->authorize = 'actions';
		$this->Auth->loginAction = array ('plugin' => 0, 'controller' => 'staffs', 'action' => 'login' );
		$this->Auth->logoutRedirect = array ('plugin' => 0, 'controller' => 'staffs', 'action' => 'login' );
		$this->Auth->loginRedirect = array ('plugin' => 0, 'controller' => 'staffs', 'action' => 'index' );
		$this->Auth->userScope = array ('Staff.status' => 1 );
		
		if ($this->Auth->user()) {
			$roleId = $this->_getStaffRoles ();
			$user_id = $this->Auth->user ( 'id' );
		}
		else{
			return array('admin_login');
		}
		
        if ($user_id && in_array(1,$roleId)) { // Role: Admin
            $this->Auth->allowedActions = array('*');
            return true;
        }
        else{
			// 若存在缓存，则直接调用缓存
			$allow_actions = Cache::read ( $user_id . $this->controller_name . "_admin_allow_actions" );
           if($allow_actions!==false){
           	 $this->controller->Auth->allowedActions = $allow_actions;
           }
           else{
           		$allow = $this->getControllerAclActions($this->controller_name);
           		$this->getUserAcoIds(); // 生成aco_id缓存，在其它地方会调用到。如后台用户是否具备某操作链接地址的权限
				$allow [] = 'admin_login';
				$allow [] = 'admin_logout';
				$allow [] = 'admin_select';
				$allow [] = 'admin_index';
				$allow [] = 'admin_menu';
				$allow [] = 'admin_getcss';
				
				$this->Auth->allowedActions = $allow;
				Cache::write ( $user_id . $this->controller_name . "_admin_allow_actions", $allow );
			}
		}
	}

}
?>