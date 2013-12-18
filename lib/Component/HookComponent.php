<?php
class HookComponent extends Component {
	
	private $Controller = null;
	
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->Controller = $collection->getController();
		
		/**
		 * 插件实现原理：
		 * 将钩子组件加入 $this->components中，
		 * 在组件初始化时， 包含的组件（$this->components中的）会通过app::uses包含,在调用时加载
		 */
		$confighooks = Configure::read('Hook.components');
		if(is_array($confighooks)){
			$settings = array_merge($settings,$confighooks);
		}
		$this->__loadHooks($settings);
        parent::__construct($collection,$settings);
    }
    
    private function __loadHooks($hooks=array()) {
        if (!empty($hooks)) {   
            foreach ($hooks AS $hook) {            	
            	$hook = trim($hook);
            	if(empty($hook)) continue;
            	
                if (strstr($hook, '.')!==false) {
                    $hookE = explode('.', $hook);
                    $plugin = $hookE['0'];
                } else {
                    $plugin = null;
                }
                // 插件未开启时，跳过插件中的组件
                if($plugin && !CakePlugin::loaded($plugin)){ 
                	continue;
                }
                $this->components[] = $hook;
            }
        }
    }
    /**
     * 数组或者字符串
     * @param array/string $hooks 加载的钩子
     */
    public function loadhook($hooks){
    	if(!is_array($hooks)){
    		$hooks = explode(',',$hooks);
    	}
    	$this->__loadHooks($hooks);
    	// reset component lookup table,when add a hook
    	$this->_componentMap = ComponentCollection::normalizeObjectArray($this->components);
    }
    /**
     * hookComponent,call不支持返回，使用引用传参返回，或者直接改变request->data的值
     * @param unknown_type $methodName
     * @param unknown_type $params
     */
    public function call($methodName,$params=array()) {
    	$result = '';
        foreach ($this->components AS $hook) {
            if (strstr($hook, '.')) {
                $hookE = explode('.', $hook);
                $hook = $hookE['1'];
            }
            try	{
	            if (method_exists($this->{$hook}, $methodName)) {
	                // controller对象作为hookcomponent的第一个参数
	            	array_unshift($params,$this->Controller);
	                $ret = call_user_func_array(array(&$this->{$hook}, $methodName), $params);
	                if(is_array($ret)){
	                	$result = array_merge($result,$ret);
	                }
	                else{
	                	$result .= $ret;
	                }
	            }
	        }
	    	catch(MissingComponentException $e){					
	    		continue;
			}
        }
        return $result;
    }
    
    /**
     * Called before the Controller::beforeFilter().
     * 触发组件hook的initialize
     */
    public function initialize(Controller $controller) {
    	$this->call('initialize');
    }
    
    /**
     * Called before the Controller::beforeRender(), and before
     * the view class is loaded, and before Controller::render()
     *  触发组件hook的beforeRender
     */
    public function beforeRender(Controller $controller) {
    	$this->call('beforeRender');
    }
    
    /**
     * Called after the Controller::beforeFilter() and before the controller action
     * 触发组件hook的startup
     */
    public function startup(Controller $controller) {
    	$this->call('startup');
    }
    /**
     * Called after Controller::render() and before the output is printed to the browser.
     */
    public function shutdown(Controller $controller) {
    	$this->call('shutdown');
    }
    
    /**
     * Called before Controller::redirect(). Allows you to replace the url that will
     * be redirected to with a new url. The return of this method can either be an array or a string.
     *
     * If the return is an array and contains a 'url' key. You may also supply the following:
     *
     * - `status` The status code for the redirect
     * - `exit` Whether or not the redirect should exit.
     *
     * If your response is a string or an array that does not contain a 'url' key it will
     * be used as the new url to redirect to.
     *
     * @param Controller $controller Controller with components to beforeRedirect
     * @param string|array $url Either the string or url array that is being redirected to.
     * @param integer $status The status code of the redirect
     * @param boolean $exit Will the script exit.
     * @return array|void Either an array or null.
     * @link @link http://book.cakephp.org/2.0/en/controllers/components.html#Component::beforeRedirect
     */
    public function beforeRedirect(Controller $controller, $url, $status = null, $exit = true) {
    	$this->call('beforeRedirect',array($url, $status, $exit));
    }

}