<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2010, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.config
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/views/pages/home.ctp)...
 */
 
	CustomRouter::connect('/taobaokes/index', array('controller' => 'taobaokes','action' => 'index','plugin'=>'Taobao', ));
	
	CustomRouter::connect('/taobaokes/:yearmonth/:slug', 
		array('action' => 'view','plugin'=>'Taobao','controller' => 'taobaokes',), 
		array('pass' => array('slug'),'yearmonth'=>"[0-9]{4,6}") 
	);
	CustomRouter::connect('/taobaokes/:slug', 
		array('action' => 'view','plugin'=>'Taobao','controller' => 'taobaokes',), 
		array('pass' => array('slug'),'slug'=>"[0-9]*") 
	);
	
//	CustomRouter::connect('/index.php', array('controller' => 'taobaokes','action' => 'index','plugin'=>'Taobao', ));
//	CustomRouter::connect('/taobao/taobaokes/', array('controller' => 'taobaokes','action' => 'index','plugin'=>'Taobao', ));
	