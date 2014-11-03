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


    const COUPON_STATUS_VALID = 1;

    const STATUS_TO_USE = 1;
    const TYPE_FOR_PID = 1;

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
                if ($coupon_brandId && $coupon_brandId != $brandItem->brandId) {
                    continue;
                }

                foreach($brandItem->productItems as $productItem) {
                    if (empty($coupon_pids)) {
                        //TODO: continue check  for category_id, least_total_price, least_total_in_brand or least_total_in_product
                        $availCoupons[$productItem->pid][] = $coupon;
                    } else if (array_search($coupon_pids, $productItem->pid) !== false) {
                        $least_price_in_brand = Hash::get($coupon, 'Coupon.least_total_in_brand');
                        if (!$least_price_in_brand || $least_price_in_brand < $productItem->total_price()) {
                            $availCoupons[$productItem->pid][] = $coupon;
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


    public function find_my_valid_coupons($user_id, $brandId = null) {
        if (!$user_id) { return false; }

        $joins = array(
            array('table' => 'coupons',
                'alias' => 'Coupon',
                'type' => 'INNER',
                'conditions' => array(
                    'Coupon.id = CouponItem.coupon_id',
                )
            )
        );

        $dt = new DateTime();
        $cond = array('CouponItem.bind_user' => $user_id,
            'CouponItem.status' => self::STATUS_TO_USE,
            'Coupon.published' => 1,
            'Coupon.status' => self::COUPON_STATUS_VALID,
            'Coupon.valid_begin <= ' => $dt->format(FORMAT_DATETIME),
            'Coupon.valid_end >= ' => $dt->format(FORMAT_DATETIME)
        );
        if ($brandId) {
            $cond['OR'] = array('Coupon.brand_id' => $brandId, 'Coupon.brand_id is null', 'Coupon.brand_id == 0');
        }

        $items = $this->find('all', array(
            'conditions' => $cond,
            'joins' => $joins
        ));

        $this->pid_list_to_array($items);

        return $items;
    }

    /**
     * @param $items
     * @internal param $item
     * @return mixed
     */
    protected function pid_list_to_array($items) {
        foreach ($items as &$item) {
            if (!empty($item['Coupon']['product_list'])) {
                $pid_array = json_decode($item['Coupon']['product_list']);
            } else {
                $pid_array = array();
            }
            $item['Coupon']['product_ids'] = $pid_array;
        }
    }
}