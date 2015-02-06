<?php

class ProductsController extends AppController{
    var $name = 'Products';
    public $brand = null;

    public function beforeFilter(){
        parent::beforeFilter();
    }

    private function checkAccess(){

        if(empty($this->currentUser['id'])){
            $this->__message('您需要先登录才能操作','/users/login');
        }

        $this->loadModel('Brand');
        $this->brand = $this->Brand->find('first',array('conditions'=>array(
            'creator'=>$this->currentUser['id'],
        )));
        if(empty($this->brand)){
            $this->__message('只有合作商家才能添加商品','/');
        }

    }

//    public function view() {
//        parent::view();
//
//        $afford_for_curr_user = true;
//        if ($this->current_data_id == ShipPromotion::QUNAR_PROMOTE_ID) {
//            $ordersModel = ClassRegistry::init('Order');
//            $order_ids = $ordersModel->find('list', array(
//                'conditions' => array('brand_id' => ShipPromotion::QUNAR_PROMOTE_BRAND_ID, 'deleted' => 0),
//                'fields' => array('id', 'id')
//            ));
//            if (!empty($order_ids)) {
//                $cartModel = ClassRegistry::init('Cart');
//                $c = $cartModel->find('count', array(
//                    'conditions' => array('order_id' => $order_ids, 'product_id' => $this->current_data_id, 'deleted' => 0)
//                ));
//                if ($c > 0) {
//                    $afford_for_curr_user = false;
//                }
//            }
//        }
//        $this->set('afford_for_curr_user', $afford_for_curr_user);
//    }


    public function add(){

        $this->checkAccess();

        if(!empty($this->data)){
            $this->data[$this->modelClass]['brand_id'] = $this->brand['Brand']['id'];
        }
        parent::add();
    }

    public function mine(){
        $this->checkAccess();

        $pagesize = intval(Configure::read($this->modelClass.'.pagesize'));
        if(!$pagesize){
            $pagesize = 15;
        }

        $total = $this->{$this->modelClass}->find('count', array('conditions' => array('brand_id' => $this->brand['Brand']['id'])));
        $datalist = $this->{$this->modelClass}->find('all', array(
            'conditions' => array('brand_id' => $this->brand['Brand']['id']),
            'fields'=>array('id','name','price','published','coverimg'),
        ));

        $page_navi = getPageLinks($total, $pagesize, '/products/mine', $page);
        $this->set('datalist',$datalist);
        $this->set('page_navi', $page_navi);
    }

    function edit($id) {
        $modelClass = $this->modelClass;

        $this->checkAccess();

        $datainfo = $this->{$this->modelClass}->find('first', array('conditions' => array('id' => $id, 'brand_id' => $this->brand['Brand']['id'])));
        if (empty($datainfo)) {
            throw new ForbiddenException(__('You cannot edit this data'));
        }

        if (!empty($this->data)) { //有数据提交
            $this->autoRender = false;
            $this->data[$modelClass]['creator'] = $this->currentUser['id'];

            if ($this->{$this->modelClass}->save($this->data)) {
                $this->Session->setFlash(__('The Data has been saved'));
                //$this->redirect(array('action'=>'index'));
            } else {
                $this->Session->setFlash(__('The Data could not be saved. Please, try again.'));
            }
            $successinfo = array('success' => __('Edit success'), 'actions' => array('OK' => 'closedialog'));
            //echo json_encode($successinfo);
            //return ;
            $this->redirect(array('action' => 'edit',$id));
        }
        else{
            $this->data = $datainfo; //加载数据到表单中
        }
    }

    function product_detail($slug){
        $this->setHistory();
        $fields = array('id','slug','name','content','created');
        $this->set('hideNav',true);
        parent::view($slug,$fields);
    }

    function view_shichi_comment($slug){
        $this->setHistory();
        $fields = array('id','slug','name','created');
        $this->set('hideNav',true);
        parent::view($slug,$fields);

    }

    function product_comments($slug){
        $this->setHistory();
        $fields = array('id','slug','name','content','created');
        $this->set('hideNav',true);
        parent::view($slug,$fields);
    }

    function view($slug='/'){
        //要求评论登录
        if ($_GET[SPEC_PARAM_KEY_COMM] == 1 || $_GET[SPEC_PARAM_KEY_SHICHI_COMM] == 1) {
            if ($this->is_weixin()) {
                if (empty($this->currentUser) || name_empty_or_weixin($this->currentUser['nickname'])) {
                    $ref = Router::url($_SERVER['REQUEST_URI']);
                    $this->redirect('/users/login.html?force_login=1&auto_weixin=1&referer=' . urlencode($ref));
                }
            }
        }

        if($this->RequestHandler->isMobile()){
            $fields=array('id','user_id','name','coverimg','slug','color','material','manufacturer','price','special','manual','remoteurl','status','deleted','priority','views_count','saled','storage','seotitle',
                'seodescription','seokeywords', 'created', 'updated', 'published', 'brand_id', 'photo', 'cate_id', 'end_time', 'promote_name', 'comment_nums', 'recommend', 'ship_fee', 'original_price', 'cost_price', 'specs', 'sort_in_store',);
        }
        parent::view($slug,$fields);
        $pid = $this->current_data_id;
        if($this->RequestHandler->isMobile()){
            $this->loadModel('Comment');
            //load shichi comment count
            $shi_chi_comment_count = $this->Comment->find('count',array(
                'conditions'=>array(
                    'data_id'=>$pid,
                    'status'=>1,
                    'is_shichi_tuan_comment'=>1
                )
            ));
            $comment_count = $this->viewdata['Product']['comment_nums'];
            $this->set('shi_chi_comment_count',$shi_chi_comment_count);
            $this->set('commet_count',($comment_count-$shi_chi_comment_count));
            $this->set('limitCommentCount',COMMENT_LIMIT_IN_PRODUCT_VIEW);
        }
        if ($pid == PRODUCT_ID_RICE_10) {

            $current_uid = $this->currentUser['id'];

            if($this->is_weixin() || !empty($_GET['trid'])) {
                if (!$current_uid) {
                    $this->redirect('/users/login?referer=' . urlencode($_SERVER['REQUEST_URI']));
                }
                $track_type = TRACK_TYPE_PRODUCT_RICE;
                list($friend, $shouldAdd, ) = $this->track_or_redirect($current_uid, $track_type);
                if ($shouldAdd) {
                    //$this->AwardInfo->updateAll(array('times' => 'times + 1',), array('uid' => $friend['User']['id']));
                }
                if (!empty($friend)) {
                    $this->redirect_for_append_tr_id($current_uid, $track_type, $_SERVER['REQUEST_URI']);
                }
            }
        }

        $brandId = $this->viewdata['Product']['brand_id'];


        $this->loadModel('SpecialList');
        $specialLists = $this->SpecialList->has_special_list($pid);
        if (!empty($specialLists)) {
            foreach ($specialLists as $specialList) {
                if ($specialList['type'] == 1) {
                    $special = $specialList;
                    break;
                }
            }
        }

        $this->log("view product:".$pid.", specialLists=".json_encode($specialLists));

        $use_special = false;
        $price = $this->viewdata['Product']['price'];
        $currUid = $this->currentUser['id'];
        if (!empty($special) && $special['special']['special_price'] >= 0) {

            $special_rg = range_by_special($special);

            //TODO: check time (current already checked)
            //CHECK time limit!!!!
            list($afford_for_curr_user, $left_cur_user, $total_left) =
                calculate_afford($pid, $currUid, $special['special']['limit_total'], $special['special']['limit_per_user'], $special_rg);

            $this->log('view product afford(special): for_curr_user='.$afford_for_curr_user.', left_cur_user='.$left_cur_user.', total_left='.$total_left.', range='.json_encode($special_rg).', uid='.$currUid);

            $promo_name = $special['name'];
            $special_least_num = $special['special']['least_num'];
            $special_price = $special['special']['special_price'] / 100;
            App::uses('CakeNumber', 'Utility');
            $promo_desc = ($special_least_num > 0 ? '满'.$special_least_num.'件' : '') .'￥'.CakeNumber::precision($special_price, 2);
            if ($special['special']['limit_total'] > 0) {
                $promo_desc .= ' 限'.$special['special']['limit_total'].'件';
            }
            if ($special['special']['limit_per_user'] > 0) {
                $promo_desc .= ' 每人限'.$special['special']['limit_per_user'].'件';
            }
            if ($afford_for_curr_user) {
                if ($special_least_num <= 0) { $price = $special_price; }
                $use_special = true;
            } else {
                $promo_desc .=  '('. ($left_cur_user == 0 ? '您已买过' : '已抢完') . ')';
            }
            $this->set('special_desc', $promo_desc);
            $this->set('special_name', $promo_name);
            $this->set('special_slug', $special['slug']);
            $this->set('show_special_link', $special['visible'] > 0);
        }

        if (!$use_special) {
            list($afford_for_curr_user, $left_cur_user, $total_left) = self::__affordToUser($pid, $currUid);
            $this->log('view product afford(not special): for_curr_user=' . $afford_for_curr_user . ', left_cur_user=' . $left_cur_user . ', total_left=' . $total_left . ', uid=' . $currUid);
        }

        $this->set('price', $price);

        //possible problem
        $this->set('limit_per_user', $left_cur_user);
        $this->set('total_left', $total_left);
        $this->set('afford_for_curr_user', $afford_for_curr_user);

        $specs_map = product_spec_map($this->viewdata['Product']['specs']);
        if (!empty($specs_map['map'])) {
            $str = '<script>var _p_spec_m = {';
            foreach($specs_map['map'] as $mid => $mvalue) {
                $str .= '"'.$mvalue['name'].'":"'. $mid ."\",";
            }
            $str .= '};</script>';
            $this->set('product_spec_map', $str);
        }
        $this->set('specs_map', $specs_map);
        $this->setHasOfferBrandIds($this->viewdata['Product']['brand_id']);
        $this->set('hideNav', $this->RequestHandler->isMobile());


        $this->loadModel('OrderShichi');
        $order_shichi = $this->OrderShichi->find('first', array('conditions' => array('creator' => $currUid, 'data_id' => $pid))); //查找是否有试吃订单
        $is_product_has_shichi = $this->OrderShichi->find('first',array('conditions' => array('data_id' => $pid)));
        $this->set('is_product_has_shichi',$is_product_has_shichi);
        $this->set('order_shichi', $order_shichi);

        $this->loadModel('Order');
        if (!empty($order_shichi)) {
            $order_id = $order_shichi['OrderShichi']['order_id'];
            $order = $this->Order->find('first',array('conditions' => array('id' => $order_id)));
            $order_shichi_status = $order['Order']['status'];
            $this->set('order_shichi_status',$order_shichi_status);
        }


        $this->loadModel('Brand');
        $brand = $this->Brand->findById($brandId);
        $this->set('brand', $brand);

        $recommC = $this->Components->load('ProductRecom');
        $recommends = $recommC->recommend($pid);
        $this->set('items', $recommends);

        if($this->RequestHandler->isMobile()){
            $this->setHistory();
        }else{
            if($_REQUEST['tag']){
                $this->set('history',$_REQUEST['history']);
                $this->set('tag',$_REQUEST['tag']);
            }else{
                $productTag = $this->findTagsByProduct($this->viewdata['Product']['id']);
                $this->set('history','/categories/tag/'.$productTag['ProductTag']['slug'].'.html');
                $this->set('tag',$productTag['ProductTag']['name']);
            }
        }
        
        App::uses('CakeNumber', 'Utility');
        $this->loadModel('ShipSetting');
        $shipSettings = $this->ShipSetting->find_by_pids($pid, null);
        foreach($shipSettings as $shipS) {
            $type = $shipS['ShipSetting']['type'];
            $ship_fee = $shipS['ShipSetting']['ship_fee'];
            if ($type == TYPE_MUL_NUMS) {
                $this->set('ship_by_item_num', true);
            } else if ($type == TYPE_ORDER_FIXED) {
                $this->set('ship_fixed', true);
            } else if ($type == TYPE_REDUCE_BY_NUMS) {
                $this->set('ship_promo_nums', '满'.$shipS['ShipSetting']['least_num'].'件'. $this->ship_desc_1($ship_fee));
            } else if ($type == TYPE_ORDER_PRICE) {
                $this->set('ship_promo_amount', '参与本店购满'.CakeNumber::precision($shipS['ShipSetting']['least_total_price']/100, 2).'元'. $this->ship_desc_1($ship_fee));
            }
        }

        if (empty($shipSettings)) {
            $this->loadModel('ShipPromotion');
            $shipPromotions = $this->ShipPromotion->findShipPromotions(array($pid));
            if ($shipPromotions && !empty($shipPromotions)) {
                $this->set('limit_ship', $shipPromotions['limit_ship']);
            }
        }

        $currentUser = $_SESSION['Auth']['User'];
        if($currentUser){
            $this->loadModel('ViewedProduct');
            $userId = $currentUser['id'];
            $browsingHistoryProductsData = $this->ViewedProduct->find('first',
                array(
                    'conditions' => array('uid' => $userId),
                )
            );
            $cur = current($browsingHistoryProductsData);
            $viewedDataId = $cur['id'];
        }
        $browsing_history = $_SESSION['BrowsingHistory'];
        if(!$browsing_history){
            $browsing_history =array();
        }

        if(!is_array($browsing_history)){
            $browsing_history = array();
            array_push($browsing_history,$_SESSION['BrowsingHistory']);
        }

        if($browsingHistoryProductsData){
            $viewedData = $browsingHistoryProductsData[0]['ViewedProduct']['browsing_history'];
        }
        if($viewedData){
            $browsing_history = explode(',',$viewedData);
        }
        $browsing_history = array_unique($browsing_history);
        $browsingHistoryProducts = $this->Product->find('all',array(
            'conditions'=>array(
                'id' =>$browsing_history
            ),
            'recursive' => -1,
        ));


        $browsingHistoryProducts = Hash::combine($browsingHistoryProducts, '{n}.Product.id','{n}.Product');
        $this->set('browsing_history_products',$browsingHistoryProducts);

        $this->set('browsing_history_ids',$browsing_history);
        if(count($browsing_history)>9){
            array_shift($browsing_history);
        }
        array_push($browsing_history,$this->viewdata['Product']['id']);
        if($currentUser){
            $this->ViewedProduct->id = $viewedDataId;
            $this->ViewedProduct->save(array(
                'uid'=>$userId,
                'browsing_history'=>join($browsing_history,',')
            ));
        }
        $this->Session->write('BrowsingHistory',$browsing_history);


        $this->loadModel('ProductProductTag');
        $nianhuo = $this->ProductProductTag->find('first', array(
                'conditions' => array(
                    'tag_id' => 20,
                    'product_id' => $pid
                ),
            )
        );
        if (!empty($nianhuo)) {
            $this->set('in_nianhuo', true);
        }

        if ($pid == PRODUCT_ID_CAKE) {
            $this->set('cake_dates', cake_send_date());
        }

        $this->set('category_control_name', 'products');
        $this->track_share_click();
        if($this->is_weixin()){
            if($currUid){
                $this->loadModel('WxOauth');
                $signPackage = $this->WxOauth->getSignPackage();
                $share_string = $currUid.'-'.time().'-rebate-pid_'.$pid;
                $share_code = authcode($share_string, 'ENCODE', 'SHARE_TID');
                $this->set('signPackage', $signPackage);
                $this->set('share_string',urlencode($share_code));
                $this->set('jWeixinOn', true);
            }
        }
    }

    /**
     * @param $ship_fee
     * @return string
     */
    private function ship_desc_1($ship_fee) {
        return ($ship_fee == 0 ? '包邮' : '邮费' . CakeNumber::precision($ship_fee / 100, 2) . '元');
    }

    /**
     * @return mixed
     */
    private function findTagsByProduct($productId) {
        $this->loadModel('ProductTag');
        $join_conditions = array(
            array(
                'table' => 'product_product_tags',
                'alias' => 'Tag',
                'conditions' => array(
                    'Tag.tag_id = ProductTag.id',
                    'Tag.product_id' => $productId
                ),
                'type' => 'RIGHT',
            )
        );
        $productTags = $this->ProductTag->find('first', array('conditions' => array(
            'show_in_home' => 1,
            'published' => 1,
        ),
            'joins' => $join_conditions,
            'order' => 'priority desc'
        ));
        return $productTags;
    }
    private function track_share_click(){
        if($_GET['share_type'] && $_GET['trstr']){
            if (empty($this->currentUser['id']) && $this->is_weixin()) {
                $ref = Router::url($_SERVER['REQUEST_URI']);
                $this->redirect('/users/login.html?force_login=1&auto_weixin=' . $this->is_weixin() . '&referer=' . urlencode($ref));
                exit();
            }
            $share_type = $_GET['share_type'];
            $trstr = $_GET['trstr'];
            if($share_type != 'timeline' && $share_type != 'appMsg'){
                $this->log("WxShare: type wrong");
                return;
            }
            $type = $share_type == 'timeline' ? 1:0;
            $decode_string = authcode($trstr, 'DECODE', 'SHARE_TID');
            $str = explode('-',$decode_string);
            $data_str = explode('_',$str[3]);
            if($str[2] != 'rebate'){
                $this->log("WxShare: PRODUCT_KEY WRONG");
                return;
            }
            if($data_str[0] == 'pid'){
                $data_type = 'product';
            }else{
                $this->log("WxShare: data type error");
                return;
            }
            $sharer = intval($str[0]);
            $created = intval($str[1]);
            $clicker = $this->currentUser['id']? $this->currentUser['id']:0;
            $this->loadModel('ShareTrackLog');
            $data =array('sharer' => $sharer, 'clicker' => $clicker, 'share_time' => $created, 'click_time'=>time(), 'data_type' => $data_type, 'data_id' => intval($data_str[1]) , 'share_type' => $type);
            $this->ShareTrackLog->save($data);
        }
        return;
    }

    function guess_product_price(){

        global $order_after_paid_status;

        if (empty($this->currentUser['id']) && $this->is_weixin()) {
            $ref = Router::url($_SERVER['REQUEST_URI']);
            $this->redirect('/users/login.html?force_login=1&auto_weixin=' . $this->is_weixin() . '&referer=' . urlencode($ref));
        }

        $cartM = ClassRegistry::init('Cart');
        $total_sold = total_sold(PRODUCT_ID_JD_HS_NZT, array('start' => '2015-01-28 00:00:00', 'end' => '2014-01-29 00:00:00'), $cartM);

        $this->pageTitle = '你说多少钱，就卖多少钱！';
        $bannerItems = array(
            array('img' => "/img/guess_price/banner01.jpg"),
            array('img' => "/img/guess_price/banner02.jpg"),
            array('img' => "/img/guess_price/banner03.jpg"),
        );

        $this->loadModel('Cart');
        $this->loadModel('User');

        $this->loadModel('Order');
        $order_creators = $this->Order->find('all', array(
            'conditions' => array('brand_id' => 143, 'status' => $order_after_paid_status, 'id > 11000'),
            'fields' => 'creator'
        ));

        $this->log("order ids for guess_price:". json_encode($order_creators));

        $ids = Hash::extract($order_creators, '{n}.Order.creator');

//        $top_price_cart = $this->Cart->find('all', array(
//            'conditions' => array('order_id' => $ids, 'product_id' => 484),
//            'order' => 'price desc',
//            'limit' => 1,
//            )
//        );

        $this->loadModel('UserPrice');
        $top_price = $this->UserPrice->find('first', array(
                'conditions' => array('uid' => $ids, 'product_id' => 484),
                'order' => 'customized_price desc',
                'limit' => 1,
            )
        );

//        $this->log("find top_price_cart ".json_encode($top_price_cart).", with order_ids:".$order_creators);

        $user_info = $this->User->find('first',array('conditions' => array('id' => $top_price['UserPrice']['uid'])));
        $this->set('user_info',$user_info);
        $this->set('top_price', max($top_price['UserPrice']['customized_price'], 19.9)); //assume 20 at lease
        $this->set('bannerItems',$bannerItems);
        $this->set('hideNav',true);
        $this->set('soldout', $total_sold > 100);
        if($this->is_weixin()){
            $this->loadModel('WxOauth');
            $signPackage = $this->WxOauth->getSignPackage();
            $this->set('signPackage', $signPackage);
            $this->set('jWeixinOn', true);
        }
    }

    function guess_product_detail(){
        $this->pageTitle = '商品详情';
        $this->set('hideNav',true);
    }

    function setHistory(){
        $history = $_REQUEST['history'];
        if(!$history){
            $history ='/';
        }
        if(!(strpos($history,WX_HOST)>=0)){
            $history='/';
        }
        $this->set('history',$history);
    }

}