<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/10/14
 * Time: 5:04 PM
 */

class Order extends AppModel {


    /**
     * @param $operator
     * @param $order_id
     * @return bool
     */
    public function cancelWaitingPayOrder($operator, $order_id, $owner) {

        $rtn = $this->updateAll(array('status' => ORDER_STATUS_CANCEL, 'lastupdator' => $operator),
            array('id' => $order_id, 'status' => ORDER_STATUS_WAITING_PAY));

        ClassRegistry::init('CouponItem')->unapply_coupons($owner, $order_id);

        return $rtn;
    }

} 