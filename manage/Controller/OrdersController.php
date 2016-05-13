<?php

class OrdersController extends AppController {

    var $name = 'Orders';
    public $components = array('Weixin');

    public function admin_order_remark() {

    }

    public function admin_view($id) {
        $this->loadModel('Cart');
        $carts = $this->Cart->find('all', array(
            'conditions' => array('order_id' => $id),
        ));
        $this->set('carts', $carts);
        parent::admin_view($id);
    }

    public function admin_edit($id = null, $copy = NULL) {
        parent::admin_edit($id, $copy);

        $this->log('edit order: ' . json_encode($this->data));

        $username = $this->currentUser['username'];
        $user_agent = $this->request->header('User-Agent');
        $user_ip = $this->request->clientIp(true);
        $this->log('admin user edit order' . $id . ' admin user is ' . $username . ' request ip ' . $user_ip . ' user_agent ' . $user_agent);
    }

    public function admin_edit2($id = null, $copy = NULL) {
        $this->loadModel('Order');
        $this->loadModel('Cart');

        $order = $this->Order->findById($id);
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $id
            )
        ));

        $this->set('ship_types', Hash::combine(ShipAddress::ship_types(), '{n}.id', '{n}.name'));
        $this->set('order', $order);
        $this->set('carts', $carts);
    }

    public function admin_update2($id) {
        $this->autoRender = false;

        $username = $this->currentUser['username'];
        $user_agent = $this->request->header('User-Agent');
        $user_ip = $this->request->clientIp(true);
        $this->log('user ' . $username . ' is to update order ' . $id . ', request ip ' . $user_ip . ', user_agent ' . $user_agent);

        $order = $this->Order->findById($id);
        if (empty($order)) {
            echo json_encode(array('success' => false, 'reason' => 'order_not_exists'));
            return;
        }

        // 检查权限
        if (!has_permission_to_modify_order($this->data['modify_user'])) {
            echo json_encode(array('success' => false, 'reason' => 'no_permission'));
            return;
        }

        $modify_user = $this->data['modify_user'];
        unset($this->data['send_date']);
        unset($this->data['modify_user']);

        // 必须修改至少一个字段
        if (empty($this->data)) {
            echo json_encode(array('success' => false, 'reason' => 'fields_are_empty'));
            return;
        }

        $new_order_status = $this->data['status'];

        if (!empty($this->data['ship_mark'])) {
            if ($this->data['ship_mark'] == 'ziti') {
                // 修改为自提，需有自提点
                if (empty($this->data['consignee_id']) || $this->data['consignee_id'] == 0) {
                    echo json_encode(array('success' => false, 'reason' => 'missed_consignee_id'));
                    return;
                }
                $this->data['consignee_area'] = null;
                $this->loadModel('OfflineStore');
                $offlineStore = $this->OfflineStore->find('first', array(
                    'conditions' => array(
                        'id' => $this->data['consignee_id'],
                    )
                ));
                if (!empty($offlineStore)) {
                    $this->data['consignee_address'] = get_address(null, $offlineStore);
                }
            } else {
                $this->data['consignee_id'] = 0;
            }
        }

        // 添加备注，而不是直接修改
        if (!empty($this->data['remark'])) {
            $remark = (empty($order['Order']['remark']) ? '' : $order['Order']['remark'] . ', ') . $this->data['remark'] . '(' . $modify_user . ')';
            $this->data['remark'] = $remark;
        }
        foreach ($this->data as $key => $value) {
            $this->data[$key] = "'" . $value . "'";
        }

        $this->log('update order ' . $id . ': ' . json_encode($this->data));
        if (!$this->Order->updateAll($this->data, array('id' => $id))) {
            echo json_encode(array('success' => false, 'reason' => 'failed_to_save_order'));
            return;
        }

        echo json_encode(array('success' => true, 'message_sent' => false));
    }

    public function admin_trash($ids) {
        if (is_array($_POST['ids']) && !empty($_POST['ids'])) {
            $ids = $_POST['ids'];
        } else {
            if (!$ids) {
                $this->redirect(array('action' => 'index'));
            }
            $ids = explode(',', $ids);
        }
        $error_flag = false;
        $this->loadModel('Cart');
        foreach ($ids as $id) {
            if (!intval($id))
                continue;
            $data = array();
            $data['deleted'] = 1;
            $this->Order->updateAll($data, array('id' => $id));
            //同时标记删除订单的商品
            $this->Cart->updateAll(array('deleted' => 1), array('order_id' => $id));
        }

        $successinfo = array('success' => __('Trash success'));

        $this->set('successinfo', $successinfo);
        $this->set('_serialize', 'successinfo');

        if ($error_flag) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 删除数据
     * @param $id
     */
    function admin_delete($ids = null) {
        @set_time_limit(0);
        if (is_array($_POST['ids']) && !empty($_POST['ids'])) {
            $ids = $_POST['ids'];
        } else {
            if (!$ids) {
                $this->redirect(array('action' => 'index'));
            }
            $ids = explode(',', $ids);
        }
        $istree = false;
        $delete_flag = $this->Order->deleteAll(array('id' => $ids, 'deleted' => 1), true, true);
        //删除相关的订单商品
        $this->loadModel('Cart');
        $this->Cart->deleteAll(array('order_id' => $ids, 'deleted' => 1), true, true);

        if ($delete_flag) {
            $successinfo = array('success' => __('Delete success'));
        } else {
            $successinfo = array('error' => __('Delete error'));
        }
        $this->set('successinfo', $successinfo);
        $this->set('_serialize', 'successinfo');

        if ($delete_flag) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 恢复删除标记
     * @param $id
     */
    function admin_restore($ids = null) {
        if (is_array($_POST['ids']) && !empty($_POST['ids'])) {
            $ids = $_POST['ids'];
        } else {
            if (!$ids) {
                $this->redirect(array('action' => 'index'));
            }
            $ids = explode(',', $ids);
        }
        $this->loadModel('Cart');
        foreach ($ids as $id) {
            if (!intval($id))
                continue;
            $data = array();
            $data['id'] = $id;
            $data['deleted'] = 0;

            if ($this->Order->save($data)) {
                $this->Cart->updateAll(array('deleted' => 0), array('order_id' => $id));
                $successinfo = array('success' => __('Restore success'));
            } else {
                $successinfo = array('error' => __('Restore error'));
            }
        }
        $this->set('successinfo', $successinfo);
        $this->set('_serialize', 'successinfo');
    }


    protected function _custom_list_option(&$searchoptions) {
        $filterType = $_REQUEST['filterType'];
        $filter = $_REQUEST['filter'];
        if ($filterType) {
            switch ($filterType) {
                case 'tag_id':
                    $tagId = intval($filter);
                    if ($tagId) {
                        if ($searchoptions['conditions']) {
                            $searchoptions['conditions']['Tag.tag_id'] = $tagId;
                        } else {
                            $searchoptions['conditions'] = array(
                                'Tag.tag_id' => $tagId
                            );
                        }
                        $searchoptions['joins'][] = array(
                            'table' => 'product_product_tags',
                            'alias' => 'Tag',
                            'type' => 'left',
                            'conditions' => array('Product.id=Tag.product_id'),
                        );
                        $this->set('filter_string', "Product.Tagid=" . $tagId);
                    }
                    break;
                case 'brand_id':
                    $brand_id = intval($filter);
                    if ($brand_id > 0) {
                        if ($searchoptions['conditions']) {
                            $searchoptions['conditions']['Order.brand_id'] = $brand_id;
                        } else {
                            $searchoptions['conditions'] = array(
                                'Order.brand_id' => $brand_id
                            );
                        }
//                        $searchoptions['joins'][] = array(
//                            'table' => 'products',
//                            'alias' => 'Product',
//                            'type' => 'left',
//                            'conditions' => array('Product.id=Order.brand_id'),
//                        );
                        $this->set('filter_string', "Product.BrandId=" . $brand_id);
                    }
            }
        }
        return $searchoptions;
    }


    public function admin_list_today() {

        $sent_by_our_self = array(
            35, //电科院-刘炎炎
            413, //腾讯-nancyqi
            41, //小牛村-王成宝
            92, //朋友说
            116, //中国移动-黄亮
            88, //阿里巴巴—李瑞
            36, //你好植物-张小伟
            72, //蓝靛果-王野
            34, //去哪儿-荣浩
        );

        $start_date = $this->get_day_start($_REQUEST['start_date']);
        $end_date = $this->get_day_end($_REQUEST['end_date']);
        $brand_id = empty($_REQUEST['brand_id']) ? 0 : $_REQUEST['brand_id'];

        if (empty($brand_id)) {
            if ($_REQUEST['sent_by_us'] == 1) {
                $brand_id = $sent_by_our_self;
            }
        }

        $order_status = !isset($_REQUEST['order_status']) ? -1 : $_REQUEST['order_status'];
        $order_id = !isset($_REQUEST['order_id']) ? "" : $_REQUEST['order_id'];
        $consignee_name = !isset($_REQUEST['consignee_name']) ? "" : $_REQUEST['consignee_name'];
        $consignee_mobilephone = !isset($_REQUEST['consignee_mobilephone']) ? "" : $_REQUEST['consignee_mobilephone'];

        $product_scheduling_date = $_REQUEST['product_scheduling_date'];
        $product_id = $_REQUEST['product_id'];


        $this->loadModel('Brand');
        $this->loadModel('ConsignmentDate');
        $this->loadModel('Cart');
        $c_date = $this->ConsignmentDate->find('first', array(
            'conditions' => array(
                'product_id' => $product_id,
                'send_date' => $product_scheduling_date
            )
        ));

        $brands = $this->Brand->find('all', array(
            'conditions' => array(
                'deleted != 1',
                'published = 1'
            ),
            'order' => 'id desc'
        ));

        $this->loadModel('Order');
        $conditions = array(
            'Order.created >"' . date("Y-m-d\TH:i:s", $start_date) . '"',
            'Order.created <"' . date("Y-m-d\TH:i:s", $end_date) . '"',
            'Order.type' => array(ORDER_TYPE_DEF, ORDER_TYPE_GROUP_FILL, ORDER_TYPE_TUAN, ORDER_TYPE_TUAN_SEC)
        );

        if (!empty($product_scheduling_date) && !empty($product_id)) {
            if (!empty($c_date)) {
                $c_date_id = $c_date['ConsignmentDate']['id'];
                $carts = $this->Cart->find('all', array(
                    'conditions' => array(
                        'consignment_date' => $c_date_id,
                        'product_id' => $product_id
                    ),
                    'fields' => array(
                        'order_id'
                    )
                ));
                $order_ids = Hash::extract($carts, '{n}.Cart.order_id');
                if (!empty($order_ids)) {
                    $conditions['Order.id'] = $order_ids;
                }
            }
        }

        if ($_REQUEST['search_groupon'] === "1" && $_REQUEST['consignee_mobilephone']) {
            $mobile_num = intval($_REQUEST['consignee_mobilephone']);
            $this->loadModel('Groupon');
            $groupons = $this->Groupon->find('all', array(
                'conditions' => array('mobile' => $mobile_num)
            ));
            if ($groupons) {
                $groupon_lists = Hash::extract($groupons, '{n}.Groupon.id');
                $organizer_ids = Hash::extract($groupons, '{n}.Groupon.user_id');
            } else {
                //团购订单查询不到
                echo '<span style="font-size: large;color: #ff0000">&#x56E2;&#x8D2D;&#x8BA2;&#x5355;&#x67E5;&#x8BE2;&#x4E0D;&#x5230;</span>';
                return;
            }
            $this->loadModel('GrouponMember');
            $groupon_members = $this->GrouponMember->find('all', array(
                'conditions' => array('groupon_id' => $groupon_lists, 'status' => 1),
                'fields' => array('id', 'groupon_id')
            ));
            $groupon_member_lists = Hash::extract($groupon_members, '{n}.GrouponMember.id');
            $order_groupon_link = Hash::combine($groupon_members, '{n}.GrouponMember.id', '{n}.GrouponMember.groupon_id');
            if ($groupon_member_lists) {
                $conditions[] = array('Order.member_id' => $groupon_member_lists);
                $conditions['Order.type'] = array(ORDER_TYPE_GROUP, ORDER_TYPE_GROUP_FILL, ORDER_TYPE_TUAN, ORDER_TYPE_TUAN_SEC);
                $consignee_mobilephone = null;
            } else {
                //团购订单暂时无人参团
                echo '<span style="font-size: large;color: #ff0000">&#x56E2;&#x8D2D;&#x8BA2;&#x5355;&#x65E0;&#x4EBA;&#x53C2;&#x56E2;</span>';
                return;
            }
            $find_order_conditions = array('member_id' => $groupon_member_lists, 'creator' => $organizer_ids, 'status !=' => array(ORDER_STATUS_PAID, ORDER_STATUS_WAITING_PAY));
            if ($this->Order->hasAny($find_order_conditions)) {
                //团购订单已发货或已收货
                echo '<span style="font-size: large;color: #ff0000">&#x56E2;&#x8D2D;&#x8BA2;&#x5355;&#x5DF2;&#x53D1;&#x8D27;&#x6216;&#x5DF2;&#x6536;&#x8D27;</span>';
                return;
            }
            $this->set('order_groupon_link', $order_groupon_link);
        }

        if ($brand_id != 0) {
            $conditions['Order.brand_id'] = $brand_id;
        }
        if ($order_status != -1) {
            array_push($conditions, 'Order.status = ' . $order_status);
        }
        if (!empty($order_id)) {
            array_push($conditions, 'Order.id = ' . $order_id);
        }
        if (!empty($consignee_name)) {
            array_push($conditions, 'Order.consignee_name like "%' . $consignee_name . '%"');
        }
        if (!empty($consignee_mobilephone)) {
            array_push($conditions, 'Order.consignee_mobilephone = ' . $consignee_mobilephone);
        }

        $payNotifyModel = ClassRegistry::init('PayNotify');
        $payNotifyModel->query("update cake_pay_notifies set order_id =  substring_index(substring_index(out_trade_no,'-',2),'-',-1) where status = 6");
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

        $orders = $this->Order->find('all', array(
            'order' => 'id desc',
            'conditions' => $conditions,
            'joins' => $join_conditions,
            'fields' => array('Order.*', 'Pay.trade_type'),
        ));

        if ($order_status == -1) {
            $conditions['Order.status'] = array(ORDER_STATUS_CANCEL, ORDER_STATUS_WAITING_PAY);
            $orders_invalid = $this->Order->find('count', array(
                'conditions' => $conditions,
            ));
            $this->set('orders_invalid', $orders_invalid);
            if ($orders_invalid > 0) {
                $r = $this->Order->find('all', array(
                    'fields' => array('sum(Order.total_all_price)   AS total'),
                    'conditions' => $conditions,
                ));
                if (!empty($r)) {
                    $this->set('total_unpaid', $r[0][0]['total']);
                }
            }
        }

        $ids = array();
        $total_money = 0;
        foreach ($orders as $o) {
            $ids[] = $o['Order']['id'];
            $o_status = $o['Order']['status'];
            if ($o_status == 1 || $o_status == 2 || $o_status == 3) {
                $total_money = $total_money + $o['Order']['total_all_price'];
            }
        }
        $carts = array();
        $this->log('order ids ' . json_encode($ids));
        if (!empty($ids)) {
            $carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $ids,
                )));
        }
        $order_carts = array();
        foreach ($carts as $c) {
            $c_order_id = $c['Cart']['order_id'];
            if (!isset($order_carts[$c_order_id])) {
                $order_carts[$c_order_id] = array();
            }
            $order_carts[$c_order_id][] = $c;
        }
        //规格
        $spec_ids = array_unique(Hash::extract($carts, '{n}.Cart.specId'));
        if (count($spec_ids) != 1 || !empty($spec_ids[0])) {
            $this->loadModel('ProductSpecGroup');
            $spec_groups = $this->ProductSpecGroup->find('all', array(
                'conditions' => array(
                    'id' => $spec_ids
                )
            ));
            $spec_groups = Hash::combine($spec_groups, '{n}.ProductSpecGroup.id', '{n}.ProductSpecGroup.spec_names');
            $this->set('spec_groups', $spec_groups);
        }
        //排期
        $consign_ids = array_unique(Hash::extract($carts, '{n}.Cart.consignment_date'));
        if (count($consign_ids) != 1 || !empty($consign_ids[0])) {
            $this->loadModel('ConsignmentDate');
            $consign_dates = $this->ConsignmentDate->find('all', array(
                'conditions' => array(
                    'id' => $consign_ids
                )
            ));
            $consign_dates = Hash::combine($consign_dates, '{n}.ConsignmentDate.id', '{n}.ConsignmentDate.send_date');
            $this->set('consign_dates', $consign_dates);
        }
        $tuan_ids = array();
        foreach ($orders as $order) {
            if ($order['Order']['type'] == ORDER_TYPE_TUAN) {
                if (!in_array($order['Order']['member_id'], $tuan_ids)) {
                    $tuan_ids[] = $order['Order']['member_id'];
                }
            }
        }
        if (!empty($tuan_ids)) {
            $this->loadModel('TuanBuying');
            $tuan_consign_times = $this->TuanBuying->find('list', array(
                'conditions' => array('id' => $tuan_ids),
                'fields' => array('id', 'consign_time')
            ));
            $this->set('tuan_consign_times', $tuan_consign_times);
        }
        $this->set('orders', $orders);
        $this->set('total_money', $total_money);
        $this->set('order_carts', $order_carts);
        $this->set('ship_type', $this->ship_type);
        $this->set('brands', $brands);
        $this->set('start_date', date("Y-m-d", $start_date));
        $this->set('end_date', date("Y-m-d", $end_date));
        $this->set('brand_id', $brand_id);
        $this->set('order_status', $order_status);
        $this->set('order_id', $order_id);
        $this->set('consignee_name', $consignee_name);
        $this->set('consignee_mobilephone', $consignee_mobilephone);
        $this->set('product_scheduling_date', $product_scheduling_date);
        $this->set('product_id', $product_id);
    }

    private function get_day_start($start_day = '') {
        return $this->get_day_time($start_day);
    }

    private function get_day_end($end_day = '') {
        return $this->get_day_time($end_day, 23, 59, 59);
    }

    private function get_day_time($day = '', $hour = 0, $minute = 0, $second = 0) {
        if (!empty($day)) {
            $start_date = date_parse_from_format("Y-m-d", $day);
            $y = $start_date["year"];
            $m = $start_date["month"];
            $d = $start_date["day"];
        } else {
            $y = date("Y");
            $m = date("m");
            $d = date("d");
        }
        return mktime($hour, $minute, $second, $m, $d, $y);
    }

    var $ship_type = array(
        101 => '申通',
        102 => '圆通',
        103 => '韵达',
        104 => '顺丰',
        105 => 'EMS',
        106 => '邮政包裹',
        107 => '天天',
        108 => '汇通',
        109 => '中通',
        110 => '全一',
        111 => '宅急送'
    );

    public function admin_get_share_refund_log($order_id) {
        $this->autoRender = false;
        $this->loadModel('RefundLog');
        $refundLog = $this->RefundLog->find('first', array(
            'conditions' => array(
                'order_id' => $order_id
            )
        ));
        echo json_encode($refundLog['RefundLog']);
        return;
    }

    public function admin_process_order_added_refund($orderId) {
        $this->autoRender = false;
        $this->loadModel('User');
        $this->loadModel('Weshare');
        $this->loadModel('RefundLog');
        $this->loadModel('PayLog');
        $this->loadModel('Order');
        $orderInfo = $this->Order->find('first', array(
            'conditions' => array(
                'parent_order_id' => $orderId,
                'status' => ORDER_STATUS_REFUND
            )
        ));
        if ($orderInfo) {
            $showRefundMoney = abs($orderInfo['Order']['total_all_price']);
            $PayLogInfo = $this->PayLog->find('first', array(
                'conditions' => array(
                    'order_id' => $orderId,
                    'status' => 2,
                    'type' => GOOD_ORDER_PAY_TYPE
                )
            ));
            $trade_type = $PayLogInfo['PayLog']['trade_type'];
            if (empty($trade_type)) {
                $trade_type = 'JSAPI';
            }
            $refundMoney = intval($showRefundMoney * 100);
            $saveRefundLogData = array(
                'order_id' => $orderId,
                'refund_fee' => $refundMoney,
                'created' => date('Y-m-d H:i:s'),
                'trade_type' => $trade_type,
                'remark' => '尾款退款',
                'type' => REFUND_ADD_ORDER_TYPE
            );
            $this->RefundLog->save($saveRefundLogData);
            $weshareId = $orderInfo['Order']['member_id'];
            //refund processing
            $weshareInfo = $this->Weshare->find('first', array(
                'conditions' => array('id' => $weshareId)
            ));
            $order_creator_id = $orderInfo['Order']['creator'];
            $order_creator_info = $this->User->find('first', array(
                'conditions' => array(
                    'User.id' => $order_creator_id
                ),
                'recursive' => 0, //int
                'fields' => array('User.id', 'User.nickname')
            ));
            $weshareTitle = $weshareInfo['Weshare']['title'];
            $remark = '点击查看详情';
            $detail_url = WX_HOST . '/weshares/view/' . $weshareId;
            $this->Order->updateAll(array('status' => ORDER_STATUS_REFUND_DONE), array('id' => $orderInfo['Order']['id']));
            $this->Order->updateAll(array('process_prepaid_status' => ORDER_STATUS_REFUND_DONE), array('id' => $orderId));
            $title = $order_creator_info['User']['nickname'] . '，你好，我们已经为你退款，会在3-5个工作日内到账，请注意查收。';
            $this->Weixin->send_refund_order_notify($order_creator_id, $title, $weshareTitle, $showRefundMoney, $detail_url, $orderId, $remark);
            //refund complete
            //clean cache share
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId . '_1_1', '');
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId . '_0_1', '');
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId . '_1_0', '');
            Cache::write(SHARE_ORDER_DATA_CACHE_KEY . '_' . $weshareId . '_0_0', '');
            echo json_encode(array('success' => true));
            return;
        }
        echo json_encode(array('success' => false, 'reason' => 'no_order'));
        return;
    }

    /**
     * @param $share_id
     * @param $order_creator
     * @param $order_id
     * 退款后每单5元自提费用减去
     */
    public function remove_money_for_offline_address($share_id, $order_creator, $order_id) {
        $WeshareM = ClassRegistry::init('Weshare');
        $weshare = $WeshareM->find('first', array(
            'conditions' => array(
                'id' => $share_id,
                'type' => SHARE_TYPE_GROUP
            )
        ));
        if (!empty($weshare)) {
            //update is paid
            $share_creator = $weshare['Weshare']['creator'];
            $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
            $rebateTrackLogM->updateAll(array('is_paid' => 0), array('sharer' => $share_creator, 'share_id' => $share_id, 'clicker' => $order_creator, 'order_id' => $order_id, 'is_paid' => 1, 'type' => GROUP_SHARE_BUY_REBATE_TYPE));
        }
    }

    public function admin_process_share_refund() {
        $this->autoRender = false;
        $this->loadModel('User');
        $this->loadModel('Weshare');
        $this->loadModel('RefundLog');
        $this->loadModel('PayLog');
        $this->loadModel('Order');
        $orderId = $_REQUEST['orderId'];
        $refundMoney = $_REQUEST['refundMoney'];
        $refundMoney = intval($refundMoney * 1000 / 10);
        App::uses('CakeNumber', 'Utility');
        $showRefundMoney = CakeNumber::precision($refundMoney / 100, 2);
        $refundMark = $_REQUEST['refundMark'];
        $refundStatus = $_REQUEST['refundStatus'];
        $refundLog = $this->RefundLog->find('first', array(
            'conditions' => array(
                'order_id' => $orderId
            )
        ));
        $orderInfo = $this->Order->find('first', array(
            'conditions' => array('id' => $orderId)
        ));
        $weshareId = $orderInfo['Order']['member_id'];
        if (empty($refundLog)) {
            $PayLogInfo = $this->PayLog->find('first', array(
                'conditions' => array(
                    'order_id' => $orderId,
                    'status' => 2,
                    'type' => GOOD_ORDER_PAY_TYPE
                )
            ));
            $trade_type = $PayLogInfo['PayLog']['trade_type'];
            if (empty($trade_type)) {
                $trade_type = 'JSAPI';
            }
            $saveRefundLogData = array(
                'order_id' => $orderId,
                'refund_fee' => $refundMoney,
                'created' => date('Y-m-d H:i:s'),
                'trade_type' => $trade_type,
                'remark' => $refundMark,
                'data_id' => $weshareId
            );
            $this->RefundLog->save($saveRefundLogData);
        } else {
            $refundLogId = $refundLog['RefundLog']['id'];
            $this->RefundLog->updateAll(array('refund_fee' => $refundMoney, 'remark' => "'" . $refundMark . "'"), array('id' => $refundLogId));
        }
        //refund processing
        $weshareInfo = $this->Weshare->find('first', array(
            'conditions' => array('id' => $weshareId)
        ));
        $order_creator_id = $orderInfo['Order']['creator'];
        $order_creator_info = $this->User->find('first', array(
            'conditions' => array(
                'User.id' => $order_creator_id
            ),
            'recursive' => 0, //int
            'fields' => array('User.id', 'User.nickname')
        ));
        $weshareTitle = $weshareInfo['Weshare']['title'];
        $remark = '点击查看详情';
        $detail_url = WX_HOST . '/weshares/view/' . $weshareId;
        if ($refundStatus == 0) {
            $this->Order->updateAll(array('status' => ORDER_STATUS_RETURNING_MONEY), array('id' => $orderId));
            $title = $order_creator_info['User']['nickname'] . '，你好，我们已经为你申请退款，会在3-5个工作日内完成退款。';
            $this->Weixin->send_refunding_order_notify($order_creator_id, $title, $weshareTitle, $showRefundMoney, $detail_url, $orderId, $remark);
            //如果是拼团订单 退款减去余额
            if ($orderInfo['Order']['ship_mark'] == SHARE_SHIP_GROUP_TAG) {
                $this->remove_money_for_offline_address($weshareId, $order_creator_id, $orderId);
            }
        }
        //refund complete
        if ($refundStatus == 1) {
            $this->Order->updateAll(array('status' => ORDER_STATUS_RETURN_MONEY), array('id' => $orderId));
            $title = $order_creator_info['User']['nickname'] . '，你好，我们已经为你退款，会在3个工作日内到账，请注意查收。';
            $this->Weixin->send_refund_order_notify($order_creator_id, $title, $weshareTitle, $showRefundMoney, $detail_url, $orderId, $remark);
        }
        echo json_encode(array('success' => true));
        return;
    }


    public function admin_send_refund_notify() {
        $this->autoRender = false;
        $this->loadModel('User');
        $this->loadModel('Cart');
        $orderId = $_REQUEST['orderId'];
        $refundMoney = $_REQUEST['refundMoney'];
        $refundMark = $_REQUEST['refundMark'];
        $creator = $_REQUEST['creator'];
        $orderStatus = $_REQUEST['orderStatus'];
        $orderScores = $_REQUEST['orderScores'];
        $orderTotalALlPrice = $_REQUEST['orderTotalAllPrice'];
        $userInfo = $this->User->find('first', array('conditions' => array('id' => $creator)));
        $cartInfo = $this->Cart->find('all', array('conditions' => array('order_id' => $orderId)));
        $this->loadModel('RefundLog');
        $this->loadModel('PayLog');

        $PayLogInfo = $this->PayLog->find('first', array(
            'conditions' => array(
                'order_id' => $orderId,
                'status' => 2,
                'type' => GOOD_ORDER_PAY_TYPE
            )
        ));
        $product_name = null;
        foreach ($cartInfo as $cartinfo) {
            $product_name = $product_name . $cartinfo['Cart']['name'] . '*' . $cartinfo['Cart']['num'] . '  ';
        }
        if ($PayLogInfo['PayLog']['trade_type'] == 'JSAPI') {
            $trade_type = '微信支付';
        } else if ($PayLogInfo['PayLog']['trade_type'] == 'ZFB') {
            $trade_type = '支付宝';
        } else {
            $trade_type = '支付';
        }
        $title = '亲，您有一笔' . $refundMoney . '元的退款已经退至您的' . $trade_type . '账户，预计3-15个工作日到账，请您及时查看';
        $phone = $userInfo['User']['mobilephone'];
        $msg = $title;
        $detail_url = WX_HOST . '/orders/detail/' . $orderId;
        $remark = '点击查看订单，如有问题，请联系客服!';
        if ($orderStatus == 4) {
            if ($refundMoney == $orderTotalALlPrice) {
                if ($this->_send_refund_scores($userInfo['User']['id'], $orderScores, $orderId)) {
                    $score_msg = '积分变动通知已发出';
                } else {
                    $score_msg = '积分变动通知发送失败';
                }
            } else {
                $score_msg = '不发送积分变动通知';
            }
            if (message_send($msg, $phone)) {
                $flag_1 = true;
            } else {
                $flag_1 = false;
            }
            if ($this->Weixin->send_refund_order_notify($creator, $title, $product_name, $refundMoney, $detail_url, $orderId, $remark)) {
                $flag_2 = true;
            } else {
                $flag_2 = false;
            }
            if ($flag_1 || $flag_2) {
                $data['RefundLog']['order_id'] = $orderId;
                $data['RefundLog']['refund_fee'] = intval(intval($refundMoney) * 1000 / 10);
                $data['RefundLog']['trade_type'] = $PayLogInfo['PayLog']['trade_type'];
                $data['RefundLog']['remark'] = '已退款:' . $refundMark;
                $this->RefundLog->save($data);
                $returnInfo = array('success' => true, 'msg' => '退款通知发送成功  ' . $score_msg);
            } else {
                $returnInfo = array('success' => false, 'msg' => '退款通知发送失败，请重试  ' . $score_msg);
            }
            echo json_encode($returnInfo);
        } else {
            $data['RefundLog']['order_id'] = $orderId;
            $data['RefundLog']['refund_fee'] = intval(intval($refundMoney) * 1000 / 10);
            $data['RefundLog']['trade_type'] = $PayLogInfo['PayLog']['trade_type'];
            $data['RefundLog']['remark'] = '退款中:' . $refundMark;
            if ($this->RefundLog->save($data)) {
                $returnInfo = array('success' => true, 'msg' => '退款记录保存成功');
            } else {
                $returnInfo = array('success' => false, 'msg' => '退款记录保存失败');
            }
            echo json_encode($returnInfo);
        }
    }

    public function  admin_compute_refund_money() {
        $this->autoRender = false;
        $orderId = $_REQUEST['orderId'];
        $this->loadModel('RefundLog');
        $refund_money = $this->RefundLog->query('select sum(refund_fee) as refund_money from cake_refund_logs where order_id =' . $orderId . ' and remark like "%已退款%"');
        $refund_money = $refund_money[0][0]['refund_money'];
        echo json_encode($refund_money / 100);
    }

    public function admin_get_refund_log($order_id, $total_price) {
//        $total_price = $_REQUEST['total_price'];
        $this->loadModel('RefundLog');
        $RefundLogInfo = $this->RefundLog->find('all', array(
            'conditions' => array('order_id' => $order_id)
        ));
        $refund_money = $this->RefundLog->query('select sum(refund_fee) as refund_money from cake_refund_logs where order_id =' . $order_id . ' and remark like "%已退款%"');
        $refund_money = $refund_money[0][0]['refund_money'] / 100;
        $this->set('refund_money', $refund_money);
        $this->set('RefundInfo', $RefundLogInfo);
        $this->set('total_price', $total_price);
    }

    /*
     * refund scores and send_message
     */
    private function _send_refund_scores($userId, $scores, $orderId) {
        $this->loadModel('Score');
        $rtn = $this->Score->refund_user_scores($userId, $scores, $orderId);
        if (!empty($rtn)) {
            $userM = ClassRegistry::init('User');
            $userM->add_score($userId, $rtn['Score']['score']);
            return true;
        } else return false;
    }

    private function _insert_pay_notifies($order) {
        $data = array();

        $time = date('YmdHis');
        $data['PayNotify']['out_trade_no'] = 'OFFLINE-' . $time;
        $data['PayNotify']['transaction_id'] = -1;
        $data['PayNotify']['trade_type'] = 'OFFLINE';
        $data['PayNotify']['bank_type'] = '';
        $data['PayNotify']['total_fee'] = $order['Order']['total_all_price'];
        $data['PayNotify']['openid'] = '';
        $data['PayNotify']['time_end'] = $time;
        $data['PayNotify']['status'] = 6;
        $data['PayNotify']['order_id'] = $order['Order']['id'];

        $this->loadModel('PayNotify');
        if ($this->PayNotify->save($data)) {
            return $this->Order->getLastInsertID();
        } else {
            $this->log($this->Order->validationErrors);
        }
    }

    private function _on_order_paid($order) {
        $this->Order->updateAll(array('pay_time' => "'" . date("Y-m-d H:i:s") . "'"), array('id' => $order['Order']['id']));
        $this->_insert_pay_notifies($order);
    }

    private function _on_order_returning_money($order) {

    }

    private function _on_order_return_money($order) {

    }

    private function _on_order_shipped($order, $data) {
        $this->loadModel('Oauthbind');
        $oauth_bind = $this->Oauthbind->find('first', array(
            'conditions' => array('user_id' => $order['Order']['creator'], 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));
        if (empty($oauth_bind)) {
            return false;
        }
        if (empty($data['ship_type']) || !empty($data['ship_code'])) {
            return false;
        }

        $good = $this->_get_order_good_info($order['Order']['id']);
        $ship_type_list = ShipAddress::ship_type_list();
        $post_data = array(
            "touser" => $oauth_bind['Oauthbind']['oauth_openid'],
            "template_id" => '87uu4CmlZT-xlZGO45T_XTHiFYAWHQaLv94iGuH-Ke4',
            "url" => WX_HOST . '/orders/detail/' . $order['Order']['id'],
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => "亲，您的特产已经从家乡启程啦。"),
                "keyword1" => array("value" => $ship_type_list[$order['Order']['ship_type']]),
                "keyword2" => array("value" => $order['Order']['ship_code']),
                "keyword3" => array("value" => $good['good_info']),
                "keyword4" => array("value" => $good['good_number']),
                "remark" => array("value" => "点击查看订单详情。", "color" => "#FF8800")
            )
        );
        if (send_weixin_message($post_data)) {
            return true;
        } else {
            $this->log("ship code B2C: failed to send weixin message for order " . $order['Order']['id']);
            return false;
        }
    }

    private function _get_order_good_info($order_id) {
        $info = '';
        $number = 0;
        $carts = $this->_get_carts_on_one_order($order_id);
        foreach ($carts as $cart) {
            $info = $info . $cart['Cart']['name'] . ':' . $cart['Cart']['num'] . '件、';
            $number += $cart['Cart']['num'];
        }

        $info = substr($info, 0, strlen($info) - 2);
        return array("good_info" => $info, "good_number" => $number);
    }

    public function _get_carts_on_one_order($order_id) {
        $this->loadModel('Cart');
        $carts = $this->Cart->find('all', array(
            'conditions' => array('order_id' => $order_id)));
        return $carts;
    }

    public function admin_carts_edit($order_id) {
        $carts = $this->_get_carts_on_one_order($order_id);
        $this->set('carts', $carts);
    }
}