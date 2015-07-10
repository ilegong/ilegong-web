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
        $postDataArray = json_decode($postData, true);
        $tinyBuyData = $postDataArray['tinyBuy'];
        $tinyBuyProductData = $postDataArray['tinyBuyProduct'];
        $tinyBuyAddressData = $postDataArray['tinyBuyAddress'];
        $saveBuyFlag = $tinyBuy = $this->TinyBuy->save($tinyBuyData);
        $saveProductFlag = $this->saveTinyBuyProducts($tinyBuy['TinyBuy']['id'], $tinyBuyProductData);
        $saveAddressFlag = $this->saveTinyBuyAddresses($tinyBuy['TinyBuy']['id'], $tinyBuyAddressData);
        if ($saveBuyFlag && $saveProductFlag && $saveAddressFlag) {
            echo json_encode(array('success' => true, 'id' => $tinyBuy['TinyBuy']['id']));
            return;
        } else {
            echo json_encode(array('success' => false));
            return;
        }
    }

    public function detail($tinyBuyId) {

    }

    public function buy() {

    }

    public function pay($orderId) {

    }

    public function makeOrder() {
        $uid = $this->currentUser['id'];
        $postData = $_POST['postData'];
        $postDataArray = json_decode($postData, true);
        $products = $postDataArray['products'];
        $tinyBuyId = $postDataArray['tinyBuyId'];
        $addressId = $postDataArray['addressId'];
        $cart = array();
        $tinyBuyProductIds = Hash::extract($products, '{n}.id');
        $productIdNumMap = Hash::combine($products, '{n}.id', '{n}.num');
        $tinyProducts = $this->TinyBuyProduct->find('all', array(
            'conditions' => array(
                'id' => $tinyBuyProductIds,
                'tiny_buy_id' => $tinyBuyId
            )
        ));
        $order = $this->Order->save(array('creator' => $uid, 'member_id' => $tinyBuyId, 'type' => ORDER_TYPE_TINY_BUY, 'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'), 'consignee_id' => $addressId));
        $orderId = $order['Order']['id'];
        $totalPrice = 0;
        foreach ($tinyProducts as $p) {
            $item = array();
            $pid = $p['TinyBuyProduct']['id'];
            $num = $productIdNumMap[$pid];
            $price = $p['TinyBuyProduct']['price'];
            $item['name'] = $p['TinyBuyProduct']['name'];
            $item['num'] = $num;
            $item['price'] = $price;
            $item['type'] = ORDER_TYPE_TINY_BUY;
            $item['product_id'] = $p['TinyBuyProduct']['id'];
            $item['created'] = date('Y-m-d H:i:s');
            $item['updated'] = date('Y-m-d H:i:s');
            $item['creator'] = $uid;
            $item['order_id'] = $orderId;
            $item['tuan_buy_id'] = $tinyBuyId;
            $cart[] = $item;
            $totalPrice += $num * $price;
        }
        $this->Cart->saveAll($cart);
        $this->Order->updateAll(array('total_all_price' => $totalPrice / 100, 'total_price' => $totalPrice / 100, 'ship_fee' => 0), array('id' => $orderId));
        echo json_encode(array('success' => true, 'orderId' => $orderId));
        return;
    }

    private function saveTinyBuyProducts($tinyBuyId, $tinyBuyProductData) {
        foreach ($tinyBuyProductData as &$product) {
            $product['tiny_buy_id'] = $tinyBuyId;
        }
        return $this->TinyBuyProduct->saveAll($tinyBuyProductData);
    }

    private function saveTinyBuyAddresses($tinyBuyId, $tinyBuyAddressData) {
        foreach ($tinyBuyAddressData as &$address) {
            $address['tiny_buy_id'] = $tinyBuyId;
        }
        return $this->TinyBuyProduct->saveAll($tinyBuyAddressData);
    }
}