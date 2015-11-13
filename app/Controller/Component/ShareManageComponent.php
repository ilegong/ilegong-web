<?php

class ShareManageComponent extends Component {

    public $components = array('ShareUti', 'WeshareBuy');


    public function get_weshare_products($shareId) {
        $weshareProductM = ClassRegistry::init('WeshareProduct');
        $weshare_products = $weshareProductM->find('all', array(
            'conditions' => array(
                'weshare_id' => $shareId,
                'deleted' => DELETED_NO
            )
        ));
        return $weshare_products;
    }

    public function get_share_product_tags($uid) {
        $weshareProductTagM = ClassRegistry::init('WeshareProductTag');
        $tags = $weshareProductTagM->find('all', array(
            'conditions' => array(
                'user_id' => $uid,
                'deleted' => DELETED_NO
            )
        ));
        return $tags;
    }


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


    public function set_dashboard_collect_data($uid) {
        $weshareM = ClassRegistry::init('Weshare');
        $orderM = ClassRegistry::init('Order');
        $userRelationM = ClassRegistry::init('UserRelation');
        $shareFaqM = ClassRegistry::init('ShareFaq');
        $share_count = $weshareM->find('count', array(
            'conditions' => array(
                'creator' => $uid
            )
        ));
        $last_300_shares = $weshareM->find('all', array(
            'conditions' => array(
                'creator' => $uid
            ),
            'order' => array('id' => 'desc'),
            'fields' => array('id'),
            'limit' => 300
        ));
        $last_300_share_ids = Hash::extract($last_300_shares, '{n}.Weshare.id');
        $order_count = $orderM->find('count', array(
            'conditions' => array(
                'member_id' => $last_300_share_ids,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_COMMENT, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_COMMENT, ORDER_STATUS_DONE),
            )
        ));
        $faq_count = $shareFaqM->find('count', array(
            'conditions' => array(
                'receiver' => $uid,
                'has_read' => 0
            )
        ));
        $fans_count = $userRelationM->find('count', array(
            'conditions' => array(
                'user_id' => $uid
            )
        ));
        return array('share_count' => $share_count, 'order_count' => $order_count, 'faq_count' => $faq_count, 'fans_count' => $fans_count);
    }
}