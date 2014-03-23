<?php

class BrandsController extends AppController {

    var $name = 'Brands';
    
    public function view($slug='/'){
    	$modelClass = $this->modelClass;
    	
    	parent::view($slug);
    	
    	$id = $this->viewdata[$modelClass]['id'];
    	$this->loadModel('Product');
    	$products = $this->Product->find('all',array(
    		'conditions' => array(
    			'brand_id' => $id,
    			'Product.published' => 1, 
    			'Product.deleted' => 0
    		),
    		'limit'=>'20',
    	));
    	$this->set('products',$products);
    }
}
?>