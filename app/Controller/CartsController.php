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
        $uid = $this->currentUser['id'];
		foreach($products as $productinfo){
			$data = array();
			$data['Cart']['product_id'] = $productinfo['id'];
			$data['Cart']['num'] = $product_nums[$productinfo['id']];
			$data['Cart']['num'] = $productinfo['id'];
			$data['Cart']['session_id'] = $this->Session->id();
			$data['Cart']['coverimg'] = $productinfo['Product']['coverimg'];
			$data['Cart']['name'] = $productinfo['Product']['name'];
            $data['Cart']['price'] = calculate_price($productinfo['Product']['id'], $productinfo['Product']['price'], $uid);
			$data['Cart']['creator'] = $uid;
			$this->Cart->save($data);
		}
		
	}
	
	function add(){

        //清除Flash信息，避免登录信息显示出来
        $this->Session->delete('Message.flash');

		$carts = array();
		if(!empty($this->data)){
			$this->autoRender = false;
            $product_id = $this->data['Cart']['product_id'];
            $num = $this->data['Cart']['num'];
            $specId = $this->data['Cart']['spec'];
            $this->_addToCart($product_id, $num, $specId);
			
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

	function cart_total(){
        $count = $this->Cart->find('count', array(
            'conditions' => array(
                'status' => 0,
                'order_id' => NULL,
                'OR' => $this->user_condition
            ),
        ));
		$successinfo = array('count' => $count);
		echo json_encode($successinfo);
		exit;
	}

	function listcart(){
        $dbCartItems = $this->Cart->find('all',array(
				'conditions'=>array(
					'status' => 0,
					'order_id' => NULL,
					'OR'=> $this->user_condition
			)));
        $cartsByPid = Hash::combine($dbCartItems, '{n}.Cart.product_id', '{n}.Cart');
        $poductModel = ClassRegistry::init('Product');
        if(!empty($_COOKIE['cart_products'])){
            $info = explode(',', $_COOKIE['cart_products']);
            mergeCartWithDb($this->currentUser['id'], $info, $cartsByPid, $poductModel, $this->Cart, $this->Session->id());
            setcookie("cart_products", '',time()-3600,'/');
        }

        $Carts = $this->Cart->find('all',array(
            'conditions'=>array(
                'status' => 0,
                'order_id' => NULL,
                'OR'=> $this->user_condition
            )));

        //TODO: 此处修改通知用户购物车价格有变化！

		$total_price = 0;
		foreach($Carts as $cart){
			$total_price += $cart['Cart']['price']*$cart['Cart']['num'];
		}
		$this->set('total_price',$total_price);
		$this->set('Carts',$Carts);
        $this->set('op_cate', OP_CATE_CATEGORIES);
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

    /**
     * @param $product_id
     * @param int $num
     * @param int $spec specified id for
     * @return bool whether saved successfully
     */
    private function _addToCart($product_id, $num = 1, $spec = 0) {
        $Carts = $this->Cart->find('first', array(
            'conditions' => array(
                'product_id' => $product_id,
                'order_id' => null,
                'OR' => $this->user_condition
            )));
        if (!empty($Carts)) {
            $this->data['Cart']['id'] = $Carts['Cart']['id'];
        }

        $this->data['Cart']['num'] = $num;
        $this->data['Cart']['product_id'] = $product_id;

        $this->loadModel('Product');
        $p = $this->Product->findById($product_id);

        $this->data['Cart']['session_id'] = $this->Session->id();
        $this->data['Cart']['coverimg'] = $p['Product']['coverimg'];
        $this->data['Cart']['name'] = product_name_with_spec($p['Product']['name'], $spec, $p['Product']['specs']);;
        $this->data['Cart']['price'] = calculate_price($p['Product']['id'], $p['Product']['price'], $this->currentUser['id']);
        $this->data['Cart']['creator'] = $this->currentUser['id'];
        $this->data['Cart']['specId'] = $spec;
        return $this->Cart->save($this->data);
    }

}