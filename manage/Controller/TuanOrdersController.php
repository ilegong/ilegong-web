<?php
class TuanOrdersController extends AppController{

    var $name = 'TuanOrders';

    var $uses = array('Order', 'TuanTeam', 'TuanBuying', 'Location', 'TuanProduct', 'OfflineStore', 'Oauthbind', 'OrderMessage', 'User','ProductTry','Cart');
    var $order_msg_log = array(1=>"paid", 2 => 'py-reach');
    public function admin_ship_to_pys_stores(){
        $this->autoRender = false;

        $data=$_POST;
        if(empty($data)){
            echo json_encode(array('success' => true, 'res' => array(), 'already'=>array()));
            return;
        }

        $order_ids = $data['ids'];
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'id'=> $order_ids
            ),
        ));
        // all orders must be paid or
        foreach($orders as &$order){
            if(!in_array($order['Order']['status'], array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED))){
                echo json_encode(array('success' => false, 'res' => 'order '.$order['Order']['id'].' status is '.$order['Order']['status']));
                return;
            }
        }

        // TODO: validate send_date of carts should be the same day

        $validate_res = $this->_validate_py_store($orders);
        if($validate_res['success']){
            $offline_stores = $validate_res['data'];
        }else{
            return $validate_res;
        }

        $user_ids = Hash::extract($orders, '{n}.Order.creator');
        $oauth_binds = $this->Oauthbind->find('list', array(
            'conditions' => array( 'user_id' => $user_ids, 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));

        $arrived_log = $this->OrderMessage->find('all', array(
            'conditions' => array('order_id' => $order_ids, 'status' => 0, 'type' => 'py-reach')
        ));
        $arrived_order_ids = Hash::extract($arrived_log, '{n}.OrderMessage.order_id');

        $this->log('ship to pys stores: set status to shipped for orders: '.json_encode($order_ids));
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED),array('id' => $order_ids));
        $this->loadModel('Cart');
        $this->Cart->updateAll(array('status' => ORDER_STATUS_SHIPPED),array('order_id' => $order_ids));

        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => array_diff($order_ids, $arrived_order_ids)
            ),
            'fields' => array('id', 'order_id', 'product_id', 'status', 'send_date')
        ));

        $this->loadModel('Product');
        $products = $this->Product->find('all', array(
            'conditions' => array(
                'id' => array_unique(Hash::extract($carts, '{n}.Cart.product_id'))
            ),
            'fields' => array('id', 'name', 'product_alias')
        ));
        $products = Hash::combine($products, '{n}.Product.id', '{n}');

        $success = array();
        foreach($orders as &$order){
            if(in_array($order['Order']['id'], $arrived_order_ids)){
                continue;
            }
            $order_carts = array_filter($carts, function($cart) use ($order){
                return $cart['Cart']['order_id'] == $order['Order']['id'];
            });
            $product_name = $this->_get_product_name($order_carts, $products);
            $offline_store = $offline_stores[$order['Order']['consignee_id']];
            $tipMsg = '已经到达自提点，生鲜娇贵，请尽快取货哈。';
            if($offline_store['OfflineStore']['can_remark_address']==1){
                //这个自提点支持送货上门,不发送取货消息
                //continue;
                $tipMsg = '已经到达自提点，自提点将尽快为您配货。';
            }
            if($offline_store['OfflineStore']['owner_phone']){
                $tipMsg = $tipMsg.'自提点联系电话: '.$offline_store['OfflineStore']['owner_phone'];
            }
            $post_data = array(
                "touser" => $oauth_binds[$order['Order']['creator']],
                "template_id" => '3uA5ShDuM6amaaorl6899yMj9QvBmIiIAl7T9_JfR54',
                "url" => WX_HOST . '/orders/detail/'.$order['Order']['id'],
                "topcolor" => "#FF0000",
                "data" =>  array(
                    "first" => array("value" => "亲，您订购的".$product_name.$tipMsg),
                    "keyword1" => array("value" => $order['Order']['id']),
                    "keyword2" => array("value" => $offline_store['OfflineStore']['alias']),
                    "keyword3" => array("value" => $order['Order']['consignee_address']),
                    "remark" => array("value" => "感谢您的支持".$offline_store['OfflineStore']['owner_phone'], "color" => "#FF8800")
                )
            );
            $this->log('ship to pys stores: send weixin message for order: '.$order['Order']['id'].': '.json_encode($post_data));
            $wx_send_status = send_weixin_message($post_data);
            $this->OrderMessage->create();
            if($wx_send_status){
                $success[] = $order['Order']['id'];
                $this->OrderMessage->save(array('order_id' => $order['Order']['id'], 'status' => 0, 'type'=>'py-reach'));
            }else{
                $this->log("ship to pys stores: failed to send weixin message for order ".$order['Order']['id']);
                $fail[] = $order['Order']['id'];
                $this->OrderMessage->save(array('order_id' => $order['Order']['id'], 'status' => 1, 'type'=>'py-reach'));
            }
            if($offline_store['OfflineStore']['can_remark_address']==1){
                $msg = "亲，您订购的".$product_name."已经到达".$offline_store['OfflineStore']['alias']."自提点(".$offline_store['OfflineStore']['owner_phone'].")，自提点将尽快为您配货。确认收货可得积分。";
            }else{
                $msg = "亲，您订购的".$product_name."已经到达".$offline_store['OfflineStore']['alias']."自提点(".$offline_store['OfflineStore']['owner_phone'].")，生鲜娇贵，请尽快取货。确认收货可得积分。";
            }
            $this->_send_phone_msg($order['Order']['creator'], $order['Order']['consignee_mobilephone'], $msg, false);
        }

        echo json_encode(array('success' => true, 'res' => $success, 'already'=> $arrived_order_ids));
    }
    public function admin_send_by_pys_stores(){
        $this->autoRender = false;
        $data=$_POST;
        if(empty($data)){
            echo json_encode(array('success' => true, 'res' => array()));
            return;
        }
        $order_ids = $data['ids'];
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'id'=> $order_ids,
                'status' => ORDER_STATUS_PAID
            ),
        ));
        if(empty($orders)){
            echo json_encode(array('success' => true, 'res' => array()));
            return;
        }

        $order_ids = Hash::extract($orders, '{n}.Order.id');
        // validate goods should be shipped to pys stores
        $validate_res = $this->_validate_py_store($orders);
        if($validate_res['success']){
            $offline_stores = $validate_res['data'];
        }else{
            return $validate_res;
        }
        $user_ids = Hash::extract($orders, '{n}.Order.creator');
        $oauth_binds = $this->Oauthbind->find('list', array(
            'conditions' => array( 'user_id' => $user_ids, 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));

        $this->log('ship to pys stores: set status to shipped for orders: '.json_encode($order_ids));
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED),array('id' => $order_ids));

        $this->loadModel('Cart');
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $order_ids
            ),
            'fields' => array('id', 'order_id', 'product_id', 'status', 'send_date')
        ));

        $this->loadModel('Product');
        $products = $this->Product->find('all', array(
            'conditions' => array(
                'id' => array_unique(Hash::extract($carts, '{n}.Cart.product_id'))
            ),
            'fields' => array('id', 'name', 'product_alias')
        ));
        $products = Hash::combine($products, '{n}.Product.id', '{n}');

        $success = array();
        $fail = array();
        foreach($orders as &$order){
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
    public function _validate_py_store($orders){

        // validate goods should be shipped to pys stores
        $offline_store_ids = array_unique(Hash::extract($orders, '{n}.Order.consignee_id'));
        $this->log('will query offline stores: '.json_encode($offline_store_ids));
        if(!empty($offline_store_ids)){
            $offline_stores = $this->OfflineStore->find('all', array(
                'conditions' => array('id'=>$offline_store_ids)
            ));
            $offline_stores = Hash::combine($offline_stores, '{n}.OfflineStore.id', '{n}');
        }

        foreach($orders as &$order){
            $offline_store = $offline_stores[$order['Order']['consignee_id']];
            if(empty($offline_store)){
                return array('success' => false, 'res' => 'order '.$order['Order']['id'].' has no offline store');
            }
            if($offline_store['OfflineStore']['type'] == OFFLINE_STORE_HAOLINJU){
                return array('success' => false, 'res' => 'order '.$order['Order']['id'].' is sent to haolinju');
            }
        }
        return array('success' => true, 'data' => $offline_stores);
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
        $offline_store = $this->OfflineStore->find('first', array(
            'conditions' => array('id'=>$order['Order']['consignee_id'])
        ));
        if(empty($offline_store)){
            echo json_encode(array('success' => false, 'res' => 'order '.$order['Order']['id'].' has no offline store'));
            return;
        }
        if($offline_store['OfflineStore']['type'] != OFFLINE_STORE_HAOLINJU){
            echo json_encode(array('success' => false, 'res' => 'order '.$order['Order']['id'].' is not sent to haolinju'));
            return;
        }

        $this->loadModel('Cart');
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $order_id
            ),
            'fields' => array('id', 'order_id', 'product_id', 'status', 'send_date')
        ));

        $this->loadModel('Product');
        $products = $this->Product->find('all', array(
            'conditions' => array(
                'id' => array_unique(Hash::extract($carts, '{n}.Cart.product_id'))
            ),
            'fields' => array('id', 'name', 'product_alias')
        ));
        $products = Hash::combine($products, '{n}.Product.id', '{n}');
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
        $order = $this->Order->findById($order_id);

		if($order['Order']['status'] != ORDER_STATUS_PAID){
			echo json_encode(array('success'=>false,'res'=>'订单状态不正确'));
			exit;
		}
        $update_status =  $this->Order->updateAll(array('status'=> ORDER_STATUS_SHIPPED,'ship_code'=>"'".addslashes($ship_code)."'",'ship_type'=>$ship_type,
            'lastupdator'=> 0),array('id'=>$order_id));
        //add weixin message
        if(!$update_status){
            echo json_encode(array('success'=>false,'res'=>'更新订单状态失败'));
            return;
        }
        echo json_encode(array('success' => true, 'res' => '订单状态已更新'));

        $this->loadModel('Cart');
        $this->Cart->updateAll(array('status'=> ORDER_STATUS_SHIPPED), array('order_id'=>$order_id));

        $oauth_bind = $this->Oauthbind->find('first', array(
            'conditions' => array( 'user_id' => $order['Order']['creator'], 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));
        if(empty($oauth_bind)){
            echo json_encode(array('success' => true, 'res' => '订单状态已更新'));
            return;
        }

        $good = $this->_get_order_good_info($order_id);
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
                "keyword3" => array("value" => $good['good_info']),
                "keyword4" => array("value" => $good['good_number']),
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

    private function _get_order_good_info($order_id){
        $info ='';
        $number =0;
        $this->loadModel('Cart');
        $carts = $this->Cart->find('all',array('conditions'=>array('order_id' => $order_id)));
        $this->loadModel('Product');
        $products = $this->Product->find('all', array('conditions'=>array('id' => array_unique(Hash::extract($carts, '{n}.Cart.product_id')))));
        $products = Hash::combine($products, '{n}.Product.id', '{n}');
        foreach($carts as $cart){
            $product = $products[$cart['Cart']['product_id']];
            $product_name = empty($product['Product']['product_alias']) ? $product['Product']['name'] : $product['Product']['product_alias'];
            $info = $info.$product_name.'x'.$cart['Cart']['num'];
            $number +=$cart['Cart']['num'];
        }
        return array("good_info"=>$info,"good_number"=>$number);
    }
    public function _send_phone_msg($order_creator, $consignee_mobilephone, $msg, $wx_send_status = true){
        $mobilephone = '';
        $user_info = $this->User->find('first', array(
            'conditions' => array('id' => $order_creator),
            'fields' => array('id','mobilephone')
        ));
        $user_mobilephone = $user_info['User']['mobilephone'];
        if(empty($consignee_mobilephone)){
            if(!empty($user_mobilephone) && !$wx_send_status ){
                $mobilephone = $user_mobilephone;
            }
        }else{
            if($consignee_mobilephone == $user_mobilephone){
                if(!$wx_send_status){
                    $mobilephone = $consignee_mobilephone;
                }
            }else{
                if($wx_send_status){
                    $mobilephone = $consignee_mobilephone;
                }else{
                    $mobilephone = $user_mobilephone;
                }
            }
        }
        if(!empty($mobilephone)){
            message_send($msg, $mobilephone);
        }
    }

    private function _get_product_name($order_carts, $products){
        return implode(array_unique(array_map(function($cart) use ($products){
            $product = $products[$cart['Cart']['product_id']];
            return empty($product['Product']['product_alias']) ? $product['Product']['name'] : $product['Product']['product_alias'];
        }, $order_carts)), ', ');
    }

    public function admin_on_order_status_change($orders=null){
        $this->send_msg($orders);
    }

    public function send_msg($orders){
        $user_ids = Hash::extract($orders, '{n}.Order.creator');
        $order_ids = Hash::extract($orders, '{n}.Order.id');
        $oauth_binds = $this->Oauthbind->find('list', array(
            'conditions' => array( 'user_id' => $user_ids, 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));
        $arrived_log = $this->OrderMessage->find('all', array(
            'conditions' => array('order_id' => $order_ids, 'status' => 0)
        ));
        $join_conditions = array(
            array(
                'table' => 'products',
                'alias' => 'Product',
                'conditions' => array(
                    'Cart.product_id = Product.id',
                ),
                'type' => 'LEFT',
            )
        );
        $carts = $this->Cart->find('all', array(
            'conditions' => array('order_id' => $order_ids),
            'joins' => $join_conditions,
            'fields' => array('Cart.id','Cart.num','Cart.order_id','Cart.send_date','Product.product_alias', 'Product.name'),
        ));
        foreach($orders as $order){
            $openid = $oauth_binds[$order['Order']['id']];
            if(!$this->in_arrived_log($order['Order']['id'], $arrived_log,$order['Order']['status'])){
                $this->send_wx_msg_sms($openid,$order, $carts);
            }
        }
    }
    public function send_wx_msg_sms($openid,$order, $carts){
        if($order['Order']['status'] == ORDER_STATUS_PAID){
            $this->_pay_done_wx_msg($order,$openid);
            $this->_pay_done_sms($order);
        }elseif($order['Order']['status'] ==ORDER_STATUS_SHIPPED){
            $this->_goods_shipped_wx_msg($order,$openid);
        }else{
            $this->log('invalid order status change, order_id:' . $order['Order']['id'].',status:'. $order['Order']['status']);
        }
    }
    private function in_arrived_log($order_id,$arrived_log,$status){
        foreach($arrived_log as $log){
            if($log['OrderMessage']['id'] == $order_id && $log['OrderMessage']['type'] == $this->order_msg_log[$status]){
                return true;
            }
        }
        return false;
    }
    public function _wx_msg_send_and_log($post_data,$order,$type){
        $this->OrderMessage->create();
        if(send_weixin_message($post_data)){
            $this->OrderMessage->save(array('order_id' => $order['Order']['id'], 'status' => 0, 'type'=>$type));
        }else{
            $this->OrderMessage->save(array('order_id' => $order['Order']['id'], 'status' => 1, 'type'=>$type));
        }
    }
    public function _pay_done_wx_msg($order,$openid){
        $product_name ="";
        $post_data = array(
            "touser" => $openid,
            "template_id" => 'UJvs1MAnfA7ATAiXVN0w122E53BauMSw8iCt0M2mSBQ',
            "url" => WX_HOST . '/orders/detail/'.$order['Order']['id'],
            "topcolor" => "#FF0000",
            "data" =>  array(
                "first" => array("value" => "您的订单支付成功，我们会按照订单中预计的时间为你发货"),
                "orderProductPrice" => array("value" => $order['Order']['total_price']),
                "orderProductName" => array("value" => $product_name),
                "orderAddress" => array("value" => $order['Order']['consignee_address']),
                "orderName" => array("value" => $order['Order']['id']),
                "remark" => array("value" => "点击查看订单详情", "color" => "#FF8800")
            )
        );
        $this->_wx_msg_send_and_log($post_data, $order, "paid");
    }
    public function _goods_shipped_wx_msg($order,$openid){

    }
    public function _pay_done_sms($order){
        $msg = "";
        $mobilephone = $order['Order']['consignee_mobilephone'];
        if($order['Order']['ship_mark'] == "ziti"){

        }elseif($order['Order']['ship_mark'] == "kuaidi"){
            $msg = "【朋友说】您的订单付款成功，订单号".$order['Order']['id']."，我们会按照订单中预计的时间为您发货！";
        }else{
            return false;
        }
        message_send($msg, $mobilephone);
    }
}