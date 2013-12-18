<?php
if($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1'){
	define('IN_LOCALHOST',true);
	Configure::write('debug', 1);
	Configure::write('Cache.disable', false);
}
else{
	Configure::write('debug', 0);
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
Configure::write('Routing.prefixes', array('admin'));


//Configure::write('Cache.check', true);

define('LOG_ERROR', 2);

Configure::write('Session', array(
   'defaults' => 'php',
   'timeout' => 900,
   'name' => 'Miao'
));

// Configure::write('Session', array(
//     'defaults' => 'php',
//     'timeout' => 150,
//     'name' => 'CAKEPHP',
//     'handler' => array(
//         'engine' => 'CustomDatabaseSession',
// 	'model' => 'Session'
//     )
// ));
Configure::write('Session.cookie', 'cake');

Configure::write('Security.level', 'medium');

Configure::write('Acl.classname', 'DbAcl');
Configure::write('Acl.database', 'default');

if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Etc/GMT-8');
}

if (defined('SAE_MYSQL_DB')) {
    $engine = 'Saemc';
} else {
    $engine = 'File';
//     if (extension_loaded('apc') && (php_sapi_name() !== 'cli' || ini_get('apc.enable_cli'))) {
//         $engine = 'Apc';
//     }
}

$duration = '+999 days';
if (Configure::read('debug') >= 1) {
    $duration = '+10 seconds';
}
$cache_prefix = '';
if(defined('SAE_MYSQL_DB')){
	// 区分各版本的缓存，不互相冲突
	$cache_prefix = $_SERVER['HTTP_APPVERSION'];
}
// 缓存的配置，后台的前缀都包含前台的前缀。后台删除缓存时，就都能删除了
Cache::config('_cake_core_', array(
            'engine' => $engine,
            'prefix' => $cache_prefix.'cake_core_',
            'path' => CACHE . 'persistent' . DS,
            'serialize' => ($engine === 'File'),
            'duration' => $duration,
            'probability' => 100,
        ));

/**
 * Configure the cache for model, and datasource caches.  This cache configuration
 * is used to store schema descriptions, and table listings in connections.
 */
Cache::config('_cake_model_', array(
            'engine' => $engine,
            'prefix' => $cache_prefix.'cake_model_',
            'path' => CACHE . 'models' . DS,
            'serialize' => ($engine === 'File'),
            'duration' => $duration,
            'probability' => 100,
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
 * Cache Engine Configuration
 * Default settings provided below
 *
 * File storage engine.
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'File', //[required]
 *		'duration'=> 3600, //[optional]
 *		'probability'=> 100, //[optional]
 * 		'path' => CACHE, //[optional] use system tmp directory - remember to use absolute path
 * 		'prefix' => 'cake_', //[optional]  prefix every cache file with this string
 * 		'lock' => false, //[optional]  use file locking
 * 		'serialize' => true, // [optional]
 * 		'mask' => 0666, // [optional] permission mask to use when creating cache files
 *	));
 *
 * APC (http://pecl.php.net/package/APC)
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'Apc', //[required]
 *		'duration'=> 3600, //[optional]
 *		'probability'=> 100, //[optional]
 * 		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
 *	));
 *
 * Xcache (http://xcache.lighttpd.net/)
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'Xcache', //[required]
 *		'duration'=> 3600, //[optional]
 *		'probability'=> 100, //[optional]
 *		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional] prefix every cache file with this string
 *		'user' => 'user', //user from xcache.admin.user settings
 *		'password' => 'password', //plaintext password (xcache.admin.pass)
 *	));
 *
 * Memcache (http://memcached.org/)
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'Memcache', //[required]
 *		'duration'=> 3600, //[optional]
 *		'probability'=> 100, //[optional]
 * 		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
 * 		'servers' => array(
 * 			'127.0.0.1:11211' // localhost, default port 11211
 * 		), //[optional]
 * 		'persistent' => true, // [optional] set this to false for non-persistent connections
 * 		'compress' => false, // [optional] compress data in Memcache (slower, but uses less memory)
 *	));
 *
 *  Wincache (http://php.net/wincache)
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'Wincache', //[required]
 *		'duration'=> 3600, //[optional]
 *		'probability'=> 100, //[optional]
 *		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
 *	));
 *
 * Redis (http://http://redis.io/)
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'Redis', //[required]
 *		'duration'=> 3600, //[optional]
 *		'probability'=> 100, //[optional]
 *		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
 *		'server' => '127.0.0.1' // localhost
 *		'port' => 6379 // default port 6379
 *		'timeout' => 0 // timeout in seconds, 0 = unlimited
 *		'persistent' => true, // [optional] set this to false for non-persistent connections
 *	));
 */
