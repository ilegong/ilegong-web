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
}