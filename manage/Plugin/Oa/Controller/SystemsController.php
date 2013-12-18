<?php

class SystemsController extends AppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
    var $name = 'Systems';

    function admin_index() {
    	$roles = $this->Auth->user('role_id');
    	if(!in_array(1,$roles)){
    		$menuid = 53;
    	}
    	else{
	    	if(empty($_GET['menu'])){
	    		$_GET['menu'] = 1; //30
	    	}
	    	$menuid = $_GET['menu'];
    	}
        $this->layout = 'admin_layout';
        $this->pageTitle = __('System Index', true);        
        $this->set('menuid', $menuid);        
    }
    
}
?>