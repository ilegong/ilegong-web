<?php 

class BuyingComponent extends Component {
	
	/* component configuration */
	var $name = 'BuyingComponent';
	var $params = array();
	/**
	 * Contructor function
	 * @param Object &$controller pointer to calling controller
	 */
	function startup(&$controller) {
		$this->params = $controller->params;
	}

    function convert_cart_type($typeStr) {
        if ($typeStr == 'normal') {
            return CART_ITEM_TYPE_NORMAL;
        } else if ($typeStr == 'try') {
            return CART_ITEM_TYPE_TRY;
        } else if ($typeStr == 'quick_buy') {
            return CART_ITEM_TYPE_QUICK_ORDER;
        }  else {
            return CART_ITEM_TYPE_NORMAL;
        }
    }

    /**
     * check and add to cart
     * @param $cartM
     * @param $type
     * @param $tryId
     * @param $uid
     * @param $num
     * @param $product_id
     * @param $specId
     * @param $sessionId
     * @return array
     * @throws CakeException
     */
    public function check_and_add($cartM, $type, $tryId, $uid, $num, $product_id, $specId, $sessionId) {
        if ($type == CART_ITEM_TYPE_TRY) {
            $success = true;
            $reason = '';
            if (!$tryId) {
                $success = false;
                $reason = 'no_try_id';
            } else {
                if (empty($uid)) {
                    $success = false;
                    $reason = 'not_login';
                } else {
                    $tryM = ClassRegistry::init('ProductTry');
                    $prodTry = $tryM->findById($tryId);
                    list($afford, $my_limit, $total_left) = afford_product_try($tryId, $uid, $prodTry);
                    if ($total_left == 0) {
                        $reason = 'sold_out';
                        $success = false;
                    }
                    if (!$afford || ($my_limit >= 0 && $my_limit < $num)) {
                        $success = false;
                        $reason = 'already_buy';
                    }

                    if ($success) {
                        $sctM = ClassRegistry::init('Shichituan');
                        $shichituan = $sctM->find_in_period($uid, get_shichituan_period());
                        $parallelCnt = (!empty($shichituan)) ? 2 : 1;
                        $orderShichiM = ClassRegistry::init('OrderShichi');
                        $notCommentedCnt = $orderShichiM->find('count', array('conditions' => array(
                            'creator' => $uid,
                            'is_comment' => 0
                        )));
                        if ($parallelCnt <= $notCommentedCnt) {
                            $success = false;
                            $reason = 'not_comment';
                        }
                    }
                }
            }

            $returnInfo = array('success' => $success, 'reason' => $reason, 'not_comment_cnt' => intval($notCommentedCnt));
            if ($success) {
                $savedD = $cartM->add_to_cart($product_id, $num, $specId, $type, $tryId, $uid, $sessionId, $prodTry, $shichituan);
                $returnInfo['id'] = $savedD['Cart']['id'];
            }
        } else {
            $savedD = $cartM->add_to_cart($product_id, $num, $specId, $type, $tryId, $uid, $sessionId);
            if ($savedD) {
                $cartId = $savedD['Cart']['id'];
            }
            $returnInfo = array('success' => true, 'msg' => __('Success add to cart.'), 'id' => $cartId);
        }
        return $returnInfo;
    }

    public function total_reduced($uid, $applied_coupons, $applied_code, $score_num) {
        $itemM = ClassRegistry::init('CouponItem');
        $total_reduce = $itemM->compute_total_reduced($uid, $applied_coupons);
        //TODO: fix coupon code!!!
        if ($applied_code == 'pengyoushuo2014') {
            $total_reduce += 500;
        }

        if (!empty($score_num)) {
            $total_reduce += intval($score_num);
        }
        return $total_reduce;
    }


    /**
     * @param $shipPromotionId
     * @param $balanceCartIds (cart ids usually, but can put a 'try'=>$pid to identity a trying product)
     * @param $uid
     * @param null $sessionId
     * @throws CakeException
     * @throws Exception
     * @return array
     */
    public function createTmpCarts($shipPromotionId, $balanceCartIds, $uid, $sessionId = null) {
        $isTry = !empty($balanceCartIds) && $balanceCartIds['try'];
        if ($isTry) {

            $pids = $balanceCartIds;
            unset($pids['try']);
            if (empty($pids)) {
                throw new Exception("try type set but no pid found!");
            }
            $cartId = $pids[0];

            $cartM = ClassRegistry::init('Cart');
            $cartItem = $cartM->findById($cartId);
            if (empty($cartItem)) {
                throw new Exception("error to find try_cart_item: $cartId , $uid");
            }


            $pid = $cartItem['Cart']['product_id'];

            $pids = array($pid);

            $tryId =$balanceCartIds['try'];
            $prodTryM = ClassRegistry::init('ProductTry');
            $prodTry = $prodTryM->findById($tryId);
            if (empty($prodTry)) {
                throw new Exception("cannot found ".$tryId);
            }

            $pidOfTry = $prodTry['ProductTry']['product_id'];
            if ($pidOfTry != $pid) {
                throw new Exception("product_id not equal: $pidOfTry ,". $pid);
            }

            $proM = ClassRegistry::init('Product');
            $products = $proM->find_products_by_ids($pid, array(), false);
            if (empty($products)) {
                throw new Exception("cannot find the specified product: $pid");
            }

            $cart = new OrderCartItem();
            $cart->is_try = true;
            $brand_id = $products[0]['Product']['brand_id'];
            $cart->add_product_item($brand_id, $cartItem['Cart']['id'], calculate_try_price($prodTry['ProductTry']['price'], $uid), 1, array(), $cartItem['Cart']['name'], $pid);
            $shipFee = 0;
            $shipFees = array($brand_id => $shipFee);
        } else {
            $cartsDict = $this->cartsByIds($balanceCartIds, $uid, $sessionId);
            $pids = array_unique(Hash::extract($cartsDict, '{n}.product_id'));
            list($cart, $shipFee, $shipFees) = $this->applyPromoToCart($cartsDict, $shipPromotionId, $uid);
        }
        return array($pids, $cart, $shipFee, $shipFees);
    }

    /**
     * @param array $limitPids
     * @param $uid
     * @param null $session_id
     * @throws CakeException
     * @return array
     */
    public function cartsByPid($limitPids = array(), $uid, $session_id = null) {
        $cartM = ClassRegistry::init('Cart');
        $cond = array(
            'status' => 0,
            'order_id' => null,
            'type != ' => CART_ITEM_TYPE_TRY,
            'OR' => create_user_cond($uid, $session_id),
        );
        if (!empty($limitPids)) {
            $cond['product_id'] = $limitPids;
        }
        $dbCartItems = $cartM->find('all', array(
            'conditions' => $cond,
            'order' => 'id desc',
            )
        );

        return Hash::combine($dbCartItems, '{n}.Cart.product_id', '{n}.Cart');
    }

    /**
     * @param array $limitCartIds
     * @param $uid
     * @param null $session_id
     * @throws CakeException
     * @return array
     */
    public function cartsByIds($limitCartIds = array(), $uid, $session_id = null) {
        $cartM = ClassRegistry::init('Cart');
        $cond = array(
            'status' => 0,
            'order_id' => null,
            'OR' => create_user_cond($uid, $session_id)
        );

        if (empty($limitCartIds)) {
            $cond['type'] = CART_ITEM_TYPE_NORMAL;
        } else {
            $cond['id'] = $limitCartIds;
        }

        $dbCartItems = $cartM->find('all', array(
            'conditions' => $cond,
            'order' => 'id desc',
            )
        );

        return Hash::combine($dbCartItems, '{n}.Cart.id', '{n}.Cart');
    }


    /**
     * @param $cartsByIds
     * @param $shipPromotionId
     * @param $uid
     * @throws CakeException
     * @return mixed
     */
    public function applyPromoToCart($cartsByIds, $shipPromotionId, $uid) {
        $cart = new OrderCartItem();
        $cart->user_id = $uid;

        $totalPrices = array();
        $pids = array_unique(Hash::extract($cartsByIds, '{n}.product_id'));

        $proM = ClassRegistry::init('Product');
        $shipPromo = ClassRegistry::init('ShipPromotion');
        //only not publish product brand show problem
        //$productByIds = $proM->find_published_products_by_ids($pids, array('Product.ship_fee'));
        $productByIds = $proM->find_products_by_ids($pids, array('Product.ship_fee'),false);

        $params = array();
        foreach($cartsByIds as $cid => $cartItem) {
            $pid = $cartItem['product_id'];
            //no publish product default price 0
            if($productByIds[$pid]['published']==0){
                $defaultPrice = 0;
                $published = false;
            }else{
                $defaultPrice = $productByIds[$pid]['price'];
                $published = true;
            }
            $params[] = array('pid' => $pid, 'specId' => $cartItem['specId'], 'defaultPrice' => $defaultPrice, 'published'=>$published);
        }

        $result = get_spec_by_pid_and_sid($params);

        $numByPid = array();
        foreach ($cartsByIds as $cid => $cartItem) {
            $pid = $cartItem['product_id'];
            $brand_id = $productByIds[$pid]['brand_id'];
            $pp = $shipPromotionId ? $shipPromo->find_ship_promotion($pid, $shipPromotionId) : array();
            $num = $cartItem['num'];
            $numByPid[$pid] += $num;
            $published = true;
            if($productByIds[$pid]['published']==0){
                $published = false;
            }
            $price = $result[cart_dict_key($pid, $cartItem['specId'])][0];

            list($itemPrice,) = calculate_price($pid, $price, $uid, $num, $cartItem['id'], $pp);

            $totalPrices[$brand_id] += ($itemPrice * $num);
            $cart->add_product_item($brand_id, $cid, $itemPrice, $num, $cartItem['used_coupons'], $cartItem['name'], $pid, $published);
        }


        $shipSM = ClassRegistry::init('ShipSetting');
        $shipSettings = $shipSM->find_by_pids($pids, null);

        $shipFeeContext = array();
        $shipFees = array();
        $brandItems = $cart->brandItems;
        foreach ($brandItems as $brandId => $brandItem) {
            $calculated_pid = array();
            foreach ($brandItem->items as $cid => $item) {
                $pid = $item->pid;

                if (array_search($pid, $calculated_pid) !== false) {
                    continue;
                }

                $pidShipSettings = array();
                foreach($shipSettings as $val){
                    if($val['ShipSetting']['product_id'] == $pid){
                        $pidShipSettings[] = $val;
                    }
                };
                $num = $numByPid[$pid];
                $pp = $shipPromotionId ? $shipPromo->find_ship_promotion($pid, $shipPromotionId) : array();
                $singleShipFee = empty($pp) || !isset($pp['ship_price']) ? $productByIds[$pid]['ship_fee'] : $pp['ship_price'];
                $total_price = $totalPrices[$brandId];
                //FIXME: add ship fee by province
                $shipFees[$brandId] += ShipPromotion::calculateShipFee($total_price, $singleShipFee, $num, $pidShipSettings, $shipFeeContext);
                $calculated_pid[] = $pid;
            }
        }

        $shipFee = 0;
        foreach($shipFees as $ship) {
            $shipFee += $ship;
        }

        return array($cart, $shipFee, $shipFees);
    }

    function confirm_receive($uid, $order_id){
        $this->edit_status_by_owner_ajax($uid, $order_id, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, '已收货', $uid);
    }

    function confirm_undo($uid, $order_id){
        $this->edit_order_by_owner_ajax($uid, $order_id, function($orderModel, $order, $order_id) use ($uid){
            if ($order['Order']['status'] == ORDER_STATUS_WAITING_PAY) {
                $orderModel->cancelWaitingPayOrder($uid, $order_id, $order['Order']['creator']);
                echo json_encode(array('order_id' => $order_id, 'ok' => 1, 'msg' => '订单已取消'));
                exit;
            } else {
                echo json_encode(array('order_id' => $order_id, 'ok' => 0, 'msg' => '不能修改订单状态了。'));
                exit;
            }
        });
    }

    function confirm_remove($uid, $order_id){
        $this->edit_order_by_owner_ajax($uid, $order_id, function($orderModel, $order, $order_id){
            if ($order['Order']['status'] == ORDER_STATUS_CANCEL) {
                $orderModel->updateAll(array('published' => 0), array('id' => $order_id));
                echo json_encode(array('order_id' => $order_id, 'ok' => 1));
            } else {
                echo json_encode(array('order_id' => $order_id, 'ok' => 0));
            }
            exit;
        });
    }

    private function edit_status_by_owner_ajax($uid, $order_id, $origStatus, $toStatus, $okMsg = '', $operator = 0){
        $this->edit_order_by_owner_ajax($uid, $order_id, function($orderModel, $order, $order_id) use ($origStatus, $toStatus,$okMsg, $operator) {
            if ($order['Order']['status'] == $origStatus) {
                $orderModel->update_order_status($order_id, $toStatus, $origStatus, $operator);
                echo json_encode(array('order_id' => $order_id, 'ok' => 1, 'msg' => $okMsg));
                exit;
            } else {
                echo json_encode(array('order_id' => $order_id, 'ok' => 0, 'msg' => '不能修改订单状态了。'));
                exit;
            }
        });
    }

    /**
     * @param $currUid
     * @param $order_id
     * @param $fun callback:  a callback with parameters: OrderModel, curr_status, and order_id
     */
    private function edit_order_by_owner_ajax($currUid, $order_id, $fun){

        if (empty($order_id)) {
            echo json_encode(array('order_id' => $order_id, 'msg' => '参数错误'));
            exit;
        }

        $orderM = ClassRegistry::init('Order');

        $order_info = $orderM->find('first', array(
            'conditions' => array('id' => $order_id, 'creator' => $currUid),
        ));

        if (empty($order_info) || $currUid != $order_info['Order']['creator']) {
            $this->log("denied edit_order_by_owner_ajax: order($order_id) is empty?".empty($order_info).", current-user-id=".$currUid);
            echo json_encode(array('order_id' => $order_id, 'msg' => '您不具备此订单的修改权限。'));
            exit;
        }

        $fun($orderM, $order_info, $order_id);
    }

}
?>