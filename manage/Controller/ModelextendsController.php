<?php
class ModelextendsController extends AppController{
	
	var $name = 'Modelextends';
	
    // 根据表，自动生成模块记录
	function admin_generate()
	{
		 $tables = $this->{$this->modelClass}->query("show tables");
		 
		  foreach($tables as $table)
		  {
		  	$tablename = array_pop($table['TABLE_NAMES']);
		  	$modelname  = str_replace($this->{$this->modelClass}->tablePrefix,'',$tablename);
		  	if($modelname=='i18n')
		  		continue;
		  	$modelClass = Inflector::classify($modelname);
		  	//echo $modelClass;exit;
		  	//$this->loadModel($modelClass);
		  	
	  		unset($this->data[$this->modelClass]);
	  		$this->{$this->modelClass}->create();
	  		$this->data[$this->modelClass]['name'] = $modelClass;
	  		$this->data[$this->modelClass]['cname'] = $modelClass;
	  		$this->data[$this->modelClass]['tablename'] = $tablename;
	  		$this->data[$this->modelClass]['status'] = 1;
	  		$this->data[$this->modelClass]['belongtype'] = 'onetomany';		  		
	  		$exists = $this->{$this->modelClass}->find('first',array('conditions'=>array(
	  			'name' => $modelClass
	  			)));
	  		//print_r($exists[$this->modelClass]);
	  		if(empty($exists[$this->modelClass]))
	  		{
	  			$this->{$this->modelClass}->save($this->data);
	  		}
//		  		
		  }
		  $this->__message(__('Done',true),array('action' => 'index'));
	}
	
	function admin_add($name='')
	{
		if(is_array($this->request->params['named']['data'])){
			$this->data += $this->request->params['named']['data'];
		}
		
		if(!empty($this->data)){ // 格式化名称，设置表名			
			$this->data[$this->modelClass]['name'] = Inflector::classify($this->data[$this->modelClass]['name']);
			$tablename = $this->data[$this->modelClass]['tablename'] = $this->{$this->modelClass}->tablePrefix.Inflector::tableize($this->data[$this->modelClass]['name']);
		}
		// 在父类方法中 保存值，表中插入数据
		parent::admin_add();
		
		$modelClass = $this->data[$this->modelClass]['name'];
		if(!empty($_POST))
		{
			$this->autoRender = false;
			$sql = "CREATE TABLE `$tablename` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` varchar(200) null default '',
`coverimg` varchar(200) null default '',
`cate_id` int(11) null default 0,
`creator` int(11) null default 0,
`status` tinyint NULL DEFAULT '0',
`published` tinyint(1) NULL DEFAULT '0',
`deleted` tinyint(1) NULL DEFAULT '0',
`created` DATETIME NULL ,
`updated` DATETIME NULL
) ENGINE = MYISAM ";
			//`lastupdator` int(11) null default 0,
			//`remoteurl` varchar(200) null default '',
			//`locale`	char(5) NULL DEFAULT '',
			// status:记录状态，用于多级审批，工作流设置状态等
			//$comments = $this->requestAction("/comments/get_comments_data/$modelClass/".${$modelClass}[$modelClass]['id']);
			$this->{$this->modelClass}->query($sql);
			$date = date('Y-m-d H:i:s');
			//id
			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`) VALUES (1, 6, 1, 1, 1, 0, 'equal', 0, 'id', '编号', 'integer', 11, NULL, 1, '$modelClass', '$date', '$date')";
			$this->{$this->modelClass}->query($sql);
			//name
			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`) VALUES (1, 5, 1, 1, 1, 0, 'equal', 0, 'name', '名称', 'string', 200, NULL, 1, '$modelClass', '$date', '$date')";
			$this->{$this->modelClass}->query($sql);
			//coverimg
			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`) VALUES (1, 5, 1, 1, 1, 0, 'equal', 0, 'coverimg', '封面图片', 'string', 200, NULL, 1, '$modelClass', '$date', '$date')";
			$this->{$this->modelClass}->query($sql);
				
			//cate_id
			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`) VALUES (1, 6, 1, 1, 1, 0, 'equal', 0, 'cate_id', '分类', 'integer', 11, NULL, 1, '$modelClass', '$date', '$date')";
			$this->{$this->modelClass}->query($sql);
			// creator
			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`) VALUES (1, 6, 1, 1, 1, 0, 'equal', 0, 'creator', '编创建者', 'integer', 11, NULL, 1, '$modelClass', '$date', '$date')";
			$this->{$this->modelClass}->query($sql);
			//lastupdator
// 			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`) VALUES (1, 6, 1, 1, 1, 0, 'equal', 0, 'lastupdator', '最后修改人', 'integer', 11, NULL, 1, '$modelClass', '$date', '$date')";
// 			$this->{$this->modelClass}->query($sql);
			// remoteurl
// 			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`) VALUES (1, 5, 1, 1, 1, 0, 'equal', 0, 'remoteurl', '引用地址', 'string', 200, NULL, 1, '$modelClass', '$date', '$date')";
// 			$this->{$this->modelClass}->query($sql);
			//status
			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`,`selectvalues`,`formtype`) VALUES (1, 3, 1, 1, 1, 0, 'equal', 0, 'status', '状态', 'integer', 11, '0', 1, '$modelClass', '$date', '$date','0=>否\n1=>是','select')";
			$this->{$this->modelClass}->query($sql);
			//published
			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`,`selectvalues`,`formtype`) VALUES (1, 3, 1, 1, 1, 0, 'equal', 0, 'published', '是否发布', 'integer', 11, '0', 1, '$modelClass', '$date', '$date','0=>否\n1=>是','select')";
			$this->{$this->modelClass}->query($sql);
			//deleted
			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`,`selectvalues`,`formtype`) VALUES (1, 3, 1, 1, 1, 0, 'equal', 0, 'deleted', '是否删除', 'integer', 11, '0', 1, '$modelClass', '$date', '$date','0=>否\n1=>是','select')";
			$this->{$this->modelClass}->query($sql);
			//locale
// 			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`,`selectvalues`,`formtype`) VALUES (1, 3, 1, 1, 1, 0, 'equal', 0, 'locale', '语言版本', 'char', 5, '0', 1, '$modelClass', '$date', '$date','zh_cn','select')";
// 			$this->{$this->modelClass}->query($sql);
			// created
			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `formtype`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`) VALUES (1, 2, 1, 1, 1, 0, 'equal', 0, 'datetime', 'created', '创建时间', 'datetime', NULL, NULL, 1, '$modelClass', '$date', '$date')";
			$this->{$this->modelClass}->query($sql);
			// updated
			$sql ="INSERT INTO `cake_i18nfields` (`savetodb`, `sort`, `allowadd`, `allowedit`, `selectautoload`, `associateflag`, `associatetype`, `deleted`, `formtype`, `name`, `translate`, `type`, `length`, `default`, `allownull`, `model`, `updated`, `created`) VALUES (1, 1, 1, 1, 1, 0, 'equal', 0, 'datetime', 'updated', '修改时间', 'datetime', NULL, NULL, 1, '$modelClass', '$date', '$date')";
			$this->{$this->modelClass}->query($sql);	
		}
		elseif(empty($this->data)){
			$this->data[$this->modelClass]['name'] = $name;
		}
	}
	
	function admin_delete($id){
		
		$data = $this->{$this->modelClass}->read(null, $id); 
		
		parent::admin_delete($id);
		
		$sql ="drop table '".$data[$this->modelClass]['tablename']."'";
		$this->{$this->modelClass}->query($sql);
		
		$i18nfield_table = $this->{$this->modelClass}->tablePrefix.Inflector::tableize('i18nfield');
		$sql ="delete from '".$i18nfield_table."' where model = '".$data[$this->modelClass]['model']."'";
		$this->{$this->modelClass}->query($sql);
		
		
	}
	
	function admin_edit($id=null,$copy = NULL)
	{
		if (!empty($this->data)) {
			$this->data[$this->modelClass]['name'] = Inflector::classify($this->data[$this->modelClass]['name']);
			$this->data[$this->modelClass]['tablename'] = $this->{$this->modelClass}->tablePrefix.Inflector::tableize($this->data[$this->modelClass]['name']);
			if(!$id){
				$id = $this->data[$this->modelClass]['id'];
			}
		}
		$before_edit = $this->{$this->modelClass}->read(null, $id);
		parent::admin_edit($id,$copy);
		
		if (!empty($_POST)) {
			if(empty($copy)){
				//RENAME TABLE `测试`  TO `测试s` ;
				if($before_edit[$this->modelClass]['tablename']!=$this->data[$this->modelClass]['tablename']){
					$sql = "RENAME TABLE `".$before_edit[$this->modelClass]['tablename']."`  TO `".$this->data[$this->modelClass]['tablename']."` ;";
					$this->{$this->modelClass}->query($sql);
					// 修改i18nfields表中相关记录里的模块名称的值
					$sql = "update ".$this->{$this->modelClass}->tablePrefix."i18nfields set model = '".$this->data[$this->modelClass]['name']."' where model='".$before_edit[$this->modelClass]['name']."' ;";
					$this->{$this->modelClass}->query($sql);
				}
				$modelname = Inflector::classify($this->data[$this->modelClass]['name']);
				Cache::delete('model_extend_info_'.$modelname); 
			}
			elseif($copy){
				// 复制，创建一份新表，并新建I18nfield中对应的字段
				$sql = "SHOW CREATE TABLE `".$before_edit[$this->modelClass]['tablename']."`;";
				$result = $this->{$this->modelClass}->query($sql);
				$create_sql = $result[0][0]['Create Table'];
				$create_sql = preg_replace('/\sAUTO_INCREMENT=\d*/is',' AUTO_INCREMENT=1 ',$create_sql);				
				$create_sql = preg_replace('/'.$before_edit[$this->modelClass]['tablename'].'/is',$this->data[$this->modelClass]['tablename'],$create_sql);
				$this->{$this->modelClass}->query($create_sql);
				
				$this->loadModel('I18nfield'); 
				$fields = $this->I18nfield->find('all',array(
					'conditions'=>array('model'=> $before_edit[$this->modelClass]['name']),
				));
				foreach($fields as $field){
					$field['I18nfield']['model'] = $this->data[$this->modelClass]['name'];
					unset($field['I18nfield']['id']);
					$this->I18nfield->create();
					$this->I18nfield->save($field);
				}
			}
		}
	}
}