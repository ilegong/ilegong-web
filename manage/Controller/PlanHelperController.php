<?php
class PlanHelperController extends AppController
{

    var $name = 'PlanHelper';

    var $uses = array('User', 'Order', 'Cart', 'Product', 'ProductSpecGroup', 'OfflineStore');

    public function admin_orders()
    {
        $this->autoRender = false;

        $order_count = $_REQUEST['order_count'];
        $user_ids = explode(',', $_REQUEST['user_ids']);

        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $user_ids
            )
        ));
        if (empty($users)) {
            echo json_encode(array('result' => false, 'reason' => 'please provide at least 1 user'));
            return;
        }
        foreach ($users as $user) {
            if (!$this->_is_user_valid($user)) {
                echo json_encode(array('result' => false, 'reason' => 'invalid user ' . $user['User']['id']));
                return;
            }
        }

        $pys_products = $this->Product->find('all', array(
            'conditions' => array(
                'brand_id' => PYS_BRAND_ID,
                'published' => PUBLISH_YES
            )
        ));
        $pys_products = Hash::combine($pys_products, '{n}.Product.id', '{n}');

        $this->loadModel('ProductSpecGroup');
        $product_spec_groups = $this->ProductSpecGroup->find('all', array(
            'conditions' => array(
                'product_id' => Hash::extract($pys_products, '{n}.Product.id')
            )
        ));
        $product_spec_groups = Hash::extract($product_spec_groups, '{n}.ProductSpecGroup.product_id', '{n}');

        $offline_store = $this->OfflineStore->findById(54);

        $results = array();
        for ($i = 0; $i < $order_count; $i++) {
            $count = $this->_get_random_product_count();
            $products = $this->_get_random_products($pys_products, $product_spec_groups, $count);
            $user = $users[array_rand($users)];
            $order_id = $this->_try_to_create_order($i, $user, $products, $offline_store);

            if (!isset($results[$user['User']['id']])) {
                $results[$user['User']['id']] = array();
            }
            $results[$user['User']['id']][] = $order_id;
        }

        echo json_encode($results);
    }

    public function admin_order()
    {
        $this->autoRender = false;

        $user_id = $_REQUEST['user_id'];

        $user = $this->User->findById($user_id);
        if (empty($user) || !$this->_is_user_valid($user)) {
            echo json_encode(array('result' => false, 'reason' => 'user is invalid'));
            return;
        }

        $count = $this->_get_random_product_count();
        $products = $this->Product->find('all', array(
            'conditions' => array(
                'brand_id' => PYS_BRAND_ID,
                'published' => PUBLISH_YES,
                'deleted' => DELETED_NO
            )
        ));
        $product_spec_groups = $this->ProductSpecGroup->find('all', array(
            'conditions' => array(
                'product_id' => Hash::extract($products, '{n}.Product.id')
            )
        ));
        $product_spec_groups = Hash::combine($product_spec_groups, '{n}.ProductSpecGroup.product_id', '{n}');

        $products = $this->_get_random_products($products, $product_spec_groups, $count);

        $offline_store = $this->_get_random_offline_store($user_id);
        try {
            $order_id = $this->_try_to_create_order(0, $user, $products, $offline_store);
            echo json_encode(array('result' => true, "order_id" => $order_id));
        } catch (Exception $e) {
            echo json_encode(array('result' => false, 'reason' => $e->getMessage()));
        }
    }

    public function admin_order_shipped()
    {
        $this->autoRender = false;

        $order_ids = explode(",", $_REQUEST['order_ids']);
        if (empty($order_ids)) {
            echo json_encode(array('result' => false, 'reason' => 'please provide order ids'));
            return;
        }

        $orders = $this->Order->find('all', array(
            "conditions" => array(
                'id' => $order_ids
            )
        ));
        if (empty($orders)) {
            echo json_encode(array('result' => false, 'reason' => 'orders does not exist'));
            return;
        }

        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => array_unique(Hash::extract($orders, '{n}.Order.creator'))
            )
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}');

        foreach ($orders as $order) {
            if ($order['Order']['brand_id'] != 92) {
                echo json_encode(array('result' => false, 'reason' => 'order ' . $order['Order']['id'] . ' is not a pyshuo product'));
                return;
            }
            if ($order['Order']['status'] != ORDER_STATUS_PAID) {
                echo json_encode(array('result' => false, 'reason' => 'order ' . $order['Order']['id'] . ' is not in paid status'));
                return;
            }
            $user = $users[$order['Order']['creator']];
            if (!$this->_is_user_valid($user)) {
                echo json_encode(array('result' => false, 'reason' => 'invalid user id ' . $order['Order']['creator']));
                return;
            }
        }

        $users = $this->User->find('all', array(
            "conditions" => array(
                "id NOT IN (SELECT DISTINCT creator FROM cake_orders WHERE creator IS NOT NULL AND status > 0)",
                "nickname IS NOT NULL and nickname != ''",
                "mobilephone IS NOT NULL and mobilephone != ''"
            ),
            'limit' => max(count($orders) / 3, 1),
        ));

        foreach ($orders as &$order) {
            $user = $users[array_rand($users)];
            $this->Order->updateAll(array("creator" => "'" . $user['User']['id'] . "'", 'consignee_name' => "'" . $user['User']['nickname'] . "'", 'consignee_mobilephone' => "'" . $user['User']['nickname'] . "'", 'status' => "'" . ORDER_STATUS_SHIPPED . "'", 'published' => "'" . PUBLISH_NO . "'"), array('id' => $order['Order']['id']));
            $this->Cart->updateAll(array('status' => "'" . ORDER_STATUS_SHIPPED . "'"), array('order_id' => $order['Order']['id']));
        }
        echo json_encode(array("order_ids" => $order_ids));
    }

    private function _try_to_create_order($index, $user, $products, $offline_store)
    {
        $send_date = date_format(get_send_date(6, "23:59:59", '2,4,6'), 'Y-m-d');
        $seconds = $index * 60 + rand(0, 59);
        $date = date('Y-m-d H:i:s', strtotime('+' . $seconds . ' seconds'));

        $order_id = $this->_insert_order($user, $products, $offline_store, $date);
        foreach($products as &$product){
            $this->_insert_cart($user, $product, $send_date, $order_id, $date);
        }

        return $order_id;
    }

    function _insert_order($user, $products, $offline_store, $date)
    {
        if(empty($user['User']['mobilephone'])){
            $this->User->updateAll(array('mobilephone'=>$this->_get_random_mobilephone()), array('id'=>$user['User']['id']));
        }
        $total_price = 0;
        foreach($products as $product){
            $price = empty($product['ProductSpecGroup']['price']) ? $products['Product']['price'] : $product['ProductSpecGroup']['price'];
            $total_price = $total_price + $price * $product['Product']['num'];
        }

        $data = array();
        $data['Order']['creator'] = $user['User']['id'];
        $data['Order']['status'] = 0;
        $data['Order']['ship_mark'] = 'ziti';
        $data['Order']['created'] = $date;
        $data['Order']['updated'] = $date;
        $data['Order']['consignee_name'] = empty($user['User']['nickname']) ? '李嘉' : $user['User']['nickname'];
        $data['Order']['consignee_mobilephone'] = $user['User']['mobilephone'];
        $data['Order']['consignee_id'] = $offline_store['OfflineStore']['id'];
        $data['Order']['consignee_address'] = $offline_store['OfflineStore']['name'];
        $data['Order']['coverimg'] = $product['Product']['coverimg'];
        $data['Order']['total_price'] = $total_price;
        $data['Order']['total_all_price'] = $total_price;
        $data['Order']['brand_id'] = $product['Product']['brand_id'];
        $data['Order']['type'] = 1;
        $data['Order']['published'] = PUBLISH_YES;

        $this->Order->id = null;
        if ($this->Order->save($data)) {
            $order_id = $this->Order->getLastInsertID();
            $this->log("plan helper create order successfully: " . $order_id);
            return $order_id;
        } else {
            $this->log($this->Order->validationErrors); //show validationErrors
            throw new Exception("plan helper create order failed");
        }
    }

    function _insert_cart($user, $product, $send_date, $order_id, $date)
    {
        $price = empty($product['ProductSpecGroup']['price']) ? $product['Product']['price'] : $product['ProductSpecGroup']['price'];

        $data = array();
        $data['Cart']['name'] = $product['Product']['name'];
        $data['Cart']['order_id'] = $order_id;
        $data['Cart']['creator'] = $user['User']['id'];
        $data['Cart']['type'] = 5;
        $data['Cart']['status'] = 0;
        $data['Cart']['product_id'] = $product['Product']['id'];
        $data['Cart']['spec_id'] = $product['ProductSpecGroup']['id'];
        $data['Cart']['coverimg'] = $product['Product']['coverimg'];
        $data['Cart']['price'] = $price * $product['Product']['num'];
        $data['Cart']['num'] = $product['Product']['num'];
        $data['Cart']['session_id'] = $this->Session->id();
        $data['Cart']['created'] = $date;
        $data['Cart']['updated'] = $date;
        $data['Cart']['modified'] = $date;
        $data['Cart']['send_date'] = $send_date;

        $this->Cart->id = null;
        if ($this->Cart->save($data)) {
            $cart_id = $this->Cart->getLastInsertID();
            $this->log("plan helper create cart successfully: " . $cart_id);
            return $cart_id;
        } else {
            $this->log($this->Cart->validationErrors); //show validationErrors
            throw new Exception("plan helper create cart failed");
        }
    }

    private function _is_user_valid($user)
    {

        return !empty($user) && $user['User']['username'];
    }

    private function _get_random_product_count(){
        $random_num = rand(1, 10);
        if ($random_num > 9) {
            $count = 4;
        } else if ($random_num > 7) {
            $count = 3;
        } else if ($random_num > 4) {
            $count = 2;
        } else {
            $count = 1;
        }
        return $count;
    }
    private function _get_random_products($pys_products, $product_spec_groups, $count)
    {
        $products = array();
        for ($i = 0; $i < $count; $i++) {
            $product = $pys_products[array_rand($pys_products)];
            $product_id = $product['Product']['id'];
            $spec_group = $this->_get_random_spec_group($product_spec_groups, $product_id);
            $price = empty($spec_group) ? $product['Product']['price'] : $spec_group['ProductSpecGroup']['price'];

            $products[$product_id] = $product;
            $products[$product['Product']['id']]['Product']['num'] = $this->_get_random_num($price);
            $products[$product['Product']['id']]['ProductSpecGroup'] = $spec_group['ProductSpecGroup'];
        }
        return $products;
    }

    private function _get_random_spec_group($product_spec_groups, $product_id)
    {
        $spec_groups = array_filter($product_spec_groups, function ($spec_group) use ($product_id) {
            return $spec_group['ProductSpecGroup']['product_id'] = $product_id;
        });
        $spec_groups = Hash::combine($spec_groups, '{n}.ProductSpecGroup.id', '{n}');

        if (empty($spec_groups)) {
            return array();
        }
        return $spec_groups[array_rand($spec_groups)];
    }

    private function _get_random_offline_store($user_id){
        $offline_stores = $this->OfflineStore->find('all', array(
            'conditions' => array(
            'deleted' => DELETED_NO
            )
        ));
        $offline_stores = Hash::combine($offline_stores, '{n}.OfflineStore.id', '{n}');
        return $offline_stores[array_rand($offline_stores)];
    }
    private function _get_random_num($price)
    {
        if ($price < 10) {
            $rand_num = rand(3, 10);
        } else if ($price < 20) {
            $rand_num = rand(2, 10);
        } else if ($price < 30) {
            $rand_num = rand(1, 9);
        } else if ($price < 50) {
            $rand_num = rand(1, 9);
        } else if ($price < 60) {
            $rand_num = rand(1, 8);
        } else if ($price < 70) {
            $rand_num = rand(1, 7);
        } else if ($price < 80) {
            $rand_num = rand(1, 6);
        } else {
            $rand_num = rand(1, 5);
        }

        $num = 1;
        if ($rand_num > 10) {
            $num = 10;
        } else if ($rand_num > 9) {
            $num = 5;
        } else if ($rand_num > 8) {
            $num = 4;
        } else if ($rand_num > 7) {
            $num = 3;
        } else if ($rand_num > 5) {
            $num = 2;
        }
        return $num;
    }
    private function _get_random_mobilephone(){
        $second = array(3, 5, 6, 8);
        $mobilephone = '1'.$second[array_rand($second)].rand(0,9).rand(0,9999).rand(0,9999);
        return $mobilephone;
    }
}