<?php
class TuanOrdersController extends AppController{

    var $name = 'TuanOrders';

    var $uses = array('Order', 'Cart', 'Product', 'TuanTeam', 'TuanBuying', 'Location', 'TuanProduct', 'OfflineStore', 'Oauthbind', 'OrderMessage', 'User','ProductTry');
    public function admin_ship_to_pys_stores(){
        $this->autoRender = false;

        $data=$_POST;
        if(empty($data)){
            echo json_encode(array('success' => true, 'res' => array(), 'already'=>array()));
            return;
        }

        try{
            $orders = $this->_get_orders($data['ids'], array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED));
            $order_ids = Hash::extract($orders, '{n}.Order.id');
            $carts = $this->_get_carts_of_orders($orders);
            $offline_stores = $this->_validate_offline_stores($orders, OFFLINE_STORE_PYS);
            $products = $this->_get_products($carts);
            $oauth_binds = $this->_get_oauth_binds($orders);
            $arrived_logs = $this->_get_arrived_logs($order_ids, 'py-reach');
            $arrived_order_ids = Hash::extract($arrived_logs, '{n}.OrderMessage.order_id');
        }
        catch(Exception $e){
            echo json_encode(array('success' => false, 'res' => $e->getMessage()));
            return;
        }

        $this->log('goods arrived at pys stores: set status to shipped for orders: '.json_encode($order_ids));
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED),array('id' => $order_ids));
        $this->Cart->updateAll(array('status' => ORDER_STATUS_SHIPPED),array('order_id' => $order_ids));

        $success = array();
        $fail = array();
        foreach($orders as &$order){
            if(in_array($order['Order']['id'], $arrived_order_ids)){
                continue;
            }
            $order_carts = $this->_get_order_carts($carts, $order);
            $product_name = $this->_get_product_name($order_carts, $products);
            $offline_store = $offline_stores[$order['Order']['consignee_id']];
            $wx_message = $this->_get_wx_message($product_name, $offline_store);

            $post_data = array(
                "touser" => $oauth_binds[$order['Order']['creator']],
                "template_id" => '3uA5ShDuM6amaaorl6899yMj9QvBmIiIAl7T9_JfR54',
                "url" => WX_HOST . '/orders/detail/'.$order['Order']['id'],
                "topcolor" => "#FF0000",
                "data" =>  array(
                    "first" => array("value" => $wx_message),
                    "keyword1" => array("value" => $order['Order']['id']),
                    "keyword2" => array("value" => $offline_store['OfflineStore']['alias']),
                    "keyword3" => array("value" => $order['Order']['consignee_address']),
                    "remark" => array("value" => "感谢您的支持".$offline_store['OfflineStore']['owner_phone'], "color" => "#FF8800")
                )
            );
            $this->log('goods arrived at pys stores: send weixin message for order: '.$order['Order']['id'].': '.json_encode($post_data));
            $wx_send_status = send_weixin_message($post_data);
            $this->OrderMessage->create();
            if($wx_send_status){
                $success[] = $order['Order']['id'];
                $this->OrderMessage->save(array('order_id' => $order['Order']['id'], 'status' => 0, 'type'=>'py-reach'));
            }else{
                $this->log("goods arrived at pys stores: failed to send weixin message for order ".$order['Order']['id']);
                $fail[] = $order['Order']['id'];
                $this->OrderMessage->save(array('order_id' => $order['Order']['id'], 'status' => 1, 'type'=>'py-reach'));
            }
            $sms_message = $this->_get_sms_message($product_name, $offline_store);
            $this->_send_phone_msg($order['Order']['creator'], $order['Order']['consignee_mobilephone'], $sms_message, false);
        }

        echo json_encode(array('success' => true, 'res' => $success, 'already'=> $arrived_order_ids,'fail' => $fail));
    }

    public function admin_send_by_pys_stores(){
        $this->autoRender = false;
        $data=$_POST;
        if(empty($data)){
            echo json_encode(array('success' => true, 'res' => array()));
            return;
        }

        try{
            $orders = $this->_get_orders($data['ids'], array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED));
            $order_ids = Hash::extract($orders, '{n}.Order.id');
            $offline_stores = $this->_validate_offline_stores($orders, OFFLINE_STORE_PYS);
            $carts = $this->_get_carts_of_orders($orders);
            $products = $this->_get_products($carts);
            $oauth_binds = $this->_get_oauth_binds($orders);
            $arrived_logs = $this->_get_arrived_logs($order_ids, 'py-send-out');
            $arrived_order_ids = Hash::extract($arrived_logs, '{n}.OrderMessage.order_id');
        }
        catch(Exception $e){
            echo json_encode(array('success' => false, 'res' => $e->getMessage()));
            return;
        }

        $this->log('goods shipped to pys stores: set status to shipped for orders: '.json_encode($order_ids));
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED),array('id' => $order_ids));

        $success = array();
        $fail = array();
        foreach($orders as &$order){
            if(in_array($order['Order']['id'], $arrived_order_ids)){
                continue;
            }

            $offline_store = $offline_stores[$order['Order']['consignee_id']];
            $order_carts = array_filter($carts, function($cart) use ($order){
                return $cart['Cart']['order_id'] == $order['Order']['id'];
            });
            $product_name = $this->_get_product_name($order_carts, $products);

            $post_data = array(
                "touser" => $oauth_binds[$order['Order']['creator']],
                "template_id" => 'UJvs1MAnfA7ATAiXVN0w122E53BauMSw8iCt0M2mSBQ',
                "url" => WX_HOST . '/orders/detail/'.$order['Order']['id'],
                "topcolor" => "#FF0000",
                "data" =>  array(
                    "first" => array("value" => "亲，您订购的".$product_name."已经在路上啦，大概下午五点前后到达，亲不要着急，到达后，我们会第一时间通知你。"),
                    "orderProductPrice" => array("value" => $order['Order']['total_price']),
                    "orderProductName" => array("value" => $product_name),
                    "orderAddress" => array("value" => $order['Order']['consignee_address']),
                    "orderName" => array("value" => $order['Order']['id']),
                    "remark" => array("value" => "感谢您的支持".$offline_store['OfflineStore']['owner_phone'], "color" => "#FF8800")
                )
            );
            $this->log('ship to pys stores: send weixin message for order: '.$order['Order']['id'].': '.json_encode($post_data));
            $wx_send_status = send_weixin_message($post_data);
            $this->OrderMessage->create();
            if($wx_send_status){
                $success[] = $order['Order']['id'];
                $this->OrderMessage->save(array('order_id' => $order['Order']['id'], 'status' => 0, 'type'=>'py-send-out'));
            }else{
                $this->log("ship to pys stores: failed to send weixin message for order ".$order['Order']['id']);
                $fail[] = $order['Order']['id'];
            }
            $msg = "亲，您订购的".$product_name."已经出库，请留意到店短信。";
            $this->_send_phone_msg($order['Order']['creator'], $order['Order']['consignee_mobilephone'], $msg, false);
        }
        echo json_encode(array('success' => true, 'res' => $success, 'fail' => $fail));
    }

    public function _extract_tryid_tuanbuyid($orders){
        $tryids = array();
        $tuanbuyids = array();
        foreach ($orders as $order) {
            if($order['Order']['type']==5){
                //tuan buy product
                $tuanbuyids[] = $order['Order']['member_id'];
            }
            if($order['Order']['type']==6){
                //sec kill product
                $tryids[] = $order['Order']['try_id'];
            }
        }
        $tryids = array_unique($tryids);
        $tuanbuyids = array_unique($tuanbuyids);
        return array('tuanbuyids'=>$tuanbuyids,'tryids'=>$tryids);
    }

    public function admin_ship_to_haolinju_store(){
        $this->autoRender = false;

        $data=$_POST;
        $order_id = $data['orderId'];
        $send_weixin_message = $data['sendWeixinMessage'];
        $haolinju_code = $data['haolinjuCode'];
        if(empty($order_id)){
            echo json_encode(array('success' => false, 'res' => 'order is empty'));
            return;
        }
        if($send_weixin_message && empty($haolinju_code)){
            echo json_encode(array('success' => false, 'res' => 'send weixin message, but haolinju code is empty'));
            return;
        }

        $order = $this->Order->find('first', array(
            'conditions' => array(
                'id'=> $order_id
            ),
        ));
        // all orders must be paid
        if($order['Order']['status'] != ORDER_STATUS_PAID){
            echo json_encode(array('success' => false, 'res' => 'order '.$order['Order']['id'].' status is '.$order['Order']['status']));
            return;
        }

        // validate goods should be shipped to pys stores
        $offline_stores = $this->_validate_offline_stores(array($order), OFFLINE_STORE_HAOLINJU);
        $offline_store = $offline_stores[0];

        $carts = $this->_get_carts_of_orders(array($order), false);

        $products = $this->_get_products($carts);
        $product_name = $this->_get_product_name($carts, $products);

        $oauth_bind = $this->Oauthbind->find('first', array(
            'conditions' => array( 'user_id' => $order['Order']['creator'], 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));

        $this->log('ship to haolinju store: set status to shipped for order: '.json_encode($order['Order']['id']));
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED),array('id' => $order['Order']['id']));

        if($send_weixin_message){
            $post_data = array(
                "touser" => $oauth_bind['Oauthbind']['oauth_openid'],
                "template_id" => '3uA5ShDuM6amaaorl6899yMj9QvBmIiIAl7T9_JfR54',
                "url" => WX_HOST . '/orders/detail/'.$order['Order']['id'],
                "topcolor" => "#FF0000",
                "data" =>  array(
                    "first" => array("value" => "亲，您订购的".$product_name."已经到达自提点，提货码：".$haolinju_code."，生鲜娇贵，请尽快取货哈。"),
                    "keyword1" => array("value" => $order['Order']['id']),
                    "keyword2" => array("value" => $offline_store['OfflineStore']['alias']),
                    "keyword3" => array("value" => $order['Order']['consignee_address']),
                    "remark" => array("value" => "感谢您的支持，现场提货遇到任何问题请拨打电话：4000-508-528", "color" => "#FF8800")
                )
            );
            $this->log('ship to haolinju store: send weixin message for order: '.$order['Order']['id'].': '.json_encode($post_data));
            $this->OrderMessage->create();
            if(send_weixin_message($post_data)){
                echo json_encode(array('success' => true, 'res' => $order['Order']['id']));
                $this->OrderMessage->save(array('order_id' => $order['Order']['id'], 'status' => 0, 'type'=>'hlj-reach', 'data'=>$haolinju_code));
            }else{
                $this->log("ship to haolinju store: failed to send weixin message for order ".$order['Order']['id']);
                echo json_encode(array('success' => true, 'res' => ''));
            }
            $msg = "亲，您订购的".$product_name."已经到达".$offline_store['OfflineStore']['alias']."自提点，提货码：".$haolinju_code.",生鲜娇贵，请尽快取货。";
            $this->_send_phone_msg($order['Order']['creator'], $order['Order']['consignee_mobilephone'], $msg, false);
        }
        else{
            echo json_encode(array('success' => true, 'res' => $order['Order']['id']));
        }
    }

    public function admin_input_ordinary_ship_code(){
        $this->autoRender = false;
		$order_id = $_REQUEST['orderId'];
		$ship_type = $_REQUEST['shipType'];
        $ship_code = $_REQUEST['shipCode'];
		if(empty($order_id) || empty($ship_type) || empty($ship_code)){
			echo json_encode(array('success'=>false,'res'=>'参数错误'));
			exit;
		}

        try{
            $orders = $this->_get_orders(array($order_id), array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED));
            $order = $orders[0];
            $carts = $this->_get_carts_of_orders(array($order), false);
            $products = $this->_get_products($carts);
        }
        catch(Exception $e){
            echo json_encode(array('success' => false, 'res' => $e->getMessage()));
            return;
        }

        $update_status =  $this->Order->updateAll(array('status'=> ORDER_STATUS_SHIPPED,'ship_code'=>"'".addslashes($ship_code)."'",'ship_type'=>$ship_type,
            'lastupdator'=> 0),array('id'=>$order_id));
        //add weixin message
        if(!$update_status){
            echo json_encode(array('success'=>false,'res'=>'更新订单状态失败'));
            return;
        }

        $this->Cart->updateAll(array('status'=> ORDER_STATUS_SHIPPED), array('order_id'=>$order_id));

        $oauth_bind = $this->Oauthbind->find('first', array(
            'conditions' => array( 'user_id' => $order['Order']['creator'], 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));
        if(empty($oauth_bind)){
            echo json_encode(array('success' => true, 'res' => '订单状态已更新'));
            return;
        }

        $number =0;
        $info ='';
        foreach($carts as $cart){
            $product = $products[$cart['Cart']['product_id']];
            $product_name = empty($product['Product']['product_alias']) ? $product['Product']['name'] : $product['Product']['product_alias'];
            $info = $info.$product_name.'x'.$cart['Cart']['num'];
            $number +=$cart['Cart']['num'];
        }

        $ship_type_list = ShipAddress::ship_type_list();
        $post_data = array(
            "touser" => $oauth_bind['Oauthbind']['oauth_openid'],
            "template_id" => '87uu4CmlZT-xlZGO45T_XTHiFYAWHQaLv94iGuH-Ke4',
            "url" => WX_HOST . '/orders/detail/' . $order_id,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => "亲，您的特产已经从家乡启程啦。"),
                "keyword1" => array("value" => $ship_type_list[$ship_type]),
                "keyword2" => array("value" => $ship_code),
                "keyword3" => array("value" => $info),
                "keyword4" => array("value" => $number),
                "remark" => array("value" => "点击查看订单详情。", "color" => "#FF8800")
            )
        );
        if(send_weixin_message($post_data)){
            echo json_encode(array('success' => true, 'res' => '订单状态已更新，微信模版消息发送成功'));
        }else{
            $this->log("ship code B2C: failed to send weixin message for express".$order['Order']['id']);
            echo json_encode(array('success' => true, 'res' => '订单状态已更新，模版消息发送失败'));
        }
	}

    private function _validate_offline_stores($orders, $expected_type){
        if(empty($orders)){
            return array();
        }

        // validate goods should be shipped to pys stores
        $offline_store_ids = array_unique(Hash::extract($orders, '{n}.Order.consignee_id'));
        $offline_stores = $this->OfflineStore->find('all', array(
            'conditions' => array('id'=>$offline_store_ids)
        ));
        $offline_stores = Hash::combine($offline_stores, '{n}.OfflineStore.id', '{n}');

        foreach($orders as &$order){
            $offline_store = $offline_stores[$order['Order']['consignee_id']];
            if(empty($offline_store)){
                throw new Exception('order '.$order['Order']['id'].' has no offline store');
            }
            if($offline_store['OfflineStore']['type'] != $expected_type){
                throw new Exception('order '.$order['Order']['id'].' is sent to invalid offline store');
            }
        }
        return $offline_stores;
    }
    private function _get_carts_of_orders($orders, $validate_send_date = true){
        if(empty($orders)){
            return array();
        }

        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => Hash::extract($orders, '{n}.Order.id')
            ),
            'fields' => array('id', 'order_id', 'product_id', 'status', 'send_date')
        ));

        if($validate_send_date){
            foreach($orders as &$order){
                $order_carts = $this->_get_order_carts($carts, $order);
                $order_carts_send_dates = array_unique(Hash::extract($order_carts, '{n}.Cart.send_date'));
                if(count($order_carts_send_dates) > 1){
                    throw new Exception('order '.$order['Order']['id'].' has various send dates');
                }
            }
        }
        return $carts;
    }

    private function _get_orders($order_ids, $expected_status){
        if(empty($order_ids)){
            throw new Exception('orders are empty');
        }

        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'id'=> $order_ids
            ),
        ));

        if(empty($orders)){
            throw new Exception('orders are empty');
        }

        // all orders must be paid or shipped
        foreach($orders as &$order){
            if(!in_array($order['Order']['status'], $expected_status)){
                throw new Exception('order '.$order['Order']['id'].' status is not expected: '.$order['Order']['status']);
            }
        }
        return $orders;
    }

    private function _get_order_carts($carts, $order){
        return array_filter($carts, function($cart) use ($order){
            return $cart['Cart']['order_id'] == $order['Order']['id'];
        });
    }
    public function _send_phone_msg($order_creator, $consignee_mobilephone, $msg, $wx_send_status = true){
        if(empty($consignee_mobilephone)){
            $user_info = $this->User->find('first', array(
                'conditions' => array('id' => $order_creator),
                'fields' => array('id','mobilephone')
            ));
            $consignee_mobilephone = $user_info['User']['mobilephone'];
        }

        if(!empty($consignee_mobilephone)){
            message_send($msg, $consignee_mobilephone);
        }
    }

    private function _get_product_name($order_carts, $products){
        return implode(array_unique(array_map(function($cart) use ($products){
            $product = $products[$cart['Cart']['product_id']];
            return empty($product['Product']['product_alias']) ? $product['Product']['name'] : $product['Product']['product_alias'];
        }, $order_carts)), ', ');
    }

    private function _get_wx_message($product_name, $offline_store){
        $message = "亲，您订购的".$product_name.'已经到达自提点';
        if($offline_store['OfflineStore']['can_remark_address']==1){
            $message = $message.'，自提点将尽快为您配货。';
        } else{
            $message = $message.'，生鲜娇贵，请尽快取货哈。';
        }
        if(!empty($offline_store['OfflineStore']['owner_phone'])){
            $message = $message.'自提点联系电话: '.$offline_store['OfflineStore']['owner_phone'];
        }
        return $message;
    }

    private function _get_sms_message($product_name, $offline_store){
        $message =  "亲，您订购的".$product_name."已经到达".$offline_store['OfflineStore']['alias']."自提点";
        if(!empty($offline_store['OfflineStore']['owner_phone'])){
            $message = $message.'('.$offline_store['OfflineStore']['owner_phone'].')';
        }

        if($offline_store['OfflineStore']['can_remark_address']==1){
            $message = $message."，自提点将尽快为您配货。";
        }else{
            $message = $message."，为了保证您吃到最新鲜的美味，请尽快接它回家吧！。";
        }
        return $message;
    }

    private function _get_products($carts){
        $product_ids = array_unique(Hash::extract($carts, '{n}.Cart.product_id'));
        if(empty($product_ids)){
            return array();
        }
        $products = $this->Product->find('all', array(
            'conditions' => array(
                'id' => $product_ids
            ),
            'fields' => array('id', 'name', 'product_alias')
        ));
        return Hash::combine($products, '{n}.Product.id', '{n}');
    }

    private function _get_oauth_binds($orders){
        if(empty($orders)){
            return array();
        }
        return $this->Oauthbind->find('list', array(
            'conditions' => array( 'user_id' => array_unique(Hash::extract($orders, '{n}.Order.creator')), 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));
    }

    private function _get_arrived_logs($order_ids, $type){
        if(empty($order_ids)){
            return array();
        }
        return $this->OrderMessage->find('all', array(
            'conditions' => array('order_id' => $order_ids, 'status' => 0, 'type' => $type)
        ));
    }
}