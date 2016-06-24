<?php

class BalanceComponent extends Component
{

    public static $WAIT_BALANCE_STATUS = 0;
    public static $ALREADY_BALANCE_STATUS = 1;

    public static $SELF_BALANCE_TYPE = 1; //自有分享费用
    public static $POOL_SHARE_BALANCE_TYPE = 2; //产品街代销费用
    public static $POOL_BRAND_BALANCE_TYPE = 3; //产品街商家费用

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
        $data = $this->get_balance_list($uid, $page,$limit,self::$WAIT_BALANCE_STATUS);
        return $data;
    }

    public function get_already_balance_share_list($uid, $page,$limit)
    {
        $data = $this->get_balance_list($uid, $page,$limit,self::$ALREADY_BALANCE_STATUS);
        return $data;
    }

    private function get_balance_list($uid, $page, $limit, $status){
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
                'id' => $item['BalanceLog']['id'],
                'trade_fee' => $item['BalanceLog']['trade_fee'],
                'share_title' => $item['BalanceLog']['title'],
                'default_image' => $item['BalanceLog']['share_id'],
                'type' => $item['BalanceLog']['type'],
                'created' => $item['BalanceLog']['created'],
                'share_id' => $item['BalanceLog']['share_id']
            ];
        }
        return $data;
    }


}