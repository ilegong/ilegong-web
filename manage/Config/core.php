<?php
if($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_ADDR'] == '::1'){
	define('IN_LOCALHOST',true);
	Configure::write('debug',0);
	Configure::write('Cache.disable', false);
}
else{
	Configure::write('debug',0);
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

$duration = 7200;
if (Configure::read('debug') > 1) {
    $duration = 300;
}
if (class_exists('Memcached')) {
    $engine = 'Memcached';
    $cache_prefix = 'pys_';
    Cache::config('default', array(
        'engine' => $engine,
        'servers' => array(MEMCACHE_HOST . ':11211'),
        'duration' => $duration,
        'probability' => 100,
        'prefix' => $cache_prefix . 'miaocms_'
    ));

    Cache::config('_cake_core_', array(
        'engine' => $engine,
        'prefix' => $cache_prefix . 'core_app_',
        'servers' => array(MEMCACHE_HOST . ':11211'),
        'duration' => $duration,
        'probability' => 100,
    ));

    Cache::config('_cake_model_', array(
        'engine' => $engine,
        'prefix' => $cache_prefix . 'model_app_',
        'servers' => array(MEMCACHE_HOST . ':11211'),
        'duration' => $duration,
        'probability' => 100,
    ));
} else {
    if (defined('SAE_MYSQL_DB')) {
        $engine = 'Saemc';
    } else {
        $engine = 'File';
    }
    $cache_prefix = '';
    if (defined('SAE_MYSQL_DB')) {
        // 区分各版本的缓存，不互相冲突
        $cache_prefix = $_SERVER['HTTP_APPVERSION'];
    }
// 缓存的配置，前台的前缀包含后台的前缀（利用后台的prefix比较时能涵盖前台的文件）。后台删除缓存时，前后台就都能删除了
    Cache::config('_cake_core_', array(
        'engine' => $engine,
        'prefix' => $cache_prefix . 'core_',
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
        'prefix' => $cache_prefix . 'model_',
        'path' => CACHE . 'models' . DS,
        'serialize' => ($engine === 'File'),
        'duration' => $duration,
        'probability' => 100,
    ));

    Cache::config('default', array(
        'engine' => $engine, //[required]
        'duration' => $duration, //[optional]
        'probability' => 100, //[optional]
        'prefix' => $cache_prefix . 'miaocms_', //[optional]  prefix every cache file with this string
        'lock' => false,
        'serialize' => true, // [optional] compress data in Memcache (slower, but uses less memory)
    ));
}


