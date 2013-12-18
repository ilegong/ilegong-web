<?php

class CustomRouter extends Router{
/**
 * An extra Route will be created for locale-based URLs
 *
 * For example,
 * http://yoursite.com/zh-CN/post-title, and
 * http://yoursite.com/en/blog/post-title
 *
 * Returns this object's routes array. Returns false if there are no routes available.
 *
 * @param string $route			An empty string, or a route string "/"
 * @param array $default		NULL or an array describing the default route
 * @param array $params			An array matching the named elements in the route to regular expressions which that element should match.
 * @return void
 */
    public static function connect($route, $default = array(), $options = array()) {
        
        if ($route == '/') {
            $route = '';
        }
//         if(is_array($options['pass'])){
//         	if(!in_array('locale',$options['pass'])){
//         		array_push($options['pass'], 'locale');
//         	}
//         }
//         else{
//         	$options['pass'] = array('locale');
//         }
        
        //echo '/:locale' . $route; print_r($default);print_r($params);
        // 规则，按加入先后匹配，先加入的优先。默认规则在最后加入
        // :locale的规则要先加入，防止部分规则先匹配到默认的去了 如/:controller/:action/*        
        parent::connect('/:locale' . $route, $default, array_merge(array('locale' => '[a-z]{2}(\-[a-zA-Z]{2})?'), $options));
        parent::connect($route, $default, $options);
    }

    /**
     * 加载默认的规则。默认规则在所有规则加入之后加入。
     * 在routes文件的最后执行.
     * 不支持action=index时，不写action的操作。 当index缺失时，传入参数named无法正常route，
     * 如：http://www.miaomiaoxuan.com/index.php/Taobao/taobaokes/page:2/ 路由错误，无法找到页面
     */
	public static function loadDefaultRoutes() {		
		if ($plugins = App::objects('plugin')){
			$pluginPattern = implode('|', $plugins);
			$match = array('plugin' => $pluginPattern); //Array ( [plugin] => acl|communicate|defined_language|extensions|install|museum|oauth|translate ) 
//			print_r($match);print_r(self::$_prefixes);
			if(!empty(self::$_prefixes)){
				foreach (self::$_prefixes as $prefix) {
					$params = array('prefix' => $prefix, $prefix => true);
					//$indexParams = $params + array('action' => 'index');
					//self::connect("/{$prefix}/:plugin/:controller", $indexParams, $match);
					self::connect("/{$prefix}/:plugin/:controller/:action/*", $params, $match);
				}
			}
			//self::connect('/:plugin/:controller', array('action' => 'index'), $match);
			self::connect('/:plugin/:controller/:action/*', array(), $match);
		}
		if(!empty(self::$_prefixes)){
			foreach (self::$_prefixes as $prefix) {
				$params = array('prefix' => $prefix, $prefix => true);
				//$indexParams = $params + array('action' => 'index');
				//self::connect("/{$prefix}/:controller", $indexParams);
				self::connect("/{$prefix}/:controller/:action/*", $params);
			}
		}
		//self::connect('/:controller', array('action' => 'index'));
		self::connect('/:controller/:action/*');		
	}
    
	public static function url($url = null, $full = false){
			if (!isset($url['ext'])) {
				$url['ext']='html';
			}
			return parent::url($url, $full);
	}
}