<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/16/14
 * Time: 12:06 AM
 */

class ProductTry extends AppModel {

    public function find_trying($limit = 2) {
        $tries = $this->find('all', array(
            'conditions' => array(
                'status' => PRODUCT_TRY_ING,
                'start_time >= date_format(now(), "%Y-%m-%d 00:00:00")',
            ),
            'limit' => $limit,
            'order' => 'start_time asc'
        ));

        foreach($tries as &$trying) {
            $trying['status'] = self::cal_op($trying['ProductTry']['limit_num'], $trying['ProductTry']['sold_num'], $trying['ProductTry']['start_time'], $trying['ProductTry']['status']);
        }

        return $tries;
    }

    static function cal_op($limit_num, $sold_num, $start_time, $status) {
        if ($status == PRODUCT_TRY_ING) {
            if ($limit_num <= $sold_num ) {
                return 'sec_end';
            } else if (before_than($start_time)) {
                return 'sec_kill';
            } else {
                return 'sec_unstarted';
            }
        } else {
            return 'sec_end';
        }
    }

} 