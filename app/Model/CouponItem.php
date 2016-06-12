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

        $total_price_cent = $cart->total_price() * 100;

        $myCoupons = $this->find_my_valid_coupons($user_id);

        $availCoupons = array();
        foreach ($myCoupons as &$coupon) {

            $coupon_brandId = Hash::get($coupon, 'Coupon.brand_id');
            $coupon_pids = Hash::get($coupon, 'Coupon.product_ids');
            $categories = Hash::get($coupon, 'Coupon.category_id');
            if (!$coupon_brandId && !$coupon_pids && !$categories) {
                if ($coupon['Coupon']['type'] != COUPON_TYPE_TYPE_MAN_JIAN
                    || $total_price_cent >= $coupon['Coupon']['least_price']) {
                    $availCoupons[0][] = $coupon;
                }
                continue;
            }

            foreach($cart->brandItems as $brandItem) {
                if ($coupon_brandId && $coupon_brandId != $brandItem->id) {
                    continue;
                }

                foreach($brandItem->items as $productItem) {
                    $brand_total_price = $brandItem->total_price() * 100;
                    if (empty($coupon_pids) && $coupon_brandId == $brandItem->id) {
                        //TODO: continue check  for category_id, least_total_price, least_total_in_brand or least_total_in_product
                        if ($coupon['Coupon']['type'] != COUPON_TYPE_TYPE_MAN_JIAN
                            || $brand_total_price >= $coupon['Coupon']['least_price']) {
                            $availCoupons[$brandItem->id][] = $coupon;
                        }
                    } else if (!empty($coupon_pids) &&
                        ( $coupon_pids == $productItem->pid  ||
                        (is_array($coupon_pids) && array_search($productItem->pid, $coupon_pids) !== false)
                    )) {
                        if ($coupon['Coupon']['type'] != COUPON_TYPE_TYPE_MAN_JIAN
                            || $brand_total_price >= $coupon['Coupon']['least_price']) {
                            $availCoupons[$brandItem->id][] = $coupon;
                        }
                    }
                    break;
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
        return $result;
    }

    /**
     * @param $couponId
     * @param $time
     * @return mixed
     */
    public function couponCountDaily($couponId, $time) {
        $day = date(FORMAT_DATE, $time);
        $key = $this->key_coupon_count_day($couponId, $day);
        $result = Cache::read($key);
        if (!$result) {
            $result = $this->find('count', array(
                'conditions' => array('coupon_id' => $couponId, 'deleted = 0', 'date(created)' => $day)
            ));
            Cache::write($key, $result);
        }
        return $result;
    }

    /**
     * @param $couponId
     * @param $time
     * @return mixed
     */
    public function couponCountHourly($couponId, $time) {
        list($hourStr, $key) = $this->key_hourly($couponId, $time);
        $result = Cache::read($key);
        if (empty($result)) {
            $result = $this->couponCountHourlyNoCache($couponId, $time);
            if ($result > 0 ) {
                Cache::write($key, $result);
            }
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
        $coupon_id = $this->data['CouponItem']['coupon_id'];
        Cache::delete($hourly_key = 'ci_count_'. $coupon_id);

        $created_time = $this->data['CouponItem']['created'];
        $dateObj = date_create_from_format(FORMAT_DATETIME, $created_time);
        if (!empty($dateObj)) {
            $created = $dateObj->getTimestamp();
            list($hourStr, $hourly_key) = $this->key_hourly($coupon_id, $created);
            $daily_key = $this->key_coupon_count_day($coupon_id, date(FORMAT_DATE, $created));
            Cache::delete($hourly_key);
            Cache::delete($daily_key);
            $this->log("clear-cache:".$coupon_id.", created_time:".$created_time.", dateObj=".json_encode($dateObj).", hourly_Key=".$hourly_key.", daily_key=".$daily_key, LOG_DEBUG);
        } else {
            $this->log("clear-cache: for dateObj being empty: coupon_id=".$coupon_id.", created_time:".$created_time, LOG_DEBUG);
        }

    }

    public function unapply_coupons($owner, $order_id) {
        $this->updateAll(array('status' => COUPONITEM_STATUS_TO_USE, 'applied_order' => '0'),
            array('bind_user' => $owner, 'applied_order' => $order_id, 'status' => COUPONITEM_STATUS_USED));
    }

    public function add_coupon_type($name, $brand_id, $valid_begin, $valid_end, $reduced_price, $published, $type, $operator, $status, $product_list=0) {
        if(empty($operator)){
            return 0;
        }
        $couponM = ClassRegistry::init('Coupon');
        if($couponM->save(array(
            'name' => $name,
            'brand_id' => $brand_id,
            'product_list' => $product_list,
            'valid_begin' => $valid_begin,
            'valid_end' => $valid_end,
            'reduced_price' => $reduced_price,
            'published' => $published,
            'last_updator' => $operator,
            'status' => $status,
            'type' => $type
        ))) {
            return $couponM->getLastInsertID();
        } else {
            return 0;
        }
    }

    public function add_spring_festival_coupon($userId, $pid) {
        $coupon_types = $this->read_spring_festivals($pid);
        $couponType = Hash::combine($coupon_types,'{n}.Coupon.product_list','{n}.Coupon.id');
        $couponType=$couponType[$pid];
        if (!empty($couponType)) {
            $got_items = $this->find_got_spring_festival_coupons($userId, $pid);
            if(empty($got_items[$pid]) && !$got_items[$pid]){
                $this->addCoupon($userId, $couponType, $userId, 'spring_festival');
                return true;
            } else {
                $this->log("already got for ".$userId." of ". $couponType);
                return false;
            }
        }

        throw new CakeException("unknown coupon type ".$couponType);
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
//        if ($couponType == COUPON_TYPE_CHZ_90) {
//            Cache::write('ci_5_last', mktime());
//        }
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

    public function find_coupon_item_by_type_no_join($uid, $couponIds, $start = null) {
        if (!empty($couponIds) && $uid) {
            $cond = array('coupon_id' => $couponIds, 'deleted = 0', 'bind_user' => $uid);
            if (!empty($start)) {
                $cond['created >=\''.$start.'\''];
            }
            return $this->find('all', array(
                'conditions' => $cond,
                'order' => 'id asc'
            ));
        }
        return false;
    }

    public function find_latest_coupon_item_by_type_no_join($couponIds, $limit) {
        if (!empty($couponIds)) {
            return $this->find('all', array(
                'conditions' => array('coupon_id' => $couponIds, 'deleted = 0'),
                'order' => 'created desc',
                'limit' => $limit
            ));
        }
        return false;
    }

    public function find_coupon_item_by_type($uid, $couponIds) {
        if (!empty($couponIds) && $uid) {
            return $this->find('all', array(
                'joins' => $this->joins_link,
                'fields' => array('Coupon.*', 'CouponItem.*'),
                'conditions' => array('CouponItem.coupon_id' => $couponIds, 'CouponItem.deleted = 0', 'CouponItem.bind_user' => $uid),
            ));
        }
        return false;
    }

    public function find_my_all_coupons($user_id) {
        return $this->find('all', array(
            'conditions' => array('CouponItem.bind_user' => $user_id, 'CouponItem.deleted = 0'),
            'joins' => $this->joins_link,
            'fields' => array('Coupon.*', 'CouponItem.*'),
            'order' => 'CouponItem.created desc'
        ));
    }

    public function read_spring_festivals($pid_lists) {

        $cache_key = '_spring_fes_coupon_types';
        $cached = Cache::read($cache_key);

        if (empty($cached)) {
            $couponM = ClassRegistry::init('Coupon');
            $found = $couponM->find('all', array(
                'conditions' => array(
                    'product_list' => $pid_lists,
                    'valid_end' => '2015-02-06 00:00:00'
                ),
                'fields' => array('id','reduced_price','product_list')
            ));

            $result = array();

            if (!empty($found)) {
                foreach ($found as $key => $value) {
                    $key = trim($key);
                    $result[$key] = $value;
                }
            }

            $cached = json_encode($result);
            Cache::write($cache_key, $cached);
        } else {
            $result = json_decode($cached, true);
        }

        $this->log("read_spring_festivals:". $cached);

        return $result;
    }

    public function find_got_spring_festival_coupons($user_id, $pid_lists) {
        $rtn = array();
        $spring_coupons = $this->read_spring_festivals($pid_lists);
        if (!empty($spring_coupons)) {
            $spring_coupon_ids = Hash::combine($spring_coupons,'{n}.Coupon.product_list','{n}.Coupon.id');
            $itemIdByCouponIds = $this->find('list', array(
                'conditions' => array('coupon_id' => $spring_coupon_ids, 'bind_user' => $user_id),
                'fields' => array('coupon_id', 'id')
            ) );

            if (!empty($itemIdByCouponIds)) {
                foreach ($spring_coupon_ids as $pid => $coupon_id) {
                    $rtn[$pid] = empty($itemIdByCouponIds[$coupon_id]) ? false : $itemIdByCouponIds[$coupon_id];
                }
                return $rtn;
            }
        }

        $this->log("find_got_spring_festival_coupons:".$user_id.", pid_lists:".json_encode($pid_lists).", result:".json_encode($rtn));

        return $rtn;
    }

    public function find_got_spring_festival_coupons_infos($pid_lists) {
        $spring_coupons = $this->read_spring_festivals($pid_lists);
        if (!empty($spring_coupons)) {
            return Hash::combine($spring_coupons,'{n}.Coupon.product_list','{n}.Coupon.reduced_price');
        }
    }

    public function find_my_valid_coupon_items($user_id, $couponItemIds, $brandId = null) {

        if (empty($couponItemIds) || !$user_id) return false;

        $dt = new DateTime();
        $cond = array('CouponItem.bind_user' => $user_id,
            'CouponItem.status' => COUPONITEM_STATUS_TO_USE,
            'CouponItem.id' => $couponItemIds,
            'CouponItem.deleted = 0',
            '(CouponItem.applied_order is null or CouponItem.applied_order = 0)',
            'Coupon.published' => 1,
            'Coupon.status' => COUPON_STATUS_VALID,
            'Coupon.valid_begin <= ' => $dt->format(FORMAT_DATETIME),
            'Coupon.valid_end >= ' => $dt->format(FORMAT_DATETIME),
        );
        if ($brandId) {
            $cond['OR'] = array('Coupon.brand_id' => $brandId, 'Coupon.brand_id is null', 'Coupon.brand_id = 0');
        }

        $items = $this->find('all', array(
            'conditions' => $cond,
            'joins' => $this->joins_link,
            'fields' => array('Coupon.*', 'CouponItem.*'),
            'order' => 'CouponItem.created desc'
        ));

        return $items;
    }

    public function find_latest_created_coupon_item($userId, $couponId) {
        return $this->find('first', array(
            'conditions' => array('CouponItem.bind_user' => $userId, 'CouponItem.coupon_id' => $couponId),
            'order' => 'CouponItem.created desc'
        ));
    }


    public function find_my_share_coupons_count($user_id, $limit_non_used = true){
        if (!$user_id) {
            return 0;
        }
        $dt = new DateTime();
        $cond = array('CouponItem.bind_user' => $user_id,
            'CouponItem.status' => COUPONITEM_STATUS_TO_USE,
            'CouponItem.deleted = 0',
            'Coupon.published' => 1,
            'Coupon.status' => COUPON_STATUS_VALID,
            'Coupon.valid_begin <= ' => $dt->format(FORMAT_DATETIME),
            'Coupon.valid_end >= ' => $dt->format(FORMAT_DATETIME),
            'Coupon.brand_id' => SHARE_COUPON_OFFER_TYPE,
        );
        if ($limit_non_used) {
            $cond[] = '(CouponItem.applied_order is null or CouponItem.applied_order = 0)';
        }
        //order by reduced default use first
        $count = $this->find('count', array(
            'conditions' => $cond,
            'joins' => $this->joins_link,
            'fields' => array('Coupon.*', 'CouponItem.*'),
            'order' => 'Coupon.reduced_price desc',
            'group' => array('CouponItem.coupon_id'),
        ));
        return $count;
    }

    public function find_my_all_valid_share_coupons($user_id, $limit_non_used = true){
        if (!$user_id) {
            return [];
        }
        $dt = new DateTime();
        $cond = array('CouponItem.bind_user' => $user_id,
            'CouponItem.status' => COUPONITEM_STATUS_TO_USE,
            'CouponItem.deleted = 0',
            'Coupon.published' => 1,
            'Coupon.status' => COUPON_STATUS_VALID,
            'Coupon.valid_begin <= ' => $dt->format(FORMAT_DATETIME),
            'Coupon.valid_end >= ' => $dt->format(FORMAT_DATETIME),
            'Coupon.brand_id' => SHARE_COUPON_OFFER_TYPE,
        );
        if ($limit_non_used) {
            $cond[] = '(CouponItem.applied_order is null or CouponItem.applied_order = 0)';
        }
        //order by reduced default use first
        $items = $this->find('all', array(
            'conditions' => $cond,
            'joins' => $this->joins_link,
            'fields' => array('Coupon.*', 'CouponItem.*'),
            'order' => 'Coupon.reduced_price desc',
            'group' => array('CouponItem.coupon_id'),
            'limit' => 100
        ));
        $sharerSource = Hash::extract($items, '{n}.CouponItem.source');
        array_walk($sharerSource, function (&$item1) {
            $item1 = str_replace('shared_offer', '', $item1);
        });
        $SharedOfferM = ClassRegistry::init('SharedOffer');
        $sharedOffers = $SharedOfferM->find('all', [
            'conditions' => [
                'SharedOffer.id' => $sharerSource
            ]
        ]);
        //$this->pid_list_to_array($items);
        return $items;
    }

    /**
     * @param $user_id
     * @param $sharer
     * @param bool $limit_non_used
     * @return array|bool
     * 获取分享的红包
     */
    public function find_my_valid_share_coupons($user_id, $sharer, $limit_non_used = true) {
        if (!$user_id || !$sharer) {
            return false;
        }
        $SharedOfferM = ClassRegistry::init('SharedOffer');
        $sharedOffers = $SharedOfferM->find_offers_by_weshare_creator($sharer);
        $sharerSource = Hash::extract($sharedOffers, '{n}.SharedOffer.id');
        array_walk($sharerSource, function (&$item1) {
            $item1 = 'shared_offer' . $item1;
        });
        //$this->log('query coupon item source '.json_encode($sharerSource));
        $dt = new DateTime();
        $cond = array('CouponItem.bind_user' => $user_id,
            'CouponItem.status' => COUPONITEM_STATUS_TO_USE,
            'CouponItem.deleted = 0',
            'Coupon.published' => 1,
            'Coupon.status' => COUPON_STATUS_VALID,
            'Coupon.valid_begin <= ' => $dt->format(FORMAT_DATETIME),
            'Coupon.valid_end >= ' => $dt->format(FORMAT_DATETIME),
            'Coupon.brand_id' => SHARE_COUPON_OFFER_TYPE,
            'CouponItem.source' => $sharerSource
        );
        if ($limit_non_used) {
            $cond[] = '(CouponItem.applied_order is null or CouponItem.applied_order = 0)';
        }
        //order by reduced default use first
        $items = $this->find('all', array(
            'conditions' => $cond,
            'joins' => $this->joins_link,
            'fields' => array('Coupon.*', 'CouponItem.*'),
            'order' => 'Coupon.reduced_price desc',
            'group' => array('CouponItem.coupon_id')
        ));
        $this->pid_list_to_array($items);
        //$this->log('fetch share coupon '.json_encode($items));
        return $items;
    }

    public function find_my_valid_coupons($user_id, $brandId = null, $limit_non_used = true) {
        if (!$user_id) { return false; }

        $dt = new DateTime();
        $cond = array('CouponItem.bind_user' => $user_id,
            'CouponItem.status' => COUPONITEM_STATUS_TO_USE,
            'CouponItem.deleted = 0',
            'Coupon.published' => 1,
            'Coupon.status' => COUPON_STATUS_VALID,
            'Coupon.valid_begin <= ' => $dt->format(FORMAT_DATETIME),
            'Coupon.valid_end >= ' => $dt->format(FORMAT_DATETIME)
        );
        if ($limit_non_used) {
            $cond[] = '(CouponItem.applied_order is null or CouponItem.applied_order = 0)';
        }
        if ($brandId) {
            $cond['OR'] = array('Coupon.brand_id' => $brandId, 'Coupon.brand_id is null', 'Coupon.brand_id = 0');
        }

        $items = $this->find('all', array(
            'conditions' => $cond,
            'joins' => $this->joins_link,
            'fields' => array('Coupon.*', 'CouponItem.*'),
            'order' => 'CouponItem.created desc',
            'group' => array('CouponItem.coupon_id')
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
        return $this->updateAll(array('sent_message_status' => COUPONITEM_MESSAGE_STATUS_SENT),
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

    /**
     * @param $couponId
     * @param $time
     * @return array
     */
    protected function key_hourly($couponId, $time) {
        $hourStr = date('YmdH', $time);
        $key = 'ci_count_h_' . $couponId . '-' . $hourStr;
        return array($hourStr, $key);
    }

    /**
     * @param $couponId
     * @param $day
     * @return string
     */
    private function key_coupon_count_day($couponId, $day) {
        $key = 'ci_count_' . $couponId . '_' . $day;
        return $key;
    }

    /**
     * @param $couponId
     * @param $time
     * @return array
     */
    public function couponCountHourlyNoCache($couponId, $time) {
        $hourStr = date('YmdH', $time);
        $result = $this->find('count', array(
            'conditions' => array('coupon_id' => $couponId, 'deleted = 0', "date_format(created, '%Y%m%d%H') = '" . $hourStr . "'")
        ));
        return $result;
    }
}