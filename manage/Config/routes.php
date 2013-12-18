<?php

App::uses('CustomRouter', 'Lib');

CustomRouter::parseExtensions('html', 'rss','json','xml','css');

CustomRouter::connect('/', array('controller' => 'systems', 'admin' => true, 'action' => 'index'));
CustomRouter::connect('/admin', array('controller' => 'systems', 'admin' => true, 'action' => 'index'));

try{CakePlugin::load('Acl');}catch(Exception $e){}
try{CakePlugin::load('Extensions');}catch(Exception $e){}
try{CakePlugin::load('DefinedLanguage');}catch(Exception $e){}
//try{CakePlugin::load('CrawlTool');}catch(Exception $e){}
//try{CakePlugin::load('Oa');}catch(Exception $e){}


CakePlugin::routes();
//require CAKE . 'Config' . DS . 'routes.php';

$prefixes = Router::prefixes();

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


