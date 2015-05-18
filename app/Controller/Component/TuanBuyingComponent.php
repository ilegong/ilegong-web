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

}