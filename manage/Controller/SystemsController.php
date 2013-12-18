<?php

class SystemsController extends AppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
    public $name = 'Systems';
    
    public $uses = array(); //This will allow you to use a controller without a need for a corresponding Model file.

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
//     	$menuid =1;
    	/* 多语言 */
    	
    	if (isset($this->request->params['locale'])) {
    		$locale = $this->request->params['locale'];
    	}
    	else{
    		$locale = Configure::read('Config.language');
    	}
    	if(preg_match('/MSIE 6.0/i',$_SERVER['HTTP_USER_AGENT'])){
    		$this->layout = 'ie6';
    	}
    	else{
        	$this->layout = 'admin_layout';
    	}
    	$this->Session->write('menuid',$menuid); // admin/menus/menu中调用了
        $this->pageTitle = __('System Index', true);        
        $this->set('menuid', $menuid);   
        $this->set('locale', $locale);
        
    }
    
}
?>