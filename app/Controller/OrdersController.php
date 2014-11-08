<?php
class OrdersController extends AppController{
	
	var $name = 'Orders';

	var $user_condition = array();

    public $components = array('Weixin');
	
    var $customized_not_logged = array('apply_coupon');

    public function __construct($request = null, $response = null) {
        $this->helpers[] = 'PhpExcel';
        parent::__construct($request, $response);
        $this->pageTitle = __('订单');

    }

    /**
     * @param $bid
     * @return string
     */
    public static function key_balanced_conpons($bid) {
        return "Balance." . $bid . ".coupons";
    }

    public static function key_balanced_ship_promotion_id() {
        return "Balance.promotion.id";
    }

    function beforeFilter(){
		parent::beforeFilter();
		if(empty($this->currentUser['id']) && array_search($this->request->params['action'], $this->customized_not_logged) === false){
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
     */
	function balance(){
		$this->loadModel('Cart');
		$this->loadModel('Product');
		$product_ids = array();
        $shipPromotionId = intval($_REQUEST['ship_promotion']);
        if (!$shipPromotionId) {
            $shipPromotionId = intval($this->Session->read(self::key_balanced_ship_promotion_id()));
        }
        $this->loadModel('ShipPromotion');

        $nums = array();
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

		if(empty($Carts)){
			$this->Session->setFlash('订单金额错误，请返回购物车查看');
			$this->redirect('/');
		}

        $ship_fees = array();
		$business = array();
		foreach($products as $p){
            $pid = $p['Product']['id'];
            $pBrandId = $p['Product']['brand_id'];
            if(isset($business[$pBrandId])){
                $business[$pBrandId][] = $pid;
			}
			else{
				$business[$pBrandId] = array($pid);
			}
            $pp = $shipPromotionId ? $this->ShipPromotion->find_ship_promotion($pid, $shipPromotionId) : array();
            $singleShipFee = empty($pp) ? $p['Product']['ship_fee'] : $pp['ship_price'];
            $ship_fees[$pid] = ShipPromotion::calculateShipFee($pid, $singleShipFee, $nums[$pid], null);
		}

        $new_order_ids = array();
		$hasfalse = false;
		foreach($business as $brand_id => $busi){
			$total_price = 0.0;
            $ship_fee = 0.0;
            $uid = $this->currentUser['id'];
            foreach($busi as $pid){
				$total_price+= $Carts[$pid]['Cart']['price']*$Carts[$pid]['Cart']['num'];
                $ship_fee += $ship_fees[$pid];

                list($afford_for_curr_user, $limit_per_user) = AppController::__affordToUser($pid, $uid);
                if (!$afford_for_curr_user) {
                    $this->__message(__($Carts[$pid]['name'].'已售罄或您已经购买超限，请从购物车中调整后再结算'), '/carts/listcart', 5);
                    return;
                } else if ($limit_per_user > 0 && $Carts[$pid]['Cart']['num'] > $limit_per_user) {
                    $this->__message(__($Carts[$pid]['name'].'购买超限，请从购物车中调整后再结算'), '/carts/listcart', 5);
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
			$data['creator'] = $uid;

            $remark = $_REQUEST['remark_' . $brand_id];
            $data['remark'] = empty($remark)?"":$remark;

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
                $this->apply_coupons_to_order($brand_id, $uid, $order_id);

                foreach($busi as $pid){
					$cart = $Carts[$pid];
// 					echo "==$order_id=====$pid======$total_price====\n";
					$this->Cart->updateAll(array('order_id'=>$order_id,'status'=>1),array('id'=>$cart['Cart']['id'],'creator'=> $uid));
				}
			}
			else{
				$hasfalse = true;
			}
		}

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
     */
	function info($order_id=''){

        if ($this->RequestHandler->isMobile()) {
            $this->layout = 'newv1';
        }

        $this->Session->write(self::key_balanced_ship_promotion_id(), '');

		$has_chosen_consignee = false;
		$this->loadModel('OrderConsignee');
        $shipPromotionId = intval($_REQUEST['ship_promotion']);
		$this->loadModel('Cart');
        $this->loadModel('Product');
        $this->loadModel('ShipPromotion');

		if(empty($order_id)){
            $cartsByPid = $this->cartsByPid();
			if(!empty($_COOKIE['cart_products'])){
                $info = explode(',', $_COOKIE['cart_products']);
                mergeCartWithDb($this->currentUser['id'], $info, $cartsByPid, $this->Product, $this->Cart);
                setcookie("cart_products", '',time()-3600,'/');
			}
		}
		else{
            $this->log("/orders/info with a orderid=$order_id");
            $this->__message('订单已经生成，不能再修改', '/orders/detail/'.$order_id);
            return;
		}

        $pids = array_keys($cartsByPid);
        list($cart, $shipFee) = $this->applyPromoToCart($pids, $cartsByPid, $shipPromotionId);

        $consignees = $this->OrderConsignee->find('all',array(
            'conditions'=>array('creator'=>$this->currentUser['id']),
            'order' => 'status desc',
        ));
        $total_consignee = count($consignees);

        if ($_REQUEST['action'] == 'savePromo') {
            list($specialPid, $specialAddress) = $this->ShipPromotion->find_special_address_by_id($shipPromotionId);
            if (!empty($specialAddress)) {

                $productItemInCart = $cart->find_product_item($specialPid);
                if (isset($specialAddress['least_num']) && (!$productItemInCart || ($specialAddress['least_num'] > $productItemInCart->num))) {
                    $flash_msg = __('使用您选定的优惠地址需要购买'.$specialAddress['least_num'].'件"'.$productItemInCart->name.'"');
                    unset($shipPromotionId);
                } else {

                    $consignee = array();
                    $consignee['name'] = trim($_REQUEST['consignee_name']);
                    $consignee['mobilephone'] = trim($_REQUEST['consignee_mobilephone']);
                    $consignee['address'] = trim($specialAddress['address']).($specialAddress['need_address_remark']? trim($_REQUEST['consignee_address']):'');
                    $this->Session->write('OrderConsignee', $consignee);
                    $has_chosen_consignee = true;
                    $this->Session->write(self::key_balanced_ship_promotion_id(), $shipPromotionId);
                }
            } else {
                //error:
                unset($shipPromotionId);
                $flash_msg = __('输入的地址不对');
            }
        } else {
            $current_consignee = $this->Session->read('OrderConsignee');
            if (empty($current_consignee)) {
                $first_consignees = current($consignees);
                $current_consignee = array();
                // empty 不能检测函数，只能检测变量
                if (!empty($first_consignees)) {
                    $current_consignee = $first_consignees['OrderConsignee'];
                    $has_chosen_consignee = true;
                }
                $this->Session->write('OrderConsignee', $current_consignee);
            } elseif (!empty($current_consignee['id'])) {
                $has_chosen_consignee = true;
            }
        }

        $brand_ids = array_keys($cart->brandItems);
        if (!empty($brand_ids)) {
            $this->loadModel('Brand');
            $brands = $this->Brand->find('list', array('conditions' => array('id' => $brand_ids), 'fields' => array('id', 'name')));
        } else {
            $brands = array();
        }
        foreach($brand_ids as $bid) {
            $this->Session->write(self::key_balanced_conpons($bid), '');
        }

        //TODO: 计算邮费优惠等
        $total_reduced = 0.0;

        $couponItem = ClassRegistry::init('CouponItem');
        $coupons_of_products = $couponItem->find_user_coupons_for_cart($this->currentUser['id'], $cart);

		$total_price = $cart->total_price();
        $this->set(compact('total_price', 'shipFee', 'coupons_of_products', 'cart', 'brands', 'flash_msg', 'total_reduced'));
		$this->set('has_chosen_consignee', $has_chosen_consignee);
		$this->set('total_consignee', $total_consignee);
		$this->set('consignees', $consignees);

        $shipPromotions = $this->ShipPromotion->findShipPromotions($pids);
        if ($shipPromotions && !empty($shipPromotions)) {
            $this->set('specialShipPromotionId', $shipPromotionId);
            $this->set('specialShipPromotion', $shipPromotions['items']);
            $this->set('limit_ship', $shipPromotions['limit_ship']);
        }
        $this->pageTitle = __('订单详情');
	}

    /**
     * Display and options for already submitted order
     * @Param int $order_id
     * @Param string action
     */
    function detail($orderId='', $action = '') {

        if ($this->RequestHandler->isMobile()) {
            $this->layout = 'newv1';
        }

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

        $this->set('isMobile', $this->RequestHandler->isMobile());
        $this->set('ship_type', ShipAddress::$ship_type);
        $this->set('order_id',$orderId);
        $this->set('order', $orderinfo);
        $this->set('Carts',$Carts);
        $this->set('action', $action);
        $this->set('products', $products);
    }

    public function apply_coupon() {

        $this->autoRender = false;

        if (empty($this->currentUser['id'])) {
            echo json_encode(array('changed' => false, 'reason' => 'not_login'));
            return;
        }

        $shipPromotionId = intval($_REQUEST['ship_promotion']);
        $coupon_item_id = $_POST['coupon_item_id'];
        $brand_id = $_POST['brand_id'];
        $applying = $_POST['action'] == 'apply';

        $cartsByPid = $this->cartsByPid();
        list($cart, $shipFee) = $this->applyPromoToCart(array_keys($cartsByPid), $cartsByPid, $shipPromotionId);

        $this->loadModel('CouponItem');

        $appliedCoupons = array();
        $coupon_used_key = self::key_balanced_conpons($brand_id);
        $coupon_value = $this->Session->read($coupon_used_key);
        if ($coupon_value) {
            $appliedCoupons = json_decode($coupon_value);
        }

        $changed = false;
        //TODO: 需要考虑各种券的一致性，排他性
        if ($applying) {

            if (empty($appliedCoupons) || array_search($coupon_item_id, $appliedCoupons) === false) {
//            if($cart->could_apply($brand_id, $cou)){
                //TODO: 需要考虑券是否满足可用性等等
                $appliedCoupons[] = $coupon_item_id;
                $changed = true;
//            }
            }
        } else {
            if (!empty($appliedCoupons) && array_search($coupon_item_id, $appliedCoupons) !== false) {
                array_delete_value($appliedCoupons, $coupon_item_id);
                $changed = true;
            }
        }

        $resp = array('changed' => $changed);
        if ($changed) {
            $total_reduced = $this->CouponItem->compute_total_reduced($this->currentUser['id'], $appliedCoupons);
            $this->Session->write($coupon_used_key, json_encode($appliedCoupons));
            $resp['total_reduced'] = $total_reduced/100;
            $resp['total_price'] = $cart->total_price() - $total_reduced/100 + $shipFee;
        }

        echo json_encode($resp);
    }
	
	function mine(){

        if ($this->RequestHandler->isMobile()) {
            $this->layout = 'newv1';
        }

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
		$this->set('ship_type', ShipAddress::$ship_type);
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
		$this->set('ship_type', ShipAddress::$ship_type);
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
		$this->set('ship_type', ShipAddress::$ship_type);
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

        if (empty($order_id)) {
            echo json_encode(array('order_id' => $order_id, 'msg' => '参数错误'));
            exit;
        }

        $currUid = $this->currentUser['id'];
        $order_info = $this->Order->find('first', array(
            'conditions' => array('id' => $order_id, 'creator' => $currUid),
        ));

        if (empty($order_info) || $currUid != $order_info['Order']['creator']) {
            $this->log("denied edit_order_by_owner_ajax: order($order_id) is empty?".empty($order_info).", current-user-id=".$currUid);
            echo json_encode(array('order_id' => $order_id, 'msg' => '您不具备此订单的修改权限。'));
            exit;
        }

        $orig_status = $order_info['Order']['status'];
        $fun($this->Order, $orig_status, $order_id);
	}

//    public function test_notify_paid_done($order_id) {
//        $this->loadModel('Order');
//        $o = $this->Order->findById($order_id);
//        $this->Weixin->notifyPaidDone($o);
//    }

	/**
	 * 商家设置订单的状态
	 */
	function set_status($creator=0){
		$order_id = $_REQUEST['order_id'];
		$status = $_REQUEST['status'];

        $currentUid = $this->currentUser['id'];
        $is_admin = $this->is_admin($currentUid);

		if(empty($order_id) || empty($status)){
			echo json_encode(array('order_id'=>$order_id,'msg'=>'参数错误'));
			exit;
		}

        $order_info = $this->Order->find('first', array(
            'conditions' => array('id' => $order_id),
        ));

		if(empty($order_info)){
			echo json_encode(array('order_id'=>$order_id,'msg'=>'您不具备此订单的修改权限，请联系管理员。'));
			exit;
		}

		$this->loadModel('Brand');
		$brand = $this->Brand->findById($order_info['Order']['brand_id']);
        $is_brand_admin = !empty($brand) && $brand['Brand']['creator'] == $currentUid;

		$orig_status = $order_info['Order']['status'];
        if ($status == ORDER_STATUS_PAID) {
            if (!$is_admin) {
                echo json_encode(array('order_id'=>$order_id,'msg'=>'您没有权限确认已支付'));
                exit;
            }
            if ($orig_status != ORDER_STATUS_WAITING_PAY) {
                echo json_encode(array('order_id'=>$order_id,'msg'=>'订单状态不对'));
                exit;
            }

            $this->Order->updateAll(array('status'=>$status, 'lastupdator'=> $currentUid),array('id'=>$order_id, 'status' => ORDER_STATUS_WAITING_PAY));
            echo json_encode(array('order_id'=>$order_id,'msg'=>'订单已支付'));
            exit;
        } else if ($status == ORDER_STATUS_CANCEL) {
            if ($order_info['Order']['creator'] != $currentUid) {
                echo json_encode(array('order_id'=>$order_id,'msg'=>'您没有权限取消此订单'));
                exit;
            }
            if ($orig_status != ORDER_STATUS_WAITING_PAY) {
                echo json_encode(array('order_id'=>$order_id,'msg'=>'该订单状态已不能取消'));
                exit;
            }

            $this->Order->updateAll(array('status'=>$status, 'lastupdator'=> $currentUid),array('id'=>$order_id, 'status' => ORDER_STATUS_WAITING_PAY));
            echo json_encode(array('order_id'=>$order_id,'msg'=>'订单已取消'));
            exit;
        } else if ($status == ORDER_STATUS_SHIPPED) {

            if (!$is_brand_admin && !$is_admin) {
                echo json_encode(array('order_id'=>$order_id,'msg'=>'您没有权限修改此订单'));
                exit;
            }

            if ($orig_status != ORDER_STATUS_PAID && $orig_status != ORDER_STATUS_SHIPPED) {
                echo json_encode(array('order_id' => $order_id, 'msg' => '您只能将此订单设为已发货'));
                exit;
            }

            $ship_code = $_REQUEST['ship_code'];
            $ship_type = $_REQUEST['ship_type'];
            $this->Order->updateAll(array('status'=>$status,'ship_code'=>"'".addslashes($ship_code)."'",'ship_type'=>$ship_type, 'lastupdator'=>$currentUid),array('id'=>$order_id, 'status' => $orig_status));
            //add weixin message
            $this->loadModel('Oauthbind');
            $user_weixin = $this->Oauthbind->findWxServiceBindByUid($order_info['Order']['creator']);
            if($user_weixin!=false){
                $good = $this->get_order_good_info($order_id);
                $this->log("good info:".$good['good_info'].$good['good_number']);
                $this->Weixin->send_order_shipped_message($user_weixin['oauth_openid'],$ship_type, ShipAddress::$ship_type[$ship_type], $ship_code, $good['good_info'], $good['good_number']);
            }

            echo json_encode(array('order_id'=>$order_id,'msg'=>'订单状态已更新为“已发货”'));
            exit;
        } else{
            echo json_encode(array('order_id'=>$order_id,'msg'=>'不能修改订单状态了'));
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

    /**
     * @param $pids
     * @param $cartsByPid
     * @param $shipPromotionId
     * @return mixed
     */
    protected function applyPromoToCart($pids, $cartsByPid, $shipPromotionId) {
        $cart = new OrderCartItem();
        $cart->user_id = $this->currentUser['id'];

        $shipFee = 0.0;
        $this->loadModel('Product');
        $this->loadModel('ShipPromotion');
        $productByIds = Hash::combine($this->Product->findPublishedProductsByIds($pids), '{n}.Product.id', '{n}.Product');
        foreach ($cartsByPid as $pid => $cartItem) {
            $pp = $shipPromotionId ? $this->ShipPromotion->find_ship_promotion($pid, $shipPromotionId) : array();
            $num = ($pid != ShipPromotion::QUNAR_PROMOTE_ID && $cartsByPid[$pid]['num']) ? $cartsByPid[$pid]['num'] : 1;
            $singleShipFee = empty($pp) || !isset($pp['ship_price']) ? $productByIds[$pid]['ship_fee'] : $pp['ship_price'];
            $shipFee += ShipPromotion::calculateShipFee($pid, $singleShipFee, $num, null);
            $itemPrice = empty($pp) || !isset($pp['price']) ? $productByIds[$pid]['price'] : $pp['price'];
            $cart->add_product_item($productByIds[$pid]['brand_id'], $pid, $itemPrice, $num, $cartItem['used_coupons'], $cartItem['name']);
        }
        return array($cart, $shipFee);
    }

    /**
     * @return array
     */
    protected function cartsByPid() {
        $this->loadModel('Cart');
        $dbCartItems = $this->Cart->find('all', array(
            'conditions' => array(
                'status' => 0,
                'order_id' => null,
                'OR' => $this->user_condition
            )));

        return Hash::combine($dbCartItems, '{n}.Cart.product_id', '{n}.Cart');
    }

    /**
     * @param $brand_id
     * @param $uid
     * @param $order_id
     */
    protected function apply_coupons_to_order($brand_id, $uid, $order_id) {
        //TODO：检查是否可以应用这些券的合法性
        $used_coupons_str = $this->Session->read(self::key_balanced_conpons($brand_id));
        if ($used_coupons_str) {
            $used_coupons = json_decode($used_coupons_str);
        }
        if (!empty($used_coupons) && is_array($used_coupons)) {
            $this->loadModel('CouponItem');
            if ($this->CouponItem->apply_coupons_to_order($uid, $order_id, $used_coupons)) {
                $computed = $this->CouponItem->compute_coupons_for_order($uid, $order_id);
                $applied_coupons = $computed['applied'];
                $coupon_total = $computed['reduced'];
                if (!empty($applied_coupons)) {
                    $reduced = $coupon_total / 100;
                    $toUpdate = array('applied_coupons' => '\''.implode(',', $applied_coupons).'\'',
                        'coupon_total' => $coupon_total,
                        'total_all_price' => 'if(total_all_price - ' . $reduced . ' < 0, 0, total_all_price - ' . $reduced . ')');
                    $this->Order->updateAll($toUpdate, array('id' => $order_id, 'status' => ORDER_STATUS_WAITING_PAY));
                }
                if (count($used_coupons) != count($applied_coupons) || array_diff($used_coupons, $applied_coupons)) {
                    $this->log("not expected coupon size: order_id=$order_id, original:" . json_encode($used_coupons) . ", final:" . json_encode($applied_coupons));
                }
            }
        }
    }
}