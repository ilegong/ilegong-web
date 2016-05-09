<?php

class InstallController extends InstallAppController {
	var $name = 'Install';
    var $uses = null;
    var $components = array('Session','Auth');
    var $helpers = array('Html', 'Session', 'Form', 'Js',);
    function beforeFilter() {
//     	$this->Auth->allowedActions = array('*');
    	$this->Auth->allowedActions = array('index','check','database','data','finish','adduser');
    	
        parent::beforeFilter();
//     	print_r($this->request);
        $this->layout = 'install';
        if(file_exists(DATA_PATH.'install.lock')){
        	echo 'has installed.if you want intall again. delete "'.(DATA_PATH.'install.lock').'" at the first.';
        	exit;
        }
        load_lang('install');
    }
    function beforeRender(){
    	$this->set('pageTitle',$this->pageTitle);
    }
    function index() {
        $this->pageTitle = __('Installation: Welcome');
    }
    
    function check(){
    	$this->pageTitle = __('Environment Check');
    	if(defined('IN_SAE')){ // sae 无需配置数据库
    		$this->redirect(array('action' => 'data'));
    	}
    	$dir = array();
    	$dir['web_cache'] = is_writeable(WWW_ROOT.'cache');
    	$dir['upload'] = is_writeable(WWW_ROOT.'files');
    	$dir['data'] = is_writeable(TMP);
    	$dir['app_config'] = is_writeable(APP_PATH.'Config');
    	$dir['man_config'] = is_writeable(ROOT.'/manage/Config');
    	
    	$this->set('dirinfo',$dir);
    	$allchecked = min($dir);
    	$this->set('allchecked',$allchecked);
    }
	/**
	 * Step 1: database
	 *
	 * @return void
	 */
    function database() {
    	if(defined('IN_SAE')){ // sae 无需配置数据库
    		$this->redirect(array('action' => 'data'));
    	}
        $this->pageTitle = __('Step 1: Config Database');
        if (!empty($this->data)) {
            // test database connection
            //if (@mysql_connect($this->data['Install']['host'], $this->data['Install']['login'], $this->data['Install']['password']) &&
			//@mysql_select_db($this->data['Install']['database'])) {
			if (@mysqli_connect($this->data['Install']['host'], $this->data['Install']['login'], $this->data['Install']['password'] ,$this->data['Install']['database'])){
                // open database.php file
                $content = file_get_contents(APP.'Config'.DS.'database.php.install');
                // write database.php file
                $content = str_replace('{default_host}', $this->data['Install']['host'], $content);
                $content = str_replace('{default_login}', $this->data['Install']['login'], $content);
                $content = str_replace('{default_password}', $this->data['Install']['password'], $content);
                $content = str_replace('{default_database}', $this->data['Install']['database'], $content);
                $content = str_replace('{default_prefix}', $this->data['Install']['prefix'], $content);
                App::uses('File', 'Utility');
                $file = new File(APP.'Config'.DS.'database.php', true);
                if(!$file->write($content) ) {
                    $this->Session->setFlash(__('Could not write "%s" file.', 'app/Config/database.php'));
                }
                
                if(file_exists(ROOT.DS .'manage'.DS.'Config'.DS.'database.php.install')){
                	$file = new File(ROOT.DS .'manage'.DS.'Config'.DS.'database.php.install');
                	$content = $file->read();
                	
                	// write database.php file
                	$content = str_replace('{default_host}', $this->data['Install']['host'], $content);
                	$content = str_replace('{default_login}', $this->data['Install']['login'], $content);
                	$content = str_replace('{default_password}', $this->data['Install']['password'], $content);
                	$content = str_replace('{default_database}', $this->data['Install']['database'], $content);
                	$content = str_replace('{default_prefix}', $this->data['Install']['prefix'], $content);
                	$file = new File(ROOT.DS .'manage'.DS.'Config'.DS.'database.php', true);
                	if(!$file->write($content) ) {
                		$this->Session->setFlash(__('Could not write "%s" file.', 'manage/Config/database.php'));
                	}
                }
                
                $this->redirect(array('action' => 'data'));                
            } else {
                $this->Session->setFlash(lang('could_not_connect_to_database', 'install'));
            }
        }
    }
	/**
	 * Step 2: create table and insert required data
	 *
	 * @return void
	 */
    function data() {
        $this->pageTitle = __('Step 2: Run SQL', true);
        //App::import('Core', 'Model');
        //$Model = new Model;
        App::import('Model', 'ConnectionManager');
        $db = ConnectionManager::getDataSource('master');
        
        $dbconfig = new DATABASE_CONFIG();
        $prefix = $dbconfig->master['prefix'];
        
        $sql = "show tables like '$prefix%'";
        $has_old_table = $db->query($sql);
        
        if(!empty($has_old_table) && empty($this->data['Install']['force'])){
        	$this->set('has_old_table',true);
        }
        elseif (!empty($this->data['Install']['models'])) {
        	$this->set('has_old_table',false);
        	$system_models = array(
        			'i18nfields','modelextends','i18nfield_i18ns', 
        			// 最开始库中没有任何表，i18nfields，modelextends。必需在最前面
        			// i18nfields.sql中的“REPLACE INTO cake_modelextends...”语句移到modelextends.sql中
        			'aros','acos','aros_acos',
        			'categories','users','user_cates','shortmessages','favorites',
        			'','','',
        			'defined_languages','languages',
        			'regions','styles','stylevars','settings','uploadfiles',
        			'menus','menu_i18ns','',
        			'misccates','modelcates','keywords','keyword_relateds',
        			'staffs','roles','staff_roles',
        			'','','','','','',);
        	
        	if(!$db->isConnected()) {
        		$this->Session->setFlash(__('Could not connect to database.', true));
        	} else {
        		$install_models = $system_models;
        		foreach($this->data['Install']['models'] as $model){
        			// 安装的一个模块可以包含多个模型，之间用逗号隔开。
        			if(strpos($model,',')!==false){
        				$install_models = array_merge($install_models,explode(',',$model));
        			}
        			else{
        				$install_models[] = $model;
        			}
        		}
	        	if(in_array('products',$install_models)){
	        		$install_models[] = 'brands'; //含产品时，安装品牌
	        	}
	        	if(!empty($install_models)){ //评论模块
	        		$install_models[] = 'comments';
	        	}
	        	$install_models = array_delete_value($install_models);
	        	foreach($install_models as $model){	        		
	        		if(file_exists(ROOT.DS.'data'.DS.'sql'.DS.$model.'.sql')){
	        			$statements = file_get_contents(ROOT.DS.'data'.DS.'sql'.DS.$model.'.sql');
	        			if(!$this->data['Install']['force']){ //强制安装，删除数据库
	        				$statements = preg_replace("/DROP TABLE .+?;\s+/is",'',$statements);
	        			}
	        			$statements = str_replace(array('`cake_','`miao_',),'`'.$prefix,$statements);
	        			$this->__executeSQLScript($db, $statements);
	        		}
	        	}
	        	// 创建完所有表格后，再导入tree型结构的数据。
	        	foreach($install_models as $model){
	        		if(file_exists(ROOT.DS.'data'.DS.'sql'.DS.$model.'.php')){
	        			include ROOT.DS.'data'.DS.'sql'.DS.$model.'.php';
	        		}
	        	}
	        	/**
	        	 * TODO 将'Security.salt'，'Security.cipherSeed'，define('CLOUD_CRON_SECRET'写入到数据库
	        	 * 
				 * 加密的密钥，'Security.salt'，'Security.cipherSeed'，不能修改。
				 * 修改后用户密码等由于密钥更换无法匹配，造成所有已有用户的密码失效. 
				 * 若发生此种情况，可通知用户密码过期，要求用户通过注册邮箱发送找回密码邮件，重设密码
				 * $security_salt = random_str(42);
                 * $security_cipher = random_str(24,'num');
                 * $sae_cron_secret = random_str(24);
	        	 */
	        	$sql = "REPLACE INTO `{$prefix}settings` 
(`id`, `key`, `value`, `title`,`input_type`, `editable`, `weight`,`scope`, `locale`) VALUES
(NULL, 'Security.salt', '".random_str(42)."', '密码前缀','text', 0, 0, 'global', 'zh_cn'),
(NULL, 'Security.cipherSeed', '".random_str(24,'num')."', '加密种子','text', 0, 0,'global', 'zh_cn'),
(NULL, 'Security.cloud_cron_secret', '".random_str(24)."', '云定时任务密钥','text', 0, 0,'global', 'zh_cn');";
	        	$this->__executeSQLScript($db, $sql);
	        	
	        	$setting_obj = loadModelObject('Setting');
	        	$setting_obj->writeConfiguration(true);
	        	
        		$this->redirect(array('action' => 'adduser'));
        	}
        }
    }
    
    function adduser(){
    	if(!empty($this->data)){    		
    		if(!empty($this->data['Staff']['name']) && !empty($this->data['Staff']['password'])){
	    			$this->loadModel('Setting');
	    			$this->Setting->writeConfiguration(true);
	    			$this->loadModel('Staff');
	    			
	    			$vali = $this->Staff->validator();
	    			$vali->add('name',array(
	    				'unique'=>array(
    						'rule' => 'isUnique',
    						'message' => 'The username has already been taken.',
	    				),
	    				'minLength'=>array(
	    						'rule' => array('minLength',6),
	    						'message' => 'This field length must big than 6',
	    				)));
	    			$vali->add('password', array(
					    'required' => array(
					        'rule' => 'notEmpty',
					        'required' => 'create'
					    ),
					    'size' => array(
					        'rule' => array('between', 6, 20),
					        'message' => 'Password should be at least 6 and less than 20 chars long'
					    )
					));
	    			
	    			$this->Staff->set($this->data); // 校验值是否正确。
	    			if($this->Staff->validates()){
		    			$this->data['Staff']['password'] = $this->Auth->password($this->data['Staff']['password']);
		    			$this->data['Staff']['role_id']=1;
		    			$this->data['Staff']['status']=1;
		    			$this->data['StaffRole']['role_id']=1;
		    			if($this->Staff->save($this->data,false)){		    				
		    				$this->data['StaffRole']['staff_id'] = $this->Staff->getLastInsertID();
		    				
		    				$this->redirect(array('action' => 'finish'));
		    			}
	    			}
    		}
    	}
    }
	/**
	 * Step 4: finish
	 *
	 * Remind the user to delete 'install' plugin.
	 *
	 * @return void
	 */
    function finish() {
        $this->pageTitle = __('Installation completed successfully');
        
        file_put_contents(DATA_PATH.'install.lock', 'installed');

        if (isset($this->query['delete'])) {
            App::import('Core', 'Folder');
            $this->folder = new Folder;
            if ($this->folder->delete(COMMON_PATH.'Plugin'.DS.'Install')) {
                $this->Session->setFlash(__('Installataion files deleted successfully.'));
                $this->redirect('/');
            } else {
                $this->Session->setFlash(__('Could not delete installation files.'));
            }
        }
    }
	/**
	 * Execute SQL querys
	 *
	 * @link http://cakebaker.42dh.com/2007/04/16/writing-an-installer-for-your-cakephp-application/
	 * @param object $db Database
	 * @param string $fileName sql strings.
	 * @return void
	 */
    private function __executeSQLScript(&$db, $statements) {
        $statements = explode(";\n", $statements);
        foreach ($statements as $statement) {
            if (trim($statement) != '') {
                $db->query($statement);
            }
        }
    }
}
?>