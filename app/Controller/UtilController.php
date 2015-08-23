<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 8/23/15
 * Time: 20:16
 */

class UtilController extends AppController{

    public $name = 'util';

    public $uses = array('UserRelation', 'Order', 'Cart');

    public $components = array('ShareUtil');


    /**
     * @param $product_id
     * @param $user_id
     * 迁移粉丝数据
     */
    public function transferFansData($product_id, $user_id) {
        $this->autoRender = false;
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'product_id' => $product_id,
                'not' => array('order_id' => null, 'order_id' => 0, 'type' => ORDER_TYPE_WESHARE_BUY, 'creator' => 0, 'creator' => null),
            ),
            'group' => array('creator'),
            'limit' => 500,
            'order' => array('created DESC')
        ));
        $save_data = array();
        foreach ($carts as $cart_item) {
            $cart_creator = $cart_item['Cart']['creator'];
            if ($this->ShareUtil->check_user_relation($user_id, $cart_creator)) {
                $save_data[] = array('user_id' => $user_id, 'follow_id' => $cart_creator, 'type' => 'Transfer', 'created' => date('Y-m-d H:i:s'));
            }
        }
        $this->UserRelation->saveAll($save_data);
        echo json_encode(array('success' => true));
        return;
    }
}