<?php
/**
 * 用于表的拆分，如产品库不同类型的产品有不同的产品参数，拆分到不同的表 
 * @author Arlon
 *
 */
class ModelSplitBehavior extends ModelBehavior {
	public $name = 'ModelSplit';
	
	/**
	 * 保存数据时，保存拆分表数据
	 * @param $Model
	 * @param $created
	 */
	public function aftersave(&$Model,$created) {		
		if (isset($Model->data['Modelcate']['id'])) {			
			$modelcateinfo = $Model->Modelcate->read(null,$Model->data['Modelcate']['id']);			
			$modelClass = $Model->alias.'Split'.$modelcateinfo['Modelcate']['id'];
			$splitmodel = $this->_loadmodel($modelClass);
	    	$Model->data[$modelClass]['id'] = $Model->id;
	    	$splitmodel->save($Model->data[$modelClass]);	    	
		}
		return true;
	}
	
	/**
	 * 加载一个表模型
	 * @param $modelClass
	 */
	private function _loadmodel($modelClass){
		if($splitmodel = & ClassRegistry::getObject($modelClass)){
    		return $splitmodel;
    	}
    	else{
    		return $splitmodel =  ClassRegistry::init(array('class' => $modelClass, 'alias' => $modelClass, 'id' => null));
    	}
	}
	
	/**
	 * 当只返回单条结果且有id数据时，如果这个数据是拆分表的，则从拆分表中取出相关部分。
	 * @param unknown_type $model
	 * @param unknown_type $results
	 * @param unknown_type $primary
	 */
	public function  afterFind(&$model, $results, $primary) {		
		if(count($results)==1 && $results[0][$model->alias]['id']){
			$modelcateinfo = $results[0]['Modelcate'][0];
			if($modelcateinfo['has_split']){
				// 只取出单条信息时，找到拆分表，加载出相应的信息
				$cateid = $modelcateinfo['id'];
				$modelClass = $model->alias.'Split'.$cateid;				
				$splitmodel = $this->_loadmodel($modelClass);
				$splitinfo = $splitmodel->find('first',array(
					'conditions'=>array( 'id'=> $results[0][$model->alias]['id']),
				));
				if(!empty($splitinfo)){
					$results[0]['ModelSplitInfo'] = $splitinfo[$modelClass];					
					$model->modelSplitOptions = $this->_loadMiscCache($modelClass);
					$model->modelSplitSchema = $this->_loadModelI18nInfo($modelClass);					
				}
			}
		}
		return $results;
	}
	
	/**
	 * 加载拆分模块的在Misccate中的所有选项的值
	 * @param $modelClass
	 */
	private function _loadMiscCache($modelClass){
		$cachekey = 'Misc-model-options-'.$modelClass;
		$result = Cache::read($cachekey); 
		if ($result === false){
			$miscmodel = $this->_loadmodel('Misccate');
			$result = array();
			$options = $miscmodel->find('all',array(
					'conditions'=>array('model'=> $modelClass),
					'fields'=>array('id','name')
			));
			foreach($options as $value){
				$result[$value['Misccate']['id']] = $value['Misccate']['name'];
			}
		}
		return $result;
	}
	
	/**
	 * 加载拆分模块的在Misccate中的所有选项的值
	 * @param $modelClass
	 */
	private function _loadModelI18nInfo($modelClass){
		$cachekey = 'model-i18nfield-name-translate-'.$modelClass;
		$extschema = Cache::read($cachekey); 
		if ($extschema === false){
			$I18nfield = $this->_loadmodel('I18nfield');
			$extschema=array();
			$tempextschema = $I18nfield->find('all',array(
				'conditions'=>array('model'=> $modelClass,'savetodb'=>1,'deleted'=>0), //'locale'=> $locale,
				'fields' => array('id','name','translate'),
		  		'order' => array('sort desc'),
		  		)
		  	);
		  	foreach($tempextschema as $value){
		  		$extschema[$value['I18nfield']['name']] = $value['I18nfield']['translate'];
		  	}
		}
		return $extschema;
	}
	
	public function setup($Model, $config = array()) {
		
	}
}