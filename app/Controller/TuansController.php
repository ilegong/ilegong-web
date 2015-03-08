<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 15/3/7
 * Time: 下午4:37
 */

class TuansController extends AppController{
    public function detail(){
        $this->set('hideNav', true);

    }
    public function join($pid, $tuan_id){
        $this->loadModel('Cart');
        $this->loadModel('Tuan');
        $tuan_info = $this->Tuan->findById($tuan_id);
        $this->Cart->find('first');
        $user_condition = array(
            'session_id'=>	$this->Session->id(),
        );
        if($this->currentUser['id']){
            $user_condition['creator']=$this->currentUser['id'];
        }
        $cond = array(
            'status' => CART_ITEM_STATUS_NEW,
            'order_id' => null,
            'num > 0',
            'product_id' => $pid,
            'type' => CART_ITEM_TYPE_TUAN,
            'OR' => $user_condition
        );
        $Carts = $this->Cart->find('first', array(
            'conditions' => $cond));
        $total_price = $Carts['Cart']['price'] * $Carts['Cart']['num'];
        $this->set('total_price', $total_price);
        $this->set('cart_id', $Carts['Cart']['id']);
        $this->set('tuan_id', $tuan_id);
        $this->set('tuan_address', $tuan_info['Cart']['address']);
    }

    public function tuan_pay($orderId){
        $this->loadModel('Order');
        $order_info = $this->Order->find('first', array(
            'conditions' =>array('id' => $orderId),
            'fields' => array('total_all_price')
        ));
        $this->set('orderId', $orderId);
        $this->set('total_price', $order_info['Order']['total_all_price']);
    }

    public function pre_order() {
        $this->autoRender = false;
        $cart_id = $_POST['cart_id'];
        $tuan_id = $_POST['tuan_id'];
        $mobile = $_POST['mobile'];
        $name = $_POST['name'];
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $this->redirect('/users/login?referer=' . urlencode($_SERVER['REQUEST_URI']));
            return;
        } else {
            $tuan_url = '/tuans/detail';
            if(empty($cart_id)){
                $this->__message('团购信息出错，请返回重试', $tuan_url);
                return;
            }else{
                $this->loadModel('Cart');
                $this->loadModel('Order');
                $this->loadModel('Tuan');
                $cart_info = $this->Cart->findById($cart_id);
                $creator = $cart_info['Cart']['creator'];
                $order_type = $cart_info['Cart']['type'];
                if($creator != $uid){
                    $this->__message('团购订单不属于你，请重试', $tuan_url);
                    return;
                }
                if($order_type != CART_ITEM_TYPE_TUAN){
                    $this->__message('该订单不属于团购订单，请重试', $tuan_url);
                    return;
                }
                if(!empty($cart_info['Cart']['order_id'])){
                    throw new CakeException("cart order id error ");
                }
                $total_price = $cart_info['Cart']['num'] * $cart_info['Cart']['price'];
                if($total_price <= 0 ){
                    throw new CakeException("error tuan order price ");
                }
                $pid = $cart_info['Cart']['product_id'];
                $area = '';
                $tuan_info = $this->Tuan->findById($tuan_id);
                $address = $tuan_info['Tuan']['address'];

                $order = $this->Order->createTuanOrder($tuan_id, $uid, $total_price, $pid, $order_type, $area, $address, $mobile, $name, $cart_id);
                if ($order['Order']['status'] != ORDER_STATUS_WAITING_PAY) {
                    $this->__message('您已经支付过了', $tuan_url);
                    return;
                }else{
                    $res = array('success'=> true, 'order_id'=>$order['Order']['id']);
                    echo json_encode($res);
                }
            }
        }
    }

}