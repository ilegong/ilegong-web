<?php
/**
 * AclActions Controller
 *
 * PHP version 5
 *
 * @category Controller
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class AclActionsController extends AclAppController {
    var $name = 'AclActions';
    var $uses = array('Acl.AclAco');
    var $components = array('Acl.AclGenerate');

	function admin_index() {
		$this->pageTitle = __('Actions', true);

        $conditions = array(
            'parent_id !=' => null,
            'model' => null,
            'foreign_key' => null,
            'alias !=' => null,
        );
		$this->set('acos', $this->Acl->Aco->generateTreeList($conditions, '{n}.Aco.id', '{n}.Aco.alias'));
	}

	function admin_add() {
		$this->pageTitle = __("Add Action", true);

		if (!empty($this->data)) {
            $this->Acl->Aco->create();
            
            // if parent_id is null, assign 'controllers' as parent
            if ($this->data['Aco']['parent_id'] == null) {
                $this->data['Aco']['parent_id'] = 1;
                $acoType = 'Controller';
            } else {
                $acoType = 'Action';
            }

			if ($this->Acl->Aco->save($this->data['Aco'])) {
				$this->Session->setFlash(__('The '. $acoType .' has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__('The '. $acoType .' could not be saved. Please, try again.', true));
			}
		}

        $conditions = array(
            'model' => null,
        );
        $controllersAco = $this->Acl->Aco->find('first', array(
            'conditions' => array(
                'alias' => 'controllers',
                'parent_id' => null,
                'model' => null,
                'foreign_key' => null,
            ),
        ));
        if (isset($controllersAco['Aco']['id'])) {
            $conditions['parent_id'] = $controllersAco['Aco']['id'];
        }
        $acos = $this->Acl->Aco->generateTreeList($conditions, '{n}.Aco.id', '{n}.Aco.alias');
        $this->set('acos',$acos);
	}

	function admin_edit($id = null) {
		$this->pageTitle = __("Edit Action", true);

		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid Action', true));
			$this->redirect(array('action'=>'index'));
		}
		if (!empty($this->data)) {
			if ($this->Acl->Aco->save($this->data['Aco'])) {
				$this->Session->setFlash(__('The Action has been saved', true));
				$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__('The Action could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->Acl->Aco->read(null, $id);
		}

        $conditions = array(
            'model' => null,
        );
        $controllersAco = $this->Acl->Aco->find('first', array(
            'conditions' => array(
                'alias' => 'controllers',
                'parent_id' => null,
                'model' => null,
                'foreign_key' => null,
            ),
        ));
        if (isset($controllersAco['Aco']['id'])) {
            $conditions['parent_id'] = $controllersAco['Aco']['id'];
        }
        $acos = $this->Acl->Aco->generateTreeList($conditions, '{n}.Aco.id', '{n}.Aco.alias');
        $this->set('acos',$acos);
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for Action', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Acl->Aco->delete($id)) {
			$this->Session->setFlash(__('Action deleted', true));
			$this->redirect(array('action'=>'index'));
		}
	}

    function admin_move($id, $direction = 'up', $step = '1') {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for Action', true));
			$this->redirect(array('action'=>'index'));
		}
        if ($direction == 'up') {
            if ($this->Acl->Aco->moveUp($id)) {
                $this->Session->setFlash(__('Action moved up', true));
                $this->redirect(array('action'=>'index'));
            }
        } else {
            if ($this->Acl->Aco->moveDown($id)) {
                $this->Session->setFlash(__('Action moved down', true));
                $this->redirect(array('action'=>'index'));
            }
        }
    }

    function admin_generate() {
    	set_time_limit(0);
        $aco =& $this->Acl->Aco;
        $root = $aco->node('controllers');
        if (!$root) {
            $aco->create(array(
                'parent_id' => null,
                'model' => null,
                'alias' => 'controllers',
            ));
            $root = $aco->save();
            $root['Aco']['id'] = $aco->id;
        } else {
            $root = $root[0];
        }
        
        $controllerPaths = $this->AclGenerate->listControllers();
        $gen_controllers = array(
        		'Categories','Crawls','CrawlTitleLists','CrawlReleaseSites',
        		'I18nfields','Keywords','Languages','Menus',
        		'Articles','',
        		'Products','Regions','Roles','Settings',
        		'Staffs','Stylevars','Tools','Uploadfiles',
        		'Filemanager','','','',
        		'AclActions','AclAros','AclPermissions','',
        		'Flowsteps','','','',
        		);
        foreach ($controllerPaths AS $controllerName => $controllerPath) {
        	
        	if(!in_array($controllerName,$gen_controllers)){
            	continue;
            }
        	$controllerNode = $aco->node('controllers/'.$controllerName);
            
        	
        	
            if (!$controllerNode) {
                $aco->create(array(
                    'parent_id' => $root['Aco']['id'],
                    'model' => null,
                    'alias' => $controllerName,
                ));
                $controllerNode = $aco->save();
                $controllerNode['Aco']['id'] = $aco->id;
                $log[] = 'Created Aco node for '.$controllerName;
            } else {
                $controllerNode = $controllerNode[0];
            }
            
            $methods = $this->AclGenerate->listActions($controllerName, $controllerPath);
            if(empty($methods)) continue;
            foreach ($methods AS $method) {
                $methodNode = $aco->node('controllers/'.$controllerName.'/'.$method);
                if (!$methodNode) {
                    $aco->create(array(
                        'parent_id' => $controllerNode['Aco']['id'],
                        'model' => null,
                        'alias' => $method,
                    ));
                    $methodNode = $aco->save();
                }
            }
            
        }

        if (isset($this->params['named']['permissions'])) {
            $this->redirect(array('plugin' => 'acl', 'controller' => 'acl_permissions', 'action' => 'index'));
        } else {
            $this->redirect(array('action' => 'index'));
        }
    }

}
?>