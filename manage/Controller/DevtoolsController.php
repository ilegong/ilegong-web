<?php
App::uses('CakeSchema','Model');
App::uses('Folder','Utility');
App::uses('File','Utility');
class DevtoolsController extends AppController {

    public $name = 'Devtools';
    
    public $components = array('DbBackup');
    
    public $uses = false;//This will allow you to use a controller without a need for a corresponding Model file.

    public function beforeFilter() {
    	parent::beforeFilter();
    	$this->autoRender = false;
    	@set_time_limit(0);
    }
    /**
     * SAE环境初始化。
     */
    function admin_saeinit(){
        
    }
    
    function admin_getpassword(){
    	$this->autoRender = true;
    	if(!empty($this->data)){
    		Configure::read($this->data['Staff']['salt']);
    		echo $password =  Security::hash($this->data['Staff']['password'], null, true);
    		
    		$this->Session->setFlash($password);
    	}
    }
 
    /**
     * 比对数据库差异，生成升级语句
     */
    function admin_dbsync() {
        // 使用两个Model，一个连接新库，一个连接旧库。比对差异
        $this->autoRender = false;
        $this->loadModel('I18nfield');  // 连新库
        $this->loadModel('Modelextend'); // 连旧库
        $this->Modelextend->setDataSource('olddb');


        $useDbConfig = $this->I18nfield->useDbConfig;
        $dbconfig = new DATABASE_CONFIG();
        if ($dbconfig->{$useDbConfig}['prefix']) {
            $tables = $this->I18nfield->query("SHOW TABLES like '" . $dbconfig->{$useDbConfig}['prefix'] . "%'");
            $old_tables = $this->Modelextend->query("SHOW TABLES like '" . $dbconfig->{$useDbConfig}['prefix'] . "%'");
        } else {
            $tables = $this->I18nfield->query("SHOW TABLES");
            $old_tables = $this->Modelextend->query("SHOW TABLES");
        }
        $old_tables_name = array();
        foreach ($old_tables as $key => $val) {
            $old_tables_name[] = array_pop($val['TABLE_NAMES']);
        }

        App::uses('DbStructUpdater', 'Lib');
        App::uses('DbDataSync', 'Lib');

        $updater = new DbStructUpdater();


        $struct_sqls = array();
        $data_sqls = array();
        try {
            foreach ($tables as $key => $val) {
                //$tables[$key]['TABLE_NAMES'] = str_replace($dbconfig->{$useDbConfig}['prefix'],'',$val['TABLE_NAMES']);
                $table_name = array_pop($val['TABLE_NAMES']);
                
                /** 比较表结构开始 **/
                if (in_array($table_name, $old_tables_name)) {
                    $create_str_old = $this->Modelextend->query("SHOW CREATE TABLE " . $table_name);
                } else {
                    $create_str_old = '';
                }
                $create_str_new = $this->I18nfield->query("SHOW CREATE TABLE " . $table_name);
                if ($create_str_old) {
                    $res = $updater->getUpdates($create_str_old[0][0]['Create Table'], $create_str_new[0][0]['Create Table']);
                    if (!empty($res)) {
                        $struct_sqls = array_merge($struct_sqls, $res);
                    }
                } else {
                    $struct_sqls[] = $create_str_new[0][0]['Create Table'];
                }
                /** 比较表结构结束 **/

                
                /** 比较表数据开始 **/
                if (in_array($table_name, array(
                	$dbconfig->default['prefix'] . 'categories',
                    $dbconfig->default['prefix'] . 'crawls',
                    $dbconfig->default['prefix'] . 'i18nfields',
                    $dbconfig->default['prefix'] . 'menus',
                    $dbconfig->default['prefix'] . 'misccates',
                    $dbconfig->default['prefix'] . 'modelcates',
                    $dbconfig->default['prefix'] . 'modelextends',))) {
                    	// 需要覆盖全部内容的数据表
                    	
                    $data_sync = new DbDataSync();
                    $data_sync->masterSet($dbconfig->default['host'], $dbconfig->default['login'], $dbconfig->default['password'], $dbconfig->default['database'], $table_name, "id");
                    $datasql = $data_sync->slaveSyncronization();
                    if (!empty($datasql)) {
                        $data_sqls = array_merge($data_sqls, $datasql);
                    }                    	
                }
                elseif (!in_array($table_name, array(
                
                    $dbconfig->default['prefix'] . 'taobaokes',
                    $dbconfig->default['prefix'] . 'articles',
                    $dbconfig->default['prefix'] . 'crawl_title_lists',
                    $dbconfig->default['prefix'] . 'estate_articles',
                    $dbconfig->default['prefix'] . 'estate_invite_tenders',
                    $dbconfig->default['prefix'] . 'taobao_trade_rates',
                    $dbconfig->default['prefix'] . 'taobao_promotions',
                    $dbconfig->default['prefix'] . 'category_articles',
                    $dbconfig->default['prefix'] . 'stats_days',
                    $dbconfig->default['prefix'] . 'sessions',
                    $dbconfig->default['prefix'] . 'template_histories'))) {
                    	// 跳过不需要覆盖内容的数据表

                    $data_sync = new DbDataSync();
                    $data_sync->masterSet($dbconfig->default['host'], $dbconfig->default['login'], $dbconfig->default['password'], $dbconfig->default['database'], $table_name, "id");
                    if (in_array($table_name, $old_tables_name)) {
                        $data_sync->slaveSet($dbconfig->olddb['host'], $dbconfig->olddb['login'], $dbconfig->olddb['password'], $dbconfig->olddb['database'], $table_name, "id");
                    } else {
                        $data_sync->slaveSet($dbconfig->olddb['host'], $dbconfig->olddb['login'], $dbconfig->olddb['password'], $dbconfig->olddb['database'], '', "id");
                    }
                    $datasql = $data_sync->slaveSyncronization();
                    if (!empty($datasql)) {
                        $data_sqls = array_merge($data_sqls, $datasql);
                    }
                }
                /** 比较表数据结束 **/
            }
            echo implode(";\r\n", $struct_sqls) . ";\r\n";
            echo implode(";\r\n", $data_sqls) . ";\r\n";
            //print_r($struct_sqls);
            //print_r($data_sqls);
        } catch (CakeException $e) {
            echo $e->getMessage();
        } catch (RuntimeException $e) {
            echo $e->getMessage();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
	/**
	 * 导出模块的sql数据，用于模块的安装.
	 * 包括表结构，表数据，I18nfield字段信息，modelextend模块信息，
	 * @param string $model
	 */
    function admin_exportModelSql($model=''){
    	if(empty($model)){
    		$models = array(
    		'Aco','Aro','ArosAco','DefinedLanguage','Language', //'I18n',
    		'Region','Style','Stylevar','Setting','Session',
    		'Menu','MenuI18n','Misccate','Modelcate',
    		'I18nfield','I18nfieldI18n','Modelextend',
    		'Template','TemplateHistorie',
    		'Keyword','KeywordRelated',
    		'Tag','TagRelated',
    		
    		'Advertises','Link',
    		/* 调查及问卷 */
    		'Appraiselog','Appraiseoption','Appraiseresult','Appraise',
    		'Flow','Flowstep',
    				
    		//'Lottery',
    		/* 前台模块 */
    		'Article','Category','Download','Photo','Question','Idiom',
    		'Uploadfile',
    		'EstateArticle','EstateInviteTender',
    		/* 评论与观点 */
    		'Comment','Viewpoint','Pointsupport',
    		
    		/* 商城 */
    		'Product','Cart','Brand','Order','OrderConsignee','OrderInvoice','',
    		
    		/* 抓取 */	
    		'Crawl','CrawlRelease','CrawlReleaseSite','CrawlTitleList',
    		/* 用户模块 */
    		'User','Favorite','Note','UserCate','AutoSign',
    		'Schedule','Shortmessage',
    		'Oauthbind',
    		/* 后台模块 */    		
    		'Contact','Customer','Organization',
    		'Staff','Role','StaffRole','Tasking','Task','Taskexecute','Tenure',
    		/* 访问统计 */
    		'StatsDay','StatsWeek','StatsMonth','',
    		/* 淘宝客 */
    		'Taobaoke','TaobaoCate','TaobaoPromotion','TaobaoRate','TaobaoShop','TaobaoTradeRate',
    		);
    		foreach($models as $model){
    			if(!empty($model)){
    				$url = '/admin/devtools/exportModelSql/'.$model;
    				$result =  $this->requestAction($url);
    				echo $url.'<BR/>'.$result.'<hr/>';
    			}
    		}
    		exit;
    	}
    	$this->autoRender = false;
    	$sql = '';
    	
    	$model = Inflector::classify($model);
    	$this->loadModel($model);
    	
    	$useDbConfig = $this->{$model}->useDbConfig;
    	$dbconfig = new DATABASE_CONFIG();
    	
    	$table_name = $dbconfig->{$useDbConfig}['prefix'] . Inflector::tableize($model);
    	$create_sql = $this->{$model}->query("SHOW CREATE TABLE " . $table_name);
    	
    	$sql= 'DROP TABLE IF EXISTS `'.$table_name.'`;'."\n";
    	$creatsql = $create_sql[0][0]['Create Table'].";\n";
    	$creatsql = str_ireplace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$creatsql);
    	$creatsql = preg_replace('/AUTO_INCREMENT=\d+/','',$creatsql);
    	$creatsql = str_ireplace('ENGINE=InnoDB','ENGINE=MYISAM',$creatsql);
    	
    	
    	$sql .= $creatsql;
    	// 导出i18nfield字段信息
    	$table_name = $dbconfig->{$useDbConfig}['prefix'] . Inflector::tableize('I18nfield');
    	$this->loadModel('I18nfield');
    	$this->I18nfield->recursive = -1;
    	$DataSource = $this->I18nfield->getDataSource();
    	$datas = $this->I18nfield->find('all',array('conditions' => array('model'=>$model,)));
    	if(!empty($datas)){
    		$fields = array_keys($datas[0]['I18nfield']);
    		$count = count($fields);
    		$fieldInsert = array();
    		for ($i = 0; $i < $count; $i++) {
    			$fieldInsert[] = $DataSource->name($fields[$i]);
    		}
    		$fieldsInsertComma = implode(', ', $fieldInsert);
    		$f_values = array();
	    	foreach($datas as $row){
	    		$valueInsert = array();
	    		$row['I18nfield']['id'] = null;
	    		for ($i = 0; $i < $count; $i++) {
	    			$valueInsert[] = $DataSource->value($row['I18nfield'][$fields[$i]], $this->I18nfield->getColumnType($fields[$i]), false);
	    		}
	    		$f_values[] = '('.implode(', ', $valueInsert).')';
	    	}
	    	$sql .= 'REPLACE INTO `'.$table_name.'` ('.$fieldsInsertComma.') VALUES '.implode(",\n", $f_values).";\n";
    	}
    	// 导出Modelextend模块信息
    	if($model!='I18nfield'){
	    	$table_name = $dbconfig->{$useDbConfig}['prefix'] . Inflector::tableize('Modelextend');
	    	$this->loadModel('Modelextend');
	    	$this->Modelextend->recursive = -1;
	    	$DataSource = $this->Modelextend->getDataSource();
	    	if($model=='Modelextend'){
	    		$datas = $this->Modelextend->find('all',array('conditions' => array('name'=>array('Modelextend','I18nfield'),)));
	    	}
	    	else{
	    		$datas = $this->Modelextend->find('all',array('conditions' => array('name'=>$model,)));
	    	}
	    	if(!empty($datas)){
	    		$fields = array_keys($datas[0]['Modelextend']);
	    		$count = count($fields);
	    		$fieldInsert = array();
	    		for ($i = 0; $i < $count; $i++) {
	    			$fieldInsert[] = $DataSource->name($fields[$i]);
	    		}
	    		$fieldsInsertComma = implode(', ', $fieldInsert);
	    		$f_values = array();
	    		foreach($datas as $row){
	    			$valueInsert = array();
	    			$row['Modelextend']['id'] = null;
	    			for ($i = 0; $i < $count; $i++) {
	    				$valueInsert[] = $DataSource->value($row['Modelextend'][$fields[$i]], $this->Modelextend->getColumnType($fields[$i]), false);
	    			}
	    			$f_values[] = '('.implode(', ', $valueInsert).')';
	    		}
	    		$sql .= 'REPLACE INTO `'.$table_name.'` ('.$fieldsInsertComma.') VALUES '.implode(",\n", $f_values).";\n";
	    	}
    	}
    	
    	/**
    	 * 需要导出数据的模型表
    	 * @var array
    	 */
    	$contentmodel = array(
    		'DefinedLanguage','Language','Keyword',
    		'Style','Stylevar','Setting',
    		'Misccate','Modelcate',
    		'Menu','MenuI18n',
    		'Appraise','Appraiseoption',
    		'Role','Aro','Aco','ArosAco',
    		'Crawl','CrawlRelease','CrawlReleaseSite',
    		'Region',
    		'Category','Article','Download','Product','Photo',
    		'Advertise','Link',
    	);
    	// 'CrawlTitleList',
    	
    	/**
    	 * TODO.如果模块内容为树结构时，如何使parent_id保存对应的关系。新插入的顶层数据没有parent_id
    	 */
    	if(in_array($model,$contentmodel)){
    		$sql .= "\n\n\n";
    		$table_name = $dbconfig->{$useDbConfig}['prefix'] . Inflector::tableize($model);
    		$this->loadModel($model);
    		$this->{$model}->recursive = -1;
    		$DataSource = $this->{$model}->getDataSource();
    		if($this->{$model}->actsAs['Tree']){
    			// 树形结构的导出php文件，执行来按层级插入数据
    			$filecontent = "<?php\n";
    			$datas = $this->{$model}->find('threaded');
    			$filecontent.='$datas = '.var_export($datas,true).";\n\n";
    			$filecontent.="\n	saveTreeItems(\$datas,'$model');\n";
/*    			
$filecontent.=<<<EOF
    saveTreeItems(\$datas,'$model');
EOF;*/
//“EOF“结束的标识符必须在行首
				$treephpfile =  DATA_PATH.'sql'.DS.Inflector::tableize($model).'.php';
				file_put_contents($treephpfile, $filecontent);
    		}
    		else{
	    		$datas = $this->{$model}->find('all');
	    		if(!empty($datas)){
	    			$fields = array_keys($datas[0][$model]);
	    			$count = count($fields);
	    			$fieldInsert = array();
	    			for ($i = 0; $i < $count; $i++) {
	    				$fieldInsert[] = $DataSource->name($fields[$i]);
	    			}
	    			$fieldsInsertComma = implode(', ', $fieldInsert);
	    			$f_values = array();
	    			foreach($datas as $row){
	    				$valueInsert = array();
	    				// 不需要保持id不变模块，id不与其他模块关联。
	    				if(in_array($model,array('I18nfield','Modelextend'))){
	    					$row[$model]['id'] = null;
	    				}
	    				elseif($model=='Setting'){
	    					if(strpos($row[$model]['key'],'Security.')!==false){ //跳过Setting中的Security.项
	    						continue;
	    					}
	    					$row[$model]['id'] = null;
	    				}
	    				
	    				for ($i = 0; $i < $count; $i++) {
	    					$valueInsert[] = $DataSource->value($row[$model][$fields[$i]], $this->{$model}->getColumnType($fields[$i]), false);
	    				}
	    				$f_values[] = '('.implode(', ', $valueInsert).')';
	    			}
	    			$sql .= 'REPLACE INTO `'.$table_name.'` ('.$fieldsInsertComma.') VALUES '.implode(",\n", $f_values).";\n";
	    		}
    		}
    	}
    	
    	$sqlfile =  DATA_PATH.'sql'.DS.Inflector::tableize($model).'.sql';
    	file_put_contents($sqlfile, $sql);
    	return $sqlfile;
    }
    
    /**
     * 以下几个方法用于自动生成aco表的记录
     */
    function admin_build_acl() {
    	
        if (!Configure::read('debug')) {
            return $this->_stop(); // 仅在调试模式才能运行
        }
        $log = array();

        $aco = & $this->Acl->Aco;
        $root = $aco->node('controllers');
        if (!$root) {
            $aco->create(array('parent_id' => null, 'model' => null, 'alias' => 'controllers'));
            $root = $aco->save();
            $root['Aco']['id'] = $aco->id;
            $log[] = 'Created Aco node for controllers';
        } else {
            $root = $root[0];
        }

        $Controllers = array('Systems' => array('admin_index'), 'Menus' => array('admin_menu'), 'Flowsteps' => array('admin_dataadd', 'admin_dataedit', 'admin_datalist', '', ''));
        // look at each controller in app/controllers
        foreach ($Controllers as $ctrlName => $methods) {

            if (!$methods) {
                $modelClass = Inflector::tableize($ctrlName);
                $methods = get_class_methods($modelClass . 'Model');
            }

            // find / make controller node
            $controllerNode = $aco->node('controllers/' . $ctrlName);
            if (!$controllerNode) {
                $aco->create(array('parent_id' => $root['Aco']['id'], 'model' => null, 'alias' => $ctrlName));
                $controllerNode = $aco->save();
                $controllerNode['Aco']['id'] = $aco->id;
                $log[] = 'Created Aco node for ' . $ctrlName;
            } else {
                $controllerNode = $controllerNode[0];
            }

            //clean the methods. to remove those in Controller and private actions.
            foreach ($methods as $k => $method) {
                if (strpos($method, '_', 0) === 0) {
                    unset($methods[$k]);
                    continue;
                }
                if (empty($method)) {
                    continue;
                }
                $methodNode = $aco->node('controllers/' . $ctrlName . '/' . $method);
                if (!$methodNode) {
                    $aco->create(array('parent_id' => $controllerNode['Aco']['id'], 'model' => null, 'alias' => $method));
                    $methodNode = $aco->save();
                    $log[] = 'Created Aco node for ' . $method;
                }
            }
        }
        if (count($log) > 0) {
            debug($log);
        }
    }

}

?>