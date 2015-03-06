<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/2/14
 * Time: 10:59 AM
 */

class ApiOrdersController extends AppController {
    public $components = array('OAuth.OAuth', 'Session');
    public function beforeFilter() {
        parent::beforeFilter();
        $allow_action = array('test','product_detail','store_list','product_content', 'store_content', 'store_story','_save_comment', 'home','articles');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action)  == false) {
            $this->currentUser = $this->OAuth->user();
        }
    }
    //写入一个client_id到clien表,每个app版本对应一个id
    /*
    public function oauth_writte(){
        $data = array('Client' => array('user_id'=>'1', 'redirect_uri'=>'http://www.tongshijia.com'));
        $client = $this->OAuth->Client->add($data);
        $this->set('client', $client);
        $this->set('_serialize','client');
    }
*/
    public function mine() {

        $this->loadModel('Order');

        $uid = $this->currentUser['id'];

        $status = null;
        if (isset($_REQUEST['status'])) {
            $status = intval($_REQUEST['status']);
        }

        list($orders, $order_carts, $mappedBrands) = $this->Order->get_user_orders($uid, $status);

        $counts = array();
        foreach($order_carts as $order_id => $c){
            $counts[$order_id] += $c['Cart']['num'];
        }

        $this->set('brands', $mappedBrands);
        $this->set('orders',$orders);
        $this->set('order_carts',$order_carts);
        $this->set('ship_type', ShipAddress::ship_type_list());
        $this->set('_serialize', array('brands', 'orders', 'order_carts', 'ship_type'));
    }

    public function list_cart() {
        $this->loadModel('Cart');
        $Carts = $this->Cart->find('all',array(
            'conditions'=>array(
//                'published' => PUBLISH_YES,
                'deleted' => DELETED_NO,
                'status' => 0,
                'order_id' => NULL,
                'creator'=> $this->currentUser['id'],
            ),
            'fields' => array('id', 'name', 'product_id', 'num', 'name', 'price', 'coverimg', 'used_coupons','specId'),
        ));
        $total_price = 0;
        foreach($Carts as $cart){
            $total_price += $cart['Cart']['price']*$cart['Cart']['num'];
        }

        $pids = Hash::extract($Carts, '{n}.Cart.product_id');
        $this->loadModel('Product');
        $products = $this->Product->find_products_by_ids($pids);

        foreach($Carts as &$cart) {
            $cart['Cart']['brand_id'] = $products[$cart['Cart']['product_id']]['brand_id'];
            $specObj = json_decode($products[$cart['Cart']['product_id']]['specs'],true);
            $specId = intval($cart['Cart']['specId']);
            if($specObj){
                $cart['Cart']['spec']=$specObj['map'][$specId]['name'];
            }else{
                $cart['Cart']['spec']=null;
            }

        }

        $brandIds = Hash::extract($products, '{n}.brand_id');
        $brands = $this->findBrands($brandIds);

        $this->set('total_price', $total_price);
        $this->set('brands', $brands);
        $this->set('carts', $Carts);
        $this->set('_serialize', array('total_price', 'carts', 'brands'));
    }

    /**
     * Display and options for already submitted order
     * @Param int $order_id
     * @Param string action
     */
    function order_detail($orderId) {
        $uid = $this->currentUser['id'];
        $this->loadModel('Order');
        $order = $this->Order->find_my_order_byId($orderId, $uid);
        if(empty($order)){
            echo json_encode(array('success' => 'false', 'msg' => 'not found'));
            return;
        }

        $this->loadModel('Cart');
        $Carts = $this->Cart->find('all', array(
            'conditions'=>array(
                'order_id' => $orderId,
                'creator'=> $uid
            )));
        $product_ids = Hash::extract($Carts, '{n}.Cart.product_id');
        $this->loadModel('Product');
        $products = $this->Product->find('all', array(
            'fields' => array('id', 'created', 'slug', 'published', 'deleted','specs'),
            'conditions'=>array(
                'id' => $product_ids
            )));
        $product_spec = Hash::combine($products, '{n}.Product.id', '{n}.Product.specs');
        $num = 0;
        foreach ($Carts as $cart){
            $value = $product_spec[$cart['Cart']['product_id']];
            $spec_info = json_decode($value,true);
            $specId = $cart['Cart']['specId'];
            $Carts[$num]['Cart']['spec'] =  $spec_info['map'][$specId]['name'];
            $num ++;
        }
        $expired_pids = array();
        foreach($product_ids as $pid) {
            if (empty($products[$pid])
                || $products[$pid]['Product']['published'] == PUBLISH_NO
                || $products[$pid]['Product']['deleted'] == 1) {
                $expired_pids[] = $pid;
            }
        }

        $totalCents = $order['Order']['total_all_price'] * 100;
        $no_more_money = $totalCents < 1 && $totalCents >= 0;
        $store = $this->findBrands($order['Order']['brand_id']);
        $products = Hash::combine($products, '{n}.Product.id', '{n}.Product.slug');
        $this->set(compact('no_more_money', 'order_id', 'order', 'expired_pids'));

        $this->set('ship_type', ShipAddress::ship_type_list());
        $this->set('order', $order);
        $this->set('carts',$Carts);
        $this->set('products', $products);
        $this->set('store', array_slice($store[0]['Brand'],0,4));

        $this->set('_serialize', array('order', 'carts', 'ship_type', 'expired_pids', 'no_more_money', 'products', 'store'));
    }

    function confirm_receive($order_id){
        $buyingCom = $this->Components->load('Buying');
        $buyingCom->confirm_receive($this->currentUser['id'], $order_id);
    }

    function confirm_undo($order_id){
        $buyingCom = $this->Components->load('Buying');
        $buyingCom->confirm_undo($this->currentUser['id'], $order_id);
    }

    function confirm_remove($order_id){
        $buyingCom = $this->Components->load('Buying');
        $buyingCom->confirm_remove($this->currentUser['id'], $order_id);
    }

    public function product_detail($pid) {

        if (!empty($pid)) {
            $is_limit_ship = ClassRegistry::init('ShipPromotion')->is_limit_ship($pid);
            $productM = ClassRegistry::init('Product');
            $pro = $productM->findById($pid);
            if (!empty($pro) && $pro['Product']['deleted'] == DELETED_NO && $pro['Product']['published'] == PUBLISH_YES) {
                unset($pro['Product']['content']);
                unset($pro['Product']['saled']);
                unset($pro['Product']['storage']);
                unset($pro['Product']['views_count']);
                unset($pro['Product']['cost_price']);
                $pro['Product']['limit_ship']=$is_limit_ship;
                $brandM = ClassRegistry::init('Brand');
                $brand = $brandM->findById($pro['Product']['brand_id']);
                $this->set('brand', $brand);

                $recommC = $this->Components->load('ProductRecom');
                $recommends = $recommC->recommend($pid);

                $this->set('product',$pro);
                $this->set('recommends', $recommends);
                $this->set('brand', $brand);

                $specialListM = ClassRegistry::init('SpecialList');
                $specialLists = $specialListM->has_special_list($pid);
                if (!empty($specialLists)) {
                    foreach ($specialLists as $specialList) {
                        if ($specialList['type'] == 1) {
                            $special = $specialList;
                            break;
                        }
                    }
                }
                $currUid = $this->currentUser['id'];
                if (!empty($special) && $special['special']['special_price'] >= 0) {
                    $special_rg = array('start' => $special['start'], 'end' => $special['end']);
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
                        ;
                    } else {
                        $promo_desc .=  '('. ($left_cur_user == 0 ? '您已买过' : '已售完') . ')';
                    }
                    $special = array('special_desc' => $promo_desc, 'special_name'=>$promo_name, 'special_slug'=>$special['slug']);
                    $this->set('special', $special);
                }
            }
        }
        $this->set('_serialize', array('product', 'recommends', 'brand','special'));
    }

    public function product_content($pid) {

        if (!empty($pid)) {
            $productM = ClassRegistry::init('Product');
            $pro = $productM->findById($pid);
            //&& $pro['Product']['published'] == PUBLISH_YES
            if (!empty($pro) && $pro['Product']['deleted'] == DELETED_NO) {
                $this->set('content',array('Product' => array('id' => $pid, 'content' => $pro['Product']['content'])));
            }

        }
        $this->set('_serialize', array('content'));
    }

    public function store_list() {
        $brandM = ClassRegistry::init('Brand');
        $brands = $brandM->find('all', array(
            'conditions' => array('published' => PUBLISH_YES, 'deleted' => DELETED_NO),
            'fields' => array('creator', 'name', 'slug', 'coverimg', 'id'),
            'order' =>  'id desc',
        ));

        $this->set('brands', $brands);
        $this->set('_serialize', array('brands'));
    }

    public function store_content($id){
        $info = $this->findBrands($id);
        if(!empty($info)){
            $productM = ClassRegistry::init('Product');
            $products = $productM->find('all', array(
                'conditions' => array('brand_id' => $id, 'published' => PUBLISH_YES, 'deleted' => DELETED_NO),
                'fields' => array('id','name', 'coverimg', 'slug', 'price', 'original_price')
            ));
        }
        $this->set('content', array('info' => $info['0']['Brand'], 'products' => $products));
        $this->set('_serialize', array('content'));
    }

    public function store_story($id){
        $brandM = ClassRegistry::init('Brand');
        $info = $brandM->find('first', array(
            'conditions' => array('id' => $id, 'published' => PUBLISH_YES, 'deleted' => DELETED_NO),
            'fields' => array( 'content')
        ));
        $this->set('story', $info);
        $this->set('_serialize', 'story');
    }

    public function my_coupons() {
        $this->loadModel('CouponItem');
        $coupons = $this->CouponItem->find_my_all_coupons($this->currentUser['id']);

        $brandIds = Hash::extract($coupons, '{n}.Coupon.brand_id');

        foreach($coupons as &$coupon) {
            unset($coupon['Coupon']['deleted']);
            unset($coupon['Coupon']['published']);
            unset($coupon['Coupon']['last_updator']);
            unset($coupon['Coupon']['created']);
            unset($coupon['Coupon']['modified']);
            unset($coupon['CouponItem']['sent_message_status']);
            unset($coupon['CouponItem']['deleted']);
            unset($coupon['CouponItem']['modified']);
            unset($coupon['CouponItem']['last_updator']);
        }

        $brands = $this->findBrands($brandIds);
        if (!empty($brands)) {
            $brands = Hash::combine($brands, '{n}.Brand.id', '{n}');
        }


        $this->set(compact('coupons', 'brands'));
        $this->set('_serialize', array('coupons', 'brands'));
    }

    public function my_offers() {
        $this->loadModel('SharedOffer');
        $sharedOffers = $this->SharedOffer->find_my_all_offers($this->currentUser['id']);
        $expiredIds = array();
        $brandIds = Hash::extract($sharedOffers, '{n}.ShareOffer.brand_id');
        foreach($sharedOffers as &$o) {
            $expired = is_past($o['SharedOffer']['start'], $o['ShareOffer']['valid_days']);
            if($expired) {
                $expiredIds[] = $o['SharedOffer']['id'];
            } else if (SharedOffer::slicesSharedOut($o['SharedOffer']['id'], $o['SharedOffer']['status'])) {
                $soldOuts[] = $o['SharedOffer']['id'];
            }

            $o['SharedOffer']['valid_days'] = $o['ShareOffer']['valid_days'];
            $o['SharedOffer']['brand_id'] = $o['ShareOffer']['brand_id'];
            $o['SharedOffer']['name'] = $o['ShareOffer']['name'];

            unset($o['ShareOffer']);
            unset($o['SharedOffer']['order_id']);
            unset($o['SharedOffer']['share_offer_id']);
            unset($o['SharedOffer']['modified']);
            unset($o['SharedOffer']['created']);
        }

        $brands = $this->findBrands($brandIds);
        if (!empty($brands)) {
            $brands = Hash::combine($brands, '{n}.Brand.id', '{n}');
        }

        $this->set(compact('sharedOffers', 'expiredIds', 'soldOuts', 'brands'));
        $this->set('_serialize', array('sharedOffers', 'expiredIds', 'soldOuts', 'brands'));
    }

    /**
     * @param $ids
     * @param $brandM
     * @return mixed
     */
    private function findBrands($ids, $brandM = null) {
        if ($brandM == null) {
            $brandM = ClassRegistry::init('Brand');
        }
        $info = $brandM->find('all', array(
            'conditions' => array('id' => $ids, 'deleted' => DELETED_NO),
            'fields' => array('id', 'name', 'slug', 'coverimg', 'weixin_id', 'notice')
        ));
        return $info;
    }

    public function order_consignees(){
        $creator = $this->currentUser['id'];
        $orderM = ClassRegistry::init('OrderConsignees');
        $info = $orderM->find('all', array(
            'conditions' => array('creator' => $creator, 'deleted' => 0 ),
            'fields' => array('id', 'name', 'status', 'area', 'address', 'mobilephone', 'telephone', 'email', 'postcode', 'province_id', 'city_id', 'county_id')
        ));
        $this->set('order_consigness', $info);
        $this->set('_serialize', 'order_consigness');
    }

    public function my_profile() {
        $shichituanM = ClassRegistry::init('Shichituan');
        $result = $shichituanM->findByUser_id($this->currentUser['id'],array('Shichituan.shichi_id','Shichituan.pictures','Shichituan.status','Shichituan.period'),'Shichituan.shichi_id DESC');
        $userM = ClassRegistry::init('User');
        $user_id = $this->currentUser['id'];
        $datainfo = $userM->find('first', array('recursive' => -1,
            'conditions' => array('id' => $user_id),
            'fields'=>array('nickname', 'email', 'image', 'sex', 'companies', 'bio', 'mobilephone', 'email', 'username', 'id')));
        $this->set('my_profile', array('Shichituan' => $result['Shichituan'], 'User' => $datainfo['User']));
        $this->set('_serialize', array('my_profile'));

    }

    public function cart_edit_num($id, $num) {
        $cartM = ClassRegistry::init('Cart');
        $success = $cartM->edit_num($id, $num, $this->currentUser['id']);

        $info = array('success' => $success);
        $this->set('info', $info);
        $this->set('_serialize', 'info');
    }

    public function cart_del($id) {
        $cartM = ClassRegistry::init('Cart');
        $success = $cartM->delete_item($id, $this->currentUser['id']);

        $info = array('success' => $success);
        $this->set('info', $info);
        $this->set('_serialize', 'info');
    }

    public function cart_add() {
        $buyingCom = $this->Components->load('Buying');
        $postStr = file_get_contents('php://input');;
        $data = json_decode(trim($postStr), true);
        if (!empty($data)) {
            $product_id = $data['product_id'];
            $num = $data['num'];
            $specId = $data['spec'];
            $type = $buyingCom->convert_cart_type($data['type']);
            $tryId = intval($data['try_id']);
            $uid = $this->currentUser['id'];
            $cartM = ClassRegistry::init('Cart');

            if (!$type) {
                $type = CART_ITEM_TYPE_NORMAL;
            }

            $info = $buyingCom->check_and_add($cartM, $type, $tryId, $uid, $num, $product_id, $specId, null);
        } else {
            $info = array('success' => false, 'reason' => 'invalid_parameter');
        }
        $this->set('info', $info);
        $this->set('_serialize', 'info');
    }


    /**
     * 订单信息页，确认各项订单信息
     * @throws CakeException
     * @throws MissingModelException
     * @internal param int|string $order_id
     */
    function cart_info(){
        $success = false;
        $postStr = file_get_contents('php://input');;
        $data = json_decode(trim($postStr), true);
        $pidList = $data['pid_list'];
        $couponCode = $data['coupon_code'];
        //$addressId = $data['addressId'];
        if (!empty($pidList)) {
            $this->loadModel('Cart');
            $this->loadModel('Product');
            $this->loadModel('ShipPromotion');
            $uid = $this->currentUser['id'];
            $buyingCom = $this->Components->load('Buying');
            //FIXME: check pid list
            $cartsByPid = $buyingCom->cartsByPid($pidList, $uid);
            list($pids, $cart, $shipFee, $shipFees) = $buyingCom->createTmpCarts($cartsByPid, 0, $pidList, $uid);

            $products = $this->Product->find('all', array(
                'fields' => array('id', 'created', 'slug', 'published', 'deleted','specs','coverimg'),
                'conditions'=>array(
                    'id' => $pidList
                )));
//            $product_specs = array();
//            foreach ($products as $product) {
//                $product_specs[$product['Product']['id']] = array('img' => $product['Product']['coverimg'], 'spec'=> $product['Product']['specs']);
//            }
//            $cart->products = $product_specs;
            $products= Hash::combine($products,'{n}.Product.id','{n}.Product');
            $bis = $cart->brandItems;
            foreach($bis as &$bi){
                $items=$bi->items;
                foreach($items as $index=>$i){
                    $i->coverimg=$products[$index]['coverimg'];
                    $specs = json_decode($products[$index]['specs'],true);
                    $specId = intval($cartsByPid[$index]["specId"]);
                    $i->specId=$specId;
                    $i->spec=$specs['map'][$specId]['name'];
                }
            }

            $brand_ids = array_keys($cart->brandItems);
            $brands = $this->findBrands($brand_ids);

            if (!$cart->is_try) {
                $couponItem = ClassRegistry::init('CouponItem');
                $coupons_of_products = $couponItem->find_user_coupons_for_cart($uid, $cart);
            }

            $reduced_percent = $buyingCom->total_reduced($uid, array(), $couponCode,0);
            $total_price = max(0,  ($cart->total_price() * 100 - $reduced_percent) /100 );
            $reduced = $reduced_percent/100;
            $success = true;
        } else {
            if (empty($pidList)) {
                $reason[] = 'empty_products';
            }
//            if (empty($addressId)) {
//                $reason[] = 'invalid_address';
//            }
        }
        $this->set(compact('success', 'total_price', 'shipFee', 'coupons_of_products', 'cart', 'brands', 'shipFees', 'reduced', 'reason'));
        $this->set('_serialize', array('success', 'total_price', 'shipFee', 'coupons_of_products', 'cart', 'brands', 'shipFees', 'reduced', 'reason'));
    }

    public function balance() {

        $success = true;
        $postStr = file_get_contents('php://input');;
        $data = json_decode(trim($postStr), true);
        $pidList = $data['pid_list'];
        $addressId = $data['addressId'];
        $couponCode = $data['coupon_code'];
        $remarks = $data['remarks'];
        if (!empty($pidList) && !empty($addressId)) {

            $this->loadModel('Cart');
            $this->loadModel('Product');
            $product_ids = array();
            $shipPromotionId = intval($_REQUEST['ship_promotion']);
            $this->loadModel('ShipPromotion');
            $this->loadModel('Order');

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

            $Carts = array();
            $uid = $this->currentUser['id'];
            $cond = array(
                'status' => CART_ITEM_STATUS_NEW,
                'order_id' => null,
                'num > 0',
                'product_id' => $pidList,
                'creator' => $uid
            );

            $Carts_tmp = $this->Cart->find('all', array(
                'conditions' => $cond));

            foreach($Carts_tmp as $c){
                $product_ids[]=$c['Cart']['product_id'];
                $Carts[$c['Cart']['product_id']] = $c;
            }

            if(empty($Carts)){
                $success = false;
                $reason[] = 'empty_products';
            } else {
                $this->loadModel('OrderConsignee');
                $address = $this->OrderConsignee->find('first', array(
                    'conditions' => array('id' => $addressId, 'creator' => $uid, 'deleted' => DELETED_NO)
                ));
                if (empty($address) || empty($address['OrderConsignee']['name'])
                    || empty($address['OrderConsignee']['address'])
                    || empty($address['OrderConsignee']['mobilephone'])) {
                    $this->log('orders_balance: cannot find address:'.$addressId.', uid='.$uid);
                    $success = false;
                    $reason[] = 'invalid_address';
                } else {
                    $provinceId = $address['OrderConsignee']['province_id'];
                    $allP = $this->Product->find('all',array('conditions'=>array(
                        'id' => $product_ids
                    )));

                    $business = array();
                    foreach($allP as $p) {
                        if(!is_array($business[$p['Product']['brand_id']])) {
                            $business[$p['Product']['brand_id']] = array();
                        }
                        $business[$p['Product']['brand_id']][] = $p['Product'];
                    }

                    $pids = Hash::extract($allP, '{n}.Product.id');

                    $tryId = 0;

                    $this->loadModel('ShipSetting');
                    $shipSettings = $this->ShipSetting->find_by_pids($pids, $provinceId);

                    $new_order_ids = array();
                    foreach ($business as $brand_id => $products) {
                        $total_price = 0.0;
                        foreach($products as $pro){
                            $pid = $pro['id'];
                            $num = $Carts[$pid]['Cart']['num'];
                            $total_price+= $Carts[$pid]['Cart']['price'] * $num;

                            list($afford_for_curr_user, $limit_cur_user) = $tryId ? afford_product_try($tryId, $uid) : AppController::__affordToUser($pid, $uid);
                            if (!$afford_for_curr_user) {
                                $success = false;
                                $reason[] = 'sold_out_'.$pid;
                                break;
                            } else if ($limit_cur_user == 0 || ($limit_cur_user > 0 && $num > $limit_cur_user)) {
                                $success = false;
                                $reason[] = 'exceed_limit_'.$pid;
                                break;
                            }
                        }

                        if ($success) {
                            if($total_price <= 0){
                                $success = false;
                                $reason[] = 'invalid_total_price';
                            }

                            $shipFeeContext = array();
                            $ship_fee = 0.0;
                            $ship_fees = array();
                            foreach($products as $pro) {
                                $pid = $pro['id'];
                                $pidShipSettings = array();
                                foreach($shipSettings as $val){
                                    if($val['ShipSetting']['product_id'] == $pid){
                                        $pidShipSettings[] = $val;
                                    }
                                };

                                $num = $Carts[$pid]['Cart']['num'];

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

                            if (!$tryId) {
                                //$ship_fee = ShipPromotion::calculateShipFeeByOrder($ship_fee, $brand_id, $total_price);
                            } else {
                                $data['try_id'] = $tryId;
                            }

                            $data['total_price'] = $total_price;
                            $data['total_all_price'] = $total_price + $ship_fee;
                            $data['ship_fee'] = $ship_fee;
                            $data['brand_id'] = $brand_id;
                            $data['creator'] = $uid;

                            $remark = $remarks[$brand_id];
                            $data['remark'] = empty($remark) ? "" : $remark;

                            $data['consignee_id'] = $addressId;
                            $data['consignee_name'] = $address['OrderConsignee']['name'];
                            $data['consignee_area'] = $address['OrderConsignee']['area'];
                            $data['consignee_address'] = $address['OrderConsignee']['address'];
                            $data['consignee_mobilephone'] = $address['OrderConsignee']['mobilephone'];
                            $data['consignee_telephone'] = $address['OrderConsignee']['telephone'];
                            $data['consignee_email'] = $address['OrderConsignee']['email'];
                            $data['consignee_postcode'] = $address['OrderConsignee']['postcode'];

                            $this->Order->create();

                            if($this->Order->save($data)){
                                $order_id = $this->Order->getLastInsertID();
                                if ($order_id) {
                                    array_push($new_order_ids, $order_id);
                                }
                                foreach($products as $pro){
                                    $pid = $pro['id'];
                                    $cart = $Carts[$pid];
                                    $this->Cart->updateAll(array('order_id'=>$order_id,'status'=>CART_ITEM_STATUS_BALANCED),
                                        array('id'=>$cart['Cart']['id'], 'status' => CART_ITEM_STATUS_NEW));

                                    if (!$tryId) {
                                        $this->Product->update_storage_saled($pid, $cart['Cart']['num']);
                                    }
                                }
                                if (!$tryId) {
                                    //$this->apply_coupons_to_order($brand_id, $uid, $order_id);
                                    //$this->apply_coupon_code_to_order($uid, $order_id);
                                }
                            }
                            else{
                                $this->log('failed to save order: uid='.$uid.', order_content:'.json_encode($data));
                            }
                        }
                    }
                }
            }
        } else {
            $success = false;
            $reason[] = 'invalid_parameter';
        }

        $this->set(compact('success', 'reason'));
        $this->set('order_ids', isset($new_order_ids) ? $new_order_ids : array());
        $this->set('_serialize', array('success', 'order_ids', 'reason'));
    }

    public function info_consignee(){
        if (!isset($inputData)) {
            $inputData = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
        }
        $orderC = ClassRegistry::init('OrderConsignees');
        $areaC = ClassRegistry::init('Locations');
        $data = array();
        $data['creator'] = $this->currentUser['id'];
        $data['name'] = trim($inputData['name']);
        $data['address'] = trim($inputData['address']);
        $data['province_id'] = intval($inputData['province_id']);
        $data['city_id']= intval($inputData['city_id']);
        $data['county_id']= intval($inputData['county_id']) ;
        $data['mobilephone'] = $inputData['mobilephone'];
        if($inputData['province_id']){
            $area = $areaC->find('list', array(
                'conditions'=> array('id' => array($data['province_id'], $data['city_id'], $data['county_id'])),
                'fields'=> array('name')
            ));
            $data['area']= implode("", $area);
        }
        $info = array('success' => false);
        if($inputData['type'] == 'edit' && $inputData['id'] && $inputData['mobilephone']){
            $data['id'] = $inputData['id'];
            if($orderC->hasAny(array('creator' => $this->currentUser['id'], 'id' => $inputData['id']))){

                if($orderC->save($data)){
                    $info =  array('success' => true);
                }
            }

        }else if($inputData['type'] && $inputData['type'] == 'create'){
            if($orderC->save($data)){
                $info =  array('success' => true);
            }
        }else if($inputData['type'] == 'default' && $inputData['id']){
            $oid = intval($inputData['id']);
            $orderC->updateAll(array('status'=> 0), array('creator' => $this->currentUser['id']));
            $orderC->updateAll(array('status'=> 1), array('id' => $oid,'creator' => $this->currentUser['id']));
            $info =  array('success' => true);
        }
        $this->set('info', $info);
        $this->set('_serialize', 'info');
    }

    public function delete_consignee($id){
        $orderC = ClassRegistry::init('OrderConsignees');
        if($orderC->updateAll(array('deleted'=> 1), array('id'=> $id, 'creator' => $this->currentUser['id']))){
            $this->set('info', array('success' => true));
        }
        $this->set('_serialize', 'info');
    }
    public function test(){}

    public  function edit_my_profile(){
        $allow_edit_fields = array('nickname'=>'', 'image'=>'', 'sex'=>'', 'bio'=>'', 'companies'=>'');
        $info = array('success' => false);
        $postStr = file_get_contents('php://input');
        $data = json_decode(trim($postStr), true);
        $accept_data=array_intersect_key($data, $allow_edit_fields);
        if(array_key_exists('sex', $accept_data)){
            $accept_data['sex'] = intval($accept_data['sex']);
        }
        $userM = ClassRegistry::init('User');
        if($this->currentUser['id']){
            $accept_data['id'] = $this->currentUser['id'];
            if($userM->save($accept_data)){
                $info = array('success' => true, 'my_profile' => $accept_data);
            }
        }
        $this->set('info', $info);
        $this->set('_serialize', 'info');
    }

    /**
     * 增加商品评论
     */
    public function comment_add() {
//       $commentC = ClassRegistry::init('Comment');
//      $postStr = file_get_contents('php://input');
//       $data = json_decode(trim($postStr), true);
        $this->loadModel('Comment');
        $data = array();
        $data['data_id'] = $_REQUEST['data_id'];
        $data['type'] = $_REQUEST['type'];
        $data['rating'] = $_REQUEST['rating'];
        $data['body'] = $_REQUEST['body'];
        $data['pictures'] = $_REQUEST['pictures'];
       if (!isset($data['data_id'])) {
//           $this->Session->setFlash(__('Invalid Params', true));
           $info = array('success' => false,'reason' => 'Invalid Params');
           $this->redirect('/');
       }else{
        $returnInfo = $this->_save_comment($data);
        $info = array('success' => true,'returnInfo' => $returnInfo);
       }
        $this->set('info',$info);
        $this->set('_serialize','info');

    }

   private function _save_comment($inputData) {
        $commentC = ClassRegistry::init('Comment');
     if(!empty($inputData)) {

         $data = array();
         $data['user_id'] = $this->currentUser['id'];
         $data['username'] = $this->currentUser['nickname'];
         $data['data_id'] = $inputData['data_id'];
         $data['body'] = $inputData['body'];
         $data['rating'] = $inputData['rating'];
         $data['type'] = $inputData['type'];
         $data['pictures'] = $inputData['pictures'];
         $data['created'] = date('Y-m-d H:i:s');
         $data['status'] = 1;

         $shichituanC=ClassRegistry::init('Shichituan');
         $shichituan_status = $shichituanC->findByUser_id($this->currentUser['id'],array('status'));
         if($shichituan_status['Shichituan']['status'] == 1) {
             $data['is_shichi_tuan_comment'] = 1;
         }

         if($commentC->save($data)) {
             $type_model = $data['type'];
             $this->loadModel($type_model);
             $this->{$type_model}->updateAll(
                 array('comment_nums' => 'comment_nums+1'),
                 array('id' => $inputData['data_id'])
             );
             if ($data['status']) {
                 $returnInfo = array('success' => '您的评论已成功提交');
                 //$this->Session->setFlash(__('Your comment has been added successfully.', true));
             } else {
                 $returnInfo = array('success' => '您的评论已成功提交');
                 //$this->Session->setFlash(__('Your comment will appear after moderation.', true));
             }
             $returnInfo['Comment'] = $data;

         } else {
             $returnInfo = $commentC->validationErrors;

         }
     }else {
           $returnInfo = array('error' => 'please_login');
    }
         return $returnInfo;


    }

    public function home(){
        $bannerItems = array(
            array('img' => "http://www.tongshijia.com/img/banner/spring-weixin.jpg", 'id' => null),
            array('img' => "http://www.tongshijia.com/img/banner/banner_cao_mei_cai_zhai.jpg", 'id' => 705),
        );
        //TODO manage it
        $hotItems =array(
            array('img' => "http://www.tongshijia.com/img/mobile/index/d1.jpg", 'id' => 818),
            array('img' => "http://www.tongshijia.com/img/mobile/index/d2.jpg", 'id' => 826),
            array('img' => "http://www.tongshijia.com/img/mobile/index/d3.jpg", 'id' => 253),
            array('img' => "http://www.tongshijia.com/img/mobile/index/d4.jpg", 'id' => 747),
            array('img' => "http://www.tongshijia.com/img/mobile/index/d5.jpg", 'id' => 336),
        );
        $specTagIds = array(13,14,15);
        $specTagImg = array('http://www.tongshijia.com/img/mobile/index/p1.jpg', 'http://www.tongshijia.com/img/mobile/index/p2.jpg', 'http://www.tongshijia.com/img/mobile/index/p3.jpg');
        $mainTagIds = array(3,5,8,12,9,6,4,10);
        $mainTagImg = array('http://www.tongshijia.com/img/mobile/index/c1.jpg', 'http://www.tongshijia.com/img/mobile/index/c2.jpg', 'http://www.tongshijia.com/img/mobile/index/c3.jpg', 'http://www.tongshijia.com/img/mobile/index/c4.jpg', 'http://www.tongshijia.com/img/mobile/index/c5.jpg', 'http://www.tongshijia.com/img/mobile/index/c6.jpg', 'http://www.tongshijia.com/img/mobile/index/c7.jpg', 'http://www.tongshijia.com/img/mobile/index/c8.jpg');
        $resultTag = array_merge($mainTagIds, $specTagIds);
        $productTagM = ClassRegistry::init('ProductTag');
        $tagInfo = $productTagM->find('all', array('conditions' => array(
            'id'=> $resultTag,
            'published' => 1
        ),
            'fields' => array('id', 'slug','name'),
            'order' => 'priority desc'
        ));
        $productTags = Hash::combine($tagInfo, '{n}.ProductTag.id', '{n}.ProductTag');
        $mainTagItems = array();
        $specTagItems = array();
        $imgNum = 0;
        foreach($mainTagIds as $mainTagId){
            $mainTagItems[] = array('id' =>$mainTagId , 'name' => $productTags[$mainTagId]['name'], 'slug'=>$productTags[$mainTagId]['slug'], 'img'=> $mainTagImg[$imgNum]);
            $imgNum ++;
        }
        $imgNum = 0;
        foreach($specTagIds as $specTagId){
            $specTagItems[] = array('id' =>$specTagId , 'name' => $productTags[$specTagId]['name'], 'slug'=>$productTags[$specTagId]['slug'], 'img'=> $specTagImg[$imgNum]);
            $imgNum ++;
        }
        $productTryM = ClassRegistry::init('ProductTry');
        $tryingItems = $productTryM->find_trying(2);
        $info = array('bannerItems' => $bannerItems, 'tryingItems' => $tryingItems, 'specTagItems' => $specTagItems, 'mainTagItems' => $mainTagItems, 'hotItems' => $hotItems);
        $this->set('info', $info);
        $this->set('_serialize','info');
    }
    public function articles($id){
        if(empty($id)){
            exit();
        }
        $articleM = ClassRegistry::init('Article');
        $article = $articleM->find('first', array(
            'conditions' => array('id' => $id),
            'fields' => array('name', 'content')
        ));
        $this->set('info', $article);
        $this->set('_serialize','info');
    }
}