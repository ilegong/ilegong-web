<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 7/20/15
 * Time: 17:11
 */

class ShareController extends AppController{

    var $name = 'Share';

    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User', 'OrderConsignees');

    public function beforeFilter(){
        parent::beforeFilter();
        $this->layout='bootstrap_layout';
    }

    public function admin_make_order($num=1,$weshare_id){
        $this->autoRender=false;
        $users = $this->User->query('select id, nickname, status, username from cake_users where status=9 limit 0,'.$num);
        $weshare = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $weshare_id
            )
        ));
        $weshare_products = $this->WeshareProduct->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id
            )
        ));
        $weshare_addresses = $this->WeshareAddress->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshare_id
            )
        ));

        foreach($users as $user){
            $this->gen_order($weshare,$user,$weshare_products,$weshare_addresses);
        }
        echo json_encode(array('success' => true));
        return;
    }

    public function admin_index(){
        $weshare_count = $this->Weshare->find('count', array(
            'limit' => 5000
        ));

        $weshare_creator_count = $this->Weshare->find('count', array(
            'limit' => 5000,
            'fields' => 'DISTINCT creator'
        ));

        $join_weshare_count = $this->Order->find('count', array(
            'conditions' => array(
                'type' => 9,
                'DATE(created)' => date('Y-m-d'),
            ),
            'limit' => 15000,
            'fields' => array('DISTINCT creator')
        ));

        $order_count = $this->Order->find('count', array(
            'conditions' => array(
                'type' => 9,
                'DATE(created)' => date('Y-m-d'),
            )
        ));

        $this->set('share_count', $weshare_count);
        $this->set('share_creator_count', $weshare_creator_count);
        $this->set('join_share_count', $join_weshare_count);
        $this->set('today_order_count', $order_count);
    }

    public function admin_all_shares() {
        $shares = $this->Weshare->find('all', array(
            'order' => array('created DESC'),
            'limit' => 200
        ));
        $shareIds = Hash::extract($shares, '{n}.Weshare.id');
        $products = $this->WeshareProduct->find('all',array(
            'conditions' => array(
                'weshare_id' => $shareIds
            )
        ));
        $share_product_map = array();
        foreach($products as $item){
            if(!isset($share_product_map[$item['WeshareProduct']['weshare_id']])){
                $share_product_map[$item['WeshareProduct']['weshare_id']] = array();
            }
            $share_product_map[$item['WeshareProduct']['weshare_id']][] = $item['WeshareProduct'];
        }
        $this->set('shares',$shares);
        $this->set('share_product_map',$share_product_map);
    }

    public function admin_share_orders(){
        $query_date = date('Y-m-d');
        if($_REQUEST['date']){
            $query_date = $_REQUEST['date'];
        }
        $orders = $this->Order->find('all',array(
            'conditions' => array(
                'DATE(created)' => $query_date,
                'type' => 9,
            ),
            'order' => array('created DESC')
        ));
        $order_ids = Hash::extract($orders, '{n}.Order.id');
        $member_ids = Hash::extract($orders, '{n}.Order.member_id');
        $weshares = $this->Weshare->find('all', array(
            'conditions' => array(
                'id' => $member_ids
            )
        ));
        $creatorIds = Hash::extract($weshares,'{n}.Weshare.creator');
        $creators = $this->User->find('all',array(
            'conditions' => array(
                'id' => $creatorIds
            ),
            'fields' => array('id', 'nickname', 'mobilephone')
        ));
        $creators = Hash::combine($creators,'{n}.User.id','{n}.User');
        $weshares = Hash::combine($weshares, '{n}.Weshare.id', '{n}.Weshare');
        $carts = $this->Cart->find('all',array(
            'conditions' => array(
                'order_id' => $order_ids
            )
        ));
        $order_cart_map = array();
        foreach($carts as $item){
            $order_id = $item['Cart']['order_id'];
            if(!isset($order_cart_map[$order_id])){
                $order_cart_map[$order_id] = array();
            }
            $order_cart_map[$order_id][] = $item['Cart'];
        }
        $this->set('orders',$orders);
        $this->set('order_cart_map',$order_cart_map);
        $this->set('weshares',$weshares);
        $this->set('weshare_creators',$creators);
    }

    private function get_random_item($items){
        return $items[array_rand($items)];
    }

    private function gen_order($weshare,$user, $weshare_products, $weshare_address){
        $weshareProducts = array();
        $weshareProducts[] = $this->get_random_item($weshare_products);
        $tinyAddress = $this->get_random_item($weshare_address);
        $cart = array();
        try {
            $mobile_phone = $this->randMobile(1);
            $addressId = $tinyAddress['WeshareAddress']['id'];
            $weshare_id = $weshare['Weshare']['id'];
            $user = $user['cake_users'];
            $this->Order->id = null;
            $order = $this->Order->save(array('creator' => $user['id'], 'consignee_address' => $tinyAddress['WeshareAddress']['address'] ,'member_id' => $weshare['Weshare']['id'], 'type' => ORDER_TYPE_WESHARE_BUY, 'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'), 'consignee_id' => $addressId, 'consignee_name' => $user['nickname'], 'consignee_mobilephone' => $mobile_phone[0]));
            $orderId = $order['Order']['id'];
            $totalPrice = 0;
            foreach ($weshareProducts as $p) {
                $item = array();
                $num = rand (1 , 5);
                $price = $p['WeshareProduct']['price'];
                $item['name'] = $p['WeshareProduct']['name'];
                $item['num'] = $num;
                $item['price'] = $price;
                $item['type'] = ORDER_TYPE_WESHARE_BUY;
                $item['product_id'] = $p['WeshareProduct']['id'];
                $item['created'] = date('Y-m-d H:i:s');
                $item['updated'] = date('Y-m-d H:i:s');
                $item['creator'] = $user['id'];
                $item['order_id'] = $orderId;
                $item['tuan_buy_id'] = $weshare_id;
                $cart[] = $item;
                $totalPrice += $num * $price;
            }
            $this->Cart->id = null;
            $this->Cart->saveAll($cart);
            $this->Order->updateAll(array('total_all_price' => $totalPrice / 100, 'total_price' => $totalPrice / 100, 'ship_fee' => 0, 'status' => ORDER_STATUS_VIRTUAL), array('id' => $orderId));
            //echo json_encode(array('success' => true, 'orderId' => $orderId));
            return array('success' => true, 'orderId' => $orderId);
        } catch (Exception $e) {
            $this->log($user['id'].'buy share '.$weshare_id.$e);
            //echo json_encode(array('success' => false, 'msg' => $e->getMessage()));
            return array('success' => false, 'msg' => $e->getMessage());
        }
    }

    /**
     * @desc 生成n个随机手机号
     * @param int $num 生成的手机号数
     * @author niujiazhu
     * @return array
     */
    function randMobile($num = 1){
        //手机号2-3为数组
        $numberPlace = array(30,31,32,33,34,35,36,37,38,39,50,51,58,59,89);
        for ($i = 0; $i < $num; $i++){
            $mobile = 1;
            $mobile .= $numberPlace[rand(0,count($numberPlace)-1)];
            $mobile .= str_pad(rand(0,99999999),8,0,STR_PAD_LEFT);
            $result[] = $mobile;
        }
        return $result;
    }

}