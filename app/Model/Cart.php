<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/22/14
 * Time: 11:14 PM
 */

class Cart extends AppModel {

    public function merge_user_carts_after_login($uid, $oldSid) {
        $cond_carts_of_users = array(
            'session_id' => $oldSid,
            'status' => 0,
            'order_id' => NULL);

        $oldCartItems = $this->find('all', array('conditions' => $cond_carts_of_users));

        $old_pids = Hash::extract($oldCartItems, '{n}.Cart.product_id');

         if (!empty($old_pids)) {
             $this->deleteAll(array('creator' => $uid,
                 'product_id' => $old_pids,
                 'OR' => array('session_id != ' => $oldSid, 'session_id' => NULL),
                 'order_id' => NULL,
                 'status' => 0), false);
         }

        $this->updateAll(array('creator' => $uid, 'session_id' => NULL), $cond_carts_of_users);
    }

    public function balanced_items($pid, $creator) {
        return $this->find('all', array(
            'conditions' => array('product_id' => $pid, 'status' => CART_ITEM_STATUS_BALANCED, 'num > ' => 0, 'creator' => $creator),
            'fields' => array('num', 'order_id'),
        ));
    }

    public function find_try_cart_item($pid, $creator) {
        return $this->find('first', array(
            'conditions' => array('product_id' => $pid, 'status ' => CART_ITEM_STATUS_NEW, 'num > ' => 0, 'creator' => $creator, 'type' => CART_ITEM_TYPE_TRY),
        ));
    }

    /**
     * Find balanced Cart items (product_id, num, name, creator, cover img) through cache.
     * Balanced items won't change.
     * @param $order_id int order_id or order id list.
     * @return array Cart elements
     */
    public function find_balanced_items($order_id) {
        if (empty($order_id)) {
            return false;
        }
        $balanced_order_key = "balanced_cart_items_" . $order_id;
        $cache = Cache::read($balanced_order_key);
        if (empty($cache)) {
            $carts = $this->find('all', array(
                'conditions' => array('order_id' => $order_id, 'status' => CART_ITEM_STATUS_BALANCED),
                'fields' => array('id','num', 'product_id', 'name', 'creator', 'coverimg'),
            ));
            $jsonStr = json_encode($carts);
            Cache::write($balanced_order_key, $jsonStr);
            return $carts;
        } else {
            return json_decode($cache, true);
        }
    }

    /**
     * @param $product_id
     * @param int $num
     * @param int $specId specified id for
     * @param int $type
     * @param int $try_id
     * @param null $uid
     * @param null $sessionId
     * @param null $prodTry
     * @param null $shichituan
     * @throws Exception
     * @return mixed On success Model::$data if its not empty or true, false on failure
     */
    public function add_to_cart($product_id, $num = 1, $specId = 0, $type = CART_ITEM_TYPE_NORMAL, $try_id = 0,
                                $uid = null, $sessionId=null, $prodTry = null, $shichituan = null,$tuan_param=array()) {

        $user_cond = $this->create_user_cond($uid, $sessionId);

        $Carts = $this->find('first', array(
            'conditions' => array(
                'product_id' => $product_id,
                'order_id' => null,
                'try_id' => $try_id,
                'specId' => $specId,
                'OR' => $user_cond
            )));

        $data = array();
        if (!empty($Carts)) {
            $data['Cart']['id'] = $Carts['Cart']['id'];
        }

        $data['Cart']['num'] = $num;
        $data['Cart']['product_id'] = $product_id;
        $data['Cart']['status'] = CART_ITEM_STATUS_NEW;

        $proM = ClassRegistry::init('Product');
        $p = $proM->findById($product_id);

        if (!empty($prodTry)) {
            $price = calculate_try_price($prodTry['ProductTry']['price'], $uid, $shichituan);
            //$cart_name = $p['Product']['name'].'(试吃: '.$prodTry['ProductTry']['spec'].')';
            //$cart_name = $p['Product']['name'].'(规格: '.$prodTry['ProductTry']['spec'].')';
            $cart_name = $p['Product']['name'];
        } else {
            $result = get_spec_by_pid_and_sid(array(
                    array('pid' => $product_id, 'specId' => $specId, 'defaultPrice' => $p['Product']['price']),
            ));
            $spec_detail_arr = $result[cart_dict_key($product_id, $specId)];
            //$cart_name =  $p['Product']['name'] . (empty($spec_detail_arr[1])?'':'('.$spec_detail_arr[1].')');
            $cart_name = $p['Product']['name'];
            list($price, $special_id) = calculate_price($p['Product']['id'], $spec_detail_arr[0], $uid, $num,0,null,$tuan_param);
        }

        $data['Cart']['session_id'] = $sessionId;
        $data['Cart']['coverimg'] = $p['Product']['coverimg'];
        $data['Cart']['name'] = $cart_name;

        $data['Cart']['price'] = $price;
        $data['Cart']['creator'] = $uid;
        $data['Cart']['specId'] = $specId;
        $data['Cart']['type'] = $type;
        $data['Cart']['try_id'] = $try_id;
        if(!empty($tuan_param)){
            $data['Cart']['tuan_buy_id'] = $tuan_param['tuan_buy_id'];
        }
        if (!empty($special_id)) {
            $data['Cart']['applied_special'] = $special_id;
        }

        return $this->save($data);
    }

    function delete_item($id, $uid, $sessionId = null) {
        $cond = $this->create_user_cond($uid, $sessionId);

        return $this->deleteAll(array(
            'status' => CART_ITEM_STATUS_NEW,
            'id' => $id,
            'order_id' => NULL,
            'OR' => $cond
        ));
    }

    function edit_num($id, $num, $uid, $sessionId = null) {
        $user_cond = $this->create_user_cond($uid, $sessionId);
        if ($num <= 0) {
            $op_flag = $this->deleteAll(array('id' => $id, 'status' => CART_ITEM_STATUS_NEW, 'order_id' => NULL, 'OR' => $user_cond), true, true);
        }
        else{
            $op_flag = $this->updateAll(array('num' => $num), array('id' => $id, 'status' => CART_ITEM_STATUS_NEW, 'order_id' => NULL, 'OR' => $user_cond));
        }
        return $op_flag;
    }

    /**
     * @param $uid
     * @param $sessionId
     * @return array
     * @throws Exception
     */
    private function create_user_cond($uid, $sessionId) {
        return create_user_cond($uid, $sessionId);
    }

} 