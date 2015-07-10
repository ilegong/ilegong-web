<?php

class WesharesController extends AppController {

    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    public function add() {

    }

    /**
     * {
     *   "weshare":{
            "title":"",
     *      "description": "",
     *      "images":"",
     *      "send_date": ""
     *    },
     *    "weshareProduct":[
     *          {
                    "name":
     *              "price":
     *          }
     *     ]
     *
     *     "weshareAddress":[
     *          {
                    "address":"",
     *          }
     *      ]
     *
     *
     * }
     */
    public function create() {
        $uid = $this->currentUser['id'];
        if(empty($uid)){
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $postStr = file_get_contents('php://input');
        $postDataArray = json_decode($postStr, true);
        $weshareData = $postDataArray['weshare'];
        $weshareProductData = $postDataArray['weshareProduct'];
        $weshareAddressData = $postDataArray['weshareAddress'];
        $saveBuyFlag = $weshare = $this->Weshare->save($weshareData);
        $saveProductFlag = $this->saveWeshareProducts($weshare['Weshare']['id'], $weshareProductData);
        $saveAddressFlag = $this->saveWeshareAddresses($weshare['Weshare']['id'], $weshareAddressData);
        if ($saveBuyFlag && $saveProductFlag && $saveAddressFlag) {
            echo json_encode(array('success' => true, 'id' => $weshare['Weshare']['id']));
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
        $weshareId = $postDataArray['weshareId'];
        $addressId = $postDataArray['addressId'];
        $cart = array();
        $tinyBuyProductIds = Hash::extract($products, '{n}.id');
        $productIdNumMap = Hash::combine($products, '{n}.id', '{n}.num');
        $tinyProducts = $this->WeshareProduct->find('all', array(
            'conditions' => array(
                'id' => $tinyBuyProductIds,
                'wesahre_id' => $weshareId
            )
        ));
        $order = $this->Order->save(array('creator' => $uid, 'member_id' => $weshareId, 'type' => ORDER_TYPE_WESHARE_BUY, 'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'), 'consignee_id' => $addressId));
        $orderId = $order['Order']['id'];
        $totalPrice = 0;
        foreach ($tinyProducts as $p) {
            $item = array();
            $pid = $p['WeshareProduct']['id'];
            $num = $productIdNumMap[$pid];
            $price = $p['WeshareProduct']['price'];
            $item['name'] = $p['WeshareProduct']['name'];
            $item['num'] = $num;
            $item['price'] = $price;
            $item['type'] = ORDER_TYPE_TINY_BUY;
            $item['product_id'] = $p['TinyBuyProduct']['id'];
            $item['created'] = date('Y-m-d H:i:s');
            $item['updated'] = date('Y-m-d H:i:s');
            $item['creator'] = $uid;
            $item['order_id'] = $orderId;
            $item['tuan_buy_id'] = $weshareId;
            $cart[] = $item;
            $totalPrice += $num * $price;
        }
        $this->Cart->saveAll($cart);
        $this->Order->updateAll(array('total_all_price' => $totalPrice / 100, 'total_price' => $totalPrice / 100, 'ship_fee' => 0), array('id' => $orderId));
        echo json_encode(array('success' => true, 'orderId' => $orderId));
        return;
    }

    private function saveWeshareProducts($weshareId, $weshareProductData) {
        foreach ($weshareProductData as &$product) {
            $product['weshare_id'] = $weshareId;
        }
        return $this->WeshareProduct->saveAll($weshareProductData);
    }

    private function saveWeshareAddresses($weshareId, $weshareAddressData) {
        foreach ($weshareAddressData as &$address) {
            $address['weshare_id'] = $weshareId;
        }
        return $this->WeshareAddress->saveAll($weshareAddressData);
    }
}