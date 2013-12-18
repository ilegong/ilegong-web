<?php
/**
 * 后台模型仅使用主库，不连接从库
 */
App::uses('Model', 'Model');

class AppModel extends Model {
	var $useDbConfig = 'default';
	
	public $recursive = -1;
	
	private $_extschema = array();
	
	private $_modelinfo = array();
	
	public function __construct($id = false, $table = null, $ds = null) {
		/**
		 * 在parent::__construct中，会初始化$this->Behaviors，并调用behavior的setup方法。
		 */
		parent::__construct($id, $table, $ds);		
		$this->setDataSource('default');
		if($this->name=='Ajax' || $this->name=='Ajaxis'){
			return false;
		}
		else{
			$this->getModelInfo();
			$this->bindAssociate();
			if($this->table && substr($this->name,-4)!='I18n'){
				// 当模型名称的最后4位为I18n时，多语言一对一的记录模块。无需加载模块信息。 加载造成I8循环调用，fatal error。
				$this->getExtSchema();
			}
			// 根据字段设置，加载字段值的验证规则。
			foreach($this->_extschema as $k => $v){
				if(!$v['allownull'] || 	$v['validationregular']=='notempty'){
					$this->validate[$k]['notempty']=array('rule' => 'notEmpty','message' => __('This field cannot be left blank.',true),);
				}
				// 不能为空的条件可以与其它判断条件组合
				if($v['validationregular']=='email'){
					$this->validate[$k]['email']=array(
							'rule' => 'email',
							'message' => __('Please enter a valid email address.',true),
					);
				}
				elseif($v['validationregular']=='unique'){
					$this->validate[$k]['unique'] = array(
							'rule' => 'isUnique',
							'message' => __('This value has already been taken.',true),
					);
				}
				elseif($v['validationregular']=='url'){
					$this->validate[$k]['url']=array(
							'rule' => 'url',
							'message' => __('Please enter a valid url.',true),
					);
				}
				elseif($v['validationregular']=='numeric'){
					$this->validate[$k]['numeric']=array(
							'rule' => 'numeric',
							'message' => __('Please enter a valid numeric.',true),
					);
				}
				elseif($v['validationregular']=='alphaNumeric'){
					$this->validate[$k]['alphaNumeric']=array(
							'rule' => 'alphaNumeric',
							'message' => __('Please enter a valid alphaNumeric string.',true),
					);
				}
				elseif($v['validationregular']=='decimal'){
					$this->validate[$k]['decimal']=array(
							'rule' => 'decimal',
							'message' => __('Please enter a valid decimal.',true),
					);
				}
			}
			
// 			$this->bindModel(array());// 其中调用 $this->__createLinks();//关联模块
			
			if(!empty($this->_modelinfo['modeltype'])){
				if($this->_modelinfo['modeltype'] == 'tree'){
					if(!isset($this->actsAs['Tree'])){
						$this->Behaviors->load('Tree',array('left'=>'left','right'=>'right'));
					}
				}
			}
			
			/**
			 * 在模型表信息中，配置使用的behavior，动态启用行为
			 */
			if(!empty($GLOBALS['model_behaviors']['*'])){
				foreach($GLOBALS['model_behaviors']['*'] as $behavior => $config){
					$this->Behaviors->load($behavior, $config);
				}
			}
			if(!empty($GLOBALS['model_behaviors'][$this->name])){
				foreach($GLOBALS['model_behaviors'][$this->name] as $behavior => $config){
					$this->Behaviors->load($behavior, $config);
				}
			}
		}
	}
	
	private function bindAssociate(){
		// 绑定相关标签
		if(!in_array($this->name,array('Uploadfile','Staff'))  && !isset($this->hasMany['Uploadfile'])){
			$this->bindModel(array(					
			'hasMany'=>array(
				'Uploadfile'=> array(
					'className'     => 'Uploadfile',
					'foreignKey'    => 'data_id',
					'conditions'    => array('Uploadfile.modelclass'=>$this->name,'Uploadfile.trash' => '0'),
					'order'    => 'Uploadfile.sortorder asc,Uploadfile.created ASC',
					'limit'        => '',
					'dependent'=> true
			))));
			
		}
		// 绑定相关标签
		if(!in_array($this->name,array('Tag','Staff')) && !isset($this->hasAndBelongsToMany['Tag'])){
			$this->bindModel(array(
			'hasAndBelongsToMany' => array(
					'Tag' => array(
							'className'              => 'Tag',
							'joinTable'              => 'tag_relateds',
							'foreignKey'             => 'relatedid', // 对应本模块的id
							'associationForeignKey'  => 'tag_id', // 对应tag的id
							'conditions'             => array('TagRelated.relatedmodel' => $this->name),
							'unique'                 => true,//'keepExisting'
							'dependent'            => true,
							'exclusive'            => true,
					)
			)));
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
		
		if(!empty($this->_modelinfo['related_model'])){
	  		$ralated_models = optionstr_to_array($this->_modelinfo['related_model']);
	  		foreach($ralated_models as $key => $value){
	  			$r_model = explode('|',$key);
	  			$hastype=$r_model[1];
	  			$r_modelname = $r_model[0];
	  			if(ModelExists($r_modelname)){
		  			// hasMany
		  			$this->{$hastype}[$r_modelname] =  array(
				       'className'     => $r_modelname,
				       'foreignKey'    => $value,
				       'conditions'    => array($r_modelname.'.deleted' => '0'), 
				       'order'    => $r_modelname.'.id ASC',
				       'limit'        => '',
				       'dependent'=> true
			        );
	  			}
	  		}
	  	}
	  	// 		print_r($this->_modelinfo);
		return $this->_modelinfo;
	}
	
	/**
	 *  加载数据表结构
	 */
	public function getExtSchema(){
		//echo $local = Configure::read('Config.language');
		if(!$this->table){
			return array();
		}
		if(empty($this->_extschema)){
			$cachekey = 'extschema_'.$this->name;
			$this->_extschema = Cache::read($cachekey,'_cake_model_'); 
			if ($this->_extschema === false) {
				$this->_extschema=array();
				if($this->name=='I18nfield'){
					$I18nObj = $this;
				}
				else{
					$I18nObj =  loadModelObject('I18nfield');
				}
				$I18nObj->recursive = -1;
		    	$tempextschema = $I18nObj->find('all',array(
					'conditions'=>array('model'=>$this->name,'deleted'=>0), //'locale'=> $locale,'savetodb'=>1
			  		'order' => array('sort desc'),
			  		)
			  	);
		    	
			  	foreach($tempextschema as $value){
			  		$this->_extschema[$value['I18nfield']['name']] = $value['I18nfield'];
			  	}
			  	Cache::write($cachekey,$this->_extschema,'_cake_model_'); 
			}
		}
		return $this->_extschema;
	}
	/**
	 * 
	 * @param  $fields 数组，索引为字段名，指为要设置的值
	 * @param  $conditions  //数组，同查询的$conditions，update的条件.没有设置条件时，禁止update
	 */
	function update($fields,$conditions)
	{
		//$this->beforeSave();
		$db =& ConnectionManager::getDataSource($this->useDbConfig);	
		if(!empty($conditions)){
			$success = (bool)$db->update($this, $fields, null,$conditions);
		}
		else{
			$success = false;
		}
		//$this->afterSave();
		return $success;
	}
	
	public function escape_string($value){
		return $this->getDataSource()->value($value);
	}
	
	/**
	 * 后台有插入和取数据，可能有超时现象，不启用从库 
	 * (non-PHPdoc)
	 * @see lib/Cake/Model/Model#beforeSave($options)
	 */
//	function beforeSave()
//	{
//	     $this->setDataSource('master');
//	     return true;
//	}
//	 
//	function afterSave($created=false)
//	{
//	     $this->setDataSource('default'); 
//		return true;
//	}
	 
//	function beforeDelete()
//	{
//	     $this->setDataSource('master'); 
//		return true;
//	}
//	 
//	function afterDelete()
//	{
//	     $this->setDataSource('default'); 
//		return true;
//	}

	/**
	 *  query方法支持主从
	 */
//	function query() {
//		$params = func_get_args();
//		$this->beforeSave();
//		$db = $this->getDataSource();		
//		$result = call_user_func_array(array(&$db, 'query'), $params);
//		//$result = parent::query($params);
//		$this->afterSave();
//		return $result;
//	}	
//	function deleteAll($conditions,$cascade = true, $callbacks = false){
//		$this->beforeSave();
//        $output = parent::deleteAll($conditions,$cascade,$callbacks);
//        $this->afterSave();
//        return $output;
//	}

//	function saveAll($data = null, $options = array())
//	{
//		$this->beforeSave();
//		$return = parent::saveAll($data, $options);
//		$this->afterSave();
//		return $return;
//	}
//	
	/**
	 * 用于检测多个字段合并的unique索引。在i18nfield,template模块(Model)中有使用
	 * @param $data
	 * @param $fields
	 */
	function isUniqueMulti($data, $fields) {
	    if (!is_array($fields)) {
			$fields = array($fields);
	    }
	    $tmp = array();  
	    foreach ($fields as $key) {
			$tmp[$key] = $this->data[$this->name][$key];
	    }
	    return $this->isUnique($tmp, FALSE);
	}

}
?>