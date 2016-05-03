<?php

class AppController extends Controller {

    public $theme = 'default';
    public $viewClass = 'Miao';
    var $helpers = array(
        'Html', 'Section', 'Session', 'Form', 'Js', 'Hook' => array(),
    	'Ckeditor', 'Swfupload', 'Ajax','TagRelated',
    ); //'Oauth.OauthHook'
    public $__viewFileName = '';
    /*AuthComponent 移到UsersController(或其它需要控制访问权限的controller)中，AppController使用$this->Session->read('Auth.User')获取用户*/
    var $components = array(
    	'RequestHandler','Session', 'Hook' => array(),
    );
    var $uses = array('Setting', 'StatsDay');
 //,'Category'
    var $currentUser = null;
    var $insert_id;
 // 添加数据时。插入数据库的id
    var $WeiboUtil = null;
    var $role_ids = array();
 	/**
 	 * 编码转换方法，用于在简体与繁体互换，在dzstyle中执行转换操作。
 	 * @var string
 	 */
    var $convertCode = false;
 // 是否转码
    var $pageTitle;
    var $current_data_id;

    public $firephp_vars = array();
    public $viewdata = null;

    // 自定义构造函数
    public function __construct($request = null, $response = null) {
        global $_admin_uids;
        if ($request instanceof CakeRequest) {
            $this->name = $request['params']['controller'];
        }

        if (substr($_SERVER['REQUEST_URI'], 0, 11) == '/index.php/') {
            header('location:' . substr($_SERVER['REQUEST_URI'], 10));
            exit;
        }
        if($request){
            $request->webroot = '/';  // webroot目录直接放在根目录
        }
        parent::__construct($request, $response);
        $this->_international();
        if (!empty($this->uses) && !in_array($this->modelClass, $this->uses)) {
            if ($this->plugin) {
                $this->plugin = Inflector::camelize($this->plugin);
                array_unshift($this->uses, $this->plugin . '.' . $this->modelClass);
            } else {
                array_unshift($this->uses, $this->modelClass);
            }
        }
    }

    public function beforeFilter() {
    	load_lang('default'); // 加载默认语言

    	$this->Setting->writeConfiguration();
    	$site_info = Configure::read('Site');
    	$this->set('site', $site_info);
    	$GLOBALS['site_cate_id'] = Configure::read('Site.default_site_cate_id');


    	// 无Session，且有Cookie登录信息时，解析cookie生成信息。否则忽略cookie，防止每次都要消耗性能解密cookie
    	// 其余时间使用session。
    	if(!$this->Session->read('Auth.User.id') && isset($_COOKIE['SAECMS']) && $_COOKIE['SAECMS']['Auth']['User']){

            $this->Cookie = $this->Components->load('Cookie',array('name' => 'SAECMS', 'time' => '+2 weeks'));
            $user = $this->Cookie->read('Auth.User');

    		if(is_array($user) && intval($user['id'])>0){
    			$this->loadModel('User');
    			$this->User->recursive = -1;
    			$data = $this->User->find('first', array('conditions' => array('id' => $user['id'])));
    			$this->Session->write('Auth.User',$data['User']);
    		}
    		else{
    			$this->Cookie->delete('Auth.User');//删除解密错误的cookie信息
    		}
    	}

        if(!Configure::read('Site.status')){
    		$this->layout = 'maintain';
    		$this->autoRender = false;
    		echo $this->render('message');
    		exit;
    	}

    	$this->currentUser = $this->Session->read('Auth.User');

    	$this->theme = Configure::read('Site.theme');

    	if($this->RequestHandler->isMobile()){
    		$this->theme = '3g';
    	}
    	if($_GET['theme']){
    		$this->theme=$_GET['theme'];
    	}

    	if ($this->RequestHandler->isAjax() || isset($_GET['inajax'])) {
    		// ajax 操作
    		$this->layout = 'ajax';
    	}

    	if (Configure::read('Site.openStatic')){
    		App::uses('HtmlCache', 'Lib');
    		$content = HtmlCache::getfile($this->request->here);
    		if(!empty($content)){
    			$etag = md5($content);
    			if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH']==$etag){
    				Header("HTTP/1.0 304 Not Modified");
    			}
    			header("Pragma: public");
    			header("Cache-Control: max-age=31536000");
    			header("Expires: ".gmdate("D, d M Y H:i:s", time()+31536000)."  GMT");
	    		header('Content-Type:text/html; charset=UTF-8');
	    		header('ETag: ' . $etag);
	    		echo $content;
	    		echo '<!-- get from static file cache. -->';
	    		exit;
    		}
    	}

        if($this->is_weixin()){
            if (empty($this->currentUser) && $this->is_weixin() && !in_array($this->request->params['controller'], array('users', 'check'))) {
                $this->redirect($this->login_link());
            }
        }

        if ($this->is_weixin() && !empty($this->currentUser['id'])) {
            $this->set('jWeixinOn', true);
            $this->loadModel('WxOauth');
            $signPackage = $this->WxOauth->getSignPackage();
            $this->set('signPackage', $signPackage);
        }
        //log weixin share
        if($_GET['share_type'] && $_GET['trstr']){
            $share_type = $_GET['share_type'];
            $trstr = $_GET['trstr'];
            if($share_type != 'timeline' && $share_type != 'appMsg'){
                $this->log("WxShare: type wrong");
                return;
            }
            $type = $share_type == 'timeline' ? 1:0;
            $decode_string = authcode($trstr, 'DECODE', 'SHARE_TID');
            $str = explode('-',$decode_string);
            $data_str = explode('_',$str[3]);
            if($str[2] != 'rebate'){
                $this->log("WxShare: PRODUCT_KEY WRONG");
                return;
            }
            $data_type =$data_str[0];
            $sharer = intval($str[0]);
            $created = intval($str[1]);
            $clicker = $this->currentUser['id'];
            $clicker = $clicker == null ? 0 : $clicker;
            if($clicker != $sharer){
                $this->loadModel('ShareTrackLog');
                $data =array('sharer' => $sharer, 'clicker' => $clicker, 'share_time' => $created, 'click_time'=>time(), 'data_type' => $data_type, 'data_id' => intval($data_str[1]) , 'share_type' => $type);
                $this->ShareTrackLog->save($data);
            }
        }
        //set static file path
        $this->set('STATIC_HOST', STATIC_HOST);
        $this->set('IMAGES_HOST', IMAGES_HOST);
    }

    public function afterFilter() {
        if($_GET['output']=='pdf'){
        	$html = $this->response->body();
        	$file_name = urlencode($this->request->here);
        	file_put_contents(TMP.$file_name, $html);
        	// 使用wkhtmltopdf命令导出pdf
        	$ret =system("wkhtmltopdf ".(TMP.$file_name)." ".(TMP.$file_name).".pdf",$retval);
        	if($ret!==false){
        		//header('Content-Type:application/pdf');
        		header("Content-Type: application/force-download");
        		header("Content-Disposition: attachment; filename=".basename($this->request->here).'.pdf');
        		echo file_get_contents((TMP.$file_name).".pdf");
        	}
        	else{
        		echo $retval;
        	}
        	exit;
        }

        if(extension_loaded('xhprof') && XHPROF_ON) {
            include_once __DIR__ . '/../lib/Xhprof_lib/utils/xhprof_lib.php';
            include_once __DIR__ . '/../lib/Xhprof_lib/utils/xhprof_runs.php';
            $objXhprofRun = new XHProfRuns_Default();
            $data = xhprof_disable();
            $run_id = $objXhprofRun->save_run($data, "xhprof");
            error_log($run_id.PHP_EOL , 3 , '/tmp/xhprof.log');
        }

        if (Configure::read('Site.openStatic') && $this->modelClass!='Category' && in_array($this->request->params['action'], array('view'))) {
            $html = $this->response->body();
            if (substr($this->request->here, -5) == '.html' && empty($this->request->query)) {
                // when action is view or index,write html cache file on the server.
                // 无html后缀的静态文件在浏览器中无法显示成网页
                App::uses('HtmlCache', 'Lib');
                HtmlCache::writefile($this->request->here, $html);
            }
        }

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


    public function beforeRender() {
    	if (!empty($this->request->params['locale'])) {
    		// 当传入locale为默认内容时，跳转去除locale参数。
    		// [REQUEST_URI] => /saecms/trunk/zh-cn/?helo
    		$url = str_replace($this->request->params['locale'],'',$this->request->url);
    		$url = str_replace('//', '/', $url);
    		$this->set('currentUrl',$url);
    	}
    	else{
    		$this->set('currentUrl', $this->request->url);
    	}
    	$this->set('basedir', $this->request->base);
    	$this->set('site_cate_id',$GLOBALS['site_cate_id']);
        $this->set('CurrentUser', $this->currentUser);
        $this->set('is_admin', $this->is_admin($this->currentUser['id']));
        $this->set('pageTitle', $this->pageTitle);

		// view时，有current_data_id。
        $this->set('current_url', Router::url().'?'.http_build_query($this->request->query));
        $this->set('current_model', $this->modelClass);
        $this->set('current_controller', $this->request->params['controller']);
        $this->set('current_action', $this->action);
        $this->set('current_pass', $this->request->params['pass']);
        $this->set('current_named', $this->request->params['named']);

        $this->set('in_weixin', $this->is_weixin());
        $this->set('wx_follow_url', 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=200769784&idx=1&sn=8cce5a47e8a6123028169065877446b9#rd');
        $this->set('isMobile', $this->RequestHandler->isMobile());
    }

    protected function is_admin($uid) {
        return is_admin_uid($uid);
    }

    /** Also used in view files as a global javascript variable */
    protected function is_weixin(){
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false );
    }

    protected function nick_should_edited($nick) {
        return name_empty_or_weixin($nick);
    }

    /**
     * @return string
     */
    protected function login_link() {
        return '/users/login?force_login=1&auto_weixin=' . $this->is_weixin() . '&referer=' . urlencode($_SERVER['REQUEST_URI']);
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

    public function valifiled($field='') {
        // 直接输出，jsonencode，不需要调用模板
        $this->autoRender = false;
        $modelClass = $this->modelClass;
        //往model中注入值，否则无法验证
        $this->{$modelClass}->set($this->data);
        // 只验证提交过来的单个字段，或几个字段
        $errors = $this->{$modelClass}->invalidFields(array('fieldList' => array_keys($this->data[$modelClass])));
        //$errors = $this->{$modelClass}->invalidFields();
        echo json_encode($errors);
    }

    public function view($slug='/') {
    	$modelClass = $this->modelClass;

        if (empty($slug)) {
            $slug = $this->_getParamVars('slug');
        }
        if (empty($id)) {
            $id = $this->_getParamVars('id');
            if (empty($id)) {
                $id = intval($slug);
            }
        }
    	$this->{$modelClass}->recursive = 1; // 显示时，查询出相关联的数据。

        $cond = array($modelClass . '.deleted' => 0, );
        if ($modelClass != 'Product') {
            $cond[$modelClass . '.published'] = 1;
        }
        if (!empty($slug) && $slug != strval(intval($slug))) {
            $cond[$modelClass . '.slug'] = $slug;
        } elseif ($id) {
            $cond[$modelClass.'.id'] = $id;
        } else {
            $this->redirect(array('action' => 'lists'));
        }
        ${$modelClass} = $this->{$modelClass}->find('first', array(
                'conditions' => $cond,
        ));
        $this->viewdata = ${$modelClass};

        if (empty(${$modelClass})) {
            $url = $this->referer();
            if (empty($url))
                $url = '/';
            throw new NotFoundException();
        }

        $this->loadModel('Uploadfile');
    	${$modelClass}['Uploadfile'] = $this->Uploadfile->find('all',array(
        		'conditions'=> array(
        				'modelclass'=>$modelClass,
        				'data_id'=>${$modelClass}[$modelClass]['id']
        			),
                'order'=> array('sortorder DESC')
        ));
        if(Configure::read($modelClass.'.view_nums')){// 记录访问次数
        	$this->{$modelClass}->updateAll(
        			array('views_count' => 'views_count+1'),
        			array('id'=>${$modelClass}[$modelClass]['id'])
        	);
        }
        // modelSplitOptions,modelSplitSchema 在ModelSplitBehavior->afterFind中的生成
        $this->set($modelClass . 'SplitOptions', $this->{$modelClass}->modelSplitOptions);
        $this->set($modelClass . 'SplitSchema', $this->{$modelClass}->modelSplitSchema);

//		print_r(${$modelClass});
        // 若同时发布到了多个栏目，导航默认只算第一个栏目的
        $current_cateid = ${$modelClass}[$modelClass]['cate_id'];

        $this->loadModel('Category');
        $path_cachekey = 'category_path_'.$current_cateid;
        $navigations = Cache::read($path_cachekey);
        if ($navigations === false) {
        	$navigations = $this->Category->getPath($current_cateid);
        	Cache::write($path_cachekey, $navigations);
        }
        // 去除站点类型的节点
        while($navigations[0]['Category']['model']=='website'){
        	array_shift($navigations);
        }

        $this->set('top_category_id', $navigations[0]['Category']['id']);
        $this->set('top_category_name', $navigations[0]['Category']['name']);
        //seotitle  seodescription  seokeywords
        if (empty(${$modelClass}[$modelClass]['seotitle'])) {
            ${$modelClass}[$modelClass]['seotitle'] = ${$modelClass}[$modelClass]['title'];
        }
        if (empty(${$modelClass}[$modelClass]['seodescription'])) {
            ${$modelClass}[$modelClass]['seodescription'] = trim(${$modelClass}[$modelClass]['summary']);
        }

        ${$modelClass}[$modelClass]['content'] = $this->_lazyloadimg(${$modelClass}[$modelClass]['content']);

        if (${$modelClass}[$modelClass]['seotitle']) {
            $this->pageTitle = ${$modelClass}[$modelClass]['seotitle'];
        } else {
            $this->pageTitle = ${$modelClass}[$modelClass]['name'];
        }
        $this->set('seodescription', ${$modelClass}[$modelClass]['seodescription']);
        $this->set('seokeywords', ${$modelClass}[$modelClass]['seokeywords']);
        $this->set('current_cateid', $current_cateid);
        $this->set('use_stat', 'view'); // view action 使用统计，记录
        $this->current_data_id = ${$modelClass}[$modelClass]['id'];
        $this->set('current_data_id', ${$modelClass}[$modelClass]['id']);
        $this->set('current_model', $modelClass);
        $this->set('navigations', $navigations);
//		print_r( ${$modelClass});
        $this->set($modelClass, ${$modelClass});
//         print_r(${$modelClass});

        $params = array($modelClass, ${$modelClass}[$modelClass]['id']);
        $this->Hook->call('viewItem', $params);
//         $this->Hook->call('nextItems', $params);
    }

    function add() {
        $modelClass = $this->modelClass;
        if (!empty($this->data)) {
        	foreach ($this->data[$modelClass] as &$item){
        		if(is_array($item)){
        			$item = implode(',',$item); // 若提交的内容为数组，则使用逗号连接各项值保存到一个字段里
        		}
        	}
        	if(!isset($this->data[$modelClass]['published'])){
            	$this->data[$modelClass]['published'] = 1;
        	}
            $this->data[$modelClass]['deleted'] = 0;
            $this->data[$modelClass]['creator'] = $this->currentUser['id'];

            //print_r($this->data);
            //exit;
            $this->{$modelClass}->create();
            if ($this->{$modelClass}->save($this->data)) {
                $this->Session->setFlash(__('The Data has been saved'));
                $this->redirect(array('action' => 'mine'));
            } else {
                $this->Session->setFlash(__('The Data could not be saved. Please, try again.'));
            }
        }
    }

    function index() {

    }

    function edit($id) {
        $modelClass = $this->modelClass;
        if (!$id && empty($this->data)) {
            throw new ForbiddenException('Error url');
        }
        $datainfo = $this->{$this->modelClass}->find('first', array('conditions' => array('id' => $id, 'creator' => $this->currentUser['id'])));
        if (empty($datainfo)) {
            throw new ForbiddenException(__('You cannot edit this data'));
        }

        if (!empty($this->data)) {
            $this->autoRender = false;
            $this->data[$modelClass]['creator'] = $this->currentUser['id'];

            if ($this->{$this->modelClass}->save($this->data)) {
                $this->Session->setFlash(__('The Data has been saved'));
                //$this->redirect(array('action'=>'index'));
            } else {
                $this->Session->setFlash(__('The Data could not be saved. Please, try again.'));
            }
            $successinfo = array('success' => __('Edit success'), 'actions' => array('OK' => 'closedialog'));
            echo json_encode($successinfo);
            //return ;
        }
        if (empty($this->data)) {
            $this->data = $datainfo;
            $this->set('id',$id);
        }
    }

    /**
     * 列表
     * @param $slug  为所在类别的slug
     */
    public function lists($slug='') {
        $page = $this->_getParamVars('page');
        $rows = 60;
        $joins = array();

        $conditions = getSearchOptions($this->request->query,$this->modelClass);
        $datalist = $this->{$this->modelClass}->find('all', array(
                    'conditions' => $conditions,
                    'joins' => $joins,
                    'limit' => $rows,
                    'page' => $page,
                        )
        );

        $total = $this->{$this->modelClass}->find('count',
                        array(
                            'conditions' => $conditions,
                            'joins' => $joins,
                        )
        );
        $this->set('modelClass', $this->modelClass);
        $this->set('region_control_name', Inflector::tableize($this->modelClass));
        $this->set('datalist', $datalist);
        $this->set('total', $total);

        $page_navi = getPageLinks($total, $rows, $this->request, $page);
        $this->set('list_page_navi', $page_navi); // page_navi 在region调用中有使用，防止被覆盖，此处使用 list_page_navi
    }

    function __message($message, $url, $seconds=3) {
        $this->layout = 'message';
        $this->set('message', $message);
        $this->set('seconds', $seconds);
        $this->set('url', $url);
        $this->autoRender = false;
        echo $this->render('message');
        exit;
    }

    function renderElement($element) {
    	$this->viewClass='Miao'; //可能会被改成Json，需要强制指定
    	$this->View = $this->_getViewObject();
    	$response =  $this->render($element,false);
//     	var_dump($response);
    	if($response instanceof CakeResponse){
    		return $response->body();
    	}
    	return $response;
    }

    function _lazyloadimg($content) {
        //<img border="1" alt="" id="vimage_3462857" src="/files/remote/2010-10/cnbeta_2038145814065004.jpg" />
        // 双引号，单引号，无引号三种图片类型的代码。
        $content = preg_replace('/<img([^>]+?)src="([^"]*?)"([^>]*?)>/is', "<img \\1src=\"" . Router::url('/img/grey.gif') . "\" class=\"lazy\" data-original=\"\\2\" \\3>", $content);

        $content = preg_replace('/<img([^>]+?)src=\'([^\']+?)\'([^>]+?)>/is', "<img \\1src=\"" . Router::url('/img/grey.gif') . "\" class=\"lazy\" data-original=\"\\2\" \\3>", $content);
        //[^\s"\'] 表示非空、非引号同时成立
        $content = preg_replace('/<img([^>]+?)src=([^\s"\']+?)([^>]+?)>/is', "<img \\1src=\"" . Router::url('/img/grey.gif') . "\" class=\"lazy\" data-original=\"\\2\" \\3>", $content);
//    	echo $content;exit;
        return $content;
    }

    public function logoutCurrUser()
    {
        if ($this->Cookie) {
            $this->Cookie->destroy();
        }
        if ($this->Session) {
            $this->Session->destroy();
        }
        unset($this->currentUser);
    }


    /**
     * @param $current_cateid
     * @param $cateModel
     * @return mixed
     */
    protected function readOrLoadAndCacheNavigations($current_cateid, $cateModel)
    {
        $path_cachekey = 'category_path_' . $current_cateid;
        $navigations = Cache::read($path_cachekey);
        if ($navigations === false) {
            $navigations = $cateModel->getPath($current_cateid);
            Cache::write($path_cachekey, $navigations);
            return $navigations;
        }
        return $navigations;
    }

    /**
     * @param $pid
     * @param $currUid
     * @return array whether afford to current user; limit for current user; total left for all users
     */
    public static function __affordToUser($pid, $currUid) {

        ClassRegistry::init('ShipPromotion');
        if ($pid == ShipPromotion::QUNAR_PROMOTE_ID) {
            $afford_for_curr_user = false;
            return array($afford_for_curr_user, 0);

        } else {
            list($total_limit, $brand_id, $limit_cur_user) = ClassRegistry::init('ShipPromotion')->findNumberLimitedPromo($pid);
            list($afford_for_curr_user, $limit_cur_user, $total_left) = calculate_afford($pid, $currUid, $total_limit, $limit_cur_user);
        }
        $least_num = 1;
        if($pid == 877) { //好好蛋糕，满5起送
            $least_num = 5;
        }
        return array($afford_for_curr_user, $limit_cur_user, $total_left,$least_num);
    }


    /**
     * if $tr_id is empty, redirect with current user's track id;
     * else return user id and whether is self.
     * Throw an exception if the $tr_id cannot be decoded to a valid $uid
     * @param $tr_id
     * @param $curr_uid
     * @return array array of uid, isSelf and track type
     */
    private function check_tr_id($tr_id, $curr_uid) {
        if (!empty($tr_id)) {
            list($uid, $track_type) = $this->decode_apple_tr_id($tr_id);
            if (!empty($uid) && !empty($track_type) && is_numeric($uid)) {
                return array($uid, $uid == $curr_uid, $track_type);
            }
        }
        return false;
    }

    /**
     * @param $uid
     * @param $trackType
     * @param $defUri
     */
    protected function redirect_for_append_tr_id($uid, $trackType, $defUri = '/') {
        $uri = game_uri($trackType, $defUri);
        $encodedTrid = $this->encode_apple_tr_id($uid, $trackType);
            $url = "$uri?trid=" . urlencode($encodedTrid);
        if (!empty($_GET['section'])) {
            $url .= '&section='.$_GET['section'];
        }
            $this->redirect($url);
    }

    protected  function encode_apple_tr_id($id, $trackType) {
        return authcode($id.'-'.$trackType, 'ENCODE', 'GAME_TID');
    }

    /**
     * @param $tr_id
     * @return array|bool if correct return userid string and track type string, otherwise return false
     */
    protected  function decode_apple_tr_id($tr_id) {
        $str = authcode($tr_id, 'DECODE', 'GAME_TID');
        $split = mb_split('-', $str, 2);
        return (!empty($split) && count($split) == 2) ? $split : false;
    }

    protected  function getTrackKey($trackType) {
        return $trackType . ':';
    }

    /**
     * @param $track_type
     * @param $current_uid
     * @param $friendUid
     * @param $dailyHelpLimit
     * @return bool
     */
    private function recordTrack($track_type, $current_uid, $friendUid, $dailyHelpLimit = 0) {

        $shouldAdd = true;
        if ($dailyHelpLimit > 0) {
            $helped = $this->TrackLog->today_helped($track_type, $current_uid);
            if ($helped >= $dailyHelpLimit) {
                $shouldAdd = false;
            }
        }

        $trackLogs = $this->TrackLog->find_track_log($track_type, $current_uid, $friendUid);

        $clientIp = $this->request->clientIp(false);
        $noTrackLogs = empty($trackLogs);
        $shouldAdd = $shouldAdd && ($noTrackLogs || $trackLogs['TrackLog']['got'] == 0);
        if ($noTrackLogs) {
            $toUpdate = array('TrackLog' => array('type' => $track_type, 'got' => $shouldAdd?1:0, 'last_ip' => '\''.$clientIp.'\'', 'from' => $current_uid, 'to' => $friendUid, 'award_time' => date(FORMAT_DATETIME)));
            $this->TrackLog->save($toUpdate);
        } else {
            $updating = array('latest_click_time' => '\'' . date(FORMAT_DATETIME) . '\'', 'last_ip' => '\'' . $clientIp . '\'');
            if ($shouldAdd) {
                $updating['got'] = 1;
                $updating['award_time'] = '\''.date(FORMAT_DATETIME).'\'';
            }
            $this->TrackLog->updateAll($updating, array('id' => $trackLogs['TrackLog']['id']));
        }
        return $shouldAdd;
    }

    /**
     *
     * Check the incoming track code. If it's empty, redirect to current user's link;
     * If it's from self, return (null, false); If it's other's link, return (friend id and shouldAdd), the caller should
     * redirect to current user's link.
     *
     * @param $current_uid
     * @param $default_track_type
     * @param $dailyHelpLimit int daily limit help per day for a user
     * @return array ($friend, $shouldAdd, $trType)
     */
    protected function track_or_redirect($current_uid, $default_track_type, $dailyHelpLimit = 0) {
        $tr_id = $_GET['trid'];
        if (!empty($tr_id)) {
            $this->loadModel('TrackLog');
            list($friendUid, $isSelf, $trType) = $this->check_tr_id($tr_id, $current_uid);
            if ($friendUid && !$isSelf && $trType) {
                $this->loadModel('User');
                $friend = $this->User->findById($friendUid);
                if (!empty($friend)) {
                    $shouldAdd = $this->recordTrack($trType, $current_uid, $friendUid, $dailyHelpLimit);
                    return array($friend, $shouldAdd, $trType);
                }
                //treat as self
                $this->redirect_for_append_tr_id($current_uid, $trType);
            } else if ($isSelf) {
                return array(false, true, $trType);
            }
        }

        $this->redirect_for_append_tr_id($current_uid, $default_track_type);
        return false;
    }


    protected function setHasOfferBrandIds($brandId = null) {
        $this->loadModel('ShareOffer');
        $allValidOffer = $this->ShareOffer->find_all_def_valid_offer($brandId);
        if (!empty($allValidOffer)) {
            $hasOfferBrandIds = Hash::combine($allValidOffer, '{n}.ShareOffer.brand_id');
        }
        $this->set('hasOfferBrandIds', $hasOfferBrandIds);
    }

    protected function setHistory(){
        $history = $_REQUEST['history'];
        if(!$history){
            $history ='/';
        }
        if(!(strpos($history,WX_HOST)>=0)){
            $history='/';
        }
        if($history=='/'){
            if($_REQUEST['tagId']){
                $history=$history.'?tagId='.$_REQUEST['tagId'];
            }
        }
        $this->set('history',$history);
    }

    protected function setTraceFromData($type,$data_id){
        $this->set('from',$type);
        $this->set('data_id',$data_id);
    }

    protected function get_product_consignment_date($pid){
        $consignment_date = get_pure_product_consignment_date($pid);
        if(empty($consignment_date)){
            return null;
        }
        return $this->format_consignment_date($consignment_date);
    }

    protected function format_consignment_date($consignment_date){
        $product_consignment_date = date('m月d日',strtotime($consignment_date));
        $product_consignment_date = $product_consignment_date.'('.day_of_week($consignment_date).')';
        return $product_consignment_date;
    }

    //获取客户端用户IP
    protected function get_ip() {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (!empty($_SERVER["REMOTE_ADDR"])) {
            $cip = $_SERVER["REMOTE_ADDR"];
        } else {
            $cip = "0.0.0.0";
        }
        return $cip;
    }

    protected function get_post_raw_data(){
        $postStr = file_get_contents('php://input');
        $postData = json_decode($postStr, true);
        return $postData;
    }

    protected function set_current_user_by_access_token(){
        $access_token = $_REQUEST['access_token'];
        if (!empty($access_token)) {
            $this->OAuth = $this->Components->load('OAuth.OAuth');
            $this->currentUser = $this->OAuth->user();
        }
    }

}
?>