<?php
/**
 * AclGenerate Component
 *
 * PHP version 5
 *
 * @category Component
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');
class AclGenerateComponent extends Component {

    /**
     * @param object $controller controller
     * @param array  $settings   settings
     */
    function initialize(&$controller, $settings = array()) {
		$this->controller = $controller;
        $this->folder = new Folder;
	}

    /**
     * List all controllers (including plugin controllers)
     *
     * @return array
     */
    function listControllers() {
        $controllerPaths = array();
        // app/controllers
        $this->folder->path = APP.'Controller'.DS;
        $controllers = $this->folder->read();
        foreach ($controllers['1'] AS $c) {
            $cName = Inflector::camelize(str_replace('Controller.php', '', $c));
            $controllerPaths[$cName] = APP.'Controller'.DS.$c;
        }

        // plugins/*/controllers/
        $this->folder->path = APP.'Plugin'.DS;
        $plugins = $this->folder->read();
        foreach ($plugins['0'] AS $p) {
            if ($p != 'install') {
                $this->folder->path = APP.'Plugin'.DS.$p.DS.'Controller'.DS;
                $pluginControllers = $this->folder->read();
                foreach ($pluginControllers['1'] AS $pc) {
                    $pcName = Inflector::camelize(str_replace('Controller.php', '', $pc));
                    $controllerPaths[$pcName] = APP.'Plugin'.DS.$p.DS.'Controller'.DS.$pc;
                }
            }
        }

        return $controllerPaths;
    }

    /**
     * List actions of a particular Controller.
     *
     * @param string  $name Controller name (the name only, without having Controller at the end)
     * @param string  $path full path to the controller file including file extension
     * @param boolean $all  default is false. it true, private actions will be returned too.
     *
     * @return array
     */
    function listActions($name, $path) {
        // base methods
        if (strpos($path, 'app'.DS.'Plugin')) {
            $plugin = $this->getPluginFromPath($path);
            $pacName = Inflector::camelize($plugin) . 'AppController'; // pac - PluginAppController
            $pacPath = APP.'Plugin'.DS.$plugin.DS.$plugin.'AppController.php';
            App::import('Controller', $pacName, null, null, $pacPath);
            $baseMethods = get_class_methods($pacName);
        } else {
            $baseMethods = get_class_methods('AppController');
        }

        $controllerName = $name.'Controller';
        App::uses($controllerName,'Controller');
        
//         App::import('Controller', $controllerName, null, null, $path);
        $methods = get_class_methods($controllerName);
        // filter out methods
        if(empty($methods))
        	$methods = array();
        foreach ($methods AS $k => $method) {
            if (strpos($method, '_', 0) === 0) {
                unset($methods[$k]);
                continue;
            }
            if (in_array($method, $baseMethods)) {
                unset($methods[$k]);
                continue;
            }
        }
        $methods = array_merge($methods,array(
        		'admin_add','admin_view',
        		'admin_edit','admin_publish','admin_trash',
        		'admin_batchEdit',
        		'admin_restore',
        		//'admin_treerecover', //不出现在权限列表中，仅超级管理员可操作修复（模板链接或者手动输入）
        //'admin_select',// select加入到allowedActions中
        		'admin_delete','admin_list',
        		
        		));
        $methods = array_unique($methods);
        return $methods;
    }

    /**
     * Get plugin name from path
     *
     * @param string $path file path
     *
     * @return string
     */
    function getPluginFromPath($path) {
        $pathE = explode(DS, $path);
        $pluginsK = array_search('Plugin', $pathE);
        $pluginNameK = $pluginsK + 1;
        $plugin = $pathE[$pluginNameK];

        return $plugin;
    }

}
?>