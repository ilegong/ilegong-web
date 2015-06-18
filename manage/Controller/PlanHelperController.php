<?php
class PlanHelperController extends AppController
{

    var $name = 'PlanHelper';

    var $uses = array('User', 'Order', 'Cart', 'Product', 'OfflineStore');

    public function admin_order()
    {
        $this->autoRender = false;

        $user_id = $_REQUEST['user_id'];
        $product_id = $_REQUEST['product_id'];
        $offline_store_id = $_REQUEST['offline_store_id'];
        $send_date = $_REQUEST['send_date'];
        $spec_id = isset($_REQUEST['spec_id']) ? $_REQUEST['spec_id'] : 0;
        $num = isset($_REQUEST['num']) ? $_REQUEST['num'] : 1;

        if (!$this->_is_user_valid($user_id)) {
            echo json_encode(array('result'=>false, 'reason' => "invalid user id ".$user_id));
            return;
        }
        $user = $this->User->findById($user_id);

        $product = $this->Product->findById($product_id);
        if ($product['Product']['brand_id'] != 92) {
            echo json_encode(array('result'=>false, 'reason' => 'not pyshuo products'));
            return;
        }

        if ($offline_store_id != 54 && $offline_store_id != 55) {
            echo json_encode(array('result'=>false, 'reason' => 'only offline store 54 or 55 is supported'));
            return;
        }
        $offline_store = $this->OfflineStore->findById($offline_store_id);
        if (empty($offline_store)) {
            echo json_encode(array('result'=>false, 'reason' => 'offline store does not exist: ' . $offline_store_id));
            return;
        }

        $this->log("plan helper is to create order for " . $user_id . " and product " .$product_id. " with num " . $num);

        $order_id = $this->_insert_order($user, $product, $num, $offline_store);
        $cart_id = $this->_insert_cart($user, $product, $num, $spec_id, $send_date, $order_id);
        $this->log("plan helper create order successfully: " . $order_id . ", " . $cart_id);

        echo json_encode(array("order_id" => $order_id, "cart_id" => $cart_id));
    }

    public function admin_order_shipped(){
        $this->autoRender = false;

        $order_ids = explode(",", $_REQUEST['order_ids']);
        if(empty($order_ids)){
            echo json_encode(array('result'=>false, 'reason' => 'please provide order ids'));
            return;
        }

        $orders = $this->Order->find('all', array(
            "conditions" => array(
                'id'=>$order_ids
            )
        ));
        if(empty($orders)){
            echo json_encode(array('result'=>false, 'reason' => 'orders does not exist'));
            return;
        }
        foreach($orders as $order){
            if ($order['Order']['brand_id'] != 92) {
                echo json_encode(array('result'=>false, 'reason' => 'order '.$order['Order']['id'].' is not a pyshuo product'));
                return;
            }
            if ($order['Order']['status'] != ORDER_STATUS_PAID) {
                echo json_encode(array('result'=>false, 'reason' => 'order '.$order['Order']['id'].' is not in paid status'));
                return;
            }
            if (!$this->_is_user_valid($order['Order']['creator'])) {
                echo json_encode(array('result'=>false, 'reason' => 'invalid user id '.$order['Order']['creator']));
                return;
            }
        }

        $users = $this->User->find('all', array(
            "conditions" => array(
                "id NOT IN (SELECT DISTINCT creator FROM cake_orders WHERE creator IS NOT NULL AND status > 0)",
                "nickname IS NOT NULL"
            ),
            'limit' => count($orders)
        ));

        foreach($orders as $index => &$order){
            $user = $users[$index];
            $this->log('update user to '.$user['User']['id']);
            $this->Order->updateAll(array('creator'=>$user['User']['id'], 'consignee_name'=>$user['User']['nickname'], 'status'=>ORDER_STATUS_SHIPPED), array('id' => $order['Order']['id']));
            $this->Cart->updateAll(array('status'=>ORDER_STATUS_SHIPPED), array('order_id' => $order['Order']['id']));
        }
        echo json_encode(array("order_ids" => $order_ids));
    }

    function _insert_order($user, $product, $num, $offline_store)
    {
        $date = date('Y-m-d H:i:s', strtotime("+17 seconds"));

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
        $data['Order']['total_price'] = $product['Product']['price'] * $num;
        $data['Order']['total_all_price'] = $product['Product']['price'] * $num;
        $data['Order']['brand_id'] = $product['Product']['brand_id'];
        $data['Order']['type'] = 1;
        $data['Order']['flag'] = 7;

        $this->log("plan helper is to create order: ".json_encode($data));

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

        $this->log("plan helper is to create cart: ".json_encode($data));

        if ($this->Cart->save($data)) {
            return $this->Cart->getLastInsertID();
        } else {
            $this->log($this->Cart->validationErrors); //show validationErrors
            throw new Exception("plan helper create cart failed");
        }
    }

    private function _is_user_valid($user_id){
        return ($user_id >= 810163 && $user_id <= 810223) || ($user_id >= 810096 && $user_id <= 810158);
    }
}