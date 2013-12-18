<?php
/**
 * 流程步骤控制
 * 流程步骤的每一个action所有登录的后台用户都具备进入权限。具体可操纵的数据在流程内控制
 * 必须指定一个流程步骤，用户才能进入action。进入后，检验用户是否对该流程步骤具备操作权限
 */
class FlowstepsController extends AppController {

    var $name = 'Flowsteps';
    
    var $actsAs = array('Acl' => array('type' => 'controlled'));
    
    function beforeFilter(){
    	parent::beforeFilter();
//     	print_r($this->request);
    	// 所有登录用户都具备进入流程action的权限，具体的操作在action中校验
//     	$this->Auth->allowedActions = array_merge($this->Auth->allowedActions,array(
//     		'admin_dataadd','admin_dataedit','admin_datadelete',
//     		'admin_dataview','admin_datalist','admin_datatrash','admin_datarestore',
//     	)); 
//     	print_r($this->Auth->allowedActions);
    }
    
    /**
     * 增加操作时，生成aco记录
     */
    function admin_add()
    {
    	parent::admin_add();
    	
    	$insert_id = $this->insert_id;
		
		if($insert_id && !empty($_POST))
		{
			$stepid = $insert_id;
			$aco =& $this->Acl->Aco;
			
			$root = $aco->node('flowsteps');
			if (!$root) {
				$aco->create(array('parent_id' => null, 'model' => null, 'alias' => 'flowsteps'));
				$root = $aco->save();
				$root['Aco']['id'] = $aco->id; 
				$log[] = 'Created Aco node for controllers';
			} else {
				$root = $root[0];
			} 
			
	    	$stepNode = $aco->node('flowsteps/'.$stepid);
			if (!$stepNode) {
				$aco->create(array('parent_id' => $root['Aco']['id'], 'model' => 'Flowstep','foreign_key'=>$stepid, 'alias' => $stepid));
				$stepNode = $aco->save();
				$stepNode['Aco']['id'] = $aco->id;
				//$log[] = 'Created Aco node for ' . $ctrlName;				
			} else {
				$stepNode = $stepNode[0];
			}
		}
		
    }
    
    function __stepAllowActions($data)
    {
    	if($data['Flowstep']['allowactions'])
    	{
    		$allowactions = explode(',',$data['Flowstep']['allowactions']);
    		if(substr($this->action,0,10)=='admin_data')
    		{
    			$current_action = substr($this->action,10); // 判断时，去掉前面的admin_data
	    		if(in_array($current_action,$allowactions))
	    		{
	    			return true;
	    		}
    		}
    	}
    	if(in_array('*',$this->Auth->allowedActions))
    	{
    		return true;
    	}
    	
    	return false;
    }
    
	function admin_menu($parent_id=null) {
		
		$user_id = $this->Auth->user('id');
		if($user_id)
		{
			
			$roleClass = 'Staff';
			
			$role_ids = $this->_getStaffRoles();
			
			$conditions =  array(
				'OR'=>array(
					array('Aro.foreign_key' => $user_id,'Aro.model'=>'Staff'),
					array('Aro.foreign_key' => $role_ids,'Aro.model'=>'Role'),
					),
			);
			
			if(!in_array(1,$role_ids))
			{
				$this->loadModel('Aro');
				$options = array(
					'conditions' => $conditions,
					'recursive' => -1,
					//'limit' => 10,
		            // 'page' => 1,
		            'fields' => array('Aro.id','Flowstep.name','Flowstep.id','Flowstep.flow_id'),
					'joins'=> array(					
						array(
							'table' => Inflector::tableize('ArosAco'),
							'alias' => 'ArosAco',  
							'type' => 'inner',
							'conditions' => array("ArosAco.aro_id = Aro.id"),
						),
						array(
							'table' => Inflector::tableize('Aco'),
							'alias' => 'Aco',  
							'type' => 'inner',
							'conditions' => array("ArosAco.aco_id = Aco.id",'Aco.model'=>'Flowstep'),
						),
						array(
							'table' => Inflector::tableize('Flowstep'),
							'alias' => 'Flowstep',  
							'type' => 'left',
							'conditions' => array("Flowstep.id = Aco.foreign_key"),
						)
					),
				);
				$role_acos = $this->Aro->find('all',$options);
			}
			else
			{
				$options = array(
					'conditions' => array(),
					'recursive' => -1,
					//'limit' => 10,
		            // 'page' => 1,
		            'fields' => array('Flowstep.name','Flowstep.id','Flowstep.flow_id'),
					'joins'=> array(
						array(
							'table' => Inflector::tableize('Aco'),
							'alias' => 'Aco',  
							'type' => 'left',
							'conditions' => array("Flowstep.id = Aco.foreign_key",'Aco.model'=>'Flowstep'),
						),
					)
				);
				$role_acos = $this->Flowstep->find('all',$options);
			}
			
			$acos_list = array();
			foreach($role_acos as $aco)
			{
				$acos_list[$aco['Flowstep']['flow_id']][] = $aco;
			}
			
			$this->loadModel('Flow');
			$flows = $this->Flow->find('all');
			
//			print_r($acos_list);
			//print_r($flows);
			$this->set('role_ids',$role_ids);
			$this->set('flows',$flows);
			$this->set('acos_list',$acos_list);
		}
		else
		{
			$this->__message(__('no permission!',true),'',99999);
			exit;
		}

	}
    
    function admin_datalist($step_id)
    {
    	$this->data = $this->{$this->modelClass}->read(null, $step_id);
//		print_r($this->Auth->allowedActions);
//     	if(!in_array('*',$this->Auth->allowedActions) && !in_array($step_id,$this->Auth->allowedActions) ) // || !$this->__stepAllowActions($this->data)
// 		{
// 			// 列表时，只判断是否有权限进入流程。各流程的数据都能列表，不用判断列表list的权限是否分配
// 			$this->__message(__('no permission!',true),'',99999);
// 			exit;
// 		}
//    	print_r($this->data);
    	$modelClass = $this->data['Flowstep']['flowmodel'];
    	$this->loadModel($modelClass);
    	
    	// 加载表单默认值。用于搜索表单、行内编辑
//     	$this->__loadFormValues($modelClass);    	
//    	print_r($this->viewVars);
    	
//     	$fileds = array_keys($this->{$modelClass}->_schema);
    	
    	$ext_schema = $this->{$modelClass}->getExtSchema();
    	$fileds = array_keys($ext_schema);
    	$xmlarray = xml_to_array($this->data['Flowstep']['conditions']);
    	$step_options = $xmlarray['options'];
    	foreach ($step_options as $key => $value) {
    		$searchoptions[$key] = $value;
    	}
    	if (!empty($searchoptions['joins'])) {
    		$searchoptions['joins'] = array_values($searchoptions['joins']);
    	}

    	$this->set('modelinfo',$this->{$modelClass}->getModelInfo());

    	$this->set('allowactions',$allowactions);	
    	$this->set('fieldlist',$fieldlist);	
		$this->set('col_names',$col_names);	
		$this->set('current_model',$modelClass);
		
		$this->set('datalists',$this->paginate());
    	$this->set('step_id',$step_id);
    }
	
/**
     * 显示数据
     * @param $id
     */
    function admin_dataview($step_id,$id = null)
    {
    	$step_info = $this->{$this->modelClass}->read(null, $step_id);
		
		if(!in_array('*',$this->Auth->allowedActions) && (!in_array($step_id,$this->Auth->allowedActions) || !$this->__stepAllowActions($step_info) )) 
		{
			$this->__message(__('no permission!',true),'',99999);
			exit;
		}
		$modelClass = $step_info['Flowstep']['flowmodel'];
		$this->loadModel($modelClass);
		
    	$searchoptions =  array(
            'conditions' => array($modelClass.'.id' => $id),
            'fields' => array('*'),
        );
     	$joinmodel_fields = array();
        $alias = 0;
        $_schema_keys = array_keys($this->{$modelClass}->_schema);
        
        if(!in_array($modelClass,array('I18nfield','Modelextend')))
        {
			foreach($this->{$modelClass}->getExtSchema() as $k => $fieldinfo)
			{
				if(in_array($k,$_schema_keys) && $fieldinfo['selectmodel'] && $fieldinfo['selectvaluefield'] && $fieldinfo['selecttxtfield'] && in_array($fieldinfo['formtype'],array('select','checkbox','radio')) )
				{
					$alias ++;
					$join_model = $fieldinfo['selectmodel'];
					$model_alias = $join_model.'_'.$alias;
					$selectvaluefield = $fieldinfo['selectvaluefield'];
					$selecttxtfield = $fieldinfo['selecttxtfield'];
					$searchoptions['fields'][] = $model_alias.'.'.$selecttxtfield.' as '.$k.'_txt';
					//$searchoptions['order'] = $join_model.'.'.$selecttxtfield;
					$joinmodel_fields[$k] = $model_alias;
					
					$joinconditions = array($model_alias.'.'.$selectvaluefield.' = '.$modelClass.'.'.$k);
					
					if($fieldinfo['associateflag'] && $fieldinfo['associateelement'] && $fieldinfo['associatefield'] )
					{
						//将级联操作的字段也作为表单连接的条件，否则会包含不符合条件的多余的记录
						$joinconditions[] = $model_alias.'.'.$fieldinfo['associatefield'] .'='.$modelClass.'.'.$fieldinfo['associateelement'];
					}
					$searchoptions['joins'][] = array(
						'table'=> Inflector::tableize($join_model),
						'alias' => $model_alias,  
						'type' => 'left',
						'conditions' => $joinconditions,
					);
				}
			}
        }
        $datas = $this->{$modelClass}->find('first',$searchoptions);
        
    	foreach($joinmodel_fields as $joinfield => $joinmodel)
		{
			if($datas[$joinmodel][$joinfield.'_txt'])
			{
				$datas[$modelClass][$joinfield] = $datas[$joinmodel][$joinfield.'_txt'];
			}
		}
        $this->set('view_fields',explode(',',$step_info['Flowstep']['view_fields']));
        
        $this->set('item', $datas[$modelClass]);
        $this->set('_extschema', $this->{$modelClass}->getExtSchema());
    }
    // 添加各模块数据
	function admin_dataadd($step_id) {
		
		$step_info = $this->{$this->modelClass}->read(null, $step_id);
		
		if(!in_array('*',$this->Auth->allowedActions) && (!in_array($step_id,$this->Auth->allowedActions) || !$this->__stepAllowActions($step_info) )) 
		{
			$this->__message(__('no permission!',true),'',99999);
			exit;
		}
   	print_r($step_info);exit;
    	$modelClass = $step_info['Flowstep']['flowmodel'];
		$this->loadModel($modelClass);
		
//		print_r($this->{$modelClass}->_schema);
//		print_r($this->data);
		$explodefield = $this->__getexplodefield($modelClass);
        if (!empty($this->data)) {
        	// 处理时间格式的数据
        	
	        foreach($this->{$modelClass}->_extschema as $k => $v)
			{
				
				if($v['formtype']=='datetime')
				{
					if(isset($this->data[$modelClass][$k]['ymd']))
					{
						$this->data[$modelClass][$k] = $this->data[$modelClass][$k]['ymd'].' '.$this->data[$modelClass][$k]['his'];
					}
				}
				else
				{
					if($k!=$explodefield && !empty($this->data[$modelClass][$k]) && is_array($this->data[$modelClass][$k]))
					{
						$this->data[$modelClass][$k] = implode(',',$this->data[$modelClass][$k]);
					}
				}
			}
        	// 保存数据
        	$this->autoRender = false;
        	if($explodefield)
        	{
        		
        		$explode_array  = $this->data[$modelClass][$explodefield];
        		if(!is_array($explode_array))
        		{
        			$explode_array = explode("\n",$explode_array);
        		}
        		
        		foreach($explode_array as $val)
        		{
        			if(empty($val)) continue;
        			
	        		$this->{$modelClass}->create();
	        		$this->data[$modelClass]['deleted'] = 0;
		         	$this->data[$modelClass][$explodefield] = $val;
	        		if ($this->{$modelClass}->save($this->data)) {
		         		$successinfo = array('success'=>__('Add success',true),'actions'=>array('OK'=>'closedialog'));
		                $this->Session->setFlash(__('The Data has been saved', true));
		                
		            } else {
		                $this->Session->setFlash(__('The Data could not be saved. Please, try again.', true));
		                echo json_encode($this->{$modelClass}->validationErrors);
		                return ;
		                //$error_info = implode('<br/>',$this->{$this->modelClass}->validationErrors);
		                //$successinfo = array('save_error'=>__('Add error',true),'actions'=>array('OK'=>'closedialog'));
		            }
        		}
        		echo json_encode($successinfo);
        		return ;
        	}
        	else
        	{
	            //$this->{$modelClass}->create();
//	            print_r($this->data);
				$this->data[$modelClass]['deleted'] = 0;
	         	if ($this->{$modelClass}->save($this->data)) {
	         		$successinfo = array('success'=>__('Add success',true),'actions'=>array('OK'=>'closedialog'));
	         		
		         	//保存上传的附件
	            	if(isset($this->data['Uploadfile']) && is_array($this->data['Uploadfile']['uploadfile_id']))
	            	{
	            		$insertid = $this->{$modelClass}->getLastInsertID();
	            		
	            		$this->loadModel('Uploadfile');
	            		$files = $this->data['Uploadfile']['uploadfile_id'];
	            		$this->data['Uploadfile']['data_id']= $insertid;
	            		foreach($files as $file_id)
	            		{
	            			$this->Uploadfile->create();
			            	$this->data['Uploadfile']['id'] = $file_id;	
			            	$this->data['Uploadfile']['data_id'] = $insertid;	
			            	// 只修改  data_id	            	      	
			            	$this->Uploadfile->save($this->data,true,array('data_id'));
	            		}
	            	}
            	
	                $this->Session->setFlash(__('The Data has been saved', true));
	                //$this->redirect(array('action'=>'index'));
	                echo json_encode($successinfo);
	            } else {
	                $this->Session->setFlash(__('The Data could not be saved. Please, try again.', true));
	                echo json_encode($this->{$modelClass}->validationErrors);
	                //$error_info = implode('<br/>',$this->{$this->modelClass}->validationErrors);
	                //$successinfo = array('save_error'=>__('Add error',true),'actions'=>array('OK'=>'closedialog'));
	            }
        	}
            return ;
        }
        else
        {
        	// 无提交值，生成表单。加载选项值,及修改时加载上传文件的列表
	        $this->__loadFormValues($modelClass);
        }
        //http://www.saecms.com/admin/misccates/add/parent_id:25 
        // "/controller/action/param1:value1/param2:value2"
        // 让形如这种形式传参的数据传入data中，一般用于表单默认值的选择。 
		if(is_array($this->params['named']))
        {
        	foreach($this->params['named'] as $k => $v)
        	{
        		$this->data[$modelClass][$k] = $v;
        	}
        }
//         echo $modelClass;exit;
// 		if(!empty($this->{$modelClass}->getExtSchema()))
//         {
//         	// 将 _extschema传入helps中使用
//         	$this->params['_extschema'] = $this->{$modelClass}->getExtSchema();
//         	if(!empty($step_info['Flowstep']['edit_fields']))
//         	{
//         		$this->params['_flowstep_edit_fields'] = explode(',',$step_info['Flowstep']['edit_fields']);
//         	}
//         	else
//         	{
//         		die('no edit_fields,please edit the flow step');
//         	}
//         }
        $this->set('current_model',$modelClass);	
        $this->set('step_id',$step_id);	
    }
    
    // 修改各模块的数据
    function admin_dataedit($step_id,$data_id)
    {
    	$step_info = $this->{$this->modelClass}->read(null, $step_id);
    	
    	if(!in_array('*',$this->Auth->allowedActions) && (!in_array($step_id,$this->Auth->allowedActions) || !$this->__stepAllowActions($step_info) ))
		{
			$this->__message(__('no permission!',true),'',99999);
			exit;
		}
		$modelClass = $step_info['Flowstep']['flowmodel'];
		$this->loadModel($modelClass);
		
		$table_fields = array_keys($this->{$modelClass}->_schema);
		if(!empty($_POST['oper']) && $_POST['oper']=='edit')	// jqgrid 行内编辑的保存 
		{
			$data_id = $_POST['id'];
			foreach($_POST as $key => $val)
			{
				if(in_array($key,$table_fields))
				{
					$this->data[$modelClass][$key] = $val;
				}
			}
		}
//    	print_r($step_info);
    	
		
        if (!$data_id && empty($this->data)) {
            $this->Session->setFlash(__('Invalid Data', true));
            $this->redirect(array('action'=>'index'));
        }
        if (!empty($this->data)) {
        	// 处理时间格式的数据
	        if(empty($_POST['data'][$modelClass]['password']))
			{
				// 当post提交空密码时，不修改密码。修改用户资料，输入了新密码时，修改密码；无输入密码时不修改密码。
				unset($this->data[$modelClass]['password']);
			}
			
	        foreach($this->{$modelClass}->getExtSchema() as $k => $v)
			{
				
				if($v['formtype']=='datetime')
				{
					if(isset($this->data[$modelClass][$k]['ymd']))
					{
						$this->data[$modelClass][$k] = $this->data[$modelClass][$k]['ymd'].' '.$this->data[$modelClass][$k]['his'];
					}
				}
				else
				{
					if(is_array($this->data[$modelClass][$k]))
					{
						$this->data[$modelClass][$k] = implode(',',$this->data[$modelClass][$k]);
					}
				}
			}
			
        	$this->autoRender = false;        	
            if ($this->{$modelClass}->save($this->data)) {
            	$successinfo = array('success'=>__('Edit success',true),'actions'=>array('OK'=>'closedialog'));
            	
            	//保存上传的附件
            	if(isset($this->data['Uploadfile']) && is_array($this->data['Uploadfile']['uploadfile_id']))
            	{
            		$insertid = $id;
            		
            		$this->loadModel('Uploadfile');
            		$files = $this->data['Uploadfile']['uploadfile_id'];
            		$this->data['Uploadfile']['data_id']= $insertid;
            		foreach($files as $file_id)
            		{
            			$this->Uploadfile->create();
		            	$this->data['Uploadfile']['id'] = $file_id;	
		            	$this->data['Uploadfile']['data_id'] = $insertid;	
		            	// 只修改  data_id	            	      	
		            	$this->Uploadfile->save($this->data,true,array('data_id'));
            		}
            	}
            	
            	$this->Session->setFlash(__('The Data has been saved', true));
                echo json_encode($successinfo);
            } else {
                $this->Session->setFlash(__('The Data could not be saved. Please, try again.', true));
                echo json_encode($this->{$modelClass}->validationErrors);
            }
            return ;
        }
        if (empty($this->data)) {
            $this->data = $this->{$modelClass}->read(null, $data_id);
            foreach($this->{$modelClass}->getExtSchema() as $k => $v)
			{
				
				if(in_array($v['formtype'],array('checkbox')) && isset($this->data[$modelClass][$k]))
				{
					$this->data[$modelClass][$k]= explode(',',$this->data[$modelClass][$k]);
				}
			}
            $this->__loadFormValues($modelClass,$data_id);
            
            unset($this->data[$modelClass]['password']);
        }
        $this->data['Flowstep'] = $step_info['Flowstep'];
//     	if(!empty($this->{$modelClass}->getExtSchema()))
//         {
//         	// 将 _extschema传入helps中使用
//         	$this->params['_extschema'] = $this->{$modelClass}->getExtSchema();
//         	if(!empty($step_info['Flowstep']['edit_fields']))
//         	{
//         		$this->params['_flowstep_edit_fields'] = explode(',',$step_info['Flowstep']['edit_fields']);
//         	}
//         	else
//         	{
//         		die('no edit_fields,please edit the flow step');
//         	}
//         }
        $this->set('current_model',$modelClass);	
        $this->set('step_id',$step_id);	
        $this->set('data_id',$data_id);	
    }
  
    /**
     * 设置删除标记
     * @param $id
     */
	function admin_datatrash($step_id,$id = null) {
		$this->autoRender = false;  
        if (!$id) {
            $this->redirect('/');
        }
        $step_info = $this->{$this->modelClass}->read(null, $step_id);
		
        if(!in_array('*',$this->Auth->allowedActions) && (!in_array($step_id,$this->Auth->allowedActions) || !$this->__stepAllowActions($step_info) ))
		{
			$this->__message(__('no permission!',true),'',99999);
			exit;
		}
		$modelClass = $step_info['Flowstep']['flowmodel'];
		$this->loadModel($modelClass);
		
        $data = array();
	    $data[$modelClass]['id'] = $id;
	    $data[$modelClass]['deleted'] = 1;
	    
		if ($this->{$modelClass}->save($data[$modelClass])){
            $this->Session->setFlash(__('The Data is trashed success.', true));
            $successinfo = array('success'=>__('Trash success',true));
        }
        else
        {
        	$successinfo = array('error'=>__('Trash error',true));
        }
        echo json_encode($successinfo);
        exit;
    }
	/**
     * 恢复删除标记
     * @param $id
     */
	function admin_datarestore($step_id,$id = null) {
		$this->autoRender = false;  
        if (!$id) {
            $this->redirect(array('action' => 'index'));
        }
        
        $step_info = $this->{$this->modelClass}->read(null, $step_id);
		
        if(!in_array('*',$this->Auth->allowedActions) && (!in_array($step_id,$this->Auth->allowedActions) || !$this->__stepAllowActions($step_info) ))
		{
			$this->__message(__('no permission!',true),'',99999);
			exit;
		}
		$modelClass = $step_info['Flowstep']['flowmodel'];
		$this->loadModel($modelClass);
		
        $data = array();
	    $data[$modelClass]['id'] = $id;
	    $data[$modelClass]['deleted'] = 0;
	    
		if ($this->{$modelClass}->save($data[$modelClass])){
            $this->Session->setFlash(__('The Data is restore success.', true));
            $successinfo = array('success'=>__('Restore success',true));
        }
        else
        {
        	$successinfo = array('error'=>__('Restore error',true));
        }
        echo json_encode($successinfo);
        exit;
    }
    /**
     * 删除数据
     * @param $id
     */
	function admin_datadelete($step_id,$id = null) {
		$this->autoRender = false;  
		
		$step_info = $this->{$this->modelClass}->read(null, $step_id);
		
        if(!in_array('*',$this->Auth->allowedActions) && (!in_array($step_id,$this->Auth->allowedActions) || !$this->__stepAllowActions($step_info) ))
		{
			$this->__message(__('no permission!',true),'',99999);
			exit;
		}
		$modelClass = $step_info['Flowstep']['flowmodel'];
		$this->loadModel($modelClass);
		
        if (!$id) {
            $successinfo = array('error'=>__('No data selectd',true));;
        }
        if ($this->{$modelClass}->delete($id)) {
            $successinfo = array('success'=>__('Delete success',true));
        }
        else
        {
        	$successinfo = array('error'=>__('Delete error',true));
        }
        echo json_encode($successinfo);
        exit;
    }
	
    function parentNode() {
		return null;
	}
    
    
}
?>