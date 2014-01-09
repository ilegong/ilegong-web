<?php

class CartsController extends AppController{
	
	var $name = 'Carts';
	
	protected function _custom_list_option($searchoptions){
		print_r($searchoptions);
		exit;
		return $searchoptions;
	}	
	
}