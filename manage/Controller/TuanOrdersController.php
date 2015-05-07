<?php
class TuanOrdersController extends AppController{

    var $name = 'TuanOrders';

    var $uses = array('Order', 'TuanTeam', 'TuanBuying', 'Location', 'TuanProduct', 'OfflineStore', 'Oauthbind');


    /**
     * show all tuan teams
     */
    public function admin_ship_to_pys_stores(){
        $this->autoRender = false;

        $data=$_POST;
        if(empty($data)){
            echo json_encode(array('success' => true, 'res' => array()));
            return;
        }

        $order_ids = array_keys($data);
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'id'=> $order_ids
            ),
        ));
        // all orders must be paid
        foreach($orders as &$order){
            if($order['Order']['status'] != ORDER_STATUS_PAID){
                echo json_encode(array('success' => false, 'res' => 'order '.$order['Order']['id'].' status is '.$order['Order']['status']));
                return;
            }
            if($order['Order']['type'] != 5 && $order['Order']['type'] != 6){
                echo json_encode(array('success' => false, 'res' => 'order '.$order['Order']['id'].' is of type '.$order['Order']['type']));
                return;
            }
        }

        // validate goods should be shipped to pys stores
        $offline_store_ids = array_unique(Hash::extract($orders, '{n}.Order.consignee_id'));
        if(!empty($offline_store_ids)){
            $offline_stores = $this->OfflineStore->find('all', array(
                'conditions' => array('id'=>$offline_store_ids)
            ));
            $offline_stores = Hash::combine($offline_stores, '{n}.OfflineStore', '{n}');
        }
        foreach($orders as &$order){
            $offline_store = $offline_stores[$order['Order']['consignee_id']];
            if(!empty($offline_store) && $offline_store['OfflineStore']['type'] == OFFLINE_STORE_HAOLINJU){
                echo json_encode(array('success' => false, 'res' => 'order '.$order['Order']['id'].' is haolinju'));
                return;
            }
        }

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

        $user_ids = Hash::extract($orders, '{n}.Order.creator');
        $oauth_binds = $this->Oauthbind->find('list', array(
            'conditions' => array( 'user_id' => $user_ids, 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));

        $this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED),array('id' => $order_ids));

        foreach($orders as &$order){
            $offline_store = $offline_stores[$order['Order']['consignee_id']];
            $post_data = array(
                "touser" => $oauth_binds[$order['Order']['creator']],
                "template_id" => '3uA5ShDuM6amaaorl6899yMj9QvBmIiIAl7T9_JfR54',
                "url" => WX_HOST . '/orders/detail/'.$order['Order']['id'],
                "topcolor" => "#FF0000"
            );

            $post_data['data'] =  array(
                "first" => array("value" => "亲，您订购的".$tuan_products[$order['Order']['member_id']]."已经到达自提点，生鲜娇贵，请尽快取货哈。"),
                "keyword1" => array("value" => $order['Order']['id']),
                "keyword2" => array("value" => $offline_store['OfflineStore']['alias']),
                "keyword3" => array("value" => $order['Order']['consignee_address']),
                "remark" => array("value" => "感谢您的支持".$offline_store['OfflineStore']['owner_phone'], "color" => "#FF8800")
            );
            if(send_weixin_message($post_data)){
                $success[] = $order['Order']['id'];
            }else{
                $fail[] = $order['Order']['id'];
            }
        }

        echo json_encode(array('success' => true, 'res' => $order_ids));
    }
}