<?php

class OrdersController extends AppController{
	
	var $name = 'Orders';
	
	public function admin_view($id){
		$this->loadModel('Cart');
		$carts = $this->Cart->find('all',array(
			'conditions' => array( 'order_id' => $id ),		
		));
		$this->set('carts',$carts);
		parent::admin_view($id);				
	}
	
}