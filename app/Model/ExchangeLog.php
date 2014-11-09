<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/20/14
 * Time: 5:38 PM
 */
class ExchangeLog extends AppModel
{

    public function getLatestExchangeLogByUidAndSource($uid, $source)
    {
        $exchangeLogs = $this->find('first', array(
            'conditions' => array('user_id' => $uid, 'source' => $source),
            'order' => 'id desc'
        ));
        return $exchangeLogs ? $exchangeLogs['ExchangeLog'] : false;
    }

    public function addExchangeLog($uid, $apple_count_snapshot, $exchange_apple_count, $exchange_coupon_count, $source = 'unknown')
    {
        $this->save(array('ExchangeLog' => array(
            'user_id' => $uid,
            'apple_count_snapshot' => $apple_count_snapshot,
            'exchange_apple_count' => $exchange_apple_count,
            'exchange_coupon_count' => $exchange_coupon_count,
            'source' => $source
        )));
        return $this->id;
    }
}