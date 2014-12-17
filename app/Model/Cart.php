<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/22/14
 * Time: 11:14 PM
 */

class Cart extends AppModel {

    public function merge_user_carts_after_login($uid, $oldSid) {
        $cond_carts_of_users = array(
            'session_id' => $oldSid,
            'status' => 0,
            'order_id' => NULL);

        $oldCartItems = $this->find('all', array('conditions' => $cond_carts_of_users));

        $old_pids = Hash::extract($oldCartItems, '{n}.Cart.product_id');

         if (!empty($old_pids)) {
             $this->deleteAll(array('creator' => $uid,
                 'product_id' => $old_pids,
                 'OR' => array('session_id != ' => $oldSid, 'session_id' => NULL),
                 'order_id' => NULL,
                 'status' => 0), false);
         }

        $this->updateAll(array('creator' => $uid, 'session_id' => NULL), $cond_carts_of_users);
    }

    public function balanced_items($pid, $creator) {
        return $this->find('all', array(
            'conditions' => array('product_id' => $pid, 'status' => CART_ITEM_STATUS_BALANCED, 'num > ' => 0, 'creator' => $creator),
            'fields' => array('num', 'order_id'),
        ));
    }

    public function find_try_cart_item($pid, $creator) {
        return $this->find('first', array(
            'conditions' => array('product_id' => $pid, 'status ' => CART_ITEM_STATUS_NEW, 'num > ' => 0, 'creator' => $creator, 'type' => CART_ITEM_TYPE_TRY),
        ));
    }

    /**
     * Find balanced Cart items (product_id, num, name, creator, cover img) through cache.
     * Balanced items won't change.
     * @param $order_id int order_id or order id list.
     * @return array Cart elements
     */
    public function find_balanced_items($order_id) {
        $balanced_order_key = "balanced_cart_items_" . $order_id;
        $cache = Cache::read($balanced_order_key);
        if (empty($cache)) {
            $carts = $this->find('all', array(
                'conditions' => array('order_id' => $order_id, 'status' => CART_ITEM_STATUS_BALANCED),
                'fields' => array('num', 'product_id', 'name', 'creator', 'coverimg'),
            ));
            $jsonStr = json_encode($carts);
            Cache::write($balanced_order_key, $jsonStr);
            return $cache;
        } else {
            return json_decode($cache, true);
        }
    }


} 