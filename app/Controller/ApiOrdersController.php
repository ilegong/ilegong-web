<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/2/14
 * Time: 10:59 AM
 */

class ApiOrdersController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $access_token = $_REQUEST['token'];
        if (!empty($access_token) || array_search($this->request->params['action'], array('product_detail', 'store_list')) !== false) {
            $this->loadModel('User');
            $user = $this->User->findById('146');
            $this->currentUser = $user['User'];
        }  else {
            exit('denied');
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
                'published' => PUBLISH_YES,
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
                $this->set('brand', $recommends);

                $this->set('_serialize', array('product', 'recommends', 'brand'));
            }
        }
    }

    public function store_list() {
        $brandM = ClassRegistry::init('Brand');
        $brands = $brandM->find('all', array(
            'conditions' => array('published' => PUBLISH_YES, 'deleted' => DELETED_NO),
            'fields' => array('creator', 'name', 'slug', 'id'),
            'order' =>  'id desc',
        ));

        $this->set('brands', $brands);
        $this->set('_serialize', array('brands'));
    }
}