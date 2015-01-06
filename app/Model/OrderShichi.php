<?php
/**
 * Created by PhpStorm.
 * User: algdev
 * Date: 14/12/16
 * Time: 下午12:25
 */

class OrderShichi extends AppModel {


    public function find_my_shichi_order_byId($orderId, $uid) {
        return $this->find('first', array(
            'conditions' => array('id' => $orderId, 'creator' => $uid),
        ));
    }

    public function bought_by_curr_user($tryId, $currUid) {
        return $this->find('count', array(
            'conditions' => array('data_id' => $tryId, 'creator' => $currUid)
        ));
    }


    /*
     * @param $uid
     * @param $order_status  int|array
     * @return array orders, order_carts and mapped brands
     */

    public function get_user_shichi_orders($uid,$order_status=null) {

        $cond = array('creator' => $uid);
        if ($order_status !== null) {
            $cond['status'] = $order_status;
        }
        $orders = $this->find('all', array(
            'order' => 'id desc',
            'conditions' => $cond,
        ));
        $order_ids = array();
        $brandIds = array();
        foreach ($orders as $o) {
            $order_ids[] = $o['OrderShichi']['order_id'];
//            $brandIds[] = $o['OrderShichi']['brand_id'];
        }

        $ordershichis = array();
        if (!empty($order_ids)) {
            $orderM = ClassRegistry::init('Order');
            $order_shichis = $orderM->find('all',array(
                'conditions' => array(
                    'id' => $order_ids
                ),
                'fields' => array(
                    'id','status','brand_id'
                )
            ));

            foreach ($order_shichis as $order_shichi) {
                $brandIds[] = $order_shichi['Order']['brand_id'];
                $ordershichis[$order_shichi['Order']['id']] = $order_shichi;
            }
        }

        $order_carts = array();
        if (!empty($order_ids)) {
            $cartM = ClassRegistry::init('Cart');
            $Carts = $cartM->find('all', array(
                'conditions' => array(
                    'order_id' => $order_ids,
                    'creator' => $uid
//                    'status' => CART_ITEM_STATUS_BALANCED,
                )));

            foreach ($Carts as $c) {
                $order_id = $c['Cart']['order_id'];
                if (!isset($order_carts[$order_id])) $order_carts[$order_id] = array();
                $order_carts[$order_id][] = $c;
            }
        }



        $mappedBrands = array();
        if (!empty($brandIds)) {
            $brandM = ClassRegistry::init('Brand');
            $brands = $brandM->find('all', array(
                'conditions' => array('id' => $brandIds),
                'fields' => array('id', 'name', 'created', 'slug', 'coverimg')
            ));

            foreach ($brands as $brand) {
                $mappedBrands[$brand['Brand']['id']] = $brand;
            }
        }
        return array($orders,$ordershichis, $order_carts, $mappedBrands);
    }

}