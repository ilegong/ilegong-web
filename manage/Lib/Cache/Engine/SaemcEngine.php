<?php
/* SVN FILE: $Id$ */
class SaemcEngine extends CacheEngine {

	var $_Memcache = null;

	var $settings = array();

	function init($settings = array()) {
		$this->settings =array_merge(array(
			'engine'=> 'Memcache', 'prefix' => Inflector::slug(APP_DIR) . '_', 'servers' => array('127.0.0.1'), 'compress'=> false
			),$settings);
		$this->_Memcache = @memcache_init();
		if($this->_Memcache == false){
			return false;
		}
		else{
			return $this->_Memcache;
		}
	}

	function write($key, $value, $duration) {
		if($this->_Memcache == false){
			return false;
		}
// 		echo 'write cache'.$key.' in page '.$_SERVER['REQUEST_URI'].'<br/>';
		//http://ideacms-ideacms.stor.sinaapp.com/mc_20130922.txt
 		//file_put_contents('saestor://upload/mc_'.date('Ymd').'.txt', 'write cache'.$key."\r\n".strlen(var_export($value,true)),FILE_APPEND );
		return $this->_Memcache->set($key,$value,$this->settings['compress'],  $duration);
	}

	function read($key) {
		if($this->_Memcache == false){
			return false;
		}
// 		echo 'read cache'.$key.' in page '.$_SERVER['REQUEST_URI'].'<br/>';
// 		file_put_contents('saemc://cache/mc.log', 'read cache'.$key."\r\n".var_export($_SERVER,true),FILE_APPEND );
		return $this->_Memcache->get($key);
	}

	function delete($key) {
		if($this->_Memcache == false){
			return false;
		}
		return $this->_Memcache->delete($key);
	}

	function clear($check) {
		if($this->_Memcache == false){
			return false;
		}
		return $this->_Memcache->flush();
	}

	function connect($host, $port = 11211) {
		return true;
	}
	
	public function increment($key, $offset = 1) {
		if($this->_Memcache == false){
			return false;
		}
		return $this->_Memcache->increment($key, $offset);
	}
	
	public function decrement($key, $offset = 1) {
		if($this->_Memcache == false){
			return false;
		}
		return $this->_Memcache->decrement($key, $offset);
	}
}
?>