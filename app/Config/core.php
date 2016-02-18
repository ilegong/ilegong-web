<?php
if(isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1'){
	Configure::write('debug',1);
	Configure::write('Cache.disable', false);
	define('IS_LOCALHOST', true);
}
else{
	Configure::write('debug',0);
	Configure::write('Cache.disable', false);
}

Configure::write('Error', array(
            'handler' => 'ErrorHandler::handleError',
            'level' => E_ERROR | E_WARNING | E_PARSE,
            'renderer' => 'CustomExceptionRender',
            'trace' => true
        ));
Configure::write('Exception', array(
            'handler' => 'ErrorHandler::handleException',
            'renderer' => 'CustomExceptionRender',
            'log' => true
        ));
Configure::write('App.encoding', 'UTF-8');

define('DEFAULT_LANGUAGE', 'zh-cn');
Configure::write('Config.language', 'zh-cn');

Configure::write('kuaidi100_key','1c9cbcbc54d0ecf5');

define('WX_APPID', 'wxca7838dcade4709c');
//ID for service account(DO NOT CHANGE)
define('WX_APPID_SOURCE', 'wxca78');
define('WX_SECRET', '79b787ec8f463eeb769540464c9277b2');
define('WX_SERVICE_ID_NAME', '朋友说');
define('WX_SERVICE_ID_NO', 'pyshuo2014');

// Configure::write('App.baseUrl', env('SCRIPT_NAME'));
define('LOG_ERROR', 2);
// Configure::write('Session', array(
//     'defaults' => 'database',
//     'timeout' => 150,
//     'name' => 'CAKEPHP',
//     'handler' => array(
//         'engine' => 'CustomDatabaseSession',
// 	'model' => 'Session'
//     )
// ));
Configure::write('Session', array(
   'defaults' => 'php',
   'timeout' => 900,
   'name' => 'Miao'
));


Configure::write('Session.cookie', 'cake');
Configure::write('Security.level', 'medium');

Configure::write('Asset.timestamp', true);
// Configure::write('Asset.compress', true);
// Configure::write('Asset.filter.css', 'asset_filter.php');
// Configure::write('Asset.filter.js', 'asset_filter.php');


Configure::write('Acl.classname', 'DbAcl');
Configure::write('Acl.database', 'default');

if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Etc/GMT-8');
}

if (defined('SAE_MYSQL_DB')) {
    Configure::write('App.assetsUrl', 'http://51daifan.sinaapp.com');
}

// In development mode, caches should expire quickly.
// 缓存的配置，后台的前缀与前台的前缀保持一致。后台删除缓存时，前后台缓存就都能删除了
$duration = 7200;
if (Configure::read('debug') > 1) {
    $duration = 300;
}
$cache_prefix = 'pys';
if(class_exists('Memcached')){
    $engine = 'Memcached';
    Cache::config('default', array(
        'engine' => $engine,
        'servers' => array('127.0.0.1:11211'),
        'duration' => $duration,
        'probability' => 100,
        'prefix' => $cache_prefix . 'miaocms_'
    ));

    Cache::config('_cake_core_', array(
        'engine' => $engine,
        'prefix' => $cache_prefix . 'core_app_',
        'servers' => array('127.0.0.1:11211'),
        'duration' => $duration,
        'probability' => 100,
    ));

    Cache::config('_cake_model_', array(
        'engine' => $engine,
        'prefix' => $cache_prefix . 'model_app_',
        'servers' => array('127.0.0.1:11211'),
        'duration' => $duration,
        'probability' => 100,
    ));
}else{
    if (defined('SAE_MYSQL_DB')) {
        $engine = 'Saemc';
    }
    elseif(isset($_SERVER['HTTP_HOST'])&&preg_match('/\.aliapp\.com$/',$_SERVER['HTTP_HOST'])){
        $engine = 'Acemc';
    }
    else {
        $engine = 'File';
        if (extension_loaded('apc') && (php_sapi_name() !== 'cli' || ini_get('apc.enable_cli'))) {
            $engine = 'Apc';
        }
    }

    if(defined('SAE_MYSQL_DB')){
        // 区分各版本的缓存，不互相冲突
        $cache_prefix = $_SERVER['HTTP_APPVERSION'];
    }
    /*
     *  前后台使用不同的缓存文件 _cake_core_,_cake_model_
     *  缓存的配置，前台的前缀包含后台的前缀（利用后台的prefix比较时能涵盖前台的文件）。后台删除缓存时，前后台就都能删除了.
     *  如后台的前缀为 miaocms_, 则前台的前缀可使用miaocms_app_
     *
     *  前台后使用相同的配置文件 default
     *  后台操作修改数据更新缓存时，同时能更新前台的缓存（使用同一份缓存文件）
     *  如后台修改Setting设置项
     */
    Cache::config('_cake_core_', array(
        'engine' => $engine,
        'prefix' => $cache_prefix.'core_app_',
        'path' => CACHE . 'persistent' . DS,
        'serialize' => ($engine === 'File'),
        'duration' => $duration,
        'probability'=> 100,
    ));
    Cache::config('_cake_model_', array(
        'engine' => $engine,
        'prefix' => $cache_prefix.'model_app_',
        'path' => CACHE . 'models' . DS,
        'serialize' => ($engine === 'File'),
        'duration' => $duration,
        'probability'=> 100,
    ));

    Cache::config('default', array(
        'engine' => $engine,
        'duration' => $duration,
        'probability' => 100,
        'prefix' => $cache_prefix.'miaocms_',
        'lock' => false,
        'serialize' => true,
    ));
}


// UCenter config
/*
if(defined('SAE_MYSQL_DB')){ // in sae
	define('UC_CONNECT', 'POST');
// 	define('UC_DBHOST', 'w.rdc.sae.sina.com.cn:3307');
// 	define('UC_DBUSER', '');
// 	define('UC_DBPW', '');
// 	define('UC_DBNAME', '');
// 	define('UC_DBCHARSET', 'utf8');
// 	define('UC_DBTABLEPRE', 'pre_ucenter_');
// 	define('UC_DBCONNECT', '0');
	define('UC_KEY', '1W72UJixUISdjknEUkqtR9t2KSY7QxAswfaH0UQsU23OQ');
	define('UC_API', 'http://www.51daifan.com/uc_server');
	define('UC_CHARSET', 'utf-8');
	define('UC_IP', '');
	define('UC_APPID', '2');
	define('UC_PPP', '20');
}
*/
