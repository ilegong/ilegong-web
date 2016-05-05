<?php

define('DEFAULT_SITE_HOST','www.tongshijia.com');
define('SH_SITE_HOST','sh.tongshijia.com');

if($_SERVER['HTTP_HOST'] == SH_SITE_HOST){
    define('WX_APPID', 'wx65d0c25ec24bb4c2');
    define('WX_APPID_SOURCE', 'wx65d0');
    define('WX_SECRET', '36dac48558049698efe1d2da8b1ee904');
    define('WX_SERVICE_ID_NAME', '朋友说上海');
    define('WX_SERVICE_ID_NO', 'pyshuo01');
    define('CACHE_PREFIX' , 'sh');
    define('WX_HOST',SH_SITE_HOST);
}else{
    define('WX_APPID', 'wxca7838dcade4709c');
    define('WX_APPID_SOURCE', 'wxca78');
    define('WX_SECRET', '79b787ec8f463eeb769540464c9277b2');
    define('WX_SERVICE_ID_NAME', '朋友说');
    define('WX_SERVICE_ID_NO', 'pyshuo2014');
    define('CACHE_PREFIX' , '');
    define('WX_HOST', DEFAULT_SITE_HOST);
    define('WX_SERVICE_ID_GOTO', 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=200757804&idx=1&sn=90b121983525298a4ac26ee8d6c0bc1c#rd');
}
define('WX_JS_API_CALL_URL', 'http://'.WX_HOST.'/wxPay/jsApiPay');
define('WX_JS_LOGISTICS_API_CALL_URL', 'http://'.WX_HOST.'/wxPay/logistics_order_pay');
define('WX_NOTIFY_URL', 'http://'.WX_HOST.'/wxPay/notify.html');
define('WX_LOGISTICS_NOTIFY_URL', 'http://'.WX_HOST.'/wxPay/logistics_notify.html');

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('ROOT')) {
    define('ROOT', dirname(dirname(__FILE__)));
}
/**
 * The actual directory name for the "app".
 *
 */
// if (!defined('APP_DIR')) {
//     define('APP_DIR', 'app');
// }
if (!defined('APP_DIR')) {
    define('APP_DIR', 'app');
}
/**
 * The absolute path to the "cake" directory, WITHOUT a trailing DS.
 *
 */
if (!defined('CAKE_CORE_INCLUDE_PATH')) {
    define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'lib');
}

/**
 * Editing below this line should NOT be necessary.
 * Change at your own risk.
 *
 */
if (!defined('WEBROOT_DIR')) {
    define('WEBROOT_DIR', 'webroot' );
}
if (!defined('WWW_ROOT')) {
    define('WWW_ROOT', ROOT . DS . 'webroot' . DS);
}
if (!defined('CORE_PATH')) {
    define('APP_PATH', ROOT . DS . APP_DIR . DS);
    define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
}
define('VIEWS', APP_PATH . 'View' . DS);
$app_sub_dir = dirname($_SERVER['PHP_SELF']);
if(basename($app_sub_dir)=='webroot'){
    $app_sub_dir = dirname($app_sub_dir);
}

if(!empty($app_sub_dir) && strlen($app_sub_dir)>1){ // skip / in linux or \ in windows
    /**
     * APP_SUB_DIR 应用所在的二级目录
     * @var APP_SUB_DIR
     */
    define('APP_SUB_DIR', $app_sub_dir);
    define('IMAGES_URL', $app_sub_dir.'/img/');
    define('CSS_URL', $app_sub_dir.'/css/');
    define('JS_URL', $app_sub_dir.'/js/');
}
else{
    define('APP_SUB_DIR', '');
    define('IMAGES_URL', '/img/');
    define('CSS_URL', '/css/');
    define('JS_URL', '/js/');
}

if (defined('SAE_MYSQL_DB')) {
    define('TMP', 'saemc://'.$_SERVER['HTTP_APPVERSION'].'/tmp/');
} else {
    define('TMP', ROOT . DS . 'data' . DS);
}


if (getenv('TONGSHIJIA_ENV') == 'product') {
    define('STATIC_HOST', 'http://static.tongshijia.com');
    define('IMAGES_HOST', 'http://images.tongshijia.com');
    define('JPUSH_APP_KEY', '32a7a17d552b6dd3d7736c72');
    define('JPUSH_APP_SECRET', '08be41e830d5faf1d5f6b660');
    define('JPUSH_IS_PRODUCT', true);
    //define('JPUSH_APP_KEY', 'dca84c4492a450f738918b65');
    //define('JPUSH_APP_SECRET', '376cf6f26a72c31ca769da44');
    define('MYSQL_SERVER_HOST', 'db.tongshijia.com');
    define('MEMCACHE_HOST', 'mem.tongshijia.com');
    define('REDIS_HOST', 'redis.tongshijia.com');
} elseif (getenv('TONGSHIJIA_ENV') == 'test') {
    define('STATIC_HOST', 'http://static-test.tongshijia.com');
    define('IMAGES_HOST', 'http://images-test.tongshijia.com');
    define('JPUSH_APP_KEY', 'dca84c4492a450f738918b65');
    define('JPUSH_APP_SECRET', '376cf6f26a72c31ca769da44');
    define('JPUSH_IS_PRODUCT', false);
    define('MYSQL_SERVER_HOST', 'test.tongshijia.com');
    define('MEMCACHE_HOST', 'test.tongshijia.com');
    define('REDIS_HOST', 'test.tongshijia.com');
} else {
    define('STATIC_HOST', 'http://dev.tongshijia.com');
    define('IMAGES_HOST', 'http://dev.tongshijia.com');
    define('JPUSH_APP_KEY', 'dca84c4492a450f738918b65');
    define('JPUSH_APP_SECRET', '376cf6f26a72c31ca769da44');
    define('JPUSH_IS_PRODUCT', false);
    define('MYSQL_SERVER_HOST', '127.0.0.1');
    define('MEMCACHE_HOST', '127.0.0.1');
    define('REDIS_HOST', '127.0.0.1');
}

if (!include(CORE_PATH . 'Cake' . DS . 'bootstrap.php')) {
    trigger_error("CakePHP core could not be found.  Check the value of CAKE_CORE_INCLUDE_PATH in APP/webroot/index.php.  It should point to the directory containing your " . DS . "cake core directory and your " . DS . "vendors root directory.", E_USER_ERROR);
}

if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] == '/favicon.ico') {
    return;
}

App::uses('Dispatcher', 'Routing');
$Dispatcher = new Dispatcher();

//replace '/Taobao' to '/taobao'.
if(strpos($_SERVER['REQUEST_URI'],'/Taobao')!==false){
    $_SERVER['REQUEST_URI'] = str_replace('/Taobao','/taobao',$_SERVER['REQUEST_URI'] );
    header('location:'.$_SERVER['REQUEST_URI']);
    exit;
}

if(isset($_GET['url'])){
    $request = new CakeRequest($_GET['url']);
}
else{
    $request = new CakeRequest();
}
unset($request->query['url']);


// print_r($request);exit;

// echo CSS_URL;exit;xyz
// RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
// $_GET['url'] is passed from rewrite rules. where make request obj ,should unset $request->query['url'].this would make mistakes

$Dispatcher->dispatch($request, new CakeResponse(array('charset' => Configure::read('App.encoding'))));