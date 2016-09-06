<?php

class RebateLog extends Model
{

    public function has_bind_rebate_log($userId, $orderId, $reason)
    {
        return $this->hasAny(['user_id' => $userId, 'order_id' => $orderId, 'reason' => $reason]);
    }

    public function save_rebate_log($userId, $change, $orderId, $reason)
    {
        $desc = $this->get_reason($reason, $orderId, $change);
        $saved = $this->save(['user_id' => $userId, 'reason' => $reason, 'order_id' => $orderId, 'money' => $change, 'description' => $desc, 'created' => date('Y-m-d H:i:s')]);
        if ($saved) {
            return $saved;
        }
        return false;
    }

    public function restore_rebate_by_undo_order($userId, $change, $orderId){
        if ($change > 0) {
            $desc = $this->get_reason(USER_REBATE_MONEY_UNDO, $orderId, $change);
            return $this->save(['user_id' => $userId, 'reason' => USER_REBATE_MONEY_UNDO, 'order_id' => $orderId, 'money' => $change, 'description' => $desc, 'created' => date('Y-m-d H:i:s')]);
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
        if($reason == USER_REBATE_MONEY_UNDO){
            return '取消订单 ' . $orderId . ' 返还 ' . get_format_number(abs($change) / 100);
        }
    }

}