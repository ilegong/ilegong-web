<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 15/3/16
 * Time: 下午6:31
 */
class TuanBuyingsController extends AppController{


    public $components = array('ProductSpecGroup');

    private static function key_balance_pids() {
        return "Balance.balance.pids";
    }
    public static function key_balanced_scores() {
        return "Balance.apply_scores";
    }
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
        }
        if($tuan_b['TuanBuying']['status'] == 0){
            $this->set('tuan_buy_status', 0);
        }elseif($tuan_b['TuanBuying']['status'] == 2 || $tuan_b['TuanBuying']['status'] == 21 ){
            $this->set('tuan_buy_status', 2);
        }
        $pid=$tuan_b['TuanBuying']['pid'];
        //$end_time =  friendlyDateFromStr($tuan_b['TuanBuying']['end_time'], FORMAT_DATETIME);
        $end_time = $tuan_b['TuanBuying']['end_time'];
        //芒果
        if($pid==851){
            $consign_time = empty($tuan_b['TuanBuying']['consign_time'])? '成团后现摘' : friendlyDateFromStr($tuan_b['TuanBuying']['consign_time'], FFDATE_CH_MD);
        }else{
            $consign_time = empty($tuan_b['TuanBuying']['consign_time'])? '成团后发货' : friendlyDateFromStr($tuan_b['TuanBuying']['consign_time'], FFDATE_CH_MD);
        }
        $this->set(compact('pid', 'consign_time', 'tuan_buy_id', 'tuan_team', 'end_time'));
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
        $tuan_buy_type = $tuan_b['TuanBuying']['consignment_type'];
        if($tuan_buy_type==2){
            //排期
            $consignment_dates = consignment_send_date($pid);
            if(!empty($consignment_dates)){
                $this->set('consignment_dates', $consignment_dates);
            }
        }
        $this->set('tuan_buy_type',$tuan_buy_type);
        $this->set('hideNav',true);
        if($this->is_weixin()){
            $currUid = empty($this->currentUser) ? 0 : $this->currentUser['id'];
            $weixinJs = prepare_wx_share_log($currUid, 'pid', $pid);
            $this->set($weixinJs);
            $this->set('jWeixinOn', true);
        }
        $this->loadModel('Product');
        $this->loadModel('Uploadfile');
        $this->loadModel('Brand');
        $Product = $this->Product->find('first',array('conditions' => array('id' => $pid,'deleted' => DELETED_NO)));
        $brand = $this->Brand->find('first', array(
            'conditions' => array('id' => $Product['Product']['brand_id']),
            'fields' => array('name', 'slug','coverimg')
        ));
        $this->set('brand',$brand);
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
       
        $con = array('modelclass' => 'Product','fieldname' =>'photo','data_id' => $pid);
        $Product['Uploadfile']= $this->Uploadfile->find('all',array('conditions' => $con, 'order'=> array('sortorder DESC')));
        $original_price = $Product['Product']['original_price'];
        $tuan_price = $tuan_b['TuanBuying']['tuan_price'];
        if($tuan_price > 0){
            $this->set('tuan_price',$tuan_price);
        }else{
            $tuan_product_price = getTuanProductPrice($pid);
            if($tuan_product_price>0){
                $this->set('tuan_product_price',$tuan_product_price);
            }
        }
        $this->set('original_price',$original_price);
        $this->set('Product', $Product);
        $this->set('category_control_name', 'products');
        $this->set('current_data_id', $pid);

        $this->loadModel('Comment');
        //load shichi comment count
        $same_pids = get_group_product_ids($pid);
        $shi_chi_comment_count = $this->Comment->find('count',array(
            'conditions'=>array(
                'data_id'=>$same_pids,
                'status'=>1,
                'is_shichi_vote'=>1
            )
        ));
        $comment_count = $this->Comment->find('count',array(
            'conditions'=>array(
                'data_id'=>$same_pids,
                'status'=>1
            )
        ));
        $this->set('shi_chi_comment_count',$shi_chi_comment_count);
        $this->set('comment_count',($comment_count-$shi_chi_comment_count));
        $this->set('limitCommentCount',COMMENT_LIMIT_IN_PRODUCT_VIEW);
        $recommC = $this->Components->load('ProductRecom');
        $recommends = $recommC->recommend($pid);
        $this->set('items', $recommends);
        if($tuan_team['TuanTeam']['type'] == 1){
            $this->set('big_tuan', true);
        }
    }

    public function cart_info(){
        $this->autoRender = false;
        $this->loadModel('Cart');
        $tuan_buy_id = intval($_REQUEST['tuan_buy_id']);
        $product_id = intval($_REQUEST['product_id']);
        $product_num = intval($_REQUEST['product_num']);
        $spec_id = intval($_REQUEST['spec_id']);
        $uId = $this->currentUser['id'];
        $cart_tuan_param = array(
            'tuan_buy_id' => $tuan_buy_id,
            'product_id' => $product_id
        );
        $cartInfo = $this->Cart->add_to_cart($product_id,$product_num,$spec_id,ORDER_TYPE_TUAN,0,$uId,null,  null, null,$cart_tuan_param);
        $this->log('cartInfo'.json_encode($cartInfo));
        if($cartInfo){
            $consignment_date_id = intval($_REQUEST['consignment_date_id']);
            $cart_id = $cartInfo['Cart']['id'];
            if ($consignment_date_id!=0 && $cart_id) {
                if ($consignment_date_id) {
                    $cartM = ClassRegistry::init('Cart');
                    $cartM->updateAll(array('consignment_date' => $consignment_date_id), array('id' => $cart_id));
                }
            }
            if($_POST['way_type'] == 'ziti'){
                echo json_encode(array('success' => true, 'direct'=>'big_tuan_list'));
            }else{
                $ship_fee = floatval($_POST['way_fee']);
                echo json_encode(array('success' => true, 'direct'=>'normal','way_type'=>$_POST['way_type'],'way_fee'=>$ship_fee));
            }
            $cart_array = array(0 => strval($cart_id));
            $this->Session->write(self::key_balance_pids(), json_encode($cart_array));
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
        $ship_fee = floatval($_REQUEST['way_fee']);
        $total_price = $Carts['Cart']['price'] * $Carts['Cart']['num'];
        $this->set('ship_fee',$ship_fee);
        $total_price = $total_price+floatval($ship_fee);
        $pid = $tuan_b['TuanBuying']['pid'];
        $this->loadModel('Product');
        $this->loadModel('Brand');
        $product_brand = $this->Product->find('first', array(
            'conditions' => array('id' => $pid),
            'fields' => array('brand_id')
        ));
        $brand = $this->Brand->find('first', array(
            'conditions' => array('id', $product_brand['Product']['brand_id']),
            'fields' => array('name', 'slug')
        ));
        $this->loadModel('OrderConsignees');
        $consignee_info = $this->OrderConsignees->find('first', array(
            'conditions' => array('creator' => $uid, 'status' => STATUS_CONSIGNEES_TUAN),
            'fields' => array('name', 'address', 'mobilephone')
        ));
        if($consignee_info){
            $this->set('consignee_info', $consignee_info['OrderConsignees']);
        }
        //积分统计
        $this->loadModel('User');
        $score = $this->User->get_score($uid, true);
        $could_score_money = cal_score_money($score, $total_price);
        $this->set('score_usable', $could_score_money * 100);

        $way_type = $_REQUEST['way_type'];
        $this->set('way_type',$way_type);
        $this->set('buy_count',$Carts['Cart']['num']);
        $this->set('total_price', $total_price);
        $this->set('cart_id', $Carts['Cart']['id']);
        $this->set('tuan_id', $tuan_info['TuanTeam']['id']);
        $this->set('tuan_address', $tuan_info['TuanTeam']['tuan_addr']);
        $this->set('end_time', date('m-d', $current_time));
        $this->set('tuan_buy_id', $tuan_buy_id);
        $this->set('cart_info',$Carts);
        $this->set('max_num',$max_num);
        $this->set('brand', $brand['Brand']);
        if($tuan_info['TuanTeam']['type'] == 1){
            $this->set('big_tuan', true);
        }
        $this->set('hideNav',true);
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
            $this->loadModel('OrderConsignees');
            $consignees = array('name' => $name, 'mobilephone' => $mobile, 'status' => STATUS_CONSIGNEES_TUAN);
            $p_address = $_POST['address'];
            if($tuan_info['TuanTeam']['type'] == 1){
                if($_POST['way'] != 'ziti'){
                    $consignees['address'] = $p_address;
                }
                $address = $p_address;
            }else{
                $address = $tuan_info['TuanTeam']['address'];
                if(!empty($p_address)){
                    $address = $address.$p_address;
                }
            }
            if($_POST['way'] == 'kddj'){
                if($pid==876){
                    //蔬菜加10元邮费
                    $total_price = $total_price+10;
                }
                if($pid==879){
                    //蔬菜加10元邮费
                    $total_price = $total_price+13;
                }
            }
            $tuan_consignees = $this->OrderConsignees->find('first', array(
                'conditions' => array('status' => STATUS_CONSIGNEES_TUAN, 'creator' => $uid),
                'fields' => array('id')
            ));
            if($tuan_consignees){
                $consignees['id'] = $tuan_consignees['OrderConsignees']['id'];
            }else {
                $consignees['creator'] = $uid;
            }
            $this->OrderConsignees->save($consignees);
            $order = $this->Order->createTuanOrder($tuan_buy_id, $uid, $total_price, $pid, $order_type, $area, $address, $mobile, $name, $cart_id);
            $order_id = $order['Order']['id'];
            $score_consumed = 0;
            $spent_on_order = intval($this->Session->read(self::key_balanced_scores()));
            $order_id_spents = array();
            if($spent_on_order > 0 ) {
                $reduced = $spent_on_order / 100;
                $toUpdate = array('applied_score' => $spent_on_order,
                    'total_all_price' => 'if(total_all_price - ' . $reduced .' < 0, 0, total_all_price - ' . $reduced .')');
                if($this->Order->updateAll($toUpdate, array('id' => $order_id, 'status' => ORDER_STATUS_WAITING_PAY))){
                    $this->log('apply user score=' . $spent_on_order . ' to order-id=' . $order_id . ' successfully');
                    $score_consumed += $spent_on_order;
                    $order_id_spents[$order_id] = $spent_on_order;
                }
            }
            if ($score_consumed > 0) {
                $scoreM = ClassRegistry::init('Score');
                $scoreM->spent_score_by_order($uid, $score_consumed, $order_id_spents);
                $this->loadModel('User');
                $this->User->add_score($uid, -$score_consumed);
            }
            // 注意必须清除key_balanced_scores
            $this->Session->write(self::key_balanced_scores(), '');

            if ($order['Order']['status'] != ORDER_STATUS_WAITING_PAY) {
                $res = array('success'=> false, 'info'=> '你已经支付过了');
            }else{
                //$consign_time = friendlyDateFromStr($tuanBuy['TuanBuying']['consign_time'], FFDATE_CH_MD);
                $cart_name = $cart_info['Cart']['name'];
                if($tuan_info['TuanTeam']['type'] == 1 && $_POST['way'] == 'sf'){
                    $cart_name = $cart_name.'(顺丰到付)';
                }
                if($tuan_info['TuanTeam']['type'] == 1 && $_POST['way'] == 'baoyou'){
                    $cart_name = $cart_name.'(包邮)';
                }
                if($tuan_info['TuanTeam']['type'] == 1 && $_POST['way'] == 'kddj'){
                    $cart_name = $cart_name.'(快递到家)';
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
        $tuan_buy_id = $_REQUEST['tuan_buy_id'];
        $fields = array('id','slug','name','content','created');
        $this->loadModel('Product');
        $Product =$this->Product->find('first', array(
            'conditions' => array('id' => $pid),
            'fields' => $fields
        ));
        $this->pageTitle = mb_substr($Product['Product']['name'],0,13);
        $this->set('pid',$pid);
        $this->set('tuan_buy_id',$tuan_buy_id);
        $this->set('Product',$Product);
        $this->set('hideNav',true);
    }
    public function goods(){
        $this->pageTitle = '团购商品';
        $currentDate = date(FORMAT_DATETIME);
        $tuanProducts = getTuanProducts();
        $tuanProducts = Hash::combine($tuanProducts,'{n}.TuanProduct.product_id','{n}.TuanProduct');
        $tuan_products = $this->TuanBuying->find('all',array('conditions' => array("pid != " => 863,'status'=>0,'end_time > '=>$currentDate),'group' => array('pid')));
        $tuan_product_ids = Hash::extract($tuan_products,'{n}.TuanBuying.pid');
        $this->loadModel('Product');
        $tuan_products_info = array();
        foreach($tuan_product_ids as $pid){
            $tuan_products_info[$pid] = $this->TuanBuying->query("select sum(sold_num) as sold_number from cake_tuan_buyings  where pid = $pid");
            $tuan_product = $this->Product->find('first',array('conditions' => array('id' => $pid),'fields' => array('deleted','name','original_price','price')));
            $tuan_products_info[$pid]['status'] = $tuan_product['Product']['deleted'];
            $tuan_products_info[$pid]['name'] = $tuan_product['Product']['name'];
            $tuan_products_info[$pid]['price'] = $tuan_product['Product']['price'];
            $tuan_products_info[$pid]['list_img'] = $tuanProducts[$pid]['list_img'];
            $tuan_products_info[$pid]['original_price'] = $tuan_product['Product']['original_price'];
        }
        $this->set('tuan_product_ids',$tuan_product_ids);
        $this->set('tuan_products_info',$tuan_products_info);
        $this->set('hideNav',true);
    }

    public function goods_tuans($pid=null){
        $this->pageTitle = '团购列表';
        $this->loadModel('TuanTeam');
        $date_time = date('Y-m-d H:i:s', time());
        $tuan_buy_num = $this->TuanBuying->query("select sum(sold_num) as sold_number from cake_tuan_buyings  where pid = $pid");
        $tuan_buyings = $this->TuanBuying->find('all',array(
            'conditions' => array('pid' => $pid, 'status'=>0, 'end_time >'=> $date_time),
            'order' => array('TuanBuying.end_time')
        ));
        $tuan_ids = array_unique(Hash::extract($tuan_buyings, '{n}.TuanBuying.tuan_id'));
        $tuan_info = $this->TuanTeam->find('all', array(
            'conditions' =>array('id'=>$tuan_ids),
        ));
        $tuan_info = Hash::combine($tuan_info,'{n}.TuanTeam.id','{n}.TuanTeam');
        //只有一个大团
        if(count($tuan_info)==1){
            $tuan_id = $tuan_buyings[0]['TuanBuying']['tuan_id'];
            $tuan_buy_id = $tuan_buyings[0]['TuanBuying']['id'];
            $tuan = $tuan_info[$tuan_id];
            if($tuan['type']==1){
                $this->redirect('/tuan_buyings/detail/'.$tuan_buy_id);
            }
        }
        $this->loadModel('Product');
        $tuan_product = $this->Product->find('first', array(
            'conditions' => array('id' => $pid),
            'fields' => array('name', 'promote_name', 'price','id','original_price')
        ));
        $tuanProducts = getTuanProducts();
        $tuanProducts = Hash::combine($tuanProducts,'{n}.TuanProduct.product_id','{n}.TuanProduct');
        $tuan_product_price = getTuanProductPrice($pid);
        if($tuan_product_price>0){
            $this->set('tuan_product_price',$tuan_product_price);
        }
        $this->set('detail_img',$tuanProducts[$pid]['detail_img']);
        $this->set('tuan_product', $tuan_product);
        $this->set('tuan_info',$tuan_info);
        $this->set('pid',$pid);
        $this->set('tuan_buyings',$tuan_buyings);
        $this->set('tuan_buy_num',$tuan_buy_num[0][0]['sold_number']);
        $this->set('hideNav',true);
    }
    public function big_tuan_balance($tuan_buy_id){
        $this->balance($tuan_buy_id);
        $this->set('hideNav',true);
    }
}

