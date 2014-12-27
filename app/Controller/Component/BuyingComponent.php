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

            if ($success) {
                $cartM->add_to_cart($product_id, $num, $specId, $type, $tryId, $uid, $sessionId, $prodTry, $shichituan);
            }
            $returnInfo = array('success' => $success, 'reason' => $reason, 'not_comment_cnt' => intval($notCommentedCnt));
        } else {
            $cartM->add_to_cart($product_id, $num, $specId, $type, $tryId, $uid, $sessionId);
            $returnInfo = array('success' => true, 'msg' => __('Success add to cart.'));
        }
        return $returnInfo;
    }

    public function total_reduced($uid, $applied_coupons, $applied_code) {
        $itemM = ClassRegistry::init('CouponItem');
        $total_reduce = $itemM->compute_total_reduced($uid, $applied_coupons);
        //TODO: fix coupon code!!!
        if ($applied_code == 'pengyoushuo2014') {
            $total_reduce += 500;
        }
        return $total_reduce;
    }


    /**
     * @param $cartsByPid
     * @param $shipPromotionId
     * @param $balancePids
     * @param $uid
     * @param null $sessionId
     * @throws CakeException
     * @throws Exception
     * @return array
     */
    public function createTmpCarts(&$cartsByPid, $shipPromotionId, $balancePids, $uid, $sessionId = null) {
        $isTry = !empty($balancePids) && $balancePids['try'];
        if ($isTry) {

            $pids = $balancePids;
            unset($pids['try']);
            if (empty($pids)) {
                throw new Exception("try type set but no pid found!");
            }
            $pid = $pids[0];

            $tryId =$balancePids['try'];
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

            $cartM = ClassRegistry::init('Cart');
            $cartItem = $cartM->find_try_cart_item($pid, $uid);
            if (empty($cartItem)) {
                throw new Exception("error to find try_cart_item: $pid , $uid");
            }

            $cart = new OrderCartItem();
            $cart->is_try = true;
            $cart->add_product_item($products[0]['Product']['brand_id'], $pid, calculate_try_price($prodTry['ProductTry']['price'], $uid), 1, array(), $cartItem['Cart']['name']);
            $shipFee = 0;
        } else {
            if (!empty($balancePids)) {
                $pids = $balancePids;
                $cartsByPid = $this->cartsByPid($balancePids, $uid, $sessionId);
            } else {
                $pids = array_keys($cartsByPid);
            }
            list($cart, $shipFee, $shipFees) = $this->applyPromoToCart($pids, $cartsByPid, $shipPromotionId, $uid);
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
            'OR' => create_user_cond($uid, $session_id)
        );
        if (!empty($limitPids)) {
            $cond['product_id'] = $limitPids;
        }
        $dbCartItems = $cartM->find('all', array(
            'conditions' => $cond));

        return Hash::combine($dbCartItems, '{n}.Cart.product_id', '{n}.Cart');
    }


    /**
     * @param $pids
     * @param $cartsByPid
     * @param $shipPromotionId
     * @param $uid
     * @throws CakeException
     * @return mixed
     */
    public function applyPromoToCart($pids, $cartsByPid, $shipPromotionId, $uid) {
        $cart = new OrderCartItem();
        $cart->user_id = $uid;

        $totalPrices = array();

        $proM = ClassRegistry::init('Product');
        $shipPromo = ClassRegistry::init('ShipPromotion');
        $productByIds = $proM->find_published_products_by_ids($pids, array('Product.ship_fee'));
        foreach ($cartsByPid as $pid => $cartItem) {
            $brand_id = $productByIds[$pid]['brand_id'];
            $pp = $shipPromotionId ? $shipPromo->find_ship_promotion($pid, $shipPromotionId) : array();
            $num = ($pid != ShipPromotion::QUNAR_PROMOTE_ID && $cartsByPid[$pid]['num']) ? $cartsByPid[$pid]['num'] : 1;
            $itemPrice = empty($pp) || !isset($pp['price']) ? calculate_price($pid, $productByIds[$pid]['price'], $uid) : $pp['price'];

            $totalPrices[$brand_id] += ($itemPrice * $num);

            $cart->add_product_item($brand_id, $pid, $itemPrice, $num, $cartItem['used_coupons'], $cartItem['name']);
        }


        $shipSM = ClassRegistry::init('ShipSetting');
        $shipSettings = $shipSM->find_by_pids($pids, null);

        $shipFeeContext = array();
        $shipFees = array();
        $brandItems = $cart->brandItems;
        foreach ($brandItems as $brandId => $brandItem) {
            foreach ($brandItem->items as $pid => $item) {
                $pidShipSettings = array();
                foreach($shipSettings as $val){
                    if($val['ShipSetting']['product_id'] == $pid){
                        $pidShipSettings[] = $val;
                    }
                };
                $num = ($cartsByPid[$pid]['num']) ? $cartsByPid[$pid]['num'] : 1;
                $singleShipFee = empty($pp) || !isset($pp['ship_price']) ? $productByIds[$pid]['ship_fee'] : $pp['ship_price'];
                $total_price = $totalPrices[$brandId];
                //FIXME: add ship fee by province
                $shipFees[$brandId] += ShipPromotion::calculateShipFee($total_price, $singleShipFee, $num, $pidShipSettings, $shipFeeContext);
            }
        }

        $shipFee = 0;
        foreach($shipFees as $ship) {
            $shipFee += $ship;
        }

        return array($cart, $shipFee, $shipFees);
    }

}
?>