<?php
App::uses('CustomRouter', 'Lib');


	Router::parseExtensions('html', 'rss','json','xml','css');


	
	Configure::write('Install.installed',
		file_exists(APP . 'Config' . DS . 'database.php') && file_exists(DATA_PATH . 'install.lock')
	);
	if (!Configure::read('Install.installed')) {
		if(!file_exists(APP . 'Config' . DS . 'database.php')){
			$request = Router::getRequest();
			if(strpos($request->url,'install')===false){ //未安装时，访问其它路径时自动跳转到install
				header('location:'.Router::url('/install'));
				exit;
			}
		}
		CakePlugin::load('Install', array( 'routes' => true));	
	}
	else{
		$GLOBALS['site_cate_id'] = 0;
		$GLOBALS['site_info'] = array();
	}
	CakePlugin::routes();
	CustomRouter::connect('/', array('controller' => 'pys', 'action' => 'index','/'));
	CustomRouter::connect('/jserror', array('controller' => 'util', 'action' => 'log_js_error'));

	$prefixes = Router::prefixes();
	
	/**
	 * CustomRouter让默认的路由支持多语言。
	 */
	
	if ($plugins = CakePlugin::loaded()) {
		App::uses('PluginShortRoute', 'Routing/Route');
		foreach ($plugins as $key => $value) {
			$plugins[$key] = Inflector::underscore($value);
		}
		$pluginPattern = implode('|', $plugins);
		$match = array('plugin' => $pluginPattern);
		$shortParams = array('routeClass' => 'PluginShortRoute', 'plugin' => $pluginPattern);
		
		foreach ($prefixes as $prefix) {
			$params = array('prefix' => $prefix, $prefix => true);
			$indexParams = $params + array('action' => 'index');
			CustomRouter::connect("/{$prefix}/:plugin", $indexParams, $shortParams);
			CustomRouter::connect("/{$prefix}/:plugin/:controller", $indexParams, $match);
			CustomRouter::connect("/{$prefix}/:plugin/:controller/:action/*", $params, $match);
		}
		CustomRouter::connect('/:plugin', array('action' => 'index'), $shortParams);
		CustomRouter::connect('/:plugin/:controller', array('action' => 'index'), $match);
		CustomRouter::connect('/:plugin/:controller/:action/*', array(), $match);
	}
	
	foreach ($prefixes as $prefix) {
		$params = array('prefix' => $prefix, $prefix => true);
		$indexParams = $params + array('action' => 'index');
		CustomRouter::connect("/{$prefix}/:controller", $indexParams);
		CustomRouter::connect("/{$prefix}/:controller/:action/*", $params);
	}
	CustomRouter::connect('/:controller', array('action' => 'index'));
	CustomRouter::connect('/:controller/:action/*');
	
	$namedConfig = Router::namedConfig();
	if ($namedConfig['rules'] === false) {
		Router::connectNamed(true);
	}
	
	unset($namedConfig, $params, $indexParams, $prefix, $prefixes, $shortParams, $match,
			$pluginPattern, $plugins, $key, $value);
	
	

