<?php
class PlanHelperController extends AppController
{

    var $name = 'PlanHelper';

    var $uses = array('User', 'Order', 'Cart', 'Product', 'OfflineStore');

    public function admin_orders()
    {
        $order_count = $_REQUEST['order_count'];

        $this->loadModel("Product");

        $product_ids = array(1004, 1020, 231, 852, 940, 883, 954, 971, 973);
        $products = $this->Product->find('all', array(
            'conditions' => array(
                'id' => $product_ids
            )
        ));

        $this->loadModel('ProductSpecGroup');
        $product_spec_groups = $this->ProductSpecGroup->find('all', array(
            'conditions' => array(
                'product_id' => $product_ids
            )
        ));
        $product_spec_groups = Hash::extract($product_spec_groups, '{n}.ProductSpecGroup.product_id', '{n}');

        $offline_stores = $this->OfflineStore->find('all', array(
            'conditions' => array(
                'type' => 1,
                'deleted' => DELETED_YES
            )
        ));
    }

    public function admin_order()
    {
        $this->autoRender = false;

        $user_id = $_REQUEST['user_id'];
        $product_id = $_REQUEST['product_id'];
        $offline_store_id = $_REQUEST['offline_store_id'];
        $spec_id = isset($_REQUEST['spec_id']) ? $_REQUEST['spec_id'] : 0;
        $num = isset($_REQUEST['num']) ? $_REQUEST['num'] : 1;

        try{
            $order_id = $this->_try_to_create_order($user_id, $product_id, $offline_store_id, $num, $spec_id);
            echo json_encode(array('result' => true, "order_id" => $order_id));
        }
        catch(Exception $e){
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
        foreach ($orders as $order) {
            if ($order['Order']['brand_id'] != 92) {
                echo json_encode(array('result' => false, 'reason' => 'order ' . $order['Order']['id'] . ' is not a pyshuo product'));
                return;
            }
            if ($order['Order']['status'] != ORDER_STATUS_PAID) {
                echo json_encode(array('result' => false, 'reason' => 'order ' . $order['Order']['id'] . ' is not in paid status'));
                return;
            }
            if (!$this->_is_user_valid($order['Order']['creator'])) {
                echo json_encode(array('result' => false, 'reason' => 'invalid user id ' . $order['Order']['creator']));
                return;
            }
        }

        $users = $this->User->find('all', array(
            "conditions" => array(
                "id NOT IN (SELECT DISTINCT creator FROM cake_orders WHERE creator IS NOT NULL AND status > 0)",
                "nickname IS NOT NULL and nickname != ''"
            ),
            'limit' => count($orders)
        ));

        foreach ($orders as $index => &$order) {
            $user = $users[$index];
            $this->Order->updateAll(array("creator" => "'" . $user['User']['id'] . "'", 'consignee_name' => "'" . $user['User']['nickname'] . "'", 'status' => "'" . ORDER_STATUS_SHIPPED . "'", 'published' => "'".PUBLISH_NO."'"), array('id' => $order['Order']['id']));
            $this->Cart->updateAll(array('status' => "'" . ORDER_STATUS_SHIPPED . "'"), array('order_id' => $order['Order']['id']));
        }
        echo json_encode(array("order_ids" => $order_ids));
    }

    private function _try_to_create_order($user_id, $product_id, $offline_store_id, $num, $spec_id)
    {
        if (!$this->_is_user_valid($user_id)) {
            throw new Exception("invalid user id " . $user_id);
        }
        $user = $this->User->findById($user_id);

        $product = $this->Product->findById($product_id);
        if ($product['Product']['brand_id'] != 92) {
            throw new Exception('not pyshuo products');
        }

        if ($offline_store_id != 54 && $offline_store_id != 55) {
            throw new Exception('not pyshuo products');
            return;
        }
        $offline_store = $this->OfflineStore->findById($offline_store_id);
        if (empty($offline_store)) {
            throw new Exception('offline store does not exist: ' . $offline_store_id);
            return;
        }

        $this->log("plan helper is to create order for " . $user_id . " and product " . $product_id . " with num " . $num);

        $send_date = get_send_date(10, "23:59:59", '2,4,6');
        $order_id = $this->_insert_order($user, $product, $num, $spec_id, $offline_store);
        $cart_id = $this->_insert_cart($user, $product, $num, $spec_id, $send_date, $order_id);
        $this->log("plan helper create order successfully: " . $order_id . ", " . $cart_id);

        return $order_id;
    }

    function _insert_order($user, $product, $num, $spec_id, $offline_store)
    {
        $date = date('Y-m-d H:i:s', strtotime("+17 seconds"));
        $price = $product['Product']['price'];
        if (!empty($spec_id)) {
            $this->loadModel('ProductSpecGroup');
            $product_spec_group = $this->ProductSpecGroup->findById($spec_id);
            if (!empty($product_spec_group)) {
                $price = $product_spec_group['ProductSpecGroup']['price'];
            }
        }
        $total_price = $price * $num;

        $data = array();
        $data['Order']['creator'] = $user['User']['id'];
        $data['Order']['status'] = 0;
        $data['Order']['ship_mark'] = 'ziti';
        $data['Order']['created'] = $date;
        $data['Order']['updated'] = $date;
        $data['Order']['consignee_name'] = empty($user['User']['nickname']) ? '李嘉' : $user['User']['nickname'];
        $data['Order']['consignee_mobilephone'] = empty($user['User']['mobilephone']) ? '17910808972' : $user['User']['mobilephone'];
        $data['Order']['consignee_id'] = $offline_store['OfflineStore']['id'];
        $data['Order']['consignee_address'] = $offline_store['OfflineStore']['name'];
        $data['Order']['coverimg'] = $product['Product']['coverimg'];
        $data['Order']['total_price'] = $total_price;
        $data['Order']['total_all_price'] = $total_price;
        $data['Order']['brand_id'] = $product['Product']['brand_id'];
        $data['Order']['type'] = 1;
        $data['Order']['flag'] = 7;
        $data['Order']['published'] = PUBLISH_YES;

        $this->log("plan helper is to create order: " . json_encode($data));

        if ($this->Order->save($data)) {
            return $this->Order->getLastInsertID();
        } else {
            $this->log($this->Order->validationErrors); //show validationErrors
            throw new Exception("plan helper create order failed");
        }
    }

    function _insert_cart($user, $product, $num, $spec_id, $send_date, $order_id)
    {
        $date = date('Y-m-d H:i:s');
        $data = array();
        $data['Cart']['name'] = $product['Product']['name'];
        $data['Cart']['order_id'] = $order_id;
        $data['Cart']['creator'] = $user['User']['id'];
        $data['Cart']['type'] = 5;
        $data['Cart']['status'] = 0;
        $data['Cart']['product_id'] = $product['Product']['id'];
        $data['Cart']['spec_id'] = $spec_id;
        $data['Cart']['coverimg'] = $product['Product']['coverimg'];
        $data['Cart']['price'] = $product['Product']['price'] * $num;
        $data['Cart']['num'] = $num;
        $data['Cart']['session_id'] = $this->Session->id();
        $data['Cart']['created'] = $date;
        $data['Cart']['updated'] = $date;
        $data['Cart']['modified'] = $date;
        $data['Cart']['send_date'] = $send_date;

        $this->log("plan helper is to create cart: " . json_encode($data));

        if ($this->Cart->save($data)) {
            return $this->Cart->getLastInsertID();
        } else {
            $this->log($this->Cart->validationErrors); //show validationErrors
            throw new Exception("plan helper create cart failed");
        }
    }

    private function _is_user_valid($user_id)
    {
        return ($user_id >= 810163 && $user_id <= 810223) || ($user_id >= 810096 && $user_id <= 810158);
    }
}