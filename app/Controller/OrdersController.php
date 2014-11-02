<?php
class OrdersController extends AppController{
	
	var $name = 'Orders';

	var $user_condition = array();

    public $components = array('Weixin');
	
	var $ship_type = array(
		101=>'申通',
		102=>'圆通',
		103=>'韵达',
		104=>'顺丰',
		105=>'EMS',
		106=>'邮政包裹',
		107=>'天天',
		108=>'汇通',
		109=>'中通',
		110=>'全一',
        111=>'宅急送'
    );

    public function __construct($request = null, $response = null) {
        $this->helpers[] = 'PhpExcel';
        parent::__construct($request, $response);
        $this->pageTitle = __('订单');
    }
	
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
		$this->loadModel('Product');
		$product_ids = array();
        $shipPromotionId = intval($_REQUEST['ship_promotion']);
        $this->loadModel('ShipPromotion');

        $nums = array();
		if(!empty($_COOKIE['cart_products'])){

			$info = explode(',',$_COOKIE['cart_products']);
			foreach($info as $item){
				list($id,$num) = explode(':',$item);
				if($id){
					$product_ids[] = $id;
					$nums[$id] = $num;
				}
			}
			
			$products = $this->Product->find('all',array('conditions'=>array(
					'id' => $product_ids
			)));
			/*清空购物车中的商品，将cookie信息中的商品重新加入购物车*/
			$this->Cart->deleteAll(array(
					'status'=> 0,
					'order_id' => null,
					'OR'=> $this->user_condition
			));
			
			$Carts = array();
			foreach($products as $p){
                $pp = $shipPromotionId ? $this->ShipPromotion->find_ship_promotion($p['Product']['id'], $shipPromotionId) : array();
                $pid = $p['Product']['id'];
                list($afford_for_curr_user, $limit_per_user) = AppController::affordToUser($pid, $this->currentUser['id']);
                if (!$afford_for_curr_user) {
                    $this->__message(__($Carts[$pid]['name'].'已售罄或您已经购超限，请从购物车中删除后再结账'), '/orders/info', 5);
                    return;
                } else if ($limit_per_user > 0 && $nums[$p['Product']['id']] > $limit_per_user) {
                    $nums[$p['Product']['id']] = $limit_per_user;
                }
				$Cart = array('Cart'=>array(
						'product_id'=> $p['Product']['id'],
						'name'=> $p['Product']['name'],
						'coverimg'=> $p['Product']['coverimg'],
                         'num' =>  $nums[$p['Product']['id']],
						'creator'=> $this->currentUser['id'],
                        'price'=> empty($pp)? $p['Product']['price'] : $pp['price'],
                ));


				$this->Cart->create();
				if($this->Cart->save($Cart)){
					$Cart['Cart']['id'] = $this->Cart->getLastInsertID();
					$Carts[$p['Product']['id']] = $Cart;
				}
			}
		}
		else{
			$Carts = array();
			$Carts_tmp = $this->Cart->find('all',array(
					'conditions'=>array(
							'status'=> 0,
							'order_id' => null,
							'OR'=> $this->user_condition
					)));
			foreach($Carts_tmp as $c){
				$product_ids[]=$c['Cart']['product_id'];
				$Carts[$c['Cart']['product_id']] = $c;
                $nums[$c['Cart']['product_id']] = $c['Cart']['num'];
			}
			$products = $this->Product->find('all',array('conditions'=>array(
					'id' => $product_ids
			)));
		}

		if(empty($Carts)){
			$this->Session->setFlash('订单金额错误，请返回购物车查看');
			$this->redirect('/');
		}

        $ship_fees = array();
		$business = array();
		foreach($products as $p){
            $pid = $p['Product']['id'];
			if(isset($business[$p['Product']['brand_id']])){
                $business[$p['Product']['brand_id']][] = $pid;
			}
			else{
				$business[$p['Product']['brand_id']] = array($pid);
			}
            $pp = $shipPromotionId ? $this->ShipPromotion->find_ship_promotion($p['Product']['id'], $shipPromotionId) : array();
            $singleShipFee = empty($pp) ? $p['Product']['ship_fee'] : $pp['ship_price'];
            $ship_fees[$pid] = ShipPromotion::calculateShipFee($pid, $singleShipFee, $nums[$pid], null);
		}

        $new_order_ids = array();
		$hasfalse = false;
		foreach($business as $brand_id => $busi){
			$bs_carts = array();
			$total_price = 0.0;
            $ship_fee = 0.0;
			foreach($busi as $pid){
				$total_price+= $Carts[$pid]['Cart']['price']*$Carts[$pid]['Cart']['num'];
                $ship_fee += $ship_fees[$pid];

                list($afford_for_curr_user, $limit_per_user) = AppController::affordToUser($pid, $this->currentUser['id']);
                if (!$afford_for_curr_user) {
                    $this->__message(__($Carts[$pid]['name'].'已售罄或您已经购买超限，请从购物车中删除后再结账'), '/orders/info', 5);
                    return;
                } else if ($limit_per_user > 0 && $Carts[$pid]['Cart']['num'] > $limit_per_user) {
                    $this->__message(__($Carts[$pid]['name'].'购买超限，请从购物车中删除后再结账'), '/orders/info', 5);
                }

			}
			
			if($total_price <= 0){
				$this->Session->setFlash('订单金额错误，请返回购物车查看');
				$this->redirect('/carts/listcart');
			}
			
			$data = array();
			$data['total_price'] = $total_price;
			$data['total_all_price'] = $total_price + $ship_fee;
            $data['ship_fee'] = $ship_fee;
			$data['brand_id'] = $brand_id;
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
			
			if(empty($data['consignee_name']) || empty($data['consignee_address']) || empty($data['consignee_mobilephone']) ){
				$this->__message('请填写收货人信息','/orders/info');
			}
			$this->Order->create();
			
			if($this->Order->save($data)){
				$order_id = $this->Order->getLastInsertID();
                if ($order_id) {
                    array_push($new_order_ids, $order_id);
                }
				foreach($busi as $pid){
					$cart = $Carts[$pid];
// 					echo "==$order_id=====$pid======$total_price====\n";
					$this->Cart->updateAll(array('order_id'=>$order_id,'status'=>1),array('id'=>$cart['Cart']['id'],'creator'=>$this->currentUser['id']));
				}
			}
			else{
				$hasfalse = true;
			}
		}

		setcookie("cart_products", '',time()-3600,'/');
		if($hasfalse == false){
			$this->Session->setFlash('订单已生成,不同商家的商品会拆分到不同的订单，请您知悉。');
            if (count($new_order_ids) == 1) {
                $this->redirect(array('action' => 'detail', $new_order_ids[0], 'pay'));
            }  else {
                $this->redirect('/orders/mine');
            }
		}
		else{
			$this->Session->setFlash('订单生成失败，请稍候重试或联系管理员');
			$this->redirect('/orders/info');
		}
	}

    /**
     * 订单信息页，确认各项订单信息
     * @param int|string $order_id
     * @param string $action
     */
	function info($order_id='', $action = ''){
		$has_chosen_consignee = false;
		$this->loadModel('OrderConsignee');
		$consignees = $this->OrderConsignee->find('all',array(
			'conditions'=>array('creator'=>$this->currentUser['id']),
			'order' => 'status desc',
		));
		$total_consignee = count($consignees);
        $shipPromotionId = intval($_REQUEST['ship_promotion']);
        $shipFee = 0.0;
		$this->loadModel('Cart');
	    $product_ids = array();
        $nums = array();
		if(empty($order_id)){
            $this->loadModel('ShipPromotion');
			if(!empty($_COOKIE['cart_products'])){
				$info = explode(',',$_COOKIE['cart_products']);
				foreach($info as $item){
					list($id,$num) = explode(':',$item);
					if($id){
						$product_ids[] = $id;
						$nums[$id] = $num;
					}
				}
				$this->loadModel('Product');
				$products = $this->Product->find('all',array('conditions'=>array(
						'id' => $product_ids
				)));
				$Carts = array();
				foreach($products as $p){
                    $pid = $p['Product']['id'];
                    $pp = $shipPromotionId ? $this->ShipPromotion->find_ship_promotion($pid, $shipPromotionId) : array();
                    if ($_REQUEST['action'] == 'savePromo' && !empty($pp)) {
                        $consignee = array();
                        $consignee['name'] = trim($_REQUEST['consignee_name']);
                        $consignee['mobilephone'] = trim($_REQUEST['consignee_mobilephone']);
                        $consignee['address'] = trim($pp['address']);
                        $this->Session->write('OrderConsignee', $consignee);
                        $has_chosen_consignee = true;
                    }

                    $num = ($pid != ShipPromotion::QUNAR_PROMOTE_ID && $nums[$pid]) ? $nums[$pid] : 1;
                    $Carts[] = array(
							'Cart'=>array(
									'product_id'=> $pid,
									'name'=> $p['Product']['name'],
									'coverimg'=> $p['Product']['coverimg'],
									'num'=> $num,
									'price'=> empty($pp)? $p['Product']['price'] : $pp['price'],
					));

                    $singleShipFee = (empty($pp)? $p['Product']['ship_fee'] : $pp['ship_price']);
                    $shipFee += ShipPromotion::calculateShipFee($pid, $singleShipFee, $num, null);
				}

			}
			else{

                //TODO: adjust to PC version
				$Carts = $this->Cart->find('all',array(
					'conditions'=>array(
						'status'=> 0,
						'order_id' => null,
						'OR'=> $this->user_condition
				)));

                foreach($Carts as $cart) {
                    $nums[$cart['Cart']['product_id']] = $cart['Cart']['num'];
                }

                $product_ids = array_map(function($val){ return $val['Cart']['product_id']; }, $Carts);

                $this->loadModel('Product');
                $products = $this->Product->find('all',array('conditions'=>array(
                    'id' => $product_ids
                )));
                foreach($products as $p){
                    $pid = $p['Product']['id'];
                    $pp = $shipPromotionId ? $this->ShipPromotion->find_ship_promotion($pid, $shipPromotionId) : array();
                    $num = ($pid != ShipPromotion::QUNAR_PROMOTE_ID && $nums[$pid]) ? $nums[$pid] : 1;
                    $singleShipFee = (empty($pp)? $p['Product']['ship_fee'] : $pp['ship_price']);
                    $shipFee += ShipPromotion::calculateShipFee($pid, $singleShipFee, $num, null);
                }
			}

			$current_consignee = $this->Session->read('OrderConsignee');
			if(empty($current_consignee)){			
				$first_consignees = current($consignees);
				$current_consignee = array();
				// empty 不能检测函数，只能检测变量
				if(!empty($first_consignees)){
					$current_consignee = $first_consignees['OrderConsignee'];
					$has_chosen_consignee = true;
				}
				else{				
//					$current_consignee['name'] = $this->Session->read('Auth.User.nickname');
//					$current_consignee['email'] = $this->Session->read('Auth.User.email');
//					$current_consignee['mobilephone'] = $this->Session->read('Auth.User.mobilephone');
//					$current_consignee['telephone'] = $this->Session->read('Auth.User.telephone');
//					$current_consignee['postcode'] = $this->Session->read('Auth.User.postcode');
//					$current_consignee['address'] = $this->Session->read('Auth.User.address');
				}
				$this->Session->write('OrderConsignee',$current_consignee);
			}
			elseif(!empty($current_consignee['id'])){
				$has_chosen_consignee = true;
			}
		}
		else{
			$has_chosen_consignee = true;
			$orderinfo = $this->Order->find('first',array(
				'conditions'=> array('id'=>$order_id,'creator'=>$this->currentUser['id']),
			));	
			if(empty($orderinfo)){
				$this->__message('订单不存在，或无权查看','/');
			}
			$current_consignee = array(
				'id' => $orderinfo['Order']['consignee_id'],
				'name' => $orderinfo['Order']['consignee_name'],
				'address' => $orderinfo['Order']['consignee_address'],
				'email'  => $orderinfo['Order']['consignee_email'],
				'mobilephone'  => $orderinfo['Order']['consignee_mobilephone'],
				'telephone'  => $orderinfo['Order']['consignee_telephone'],
				'postcode'  => $orderinfo['Order']['consignee_postcode'],
			);	
			$Carts = $this->Cart->find('all',array(
				'conditions'=>array(					
					'order_id' => $order_id,
					'creator'=> $this->currentUser['id']
			)));
			$this->Session->write('OrderConsignee',$current_consignee);
            $shipFee = $orderinfo['Order']['ship_fee'];
		}

		$total_price = $this->_calculateTotalPrice($Carts);
		$this->set('has_chosen_consignee',$has_chosen_consignee);
		$this->set('total_consignee',$total_consignee);
		$this->set('consignees',$consignees);	
		$this->set('order_id', $order_id);
		$this->set('total_price',$total_price);
        $this->set('shipFee', $shipFee);
		$this->set('Carts',$Carts);
        $this->set('action', $action);

        $shipPromotions = $this->ShipPromotion->findShipPromotions($product_ids);
        if ($shipPromotions && !empty($shipPromotions)) {
            $this->set('specialShipPromotionId', $shipPromotionId);
            $this->set('specialShipPromotion', $shipPromotions['items']);
            $this->set('limit_ship', $shipPromotions['limit_ship']);
        }
	}

    /**
     * Display and options for already submitted order
     * @Param int $order_id
     * @Param string action
     */
    function detail($orderId='', $action = '') {
        $orderinfo = $this->Order->find('first',array(
            'conditions'=> array('id'=>$orderId,'creator'=>$this->currentUser['id']),
        ));
        if(empty($orderinfo)){
            $this->__message('订单不存在，或无权查看','/');
        }

        if ($action == 'pay') {
            $this->set('paid_msg', htmlspecialchars($_GET['paid_msg']));
            $display_status = $_GET['display_status'];
            $this->set('display_status', $display_status);
        }
        $this->set('show_pay', $orderinfo['Order']['status'] == ORDER_STATUS_WAITING_PAY
            && ($display_status != PAID_DISPLAY_PENDING && $display_status != PAID_DISPLAY_SUCCESS));

        if ($action == 'paid') {
            $this->log("paid done: $orderId, msg:". $_GET['msg']);
            //:orders/detail/1118/paid?tradeNo=wxca78-1118-1414580077&msg=ok
            //TODO: check status, if status is not paid, tell user to checking; notify administrators to check
        }


        $this->loadModel('Cart');
        $Carts = $this->Cart->find('all',array(
            'conditions'=>array(
                'order_id' => $orderId,
                'creator'=> $this->currentUser['id']
            )));
        $product_ids = array();
        foreach($Carts as $cart) {
            $product_ids[] = $cart['Cart']['product_id'];
        }
        $this->loadModel('Product');
        $products = $this->Product->find('all', array(
            'fields' => array('id', 'created', 'slug'),
            'conditions'=>array(
            'id' => $product_ids
        )));

        $product_new = array();
        foreach($products as &$p) {
            $product_new[$p['Product']['id']] = $p;
        }
        $products = $product_new;
        unset($product_new);


        $this->set('ship_type',$this->ship_type);
        $this->set('order_id',$orderId);
        $this->set('order', $orderinfo);
        $this->set('Carts',$Carts);
        $this->set('action', $action);
        $this->set('products', $products);
    }
	
	function mine(){
		$this->loadModel('Brand');
		$brands = $this->Brand->find('first',array(
				'conditions' => array('creator'=> $this->currentUser['id'])));
		if(!empty($brands)){
			$this->set('is_business',true);
		}
		
		$orders = $this->Order->find('all',array(
				'order' => 'id desc',
				'conditions'=> array('creator'=>$this->currentUser['id'], 'published' => 1),
		));
		$ids = array();
        $brandIds = array();
		foreach($orders as $o){
			$ids[] = $o['Order']['id'];
            $brandIds[] = $o['Order']['brand_id'];
		}

		$this->loadModel('Cart');
		$Carts = $this->Cart->find('all',array(
				'conditions'=>array(
						'order_id' => $ids,
						'creator'=> $this->currentUser['id']
		)));

        $counts = array();
		$order_carts = array();
		foreach($Carts as $c){
			$order_id = $c['Cart']['order_id'];
			if(!isset($order_carts[$order_id])) $order_carts[$order_id] = array();
			$order_carts[$order_id][] = $c;
            $counts[$order_id] = $c['Cart']['num'];
		}

        $mappedBrands = array();
        if ($brandIds) {
            $brands = $this->Brand->find('all', array(
                'conditions' => array('id' => $brandIds),
                'fields' => array('id', 'name', 'created', 'slug', 'coverimg')
            ));

            foreach($brands as $brand) {
                $mappedBrands[$brand['Brand']['id']] = $brand;
            }
        }

        $this->set('brands', $mappedBrands);
		$this->set('orders',$orders);
		$this->set('order_carts',$order_carts);
		$this->set('ship_type',$this->ship_type);
        $this->set('counts', $counts);
	}

	function business($creator=0){

        $creator = $this->authAndGetCreator($creator);

        $this->loadModel('Brand');
		$brands = $this->Brand->find('list',array('conditions'=>array(
				'creator'=> $creator,
		)));
		
		if(!empty($brands)){
			$brand_ids = array_keys($brands);
			$this->set('is_business',true);
		}
		else{
			$this->__message('只有合作商家才能查看商家订单，正在为您转向个人订单','/orders/mine');
		}
		/*
		$this->loadModel('Product');
		$bu_pros = $this->Product->find('all',array(
			'fields'=> 'id,name,brand_id',
			'conditions'=>array(
				'brand_id' => $brand_ids,
		)));
		$product_ids = array();
		foreach($bu_pros as $p){
			$product_ids[] = $p['Product']['id'];
		}
		$cart_conditions = array(
			'product_id' => $product_ids,
		);*/
		$orders = $this->Order->find('all',array(
				'order' => 'id desc',
				'conditions' => array('brand_id' => $brand_ids, 'NOT' => array(
                    'status' => array(ORDER_STATUS_CANCEL, ORDER_STATUS_WAITING_PAY)
                ) ),
				/*'group' => 'Cart.order_id',
				'joins'=>array(
						array(
							'table' => 'carts',
							'alias' => 'Cart',
							'type' => 'inner',
							'conditions' => array(
									'Cart.order_id=Order.id',
									'Cart.product_id' => $product_ids,
							),
						)
				),*/
		));
		$ids = array();
		foreach($orders as $o){
			$ids[] = $o['Order']['id'];
		}
		$this->loadModel('Cart');
		$Carts = $this->Cart->find('all',array(
				'conditions'=>array(
						'order_id' => $ids,
						//'creator'=> $this->currentUser['id']
				)));
		$order_carts = array();
		foreach($Carts as $c){
			$order_id = $c['Cart']['order_id'];
            if (!isset($order_carts[$order_id])) {
                $order_carts[$order_id] = array();
            }
			$order_carts[$order_id][] = $c;
		}
		
		$this->set('orders',$orders);
		$this->set('order_carts',$order_carts);
		$this->set('ship_type',$this->ship_type);
        $this->set('creator', $creator);


        if($_REQUEST['export']=='true'){
            $this->autoRender = false;
            $this->_download_excel($orders, $order_carts);
            exit;
        }
	}

    function tobe_shipped_orders($creator=0){

        $creator = $this->authAndGetCreator($creator);

        $this->loadModel('Brand');
		$brands = $this->Brand->find('list',array('conditions'=>array(
				'creator'=> $creator,
		)));

		if(!empty($brands)){
			$brand_ids = array_keys($brands);
			$this->set('is_business',true);
		}
		else{
			$this->__message('只有合作商家才能查看商家订单，正在为您转向个人订单','/orders/mine');
		}

		$orders = $this->Order->find('all',array(
				'order' => 'id desc',
				'conditions' => array('brand_id' => $brand_ids, 'status' => ORDER_STATUS_PAID
                )
		));

		$ids = array();
		foreach($orders as $o){
			$ids[] = $o['Order']['id'];
		}
		$this->loadModel('Cart');
		$Carts = $this->Cart->find('all',array(
				'conditions'=>array(
						'order_id' => $ids,
						//'creator'=> $this->currentUser['id']
				)));
		$order_carts = array();
		foreach($Carts as $c){
			$order_id = $c['Cart']['order_id'];
            if (!isset($order_carts[$order_id])) {
                $order_carts[$order_id] = array();
            }
			$order_carts[$order_id][] = $c;
		}

		$this->set('orders',$orders);
		$this->set('order_carts',$order_carts);
		$this->set('ship_type',$this->ship_type);
        $this->set('creator', $creator);


        if($_REQUEST['export']=='true'){
            $this->autoRender = false;
            $this->_download_excel($orders, $order_carts);
            exit;
        }
	}


    function business_export($creator=0) {
        $this->business($creator);
    }

    function tobe_shipped_export($creator=0) {
        $this->tobe_shipped_orders($creator);
    }

    /**
     * 占用较小的内存，更适合网站空间php占用内存限制小的情况。
     */
    private function _download_excel($orders, $order_carts){
        @set_time_limit(0);
        App::import('Vendor', 'Excel_XML', array('file' => 'phpexcel'.DS.'excel_xml.class.php'));
        $xls = new Excel_XML('UTF-8', true, 'Sheet Orders');

        $add_header_flag = false;
        $fields = array('id','consignee_name','created','goods', 'total_all_price','status','consignee_mobilephone','consignee_address');
        $header = array('订单号','客户姓名','下单时间','商品','总价','状态','联系电话','收货地址');
        $order_status = array('待确认', '已支付','已发货','已收货','已退款','','','','','已完成','已做废', '已确认', '已投诉');
        $page = 1;
        $pagesize = 500;
        do{
            $rows = count($orders);
            foreach($orders as $item){
                if($add_header_flag==false){
                    $xls->addRow($header);
                    $add_header_flag = true;
                }
                $row = array();
                foreach($fields as $fieldName){
                    if ($fieldName == 'goods') {
                        $orderId = $item['Order']['id'];
                        $goods = $order_carts[$orderId];
                        $value = '';
                        if (is_array($goods)) {
                            foreach ($goods as $good) {
                                $value .= $good['Cart']['name'] . '*' . $good['Cart']['num'] . '; ';
                            }
                        }

                    } else {
                        $value = $item['Order'][$fieldName];
                        if ($fieldName == 'status') {
                            $value = $order_status[$value];
                        }
                    }

                    $row[] = $value;
                }
                $xls->addRow($row);
            }
            ++$page;
        }while($rows==$pagesize);

        $xls->generateXML('orders'.'_'.date('Y-m-d'));
    }


	function confirm_receive(){
        $this->edit_status_by_owner_ajax(ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, '已收货');
	}

	function confirm_undo(){
        $this->edit_status_by_owner_ajax(ORDER_STATUS_WAITING_PAY, ORDER_STATUS_CANCEL);
	}

	function confirm_remove(){
        $this->edit_order_by_owner_ajax(function($orderModel, $curr_status, $order_id){
            if ($curr_status == ORDER_STATUS_CANCEL) {
                $orderModel->updateAll(array('published' => 0), array('id' => $order_id));
                echo json_encode(array('order_id' => $order_id, 'ok' => 1));
            } else {
                echo json_encode(array('order_id' => $order_id, 'ok' => 0));
            }
            exit;
        });
	}

	private function edit_status_by_owner_ajax($origStatus, $toStatus, $okMsg = ''){
		$this->edit_order_by_owner_ajax(function($orderModel, $curr_status, $order_id) use ($origStatus, $toStatus,$okMsg) {
            if ($curr_status == $origStatus) {
                $orderModel->updateAll(array('status' => $toStatus), array('id' => $order_id));
                echo json_encode(array('order_id' => $order_id, 'ok' => 1, 'msg' => $okMsg));
                exit;
            } else {
                echo json_encode(array('order_id' => $order_id, 'ok' => 0, 'msg' => '不能修改订单状态了。'));
                exit;
            }
        });
	}

    /**
     * @param $fun callback:  a callback with parameters: OrderModel, curr_status, and order_id
     */
    private function edit_order_by_owner_ajax($fun){
		$order_id = $_REQUEST['order_id'];

		if(empty($order_id)){
			echo json_encode(array('order_id'=>$order_id,'msg'=>'参数错误'));
			exit;
		}

		$order_info = $this->Order->find('first',array(
				'conditions'=> array('id'=>$order_id,'creator'=>$this->currentUser['id']),
		));

		if(empty($order_info)){
			echo json_encode(array('order_id'=>$order_id,'msg'=>'您不具备此订单的修改权限。'));
			exit;
		}
		$orig_status = $order_info['Order']['status'];
		$fun($this->Order, $orig_status, $order_id);
	}

	/**
	 * 商家设置订单的状态
	 */
	function set_status($creator = 0){
		$order_id = $_REQUEST['order_id'];
		$status = $_REQUEST['status'];

        $creator = $this->authAndGetCreator($creator);

        if (1 == $status && !$this->is_admin($this->currentUser['id'])) {
            echo json_encode(array('order_id'=>$order_id,'msg'=>'您不具备此订单的支付确认权限，请联系管理员。'));
            exit;
        }
		
		if(empty($order_id) || empty($status)){
			echo json_encode(array('order_id'=>$order_id,'msg'=>'参数错误'));
			exit;
		}
		$this->loadModel('Brand');
		$brands = $this->Brand->find('list',array(
			'conditions'=>array(
				'creator'=> $creator,
		)));
		$brand_ids = array_keys($brands);
		
		$order_info = $this->Order->find('first',array(
				'conditions'=> array('id'=>$order_id,'brand_id'=>$brand_ids),
		));
		//'or'=>array('brand_id'=>$brand_ids,'creator' => $this->currentUser['id'])
		
		if(empty($order_info)){
			echo json_encode(array('order_id'=>$order_id,'msg'=>'您不具备此订单的修改权限，请联系管理员。'));
			exit;
		}
		$orig_status = $order_info['Order']['status'];
		if($orig_status==0){
			//待确认订单只能修改为订单已确认与订单已作废
			if(!in_array($status,array(10,11))){
				echo json_encode(array('order_id'=>$order_id,'msg'=>'您只能确认订单与作废订单。'));
				exit;
			}
			else{
				$this->Order->updateAll(array('status'=>$status, 'lastupdator'=>$creator),array('id'=>$order_id));
				if($status==11)  $msg = '订单已确认';
				elseif($status==10)  $msg = '订单已作废';
				echo json_encode(array('order_id'=>$order_id,'msg'=>$msg));
				exit;
			}
		} elseif ($orig_status == 11) {
            if (1 == $status && $this->is_admin($this->currentUser['id'])) {
                $this->Order->updateAll(array('status'=>$status, 'lastupdator'=>$creator),array('id'=>$order_id));
                echo json_encode(array('order_id'=>$order_id,'msg'=>'订单已支付'));
                exit;
            } else {
                echo json_encode(array('order_id'=>$order_id,'msg'=>'您不能变更订单到指定状态，请联系管理员。'));
                exit;
            }
        } elseif($orig_status==1 || $orig_status == 2){
			//已支付订单，修改状态为已发货
			if(!in_array($status,array(2))){
				echo json_encode(array('order_id'=>$order_id,'msg'=>'您只能将此订单设为已发货。'));
				exit;
			}
			else{
				$ship_code = $_REQUEST['ship_code'];
				$ship_type = $_REQUEST['ship_type'];
				$this->Order->updateAll(array('status'=>$status,'ship_code'=>"'".addslashes($ship_code)."'",'ship_type'=>$ship_type, 'lastupdator'=>$creator),array('id'=>$order_id));
                //add weixin message
                $order = $this->Order->find('first',array('conditions'=> array('id'=>$order_id)));
                $this->log($order['Order']['creator'],LOG_DEBUG);
                $this->loadModel('Oauthbind');
                $user_weixin = $this->Oauthbind->findWxServiceBindByUid($order['Order']['creator']);
                if($user_weixin!=false){
                    $good = $this->get_order_good_info($order_id);
                    $this->log("good info:".$good['good_info'].$good['good_number'],LOG_DEBUG);
                    $this->Weixin->send_order_shipped_message($user_weixin['oauth_openid'],$ship_type, $this->ship_type[$ship_type], $ship_code, $good['good_info'], $good['good_number']);
                }

				echo json_encode(array('order_id'=>$order_id,'msg'=>'订单状态已更新为“已发货”'));
				exit;
			}
		}
		else{
			echo json_encode(array('order_id'=>$order_id,'msg'=>'不能修改订单状态了。'));
			exit;
		}
	}

    function get_order_good_info($order_id){
        $info ='';
        $number =0;
        $this->loadModel('Cart');
        $carts = $this->Cart->find('all',array(
            'conditions'=>array('order_id' => $order_id)));
        foreach($carts as $cart){
            $info = $info.$cart['Cart']['name'].' x '.$cart['Cart']['num'].';';
            $number +=$cart['Cart']['num'];
        }
        return array("good_info"=>$info,"good_number"=>$number);
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
		$consignees = $this->OrderConsignee->find('all',array(
			'conditions'=>array('creator'=>$this->currentUser['id']),'order' => 'status desc',
		));
		$total_consignee = count($consignees);
		$this->set('total_consignee',$total_consignee);
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

    /**
     * @param $receivedCreator
     * @return mixed
     */
    public function authAndGetCreator($receivedCreator) {
        if ($this->is_admin($this->currentUser['id']) && $receivedCreator > 0) {
            return $receivedCreator;
        } else {
            return $this->currentUser['id'];
        }
    }


}