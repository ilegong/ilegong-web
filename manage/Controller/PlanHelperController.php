<?php
class PlanHelperController extends AppController
{

    var $name = 'PlanHelper';

    var $uses = array('User', 'TuanTeam', 'TuanBuying', 'Order', 'Cart', 'Product', 'OfflineStore', 'ConsignmentDate');

    public function admin_order()
    {
        $this->autoRender = false;

        $user_id = $_REQUEST['user_id'];
        $product_id = $_REQUEST['product_id'];
        $offline_store_id = $_REQUEST['offline_store_id'];
        $num = isset($_REQUEST['num']) ? $_REQUEST['num'] : 1;

        if (!($user_id >= 810163 || $user_id <= 810223) && !($user_id >= 810096 || $user_id <= 810158)) {
            throw new Exception("invalid user id " . $user_id);
        }
        $user = $this->User->findById($user_id);

        $product = $this->Product->findById($product_id);
        if ($product['Product']['brand_id'] != 92) {
            throw new Exception('only pyshuo products are supported');
        }

        if ($offline_store_id != 54 && $offline_store_id != 55) {
            throw new Exception('only offline store 54 or 55 is supported');
        }
        $offline_store = $this->OfflineStore->findById($offline_store_id);
        if (empty($offline_store)) {
            throw new Exception('offline store does not exist: ' . $offline_store_id);
        }

        $tuan_buying = $this->TuanBuying->find('first', array(
            'conditions' => array(
                'tuan_id' => PYS_M_TUAN,
                'pid' => $product_id
            ),
            'order' => 'id DESC'
        ));

        $member_id = 0;
        $ship_mark = null;
        if (!empty($tuan_buying)) {
            $ship_mark = 'ziti';
            $member_id = $tuan_buying['TuanBuying']['id'];
            $send_date = $tuan_buying['TuanBuying']['consign_time'];
        }
        if (empty($send_date) && !empty($tuan_buying)) {
            $consignment_dates = $this->ConsignmentDate->find('first', array(
                'conditions' => array(
                    'product_id' => $product_id,
                    'published' => 1
                ),
                'order' => 'send_date DESC'
            ));

            if (!empty($consignment_dates)) {
                $send_date = $consignment_dates['ConsignmentDate']['send_date'];
            }
        }

        $this->log("plan helper is to create order for " . $user_id . " and product " .$product_id. " with num " . $num);

        $order_id = $this->_insert_order($user, $product, $num, $member_id, $ship_mark, $offline_store);
        $cart_id = $this->_insert_cart($user, $product, $num, $send_date, $order_id);
        $this->log("plan helper create order successfully: " . $order_id . ", " . $cart_id);

        echo json_encode(array("order_id" => $order_id, "cart_id" => $cart_id));
    }

    function _insert_order($user, $product, $num, $member_id, $ship_mark, $offline_store)
    {
        $date = date('Y-m-d H:i:s', strtotime("+17 seconds"));

        $data = array();
        $data['Order']['creator'] = $user['User']['id'];
        $data['Order']['status'] = 0;
        if(!empty($ship_mark)){
            $data['Order']['ship_mark'] = $ship_mark;
        }
        $data['Order']['created'] = $date;
        $data['Order']['updated'] = $date;
//        $data['Order']['pay_time'] = date('Y-m-d H:i:s', strtotime("+59 seconds"));
        $data['Order']['consignee_name'] = empty($user['User']['nickname']) ? '李嘉' : $user['User']['nickname'];
        $data['Order']['consignee_mobilephone'] = empty($user['User']['mobilephone']) ? '17910808972' : $user['User']['mobilephone'];
        $data['Order']['consignee_id'] = $offline_store['OfflineStore']['id'];
        $data['Order']['consignee_address'] = $offline_store['OfflineStore']['name'];
        $data['Order']['coverimg'] = $product['Product']['coverimg'];
        $data['Order']['total_price'] = $product['Product']['price'] * $num;
        $data['Order']['total_all_price'] = $product['Product']['price'] * $num;
        $data['Order']['brand_id'] = $product['Product']['brand_id'];
        $data['Order']['member_id'] = $member_id;
        $data['Order']['type'] = 5;

        $this->log("plan helper is to create order: ".json_encode($data));

        if ($this->Order->save($data)) {
            return $this->Order->getLastInsertID();
        } else {
            $this->log($this->Order->validationErrors); //show validationErrors
            throw new Exception("plan helper create order failed");
        }
    }

    function _insert_cart($user, $product, $num, $send_date, $order_id)
    {
        $date = date('Y-m-d H:i:s');
        $data = array();
        $data['Cart']['name'] = $product['Product']['name'];
        $data['Cart']['order_id'] = $order_id;
        $data['Cart']['creator'] = $user['User']['id'];
        $data['Cart']['type'] = 5;
        $data['Cart']['status'] = 0;
        $data['Cart']['product_id'] = $product['Product']['id'];
        $data['Cart']['coverimg'] = $product['Product']['coverimg'];
        $data['Cart']['price'] = $product['Product']['price'] * $num;
        $data['Cart']['num'] = 1;
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
}