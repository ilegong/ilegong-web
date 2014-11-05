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
	
	function saveCookieCart(){
		$product_ids = array();$product_nums = array();
		$products = $this->Product->find('all',array(
				'conditions'=>array(
					'id' => $product_ids,
			)));
		foreach($products as $productinfo){
			$data = array();
			$data['Cart']['product_id'] = $productinfo['id'];
			$data['Cart']['num'] = $product_nums[$productinfo['id']];
			$data['Cart']['num'] = $productinfo['id'];
			$data['Cart']['session_id'] = $this->Session->id();
			$data['Cart']['coverimg'] = $productinfo['Product']['coverimg'];
			$data['Cart']['name'] = $productinfo['Product']['name'];
			$data['Cart']['price'] = $productinfo['Product']['price'];
			$data['Cart']['creator'] = $this->currentUser['id'];
			$this->Cart->save($data);
		}
		
	}
	
	function add(){

        //清除Flash信息，避免登录信息显示出来
        $this->Session->delete('Message.flash');

		$carts = array();
		if(!empty($this->data)){
			$this->autoRender = false;
			$Carts = $this->Cart->find('first',array(
				'conditions'=>array(
					'product_id' => $this->data['Cart']['product_id'],
					'order_id' => null,
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

    //FIXME: authorized
	function editCartNum($id,$num){
		if($num<=0){
			$op_flag = $this->Cart->deleteAll(array('id' => $id,'status' => 0,'order_id' => NULL,'OR'=> $this->user_condition), true, true);
		}
		else{
			$op_flag = $this->Cart->updateAll(array('num'=>$num), array('id' => $id,'status' => 0,'order_id' => NULL,'OR'=> $this->user_condition));
		}
		$successinfo = array('success' => __('Success edit nums.'));
		echo json_encode($successinfo);
		exit;
	}

    //FIXME: check authorized problem
	function cart_total(){
        $count = 0;
        if($this->currentUser && $this->currentUser['id']) {
            $count = $this->Cart->find('count', array(
                'conditions' => array(
                    'status' => 0,
                    'order_id' => NULL,
                    'OR' => $this->user_condition
                )
            ));
        }
		$successinfo = array('count' => $count);
		echo json_encode($successinfo);
		exit;
	}
	
	function listcart(){

        if (empty($this->currentUser['id'])) {
            $this->redirect("/users/login?referer=/carts/listcart");
        }

        $dbCartItems = $this->Cart->find('all',array(
				'conditions'=>array(
					'status' => 0,
					'order_id' => NULL,
					'OR'=> $this->user_condition
			)));
        $cartsByPid = Hash::combine($dbCartItems, '{n}.Cart.product_id', '{n}.Cart');
        if(!empty($_COOKIE['cart_products'])){
            $info = explode(',', $_COOKIE['cart_products']);
            mergeCartWithDb($this->currentUser['id'], $info, $cartsByPid, ClassRegistry::init('Product'), $this->Cart);
            setcookie("cart_products", '',time()-3600,'/');
        }

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

        $this->pageTitle = __('购物车');
	}
	
	function delete($id){
		$this->Cart->deleteAll(array(
				'status' => 0,
				'id' =>$id,
				'OR' => $this->user_condition
		));
		$this->redirect('/carts/listcart.html');
	}
       
}