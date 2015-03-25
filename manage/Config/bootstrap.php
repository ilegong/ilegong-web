<?php
if(!defined('APP_PATH')){
	define('APP_PATH', ROOT . DS . 'manage' . DS);
}
if(!defined('APP_SUB_DIR')){
	define('APP_SUB_DIR', '');
}
if(php_sapi_name()==='cli' && empty($_GET)){
	$_SERVER['SERVER_ADDR']='127.0.0.1';
}
// 变量混淆加密，不支持 global，而使用$GLOBALS
// 变量混淆加密，不支持extract方法，要使用数组方式来使用变量

$GLOBALS['hookvars']['navmenu'] = array();
$GLOBALS['hookvars']['submenu'] = array();
Configure::write('Hook.helpers.Miao','MiaoHook');
Configure::write('Hook.components.Miao','MiaoHook');

define('COMMON_PATH', ROOT . DS . 'lib' . DS);
include_once COMMON_PATH.'bootstrap.php';

const PRODUCT_ID_CAOMEI = 838;//草莓
const PRODUCT_ID_MANGUO = 851;//海南空运芒果
