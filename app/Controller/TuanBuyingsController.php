<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 15/3/16
 * Time: 下午6:31
 */
class TuanBuyingsController extends AppController{


    public $components = array('ProductSpecGroup');

    public function detail($tuan_buy_id){
        $this->pageTitle = '团购详情';
        $this->loadModel('TuanTeam');
        if($tuan_buy_id == null){
            $this->redirect('/tuan_teams/mei_shi_tuan');
            return;
        }else{
            $tuan_b = $this->TuanBuying->find('first',array(
                'conditions' => array('id' => $tuan_buy_id)
            ));
        }
        if(empty($tuan_b)){
            $this->__message('该团购不存在', '/tuan_teams/mei_shi_tuan');
            return;
        }
        $tuan_team = $this->TuanTeam->find('first',array('conditions' => array('id' => $tuan_b['TuanBuying']['tuan_id'])));
        if(empty($tuan_team)){
            $this->__message('该团不存在', '/tuan_teams/mei_shi_tuan');
        }
        $current_time = time();
        if(strtotime($tuan_b['TuanBuying']['end_time']) < $current_time){
            $this->set('exceed_time', true);
            $tuan_new_b = $this->TuanBuying->find('first',array(
                'conditions' => array('tuan_id' => $tuan_b['TuanBuying']['tuan_id'], 'status' => 0),
                'order' => array('TuanBuying.end_time DESC')
            ));
            if($tuan_new_b['TuanBuying']['id'] != $tuan_buy_id && strtotime($tuan_new_b['TuanBuying']['end_time']) > $current_time){
                $this->set('new_tuan_buy', $tuan_new_b['TuanBuying']['id']);
            }
        }
        $pid=$tuan_b['TuanBuying']['pid'];
        $consign_time = empty($tuan_b['TuanBuying']['consign_time'])? '成团后发货' : friendlyDateFromStr($tuan_b['TuanBuying']['consign_time'], FFDATE_CH_MD);
        $this->set(compact('pid', 'consign_time', 'tuan_buy_id', 'tuan_team'));
        $sold_num = $tuan_b['TuanBuying']['sold_num'];
        $max_num = $tuan_b['TuanBuying']['max_num'];
        $per_buy_num = $tuan_b['TuanBuying']['limit_buy_num'];
        if($per_buy_num>0){
            $this->set('is_limit_num',true);
            //TODO limit buy num
            $this->set('limit_num',$per_buy_num);
        }
        if($max_num>0){
            if($sold_num>=$max_num){
                $this->set('is_limit',true);
            }
        }
        $this->set('sold_num',$sold_num);
        $this->set('tuan_id',$tuan_b['TuanBuying']['tuan_id']);
        $target_num = max($tuan_b['TuanBuying']['target_num'], 1);
        $this->set('target_num', $target_num);
        $this->set('hideNav',true);
        if($this->is_weixin()){
            $currUid = empty($this->currentUser) ? 0 : $this->currentUser['id'];
            $this->prepare_wx_sharing($currUid, $pid);
        }
        $this->loadModel('Product');
        $this->loadModel('Uploadfile');
        $Product = $this->Product->find('first',array('conditions' => array('id' => $pid,'deleted' => DELETED_NO)));
        //get specs from database
        $product_spec_group = $this->ProductSpecGroup->extract_spec_group_map($pid,'spec_names');
        $this->set('product_spec_group',json_encode($product_spec_group));
        //product spec
        $specs_map = $this->ProductSpecGroup->get_product_spec_json($pid);
        if (!empty($specs_map['map'])) {
            $str = '<script>var _p_spec_m = {';
            foreach($specs_map['map'] as $mid => $mvalue) {
                $str .= '"'.$mvalue.'":"'. $mid ."\",";
            }
            $str .= '};</script>';
            $this->set('product_spec_map', $str);
        }
        $this->set('specs_map', $specs_map);
        //评论数量
        if($this->RequestHandler->isMobile()){
            $this->loadModel('Comment');
            $comment_count = intval($Product['Product']['comment_nums']);
            $this->set('comment_count',$comment_count);
        }
        $con = array('modelclass' => 'Product','fieldname' =>'photo','data_id' => $pid);
        $Product['Uploadfile']= $this->Uploadfile->find('all',array('conditions' => $con,'fields' => array('mid_thumb'),'order'=> array('sortorder DESC')));
        $tuan_price = $tuan_b['TuanBuying']['tuan_price'];
        if($tuan_price > 0){
            $this->set('tuan_price',$tuan_price);
        }
        $this->set('Product', $Product);
        $this->set('category_control_name', 'products');
        $this->set('current_data_id', $pid);
        if($this->RequestHandler->isMobile()){
//            $comment_count = intval($Product['Product']['comment_nums']);
//            $this->set('comment_count',$comment_count);
            $this->set('limitCommentCount',COMMENT_LIMIT_IN_PRODUCT_VIEW);
        }
        $recommC = $this->Components->load('ProductRecom');
        $recommends = $recommC->recommend($pid);
        $this->set('items', $recommends);
        if($tuan_team['TuanTeam']['type'] == 1){
            $this->set('big_tuan', true);
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

    public function cart_info(){
        $this->autoRender = false;
        $this->loadModel('Cart');
        $tuan_buy_id = intval($_REQUEST['tuan_buy_id']);
        $product_id = intval($_REQUEST['product_id']);
        $product_num = intval($_REQUEST['product_num']);
        $spec_id = intval($_REQUEST['spec_id']);
        $uId = $this->currentUser['id'];
        $cartInfo = $this->Cart->add_to_cart($product_id,$product_num,$spec_id,ORDER_TYPE_TUAN,0,$uId,null,  null, null,$tuan_buy_id);
        $this->log('cartInfo'.json_encode($cartInfo));
        if($cartInfo){
            if($_POST['way_type'] == 'ziti'){
                echo json_encode(array('success' => true, 'direct'=>'big_tuan_list'));
            }else{
                echo json_encode(array('success' => true, 'direct'=>'normal'));
            }
        }else{
            echo json_encode(array('error' => false));
        }

    }

    public function balance($tuan_buy_id){
        $this->pageTitle = '订单确认';
        if(empty($this->currentUser['id'])){
            $this->redirect('/users/login?referer=' . urlencode($_SERVER['REQUEST_URI']));
            return;
        }
        $tuan_b = $this->TuanBuying->find('first', array(
            'conditions' => array('id' => $tuan_buy_id),
            'fields' => array('pid', 'tuan_id', 'status', 'end_time')
        ));
        $current_time = strtotime($tuan_b['TuanBuying']['end_time']);
        $sold_num = $tuan_b['TuanBuying']['sold_num'];
        $max_num = $tuan_b['TuanBuying']['max_num'];
        if($max_num>0){
            if($sold_num>=$max_num){
                $message = '该团购已经结束。';
                $url = '/';
                $this->__message($message, $url);
                return;
            }
        }
        if(empty($tuan_b) && $current_time < time()){
            $message = '该团购已到截止时间';
            $url = '/tuan_teams/mei_shi_tuan';
            $this->__message($message, $url);
            return;
        }
        $this->loadModel('TuanTeam');
        $tuan_info = $this->TuanTeam->findById($tuan_b['TuanBuying']['tuan_id']);
        if(empty($tuan_info)){
            $this->__message('该团不存在', '/tuan_teams/mei_shi_tuan');
            return;
        }
        $this->loadModel('Cart');
        //$this->Cart->find('first');
        $uid =$this->currentUser['id'];
        $user_condition = array(
            'session_id'=>	$this->Session->id(),
            'creator' => $uid
        );
        $cond = array(
            'status' => CART_ITEM_STATUS_NEW,
            'order_id' => null,
            'num > 0',
            'product_id' => $tuan_b['TuanBuying']['pid'],
            'type' => CART_ITEM_TYPE_TUAN,
            'OR' => $user_condition
        );
        $Carts = $this->Cart->find('first', array(
            'conditions' => $cond,
            'order' => 'id DESC'
        ));
        $total_price = $Carts['Cart']['price'] * $Carts['Cart']['num'];
        $this->set('buy_count',$Carts['Cart']['num']);
        $this->set('total_price', $total_price);
        $this->set('cart_id', $Carts['Cart']['id']);
        $this->set('tuan_id', $tuan_info['TuanTeam']['id']);
        $this->set('tuan_address', $tuan_info['TuanTeam']['tuan_addr']);
        $this->set('end_time', date('m-d', $current_time));
        $this->set('tuan_buy_id', $tuan_buy_id);
        $this->set('cart_info',$Carts);
        $this->set('max_num',$max_num);
        if($tuan_info['TuanTeam']['type'] == 1){
            $this->set('big_tuan', true);
        }
    }

    public function tuan_pay($orderId){
        $this->pageTitle = '团购';
        $this->loadModel('Order');
        $this->loadModel('Cart');
        $order_info = $this->Order->find('first', array(
            'conditions' =>array('id' => $orderId),
            'fields' => array('total_all_price', 'created', 'id', 'consignee_address', 'consignee_name', 'member_id')
        ));
        $cart_info = $this->Cart->find('first', array(
            'conditions' =>array('order_id' => $orderId),
            'fields' => array('num', 'price')
        ));
        $this->set('orderId', $orderId);
        $this->set('order', $order_info);
        $this->set('cart', $cart_info );
        if($_GET['tuan_id']){
            $this->set('tuan_id',$_GET['tuan_id'] );
        }
    }

    public function pre_order() {
        $this->autoRender = false;
        $cart_id = $_POST['cart_id'];
        $tuan_id = $_POST['tuan_id'];
        $tuan_buy_id = $_POST['tuan_buy_id'];
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
        $this->loadModel('TuanTeam');
        $cart_info = $this->Cart->findById($cart_id);
        $creator = $cart_info['Cart']['creator'];
        $order_type = $cart_info['Cart']['type'];
        if(empty($cart_info)){
            $this->log("cart record not exist". $cart_id);
            $res = array('success'=> false, 'info'=> '购物车记录为查询到');
        }elseif($creator != $uid){
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
            $tuan_info = $this->TuanTeam->findById($tuan_id);
            if(empty($tuan_info)){
                $this->log("can't find tuan".$tuan_id);
                return;
            }
            if($tuan_info['TuanTeam']['type'] == 1){
                $address = $_POST['address'];
            }else{
                $address = $tuan_info['TuanTeam']['address'];
            }
            $order = $this->Order->createTuanOrder($tuan_buy_id, $uid, $total_price, $pid, $order_type, $area, $address, $mobile, $name, $cart_id);
            if ($order['Order']['status'] != ORDER_STATUS_WAITING_PAY) {
                $res = array('success'=> false, 'info'=> '你已经支付过了');
            }else{
                $tuanBuy = $this->TuanBuying->find('first', array(
                    'conditions' => array('id' => $tuan_buy_id),
                    'fields' => array('consign_time')
                ));
                $consign_time = friendlyDateFromStr($tuanBuy['TuanBuying']['consign_time'], FFDATE_CH_MD);
                $cart_name = $cart_info['Cart']['name'] . ' 送货'. $consign_time;
                if($tuan_info['TuanTeam']['type'] == 1 && $_POST['way'] == 'sf'){
                    $cart_name = $cart_name.'(顺丰到付)';
                }
                $this->Cart->update(array('name' => '\'' . $cart_name . '\'' ), array('id' => $cart_id));
                $res = array('success'=> true, 'order_id'=>$order['Order']['id']);
            }
        }
        echo json_encode($res);
    }
    function product_detail($pid){
        if(empty($pid)){
            return;
        }
//        if($_GET['tuan_id'] && $_GET['tuan_buy_id'] && $_GET['pid']){
//            $url = '/tuan_buyings/detail/'. strval($_GET['tuan_id']) . '/' . strval($_GET['tuan_buy_id']) ;
//        }else{
//            $url = '/tuan_teams/lists';
//        }
        $fields = array('id','slug','name','content','created');
        $this->loadModel('Product');
        $Product =$this->Product->find('first', array(
            'conditions' => array('id' => $pid),
            'fields' => $fields
        ));
//        $this->set('tuan_url', $url);
        $this->pageTitle = mb_substr($Product['Product']['name'],0,13);
        $this->set('Product',$Product);
        $this->set('hideNav',true);
    }
    public function goods(){
        $this->pageTitle = '团购商品';
        $tuan_products = $this->TuanBuying->find('all',array('conditions' => array("pid != " => 863),'group' => array('pid')));
        $tuan_product_ids = Hash::extract($tuan_products,'{n}.TuanBuying.pid');
        $this->loadModel('Product');
        $tuan_products_info = array();
        foreach($tuan_product_ids as $pid){
            $tuan_products_info[$pid] = $this->TuanBuying->query("select sum(sold_num) as sold_number from cake_tuan_buyings  where pid = $pid");
            $tuan_product = $this->Product->find('first',array('conditions' => array('id' => $pid),'fields' => array('deleted','name')));
            $tuan_products_info[$pid]['status'] = $tuan_product['Product']['deleted'];
            $tuan_products_info[$pid]['name'] = $tuan_product['Product']['name'];
        }
        $this->set('tuan_product_ids',$tuan_product_ids);
        $this->set('tuan_products_info',$tuan_products_info);
        $this->set('hideNav',true);
    }

    public function goods_tuans($pid=null){
        $this->pageTitle = '团购列表';
        $this->loadModel('TuanTeam');
        $date_time = date('Y-m-d', time());
        $tuan_buy_num = $this->TuanBuying->query("select sum(sold_num) as sold_number from cake_tuan_buyings  where pid = $pid");
        $tuan_buy_info = $this->TuanBuying->find('all',array(
            'conditions' => array('pid' => $pid, 'status'=>0),
            'order' => array('TuanBuying.end_time')
        ));
        $tuan_buy = Hash::combine($tuan_buy_info,'{n}.TuanBuying.tuan_id','{n}.TuanBuying');
        $tuan_ids = array_unique(Hash::extract($tuan_buy_info, '{n}.TuanBuying.tuan_id'));
        $tuan_info = $this->TuanTeam->find('all', array(
            'conditions' =>array('id'=>$tuan_ids),
            'order' => array('TuanTeam.priority DESC')
        ));
        $this->loadModel('Product');
        $tuan_product = $this->Product->find('first', array(
            'conditions' => array('id' => $pid),
            'fields' => array('name', 'promote_name', 'price','id')
        ));
        $this->set('tuan_product', $tuan_product);
        $this->set('tuan_info',$tuan_info);
        $this->set('pid',$pid);
        $this->set('tuan_buy',$tuan_buy);
        $this->set('tuan_buy_num',$tuan_buy_num[0][0]['sold_number']);
        $this->set('hideNav',true);
    }
    public function big_tuan_balance($tuan_buy_id){
        $this->balance($tuan_buy_id);
        $this->set('hideNav',true);
    }
}

