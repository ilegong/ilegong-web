<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 7/8/15
 * Time: 14:12
 */
class TinyBuyController extends AppController {

    var $uses = array('TinyBuyProduct', 'TinyBuy', 'TinyBuyAddress', 'Order', 'Cart');

    public function add() {

    }

    public function create() {
        $postData = $_POST['postData'];
        $postDataArray = json_decode($postData,true);
        $tinyBuyData = $postDataArray['tinyBuy'];
        $tinyBuyProductData = $postDataArray['tinyBuyProduct'];
        $tinyBuyAddressData = $postDataArray['tinyBuyAddress'];
        $saveBuyFlag = $tinyBuy = $this->TinyBuy->save($tinyBuyData);
        $saveProductFlag = $this->saveTinyBuyProducts($tinyBuy['TinyBuy']['id'],$tinyBuyProductData);
        $saveAddressFlag = $this->saveTinyBuyAddresses($tinyBuy['TinyBuy']['id'],$tinyBuyAddressData);
        if($saveBuyFlag&&$saveProductFlag&&$saveAddressFlag){
            echo json_encode(array('success' => true, 'id' => $tinyBuy['TinyBuy']['id']));
            return;
        }else{
            echo json_encode(array('success' => false));
            return;
        }
    }

    public function detail($tinyBuyId) {

    }

    public function buy() {

    }

    public function makeOrder(){
        
    }

    private function saveTinyBuyProducts($tinyBuyId,$tinyBuyProductData) {
        foreach($tinyBuyProductData as &$product){
            $product['tiny_buy_id'] = $tinyBuyId;
        }
        return $this->TinyBuyProduct->saveAll($tinyBuyProductData);
    }

    private function saveTinyBuyAddresses($tinyBuyId,$tinyBuyAddressData) {
        foreach($tinyBuyAddressData as &$address){
            $address['tiny_buy_id'] = $tinyBuyId;
        }
        return $this->TinyBuyProduct->saveAll($tinyBuyAddressData);
    }
}