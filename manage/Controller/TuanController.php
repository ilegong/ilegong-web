<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 3/19/15
 * Time: 20:26
 */

class TuanController extends AppController
{

    var $name = 'Tuan';

    var $uses = array('TuanTeam', 'TuanBuying', 'Order', 'Cart', 'TuanBuyingMessages', 'TuanProduct', 'ConsignmentDate', 'ProductTry', 'Brand', 'ProductSpecGroup', 'PayNotify');

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
            'fields' => array('Order.*', 'Pay.trade_type'),
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

    public function admin_query_by_order_id()
    {
        $order_id = $_REQUEST['order_id'];

        $conditions = array();
        if (!empty($order_id)) {
            $conditions['Order.id'] = $order_id;
        }

        $this->_query_orders($conditions, 'Order.consignee_address DESC');

        $this->set('order_id', $order_id);
        $this->set('query_type', 'byOrderId');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_by_user()
    {
        $con_name = $_REQUEST['con_name'];
        $con_phone = $_REQUEST['con_phone'];
        $order_status = $_REQUEST['order_status'];

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

        $this->_query_orders($conditions, 'Order.created DESC');

        $this->set('con_name', $con_name);
        $this->set('con_phone', $con_phone);
        $this->set('order_status', $order_status);
        $this->set('query_type', 'byUser');
        $this->render("admin_tuan_orders");
    }

    public function admin_query_by_product()
    {
        $product_id = $_REQUEST['product_id'];
        $order_status = $_REQUEST['order_status'];
        $order_type = $_REQUEST['order_type'];
        $send_date_start = $_REQUEST['send_date_start'];
        $send_date_end = $_REQUEST['send_date_end'];

        $conditions = array();
        if (!empty($product_id) && $product_id != -1) {
            $conditions['Cart.product_id'] = $product_id;
            if ($order_type == -1) {
                $conditions['Order.type'] = array(ORDER_TYPE_DEF, ORDER_TYPE_TUAN, ORDER_TYPE_TUAN_SEC);
            }
            else{
                $conditions['Order.type'] = $order_type;
            }
            if ($order_status != -1) {
                $conditions['Order.status'] = $order_status;
            }
            if (empty($send_date_start)) {
                $send_date_start = date('Y-m-d', strtotime('-2 days'));
            }
            if (empty($send_date_end)) {
                $send_date_end = date('Y-m-d', strtotime('+5 days'));
            }
            $conditions['DATE(Cart.send_date) >= '] = $send_date_start;
            $conditions['DATE(Cart.send_date) <= '] = $send_date_end;
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
        $team_id = $_REQUEST['team_id'];
        $order_status = $_REQUEST['order_status'];
        $send_date_start = $_REQUEST['send_date_start'];
        $send_date_end = $_REQUEST['send_date_end'];

        $conditions = array();
        if (!empty($team_id) && $team_id != -1) {
            $tuan_buyings = $this->TuanBuying->find('all', array(
               'conditions' => array(
                   'tuan_id' => $team_id
               ),
                'fields' => array('id')
            ));
            $conditions['Order.type'] = ORDER_TYPE_TUAN;
            $conditions['Order.member_id'] = Hash::extract($tuan_buyings, "{n}.TuanBuying.id");
            if ($order_status != -1) {
                $conditions['Order.status'] = $order_status;
            }
            if (empty($send_date_start)) {
                $send_date_start = date('Y-m-d', strtotime('-2 days'));
            }
            if (empty($send_date_end)) {
                $send_date_end = date('Y-m-d', strtotime('+5 days'));
            }
            $conditions['DATE(Cart.send_date) >= '] = $send_date_start;
            $conditions['DATE(Cart.send_date) <= '] = $send_date_end;
        }

        $this->_query_orders($conditions, 'Order.created DESC');

        $this->set('team_id', $team_id);
        $this->set('send_date_start', $send_date_start);
        $this->set('send_date_end', $send_date_end);
        $this->set('order_status', $order_status);
        $this->set('query_type', 'byTuanTeam');
        $this->render("admin_tuan_orders");
    }

    /**
     * 团购功能列表
     */
    public function admin_tuan_func_list()
    {
        $tuan_team_count = $this->TuanTeam->query('select count(*) as c from cake_tuan_teams');
        $this->set('tuan_team_count', $tuan_team_count[0][0]['c']);
        $tuan_product_count = $this->TuanProduct->query('select count(*) as c from cake_tuan_products where deleted = 0');
        $this->set('tuan_product_count', $tuan_product_count[0][0]['c']);
        $seckill_product_count = $this->ProductTry->query('select count(*) as c from cake_product_tries where deleted = 0');
        $this->set('seckill_product_count', $seckill_product_count[0][0]['c']);
        $brand_count = $this->Brand->query('select count(*) as c from cake_brands where deleted = 0');
        $this->set('brand_count', $brand_count[0][0]['c']);
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

    public function _query_orders($conditions, $order_by)
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
                'type' => 'LEFT'
            )
        );
        $this->log('query order conditions: ' . json_encode($conditions));

        $orders = array();
        if (!empty($conditions)) {
            $orders = $this->Order->find('all', array(
                'conditions' => $conditions,
                'joins' => $join_conditions,
                'fields' => array('Order.*', 'Pay.trade_type', 'Cart.product_id', 'Cart.try_id', 'Cart.send_date'),
                'order' => $order_by
            ));
        }
        $order_ids = Hash::extract($orders, "{n}.Order.id");
        $this->log('order ids: ' . json_encode($order_ids));

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
        $this->log('tuan buyings: ' . json_encode($tuan_buys));

        $p_ids = Hash::extract($tuan_buys, '{n}.TuanBuying.pid');
        $spec_groups = array();
        if (!empty($p_ids)) {
            $spec_groups = $this->ProductSpecGroup->find('all', array(
                'conditions' => array(
                    'product_id' => $p_ids
                )
            ));
            $spec_groups = Hash::combine($spec_groups, '{n}.ProductSpecGroup.id', '{n}.ProductSpecGroup.spec_names');
        }

        $carts = array();
        if (!empty($order_ids)) {
            $this->log('will query carts: ' . json_encode($order_ids));
            $carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $order_ids
                ),
            ));
        }
        $this->log('carts: ' . json_encode($carts));

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
        if(!empty($order_ids)){
            $order_id_strs = '(' . join(',', $order_ids) . ')';
            $result = $this->Cart->query('select sum(num) from cake_carts where order_id in ' . $order_id_strs);
            $product_count =  $result[0][0]['sum(num)'];
        }

        $this->set('should_count_nums', true);
        $this->set('product_count', $product_count);
        $this->set('orders', $orders);
        $this->set('tuan_buys', $tuan_buys);
        $this->set('order_carts', $order_carts);
        $this->set('consign_dates', $consign_dates);
        return $c;
    }
}