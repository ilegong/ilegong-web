<?php
/**
 * Created by PhpStorm.
 * User: algdev
 * Date: 14/12/16
 * Time: 下午12:14
 */
class OrderShichisController extends  AppController {

    var $name = 'OrderShichis';

    public function __construct($request = null, $response = null) {
        $this->helpers[] = 'PhpExcel';
        parent::__construct($request, $response);
        $this->pageTitle = __('试吃订单');

    }


    function beforeFilter(){
        parent::beforeFilter();
        if(empty($this->currentUser['id']) && array_search($this->request->params['action'], $this->customized_not_logged) === false){
            $this->redirect('/users/login?referer='.urlencode($_SERVER['REQUEST_URI']));
        }
        $this->user_condition = array(
            'session_id'=>	$this->Session->id(),
        );
        if($this->currentUser['id']){
            $this->user_condition['creator']=$this->currentUser['id'];
        }
    }


    function my_shichi(){
        $uid = $this->currentUser['id'];

        list($orders,$order_carts,$mappedBrands) = $this->OrderShichi->get_user_shichi_orders($uid,null);

        $counts = array();
        foreach($order_carts as $order_id => $c){
            $nums = Hash::extract($c, '{n}.Cart.num');

            $total = 0;
            foreach($nums as $num) $total += $num;

            $counts[$order_id] += $total;
        }
        $this->set('brands', $mappedBrands);
        $this->set('orders',$orders);
        $this->set('order_carts',$order_carts);
//        $this->set('ship_type', ShipAddress::ship_type_list());
        $this->set('counts', $counts);



        $this->log('order'.json_encode($orders));
        $this->log('brands'.json_encode($mappedBrands));
        $this->log('order_carts'.json_encode($order_carts));
        $this->log('counts'.json_encode($counts));
    }




}