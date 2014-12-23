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

        $pid = $this->current_data_id;
        if ($pid == PRODUCT_ID_RICE_10) {

            $current_uid = $this->currentUser['id'];

            if($this->is_weixin() || !empty($_GET['trid'])) {
                if (!$current_uid) {
                    $this->redirect('/users/login?referer=' . urlencode($_SERVER['REQUEST_URI']));
                }
                $track_type = TRACK_TYPE_PRODUCT_RICE;
                list($friend, $shouldAdd, ) = $this->track_or_redirect($current_uid, $track_type);
                if ($shouldAdd) {
                    //$this->AwardInfo->updateAll(array('times' => 'times + 1',), array('uid' => $friend['User']['id']));
                }
                if (!empty($friend)) {
                    $this->redirect_for_append_tr_id($current_uid, $track_type, $_SERVER['REQUEST_URI']);
                }
            }
        }

        $brandId = $this->viewdata['Product']['brand_id'];

        $this->loadModel('SpecialList');
        $specialLists = $this->SpecialList->has_special_list($pid);
        if (!empty($specialLists)) {
            foreach ($specialLists as $specialList) {
                if ($specialList['type'] == 1) {
                    $special = $specialList;
                    break;
                }
            }
        }

        $use_special = false;
        $price = $this->viewdata['Product']['price'];
        $currUid = $this->currentUser['id'];
        if (!empty($special) && $special['special']['special_price'] >= 0) {
            $special_rg = array('start' => $special['start'], 'end' => $special['end']);
            //TODO: check time (current already checked)
            //CHECK time limit!!!!
            list($afford_for_curr_user, $left_cur_user, $total_left) =
                calculate_afford($pid, $currUid, $special['special']['limit_total'], $special['special']['limit_per_user'], $special_rg);

            $promo_name = $special['name'];
            $special_price = $special['special']['special_price'] / 100;
            App::uses('CakeNumber', 'Utility');
            $promo_desc = '￥'.CakeNumber::precision($special_price, 2);
            if ($special['special']['limit_total'] > 0) {
                $promo_desc .= ' 共限'.$special['special']['limit_total'].'件';
            }
            if ($special['special']['limit_per_user'] > 0) {
                $promo_desc .= ' 每人限'.$special['special']['limit_per_user'].'件';
            }
            if ($afford_for_curr_user) {
                $price = $special_price;
                $use_special = true;
            } else {
                $promo_desc .=  '('. ($left_cur_user == 0 ? '您已买过' : '已售完') . ')';
            }
            $this->set('special_desc', $promo_desc);
            $this->set('special_name', $promo_name);
            $this->set('special_slug', $special['slug']);
        }

        if (!$use_special) {
            list($afford_for_curr_user, $left_cur_user, $total_left) = self::__affordToUser($pid, $currUid);
        }

        $this->set('price', $price);

        //possible problem
        $this->set('limit_per_user', $left_cur_user);
        $this->set('total_left', $total_left);
        $this->set('afford_for_curr_user', $afford_for_curr_user);

        $specs_map = product_spec_map($this->viewdata['Product']['specs']);
        if (!empty($specs_map['map'])) {
            $str = '<script>var _p_spec_m = {';
            foreach($specs_map['map'] as $mid => $mvalue) {
                $str .= '"'.$mvalue['name'].'":"'. $mid ."\",";
            }
            $str .= '};</script>';
            $this->set('product_spec_map', $str);
        }
        $this->set('specs_map', $specs_map);
        $this->setHasOfferBrandIds($this->viewdata['Product']['brand_id']);
        $this->set('hideNav', $this->RequestHandler->isMobile());


        $this->loadModel('OrderShichi');
        $order_shichi = $this->OrderShichi->find('first', array('conditions' => array('creator' => $currUid, 'data_id' => $pid))); //查找是否有试吃订单
        $is_product_has_shichi = $this->OrderShichi->find('first',array('conditions' => array('data_id' => $pid)));
        $this->set('is_product_has_shichi',$is_product_has_shichi);
        $this->set('order_shichi', $order_shichi);

        $this->loadModel('Order');
        if (!empty($order_shichi)) {
            $order_id = $order_shichi['OrderShichi']['order_id'];
            $order = $this->Order->find('first',array('conditions' => array('id' => $order_id)));
            $order_shichi_status = $order['Order']['status'];
            $this->set('order_shichi_status',$order_shichi_status);
        }


        $this->loadModel('Brand');
        $brand = $this->Brand->findById($brandId);
        $this->set('brand', $brand);

        $recommC = $this->Components->load('ProductRecom');
        $recommends = $recommC->recommend($pid);
        $this->set('items', $recommends);

        $this->set('category_control_name', 'products');
    }

}