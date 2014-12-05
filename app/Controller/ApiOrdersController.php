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
        if (!empty($access_token)) {
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
        $this->set('_serialize', array('brands', '$orders', 'order_carts', 'ship_type'));
    }

} 