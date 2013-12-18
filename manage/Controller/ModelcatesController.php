<?php

class ModelcatesController extends AppController {

    var $name = 'Modelcates';	
    function admin_loadSplitForm($model,$cateid,$dataid = null){    	
    	$this->autoRender = false;
    	$cateinfo = $this->Modelcate->read(null,$cateid);
    	if($cateinfo['Modelcate']['has_split']){
    		$controllername = $model.'_split'.$cateid.'s';
    		if($dataid){
    			$url = '/admin/'.$controllername.'/edit/'.$dataid;
    		}
    		else{
    			$url = '/admin/'.$controllername.'/add';
    		}
    		echo $url;
    		echo $this->requestAction($url);    		
    	}
    	else{
    		echo 'no fields';
    	}
    	exit;
    }
    
    function admin_add(){
    	parent::admin_add();    	
    	$this->_createModel();
    }
    
    function admin_edit($id){
    	parent::admin_edit($id);
    	$this->_createModel();
    }
    /**
     * 拆分表不存在时，创建数据表
     */
    function _createModel(){
    	if(!empty($_POST) && $this->data[$this->modelClass]['has_split']){    		
    		$model_name = $this->data[$this->modelClass]['model'].'Split'.$this->data[$this->modelClass]['id'];
    		try{
    			$this->loadModel($model_name);
    			$fields = $this->{$model_name}->schema(true);    			
    		}
    		catch(Exception $e){
    			// 缺少 table
    			$url = "/admin/modelextends/add/data[Modelextend][name]:$model_name/data[Modelextend][cname]:$model_name";
    			echo $this->requestAction($url);   
    		}
    	}
    }
}
?>