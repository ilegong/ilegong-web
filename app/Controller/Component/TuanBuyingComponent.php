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
            //echo json_encode(array('success'=> false, 'error' => '对不起，系统出错，请联系客服'));
        }
    }

    public function balance_sec_kill($tuan_id,$cart_id,$try_id){
        $ship_type=-1;
        $result_data = array();
        if(!empty($tuan_id)){
            $this->loadModel('TuanTeam');
            $team = $this->TuanTeam->find('first',array(
                'conditions' => array(
                    'id' => $tuan_id
                )
            ));
            if(empty($team)){
                //$this->__message('该团不存在', '/tuan_teams/mei_shi_tuan');
                return;
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
        $this->loadModel('Cart');
        $Carts = $this->Cart->find('first', array(
            'conditions' => array(
                'id' => $cart_id
            ),
        ));
        $pid = $Carts['Cart']['product_id'];
        $total_price = $Carts['Cart']['price'] * $Carts['Cart']['num'];
        $this->loadModel('Product');
        $this->loadModel('Brand');
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
        return $result_data;
    }


    //可以备注的地址
    //TODO 昌平的的团可以备注地址
    private function can_mark_address($tuan_id){
        $mark_address_tuan_ids = array(15,25,28,41,43,45,46,47,48,58,60,66,104);
        return in_array($tuan_id,$mark_address_tuan_ids);
    }

    private function get_offline_store($id){
        $offline_store = $this->OfflineStores->find('first',array(
            'conditions' => array('id' => $id)
        ));
        return $offline_store;
    }

}