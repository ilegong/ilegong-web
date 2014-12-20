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
}
?>