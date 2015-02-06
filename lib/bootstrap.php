<?php
include_once COMMON_PATH.'Config'.DS.'extend.php';

include_once COMMON_PATH.'global_function.php';

error_reporting(E_ERROR | E_WARNING | E_PARSE);

const ORDER_TYPE_DEF = 1;
const ORDER_TYPE_GROUP = 2;
const ORDER_TYPE_GROUP_FILL = 4; //团购补充素有剩下的
const ORDER_TYPE_PARENT = 3;

const ALI_PAY_TYPE_WAP = "wap";
const ALI_PAY_TYPE_WAPAPP = "wapapp";
const ALI_PAY_TYPE_PC = "pc";

const SCORE_ORDER_COMMENT = 1;
const SCORE_NEW_USER = 2;
const SCORE_ORDER_DONE = 3;
const SCORE_ORDER_SPENT = 4;

const SPEICAL_LIST_DAILY_ID = 4;

const ASSET_SXA_DOMAIN = 's.tongshijia.com';

const TAG_ID_CHULIANG = 16;
const TAG_ID_ROUQIN_DANPIN = 17;
const TAG_ID_XINXIANSHUIGUO = 18;
const TAG_ID_XINPIN_SHICHI = 19;

$_display_tags_in_home = array(TAG_ID_CHULIANG, TAG_ID_ROUQIN_DANPIN, TAG_ID_XINPIN_SHICHI, TAG_ID_XINPIN_SHICHI);

$_coupon_could_distribute = array(18483 => '新用户50返10元券', 18482 => '新用户100返20元券');

function is_admin_uid($uid) {

    $_admin_uids = array('753', '632', '658', '146', '8', '141'
    , '818' //罗武林
    , '819' //高静静
    , '755'
    , '773'
    , '701166' //刘丹
    );
    return $uid && false !== array_search($uid, $_admin_uids, true);
}

App::build(array(
	'plugins' => array(COMMON_PATH.'Plugin'.DS, APP_PATH.'Plugin'.DS, ),
	//'views' => array(COMMON_PATH.'View'.DS),
	'libs' => array(COMMON_PATH,APP_PATH.'Lib'.DS),
	'vendors' => array(COMMON_PATH.'Vendor'.DS,),
	'helpers' => array(COMMON_PATH.'View'.DS.'Helper'.DS,),
	'locales' => array(ROOT.DS.'data'.DS.'locale' . DS),
	'components' => array(COMMON_PATH.'Component'. DS),
	'behaviors' => array(COMMON_PATH.'Behavior'. DS),
));
if (defined('SAE_MYSQL_DB')) {
	App::build(array(
		'locales' => array(TMP.'locale' . DS),
	));
	
	// sae上禁止放在二级目录
    define('IN_SAE',true);
    define('SAE_STORAGE_UPLOAD_DOMAIN_NAME','images');
    define('SAE_STORAGE_UPLOAD_DOMAIN_URL','http://'.$_SERVER['HTTP_APPNAME'].'-'.SAE_STORAGE_UPLOAD_DOMAIN_NAME.'.stor.sinaapp.com');
    define('UPLOAD_FILE_URL', 'http://'.$_SERVER['HTTP_APPNAME'].'-'.SAE_STORAGE_UPLOAD_DOMAIN_NAME.'.stor.sinaapp.com');
    // SAE上传地址，S3,Storage等. 带 saestor://，可直接用于fwrite,copy,等函数读写文件
    // 结合UPLOAD_RELATIVE_PATH能获取到文件的地址
    define('UPLOAD_FILE_PATH', 'saestor://'.SAE_STORAGE_UPLOAD_DOMAIN_NAME.'/');
    
    define('WEB_VISIT_CACHE','saemc://cache/');
    define('WEB_VISIT_CACHE_URL','/cache/');
    
    define('DATA_PATH', 'saemc://data/'); //data目录使用kvdb，其余stor的均使用upload_file_path
}
else{
	/**
	 * 数据库保存的文件路径从files开始，UPLOAD_FILE_URL不用包含files
	 * 结合UPLOAD_RELATIVE_PATH能获取到文件的地址
	 */
    define('UPLOAD_FILE_PATH', WWW_ROOT.DS); // 本地上传路径
    define('UPLOAD_FILE_URL',str_replace('\\','/',APP_SUB_DIR)); //APP_SUB_DIR    
    define('WEB_VISIT_CACHE',WWW_ROOT.DS.'cache'.DS);
    define('WEB_VISIT_CACHE_URL',APP_SUB_DIR.'/cache/');
    define('DATA_PATH', ROOT.DS.'data'.DS);    
}
// 文件的相对地址,结合UPLOAD_FILE_PATH获取到本地的地址
define('UPLOAD_RELATIVE_PATH', '/files/');
// sae 队列接口，提供给外部调用。 类似于分布式任务。本地调用接口添加任务，sae队列中的任务一个一个去执行。
//define('SAE_TASK_QUEUE_URL', 'http://'.$_SERVER['HTTP_APPNAME'].'.sinaapp.com/api/sae/TaskQueue.php');
define('SAE_TASK_QUEUE_URL', 'http://www.miaomiaoxuan.com/api/sae/TaskQueue.php');

if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('Etc/GMT-8');
}


define('HTTP_REQUEST_METHOD', 'HttpCurl'); // or HttpSocket

define('DEVELOP_MODE', '1'); // 显示隐藏的控制项，显示页面钩子

define('OPEN_INTERNATIONAL',1);	// 站点是否开启多语言

Configure::write('Dispatcher.filters', array(
	'AssetDispatcher', // 处理js和css文件，压缩输出，作用不大。不能支持将内容汇总到一个文件输出
	//'CacheDispatcher', // 检查整个页面的缓存.php文件，有则直接包含输出。与cachehelp配合使用，缓存文件有cachehelp生成。
));

if(!defined('SAE_MYSQL_DB')){
	/**
	 * Configures default file logging options
	 */
	App::uses('CakeLog', 'Log');
	CakeLog::config('debug', array(
		'engine' => 'FileLog',
		'types' => array('notice', 'info', 'debug'),
		'file' => 'debug',
	));
	CakeLog::config('error', array(
		'engine' => 'FileLog',
		'types' => array('warning', 'error', 'critical', 'alert', 'emergency'),
		'file' => 'error',
	));
}



/**
 * 加载扩展配置项中开启的各插件
 */
App::uses('PhpReader', 'Configure');
Configure::config('default', new PhpReader(DATA_PATH));
try{
	Configure::load('settings');
}
catch(ConfigureException $e){
	CakeLog::error('Configure load settings failed in lib/bootstrap.');
}

define('CLOUD_CRON_SECRET',Configure::read('Security.cloud_cron_secret'));

if(php_sapi_name()==='cli' || (defined('IN_SAE') && isset($_GET['cron_secret']) && $_GET['cron_secret'] == CLOUD_CRON_SECRET)){
	define('IN_CLI',true);
	unset($_GET['cron_secret'],$_REQUEST['cron_secret']);
}

$pluginBootstraps = Configure::read('Hook.bootstraps');
$plugins = array_filter(explode(',', $pluginBootstraps));

if (!empty($plugins)) {
	foreach ($plugins as $plugin) {
		$pluginName = Inflector::camelize($plugin);
		try{
			CakePlugin::load($pluginName, array(
					'bootstrap'=>true,
					'routes' => true,
					'ignoreMissing' => true,
			));
		}
		catch(Exception $e){
			CakeLog::error('Plugin not found in lib/bootstrap: ' . $pluginName);
			continue;
		}
	}
}
