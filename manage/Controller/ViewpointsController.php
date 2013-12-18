<?php

class ViewpointsController extends AppController {

	var $name = 'Viewpoints';

	
	function publish()
	{
		$modelClass = $this->modelClass;
		if(empty($this->currentUser['User']))
		{
			echo json_encode(array('error'=>'用户信息错误，请重新登录！'));
		    return ;
		}
		if (!empty($this->data)) {
			
			$this->data[$modelClass]['published'] = 0;
			$this->data[$modelClass]['deleted'] = 0;
			$this->autoRender = false;
			$this->loadModel('Pointsupport');
			
			$hassupport = $this->Pointsupport->find('first',array(
				'conditions'=>array(
					'Pointsupport.model'=> $this->data[$modelClass]['model'],
					'Pointsupport.data_id'=> $this->data[$modelClass]['data_id'],
					'Pointsupport.creator'=> $this->currentUser['User']['id']
				),
				'fileds'=>'id'
			));
			if(!empty($hassupport['Pointsupport']['id']))
			{
				// 发表过观点
				$errorinfo = array('error'=>__('Already pointed',true));
				echo json_encode($errorinfo);
				return false;
			}
			
			$haspoint = $this->{$modelClass}->find('first',array(
				'conditions'=>array(
					'Viewpoint.name' => $this->data[$modelClass]['name'],
					'Viewpoint.model'=> $this->data[$modelClass]['model'],
					'Viewpoint.data_id'=> $this->data[$modelClass]['data_id']
				),
				'fields' =>array('Viewpoint.*'),
			));
			//print_r($haspoint);
			$target_model = $this->data[$modelClass]['model'];
			$this->loadModel($target_model);
			
			if(!empty($haspoint))
			{
				// 别人发表过相同的观点
				$this->{$modelClass}->update(
					array('support_nums' => $haspoint[$modelClass]['support_nums']+1),
					array('id' => $haspoint[$modelClass]['id'])
				);
				$this->{$target_model}->update(
					array('point_nums'=>'point_nums+1'),
					array('id' => $haspoint[$modelClass]['data_id'])
				);
				
		        $this->data['Pointsupport']['data_id'] = $haspoint[$modelClass]['data_id'];
		        $this->data['Pointsupport']['model'] = $haspoint[$modelClass]['model'];
		        
		        $this->data['Pointsupport']['viewpoint_id'] = $haspoint[$modelClass]['id']; // 观点编号
		        $this->data['Pointsupport']['creator'] = $this->currentUser['User']['id'];
		        $this->Pointsupport->save($this->data);
		        $successinfo = array('success'=>__('Add success',true));
		        echo json_encode($successinfo);
				return false;			
			}
			$this->data[$modelClass]['support_nums'] = 1;
			$this->data[$modelClass]['creator'] = $this->currentUser['User']['id'];			
			$this->autoRender = false;
			$this->{$modelClass}->create();
			
			if ($this->{$modelClass}->save($this->data)) {
		        $last_insert_id = $this->{$modelClass}->getLastInsertID();
		        $successinfo = array('success'=>__('Add success',true));
		        $this->{$target_model}->update(
					array('point_nums'=>'point_nums+1'),
					array('id' => $this->data[$modelClass]['data_id'])
				);
		        $this->loadModel('Pointsupport');
		        $this->data['Pointsupport']['data_id'] = $this->data[$modelClass]['data_id'];
		        $this->data['Pointsupport']['model'] = $this->data[$modelClass]['model'];
		        
		        $this->data['Pointsupport']['viewpoint_id'] = $last_insert_id; // 观点编号
		        
		        $this->data['Pointsupport']['creator'] = $this->currentUser['User']['id'];
		        $this->Pointsupport->save($this->data);
			} else {
				echo json_encode($this->{$this->modelClass}->validationErrors);
		        return ;
			}
			echo json_encode($successinfo);
        	return ;
		}
	}
	
	function getjson($model,$data_id)
	{
		$this->autoRender = false;
		$modelClass = $this->modelClass;
		$viewpoints = $this->{$modelClass}->find('all',array(
				'conditions'=>array(
					'Viewpoint.model'=> $model,
					'Viewpoint.data_id'=> $data_id
				),
				'fields' =>array('Viewpoint.*'),
			));	
		if(empty($viewpoints))
		{
			$errorinfo = array('error'=>__('have no points',true));
			echo json_encode($errorinfo);
			return false;
		}
		echo json_encode($viewpoints);
	}


}
?>