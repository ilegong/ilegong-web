<?php
class TuanController extends AppController
{

    var $name = 'Tuan';

    var $uses = array('TuanTeam', 'TuanBuying', 'Order', 'Cart', 'TuanBuyingMessages', 'TuanProduct', 'ConsignmentDate', 'ProductTry', 'Brand', 'ProductSpecGroup', 'PayNotify', 'OfflineStore');

    /**
     * query tuan orders
     */
    public function beforeFilter(){
        parent::beforeFilter();
        $ship_types = $this->_get_ship_types();
        $this->set('ship_type', $ship_types);
    }
    public function admin_tuan_orders()
    {
        $this->set('b2c_empty_date_address_count', $this->_query_b2c_empty_date_address());
        $this->set('b2c_paid_not_sent_count', $this->_query_b2c_paid_not_send_count());
        $this->set('c2c_paid_not_sent_count', $this->_query_c2c_paid_not_send_count());
        $this->set('orders_today_count', $this->_query_orders_today_count());
        $this->set('query_type', 'byUser');
    }

    public function admin_query_by_user()
    {
        $order_id = $_REQUEST['order_id'];
        $con_name = $_REQUEST['con_name'];
        $con_phone = $_REQUEST['con_phone'];
        $con_creator = $_REQUEST['con_creator'];
        $order_status = empty($_REQUEST['order_status']) ? -1 : $_REQUEST['order_status'];

        $conditions = array();
        if (!empty($con_name)) {
            $conditions['Order.consignee_name'] = $con_name;
            if ($order_status != -1) {
                $conditions['Order.status'] = $order_status;
            }
        }
        if (!empty($con_phone)) {
            $conditions['Order.consignee_mobilephone'] = $con_phone;
            if ($order_status != -1) {
                $conditions['Order.status'] = $order_status;
            }
        }
        if (!empty($con_creator)) {
            $conditions['Order.creator'] = $con_creator;
            if ($order_status != -1) {
                $conditions['Order.status'] = $order_status;
            }
        }
        if (!empty($order_id)) {
            $conditions['Order.id'] = $order_id;
        }
        if(empty($con_name)&&empty($con_creator)&&empty($con_phone)&&$order_status == 14){
            $conditions['Order.status'] = $order_status;
        }

        $this->_query_orders($conditions, 'Order.created DESC');

        $this->set('con_name', $con_name);
        $this->set('order_id', $order_id);
        $this->set('con_phone', $con_phone);
        $this->set('con_creator', $con_creator);
        $this->set('order_status', $order_status);
        $this->set('query_type', 'byUser');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_by_product()
    {
        $product_id = !empty($_REQUEST['product_id']) ? $_REQUEST['product_id'] : -1;
        $order_status = empty($_REQUEST['order_status']) ? -1 : $_REQUEST['order_status'];
        $order_type = empty($_REQUEST['order_type']) ? -1 : $_REQUEST['order_type'];
        $send_date_start = $_REQUEST['send_date_start'];
        $send_date_end = $_REQUEST['send_date_end'];

        $conditions = array();
        if ($product_id != -1 || !empty($send_date_start)) {
            if($product_id != -1){
                $conditions['Cart.product_id'] = $product_id;
            }
            if ($order_type == -1) {
                $conditions['Order.type'] = array(ORDER_TYPE_DEF, ORDER_TYPE_TUAN, ORDER_TYPE_TUAN_SEC);
            } else {
                $conditions['Order.type'] = $order_type;
            }
            if ($order_status != -1) {
                $conditions['Order.status'] = $order_status;
            }
            if (!empty($send_date_start) && !empty($send_date_end)) {
                $conditions['DATE(Cart.send_date) >= '] = $send_date_start;
                $conditions['DATE(Cart.send_date) <= '] = $send_date_end;
            }elseif(!empty($send_date_start)){
                $conditions['DATE(Cart.send_date)'] = $send_date_start;
                if($product_id != -1){
                    $send_date_end = $send_date_start;
                }
            }elseif(!empty($send_date_end)){
                $conditions['DATE(Cart.send_date)'] = $send_date_end;
                $send_date_start = $send_date_end;
            }else{
                $send_date = date("Y-m-d", time());
                $conditions['DATE(Cart.send_date)'] = $send_date;
                $send_date_start = $send_date;
                if($product_id != -1){
                    $send_date_end = $send_date_start;
                }
            }
        }

        $this->_query_orders($conditions, 'Order.created DESC');

        $this->set('product_id', $product_id);
        $this->set('send_date_start', $send_date_start);
        $this->set('send_date_end', $send_date_end);
        $this->set('order_status', $order_status);
        $this->set('order_type', $order_type);
        $this->set('query_type', 'byProduct');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_by_tuan_team()
    {
        $team_id = !empty($_REQUEST['team_id']) ? $_REQUEST['team_id'] : -1;
        $tuan_buying_id = !empty($_REQUEST['tuan_buying_id']) ? $_REQUEST['tuan_buying_id'] : -1;
        $order_status = !empty($_REQUEST['order_status']) ? $_REQUEST['order_status'] : -1;

        $conditions = array();
        $order_by = 'Order.created DESC';
        if ($team_id == -1) {
            $send_date_start = $_REQUEST['send_date_start'];
            if (!empty($send_date_start)) {
                $conditions['Order.type'] = ORDER_TYPE_TUAN;
                $conditions['DATE(Cart.send_date)'] = $send_date_start;
                $order_by = "Order.consignee_address ASC";
            }
            if ($order_status != -1) {
                $conditions['Order.status'] = $order_status;
            }
        } else {
            $conditions['Order.type'] = ORDER_TYPE_TUAN;
            if ($tuan_buying_id != -1) {
                $conditions['Order.member_id'] = $tuan_buying_id;
            } else {
                $tuan_buyings = $this->TuanBuying->find('all', array(
                    'conditions' => array('tuan_id' => $team_id),
                    'fields' => array('id')
                ));
                $conditions['Order.member_id'] = Hash::extract($tuan_buyings, "{n}.TuanBuying.id");
                $send_date_start = $_REQUEST['send_date_start'];
                $send_date_end = $_REQUEST['send_date_end'];
                if (empty($send_date_start)) {
                    $send_date_start = date('Y-m-d', strtotime('-2 days'));
                }
                if (empty($send_date_end)) {
                    $send_date_end = date('Y-m-d', strtotime('+5 days'));
                }
                $conditions['DATE(Cart.send_date) >= '] = $send_date_start;
                $conditions['DATE(Cart.send_date) <= '] = $send_date_end;
            }
            if ($order_status != -1) {
                $conditions['Order.status'] = $order_status;
            }
        }

        $this->_query_orders($conditions, $order_by);

        $this->set('team_id', $team_id);
        $this->set('tuan_buying_id', $tuan_buying_id);
        $this->set('send_date_start', $send_date_start);
        $this->set('send_date_end', $send_date_end);
        $this->set('order_status', $order_status);
        $this->set('query_type', 'byTuanTeam');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_b2c_empty_date_address()
    {
        $conditions = array(
            'OR' => array(
                'Cart.send_date is null',
                array("Order.consignee_id = 0", "Order.consignee_address = ''")
            )
        );
        $conditions['Order.type'] = array(ORDER_TYPE_TUAN, ORDER_TYPE_TUAN_SEC);
        $conditions['Order.status'] = 1;
        $conditions['DATE(Order.created) > '] = date('Y-m-d', strtotime('-62 days'));

        $this->_query_orders($conditions, 'Order.created DESC');

        $this->set('query_type', 'emptySendDate');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_abnormal_order(){
        $conditions = array(
            'OR' => array(
                array('Cart.send_date is null','Order.type in (5,6)'),
                array("Order.consignee_id = 0", "Order.consignee_address = ''"),
                array("Order.type = 1", "Order.ship_mark !=''"),
                'Order.pay_time  is null',
                array('Order.ship_mark = "ziti"','Order.consignee_id = 0','Order.type in (5,6)'),
                array('Order.ship_mark = "kuaidi"','Order.consignee_id !=0','Order.type in (5,6)'),
            )
        );
        $conditions['Order.type'] = array(ORDER_TYPE_TUAN, ORDER_TYPE_TUAN_SEC,ORDER_TYPE_DEF);
        $conditions['Order.status'] = array(ORDER_STATUS_PAID,ORDER_STATUS_SHIPPED);
        $conditions['DATE(Order.created) > '] = date('Y-m-d', strtotime('-62 days'));
        $this->_query_orders($conditions, 'Order.created DESC');
        $this->set('query_type', 'abnormalOrder');
        $this->render("admin_tuan_orders");
    }
    public function admin_query_b2c_paid_not_send()
    {
        $conditions['Order.type'] = array(ORDER_TYPE_TUAN, ORDER_TYPE_TUAN_SEC);
        $conditions['Order.status'] = ORDER_STATUS_PAID;
        $conditions['DATE(Cart.send_date) <'] = date('Y-m-d');
        $this->_query_orders($conditions, 'Order.created DESC');

        $this->set('query_type', 'b2cPaidNotSend');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_c2c_paid_not_send()
    {
        $conditions['OR'] = array('DATE(Order.pay_time) <'=>date('Y-m-d'),'Order.pay_time' => null);
        $conditions['Order.type'] = ORDER_TYPE_DEF;
        $conditions['Order.status'] = ORDER_STATUS_PAID;
        $this->_query_orders($conditions, 'Order.updated');

        $this->set('query_type', 'c2cPaidNotSend');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_orders_today()
    {
        $pay_date = !empty($_REQUEST['pay_date']) ? $_REQUEST['pay_date'] : date('Y-m-d');
        $order_status = isset($_REQUEST['order_status']) ? $_REQUEST['order_status'] : -1;

        $conditions = array('DATE(Order.pay_time)'=>$pay_date);
        if($order_status != -1){
            $conditions['Order.status'] = $order_status;
        }

        $this->_query_orders($conditions, 'Order.updated');

        $this->set('query_type', 'ordersToday');
        $this->set('order_status', $order_status);
        $this->set('pay_date', $pay_date);
        $this->render("admin_tuan_orders");
    }

    /**
     * 团购功能列表
     */
    public function admin_tuan_func_list()
    {
        $brand_count = $this->Brand->query('select count(*) as c from cake_brands where deleted = 0');
        $this->set('brand_count', $brand_count[0][0]['c']);
        $tuan_product_count = $this->TuanProduct->query('select count(*) as c from cake_tuan_products where deleted = 0');
        $this->set('tuan_product_count', $tuan_product_count[0][0]['c']);
        $seckill_product_count = $this->ProductTry->query('select count(*) as c from cake_product_tries where deleted = 0');
        $this->set('seckill_product_count', $seckill_product_count[0][0]['c']);

        $tuan_team_count = $this->TuanTeam->query('select count(*) as c from cake_tuan_teams where published = 1');
        $this->set('tuan_team_count', $tuan_team_count[0][0]['c']);
        $offline_stores_count = $this->OfflineStore->query('select count(*) as c from cake_offline_stores where deleted = 0');
        $this->set('offline_stores_count', $offline_stores_count[0][0]['c']);
        $this->log('offline stores count: '.$offline_stores_count[0][0]['c']);
        $expired_tuan_buying_count = $this->TuanBuying->query('select count(*) as c from cake_tuan_buyings where end_time < now() and status = 0');
        $this->set('expired_tuan_buying_count', $expired_tuan_buying_count[0][0]['c']);

        $this->set('b2c_empty_date_address_count', $this->_query_b2c_empty_date_address());
        $this->set('b2c_paid_not_sent_count', $this->_query_b2c_paid_not_send_count());
        $this->set('c2c_paid_not_sent_count', $this->_query_c2c_paid_not_send_count());
        $this->set('orders_today_count', $this->_query_orders_today_count());
    }

    function admin_send_date($type)
    {
        $tuan_buyings = $this->TuanBuying->find('all', array(
            'conditions' => array(
                'consignment_type' => $type
            ),
            'fields' => array("id", "consignment_type", "consign_time")
        ));
        $tb_ids = Hash::extract($tuan_buyings, "{n}.TuanBuying.id");
        $tuan_buyings = Hash::combine($tuan_buyings, "{n}.TuanBuying.id", "{n}.TuanBuying");
        $this->log('tuan buyings: ' . json_encode($tuan_buyings));

        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'type' => 5,
                'member_id' => $tb_ids
            ),
            'fields' => array("id", "member_id")
        ));
        $order_ids = Hash::extract($orders, "{n}.Order.id");
        $orders = Hash::combine($orders, "{n}.Order.id", "{n}.Order");
        $this->log('orders: ' . json_encode($orders));

        $carts = $this->Cart->find('all', array(
            'conditions' => array("order_id" => $order_ids),
            'fields' => array("id", "consignment_date", "send_date", "order_id")
        ));
        $cart_ids = Hash::extract($carts, "{n}.Cart.id");
        $this->log('carts: ' . json_encode($carts));

        $noConsignmentDatesOfCart = array();
        $noOrdersOfCart = array();
        $noTuanBuyingsOfOrder = array();
        $unmatched = array();
        $toBeUpdated = array();
        $alreadyUpdated = array();
        foreach ($carts as &$cart) {
            if (empty($cart['Cart']['consignment_date'])) {
                // find consignment_date by member_id(tuan_buying)
                $order = $orders[$cart['Cart']['order_id']];
                if (empty($order)) {
                    $noOrdersOfCart[] = $cart['Cart']['id'];
                } else {
                    $tuan_buying = $tuan_buyings[$order['member_id']];
                    if (empty($tuan_buying)) {
                        $noTuanBuyingsOfOrder[] = $cart['Cart']['id'] . ", " . json_encode($order);
                    } else {
                        $send_date = $tuan_buying['consign_time'];
                        if (empty($send_date)) {
                            $noSendDateOfTuanBuying[] = $cart['Cart']['id'] . ", " . $order['id'] . ", " . $tuan_buying['id'];
                        } else {
                            if (!empty($cart['Cart']['send_date'])) {
                                if ($cart['Cart']['send_date'] == $send_date) {
                                    $alreadyUpdated[] = $cart['Cart']['id'];
                                } else {
                                    $unmatched[] = $cart['Cart']['id'];
                                    $unmatched[$cart['Cart']['id']] = "cart " . $cart['Cart']['id'] . ", send_date: " . $cart['Cart']['send_date'] . ", TuanBuying.consign_time: " . $send_date;
                                }
                            } else {
                                $this->Cart->updateAll(array("send_date" => "'" . $send_date . "'"), array('id' => $cart['Cart']['id']));
                                $toBeUpdated[] = $cart['Cart']['id'];
                            }
                        }
                    }
                }
            } else {
                // find by table consignmentDate
                $consignmentDate = $this->ConsignmentDate->find("first", array(
                    "conditions" => array(
                        "id" => $cart['Cart']['consignment_date']
                    )
                ));
                if (empty($consignmentDate)) {
                    $noConsignmentDatesOfCart[] = $cart['Cart']['id'];
                } else {
                    if (!empty($cart['Cart']['send_date'])) {
                        if ($cart['Cart']['send_date'] == $consignmentDate['ConsignmentDate']['send_date']) {
                            $alreadyUpdated[] = $cart['Cart']['id'];
                        } else {
                            $unmatched[$cart['Cart']['id']] = "cart " . $cart['Cart']['id'] . ", send_date: " . $cart['Cart']['send_date'] . ", Cart.consignment_date: " . $cart['Cart']['consignment_date'] . ", ConsignmentDate.consignment_date: " . $consignmentDate['ConsignmentDate']['send_date'];
                        }
                    } else {
                        $this->Cart->updateAll(array('send_date' => "'" . $consignmentDate['ConsignmentDate']['send_date'] . "'"), array('id' => $cart['Cart']['id']));
                        $toBeUpdated[] = $cart['Cart']['id'];
                    }
                }
            }
        }

        $this->set('tb_ids', $tb_ids);
        $this->set('order_ids', $order_ids);
        $this->set('cart_ids', $cart_ids);

        $this->set('noConsignmentDatesOfCart', $noConsignmentDatesOfCart);
        $this->set('noOrdersOfCart', $noOrdersOfCart);
        $this->set('noTuanBuyingsOfOrder', $noTuanBuyingsOfOrder);
        $this->set('unmatched', $unmatched);
        $this->set('toBeUpdated', $toBeUpdated);
        $this->set('alreadyUpdated', $alreadyUpdated);
    }

    public function _query_orders($conditions, $order_by, $limit = null)
    {
        $this->PayNotify->query("update cake_pay_notifies set order_id =  substring_index(substring_index(out_trade_no,'-',2),'-',-1) where status = 6 and order_id is NULL");
        $join_conditions = array(
            array(
                'table' => 'pay_notifies',
                'alias' => 'Pay',
                'conditions' => array(
                    'Pay.order_id = Order.id'
                ),
                'type' => 'LEFT',
            ),
            array(
                'table' => 'carts',
                'alias' => 'Cart',
                'conditions' => array(
                    'Cart.order_id = Order.id'
                ),
                'type' => 'INNER'
            )
        );

        $all_orders = array();
        if (!empty($conditions)) {
            $params = array(
                'conditions' => $conditions,
                'joins' => $join_conditions,
                'fields' => array('Order.*', 'Pay.trade_type', 'Pay.out_trade_no', 'Cart.product_id', 'Cart.try_id', 'Cart.send_date'),
                'group' => 'Order.id',
                'order' => $order_by
            );
            if (!empty($limit)) {
                $params['limit'] = $limit;
            }
            $this->log('query order conditions: ' . json_encode($params));
            $all_orders = $this->Order->find('all', $params);
        } else {
            $this->log('order condition is empty: ' . json_encode($conditions));
        }
        $order_ids = array_unique(Hash::extract($all_orders, "{n}.Order.id"));
        $orders = $all_orders;
        $carts = array();
        if (!empty($order_ids)) {
            $carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $order_ids
                ),
            ));
        }

        $tuan_buys = array();
        $tuan_buying_ids = array_diff(Hash::extract($orders, "{n}.Order.member_id"), array(0));
        if (!empty($tuan_buying_ids)) {
            $tuan_buys = $this->TuanBuying->find('all', array(
                'conditions' => array(
                    'id' => $tuan_buying_ids
                )
            ));
            $tuan_buys = Hash::combine($tuan_buys, '{n}.TuanBuying.id', '{n}.TuanBuying');
        }

        $tuan_teams = array();
        if (!empty($tuan_buys)) {
            $tuan_ids = Hash::extract($tuan_buys, '{n}.tuan_id');
            $tuan_teams = $this->TuanTeam->find('all', array(
                'conditions' => array(
                    'id' => $tuan_ids
                )
            ));
            $tuan_teams = Hash::combine($tuan_teams, '{n}.TuanTeam.id', '{n}.TuanTeam');
        }

        $offline_stores = array();
        $offline_store_ids = array_filter(array_unique(Hash::extract($orders, "{n}.Order.consignee_id")));
        if (!empty($offline_store_ids)) {
            $offline_stores = $this->OfflineStore->find('all', array(
                'conditions'=> array(
                    'id' => $offline_store_ids
                )
            ));
            $offline_stores = Hash::combine($offline_stores, "{n}.OfflineStore.id", "{n}");
        }

        $p_ids = Hash::extract($carts, '{n}.Cart.product_id');
        $spec_groups = array();
        if (!empty($p_ids)) {
            $spec_groups = $this->ProductSpecGroup->find('all', array(
                'conditions' => array(
                    'product_id' => $p_ids
                )
            ));
            $spec_groups = Hash::combine($spec_groups, '{n}.ProductSpecGroup.id', '{n}.ProductSpecGroup.spec_names');
        }

        $order_carts = array();
        $product_detail = array();
        foreach ($carts as &$c) {
            $c_order_id = $c['Cart']['order_id'];
            $specId = $c['Cart']['specId'];
            $c['Cart']['spec_name'] = $spec_groups[$specId];
            if (!isset($order_carts[$c_order_id])) {
                $order_carts[$c_order_id] = array();
            }
            $order_carts[$c_order_id][] = $c;
            if(isset($product_detail[$c['Cart']['product_id']])){
                $product_detail[$c['Cart']['product_id']] +=  $c['Cart']['num'];
            }else{
                $product_detail[$c['Cart']['product_id']] =  $c['Cart']['num'];
            }
        }

        //排期
        $consign_ids = array_unique(Hash::extract($order_carts, '{n}.Cart.consignment_date'));
        $consign_dates = array();
        if (count($consign_ids) != 1 || !empty($consign_ids[0])) {
            $consign_dates = $this->ConsignmentDate->find('all', array(
                'conditions' => array(
                    'id' => $consign_ids
                )
            ));
            $consign_dates = Hash::combine($consign_dates, '{n}.ConsignmentDate.id', '{n}.ConsignmentDate.send_date');
        }

        // product count
        $product_count = 0;
        if (!empty($order_ids)) {
            $order_id_strs = '(' . join(',', $order_ids) . ')';
            $result = $this->Cart->query('select sum(num) from cake_carts where order_id in ' . $order_id_strs);
            $product_count = $result[0][0]['sum(num)'];
        }

        //total_money
        $total_money = 0;
        if(!empty($orders)){
            foreach ($orders as $o) {
                $o_status = $o['Order']['status'];
                if ($o_status == 1 || $o_status == 2 || $o_status == 3) {
                    $total_money = $total_money + $o['Order']['total_all_price'];
                }
            }
            $this->set('total_order_money', $total_money);
        }

        // brands
        if (!empty($p_ids)) {
            $join_conditions = array(
                array(
                    'table' => 'products',
                    'alias' => 'Product',
                    'conditions' => array(
                        'Product.brand_id = Brand.id'
                    ),
                    'type' => 'INNER',
                )
            );
            $brands = $this->Brand->find('all', array(
                'conditions' => array(
                    "Product.id" => $p_ids
                ),
                'joins' => $join_conditions,
                'fields' => array('Brand.id', 'Brand.name', 'Product.id', 'Product.name')
            ));
            $brands = Hash::combine($brands, '{n}.Product.id', '{n}');
        }

        $ship_mark_enum = array('ziti'=>array('name'=>'自提','style'=>'active'),'sfby'=>array('name'=>'顺丰包邮','style'=>'success'),'sfdf'=>array('name'=>'顺丰到付','style'=>'warning'),'kuaidi'=>array('name'=>'快递','style'=>'danger'),'c2c'=>array('name'=>'c2c订单','style'=>'info'),'none'=>array('name'=>'没有标注','style'=>'info'));
        $this->set('ship_mark_enum',$ship_mark_enum);

        $ziti_orders = array_filter($orders,'ziti_order_filter');
        $sfby_orders = array_filter($orders,'sfby_order_filter');
        $sfdf_orders = array_filter($orders,'sfdf_order_filter');
        $kuaidi_orders = array_filter($orders,'kuaidi_order_filter');
        $c2c_orders = array_filter($orders,'c2c_order_filter');
        $none_orders = array_filter($orders,'none_order_filter');
        $map_other_orders = array('sfby' => $sfby_orders,'sfdf'=> $sfdf_orders,'kuaidi' => $kuaidi_orders,'none'=> $none_orders,'c2c'=> $c2c_orders);
        $map_ziti_orders = array();

        foreach($ziti_orders as $item){
           $consignee_id = $item['Order']['consignee_id'];
           if($consignee_id==null){
               $consignee_id=0;
           }
           if(!array_key_exists($consignee_id,$map_ziti_orders)){
                $map_ziti_orders[$consignee_id] = array();
           }
           $map_ziti_orders[$consignee_id][] = $item;
        }

        $pys_ziti_point = array_filter($offline_stores,'pys_ziti_filter');
        $hlj_ziti_point = array_filter($offline_stores,'hlj_ziti_filter');

        $this->set('pys_ziti_point',$pys_ziti_point);
        $this->set('hlj_ziti_point',$hlj_ziti_point);


        $this->set('map_ziti_orders',$map_ziti_orders);
        $this->set('map_other_orders',$map_other_orders);

        $conditions['Order.type'] = ORDER_TYPE_DEF;
        $conditions['Order.status'] = ORDER_STATUS_PAID;
        $conditions['DATE(Order.updated) <'] = date('Y-m-d H:i:s');

        $this->set('b2c_empty_date_address_count', $this->_query_b2c_empty_date_address());
        $this->set('b2c_paid_not_sent_count', $this->_query_b2c_paid_not_send_count());
        $this->set('c2c_paid_not_sent_count', $this->_query_c2c_paid_not_send_count());
        $this->set('orders_today_count', $this->_query_orders_today_count());
        $this->set('abnormal_order_count',$this->_query_abnormal_order());

        $this->set('should_count_nums', true);
        $this->set('product_count', $product_count);
        $this->set('orders', $orders);
        $this->set('tuan_buys', $tuan_buys);
        $this->set('tuan_teams', $tuan_teams);
        $this->set('offline_stores', $offline_stores);
        $this->set('order_carts', $order_carts);
        $this->set('brands', $brands);
        $this->set('product_detail', $product_detail);
        $this->set('consign_dates', $consign_dates);
        return $c;
    }

    public function admin_query_by_offline_store(){
        $store_id = !empty($_REQUEST['store_id']) ? $_REQUEST['store_id'] : -1;
        $order_status = !empty($_REQUEST['order_status']) ? $_REQUEST['order_status'] : -1;
        $send_date = $_REQUEST['send_date'];
        $end_stat_date = $_REQUEST['end_stat_date'];
        $conditions = array();
        $order_by = 'Cart.product_id, Order.consignee_id DESC';
        if($store_id != -1 || !empty($send_date)){
            $conditions['Order.type'] = array(ORDER_TYPE_TUAN, ORDER_TYPE_TUAN_SEC);
            if ($order_status != -1) {
                $conditions['Order.status'] = $order_status;
            }
            if($store_id != -1){
                $store_ids = explode(",", $store_id);
                $conditions['Order.consignee_id'] = $store_ids;
            }
            if (!empty($send_date) && !empty($end_stat_date)) {
                $conditions['DATE(Cart.send_date) >= '] = $send_date;
                $conditions['DATE(Cart.send_date) <= '] = $end_stat_date;
            }elseif(!empty($send_date)){
                $conditions['DATE(Cart.send_date)'] = $send_date;
                if($store_id != -1){
                    $end_stat_date = $send_date;
                }
            }elseif(!empty($end_stat_date)){
                $conditions['DATE(Cart.send_date)'] = $end_stat_date;
                $send_date = $end_stat_date;
            }else{
                $send_date = date("Y-m-d", time());
                $conditions['DATE(Cart.send_date)'] = $send_date;
            }
        }
        $this->_query_orders($conditions, $order_by);
        $this->set('store_id', $store_id);
        $this->set(compact('send_date', 'end_stat_date'));
        $this->set('order_status', $order_status);
        $this->set('query_type', 'byOfflineStore');
        $this->render("admin_tuan_orders");
    }
    public function _get_ship_types(){
        $shipTypes = ShipAddress::ship_types();
        $ship_types = Hash::combine($shipTypes, '{n}.id', '{n}.name');
        return $ship_types;
    }

    public function _query_b2c_paid_not_send_count(){
        $b2c_paid_not_sent_count = $this->Order->query('select count(distinct o.id) as ct from cake_orders o inner join cake_carts c on c.order_id = o.id where o.type in (5, 6) and o.status = 1 and c.send_date < CURDATE()');
        return $b2c_paid_not_sent_count[0][0]['ct'];
    }

    public function _query_c2c_paid_not_send_count(){
        $c2c_paid_not_sent_count = $this->Order->query('select count(distinct o.id) as ct from cake_orders o inner join cake_carts c on o.id = c.order_id where o.type = 1 and o.status = 1 and o.pay_time < CURDATE()');
        return $c2c_paid_not_sent_count[0][0]['ct'];
    }

    public function _query_b2c_empty_date_address(){
        $empty_send_date_count = $this->Order->query('select count(distinct o.id) as ct from cake_orders o inner join cake_carts c on c.order_id = o.id where (c.send_date is null or (o.consignee_id = 0 and o.consignee_address = "")) and o.type in (5, 6) and o.status = 1 and DATE(o.created) > '.date('Y-m-d', strtotime('-62 days')));
        return $empty_send_date_count[0][0]['ct'];
    }

    public function _query_orders_today_count(){
        $count = $this->Order->query('select count(distinct o.id) as ct from cake_orders o inner join cake_carts c on c.order_id = o.id where DATE(o.pay_time) >= CURDATE() and o.status > 0');
        return $count[0][0]['ct'];
    }

    public function _query_abnormal_order(){
        $abnormal_order_count = $this->Order->query('select count(o.id) as ct from cake_orders o inner join cake_carts c on c.order_id = o.id where ((c.send_date is null and o.type in (5,6)) or (o.consignee_id = 0 and o.consignee_address = "") or (o.type = 1 and o.ship_mark != "") or o.pay_time is null or (o.ship_mark = "ziti" and o.consignee_id = 0 and o.type in (5,6)) or (o.ship_mark = "kuaidi" and o.consignee_id !=0 and o.type in (5,6))) and o.type in (1,5,6) and o.status in (1,2) and DATE(o.created) > "'.date('Y-m-d', strtotime('-62 days')).'"');
       $this->log('count'.json_encode($abnormal_order_count));
        return $abnormal_order_count[0][0]['ct'];
    }
    public function admin_update_order_status_to_refunded(){
        $this->autoRender = false;
        $order_id = $_REQUEST['orderId'];
        $order_status = $_REQUEST['orderStatus'];
        $this->log('status'.json_encode($order_status));
        if(!empty($order_id)){
            if($this->Order->updateAll(array('status' => $order_status),array('id' => $order_id))){
                $returnInfo  = array('success' => true,'msg' => '订单状态修改成功');
            }else{
                $returnInfo  = array('success' => false,'msg' =>'订单状态修改失败，请重试');

            }
            echo json_encode($returnInfo);
        }
    }

    public function admin_to_fix_error_order(){
        $this->log('test');
    }

    public function admin_fix_ziti_error_order(){
        $ids = $_POST['order_ids'];
        $ids = explode(',', $ids);
        $orders = $this->Order->find('all',array(
            'conditions' => array(
                'id' => $ids
            )
        ));
        $tuan_buy_ids = Hash::extract($orders,'{n}.Order.member_id');
        $order_tuan_buy_map = Hash::combine($orders,'{n}.Order.id','{n}.Order.member_id');
        $tuan_buyings = $this->TuanBuying->find('all',array(
            'conditions' => array(
                'id' => $tuan_buy_ids
            )
        ));
        $tuan_buy_tuan_map = Hash::combine($tuan_buyings,'{n}.TuanBuying.id','{n}.TuanBuying.tuan_id');
        $tuan_ids = Hash::extract($tuan_buyings,'{n}.TuanBuying.tuan_id');
        $tuans = $this->TuanTeam->find('all',array(
            'conditions' => array(
                'id' => $tuan_ids
            )
        ));
        $tuan_offline_store_map = Hash::combine($tuans,'{n}.TuanTeam.id','{n}.TuanTeam.offline_store_id');
        foreach($ids as $id){
            $offline_store_id = $tuan_offline_store_map[$tuan_buy_tuan_map[$order_tuan_buy_map[$id]]];
            $this->Order->updateAll(array(consignee_id=>$offline_store_id),array('id'=>$id));
        }
        $this->redirect('/admin/tuan/query_abnormal_order');
    }
}