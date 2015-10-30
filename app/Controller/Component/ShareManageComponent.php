<?php

class ShareManageComponet extends Componet {

    public $components = array('ShareUti', 'WeshareBuy');

    public function get_share_orders($share_id) {
        $OrderM = ClassRegistry::init('Order');
        $orders = $OrderM->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY,
                'member_id' => $share_id,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_COMMENT, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_COMMENT, ORDER_STATUS_DONE),
            ),
            'limit' => 2000
        ));
        return $orders;
    }

    public function get_users_data($user_ids) {
        $UserM = ClassRegistry::init('User');
        $user_data = $UserM->find('all', array(
            'conditions' => array(
                'id' => $user_ids
            ),
            'fields' => array('id', 'nickname')
        ));
        return $user_data;
    }

}