<?php
App::uses('CakeSchema','Model');
App::uses('Folder','Utility');
App::uses('File','Utility');
class ToolsController extends AppController {

    public $name = 'Tools';
    
    public $components = array('DbBackup');
    
    public $uses = array();//This will allow you to use a controller without a need for a corresponding Model file.

    function admin_index() {
        $this->pageTitle = __('Tools');
    }
    /**
     * SAE环境初始化。
     */
    function admin_saeinit(){
        
    }
    
    function admin_dbimport($file='',$volume=1){
    	if(empty($file)){
	    	$backdir = ROOT . '/data/backups/';
	    	$exportvolume = $exportsize = array();
	    	$dir = dir($backdir);
	    	while($entry = $dir->read()) {
	    			if(preg_match("/(.+?)_(\d+)\.sql$/i", $entry,$matches)) {
	    				$key = $matches[1];
	    				if(!isset($exportvolume[$key])){
	    					$exportvolume[$key] = 0;
	    					$exportsize[$key] = 0;
	    				}
	    				$filesize = filesize($backdir.$entry);
	    				$exportlog[$key][] = array(
	    						'volume' => $matches[2],
	    						'filename' => $entry,
	    						'dateline' => filemtime($backdir.$entry),
	    						'size' => $filesize
	    				);
	    				$exportsize[$key] += $filesize;
	    				$exportvolume[$key] +=1;
	    			}
	    	}
	    	$dir->close();
	    	$this->set('exportlog',$exportlog);
	    	$this->set('exportsize',$exportsize);
	    	$this->set('exportvolume',$exportvolume);
    	}
    	else{
    		$dataSourceName = 'default';
    		$dbo = ConnectionManager::getDataSource($dataSourceName);
    		
    		$filename = ROOT . '/data/backups/'.$file.'_'.$volume.'.sql';
    		$content = file_get_contents($filename);
    		$sqls = explode(";\n",$content);
    		
    		foreach($sqls as $sql){
    			$dbo->execute($sql);
    		}
    	}
    }
    /**
     * 数据库导出，分卷.导出第一卷后，页面跳转自动进入下一卷。
     * 使用页面跳转的方式，防止php脚本执行超时
     */
    function admin_dbexport($filename='',$table_start='',$limit_start=0,$volume=1){
    	
    	if(empty($_POST)){
    		
    	}
    	else{
	    	@set_time_limit(0);
	    	$dataSourceName = 'default';
	    	
	    	$path = ROOT . '/data/backups/';
	    	
	    	$Folder = new Folder($path, true);
	    	if(empty($filename)){
	    		$filename = date('Ymd_His').'_'.random_str(6);
	    	}
	    	$fileSufix = $filename.'_'.$volume . '.sql';
	    	
	    	$file = $path . $fileSufix;
	    	if (!is_writable($path)) {
	    		return $this->__message('The path "' . $path . '" isn\'t writable!', 'javascript:void();', 9000);
	    	}
	    	$sql = '';
	    	$limit = 500;
	    	$file_limit_size = 2*1024*1024; //2M
	    	
	    	$File = new File($file);
	    	$config = ConnectionManager::getDataSource($dataSourceName)->config;
	    	$tables = ConnectionManager::getDataSource($dataSourceName)->listSources();
	    	foreach ($tables as $table) {
	    		$table = str_replace($config['prefix'], '', $table);
	    		if(!empty($table_start)){ //$table_start，后续的分卷,不是从第一个表格开始导出
	    			if($table!=$table_start){
	    				continue; // 与开始导出的表格不一样时，跳过这个表格
	    			}
	    			else{
	    				$table_start=''; //仅传参的第一次循环生效，将其置空，阻止第二次生效
	    			}
	    		}
	    		
	    		$ModelName = Inflector::classify($table);
	    		if($ModelName=='I18n'){
	    			$ModelName = 'I18nModel';
	    		}
	    		$Model = ClassRegistry::init($ModelName);
	    		
	    		if(!$Model instanceof Model){
	    			continue;//'I18n'
	    		}
	    		$DataSource = $Model->getDataSource();
	    	
	    		$CakeSchema = new CakeSchema();
	    		$CakeSchema->tables = array($table => $Model->schema());
	    		
	    		$tablename = $DataSource->fullTableName($table);
	    		
	    		/* 创建表格语句 */
	    		if($limit_start==0){
	    			$sql .= "\nDROP TABLE IF EXISTS {$tablename};\n";
	    			$sql .= $DataSource->createSchema($CakeSchema, $table) . "\n";
	    		}
	//     		$File->write("\n/* Backuping table data {$table} */\n");
				
	    		$total = $Model->find('count', array('recursive' => -1));
	    		unset($valueInsert, $fieldInsert);
	    		/* 插入数据 */
	    		while(strlen($sql)<$file_limit_size && $limit_start<$total){
	    			$page = intval($limit_start/$limit)+1;
		    		$rows = $Model->find('all', array('recursive' => -1,'limit'=>$limit,'page'=>$page));
		    		$limit_start +=$limit;
		    		$size = count($rows);
		    		if ($size > 0) {
		    			$fields = array_keys($rows[0][$ModelName]);
		    			//$values = array_values($rows);
		    			$count = count($fields);
		    	
		    			for ($i = 0; $i < $count; $i++) {
		    				$fieldInsert[] = $DataSource->name($fields[$i]);
		    			}
		    			$fieldsInsertComma = implode(', ', $fieldInsert);
		    			
		    			$f_values = array();
		    			foreach ($rows as $k => $row) {
		    				unset($valueInsert);
		    				for ($i = 0; $i < $count; $i++) {
		    					$valueInsert[] = $DataSource->value($row[$ModelName][$fields[$i]], $Model->getColumnType($fields[$i]), false);
		    				}
		    				$f_values[] = '('.implode(', ', $valueInsert).')';
		    				if(count($f_values)>=200){
			    				$sql.= 'INSERT INTO '.$tablename.' ('.$fieldsInsertComma.') VALUES '.implode(",\n", $f_values).";\n";
			    				unset($f_values);$f_values = array();
		    				}
		    			}
		    			if(!empty($f_values)){
		    				$sql .= 'INSERT INTO '.$tablename.' ('.$fieldsInsertComma.') VALUES '.implode(",\n", $f_values).";\n";
		    				unset($f_values);
		    			}
		    		}
		    		if($size < $limit){
		    			$limit_start = 0; // 当查出的行数小于limit数时，重置limit_start为0。当前表格数据处理完成
		    			break;
		    		}
	    		} // end while. loop export data.
	    		if($limit_start>=$total){
	    			$limit_start = 0;
	    		}
	    		
	    		if(strlen($sql) >= $file_limit_size){
	    			$File->write($sql);
	    			$File->close();
	    			unset($sql);
	    			$this->__message('正在备份分卷'.$volume, array('action'=>'dbexport',$filename,$table,$limit_start,$volume+1), 5);
	    		}
	    	} // end foreach
	    	if(!empty($sql)){
	    		$File->write($sql);
	    		$File->close();
	    		unset($sql);
	    	}
	    	
	    	return $this->__message('备份完成', array('action'=>'dbimport'), 90);
    	}
    }
    
/*-----------------------------------------------------------------------------*/    
    
    
    /**
     * set visible to 1,
     
    function admin_activestyle($id){
    	$parent_info = $this->Misccate->findById(160);
    	//     	$parent_info = $this->Misccate->findBySlug('styles'); // styles 对应的数据id为160
    	//     	$parent_id = $parent_info['Misccate']['id'];
    	$left = $parent_info['Misccate']['left'];
    	$right = $parent_info['Misccate']['right'];
    	$conditions = array('Misccate.left >' => $left,'visible'=>1,'Misccate.right <' => $right);
    	$styles = $this->Misccate->find('all', array('conditions' => $conditions, 'order' => 'left asc'));
    }*/
    
        
    /**
     * 设置所使用的模版套系
     * Site.theme
     * Admin.theme
     */
    function admin_themes(){
    	$dir = SITE_VIEWS.'./Themed/';
    	$templatedir = dir($dir);
    	$sitetpls = array();
    	while($entry = $templatedir->read()) {
    		$tpldir = realpath($dir.'/'.$entry);
    		if(!in_array($entry, array('.', '..')) && is_dir($tpldir)) {
    			$sitetpls[] = array(
    					'name' => $entry,
    					'time' => date('Y-m-d H:i:s',@filemtime($dir.'/'.$entry))
    			);
    		}
    	}
    	
    	$dir = VIEWS.'./Themed/';
    	$templatedir = dir($dir);
    	$tpls = array();
    	while($entry = $templatedir->read()) {
    		$tpldir = realpath($dir.'/'.$entry);
    		if(!in_array($entry, array('.', '..')) && is_dir($tpldir)) {    			
    			$tpls[] = array(
    					'name' => $entry,
    					'time' => date('Y-m-d H:i:s',@filemtime($dir.'/'.$entry))
    			);
    		}
    	}
    	
    	$site_theme = Configure::read('Site.theme');
    	
    	$admin_theme = Configure::read('Admin.theme');
    	
    	$this->set(array('sitetpls'=>$sitetpls,'tpls'=>$tpls,'site_theme'=>$site_theme,'admin_theme'=>$admin_theme));
    }
    
/*-----------------------------------------------------------------------------*/    
    /**
     * PHP探针 
     **/
    function admin_tz(){
    	include APP_PATH.'tz.php';
    	exit;
    }
    
    /**
     * SAE KVDB管理页
     **/
    function admin_saekv(){
    	include APP_PATH.'saekv.php';
    	exit;
    }

    /**
     * 清空缓存,需要同时删除app及manage项目生成的缓存
     */
    function admin_clearcache() {
    	 	
    	clearCacheAll();
    	/*更新语言包缓存*/
    	$this->requestAction('/admin/tools/updateLanCache',array('data'=>array('uplang'=>'zh-cn')));
    	
        if ($this->RequestHandler->accepts('json') || $this->RequestHandler->isAjax() || isset($_GET['inajax'])) {
        	$this->autoRender = false;
        	echo json_encode(array('success' => __('Done')));
        	exit;
        }
        else{
        	$this->__message(__('Done'), '', 99999);
        }
    }

    function admin_clear_template_cache() {
        clearTemplateCache();
        /*更新语言包缓存*/
        $this->requestAction('/admin/tools/updateLanCache',array('data'=>array('uplang'=>'zh-cn')));
        if ($this->RequestHandler->accepts('json') || $this->RequestHandler->isAjax() || isset($_GET['inajax'])) {
            $this->autoRender = false;
            echo json_encode(array('success' => __('Done')));
            exit;
        }
        else{
            $this->__message(__('Done'), '', 99999);
        }
    }

    /**
     * 生成链接的slug别名
     */
    function admin_genSlug() {
    	App::uses('Charset', 'Lib');
    	App::uses('Pinyin', 'Lib');
    	$PY = new Pinyin();
    	$slug = $PY->stringToPinyin(Charset::utf8_gbk($_REQUEST['word']));
        $slug = Inflector::slug($slug);
        $this->autoRender = false;
        echo json_encode(array('slug' => $slug));
    }

    /**
     * 更新语言包的缓存
     */
    function admin_updateLanCache() {
//    		$locals = App::path('locales'); print_r($locals);exit;
		$uplang = $this->data['uplang'] ? 'uplang':$_REQUEST['uplang'];
        if (empty($uplang)) {
            $this->loadModel('Language');
            $lans = $this->Language->find('all');
            $selectlans = array();
            foreach ($lans as $lang) {
                $selectlans[$lang['Language']['alias']] = $lang['Language']['native'];
            }
            $this->set('selectlans', $selectlans);
        } else {
            Configure::write('Config.language', $uplang);
            // 获取对应的地域名称
            $I18n = I18n::getInstance();
            $I18n->l10n->get(Configure::read('Config.language'));
            $locale_alias = $I18n->l10n->locale;

            // 获取第一个locale文件夹，
            if (!class_exists('I18n')) {
                App::uses('I18n', 'I18n');
            }
            $locals = App::path('locales');
            $local_path = array_shift($locals);

            App::uses('File', 'Utility');

            $this->loadModel('I18nfield');
            $fields = $this->I18nfield->find('all');
            $file_contnets = '';
            foreach ($fields as $key => $value) {
                $file_contnets .= 'msgid "Field_' . $value['I18nfield']['model'] . '_' . $value['I18nfield']['name'] . "\"\r\n";
                $file_contnets .= 'msgstr "' . str_replace('"', '\"', $value['I18nfield']['translate']) . "\"\r\n";
            }
            $filename = $local_path . $locale_alias . DS . 'LC_MESSAGES' . DS . 'i18nfield.po';
            if(defined('IN_SAE')){
            	file_put_contents($filename, $file_contnets);
            }
            else{
	            $file = new File($filename, true);
	            $file->write($file_contnets);
            }

            $this->loadModel('Modelextend');
            $fields = $this->Modelextend->find('all');
            $file_contnets = '';
            foreach ($fields as $key => $value) {
                $file_contnets .= 'msgid "Model_' . $value['Modelextend']['name'] . "\"\r\n";
                $file_contnets .= 'msgstr "' . str_replace('"', '\"', $value['Modelextend']['cname']) . "\"\r\n";
            }
            
            $filename = $local_path . $locale_alias . DS . 'LC_MESSAGES' . DS . 'modelextend.po';
            if(defined('IN_SAE')){
            	file_put_contents($filename, $file_contnets);
            }
            else{
	            $file = new File($filename, true);
	            $file->write($file_contnets);
            }
            $successinfo = array('success' => __('Edit success'));
            $this->autoRender = false;
            if($this->params['return']){
            	return $successinfo;
            }
            else{
            	echo json_encode($successinfo);
            }
        }
    }

    function admin_startseo() {
        $models = explode(',', Configure::read('Admin.contentmodels'));
        $content_models = array();
        foreach ($models as $v) {
            $content_models[$v] = __('model_' . $v);
        }
        $this->set('content_models', $content_models);
    }

    /**
     * 生成SEO数据
     * @param $modelname
     * @param $page
     * @param $pagesize
     */
    function admin_autoseo($modelname='', $page=1, $pagesize=10, $autonext = 0) {

        if ($this->data['Tool']['modelname']) {
            $modelname = $this->data['Tool']['modelname'];
        }
        //print_r($this->data);exit;
        $this->loadmodel($modelname);

        $this->{$modelname}->recursive = 1;
        $options = array(
            'limit' => $pagesize,
            'page' => $page,
            'order' => 'id desc'
        );
        $controlname = Inflector::pluralize($modelname);
        $datas = $this->{$modelname}->find('all', $options);

        $this->loadmodel('KeywordRelated');

        foreach ($datas as $data) {
            if ($data[$modelname]['content']) { //分词，并保存入库
                $keywords = $this->WordSegment->segment($data[$modelname]['content']);
                if (empty($keywords))
                    continue;
                $seokeywords = array();
                $mainkeywords = array();
                $i = 0;
                foreach ($keywords as $k => $v) {
                    if ($i < 5) {
                        $mainkeywords[$k] = $v;
                    }
                    if ($i < 20) {
                        $seokeywords[$k] = $v;
                    } else {
                        break;
                    }
                    $i++;
                }
                $seodata = array();
                $seodata['seokeywords'] = $sv_seokeywords = implode(',', $seokeywords); // 20个词作为seokeywords
                $seodata['keywords'] = $sv_keywords = implode(',', $mainkeywords); // 5个词作为keywords
                $seodata['id'] = $data[$modelname]['id'];
                $this->{$modelname}->save($seodata);
                // 修改表中关键字
//				$this->{$modelname}->updateAll(
//					array('seokeywords'=> $sv_seokeywords,'keywords'=>$sv_keywords),
//					array('id'=> $data[$modelname]['id'])
//				);
                // 更新key_related中相关的记录
                $this->KeywordRelated->deleteAll(array('relatedid' => $data[$modelname]['id'], 'relatedmodel' => $modelname), true, true);
                foreach ($mainkeywords as $key => $value) {
                    $this->KeywordRelated->create();
                    $keyword_related['relatedid'] = $data[$modelname]['id'];
                    $keyword_related['relatedmodel'] = $modelname;
                    $keyword_related['keyword_id'] = $key;
                    $this->KeywordRelated->save($keyword_related);
                }
            }
        }
        if (empty($datas) || count($datas) < $pagesize) {
            $this->__message(__("seo do over", true), array('action' => 'startseo'), 99999);
        }
        $nextpage = $page + 1;
        $this->__message(__("page %s Done", $page), array('action' => 'autoseo', $modelname, $nextpage, $pagesize), 3);
    }

}

?>