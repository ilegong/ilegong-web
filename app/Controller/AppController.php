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

    public $admins = array('753', '632', '658', '146', '8', '141', '818', '819', '755', '773');

    // 自定义构造函数
    public function __construct($request = null, $response = null) {
        if ($request instanceof CakeRequest) {
            $this->name = $request['params']['controller'];
        }
        
        if (substr($_SERVER['REQUEST_URI'], 0, 11) == '/index.php/') {
            header('location:' . substr($_SERVER['REQUEST_URI'], 10));
            exit;
        }
        $request->webroot = '/';  // webroot目录直接放在根目录
        
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

        if ($this->RequestHandler->isMobile()) {
            //TODO: cache the result
            $this->loadModel('ProductTag');
            $productTags = $this->ProductTag->find('all', array(
                'conditions' => array('published' => 1),
                'order' => 'priority desc'
            ));
            $this->set('productTags', $productTags);
        }
    
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
            if ($this->is_pengyoushuo_com_cn()) {
                $this->layout = 'pengyoushuo_com_cn';
            }
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
    }

    protected function is_admin($uid) {
        return $uid && false !== array_search($uid, $this->admins, true);
    }

    /** Also used in view files as a global javascript variable */
    protected function is_weixin(){
        return (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false );
    }

    protected function nick_should_edited($nick) {
        $nick = trim($nick);
        return (!$nick || strpos($nick, '微信用户') === 0);
    }

    protected function is_pengyoushuo_com_cn() {
        return (strpos($_SERVER['HTTP_HOST'], 'www.pengyoushuo.com.cn') !== false);
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
        
        if (!empty($slug) && $slug != strval(intval($slug))) {
            ${$modelClass} = $this->{$modelClass}->find('first', array(
                    'conditions' => array($modelClass.'.published' => 1, $modelClass.'.deleted' => 0, $modelClass.'.slug' => $slug),
            ));
        } elseif ($id) {
            ${$modelClass} = $this->{$modelClass}->find('first', array(
                    'conditions' => array($modelClass.'.published' => 1, $modelClass.'.deleted' => 0, $modelClass.'.id' => $id),
            ));
        } else {
            $this->redirect(array('action' => 'lists'));
        }
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

        if ($modelClass == 'Product') {

            $pid = $this->current_data_id;
            if ($pid == PRODUCT_ID_RICE_10) {

                $current_uid = $this->currentUser['id'];
                if (!$current_uid) {
                    $this->redirect('/users/login?referer='.$_SERVER['QUERY_STRING']);
                }
                $uri = "/products/" . date('Ymd', strtotime(${$modelClass}[$modelClass]['created'])) . "/$slug.html";
                list($friend, $shouldAdd) = $this->track_or_redirect($uri, $current_uid, 'rebate_'.PRODUCT_ID_RICE_10);
                if ($shouldAdd) {
                    //$this->AwardInfo->updateAll(array('times' => 'times + 1',), array('uid' => $friend['User']['id']));
                }
            }

            $currUid = $this->currentUser['id'];
            list($afford_for_curr_user, $limit_per_user, $total_left) = self::__affordToUser($pid, $currUid);
            if ($limit_per_user) {
                $this->set('limit_per_user', $limit_per_user);
            }
            $this->set('total_left', $total_left);
            $this->set('afford_for_curr_user', $afford_for_curr_user);


        }
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
        $this->Cookie->destroy();
        $this->Session->destroy();
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
        $afford_for_curr_user = true;
        $total_left = -1;
        $cartModel = ClassRegistry::init('Cart');
        ClassRegistry::init('ShipPromotion');
        if ($pid == ShipPromotion::QUNAR_PROMOTE_ID) {
            $afford_for_curr_user = false;
            return array($afford_for_curr_user, 0);
        } else {

            list($total_limit, $brand_id, $limit_per_user) = ClassRegistry::init('ShipPromotion')->findNumberLimitedPromo($pid);
            if ($total_limit != 0  || $limit_per_user != 0) {
                $rtn = $cartModel->find('first', array(
                    'joins' => array(array(
                        'table' => 'orders',
                        'alias' => 'Order',
                        'type' => 'inner',
                        'conditions' => array('Order.id=Cart.order_id', 'Order.status != '.ORDER_STATUS_CANCEL, 'Order.status != '.ORDER_STATUS_WAITING_PAY),
                    )),
                    'fields' => 'SUM(Cart.num) as total_num',
                    'conditions' => array('Cart.order_id > 0', 'Cart.product_id' => $pid, 'Cart.deleted' => 0)));
                $soldCnt = empty($rtn) ? 0 : $rtn[0]['total_num'];

                if ($soldCnt > $total_limit) {
                    $afford_for_curr_user = false;
                } else if ($limit_per_user > 0 ) {

                    $ordersModel = ClassRegistry::init('Order');
                    $order_ids = $ordersModel->find('list', array(
                        'conditions' => array('brand_id' => $brand_id,
                            'deleted' => 0,
                            'published' => 1,
                            'creator' => $currUid,
                            'not' => array('status' => array(ORDER_STATUS_CANCEL))
                        ),
                        'fields' => array('id', 'id')
                    ));
                    if (!empty($order_ids)) {
                        $rr = $cartModel->find('first', array(
                            'conditions' => array('order_id' => $order_ids, 'product_id' => $pid, 'deleted' => 0),
                            'fields' => array('sum(num) as total_num')
                        ));
                        $bought_by_curr_user = empty($rr) ? 0 : $rr[0]['total_num'];
                        if ($bought_by_curr_user >= $limit_per_user) {
                            $afford_for_curr_user = false;
                        }
                        $limit_per_user = $limit_per_user - $bought_by_curr_user;
                    }
                }
                $total_left =  $total_limit - $soldCnt;
                if ($total_left < 0 ) {
                    $total_left = 0;
                }
            }
        }

        return array($afford_for_curr_user, $limit_per_user, $total_left);
    }


    /**
     * if $tr_id is empty, redirect with current user's track id;
     * else return user id and whether is self.
     * Throw an exception if the $tr_id cannot be decoded to a valid $uid
     * @param $tr_id
     * @param $uri
     * @param $curr_uid
     * @param $track_type
     * @return array
     */
    protected function check_tr_id($tr_id, $uri, $curr_uid, $track_type) {
        if (empty($tr_id)) {
            $this->redirect_for_append_tr_id($uri, $curr_uid, $track_type);
        }
        if (!empty($tr_id)) {
            $uid = $this->decode_apple_tr_id($tr_id, $track_type);
            if ($uid && is_numeric($uid)) {
                return array($uid, $uid === $curr_uid);
            } else {
                return array(false, false);
            }
        }
    }

    /**
     * @param $uri
     * @param $uid
     * @param $trackType
     */
    protected function redirect_for_append_tr_id($uri, $uid, $trackType) {
        $encodedTrid = $this->encode_apple_tr_id($uid, $trackType);
        $this->redirect("$uri?trid=".urlencode($encodedTrid));
    }

    protected  function encode_apple_tr_id($id, $trackType) {
        $code = authcode($id, 'ENCODE', $trackType);
        return $code;
    }

    protected  function decode_apple_tr_id($tr_id, $trackType) {
        return authcode($tr_id, 'DECODE', $trackType);
    }

    protected  function getTrackKey($trackType) {
        return $trackType . ':';
    }

    /**
     * @param $track_type
     * @param $current_uid
     * @param $friendUid
     * @return bool
     */
    private function track($track_type, $current_uid, $friendUid) {
        $trackLogs = $this->TrackLog->find('first', array(
            'conditions' => array('type' => $track_type, 'from' => $current_uid, 'to' => $friendUid),
            'fields' => array('id',)
        ));
        $shouldAdd = empty($trackLogs);
        if ($shouldAdd) {
            $toUpdate = array('TrackLog' => array('type' => $track_type, 'from' => $current_uid, 'to' => $friendUid, 'award_time' => date(FORMAT_DATETIME)));
            $this->TrackLog->save($toUpdate);
        } else {
            $this->TrackLog->updateAll(array('latest_click_time' => '\''.date(FORMAT_DATETIME).'\''), array('id' => $trackLogs['TrackLog']['id']));
        }
        return $shouldAdd;
    }

    /**
     * @param $uri
     * @param $current_uid
     * @param $track_type
     * @return array
     */
    protected function track_or_redirect($uri, $current_uid, $track_type) {
        $tr_id = $_GET['trid'];
        if (empty($tr_id)) {
            $this->redirect_for_append_tr_id($uri, $current_uid, $track_type);
            exit;
        }
        $this->loadModel('TrackLog');
        list($friendUid, $isSelf) = $this->check_tr_id($tr_id, $uri, $current_uid, $track_type);
        if (!$isSelf) {
            $this->loadModel('User');
            $friend = $this->User->findById($friendUid);
            if (!empty($friend)) {
                $shouldAdd = $this->track($track_type, $current_uid, $friendUid);
                return array($friend, $shouldAdd);
            }
            //treat as self
            $this->redirect_for_append_tr_id($uri, $current_uid, $track_type);
        }
        return array(null, false);
    }
}
?>