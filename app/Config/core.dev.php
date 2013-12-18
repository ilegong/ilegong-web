<?php
if(isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1'){
	Configure::write('debug', 1);
	Configure::write('Cache.disable', false);
}
else{
	Configure::write('debug', 1);
	Configure::write('Cache.disable', false);
}

Configure::write('Error', array(
            'handler' => 'ErrorHandler::handleError',
            'level' => E_ERROR | E_WARNING | E_PARSE,
            'trace' => true
        ));
Configure::write('Exception', array(
            'handler' => 'ErrorHandler::handleException',
            'renderer' => 'ExceptionRenderer',
            'log' => true
        ));
Configure::write('App.encoding', 'UTF-8');

define('DEFAULT_LANGUAGE', 'zh-cn');
Configure::write('Config.language', 'zh-cn');

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
/**
 * A random string used in security hashing methods.
 */
Configure::write('Security.salt', 'DYhGsdfo290JJKIxfs2guVoUubWwvniR2G0FgaC9mi');
/**
 * A random numeric string (digits only) used to encrypt/decrypt strings.
 */
Configure::write('Security.cipherSeed', '76859349497453542496749683645');

// Configure::write('Asset.timestamp', true);
// Configure::write('Asset.compress', true);
// Configure::write('Asset.filter.css', 'asset_filter.php');
// Configure::write('Asset.filter.js', 'asset_filter.php');


Configure::write('Acl.classname', 'DbAcl');
Configure::write('Acl.database', 'default');

if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Etc/GMT-8');
}

if (defined('SAE_MYSQL_DB')) {
    $engine = 'Saemc';
}
elseif(preg_match('/\.aliapp\.com$/',$_SERVER['HTTP_HOST'])){
	$engine = 'Acemc';
}
else {
    $engine = 'File';
    if (extension_loaded('apc') && (php_sapi_name() !== 'cli' || ini_get('apc.enable_cli'))) {
        $engine = 'Apc';
    }
}

// In development mode, caches should expire quickly.
// 缓存的配置，后台的前缀都包含前台的前缀。后台删除缓存时，就都能删除了
$duration = '+99999 days';
if (Configure::read('debug') > 1) {
    $duration = '+20 seconds';
}
$cache_prefix = '';
if(defined('SAE_MYSQL_DB')){
	// 区分各版本的缓存，不互相冲突
	$cache_prefix = $_SERVER['HTTP_APPVERSION'];
}

Cache::config('_cake_core_', array(
            'engine' => $engine,
            'prefix' => $cache_prefix.'cake_core_app_',
            'path' => CACHE . 'persistent' . DS,
            'serialize' => ($engine === 'File'),
            'duration' => $duration,
            'probability'=> 100,
        ));

/**
 * Configure the cache for model, and datasource caches.  This cache configuration
 * is used to store schema descriptions, and table listings in connections.
 */
Cache::config('_cake_model_', array(
            'engine' => $engine,
            'prefix' => $cache_prefix.'cake_model_app_',
            'path' => CACHE . 'models' . DS,
            'serialize' => ($engine === 'File'),
            'duration' => $duration,
            'probability'=> 100,
        ));

Cache::config('default', array(
            'engine' => $engine, //[required]
            'duration' => 3600, //[optional]
            'probability' => 100, //[optional]
            'prefix' => $cache_prefix.'miaocms_', //[optional]  prefix every cache file with this string
            'lock' => false,
            'serialize' => true, // [optional] compress data in Memcache (slower, but uses less memory)
        ));

/**
 * 长时间的缓存，视为长期有效，用于显示页，（view,index）。缓存需要更新时，使用cron来更新。
 *
 * @var $longcache
 */
Cache::config('ViewHtmlCacheConfig', array(
            'engine' => $engine, //[required]
            'duration' => '+99999 days', //[optional]
            'probability' => 100, //[optional]
            'prefix' => $cache_prefix.'miaocms_', //[optional]  prefix every cache file with this string
            'lock' => false,
            'serialize' => true, // [optional] compress data in Memcache (slower, but uses less memory)
        ));


// UCenter config
/*
if(defined('SAE_MYSQL_DB')){ // in sae
	define('UC_CONNECT', 'mysql');
	define('UC_DBHOST', 'w.rdc.sae.sina.com.cn:3307');
	define('UC_DBUSER', 'w1yyo2lx31');
	define('UC_DBPW', 'j0i2ljiz4w3z4h42m115k0h21i4hw5lwwhkjz4m0');
	define('UC_DBNAME', 'app_discuzx');
	define('UC_DBCHARSET', 'utf8');
	define('UC_DBTABLEPRE', '`app_discuzx`.sae_ucenter_');
	define('UC_DBCONNECT', '0');
	define('UC_KEY', '9d5dj/1W72UJixnEUkqtR9t2KSY7QxAswfaH0UQ');
	define('UC_API', 'http://discuzx.sinaapp.com/uc_server');
	define('UC_CHARSET', 'utf-8');
	define('UC_IP', '');
	define('UC_APPID', '4');
	define('UC_PPP', '20');

}
elseif($_SERVER['SERVER_ADDR'] == '127.0.0.1'){ // localhost
	define('UC_CONNECT', 'mysql');
	define('UC_DBHOST', 'localhost');
	define('UC_DBUSER', 'root');
	define('UC_DBPW', 'xsdfuh232sdw!3S#sd');
	define('UC_DBNAME', 'ultrax');
	define('UC_DBCHARSET', 'utf8');
	define('UC_DBTABLEPRE', '`ultrax`.pre_ucenter_');
	define('UC_DBCONNECT', '0');
	define('UC_KEY', 'fcbe360P7xsz45nhpq4awu1BjeMqZzoaNvctV+o');
	define('UC_API', 'http://www.a.com/uc_server');
	define('UC_CHARSET', 'utf-8');
	define('UC_IP', '');
	define('UC_APPID', '2');
	define('UC_PPP', '20');
}
*/