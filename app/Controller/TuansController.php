<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 15/3/7
 * Time: 下午4:37
 */

class TuansController extends AppController{

    public function lists($pid=null){
        $this->pageTitle = '团购列表';
        $tuan_info = $this->Tuan->find('all');
        $this->set('tuans_info', $tuan_info);
        $this->loadModel('TuanProduct');
        $tuan_product_num = $this->TuanProduct->query("select sum(sold_num) as sold_number from cake_tuan_products  where pid = $pid");
        $tuan_product_nums = $this->TuanProduct->find('all',array('conditions' => array('pid' => $pid)));
        $this->log('num'.json_encode($tuan_product_nums));
        $this->set('tuan_product_num',$tuan_product_num[0][0]['sold_number']);
    }

    public function detail($teamId=null){
        $this->autoRender = true;
        $this->pageTitle = '草莓团购';
        $teamInfo = $this->Tuan->find('first',array('conditions' => array('id' => $teamId)));
//        $this->loadModel('Product');
//        $proInfo = $this->Product->find('first',array('conditions' => array('id' => 838)));
//        $this->set('Product',$proInfo);
        $this->set('teamId',$teamId);
        $this->set('sold_num',$teamInfo['Tuan']['sold_num']);
        $this->set('tuan_name',$teamInfo['Tuan']['tuan_name']);
        $this->set('tuan_leader_name',$teamInfo['Tuan']['leader_name']);
        $this->set('tuan_leader_weixin',$teamInfo['Tuan']['leader_weixin']);
        $this->set('tuan_address',$teamInfo['Tuan']['address']);
        $this->set('hideNav',true);
        if($this->is_weixin()){
            $currUid = empty($this->currentUser) ? 0 : $this->currentUser['id'];
            $pid = $teamInfo['Tuan']['pid'];
            $this->prepare_wx_sharing($currUid, $pid);
        }
    }

    protected function prepare_wx_sharing($currUid, $pid) {
        $currUid = empty($currUid) ? 0 : $currUid;
        $share_string = $currUid . '-' . time() . '-rebate-pid_' . $pid;
        $share_code = authcode($share_string, 'ENCODE', 'SHARE_TID');
        $oauthM = ClassRegistry::init('WxOauth');
        $signPackage = $oauthM->getSignPackage();
        $this->set('signPackage', $signPackage);
        $this->set('share_string', urlencode($share_code));
        $this->set('jWeixinOn', true);
    }

    public function lbs_map($teamId=''){
        $this->pageTitle =__('草莓自取点');
        $teamInfo = $this->Tuan->find('first',array('conditions' => array('id' => $teamId)));
        $this->set('teamId',$teamId);
        $this->set('name',$teamInfo['Tuan']['tuan_name']);
        $this->set('location_1',$teamInfo['Tuan']['tuan_location_1']);
        $this->set('location_2',$teamInfo['Tuan']['tuan_location_2']);
        $this->set('addr',$teamInfo['Tuan']['tuan_addr']);
        $this->log('teamInfo'.json_encode($teamInfo));
        $this->set('hideNav',true);
    }

    public function cart_info(){
        $this->autoRender = false;
        $this->loadModel('Cart');
        $product_id = intval($_REQUEST['product_id']);
        $product_num = intval($_REQUEST['product_num']);
        $uId = $this->currentUser['id'];
        $cartInfo = $this->Cart->add_to_cart($product_id,$product_num,0,5,0,$uId);
        $this->log('cartInfo'.json_encode($cartInfo));
        if($cartInfo){
            echo json_encode(array('success' => true));
        }else{
            echo json_encode(array('error' => false));
        }

    }

    public function join($pid, $tuan_id){
        $this->pageTitle = '订单确认';
        if(empty($this->currentUser['id'])){
            $this->redirect('/users/login?referer=' . urlencode($_SERVER['REQUEST_URI']));
            return;
        }
        $this->loadModel('Cart');
        $this->loadModel('Tuan');
        $tuan_info = $this->Tuan->findById($tuan_id);
        $this->Cart->find('first');
        $uid =$this->currentUser['id'];
        $cond = array(
            'status' => CART_ITEM_STATUS_NEW,
            'order_id' => null,
            'num > 0',
            'product_id' => $pid,
            'type' => CART_ITEM_TYPE_TUAN,
            'creator' => $uid
        );
        $Carts = $this->Cart->find('first', array(
            'conditions' => $cond));
        $total_price = $Carts['Cart']['price'] * $Carts['Cart']['num'];
        $this->set('buy_count',$Carts['Cart']['num']);
        $this->set('total_price', $total_price);
        $this->set('cart_id', $Carts['Cart']['id']);
        $this->set('tuan_id', $tuan_id);
        $this->set('tuan_address', $tuan_info['Tuan']['address']);
    }

    public function tuan_pay($orderId){
        $this->pageTitle = '团购';
        $this->loadModel('Order');
        $this->loadModel('Cart');
        $order_info = $this->Order->find('first', array(
            'conditions' =>array('id' => $orderId),
            'fields' => array('total_all_price', 'created', 'id', 'consignee_address', 'consignee_name')
        ));
        $cart_info = $this->Cart->find('first', array(
            'conditions' =>array('order_id' => $orderId),
            'fields' => array('num', 'price')
        ));
        $this->set('orderId', $orderId);
        $this->set('order', $order_info);
        $this->set('cart', $cart_info );
    }

    public function pre_order() {
        $this->autoRender = false;
        $cart_id = $_POST['cart_id'];
        $tuan_id = $_POST['tuan_id'];
        $mobile = $_POST['mobile'];
        $name = $_POST['name'];
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $this->log("not login for tuan order:".$cart_id);
            echo json_encode(array('success'=> false));
            return;
        }
        if(empty($cart_id)){
            $this->log("tuan cart id error:".$cart_id);
            echo json_encode(array('success'=> false));
            return;
        }
        $this->loadModel('Cart');
        $this->loadModel('Order');
        $this->loadModel('Tuan');
        $cart_info = $this->Cart->findById($cart_id);
        $creator = $cart_info['Cart']['creator'];
        $order_type = $cart_info['Cart']['type'];
        if($creator != $uid){
            $this->log("no right to this order, uid".$uid. "creator:".$creator);
            $res = array('success'=> false, 'info'=> '团购订单不属于你，请刷新重试');
        }elseif($order_type != CART_ITEM_TYPE_TUAN){
            $res = array('success'=> false, 'info'=> '该订单不属于团购订单，请重试');
        }else{
            if(!empty($cart_info['Cart']['order_id'])){
                $this->log("cart order id error,cart id".$cart_id);
                return;
            }
            $total_price = $cart_info['Cart']['num'] * $cart_info['Cart']['price'];
            if($total_price < 0 ){
                $this->log("error tuan price, cart id".$cart_id);
                return;
            }
            $pid = $cart_info['Cart']['product_id'];
            $area = '';
            $tuan_info = $this->Tuan->findById($tuan_id);
            $address = $tuan_info['Tuan']['address'];
            $order = $this->Order->createTuanOrder($tuan_id, $uid, $total_price, $pid, $order_type, $area, $address, $mobile, $name, $cart_id);
            if ($order['Order']['status'] != ORDER_STATUS_WAITING_PAY) {
                $res = array('success'=> false, 'info'=> '你已经支付过了');
            }else{
                $res = array('success'=> true, 'order_id'=>$order['Order']['id']);
            }
        }
        echo json_encode($res);
    }


    public function new_tuan(){
        $this->pageTitle = '创建新团';
    }

    public function mei_shi_tuan(){
        $this->pageTitle = '美食团';
        $tuan_info = $this->Tuan->find('all');
        $this->set('tuans_info', $tuan_info);
    }

    public function join_meishituan(){
        $this->pageTitle = '加入美食团';
    }
}

