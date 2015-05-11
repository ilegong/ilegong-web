<?php
class TuanController extends AppController
{

    var $name = 'Tuan';

    var $uses = array('TuanTeam', 'TuanBuying', 'Order', 'Cart', 'TuanBuyingMessages', 'TuanProduct', 'ConsignmentDate', 'ProductTry', 'Brand', 'ProductSpecGroup', 'PayNotify', 'OfflineStore');

    /**
     * query tuan orders
     */
    public function admin_tuan_orders()
    {
        $team_id = $_REQUEST['team_id'];
        $product_id = empty($_REQUEST['product_id']) ? -1 : $_REQUEST['product_id'];
        $query_product_id = empty($_REQUEST['query_product_id']) ? -1 : $_REQUEST['query_product_id'];
        $con_address = $_REQUEST['con_address'];
        $order_type = $_REQUEST['order_type'];
        $start_stat_datetime = $_REQUEST['start_stat_datetime'];
        $end_stat_datetime = $_REQUEST['end_stat_datetime'];
        $tuan_con_date = $_REQUEST['tuan_con_date'];
        $product_con_date = $_REQUEST['product_con_date'];
        $only_tuan_order = $_REQUEST['only_tuan_order'];
        $query_tb = array();
        $should_count_nums = false;
        if (!empty($tuan_con_date)) {
            $query_tb['DATE(consign_time)'] = $tuan_con_date;
        }
        if (!empty($team_id) && $team_id != -1) {
            $query_tb['tuan_id'] = $team_id;
        }

        if (!empty($product_id) && $product_id != -1) {
            $query_tb['pid'] = $product_id;
            $should_count_nums = true;
            $this->set('should_count_nums', $should_count_nums);
        }
        if (!empty($query_tb)) {
            $tuan_buys = $this->TuanBuying->find('all', array(
                'conditions' => $query_tb
            ));

            if (!empty($tuan_buys)) {
                $p_ids = Hash::extract($tuan_buys, '{n}.TuanBuying.pid');
            }
        }
        //统计规格
        if ($query_product_id != -1) {
            $should_count_nums = true;
            $this->set('should_count_nums', $should_count_nums);
            $p_ids = array($query_product_id);
        }

        $spec_groups = $this->ProductSpecGroup->find('all', array(
            'conditions' => array(
                'product_id' => $p_ids
            )
        ));

        $spec_groups = Hash::combine($spec_groups, '{n}.ProductSpecGroup.id', '{n}.ProductSpecGroup.spec_names');

        $order_query_cond = array(
            'Order.type' => array(ORDER_TYPE_TUAN_SEC, ORDER_TYPE_TUAN, ORDER_TYPE_DEF)
        );
        //add tuan_buys member id
        if (!empty($tuan_buys)) {
            $tb_ids = Hash::extract($tuan_buys, '{n}.TuanBuying.id');
            $order_query_cond['Order.member_id'] = $tb_ids;
        }
        if ($only_tuan_order == '1') {
            $order_query_cond['Order.type'] = ORDER_TYPE_TUAN;
        }
        //??why
        $this->PayNotify->query("update cake_pay_notifies set order_id =  substring_index(substring_index(out_trade_no,'-',2),'-',-1) where status = 6 and order_id is NULL");
        $join_conditions = array(
            array(
                'table' => 'pay_notifies',
                'alias' => 'Pay',
                'conditions' => array(
                    'Pay.order_id = Order.id'
                ),
                'type' => 'LEFT',
            )
        );
        //query cons date
        if ($product_id != -1 || $query_product_id != -1) {
            $pid = -1;
            if ($product_id != -1) {
                $pid = $product_id;
            } elseif ($query_product_id != -1) {
                $pid = $query_product_id;
            }
            if (!empty($product_con_date)) {
                $conDate = $this->ConsignmentDate->find('first', array(
                    'conditions' => array(
                        'product_id' => $pid,
                        'send_date' => $product_con_date
                    )
                ));
                if (!empty($conDate)) {
                    $conDateId = $conDate['ConsignmentDate']['id'];
                    $cartOrderIds = $this->Cart->find('all', array(
                        'conditions' => array(
                            'product_id' => $pid,
                            'consignment_date' => $conDateId
                        ),
                        'fields' => array(
                            'order_id'
                        )
                    ));
                }
            } else {
                //查询产品的ID
                $cartOrderIds = $this->Cart->find('all', array(
                    'conditions' => array(
                        'product_id' => $pid
                    ),
                    'fields' => array(
                        'order_id'
                    )
                ));
            }
            if (!empty($cartOrderIds)) {
                $cartOrderIds = Hash::extract($cartOrderIds, '{n}.Cart.order_id');
                $order_query_cond['Order.id'] = $cartOrderIds;
            }
        } else {
            if (!empty($p_ids)) {
                //查询产品的ID
                $cartOrderIds = $this->Cart->find('all', array(
                    'conditions' => array(
                        'product_id' => $p_ids
                    ),
                    'fields' => array(
                        'order_id'
                    )
                ));
                if (!empty($cartOrderIds)) {
                    $cartOrderIds = Hash::extract($cartOrderIds, '{n}.Cart.order_id');
                    $order_query_cond['Order.id'] = $cartOrderIds;
                }
            }
        }

        if (empty($start_stat_datetime)) {
            $start_stat_datetime = date('Y-m-d H:i', strtotime('-7 days'));
        }
        if (empty($end_stat_datetime)) {
            $end_stat_datetime = date('Y-m-d H:i', strtotime('+1 hours'));
        }

        if (!empty($end_stat_datetime)) {
            $order_query_cond['Order.created <'] = $end_stat_datetime;
        }

        if (!empty($start_stat_datetime)) {
            $order_query_cond['Order.created >'] = $start_stat_datetime;
        }

        if (!empty($con_address)) {
            $order_query_cond['Order.consignee_address LIKE'] = '%' . $con_address . '%';
        }

        if ($order_type != -1) {
            $order_query_cond['Order.status'] = $order_type;
        } else {
            $order_query_cond['Order.status'] = array(ORDER_STATUS_CANCEL, ORDER_STATUS_WAITING_PAY);
            $orders_invalid = $this->Order->find('count', array(
                'conditions' => $order_query_cond,
            ));
            $this->set('orders_invalid', $orders_invalid);
            if ($orders_invalid > 0) {
                $r = $this->Order->find('all', array(
                    'fields' => array('sum(Order.total_all_price)   AS total'),
                    'conditions' => $order_query_cond,
                ));
                if (!empty($r)) {
                    $this->set('total_unpaid', $r[0][0]['total']);
                }
            }
            unset($order_query_cond['Order.status']);
        }

        $this->log('query orders with conditions: ' . json_encode($order_query_cond));
        $orders = $this->Order->find('all', array(
            'conditions' => $order_query_cond,
            'joins' => $join_conditions,
            'fields' => array('Order.*', 'Pay.trade_type', 'Pay.out_trade_no'),
            'order' => 'Order.consignee_address DESC'
        ));

        if (!empty($orders)) {
            $total_money = 0;
            foreach ($orders as $o) {
                $ids[] = $o['Order']['id'];
                $o_status = $o['Order']['status'];
                if ($o_status == 1 || $o_status == 2 || $o_status == 3) {
                    $total_money = $total_money + $o['Order']['total_all_price'];
                }
            }
            $this->set('total_money', $total_money);
            $order_ids = Hash::extract($orders, '{n}.Order.id');
            $carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $order_ids,
                )
            ));
            //显示规格数量
            if ($should_count_nums) {
                $order_id_strs = '(' . join(',', $order_ids) . ')';
                $product_count = $this->Cart->query('select sum(num) from cake_carts where order_id in ' . $order_id_strs);
                $this->set('product_count', $product_count[0][0]['sum(num)']);
                $product_spec_map = $this->Cart->query('select specId,sum(num) from cake_carts where order_id in ' . $order_id_strs . ' group by specId');
                $this->set('product_spec_map', $product_spec_map);
            }
            $order_carts = array();
            foreach ($carts as &$c) {
                $c_order_id = $c['Cart']['order_id'];
                $specId = $c['Cart']['specId'];
                $c['Cart']['spec_name'] = $spec_groups[$specId];
                if (!isset($order_carts[$c_order_id])) {
                    $order_carts[$c_order_id] = array();
                }
                $order_carts[$c_order_id][] = $c;
            }
            $this->set('orders', $orders);
            $this->set('order_carts', $order_carts);
            if (!empty($tuan_buys)) {
                $tuan_ids = Hash::extract($tuan_buys, '{n}.TuanBuying.tuan_id');
                $tuans = $this->TuanTeam->find('all', array(
                    'conditions' => array(
                        'id' => $tuan_ids
                    )
                ));
                $tuans = Hash::combine($tuans, '{n}.TuanTeam.id', '{n}.TuanTeam');
                $this->set('tuans', $tuans);
                $tuan_buys = Hash::combine($tuan_buys, '{n}.TuanBuying.id', '{n}.TuanBuying');
                $this->set('tuan_buys', $tuan_buys);
            }
            //排期
            $consign_ids = array_unique(Hash::extract($carts, '{n}.Cart.consignment_date'));
            if (count($consign_ids) != 1 || !empty($consign_ids[0])) {
                $consign_dates = $this->ConsignmentDate->find('all', array(
                    'conditions' => array(
                        'id' => $consign_ids
                    )
                ));
                $consign_dates = Hash::combine($consign_dates, '{n}.ConsignmentDate.id', '{n}.ConsignmentDate.send_date');
                $this->set('consign_dates', $consign_dates);
            }
        }

        $this->set('b2c_empty_date_address_count', $this->_query_b2c_empty_date_address());
        $this->set('b2c_paid_not_sent_count', $this->_query_b2c_paid_not_send_count());
        $this->set('c2c_paid_not_sent_count', $this->_query_c2c_paid_not_send_count());
        $this->set('orders_today_count', $this->_query_orders_today_count());

        $this->set('spec_groups', $spec_groups);
        $this->set('team_id', $team_id);
        $this->set('product_id', $product_id);
        $this->set('start_stat_datetime', $start_stat_datetime);
        $this->set('end_stat_datetime', $end_stat_datetime);
        $this->set('order_type', $order_type);
        $this->set('con_address', $con_address);
        $this->set('tuan_con_date', $tuan_con_date);
        $this->set('product_con_date', $product_con_date);
        $this->set('query_product_id', $query_product_id);
        $this->set('only_tuan_order', $only_tuan_order);
        $this->set('query_type', 'general');
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
        $product_id = $_REQUEST['product_id'];
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
        $conditions = array("Order.pay_time < CURDATE()");
        $conditions['Order.type'] = ORDER_TYPE_DEF;
        $conditions['Order.status'] = ORDER_STATUS_PAID;
        $this->_query_orders($conditions, 'Order.updated');

        $this->set('query_type', 'c2cPaidNotSend');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_orders_today()
    {
        $conditions = array("Order.pay_time >= CURDATE()");
        $this->_query_orders($conditions, 'Order.updated');

        $this->set('query_type', 'ordersToday');
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

        $orders = array();
        if (!empty($conditions)) {
            $params = array(
                'conditions' => $conditions,
                'joins' => $join_conditions,
                'fields' => array('Order.*', 'Pay.trade_type', 'Pay.out_trade_no', 'Cart.product_id', 'Cart.try_id', 'Cart.send_date'),
                'order' => $order_by
            );
            if (!empty($limit)) {
                $params['limit'] = $limit;
            }
            $this->log('query order conditions: ' . json_encode($params));
            $orders = $this->Order->find('all', $params);
        } else {
            $this->log('order condition is empty: ' . json_encode($conditions));
        }

        $order_ids = Hash::extract($orders, "{n}.Order.id");

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

        $conditions['Order.type'] = ORDER_TYPE_DEF;
        $conditions['Order.status'] = ORDER_STATUS_PAID;
        $conditions['DATE(Order.updated) <'] = date('Y-m-d H:i:s');

        $this->set('b2c_empty_date_address_count', $this->_query_b2c_empty_date_address());
        $this->set('b2c_paid_not_sent_count', $this->_query_b2c_paid_not_send_count());
        $this->set('c2c_paid_not_sent_count', $this->_query_c2c_paid_not_send_count());
        $this->set('orders_today_count', $this->_query_orders_today_count());

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
        $empty_send_date_count = $this->Order->query('select count(distinct o.id) as ct from cake_orders o inner join cake_carts c on c.order_id = o.id where o.pay_time > CURDATE()');
        return $empty_send_date_count[0][0]['ct'];
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
}