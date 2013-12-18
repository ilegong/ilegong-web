<?php
/**
 * ExampleHook Helper
 *
 * An example hook helper for demonstrating hook system.
 *
 * @category Helper
 * @package  Croogo
 * @version  1.0
 * @author   Fahad Ibnay Heylaal <contact@fahad19.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
class ViewpointsHookHelper extends AppHelper {
/**
 * Other helpers used by this helper
 *
 * @var array
 * @access public
 */
    public $helpers = array(
        'Html',
    );
/**
 * Called after activating the hook in ExtensionsHooksController::admin_toggle()
 *
 * @param object $controller Controller
 * @return void
 */
    public function onActivate(&$controller) {
    	
    }
/**
 * Called after deactivating the hook in ExtensionsHooksController::admin_toggle()
 *
 * @param object $controller Controller
 * @return void
 */
    public function onDeactivate(&$controller) {
    	
    }
	
	/**
	在正文页内容后，追加内容
	*/
	public function affterContent($model,$dataid,$action='view')
	{
		return $this->_View->element('digg',array(),array('plugin'=>'Communicate'))
			.$this->_View->element('viewpoints',array(),array('plugin'=>'Communicate'))
			.$this->_View->element('next_items',array(),array('plugin'=>'Communicate'));
	}
	
	/**
	* 在页面底部追加内容
	*/
	public function appendFooter($model,$dataid,$action)
	{
		if($action=='view'){
			return '<script type="text/javascript" src="'.Router::url('/stats_days/numlog/'.$model.'/'.$data_id.'/view').'"></script>';
		}
	}
    
}
?>