<?php
class TuanController extends AppController
{

    var $name = 'Tuan';

    var $uses = array('TuanTeam', 'TuanBuying', 'Order', 'Cart', 'Product', 'ConsignmentDate', 'Brand', 'ProductSpecGroup', 'PayNotify', 'OfflineStore','OrderMessage');

    /**
     * query tuan orders
     */
    public function beforeFilter(){
        parent::beforeFilter();
        $ship_types = $this->_get_ship_types();
        $this->set('ship_type', $ship_types);
        list($reach_order,$send_out_order) = $this->_get_orderMsg();
        $this->set('reach_order',implode(',',$reach_order));
        $this->set('send_out_order',implode(',',$send_out_order));
    }
    public function admin_tuan_orders()
    {
        $this->set('abnormal_order_count', $this->_query_abnormal_order());
        $this->set('b2c_paid_not_sent_count', $this->_query_b2c_paid_not_send_count());
        $this->set('c2c_paid_not_sent_count', $this->_query_c2c_paid_not_send_count());
        $this->set('orders_today_count', $this->_query_orders_today_count());
        $this->set('query_type', 'byUser');
    }

    public function admin_quick_query()
    {
        $order_id = $_REQUEST['order_id'];
        $con_name = $_REQUEST['con_name'];
        $con_phone = $_REQUEST['con_phone'];
        $con_creator = $_REQUEST['con_creator'];
        $cart_status = isset($_REQUEST['cart_status']) ? $_REQUEST['cart_status'] : -1;
        $flag = isset($_REQUEST['flag']) ? $_REQUEST['flag'] : -1;

        $conditions = array();
        if (!empty($con_name)) {
            $conditions['Order.consignee_name'] = $con_name;
        }
        if (!empty($con_phone)) {
            $conditions['Order.consignee_mobilephone'] = $con_phone;
        }
        if (!empty($con_creator)) {
            $conditions['Order.creator'] = $con_creator;
        }
        if (!empty($order_id)) {
            $conditions['Order.id'] = $order_id;
        }
        if (!empty($con_name)|| !empty($con_phone)|| !empty($con_creator)|| !empty($order_id)|| $cart_status == 14){
            if ($cart_status != -1) {
                $conditions['Cart.status'] = $cart_status;
            }
        }
        if($flag == 7){
            $conditions['Order.flag'] = $flag;
            if(isset($_REQUEST['pay_date_start']) && !isset($_REQUEST['pay_date_end'])){
                $conditions['DATE(Order.pay_time)'] = $_REQUEST['pay_date_start'];
            }
            if(!isset($_REQUEST['pay_date_start']) && isset($_REQUEST['pay_date_end'])){
                $conditions['DATE(Order.pay_time)'] = $_REQUEST['pay_date_end'];
            }
            if(isset($_REQUEST['pay_date_start']) && isset($_REQUEST['pay_date_end'])){
                $conditions[] = "DATE(Order.pay_time) BETWEEN '".$_REQUEST['pay_date_start']."' AND '".$_REQUEST['pay_date_end']."'";
            }
        }

        $this->_query_orders($conditions, 'Order.created DESC');

        $this->set('con_name', $con_name);
        $this->set('order_id', $order_id);
        $this->set('con_phone', $con_phone);
        $this->set('con_creator', $con_creator);
        $this->set('cart_status', $cart_status);
        $this->set('query_type', 'quickQuery');
        $this->set('flag', $flag);
        $this->set('pay_date_start', $_REQUEST['pay_date_start']);
        $this->set('pay_date_end', $_REQUEST['pay_date_end']);
        $this->render("admin_tuan_orders");
    }

    public function admin_query_by_tuan_team()
    {
        $team_id = !empty($_REQUEST['team_id']) ? $_REQUEST['team_id'] : -1;
        $tuan_buying_id = !empty($_REQUEST['tuan_buying_id']) ? $_REQUEST['tuan_buying_id'] : -1;
        $cart_status = !empty($_REQUEST['cart_status']) ? $_REQUEST['cart_status'] : -1;

        $conditions = array();
        $order_by = 'Order.created DESC';
        if ($team_id == -1) {
            $send_date_start = $_REQUEST['send_date_start'];
            if (!empty($send_date_start)) {
                $conditions['Order.type'] = ORDER_TYPE_TUAN;
                $conditions['DATE(Cart.send_date)'] = $send_date_start;
                $order_by = "Order.consignee_address ASC";
            }
            if ($cart_status != -1) {
                $conditions['Cart.status'] = $cart_status;
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
            if ($cart_status != -1) {
                $conditions['Cart.status'] = $cart_status;
            }
        }

        $this->_query_orders($conditions, $order_by);

        $this->set('team_id', $team_id);
        $this->set('tuan_buying_id', $tuan_buying_id);
        $this->set('send_date_start', $send_date_start);
        $this->set('send_date_end', $send_date_end);
        $this->set('cart_status', $cart_status);
        $this->set('query_type', 'byTuanTeam');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_abnormal_order(){
        $b2c_brand_ids = implode(",", b2c_brands());

        $conditions = array(
            'OR' => array (
                array('Cart.send_date is null', 'Order.brand_id in ('.$b2c_brand_ids.')'),    // 无发货时间
                array("Order.consignee_id = 0", "Order.consignee_address = ''"), // 无自提点，送货地址为空
                array('Order.pay_time  is null', 'Order.ship_mark != "sfdf"'), // 非顺丰到付，但无付款时间
                array('Order.ship_mark = ""'), // 无配送方式
                array('Order.ship_mark is NULL'), // 无配送方式
                array('Order.ship_mark = "kuaidi"', "Order.consignee_address = '' or Order.consignee_address is null"), // 快递，收货地址为空
                array('Order.ship_mark = "ziti"','(Order.consignee_id = 0 or Order.consignee_id is null)'), // 自提，无自提点
                // 自提点不支持送货上门，但是有备注地址
            )
        );
        $conditions['Cart.status'] = array(ORDER_STATUS_PAID,ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY);
        $conditions['DATE(Order.created) > '] = date('Y-m-d', strtotime('-62 days'));
        $this->_query_orders($conditions, 'Order.created DESC');
        $this->set('query_type', 'abnormalOrder');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_b2c_paid_not_send()
    {
        $conditions['Order.brand_id'] = b2c_brands();
        $conditions['Cart.status'] = ORDER_STATUS_PAID;
        $conditions['DATE(Cart.send_date) <'] = date('Y-m-d');
        $this->_query_orders($conditions, 'Order.created DESC');

        $this->set('query_type', 'b2cPaidNotSend');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_c2c_paid_not_send()
    {
        $brand_id = isset($_REQUEST['brand_id']) ? $_REQUEST['brand_id'] : -1;

        $conditions['Cart.status'] = ORDER_STATUS_PAID;
        if($brand_id != -1){
            $conditions['Order.brand_id'] = $brand_id;
        }
        else{
            $conditions['Order.brand_id !='] = b2c_brands();
        }
        $this->_query_orders($conditions, 'Order.updated');

        if($brand_id != -1){
            $this->set('brand_id', $brand_id);
        }
        $this->set('query_type', 'c2cPaidNotSend');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_daily_orders()
    {
        $conditions = array();
        if(isset($_REQUEST['pay_date'])){
            $conditions['DATE(Order.pay_time)'] = $_REQUEST['pay_date'];
        }

        $this->_query_orders($conditions, 'Order.updated');

        $this->set('query_type', 'dailyOrders');
        $this->set('pay_date', $_REQUEST['pay_date']);
        $this->render("admin_tuan_orders");
    }

    /**
     * 团购功能列表
     */
    public function admin_tuan_func_list()
    {
        $brand_count = $this->Brand->query('select count(*) as c from cake_brands where deleted = 0');
        $this->set('brand_count', $brand_count[0][0]['c']);

        $this->loadModel('TuanProduct');
        $tuan_product_count = $this->TuanProduct->query('select count(*) as c from cake_tuan_products where deleted = 0');
        $this->set('tuan_product_count', $tuan_product_count[0][0]['c']);

        $this->loadModel('ProductTry');
        $seckill_product_count = $this->ProductTry->query('select count(*) as c from cake_product_tries where deleted = 0');
        $this->set('seckill_product_count', $seckill_product_count[0][0]['c']);

        $tuan_team_count = $this->TuanTeam->query('select count(*) as c from cake_tuan_teams where published = 1');
        $this->set('tuan_team_count', $tuan_team_count[0][0]['c']);
        $offline_stores_count = $this->OfflineStore->query('select count(*) as c from cake_offline_stores where deleted = 0');
        $this->set('offline_stores_count', $offline_stores_count[0][0]['c']);
        $this->log('offline stores count: '.$offline_stores_count[0][0]['c']);
        $expired_tuan_buying_count = $this->TuanBuying->query('select count(*) as c from cake_tuan_buyings where end_time < now() and status = 0');
        $this->set('expired_tuan_buying_count', $expired_tuan_buying_count[0][0]['c']);

        $this->set('abnormal_order_count', $this->_query_abnormal_order());
        $this->set('b2c_paid_not_sent_count', $this->_query_b2c_paid_not_send_count());
        $this->set('c2c_paid_not_sent_count', $this->_query_c2c_paid_not_send_count());
        $this->set('abnormal_order_count',$this->_query_abnormal_order());

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
                'fields' => array('Order.*', 'Pay.trade_type', 'Pay.out_trade_no', 'Cart.id', 'Cart.product_id', 'Cart.send_date'),
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
        $cart_ids = array_unique(Hash::extract($all_orders, "{n}.Cart.id"));

        $orders = array();
        foreach($all_orders as $order){
            if(!isset($orders[$order['Order']['id']])){
                $orders[$order['Order']['id']] = $order;
            }
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

        $carts = array();
        if (!empty($cart_ids)) {
            $carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $order_ids
                ),
            ));
        }

        $p_ids = Hash::extract($carts, '{n}.Cart.product_id');
        $spec_groups = array();
        $products = array();
        if (!empty($p_ids)) {
            $spec_groups = $this->ProductSpecGroup->find('all', array(
                'conditions' => array(
                    'product_id' => $p_ids
                )
            ));
            $spec_groups = Hash::combine($spec_groups, '{n}.ProductSpecGroup.id', '{n}.ProductSpecGroup.spec_names');

            $products = $this->Product->find('all', array(
                'conditions' => array(
                    'id' => $p_ids
                )
            ));
        }

        $order_carts = array();
        $product_detail = array();
        foreach ($carts as &$cart) {
            $order_id = $cart['Cart']['order_id'];
            $specId = $cart['Cart']['specId'];
            $cart['Cart']['spec_name'] = $spec_groups[$specId];
            $cart['Cart']['matched'] = in_array($cart['Cart']['id'], $cart_ids);
            if (!isset($order_carts[$order_id])) {
                $order_carts[$order_id] = array();
            }
            if($cart['Cart']['matched']){
                array_unshift($order_carts[$order_id], $cart);
                if(isset($product_detail[$cart['Cart']['product_id']])){
                    $product_detail[$cart['Cart']['product_id']] +=  $cart['Cart']['num'];
                }else{
                    $product_detail[$cart['Cart']['product_id']] =  $cart['Cart']['num'];
                }
            }
            else{
                $order_carts[$order_id][] = $cart;
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

        $ship_mark_enum = array('ziti'=>array('name'=>'自提','style'=>'active'),'sfdf'=>array('name'=>'顺丰到付','style'=>'warning'),'kuaidi'=>array('name'=>'快递','style'=>'danger'),'c2c'=>array('name'=>'c2c订单','style'=>'info'),'none'=>array('name'=>'没有标注','style'=>'info'));
        $this->set('ship_mark_enum',$ship_mark_enum);

        $ziti_orders = array_filter($orders,'ziti_order_filter');
        $sfdf_orders = array_filter($orders,'sfdf_order_filter');
        $kuaidi_orders = array_filter($orders,'kuaidi_order_filter');
        $c2c_orders = array_filter($orders,'c2c_order_filter');
        $none_orders = array_filter($orders,'none_order_filter');
        $map_other_orders = array('sfdf'=> $sfdf_orders,'kuaidi' => $kuaidi_orders,'none'=> $none_orders,'c2c'=> $c2c_orders);
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

        $offline_stores = array();
        $offline_store_ids = array_filter(array_unique(Hash::extract($ziti_orders, "{n}.Order.consignee_id")));
        if (!empty($offline_store_ids)) {
            $offline_stores = $this->OfflineStore->find('all', array(
                'conditions'=> array(
                    'id' => $offline_store_ids
                )
            ));
            $offline_stores = Hash::combine($offline_stores, "{n}.OfflineStore.id", "{n}");
        }

        $pys_ziti_point = array_filter($offline_stores,'pys_ziti_filter');
        $hlj_ziti_point = array_filter($offline_stores,'hlj_ziti_filter');

        $this->set('pys_ziti_point',$pys_ziti_point);
        $this->set('hlj_ziti_point',$hlj_ziti_point);


        $this->set('map_ziti_orders',$map_ziti_orders);
        $this->set('map_other_orders',$map_other_orders);

        $this->set('abnormal_order_count',$this->_query_abnormal_order());
        $this->set('b2c_paid_not_sent_count', $this->_query_b2c_paid_not_send_count());
        $this->set('c2c_paid_not_sent_count', $this->_query_c2c_paid_not_send_count());

        $this->set('product_count', $product_count);
        $this->set('orders', $orders);
        $this->set('tuan_buys', $tuan_buys);
        $this->set('tuan_teams', $tuan_teams);
        $this->set('offline_stores', $offline_stores);
        $this->set('order_carts', $order_carts);
        $this->set('brands', $brands);
        $this->set('product_detail', $product_detail);
        $this->set('products', $products);
        $this->set('consign_dates', $consign_dates);
    }

    public function admin_advanced_query(){
        $product_id = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : -1;
        $store_id = isset($_REQUEST['store_id']) ? $_REQUEST['store_id'] : -1;
        $cart_status = isset($_REQUEST['cart_status']) ? $_REQUEST['cart_status'] : -1;
        $send_date = $_REQUEST['send_date'];
        $end_stat_date = $_REQUEST['end_stat_date'];
        $conditions = array();
        $order_by = 'Cart.product_id, Order.consignee_id DESC';
        if($product_id != -1 || $store_id != -1 || !empty($send_date)){
            if ($cart_status != -1) {
                $conditions['Cart.status'] = $cart_status;
            }
            if($product_id != -1){
                $conditions['Cart.product_id'] = $product_id;
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
        $this->set('product_id', $product_id);
        $this->set(compact('send_date', 'end_stat_date'));
        $this->set('cart_status', $cart_status);
        $this->set('query_type', 'advancedQuery');
        $this->render("admin_tuan_orders");
    }
    public function _get_ship_types(){
        $shipTypes = ShipAddress::ship_types();
        $ship_types = Hash::combine($shipTypes, '{n}.id', '{n}.name');
        return $ship_types;
    }

    public function _query_b2c_paid_not_send_count(){
        $b2c_brand_ids = implode(",", b2c_brands());
        $b2c_paid_not_sent_count = $this->Order->query('select count(distinct c.id) as ct from cake_orders o inner join cake_carts c on c.order_id = o.id where o.brand_id in ('.$b2c_brand_ids.') and c.status = 1 and c.send_date < CURDATE()');
        return $b2c_paid_not_sent_count[0][0]['ct'];
    }

    public function _query_c2c_paid_not_send_count(){
        $b2c_brand_ids = implode(",", b2c_brands());
        $c2c_paid_not_sent_count = $this->Order->query('select count(distinct c.id) as ct from cake_orders o inner join cake_carts c on o.id = c.order_id where o.brand_id not in ('.$b2c_brand_ids.') and c.status = 1');
        return $c2c_paid_not_sent_count[0][0]['ct'];
    }

    public function _query_orders_today_count(){
        $count = $this->Order->query('select count(distinct o.id) as ct from cake_orders o inner join cake_carts c on c.order_id = o.id where DATE(o.pay_time) >= CURDATE() and c.status > 0');
        return $count[0][0]['ct'];
    }

    public function _query_abnormal_order(){
        $b2c_brand_ids = implode(",", b2c_brands());
        $abnormal_order_count = $this->Order->query('select count(distinct c.id) as ct from cake_orders o inner join cake_carts c on c.order_id = o.id where
            (
                (c.send_date is null and o.brand_id in ('.$b2c_brand_ids.'))
                or (o.consignee_id = 0 and o.consignee_address = "")
                or (o.pay_time is null and o.ship_mark != "sfdf")
                or (o.ship_mark = "")
                or (o.ship_mark is NULL)
                or (o.ship_mark = "kuaidi" and (o.consignee_address = "" or o.consignee_address is NULL))
                or (o.ship_mark = "ziti" and (o.consignee_id = 0 or o.consignee_id is null))
            ) and o.type in (1,5,6) and c.status in (1,2,3,4,14) and DATE(o.created) > "'.date('Y-m-d', strtotime('-62 days')).'"');
        return $abnormal_order_count[0][0]['ct'];
    }
    public function admin_update_order_status_to_refunded(){
        $this->autoRender = false;
        $order_id = $_REQUEST['orderId'];
        $order_status = $_REQUEST['orderStatus'];
        $this->log('status'.json_encode($order_status));
        if(!empty($order_id)){
            if($this->Order->updateAll(array('status' => $order_status),array('id' => $order_id))){
                $this->Cart->updateAll(array('status' => $order_status),array('order_id' => $order_id));
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

    public function _get_orderMsg(){
        $reachOrders = $this->OrderMessage->find('all',array('conditions' => array('status' => 0,'type' => 'py-reach')));
        $send_outOrders = $this->OrderMessage->find('all',array('conditions' => array('status' => 0,'type' => 'py-send-out')));
        $reachOrderIds = Hash::extract($reachOrders,'{n}.OrderMessage.order_id');
        $send_outOrderIds = Hash::extract($send_outOrders,'{n}.OrderMessage.order_id');
        return array(array_unique($reachOrderIds),array_unique($send_outOrderIds));
    }
    public function admin_batch_update_order_status(){
        $this->autoRender = false;
        $order_ids = $_REQUEST['orderId'];
        $success = array();
        $fail = array();
        if(!empty($order_ids)){
            foreach($order_ids as $orderId){
                $order_info = $this->Order->find('first',array('conditions' => array('id' => $orderId)));
                $ship_mark = $order_info['Order']['ship_mark'];
                $order_status = $order_info['Order']['status'];
                if(($ship_mark == 'kuaidi') && ($order_status != ORDER_STATUS_SHIPPED)){
                    if($this->Order->updateAll(array('status' => ORDER_STATUS_SHIPPED),array('id' => $orderId))){
                        $this->Cart->updateAll(array('status' => ORDER_STATUS_SHIPPED),array('order_id' => $orderId));
                        $success[] = $orderId;
                    }
                }else{
                    $fail[] = $orderId;
                }
            }
            $returnInfo  = array('success' => true,'res' => $success,'fail' => $fail);
            echo json_encode($returnInfo);
        }
    }
}