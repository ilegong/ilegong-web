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
            $num = $product_nums[$productinfo['id']];
            list($price, $special_id) = calculate_price($productinfo['Product']['id'], $productinfo['Product']['price'], $uid, $num);
			$data = array();
			$data['Cart']['product_id'] = $productinfo['id'];
            $data['Cart']['num'] = $num;
			$data['Cart']['session_id'] = $this->Session->id();
			$data['Cart']['coverimg'] = $productinfo['Product']['coverimg'];
			$data['Cart']['name'] = $productinfo['Product']['name'];
            $data['Cart']['price'] = $price;
            $data['Cart']['applied_special'] = empty($special_id)?  0 : $special_id;
			$data['Cart']['creator'] = $uid;
			$this->Cart->save($data);
		}
	}
	
	function add(){

        //清除Flash信息，避免登录信息显示出来
        $this->Session->delete('Message.flash');

        $this->autoRender = false;

		if(!empty($this->data)){

            $buyingCom = $this->Components->load('Buying');

			$this->autoRender = false;
            $product_id = $this->data['Cart']['product_id'];
            $num = $this->data['Cart']['num'];
            $specId = $this->data['Cart']['spec'];
            $type = $buyingCom->convert_cart_type($this->data['Cart']['type']);
            $tryId = intval($_POST['try_id']);
            $uid = $this->currentUser['id'];
            $sessionId = $this->Session->id();
            $cartM = $this->Cart;

            if (!$type) {
                //FIXME:should give an error to client
                $type = CART_ITEM_TYPE_NORMAL;
            }

            $returnInfo = $buyingCom->check_and_add($cartM, $type, $tryId, $uid, $num, $product_id, $specId, $sessionId);
            if (!empty($returnInfo) && $returnInfo['success']) {
                $cart_id = $returnInfo['id'];
                if ($product_id == PRODUCT_ID_CAKE && $_REQUEST['dating'] && $cart_id) {
                    $dating = trim($_REQUEST['dating']);
                    if ($dating) {
                        $cartM->updateAll(array('name' => 'concat(name, "(' . $dating . ')")'), array('id' => $cart_id));
                    }
                }
                $customized_price = $this->data['Cart']['customized_price'];
                if (accept_user_price($product_id, $customized_price)) {
                    if (empty($uid)) {
                        $returnInfo['success']  = false;
                        $returnInfo['reason'] = 'not_login';
                        echo json_encode($returnInfo);
                        exit();
                    }
                    $this->loadModel('UserPrice');
                    $this->UserPrice->add($product_id, $customized_price, $uid, $cart_id);
                }
            }
            echo json_encode($returnInfo);
			exit;
		}
		$errorinfo = array('save_error' => __('Operation failed'));
        echo json_encode($errorinfo);
	}

    //FIXME: authorized
	function editCartNum($id, $num){
        if($this->Cart->edit_num($id, $num, $this->currentUser['id'], $this->Session->id())) {
            $info = array('success' => true, 'msg' => __('Success edit nums.'));
        } else {
            $info = array('success' => false);
        }
		echo json_encode($info);
		exit;
	}

	function cart_total(){
        $count = $this->Cart->find('count', array(
            'conditions' => array(
                'status' => 0,
                'type !='.CART_ITEM_TYPE_TRY,
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
                    'type !='.CART_ITEM_TYPE_TRY,
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
                'type !='.CART_ITEM_TYPE_TRY,
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
		$this->Cart->delete_item($id, $this->currentUser['id'], $this->Session->id());
		$this->redirect('/carts/listcart.html');
	}
}