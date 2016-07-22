<?php

class AppController extends Controller {

    public $theme = 'default';
    public $viewClass = 'Miao';
    var $helpers = array(
        'Html', 'Session', 'Js','Hook' => array(),
    ); //'Oauth.OauthHook'
    public $__viewFileName = '';
    /*AuthComponent 移到UsersController(或其它需要控制访问权限的controller)中，AppController使用$this->Session->read('Auth.User')获取用户*/
    var $components = array(
    	'RequestHandler','Session', 'Hook' => array(),
    );
    var $uses = array('Setting');
    var $currentUser = null;
    var $pageTitle;
    // 自定义构造函数
    public function __construct($request = null, $response = null) {
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
    	$this->Setting->writeConfiguration();
    	$site_info = Configure::read('Site');
    	$this->set('site', $site_info);
    	$GLOBALS['site_cate_id'] = Configure::read('Site.default_site_cate_id');
    	// 无Session，且有Cookie登录信息时，解析cookie生成信息。否则忽略cookie，防止每次都要消耗性能解密cookie
    	// 其余时间使用session。
        $access_token = $_REQUEST['access_token'];
        if (!empty($access_token)) {
            $this->OAuth = $this->Components->load('OAuth.OAuth');
            $this->Cookie = $this->Components->load('Cookie', array('name' => 'SAECMS', 'time' => '+2 weeks'));
            $this->OAuth->allow([]);
            $this->currentUser = $this->OAuth->user();
            $this->Session->write('Auth.User', $this->currentUser);
            $this->Cookie->write('Auth.User', $this->currentUser, true, 3600 * 24 * 7);
        } else {
            if (!$this->Session->read('Auth.User.id') && isset($_COOKIE['SAECMS']) && $_COOKIE['SAECMS']['Auth']['User']) {
                $this->Cookie = $this->Components->load('Cookie', array('name' => 'SAECMS', 'time' => '+2 weeks'));
                $user = $this->Cookie->read('Auth.User');
                if (is_array($user) && intval($user['id']) > 0) {
                    $this->loadModel('User');
                    $this->User->recursive = -1;
                    $data = $this->User->find('first', array('conditions' => array('id' => $user['id'])));
                    $this->Session->write('Auth.User', $data['User']);
                } else {
                    $this->Cookie->delete('Auth.User');//删除解密错误的cookie信息
                }
            }
        }

//        if(!Configure::read('Site.status')){
//    		$this->layout = 'maintain';
//    		$this->autoRender = false;
//    		echo $this->render('message');
//    		exit;
//    	}

    	$this->currentUser = $this->Session->read('Auth.User');
        if(!empty($this->currentUser) && $this->currentUser['id'] == 891157){
            throw new RuntimeException('System error');
        }

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

        if($this->is_weixin()){
            if (empty($this->currentUser) && $this->is_weixin() && !in_array($this->request->params['controller'], array('users', 'check'))) {
                $this->redirect($this->login_link());
            }
        }

        if ($_GET['share_type'] && $_GET['trstr']){
            $this->__log_wx_str();
        }

        //set static file path
        $this->set('STATIC_HOST', STATIC_HOST);
        $this->set('IMAGES_HOST', IMAGES_HOST);
    }


    private function __log_wx_str(){
        $share_type = $_GET['share_type'];
        $trstr = $_GET['trstr'];
        if ($share_type != 'timeline' && $share_type != 'appMsg') {
            $this->log("WxShare: type wrong");
            return;
        }
        $type = $share_type == 'timeline' ? 1 : 0;
        $decode_string = authcode($trstr, 'DECODE', 'SHARE_TID');
        $str = explode('-', $decode_string);
        $data_str = explode('_', $str[3]);
        if ($str[2] != 'rebate') {
            $this->log("WxShare: PRODUCT_KEY WRONG", LOG_WARNING);
            return;
        }
        $data_type = $data_str[0];
        $sharer = intval($str[0]);
        $created = intval($str[1]);
        $clicker = $this->currentUser['id'];
        $clicker = $clicker == null ? 0 : $clicker;
        if ($clicker != $sharer) {
            $this->loadModel('ShareTrackLog');
            $data = array('sharer' => $sharer, 'clicker' => $clicker, 'share_time' => $created, 'click_time' => time(), 'data_type' => $data_type, 'data_id' => intval($data_str[1]), 'share_type' => $type);
            $this->ShareTrackLog->save($data);
        }
    }

    public function afterFilter() {

    }

    public function beforeRender() {
        if ($this->autoRender) {
            if ($this->is_weixin() && !empty($this->currentUser['id'])) {
                $this->set('jWeixinOn', true);
                $this->loadModel('WxOauth');
                $signPackage = $this->WxOauth->getSignPackage();
                $this->set('signPackage', $signPackage);
            }
            $this->set('basedir', $this->request->base);
            $this->set('site_cate_id', $GLOBALS['site_cate_id']);
            $this->set('CurrentUser', $this->currentUser);
            $this->set('is_admin', $this->is_admin($this->currentUser['id']));
            $this->set('pageTitle', $this->pageTitle);
            // view时，有current_data_id。
            $this->set('current_url', Router::url() . '?' . http_build_query($this->request->query));
            $this->set('current_controller', $this->request->params['controller']);
            $this->set('current_action', $this->action);
            $this->set('current_pass', $this->request->params['pass']);
            $this->set('current_named', $this->request->params['named']);
            $this->set('in_weixin', $this->is_weixin());
            $this->set('wx_follow_url', WX_SERVICE_ID_GOTO);
            $this->set('isMobile', $this->RequestHandler->isMobile());
        }
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