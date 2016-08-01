<?php

/**
 * Index
 *
 * The Front Controller for handling every request
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.webroot
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

if (getenv('TONGSHIJIA_ENV') == 'product') {
    define('STATIC_HOST', 'http://static.tongshijia.com');
    define('IMAGES_HOST', 'http://images.tongshijia.com');
    define('JPUSH_APP_KEY', '32a7a17d552b6dd3d7736c72');
    define('JPUSH_APP_SECRET', '08be41e830d5faf1d5f6b660');
    define('MYSQL_SERVER_HOST', 'db.tongshijia.com');
    define('MEMCACHE_HOST', 'mem.tongshijia.com');
    define('REDIS_HOST', 'redis.tongshijia.com');
} elseif (getenv('TONGSHIJIA_ENV') == 'test') {
    define('STATIC_HOST', 'http://static-test.tongshijia.com');
    define('IMAGES_HOST', 'http://images-test.tongshijia.com');
    define('JPUSH_APP_KEY', 'dca84c4492a450f738918b65');
    define('JPUSH_APP_SECRET', '376cf6f26a72c31ca769da44');
    define('MYSQL_SERVER_HOST', 'test.tongshijia.com');
    define('MEMCACHE_HOST', 'test.tongshijia.com');
    define('REDIS_HOST', 'test.tongshijia.com');
} else {
    define('STATIC_HOST', 'http://dev.tongshijia.com');
    define('IMAGES_HOST', 'http://dev.tongshijia.com');
    define('JPUSH_APP_KEY', 'dca84c4492a450f738918b65');
    define('JPUSH_APP_SECRET', '376cf6f26a72c31ca769da44');
    define('MYSQL_SERVER_HOST', '127.0.0.1');
    define('MEMCACHE_HOST', '127.0.0.1');
    define('REDIS_HOST', '127.0.0.1');
}

/**
 * Use the DS to separate the directories in other defines
 */

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
/**
 * These defines should only be edited if you have cake installed in
 * a directory layout other than the way it is distributed.
 * When using custom settings be sure to use the DS and do not add a trailing DS.
 */
/**
 * The full path to the directory which holds "app", WITHOUT a trailing DS.
 *
 */
if (!defined('ROOT')) {
    define('ROOT', dirname(dirname(__FILE__)));
}
/**
 * The actual directory name for the "app".
 *
 */
if (!defined('APP_DIR')) {
    define('APP_DIR', basename(dirname(__FILE__)));
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
    define('WEBROOT_DIR', 'webroot');
}
if (!defined('WWW_ROOT')) {
    define('WWW_ROOT', ROOT . DS . 'webroot' . DS);
}
if (!defined('CORE_PATH')) {
    define('APP_PATH', ROOT . DS . APP_DIR . DS);
    define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
}

define('VIEWS', APP_PATH . 'View' . DS);

define('SITE_VIEWS', ROOT . DS. 'app' . DS.'View'.DS);  // 前台站点的模版路径

$app_sub_dir = dirname($_SERVER['PHP_SELF']);
/**
 * ADMIN_SUB_DIR 应用所在的二级目录,包含manage，用在在js中拼接后台php的访问路径
 * @var APP_SUB_DIR
 */
define('ADMIN_SUB_DIR', $app_sub_dir);

if(basename($app_sub_dir)=='manage'){
	$app_sub_dir = dirname($app_sub_dir);
}

if(!empty($app_sub_dir) && strlen($app_sub_dir)>1){ // $app_sub_dir not \ and /
	/**
	 * 图片，js，css在根目录，php在manage目录
	 * @var unknown_type
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
    define('TMP', 'saemc://'.$_SERVER['HTTP_APPVERSION'].'/manage/tmp/');
} else {
    define('TMP', ROOT . DS . 'data' . DS);
}

if (!include(CORE_PATH . 'Cake' . DS . 'bootstrap.php')) {
    trigger_error("CakePHP core could not be found.  Check the value of CAKE_CORE_INCLUDE_PATH in APP/webroot/index.php.  It should point to the directory containing your " . DS . "cake core directory and your " . DS . "vendors root directory.", E_USER_ERROR);
}

if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] == '/favicon.ico') {
    return;
}



//	App::uses('Dispatcher', 'Routing');
App::uses('AppDispatcher', 'Lib');
$Dispatcher = new AppDispatcher();
$request = new CakeRequest();
// RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
// $_GET['url'] is passed from rewrite rules. where make request obj ,should unset $request->query['url'].this would make mistakes
$Dispatcher->dispatch($request, new CakeResponse(array('charset' => Configure::read('App.encoding'))));


