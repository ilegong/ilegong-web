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


} 