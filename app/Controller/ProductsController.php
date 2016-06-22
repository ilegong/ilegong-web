<?php

class ProductsController extends AppController{
    var $name = 'Products';
    public $brand = null;

    //public $components = array('ProductSpecGroup');

    public function beforeFilter(){
        parent::beforeFilter();
        $this->redirect('/');
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


    public function add(){

        $this->checkAccess();

        if(!empty($this->data)){
            $this->data[$this->modelClass]['brand_id'] = $this->brand['Brand']['id'];
        }
        parent::add();
    }


    public function consignment(){
        $this->checkAccess();
        $this->pageTitle='商品排期';
        $pagesize = intval(Configure::read($this->modelClass.'.pagesize'));
        if(!$pagesize){
            $pagesize = 15;
        }

        $total = $this->{$this->modelClass}->find('count', array('conditions' => array('brand_id' => $this->brand['Brand']['id'])));
        $datalist = $this->{$this->modelClass}->find('all', array(
            'conditions' => array('brand_id' => $this->brand['Brand']['id'],'published' => PUBLISH_YES, 'deleted' => DELETED_NO),
            'fields'=>array('id','name','price','published','coverimg'),
        ));

        $page_navi = getPageLinks($total, $pagesize, '/products/consignment', $page);
        $this->set('datalist',$datalist);
        $this->set('page_navi', $page_navi);
    }

    public function mine(){
        //$this->checkAccess();
        if(empty($this->currentUser['id'])){
            $this->__message('您需要先登录才能操作','/users/login');
        }
        $this->loadModel('Brand');
        $brands = $this->Brand->find('all',array('conditions'=>array(
            'creator'=>$this->currentUser['id'],
        )));
        if(empty($brands)){
            $this->__message('只有合作商家才能添加商品','/');
        }
        $brand_ids = Hash::extract($brands,'{n}.Brand.id');

        $pagesize = intval(Configure::read($this->modelClass.'.pagesize'));
        if(!$pagesize){
            $pagesize = 15;
        }

        $total = $this->{$this->modelClass}->find('count', array('conditions' => array('brand_id' => $brand_ids)));
        $datalist = $this->{$this->modelClass}->find('all', array(
            'conditions' => array('brand_id' => $brand_ids),
            'fields'=>array('id','name','price','published','coverimg'),
        ));

        $page_navi = getPageLinks($total, $pagesize, '/products/mine', $page);
        $this->set('datalist',$datalist);
        $this->set('page_navi', $page_navi);
    }

    function edit($id) {
        $modelClass = $this->modelClass;

        $this->checkAccess();

        if(empty($this->currentUser['id'])){
            $this->__message('您需要先登录才能操作','/users/login');
        }
        $this->loadModel('Brand');
        $brands = $this->Brand->find('all',array('conditions'=>array(
            'creator'=>$this->currentUser['id'],
        )));
        if(empty($brands)){
            $this->__message('只有合作商家才能添加商品','/');
        }
        $brand_ids = Hash::extract($brands,'{n}.Brand.id');

        $datainfo = $this->{$this->modelClass}->find('first', array('conditions' => array('id' => $id, 'brand_id' => $brand_ids)));
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
        $fields = array('id', 'slug', 'name', 'content', 'created', 'product_alias');
        $this->set('hideNav', true);
        parent::view($slug, $fields);
        $pid = $this->current_data_id;
        $currUid = $this->currentUser['id'];
        //$this->calculate_price_limitation($pid, $currUid);
        if ($this->is_weixin()) {
            $this->prepare_wx_sharing($currUid, $pid);
        }
        $this->setFrom();
    }

    function view_shichi_comment($slug){
        $this->setHistory();
        $fields = array('id', 'slug', 'name', 'created', 'product_alias');
        $this->set('hideNav', true);
        parent::view($slug, $fields);
        $currUid = $this->currentUser['id'];
        $pid = $this->current_data_id;
        $this->loadModel('OrderShichi');
        $order_shichi = $this->OrderShichi->find('first', array('conditions' => array('creator' => $currUid, 'data_id' => $pid))); //查找是否有试吃订单
        $is_product_has_shichi = $this->OrderShichi->find('first', array('conditions' => array('data_id' => $pid)));
        $this->set('is_product_has_shichi', $is_product_has_shichi);
        $this->set('order_shichi', $order_shichi);
        $this->loadModel('Order');
        if (!empty($order_shichi)) {
            $order_id = $order_shichi['OrderShichi']['order_id'];
            $order = $this->Order->find('first', array('conditions' => array('id' => $order_id)));
            $order_shichi_status = $order['Order']['status'];
            $this->set('order_shichi_status', $order_shichi_status);
        }
    }

    function piece_product_comments($slug) {
        $fields = array('id', 'slug', 'name', 'created', 'product_alias');
        parent::view($slug, $fields);
        $this->set('hideNav', true);
        if (!empty($_REQUEST['init_count'])) {
            $this->set('limitCommentCount', $_REQUEST['init_count']);
        }
    }

    function piece_product_detail($slug){
        $fields = array('content');
        $this->set('hideNav',true);
        parent::view($slug,$fields);
    }

    function product_comments($slug) {
        $this->setHistory();
        $fields = array('id', 'slug', 'name', 'content', 'created', 'product_alias');
        $this->set('hideNav', true);
        parent::view($slug, $fields);
        $this->setFrom();
        if (!empty($_REQUEST['init_count'])) {
            $this->set('limitCommentCount', $_REQUEST['init_count']);
        }
        $pid = $this->current_data_id;
        $currUid = $this->currentUser['id'];
        //$this->calculate_price_limitation($pid, $currUid);
        if ($this->is_weixin()) {
            $this->prepare_wx_sharing($currUid, $pid);
        }
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
        $tuanProducts = getTuanProducts();
        $tuan_product_not_show_ids = Hash::combine($tuanProducts, '{n}.TuanProduct.product_id', '{n}.TuanProduct.general_show');
        if(array_key_exists($pid ,$tuan_product_not_show_ids) && $tuan_product_not_show_ids[$pid] == 0){
            $this->loadModel('TuanBuying');
            $big_tuan = $this->TuanBuying->find('first', array(
                'conditions' => array('pid' => $pid, 'tuan_id'=>PYS_M_TUAN, 'status'=> 0, 'published' => 1)
            ));
            if(!empty($big_tuan)){
                $redirect_url = '/tuan_buyings/detail/'.$big_tuan['TuanBuying']['id'];
            }else{
                $redirect_url = '/tuan_buyings/goods_tuans/'.$pid;
            }
            if($_REQUEST['tagId']){
                $redirect_url = $redirect_url.'?tagId='.$_REQUEST['tagId'];
            }
            $this->redirect($redirect_url);
            return;
        }
        $this->loadModel('Comment');
        //load shichi comment count
        $shi_chi_comment_count = $this->Comment->find('count',array(
            'conditions'=>array(
                'data_id'=>$pid,
                'status'=>1,
                'is_shichi_vote'=>1
            )
        ));
        $comment_count = $this->Comment->find('count',array(
            'conditions'=>array(
                'data_id'=>$pid,
                'status'=>1
            )
        ));
        $this->set('shi_chi_comment_count',$shi_chi_comment_count);
        $this->set('comment_count',($comment_count-$shi_chi_comment_count));

        if($this->RequestHandler->isMobile()){
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

        $currUid = $this->currentUser['id'];
        list($price, $afford_for_curr_user, $left_cur_user, $total_left) = $this->calculate_price_limitation($pid, $currUid);

        //get specs from database
        $product_spec_group = $this->ProductSpecGroup->extract_spec_group_map($this->viewdata['Product']['id'],'spec_names');
        $this->set('product_spec_group',json_encode($product_spec_group));
        $product_price_range = Hash::extract($product_spec_group,'{s}.price');
        if(!empty($product_price_range)){
            $min_product_price = min($product_price_range);
            $max_product_price = max($product_price_range);
            if($min_product_price!=$max_product_price){
                $product_price_range = min($product_price_range).'-'.max($product_price_range);
                $this->set('product_price_range',$product_price_range);
            }
        }
        $specs_map = $this->ProductSpecGroup->get_product_spec_json($this->viewdata['Product']['id']);
        if (!empty($specs_map['map'])) {
            $str = '<script>var _p_spec_m = {';
            foreach($specs_map['map'] as $mid => $mvalue) {
                $str .= '"'.$mvalue.'":"'. $mid ."\",";
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

        if($currUid){
            $this->loadModel('ViewedProduct');
            $browsingHistoryProductsData = $this->ViewedProduct->find('first',
                array(
                    'conditions' => array('uid' => $currUid),
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
        if(count($browsing_history)>30){
            array_shift($browsing_history);
        }
        array_push($browsing_history,$this->viewdata['Product']['id']);
        if($currUid){
            $this->ViewedProduct->id = $viewedDataId;
            $this->ViewedProduct->save(array(
                'uid'=>$currUid,
                'browsing_history'=>join($browsing_history,',')
            ));
        }
        $this->Session->write('BrowsingHistory',$browsing_history);

        $product_consignment_date = $this->get_product_consignment_date($pid);
        if(empty($product_consignment_date)){
            $consignment_dates = consignment_send_date($pid);
            if(!empty($consignment_dates)){
                $this->set('consignment_dates', $consignment_dates);
            }
        }else{
            $this->set('product_consignment_date',$product_consignment_date);
        }

        $is_limit_ship = ClassRegistry::init('ShipPromotion')->is_limit_ship($pid);
        $this->set('limit_ship', $is_limit_ship);

        $this->set('category_control_name', 'products');
        if($this->is_weixin()){
            $this->prepare_wx_sharing($currUid, $pid);
        }
        $this->setTraceFromData('product',$pid);

    }

    /**
     * @param $ship_fee
     * @return string
     */
    private function ship_desc_1($ship_fee) {
        return ($ship_fee == 0 ? '包邮' : '邮费' . CakeNumber::precision($ship_fee / 100, 2) . '元');
    }

    /**
     * @param $productId
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


    function guess_product_detail(){
        $this->pageTitle = '商品详情';
        $this->set('hideNav',true);
    }

    function setFrom(){
        $from = $_REQUEST['from'];
        if(!empty($from)){
            $this->set('from',$from);
        }
        $data_id = $_REQUEST['data_id'];
        if(!empty($data_id)){
            $this->set('data_id',$data_id);
        }
    }


    /**
     * @param $currUid
     * @param $pid
     */
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

    /**
     * @param $pid
     * @param $currUid
     * @return array
     */
    protected function calculate_price_limitation($pid, $currUid) {

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


        $this->log("view product:".$pid.", specialLists=".json_encode($specialLists), LOG_INFO);

        $use_special = false;
        $price = $this->viewdata['Product']['price'];
        if (!empty($special) && $special['special']['special_price'] >= 0) {

            $special_rg = range_by_special($special);
            if (empty($special_rg) || in_range($special_rg)) {
                //TODO: check time (current already checked)
                //CHECK time limit!!!!
                list($afford_for_curr_user, $left_cur_user, $total_left) =
                    calculate_afford($pid, $currUid, $special['special']['limit_total'], $special['special']['limit_per_user'], $special_rg);

                $this->log('view product afford(special): for_curr_user=' . $afford_for_curr_user . ', left_cur_user=' . $left_cur_user . ', total_left=' . $total_left . ', range=' . json_encode($special_rg) . ', uid=' . $currUid, LOG_INFO);

                $promo_name = $special['name'];
                $special_least_num = $special['special']['least_num'];
                $special_price = $special['special']['special_price'] / 100;
                App::uses('CakeNumber', 'Utility');
                $promo_desc = ($special_least_num > 0 ? '满' . $special_least_num . '件' : '') . '￥' . CakeNumber::precision($special_price, 2);
                if ($special['special']['limit_total'] > 0) {
                    $promo_desc .= ' 限' . $special['special']['limit_total'] . '件';
                }
                if ($special['special']['limit_per_user'] > 0) {
                    $promo_desc .= ' 每人限' . $special['special']['limit_per_user'] . '件';
                }
                if ($afford_for_curr_user) {
                    if ($special_least_num <= 0) {
                        $price = $special_price;
                    }
                    $use_special = true;
                } else {
                    $promo_desc .= '(' . ($left_cur_user == 0 ? '您已买过' : '已抢完') . ')';
                }
                $this->set('special_desc', $promo_desc);
                $this->set('special_name', $promo_name);
                $this->set('special_slug', $special['slug']);
                $this->set('show_special_link', $special['visible'] > 0);
            }
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

        return array($price, $afford_for_curr_user, $left_cur_user, $total_left);
    }

}