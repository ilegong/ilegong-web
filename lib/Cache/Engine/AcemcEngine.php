<?php
/* SVN FILE: $Id$ */
/**
 * Memcache storage engine for cache
 *
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
 * @subpackage    cake.cake.libs.cache
 * @since         CakePHP(tm) v 1.2.0.4933
 * @version       $Revision$
 * @modifiedby    $LastChangedBy$
 * @lastmodified  $Date$
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Memcache storage engine for cache
 *
 * @package       cake
 * @subpackage    cake.cake.libs.cache
 */
class AcemcEngine extends CacheEngine {
/**
 * Memcache wrapper.
 *
 * @var Memcache
 * @access private
 */
	var $_Memcache = null;
/**
 * settings
 * 		servers = string or array of memcache servers, default => 127.0.0.1
 * 		compress = boolean, default => false
 *
 * @var array
 * @access public
 */
	var $settings = array();
	
/**
 * Initialize the Cache Engine
 *
 * Called automatically by the cache frontend
 * To reinitialize the settings call Cache::engine('EngineName', [optional] settings = array());
 *
 * @param array $setting array of setting for the engine
 * @return boolean True if the engine has been successfully initialized, false if not
 * @access public
 */
	function init($settings = array()) {
		$this->settings =array_merge(array(
			'engine'=> 'Memcache', 'prefix' => Inflector::slug(APP_DIR) . '_', 'servers' => array('127.0.0.1'), 'compress'=> false
			),$settings);
		
		$this->_Memcache =  new Memcache;
		$this->_Memcache->init();
		if($this->_Memcache == false){
			// memcache初始化失败时，不使用缓存。
			Configure::write('Cache.disable', true);
			return false;
		}
		else{
			return $this->_Memcache;
		}
	}
/**
 * Write data for key into cache
 *
 * @param string $key Identifier for the data
 * @param mixed $value Data to be cached
 * @param integer $duration How long to cache the data, in seconds
 * @return boolean True if the data was succesfully cached, false on failure
 * @access public
 */
	function write($key, &$value, $duration) {
		return $this->_Memcache->set($key,$value,$this->settings['compress'],  $duration);
	}
/**
 * Read a key from the cache
 *
 * @param string $key Identifier for the data
 * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
 * @access public
 */
	function read($key) {
		return $this->_Memcache->get($key);
	}
/**
 * Delete a key from the cache
 *
 * @param string $key Identifier for the data
 * @return boolean True if the value was succesfully deleted, false if it didn't exist or couldn't be removed
 * @access public
 */
	function delete($key) {
		return $this->_Memcache->delete($key);
	}
/**
 * Delete all keys from the cache
 *
 * @return boolean True if the cache was succesfully cleared, false otherwise
 * @access public
 */
	function clear() {
		return $this->_Memcache->flush();
	}
/**
 * Connects to a server in connection pool
 *
 * @param string $host host ip address or name
 * @param integer $port Server port
 * @return boolean True if memcache server was connected
 * @access public
 */
	function connect($host, $port = 11211) {
		return true;
	}
	
	public function increment($key, $offset = 1) {		
		return $this->_Memcache->increment($key, $offset);
	}
	
	/**
	 * Decrements the value of an integer cached key
	 *
	 * @param string $key Identifier for the data
	 * @param integer $offset How much to subtract
	 * @return New decremented value, false otherwise
	 * @throws CacheException when you try to decrement with compress = true
	 */
	public function decrement($key, $offset = 1) {		
		return $this->_Memcache->decrement($key, $offset);
	}
}
?>
