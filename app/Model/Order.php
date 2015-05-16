<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/10/14
 * Time: 5:04 PM
 */

class Order extends AppModel {

    public function createOrFindGrouponOrder($memberId, $uid, $fee, $product_id, $type = ORDER_TYPE_GROUP, $area='', $address='', $mobile='', $name='') {
        if ($type != ORDER_TYPE_GROUP && $type != ORDER_TYPE_GROUP_FILL) {
            throw new CakeException("error order type:".$type);
        }

        $existsOrder = $this->find('first', array(
            'conditions' => array('member_id' => $memberId,
                'creator' => $uid,
                'type' => $type,
            )
        ));

        if (empty($existsOrder)) {

            $product = ClassRegistry::init('Product')->find('first', array(
                'conditions' => array('id' => $product_id),
                'fields' => 'brand_id',
            ));
            $brand_id = empty($product)? 0 : $product['Product']['brand_id'];

            $arr = array(
                'creator' => $uid,
                'total_all_price' => $fee,
                'type' => $type,
                'brand_id' => $brand_id,
                'member_id' => $memberId,
                'consignee_area' => $area,
                'consignee_name' => $name,
                'consignee_address' => $address,
                'consignee_mobilephone' => $mobile,
            );
            $order = $this->save($arr);
            if (!empty($order)) {
                $cartM = ClassRegistry::init('Cart');
                $inserted = $cartM->add_to_cart($product_id, 1, 0, CART_ITEM_TYPE_GROUPON_PROM, 0, $uid);
                $cartM->updateAll(array('order_id' => $order['Order']['id'], 'status' => CART_ITEM_STATUS_BALANCED), array('id' => $inserted['Cart']['id']));
            }

            return $this->findById($order['Order']['id']);
        } else {

            if (abs($existsOrder['Order']['total_all_price'] * 100 - $fee * 100) >= 1 && $existsOrder['Order']['status'] == ORDER_STATUS_WAITING_PAY) {
                $this->updateAll(array('total_all_price'  => $fee), array('id' => $existsOrder['Order']['id'], 'status' => ORDER_STATUS_WAITING_PAY));
                return $this->createOrFindGrouponOrder($memberId, $uid, $fee, $product_id, $type, $area, $address, $mobile, $name);
            } else {
                return $existsOrder;
            }

        }
    }
    public function createTuanOrder($memberId, $uid, $fee, $product_id, $type = ORDER_TYPE_TUAN, $area='', $address='', $mobile='', $name='', $cart_id,$ship_mark,$shop_name, $shop_id=0) {
        if ($type != ORDER_TYPE_TUAN && $type != ORDER_TYPE_TUAN_SEC) {
            throw new CakeException("error order type:".$type);
        }
        $product = ClassRegistry::init('Product')->find('first', array(
            'conditions' => array('id' => $product_id),
            'fields' => 'brand_id',
        ));
        $brand_id = empty($product)? 0 : $product['Product']['brand_id'];

        if($type==ORDER_TYPE_TUAN_SEC){
            $arr = array(
                'creator' => $uid,
                'total_price' => $fee,
                'total_all_price' => $fee,
                'type' => $type,
                'brand_id' => $brand_id,
                'try_id' => $memberId,
                'consignee_id' => $shop_id,
                'consignee_area' => $area,
                'consignee_name' => $name,
                'consignee_address' => $address,
                'consignee_mobilephone' => $mobile,
                'ship_mark' => $ship_mark,
                'status' => ORDER_STATUS_WAITING_PAY,
                'remark' => $shop_name
            );
        }

        if($type==ORDER_TYPE_TUAN){
            $arr = array(
                'creator' => $uid,
                'total_price' => $fee,
                'total_all_price' => $fee,
                'type' => $type,
                'brand_id' => $brand_id,
                'member_id' => $memberId,
                'consignee_id' => $shop_id,
                'consignee_area' => $area,
                'consignee_name' => $name,
                'consignee_address' => $address,
                'consignee_mobilephone' => $mobile,
                'ship_mark' => $ship_mark,
                'status' => ORDER_STATUS_WAITING_PAY,
                'remark' => $shop_name
            );
        }

        if(empty($arr)){
            return null;
        }

        $order = $this->save($arr);
        if (!empty($order)) {
            $cartM = ClassRegistry::init('Cart');
            $cartM->updateAll(array('order_id' => $order['Order']['id'], 'status' => CART_ITEM_STATUS_BALANCED), array('id' => $cart_id));
            return $order;
        }else{
            return null;
        }

    }
    /**
     * @param $operator
     * @param $order_id
     * @param $owner
     * @return bool
     */
    public function cancelWaitingPayOrder($operator, $order_id, $owner) {

        list($rtn, $affectedRows) = $this->update_order_status($order_id, ORDER_STATUS_CANCEL, ORDER_STATUS_WAITING_PAY, $operator);

        if ($rtn && $affectedRows >= 1) {
            ClassRegistry::init('CouponItem')->unapply_coupons($owner, $order_id);
            $cartModel = ClassRegistry::init('Cart');
            $boughts = $cartModel->find('list', array(
                'conditions' => array('order_id' => $order_id, 'status' => CART_ITEM_STATUS_BALANCED),
                'fields' => 'product_id, num'
            ));
            if(!empty($boughts)) {
                $productModel = ClassRegistry::init('Product');
                foreach($boughts as $pid => $num) {
                    $productModel->update_storage_saled($pid, -$num);
                }
            }
        }

        return $rtn;
    }

    public function set_order_to_paid($orderId, $isTry, $orderOwner, $type, $memberId=0) {
        $rtn = $this->updateAll(array('status' => ORDER_STATUS_PAID, 'pay_time' => "'" . date(FORMAT_DATETIME) . "'")
            , array('id' => $orderId, 'status' => ORDER_STATUS_WAITING_PAY));
        $sold = $rtn && $this->getAffectedRows() >= 1;
        if ($sold) {
            $cartM = ClassRegistry::init('Cart');
            $cartItems = $cartM->find_balanced_items($orderId);
            if (!empty($cartItems)) {
                $pid_list = Hash::extract($cartItems, '{n}.Cart.product_id');
            }
            if (!empty($pid_list)) {
                if ($isTry) {
                    $shichiM = ClassRegistry::init('OrderShichi');
                    foreach ($pid_list as $pid) {
                        $shichiM->create();
                        $shichiM->save(array('OrderShichi' => array(
                            'data_id' => $pid,
                            'creator' => $orderOwner,
                            'order_id' => $orderId,
                        )));
                    }
                    $tryM = ClassRegistry::init('ProductTry');
                    $pTry = $tryM->findById($isTry);
                    if (!empty($pTry)) {
                        //FIXME: do retry if failed
                        $tryM->updateAll(array('sold_num' => 'sold_num + 1'), array('id' => $isTry, 'modified' => $pTry['ProductTry']['modified']));
                    }
                } else if ($type == ORDER_TYPE_GROUP || $type == ORDER_TYPE_GROUP_FILL) {
                    $gmM = ClassRegistry::init('GrouponMember');
                    $gmM->paid_done($memberId, $orderOwner, $type);
                }elseif($type == ORDER_TYPE_TUAN){
                    $gmM = ClassRegistry::init('TuanBuying');
                    $gmM->paid_done($memberId,$orderId);
                } else {
                    foreach ($pid_list as $pid) {
                        clean_total_sold($pid);
                    }
                }
            }
            $this->update_refer($orderOwner,$orderId);
        }
        return $sold;
    }

    /**
     * @param $uid
     * @param $order_status  int|array
     * @param bool $only_normal_type
     * @throws CakeException
     * @return array orders, order_carts and mapped brands
     */
    public function get_user_orders($uid, $order_status=null, $only_normal_type = true) {

        $cond = array('creator' => $uid, 'published' => PUBLISH_YES, 'deleted' => DELETED_NO);
        if ($order_status !== null) {
            $cond['status'] = $order_status;
        }

        if ($only_normal_type) {
            $cond['type'] = array(ORDER_TYPE_DEF, ORDER_TYPE_GROUP_FILL, ORDER_TYPE_TUAN,ORDER_TYPE_TUAN_SEC);
        }

        $orders = $this->find('all', array(
            'order' => 'id desc',
            'conditions' => $cond,
        ));
        $order_ids = array();
        $brandIds = array();
        foreach ($orders as $o) {
            $order_ids[] = $o['Order']['id'];
            $brandIds[] = $o['Order']['brand_id'];
        }

        $order_carts = array();
        if (!empty($order_ids)) {
            $cartM = ClassRegistry::init('Cart');
            $Carts = $cartM->find('all', array(
                'conditions' => array(
                    'order_id' => $order_ids,
                    'creator' => $uid,
//                    'status' => CART_ITEM_STATUS_BALANCED,
                )));

            foreach ($Carts as $c) {
                $order_id = $c['Cart']['order_id'];
                if (!isset($order_carts[$order_id])) $order_carts[$order_id] = array();
                $order_carts[$order_id][] = $c;
            }
        }

        $mappedBrands = array();
        if (!empty($brandIds)) {
            $brandM = ClassRegistry::init('Brand');
            $brands = $brandM->find('all', array(
                'conditions' => array('id' => $brandIds),
                'fields' => array('id', 'name', 'created', 'slug', 'coverimg')
            ));

            foreach ($brands as $brand) {
                $mappedBrands[$brand['Brand']['id']] = $brand;
            }
        }
        return array($orders, $order_carts, $mappedBrands);
    }

    public function find_my_order_byId($orderId, $uid) {
        return $this->find('first', array(
            'conditions' => array('id' => $orderId, 'creator' => $uid),
        ));
    }

    public function find_all_my_order_byId($orderIds, $uid) {
        return $this->find('all', array(
            'conditions' => array('id' => $orderIds, 'creator' => $uid),
        ));
    }

    public function count_to_comments($uid) {
        return $this->find('count', array(
            'conditions' => array('creator' => $uid, 'status' => array(ORDER_STATUS_RECEIVED, ORDER_STATUS_SHIPPED), 'is_comment != '.ORDER_COMMENTED),
        ));
    }

    public function count_received_order($uid) {
        return $this->find('count', array(
            'conditions' => array('creator' => $uid, 'status' => array(ORDER_STATUS_RECEIVED)),
        ));
    }

    public function count_to_confirm_received($uid) {
        $result = $this->query('select count(1) as cnt, ifnull(sum(floor(total_all_price)), 0) as total_price from cake_orders where status='.ORDER_STATUS_SHIPPED.' and creator='.$uid.' and deleted='.DELETED_NO.' and published='.PUBLISH_YES);
        return array($result[0][0]['cnt'], $result[0][0]['total_price']);
    }

    public function count_by_status($uid) {
        $rtn = $this->query('select count(1) as cnt, status from cake_orders where creator='.$uid.' and deleted='.DELETED_NO.' and published='.PUBLISH_YES.' group by status');
        $count = array();
        foreach($rtn as $row) {
            $count[$row['cake_orders']['status']] = $row[0]['cnt'];
        }

        return $count;
    }
//
//    public function whether_bought($pid, $creator) {
//        $cartM = ClassRegistry::init('Cart');
//        $cartItems = $cartM->balanced_items($pid, $creator);
//        $order_ids = Hash::extract($cartItems, '{n}.Cart.order_id');
//
//        if (!empty($order_ids)) {
//            $this->find('all', array(
//                'conditions' => array
//            ));
//        }
//    }

    public function used_code_cnt($uid, $code) {
        return $this->find('count', array('conditions' => array(
            'creator' => $uid,
            'applied_code' => $code,
        )));
    }

    /**
     * Update order status common method
     * @param $order_id
     * @param $toStatus
     * @param $origStatus
     * @param int $operator
     * @return array operation update result and affected rows
     */
    function update_order_status($order_id, $toStatus, $origStatus, $operator) {
        $result = $this->updateAll(array('status' => $toStatus, 'lastupdator' => $operator), array('id' => $order_id, 'status' => $origStatus));

        if ($result) {
            $affectedRows = $this->getAffectedRows();
            if ($origStatus == ORDER_STATUS_SHIPPED && $toStatus == ORDER_STATUS_RECEIVED) {
                $this->log('change order '.$order_id.' status from '.$toStatus.' to '.$origStatus);
                $order = $this->findById($order_id);
                $scoreM = ClassRegistry::init('Score');
                if (!empty($order)) {
                    $creator = $order['Order']['creator'];
                    $rtn = $scoreM->add_score_by_bought($creator, $order_id, $order['Order']['total_all_price']);
                    $this->log('add_score_by_bought: uid='.$creator.', order_id='.$order_id.', result:'. json_encode($rtn));
                    if (!empty($rtn)) {
                        $userM = ClassRegistry::init('User');
                        $userM->add_score($creator, $rtn['Score']['score']);
                    }

                    $urM = ClassRegistry::init('Refer');
                    $this->log("debug: before update_referred_new_order");
                    try {
                        $urM->test();
                        $urM->update_referred_new_order($creator);
                    }catch (Exception $e){
                        $this->log("error:".$e);
                    }
                    $this->log("debug: end update_referred_new_order");
                }
            } else if ($origStatus == ORDER_STATUS_WAITING_PAY && $toStatus == ORDER_STATUS_CANCEL) {
                $order = $this->findById($order_id);
                $scoreM = ClassRegistry::init('Score');
                if (!empty($order)) {
                    $creator = $order['Order']['creator'];
                    $rtn = $scoreM->restore_score_by_undo_order($creator, $order['Order']['applied_score'], $order_id);
                    $this->log('restore_score_by_undo_order: uid='.$creator.', order_id='.$order_id.', result:'. json_encode($rtn));
                    if (!empty($rtn)) {
                        $userM = ClassRegistry::init('User');
                        $userM->add_score($creator, $rtn['Score']['score']);
                    }
                }
            }
        } else {
            $affectedRows = 0;
        }
        return array($result, $affectedRows);
    }

    function update_refer($user_id,$order_id){
        $referM = ClassRegistry::init('Refer');
        $refer = $referM->find('first',array(
            'conditions'=>array(
                'to'=>$user_id,
                'first_order_id' => 0,
                'first_order_done' => 0
            ),
            'fields' => array(
                'id','from'
            )
        ));
        if($refer){
            if($referM->updateAll(array('first_order_done'=>1,'first_order_id'=>$order_id),array('id'=>$refer['refer']['id']))){
                //add 900 score
                $scoreM = ClassRegistry::init('Score');
                $uM = ClassRegistry::init('User');
                $user = $uM->find('first',array('conditions' => array('id' => $user_id)));
                $scoreM->add_score_by_refer_user_first_order(900, $user_id, $user['User']['nickname'], $refer['Refer']['from']);
            }
        }
    }
}
