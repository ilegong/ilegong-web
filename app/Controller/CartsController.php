<?php
class CartsController extends AppController{
	
	var $name = 'Carts';	
	
	var $user_condition = array();
	
	function beforeFilter(){
		parent::beforeFilter();
		$this->user_condition = array(
			'session_id'=>	$this->Session->id(),
		);
		if($this->currentUser['id']){
			$this->user_condition['creator']=$this->currentUser['id'];
		}
	}
	function add(){
		
		$carts = array();
		if(!empty($this->data)){
			$this->autoRender = false;
			$Carts = $this->Cart->find('first',array(
				'conditions'=>array(
					'product_id' => $this->data['Cart']['product_id'],
					'OR'=> $this->user_condition
			)));
			if(!empty($Carts)){
				$this->data['Cart']['id'] = $Carts['Cart']['id'];
				$this->data['Cart']['num'] += $Carts['Cart']['num'];
			}
			
			$productid = $this->data['Cart']['product_id'];
			
			$this->loadModel('Product');
			$productinfo = $this->Product->findById($productid);
			
			$this->data['Cart']['session_id'] = $this->Session->id();			
			$this->data['Cart']['coverimg'] = $productinfo['Product']['coverimg'];
			$this->data['Cart']['name'] = $productinfo['Product']['name'];
			$this->data['Cart']['price'] = $productinfo['Product']['price'];
			$this->data['Cart']['creator'] = $this->currentUser['id'];
			$this->Cart->save($this->data);
			
			$successinfo = array('success' => __('Success add to cart.'));
			echo json_encode($successinfo);
			exit;
		}
		$errorinfo = array('save_error' => __('Operation failed'));
        echo json_encode($errorinfo);
        exit;
	}
	
	function listcart(){
		$Carts = $this->Cart->find('all',array(
				'conditions'=>array(
					'status' => 0,
					'order_id' => NULL,
					'OR'=> $this->user_condition
			)));
		$total_price = 0;
		foreach($Carts as $cart){
			$total_price += $cart['Cart']['price']*$cart['Cart']['num'];
		}
		$this->set('total_price',$total_price);
		$this->set('Carts',$Carts);
	}
	
	function delete($id){
		$this->Cart->deleteAll(array(
				'status' => 0,
				'id' =>$id,
				'OR' => $this->user_condition
		));
		$this->redirect($this->referer());
	}
       
}