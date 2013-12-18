<?php

class ArosAcosController extends AppController
{
	
	
	var $name = 'ArosAcos';
	
	var $uses = array('ArosAco','Role','Aro','Aco','Flowstep');
	
	function admin_roleaco()
	{
		$roles = $this->Role->find('all');
		
		$this->set('roles',$roles);	
	}
	
	function admin_set($model='Staff',$data_id = '')
	{
		if(empty($_POST))
		{
			$this->set('model',$model);
			$this->set('data_id',$data_id);
			
			$this->loadModel('Flow');
			$flows = $this->Flow->find('all');
			
			$options = array(
				'conditions' => array('deleted'=>0),
				//'limit' => 10,
	           // 'page' => 1,
	            'fields' => array('*'),
				'joins'=> array(array(
					'table' => Inflector::tableize('Aco'),
					'alias' => 'Aco',  
					'type' => 'left',
					'fields'=>array('*'),
					'conditions' => array('Aco.foreign_key = Flowstep.id','Aco.model'=>'Flowstep'),
				)),
			);
			$acos = $this->Flowstep->find('all',$options);
			$flowsteps = array();
			foreach($acos as $aco)
			{
				$flowid = $aco['Flowstep']['flow_id'];
				$flowsteps[$flowid][] =  $aco;	
			}
	
			$this->set('flows',$flows);	
			
			$this->set('flowsteps',$flowsteps);	
			
			$roleClass = Inflector::classify($model); 
			$this->loadModel($roleClass);
			$conditions = array($roleClass.'.deleted'=>0);
			if($data_id)
			{
				$conditions[] =  array($roleClass.'.id'=>$data_id);
			}
			$options = array(
				'conditions' => $conditions,
				//'limit' => 10,
	           // 'page' => 1,
	            'fields' => array('*'),
				'joins'=> array(array(
					'table' => $dbconfig->{$this->Aco->useDbConfig}['prefix'].Inflector::tableize('Aro'),
					'alias' => 'Aro',  
					'type' => 'left',
					'fields'=>array('*'),
					'conditions' => array("Aro.model = '$roleClass'",'Aro.foreign_key = '.$roleClass.'.id'),
				)),
			);
			$aros = $this->{$roleClass}->find('all',$options);
			$aros_list = array();
			foreach($aros as $aro)
			{
				if(empty($aro[$roleClass]['title'])) $aro[$roleClass]['title'] = $aro[$roleClass]['name'];
				$aros_list[$aro['Aro']['id']] = $aro[$roleClass]['title'];
			}
			$this->set('aros',$aros_list);	
			
			
			if($data_id)
			{
				$conditions = array($roleClass.'.deleted'=>0);
				$conditions[] =  array($roleClass.'.id'=>$data_id);
					$options = array(
					'conditions' => $conditions,
					//'limit' => 10,
		           // 'page' => 1,
		            'fields' => array('*'),
					'joins'=> array(array(
						'table' => $dbconfig->{$this->Aco->useDbConfig}['prefix'].Inflector::tableize('Aro'),
						'alias' => 'Aro',  
						'type' => 'left',
						'fields'=>array('*'),
						'conditions' => array("Aro.model = '$roleClass'",'Aro.foreign_key = '.$roleClass.'.id'),
					),
					array(
						'table' => $dbconfig->{$this->Aco->useDbConfig}['prefix'].Inflector::tableize('ArosAco'),
						'alias' => 'ArosAco',  
						'type' => 'left',
						'fields'=>array('*'),
						'conditions' => array("ArosAco.aro_id = Aro.id"),
					)),
				);
				$role_acos = $this->{$roleClass}->find('all',$options);
				$acos = array();
				foreach($role_acos as $role_aco)
				{
					//在cakephp 2.3.0中，值判断是严格检查类型的，需要使用intval将aco_id转成整形
					//in_array($name, $attributes['value'], true)
					$acos[] = intval($role_aco['ArosAco']['aco_id']);
				}
				$this->request->data['ArosAco']['aco_id']=$acos;
			}
			
		}
		else
		{
			$this->autoRender = false;
			$aco_ids = $this->data['ArosAco']['aco_id'];
			$modelClass=$this->modelClass;
			$this->{$this->modelClass}->deleteAll(array('aro_id'=>$this->data[$modelClass]['aro_id']));
			if(!empty($aco_ids))
			{
				foreach($aco_ids as $aco_id)
				{
					$this->{$modelClass}->create();
		         	$this->data[$modelClass]['aco_id'] = $aco_id;
		         	$this->data[$modelClass]['_create']=1;
		         	$this->data[$modelClass]['_read']=1;
		         	$this->data[$modelClass]['_update']=1;
		         	$this->data[$modelClass]['_delete']=1;
		         	
	        		if ($this->{$this->modelClass}->save($this->data)) {
		         		
		                $this->Session->setFlash(__('The Data has been saved', true));
		                
		            } else {
		                $this->Session->setFlash(__('The Data could not be saved. Please, try again.', true));
		                echo json_encode($this->{$this->modelClass}->validationErrors);
		                return ;
		            }
				}
			}
			$successinfo = array('success'=>__('Add success',true),'actions'=>array('OK'=>'closedialog'));
			echo json_encode($successinfo);
			exit;
			
		}
	}
	
	
}