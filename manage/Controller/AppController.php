<?php

class AppController extends Controller {

    var $theme = 'default';//charisma,default,desktop
    var $viewClass = 'Miao';
    var $helpers = array(
        'Html', 'Paginator', 'Section', 'Session', 'Form', 'Js', 'Layout', 'MForm',
        'Hook' => array(), 'Swfupload', 'Combinator', 'Number'
        // 'Cache',// @todo. 将cache的操作集成到DzstyleView中，参考cachehelp但数据仍通过动态执行，缓存中不包含数据。相当于整合模版缓存的二级缓存
    );
    var $__viewFileName = '';
    var $components = array(
        'Acl',
    	'Auth'=>array(
        	'authenticate' => array(
        		'Form'=>array(
        				'userModel' => 'Staff','recursive' => 1,
        				'fields'=>array(
        						'username'=>'name',
        						'password'=>'password'
        				)
        		),
        	),
//         	'authorize' => 'Controller',
        ),'AclFilter','RequestHandler','Paginator',
    		'TaskQueue','WordSegment',
        'Email', 'Session', 'Hook'=>array(),
    );
//     var $uses = array('Setting', 'StatsDay'); //,'Category'
    var $currentUser = null;
    var $insert_id; // 添加数据时。插入数据库的id
    var $pageTitle;
    var $current_data_id;
    
    public $cacheAction = 368000;

    // 自定义构造函数
    function __construct($request = null, $response = null) {
        if ($request instanceof CakeRequest) {
            $this->name = $request->params['controller'];
        }
        $request->webroot = '/';  // webroot目录直接放在根目录
        parent::__construct($request, $response);
        $this->_international();
        $this->modelClass = Inflector::camelize($this->modelClass);
        
        $usesclass = $this->plugin ? $this->plugin.'.'.$this->modelClass : $this->modelClass;
//     	if (!in_array($usesclass, $this->uses)) {
//     		/**
//     		 * $this->uses不包含本controller对应的modelClass,需要手动包含进来.
//     		 * 当controller对应的类文件不存在时（默认使用appController），指定使用的数据模块
//     		 * 
//     		 * 若如包含，当不存在的controller使用appController时，会出现错误。
//     		 * 如“/manage/admin/product_split33s/add”表单的内容错误的显示为settings表对应的表单
//     		 */    		
//     		array_unshift($this->uses,$usesclass); 
//         }
    }
    
    /**
     * //当含语言的参数时，设置base追加locale的内容，当传入locale为默认内容时，跳转去除locale参数。
     */
    private function _international(){
    	if (!empty($this->request->params['locale']) && $this->request->params['locale']!=DEFAULT_LANGUAGE) {
    		//当含语言的参数时，设置base追加locale的内容，    		
    		$this->request->base = $this->request->base.'/'.$this->request->params['locale'];
    		define('APPEND_LOCALE_BASE', true);
    		if($this->request->params['locale']=='zh-tw' && DEFAULT_LANGUAGE=='zh-cn'){
    			$this->convertCode = 'g2b';//默认版本为简体，看繁体版本时，需要将简体转繁体显示，
    		}
    		elseif($this->request->params['locale']=='zh-cn' && DEFAULT_LANGUAGE=='zh-tw'){
    			$this->convertCode = 'b2g';//默认版本为繁体，看简体版本时，需要将繁体转简体显示，
    		}
    		else{//非中文时，才修改语言版本。
    			Configure::write('Config.language', $this->request->params['locale']);
    		}
    	}
    	elseif (!empty($this->request->params['locale'])) {
    		// 当传入locale为默认内容时，跳转去除locale参数。
    		// [REQUEST_URI] => /saecms/trunk/zh-cn/?helo
    		$url = str_replace($this->request->params['locale'],'',$this->request->url);
    		$url = str_replace('//', '/', $url);
    		$this->redirect($url);
    	}
    }

    public function beforeFilter() {
    	/**
    	 * 修正$this->modelClass与$this->name对应不是一个模块的问题。
    	 */
    	$this->modelClass = Inflector::camelize(Inflector::singularize($this->name));
        $this->autoRender = true; // 设置autoRender为true；不设置时/admin/modelcates/loadSplitForm/product/33中，$this->requestAction调用动作会不调用render呈现模板。
        if(preg_match('/MSIE/i',$_SERVER['HTTP_USER_AGENT'])){
        	$this->layout = 'ie6';
        	$this->theme = 'default';
        }
        else{
        	$this->layout = 'admin';
        }
        $this->theme = Configure::read('Admin.theme');
        if($_GET['theme']){
        	$this->theme=$_GET['theme'];
        	$this->Session->write('theme',$this->theme);
        }
        elseif($this->Session->read('theme')){
        	$this->theme=$this->Session->read('theme');
        }
        
        AuthComponent::$sessionKey = 'Auth.Staff'; // 静态变量，使用类名来改变值
        $this->Auth->loginAction = array(
			'controller' => 'staffs',
			'action' => 'login',
			'plugin' => null
		);
        //add allow action
        if (!in_array('*', $this->Auth->allowedActions)) {
            $this->Auth->allowedActions[] = 'admin_login';
            $this->Auth->allowedActions[] = 'admin_cron_gen_data';
            $this->Auth->allowedActions[] = 'admin_process_gen_data';
            $this->Auth->allowedActions[] = 'admin_cron_gen_day_data';
            $this->Auth->allowedActions[] = 'admin_cron_gen_proxy_data';
            $this->Auth->allowedActions[] = 'admin_gen_sharer_statics_data_task';
            $this->Auth->allowedActions[] = 'admin_save_sharer_data';
            $this->Auth->allowedActions[] = 'admin_gen_stop_share_balance_logs';
        }
        
        if (defined('IN_CLI')) {
        	$methods = get_class_methods($this);
            $this->Auth->allowedActions = $methods; // 命令行执行，允许访问所有的url
        }
        else {
        	// cookie中的用户信息加密后，解密过程消耗性能，有session时，忽略cookie。cookie仅在staffs中处理
        	// 仅判断session登录信息
        	
            $this->currentUser = $this->Auth->user();
            if ($this->Auth->user('id') && !empty($this->currentUser)) {
                $this->AclFilter->authAdmin();
            }
            else{
                if(!in_array($this->action, $this->Auth->allowedActions)){
                    $this->Session->setFlash(__('Your need to login.'));
                    $this->Auth->redirect($this->request->url);// 设置Auth.redirect，登录后跳转回来。
                    $this->redirect('/admin/staffs/login');
                }
//                if ($this->action != 'admin_login') {// 若没有登录且不在登录页面，则跳转到登录页面进行登录
//                    $this->Session->setFlash(__('Your need to login.'));
//                    $this->Auth->redirect($this->request->url);// 设置Auth.redirect，登录后跳转回来。
//                    $this->redirect('/admin/staffs/login');
//                }
            }
        }

//         print_r($this->Auth->allowedActions);
//         print_r($this->action);
        if (!in_array($this->action, $this->Auth->allowedActions) && !in_array('*', $this->Auth->allowedActions)) {
            $this->__message(__('No permission! Please contact administrator.'), '', 99999);
        }

        //设置 Configure::write('System.ActiveLanguage',...)
        $this->__loadSystemLanguages();
//		print_r($languages);

		$this->loadModel('Setting');
        $this->Setting->writeConfiguration();  //加载settings表中设置的参数
        $site_info = Configure::read('Site');
        $this->set('site', $site_info);
        $this->set('seokeywords', $site_info['seokeywords']);
        $this->set('seodescription', $site_info['seodescription']);

        $user_question_class = array();
        
        if ($this->RequestHandler->isAjax() || isset($_GET['inajax']) || $this->request->query['inajax']) {
            $this->layout = 'ajax';
            Configure::write('debug', 0);
        }
    }


    function beforeRender() {
    	
//     	$this->modelClass = Inflector::singularize($this->name);
        if (!empty($this->{$this->modelClass}->actsAs['MultiTranslate'])) {
            $this->request->params['translate_fields'] = $this->{$this->modelClass}->actsAs['MultiTranslate'];
        }
        
        $this->set('CurrentUser', $this->currentUser);
        $this->set('pageTitle', $this->pageTitle);
        $this->set('pageID', md5(String::uuid()));  // 唯一串做页面id，在后端模板中调用
        $this->set('current_model', $this->modelClass);
        $this->set('current_controller', $this->request->params['controller']);
        $this->set('current_action', $this->action);
        $this->set('current_pass', $this->request->params['pass']);
        $this->set('current_named', $this->request->params['named']);
//         header('Content-Type:text/html; charset=UTF-8');
    }

    function admin_index() {
    }

    // 后台增加
    function admin_add() {
        $this->pageTitle = __("Add " . $this->modelClass, true);
        $modelClass = $this->modelClass;
        $explodefield = $this->__getexplodefield($modelClass);
        if (!empty($_POST)) {
            $this->data[$modelClass]['locale'] = getLocal(Configure::read('Config.language'));
            // 处理时间格式的数据
            foreach ($this->{$modelClass}->getExtSchema() as $k => $v) {
                if ($v['formtype'] == 'datetime') {
                    if (isset($this->data[$modelClass][$k]['ymd'])) {
                        $this->data[$modelClass][$k] = $this->data[$modelClass][$k]['ymd'] . ' ' . $this->data[$modelClass][$k]['his'];
                    }
                } else {
                    if(is_array($this->data[$modelClass]['serialize_info'])){
		                $this->data[$modelClass]['serialize_info'] = serialize($this->data[$modelClass]['serialize_info']);
		            }
                    elseif ($k != $explodefield && !empty($this->data[$modelClass][$k]) && is_array($this->data[$modelClass][$k])) {
                        $this->data[$modelClass][$k] = implode(',', $this->data[$modelClass][$k]);
                    }
                }
            }
            // 保存数据
            $this->autoRender = false;
            if ($explodefield) { // 批量增加
                $explode_array = $this->data[$modelClass][$explodefield];
                if (!is_array($explode_array)) {
                    $explode_array = explode("\n", $explode_array);
                }

                foreach ($explode_array as $val) {
                	$val = trim($val);
                    if (empty($val))
                        continue;

                    $this->{$modelClass}->create();
                    $this->data[$modelClass]['deleted'] = 0;
                    $this->data[$modelClass][$explodefield] = $val;
                    if ($this->{$this->modelClass}->save($this->data)) {
                        $successinfo = array(
                        		'success' => __('Add success'), 
                        		'actions' => array(
                        				'nexturl' => Router::url(array('action'=>'list'))
                        		));
                        $this->Session->setFlash(__('The Data has been saved'));
                    } else {
                        $this->Session->setFlash(__('The Data could not be saved. Please, try again.'));
                        echo json_encode($this->{$this->modelClass}->validationErrors);
                        return;
                    }
                }
                echo json_encode($successinfo);
                return;
            } else {
                //$this->{$modelClass}->create();
//	            print_r($this->data);
                $this->data[$modelClass]['deleted'] = 0;
                //$this->{$modelClass}->invalidFields(array('fieldList'=> array_keys($this->data[$modelClass])));
                /*
                 * //处理hasMany,hasOne,
                $related_validationErrors = array();
                if(is_array($this->{$this->modelClass}->_associations)){
	                foreach ($this->{$this->modelClass}->_associations as $assoc) {
	                    if (!empty($this->{$this->modelClass}->{$assoc})) {
	                        foreach ($this->{$this->modelClass}->{$assoc} as $k => $val) {
	                            //	        			print_r($val);
	                            if (!empty($val['className'])) {
	                                $related_model = $val['className'];
	                            } else {
	                                $related_model = $k;
	                            }
	                            if (!empty($this->data[$related_model])) {
	                                $related_valifields = array_keys($this->data[$related_model]);
	
	                                $this->{$modelClass}->{$related_model}->set($this->data);
	
	                                $errors = $this->{$this->modelClass}->{$related_model}->invalidFields(array('fieldList' => $related_valifields));
	//			        				print_r($related_valifields);print_r($errors);
	                                if (!empty($errors)) {
	                                    foreach ($errors as $error_k => $error_v) {
	                                        $related_validationErrors[$related_model . '.' . $error_k] = $error_v;
	                                    }
	                                }
	                            } else {
	                                continue;
	                            }
	                        }
	                    }
	                }
                }*/
                // 选择关联的keyword标签，使用到了hasAndBelongsToMany，在save方法中，自动保存。
                $backup = $this->{$modelClass}->hasAndBelongsToMany;
                $this->{$modelClass}->set($this->data);
                $errors = $this->{$modelClass}->invalidFields();
                // invalidField校验后，$this->hasAndBelongsToMany字段消失，导致save时，无法保存hasAndBelongsToMany相关模块的数据，暂时使用一个变量记录值，校验后将值赋值回来。
                $this->{$modelClass}->hasAndBelongsToMany = $backup;
                if (!empty($errors)) { // || !empty($related_validationErrors)
//                     $errors = array_merge($errors, $related_validationErrors);
                    // 将索引转换成与表单的input的name相同的形式，供jquery tools validator使用
                	$error_return = array();
                    foreach($errors as $key => $info){
                    	$error_return['data['.$this->modelClass.']['.$key.']'] = $info;
                    }
                    echo json_encode($error_return);
                    exit;
                }
                if ($this->{$this->modelClass}->save($this->data)) { //, array('validate' => false)
                    $successinfo = array(
                    		'success' => __('Add success'), 
                    		'actions' => array(
                        				'nexturl' => Router::url(array('action'=>'list'))
                        	));
                    $insertid = $this->data[$this->modelClass]['id'] = $this->{$modelClass}->getLastInsertID();
                    
                    $successinfo['data'] = $this->data[$this->modelClass];
                    // 相关模块数据
					if(is_array($this->{$this->modelClass}->_associations)){
	                    foreach ($this->{$this->modelClass}->_associations as $assoc) {
	                        if (!empty($this->{$this->modelClass}->{$assoc})) {
	                            foreach ($this->{$this->modelClass}->{$assoc} as $k => $val) {
	                                //	        			print_r($val);
	                                if($k=='hasAndBelongsToMany'){
	                                	continue; // 在save中会自动保存，并建立关联。
	                                }
	                                if (!empty($val['className'])) {
	                                    $related_model = $val['className'];
	                                } else {
	                                    $related_model = $k;
	                                }
	                                if(in_array($related_model,array('Uploadfile','Modelcate'))){
	                                	continue;
	                                }
	                                $foreignKey = $val['foreignKey'];
	                                if (!empty($this->data[$related_model])) {
	                                    $this->data[$related_model][$foreignKey] = $insertid;
	                                } else {
	                                    continue;
	                                }
	                                if ($this->{$this->modelClass}->{$related_model}->save($this->data, array('validate' => false))) {
	                                    $successinfo['success'] .= __('Add data success');
	                                } else {
	                                    foreach ($this->{$this->modelClass}->{$related_model}->validationErrors as $key => $value) {
	                                        $successinfo['error'] .= __('Add data error');
	                                        $successinfo['error'] .= "$key:$value";
	                                    }
	                                }
	                            }
	                        }
	                    }
					}
                    //保存上传的附件；形如 data[Uploadfile][39][id] , data[Uploadfile][39][name]
                    if (isset($this->data['Uploadfile']) && is_array($this->data['Uploadfile'])) {
                        $this->loadModel('Uploadfile');
	                    foreach ($this->data['Uploadfile'] as $file) {
	                        $this->Uploadfile->create();
	                        $fileinfo = array();
	                        $fileinfo['id'] = $file['id'];
	                        $fileinfo['name'] = $file['name'];
	                        $fileinfo['data_id'] = $insertid;
	                        // 只修改  data_id，name
	                        $this->Uploadfile->save($fileinfo, true, array('data_id','name'));
	                    }
                     }

                    $this->Session->setFlash(__('The Data has been saved', true));
                    echo json_encode($successinfo);
                } else {
                    $this->Session->setFlash(__('The Data could not be saved. Please, try again.', true));
                    $errorinfo = array('save_error' => __('Add Error', true), 'actions' => array('OK' => 'closedialog'));
                    echo json_encode($errorinfo);
                }
            }
            $this->insert_id = $insertid;
        } else {
        	// 无提交值，生成表单。加载选项值,及修改时加载上传文件的列表
            $this->__loadFormValues($this->modelClass);
            $this->__loadAssocValues();
        }
        //http://www.saecms.com/admin/misccates/add/parent_id:25 
        // "/controller/action/param1:value1/param2:value2"
        // 让形如这种形式传参的数据传入data中，一般用于表单默认值的选择。  $this->data[$this->modelClass][param1] = value1;
        if (is_array($this->request->params['named']) && !empty($this->request->params['named'])) {
            foreach ($this->request->params['named'] as $k => $v) {
                $this->data[$this->modelClass][$k] = $v;
            }
        }
        if (is_array($this->request->query) && !empty($this->request->query)) {
        	foreach ($this->request->query as $k => $v) {
        		$this->data[$this->modelClass][$k] = $v;
        	}
        }
        $this->data[$this->modelClass]['creator'] = $this->Auth->user('id');
        
    }
	
    /**
     * 显示数据
     * @param $id
     */
    function admin_view($id = null) {
    	$this->{$modelClass}->recursive = 1;
        $modelClass = $this->modelClass;
        $searchoptions = array(
            'conditions' => array($modelClass . '.id' => $id),
            'fields' => array('*'),
        );
        $joinmodel_fields = array();
        $alias = 0;
        
        $ext_schema = $this->{$modelClass}->getExtSchema();
        $_schema_keys = array_keys($ext_schema);        

        if (!in_array($modelClass, array('I18nfield', 'Modelextend'))) {
            foreach ($ext_schema as $k => $fieldinfo) {
                if (in_array($k, $_schema_keys) && $fieldinfo['selectmodel'] && $fieldinfo['selectvaluefield'] && $fieldinfo['selecttxtfield'] && in_array($fieldinfo['formtype'], array('select', 'checkbox', 'radio'))) {
                    $alias++;
                    $join_model = $fieldinfo['selectmodel'];
                    $model_alias = $join_model . '_' . $alias;
                    $selectvaluefield = $fieldinfo['selectvaluefield'];
                    $selecttxtfield = $fieldinfo['selecttxtfield'];
                    $searchoptions['fields'][] = $model_alias . '.' . $selecttxtfield . ' as ' . $k . '_txt';
                    //$searchoptions['order'] = $join_model.'.'.$selecttxtfield;
                    $joinmodel_fields[$k] = $model_alias;

                    $joinconditions = array($model_alias . '.' . $selectvaluefield . ' = ' . $modelClass . '.' . $k);

                    if ($fieldinfo['associateflag'] && $fieldinfo['associateelement'] && $fieldinfo['associatefield']) {
                        //将级联操作的字段也作为表单连接的条件，否则会包含不符合条件的多余的记录
                        $joinconditions[] = $model_alias . '.' . $fieldinfo['associatefield'] . '=' . $modelClass . '.' . $fieldinfo['associateelement'];
                    }
                    $searchoptions['joins'][] = array(
                        'table' => Inflector::tableize($join_model),
                        'alias' => $model_alias,
                        'type' => 'left',
                        'conditions' => $joinconditions,
                    );
                }
            }
        }
        $datas = $this->{$modelClass}->find('first', $searchoptions);

        foreach ($joinmodel_fields as $joinfield => $joinmodel) {
            if ($datas[$joinmodel][$joinfield . '_txt']) {
                $datas[$modelClass][$joinfield] = $datas[$joinmodel][$joinfield . '_txt'];
            }
        }
        $this->set('item', $datas[$modelClass]);
        $this->set('_extschema', $ext_schema);
    }

    /**
     *  后台修改
     * @param $id 模块数据的id
     * @param $copy 是否已修改的数据为蓝本新建一条数据。 实现数据的复制操作。仍会弹出修改表单，但保存时不影响原数据，只创建新数据。
     */
    function admin_edit($id = null,$copy = NULL) {
    	
        $this->pageTitle = __("Edit " . $this->modelClass, true);        
        $modelClass = $this->modelClass;
        $ext_schema = $this->{$modelClass}->getExtSchema();
        
        $table_fields = array_keys($ext_schema);
        if (!empty($_POST['oper']) && $_POST['oper'] == 'edit') { // jqgrid 行内编辑的保存
            $data_id = $_POST['id'];
            foreach ($_POST as $key => $val) {
                if (in_array($key, $table_fields)) {
                    $this->data[$modelClass][$key] = $val;
                }
            }
        }
        if (!$id && empty($this->data)) {
            $this->Session->setFlash(__('Invalid Data'));
            $this->redirect(array('action' => 'index'));
        }
        if (!empty($this->data)) {
            // 处理时间格式的数据
            if (empty($id))
                $id = $this->data[$modelClass]['id'];
            if (empty($this->data[$modelClass]['id']))
                $this->data[$modelClass]['id'] = $id;
                
            if(empty($this->data[$modelClass]['id'])){
            	throw new NotFoundException('Error url');
            }

            foreach ($ext_schema as $k => $v) {
                if ($v['formtype'] == 'datetime') {
                    if (isset($this->data[$modelClass][$k]['ymd'])) {
                        $this->data[$modelClass][$k] = $this->data[$modelClass][$k]['ymd'] . ' ' . $this->data[$modelClass][$k]['his'];
                    }
                } else {
	                if(is_array($this->data[$modelClass]['serialize_info'])){
		                $this->data[$modelClass]['serialize_info'] = serialize($this->data[$modelClass]['serialize_info']);
		            }
                    elseif (!empty($this->data[$modelClass][$k]) && is_array($this->data[$modelClass][$k])) {
                        $this->data[$modelClass][$k] = ',' . implode(',', $this->data[$modelClass][$k]) . ','; // 前后加逗号“,”，方便like搜索
                    }
                }
            }
            if($copy){
                unset($id,$this->data[$modelClass]['id'],$this->{$modelClass}->id); // 当有copy选项时，取消id的值，保存一条新的记录
            	$this->{$modelClass}->create();
            }
                
            $this->autoRender = false;
            $this->{$modelClass}->recursive = -1;
            
            // 选择关联的keyword标签，使用到了hasAndBelongsToMany，在save方法中，自动保存。
        	$backup = $this->{$modelClass}->hasAndBelongsToMany;
            $this->{$modelClass}->set($this->data);
            $errors = $this->{$modelClass}->invalidFields();
            //@todo.bug. invalidField校验后，$this->hasAndBelongsToMany字段消失，导致save时，无法保存hasAndBelongsToMany相关模块的数据，暂时使用一个变量记录值，校验后将值赋值回来。
            $this->{$modelClass}->hasAndBelongsToMany = $backup;
            if (!empty($errors)) { // || !empty($related_validationErrors)
//                     $errors = array_merge($errors, $related_validationErrors);
                // 将索引转换成与表单的input的name相同的形式，供jquery tools validator使用
                $error_return = array();
                foreach($errors as $key => $info){
                   	$error_return['data['.$this->modelClass.']['.$key.']'] = $info;
                }
                echo json_encode($error_return);
                exit;
            }
            
            if ($this->{$modelClass}->save($this->data, array('validate' => false))) { //saveAll
                $successinfo = array(
                		'success' => __('Edit success'), 
                		'actions' => array(
                				'nexturl' => Router::url(array('action'=>'list','?'=>$this->request->query))
                		)
                );                
                $successinfo['data'] = $this->data[$this->modelClass];
                //保存上传的附件
                if (isset($this->data['Uploadfile']) && is_array($this->data['Uploadfile'])) {
                    $insertid = $id;
                    $this->loadModel('Uploadfile');                   
                    foreach ($this->data['Uploadfile'] as $fileinfo) {
                        $this->Uploadfile->create();                        
                        $fileinfo['data_id'] = $insertid;
                        // 只修改  data_id,name,'sortorder','version','comment'
                        $this->Uploadfile->save($fileinfo, true, array('data_id','name','sortorder','version','comment'));
                    }
                }

                $this->Session->setFlash(__('The Data has been saved', true));
                echo json_encode($successinfo);
            } else {
                $this->Session->setFlash(__('The Data could not be saved. Please, try again.', true));
                echo json_encode($this->{$modelClass}->validationErrors);
            }
            return;
        }
        $this->{$modelClass}->recursive = 1;
        if (empty($this->data)) {
            //$this->data = $this->{$modelClass}->read(null, $id);
            $this->data = $this->{$modelClass}->find('first', array(
            		'conditions' => array($modelClass. '.id'  => $id),
            		'callbacks' => 'before', //before,after,true. true means both before and after.
            		//MultiTranslate行为，进行afterFind调用会修改查询结果数据，edit编辑操作中，不调用
            ));
            foreach ($ext_schema as $k => $v) {
                if (in_array($v['formtype'], array('checkbox')) && isset($this->data[$modelClass][$k])) {
                    $this->data[$modelClass][$k] = explode(',', $this->data[$modelClass][$k]);
                }
            }
//             print_r($this->data);
            $this->autoRender = true; // 设置autoRender为true；不设置时requestAction不显示内容，如url:/admin/modelcates/loadSplitForm/product/33页面
            // 无提交值，生成表单。加载选项值,及修改时加载上传文件的列表
            $this->__loadFormValues($this->modelClass, $id);
            $this->__loadAssocValues();
            $this->set('id',$id);
            $this->set('current_data',json_encode($this->data));
            $this->set('copy', $copy);
        	if(!empty($this->data[$modelClass]['serialize_info'])){
                $this->data[$modelClass]['serialize_info'] = unserialize($this->data[$modelClass]['serialize_info']);
            }
        }
        $this->data[$this->modelClass]['lastupdator'] = $this->Auth->user('id');
        
        $this->__viewFileName = 'admin_add';
    }

    /**
     * 设置发布标记
     * @param $id
     */
    function admin_publish($ids = null) {
    	if(is_array($_POST['ids'])&& !empty($_POST['ids'])){
    		$ids = $_POST['ids'];
    	}
    	else{
	        if (!$ids) {
	            $this->redirect(array('action' => 'index'));
	        }
	        $ids = explode(',', $ids);
    	}

        foreach ($ids as $id) {
            if (!intval($id))
                continue;
            $data = array();
            $data[$this->modelClass]['id'] = $id;
            $data[$this->modelClass]['published'] = 1;

            if ($this->{$this->modelClass}->save($data[$this->modelClass])) {
//	            $this->Session->setFlash(__('The Data is published success.', true));
                $successinfo = array('success' => __('Publish success'));
            } else {
                $successinfo = array('error' => __('Publish error'));
            }
        }
        $this->set('successinfo', $successinfo);
        $this->set('_serialize', 'successinfo');
    }

    /**
     * 设置删除标记，置发布状态为未发布.
     * 若为树形结构，删除时，会连所有子类一起删除。
     * @param $id
     */
    function admin_trash($ids = null) {
    	if(is_array($_POST['ids'])&& !empty($_POST['ids'])){
    		$ids = $_POST['ids'];
    	}
    	else{
	        if (!$ids) {
	            $this->redirect(array('action' => 'index'));
	        }
	        $ids = explode(',', $ids);
    	}
        $istree = false;
        if($this->{$this->modelClass}->Behaviors->enabled('Tree')){
        	$istree = true;
        }
        $error_flag = false;
        foreach ($ids as $id) {
            if (!intval($id))
                continue;
            
            $data = array();
            $data['deleted'] = 1;
//             $data['published'] = 0;
            
            if($istree){
            	$childs = $this->{$this->modelClass}->children($id);
            	$id = array($id); 
            	foreach($childs as $item){
            		$id[] = $item[$this->modelClass]['id'];
            	}
            }

            $this->{$this->modelClass}->updateAll($data, array('id' => $id));
        }
        
        $successinfo = array('success' => __('Trash success'));
        
        $this->set('successinfo', $successinfo);
        $this->set('_serialize', 'successinfo');
        
        if ($error_flag) {
        	return false;
        }
        else{
        	return true;
        }
        
    }
    /**
     * 
     */
	function admin_batchEdit($ids){
		if (!$ids) {
            //$this->redirect(array('action' => 'index'));
        }
        $ids_array = explode(',', $ids);
		if(empty($ids_array)){
    		throw new NotFoundException('parameter "ids" needed!');
    	}
		
    	
        if (!empty($this->data)) {
//        	print_r($this->data);exit;
	        $this->{$this->modelClass}->updateAll($this->data[$this->modelClass], array('id' => $ids_array));
	        $successinfo = array('success' => __('Batch edit success'));
	        echo json_encode($successinfo);
	        exit;
        }
        else{
        	if(empty($this->request->params['named']['fields'])){
	    		throw new NotFoundException('Named parameter "fields" needed!');
	    	}
			$fields = $this->request->params['named']['fields'];
			$fields = explode(',', $fields);
			
	        $this->set('ids',$ids);
	        $this->set('editfields',$fields);
        }
	}

    /**
     * 恢复删除标记
     * @param $id
     */
    function admin_restore($ids = null) {
    	if(is_array($_POST['ids'])&& !empty($_POST['ids'])){
    		$ids = $_POST['ids'];
    	}
    	else{
	        if (!$ids) {
	            $this->redirect(array('action' => 'index'));
	        }
	        $ids = explode(',', $ids);
    	}

        foreach ($ids as $id) {
            if (!intval($id))
                continue;
            $data = array();
            $data[$this->modelClass]['id'] = $id;
            $data[$this->modelClass]['deleted'] = 0;

            if ($this->{$this->modelClass}->save($data[$this->modelClass])) {
//	            $this->Session->setFlash(__('The Data is restore success.', true));
                $successinfo = array('success' => __('Restore success'));
            } else {
                $successinfo = array('error' => __('Restore error'));
            }
        }
        $this->set('successinfo', $successinfo);
        $this->set('_serialize', 'successinfo');
    }

    /**
     * 删除数据
     * @param $id
     */
    function admin_delete($ids = null) {
    	@set_time_limit(0);
    	if(is_array($_POST['ids'])&& !empty($_POST['ids'])){
    		$ids = $_POST['ids'];
    	}
    	else{
    		if (!$ids) {
    			$this->redirect(array('action' => 'index'));
    		}
    		$ids = explode(',', $ids);
    	}
    	$istree = false;
    	if($this->{$this->modelClass}->Behaviors->enabled('Tree')){
    		$istree = true;
    		$this->{$this->modelClass}->Behaviors->disable('Tree'); // 设置了id值tree数据无法插入，先取消tree行为插入数据，再绑定tree行为修复数据。
    	}
        // 存在deleted字段时，deleted字段值为1的才能删除
        $fields = array_keys($this->{$this->modelClass}->schema());
        if(in_array('deleted',$fields)){
        	$delete_flag = $this->{$this->modelClass}->deleteAll(array('id' => $ids, 'deleted' => 1), true, true);
        }
        else{
        	$delete_flag = $this->{$this->modelClass}->deleteAll(array('id' => $ids), true, true);
        }
        if($istree){ // 若为树结构，删除前，卸载树结构，删除后修复树节点关系。
        	if(in_array('lft',$fields) && in_array('rght',$fields)){
        		$this->{$this->modelClass}->Behaviors->load('Tree', array('left'=>'lft','right'=>'rght'));
        	}
        	else{
        		$this->{$this->modelClass}->Behaviors->load('Tree', array('left'=>'left','right'=>'right'));
        	}
        	$this->{$this->modelClass}->recover('parent');
        }
        if ($delete_flag) {
            $successinfo = array('success' => __('Delete success'));
        } else {
            $successinfo = array('error' => __('Delete error'));
        }
        $this->set('successinfo', $successinfo);
        $this->set('_serialize', 'successinfo');
        
        if ($delete_flag) {
        	return true;
        }
        else{
        	return false;
        }
    }
    
    /**
     * 获取list请求数据，生成所需要的json数据，列表页面admin_list
     * 形如：
     * 	{"records":1,"page":1,"limit":30,"total":1,"rows":[{id:1,…},{…},{…},{…}]}
     */
    protected function _list() {
    	$modelClass = $this->modelClass;
    	$this->loadModel($modelClass);
    	$this->name = Inflector::pluralize($this->modelClass);
    
    	list($plugin, $modelClass) = pluginSplit($modelClass, false);
    	$options = $this->request->data;
    	$_options = array(
    			'rows' => 30,
    			'page' => 1,
    			'sidx' => 'id',
    			'sord' => 'desc',
    	);
    
    	if (empty($options))
    		$options = array();
    	foreach ($options as $key => $val) {
    		if (empty($val)) {
    			unset($options[$key]); // 传入空的值注销，用默认的$_options中的值
    		}
    	}
    	$_schema_keys = array_keys($this->{$modelClass}->schema());
    	if (in_array('sort', $_schema_keys)) {
    		$_options['sidx'] = 'sort';
    	}
    	$options = array_merge($_options, $options);
    	$search_fields = $search_groupby = $conditions = array();
    	if(isset($this->{$this->modelClass}->actsAs['Tree'])){
    		$level = 0;
    		if (!empty($_POST['nodeid'])) {
    			$conditions = array($modelClass . '.parent_id' => $_POST['nodeid']);
    			$parents = $this->{$modelClass}->getPath($_POST['nodeid']);
    			$level = count($parents);
    		}
    		// 无nodeid时，加载所有数据，自动展示3层。其余通过ajax动态加载。
    		//         else{
    		//         	$_POST['nodeid'] = null;
    		//         	$conditions[$modelClass . '.parent_id'] = null;
    		//         }
    		if (in_array('deleted', $_schema_keys)) {
    			if (!empty($_GET['conditions'][$modelClass . '.deleted'])) {
    				$conditions[$modelClass . '.deleted'] = 1;
    				// 对已删除的内容，设置parent_id条件。
    				unset($conditions[$modelClass . '.parent_id']);
    			}
    			else{
    				$conditions[$modelClass . '.deleted'] = 0;
    			}
    		}
    		
    		if (empty($options['rows'])){
    			$options['rows'] = 1000; //设置一个最大值，防止数据量过多时，造成卡死
    		}
    		/**
    		 * 将id指向parengtid，用于计算level
    		 * @var array
    		 */
    		$trunks = array();
    		$tree_fields = array('parent_id','left','lft','right','rght');
    		
    	}
    	
    	if (!empty($_GET['conditions'])) {
    		foreach ($_GET['conditions'] as $key => $val) {
    			if (trim($val) === '') {
    				unset($_GET['conditions'][$key]);
    			} elseif(substr($key, -8) == '.cate_id'){
    				// 若是分类字段，自动包含下级分类的数据
    				$this->loadModel('Modelcate');
    				$children = $this->Modelcate->children($val,false,array('id'));
    				if(empty($children)){
    					$conditions[$key] = $val;
    				}
    				else{
    					$cate_ids = array($val);
    					foreach($children as $cv){
    						$cate_ids[] = $cv['Modelcate']['id'];
    					}
    					$conditions[$key] = $cate_ids;
    				}
    			} elseif (substr($key, -8) == 'ymdstart') {
    				$newkey = substr($key, 0, -9);
    				$conditions[$newkey . ' >='] = $val;
    			} elseif (substr($key, -6) == 'ymdend') {
    				$newkey = substr($key, 0, -7);
    				$conditions[$newkey . ' <='] = $val;
    			} elseif (substr($key, -8) == '.groupby' && is_array($val)) {
    				$hasgroup = false;
    				foreach ($val as $gf) {
    					if ($gf) {
    						$hasgroup = true;
    						$search_groupby[] = $modelClass . '.' . $gf;
    					}
    				}
    				if ($hasgroup) {
    					$search_fields[] = 'count(*) as groupnum';
    				}
    				unset($_GET['conditions'][$key]);
    				continue;
    			} elseif (substr($key, -4) == '.sum' && is_array($val)) {
    				foreach ($val as $gf) {
    					if ($gf) {
    						$search_sum[] = $gf;
    						$search_fields[] = 'sum(' . $modelClass . '.' . $gf . ') as ' . $gf; // sum() 字段，求和的字段
    						// search_fields记录搜索sql中的字段名与模块名，防止在sql中多模块出现相同的字段，造成sql中无法区分属于哪个模块
    						$fields[] = $gf; // fields数组，只记录单独的字段名
    					}
    				}
    				unset($_GET['conditions'][$key]);
    				continue;
    			} elseif ($key == 'deleted' || substr($key, -8) == '.deleted') {
    				$conditions[$key] = $val;
    				unset($_GET['conditions'][$key]);
    				continue;
    			} else {
    				$conditions[$key] = $val; // 不用like，字段管理(通过模块名，列本模块的字段)选Category模块时，把CategoryArticle的字段也列出来了。
    				//$conditions[$key.'  like'] = '%'.$val.'%';
    			}
    		}
    	}
    
    	if (is_array($this->params['named']) && !empty($this->params['named'])) {
    		foreach ($this->params['named'] as $k => $v) {
    			if(strrpos('.',$k)!==false){
    				$conditions[$k] = $v;
    			}
    			else{
    				$conditions[$modelClass . '.'.$k] = $v;
    			}
    		}
    	}
    
    	$has_step_conditions = false;
    
    	$fields = $_schema_keys;
    	$search_fields = array_merge(array($modelClass . '.*'), $search_fields);
    
    	if (isset($_POST['_search']) && $_POST['_search'] == 'true' && $_POST['filters']) {
    		$filters = json_decode($_POST['filters'], true);
    		foreach ($filters['rules'] as $v) {
    			$f_k = $v['field'];
    			$f_v = $v['data'];
    			switch ($v['op']) {
    				case 'eq': //等于
    					$f_k = $v['field'];
    					break;
    				case 'ne': //不等于
    					$f_k = $v['field'] . ' <>';
    					break;
    				case 'le':  // 小于等于
    					$f_k = $v['field'] . ' <=';
    					break;
    				case 'lt':  // 小于
    					$f_k = $v['field'] . ' <';
    					break;
    				case 'ge':  // 大于等于
    					$f_k = $v['field'] . ' >=';
    					break;
    				case 'gt':  // 大于
    					$f_k = $v['field'] . ' >';
    					break;
    				case 'bw':  // 开始于
    					$f_k = $v['field'] . ' like';
    					$f_v = $v['data'] . '%';
    					break;
    				case 'bn':  // 不开始于
    					$f_k = $v['field'] . ' not like';
    					$f_v = $v['data'] . '%';
    					break;
    				case 'ew':  // 结束于
    					$f_k = $v['field'] . ' like';
    					$f_v = '%' . $v['data'];
    					break;
    				case 'en':  // 不结束于
    					$f_k = $v['field'] . ' not like';
    					$f_v = '%' . $v['data'];
    					break;
    				case 'cn':  // 包含
    					$f_k = $v['field'] . ' like';
    					$f_v = '%' . $v['data'] . '%';
    					break;
    				case 'nc':  // 不包含
    					$f_k = $v['field'] . ' not like';
    					$f_v = '%' . $v['data'] . '%';
    					break;
    				case 'in':  // 属于
    					$f_k = $v['field'];
    					$in_values = explode(',', $v['data']);
    					$f_v = $in_values;
    					break;
    				case 'ni':  // 不属于
    					$f_k = $v['field'] . ' NOT';
    					$in_values = explode(',', $v['data']);
    					$f_v = $in_values;
    					break;
    			}
    			$f_k = $modelClass . '.' . $f_k;
    			if ($filters['groupOp'] == 'AND') {
    				$conditions[$f_k] = $f_v;
    			} elseif ($filters['groupOp'] == 'OR') {
    				$conditions['OR'][$f_k] = $f_v;
    			}
    		}
    	}
    	$sort_order = '';
    	//		if(in_array('sort',$_schema_keys))
    		//		{
    		//			$sort_order = $modelClass.'.sort desc,';
    		//		}
    	if (in_array($options['sidx'], $_schema_keys)) {
    		$sort_order .= $modelClass . '.' . $options['sidx'] . ' ' . $options['sord'];
    	} else {
    		$sort_order .= $options['sidx'] . ' ' . $options['sord'];
    	}
    	if ((!isset($conditions['deleted']) && !isset($conditions[$modelClass . '.deleted'])) && in_array('deleted', $_schema_keys)) {
    		$conditions[$modelClass . '.deleted'] = 0;
    	}
    	
    
    	$searchoptions = array(
    			'conditions' => $conditions,
    			'order' => $sort_order,
    			'limit' => $options['rows'],
    			'page' => $options['page'],
    			'fields' => $search_fields,
    			'group' => $search_groupby,
    	);
    	$joinmodel_fields = array();
    	$alias = 0;
    	$extSchema = $this->{$modelClass}->getExtSchema();
    	$ext_options = array();
    	if (!in_array($modelClass, array('I18nfield', 'Modelextend'))) {
    		foreach ($extSchema as $k => $fieldinfo) {
    			// 加入判断selectmodel是否存在，不存在时不会加入到joins条件
    			if (in_array($k, $fields) && $fieldinfo['selectmodel'] && ModelExists($fieldinfo['selectmodel']) && $fieldinfo['selectvaluefield'] && $fieldinfo['selecttxtfield'] && in_array($fieldinfo['formtype'], array('select', 'checkbox', 'radio'))) {
    				$alias++;
    				$join_model = $fieldinfo['selectmodel'];
    				$model_alias = $join_model . '_' . $alias;
    				$selectvaluefield = $fieldinfo['selectvaluefield'];
    				$selecttxtfield = $fieldinfo['selecttxtfield'];
    				$searchoptions['fields'][] = '`' . $model_alias . '`.`' . $selecttxtfield . '` as ' . $k . '_txt';
    				//$searchoptions['order'] = $join_model.'.'.$selecttxtfield;
    				$joinmodel_fields[$k] = $model_alias;
    
    				$joinconditions = array($model_alias . '.`' . $selectvaluefield . '` = ' . $modelClass . '.' . $k);
    
    				if ($fieldinfo['associateflag'] && $fieldinfo['associateelement'] && $fieldinfo['associatefield']) {
    					//将级联操作的字段也作为表单连接的条件，否则会包含不符合条件的多余的记录
    					$joinconditions[] = $model_alias . '.' . $fieldinfo['associatefield'] . '=' . $modelClass . '.' . $fieldinfo['associateelement'];
    				}
    				$searchoptions['joins'][] = array(
    						'table' => Inflector::tableize($join_model),
    						'alias' => $model_alias,
    						'type' => 'left',
    						'conditions' => $joinconditions,
    				);
    			}
    			elseif($fieldinfo['selectvalues'] && in_array($fieldinfo['formtype'], array('select', 'checkbox', 'radio'))){
    				$ext_options[$fieldinfo['name']] = optionstr_to_array($fieldinfo['selectvalues']);
    			}
    		}
    	}
    	
    	if(method_exists($this,'_custom_list_option')){
    		$searchoptions = $this->_custom_list_option($searchoptions);
    	}
    	
    	if($_REQUEST['export']=='true'){
    		$this->autoRender = false;
    		$this->_downloadxml($modelClass, $searchoptions);
    		exit;
    	}
    
    	//        if(isset($options['sidx']))
    		//        {
    		//        	$searchoptions['order'] = $modelClass .'.'.$options['sidx'].' '.$options['sord'];
    		//        }
    	if(isset($this->{$this->modelClass}->actsAs['Tree'])){
    		// 树形结构时，必需已left，从小到大排序
    		if(in_array('left', $_schema_keys)){
    			$searchoptions['order'] = $modelClass . '.left asc';
    		}
    		elseif(in_array('lft', $_schema_keys)){
    			$searchoptions['order'] = $modelClass . '.lft asc';
    		}
    	}

        $_cond = &$searchoptions['conditions'];
        if ($_cond['User.mobilephone'] == '') {
            unset($_cond['User.mobilephone']);
        }
        if ($_cond['User.username'] == '') {
            unset($_cond['User.username']);
        }

    	$datas = $this->{$modelClass}->find('all', $searchoptions);
    	
    	if ($has_step_conditions) {
    		foreach ($datas as $key => $value) {
    			if (!isset($datas[$key][$modelClass])) {
    				$datas[$key][$modelClass] = array(); // 若没有查询的结果没有设置$datas[$key][$modelClass]，则设为空数组
    			}
    			foreach ($value as $k => $v) {
    				if ($k !== $modelClass) {
    					$datas[$key][$modelClass] = array_merge($datas[$key][$modelClass], $v);
    					unset($datas[$key][$k]);
    				}
    			}
    			if (!empty($step_options['userfunctions'])) {
    				foreach ($step_options['userfunctions'] as $uk => $uv) {
    					$func_name = array_shift($uv);
    					$user_param = array();
    					foreach ($uv as $param_name) {
    						$user_param[] = $datas[$key][$modelClass][$param_name];
    					}
    					//						print_r($user_param);
    					$datas[$key][$modelClass][$uk] = call_user_func_array($func_name, $user_param);
    				}
    			}
    		}
    	}
        //print_r($datas);
        if ($modelClass == 'User') {
            $count = 100000000;
        } else {
            $count = $this->{$modelClass}->find('count', array(
                'conditions' => $conditions,
                // 'group' => $search_groupby , 含有group的查询，计算总数时，要用嵌套查询 select count(*) from (select ***) tabl
            ));
        }
    	//        print_r($count);
    	if ($count > 0) {
    		$total_pages = ceil($count / $options['rows']);
    	} else {
    		$total_pages = 0;
    	}
    	$rows = array();
    	$control_name = Inflector::tableize($modelClass);
    	
    	foreach ($datas as $item) {
    		//        	$item[$modelClass]['title']='xxx';
    		$is_deleted = $item[$modelClass]['deleted'];
    		// deleted的标记位，会在下面的循环中转换为汉字"是,否"，包含原值$is_deleted方便判断
    		foreach ($fields as $field_name) {
    			if (!empty($extSchema[$field_name]['formtype']) && $extSchema[$field_name]['formtype'] == 'file') {
    				$this->loadModel('Uploadfile');
    				$searchoptions = array(
    						'conditions' => array('modelclass' => $modelClass, 'fieldname' => $field_name, 'data_id' => $item[$modelClass]['id']),
    						'order' => 'id asc',
    						'limit' => 20,
    						'page' => 1,
    						'fields' => array('*'),
    				);
    				$datas = $this->Uploadfile->find('all', $searchoptions);
    				$upload_files = array();
    				foreach ($datas as $singlefile) {
    					$upload_files[] = '<a href="' . Router::url('/' . $singlefile['Uploadfile']['fspath']) . '" target="_blank">' . $singlefile['Uploadfile']['name'] . '</a>';
    				}
    				$item[$modelClass][$field_name] = implode('<br/>', $upload_files);
    			} elseif ($extSchema[$field_name]['formtype'] == 'ckeditor' || $extSchema[$field_name]['formtype'] == 'textarea') {
    				$item[$modelClass][$field_name] = usubstr($item[$modelClass][$field_name],0,300);
    			}
    			elseif($field_name!='parent_id' && $extSchema[$field_name]['selectvalues'] && in_array($extSchema[$field_name]['formtype'], array('select', 'checkbox', 'radio'))){
    				$tmpval = $item[$modelClass][$field_name];
    				$item[$modelClass][$field_name] = $ext_options[$field_name][$tmpval];
    			}
    			$item[$modelClass][$field_name] = htmlspecialchars($item[$modelClass][$field_name]);
    		}
    		if (in_array('coverimg', $fields) && $item[$modelClass]['coverimg']) {
    			$img_url = Router::url($item[$modelClass]['coverimg']);
    			$sitepaths = Router::getPaths();
    			$img_url = str_replace($sitepaths['base'], '', $img_url);
    			//echo "$img_url<br/>";
    			$item[$modelClass]['coverimg'] = '<img src="' . $img_url . '" width="100"/>';
    		}
    		/*******重要： action的li之间要一个紧连着一个 ，不要有换行或空格，否则会引起格式错乱。****** */
    		$actions = '';
    		if (!empty($is_deleted)) {
    			if($this->AclFilter->check($this->name,'admin_restore')){
    				$actions .= '<li class="ui-state-default grid-row-restore"><a href="#" data-confirm="'.__('Are you sure to restore').'" onclick="ajaxAction(\'' . Router::url(array('controller' => $control_name, 'action' => 'restore', 'plugin' => strtolower($plugin), 'admin' => true, $item[$modelClass]['id'],'ext'=>'json')) . '\',null,null,\'deleteGridRow\',this)" title="' . __('Restore') . '"><span class="glyphicon glyphicon-retweet"></span></a></li>';
    			}
    			if($this->AclFilter->check($this->name,'admin_restore')){
    				$actions .= '<li class="ui-state-default grid-row-delete"><a href="#" data-confirm="'.__('Are you sure to delete').'" onclick="ajaxAction(\'' . Router::url(array('controller' => $control_name, 'action' => 'delete', 'plugin' => strtolower($plugin), 'admin' => true, $item[$modelClass]['id'],'ext'=>'json')) . '\',null,null,\'deleteGridRow\',this)" title="' . __('Delete') . '"><span class="glyphicon glyphicon-remove"></span></a></li>';
    			}
    		} else {
    			if($this->AclFilter->check($this->name,'admin_edit')){
	    			$actions .= '<li class="ui-state-default grid-row-edit"><a title="' . __('Edit') . '" href="' . Router::url(array('controller' => $control_name, 'action' => 'edit', 'plugin' => strtolower($plugin), 'admin' => true, $item[$modelClass]['id'])) . '"><span class="glyphicon glyphicon-pencil"></span></a></li>';
	    			$actions .= '<li class="ui-state-default grid-row-edit"><a href="' . Router::url(array('controller' => $control_name, 'action' => 'edit', 'plugin' => strtolower($plugin), 'admin' => true, $item[$modelClass]['id'],'copy')) . '" title="' . __('Copy') . '"><span class="glyphicon glyphicon-file"></span></a></li>';
    			}
    
    			// 行内编辑，保存，取消编辑
    			// $actions .= '<li id="edit_grid_row_'.$item[$modelClass]['id'].'" class="ui-state-default" onclick="editGridRow(\''.$item[$modelClass]['id'].'\');" title="'.__('Inline Edit',true).'"><span class="ui-icon ui-icon-circle-arrow-w"></span></li>';
    			// $actions .= '<li id="save_grid_row_'.$item[$modelClass]['id'].'" style="display:none" class="ui-state-default" onclick="SaveGridRow(\''.$item[$modelClass]['id'].'\');" title="'.__('Save',true).'"><span class="ui-icon ui-icon-disk"></span></li>';
    			// $actions .= '<li id="canceledit_grid_row_'.$item[$modelClass]['id'].'" style="display:none" class="ui-state-default" onclick="CancelEditGridRow(\''.$item[$modelClass]['id'].'\');" title="'.__('Cancel',true).'"><span class="ui-icon ui-icon-arrowreturnthick-1-w"></span></li>';
    			if (!isset($item[$modelClass]['deleted'])) { // 不包含deleted标记位的模块，直接删除
    				$actions .= '<li class="ui-state-default grid-row-delete"><a href="#" data-confirm="'.__('Are you sure to delete').'" onclick="ajaxAction(\'' . Router::url(array('controller' => $control_name, 'action' => 'delete', 'plugin' => strtolower($plugin), 'admin' => true, $item[$modelClass]['id'],'ext'=>'json')) . '\',null,null,\'deleteGridRow\',this)" title="' . __('Delete') . '"><span class="glyphicon glyphicon-remove"></span></a></li>';
    			} else {// 包含deleted标记位的模块，则删除到回收站
    				$actions .= '<li class="ui-state-default grid-row-trash"><a href="#" data-confirm="'.__('Are you sure to trash').'" onclick="ajaxAction(\'' . Router::url(array('controller' => $control_name, 'action' => 'trash', 'plugin' => strtolower($plugin), 'admin' => true, $item[$modelClass]['id'],'ext'=>'json')) . '\',null,null,\'deleteGridRow\',this)" title="' . __('Trash') . '"><span class="glyphicon glyphicon-trash"></span></a></li>';
    			}
    		}
    		$actions .= '<li class="ui-state-default grid-row-view"><a href="' . Router::url(array('controller' => $control_name, 'action' => 'view', 'plugin' => strtolower($plugin), 'admin' => true, $item[$modelClass]['id'])) . '" title="' . __('View') . '"><span class="glyphicon glyphicon-info-sign"></span></a></li>';
    		
    		$actions .= $this->Hook->call('gridDataAction', array($modelClass, $item[$modelClass]));
    		if(!isset($this->{$this->modelClass}->actsAs['Tree'])){
	    		foreach ($joinmodel_fields as $joinfield => $joinmodel) {
	    			if ($item[$joinmodel][$joinfield . '_txt']) {
	    				$item[$modelClass][$joinfield] = $item[$joinmodel][$joinfield . '_txt'];
	    			}
	    		}
    		}
    		$item[$modelClass]['actions'] = $actions;
    
    
    		$params = array($modelClass, &$item[$modelClass]);
    		$this->Hook->call('gridList', $params);
    		$item[$modelClass]['actions'] = '<ul class="ui-grid-actions">' . $item[$modelClass]['actions'] . '</ul>';
    		
    		if(isset($this->{$this->modelClass}->actsAs['Tree'])){
    			$level_currentid = $item[$modelClass]['id'];
    			if (empty($_POST['nodeid'])) {
    				$level = 0;
    				// 将id指向parengtid，用于计算level
    				$trunks[$level_currentid] = $item[$modelClass]['parent_id'];
    				while ($level_currentid && $trunks[$level_currentid]) {
    					$level++;
    					$level_currentid = $trunks[$level_currentid];
    				}
    				if ($level > 2)
    					continue; // 初始化加载时，只加载到第三层。0,1,2
    			}
    			
    			$item[$modelClass]['level'] = $level;
    			if ($item[$modelClass]['right'] == $item[$modelClass]['left'] + 1) {
    				$item[$modelClass]['isLeaf'] = true;
    			}
    			elseif ($item[$modelClass]['rght'] == $item[$modelClass]['lft'] + 1) {
    				$item[$modelClass]['right'] = $item[$modelClass]['rght'];
    				$item[$modelClass]['left'] = $item[$modelClass]['lft'];
    				$item[$modelClass]['isLeaf'] = true;
    			} else {
    				$item[$modelClass]['isLeaf'] = false;
    			}
    			
    			// 			if($level>3 || $item[$modelClass]['isLeaf']){
    			// 				$item[$modelClass]['expanded'] = false;
    			// 			}
    			// 			else{
    			// 				$item[$modelClass]['expanded'] = true;
    			// 			}
    			
    			$item[$modelClass]['expanded'] = false;
    			if (!$item[$modelClass]['parent_id'])
    				$item[$modelClass]['parent_id'] = 'NULL';
    		}
    		$rows[] = $item[$modelClass];
    	}
    
    	$datalist = array(
    			'records' => $count,
    			'page' => $options['page'],
    			'limit' => $options['rows'],
    			'total' => $total_pages,
    			'rows' => $rows,
    	);
    
    	$this->set('datalist', $datalist);
    	$this->set('_serialize', 'datalist');
    }

    /**
     * 列表页
     */
    function admin_list() {
    	if($this->request->params['ext']=='json'){
    		$this->_list();
    		return;
    	}
    	elseif($_GET['type']=='select'){
    		$this->_select(); // dialog显示本模块列表数据共选择，用于与其他模块建立关联。如添加新闻产品等的选择标签。
    		return;
    	}
    	if($this->plugin){
    		$requeststr = 'model=' . $this->plugin.'.'.urlencode($this->modelClass);
    	}
    	else{
        	$requeststr = 'model=' . urlencode($this->modelClass);
    	}
        $model_setting = Configure::read($this->modelClass);    	
        // 加载表单默认值。用于搜索表单、行内编辑
        $this->__loadFormValues($this->modelClass);
        $ext_schema = $this->{$this->modelClass}->getExtSchema();
        $fileds = array_keys($ext_schema);
        
        if (isset($model_setting['list_fields'])) {
            $listfields = explode(',', $model_setting['list_fields']);
        } else {
            $listfields = $fileds;
        }
        $col_names = $fieldlist = '';
        foreach ($fileds as $field) {
        	if ($field !='parent_id' && !in_array($field, $listfields)) {
        		continue;
        	}
        	
            $col_width = '';            
            $col_names .="'" . __d('i18nfield','Field_'.$this->modelClass.'_'.$field) . "',";

            $editoptions = '';
            $view_values_name = Inflector::variable(
            	Inflector::pluralize(preg_replace('/_id$/', '', $field))
            );
            if (!empty($this->viewVars[$view_values_name]) && is_array($this->viewVars[$view_values_name])) {
                foreach ($this->viewVars[$view_values_name] as $key => $value) {
                    $editoptions .= "$key:$value;";
                }               
                $editoptions = 'editable: true,edittype:"select",editoptions:{value:"' . substr(preg_replace("/\s/",'',$editoptions), 0, -1) . '"},';
            } elseif (in_array($field, array('password', 'id'))) {
                $editoptions = 'editable: false,';
            } else {
                $editoptions = 'editable: true,';
            }
            
            if ($field == 'id' || 'id' == substr($field, -2)) {
                $col_width = 'width:"30px",';
            } elseif ($field == 'name' || $field == 'title') {
                $col_width = '';
            }//width:"150px",
            elseif (!empty($ext_schema[$field]['type']) && $ext_schema[$field]['type'] == 'datetime') {
                $col_width = 'width:"100px",';
            } elseif (!empty($ext_schema[$field]['type']) && $ext_schema[$field]['type'] == 'integer') {
                $col_width = 'width:"30px",';
            } else {
                $col_width = 'width:"80px",';
            }
       		if (!in_array($field, $listfields)) {
                $fieldlist .="{name:'$field'," . $col_width . $editoptions . "index:'$field',hidden:true,searchoptions:{searchhidden:false},search:true}\r\n,";
            }
            else{
            	$fieldlist .="{name:'$field'," . $col_width . $editoptions . "index:'$field',search:true}\r\n,";
            }
        }
//		$col_names=substr($col_names,0,-1);
//		$fieldlist=substr($fieldlist,0,-1);
        $col_names.="'" . __('Actions') . "'";
        $fieldlist.="{name:'actions',index:'actions',width:90,sortable:false,hidedlg:true,search:false}";
		
        $named_str = '';
        foreach($this->request->params['named'] as $key => $val){
        	$named_str .= htmlspecialchars($key) . ':' . urlencode($val) . '/';
        }
        
        $is_tree_model = false;
        $grid_action ='jqgrid';       
    	if(!isset($this->request->query['list']) && isset($this->{$this->modelClass}->actsAs['Tree']) ){
    		$this->set('treelist', true);
    		$requeststr .='&q=tree';
    		$grid_action='jqgridtree';
    		$is_tree_model = true;
    	}
    	
    	if(!empty($named_str)){
    		$grid_action .= '/'.$named_str;
    	}
    	
    	if($this->AclFilter->check($this->name,'admin_add')){
    		$this->set('allow_add', true);
    	}
    	if($this->AclFilter->check($this->name,'admin_publish')){
    		$this->set('allow_publish', true);    		
    	}
    	if($this->AclFilter->check($this->name,'admin_delete')){
    		$this->set('allow_delete', true);
    	}
    	if($this->AclFilter->check($this->name,'admin_trash')){
    		$this->set('allow_trash', true);
    	}
    	if($this->AclFilter->check($this->name,'admin_restore')){
    		$this->set('allow_restore', true);
    	}
    	if($this->AclFilter->check('Settings','admin_fieldsetting')){
    		$this->set('allow_fieldsetting', true);
    	}
    	if($this->AclFilter->check('Settings','admin_setting')){
    		$this->set('allow_setting', true);
    	}
    	
    	$this->set('is_tree_model', $is_tree_model);
    	$this->set('grid_action', $grid_action);
        $this->set('table_fields', $fileds);
        $this->set('fieldlist', $fieldlist);
        $this->set('col_names', $col_names);
        $this->set('checked_fields', $listfields); // list_fields选中字段
        $this->set('requeststr', $requeststr);
        
        $this->set('modelinfo', $this->{$this->modelClass}->getModelInfo());
        $this->set('schema', $this->{$this->modelClass}->schema());
    }

    function admin_treesort($parentid = null) {
    	$step = intval($_POST['step']) ? intval($_POST['step']) : 1;
    	
        if (!empty($_POST)) {
            set_time_limit(0);
            if ($_POST['type'] == 'up') {
                $this->{$this->modelClass}->moveUp($_POST['id'], $step);
            } else {
                $this->{$this->modelClass}->moveDown($_POST['id'], $step);
            }
            $successinfo = array('success'=>'move '.$_POST['type'].' success.');
            $this->set('successinfo', $successinfo);
            $this->set('_serialize', 'successinfo');
            return;
        }
        $datas = $this->{$this->modelClass}->children($parentid, true);
        $this->set('datas', $datas);
        $this->set('modelinfo', $this->{$this->modelClass}->getModelInfo());
        $this->set('modelClass', $this->modelClass);

        $parents = $this->{$this->modelClass}->getPath($parentid);
        $this->set('parents', $parents);
    }

    function admin_treerecover() {
    	set_time_limit(0);
        if (isset($this->{$this->modelClass}->actsAs['Tree'])) {
            $this->{$this->modelClass}->beforeSave();
            $this->{$this->modelClass}->recover('parent');
            $this->{$this->modelClass}->afterSave(false);
            exit('rocover sucess.');
        }
        else{
        	exit('error no tree.');
        }
        
    }
    
    /**
     * 后台表单中，选择标签内容. 从列表中选择相关联的数据，嵌入在admin_list中，
     */
    function _select(){
    	$page = $_GET['page'] ? $_GET['page']:1;
    	$targetid = $_GET['targetid'];
    	$model = $_GET['m'];
    	$conditions = array();
    	if($_GET['name']){
    		$conditions['name like'] = '%'.$_GET['name'].'%';
    	}
    	$pagesize = 30;
    	
    	$this->__viewFileName = 'admin_select';
    	
    	$total = $this->{$this->modelClass}->find('count',array('conditions'=> $conditions));
    	$select_items = $this->{$this->modelClass}->find('all',array(
    			'conditions'=> $conditions,
    			'limit' => $pagesize,
    			'page' => $page,
    
    	));
    	$page_navi = getPageLinks($total, $pagesize, array('action'=>'select'), $page);
    	
    	$this->set('relatedmodel',$model);
    	$this->set('select_items',$select_items);
    	$this->set('targetid',$targetid);
    	$this->set('srcmodel',$this->modelClass);
    	$this->set('current_model',$this->modelClass);
    	$this->set('page_navi', $page_navi);
    	
    	$associd = Inflector::underscore($this->modelClass).'_id';
    	$this->set('associd',$associd);
    }

	protected function _getParamVars($name,$default='') {
        $val = '';
    	if (isset($this->request->query[$name])) {
        	$val = $this->request->query[$name];
        }elseif (isset($this->request->params['named'][$name])) {
            $val = $this->request->params['named'][$name];
        }elseif (isset($this->request->params[$name])) {
            $val = $this->request->params[$name];
        }else{
        	return $default;
        }
        return $val;
    }

    function valifiled($field='') {
        // 直接输出，jsonencode，不需要调用模板
        $modelClass = $this->modelClass;
        //往model中注入值，否则无法验证
        $this->{$modelClass}->set($this->data);
        // 只验证提交过来的单个字段，或几个字段
        $errors = $this->{$modelClass}->invalidFields(array('fieldList' => array_keys($this->data[$modelClass])));
        //$errors = $this->{$modelClass}->invalidFields();
        $this->set('errors', $errors);
        $this->set('_serialize', 'errors');
    }
    
    function renderElement($element) {
    	return $this->render($element,false);
    }

    function __message($message, $url, $seconds=5) {
    	@header('Content-Type:text/html; charset=UTF-8');
        if($this->request->params['return']){ // in controller::requestAction;
        	return $message;
        }
        else{        	
	        $this->set('message', $message);
	        $this->set('seconds', $seconds);
	        $this->set('url', $url);
	        
	        $this->layout = null;
	        $this->__viewFileName = 'message';
	        $this->autoRender = false;
        	echo $this->render();
        	exit;
        }
        
    }
	
    /**
     * 加载关联模块的相关的数据
     */
    function __loadAssocValues() {
    	/*
        if (!empty($this->{$this->modelClass}->hasAndBelongsToMany)) {
            foreach ($this->{$this->modelClass}->hasAndBelongsToMany as $k => $val) {
                $variable = Inflector::tableize($k);
                if(!empty($this->{$this->modelClass}->{$k}->actsAs['Tree'])){
                	$option_result = $this->{$this->modelClass}->{$k}->generateTreeList( $val['conditions']);
                }
                else{
                	$option_result = $this->{$this->modelClass}->{$k}->find('list', array('conditions' => $val['conditions']));
                }
                $this->set($variable, $option_result);
                $selected = array();
                if (!empty($this->data[$k])) {
                    foreach ($this->data[$k] as $v) {
                        $selected[] = $v['id'];
                    }
                }
                $this->set('selected_' . $variable, $selected);
            }
        }
        */
    	if (!empty($this->{$this->modelClass}->hasOne)) {
            foreach ($this->{$this->modelClass}->hasOne as $k => $val) {
                
                if(!empty($this->{$this->modelClass}->{$k}->actsAs['Tree'])){
                	$option_result = $this->{$this->modelClass}->{$k}->generateTreeList( $val['conditions']);
                }
                else{
                	$option_result = $this->{$this->modelClass}->{$k}->find('list', array('conditions' => $val['conditions']));
                }
                $fieldname = Inflector::variable(
                	Inflector::pluralize(preg_replace('/_id$/', '', $val['foreignKey']))
                );
                $this->set($fieldname, $option_result);                
//                $selected = array();
//                if (!empty($this->data[$k])) {
//                    foreach ($this->data[$k] as $v) {
//                        $selected[] = $v['id'];
//                    }
//                }
//                $this->set('selected_' . $variable, $selected);
            }
        }
        
    }

    /**
     * 加载表单选项值，默认值等。
     * 加载的值赋值到$this->request->data[$modelClass][$fieldname]中
     * @param  $modelClass 模块名称
     * @param  $id		数据id
     */
    function __loadFormValues($modelClass, $id = '') {
//		var_dump($this->{$modelClass});
        //$loadModelObj = null;
        $model_alias = $this->{$modelClass}->alias;
        if (isset($this->{$modelClass})) {
            $ext_schema = $this->{$modelClass}->getExtSchema();
        } elseif (isset($this->{$this->modelClass}->{$modelClass})) {
            $ext_schema = $this->{$this->modelClass}->{$modelClass}->getExtSchema();
        }
        foreach ($ext_schema as $k => $v) {
            if ($v['selectvalues']) {
                $selects = optionstr_to_array($v['selectvalues']);
                $newoptions = array('' => __('Please select'));
                foreach ($selects as $ok => $ov) {
                    $newoptions[$ok] = $ov;
                }
                $fieldname = Inflector::variable(
                	Inflector::pluralize(preg_replace('/_id$/', '', $k))
                );
                $this->set($fieldname, $newoptions);
            }
            elseif ($id && $v['formtype'] == 'file') {
                //修改，字段为文件上传时。 从uploadfile模块中加载文件
                $this->loadModel('Uploadfile');
                $searchoptions = array(
                    'conditions' => array('modelclass' => $modelClass, 'fieldname' => $v['name'], 'data_id' => $id),
                    'order' => 'id asc',
                    'limit' => 100,
                    'page' => 1,
                    'fields' => array('*'),
                );
                $datas = $this->Uploadfile->find('all', $searchoptions);
                $upload_files = array();
                foreach ($datas as $singlefile) {
                    $upload_files[] = $singlefile['Uploadfile'];
                }
                $this->data['Uploadfile'][$v['name']] = $upload_files;                
            }
            elseif ($v['selectautoload'] && $v['selectmodel'] && in_array($v['formtype'], array('select', 'checkbox', 'radio'))) {
            	$selectmodel_name = Inflector::classify($v['selectmodel']);
                if ($selectmodel_name != $this->modelClass) {
                    $this->loadModel($selectmodel_name);
                }
                // 包含deleted字段时，查询deleted为0的数据，即未删除数据
                $model_fields = array_keys($this->{$selectmodel_name}->schema());
                
                $conditions = array();
                $xml_oprions = xml_to_array($v['conditions']);	  
                if(isset($xml_oprions['options']['conditions'])){
                	$conditions = $xml_oprions['options']['conditions'];
                }
                if (in_array('deleted', $model_fields)) {
                    $conditions[$selectmodel_name . '.deleted'] = 0;
                }
// 后台无需设置必须要发布才显示，未删除的数据都显示
//				if(in_array('published',$model_fields))
//				{
//					$conditions['published'] = 1; 
//				}

                if ($v['formtype'] == 'select' && $v['name'] != 'status') {
                    $newoptions = array('' => __('Please select'));
                } else {
                    $newoptions = array();
                }
				/**
				 * 树类型的模型
				 */
                if (isset($this->{$selectmodel_name}->actsAs['Tree'])) {
                    if ($v['associatetype'] == 'treenode') {
                        if ($v['selectparentid']) {
                            $rootcate = $this->{$selectmodel_name}->findById($v['selectparentid']);
                            $conditions['left >'] = $left = $rootcate[$selectmodel_name]['left'];
                            $conditions['right <'] = $right = $rootcate[$selectmodel_name]['right'];
                            $options = $this->{$selectmodel_name}->generateTreeList($conditions);
                        } else {
                            $options = $this->{$selectmodel_name}->generateTreeList($conditions);
                        }
                        foreach ($options as $ok => $ov) {
                            $newoptions[$ok] = $ov;
                        }
                    } else {
                        // 只显示一级的情况
//						$option_result = $this->{$selectmodel_name}->children($v['selectparentid'], true);
                        if($v['selectparentid']){
                    		$conditions['parent_id'] = $v['selectparentid'];
                        }
                        else{
                        	$conditions['parent_id'] = NULL;
                        }
                        
                        $option_result = $this->{$selectmodel_name}->find('all', array(
                                    'conditions' => $conditions,
                                    'order' => $selectmodel_name . '.id ASC',
                                    'limit' => 1000,
                                    'page' => 1,
                                    'fields' => array(
                                        $v['selectvaluefield'], $v['selecttxtfield'],
                                    ),
                                ));

                        foreach ($option_result as $option) {
                            $optionvalue = $option[$selectmodel_name][$v['selectvaluefield']];
                            $optiontxt = $option[$selectmodel_name][$v['selecttxtfield']];
                            $newoptions[$optionvalue] = $optiontxt;
                        }
                    }
                } else {
                    $option_result = $this->{$selectmodel_name}->find('all', array(
                                'conditions' => $conditions,
                                'order' => $selectmodel_name . '.id ASC',
                                'limit' => 1000,
                                'page' => 1,
                                'fields' => array(
                                    $v['selectvaluefield'], $v['selecttxtfield'],
                                ),
                            ));
                    foreach ($option_result as $option) {
                        $optionvalue = $option[$selectmodel_name][$v['selectvaluefield']];
                        $optiontxt = $option[$selectmodel_name][$v['selecttxtfield']];
                        $newoptions[$optionvalue] = $optiontxt;
                    }
                }
				$fieldname = Inflector::variable(
					Inflector::pluralize(preg_replace('/_id$/', '', $k))
				);
                $this->set($fieldname, $newoptions);
            }
        }
    }

    /**
     * 获取系统所有语言,并设置Configure::write('System.ActiveLanguage',...);
     */
    private function __loadSystemLanguages() {
        $system_language = Cache::read('System.ActiveLanguage');
        if ($system_language === false) {
            $this->loadmodel('Language');
            $system_language = array();
            $languages = $this->Language->find('all', array('conditions' => array('active' => 1)));
            foreach ($languages as $val) {
                $system_language[$val['Language']['alias']] = $val['Language'];
            }
//            print_r($system_language);
            Cache::write('System.ActiveLanguage', $system_language);
        }
        Configure::write('System.ActiveLanguage', $system_language);
    }

    /**
     * 取得本模块或其它模块，explode批量增加数据的字段；
     * 一个模块仅允许一个eplode字段；（多个explode字段时，值需要两两组合，不符合keep it simple原则，用户添加数据也难以考虑是不是所有组合数据都符合预期。）
     * @param $modelClass
     */
    private function __getexplodefield($modelClass='') {
        if ($modelClass == '') {
            $modelClass = $this->modelClass;
        }
        $ext_schema = $this->{$modelClass}->getExtSchema();
        if (!empty($ext_schema)) {
            foreach ($ext_schema as $k => $v) {
                if ($v['formtype'] == 'checkbox' || $v['formtype'] == 'textarea') {
                    if ($v['explodeimplode'] == 'explode') {
                        return $k;
                    }
                }
            }
        }
        return false;
    }

    private function _lazyloadimg($content) {
        //<img border="1" alt="" id="vimage_3462857" src="/files/remote/2010-10/cnbeta_2038145814065004.jpg" />
        // 双引号，单引号，无引号三种图片类型的代码。
        $content = preg_replace('/<img([^>]+?)src="([^"]+?)"([^>]+?)>/is', "<img \\1src=\"/img/grey.gif\" class=\"lazy\" data-original=\"\\2\" \\3>", $content);

        $content = preg_replace('/<img([^>]+?)src=\'([^\']+?)\'([^>]+?)>/is', "<img \\1src=\"/img/grey.gif\" class=\"lazy\" data-original=\"\\2\" \\3>", $content);
        //[^\s"\'] 表示非空、非引号同时成立
        $content = preg_replace('/<img([^>]+?)src=([^\s"\']+?)([^>]+?)>/is', "<img \\1src=\"/img/grey.gif\" class=\"lazy\" data-original=\"\\2\" \\3>", $content);
//    	echo $content;exit;
        return $content;
    }
    
    /**
     * 占用较小的内存，更适合网站空间php占用内存限制小的情况。
     * @param unknown_type $modelClass
     * @param unknown_type $searchoptions
     */
    private function _downloadxml($modelClass,$searchoptions){
    	@set_time_limit(0);
    	App::import('Vendor', 'Excel_XML', array('file' => 'phpexcel'.DS.'excel_xml.class.php'));
    	$xls = new Excel_XML('UTF-8', true, 'Sheet '.$modelClass);
    
    	$extschema = $this->{$modelClass}->getExtSchema();
    	unset($extschema['creator'],$extschema['lastupdator'],
    			$extschema['updated'],$extschema['locale'],
    			$extschema['published'],$extschema['deleted'],
    			$extschema['favor_nums'],$extschema['point_nums'],$extschema['views_count'],
    			$extschema['seotitle'],$extschema['seodescription'],$extschema['seokeywords']);
    	
    	unset($searchoptions['limit'],$searchoptions['page']);
    	
    	$alias = array();
    	if(!empty($searchoptions['joins'])){
    		foreach($searchoptions['joins'] as $join){
    			$alias[] = $join['alias'];
    		}
    	}
    	$add_header_flag = false;
    
    	$fields = array_keys($extschema);
    	$page = 1;
    	$pagesize = 500;
    	do{
    		$searchoptions['limit'] = $pagesize;
    		$searchoptions['page']=$page;
    		$datas = $this->{$modelClass}->find('all', $searchoptions);    		
    		$rows = count($datas);
    		foreach($datas as $item){    			
    			if($add_header_flag==false){
    				$header = array();
			    	foreach($extschema as $ext_item){
			    		$header[] = $ext_item['translate'];
			    	}
			    	// 相关连表的相关字段
    				foreach($alias as $alia){
    					foreach($item[$alia] as $key=>$val){
    						$header[] = $alia.'-'.$key;
    					}
    				}
    				$xls->addRow($header);
    				$add_header_flag = true;    				
    			}
    			$row = array();
    			foreach($fields as $fieldname){
    				$row[] = $item[$modelClass][$fieldname];
    			}
    			foreach($alias as $alia){
    				foreach($item[$alia] as $val){
    					$row[] = $val;
    				}
    			}
    			$xls->addRow($row);
    		}
    		unset($datas);// 主动注销防止变量占用太多内存
    		++$page;
    	}while($rows==$pagesize);
    
    	$xls->generateXML($modelClass.'_'.date('Y-m-d'));
    }
    
    /**
     * PHPExcel examples
     * https://github.com/PHPOffice/PHPExcel/tree/develop/Examples
     * @param unknown_type $conditions
     */
    private function _downloadPHPExcel($modelClass,$searchoptions){
    	@set_time_limit(0);
    	App::import('Vendor', 'PHPExcel', array('file' => 'phpexcel'.DS.'PHPExcel.php'));
    	/** PHPExcel_Writer_Excel2007 */
    	$objPHPExcel = new PHPExcel();
    
    	$objPHPExcel->getProperties()->setCreator("MiaoMiaoXuan");
    	$objPHPExcel->getProperties()->setTitle($modelClass.'_'.date('H:i:s'));
    	$objPHPExcel->setActiveSheetIndex(0);
    
    	$extschema = $this->{$modelClass}->getExtSchema();
    	$cells = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N',
    			'O','P','Q','R','S','T','U','V','W','X','Y','Z',
    			'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN',
    			'AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ',
    			'BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN',
    			'BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ',
    	);
    	$tcols = count($cells);
    	unset($extschema['creator'],$extschema['lastupdator'],
    			$extschema['updated'],$extschema['locale'],
    			$extschema['published'],$extschema['deleted'],
    			$extschema['favor_nums'],$extschema['point_nums'],$extschema['views_count'],
    			$extschema['seotitle'],$extschema['seodescription'],$extschema['seokeywords']);
    	$i=0;
    	foreach($extschema as $item){
    		$objPHPExcel->getActiveSheet()->SetCellValue($cells[$i].'1',$item['translate']);
    		$i++;
    		if($i>=$tcols){
    			break;
    		}
    	}
    
    	unset($searchoptions['limit'],$searchoptions['page'],$searchoptions['fields']);
    
    	$fields = array_keys($extschema);
    	$page = 1;
    	$pagesize = 500;
    	$line = 2;//表头为第一行，内容从第二行开始。
    	do{
    		$searchoptions['limit'] = $pagesize;
    		$searchoptions['page']=$page;
    		$datas = $this->{$modelClass}->find('all', $searchoptions);
    		$rows = count($datas);
    		foreach($datas as $item){
    			$i = 0;
    			foreach($fields as $fieldname){
    				$objPHPExcel->getActiveSheet()->SetCellValue($cells[$i].$line,$item[$modelClass][$fieldname]);
    				$i++;
    				if($i>=$tcols){
    					break;
    				}
    			}
    			++$line;
    		}
    		unset($datas);// 主动注销防止变量占用太多内存
    		++$page;
    	}while($rows==$pagesize);
    	//$objPHPExcel->getActiveSheet()->SetCellValue('B2', 'world!');
    	//$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Hello');
    	//$objPHPExcel->getActiveSheet()->SetCellValue('D2', 'world!');
    	// Rename sheet
    	$objPHPExcel->getActiveSheet()->setTitle('Sheet1');
    
    	// Save Excel 2007 file
    	//echo date('H:i:s') . " Write to Excel2007 format\n";
    	//App::import('Vendor', 'PHPExcel_Writer_Excel2007', array('file' => 'phpexcel/PHPExcel/Writer/Excel2007.php'));
    	//include 'PHPExcel/Writer/Excel2007.php';
    	//$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    	//$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
    
    	header('Content-Type: application/vnd.ms-excel');
    	header('Content-Disposition: attachment;filename="'.$modelClass.'_'.date('Y-m-d').'.xls"');
    	header('Cache-Control: max-age=0');
    
    	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); //'Excel2007'
    	$objWriter->save('php://output');
    }

}

?>