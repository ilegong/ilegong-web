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
		print_r($searchoptions);
		return $searchoptions;
	}	
	
}