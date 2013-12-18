<?php

class HookHelper extends AppHelper {
	
	public $helpers = array();

    public function __construct(View $View, $settings = array()) {		
    	$confighooks = Configure::read('Hook.helpers');
//     	print_r($confighooks);
    	if(is_array($confighooks)){
    		$settings = array_merge($settings,$confighooks);
    	}
        $this->__loadHooks($settings);
        parent::__construct($View, $settings);
    }

    private function __loadHooks($hooks = array()) {
        //$hooks = 'Oauth.SinaOauthHook,Oauth.QQOauthHook,Oauth.TopOauthHook,Communicate.ViewpointsHook';
        if(is_string($hooks)){
    		$hooks = explode(',', $hooks);
        }
        if (!empty($hooks)) {
            // Set hooks            
            foreach ($hooks AS $hook) {
            	$hook = trim($hook);
            	if(empty($hook)) continue;
            	
            	if(strpos($hook,'.')>0){
	                $hookE = explode('.', $hook);
	                $plugin = $hookE['0'];	                
	                // 插件未开启时，跳过插件中的Helper
	                if(!CakePlugin::loaded($plugin)){
	                	continue;
	                }
            	}
                $this->helpers[] = $hook;
            }
        }
        $this->helpers = array_unique(array_delete_value($this->helpers));
    }
	/**
	 * 加载一个钩子调用的钩子实现文件
	 * @param unknown_type $hooks
	 */
    public function loadhook($hooks) {
        $this->__loadHooks($hooks);
        // reset component lookup table,when add a hook
        $this->_helperMap = ObjectCollection::normalizeObjectArray($this->helpers);
    }

    /**
     * 调用钩子
     * 若返回的值为字符串时，连接字符串到最后返回。
     * 若返回值为数组或其它形式，返回值会被抛弃。
     * 若需要返回参数为数组，可在钩子实现中，使用引用参数来达到效果
     * @param string $methodName
     * @param array() $params
     * @return array
     */
    function call($methodName, $params=array()) {
        $output = '';
//      	print_r($this->helpers);
        foreach ($this->helpers AS $hook) {
        	if(empty($hook)){
        		continue;
        	}
            if (strstr($hook, '.')) {
                $hookE = explode('.', $hook);
                $hook = $hookE['1'];
            }
            try {
                if (method_exists($this->{$hook}, $methodName)) {
//                 	echo "==$hook====$methodName============";
                    //$output .= $this->{$hook}->$methodName();
                    $tmp = call_user_func_array(array(&$this->{$hook}, $methodName), $params);
                    if(is_string($tmp)){
                    	$output .= $tmp;
                    }
                }
            } catch (MissingHelperException $e) {
                continue;
            }
        }
        return $output;
    }
    
    /**
     * Before render callback. beforeRender is called before the view file is rendered.
     */
    public function beforeRender($viewFile) {
    	$this->call('beforeRender',array($viewFile));
    }
    
    /**
     * After render callback. afterRender is called after the view file is rendered
     * but before the layout has been rendered.
     */
    public function afterRender($viewFile) {
    	$this->call('afterRender',array($viewFile));
    }
    
    /**
     * Before layout callback. beforeLayout is called before the layout is rendered.
     */
    public function beforeLayout($layoutFile) {
    	$this->call('beforeLayout',array($layoutFile));
    }
    
    /**
     * After layout callback. afterLayout is called after the layout has rendered.
     */
    public function afterLayout($layoutFile) {
    	$this->call('afterLayout',array($layoutFile));
    }
    
    /**
     * Before render file callback.
     * Called before any view fragment is rendered.
     */
    public function beforeRenderFile($viewfile) {
    	$this->call('beforeRenderFile',array($viewfile));
    }
    
    /**
     * After render file callback.
     * Called after any view fragment is rendered.
     */
    public function afterRenderFile($viewfile, $content) {
    	$this->call('afterRenderFile',array($viewfile, $content));
    }

}

?>