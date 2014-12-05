<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/10/14
 * Time: 5:04 PM
 */

class Order extends AppModel {


    /**
     * @param $operator
     * @param $order_id
     * @param $owner
     * @return bool
     */
    public function cancelWaitingPayOrder($operator, $order_id, $owner) {

        $rtn = $this->updateAll(array('status' => ORDER_STATUS_CANCEL, 'lastupdator' => $operator),
            array('id' => $order_id, 'status' => ORDER_STATUS_WAITING_PAY));

        if ($rtn && $this->getAffectedRows() >= 1) {
            ClassRegistry::init('CouponItem')->unapply_coupons($owner, $order_id);
            $cartModel = ClassRegistry::init('Cart');
            $boughts = $cartModel->find('list', array(
                'conditions' => array('order_id' => $order_id, 'status' => CART_ITEM_STATUS_BALANCED),
                'fields' => 'product_id, num'
            ));
            if(!empty($boughts)) {
                $productModel = ClassRegistry::init('Product');
                foreach($boughts as $pid => $num) {
                    $productModel->update_storage_saled($pid, -$num);
                }
            }
        }

        return $rtn;
    }

    /**
     * @param $uid
     * @param null $order_status
     * @return array orders, order_carts and mapped brands
     */
    public function get_user_orders($uid, $order_status=null) {

        $cond = array('creator' => $uid, 'published' => PUBLISH_YES, 'deleted' => DELETED_NO);
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
            $order_ids[] = $o['Order']['id'];
            $brandIds[] = $o['Order']['brand_id'];
        }

        $order_carts = array();
        if (!empty($order_ids)) {
            $cartM = ClassRegistry::init('Cart');
            $Carts = $cartM->find('all', array(
                'conditions' => array(
                    'order_id' => $order_ids,
                    'creator' => $uid,
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
        return array($orders, $order_carts, $mappedBrands);
    }

    public function find_my_order_byId($orderId, $uid) {
        return $this->find('first', array(
            'conditions' => array('id' => $orderId, 'creator' => $uid),
        ));
    }

} 