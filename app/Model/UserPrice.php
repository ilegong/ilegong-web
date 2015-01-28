<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 1/27/15
 * Time: 4:10 PM
 */

class UserPrice extends AppModel {

    public function add($product_id, $customized_price, $uid, $cart_id) {
        $found = $this->find('first', array(
            'conditions' => array('product_id' => $product_id, 'uid' => $uid)
        ));

        $action = 'new';
        if (!empty($found)) {
            $this->id = $found['UserPrice']['id'];
            $action = 'update';
        }

        $this->save(array('product_id' => $product_id, 'customized_price' => $customized_price, 'uid' => $uid, 'cart_id' => $cart_id));

        $this->log('add UserPrice:update/new?'.$action.', product_id='.$product_id.', customized_price='.$customized_price.', uid='.$uid.', cart_id='.$cart_id);
    }

} 