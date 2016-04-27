<?php

class OrdersController extends AppController {

    var $name = 'Orders';

    var $user_condition = array();

    public $components = array('Weixin', 'Buying', 'Paginator');

    var $customized_not_logged = array('apply_coupon');

    public function __construct($request = null, $response = null) {
        $this->helpers[] = 'PhpExcel';
        parent::__construct($request, $response);
        $this->pageTitle = __('订单');
    }

    /**
     * @return string
     */
    public static function key_balanced_conpons() {
        return "Balance.coupons";
    }

    public static function key_balanced_scores() {
        return "Balance.apply_scores";
    }

    public static function key_balanced_conpon_global() {
        return "Balance.coupons.global";
    }

    public static function key_balanced_ship_promotion_id() {
        return "Balance.promotion.id";
    }

    public static function key_balance_pids() {
        return "Balance.balance.pids";
    }

    public static function key_balance_total_price() {
        return "Balance.balance.totalprice";
    }

    public static function key_balanced_promotion_code() {
        return "Balance.apply_promotion_codes";
    }

    public static function key_balanced_ship_type() {
        return "Balance.apply_ship_type";
    }

    public static function key_balanced_ship_fee() {
        return "Balance.apply_ship_fee";
    }

    public function clean_score_and_coupon() {
        // 注意必须清除key_balanced_scores
        $this->Session->write(self::key_balanced_scores(), '');
        $this->Session->write(self::key_balanced_conpon_global(), '[]');
        $this->Session->write(self::key_balanced_conpons(), '[]');
    }

    function beforeFilter() {
        parent::beforeFilter();
        if (empty($this->currentUser['id']) && array_search($this->request->params['action'], $this->customized_not_logged) === false) {
            $this->redirect($this->login_link());
        }
        $this->user_condition = array(
            'session_id' => $this->Session->id(),
        );
        if ($this->currentUser['id']) {
            $this->user_condition['creator'] = $this->currentUser['id'];
        }
    }

    /**
     * 结算提交订单，进入支付页面。
     */
    function balance() {
        $this->loadModel('Cart');
        $this->loadModel('Product');
        $product_ids = array();
        $shipPromotionId = intval($_REQUEST['ship_promotion']);
        $group_tag = $_REQUEST['group_tag'];
        $ship_type = $_REQUEST['ship_type'];
        if (!$shipPromotionId) {
            $shipPromotionId = intval($this->Session->read(self::key_balanced_ship_promotion_id()));
        }
        $this->loadModel('ShipPromotion');
        $shipPromo = $this->ShipPromotion;

        //check problem:
//        $couponItems = $this->CouponItem->find_my_valid_coupon_items($uid, array_merge($appliedCoupons, (array)$coupon_item_id));
//        $couponsByShared = array_filter($couponItems, function ($val) {
//            return ($val['Coupon']['type'] == COUPON_TYPE_TYPE_SHARE_OFFER);
//        });
//
//        //这里必须安店面去限定
//        //要把没有查询到的couponItem去掉
//        if(count($couponsByShared) <= $cart->brandItems[$brand_id]->total_num()) {
//            //            if($cart->could_apply($brand_id, $cou)){
//            //TODO: 需要考虑券是否满足可用性等等
//            $appliedCoupons[] = $coupon_item_id;
//            $changed = true;
//        } else {
//            $reason = 'share_type_coupon_exceed';
//        }

        $carts = array();
        $nums = array();
        $cart_by_pids = array();

        $specified_cart_ids = $this->specified_balance_pids();
        $tryId = $specified_cart_ids['try'];
        unset($specified_cart_ids['try']);

        $error_back_url = '/';
        if (!empty($specified_cart_ids)) {
            $cond = array(
                'status' => CART_ITEM_STATUS_NEW,
                'order_id' => null,
                'num > 0',
                'OR' => $this->user_condition,
                'id' => $specified_cart_ids,
            );
            if ($tryId) {
                $cond['type'] = CART_ITEM_TYPE_TRY;
                $error_back_url = '/shichi/list_pai.html';
            } else {
                $cond['type !='] = CART_ITEM_TYPE_TRY;
                $error_back_url = '/carts/listcart.html';
            }
            $carts = $this->Cart->find('all', array(
                'conditions' => $cond));

            foreach ($carts as $c) {
                $cart_pid = $c['Cart']['product_id'];
                $product_ids[] = $cart_pid;
                $nums[$cart_pid] += $c['Cart']['num'];
                $cart_by_pids[$cart_pid][] = $c;
            }

            $product_ids = array_unique($product_ids);

        }
        if (empty($carts)) {
            $this->__message('您没有选择结算商品，请返回购物车检查', $error_back_url);
            return;
        }
        $uid = $this->currentUser['id'];
        $allP = $this->Product->find('all', array('conditions' => array(
            'id' => $product_ids,
            'published' => PUBLISH_YES
        )));
        $params = array();
        foreach ($allP as $p) {
            $pid = $p['Product']['id'];
            $carts_of_p = $cart_by_pids[$pid];
            if (!empty($carts_of_p)) {
                foreach ($carts_of_p as $cp) {
                    $specId = $cp['Cart']['specId'];
                    $price = $p['Product']['price'];
                    $params[cart_dict_key($pid, $specId)] = array('pid' => $pid, 'specId' => $specId, 'defaultPrice' => $price);
                }
            }
        }
        $business = array();
        foreach ($allP as $p) {
            if (!is_array($business[$p['Product']['brand_id']])) {
                $business[$p['Product']['brand_id']] = array();
            }
            $business[$p['Product']['brand_id']][] = $p['Product'];
            $pid = $p['Product']['id'];
            $num = $nums[$pid];
            list($afford_for_curr_user, $limit_cur_user, , $least_num) = $tryId ? afford_product_try($tryId, $uid) : AppController::__affordToUser($pid, $uid);
            $pName = $p['Product']['name'];
            if (!$afford_for_curr_user) {
                $this->__message($pName . __('已售罄或您已经购买超限，请从购物车中调整后再结算'), $error_back_url, 5);
                return;
            } else if ($limit_cur_user == 0 || ($limit_cur_user > 0 && $num > $limit_cur_user)) {
                $this->__message($pName . __('购买超限，请从购物车中调整后再结算'), $error_back_url, 5);
            } else if ($least_num > 1 && $num < $least_num) {
                $this->__message('该商品满' . $least_num . '起送', '/', 3);
            }
        }

        $pids = Hash::extract($allP, '{n}.Product.id');

        $this->loadModel('OrderConsignee');
        if ($ship_type == 'ziti') {
            $addressId = $this->Session->read('pickupConsignee.id');
        } else {
            $addressId = $this->Session->read('OrderConsignee.id');
        }
        $address = $this->OrderConsignee->find('first', array(
            'conditions' => array('id' => $addressId, 'creator' => $uid)
        ));
        if (empty($address)) {
            $this->log('orders_balance: cannot find address:' . $addressId . ', uid=' . $uid);
        } else {
            //update use time
            $this->OrderConsignee->updateAll(array('updated' => "'" . date('Y-m-d H:i:s') . "'"), array('id' => $addressId));
            $provinceId = $address['OrderConsignee']['province_id'];
        }

        $this->loadModel('ShipSetting');
        $shipSettings = $this->ShipSetting->find_by_pids($pids, $provinceId);

        $all_order_total = 0;
        $order_results = array();
        $saveFailed = false;

        $result = get_spec_by_pid_and_sid($params);
        //split order by brand
        foreach ($business as $brand_id => $products) {
            $total_price = 0.0;
            foreach ($products as $pro) {
                $pid = $pro['id'];
                $pps = $cart_by_pids[$pid];
                if (!empty($pps)) {
                    foreach ($pps as $carts_of_p) {
                        $pp = $shipPromotionId ? $shipPromo->find_ship_promotion($pid, $shipPromotionId) : array();
                        $price = $result[cart_dict_key($pid, $carts_of_p['Cart']['specId'])][0];
                        $num = $carts_of_p['Cart']['num'];
                        list($itemPrice,) = calculate_price($pid, $price, $uid, $num, $carts_of_p['Cart']['id'], $pp);

                        $total_price += $itemPrice * $num;
                    }
                }
            }

            if ($total_price <= 0) {
                $this->Session->setFlash('订单金额错误，请返回购物车查看');
                $this->redirect($error_back_url);
            }


            $shipFeeContext = array();
            $ship_fee = 0.0;
            $ship_fees = array();
            foreach ($products as $pro) {
                $pid = $pro['id'];
                $pidShipSettings = array();
                //TODO: 自提运费设置动态化
                foreach ($shipSettings as $val) {
                    if ($val['ShipSetting']['product_id'] == $pid) {
                        $pidShipSettings[] = $val;
                    }
                }
                $num = $nums[$pid];
                if ($tryId) {
                    $ship_fees[$pid] = 0;
                } else {
                    $pp = $shipPromotionId ? $this->ShipPromotion->find_ship_promotion($pid, $shipPromotionId) : array();
                    $singleShipFee = empty($pp) ? $pro['ship_fee'] : $pp['ship_price'];
                    $ship_fees[$pid] = ShipPromotion::calculateShipFee($total_price, $singleShipFee, $num, $pidShipSettings, $shipFeeContext);
                }
                $ship_fee += $ship_fees[$pid];
            }
            $data = array();
            if ($tryId) {
                $data['try_id'] = $tryId;
            }
            $ship_fee = ShipPromotion::calculateBrandShipFee($brand_id, $ship_fee, $total_price);
            if ($ship_type == ZITI_TAG) {
                $ship_fee = 0;
            }
            $data['total_price'] = $total_price;
            $total_all_price = $total_price + $ship_fee;
            $all_order_total += $total_all_price;
            $data['total_all_price'] = $total_all_price;
            $data['ship_fee'] = $ship_fee;
            $data['brand_id'] = $brand_id;
            $data['creator'] = $uid;

            $remark = $_REQUEST['remark_' . $brand_id];
            $data['remark'] = empty($remark) ? "" : $remark;

            $data['consignee_id'] = $addressId;
            $data['consignee_name'] = $address['OrderConsignee']['name'];
            $data['consignee_area'] = $address['OrderConsignee']['area'];
            $data['consignee_address'] = $address['OrderConsignee']['address'];
            $data['consignee_mobilephone'] = $address['OrderConsignee']['mobilephone'];
            $data['ship_mark'] = KUAIDI_TAG;
            if ($ship_type == 'ziti') {
                $data['consignee_id'] = $address['OrderConsignee']['ziti_id'];
                $data['consignee_area'] = '';
                $data['ship_mark'] = ZITI_TAG;
            }
            if (empty($data['consignee_name']) || empty($data['consignee_address']) || empty($data['consignee_mobilephone'])) {
                $this->__message('请填写收货人信息', '/orders/info?from=list_cart');
            }
            $this->Order->create();

            if ($this->Order->save($data)) {
                $order_id = $this->Order->getLastInsertID();
                if ($order_id) {
                    $order_results[$brand_id] = array($order_id, $total_all_price);
                    //add group buy record
                    if ($group_tag) {
                        $group_product_id = $product_ids[0];
                        $this->loadModel('GroupBuy');
                        $groupBuyInfo = $this->GroupBuy->getGroupBuyProductInfo($group_product_id);
                        $group_buy_label = $groupBuyInfo['group_buy_label'];
                        $this->loadModel('GroupBuyRecord');
                        $this->GroupBuyRecord->save(array('id' => null, 'group_buy_label' => $group_buy_label, 'user_id' => $uid, 'order_id' => $order_id, 'product_id' => $group_product_id, 'group_buy_tag' => $group_tag, 'created' => date('Y-m-d H:i:s')));
                    }
                }
                foreach ($products as $pro) {
                    $pid = $pro['id'];
                    if (!empty($cart_by_pids[$pid])) {
                        $pid_list = Hash::extract($cart_by_pids[$pid], '{n}.Cart.id');

                        $this->Cart->updateAll(array('order_id' => $order_id, 'status' => CART_ITEM_STATUS_BALANCED),
                            array('id' => $pid_list, 'status' => CART_ITEM_STATUS_NEW));

                        if (!$tryId) {
                            $this->Product->update_storage_saled($pid, $nums[$pid]);
                        }
                    }
                }
            } else {
                $saveFailed = true;
            }
        }

        if (!$tryId && !$saveFailed) {
            $score_consumed = 0;
            $score = intval($this->Session->read(self::key_balanced_scores()));
            $order_id_spents = array();
            foreach ($order_results as $brand_id => $order_val) {
                $order_id = $order_val[0];
                $total_all_price = $order_val[1];
                $this->apply_coupons_to_order($brand_id, $uid, $order_id, $order_results);
                $this->apply_coupon_code_to_order($uid, $order_id);

                if ($score > 0) {
                    $spent_on_order = round($score * ($total_all_price / $all_order_total));
                    $reduced = $spent_on_order / 100;
                    $toUpdate = array('applied_score' => $spent_on_order,
                        'total_all_price' => 'if(total_all_price - ' . $reduced . ' < 0, 0, total_all_price - ' . $reduced . ')');
                    if ($this->Order->updateAll($toUpdate, array('id' => $order_id, 'status' => ORDER_STATUS_WAITING_PAY))) {
                        $this->log('apply user score=' . $spent_on_order . ' to order-id=' . $order_id . ' successfully');
                        $score_consumed += $spent_on_order;
                        $order_id_spents[$order_id] = $spent_on_order;
                    }
                }
            }

            if ($score_consumed > 0) {
                $scoreM = ClassRegistry::init('Score');
                $scoreM->spent_score_by_order($uid, $score_consumed, $order_id_spents);
                $this->loadModel('User');
                $this->User->add_score($uid, -$score_consumed);
            }
        }

        $this->_clear_coupons_scores();

        if (!$saveFailed) {
            if (count($order_results) == 1) {
                $newOIds = array_values($order_results);
                $this->redirect(array('action' => 'detail', $newOIds[0][0], 'pay'));
            } else {
                $this->Session->setFlash('订单已生成,不同商家的商品会拆分到不同的订单，请您分别付款。');
                $this->redirect('/orders/mine');
            }
        } else {
            $this->Session->setFlash('订单生成失败，请稍候重试或联系管理员');
            $this->redirect('/orders/info');
        }
    }

    /**
     * 订单信息页，确认各项订单信息
     * @param int|string $order_id
     */
    function info($order_id = '') {

        $cidAttr = array();
        if ($_GET['from'] == 'list_cart' || $_GET['from'] == 'quick_buy' || $_GET['from'] == 'try' || $_GET['from'] == 'group') {
            $pidList = $_REQUEST['pid_list'];
            if (!empty($pidList)) {
                $cidAttr = preg_split('/,/', $pidList);
                if ($_GET['from'] == 'try') {
                    $cidAttr['try'] = $_GET['try'];
                }
                if ($_GET['from'] == 'group') {
                    //TODO
                    $this->set('hideShipTipInfo', true);
                    $groupTag = $_GET['group_tag'];
                }
            } else {
                $cidAttr = json_decode($this->Session->read(self::key_balance_pids()), true);
            }
        }

        //If came from info.html and with an action to info.html, don't reset balance pid list
        if (empty($_GET['action'])) {
            $this->Session->write(self::key_balance_pids(), json_encode($cidAttr));
        }

        $this->Session->write(self::key_balanced_ship_promotion_id(), '');

        $kuaidi_consignee_exist = false;
        $this->loadModel('OrderConsignee');
        $shipPromotionId = intval($_REQUEST['ship_promotion']);
        $this->loadModel('Cart');
        $this->loadModel('Product');
        $this->loadModel('ShipPromotion');

        $uid = $this->currentUser['id'];
        $sessionId = $this->Session->id();
        if (empty($order_id)) {
            if (!empty($_COOKIE['cart_products'])) {
                $cartsByPid = $this->Buying->cartsByPid(null, $uid, $sessionId);
                $info = explode(',', $_COOKIE['cart_products']);
                mergeCartWithDb($uid, $info, $cartsByPid, $this->Product, $this->Cart, $sessionId);
                setcookie("cart_products", '', time() - 3600, '/');
            }
        } else {
            $this->log("/orders/info with a orderid=$order_id");
            $this->__message('订单已经生成，不能再修改', '/orders/detail/' . $order_id);
            return;
        }

        $balance_cids = $this->specified_balance_pids();

        list($pids, $cart, $shipFee, , $product_info, $cartsDict) = $this->Buying->createTmpCarts($shipPromotionId, $balance_cids, $uid, $sessionId);
        if (empty($balance_cids)) {
            $balance_cids = $cart->list_cart_id();
            $this->Session->write(self::key_balance_pids(), json_encode($balance_cids));
        }

        $all_consignees = $this->OrderConsignee->find('all', array(
            'conditions' => array('creator' => $uid, 'status !=' => STATUS_CONSIGNEES_TUAN),
            'order' => 'status desc',
        ));
        $consignees = array_filter($all_consignees, function ($v) {
            return $v['OrderConsignee']['status'] != STATUS_CONSIGNEES_TUAN_ZITI;
        });
        $total_consignee = count($consignees);
        if ($_REQUEST['action'] == 'savePromo') {
            list($specialPid, $specialAddress) = $this->ShipPromotion->find_special_address_by_id($shipPromotionId);
            if (!empty($specialAddress)) {

                $total_num_cart = $cart->count_total_num($specialPid);
                if (isset($specialAddress['least_num']) && (!$total_num_cart || ($specialAddress['least_num'] > $total_num_cart))) {
                    $flash_msg = __('错误：使用您选定的优惠地址需要购买' . $specialAddress['least_num'] . '件');
                    unset($shipPromotionId);
                } else {
                    $consignee = array();
                    $consignee['name'] = trim($_REQUEST['consignee_name']);
                    $consignee['mobilephone'] = trim($_REQUEST['consignee_mobilephone']);
                    $consignee['address'] = trim($specialAddress['address']) . ($specialAddress['need_address_remark'] ? trim($_REQUEST['consignee_address']) : '');
                    $this->Session->write('OrderConsignee', $consignee);
                    $kuaidi_consignee_exist = true;
                    $this->Session->write(self::key_balanced_ship_promotion_id(), $shipPromotionId);
                }
            } else {
                //error:
                unset($shipPromotionId);
                $flash_msg = __('输入的地址不对');
            }
        } else {
            if ($total_consignee == 0) {
                $this->Session->write('OrderConsignee', array());
            }
            $current_consignee = $this->Session->read('OrderConsignee');
            if (empty($current_consignee)) {
                $first_consignees = current($consignees);
                $current_consignee = array();
                // empty 不能检测函数，只能检测变量
                if (!empty($first_consignees)) {
                    $current_consignee = $first_consignees['OrderConsignee'];
                    $kuaidi_consignee_exist = true;
                }
                $this->Session->write('OrderConsignee', $current_consignee);
            } elseif (!empty($current_consignee['id'])) {
                $kuaidi_consignee_exist = true;
            }
        }

        $brand_ids = array_keys($cart->brandItems);
        if (!empty($brand_ids)) {
            $this->loadModel('Brand');
            $brands = $this->Brand->find('all', array('conditions' => array('id' => $brand_ids), 'fields' => array('id', 'name', 'coverimg', 'created', 'slug')));
            $brands = Hash::combine($brands, '{n}.Brand.id', '{n}.Brand');
        } else {
            $brands = array();
        }
        $this->_clear_coupons_scores();

        //TODO: 计算邮费优惠等
        $total_reduced = 0.0;

        if (!$cart->is_try) {
            $couponItem = ClassRegistry::init('CouponItem');
            $coupons_of_products = $couponItem->find_user_coupons_for_cart($uid, $cart);
        }

        $total_price = $cart->total_price();

        $this->set(compact('total_price', 'shipFee', 'coupons_of_products', 'cart', 'brands', 'flash_msg', 'total_reduced', 'product_info'));
        $this->Session->write(self::key_balanced_ship_fee(), $shipFee);
        $this->set('kuaidi_consignee_exist', $kuaidi_consignee_exist);
        $this->set('total_consignee', $total_consignee);
        $this->set('consignees', $consignees);

        $commentM = ClassRegistry::init('Comment');
        $score_could_got = $commentM->base_comment_score($total_price);
        $this->set('score_got', $score_could_got);

        $this->loadModel('User');
        $score = $this->User->get_score($uid, true);
        $could_score_money = cal_score_money($score, $total_price);
        $this->set('score_usable', $could_score_money * 100);
        $this->set('score_money', $could_score_money);
        if ($cartsDict) {
            $spec_ids = array_unique(Hash::extract($cartsDict, '{n}.specId'));
            $this->set('spec_group', search_spec($spec_ids));
            $consign_ids = array_unique(Hash::extract($cartsDict, '{n}.consignment_date'));
            $this->set('consign_dates', search_consignment_date($consign_ids));
        }
        $shipPromotions = $this->ShipPromotion->findShipPromotions($pids, $brand_ids);
        if ($shipPromotions && !empty($shipPromotions)) {
            $this->set('specialShipPromotionId', $shipPromotionId);
            $this->set('specialShipPromotion', $shipPromotions['items']);
            $this->set('limit_ship', $shipPromotions['limit_ship']);
        }
        if ($this->RequestHandler->isMobile()) {
            $this->set('is_mobile', true);
        }
        $ziti_support = false;
        if (array_key_exists(PYS_BRAND_ID, $brands)) {
            $ziti_support = true;
        }
        $ziti_consignees = array_filter($all_consignees, function ($v) {
            return $v['OrderConsignee']['status'] == STATUS_CONSIGNEES_TUAN_ZITI;
        });
        if (!empty($ziti_consignees) && $ziti_support) {
            $ziti_id = $ziti_consignees[0]['OrderConsignee']['ziti_id'];
            $ziti_consign_id = $ziti_consignees[0]['OrderConsignee']['id'];
            $this->loadModel('OfflineStore');
            $ziti_info = $this->OfflineStore->find('first', array(
                'conditions' => array('id' => $ziti_id, 'deleted' => DELETED_NO)
            ));
            if (!empty($ziti_info)) {
                $ziti_info = array('consignee' => $ziti_consignees[0]['OrderConsignee'], 'ziti' => $ziti_info['OfflineStore']);
                $this->set('ziti_info', $ziti_info);
                $this->Session->write('pickupConsignee', $ziti_consignees[0]['OrderConsignee']);
            } else {
                $this->OrderConsignee->delete($ziti_consign_id);
                unset($ziti_consignees);
                $ziti_consignees = array();
            }
        }
        $param = '';
        if ($shipPromotionId || $groupTag) {
            if ($shipPromotionId) {
                $param = $param . '?ship_promotion=' . $shipPromotionId;
                if ($groupTag) {
                    $param = $param . '&group_tag=' . $groupTag;
                }
            } else {
                if ($groupTag) {
                    $param = $param . '?group_tag=' . $groupTag;
                }
            }
        }
        $this->set('extra_param', $param);
        $this->set('ziti_exist', $this->ziti_exist($ziti_support, $ziti_consignees));
        $this->set('should_show_ziti', $this->should_show_ziti_address($ziti_support, $current_consignee, $ziti_consignees[0]['OrderConsignee']));
        $this->set('should_show_kuaidi', $this->should_show_normal_address($ziti_support, $current_consignee, $ziti_consignees[0]['OrderConsignee']));
        $this->set('ziti_support', $ziti_support);
        $this->pageTitle = __('订单确认');
        $this->set('op_cate', OP_CATE_CATEGORIES);
        $this->set('hideNav', true);
    }

    function ship_detail($orderId) {
        $uid = $this->currentUser['id'];
        $orderinfo = $this->find_my_order_byId($orderId, $uid);
        if (empty($orderinfo)) {
            $this->__message('订单不存在，或无权查看', '/');
        }
        if ($orderinfo['Order']['ship_type']) {
            $this->set('shipdetail', ShipAddress::get_ship_detail($orderinfo));
        }
        $this->set('ship_type', ShipAddress::ship_type_list());
        $this->set('order', $orderinfo);
        $this->set('hideNav', true);
    }

    /**
     * Display and options for already submitted order
     * @Param int $order_id
     * @Param string action
     */
    function detail($orderId = '', $action = '') {
        $uid = $this->currentUser['id'];
        $orderinfo = $this->find_my_order_byId($orderId, $uid);
        if (empty($orderinfo)) {
            $this->__message('订单不存在，或无权查看', '/');
        }
        //process share order order status
        if ($orderinfo['Order']['type'] == ORDER_TYPE_WESHARE_BUY || $orderinfo['Order']['type'] == ORDER_TYPE_WESHARE_BUY_ADD) {
            if($action == 'pay'){
                if($orderinfo['Order']['status']==ORDER_STATUS_WAITING_PAY){
                    if($_REQUEST['from'] == 'zhifubaopay'){
                        $this->redirect('/weshares/pay/'.$orderId. '/1');
                    }else{
                        $this->redirect('/weshares/pay/'.$orderId);
                    }
                    return;
                }
            }
            $member_id = $orderinfo['Order']['member_id'];
            $this->redirect('/weshares/view/' . $member_id);
            return;
        }

        $brandId = $orderinfo['Order']['brand_id'];
        $this->loadModel('Brand');
        $brand = $this->Brand->find('first', array(
            'conditions' => array(
                'id' => $brandId
            )
        ));
        $this->set('brand', $brand);
        $this->loadModel('Cart');

        $Carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $orderId,
                'creator' => $uid
            )));
        $product_ids = Hash::extract($Carts, '{n}.Cart.product_id');
        $expired_pids = array();
        $this->loadModel('Product');
        if ($orderinfo['Order']['type'] != ORDER_TYPE_TUAN && $orderinfo['Order']['type'] != ORDER_TYPE_TUAN_SEC) {
            $products = $this->Product->find_products_by_ids($product_ids, array('published', 'deleted'), false);
            foreach ($product_ids as $pid) {
                if (empty($products[$pid])
                    || $products[$pid]['published'] == PUBLISH_NO
                    || $products[$pid]['deleted'] == DELETED_YES
                ) {
                    $expired_pids[] = $pid;
                }
            }
        }

        $this->set('expired_pids', $expired_pids);

        $this->loadModel('TuanBuying');

        if ($orderinfo['Order']['type'] == ORDER_TYPE_TUAN) {
            $tuan_buy = $this->TuanBuying->find('first', array(
                'conditions' => array(
                    'id' => $orderinfo['Order']['member_id']
                )
            ));
            //tuan can't buy
            $tuan_expired = $tuan_buy['TuanBuying']['status'];
            if ($tuan_expired != 0) {
                $this->set('tuan_expired', true);
            }
        }

        if ($action == 'pay') {
            $this->set('paid_msg', htmlspecialchars($_GET['paid_msg']));
            $display_status = $_GET['display_status'];
            $this->set('display_status', $display_status);
        }

        $has_expired_product_type = count($expired_pids);
        $totalCents = $orderinfo['Order']['total_all_price'] * 100;
        $no_more_money = $totalCents < 1 && $totalCents >= 0;
        $status = $orderinfo['Order']['status'];

        if ($orderinfo['Order']['status'] == ORDER_STATUS_WAITING_PAY) {
            $try_id = $orderinfo['Order']['try_id'];
            $afford = true;
            if ($try_id > 0) {
                list($afford, $user_left, $total_left) = afford_product_try($try_id, $uid);
                $total_items = array_sum(Hash::extract($Carts, '{n}.Cart.num'));
                if ($total_left == 0 || ($total_left > 0 && $total_left < $total_items)) {
                    $this->set('has_sold_out', true);
                    $afford = false;
                } else if ($user_left == 0 || ($user_left > 0 && $user_left < $total_items)) {
                    $this->set('has_reach_limit', true);
                    $afford = false;
                }
            }

            if ($afford && $has_expired_product_type == 0 && $no_more_money && $action == 'pay_direct') {
                if ($orderinfo['Order']['status'] == ORDER_STATUS_WAITING_PAY) {
                    $this->Order->id = $orderinfo['Order']['id'];
                    if ($this->Order->set_order_to_paid($orderId, $orderinfo['Order']['try_id'], $uid, $orderinfo['Order']['type'], $orderinfo['Order']['member_id'])) {
                        $this->Weixin->notifyPaidDone($orderinfo);
                        $_GET['msg'] = 'ok';
                        $action = 'pay';
                    };
                    $orderinfo = $this->find_my_order_byId($orderId, $uid);
                    $status = $orderinfo['Order']['status'];
                }
            }

            $this->loadModel('ConsignmentDate');
            $consignment_date_available = true;
            $tuan_buy_available = true;
            foreach ($Carts as $cart) {
                $consignment_id = $cart['Cart']['consignment_date'];
                $tuan_buy_id = $cart['Cart']['tuan_buy_id'];
                if ($tuan_buy_id) {
                    $tuan_buying_item = $this->TuanBuying->find('first', array('conditions' => array('id' => $tuan_buy_id)));
                    //tuan can't buy
                    $tuan_expired = $tuan_buying_item['TuanBuying']['status'];
                    if ($tuan_expired != 0) {
                        $this->set('tuan_expired', true);
                        $tuan_buy_available = false;
                        break;
                    }
                }
//                if($consignment_id){
//                    $consignment_date = $this->ConsignmentDate->find('first', array(
//                        'conditions' => array('id'=>$consignment_id )
//                    ));
//                    if($consignment_date['ConsignmentDate']['published'] == PUBLISH_NO){
//                        $consignment_date_available = false;
//                        $this->set('consignment_date_not_available',true);
//                        break;
//                    }
//                }
            }

//            $this->set('show_pay', ($orderinfo['Order']['type'] == ORDER_TYPE_DEF || $orderinfo['Order']['type']==ORDER_TYPE_TUAN || $orderinfo['Order']['type']==ORDER_TYPE_TUAN_SEC)
//                && $has_expired_product_type == 0
//                && (empty($tuan_expired)||$tuan_expired ==0)
//                && $orderinfo['Order']['status'] == ORDER_STATUS_WAITING_PAY
//                && ($display_status != PAID_DISPLAY_PENDING && $display_status != PAID_DISPLAY_SUCCESS)
//                && $consignment_date_available);
            $this->set('show_pay', ($orderinfo['Order']['type'] == ORDER_TYPE_DEF || $orderinfo['Order']['type'] == ORDER_TYPE_TUAN || $orderinfo['Order']['type'] == ORDER_TYPE_TUAN_SEC)
                && $afford
                && $tuan_buy_available
                && $has_expired_product_type == 0
                && (empty($tuan_expired) || $tuan_expired == 0)
                && $orderinfo['Order']['status'] == ORDER_STATUS_WAITING_PAY
                && ($display_status != PAID_DISPLAY_PENDING && $display_status != PAID_DISPLAY_SUCCESS)
                && $consignment_date_available);
        }
        if ($action == 'paid') {
            $this->log("paid done: $orderId, msg:" . $_GET['msg']);
            //:orders/detail/1118/paid?tradeNo=wxca78-1118-1414580077&msg=ok
            //TODO: check status, if status is not paid, tell user to checking; notify administrators to check

        }
        if ($action == 'pay' || $action == 'paid') {
            if ($_GET['msg'] == 'ok') {
                if ($uid && $this->is_weixin()) {
                    $this->loadModel('WxOauth');
                    if (!$this->WxOauth->is_subscribe_wx_service($uid)) {
                        $this->set('need_attentions', true);
                    }
                }
            }
        } else {
            if ($orderinfo['Order']['status'] == ORDER_STATUS_PAID || $orderinfo['Order']['status'] == ORDER_STATUS_SHIPPED) {
                if ($uid && $this->is_weixin()) {
                    $this->loadModel('WxOauth');
                    if (!$this->WxOauth->is_subscribe_wx_service($uid)) {
                        $this->set('remind_attentions', true);
                    }
                }
            }
        }

        //TODO: Handle product try orders!
        $shareOffer = ClassRegistry::init('ShareOffer');
        $toShare = $shareOffer->query_gen_offer($orderinfo, $this->currentUser['id']);

        $canComment = $this->can_comment($status);
        $this->set(compact('toShare', 'canComment', 'no_more_money', 'order_id', 'order', 'has_expired_product_type', 'expired_pids'));
        $this->set('ship_type', ShipAddress::ship_type_list());
        $this->set('order', $orderinfo);
        $this->set('Carts', $Carts);
        $this->set('products', $products);
        $this->set('is_try', $orderinfo['Order']['try_id'] > 0);
        if ($orderinfo['Order']['ship_type']) {
            $this->set('shipdetail', ShipAddress::get_ship_detail($orderinfo));
        }
        $this->set('ship_type', ShipAddress::ship_type_list());
        $this->set('isMobile', $this->RequestHandler->isMobile());
        $this->set('hideNav', true);
    }

    public function apply_coupon_code() {

        $this->autoRender = false;

        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }

        $code = trim($_REQUEST['code']);

        $this->loadModel('CouponItem');
        $shipPromotionId = intval($_REQUEST['ship_promotion']);
        $specifiedPids = $this->specified_balance_pids();
        $cartsByPid = $this->Buying->cartsByIds($specifiedPids, $uid, $this->Session->id());
        list($cart, $shipFee) = $this->Buying->applyPromoToCart($cartsByPid, $shipPromotionId, $uid);
        $shipFee = $this->get_custom_ship_fee($shipFee);
        $applied_coupon_code = $this->_applied_couon_code();
        $success = false;
        $error = '';
        if (!empty($code)) {
            if ($code == 'pengyoushuo2014') {
                if ($applied_coupon_code != $code) {
                    $usedCnt = $this->Order->used_code_cnt($uid, $code);
                    if ($usedCnt <= 0) {
                        $applying_code_ins = null;
//            $couponCodeItems = $this->CouponItem->find_valid_coupon_code_items($applied_coupon_code);
                        //TODO: check more coupon items validation
                        //TODO: 补充校验信息
//            if (!empty($couponCodeItems)) {
//                foreach($couponCodeItems as $code_ins) {
//                    if (array_search($code_ins['CouponCodeItems']['pid'], $balancingPids) !== false) {
                        $applying_code_ins = $code; // $code_ins;
                        $this->_save_applied_coupon_code($code);
//                        break;
//                    }
                        $success = true;
                    } else {
                        $error = '优惠码已失效';
                    }
//                }
//            }
                } else {
                    $error = '您已经使用过这个优惠码，无需重复';
                }
            } else {
                $error = '您输入的优惠码不存在';
            }
        } else {
            $error = '优惠码为空';
        }

        $total_reduced = $this->_cal_total_reduced($uid);
        $resp['reduced'] = 500 / 100;
        $resp['total_reduced'] = $total_reduced / 100;
        $resp['total_price'] = $cart->total_price() - $total_reduced / 100 + $shipFee;
        $resp['success'] = $success;
        $resp['error'] = $error;
        echo json_encode($resp);
    }

    public function apply_coupon() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('changed' => false, 'reason' => 'not_login'));
            return;
        }
        $shipPromotionId = intval($_REQUEST['ship_promotion']);
        $coupon_item_id = $_POST['coupon_item_id'];
        $brand_id = $_POST['brand_id'];
        $applying = $_POST['action'] == 'apply';
        $resp = $this->process_apply_coupon($uid, $shipPromotionId, $coupon_item_id, $brand_id, $applying);
        echo json_encode($resp);
    }

    public function process_apply_coupon($uid, $shipPromotionId, $coupon_item_id, $brand_id, $applying) {
        $this->Session->write(self::key_balanced_promotion_code(), '');
        $specifiedCartIds = $this->specified_balance_pids();
        $cartsByIds = $this->Buying->cartsByIds($specifiedCartIds, $uid, $this->Session->id());
        list($cart, $shipFee) = $this->Buying->applyPromoToCart($cartsByIds, $shipPromotionId, $uid);
        $shipFee = $this->get_custom_ship_fee($shipFee);
        $this->loadModel('CouponItem');
        list($changed, $reason) = $this->_try_apply_coupon_item($brand_id, $applying, $coupon_item_id, $uid, $cart);
        $resp = array('changed' => $changed);
        if ($changed) {
            $total_reduced = $this->_cal_total_reduced($uid);
            $resp['total_reduced'] = $total_reduced / 100;
            $resp['total_price'] = $cart->total_price() - $total_reduced / 100 + $shipFee;
        }
        if ($reason) {
            $resp['reason'] = $reason;
        }
        return $resp;
    }

    public function apply_promotion_code($code) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($code)) {
            echo json_encode(array('success' => false, 'reason' => 'code_error'));
        }
        if (empty($uid)) {
            echo json_encode(array('changed' => false, 'reason' => 'not_login'));
            return;
        }
        $specifiedCartIds = $this->specified_balance_pids();
        $cartsByIds = $this->Buying->cartsByIds($specifiedCartIds, $uid, $this->Session->id());
        if (empty($cartsByIds)) {
            echo json_encode(array('success' => false, 'reason' => 'cart_empty'));
            return;
        }
        $this->loadModel('PromotionCode');
        //has use
        $usedCode = $this->PromotionCode->find('first', array('conditions' => array('user_id' => $uid, 'available' => 0)));
        if (!empty($usedCode)) {
            echo json_encode(array('success' => false, 'reason' => 'has_used'));
            return;
        }
        $reducePrice = 0;
        foreach ($cartsByIds as $cartId => $cart) {
            $pid = $cart['product_id'];
            $cart_price = $cart['price'];
            $promotion_code = $this->PromotionCode->find('first', array('conditions' => array('available' => 1, 'code' => $code, 'product_id' => $pid)));
            if (!empty($promotion_code)) {
                $price = $promotion_code['PromotionCode']['price'];
                $reducePrice = $reducePrice + ($cart_price - $price);
                break;
            }
        }
        if ($reducePrice > 0) {
            //clear score and coupon
            $this->Session->write(self::key_balanced_scores(), '');
            $this->Session->write(self::key_balanced_conpon_global(), '[]');
            $this->Session->write(self::key_balanced_conpons(), '[]');
            $this->Session->write(self::key_balanced_promotion_code(), $code);
        }
        echo json_encode(array('success' => true, 'reducePrice' => $reducePrice));
        return;
    }

    public function apply_ship_change() {
        $this->autoRender = false;
        $resp = array();
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('changed' => false, 'reason' => 'not_login'));
            return;
        }
        $ziti = ("true" == $_REQUEST['ziti']);
        $shipPromotionId = intval($_REQUEST['ship_promotion']);
        $specifiedCartIds = $this->specified_balance_pids();
        $cartsByIds = $this->Buying->cartsByIds($specifiedCartIds, $uid, $this->Session->id());
        list($cart, $shipFee) = $this->Buying->applyPromoToCart($cartsByIds, $shipPromotionId, $uid);
        $this->clear_score_and_coupon();
        if ($ziti) {
            $this->Session->write(self::key_balanced_ship_type(), ZITI_TAG);
        } else {
            $this->Session->write(self::key_balanced_ship_type(), '');
        }
        $total_reduced = $this->_cal_total_reduced($uid);
        $total_price = $cart->total_price() - $total_reduced / 100 + $shipFee;
        $resp['total_reduced'] = $total_reduced / 100;
        $resp['total_price'] = $total_price;
        $this->loadModel('User');
        $score = $this->User->get_score($uid, true);
        $could_score_money = cal_score_money($score, $total_price);
        $resp['score_money'] = $could_score_money;
        echo json_encode($resp);
    }

    public function apply_score() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('changed' => false, 'reason' => 'not_login'));
            return;
        }

        $use = ("true" == $_REQUEST['use']);
        $shipPromotionId = intval($_REQUEST['ship_promotion']);
        $score_num = intval($_REQUEST['score']);

        $specifiedCartIds = $this->specified_balance_pids();
        $cartsByIds = $this->Buying->cartsByIds($specifiedCartIds, $uid, $this->Session->id());
        list($cart, $shipFee) = $this->Buying->applyPromoToCart($cartsByIds, $shipPromotionId, $uid);

        $this->Session->write(self::key_balanced_promotion_code(), '');
        $this->Session->write(self::key_balanced_scores(), '');
        $total_reduced = $this->_cal_total_reduced($uid);
        $shipFee = $this->get_custom_ship_fee($shipFee);
        $total_price = $cart->total_price() - $total_reduced / 100 + $shipFee;
        $this->loadModel('User');
        $score = $this->User->get_score($uid, true);
        $could_score_money = cal_score_money($score, $total_price);
        $could_use_score = $could_score_money * 100;

        if ($use) {
            if ($score_num > $could_use_score) {
                $score_num = $could_use_score;
            }
            $this->Session->write(self::key_balanced_scores(), $score_num);
            $total_reduced = $this->_cal_total_reduced($uid);
//            $total_price = $cart->total_price() - $total_reduced / 100 + $shipFee;
        } else {
            $this->Session->write(self::key_balanced_scores(), '');
        }

        $resp['success'] = true;
        $resp['score_usable'] = $could_use_score;
        $resp['score_money'] = $could_score_money;
        $used_score = $this->Session->read(self::key_balanced_scores());
        $resp['score_used'] = !empty($used_score);

        $resp['total_reduced'] = $total_reduced / 100;
        $resp['total_price'] = $total_price;
        echo json_encode($resp);
    }


    function mine() {
        $uid = $this->currentUser['id'];

        list($orders, $order_carts, $mappedBrands) = $this->Order->get_user_orders($uid);

        $counts = array();
        foreach ($order_carts as $order_id => $c) {
            $nums = Hash::extract($c, '{n}.Cart.num');

            $total = 0;
            foreach ($nums as $num) $total += $num;

            $counts[$order_id] += $total;
        }
        if ($this->RequestHandler->isMobile()) {
            $this->set('is_mobile', true);
        }
        $this->set('brands', $mappedBrands);
        $this->set('orders', $orders);
        $this->set('order_carts', $order_carts);
        $this->set('ship_type', ShipAddress::ship_type_list());
        $this->set('counts', $counts);
        $this->set('hideNav', true);
    }


    function find_order_status($order_id = '') {

        $this->autoRender = false;
        $order = $this->Order->find('first', array('conditions' => array('id' => $order_id), 'fields' => array('status')));
        $order_status = $order['Order']['status'];
//        $this->log('order_status'.json_encode($order_status));
        echo json_encode($order_status);
    }

    function business($creator = 0) {
        $order_Id = $_REQUEST['order-id'];
        $this->__business_orders($creator, array(), $order_Id);
        $this->set('creator', $creator);
    }

    function tobe_shipped_orders($creator = 0) {
        $this->__business_orders($creator, array(ORDER_STATUS_PAID));
    }


    function business_export($creator = 0) {
        $this->business($creator);
    }

    function tobe_shipped_export($creator = 0) {
        $this->tobe_shipped_orders($creator);
    }

    function confirm_receive() {
        $this->Buying->confirm_receive($this->currentUser['id'], $_REQUEST['order_id']);
    }

    function confirm_undo() {
        $this->Buying->confirm_undo($this->currentUser['id'], $_REQUEST['order_id']);
    }

    function confirm_remove() {
        $this->Buying->confirm_remove($this->currentUser['id'], $_REQUEST['order_id']);
    }

    public function test_add_sharedOffers($uid, $sharedOfferId, $toShareNum) {
        if ($this->is_admin($this->currentUser['id'])) {
            $this->autoRender = false;
            $so = ClassRegistry::init('ShareOffer');
            $added = $so->add_shared_slices($uid, $sharedOfferId, $toShareNum);
            echo "test_add_sharedOffers $uid $sharedOfferId $toShareNum: return:" . json_encode($added);
        }
    }

    public function test_notify_paid_done($order_id) {
        if ($this->is_admin($this->currentUser['id'])) {
            $this->autoRender = false;
            $this->loadModel('Order');
            $o = $this->Order->findById($order_id);
            $this->Weixin->notifyPaidDone($o);
        }
    }

    public function test_notify_users($start_got, $uid = 0) {
        if ($this->is_admin($this->currentUser['id'])) {
            $this->autoRender = false;

            if ($uid) {
                $this->Weixin->send_mihoutao_game_message($uid);
            } else {
                $this->loadModel('Order');
                $o = $this->Order->query("select distinct uid, got from chengzi_uid_cnt where got <=" . intval($start_got) . " order by got desc limit 6000 ");
                $last_got = $start_got;
                if (!empty($o)) {
                    foreach ($o as $value) {
                        $uid = $value['chengzi_uid_cnt']['uid'];
                        $this->Weixin->send_mihoutao_game_message($uid);
                        $last_got = $value['chengzi_uid_cnt']['got'];
                        $this->log('notify_users: got=' . $last_got . ", uid=" . $uid);
                    }
                }
            }
            echo "last got:" . $last_got;
        }
    }

    public function test_create_qrcode($sceneId) {
        $this->autoRender = false;
        try {
            if ($this->is_admin($this->currentUser['id'])) {
                $this->loadModel('WxOauth');
                $o = $this->WxOauth->create_qrcode_by_sceneid($sceneId);
                $log = "test_create_qrcode $sceneId:" . $o . ", in json:" . json_encode($o);
                $this->log($log);
                echo $log;
            }
        } catch (Exception $e) {
            echo "exception: $e";
        }
    }


    function set_mark_order() {
        $this->autoRender = false;
        $order_id = $_REQUEST['order_id'];
        $mark_date = $_REQUEST['mark_date'];
        $mark_tip = $_REQUEST['mark_tip'];
        $order_info = $this->Order->find('first', array(
            'conditions' => array('id' => $order_id),
        ));
        if (empty($order_info)) {
            echo json_encode(array('order_id' => $order_id, 'msg' => '您不具备此订单的修改权限，请联系管理员。'));
            exit;
        }
        $data = array();
        $data['Order']['mark_ship_date'] = $mark_date;
        $data['Order']['ship_mark'] = $mark_tip;
        $this->Order->id = $order_id;
        if ($this->Order->save($data)) {
            echo json_encode(array('success' => true, 'message' => '订单标记成功'));
        } else {
            echo json_encode(array('success' => false, 'message' => '订单标记失败'));
        }
    }

    /**
     * 商家设置订单的状态
     */
    function set_status($creator = 0) {
        $order_id = $_REQUEST['order_id'];
        $status = $_REQUEST['status'];

        $currentUid = $this->currentUser['id'];
        $is_admin = $this->is_admin($currentUid);

        if (empty($order_id) || !isset($status)) {
            echo json_encode(array('order_id' => $order_id, 'msg' => '参数错误'));
            exit;
        }

        $order_info = $this->Order->find('first', array(
            'conditions' => array('id' => $order_id),
        ));

        if (empty($order_info)) {
            echo json_encode(array('order_id' => $order_id, 'msg' => '您不具备此订单的修改权限，请联系管理员。'));
            exit;
        }

        $this->loadModel('Brand');
        $brand = $this->Brand->findById($order_info['Order']['brand_id']);
        $is_brand_admin = !empty($brand) && $brand['Brand']['creator'] == $currentUid;

        $orig_status = $order_info['Order']['status'];
        if ($status == ORDER_STATUS_PAID) {
            if (!$is_admin) {
                echo json_encode(array('order_id' => $order_id, 'msg' => '您没有权限确认已支付'));
                exit;
            }
            if ($orig_status != ORDER_STATUS_WAITING_PAY) {
                echo json_encode(array('order_id' => $order_id, 'msg' => '订单状态不对'));
                exit;
            }

            $this->Order->updateAll(array('status' => $status, 'lastupdator' => $currentUid), array('id' => $order_id, 'status' => ORDER_STATUS_WAITING_PAY));
            $this->loadModel('Cart');
            $this->Cart->updateAll(array('status' => $status), array('order_id' => $order_id, 'status' => ORDER_STATUS_WAITING_PAY));

            echo json_encode(array('order_id' => $order_id, 'msg' => '订单已支付'));
            exit;
        } else if ($status == ORDER_STATUS_CANCEL) {
            $owner = $order_info['Order']['creator'];
            if ($owner != $currentUid) {
                echo json_encode(array('order_id' => $order_id, 'msg' => '您没有权限取消此订单'));
                exit;
            }
            if ($orig_status != ORDER_STATUS_WAITING_PAY) {
                echo json_encode(array('order_id' => $order_id, 'msg' => '该订单状态已不能取消'));
                exit;
            }

            $this->Order->cancelWaitingPayOrder($currentUid, $order_id, $owner);
            echo json_encode(array('order_id' => $order_id, 'msg' => '订单已取消'));
            exit;
        } else if ($status == ORDER_STATUS_SHIPPED) {

            if (!$is_brand_admin && !$is_admin) {
                echo json_encode(array('order_id' => $order_id, 'msg' => '您没有权限修改此订单'));
                exit;
            }

            if ($orig_status != ORDER_STATUS_PAID && $orig_status != ORDER_STATUS_SHIPPED) {
                echo json_encode(array('order_id' => $order_id, 'msg' => '您只能将此订单设为已发货'));
                exit;
            }

            $ship_code = $_REQUEST['ship_code'];
            $ship_type = $_REQUEST['ship_type'];
            $this->Order->updateAll(array('status' => $status, 'ship_code' => "'" . addslashes($ship_code) . "'", 'ship_type' => $ship_type,
                'lastupdator' => $currentUid), array('id' => $order_id, 'status' => $orig_status));
            $this->loadModel('Cart');
            $this->Cart->updateAll(array('status' => $status), array('order_id' => $order_id, 'status' => $orig_status));

            //add weixin message
            $this->loadModel('Oauthbind');
            $user_weixin = $this->Oauthbind->findWxServiceBindByUid($order_info['Order']['creator']);
            if ($user_weixin != false) {
                $good = $this->get_order_good_info($order_id);
                $this->log("good info:" . $good['good_info'] . $good['good_number']);
                $ship_type_list = ShipAddress::ship_type_list();
                $this->Weixin->send_order_shipped_message($user_weixin['oauth_openid'], $ship_type,
                    $ship_type_list[$ship_type], $ship_code, $good['good_info'], $good['good_number'], $order_id);
            }
            $good_info = $this->get_order_good_info($order_id);
            $good = $good_info['good_info'];
//            $good = substr($good,0,strlen($good)-2);
            $mobile_phone1 = $order_info['Order']['consignee_mobilephone'];
            $this->loadModel('User');
            $user_info = $this->User->find('first', array('conditions' => array('id' => $order_info['Order']['creator']), 'fields' => array('mobilephone')));
            $mobile_phone2 = $order_info['Order']['consignee_mobilephone'] == $user_info['User']['mobilephone'] ? null : $user_info['User']['mobilephone'];
            $brand_name = $brand['Brand']['name'];
            $msg = '您在[' . $brand_name . ']购买的[' . $good . ']已经发货，请关注微信pyshuo2014追踪物流信息';
            $this->log('mobile_phone2' . json_encode($mobile_phone1) . json_encode($mobile_phone2));
            message_send($msg, $mobile_phone1);
            message_send($msg, $mobile_phone2);
            echo json_encode(array('order_id' => $order_id, 'msg' => '订单状态已更新为“已发货”'));
            exit;
        } else if ($status == ORDER_STATUS_WAITING_PAY) {
            if (!$is_brand_admin && !$is_admin) {
                echo json_encode(array('order_id' => $order_id, 'msg' => '您没有权限修改此订单'));
                exit;
            } else {
                $order_price = floatval($_REQUEST['price']);
                $original_price = $order_info['Order']['total_all_price'];
                if ($order_price >= 0) {
                    if ($this->Order->updateAll(array('lastupdator' => $currentUid, 'total_all_price' => $order_price), array('id' => $order_id))) {
                        $this->loadModel('OrderEdit');
                        $data_log = array('order_id' => $order_id, 'field_name' => 'total_all_price', 'operator' => $currentUid,
                            'original_value' => $original_price, 'modified_value' => $order_price);
                        $this->OrderEdit->save($data_log);
                        $msg = '修改成功';
                        echo json_encode(array('order_id' => $order_id, 'modify_price' => $order_price, 'msg' => $msg));
                        exit;
                    } else {
                        $msg = '修改未成功，请重试';
                        echo json_encode(array('order_id' => $order_id, 'modify_price' => "不变", 'msg' => $msg));
                        exit;
                    }
                }
            }
        } else {
            echo json_encode(array('order_id' => $order_id, 'msg' => '不能修改订单状态了'));
            exit;
        }
    }

    function get_order_good_info($order_id) {
        $info = '';
        $number = 0;
        $this->loadModel('Cart');
        $carts = $this->Cart->find('all', array(
            'conditions' => array('order_id' => $order_id)));
        foreach ($carts as $cart) {
            $info = $info . $cart['Cart']['name'] . ':' . $cart['Cart']['num'] . '件、';
            $number += $cart['Cart']['num'];
        }

        $info = substr($info, 0, strlen($info) - 2);
        return array("good_info" => $info, "good_number" => $number);
    }

    function _calculateTotalPrice($carts = array()) {
        $total_price = 0.0;
        foreach ($carts as $cart) {
            $total_price += $cart['Cart']['price'] * $cart['Cart']['num'];
        }
        return $total_price;
    }

    /**
     * 编辑表单的保存，获取收件人信息文本内容（非表单形式）
     */
    function info_consignee() {
        //$this->autoRender = false;
        if (!empty($this->data)) {
            $this->loadModel('OrderConsignee');
            $this->data['OrderConsignee']['creator'] = $this->currentUser['id'];
            $consignee = $this->OrderConsignee->find('first', array(
                    'conditions' => array(
                        'creator' => $this->currentUser['id'],
                        'name' => $this->data['OrderConsignee']['name'],
                        'address' => $this->data['OrderConsignee']['address'],
                        'area' => $this->data['OrderConsignee']['area'],
                        'mobilephone' => $this->data['OrderConsignee']['mobilephone'],
                    ))
            );
            if ($this->data['OrderConsignee']['edit_type'] == 'select') {
                $consignee = $this->OrderConsignee->find('first', array(
                        'conditions' => array(
                            'id' => $this->data['OrderConsignee']['id'],
                        ))
                );
                $this->Session->write('OrderConsignee', $consignee['OrderConsignee']);
            } elseif (empty($consignee)) {
                if (!$this->OrderConsignee->save($this->data)) {
                    echo json_encode($this->{$this->modelClass}->validationErrors);
                    return;
                }
                if (empty($this->data['OrderConsignee']['id'])) {
                    $this->data['OrderConsignee']['id'] = $this->OrderConsignee->getLastInsertID();
                }
                $this->Session->write('OrderConsignee', $this->data['OrderConsignee']);
            } else {
                $this->Session->write('OrderConsignee', $consignee['OrderConsignee']);
                //echo json_encode(array('error' => __('Already have this address. If your still want to update this to Commonly used address,please delete it in Commonly used address at first.')));
                //return;
            }

            $successinfo = array(
                'success' => __('Add success'),
                'tasks' => array(array(
                    'dotype' => 'html',
                    'selector' => '#part_consignee',
                    'content' => $this->renderElement('order_consignee')
                ))
            );
            echo json_encode($successinfo);
            exit;
        } else {
            echo $this->renderElement('order_consignee');
            exit;
        }
    }

    function edit_consignee() {
        // 常用地址列表，及收件人信息编辑表单
        $this->loadModel('OrderConsignee');
        $consignees = $this->OrderConsignee->find('all', array(
            'conditions' => array('creator' => $this->currentUser['id'], 'not' => array('status' => array(STATUS_CONSIGNEES_TUAN, STATUS_CONSIGNEES_TUAN_ZITI))),
            'order' => 'status desc',
        ));
        $total_consignee = count($consignees);
        $this->set('total_consignee', $total_consignee);
        $this->set('consignees', $consignees);
        if (count($consignees) < 10) {
            $this->Session->write('OrderConsignee.save_address', 1);
        }
    }

    /**
     * 设为默认地址
     * @param int $id
     */
    function default_consignee($id) {
        $this->autoRender = false;
        $this->loadModel('OrderConsignee');
        $consignees = $this->OrderConsignee->updateAll(array('status' => 0), array('creator' => $this->currentUser['id']));

        $consignees = $this->OrderConsignee->updateAll(array('status' => 1), array('creator' => $this->currentUser['id'], 'id' => $id));
        $successinfo = array('id' => $id);
        echo json_encode($successinfo);
        exit;
    }

    /**
     * 删除常用地址
     * @param int $id
     */
    function delete_consignee($id) {
        $this->autoRender = false;
        $this->loadModel('OrderConsignee');
        $consignees = $this->OrderConsignee->deleteAll(array('creator' => $this->currentUser['id'], 'id' => $id));
        $successinfo = array('id' => $id);
        echo json_encode($successinfo);
        exit;
    }

    /**
     * 加载常用地址信息
     * @param unknown_type $id
     */
    function load_consignee($id) {
        $this->autoRender = false;
        $this->loadModel('OrderConsignee');
        $consignee = $this->OrderConsignee->find('first',
            array(
                'conditions' => array('id' => $id, 'creator' => $this->currentUser['id']),
                'fields' => array('name', 'address', 'mobilephone', 'telephone', 'email', 'postcode', 'area', 'province_id', 'city_id', 'county_id', 'town_id')
            ));
        echo json_encode($consignee['OrderConsignee']);
        exit;
    }
    /*××××××××××××××××××收件人信息结束××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××*/


    /*××××××××××××××××××发票信息开始××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××××*/
    function edit_invoice() {
        // 常用地址列表，及收件人信息编辑表单
        $this->loadModel('OrderInvoice');
        $invoices = $this->OrderInvoice->find('all', array('conditions' => array('creator' => $this->currentUser['id'])));
        $this->set('invoices', $invoices);
    }

    function load_invoice($id) {
        $this->autoRender = false;
        $this->loadModel('OrderInvoice');
        $consignee = $this->OrderInvoice->find('first',
            array(
                'conditions' => array('id' => $id, 'creator' => $this->currentUser['id']),
            ));
        echo json_encode($consignee['OrderInvoice']);
        exit;
    }

    /**
     * 编辑表单的保存，获取收件人信息文本内容（非表单形式）
     */
    function info_invoice() {
        $this->autoRender = false;
        if (!empty($this->data)) {
            $this->loadModel('OrderInvoice');
            $this->data['OrderInvoice']['creator'] = $this->currentUser['id'];
            $this->Session->write('OrderInvoice', $this->data['OrderInvoice']);
            if ($this->data['OrderInvoice']['save_invoice']) {
                $invoice = $this->OrderInvoice->find('first', array(
                        'conditions' => array(
                            'creator' => $this->currentUser['id'],
                            'name' => $this->data['OrderInvoice']['name'],
                            'content' => $this->data['OrderInvoice']['content'],
                        ))
                );
                if (!empty($invoice)) {
                    $this->data['OrderInvoice']['id'] = $invoice['OrderInvoice']['id'];
                }
                if (!$this->OrderInvoice->save($this->data)) {
                    echo json_encode($this->{$this->modelClass}->validationErrors);
                    return;
                }
            }
            $successinfo = array('success' => __('Add success'),
                'tasks' => array(array(
                    'dotype' => 'html',
                    'selector' => '#part_invoice',
                    'content' => $this->renderElement('order_invoice')
                ))
            );
            echo json_encode($successinfo);
            exit;
        }
        echo $this->renderElement('order_invoice');
        exit;
    }

    function delete_invoice($id) {
        $this->autoRender = false;
        $this->loadModel('OrderInvoice');
        $consignees = $this->OrderInvoice->deleteAll(array(
            'creator' => $this->currentUser['id'], 'id' => $id));
        $successinfo = array('id' => $id);
        echo json_encode($successinfo);
        exit;
    }

    /***** 备注信息 ***********/
    function edit_remark() {
        $this->Session->write('Order.remark', $this->data['Order']['remark']);
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
     * @param $brand_id
     * @param $uid
     * @param $order_id
     * @param $order_results
     * @throws MissingModelException
     */
    public function apply_coupons_to_order($brand_id, $uid, $order_id, $order_results) {
        //TODO：检查是否可以应用这些券的合法性
        $used_coupons_str = $this->Session->read(self::key_balanced_conpons());
        $total_reduced_cent = 0;
        $total_reduce_coupons = array();
        $total_price = 0;
        foreach ($order_results as $val) {
            $total_price += $val[1];
        }
        if ($used_coupons_str) {
            $used_coupons = json_decode($used_coupons_str, true);
            if (!empty($used_coupons) && is_array($used_coupons)) {
                $used_coupons_of_brand = $used_coupons[$brand_id];
            }

            $global_coupons = $used_coupons[0];
            if (!empty($global_coupons)) {
                $this->loadModel('CouponItem');
                $g_items = $this->CouponItem->find_my_valid_coupon_items($uid, $global_coupons);
                if (!empty($g_items)) {
                    foreach ($g_items as $item) {
                        if ($item['Coupon']['type'] == COUPON_TYPE_TYPE_MAN_JIAN) {
                            $curr_order_price_cent = ($order_results[$brand_id][1]) * 100;
                            $total_reduced_cent = $total_reduced_cent + ($item['Coupon']['reduced_price'] * $curr_order_price_cent / ($total_price * 100));
                            $total_reduce_coupons[] = $item['CouponItem']['id'];
                        }
                    }
                }
            }
        }


        $coupon_total = 0;
        $applied_coupons = array();
        if (!empty($used_coupons_of_brand) && is_array($used_coupons_of_brand)) {
            $this->loadModel('CouponItem');
            if ($this->CouponItem->apply_coupons_to_order($uid, $order_id, $used_coupons_of_brand)) {
                $computed = $this->CouponItem->compute_coupons_for_order($uid, $order_id);
                $applied_coupons = $computed['applied'];
                $coupon_total = $computed['reduced'];
            }
        }

        if (!empty($applied_coupons) || !empty($total_reduce_coupons)) {
            $reduced = $coupon_total / 100;
            $global_reduced = $total_reduced_cent / 100;
            $toUpdate = array('applied_coupons' => '\'' . implode(',', $applied_coupons) . '\'',
                'coupon_total' => $coupon_total,
                'global_coupon_total' => $total_reduced_cent,
                'applied_global_coupon' => '\'' . implode(',', $total_reduce_coupons) . '\'',
                'total_all_price' => 'if(total_all_price - ' . $reduced . ' - ' . $global_reduced . ' < 0, 0, total_all_price - ' . $reduced . ' - ' . $global_reduced . ')');
            if ($this->Order->updateAll($toUpdate, array('id' => $order_id, 'status' => ORDER_STATUS_WAITING_PAY))) {
                //FIXME: support more orders
                if (!empty($global_coupons)) {
                    $this->CouponItem->apply_coupons_to_order($uid, $order_id, $global_coupons);
                }
            }
        }
        if (count($used_coupons_of_brand) != count($applied_coupons) || (is_array($used_coupons_of_brand) && array_diff($used_coupons_of_brand, $applied_coupons))) {
            $this->log("not expected coupon size: order_id=$order_id, original:" . json_encode($used_coupons_of_brand) . ", final:" . json_encode($applied_coupons));
        }
    }

    /**
     * @param $uid
     * @param $order_id
     */
    public function apply_coupon_code_to_order($uid, $order_id) {
        //TODO: 检查是否可以应用这些券码的合法性
        //TODO: 防止重复用券
        $code = $this->_applied_couon_code();
        $code_reduce = 500;
        if ($code == 'pengyoushuo2014') {

            $usedCnt = $this->Order->used_code_cnt($uid, $code);

            if ($usedCnt <= 0) {
                $coupon_total = $code_reduce;
                $reduced = $coupon_total / 100;
                $toUpdate = array('applied_code' => '\'' . $code . '\'',
                    'coupon_total' => 'coupon_total + 500',
                    'total_all_price' => 'if(total_all_price - ' . $reduced . ' < 0, 0, total_all_price - ' . $reduced . ')');
                $this->Order->updateAll($toUpdate, array('id' => $order_id, 'status' => ORDER_STATUS_WAITING_PAY));
            }
        }
    }

    /**
     * @param $creator
     * @param array $onlyStatus if not empty, only the specified status will be kept
     */
    protected function __business_orders($creator, $onlyStatus = array(), $order_Id = null) {
        $creator = $this->authAndGetCreator($creator);
        $this->loadModel('Brand');
        $brands = $this->Brand->find('list', array('conditions' => array(
            'creator' => $creator,
        )));

        if (!empty($brands)) {
            $brand_ids = array_keys($brands);
            $this->set('is_business', true);
        } else {
            $this->__message('只有合作商家才能查看商家订单，正在为您转向个人订单', '/orders/mine');
            return;
        }
        $cond = array('brand_id' => $brand_ids,
            'type' => array(ORDER_TYPE_DEF, ORDER_TYPE_GROUP_FILL, ORDER_TYPE_TUAN),
            'NOT' => array(
                'status' => array(ORDER_STATUS_CANCEL)
            ));
        if (!empty($onlyStatus)) {
            $cond['status'] = $onlyStatus;
        }
        if (!empty($order_Id)) {
            $cond['id'] = $order_Id;
        }
        $this->Paginator->settings = array('limit' => 100,
            'conditions' => $cond,
            'order' => 'Order.id desc'
        );
        $orders = $this->Paginator->paginate();
        $ids = array();
        foreach ($orders as $o) {
            $ids[] = $o['Order']['id'];
        }
        $order_carts = array();
        if (!empty($ids)) {
            $this->loadModel('Cart');
            $Carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $ids,
                )));
            foreach ($Carts as $c) {
                $order_id = $c['Cart']['order_id'];
                if (!isset($order_carts[$order_id])) {
                    $order_carts[$order_id] = array();
                }
                $order_carts[$order_id][] = $c;
            }
        }
        $this->set('orders', $orders);
        $this->set('order_carts', $order_carts);
        $this->set('ship_type', ShipAddress::ship_type_list());
        $this->set('creator', $creator);
    }

    /**
     * @param $orderId
     * @param $uid
     * @return mixed
     */
    private function find_my_order_byId($orderId, $uid) {
        return $this->Order->find_my_order_byId($orderId, $uid);
    }

    /**
     * @param $status
     * @return bool
     */
    private function can_comment($status) {
        return $status != ORDER_STATUS_CANCEL
        && $status != ORDER_STATUS_PAID
        && $status != ORDER_STATUS_WAITING_PAY//            && $status != ORDER_STATUS_SHIPPED
            ;
    }


    /**
     * @return mixed
     */
    private function specified_balance_pids() {
        $balancePidJson = $this->Session->read(self::key_balance_pids());
        if (!empty($balancePidJson)) {
            return json_decode($balancePidJson, true);
        }
        return null;
    }

    private function _applied_couon_code() {
        return $this->Session->read('Balance.coupon_code');
    }

    private function _save_applied_coupon_code($code) {
        return $this->Session->write('Balance.coupon_code', $code);
    }

    private function _clear_coupons_scores() {
        $this->_save_applied_coupon_code('');
        $this->Session->write(self::key_balanced_scores(), '');
        $this->Session->write(self::key_balanced_conpons(), json_encode(array()));
    }

    /**
     * @param $brandId
     * @return array
     */
    private function _applied_coupons($brandId = null) {
        $appliedCoupons = array();
        $coupon_value = $this->Session->read(self::key_balanced_conpons());
        if ($coupon_value) {
            $couponByBrands = json_decode($coupon_value, true);
            foreach ($couponByBrands as $bId => $coupons) {
                if ($brandId === null) {
                    $appliedCoupons = array_merge($appliedCoupons, $coupons);
                } else if ($brandId == $bId) {
                    return array_unique($coupons);
                }
            }
            $appliedCoupons = array_unique($appliedCoupons);
        }
        return $appliedCoupons;
    }

    private function _remove_applied_coupons($brand_id, $coupon_item_id) {
        $coupon_value = $this->Session->read(self::key_balanced_conpons());
        if ($coupon_value) {
            $couponByBrands = json_decode($coupon_value, true);
            if (!empty($couponByBrands[$brand_id])) {
                array_delete_value_ref($couponByBrands[$brand_id], $coupon_item_id);
            }
            $this->Session->write(self::key_balanced_conpons(), json_encode($couponByBrands));
        }
    }

    private function _brand_apply_coupon($brand_id, $coupon_item_id) {
        $key = self::key_balanced_conpons();
        $coupon_value = $this->Session->read($key);
        if ($coupon_value) {
            $couponByBrands = json_decode($coupon_value, true);
            if (empty($couponByBrands[$brand_id])) {
                $couponByBrands[$brand_id] = array();
            }
            array_push($couponByBrands[$brand_id], $coupon_item_id);
        } else {
            $couponByBrands = array();
        }
        $this->Session->write($key, json_encode($couponByBrands));
    }

    /**
     * @param $brand_id
     * @param $applying
     * @param $coupon_item_id
     * @param $uid
     * @param $cart
     * @return array
     */
    private function _try_apply_coupon_item($brand_id, $applying, $coupon_item_id, $uid, $cart) {

        $changed = false;
        $reason = '';

        $all_applied_coupons = $this->_applied_coupons();
        $brand_applied_coupons = $this->_applied_coupons($brand_id);
        if (in_array($coupon_item_id, $brand_applied_coupons)) {
            $coupon_type = $brand_id;
        } else {
            $coupon_type = 0;
        }
        //TODO: 需要考虑各种券的一致性，排他性
        if ($applying) {

            if (empty($all_applied_coupons) || array_search($coupon_item_id, $all_applied_coupons) === false) {

                $couponItems = $this->CouponItem->find_my_valid_coupon_items($uid, array_merge($brand_applied_coupons, (array)$coupon_item_id));
                $couponsByShared = array_filter($couponItems, function ($val) {
                    return ($val['Coupon']['type'] == COUPON_TYPE_TYPE_SHARE_OFFER);
                });

                $curr_coupon_item = array_filter($couponItems, function ($val) use ($coupon_item_id) {
                    return ($val['CouponItem']['id'] == $coupon_item_id);
                });

                if (empty($curr_coupon_item)) {
                    $reason = 'share_type_not_exists';
                } else {
                    $curr_coupon_item = array_shift($curr_coupon_item);
                    //这里必须安店面去限定
                    //要把没有查询到的couponItem去掉
                    if ($curr_coupon_item['Coupon']['type'] == COUPON_TYPE_TYPE_SHARE_OFFER
                        && !empty($cart->brandItems[$brand_id]) && count($couponsByShared) <= $cart->brandItems[$brand_id]->total_num()
                    ) {
                        //            if($cart->could_apply($brand_id, $cou)){
                        //TODO: 需要考虑券是否满足可用性等等
                        $this->_brand_apply_coupon($brand_id, $coupon_item_id);
                        $changed = true;
                    } else if ($curr_coupon_item['Coupon']['type'] == COUPON_TYPE_TYPE_MAN_JIAN && count($all_applied_coupons) < 1) {
                        $this->_brand_apply_coupon(0, $coupon_item_id);
                        $changed = true;
                    } else {
                        $reason = 'share_type_coupon_exceed';
                    }
//                    else if (count($couponItems) == 1) {
//                        $this->_brand_apply_coupon(0, $coupon_item_id);
//                        $changed = true;
//                    }
                }
//            }
            }
        } else {
            if (!empty($all_applied_coupons)
                && array_search($coupon_item_id, $all_applied_coupons) !== false
            ) {
                $this->_remove_applied_coupons($coupon_type, $coupon_item_id);
                $changed = true;
            }
        }
        return array($changed, $reason);
    }

    /**
     * @param $uid
     * @return mixed
     */
    private function _cal_total_reduced($uid) {
        $applied_coupons = $this->_applied_coupons();
        $coupon_code = $this->_applied_couon_code();
        $score_num = $this->Session->read(self::key_balanced_scores());
        $ziti = $this->Session->read(self::key_balanced_ship_type());
        $shipfee = $this->Session->read(self::key_balanced_ship_fee());
        return $this->Buying->total_reduced($uid, $applied_coupons, $coupon_code, $score_num, array('ziti' => $ziti, 'shipFee' => $shipfee));
    }


    function wait_shipped_orders($creator = 0) {
        $this->__business_orders($creator, array(ORDER_STATUS_PAID));
    }


    function wait_paid_orders($creator = 0) {
        $this->__business_orders($creator, array(ORDER_STATUS_WAITING_PAY));
    }


    function shipped_orders($creator = 0) {
        $this->__business_orders($creator, array(ORDER_STATUS_SHIPPED));
    }


    function signed_orders($creator = 0) {
        $this->__business_orders($creator, array(ORDER_STATUS_RECEIVED));
    }

    //修改商家备注
    function remark_submit() {
        $this->autoRender = false;
        $order_id = $_REQUEST['order_id'];
        $remark = $_REQUEST['remark'];
        $currentUid = $this->currentUser['id'];
        if (empty($order_id) || !isset($remark)) {
            echo json_encode(array('order_id' => $order_id, 'msg' => '参数错误'));
            exit;
        }
        $order_info = $this->Order->find('first', array(
            'conditions' => array('id' => $order_id),
            'fields' => array('brand_id'),
        ));

        if (empty($order_info)) {
            echo json_encode(array('order_id' => $order_id, 'msg' => '您不具备此订单的修改权限，请联系管理员。'));
            exit;
        }
        $this->loadModel('Brand');
        $brand = $this->Brand->findById($order_info['Order']['brand_id']);
        $is_brand_admin = !empty($brand) && $brand['Brand']['creator'] == $currentUid;
        if (!$is_brand_admin) {
            echo json_encode(array('order_id' => $order_id, 'msg' => '您没有权限修改商家备注'));
            exit;
        }
        $this->Order->updateAll(array('business_remark' => '\'' . $remark . '\''), array('id' => $order_id));
        $successinfo = array('content' => $remark);
        echo json_encode($successinfo);

    }


    /*
     * remind the sellers to deliver goods
     */
    function remind_deliver($order_id) {

        $this->autoRender = false;
        $this->loadModel('RemindDeliver');
        $this->loadModel('Brand');
        $this->loadModel('User');
        $order_info = $this->Order->find('first', array('conditions' => array('id' => $order_id), 'user_id' => $this->currentUser['id']));
        $remind_info = $this->RemindDeliver->find('first', array('conditions' => array('order_id' => $order_id, 'user_id' => $this->currentUser['id']), 'order' => 'times desc'));
        $brand_info = $this->Brand->find('first', array('conditions' => array('id' => $order_info['Order']['brand_id'])));
        $seller_info = $this->User->find('first', array('conditions' => array('id' => $brand_info['Brand']['creator'])));

        $cTime = time();
        $nTime = $remind_info['RemindDeliver']['remind_time'];
        $dTime = $cTime - strtotime($nTime);
        $data = array();
        $data['order_id'] = $order_id;
        $data['user_id'] = $this->currentUser['id'];
        $data['remind_time'] = date('Y-m-d H:i:s');
        $paid_time_past = strtotime($data['remind_time']) - strtotime($order_info['Order']['pay_time']);
        $dDay = intval(date("d", strtotime($data['remind_time']))) - intval(date("d", strtotime($order_info['Order']['pay_time'])));
        $dHour = intval(($paid_time_past / 3600) % 24);
        $dDay = $dDay > 0 ? $dDay . '天' : '';
        $dHour = $dHour > 0 ? $dHour . '小时' : '';
        $dMin = ($dDay == '' && $dHour == '') ? intval(($paid_time_past / 60) % 60 + 1) . '分钟' : '';
        $this->log('$paid_time_past' . json_encode($dHour));

        if (empty($remind_info)) {
            $data['times'] = 1;
            if ($this->RemindDeliver->save($data)) {
                $msg = '用户' . $order_info['Order']['consignee_name'] . '催促您发货。订单号' . $order_id . '，离用户支付成功已过去' . $dDay . $dHour . $dMin;
                $tel = $seller_info['User']['mobilephone'];
                message_send($msg, $tel);
                $return_Info = 1;
                echo json_encode($return_Info);
                exit;

            }
        } else {
            if (intval($dTime / 60) <= 15) {
                $return_Info = 2;
                echo json_encode($return_Info);
                exit;
            } else {
                $data['times'] = $remind_info['RemindDeliver']['times'] + 1;
                if ($this->RemindDeliver->save($data)) {
                    $msg = '用户' . $order_info['Order']['consignee_name'] . '第' . $data['times'] . '次催促您发货。订单号' . $order_id . '，离用户支付成功已过去' . $dDay . $dHour . $dMin;
                    $tel = $seller_info['User']['mobilephone'];
                    message_send($msg, $tel);
                    $return_Info = 3;
                    echo json_encode($return_Info);
                }
            }
        }
    }

    public function chose_pickup() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $res = array('status' => false);
        if (empty($uid)) {
            $res['reason'] = 'not_login';
            echo json_encode($res);
            return;
        }
        $name = trim($_POST['name']);
        $mobile = $_POST['mobile'];
        $remarkAddress = $_POST['remark_address'];
        $shop_id = intval($_POST['shop_id']);
        if (!$name || !$mobile || !$shop_id) {
            $res['reason'] = 'invalid_data';
            echo json_encode($res);
            return;
        }
        $this->loadModel('OfflineStore');
        $this->loadModel('OrderConsignee');
        $pickup = $this->OfflineStore->find('first', array(
            'conditions' => array('id' => $shop_id, 'deleted' => DELETED_NO)
        ));
        if (empty($pickup)) {
            $res['reason'] = 'invalid_shop';
            echo json_encode($res);
            return;
        }
        $old_pickup = $this->OrderConsignee->find('first', array(
            'conditions' => array('creator' => $uid, 'status' => STATUS_CONSIGNEES_TUAN_ZITI, 'deleted' => 0)
        ));
        $address = $pickup['OfflineStore']['name'] . '(联系人：' . $pickup['OfflineStore']['owner_name'] . $pickup['OfflineStore']['owner_phone'] . ')';
        $data = array();
        $data['OrderConsignee'] = array(
            'name' => $name,
            'address' => $address,
            'creator' => $uid,
            'status' => STATUS_CONSIGNEES_TUAN_ZITI,
            'mobilephone' => $mobile,
            'area' => $pickup['OfflineStore']['area_id'],
            'ziti_id' => $shop_id,
            'ziti_type' => $pickup['OfflineStore']['type'],
            'remark_address' => $remarkAddress
        );
        if (empty($old_pickup)) {
            $data = $this->OrderConsignee->save($data);
        } else {
            $data['OrderConsignee']['id'] = $old_pickup['OrderConsignee']['id'];
            $this->OrderConsignee->save($data);
        }
        $this->Session->write('pickupConsignee', $data['OrderConsignee']);
        $res = array('status' => true, 'data' => $data['OrderConsignee']);
        echo json_encode($res);
    }

    private function should_show_ziti_address($zitiSupport, $normalConsign, $zitiConsign) {
        if ($zitiSupport && !empty($zitiConsign)) {
            if (empty($normalConsign)) {
                return true;
            }
            if (strtotime($zitiConsign['updated']) > strtotime($normalConsign['updated'])) {
                return true;
            }
        }
        return false;
    }

    private function should_show_normal_address($zitiSupport, $normalConsign, $zitiConsign) {
        if (!empty($normalConsign)) {
            if (!$zitiSupport) {
                return true;
            } else {
                if (empty($zitiConsign)) {
                    return true;
                }
                if (strtotime($zitiConsign['updated']) < strtotime($normalConsign['updated'])) {
                    return true;
                }
            }
        }
        return false;
    }

    private function ziti_exist($zitiSupport, $zitiConsigns) {
        if ($zitiSupport && count($zitiConsigns) > 0) {
            return true;
        }
        return false;
    }

    private function clear_score_and_coupon() {
        $this->Session->write(self::key_balanced_scores(), '');
        $this->Session->write(self::key_balanced_conpon_global(), '[]');
        $this->Session->write(self::key_balanced_conpons(), '[]');
        $this->Session->write(self::key_balanced_promotion_code(), '');
    }

    /**
     * @param $shipFee
     * @return float
     * 由于团购的流程 计算邮费在另一个系统中 所以计算 使用积分和优惠券的时候要 重新计算邮费
     *
     * 以后使用要注意
     * TODO 统一方法
     */
    private function get_custom_ship_fee($shipFee) {
        //tuan buy can use custom ship fee when use custom ship fee
        if ($_REQUEST['ship_fee'] != null) {
            $shipFee = floatval($_REQUEST['ship_fee']);
        }
        return $shipFee;
    }
}
