<?php

class ModelcatesController extends AppController {

    var $name = 'Modelcates';

//    var $uses = array('Modelcate');
	function admin_recover(){
		$this->Modelcate->recover('parent');	
	}
	function add($model){
		
		$modelClass = $this->modelClass;		
		$model = Inflector::classify($model);
		$this->params['model_data'] = $model;
		
		if (!empty($this->data)) {
			$this->{$modelClass}->create();
			$this->data[$modelClass]['published'] = 1;
			$this->data[$modelClass]['deleted'] = 0;
			$this->data[$modelClass]['ispublic'] = 0;
			$this->data[$modelClass]['model'] = $model;
			$this->data[$modelClass]['creator_id'] = $this->currentUser['User']['id'];
			$this->autoRender = false;
//			print_r($this->data);exit;
			if ($this->{$modelClass}->save($this->data)) {
				$last_insert_id = $this->{$modelClass}->getLastInsertID();
				$successinfo = array(
					'success'=>__('Add success',true),
					'catename'=>$this->data[$modelClass]['title'],
					'cateid'=>$last_insert_id,
				);
				echo json_encode($successinfo);
        		return ;
			}
			else
			{
				echo json_encode($this->{$this->modelClass}->validationErrors);
		        return ;
			}
		}
		else
		{
			$this->__loadFormValues($this->modelClass);
			$this->set('model_data',$model);
		}
	}
}
?>