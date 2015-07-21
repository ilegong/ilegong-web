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

    public function admin_index(){

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

}