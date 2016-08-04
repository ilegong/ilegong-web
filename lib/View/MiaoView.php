<?php
/**
 * @author Administrator
 *
 */

App::uses('ThemeView', 'View');
class MiaoView extends View{

	private $layout_flag = false;
	private $view_flag = false;
	private $element_flag = false;
	private $controller;
	private $__themePath = array();
	private $__viewFileName = '';
	
	public $helpers = array('TplParse');
	public $ext = '.html';

	
	
	var $controller_name = '';
	var $convertCode = false;
	var $tpls = array();

	public function __construct(&$controller) {
		parent::__construct($controller);
        $this->theme = $controller->theme ? $controller->theme : 'default';
        if ($controller->name == 'CakeError') {
            $this->layoutPath = null;
            $this->viewPath = 'Errors';
        } else {
            $this->controller_name = Inflector::underscore($controller->name);
            $this->__viewFileName = $controller->__viewFileName;
            $this->convertCode = $controller->convertCode;
        }
        $this->ext = '.html';
	}
	
	private function _getThemePaths($paths,$with_view_path = false){
		if (!empty($this->theme)) {
			$themePaths = array();
			$count = count($paths);
			/**
			 * 添加default的theme主题目录，当模板在当前主题不存在时，调用default主题的模板
			 */
			for ($i = 0; $i < $count; $i++) {
				if (strpos($paths[$i], DS . 'Plugins' . DS) === false
						&& strpos($paths[$i], DS . 'Cake' . DS . 'View') === false) {
					$themePaths[] = $paths[$i] . 'Themed'. DS . $this->theme . DS;
					if($this->theme!='default'){
						$themePaths[] = $paths[$i] . 'Themed'. DS . 'default' . DS;
					}
				}
			}
			/**
			 * 是否添加View目录返回，当前不包含只支持themed目录
			 */
			if($with_view_path){
				$themePaths = array_merge($themePaths,$paths);
			}
		}
		return $themePaths;//return $paths;
	}
	
	/**
	 * 插件时，支持模板只放在view目录下，不必放在插件的Themed/default下
	 */
	public function getThemePaths(){
		if(!empty($this->__themePath)){
			return $this->__themePath;
		}
		$plugin = Inflector::underscore($this->plugin);
		
		$paths = $plugin_paths = array();
		$paths = App::path('View');		
		$paths = $this->_getThemePaths($paths);
		if (!empty($plugin)) {
			$plugin_paths = App::path('View', $plugin);
			$plugin_paths = $this->_getThemePaths($plugin_paths,true);
		}
		// 当运行插件时，优先选择plugin目录下的模版。
		$this->__themePath = array_merge($plugin_paths,$paths);
		return $this->__themePath;
	}

	/**
	 * 重载，减少没必要的paths可选的值。减少file_exists判断的量，节省时间 
	 * 插件时，支持模板只放在view目录下，不必放在插件的Themed/default下
	 * @param $plugin
	 * @param $cached
	 */
	protected function _paths($plugin = null, $cached = true) {
		if ($plugin === null && $cached === true && !empty($this->__paths)) {
			return $this->__paths;
		}
		$paths = $plugin_paths = array();
		$paths = App::path('View');
// 		print_r($paths);
		$paths = $this->_getThemePaths($paths);
		if (!empty($plugin)) {
			$plugin_paths = App::path('View', $plugin);
			$plugin_paths = $this->_getThemePaths($plugin_paths,true);
		}
		// 当运行插件时，优先选择plugin目录下的模版。
		$paths = array_merge($plugin_paths,$paths);
		return $paths;
	}

	/**
	 * render生成页面，使用cache记录viewFileName,避免遍历文件夹查找模版文件，其余处理和parent方法一样。
	 * @param type $view
	 * @param type $layout
	 * @return type
	 */
	public function render($view = null, $layout = null) {
		if ($this->hasRendered) {
			return true;
		}
		if (!$this->_helpersLoaded) {
			$this->loadHelpers();
		}
		if ($view !== false) {
			if(is_array($this->__viewFileName)){
				$vkey = implode('_',$this->__viewFileName);
			}
			else{
				$vkey = $this->__viewFileName;
			}
			$cache_key = APP_DIR.'view_fpath_'.$this->theme.'_'.$this->viewPath.'_'.$view.'_'.$this->action.'_'.$vkey.'_'.$layout;
			$viewFileName = Cache::read($cache_key);
			
			if($viewFileName === false){
                $this->__viewFileName = $view;
				$viewFileName = $this->_getViewFileName($view);
				Cache::write($cache_key, $viewFileName);
			}
			if($viewFileName){
				$this->Helpers->trigger('beforeRender', array($viewFileName));
				$this->output = $this->_render($viewFileName);
				$this->Helpers->trigger('afterRender', array($viewFileName));
			}
		}

		if ($layout === null) {
			$layout = $this->layout;
		}
		if ($this->output === false) {
			throw new CakeException(__d('cake_dev', "Error in view %s, got no content.", $viewFileName));
		}
		if ($layout && $this->autoLayout) {
			$this->output = $this->renderLayout($this->output, $layout);
		}
		$this->hasRendered = true;
		return $this->output;
	}

	protected function _render($___viewFn, $___dataForView = array())
	{
		if (empty($___dataForView)) {
			$___dataForView = $this->viewVars;
		}
		extract($___dataForView, EXTR_SKIP);
		if($this->_currentType!=self::TYPE_LAYOUT){
			$this->viewVars['viewfile_for_layout'] = str_replace(array(ROOT,'\\'),array('','/'),$___viewFn);
		}
		ob_start();
		$tpl_cache_file = $this->TplParse->gettpl($___viewFn);
		$this->tpls['cache_tpl_file'] = $tpl_cache_file ;
		include $tpl_cache_file;
		$content = ob_get_clean();
		if($this->convertCode=='g2b'){
			App::uses('Utf8G2b','Lib');
			$trans = new Utf8G2b;
			$content = $trans->g2b($content);
		}
		return $content;
	}
	
	public function getViewFileName($action) {
		return $this->_getViewFileName($action);
	}

	/**
	 * 获取显示模板 
	 * @param string $action
	 */
	protected function _getViewFileName($action='',$loop = false) {
		$action = Inflector::underscore($action);
		if (empty($action)) {
			$action = $this->action;
		}
		if(!$loop && is_string($this->__viewFileName) && $this->__viewFileName!=$action){
			//若指定了特殊模板文件，则模板的选择优先特殊模板，不存在时，使用默认action对应的模板
			$this->__viewFileName = array($this->__viewFileName,$action);
		}
		
		if(is_array($this->__viewFileName)&& !$loop){ /*为数组时，递归调用。*/
			if(!in_array($action,$this->__viewFileName)){
				$this->__viewFileName[] = $action;
			}
			foreach($this->__viewFileName as $viewfilename){
				if($ret = $this->_getViewFileName($viewfilename,true)){
					return $ret;
				}
			}
			CakeLog::error('Missing Template file '.$this->controller_name."\n".var_export($this->__viewFileName,true).$this->ext);
			return false;
		}
		if(!$this->__viewFileName || is_array($this->__viewFileName) ){
			$this->__viewFileName = $action;
		}
		
		if(!in_array($this->viewPath,array('Layouts','Elements','Errors'))){
                $this->viewPath = $this->controller_name; // when not use this. taobao_shops turns to taobaoshops
                $this->viewPath = strtolower($this->viewPath); // 将$this->viewPath转换成小写，模块对应的模板目录名称都用的小写。
		}
		$paths = $this->getThemePaths();
		foreach($paths as $path) {
			// 在对应的$this->viewPath（controller_name）文件夹下有模版，则使用此文件夹的模版。
			$__viewFileName = $path . $this->viewPath . DS  . $action . $this->ext;
			if (file_exists($__viewFileName)) {
				return $__viewFileName;
			}
			elseif (file_exists($__viewFileName = $path . $this->viewPath . DS  . $action . '.ctp')) {
				return $__viewFileName;
			}
			// 在模版主题的根目录下找对应的模板，若存在则调用主题下的模板
			$__viewFileName = $path.$action.$this->ext;
			if(file_exists($__viewFileName)){
				return $__viewFileName;
			}
			elseif (file_exists($__viewFileName = $path .$action . '.ctp')) {
				return $__viewFileName;
			}
		}
		CakeLog::error('Missing Template file '.$this->controller_name.DS.$action.$this->ext);
		return false;
	}

    public function thumb_by_nettype() {
//        medium_thumb_link();
    }
}
?>
