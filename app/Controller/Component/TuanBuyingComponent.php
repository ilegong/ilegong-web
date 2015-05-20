<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 5/18/15
 * Time: 12:29
 */

class TuanBuyingComponent extends Component{

    var $name = 'TuanBuyingComponent';

    public $components = array('Session');

    /**
     * @param $product_id
     * @param $product_num
     * @param $spec_id
     * @param $type
     * @param $uId
     * @param $cart_tuan_param
     * @param $consignment_date_id
     * @param $send_date
     * @param $way_id
     * @param $way_type
     *
     * @return array|void
     */
    public function add_cart($product_id,$product_num,$spec_id,$type,$uId,$cart_tuan_param,$consignment_date_id,$send_date,$way_id,$way_type){
        $this->Cart = ClassRegistry::init('Cart');
        $sessionId = $this->Session->id();
        $cartInfo = $this->Cart->add_to_cart($product_id,$product_num,$spec_id,$type,0,$uId, $sessionId,  null, null,$cart_tuan_param);
        $this->log('cartInfo: '.json_encode($cartInfo));
        if($cartInfo){
            $result = $this->Cart->updateAll(array('consignment_date' => $consignment_date_id, 'send_date' => "'".$send_date."'"), array('id' => $cartInfo['Cart']['id']));
            if(!$result){
                echo json_encode(array('success'=> false, 'error' => '发货时间选择有误，请重新点击购买'));
                $this->log("failed to update consignment_date and send_date for cart ".$cartInfo['Cart']['id'].": consignment_date: ".$consignment_date_id.", send_date: ".$send_date);
                return;
            }
            $cart_array = array(0 => strval($cartInfo['Cart']['id']));
            if(strpos($way_type,ZITI_TAG)===false){
                return array('success' => true, 'direct'=>'normal', 'cart_id'=>$cartInfo['Cart']['id'],'way_id'=>$way_id,'cart_array'=>$cart_array);
            }else{
                return array('success' => true, 'direct'=>'big_tuan_list', 'cart_id'=>$cartInfo['Cart']['id'],'way_id'=>$way_id,'cart_array'=>$cart_array);
            }
            $this->Session->write(self::key_balance_pids(), json_encode($cart_array));
        }else{
            return array('success' => false,'error' => '对不起，系统出错，请联系客服');
        }
    }

    /**
     * @param $tuan_id
     * @param $cart_id
     * @param $try_id
     *
     * @return array
     */
    public function balance_sec_kill($uid,$tuan_id,$cart_id,$try_id){
        $ship_type=-1;
        $result_data = array();
        if(!empty($tuan_id)){
            $this->TuanTeam = ClassRegistry::init('TuanTeam');
            $team = $this->TuanTeam->find('first',array(
                'conditions' => array(
                    'id' => $tuan_id
                )
            ));
            if(empty($team)){
                //$this->__message('该团不存在', '/tuan_teams/mei_shi_tuan');
                $result_data['success'] = false;
                $result_data['fail_reason'] = '该团不存在';
                return $result_data;
            }
            $offline_store = $this->get_offline_store($team['TuanTeam']['offline_store_id']);
            $result_data['tuan_id'] = $tuan_id;
            $result_data['can_mark_address'] = $this->can_mark_address($tuan_id);
            $result_data['tuan_address'] = get_address($team,$offline_store);
        }else{
            //is global sec
            $shipSetting = $this->get_ship_setting(null,$try_id,'Try');
            if(!empty($shipSetting)){
                //ziti address type
                $ship_type = $shipSetting['ProductShipSetting']['ship_val'];
            }
        }
        $Carts = $this->get_cart($cart_id);
        $pid = $Carts['Cart']['product_id'];
        $total_price = $Carts['Cart']['price'] * $Carts['Cart']['num'];
        $brand = $this->get_brand($pid);
        $result_data['try_id'] = $try_id;
        $result_data['ship_type'] = $ship_type;
        $result_data['buy_count'] = $Carts['Cart']['num'];
        $result_data['total_price'] = $total_price;
        $result_data['cart_id'] = $Carts['Cart']['id'];
        $result_data['cart_info'] = $Carts;
        $result_data['brand'] = $brand['Brand'];
        $result_data['consignee_info'] = $this->get_old_consignees($uid,$shipSetting);
        $result_data['success'] = true;
        return $result_data;
    }

    /**
     * @param $uid
     * @param $tuan_buy_id
     * @param $cart_id
     * @param int $way_id
     * @return array
     */
    public function balance_tuan($uid,$tuan_buy_id, $cart_id, $way_id=0){
        $result_data = array();
        $Carts = $this->get_cart($cart_id);
        $this->TuanBuying = ClassRegistry::init('TuanBuying');
        $tuan_b = $this->TuanBuying->find('first', array(
            'conditions' => array('id' => $tuan_buy_id),
            'fields' => array('pid', 'tuan_id', 'status', 'end_time')
        ));
        $current_time = strtotime($tuan_b['TuanBuying']['end_time']);
        $sold_num = $tuan_b['TuanBuying']['sold_num'];
        $max_num = $tuan_b['TuanBuying']['max_num'];
        //限量
        if ($max_num > 0) {
            if ($sold_num >= $max_num) {
                $result_data['success'] = false;
                $result_data['fail_reason'] = '该团购已经结束';
                return $result_data;
            }
        }
        if (empty($tuan_b) && $current_time < time()) {
            $result_data['success'] = false;
            $result_data['fail_reason'] = '该团购已到截止时间';
            return $result_data;
        }
        $this->TuanTeam = ClassRegistry::init('TuanTeam');
        $tuan_info = $this->TuanTeam->findById($tuan_b['TuanBuying']['tuan_id']);
        if (empty($tuan_info)) {
            $result_data['success'] = false;
            $result_data['fail_reason'] = '该团不存在';
            return $result_data;
        }
        $offline_store = $this->get_offline_store($tuan_info['TuanTeam']['offline_store_id']);
        $total_price = $Carts['Cart']['price'] * $Carts['Cart']['num'];
        $pid = $tuan_b['TuanBuying']['pid'];
        $ship_way = ZITI_TAG;
        $ship_val = -1;
        //way_id 代表设置商品的发货方式
        if ($way_id != 0) {
            //发货方式不为0要计算邮费
            $shipSetting = $this->get_ship_setting($way_id, $pid, 'Product');
            if (empty($shipSetting)) {
                $result_data['success'] = false;
                $result_data['fail_reason'] = '选择物流方式错误';
                return $result_data;
            }
            //发货方式代码
            $ship_way = TuanShip::get_ship_code($shipSetting['ProductShipSetting']['ship_type']);
            //邮费
            $ship_val = $shipSetting['ProductShipSetting']['ship_val'];
        }

        if (strpos($ship_way, ZITI_TAG) === false) {
            $shipFee = intval($ship_val) / 100;
            if ($shipFee > 0) {
                $total_price = $total_price + intval($ship_val) / 100;
            }
        } else {
            //如果是自提 ship_val 代表自提点类型
            $result_data['ship_val'] = $ship_val;
        }
        $brand = $this->get_brand($pid);
        $could_score_money = $this->get_score_usable($uid, $total_price);
        $result_data['consignee_info'] = $this->get_old_consignees($uid,$shipSetting);
        $tuan_id = $tuan_info['TuanTeam']['id'];
        $result_data['score_usable'] = $could_score_money * 100;
        $result_data['way_id'] = $way_id;
        $result_data['way_type'] = $ship_way;
        $result_data['buy_count'] = $Carts['Cart']['num'];
        $result_data['total_price'] = $total_price;
        $result_data['cart_id'] = $Carts['Cart']['id'];
        $result_data['tuan_id'] = $tuan_id;
        $result_data['can_mark_address'] = $this->can_mark_address($tuan_id);
        $result_data['tuan_address'] = get_address($tuan_info, $offline_store);
        $result_data['end_time'] = date('m-d', $current_time);
        $result_data['tuan_buy_id'] = $tuan_buy_id;
        $result_data['cart_info'] = $Carts;
        $result_data['max_num'] = $max_num;
        $result_data['brand'] = $brand['Brand'];
        if ($tuan_info['TuanTeam']['type'] == IS_BIG_TUAN) {
            $result_data['big_tuan'] = true;
        }
        $result_data['success'] = false;
    }

    /**
     * @param $cart_id
     * @param $tuan_id
     * @param $member_id
     * @param $mobile
     * @param $name
     * @param $uid
     * @param $way_id
     * @param $tuan_sec
     * @param $global_sec
     * @param $shop_id
     * @param $p_address
     * @return array
     */
    public function make_order($cart_id,$tuan_id,$member_id,$mobile,$name,$uid,$way_id,$tuan_sec,$global_sec,$shop_id,$p_address){
        $result_data = array();
        if (empty($uid)) {
            $result_data['success'] = false;
            $result_data['fail_reason'] = "没有登录,请先登录";
            return $result_data;
        }
        if(empty($cart_id)){
            $result_data['success'] = false;
            $result_data['fail_reason'] = "下单失败,请重新下单";
            return $result_data;
        }

        $this->Cart = ClassRegistry::init('Cart');
        $this->Order = ClassRegistry::init('Order');
        $this->TuanTeam = ClassRegistry::init('TuanTeam');
        $this->OfflineStore = ClassRegistry::init('OfflineStore');

        $cart_info = $this->Cart->findById($cart_id);
        $creator = $cart_info['Cart']['creator'];
        $order_type = $cart_info['Cart']['type'];
        if(empty($cart_info)){
            $result_data['success'] = false;
            $result_data['fail_reason'] = "购物车记录为空";
            return $result_data;
        }
        if($creator != $uid){
            $result_data['success'] = false;
            $result_data['fail_reason'] = "团购订单不属于你，请刷新重试";
            return $result_data;
        }
        if($order_type != CART_ITEM_TYPE_TUAN&&$order_type != CART_ITEM_TYPE_TUAN_SEC){
            $result_data['success'] = false;
            $result_data['fail_reason'] = "该订单不属于团购订单，请重试";
            return $result_data;
        }

        if (!empty($cart_info['Cart']['order_id'])) {
            $result_data['success'] = false;
            $result_data['fail_reason'] = "订单错误,请重新下单";
            return $result_data;
        }

        $total_price = $cart_info['Cart']['num'] * $cart_info['Cart']['price'];
        if ($total_price < 0) {
            $result_data['success'] = false;
            $result_data['fail_reason'] = "订单错误,请重新下单";
            return $result_data;
        }

        $pid = $cart_info['Cart']['product_id'];
        $area = '';
        //全局秒杀
        if ($global_sec != "true") {
            $tuan_info = $this->TuanTeam->findById($tuan_id);
            if (empty($tuan_info)) {
                $result_data['success'] = false;
                $result_data['fail_reason'] = "团购订单没有绑定团队，请重试";
                return $result_data;
            }
        }
        $way = ZITI_TAG;
        //设置了发货方式
        if ($way_id != 0) {
            $shipSetting = $this->get_ship_setting($way_id, $pid, 'Product');
            if (empty($shipSetting)) {
                $result_data['success'] = false;
                $result_data['fail_reason'] = "该订单物流方式设置有误，请重试";
                return $result_data;
            }
            $shipTypeId = $shipSetting['ProductShipSetting']['ship_type'];
            $way = TuanShip::get_ship_code($shipTypeId);
        }

        //大团或者全局秒杀
        if ($tuan_info['TuanTeam']['type'] == IS_BIG_TUAN || $global_sec == 'true') {
            if (!empty($shipSetting)) {
                //不是自提 要计算邮费
                if (strpos($way, ZITI_TAG) === false) {
                    $shipFee = intval($shipSetting['ProductShipSetting']['ship_val']) / 100;
                    if ($shipFee > 0) {
                        $total_price = $total_price + $shipFee;
                    }
                    $consignees = array('name' => $name, 'mobilephone' => $mobile, 'status' => STATUS_CONSIGNEES_TUAN,'address'=>$p_address);
                    //update consignes address
                    $this->merge_order_consignes($uid,$consignees);
                }else{
                    //rember last ziti address
                    if (empty($shipSetting) || strpos(TuanShip::get_ship_code($shipTypeId), ZITI_TAG) !== false) {
                        //ziti
                        if (!empty($shop_id)) {
                            $offline_store = $this->OfflineStore->findById($shop_id);
                            if (!empty($offline_store)) {
                                $address = get_address($tuan_info, $offline_store);
                                //save user ziti address
                                $this->merge_ziti_order_consignes($uid,$offline_store,$address);
                            }
                        }
                    }
                }
            }
            $address = $p_address;
        } else {
            //小团购买 获取自提点地址
            $offline_store = $this->get_offline_store($tuan_info['TuanTeam']['offline_store_id']);
            $address = get_address($tuan_info, $offline_store);
            if (!empty($p_address)) {
                //has a remark address
                $address = $address . '[' . $p_address . ']';
            }
        }

        if ($tuan_sec == 'true') {
            //remark order sec kill
            $order = $this->Order->createTuanOrder($member_id, $uid, $total_price, $pid, $order_type, $area, $address, $mobile, $name, $cart_id, $way, $shop_id);
        } else {
            $order = $this->Order->createTuanOrder($member_id, $uid, $total_price, $pid, $order_type, $area, $address, $mobile, $name, $cart_id, $way, $shop_id);
        }
        $order_id = $order['Order']['id'];
        $score_consumed = 0;
        $spent_on_order = intval($this->Session->read(self::key_balanced_scores()));
        $order_id_spents = array();
        if ($spent_on_order > 0) {
            $reduced = $spent_on_order / 100;
            $toUpdate = array('applied_score' => $spent_on_order,
                'total_all_price' => 'if(total_all_price - ' . $reduced . ' < 0, 0, total_all_price - ' . $reduced . ')');
            if ($this->Order->updateAll($toUpdate, array('id' => $order_id, 'status' => ORDER_STATUS_WAITING_PAY))) {
                $this->log('apply user score=' . $spent_on_order . ' to order-id=' . $order_id . ' successfully');
                $score_consumed += $spent_on_order;
                $order_id_spents[$order_id] = $spent_on_order;
            }
        }
        if ($score_consumed > 0) {
            $this->spend_score($uid,$score_consumed,$order_id_spents);
        }
        // 注意必须清除key_balanced_scores
        $this->Session->write(self::key_balanced_scores(), '');
        if ($order['Order']['status'] != ORDER_STATUS_WAITING_PAY) {
            $result_data['success'] = false;
            $result_data['fail_reason'] = "你已经支付过了";
            return $result_data;
        } else {
            $cart_name = $cart_info['Cart']['name'];
            //to set mark
            if ($tuan_info['TuanTeam']['type'] == IS_BIG_TUAN) {
                if (empty($shipSetting)) {
                    //default
                    $cart_name = $cart_name . '(自提)';
                } else {
                    $cart_name = $cart_name . '(' . TuanShip::get_ship_name($shipTypeId) . ')';
                }
            }
            $this->Cart->update(array('name' => '\'' . $cart_name . '\''), array('id' => $cart_id));
            $result_data['success'] = true;
            $result_data['order_id'] = $order['Order']['id'];
            return $result_data;
        }
    }

   private function spend_score($uid, $score_consumed, $order_id_spents){
       $scoreM = ClassRegistry::init('Score');
       $scoreM->spent_score_by_order($uid, $score_consumed, $order_id_spents);
       $this->User = ClassRegistry::init('User');
       $this->User->add_score($uid, -$score_consumed);
   }

   private function merge_ziti_order_consignes($uid,$offline_store,$address){
       $ziti_consignees = array();
       $this->OrderConsignees = ClassRegistry::init('OrderConsignees');
       $old_ziti_consignees = $this->OrderConsignees->find('first', array(
           'conditions' => array('status' => STATUS_CONSIGNEES_TUAN_ZITI, 'creator' => $uid),
           'fields' => array('id')
       ));
       $ziti_consignees['area'] = $offline_store['OfflineStore']['area_id'];
       $ziti_consignees['address'] = $address;
       $ziti_consignees['creator'] = $uid;
       $ziti_consignees['id'] = $old_ziti_consignees['OrderConsignees']['id'];
       $ziti_consignees['ziti_id'] = $offline_store['OfflineStore']['id'];
       $ziti_consignees['ziti_type'] = $offline_store['OfflineStore']['type'];
       $ziti_consignees['status'] = STATUS_CONSIGNEES_TUAN_ZITI;
       $this->OrderConsignees->save($ziti_consignees);
   }

    private function merge_order_consignes($uid,$new_consignes){
        $this->OrderConsignees = ClassRegistry::init('OrderConsignees');
        $tuan_consignees = $this->OrderConsignees->find('first', array(
            'conditions' => array('status' => STATUS_CONSIGNEES_TUAN, 'creator' => $uid),
            'fields' => array('id')
        ));
        if ($tuan_consignees) {
            $new_consignes['id'] = $tuan_consignees['OrderConsignees']['id'];
        } else {
            $new_consignes['creator'] = $uid;
        }
        $this->OrderConsignees->save($new_consignes);
    }

    private function get_cart($cart_id){
        $this->Cart = ClassRegistry::init('Cart');
        $Carts = $this->Cart->find('first', array(
            'conditions' => array(
                'id' => $cart_id
            ),
        ));
        return $Carts;
    }

    private function get_brand($pid){
        $this->Product = ClassRegistry::init('Product');
        $this->Brand = ClassRegistry::init('Brand');
        $product_brand = $this->Product->find('first', array(
            'conditions' => array('id' => $pid),
            'fields' => array('brand_id')
        ));
        $brand = $this->Brand->find('first', array(
            'conditions' => array('id' => $product_brand['Product']['brand_id']),
            'fields' => array('name', 'slug')
        ));

        return $brand;
    }


    private function get_score_usable($uid,$total_price){
        //积分统计
        $this->User = ClassRegistry::init('User');
        $score = $this->User->get_score($uid, true);
        $could_score_money = cal_score_money($score, $total_price);
        return $could_score_money;
    }

    //可以备注的地址
    private function can_mark_address($tuan_id){
        //昌平的的团可以备注地址
        $mark_address_tuan_ids = array(15,25,28,41,43,45,46,47,48,58,60,66,104);
        return in_array($tuan_id,$mark_address_tuan_ids);
    }

    private function get_offline_store($id){
        $this->OfflineStore = ClassRegistry::init('OfflineStore');
        $offline_store = $this->OfflineStore->findById($id);
        return $offline_store;
    }

    private function get_ship_setting($way_id,$data_id,$type){
        $this->ProductShipSetting = ClassRegistry::init('ProductShipSetting');
        $cond = array('data_id' => $data_id, 'data_type' => $type);
        if($way_id){
            $cond['id'] = $way_id;
        }
        $shipSetting = $this->ProductShipSetting->find('first',array(
            'conditions' => $cond
        ));
        return $shipSetting;
    }

    private function get_old_consignees($uid,$shipSetting=null){
        $this->OrderConsignees = ClassRegistry::init('OrderConsignees');
        if(empty($shipSetting)||(TuanShip::get_ship_code($shipSetting['ProductShipSetting']['ship_type'])==ZITI_TAG)){
            $ziti_consignee_info = $this->OrderConsignees->find('first', array(
                'conditions' => array('creator' => $uid, 'status' => STATUS_CONSIGNEES_TUAN_ZITI),
                'fields' => array('area', 'ziti_id','address','ziti_type')
            ));
            if($ziti_consignee_info){
                if(empty($shipSetting)||($shipSetting['ProductShipSetting']['val']==-1)||($shipSetting['ProductShipSetting']['val']==$ziti_consignee_info['OrderConsignees']['ziti_type'])){
                    //$this->set('ziti_consignee_info',$ziti_consignee_info['OrderConsignees']);
                    return $ziti_consignee_info['OrderConsignees'];
                }
            }
        }
        if(TuanShip::get_ship_code($shipSetting['ProductShipSetting']['ship_type'])!=ZITI_TAG){
            $consignee_info = $this->OrderConsignees->find('first', array(
                'conditions' => array('creator' => $uid, 'status' => STATUS_CONSIGNEES_TUAN),
                'fields' => array('name', 'address', 'mobilephone')
            ));
            if($consignee_info){
                return $consignee_info['OrderConsignees'];
            }
        }

    }

}