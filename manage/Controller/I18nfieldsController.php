<?php

class I18nfieldsController extends AppController {

    var $name = 'I18nfields';

    function admin_index($table='') {
        // i18nfield 左侧列表layout页生成
        if (empty($table)) {
            $useDbConfig = $this->{$this->modelClass}->useDbConfig;
            $dbconfig = new DATABASE_CONFIG();
            if ($dbconfig->{$useDbConfig}['prefix']) {
                $tables = $this->{$this->modelClass}->query("SHOW TABLES like '" . $dbconfig->{$useDbConfig}['prefix'] . "%'");
            } else {
                $tables = $this->{$this->modelClass}->query("SHOW TABLES");
            }

            if ($dbconfig->{$useDbConfig}['prefix']) {
                foreach ($tables as $key => $val) {
                    $tables[$key]['TABLE_NAMES'] = str_replace($dbconfig->{$useDbConfig}['prefix'], '', $val['TABLE_NAMES']);
                }
            }
            $this->set('tables', $tables);
        } else {
            $modelClass = Inflector::classify($table);
            $this->redirect(array('action' => 'list', $modelClass));
            exit;
        }

        $this->set('table', $table);
    }

    function admin_add() {
        $modelClass = $this->request->query['model'];
        if (empty($modelClass)) {
            throw new NotFoundException('Named parameter "model" needed!');
        }
        if (!empty($this->data)) {
            $this->data[$this->modelClass]['locale'] = getLocal(Configure::read('Config.language'));
        }
        $this->data[$this->modelClass]['model'] = $modelClass;
        
        // 保存值
        parent::admin_add();
        if (!empty($this->data)) {
        	Cache::delete('extschema_'.$modelClass,'_cake_model_');
        }
        
        $this->set('modelClass', $modelClass);
        //
        if (isset($this->data[$this->modelClass]['name']) && $this->data[$this->modelClass]['savetodb']) {
            $this->_alterTableStruct($this->data[$this->modelClass], $this->data[$this->modelClass]['model']);
        }
    }

    function admin_bulkadd() {

        $modelClass = $this->request->query['model'];
        if (empty($modelClass)) {
            throw new NotFoundException('Named parameter "model" needed!');
        }
        $modelClass = Inflector::classify($modelClass);
        if (!empty($this->data)) {
            if (empty($modelClass)) {
                $errorinfo = array('error' => __('Error params'));
                echo json_encode($errorinfo);
                exit;
            }
            //print_r($this->data[$this->modelClass]);//exit;
            foreach ($this->data[$this->modelClass] as $data) {
                if (empty($data['model'])) {
                    $data['model'] = $modelClass;
                }
                $data['allownull'] = 1;
                if ($this->{$this->modelClass}->save($data)) {
                    $this->_alterTableStruct($data, $data['model']);
                    // 重置id,防止多个项添加时，后面的变成了修改前面的项
                    $this->{$this->modelClass}->id = null;
                }
            }
            Cache::delete('extschema_'.$modelClass,'_cake_model_');
            $successinfo = array('success' => __('Save success'));
            echo json_encode($successinfo);
            exit;
        }
        $this->set('target_model', $modelClass);
        if (empty($this->data)) {
            parent::admin_add(); // 无提交时生成表单的默认值及选项
        }
    }

    function admin_edit($id = null) {
        if (!empty($this->data)) {
            $this->data[$this->modelClass]['tablename'] = Inflector::tableize($this->data[$this->modelClass]['name']);

            if (!$id) {
                $id = $this->data[$this->modelClass]['id'];
            }
            $before_edit = $this->{$this->modelClass}->read(null, $id);
        }

        parent::admin_edit($id);
        
        if (!empty($this->data) && !empty($_POST)) {
        	$this->autoRender = false;
            if (isset($this->data[$this->modelClass]['name']) && $this->data[$this->modelClass]['savetodb']) {
                // 模块名字不允许修改，从数据库中加载
                $modelname = $before_edit[$this->modelClass]['model'];
                $before_name = $before_edit[$this->modelClass]['name'];
                //ALTER TABLE `test` CHANGE `asdfdf` `hello` INT( 11 ) NULL
                if ($before_name != 'id') {
                    $this->_alterTableStruct($this->data[$this->modelClass], $modelname, 'change', $before_name);
                    // 不为id时，才能修改。修改不能改变索引的值和主键的属性
                    //$sql = "ALTER TABLE `$tablename` change `$before_name` `$fieldname` $type NULL $default";
                    //$this->{$this->modelClass}->query($sql);
                }
                Cache::delete('extschema_' . $modelname);
            }
        }
        // 将xml内容的条件转换为数组
        if (!empty($this->data['I18nfield']['conditions'])) {
//         	print_r($this->data['I18nfield']['conditions']);exit;
            $xmlarray = xml_to_array($this->data['I18nfield']['conditions']);
            $this->data = array_merge($this->data, $xmlarray);
        }
    }

    /**
     * 删除记录到回收站，并更新模块字段结构的缓存
     * @param unknown_type $id
     */
    function admin_trash($id) {
        $data = $this->{$this->modelClass}->read(null, $id);
        parent::admin_trash($id);
        $modelClass = $data['I18nfield']['model'];
        Cache::delete('extschema_' . $modelClass);
    }

    /**
     * 从回收站恢复记录，并更新模块字段结构的缓存
     * @param $id
     */
    function admin_restore($id) {
        $data = $this->{$this->modelClass}->read(null, $id);
        parent::admin_restore($id);
        $modelClass = $data['I18nfield']['model'];
        Cache::delete('extschema_' . $modelClass);
    }

    /**
     * 删除记录，并从数据库删除字段
     * @param int $id
     */
    function admin_delete($ids) {
        $ids = explode(',',$ids);
        $dbconfig = new DATABASE_CONFIG();
        
        foreach($ids as $id){
            if($id){
                $data = $this->I18nfield->read(null, $id);
                if(!empty($data)){
	                $modelClass = $data['I18nfield']['model'];
	                $this->loadModel($modelClass);
	                $useDbConfig = $this->{$modelClass}->useDbConfig;
	                $tablename = $dbconfig->{$useDbConfig}['prefix'] . Inflector::tableize($modelClass);
	                $field_name = $data['I18nfield']['name'];
	
	                // 加载对应的模块，取得其prefix。 操作对应的表删除字段
	                $sql = "alter table `$tablename` drop `$field_name`";
	                $this->{$modelClass}->query($sql);
	                parent::admin_delete($id);
	                Cache::delete('extschema_' . $modelClass); 
                }
            }
        }
        $successinfo = array('success' => __('Delete success'));
        $this->set('successinfo', $successinfo);
        $this->set('_serialize', 'successinfo');
    }

    function admin_list() {
        $modelClass = $this->request->query['model'];
        $modelClass = Inflector::classify($modelClass);
        //echo Inflector::tableize($modelClass);
		
        
        if (empty($modelClass)) {
            throw new NotFoundException('Named parameter "model" needed!');
        }
        parent::admin_list();
        $requeststr = 'model=' . $this->modelClass;
        $language = Configure::read('Config.language');
//    	$requeststr.='&conditions['.$this->modelClass.'.model]='.$modelClass.'&conditions['.$this->modelClass.'.local]='.$language;
        $requeststr.='&conditions[' . $this->modelClass . '.model]=' . $modelClass;
        $this->set('requeststr', $requeststr);
        $this->set('target_model', $modelClass);
        
//         $this->loadModel($modelClass);
//         if($this->{$modelClass} instanceof AppModel){ // Aro,Aco等不是AppModel子类
// 	    	$modelinfo = $this->{$modelClass}->getModelInfo();
// 	    	$this->set('modelCname', $modelinfo['cname']);
//         }
    }

    function admin_sortfield() {
        $modelClass = $this->request->query['model'];
        if (empty($modelClass)) {
            throw new NotFoundException('Named parameter "model" needed!');
        }
        $fields = $this->{$this->modelClass}->find('all', array(
                    'conditions' => array('model' => $modelClass),
                    'order' => array('sort desc', 'id asc'),
                ));
        $this->set('fields', $fields);
        $this->set('modelClass', $modelClass);
    }

    /*
      update cake_i18nfields set translate='SEO页面描述' where name='seodescription';
      update cake_i18nfields set translate='SEO页面关键字' where name='seokeywords';
      update cake_i18nfields set translate='SEO页面标题' where name='seotitle';
      update cake_i18nfields set translate='标题' where name='title';
      update cake_i18nfields set translate='创建时间',formtype='datetime' where name='created';
      update cake_i18nfields set translate='修改时间',formtype='datetime' where name='updated';
      update cake_i18nfields set translate='数据状态' where name='status';
      update `cake_i18nfields` set translate='是否发布',formtype='select',selectvalues='0=>否\n1=>是' WHERE name='published';
	  update `cake_i18nfields` set translate='是否删除',formtype='select',selectvalues='0=>否\n1=>是' WHERE name='deleted';

      update cake_i18nfields set allowadd='0',allowedit='0' where name='left' or name='right';

      update cake_i18nfields set translate='采集源地址' where name='remoteurl';
      update cake_i18nfields set translate='阅读次数' where name='views_count';
      update cake_i18nfields set translate='总评论数' where name='comment_count';

      update cake_i18nfields set translate='内容' where name='content';
      update cake_i18nfields set translate='发布状态' where name='status';
      update cake_i18nfields set translate='标题图片' where name='coverimg';
      update cake_i18nfields set translate='编号' where name='id';
      update cake_i18nfields set translate='语言类型' where name='locale';
      update cake_i18nfields set translate='所属分类' where name='cate_id';
      
      update cake_i18nfields set translate='树左节点' where name='left' or name = 'lft';
      update cake_i18nfields set translate='树右节点' where name='right' or name = 'rght';
      

      UPDATE `cake_i18nfields` SET `selectmodel` = 'Misccate', `selectvaluefield` = 'id', `selecttxtfield` = 'title', `selectparentid` = 25, `selectautoload` = 1, `associatetype` = 'treenode', `formtype` = 'select' WHERE `name` = 'status' and id!=356;

      update cake_i18nfields set allowadd='0' where name in ('id','deleted','updated','left','right');
      update cake_i18nfields set allowedit='0' where name in ('updated','left','right');
     */

    // 根据表的字段，自动生成字段记录
    function admin_generate($modelClass='', $locale='zh_cn') {
        set_time_limit(0);
        if ($modelClass) {
            $this->_generateModel($modelClass, $locale);
        } else {
            $tables = $this->{$this->modelClass}->query("show tables");
            foreach ($tables as $table) {
                $tablename = array_pop($table['TABLE_NAMES']);
                $modelname = str_replace($this->{$this->modelClass}->tablePrefix, '', $tablename);
                if ($modelname == 'i18n')
                    continue;
                $modelClass = Inflector::classify($modelname);
                $this->_generateModel($modelClass, $locale);
            }
        }
        $this->__message(__('Done', true), array('action' => 'index'), 100);
    }

    function _generateModel($modelClass, $locale) {

        $this->loadModel($modelClass);
        $this->{$modelClass}->schema(); // 生成model的_schema
        foreach ($this->{$modelClass}->_schema as $key => $value) {
//		  		echo $key.'===';
//		  		print_r($value);
            unset($this->data[$this->modelClass]);
            $this->{$this->modelClass}->create();
            $this->data[$this->modelClass]['name'] = $key;
            $this->data[$this->modelClass]['translate'] = $key;
            $this->data[$this->modelClass]['type'] = $value['type'];
            $this->data[$this->modelClass]['length'] = $value['length'];
            $this->data[$this->modelClass]['default'] = $value['default'];
            $this->data[$this->modelClass]['allownull'] = 1;
            $this->data[$this->modelClass]['model'] = $modelClass;
            $this->data[$this->modelClass]['locale'] = $local;

            $exists = $this->{$this->modelClass}->find('first', array('conditions' => array(
                            'model' => $modelClass, 'name' => $key
                            )));
            //print_r($exists[$this->modelClass]);
            if (empty($exists[$this->modelClass])) {
//		  			echo $key.'----';
                $this->{$this->modelClass}->save($this->data);
            }
		  		print_r($this->data);
        }
    }

    function _alterTableStruct($data, $modelname, $sql_type = 'add', $before_name='') {
        $fieldname = $data['name'];
        $tablename = $this->{$this->modelClass}->tablePrefix . Inflector::tableize($modelname);

        $length = $data['length'];
        $type = $default = '';
        switch ($data['type']) {
            case 'integer':
                if (!$length)
                    $length = 11;
                $default = ' default 0 ';
                if ($length < 3) {
                    $type = 'TINYINT(' . $length . ')';       // 1 byte -128 ~ 127   255
                    break;
                } elseif ($length < 5) {
                    $type = 'SMALLINT(' . $length . ')';      // 2 bytes -32768 ~ 32767 约6万多（65535）
                    break;
                } elseif ($length < 8) {
                    $type = 'MEDIUMINT(' . $length . ')';     // 3 bytes -8388608 ~ 8388607 约1.6千万（16777215）
                    break;
                } elseif ($length <= 11) {
                    $type = 'INT(' . $length . ')';     	  // 4 bytes -2147483648 ~ 2147483647 约43亿（4294967295）
                    break;
                } else {
                    $type = 'BIGINT(' . $length . ')';  	  // 8 bytes -9223372036854775808  ~ 9223372036854775807 约1.8亿亿亿（18446744073709551615）
                    break;
                }
            case 'string':
                if (!$length)
                    $length = 240;
                $type = 'varchar(' . $length . ')';
                break;
            case 'varchar':
                if (!$length)
                    $length = 240;
                $type = 'varchar(' . $length . ')';
                break;
            case 'char':
                if (!$length)
                    $length = 60;
                $type = 'char(' . $length . ')';
                break;
            case 'content':
                $type = 'text';
                break;
            case 'datetime':
                $type = 'DATETIME';
                break;
            case 'float':
                if (!$length)
                    $length = '10,2';
                $default = ' default 0 ';
                $type = 'float(' . $length . ')';
                break;
        }
        if ($sql_type == 'change') {
            $sql = "ALTER TABLE `$tablename` change `$before_name` `$fieldname` $type NULL $default";
        } else {
            $sql = "ALTER TABLE `$tablename` ADD `$fieldname` $type NULL $default";
        }
        $this->{$this->modelClass}->query($sql);
        Cache::delete('extschema_' . $modelname);
    }

}