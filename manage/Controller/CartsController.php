<?php

class CartsController extends AppController{
	
	var $name = 'Carts';
	
	protected function _custom_list_option($searchoptions){
		/*连接Order表，获取收获人信息。 */
		$searchoptions['fields'][] = 'Order.*';
		$searchoptions['joins'][] = array(
			'table'=>'orders',
			'alias'=>'Order',
			'type'=> 'left',
			'conditions'=>array('Cart.order_id=Order.id'),		
		);
		//print_r($searchoptions);
		return $searchoptions;
	}

    public function admin_edit2($id = null)
    {
        $this->loadModel('Order');
        $this->loadModel('Cart');

        $cart = $this->Cart->findById($id);
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'id' => $cart['Cart']['order_id']
            )
        ));
        $this->set('order', $order);
        $this->set('cart', $cart);
    }

    public function admin_update2($id)
    {
        $this->autoRender = false;

        $username = $this->currentUser['username'];
        $user_agent = $this->request->header('User-Agent');
        $user_ip = $this->request->clientIp(true);
        $this->log('user ' . $username . ' is to update cart ' . $id . ', request ip ' . $user_ip . ', user_agent ' . $user_agent);

        $this->loadModel('Order');
        $this->loadModel('Cart');
        $cart = $this->Cart->findById($id);
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'id' => $cart['Cart']['order_id']
            )
        ));
        if (empty($cart)) {
            echo json_encode(array('success' => false, 'reason' => 'cart_not_exists'));
            return;
        }
        if (empty($order)) {
            echo json_encode(array('success' => false, 'reason' => 'order_not_exists'));
            return;
        }

        if(!has_permission_to_modify_order($this->data['modify_user'])){
            echo json_encode(array('success' => false, 'reason' => 'no_permission'));
            return;
        }

        $remark = (empty($order['Order']['remark']) ? '' : $order['Order']['remark'] . ', ') . $this->data['modify_reason'] . '(' . $this->data['modify_user'] . ')';
        unset($this->data['remark']);
        unset($this->data['modify_user']);

        if(empty($this->data)){
            echo json_encode(array('success' => false, 'reason' => 'fields_are_empty'));
            return;
        }

        $new_cart_status = $this->data['status'];

        foreach($this->data as $key => $value){
            $this->data[$key] = "'".$value."'";
        }

        $this->log('update cart ' . $id . ': '.json_encode($this->data));
        if(!$this->Cart->updateAll($this->data, array('id' => $id))){
            echo json_encode(array('success' => false, 'reason' => 'failed_to_save_cart'));
            return;
        }

        $order_data = array('remark' => "'".$remark."'");
        if($this->_need_to_update_order_status($order['Order']['id'], $new_cart_status)){
            $order_data['status'] = "'".$new_cart_status."'";
        }
        $this->log('update order ' . $order['Order']['id'] . ': '.json_encode($order_data));
        $this->Order->updateAll($order_data, array('id' => $order['Order']['id']));

        echo json_encode(array('success' => true));
    }

    private function _need_to_update_order_status($order_id, $new_cart_status){
        if(empty($new_cart_status)){
            return false;
        }
        $carts = $this->Cart->find('all', array(
            'conditions'=>array(
                'order_id' => $order_id
            )
        ));
        foreach($carts as &$cart){
            if($cart['Cart']['status'] != $new_cart_status){
                return false;
            }
        }
        return true;
    }
    public function admin_multi_update(){
        $this->autoRender = false;
        $username = $this->currentUser['username'];
        $user_agent = $this->request->header('User-Agent');
        $user_ip = $this->request->clientIp(true);
        if(empty($this->data['carts'])){
            echo json_encode(array('success' => false, 'reason' => 'fields_are_empty'));
            return;
        }
        $ids = Hash::extract($this->data['carts'], '{n}.id');
        $this->log('user ' . $username . ' is to update cart ' . $ids . ', request ip ' . $user_ip . ', user_agent ' . $user_agent);
        list($carts,$order) = $this->_get_order_cart_info($ids);
        if (empty($carts)) {
            echo json_encode(array('success' => false, 'reason' => 'cart_not_exists'));
            return;
        }
        if (empty($order)) {
            echo json_encode(array('success' => false, 'reason' => 'order_not_exists'));
            return;
        }
        if(!has_permission_to_modify_order($this->data['modify_user'])){
            echo json_encode(array('success' => false, 'reason' => 'no_permission'));
            return;
        }
        unset($this->data['modify_user']);
        $new_cart_status = $this->data['carts'][0]['status'];
        $this->log('update cart ' . $ids . ': '.json_encode($this->data));
        $save_res = $this->Cart->saveMany($this->data['carts']);
        if(empty($save_res)){
            echo json_encode(array('success' => false, 'reason' => 'failed_to_save_cart'));
        }
        if($this->_need_to_update_order_status($order['Order']['id'], $new_cart_status)){
            $order_data['status'] = "'".$new_cart_status."'";
        }
        $this->log('update order ' . $order['Order']['id'] . ': '.$new_cart_status);
        echo json_encode(array('success' => true));
    }
    public function _get_order_cart_info($ids){
        $this->loadModel('Order');
        $this->loadModel('Cart');
        $carts = $this->Cart->find('all', array(
            'conditions' => array('id' => $ids)
        ));
        if(count($carts)<1){
            $this->log("cart not exist");
            return false;
        }
        $order = $this->Order->find('first', array(
            'conditions' => array(
                'id' => $carts[0]['Cart']['order_id']
            )
        ));
        return array($carts, $order);
    }
}