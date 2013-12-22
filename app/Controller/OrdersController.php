<?php
class OrdersController extends AppController{
	
	var $name = 'Orders';	
	
	var $user_condition = array();
	
	function beforeFilter(){
		parent::beforeFilter();
		if(empty($this->currentUser['id'])){
			$this->redirect('/users/login?referer='.Router::url('/orders/info'));
		}
		$this->user_condition = array(
			'session_id'=>	$this->Session->id(),
		);
		if($this->currentUser['id']){
			$this->user_condition['creator']=$this->currentUser['id'];
		}
	}
	
	/**
	 * 结算提交订单，进入支付页面。
	 * @param $order_id
	 */
	function balance($order_id=''){
		$this->loadModel('Cart');
		/* 保存无线端cookie购物车的商品 */
		if(!empty($_COOKIE['cart_products'])){
			$this->loadModel('Product');
			$products = $this->Product->find('all',array('conditions'=>array(
					'id' => $product_ids
			)));
			$Carts = array();
			foreach($products as $p){
				$Cart = array('Cart'=>array(
						'product_id'=> $p['Product']['id'],
						'name'=> $p['Product']['name'],
						'coverimg'=> $p['Product']['coverimg'],
						'num'=> 1,
						'creator'=> $this->currentUser['id'],
						'price'=> $p['Product']['price'],
				));
				
				$this->Cart->create();
				if($this->Cart->save($Cart)){
					$Cart['id'] = $this->Cart->getLastInsertID();
					$Carts[] = $Cart;
				}
			}
		}
		else{
			$Carts = $this->Cart->find('all',array(
					'conditions'=>array(
							'status'=> 0,
							'order_id' => null,
							'OR'=> $this->user_condition
					)));
		}
		$total_price = $this->_calculateTotalPrice($Carts);
		$data = array();
		$data['total_price'] = $total_price;
		$data['creator'] = $this->currentUser['id'];
		$data['remark'] = $this->Session->read('Order.remark');
		$data['consignee_id'] = $this->Session->read('OrderConsignee.id');
		$data['consignee_name'] = $this->Session->read('OrderConsignee.name');
		$data['consignee_area'] = $this->Session->read('OrderConsignee.area');
		$data['consignee_address'] = $this->Session->read('OrderConsignee.address');
		$data['consignee_mobilephone'] = $this->Session->read('OrderConsignee.mobilephone');
		$data['consignee_telephone'] = $this->Session->read('OrderConsignee.telephone');
		$data['consignee_email'] = $this->Session->read('OrderConsignee.email');
		$data['consignee_postcode'] = $this->Session->read('OrderConsignee.postcode');
		if($this->Order->save($data)){
			$order_id = $this->Order->getLastInsertID();
			foreach($Carts as $cart){
				$this->Cart->updateAll(array('order_id'=>$order_id,'status'=>1),array('id'=>$cart['Cart']['id'],'creator'=>$this->currentUser['id']));
			}
			$this->__message('订单已生成','/orders/info/'.$order_id);
		}
		else{
			$this->__message('订单保存失败，请稍候重新提交','/order/info/');
		}		
	}
	
	/**
	 * 订单信息页，确认各项订单信息
	 * @param unknown_type $order_id
	 */
	function info($order_id=''){
		$this->loadModel('Cart');
		if(empty($order_id)){
			if(!empty($_COOKIE['cart_products'])){
				$product_ids = explode(',',$_COOKIE['cart_products']);
				$product_ids = array_delete_value($product_ids,'');
				$this->loadModel('Product');
				$products = $this->Product->find('all',array('conditions'=>array(
						'id' => $product_ids
				)));
				$Carts = array();
				foreach($products as $p){
					$Carts[] = array(
							'Cart'=>array(
									'product_id'=> $p['Product']['id'],
									'name'=> $p['Product']['name'],
									'coverimg'=> $p['Product']['coverimg'],
									'num'=> 1,
									'price'=> $p['Product']['price'],
					));
				}
			}
			else{
				$Carts = $this->Cart->find('all',array(
					'conditions'=>array(
						'status'=> 0,
						'order_id' => null,
						'OR'=> $this->user_condition
				)));
			}
		}
		else{
			$orderinfo = $this->Order->findById($order_id);			
			$Carts = $this->Cart->find('all',array(
				'conditions'=>array(					
					'order_id' => $order_id,
					'creator'=> $this->currentUser['id']
			)));
		}
		
		$current_consignee = $this->Session->read('OrderConsignee');
		if(empty($current_consignee)){
			$this->loadModel('OrderConsignee');
			$consignees = $this->OrderConsignee->find('first',array('conditions'=>array('creator'=>$this->currentUser['id'])));
			$current_consignee = array();
			// empty 不能检测函数，只能检测变量
			if(!empty($consignees)){
				$current_consignee = $consignees['OrderConsignee'];
			}
			else{
				$current_consignee['name'] = $this->Session->read('Auth.User.nickname');
				$current_consignee['email'] = $this->Session->read('Auth.User.email');
				$current_consignee['mobilephone'] = $this->Session->read('Auth.User.mobilephone');
				$current_consignee['telephone'] = $this->Session->read('Auth.User.telephone');
				$current_consignee['postcode'] = $this->Session->read('Auth.User.postcode');
				$current_consignee['address'] = $this->Session->read('Auth.User.address');
			}
			$this->Session->write('OrderConsignee',$current_consignee);
		}
		$total_price = $this->_calculateTotalPrice($Carts);
		$this->set('order_id',$order_id);
		$this->set('total_price',$total_price);
		$this->set('Carts',$Carts);
	}
	
	function _calculateTotalPrice($carts = array()){
		$total_price = 0.0;
		foreach($carts as $cart){
			$total_price += $cart['Cart']['price']*$cart['Cart']['num'];
		}
		return $total_price;
	}
	
	/**
	 * 编辑表单的保存，获取收件人信息文本内容（非表单形式）
	 */
	function info_consignee(){
		//$this->autoRender = false;
		if(!empty($this->data)){
			$this->loadModel('OrderConsignee');			
			$this->data['OrderConsignee']['creator'] = $this->currentUser['id'];
			$consignee = $this->OrderConsignee->find('first',array(
				'conditions'=>array(
					'creator' => $this->currentUser['id'],
					'name'=>$this->data['OrderConsignee']['name'],
					'address'=>$this->data['OrderConsignee']['address'],
					'area'=>$this->data['OrderConsignee']['area'],
				))
			);
			if($this->data['OrderConsignee']['edit_type']=='select'){
				$consignee = $this->OrderConsignee->find('first',array(
					'conditions'=>array(
						'id' => $this->data['OrderConsignee']['id'],
					))
				);
				$this->Session->write('OrderConsignee',$consignee['OrderConsignee']);
			}
			elseif(empty($consignee)){				
				if(!$this->OrderConsignee->save($this->data)){
					echo json_encode($this->{$this->modelClass}->validationErrors);
	                return;
				}
				if(empty($this->data['OrderConsignee']['id'])){
					$this->data['OrderConsignee']['id'] = $this->OrderConsignee->getLastInsertID();
				}
				$this->Session->write('OrderConsignee',$this->data['OrderConsignee']);
			}
			else{
				$this->Session->write('OrderConsignee',$consignee['OrderConsignee']);
				//echo json_encode(array('error' => __('Already have this address. If your still want to update this to Commonly used address,please delete it in Commonly used address at first.')));
                //return;
			}
						
			$successinfo = array(
				'success' => __('Add success'), 
				'tasks'=>array(array(
					'dotype'=> 'html',
					'selector'=> '#part_consignee',
					'content'=> $this->renderElement('order_consignee')
				))
			);
			echo json_encode($successinfo);
            exit;
		}
		else{
			echo $this->renderElement('order_consignee');
			exit;
		}
	}
	
	function edit_consignee(){
		// 常用地址列表，及收件人信息编辑表单
		$this->loadModel('OrderConsignee');
		$consignees = $this->OrderConsignee->find('all',array('conditions'=>array('creator'=>$this->currentUser['id'])));
		$this->set('consignees',$consignees);	
		if(count($consignees)<10){
			$this->Session->write('OrderConsignee.save_address',1);
		}
	}
	/**
	 * 设为默认地址
	 * @param int $id
	 */
	function default_consignee($id){
		$this->autoRender = false;
		$this->loadModel('OrderConsignee');
		$consignees = $this->OrderConsignee->updateAll(array('status'=>0),array('creator'=>$this->currentUser['id']));
		
		$consignees = $this->OrderConsignee->updateAll(array('status'=>1),array('creator'=>$this->currentUser['id'],'id'=> $id));
		$successinfo = array('id' => $id);
		echo json_encode($successinfo);
		exit;
	}
	/**
	 * 删除常用地址
	 * @param int $id
	 */
	function delete_consignee($id){
		$this->autoRender = false;
		$this->loadModel('OrderConsignee');
		$consignees = $this->OrderConsignee->deleteAll(array('creator'=>$this->currentUser['id'],'id'=> $id));
		$successinfo = array('id' => $id);
		echo json_encode($successinfo);
        exit;
	}
	/**
	 * 加载常用地址信息
	 * @param unknown_type $id
	 */
	function load_consignee($id){
		$this->autoRender = false;
		$this->loadModel('OrderConsignee');
		$consignee = $this->OrderConsignee->find('first',
		array(
			'conditions'=>array('id'=>$id,'creator'=>$this->currentUser['id']),
		));
		echo json_encode($consignee['OrderConsignee']);
        exit;
	}
	/*××××××××××××××××××收件人信息结束××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××*/
	
	
	/*××××××××××××××××××发票信息开始××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××*/
	function edit_invoice(){
		// 常用地址列表，及收件人信息编辑表单
		$this->loadModel('OrderInvoice');
		$invoices = $this->OrderInvoice->find('all',array('conditions'=>array('creator'=>$this->currentUser['id'])));
		$this->set('invoices',$invoices);	
	}
	
	function load_invoice($id){
		$this->autoRender = false;
		$this->loadModel('OrderInvoice');
		$consignee = $this->OrderInvoice->find('first',
		array(
			'conditions'=>array('id'=>$id,'creator'=>$this->currentUser['id']),
		));
		echo json_encode($consignee['OrderInvoice']);
        exit;
	}
	
/**
	 * 编辑表单的保存，获取收件人信息文本内容（非表单形式）
	 */
	function info_invoice(){
		$this->autoRender = false;
		if(!empty($this->data)){
			$this->loadModel('OrderInvoice');
			$this->data['OrderInvoice']['creator'] = $this->currentUser['id'];
			$this->Session->write('OrderInvoice',$this->data['OrderInvoice']);			
			if($this->data['OrderInvoice']['save_invoice']){				
				$invoice = $this->OrderInvoice->find('first',array(
					'conditions'=>array(
						'creator' => $this->currentUser['id'],
						'name'=> $this->data['OrderInvoice']['name'],
						'content'=> $this->data['OrderInvoice']['content'],
					))
				);
				if(!empty($invoice)){					
	                $this->data['OrderInvoice']['id'] = $invoice['OrderInvoice']['id'];
				}				
				if(!$this->OrderInvoice->save($this->data)){
					echo json_encode($this->{$this->modelClass}->validationErrors);
	                return;
				}
			}
			$successinfo = array('success' => __('Add success'), 
				'tasks'=>array(array(
					'dotype'=>'html',
					'selector'=>'#part_invoice',
					'content'=> $this->renderElement('order_invoice')
					))
			);
			echo json_encode($successinfo);
            exit;
		}
		echo $this->renderElement('order_invoice');
		exit;
	}
	function delete_invoice($id){
		$this->autoRender = false;
		$this->loadModel('OrderInvoice');
		$consignees = $this->OrderInvoice->deleteAll(array(
		'creator'=>$this->currentUser['id'],'id'=> $id));
		$successinfo = array('id' => $id);
		echo json_encode($successinfo);
        exit;
	}
	/***** 备注信息 ***********/
	function edit_remark(){
		$this->Session->write('Order.remark',$this->data['Order']['remark']);
		echo json_encode($this->data);
        exit;
	}
	
       
}