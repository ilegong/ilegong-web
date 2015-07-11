<?php

class WesharesController extends AppController {

    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    public function index() {}

    /**
     * {
     *      "title":"",
     *      "description": "",
     *      "images":"",
     *      "send_date": ""
     *    "products":[
     *          {
                    "name":
     *              "price":
     *          }
     *     ]
     *
     *     "address":[
     *          {
                    "addresses":"",
     *          }
     *      ]
     *
     *
     * }
     */
    public function create() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if(empty($uid)){
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $postStr = file_get_contents('php://input');
        $postDataArray = json_decode($postStr, true);
        $weshareData = array();
        $weshareData['title'] = $postDataArray['title'];
        $weshareData['description'] = $postDataArray['description'];
        $weshareData['send_date'] = $postDataArray['send_date'];
        $weshareData['creator'] = $uid;
        $productsData = $postDataArray['products'];
        $addressesData = $postDataArray['addresses'];
        $weshareData['creator'] = $uid;
        $saveBuyFlag = $weshare = $this->Weshare->save($weshareData);
        $saveProductFlag = $this->saveWeshareProducts($weshare['Weshare']['id'], $productsData);
        $saveAddressFlag = $this->saveWeshareAddresses($weshare['Weshare']['id'], $addressesData);
        if ($saveBuyFlag && $saveProductFlag && $saveAddressFlag) {
            echo json_encode(array('success' => true, 'id' => $weshare['Weshare']['id']));
            return;
        } else {
            echo json_encode(array('success' => false));
            return;
        }
    }

    public function detail($weshareId) {
        $this->autoRender = false;
        $weshareInfo = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $weshareId
            )
        ));
        $weshareProducts = $this->WeshareProduct->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshareId
            )
        ));
        $weshareAddresses = $this->WeshareAddress->find('all', array(
            'conditions' => array(
                'weshare_id' => $weshareId
            )
        ));
        $creatorInfo = $this->User->find('first', array(
            'conditions' => array(
                'id' => $weshareInfo['Weshare']['creator']
            ),
            'recursive' => 1, //int
            'fields' => array('id', 'nickname', 'image', 'wx_subscribe_status'),
        ));
        echo json_encode(array('info' => $weshareInfo, 'products' => $weshareProducts, 'addresses' => $weshareAddresses, 'creator' => $creatorInfo));
        return;
    }

    public function buy() {

    }

    public function pay($orderId) {

    }

    public function makeOrder() {
        $uid = $this->currentUser['id'];
        if(empty($uid)){
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $postStr = file_get_contents('php://input');
        $postDataArray = json_decode($postStr, true);
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

    private function get_wesahre_buy_info(){

    }
}