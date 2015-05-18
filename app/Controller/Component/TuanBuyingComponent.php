<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 5/18/15
 * Time: 12:29
 */

class TuanBuyingComponent extends Component{

    var $name = 'TuanBuyingComponent';

    /**
     * @param $product_id
     * @param $product_num
     * @param $spec_id
     * @param $type
     * @param $uId
     * @param $sessionId
     * @param $cart_tuan_param
     * @param $consignment_date_id
     * @param $send_date
     * @param $way_id
     * @param $way_type
     *
     * @return array|void
     */
    public function add_cart($product_id,$product_num,$spec_id,$type,$uId,$sessionId,$cart_tuan_param,$consignment_date_id,$send_date,$way_id,$way_type){
        $this->Cart = ClassRegistry::init('Cart');
        $cartInfo = $this->Cart->add_to_cart($product_id,$product_num,$spec_id,$type,0,$uId, $sessionId,  null, null,$cart_tuan_param);
        $this->log('cartInfo'.json_encode($cartInfo));
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
            //Todo
            //$this->Session->write(self::key_balance_pids(), json_encode($cart_array));
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
    public function balance_sec_kill($tuan_id,$cart_id,$try_id){
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
                $ship_type = $shipSetting['ProductShipSetting']['ship_val'];
            }
        }
        $this->Cart = ClassRegistry::init('Cart');
        $Carts = $this->Cart->find('first', array(
            'conditions' => array(
                'id' => $cart_id
            ),
        ));
        $pid = $Carts['Cart']['product_id'];
        $total_price = $Carts['Cart']['price'] * $Carts['Cart']['num'];
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
        $result_data['try_id'] = $try_id;
        $result_data['ship_type'] = $ship_type;
        $result_data['buy_count'] = $Carts['Cart']['num'];
        $result_data['total_price'] = $total_price;
        $result_data['cart_id'] = $Carts['Cart']['id'];
        $result_data['cart_info'] = $Carts;
        $result_data['brand'] = $brand['Brand'];
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
        $user_condition = array(
            'session_id' => $this->Session->id(),
            'creator' => $uid
        );
        $cond = array(
            'id' => $cart_id,
            'OR' => $user_condition
        );
        $this->Cart = ClassRegistry::init('Cart');
        $Carts = $this->Cart->find('first', array(
            'conditions' => $cond,
            'order' => 'id DESC'
        ));
        if (empty($Carts)) {
            $result_data['success'] = false;
            $result_data['fail_reason'] = '结算失败，请重试';
            return $result_data;
        }
        $this->TuanBuying = ClassRegistry::init('TuanBuying');
        $tuan_b = $this->TuanBuying->find('first', array(
            'conditions' => array('id' => $tuan_buy_id),
            'fields' => array('pid', 'tuan_id', 'status', 'end_time')
        ));
        $current_time = strtotime($tuan_b['TuanBuying']['end_time']);
        $sold_num = $tuan_b['TuanBuying']['sold_num'];
        $max_num = $tuan_b['TuanBuying']['max_num'];
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
        $could_score_money = $this->get_score_usable($uid, $total_price);
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

}