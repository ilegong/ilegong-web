<?php

class ProductsController extends AppController{
	var $name = 'Products';	
	public $brand = null;
	
	public function beforeFilter(){
		parent::beforeFilter();
	}
	
	private function checkAccess(){
		
		if(empty($this->currentUser['id'])){
			$this->__message('您需要先登录才能操作','/users/login');
		}
		
		$this->loadModel('Brand');
		$this->brand = $this->Brand->find('first',array('conditions'=>array(
				'creator'=>$this->currentUser['id'],
		)));
		if(empty($this->brand)){
			$this->__message('只有合作商家才能添加商品','/');
		}
		
	}

//    public function view() {
//        parent::view();
//
//        $afford_for_curr_user = true;
//        if ($this->current_data_id == ShipPromotion::QUNAR_PROMOTE_ID) {
//            $ordersModel = ClassRegistry::init('Order');
//            $order_ids = $ordersModel->find('list', array(
//                'conditions' => array('brand_id' => ShipPromotion::QUNAR_PROMOTE_BRAND_ID, 'deleted' => 0),
//                'fields' => array('id', 'id')
//            ));
//            if (!empty($order_ids)) {
//                $cartModel = ClassRegistry::init('Cart');
//                $c = $cartModel->find('count', array(
//                    'conditions' => array('order_id' => $order_ids, 'product_id' => $this->current_data_id, 'deleted' => 0)
//                ));
//                if ($c > 0) {
//                    $afford_for_curr_user = false;
//                }
//            }
//        }
//        $this->set('afford_for_curr_user', $afford_for_curr_user);
//    }

	
	public function add(){
		
		$this->checkAccess();
		
		if(!empty($this->data)){
			$this->data[$this->modelClass]['brand_id'] = $this->brand['Brand']['id'];
		}
		parent::add();
	}
	
	public function mine(){
		$this->checkAccess();
		
		$pagesize = intval(Configure::read($this->modelClass.'.pagesize'));
		if(!$pagesize){
			$pagesize = 15;
		}
		
		$total = $this->{$this->modelClass}->find('count', array('conditions' => array('brand_id' => $this->brand['Brand']['id'])));
		$datalist = $this->{$this->modelClass}->find('all', array(
				'conditions' => array('brand_id' => $this->brand['Brand']['id']),
				'fields'=>array('id','name','price','published','coverimg'),
		));
		
		$page_navi = getPageLinks($total, $pagesize, '/products/mine', $page);
		$this->set('datalist',$datalist);
		$this->set('page_navi', $page_navi);
	}
	
	function edit($id) {
		$modelClass = $this->modelClass;
		
		$this->checkAccess();
		
		$datainfo = $this->{$this->modelClass}->find('first', array('conditions' => array('id' => $id, 'brand_id' => $this->brand['Brand']['id'])));
		if (empty($datainfo)) {
			throw new ForbiddenException(__('You cannot edit this data'));
		}
	
		if (!empty($this->data)) { //有数据提交
			$this->autoRender = false;
			$this->data[$modelClass]['creator'] = $this->currentUser['id'];
	
			if ($this->{$this->modelClass}->save($this->data)) {
				$this->Session->setFlash(__('The Data has been saved'));
				//$this->redirect(array('action'=>'index'));
			} else {
				$this->Session->setFlash(__('The Data could not be saved. Please, try again.'));
			}
			$successinfo = array('success' => __('Edit success'), 'actions' => array('OK' => 'closedialog'));
			//echo json_encode($successinfo);
			//return ;
			$this->redirect(array('action' => 'edit',$id));
		}
		else{
			$this->data = $datainfo; //加载数据到表单中
		}
	}
    function view($slug='/'){
        parent::view($slug);
        $tag = $this->Product->query("select tag_id from cake_product_product_tags where product_id = $this->current_data_id limit 1");
        $tag_id = $tag[0]['cake_product_product_tags']['tag_id'];
        $id_same_kind = $this->Product->query("select product_id  from cake_product_product_tags where tag_id = $tag_id and product_id != $this->current_data_id ");
        //热卖
        $hottest = '1';
        $id_hottest = $this->Product->query("select product_id  from cake_product_product_tags where tag_id =  $hottest and product_id != $this->current_data_id ");
        $same_kind_len = count($id_same_kind);
        $hottest_len = count($id_hottest);

        //利用键的唯一性，确保不同的值
        $array_product=array();
        while(count($array_product)< 5 && count($array_product)< $same_kind_len){
            $id = $id_same_kind [rand(0,$same_kind_len - 1)]['cake_product_product_tags']['product_id'];
            $array_product[$id]=null;
        }
        while(count($array_product)< 8 && count($array_product)< $hottest_len){
            $id = $id_hottest [rand(0,$hottest_len - 1)]['cake_product_product_tags']['product_id'];
            $array_product[$id]=null;
        }
        $rand_id = array_keys($array_product);
        $conditions = array('Product' .'.deleted'=>0, 'Product' .'.published'=>1, 'Product.id' => $rand_id);
        $good_recommend = $this->Product->find('all', array(
            'conditions' => $conditions,
            'limit' => 6
        ));
        $items = array();
        foreach($good_recommend as $p){
            $items[] = $p['Product'];
        }
        $this->set('items', $items);
        $this->set('category_control_name', 'products');
    }
}