<?php

App::uses('MPlugin', 'Extensions.Lib');
App::uses('Sanitize', 'Utility');

class ExtensionsController extends AppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
    public $name = 'Extensions';
/**
 * Models used by the Controller
 *
 * @var array
 * @access public
 */
    public $uses = array(
        'Setting',
        'Language',
    );
    
    public function beforeFilter(){
    	parent::beforeFilter();
    	
    	$this->_MPlugin = new MPlugin();
    	$this->_MPlugin->setController($this);
    }

    public function admin_index($id = null, $modelAlias = null) {
    	
        $this->set('title_for_layout', __('Plugins', true));
        $plugins = $this->_MPlugin->plugins();
        $this->set(array('plugins'=>$plugins));
    }
	
	public function admin_toggle($plugin = null) {
        if (!$plugin) {
            $this->Session->setFlash(__('Invalid plugin', true), 'default', array('class' => 'error'));
            $this->redirect(array('action' => 'index'));
        }
        if ($this->_MPlugin->isActive($plugin)) {
			$result = $this->_MPlugin->deactivate($plugin);
			if ($result === true) {
				$this->Session->setFlash(__d('Plugin "%s" deactivated successfully.', $plugin), 'default', array('class' => 'success'));
			} elseif (is_string($result)) {
				$this->Session->setFlash($result, 'default', array('class' => 'error'));
			} else {
				$this->Session->setFlash(__d('Plugin could not be deactivated. Please, try again.'), 'default', array('class' => 'error'));
			}
		} else {
			$result = $this->_MPlugin->activate($plugin);
			if ($result === true) {
				$this->Session->setFlash(__d('croogo', 'Plugin "%s" activated successfully.', $plugin), 'default', array('class' => 'success'));
			} elseif (is_string($result)) {
				$this->Session->setFlash($result, 'default', array('class' => 'error'));
			} else {
				$this->Session->setFlash(__d('croogo', 'Plugin could not be activated. Please, try again.'), 'default', array('class' => 'error'));
			}
		}
		
		$this->redirect(array('action' => 'index'));
    }
    
    /**
     * Move up a plugin in bootstrap order
     *
     * @param string $plugin
     * @throws CakeException
     */
    public function admin_moveup($plugin = null) {
    	if ($plugin === null) {
    		throw new CakeException(__('Invalid plugin'));
    	}
    
    	$class = 'success';
    	$result = $this->_MPlugin->move('up', $plugin);
    	if ($result === true) {
    		$message = __('Plugin %s has been moved up', $plugin);
    	} else {
    		$message = $result;
    		$class = 'error';
    	}
    	$this->Session->setFlash($message, 'default', array('class' => $class));
    	Configure::dump('Settings','default',array('Hook'));
    	$this->redirect($this->referer());
    }
    
    /**
     * Move down a plugin in bootstrap order
     *
     * @param string $plugin
     * @throws CakeException
     */
    public function admin_movedown($plugin = null) {
    	if ($plugin === null) {
    		throw new CakeException(__('Invalid plugin'));
    	}
    
    	$class = 'success';
    	
    	$result = $this->_MPlugin->move('down', $plugin);
    	if ($result === true) {
    		$message = __('Plugin %s has been moved down', $plugin);
    	} else {
    		$message = $result;
    		$class = 'error';
    	}
    	$this->Session->setFlash($message, 'default', array('class' => $class));
    	Configure::dump('Settings','default',array('Hook'));
    	print_r(Configure::read('Hook'));
    	exit;
    	$this->redirect($this->referer());
    }

}
?>