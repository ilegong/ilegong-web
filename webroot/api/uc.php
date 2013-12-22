<?php
/**
 * UCenter Ӧ�ó��򿪷� API Example
 *
 * ���ļ�Ϊ api/uc.php �ļ��Ŀ����������û����� UCenter ֪ͨ��Ӧ�ó��������
 */
define('UC_VERSION', '1.0.0');		//UCenter �汾��ʶ

define('API_DELETEUSER', 1);		//�û�ɾ�� API �ӿڿ���
define('API_GETTAG', 1);		//��ȡ��ǩ API �ӿڿ���
define('API_SYNLOGIN', 1);		//ͬ����¼ API �ӿڿ���
define('API_SYNLOGOUT', 1);		//ͬ���ǳ� API �ӿڿ���
define('API_UPDATEPW', 1);		//�����û����� ����
define('API_UPDATEBADWORDS', 1);	//���¹ؼ����б� ����
define('API_UPDATEHOSTS', 1);		//���������������� ����
define('API_UPDATEAPPS', 1);		//����Ӧ���б� ����
define('API_UPDATECLIENT', 1);		//���¿ͻ��˻��� ����
define('API_UPDATECREDIT', 1);		//�����û����� ����
define('API_GETCREDITSETTINGS', 1);	//�� UCenter �ṩ�������� ����
define('API_UPDATECREDITSETTINGS', 1);	//����Ӧ�û������� ����

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');


if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('ROOT')) {
    define('ROOT', dirname(dirname(dirname(__FILE__))));
}

if (!defined('APP_DIR')) {
	define('APP_DIR', 'app');
}

if (!defined('CAKE_CORE_INCLUDE_PATH')) {
    define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'lib');
}

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
define('COMMON_PATH', ROOT . DS . 'lib' . DS);
define('VIEWS', APP_PATH . 'View' . DS);


if (defined('SAE_MYSQL_DB')) {
    define('TMP', 'saemc://manage/tmp/');
} else {
    define('TMP', ROOT . DS . 'data' . DS);
}
if (!include(CORE_PATH . 'Cake' . DS . 'bootstrap.php')) {
    trigger_error("CakePHP core could not be found.  Check the value of CAKE_CORE_INCLUDE_PATH in APP/webroot/index.php.  It should point to the directory containing your " . DS . "cake core directory and your " . DS . "vendors root directory.", E_USER_ERROR);
}

if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] == '/favicon.ico') {
	return;
}

App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('Debugger', 'Utility');
App::uses('CakeEvent', 'Event');
App::uses('CakeEventManager', 'Event');
App::uses('CakeEventListener', 'Event');
App::uses('AppController', 'Controller');
App::uses('UsersController', 'Controller');
if(isset($_GET['url'])){
	$request = new CakeRequest($_GET['url']);
}
else{
	$request = new CakeRequest();
}
unset($request->query['url']);
$response = new CakeResponse(array("charset" => Configure::read("App.encoding")));
$controller = new UsersController($request, $response);

$controller->constructClasses();
$controller->startupProcess();



error_reporting (E_ALL ^ E_NOTICE);

define('DISCUZ_ROOT',dirname(dirname( __FILE__ )).'/');
define('UC_CLIENT_ROOT', DISCUZ_ROOT.'/lib/Vendor/uc_client/');

$code = $_GET['code'];
parse_str(authcode($code, 'DECODE', UC_KEY), $get);

//if(time() - $get['time'] > 3600) {
//	exit('Authracation has expiried');
//}
if(empty($get)) {
	exit('Invalid Request');
}
$action = $get['action'];
$timestamp = time();


if($action == 'test') {

	exit(API_RETURN_SUCCEED);

} elseif($action == 'deleteuser') {
	!API_DELETEUSER && exit(API_RETURN_FORBIDDEN);

	$uids = explode(',',str_replace("'", '', stripslashes($get['ids'])));
	if($controller->User->deleteAll(array('id' => $uids))){
		exit(API_RETURN_SUCCEED);
	}
	else{
		exit(API_RETURN_FAILED);
	}
	

} elseif($action == 'gettag') {

	!API_GETTAG && exit(API_RETURN_FORBIDDEN);

	//��ȡ��ǩ API �ӿ�
	exit(API_RETURN_SUCCEED);

} elseif($action == 'synlogin' && $_GET['time'] == $get['time']) {

	!API_SYNLOGIN && exit(API_RETURN_FORBIDDEN);
	$uid = intval($get['uid']);
	$loginstatus = -1;
	
	$Userinfo = $controller->User->find('first',array('conditions'=>array('id' => $uid,'username' => $get['username'],'status'=>1),'recursive' => -1));
	if(empty($Userinfo)){
		$user = array();
        $user['User'] = array(
            'id'=> $get['uid'],
            'username'=> $get['username'],
            'nickname'=> $get['username'],
            'email' => $get['email'],
            'password' => $get['password'],
            'status'=> 1,
            'activation_key' => md5(uniqid()),
            'last_login'=> date('Y-m-d H:i:s'),
        );
        $controller->User->create();
        if($controller->User->save($user)){
        	$Userinfo = $user;
        }
	}
	else{
		$Userinfo['User']['last_login']= date('Y-m-d H:i:s');
		$controller->User->save($Userinfo);
	}
	header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	if ($Userinfo) {
		$controller->Session->renew();
		$controller->Session->write('Auth.User', $Userinfo['User']);
	}
	
	echo '1';	
} elseif($action == 'synlogout') {

	!API_SYNLOGOUT && exit(API_RETURN_FORBIDDEN);
	
	//ͬ���ǳ� API �ӿ�
	header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
	$controller->Session->destroy();

} elseif($action == 'updateclient') {

	!API_UPDATECLIENT && exit(API_RETURN_FORBIDDEN);

	//���¿ͻ��˻���
	exit(API_RETURN_SUCCEED);

} elseif($action == 'updatepw') {

	!API_UPDATEPW && exit(API_RETURN_FORBIDDEN);

	//�����û�����
	exit(API_RETURN_SUCCEED);

} elseif($action == 'updatebadwords') {

	!API_UPDATEBADWORDS && exit(API_RETURN_FORBIDDEN);

	//���¹ؼ����б�
	exit(API_RETURN_SUCCEED);

} elseif($action == 'updatehosts') {

	!API_UPDATEHOSTS && exit(API_RETURN_FORBIDDEN);

	//����HOST�ļ�
	exit(API_RETURN_SUCCEED);

} elseif($action == 'updateapps') {

	!API_UPDATEAPPS && exit(API_RETURN_FORBIDDEN);

	//����Ӧ���б�
	exit(API_RETURN_SUCCEED);

} elseif($action == 'updateclient') {

	!API_UPDATECLIENT && exit(API_RETURN_FORBIDDEN);

	//���¿ͻ��˻���
	exit(API_RETURN_SUCCEED);

} elseif($action == 'updatecredit') {

	!UPDATECREDIT && exit(API_RETURN_FORBIDDEN);

	//�����û�����
	exit(API_RETURN_SUCCEED);

} elseif($action == 'getcreditsettings') {

	!GETCREDITSETTINGS && exit(API_RETURN_FORBIDDEN);

	//�� UCenter �ṩ��������
	echo uc_serialize($credits);

} elseif($action == 'updatecreditsettings') {

	!API_UPDATECREDITSETTINGS && exit(API_RETURN_FORBIDDEN);

	//����Ӧ�û�������
	exit(API_RETURN_SUCCEED);

} else {

	exit(API_RETURN_FAILED);

}
$controller->shutdownProcess();

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {

	$ckey_length = 4;

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}

if(!function_exists('uc_serialize')){
	function uc_serialize($arr, $htmlon = 0) {
		include_once UC_CLIENT_ROOT.'./lib/xml.class.php';
		return xml_serialize($arr, $htmlon);
	}
}
if(!function_exists('uc_unserialize')){
	function uc_unserialize($s) {
		include_once UC_CLIENT_ROOT.'./lib/xml.class.php';
		return xml_unserialize($s);
	}
}