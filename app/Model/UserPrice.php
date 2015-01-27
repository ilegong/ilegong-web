<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 1/27/15
 * Time: 4:10 PM
 */

class UserPrice extends AppModel {

    public function add($product_id, $customized_price, $uid, $cart_id) {
        $this->save(array('product_id' => $product_id, 'customized_price' => $customized_price, 'uid' => $uid, 'cart_id' => $cart_id));
    }

} 