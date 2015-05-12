<?php
class TuanOrdersController extends AppController{

    var $name = 'TuanOrders';

    var $uses = array('Order', 'TuanTeam', 'TuanBuying', 'Location', 'TuanProduct', 'OfflineStore', 'Oauthbind', 'OrderMessage');

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
            if($order['Order']['status'] != ORDER_STATUS_PAID && $order['Order']['status'] != ORDER_STATUS_SHIPPED){
                echo json_encode(array('success' => false, 'res' => 'order '.$order['Order']['id'].' status is '.$order['Order']['status']));
                return;
            }
            if($order['Order']['type'] != 5 && $order['Order']['type'] != 6){
                echo json_encode(array('success' => false, 'res' => 'order '.$order['Order']['id'].' is of type '.$order['Order']['type']));
                return;
            }
        }

        $validate_res = $this->_validate_py_store($orders);
        if($validate_res['success']){
            $offline_stores = $validate_res['data'];
        }else{
            return $validate_res;
        }
        $tuan_products = $this->_find_product_alias($orders);

        $user_ids = Hash::extract($orders, '{n}.Order.creator');
        $oauth_binds = $this->Oauthbind->find('list', array(
            'conditions' => array( 'user_id' => $user_ids, 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));

        $arrived_log = $this->OrderMessage->find('all', array(
            'conditions' => array('order_id' => $order_ids, 'status' => 0, 'type' => 'py-reach')
        ));
        $arrived_order_ids = Hash::extract($arrived_log, '{n}.OrderMessage.order_id');
        $order_ids = array_diff($order_ids, $arrived_order_ids);

        $this->log('ship to pys stores: set status to shipped for orders: '.json_encode($order_ids));
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED),array('id' => $order_ids));
        $success = array();
        foreach($orders as &$order){
            if(in_array($order['Order']['id'], $arrived_order_ids)){
                continue;
            }

            $offline_store = $offline_stores[$order['Order']['consignee_id']];
            $post_data = array(
                "touser" => $oauth_binds[$order['Order']['creator']],
                "template_id" => '3uA5ShDuM6amaaorl6899yMj9QvBmIiIAl7T9_JfR54',
                "url" => WX_HOST . '/orders/detail/'.$order['Order']['id'],
                "topcolor" => "#FF0000",
                "data" =>  array(
                    "first" => array("value" => "亲，您订购的".$tuan_products[$order['Order']['member_id']]['alias']."已经到达自提点，生鲜娇贵，请尽快取货哈。"),
                    "keyword1" => array("value" => $order['Order']['id']),
                    "keyword2" => array("value" => $offline_store['OfflineStore']['alias']),
                    "keyword3" => array("value" => $order['Order']['consignee_address']),
                    "remark" => array("value" => "感谢您的支持".$offline_store['OfflineStore']['owner_phone'], "color" => "#FF8800")
                )
            );
            $this->log('ship to pys stores: send weixin message for order: '.$order['Order']['id'].': '.json_encode($post_data));
            if(send_weixin_message($post_data)){
                $success[] = $order['Order']['id'];
                $this->OrderMessage->save(array('order_id' => $order['Order']['id'], 'status' => 0, 'type'=>'py-reach'));
            }else{
                $this->log("ship to pys stores: failed to send weixin message for order ".$order['Order']['id']);
                $fail[] = $order['Order']['id'];
                $this->OrderMessage->save(array('order_id' => $order['Order']['id'], 'status' => 1, 'type'=>'py-reach'));
            }
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
        $tuan_products = $this->_find_product_alias($orders);
        $this->log('ship to pys stores: set status to shipped for orders: '.json_encode($order_ids));
        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED),array('id' => $order_ids));
        $success = array();
        $fail = array();
        foreach($orders as &$order){
            $offline_store = $offline_stores[$order['Order']['consignee_id']];
            $post_data = array(
                "touser" => $oauth_binds[$order['Order']['creator']],
                "template_id" => '3uA5ShDuM6amaaorl6899yMj9QvBmIiIAl7T9_JfR54',
                "url" => WX_HOST . '/orders/detail/'.$order['Order']['id'],
                "topcolor" => "#FF0000",
                "data" =>  array(
                    "first" => array("value" => "亲，您订购的".$tuan_products[$order['Order']['member_id']]['alias']."已经在路上啦，大概下午五点前后到达，亲不要着急，到达后，我们会第一时间通知你。"),
                    "keyword1" => array("value" => $order['Order']['id']),
                    "keyword2" => array("value" => $offline_store['OfflineStore']['alias']),
                    "keyword3" => array("value" => $order['Order']['consignee_address']),
                    "remark" => array("value" => "感谢您的支持".$offline_store['OfflineStore']['owner_phone'], "color" => "#FF8800")
                )
            );
            $this->log('ship to pys stores: send weixin message for order: '.$order['Order']['id'].': '.json_encode($post_data));
            if(send_weixin_message($post_data)){
                $success[] = $order['Order']['id'];
                $this->OrderMessage->save(array('order_id' => $order['Order']['id'], 'status' => 0, 'type'=>'py-send-out'));
            }else{
                $this->log("ship to pys stores: failed to send weixin message for order ".$order['Order']['id']);
                $fail[] = $order['Order']['id'];
            }
        }
        echo json_encode(array('success' => true, 'res' => $success, 'fail' => $fail));
    }
    public function _find_product_alias($orders){
        $tuan_buy_ids = array_unique(Hash::extract($orders, '{n}.Order.member_id'));
        $products = $this->TuanBuying->find('all', array(
            'conditions' => array('TuanBuying.id'=>$tuan_buy_ids),
            'joins' =>array(
                array(
                    'table' => 'tuan_products',
                    'alias' => 'TuanProduct',
                    'type' => 'inner',
                    'conditions' => array(
                        'TuanProduct.product_id = TuanBuying.pid',
                    )
                )
            ),
            'fields' => array('TuanBuying.id', 'TuanProduct.alias')
        ));
        $tuan_products = Hash::combine($products, '{n}.TuanBuying.id', '{n}.TuanProduct');
        return $tuan_products;
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
        if($order['Order']['type'] != 5 && $order['Order']['type'] != 6){
            echo json_encode(array('success' => false, 'res' => 'order '.$order['Order']['id'].' is of type '.$order['Order']['type']));
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

        $tuan_product = $this->TuanBuying->find('first', array(
            'conditions' => array('TuanBuying.id'=>$order['Order']['member_id']),
            'joins' =>array(
                array(
                    'table' => 'tuan_products',
                    'alias' => 'TuanProduct',
                    'type' => 'inner',
                    'conditions' => array(
                        'TuanProduct.product_id = TuanBuying.pid',
                    )
                )
            ),
            'fields' => array('TuanProduct.alias')
        ));
        $this->log("tuan products: ".json_encode($tuan_product));


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
                    "first" => array("value" => "亲，您订购的".$tuan_product['TuanProduct']['alias']."已经到达自提点，提货码：".$haolinju_code."，生鲜娇贵，请尽快取货哈。"),
                    "keyword1" => array("value" => $order['Order']['id']),
                    "keyword2" => array("value" => $offline_store['OfflineStore']['alias']),
                    "keyword3" => array("value" => $order['Order']['consignee_address']),
                    "remark" => array("value" => "感谢您的支持，现场提货遇到任何问题请拨打电话：4000-508-528", "color" => "#FF8800")
                )
            );
            $this->log('ship to haolinju store: send weixin message for order: '.$order['Order']['id'].': '.json_encode($post_data));
            if(send_weixin_message($post_data)){
                echo json_encode(array('success' => true, 'res' => $order['Order']['id']));
                $this->OrderMessage->save(array('order_id' => $order['Order']['id'], 'status' => 0, 'type'=>'hlj-reach', 'data'=>$haolinju_code));
            }else{
                $this->log("ship to haolinju store: failed to send weixin message for order ".$order['Order']['id']);
                echo json_encode(array('success' => true, 'res' => ''));
            }
        }
        else{
            echo json_encode(array('success' => true, 'res' => $order['Order']['id']));
        }
    }
}