<?php

class BalanceComponent extends Component
{

    public static $WAIT_BALANCE_STATUS = 0;
    public static $ALREADY_BALANCE_STATUS = 1;

    public static $SELF_BALANCE_TYPE = 1; //自有分享费用
    public static $POOL_SHARE_BALANCE_TYPE = 2; //产品街代销费用
    public static $POOL_BRAND_BALANCE_TYPE = 3; //产品街商家费用


    var $components = ['Paginator'];

    /**
     * @param $uid
     * @return array
     * 获取用户结账的汇总数据
     */
    public function get_user_share_summary($uid)
    {
        $balanceLogM = ClassRegistry::init('BalanceLog');
        $sql = "select status, sum(trade_fee) as total_trade_fee, count(id) as total_count from cake_balance_logs where user_id={$uid} group by status";
        $result = $balanceLogM->query($sql);
        $data = [];
        foreach ($result as $result_item) {
            $status = $result_item['cake_balance_logs']['status'];
            if ($status == 0) {
                $data['wait_balance'] = ['count' => $result_item[0]['total_count'], 'total_fee' => $result_item[0]['total_trade_fee']];
            }
            if ($status == 1) {
                $data['already_balance'] = ['count' => $result_item[0]['total_count'], 'total_fee' => $result_item[0]['total_trade_fee']];
            }
        }
        return $data;
    }

    public function get_wait_balance_share_list($uid, $page, $limit)
    {
        $data = $this->get_balance_list($uid, $page, $limit, self::$WAIT_BALANCE_STATUS);
        return $data;
    }

    public function get_already_balance_share_list($uid, $page, $limit)
    {
        $data = $this->get_balance_list($uid, $page, $limit, self::$ALREADY_BALANCE_STATUS);
        return $data;
    }

    private function process_share_data($cond)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $orderM = ClassRegistry::init('Order');
        $weshares = $weshareM->find('all', $cond);
        $weshare_ids = Hash::extract($weshares, '{n}.Weshare.id');
        $weshares = Hash::combine($weshares, '{n}.Weshare.id', '{n}.Weshare');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'type' => ORDER_TYPE_WESHARE_BUY,
                'member_id' => $weshare_ids,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY)
            )
        ));
        $refund_orders = array();
        $refund_order_ids = array();
        $summary_data = array();
        foreach ($orders as $item) {
            $member_id = $item['Order']['member_id'];
            $order_total_price = $item['Order']['total_all_price'];
            $order_ship_fee = $item['Order']['ship_fee'];
            $order_coupon_total = $item['Order']['coupon_total'];
            $order_product_price = $item['Order']['total_price'];
            if ($item['Order']['status'] == ORDER_STATUS_RETURN_MONEY || $item['Order']['status'] == ORDER_STATUS_RETURNING_MONEY) {
                $refund_order_ids[] = $item['Order']['id'];
                if (!isset($refund_orders[$member_id])) {
                    $refund_orders[$member_id] = array();
                }
                $refund_orders[$member_id][] = $item;
            }
            if (!isset($summary_data[$member_id])) {
                $summary_data[$member_id] = array('total_price' => 0, 'ship_fee' => 0, 'coupon_total' => 0);
            }
            $summary_data[$member_id]['total_price'] = $summary_data[$member_id]['total_price'] + $order_total_price;
            $summary_data[$member_id]['ship_fee'] = $summary_data[$member_id]['ship_fee'] + $order_ship_fee;
            $summary_data[$member_id]['coupon_total'] = $summary_data[$member_id]['coupon_total'] + $order_coupon_total;
            $summary_data[$member_id]['product_total_price'] = $summary_data[$member_id]['product_total_price'] + $order_product_price;
        }
        $refundLogM = ClassRegistry::init('RefundLog');
        $refund_logs = $refundLogM->find('all', array(
            'order_id' => $refund_order_ids
        ));
        $refund_logs = Hash::combine($refund_logs, '{n}.RefundLog.order_id', '{n}.RefundLog.refund_fee');
        $weshare_refund_money_map = array();
        foreach ($refund_orders as $item_share_id => $item_orders) {
            $share_refund_money = 0;
            $weshare_refund_money_map[$item_share_id] = 0;
            foreach ($item_orders as $refund_order_item) {
                $order_id = $refund_order_item['Order']['id'];
                $share_refund_money = $share_refund_money + $refund_logs[$order_id];
            }
            $weshare_refund_money_map[$item_share_id] = $share_refund_money / 100;
        }
        $weshare_rebate_map = $this->get_share_rebate_money($weshare_ids);
        return ['weshare_rebate_map' => $weshare_rebate_map, 'weshare_refund_map' => $weshare_refund_money_map, 'weshares' => $weshares, 'weshare_summary' => $summary_data];
    }

    public function get_going_share_list($uid, $page, $limit)
    {
        $poolProductM = ClassRegistry::init('PoolProduct');
        $myPoolProducts = $poolProductM->find('all', [
            'conditions' => [
                'user_id' => $uid
            ],
            'limit' => 100,
            'order' => 'id DESC',
            'fields' => ['weshare_id']
        ]);
        $myPoolShareIds = Hash::extract($myPoolProducts, '{n}.PoolProduct.weshare_id');
        $cond = [
            'conditions' => [
                'Weshare.type' => [SHARE_TYPE_POOL, SHARE_TYPE_DEFAULT],
                'Weshare.status' => WESHARE_STATUS_NORMAL,
                'OR' => [
                    ['Weshare.creator' => $uid],
                    ['Weshare.refer_share_id' => $myPoolShareIds]
                ]
            ],
            'limit' => $limit,
            'page' => $page
        ];
        $data = $this->process_share_data($cond);
        $weshares = $data['weshares'];
        $rebate_map = $data['weshare_rebate_map'];
        $refund_map = $data['weshare_refund_map'];
        $summary = $data['weshare_summary'];
        $result = [];
        foreach ($weshares as $item) {
            $share_id = $item['id'];
            $transaction_fee = floatval($summary[$share_id]['total_price']) - floatval($refund_map[$share_id]) - floatval($rebate_map[$share_id]);
            $result[] = [
                'share_id' => $share_id,
                'trade_fee' => $transaction_fee,
                'share_title' => $item['title'],
                'default_image' => $item['default_image'],
                'type' => $this->get_type_by_uid_and_share($uid, $item),
                'created' => $item['created'],
            ];
        }
        return $result;
    }

    private function get_type_by_uid_and_share($uid, $share)
    {
        if ($share['creator'] == $uid) {
            if ($share['type'] == SHARE_TYPE_DEFAULT) {
                return self::$SELF_BALANCE_TYPE;
            }
            return self::$POOL_SHARE_BALANCE_TYPE;
        }
        return self::$POOL_BRAND_BALANCE_TYPE;
    }

    private function get_balance_list($uid, $page, $limit, $status)
    {
        $balanceLogM = ClassRegistry::init('BalanceLog');
        $logs = $balanceLogM->find('all', [
            'conditions' => [
                'BalanceLog.user_id' => $uid,
                'BalanceLog.status' => $status
            ],
            'joins' => [
                [
                    'type' => 'left',
                    'table' => 'cake_weshares',
                    'alias' => 'Weshare',
                    'conditions' => ['Weshare.id = BalanceLog.share_id']
                ]
            ],
            'order' => 'BalanceLog.id ASC',
            'page' => $page,
            'limit' => $limit,
            'fields' => ['BalanceLog.id', 'BalanceLog.trade_fee', 'Weshare.title', 'Weshare.default_image', 'BalanceLog.type', 'BalanceLog.created', 'BalanceLog.share_id']
        ]);
        $data = [];
        foreach ($logs as $item) {
            $data[] = [
                'trade_fee' => $item['BalanceLog']['trade_fee'],
                'share_title' => $item['Weshare']['title'],
                'default_image' => $item['Weshare']['default_image'],
                'type' => $item['BalanceLog']['type'],
                'created' => $item['BalanceLog']['created'],
                'share_id' => $item['BalanceLog']['share_id']
            ];
        }
        return $data;
    }


    private function get_share_rebate_money($share_ids)
    {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $rebateLogs = $rebateTrackLogM->find('all', array(
            'conditions' => array(
                'share_id' => $share_ids,
                'not' => array('order_id' => 0, 'is_paid' => 0)
            ),
            'limit' => 500
        ));
        $share_rebate_map = array();
        foreach ($rebateLogs as $log) {
            $share_id = $log['RebateTrackLog']['share_id'];
            if (!isset($share_rebate_map[$share_id])) {
                $share_rebate_map[$share_id] = array('rebate_money' => 0);
            }
            $share_rebate_map[$share_id]['rebate_money'] = $log['RebateTrackLog']['rebate_money'];
        }
        foreach ($share_rebate_map as &$rebate_item) {
            $rebate_item['rebate_money'] = number_format(round($rebate_item['rebate_money'] / 100, 2), 2);
        }
        return $share_rebate_map;
    }

}