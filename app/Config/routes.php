<?php
App::uses('CustomRouter', 'Lib');

// When making custom routes, a common pitfall is that using named parameters will break your custom routes. In order to solve this
	//Router::connectNamed(array('page'),array('separator'=>'|')); 	
	Router::parseExtensions('html', 'rss','json','xml','css');


//    Router::redirect('/products/19700101/heizhu_tuangou', '/techan/18');
//
//    CustomRouter::connect('/t/kuerlexl_ten',
//        array('controller'=>'products','action' => 'view', '2yuan_shi_chi_xin_jiang_ku_er_le_xiang_li'));
//
//    CustomRouter::connect('/t/rice_product',
//        array('controller'=>'products','action' => 'view', 'wu_chang_dao_hua_xiang_ti_qian_yu_shou_500jin_zhi_xian_bei_jing'));
//
//
//    CustomRouter::connect('/t/special_nov',
//        array('controller'=>'categories','action' => 'special_list', 'special_for_rice_coupons'));
//
//    //store
//    CustomRouter::connect('/s', array('controller' => 'stores', 'action' => 'index'));
//    CustomRouter::connect('/s/:action', array('controller' => 'stores'), array('action' => '[\w_]+') );
//    CustomRouter::connect('/b/index', array('controller' => 'brands', 'action' => 'index'));
//    CustomRouter::connect('/b/:slug', array('controller' => 'brands', 'action' => 'view'), array('pass' => array('slug'), 'slug' => '[\w_]+'));
//    CustomRouter::connect('/b/:slug/:action', array('controller' => 'brands'), array('pass' => array('slug',), 'action' => '[\w_]+' , 'slug' => '[\w_]+'));
//
//    CustomRouter::connect('/t/ag',
//        array('controller'=>'game_xiyang','action' => 'award', 'xiyang', '?' => array('from' => 'init-pys')));
//    CustomRouter::connect('/t/ag/jiujiu',
//        array('controller'=>'game_jiujiu','action' => 'award', 'jiujiu'));
//    CustomRouter::connect('/t/ag/xiyang',
//        array('controller'=>'game_xiyang','action' => 'award', 'xiyang'));
//    CustomRouter::connect('/t/ag/:type',
//        array('controller'=>'apple201410','action' => 'award'),
//        array('pass' => array('type'), 'type'=>'[\w_]+'));
//
//    CustomRouter::connect('/:controller/:yearmonth/:slug',
//	array('action' => 'view'),
//	array('pass' => array('slug'),'controller'=>'[\w_]+','yearmonth'=>"[0-9]{8}")
//	);
//	CustomRouter::connect('/products/lists',
//			array('controller'=>'products','action' => 'lists')
//	);
//	CustomRouter::connect('/products/add',
//		array('controller'=>'products','action' => 'add')
//	);
// 	CustomRouter::connect('/products/:slug',
// 			array('controller'=>'products','action' => 'view'),
// 			array('pass' => array('slug'))
// 	);
//	CustomRouter::connect('/tag/:model/:id',
//			array('controller'=>'keywords','action' => 'lists'),
//			array('pass' => array('model','id'))
//	);
//
//	CustomRouter::connect(
//	'/regions/:regionid/*',
//	array('controller' => 'regions', 'action' => 'index'),
//	array('pass' => array('regionid'),'regionid'=>"[0-9]+")
//	);
	// 	CustomRouter::connect('/products', array('controller' => 'products','action'=>'index'));
	
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
		// CakePlugin::load('Oauth', array( 'bootstrap' => true));
		// CakePlugin::load('Museum', array( 'routes' => true)); //'bootstrap' => true,
		// CakePlugin::load('Taobao', array( 'routes' => true));
		// CakePlugin::load('Communicate');	
		$GLOBALS['site_cate_id'] = 0;
		$GLOBALS['site_info'] = array();
		//try{CakePlugin::load('MultiSite', array('bootstrap'=>true));}catch(Exception $e){}
	}
	CakePlugin::routes();

	CustomRouter::connect('/', array('controller' => 'pys', 'action' => 'index','/'));
	//CustomRouter::connect('/users/51daifan.sinaapp.com', array('controller' => 'categories', 'action' => 'view','/'));

	//CustomRouter::connect('/shichituan', array('controller' => 'articles', 'action' => 'shi_chi_tuan'));
	CustomRouter::connect('/jserror', array('controller' => 'util', 'action' => 'log_js_error'));
	//CustomRouter::connect('/tk/log', array('controller' => 'util', 'action' => 'log_trace'));

	//CustomRouter::connect('/pt/:tag', array('controller' => 'categories', 'action' => 'tag'), array('pass' => array('tag')));

	/*'/:slug' 这条放在插件之后，容易对其它路由产生冲突 */
	//CustomRouter::connect('/:slug', array('controller' => 'categories', 'action' => 'view'), array('pass' => array('slug')) );
	//CustomRouter::connect('/:slug/:brand', array('controller' => 'categories', 'action' => 'view'), array('pass' => array('slug', 'brand'), 'slug' => 'techan',  'brand'=>"[0-9]+") );

// 	require CAKE . 'Config' . DS . 'routes.php';

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
	
	

