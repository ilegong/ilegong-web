<?php
/* SVN FILE: $Id$ */

/**
 * Short description for file.
 *
 * This file is application-wide helper file. You can put all
 * application-wide helper-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake
 * @since         CakePHP(tm) v 0.2.9
 * @version       $Revision$
 * @modifiedby    $LastChangedBy$
 * @lastmodified  $Date$
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::uses('Helper','View');

/**
 * This is a placeholder class.
 * Create the same file in app/app_helper.php
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       cake
 * @subpackage    cake.cake
 */
class AppHelper extends Helper {
	function url($url = null, $full = false) {
// 		if($_GET['output']=='pdf'){
// 			$full = true;
// 			defined('FULL_BASE_URL','http://'.$_SERVER['HTTP_HOST']);
// 		}
		if(is_array($url)){
			/**
			 * 登录链接不带ext，否则导致AuthComponent中判断$loginAction == $url不成立
			 */
			if(isset($url['ext']) && $url['ext']===false){ // 使用ext=>false,时去除ext。
				unset($url['ext']);
			}
			elseif(!isset($url['ext']) || empty($url['ext'])) { // 默认使用html后缀
				$url['ext']='html';
			}
		}
		else{
			if($url!='/' && substr($url,-1)=='/'){
				$url = substr($url,0,-1); //若已“/”结尾，则去掉末尾的斜线。
			}
			$urlinfo = parse_url($url);
			if(empty($urlinfo['host']) || $urlinfo['host']==$_SERVER['HTTP_HOST']){ 
				// 仅处理本站的链接地址，外站的不处理。
				if(isset($urlinfo['path'])){
					$pathinfo = pathinfo($urlinfo['path']);				
					if(isset($pathinfo["extension"]) && in_array(strtolower($pathinfo["extension"]),array('js','css','png','bmp','gif','jpg'))){
						if(defined('APPEND_LOCALE_BASE')){
							// 图片，js，css等，去除多语言目录的前缀
							$url='/../'.$url;
						}
					}
				}
			}
		}
		$url = parent::url($url, $full);
		$url = $this->fixurl($url);
		return $url;
	}
	
	/**
	 * 替换url中的"/..",切换到上一级目录。 /updir/dir/../xxx  => /updir/xxx
	 * @param string $url
	 */
	private function fixurl($url){
		if(strpos($url,'http://')!==false){//网址时直接返回（外站的地址）
			return $url;
		}
		$url = str_replace('//','/',$url);
		while(strpos($url,'/..')!==false){
			$url = preg_replace('|/([^/]+?)/\.\.|iU','',$url);
		}
		return $url;
	}

    /**
     * Generate url for given asset file. Depending on options passed provides full url with domain name.
     * Also calls Helper::assetTimestamp() to add timestamp to local files
     *
     * @param string|array Path string or url array
     * @param array $options Options array. Possible keys:
     *   `fullBase` Return full url with domain name
     *   `pathPrefix` Path prefix for relative URLs
     *   `ext` Asset extension to append
     *   `plugin` False value will prevent parsing path as a plugin
     * @return string Generated url
     */
    public function assetUrl($path, $options = array()) {
        if (is_array($path)) {
            return $this->url($path, !empty($options['fullBase']));
        }
        if (strpos($path, '://') !== false) {
            return $path;
        }
        if (!array_key_exists('plugin', $options) || $options['plugin'] !== false) {
            list($plugin, $path) = $this->_View->pluginSplit($path, false);
        }
        if (!empty($options['pathPrefix']) && $path[0] !== '/') {
            $path = $options['pathPrefix'] . $path;
        }
        if (
            !empty($options['ext']) &&
            strpos($path, '?') === false &&
            substr($path, -strlen($options['ext'])) !== $options['ext']
        ) {
            $path .= $options['ext'];
        }
        if (isset($plugin)) {
            $path = Inflector::underscore($plugin) . '/' . $path;
        }

        $path = $this->_encodeUrl($this->assetTimestamp($this->webroot($path)));
        $path = rtrim(Configure::read('App.assetsUrl'), '/') . '/' . ltrim($path, '/');

        return $path;
    }
}
?>