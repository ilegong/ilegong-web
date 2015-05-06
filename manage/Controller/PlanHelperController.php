<?php
class PlanHelperController extends AppController
{

    var $name = 'PlanHelper';

    var $uses = array('User', 'TuanTeam', 'TuanBuying', 'Order', 'Cart', 'Product', 'OfflineStore');

    public function admin_order()
    {
        $this->autoRender = false;

        $user_id = $_REQUEST['user_id'];
        $tuan_buying_id = $_REQUEST['tuan_buying_id'];
        $num = isset($_REQUEST['num']) ? $_REQUEST['num'] : 1;

        $this->log("plan helper is to create order for ".$user_id." and tuan buying ".$tuan_buying_id." with num ".$num);
        if(!($user_id >= 810163 || $user_id <= 810223) && !($user_id >= 810096 || $user_id <= 810158)){
            throw new Exception("invalid user id ".$user_id);
        }
        $user = $this->User->find('first', array(
            conditions => array(
                'id' => $user_id
            )
        ));

        $tuan_buying = $this->TuanBuying->find('first', array(
            conditions => array(
                'id' => $tuan_buying_id
            )
        ));
        if(empty($tuan_buying)){
            throw new Exception('tuan buying does not exist: '. $tuan_buying_id);
        }

        $tuan_team = $this->TuanTeam->find('first', array(
            conditions => array(
                'id' => $tuan_buying['TuanBuying']['tuan_id']
            )
        ));
        if($tuan_team['TuanTeam']['id'] != 109 && $tuan_team['TuanTeam']['id'] != 110){
            throw new Exception('Invalid tuan team: '.$tuan_team['TuanTeam']['id']);
        }

        $product = $this->Product->find('first', array(
            conditions => array(
                'id' => $tuan_buying['TuanBuying']['pid']
            )
        ));

        $offline_store = $this->OfflineStore->find('first', array(
            conditions => array(
                'id' => $tuan_team['TuanTeam']['offline_store_id']
            )
        ));
        if(empty($offline_store)){
            throw new Exception('offline store does not exist: '. $tuan_buying_id);
        }

        $order_id = $this->_insert_order($user, $product, $num, $tuan_buying, $offline_store);
        $this->log("plan helper create order successfully: ".$order_id);
        $cart_id = $this->_insert_cart($user, $product, $num, $tuan_buying, $offline_store, $order_id);
        $this->log("plan helper create cart successfully: ".$cart_id);

        echo json_encode(array("order_id" => $order_id, "cart_id" => $cart_id));
    }

    function _insert_order($user, $product, $num, $tuan_buying, $offline_store){
        $date = date('Y-m-d H:i:s', "+17 seconds");

        $data = array();
        $data['Order']['creator'] = $user['User']['id'];
        $data['Order']['status'] = 2;
        $data['Order']['created'] = $date;
        $data['Order']['updated'] = $date;
        $data['Order']['pay_time'] = date('Y-m-d H:i:s', "+52 seconds");
        $data['Order']['consignee_name'] = $user['User']['nickname'];
        $data['Order']['consignee_address'] = $offline_store['OfflineStore']['name'];
        $data['Order']['consignee_mobilephone'] = $user['User']['mobilephone'];
        $data['Order']['coverimg'] = $product['Product']['coverimg'];
        $data['Order']['total_price'] = $product['Product']['price'] * $num;
        $data['Order']['total_all_price'] = $product['Product']['price'] * $num;
        $data['Order']['brand_id'] = 92;
        $data['Order']['member_id'] = $tuan_buying['TuanBuying']['id'];
        $data['Order']['type'] = 5;

        $this->Order->save($data);
        return $this->Order->getLastInsertID();
    }

    function _insert_cart($user, $product, $num, $tuan_buying, $offline_store, $order_id){
        $date = date('Y-m-d H:i:s');
        $data = array();
        $data['Cart']['name'] = $product['Product']['name'];
        $data['Cart']['order_id'] = $order_id;
        $data['Cart']['creator'] = $user['User']['id'];
        $data['Cart']['type'] = 5;
        $data['Cart']['status'] = 2;
        $data['Cart']['product_id'] = $product['Product']['id'];
        $data['Cart']['price'] = $product['Product']['price'] * $num;
        $data['Cart']['num'] = 1;
        $data['Cart']['session_id'] = $this->Session->id();
        $data['Cart']['created'] = $date;
        $data['Cart']['updated'] = $date;
        $data['Cart']['modified'] = $date;
        $data['Cart']['send_date'] = $tuan_buying['TuanBuying']['consign_time'];

        $this->Cart->save($data);
        return $this->Cart->getLastInsertID();
    }
}