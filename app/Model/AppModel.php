<?php
App::uses('Model', 'Model');

class AppModel extends Model {

	var $useDbConfig = 'default';
	
	var $tablePrefix;
	
	private $_extschema = array();
	
	public $recursive = -1;
	
	private $_modelinfo = array();
	
	public function __construct($id = false, $table = null, $ds = null) {
		$this->setDataSource('default');		
		parent::__construct($id, $table, $ds);
		if($this->name=='Ajax' || $this->name=='Ajaxis'){
			return false;
		}
		$this->getModelInfo();
		if(!empty($this->_modelinfo['modeltype'])){
			if($this->_modelinfo['modeltype'] == 'tree'){
				if(!isset($this->actsAs['Tree'])){
					$this->Behaviors->attach('Tree',array('left'=>'left','right'=>'right'));
					$this->actsAs['Tree'] = array('left'=>'left','right'=>'right');
				}
			}
			/**
			 * 在模型表信息中，配置使用的behavior，动态启用行为
			 */
// 			if(!empty($GLOBALS['model_behaviors']['*'])){
// 				foreach($GLOBALS['model_behaviors']['*'] as $behavior => $config){
// 					$this->Behaviors->load($behavior, $config);
// 				}
// 			}
			if(!empty($GLOBALS['model_behaviors'][$this->name])){
				foreach($GLOBALS['model_behaviors'][$this->name] as $behavior => $config){
					$this->Behaviors->load($behavior, $config);
				}
			}
		}
	}


	public function isContentModel(){
		$this->getModelInfo();
		if($this->_modelinfo['cate_id']==1){
			return true;
		}
		return false;
	}
	/**
	 * 加载模块结构
	 */
	public function getModelInfo()
	{
		$cachekey = 'extend_info_'.$this->name;
		$this->_modelinfo = Cache::read($cachekey,'_cake_model_');
		if ($this->_modelinfo === false) {
			$this->_modelinfo=array();
			if($this->name=='Modelextend'){
				$extobj = $this;
			}
			else{
				$extobj =  loadModelObject('Modelextend');
			}
			$extobj->recursive = -1;
			$tempextschema = $extobj->find('first',array('conditions'=>array(
					'name'=> $this->name,  // Inflector::classify()
			)));
			$this->_modelinfo = $tempextschema['Modelextend'];
			 
			Cache::write($cachekey,$this->_modelinfo,'_cake_model_');
		}
		// 前台模块不加载数据库中配置的相关模块
		return $this->_modelinfo;
	}
	
	/**
	 * 
	 * @param  $fields 数组，索引为字段名，指为要设置的值
	 * @param  $conditions  //数组，同查询的$conditions，update的条件.没有设置条件时，禁止update
	 */
	public function update($fields,$conditions)
	{
		$this->beforeSave();
		$db =& ConnectionManager::getDataSource($this->useDbConfig);	
		if(!empty($conditions)){
			$success = (bool)$db->update($this, $fields, null,$conditions);
		}
		else{
			$success = false;
		}
		$this->afterSave();
		return $success;
	}
	
	public function escape_string($value){
		return $this->getDataSource()->value($value);
	}
	
	public function beforeSave($options = array())
	{
	     $this->setDataSource('master');
	     return true;
	}
	
	// query方法支持主从
	public function query() {
		$params = func_get_args();
		$this->beforeSave();
		$db =& ConnectionManager::getDataSource($this->useDbConfig);
		$result = call_user_func_array(array(&$db, 'query'), $params);
		//$result = parent::query($params);
		$this->afterSave();
		return $result;
	}
	 
	public function afterSave($created=false)
	{
	    $this->setDataSource('default'); 
		return true;
	}
	 
	public function beforeDelete()
	{
	    $this->setDataSource('master'); 
		return true;
	}
	 
	public function afterDelete()
	{
	    $this->setDataSource('default'); 
		return true;
	}
	 
	public function updateAll($fields, $conditions = true) {
		$this->beforeSave();
        $output = parent::updateAll($fields, $conditions);
        //$args = func_get_args();
        //$output = call_user_func_array(array('parent', 'updateAll'), $args);
        $created = false; // $created表示新建数据
        if ($output) {
            
            $options = array();
            $this->Behaviors->trigger('afterSave', array(&$this, $created, $options));
            
            // tree行为afterSave中，调用了updateAll修改左右节点。
            // 保存后再触发afterSave，注意$created = false;否则会陷入死循环
            $this->_clearCache();
            //$this->id = false;
        }
	    $this->afterSave($created);
	    return $output;
	}
	
	public function deleteAll($conditions,$cascade = true, $callbacks = false){
		$this->beforeSave();
        $output = parent::deleteAll($conditions,$cascade,$callbacks);
        $this->afterSave();
        return $output;
	}
	
	public function saveAll($data = null, $options = array())
	{
		$this->beforeSave();
		$return = parent::saveAll($data, $options);
		$this->afterSave();
		return $return;
	}
}
?>