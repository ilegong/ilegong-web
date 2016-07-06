<?php

class OrdersComponent extends Component
{

    var $query_order_fields = array('id', 'creator', 'created', 'consignee_name', 'consignee_mobilephone', 'consignee_address', 'status', 'total_all_price', 'coupon_total', 'ship_mark', 'ship_code', 'ship_type', 'ship_type_name', 'member_id', 'process_prepaid_status', 'price_difference', 'is_prepaid');

    public $components = array('Weixin');

    //
    public function on_order_created($uid, $share_id, $order_id)
    {
        $this->clearCacheForUser($uid);
        // 计算团长返利
        // 用户发送模板消息
        // 积分
    }

    public function get_order_info($order_id)
    {
        $orderM = ClassRegistry::init('Order');
        $order = $orderM->find('first', array(
            'conditions' => array(
                'id' => $order_id
            ),
            'fields' => $this->query_order_fields
        ));
        return $order;
    }

    // TODO: replace this method with get_order_info()
    public function get_order_info2($orderId)
    {
        $orderM = ClassRegistry::init('Order');
        $order_info = $orderM->find('first', array(
            'conditions' => array('id' => $orderId, 'status' => ORDER_STATUS_SHIPPED, 'type' => ORDER_TYPE_WESHARE_BUY, 'ship_mark' => SHARE_SHIP_PYS_ZITI_TAG),
            'fields' => $this->query_order_fields
        ));
        return $order_info;
    }

    public function get_order_info_with_cart($order_id)
    {
        $orderM = ClassRegistry::init('Order');
        $cartM  = ClassRegistry::init('Cart');
        $result = $orderM->find('first', [
            'conditions' => [
                'Order.id' => $order_id
            ],
            'joins' => [
                [
                    'table' => 'cake_weshares',
                    'alias' => 'Weshare',
                    'conditions' => ['Weshare.id = Order.member_id'],
                    'type' => 'left'
                ],
                [
                    'table' => 'pay_notifies',
                    'alias' => 'Pay',
                    'conditions' => [ 'Pay.order_id = Order.id'],
                    'type' => 'left'
                ]
            ],
            'fields' => ['Weshare.status' ,'Order.id', 'Order.status', 'Order.business_remark' ,'Order.created', 'Order.consignee_name', 'Order.consignee_address', 'Order.consignee_mobilephone', 'Order.total_all_price', 'Order.ship_type_name', 'Order.ship_type', 'Order.ship_fee', 'Order.ship_code', 'Order.pay_time', 'Order.coupon_total', 'Order.brand_id', 'Pay.trade_type']
        ]);
        $carts = $cartM->find('all', [
            'conditions' => [
                'order_id' => $order_id
            ],
            'fields' => ['Cart.name', 'Cart.num', 'Cart.price']
        ]);
        $result['carts'] = Hash::extract($carts, '{n}.Cart');
        return $result;
    }

    private function clearCacheForUser($uid)
    {
        $key = USER_SHARE_INFO_CACHE_KEY . '_' . $uid;
        Cache::write($key, '');
    }


    public function get_user_order($params)
    {
        $orderM = ClassRegistry::init('Order');
        $result = $orderM->find('all', [
            'conditions' => [
                'Order.creator' => $params['user_id'],
                'Order.status' => $params['status'],
                'Order.type' => ORDER_TYPE_WESHARE_BUY
            ],
            'joins' => [
                [
                    'table' => 'cake_weshares',
                    'alias' => 'Weshare',
                    'conditions' => ['Order.member_id = Weshare.id'],
                    'type' => 'left'
                ]
            ],
            'fields' => ['Order.id', 'Order.status', 'Order.created', 'Order.creator', 'Order.total_price', 'Order.brand_id', 'Order.ship_mark', 'Order.member_id', 'Weshare.title', 'Weshare.status', 'Weshare.default_image', 'Weshare.send_info'],
            'limit' => $params['limit'],
            'page' => $params['page'],
            'recursive' => 1,
            'order' => ['Order.id DESC']
        ]);
        return $result;
    }
}