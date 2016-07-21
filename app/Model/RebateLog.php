<?php

class RebateLog extends Model
{

    protected function save_rebate_log($userId, $change, $orderId, $reason)
    {
        $desc = $this->get_reason($reason, $orderId, $change);
        $saved = $this->save(['user_id' => $userId, 'reason' => $reason, 'order_id' => $orderId, 'money' => $change, 'description' => $desc, 'created' => date('Y-m-d H:i:s')]);
        if ($saved) {
            return $saved;
        }
        return false;
    }

    public function after_rebate_change($user_id, $change)
    {
        //todo send msg
    }

    private function get_reason($reason, $orderId, $change)
    {
        if ($reason == USER_REBATE_MONEY_GOT) {
            return '推荐订单 ' . $orderId . ' 获得 ' . get_format_number($change / 100);
        }

        if ($reason == USER_REBATE_MONEY_USE) {
            return '订单 ' . $orderId . ' 使用 ' . get_format_number(abs($change) / 100);
        }
    }

}