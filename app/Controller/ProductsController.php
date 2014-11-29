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

        $MAX_SAME_KIND = 2;
        $MAX_RECOMMEND = 6;

        $tag = $this->Product->query("select tag_id from cake_product_product_tags where product_id = $this->current_data_id limit 1");
        $recomm_same_kind = empty($tag) ? array() : $this->rand_recommend_pids($tag[0]['cake_product_product_tags']['tag_id'], $MAX_SAME_KIND * 2);
        $recomm_hottest = $this->rand_recommend_pids(PRO_TAG_HOTTEST, ($MAX_RECOMMEND - $MAX_SAME_KIND) * 2);

        $items = array();
        $same_kind = $this->Product->find_published_products_by_ids($recomm_same_kind);
        if (!empty($same_kind)) {
            foreach($recomm_same_kind as $pid){
                if (!empty($same_kind[$pid])) {
                    $items[$pid] = $same_kind[$pid];
                    if (count($items) > $MAX_SAME_KIND) {
                        break;
                    }
                }
            }
        }

        $hottest = $this->Product->find_published_products_by_ids(array_keys($recomm_hottest));
        if(!empty($hottest)) {
            foreach($recomm_hottest as $pid => $val){
                $item = $hottest[$pid];
                if (!empty($item)) {
                    $items[$pid] = $item;
                    if (count($items) > $MAX_RECOMMEND) {
                        break;
                    }
                }
            }
        }

        $this->set('items', $items);
        $this->set('category_control_name', 'products');
    }

    /**
     * @param $tag
     * @param $max
     * @return mixed array keyed with the product id
     */
    private function rand_recommend_pids($tag, $max) {
        $recommend = array();
        if (!empty($tag) && $max > 0) {
            $pid_candidates = $this->Product->query('select distinct product_id from cake_product_product_tags where tag_id = ' . $tag . ' and product_id != ' . $this->current_data_id);
            $candidates_len = count($pid_candidates);
            $tries = 100;
            while (count($recommend) <= min($max, $candidates_len) && $tries-- > 0) {
                $idx = rand(0, $candidates_len - 1);
                $id = $pid_candidates [$idx]['cake_product_product_tags']['product_id'];
                $this->log("randomed result: $id $idx");
                $recommend[$id] = null;
            }
        }
        return $recommend;
    }
}