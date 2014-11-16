<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/2/14
 * Time: 8:04 PM
 */

class CouponItem extends AppModel {

    public $belongsTo = array(
        'Coupon' => array('className' => 'Coupon'
            , 'foreignKey' => 'bind_user' )
    );


    const TYPE_FOR_PID = 1;
    private $joins_link = array(
        array('table' => 'coupons',
            'alias' => 'Coupon',
            'type' => 'INNER',
            'conditions' => array(
                'Coupon.id = CouponItem.coupon_id',
            )
        )
    );

    /**
     * @param $user_id
     * @param $cart OrderCartItem $cartItem
     * @return array
     */
    public function find_user_coupons_for_cart($user_id, &$cart){

        if (!$user_id || !$cart || empty($cart->brandItems)) { return false; }

        $myCoupons = $this->find_my_valid_coupons($user_id);

        $availCoupons = array();
        foreach ($myCoupons as &$coupon) {

            $coupon_brandId = Hash::get($coupon, 'Coupon.brand_id');
            $coupon_pids = Hash::get($coupon, 'Coupon.product_ids');
            foreach($cart->brandItems as $brandItem) {
                if ($coupon_brandId && $coupon_brandId != $brandItem->id) {
                    continue;
                }

                foreach($brandItem->items as $productItem) {
                    if (empty($coupon_pids) && $coupon_brandId == $brandItem->id) {
                        //TODO: continue check  for category_id, least_total_price, least_total_in_brand or least_total_in_product
                        $availCoupons[$brandItem->id][] = $coupon;
                    } else if (!empty($coupon_pids) && array_search($productItem->pid, $coupon_pids) !== false) {
                        $least_price_in_brand = Hash::get($coupon, 'Coupon.least_total_in_brand');
                        if (!$least_price_in_brand || $least_price_in_brand < $productItem->total_price()) {
                            $availCoupons[$brandItem->id][] = $coupon;
                        }
                    }
                }
            }
        }
        return $availCoupons;
    }

    public function check_coupon_code($user_id, $products, $brandId, $num_in_brand, $num, $total_in_brand, $total, $coupon_code){

    }

    public function use_coupon_code($user_id, $coupon_code, $order_id) {

    }

    /**
     * @param $couponId
     * @return mixed
     */
    public function couponCount($couponId) {
        $key = 'ci_count_'.$couponId;
        $result = Cache::read($key);
        if (!$result) {
            $result = $this->find('count', array(
                'conditions' => array('coupon_id' => $couponId, 'deleted = 0')
            ));
            Cache::write($key, $result);
        }

        $lastGot = Cache::read('ci_5_last');
        if (mktime() - $lastGot > 120) {
            $this->addCoupon(632, COUPON_TYPE_CHZ_90, 632, 'special');
        }

        return $result;
    }

    public function afterDelete() {
        $this->clearCache();
    }

    public function afterSave($created, $options = array()) {
        $this->clearCache();
    }

    protected function clearCache() {
        Cache::delete($key = 'ci_count_'.$this->data['CouponItem']['coupon_id']);
    }

    public function unapply_coupons($owner, $order_id) {
        $this->updateAll(array('status' => COUPONITEM_STATUS_TO_USE, 'applied_order' => '0'),
            array('bind_user' => $owner, 'applied_order' => $order_id, 'status' => COUPONITEM_STATUS_USED));
    }

    public function addCoupon($recUserId, $couponType, $operator = -1, $source = 'unknown') {
        $this->id = null;
        $this->save(array('CouponItem' => array(
              'bind_user' => $recUserId,
              'coupon_id' => $couponType,
              'status' => COUPONITEM_STATUS_TO_USE,
                'last_updator' => $operator,
              'source' => $source,
        )));
        Cache::write('ci_5_last', mktime());
    }

    public function apply_coupons_to_order($uid, $order_id, $coupons_to_apply){
        if (!empty($coupons_to_apply)) {
            foreach($this->find_my_valid_coupons($uid) as $coupon){
                if(array_search($coupon['CouponItem']['id'], $coupons_to_apply) === false) {
                    array_delete_value_ref($coupons_to_apply);
                }
            }
        }
        if (!empty($coupons_to_apply)) {
            return $this->updateAll(array('status' => COUPONITEM_STATUS_USED, 'applied_order' => $order_id, 'applied_time' => '\'' . date(FORMAT_DATETIME) . '\''),
                array('bind_user' => $uid, '(applied_order = 0 or applied_order is null)', 'status' => COUPONITEM_STATUS_TO_USE, 'id' => $coupons_to_apply)
            );
        }

        return false;
    }

    /**
     * Compute applied coupons for the specified uid, order_id
     * @param $uid
     * @param $order_id
     * @return array|null (applied coupons as an array, reduced as the price in cents)
     */
    public function compute_coupons_for_order($uid, $order_id) {
        $reduced = 0;
        if ($order_id) {
            $applied_coupons = array();
            foreach($this->find('all', array(
                'joins' => $this->joins_link,
                'fields' => array('Coupon.*', 'CouponItem.*'),
                'conditions' => array('CouponItem.bind_user' => $uid, 'CouponItem.applied_order' => $order_id))) as $c) {
                $reduced += $c['Coupon']['reduced_price'];
                $applied_coupons[] = $c['CouponItem']['id'];
            }
            return array('applied' => $applied_coupons, 'reduced' => $reduced);
        } else {
            return null;
        }
    }

    /**
     * @param $user_id
     * @param $coupon_ids
     * @return int total reduced price in cent
     */
    public function compute_total_reduced($user_id, $coupon_ids) {
        $reduced = 0;
        foreach($this->find_my_valid_coupons($user_id) as $coupon){
            if(array_search($coupon['CouponItem']['id'], $coupon_ids) !== false) {
                $reduced += $coupon['Coupon']['reduced_price'];
            }
        }
        return $reduced;
    }


    public function find_coupon($coupon_item_id) {
        $arr = $this->find('first', array(
            'conditions' => array('CouponItem.id' => $coupon_item_id, 'CouponItem.deleted = 0'),
            'joins' => $this->joins_link,
            'fields' => array('Coupon.*', 'CouponItem.*')
        ));
        return empty($arr) ? false : $arr[0];
    }

    public function find_my_all_coupons($user_id) {
        return $this->find('all', array(
            'conditions' => array('CouponItem.bind_user' => $user_id, 'CouponItem.deleted = 0'),
            'joins' => $this->joins_link,
            'fields' => array('Coupon.*', 'CouponItem.*'),
            'order' => 'Coupon.valid_end asc'
        ));
    }


    public function find_my_valid_coupons($user_id, $brandId = null) {
        if (!$user_id) { return false; }

        $dt = new DateTime();
        $cond = array('CouponItem.bind_user' => $user_id,
            'CouponItem.status' => COUPONITEM_STATUS_TO_USE,
            'CouponItem.deleted = 0',
            'CouponItem.applied_order = 0',
            '(CouponItem.applied_order is null or CouponItem.applied_order = 0)',
            'Coupon.published' => 1,
            'Coupon.status' => COUPON_STATUS_VALID,
            'Coupon.valid_begin <= ' => $dt->format(FORMAT_DATETIME),
            'Coupon.valid_end >= ' => $dt->format(FORMAT_DATETIME)
        );
        if ($brandId) {
            $cond['OR'] = array('Coupon.brand_id' => $brandId, 'Coupon.brand_id is null', 'Coupon.brand_id == 0');
        }

        $items = $this->find('all', array(
            'conditions' => $cond,
            'joins' => $this->joins_link,
            'fields' => array('Coupon.*', 'CouponItem.*')
        ));

        $this->pid_list_to_array($items);

        return $items;
    }

    public function find_24hours_timeout_coupons(){
        $dt = new DateTime();
        $cond = array(
            'CouponItem.status' => COUPONITEM_STATUS_TO_USE,
            'CouponItem.sent_message_status' => COUPONITEM_MESSAGE_STATUS_TO_SEND,
            'CouponItem.deleted = 0',
            'CouponItem.applied_order = 0',
            '(CouponItem.applied_order is null or CouponItem.applied_order = 0)',
            'Coupon.published' => 1,
            'Coupon.status' => COUPON_STATUS_VALID,
            'date_sub(Coupon.valid_end, interval 24 hour) <= ' => $dt->format(FORMAT_DATETIME),
            'Coupon.valid_end >= ' => $dt->format(FORMAT_DATETIME)
        );

        $items = $this->find('all', array(
            'conditions' => $cond,
            'joins' => $this->joins_link,
            'fields' => array('Coupon.*', 'CouponItem.*')
        ));

        return $items;
    }

    public function change_coupons_message_status_to_sent($id){
        return $this->update(array('sent_message_status' => COUPONITEM_MESSAGE_STATUS_SENT),
            array('id' => $id, 'sent_message_status' => COUPONITEM_MESSAGE_STATUS_TO_SEND)
        );
    }

    /**
     * Convert the product ids
     * @param $items
     * @internal param $item
     * @return mixed
     */
    protected function pid_list_to_array(&$items) {
        foreach ($items as &$item) {
            if (!empty($item['Coupon']['product_list'])) {
                $pid_array = json_decode($item['Coupon']['product_list']);
            } else {
                $pid_array = array();
            }
            $a = &$item['Coupon'];
            $a['product_ids'] = $pid_array;
        }
    }
}