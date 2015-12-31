<?php

/**
 * 拼团
 * User: shichaopeng
 * Date: 12/29/15
 * Time: 09:24
 */
class PintuanController extends AppController {

    var $name = 'pintuan';

    var $uses = array('OrderConsignee', 'User');

    var $components = array('PintuanHelper');

    public function beforeFilter() {
        parent::beforeFilter();
        //暂时不使用angular
        $this->layout = null;
    }

    /**
     * @param $share_id
     * 拼团的详情
     */
    public function detail($share_id) {
        $uid = $this->currentUser['id'];
        $conf = $this->get_pintuan_conf($share_id);
        $tag_id = $_REQUEST['tag_id'];
        $wx_title = $conf['wx_title'];
        if (!empty($tag_id)) {
            $this->set('tag_id', $tag_id);
            $tag = $this->get_pintuan_tag($tag_id);
            $this->set('tag', $tag);
            if ($tag['PintuanTag']['status'] == PIN_TUAN_TAG_PROGRESS_STATUS && $tag['PintuanTag']['creator'] == $uid) {
                if ($tag['PintuanTag']['creator'] == $uid) {
                    $wx_title = '我报名了“[和你一起立省5元] 越南红心火龙果 4个49元”，就差你一个啦  !';
                } else {
                    $user_nickname = $this->User->findNicknamesOfUid($tag['PintuanTag']['creator']);
                    $wx_title = $user_nickname . '报名了“[和你一起立省5元] 越南红心火龙果 4个49元”，就差你一个啦  !';
                }
            }
        }
        $records = $this->PintuanHelper->get_pintuan_records($tag_id);
        $order_count = count($records);
        $this->set('order_count', $order_count);
        $this->set('uid', $uid);
        $this->set('share_id', $share_id);
        $this->set('conf', $conf);
        $this->set('records', $records);
        $this->set_share_weixin_params($uid, $wx_title, $conf['banner_img'], $conf['wx_desc']);
    }

    /**
     * 拼团的规则
     */
    public function rule() {

    }

    /**
     * @param $type 0=>normal 1=>start 2=>join
     * 用户下单
     */
    public function make_order($type) {
        $this->autoRender = false;
        $share_id = $_REQUEST['share_id'];
        $pintuan_conf = $this->get_pintuan_conf($share_id);
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        if (empty($pintuan_conf)) {
            echo json_encode(array('success' => false, 'reason' => 'param_error'));
            return;
        }
        $price = $pintuan_conf['product']['normal_price'];
        $tag_id = 0;
        $now_date = date('Y-m-d H:i:s');
        //join
        if ($type == 2) {
            $tag_id = $_REQUEST['tag_id'];
            if (empty($tag_id)) {
                echo json_encode(array('success' => false, 'reason' => 'param_error'));
                return;
            }
            $tag = $this->get_pintuan_tag($tag_id);
            //valid tag
            if ($tag['PintuanTag']['status'] == PIN_TUAN_TAG_EXPIRE_STATUS) {
                echo json_encode(array('success' => false, 'reason' => 'tag_error'));
                return;
            }
            $price = $pintuan_conf['product']['pintuan_price'];
        }
        //new
        if ($type == 1) {
            $price = $pintuan_conf['product']['pintuan_price'];
            //init tag??
            $tag_id = $this->new_pintuan_tag($uid, $now_date, $share_id);
            if (empty($tag_id)) {
                echo json_encode(array('success' => false, 'reason' => 'system_error'));
                return;
            }
        }
        $order_data = $this->init_order_data($uid);
        $order_data['creator'] = $uid;
        $order_data['created'] = $now_date;
        $order_data['updated'] = $now_date;
        $order_data['total_all_price'] = $price;
        $order_data['total_price'] = $price;
        $order_data['group_id'] = $tag_id; //pin tuan group id
        $order_data['member_id'] = $share_id;
        $orderM = ClassRegistry::init('Order');
        $order = $orderM->save($order_data);
        $cart_data = array('order_id' => $order['Order']['id'], 'name' => $pintuan_conf['product']['name'], 'num' => 1, 'price' => $price * 100, 'type' => ORDER_TYPE_PIN_TUAN, 'product_id' => $pintuan_conf['product']['id'], 'created' => $now_date, 'updated' => $now_date, 'tuan_buy_id' => $share_id);
        $cartM = ClassRegistry::init('Cart');
        $cart = $cartM->save($cart_data);
        if ($order && $cart) {
            $order_id = $order['Order']['id'];
            if ($tag_id) {
                //save pin tuan record
                $record = array('tag_id' => $tag_id, 'order_id' => $order_id, 'user_id' => $uid, 'created' => $now_date);
                $this->PintuanHelper->save_pintuan_record($record);
            }
            echo json_encode(array('success' => true, 'order_id' => $order_id));
            return;
        }
        echo json_encode(array('success' => false, 'reason' => 'system_error'));
        return;
    }

    /**
     * @param $type
     * @param $orderId
     * 支付拼团的订单
     */
    public function pay($type, $orderId) {
        if ($type == 0) {
            $this->redirect('/wxPay/jsApiPay/' . $orderId . '?from=pintuan');
            return;
        }
        if ($type == 1) {
            $this->redirect('/ali_pay/wap_to_alipay/' . $orderId . '?from=pintuan');
            return;
        }
    }

    /**
     * @param $uid
     * @param $date
     * @param $share_id
     * @return mixed
     * 生成新的拼团标示
     */
    private function new_pintuan_tag($uid, $date, $share_id) {
        $pinTuanTagM = ClassRegistry::init('PintuanTag');
        $tag_data = array('creator' => $uid, 'created' => $date, 'share_id' => $share_id, 'num' => 2);
        $tag = $pinTuanTagM->save($tag_data);
        return $tag['PintuanTag']['id'];
    }

    /**
     * @param $uid
     * @return array
     * 初始化订单数据
     */
    private function init_order_data($uid) {
        $consignee_address = $_REQUEST['consignee_address'];
        $consignee_mobilephone = $_REQUEST['consignee_mobilephone'];
        $business_remark = $_REQUEST['business_remark'];
        $consignee_name = $_REQUEST['consignee_name'];
        $order_data = array('consignee_address' => $consignee_address, 'type' => ORDER_TYPE_PIN_TUAN, 'consignee_name' => $consignee_name, 'consignee_mobilephone' => $consignee_mobilephone, 'business_remark' => $business_remark, 'ship_mark' => SHARE_SHIP_KUAIDI_TAG);
        $this->setPintuanConsignees($consignee_name, $consignee_mobilephone, $consignee_address, $uid);
        return $order_data;
    }

    /**
     * @param $share_id 分享的id
     * 结算页面
     */
    public function balance($share_id) {
        $uid = $this->currentUser['id'];
        $tag_id = $_REQUEST['tag_id'];
        $conf = $this->get_pintuan_conf($share_id);
        $price = $conf['product']['normal_price'];
        if (empty($tag_id)) {
            //没有拼团的tag
            $start_pintuan = $_REQUEST['create'];
            if (!empty($start_pintuan)) {
                //发起拼团
                $this->set('start', 1);
                $price = $conf['product']['pintuan_price'];
            } else {
                //原价购买
                $this->set('normal', 1);
            }
        } else {
            //加入拼团
            $this->set('tag_id', $tag_id);
            $price = $conf['product']['pintuan_price'];
        }
        $consignee = $this->getPintuanConsignees($uid);
        $this->set('consignee', $consignee);
        $this->set('price', $price);
        $this->set('conf', $conf);
        $this->set('share_id', $share_id);
    }

    private function get_pintuan_tag($tag_id) {
        return $this->PintuanHelper->check_and_return_pintuan_tag($tag_id);
    }

    private function get_pintuan_conf($share_id) {
        $pintuanConfigM = ClassRegistry::init('PintuanConfig');
        $conf = $pintuanConfigM->get_conf_data($share_id);
        return $conf;
    }

    /**
     * @param $uid
     * @param $weshareId
     * @return array|null
     * 把微信分享的一些参数设置好
     */
    public function set_weixin_share_data($uid, $weshareId) {
        if (parent::is_weixin()) {
            $weixinJs = prepare_wx_share_log($uid, 'wsid', $weshareId);
            return $weixinJs;
        }
        return null;
    }

    /**
     * @param $uid
     * @param $title
     * @param $image
     * @param $desc
     */
    private function set_share_weixin_params($uid, $title, $image, $desc) {
        if (parent::is_weixin()) {
            $wexin_params = $this->set_weixin_share_data($uid, -1);
            $this->set($wexin_params);
            $this->set('title', $title);
            $this->set('image', $image);
            $this->set('desc', $desc);
        }
    }


    /**
     * @param $userInfo
     * @param $mobileNum
     * @param $address
     * @param $uid
     * 记住用户填写的地址
     */
    private function setPintuanConsignees($userInfo, $mobileNum, $address, $uid) {
        $consignee = $this->OrderConsignee->find('first', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => STATUS_CONSIGNEES_PINTUAN
            ),
            'fields' => array('id', 'name', 'mobilephone', 'address')
        ));
        if (!empty($consignee)) {
            //update
            $saveData = array('name' => "'" . $userInfo . "'", 'mobilephone' => "'" . $mobileNum . "'", 'address' => "'" . $address . "'");
            $this->OrderConsignee->updateAll($saveData, array('id' => $consignee['OrderConsignees']['id']));
            return;
        }
        //save
        $this->OrderConsignee->save(array('creator' => $uid, 'status' => STATUS_CONSIGNEES_PINTUAN, 'name' => $userInfo, 'mobilephone' => $mobileNum, 'address' => $address));
    }

    /**
     * @param $uid
     * @return mixed
     * 获取拼团的地址
     */
    private function getPintuanConsignees($uid) {
        $consignee = $this->OrderConsignee->find('first', array(
            'conditions' => array(
                'creator' => $uid,
                'status' => STATUS_CONSIGNEES_PINTUAN
            ),
            'fields' => array('id', 'name', 'mobilephone', 'address')
        ));
        return $consignee;
    }

}