<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/2/14
 * Time: 10:59 AM
 */

class ApiOrdersController extends AppController {
    public $components = array('OAuth.OAuth', 'Auth', 'Session', 'Security');
    public function beforeFilter() {
        parent::beforeFilter();
        $allow_action = array('product_detail', 'store_list', 'product_content', 'store_content', 'store_story');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action)  == false) {
            $this->currentUser = $this->OAuth->user();
        }
    }

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
            'fields' => array('name', 'product_id', 'name', 'price', 'coverimg', 'used_coupons'),
        ));
        $total_price = 0;
        foreach($Carts as $cart){
            $total_price += $cart['Cart']['price']*$cart['Cart']['num'];
        }

        $this->set('total_price', $total_price);
        $this->set('carts', $Carts);
        $this->set('_serialize', array('total_price', 'carts'));
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
            'fields' => array('id', 'created', 'slug', 'published', 'deleted'),
            'conditions'=>array(
                'id' => $product_ids
            )));

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

        $this->set(compact('no_more_money', 'order_id', 'order', 'expired_pids'));

        $this->set('ship_type', ShipAddress::ship_type_list());
        $this->set('order', $order);
        $this->set('carts',$Carts);
        $this->set('products', $products);

        $this->set('_serialize', array('order', 'carts', 'ship_type', 'expired_pids', 'no_more_money', 'products'));
    }

    public function product_detail($pid) {

        if (!empty($pid)) {

            $productM = ClassRegistry::init('Product');
            $pro = $productM->findById($pid);
            if (!empty($pro) && $pro['Product']['deleted'] == DELETED_NO && $pro['Product']['published'] == PUBLISH_YES) {
                unset($pro['Product']['content']);
                unset($pro['Product']['saled']);
                unset($pro['Product']['storage']);
                unset($pro['Product']['views_count']);
                unset($pro['Product']['cost_price']);

                $brandM = ClassRegistry::init('Brand');
                $brand = $brandM->findById($pro['Product']['brand_id']);
                $this->set('brand', $brand);

                $recommC = $this->Components->load('ProductRecom');
                $recommends = $recommC->recommend($pid);
                $this->set('product',$pro);
                $this->set('recommends', $recommends);
                $this->set('brand', $brand);

            }
        }
        $this->set('_serialize', array('product', 'recommends', 'brand'));
    }

    public function product_content($pid) {

        if (!empty($pid)) {
            $productM = ClassRegistry::init('Product');
            $pro = $productM->findById($pid);
            if (!empty($pro) && $pro['Product']['deleted'] == DELETED_NO && $pro['Product']['published'] == PUBLISH_YES) {
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
        $this->set('content', array('info' => $info['Brand'], 'products' => $products));
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
            'conditions' => array('id' => $ids, 'published' => PUBLISH_YES, 'deleted' => DELETED_NO),
            'fields' => array('id', 'name', 'slug', 'coverimg', 'weixin_id', 'notice')
        ));
        return $info;
    }

    public function order_consignees(){
        $creator = $this->currentUser['id'];
        $orderM = ClassRegistry::init('OrderConsignees');
        $info = $orderM->find('all', array(
            'conditions' => array('creator' => $creator),
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
}