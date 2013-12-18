<?php
/**
 * Plugin Component 插件组件，
 * @author sina
 *
 */
class PluginComponent extends Component {
	
	public $components = array();
	/**
 * Hook components
 *
 * @var array
 * @access public
 */
	public $hooks = array();
	
	public function __construct($options = array()) {        
        return parent::__construct($options);
    }
/**
 * Startup
 *
 * @param object $controller instance of controller
 * @return void
 */
    public function startup(&$controller) {
        //$this->controller = $controller;        
    }    
	
    public function initialize(){
    	
    }
}