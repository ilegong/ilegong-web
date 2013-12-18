<?php
/**
 * AclPermissions Controller
 *
 * PHP version 5
 *
 * @category Controller
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class AclPermissionsController extends AclAppController {
    var $name = 'AclPermissions';
    var $uses = array('Acl.AclAco', 'Acl.AclAro', 'Acl.AclArosAco', 'Role');
    
    function admin_init(){
    	
//		当role中的记录在aro表中不存在对应的记录时，修复插入记录。    	
//     	$this->Acl->Aro->create(array('parent_id' => null,'foreign_key'=>4, 'model' => 'Role'));
// 	    $aros = $this->Acl->Aro->save();exit;
    	
    	$acos = $this->Acl->Aco->find('first',array('conditions'=>array('parent_id' => null, 'alias' => 'controllers')));
    	if($acos['Aco']){
    		$parent_id = $acos['Aco']['id'];
    	}
    	else{
	    	$this->Acl->Aco->create(array('parent_id' => null,'foreign_key'=>null, 'alias' => 'controllers'));
	    	$acos = $this->Acl->Aco->save();
	    	$parent_id = $acos['Aco']['id'];
    	}
    	
    	$acos = $this->Acl->Aco->find('first',array('conditions'=>array('parent_id' => $parent_id, 'alias' => 'articles')));
    	if(empty($acos['Aco'])){
    		$this->Acl->Aco->create(array('parent_id' => $parent_id,'foreign_key'=>null, 'alias' => 'articles'));
    		$this->Acl->Aco->save();
    	}
    	
    	$acos = $this->Acl->Aco->find('first',array('conditions'=>array('parent_id' => $parent_id, 'alias' => 'categories')));
    	if(empty($acos['Aco'])){
    		$this->Acl->Aco->create(array('parent_id' => $parent_id, 'foreign_key'=>null,'alias' => 'categories'));
    		$this->Acl->Aco->save();
    	}
    	
    	$acos = $this->Acl->Aco->find('first',array('conditions'=>array('parent_id' => $parent_id, 'alias' => 'products')));
    	if(empty($acos['Aco'])){
    		$this->Acl->Aco->create(array('parent_id' => $parent_id,'foreign_key'=>null, 'alias' => 'products'));
    		$this->Acl->Aco->save();
    	}
    	
//     	$this->Acl->Aco->create(array('parent_id' => null, 'alias' => 'controllers'));
    }

    /**
     * UPDATE `cake_acos` SET `name` = `alias` WHERE `name` IS NULL 
     * @see AppController::admin_index()
     */
    function admin_index() {
		$this->pageTitle = __('Permissions', true);

        $acoConditions = array(
//             'parent_id !=' => null,
            'model' => null,
            'alias is not null and alias!=\'controllers\'',
        );
        $acos  = $this->Acl->Aco->generateTreeList($acoConditions, '{n}.Aco.id', '{n}.Aco.alias');
        $roles = $this->Role->find('list');
        $this->set(array('acos'=>$acos, 'roles'=>$roles));

        $rolesAros = $this->AclAro->find('all', array(
            'conditions' => array(
                'AclAro.model' => 'Role',
                'AclAro.foreign_key' => array_keys($roles),
            ),
        ));
        $rolesAros = Set::combine($rolesAros, '{n}.AclAro.foreign_key', '{n}.AclAro.id');
        $permissions = array(); // acoId => roleId => bool
        foreach ($acos AS $acoId => $acoAlias) {
                $permission = array();
                foreach ($roles AS $roleId => $roleTitle) {
                    $hasAny = array(
                        'aco_id'  => $acoId,
                        'aro_id'  => $rolesAros[$roleId],
                        '_create' => 1,
                        '_read'   => 1,
                        '_update' => 1,
                        '_delete' => 1,
                    );
                    if ($this->AclArosAco->hasAny($hasAny)) {
                        $permission[$roleId] = 1;
                    } else {
                        $permission[$roleId] = 0;
                    }
                    $permissions[$acoId] = $permission;
                }
        }
        $this->set(array('rolesAros'=>$rolesAros, 'permissions'=>$permissions));
    }

    function admin_toggle($acoId, $aroId) {
        // see if acoId and aroId combination exists
        $conditions = array(
            'AclArosAco.aco_id' => $acoId,
            'AclArosAco.aro_id' => $aroId,
        );
        if ($this->AclArosAco->hasAny($conditions)) {
            $data = $this->AclArosAco->find('first', array('conditions' => $conditions));
            if ($data['AclArosAco']['_create'] == 1 &&
                $data['AclArosAco']['_read'] == 1 &&
                $data['AclArosAco']['_update'] == 1 &&
                $data['AclArosAco']['_delete'] == 1) {
                // from 1 to 0
                $data['AclArosAco']['_create'] = 0;
                $data['AclArosAco']['_read'] = 0;
                $data['AclArosAco']['_update'] = 0;
                $data['AclArosAco']['_delete'] = 0;
                $permitted = 0;
            } else {
                // from 0 to 1
                $data['AclArosAco']['_create'] = 1;
                $data['AclArosAco']['_read'] = 1;
                $data['AclArosAco']['_update'] = 1;
                $data['AclArosAco']['_delete'] = 1;
                $permitted = 1;
            }
        } else {
            // create - CRUD with 1
            $data['AclArosAco']['aco_id'] = $acoId;
            $data['AclArosAco']['aro_id'] = $aroId;
            $data['AclArosAco']['_create'] = 1;
            $data['AclArosAco']['_read'] = 1;
            $data['AclArosAco']['_update'] = 1;
            $data['AclArosAco']['_delete'] = 1;
            $permitted = 1;
        }

        // save
        $success = 0;
        if ($this->AclArosAco->save($data)) {
            $success = 1;
        }

        $this->set(array(
        		'acoId'=>$acoId, 'aroId'=>$aroId,
        		 'data'=>$data, 'success'=>$success,
        		 'permitted'=>$permitted));
    }
    
}
?>