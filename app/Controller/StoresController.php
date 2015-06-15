<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/28/14
 * Time: 12:45 PM
 */
class StoresController extends AppController
{

    public $uses = array('Product', 'Brand', 'Order','OrderTrack','OrderTrackLog','TrackOrderMap','Cart','User');

    public $components = array('Paginator','ProductSpecGroup','Weixin');


    /* lower case */
    public $allowdPostProductFields = array('id', 'promote_name', 'name', 'coverimg','photo' ,'content', 'published', 'price', 'ship_fee', 'original_price','specs');

    public $brand = null;

    private function checkAccess($refuse_redirect = true)
    {

        if (empty($this->currentUser['id'])) {
            if ($refuse_redirect) {
                $this->__message('您需要先登录才能操作', '/users/login?referer=' . urlencode($_SERVER['REQUEST_URI']));
                return false;
            }
        }

        if ($this->is_admin($this->currentUser['id'])) {
            $brand_id = $_REQUEST['brand_id'];
            if(empty($brand_id)){
                $brand_id = $this->Session->read('admin_brand_id');
            }
            if(!empty($brand_id)){
                $this->brand = $this->find_brand_by_id($brand_id);
                if(empty($this->brand)){
                    $this->__message('商家ID有误', '/');
                    return false;
                }
                //admin user write admin brand id
                $this->Session->write('admin_brand_id',$brand_id);
                return true;
            }else{
                $this->__message('商家ID有误', '/');
                return false;
            }
        }

        $this->brand = $this->find_my_brand($this->currentUser['id']);
        if (empty($this->brand)) {
            if ($refuse_redirect) {
                $this->__message('您没有权限访问相关页面', '/');
                return false;
            }
        }

        $this->set('brand', $this->brand);

        return true;
    }

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = 'store';
        $this->pageTitle = '商家后台';
    }


    public function profile()
    {
        $this->checkAccess();
        $this->set('brand', $this->brand);
        $this->set_profile_info($this->brand['Brand']['weixin_id'], $this->brand['Brand']['notice']);
        $this->set('op_cate', 'profile');
    }

    public function edit_profile()
    {
        $this->checkAccess();
        $this->set('brand', $this->brand);

        if ($this->request->method() == 'POST') {
            $brandId = $this->brand['Brand']['id'];
            $weixin_id = trim($_REQUEST['profile_weixin_id']);
            $notice = trim($_REQUEST['profile_notice']);
            if (empty($weixin_id) || mb_strlen($weixin_id) <= 3) {
                setFlashError($this->Session, '微信Id不能为空，长度不能小于3个字符');
            } else if (mb_strlen($notice) > 30) {
                setFlashError($this->Session, '公告信息不能超过30个汉字');
            } else {
                $this->Brand->updateAll(array('weixin_id' => '\'' . $weixin_id . '\'', 'notice' => '\'' . addslashes(htmlspecialchars($notice)) . '\''), array('id' => $brandId));
                $this->Session->setFlash('保存成功');
                $this->redirect('/s/profile');
            }
            $this->set_profile_info($weixin_id, $notice);
        } else {
            $this->set_profile_info($this->brand['Brand']['weixin_id'], $this->brand['Brand']['notice']);
        }
        $this->set('op_cate', 'profile');
    }

    public function my_story()
    {
        $this->checkAccess();

        if (!empty($this->data)) { //有数据提交
            $brandId = $this->brand['Brand']['id'];
            $story = $this->data['Brand']['content'];
            $this->Brand->id = $brandId;
            $this->Brand->set('content', $story);
            $this->Brand->save();
            $this->Session->setFlash('保存成功');

            $this->redirect(array('action' => 'my_story', $brandId));
        } else {
            $this->data = $this->brand;; //加载数据到表单中
        }

        $this->set('op_cate', 'profile');
    }

    public function index()
    {
        $this->checkAccess();
        if (!empty($this->brand)) {
            $this->set('brand', $this->brand);
            $uid = $this->currentUser['id'];
            if ($uid == $this->brand['Brand']['creator']) {
                $this->loadModel('Oauthbind');
                $bind = $this->Oauthbind->findWxServiceBindByUid($uid);
                if (empty($bind)) {
                    $this->set('should_bind', true);
                }
            }
        } else {
            $this->redirect('/');
        }
    }

    public function add_product()
    {
        $this->checkAccess();

        if (!empty($this->data) && $this->check_product_post()) {
            $this->data['Product']['brand_id'] = $this->brand['Brand']['id'];
            foreach ($this->data['Product'] as &$item) {
                if (is_array($item)) {
                    $item = implode(',', $item); // 若提交的内容为数组，则使用逗号连接各项值保存到一个字段里
                }
            }
            if (!isset($this->data['Product']['published'])) {
                $this->data['Product']['published'] = 1;
//                $this->data['Product']['published'] = 2;
            }
            $this->data['Product']['status'] = IN_CHECK; //默认商家增加的商品进入审核中状态
            $this->data['Product']['deleted'] = DELETED_NO;
            $this->data['Product']['creator'] = $this->currentUser['id'];
            //$this->data['Product']['coverimg'] = trim($this->data['coverimg']);

            if (!isset($this->data['Product']['slug'])) {
                $name = $this->data['Product']['name'];
                $slug = $this->generate_slug($name);
                if (empty($slug)) {
                    $slug = random_str(8);
                }

                $tries = 10;
                $tryingSlug = $slug;
                while ($tries-- > 0) {
                    $proBySlug = $this->Product->findBySlug($tryingSlug);
                    if (!empty($proBySlug)) {
                        $tryingSlug = $slug . '_' . random_str(4);
                    } else {
                        break;
                    }
                }
                $slug = $tryingSlug;

                $this->data['Product']['slug'] = $slug;
            }


            $error = $this->check_product_publish();
            if (!empty($error)) {
                setFlashError($this->Session, $error);
            } else {
                $this->Product->create();
                $p =$this->Product->save($this->data);
                if ($p) {
                    //保存上传的附件；形如 data[Uploadfile][39][id] , data[Uploadfile][39][name]
                    if (isset($this->data['Uploadfile']) && is_array($this->data['Uploadfile'])) {
                        $this->loadModel('Uploadfile');
                        foreach ($this->data['Uploadfile'] as $file) {
                            $this->Uploadfile->create();
                            $fileinfo = array();
                            $fileinfo['id'] = $file['id'];
                            $fileinfo['data_id'] =$p['Product']['id'];
                            // 只修改  data_id
                            $this->Uploadfile->save($fileinfo, true, array('data_id'));
                        }
                    }
                    $this->Session->setFlash(__('The Data has been saved'));

                    $this->redirect(array('action' => 'products'));
                } else {
                    setFlashError($this->Session, __('The Data could not be saved. Please, try again.'));
                }
            }
        }
        $this->set('product_attrs',ProductSpeciality::get_product_attrs());
        $this->set('op_cate', 'products');
    }

    function product_sale_status($id, $on_sale)
    {
        $this->autoRender = false;
        $resp = array();
        $success = false;
        $message = 'ok';
        if ($this->checkAccess(false)) {
            $datainfo = $this->find_product_by_id_and_brandId($id, $this->brand['Brand']['id']);
            if (!empty($datainfo)) {
                $publish_status = $on_sale ? PUBLISH_YES : PUBLISH_NO;
                $this->Product->updateAll(array('published' => $publish_status), array('id' => $id, 'deleted' => DELETED_NO));
                $success = true;
            } else {
                $message = 'no_data_right';
            }
        } else {
            $message = 'no_right';
        }
        $resp = array('msg' => $message, 'success' => $success);
        echo json_encode($resp);
    }

    function edit_product($id)
    {
        $this->checkAccess();
        $datainfo = $this->find_product_by_id_and_brandId($id, $this->brand['Brand']['id']);
        if (empty($datainfo)) {
            throw new ForbiddenException(__('You cannot edit this data'));
        }

        if (!empty($this->data) && $this->check_product_post()) { //有数据提交
            $this->autoRender = false;
            $this->data['Product']['creator'] = $this->currentUser['id'];
            $error = $this->check_product_publish();
            if (!empty($error)) {
                setFlashError($this->Session, $error);
            } else {
                $p =$this->Product->save($this->data);
                if ($p) {
                    //save product spec
                    $this->save_product_spec($id,true);
                    $this->save_product_spec_gorup($id,true);
                    //保存上传的附件；形如 data[Uploadfile][39][id] , data[Uploadfile][39][name]
                    if (isset($this->data['Uploadfile']) && is_array($this->data['Uploadfile'])) {
                        $this->loadModel('Uploadfile');
                        foreach ($this->data['Uploadfile'] as $file) {
                            $this->Uploadfile->create();
                            $fileinfo = array();
                            $fileinfo['id'] = $file['id'];
                            $fileinfo['data_id'] =$p['Product']['id'];
                            // 只修改  data_id
                            $this->Uploadfile->save($fileinfo, true, array('data_id'));
                        }
                    }
                    $this->Session->setFlash(__('The Data has been saved'));
                    //$this->redirect(array('action'=>'index'));
                } else {
                    setFlashError($this->Session, __('The Data could not be saved. Please, try again.'));
                }
            }
            $successinfo = array('success' => __('Edit success'), 'actions' => array('OK' => 'closedialog'));
            //echo json_encode($successinfo);
            //return ;
            $this->redirect(array('action' => 'edit_product', $id));
        } else {
            //get specs
            $this->set('product_attrs',ProductSpeciality::get_product_attrs());
            $specs = $this->get_product_spec($id);
            $specs = Hash::extract($specs,'{n}.ProductSpec');
            $this->set('specs',json_encode($specs));
            //get spec group by pid
            $specGroups = $this->ProductSpecGroup->extract_spec_group_map($id,'spec_names');
            $this->set('specGroups',json_encode($specGroups));
            $this->data = $datainfo; //加载数据到表单中
            $this->loadModel('Uploadfile');
            $uploadFiles=$this->Uploadfile->find('all',array(
                'conditions'=>array(
                        'modelclass'=>'Product',
                        'data_id'=>$id,
                    ),
            ));
            $uploadFiles = Hash::extract($uploadFiles,'{n}.Uploadfile');
            $this->data['Uploadfile']=$uploadFiles;

        }
        $this->set('op_cate', 'products');
    }

    function del_product($id)
    {
        $this->checkAccess();

        $brandId = $this->brand['Brand']['id'];
        $datainfo = $this->find_product_by_id_and_brandId($id, $brandId);
        if (empty($datainfo)) {
            throw new ForbiddenException(__('You cannot delete this data'));
        }

        $this->Product->updateAll(array('deleted' => DELETED_YES), array('id' => $id, 'deleted' => DELETED_NO, 'brand_id' => $brandId));
        $this->Session->setFlash('删除成功');

        if($this->is_admin){
            $this->redirect(array('action' => 'products','?' => array(
                'brand_id' => $this->admin_brand_id
            )));
        }else{
            $this->redirect(array('action' => 'products'));
        }
    }

    //todo 排期上了之后根据排期加功能
    public function get_product_orders_by_date(){
        $this->autoRender=false;
        $product_id = $_REQUEST['product_id'];
        $date_id = $_REQUEST['date_id'];
        $carts = $this->Cart->find('all',array(
            'conditions'=>array(
                'status' => CART_ITEM_STATUS_BALANCED,
                'deleted' => DELETED_NO,
                'consignment_date' => $date_id,
                'product_id' => $product_id
            ),
            'fields' => array(
                'id','order_id'
            )
        ));
        if(!empty($carts)){
            $order_ids = Hash::extract($carts,'{n}.Cart.order_id');
            $orders = $this->Order->find('all',array(
                'conditions' => array(
                    'id' => $order_ids,
                    'status' => ORDER_STATUS_PAID
                )
            ));
            if(!empty($orders)){
                $orders = Hash::extract($orders,'{n}.Order');
            }
            echo json_encode($orders);
        }else{
            echo json_encode(array());
        }
    }

    public function get_order_by_ids(){
        $this->autoRender=false;
        $ids = $_REQUEST['ids'];
        $ids = preg_split('/(,|\n)/',$ids);
        $orders = $this->Order->find('all',array(
            'conditions'=>array(
                'status' => 1,
                'deleted' => 0,
                'id' => $ids
            ),
            'fields' => array(
                'id','consignee_name','consignee_mobilephone','consignee_address'
            )
        ));
        $orders = Hash::extract($orders,'{n}.Order');
        echo json_encode($orders);
    }

    public function delete_order_track_map(){
        $this->autoRender=false;
        $order_id = $_REQUEST['order_id'];
        $track_id = $_REQUEST['track_id'];
        if($this->TrackOrderMap->deleteAll(array('track_id' => $track_id,'order_id' => $order_id),false)){
            $result = array('success'=>true);
        }else{
            $result = array('success'=>false);
        }
        echo json_encode($result);
    }

    public function save_track($trackid=null){
        $post_order_ids = $_REQUEST['order_ids'];
        $post_logs = $_REQUEST['logs'];
        $is_first = false;
        if(!empty($trackid)){
            $this->OrderTrack->id = $trackid;
            $order_track = $this->OrderTrack->find('first',array(
                'id' => $trackid
            ));
            $product_id = $order_track['OrderTrack']['product_id'];
        }else{
            $is_first = true;
            $date = $_REQUEST['date'];
            $order_track['date']=$date;
            $product_id = $_REQUEST['product_id'];
            $order_track['product_id']=$product_id;
            $order_track = $this->OrderTrack->save($order_track);
            //todo add fail
            $trackid = $order_track['OrderTrack']['id'];
        }
        if($order_track){
            $post_order_ids = json_decode($post_order_ids,true);
            $track_order_map = array();
            foreach($post_order_ids as $id){
                $track_order_map[]=array('track_id'=>$trackid,'order_id'=>$id);
            }
            $this->TrackOrderMap->saveAll($track_order_map);
            $post_logs = json_decode($post_logs,true);
            $track_log = array();
            foreach($post_logs as $log){
                $track_log[]=array(
                    'log' => $log,
                    'track_id' => $trackid,
                    'date' => date('Y-m-d h:i:s')
                );
            }
            $this->OrderTrackLog->saveAll($track_log);
        }
        $p = $this->Product->find('first',array(
            'conditions'=>array(
                'id' => $product_id
            )
        ));
        $p_name = $p['Product']['name'];
        $this->send_track_log($trackid,$product_id,$is_first);
        if($this->is_admin){
            $this->redirect('/stores/view_track/'.$product_id.'.html?productname='.$p_name.'&brand_id='.$this->admin_brand_id);
        }else{
            $this->redirect('/stores/view_track/'.$product_id.'.html?productname='.$p_name);

        }
    }

    public function delete_track_log($id){
        $this->OrderTrack->id=$id;
        $this->OrderTrack->saveField('deleted',1);
        $p_id = $_REQUEST['product_id'];
        $p_name = $_REQUEST['product_name'];
        $this->redirect('/stores/view_track/'.$p_id.'.html?productname='.$p_name);
    }

    public function add_track_log(){
        $product_id = $_REQUEST['product_id'];
        $this->set('product_id',$product_id);
        $this->setConsignmentDate($product_id);

    }

    public function edit_track_log($id){
        $date = $_REQUEST['date'];
        $this->set('order_track_id',$id);
        $this->set('date',$date);
        $order_ids = $this->TrackOrderMap->find('all',array(
            'conditions'=>array(
                'track_id' => $id
            )
        ));
        $order_ids = Hash::extract($order_ids,'{n}.TrackOrderMap.order_id');
        $orders = $this->Order->find('all',array(
            'conditions'=>array(
                'id' => $order_ids,
                'deleted' => 0,
            ),
            'fields' => array(
                'id','consignee_name','consignee_mobilephone','consignee_address'
            )
        ));
        $orders = Hash::extract($orders,'{n}.Order');
        $this->set('orders',$orders);
        $track_logs = $this->OrderTrackLog->find('all',array(
            'conditions' => array(
                'track_id' => $id
            ),
            'order' => 'date desc'
        ));
        $track_logs = Hash::extract($track_logs,'{n}.OrderTrackLog');
        $this->set('track_logs',$track_logs);
    }

    public function view_track($productId) {
        $page = 1;
        $pagesize = 30;
        $cond = array('product_id'=>$productId, 'deleted' => DELETED_NO);
        $total = $this->OrderTrack->find('count', array(
            'conditions' => $cond
        ));
        $datalist = $this->OrderTrack->find('all', array(
            'conditions' => $cond,
            'fields' => array('id', 'date'),
            'order' => 'date desc'
        ));
        foreach ($datalist as &$data) {
            $trackId = $data['OrderTrack']['id'];
            $orderCount = $this->TrackOrderMap->find('count', array(
                'conditions' => array('track_id' => $trackId)
            ));
            $data['OrderTrack']['order_count']=$orderCount;
            $lastLog = $this->OrderTrackLog->find('first', array(
                'conditions' => array('track_id' => $trackId),
                'order' => 'date desc'
            ));
            $data['OrderTrack']['last_log']=$lastLog['OrderTrackLog']['log'];
            $data['OrderTrack']['date']= date('Y-m-d',strtotime($data['OrderTrack']['date']));
        }
        $productName = $_REQUEST['productname'];
        $page_navi = getPageLinks($total, $pagesize, '/tracklog/mine', $page);
        $this->set('product_id',$productId);
        $this->set('datalist', $datalist);
        $this->set('productName',$productName);
        $this->set('priduct_id',$productId);
        $this->set('page_navi', $page_navi);
    }

    public function products()
    {
        $this->checkAccess();

        $page = 1;
        if($_REQUEST['page']){
            $page = intval($_REQUEST['page']);
        }
        $pagesize = intval(Configure::read('Product.pagesize'));
        if (!$pagesize) {
            $pagesize = 15;
        }

        $cond = array('brand_id' => $this->brand['Brand']['id'], 'deleted' => DELETED_NO);
        $total = $this->Product->find('count', array('conditions' => $cond));
        $datalist = $this->Product->find('all', array(
            'conditions' => $cond,
            'fields' => array('id', 'name', 'price', 'published', 'coverimg', 'deleted', 'saled', 'storage', 'updated', 'slug','sort_in_store'),
            'order' => array('published desc','updated desc'),
            'limit' => $pagesize,
            'offset' => ($page-1)*$pagesize
        ));

        $p_ids = Hash::extract($datalist,'{n}.Product.id');

        $this->loadModel('ConsignmentDate');
        $dates = $this->ConsignmentDate->find('all', array(
            'conditions' => array(
                'product_id' => $p_ids,
                'published' => PUBLISH_YES
            ),
            'order' => 'send_date desc'
        ));
        $dates = Hash::combine($dates,'{n}.ConsignmentDate.product_id','{n}.ConsignmentDate');
        $this->set('dates', $dates);

        $page_navi = getPageLinks($total, $pagesize, '/stores/products', $page);
        $this->set('datalist', $datalist);
        $this->set('page_navi', $page_navi);
        $this->set('op_cate', 'products');
        $this->set('brand_id', $this->brand['Brand']['id']);
    }

    /**
     * 生成链接的slug别名
     */
    function genSlug()
    {
        $success = false;
        if ($this->checkAccess(false)) {
            $word = $_REQUEST['word'];
            $slug = $this->generate_slug($word);
            $success = false;
        } else {
            $slug = 'access_refused';
            $success = true;
        }

        $this->autoRender = false;
        echo json_encode(array('success' => $success, 'slug' => $slug));
    }

    /**
     * @param $id
     * @param $brandId
     * @return mixed
     */
    private function find_product_by_id_and_brandId($id, $brandId)
    {
        return $this->Product->find('first', array('conditions' => array('id' => $id, 'brand_id' => $brandId)));
    }


    public function orders()
    {
        $this->__business_orders(
            array(ORDER_STATUS_PAID, ORDER_STATUS_WAITING_PAY, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED));
    }

    function wait_shipped_orders()
    {
        $this->__business_orders(array(ORDER_STATUS_PAID));
        if ($this->brand && $this->brand['Brand']['id'] == BRAND_ID_CAKE) {
            $this->set('default_ship_id', SHIPTYPE_ID_ZITI);
        }
        $this->render("orders");
    }

    function wait_paid_orders()
    {
        $this->__business_orders(array(ORDER_STATUS_WAITING_PAY));
        $this->render("orders");
    }


    function shipped_orders()
    {
        $this->__business_orders(array(ORDER_STATUS_SHIPPED));
        $this->render("orders");
    }


    function signed_orders()
    {
        $this->__business_orders(array(ORDER_STATUS_RECEIVED));
        $this->render("orders");
    }

    public function orders_export()
    {
        $this->__business_orders_export(
            array(ORDER_STATUS_PAID, ORDER_STATUS_WAITING_PAY, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED));
    }

    function wait_shipped_orders_export()
    {
        $this->__business_orders_export(array(ORDER_STATUS_PAID));
        $this->render("orders_export");
    }

    function wait_paid_orders_export()
    {
        $this->__business_orders_export(array(ORDER_STATUS_WAITING_PAY));
        $this->render("orders_export");
    }


    function shipped_orders_export()
    {
        $this->__business_orders_export(array(ORDER_STATUS_SHIPPED));
        $this->render("orders_export");
    }


    function signed_orders_export()
    {
        $this->__business_orders_export(array(ORDER_STATUS_RECEIVED));
        $this->render("orders_export");
    }


    function product_in_check()
    {
        $this->__business_products(array(IN_CHECK));
        $this->render('products');
    }


    protected function __business_orders($onlyStatus = array())
    {
        $creator = $this->currentUser['id'];
        $this->checkAccess();
        $brand = $this->brand;
        if (!empty($brand)) {
            $brand_id = $brand['Brand']['id'];
            $this->set('is_business', true);
        } else {
            $this->__message('只有合作商家才能查看商家订单，正在为您转向个人订单', '/orders/mine');
            return;
        }
        $cond = array('brand_id' => $brand_id,
            'type' => array(ORDER_TYPE_DEF, ORDER_TYPE_GROUP_FILL, ORDER_TYPE_TUAN, ORDER_TYPE_TUAN_SEC),
            'NOT' => array(
                'status' => array(ORDER_STATUS_CANCEL)
            )
        );
        $cond['status'] = $onlyStatus;
        $total_count = $this->Order->find('count', array('conditions' => $cond));
        $wait_ship_cond = $cond;
        $wait_ship_cond['status'] = array(ORDER_STATUS_PAID);
        $total_wait_ship_count = $this->Order->find('count', array('conditions' => $wait_ship_cond));

        $tuan_id = $_REQUEST['tuan_id'];
        if($tuan_id!=null&&$tuan_id!='-1'){
            //load tuanbuy for query
            $this->loadModel('TuanBuying');
            $tbs = $this->TuanBuying->find('all',array(
                'conditions' => array(
                    'tuan_id' => $tuan_id
                ),
                'fields' => array(
                    'id'
                )
            ));
            $tb_ids = Hash::extract($tbs,'{n}.TuanBuying.id');
            if(!empty($tb_ids)){
                $cond['member_id'] = $tb_ids;
            }
            $this->set('tuan_id',$tuan_id);
        }

        $mark_date = $_REQUEST['mark_date'];
        $mark_tip = $_REQUEST['mark_tip'];
        if($mark_date!=null&&$mark_date!="all"&&$mark_tip!=null&&$mark_tip!="all"){
            $cond['mark_ship_date'] = $mark_date;
            $cond['ship_mark'] = $mark_tip;
            $this->set('mark_date',$mark_date);
            $this->set('mark_tip',$mark_tip);
        }
        //代发货订单
        if(in_array(ORDER_STATUS_PAID,$onlyStatus)){
            //load order tags
            $result  = $this->Order->query('SELECT ship_mark,mark_ship_date,count(mark_ship_date) AS total_count FROM cake_orders WHERE  brand_id='.$brand_id.' AND status='.ORDER_STATUS_PAID.' GROUP BY ship_mark,mark_ship_date');
            $tags = array();
            //全部
            $tags[] = array(
                'mark_date' => "all",
                'mark_tip' => "all",
                'count' => $total_wait_ship_count
            );
            //标记
            foreach($result as $order_mark){
                $mark_date = $order_mark['cake_orders']['mark_ship_date'];
                $mark_tip = $order_mark['cake_orders']['ship_mark'];
                if($mark_date!=null){
                    $mark_date = date(FORMAT_DATE,strtotime($mark_date));
                }
                $tags[] = array(
                    'mark_date' => $mark_date,
                    'mark_tip' => $mark_tip,
                    'count' => $order_mark[0]['total_count']
                );
            }
            $this->set('tags',$tags);
            $this->loadModel('TuanTeam');
            $tuanTeams = $this->TuanTeam->find('all');
            $this->set('tuanTeams',$tuanTeams);
        }

        $this->Paginator->settings = array(
            'conditions' => $cond,
            'limit' => 15,
            'order' => array(
                'Order.id' => 'desc'
            )
        );

        $orders = $this->Paginator->paginate('Order');

        $ids = array();
        foreach ($orders as $o) {
            $ids[] = $o['Order']['id'];
        }

        $order_carts = array();
        if(!empty($ids)){
            $this->loadModel('Cart');
            $Carts = $this->Cart->find('all', array(
                'conditions' => array(
                    'order_id' => $ids,
                )));
            foreach ($Carts as $c) {
                $order_id = $c['Cart']['order_id'];
                if (!isset($order_carts[$order_id])) {
                    $order_carts[$order_id] = array();
                }
                $order_carts[$order_id][] = $c;
            }

            $spec_ids = Hash::extract($Carts,'{n}.Cart.specId');
            $spec_ids = array_unique($spec_ids);
            if(!empty($spec_ids)){
                $this->loadModel('ProductSpecGroup');
                $spec_groups = $this->ProductSpecGroup->find('all',array(
                    'conditions' => array(
                        'id' => $spec_ids
                    )
                ));
                if(!empty($spec_groups)){
                    $spec_groups = Hash::combine($spec_groups,'{n}.ProductSpecGroup.id','{n}.ProductSpecGroup.spec_names');
                    $this->set('spec_groups', $spec_groups);
                }
            }
        }

        $this->set('orders', $orders);
        $this->set('total_count', $total_count);
        $this->set('total_wait_ship_count', $total_wait_ship_count);
        $this->set('order_carts', $order_carts);
        $this->set('ship_type', ShipAddress::ship_type_list());
        $this->set('creator', $creator);
        if (sizeof($onlyStatus) > 1) {
            $this->set('status', -1);
        } else {
            $this->set('status', $onlyStatus[0]);
        }
        $this->set('op_cate', 'orders');
    }

    protected function __business_orders_export($onlyStatus = array())
    {
        $creator = $this->currentUser['id'];
        $this->checkAccess();
        $brand = $this->brand;
        if (!empty($brand)) {
            $this->set('is_business', true);
        } else {
            $this->__message('只有合作商家才能查看商家订单，正在为您转向个人订单', '/orders/mine');
            return;
        }

        $cond = array('brand_id' => $this->brand['Brand']['id'],
            'type' => array(ORDER_TYPE_DEF, ORDER_TYPE_GROUP_FILL, ORDER_TYPE_TUAN, ORDER_TYPE_TUAN_SEC),
            'NOT' => array(
            'status' => array(ORDER_STATUS_CANCEL)
        ));
        $cond['status'] = $onlyStatus;
        $mark_date = $_REQUEST['mark_date'];
        $mark_tip = $_REQUEST['mark_tip'];
        if($mark_date!=null&&$mark_date!="all"&&$mark_tip!=null&&$mark_tip!="all"){
            $cond['mark_ship_date'] = $mark_date;
            $cond['ship_mark'] = $mark_tip;
            $this->set('mark_date',$mark_date);
            $this->set('mark_tip',$mark_tip);
        }
        $orders = $this->Order->find('all', array(
            'order' => 'id desc',
            'conditions' => $cond,
        ));

        $ids = array();
        foreach ($orders as $o) {
            $ids[] = $o['Order']['id'];
        }
        $this->loadModel('Cart');
        $Carts = $this->Cart->find('all', array(
            'conditions' => array(
                'order_id' => $ids,
            )));
        $order_carts = array();
        foreach ($Carts as $c) {
            $order_id = $c['Cart']['order_id'];
            if (!isset($order_carts[$order_id])) {
                $order_carts[$order_id] = array();
            }
            $order_carts[$order_id][] = $c;
        }
        //查规格
        $spec_ids = Hash::extract($Carts,'{n}.Cart.specId');
        $spec_ids = array_unique($spec_ids);
        if(count($spec_ids)!=1 || !empty($spec_ids[0])){
            $this->loadModel('ProductSpecGroup');
            $spec_groups = $this->ProductSpecGroup->find('all',array(
                'conditions' => array(
                    'id' => $spec_ids
                )
            ));
            $spec_groups = Hash::combine($spec_groups,'{n}.ProductSpecGroup.id','{n}.ProductSpecGroup.spec_names');
            $this->set('spec_groups', $spec_groups);
        }
        //查排期
        $consign_ids = array_unique(Hash::extract($Carts,'{n}.Cart.consignment_date'));
        if(count($consign_ids)!=1 || !empty($consign_ids[0])){
            $this->loadModel('ConsignmentDate');
            $consign_dates = $this->ConsignmentDate->find('all',array(
                'conditions' => array(
                    'id' => $consign_ids
                )
            ));
            $consign_dates = Hash::combine($consign_dates,'{n}.ConsignmentDate.id','{n}.ConsignmentDate.send_date');
            $this->set('consign_dates', $consign_dates);
        }
        //团购发货时间
        $tuan_ids = array();
        foreach($orders as $order){
            if($order['Order']['type'] == ORDER_TYPE_TUAN){
                if(!in_array($order['Order']['member_id'], $tuan_ids)){
                    $tuan_ids[] = $order['Order']['member_id'];
                }
            }
        }
        if(!empty($tuan_ids)){
            $this->loadModel('TuanBuying');
            $tuan_consign_times =$this->TuanBuying->find('list', array(
                'conditions' => array('id' => $tuan_ids),
                'fields' => array('id', 'consign_time')
            ));
            $this->set('tuan_consign_times', $tuan_consign_times);
        }
        $this->set('orders', $orders);
        $this->set('order_carts', $order_carts);
        $this->set('ship_type', ShipAddress::ship_type_list());
        $this->set('creator', $creator);
    }

    private function set_profile_info($weixinId, $notice)
    {
        $this->set('profile_weixin_id', $weixinId);
        $this->set('profile_notice', $notice);
    }

    /**
     * @param $word
     * @return string
     */
    private function generate_slug($word)
    {
        return generate_slug($word);
    }

    /**
     * Set flash message if checking failed
     *
     * @return string true if passed
     */
    private function check_product_post()
    {

        foreach ($this->data['Product'] as $key => $item) {
            if (array_search($key, $this->allowdPostProductFields) === FALSE) {
                $error = "不能编辑特定信息:$key";
                break;
            }
        }

        if (mb_strlen($this->data['Product']['name'], "UTF-8") > VAL_PRODUCT_NAME_MAX_LEN) {
            $error = '名称不能长于' . VAL_PRODUCT_NAME_MAX_LEN . '个字符';
        }

        if (!empty($error)) {
            setFlashError($this->Session, $error);
        }

        return empty($error);
    }

    /**
     * @return string error message
     */
    private function check_product_publish()
    {
        if (isset($this->data['Product']['brand_id'])
            && $this->data['Product']['brand_id'] != $this->brand['Brand']['id']
        ) {
            $error = "不能编辑所属商家Id";
        } else if ($this->data['Product']['published'] == PUBLISH_YES) {
            if (empty($this->data['Product']['coverimg'])) {
                $error = '上架产品的图片不能为空';
            } else if (empty($this->data['Product']['name'])) {
                $error = '请设置产品的标题，最多不超过8个字';
            } else if (empty($this->data['Product']['price']) || $this->data['Product']['price'] < 0.01) {
                $error = '上架产品的价格最低为1分钱';
            }
        }

        return $error;
    }

    /**
     * @param $userId
     * @return mixed
     */
    private function find_my_brand($userId)
    {
        return $this->Brand->find('first', array('conditions' => array(
            'creator' => $userId,
            'deleted' => DELETED_NO,
        )));
    }

    private function find_brand_by_id($id){
        return $this->Brand->find('first', array('conditions' => array(
            'id' => $id,
        )));
    }

    /**店内商品排序
    */
    public function product_sort(){
        $this->autoRender = false;
        $this->checkAccess();
        $pid = intval($_REQUEST['pid']);
        $weight = intval($_REQUEST['weight']);
        $brand_id=$this->Product->find('first', array(
            'conditions' => array('id'=>$pid),
            'fields'=>'brand_id'
        ));
        if($brand_id['Product']['brand_id'] != $this->brand['Brand']['id']){
            throw new ForbiddenException(__('You cannot edit this data'));
        }else{
            $this->loadModel('Product');
            $this->Product->updateAll(array('sort_in_store' => $weight), array('id' => $pid));
            echo json_encode(array('success'=> true, 'weight'=>$weight));
        }
    }

    /**
     * 店铺红包
     */

    public function share_offers() {
        $this->checkAccess();
        $this->loadModel('ShareOffer');
        $this->set('brand',$this->brand);
        $brand_id = $this->brand['Brand']['id'];
//        $this->log('id'.json_encode($brand_id));

        $cond = array('brand_id' => $brand_id,'deleted' => DELETED_NO);
        $store_offer = $this->ShareOffer->find('all',array(
            'conditions' =>$cond,
            'order' =>'created desc',
            'fields' => array('id','name','introduct','deleted','start','end','valid_days','avg_number','is_default')));
        $this->set('store_offers',$store_offer);
//        $this->log('ss'.json_encode($store_offer));


        if($this->request->is('post')){
            $shareNum = $_REQUEST['shareNum'];
            $shareOfferId = $_REQUEST['shareOfferId']; $this->log('shareNum'.json_encode($shareNum));
            $share_avg_num = $_REQUEST['share_avg_num'];
            $brand_id = $this->brand['Brand']['id'];

            $toShareNum = $shareNum * $share_avg_num;
            $brandId = $this->ShareOffer->find('first',array('conditions' => array('id' => $shareOfferId)));
            if ($brandId['ShareOffer']['brand_id'] != $brand_id) {
                throw new ForbiddenException(__('You cannot edit this data'));
            } else {
                $user_id = $this->Brand->find('first',array('conditions' => array('id' => $brand_id)));
                $uid = $user_id['Brand']['creator'];
                $pub_share_offers = $this->ShareOffer->add_shared_slices($uid,$shareOfferId,$toShareNum);  $this->log('share_offer'.json_encode($pub_share_offers));
                $this->Session->setFlash(__('红包发布成功,请到移动端个人中心查看我的红包'));

            }
        }

        $this->set('op_cate','share_offers');




    }


    /**
     * 店铺红包设置
     */
     public function add_share_offers() {
        $this->checkAccess();
        $this->set('brand',$this->brand);
        $this->loadModel('ShareOffer');
        $brand_id = $this->brand['Brand']['id'];
        $store_offer = $this->ShareOffer->find('first',array('conditions' =>array('brand_id' => $brand_id,'is_default' =>1),'fields' => array('is_default')));
        if (!empty($this->data)) {
            if (strtotime($this->data['ShareOffer']['start'])>strtotime($this->data['ShareOffer']['end'])) {
                $this->Session->setFlash(__('开始时间必须小于等于结束时间'));
                $this->redirect(array('action' => 'edit_share_offers'));
                }
            $this->data['ShareOffer']['brand_id'] = $this->brand['Brand']['id'];
            if($this->ShareOffer->save($this->data)) {
//                if ($this->data['ShareOffer']['published'] == 1) {
//                    $datainfo = $this->ShareOffer->find('first',array('conditions' =>array('brand_id' => $brand_id,'published' => 1,'name' => $this->data['ShareOffer']['name'],'share_num' => $this->data['ShareOffer']['share_num'])));
//                    $this->log('hi'.json_encode($datainfo));
//                    $shareOfferId = $datainfo['ShareOffer']['id'];
//                    $shareNum = $this->data['ShareOffer']['share_num'];
//                    $share_avg_num = $this->data['ShareOffer']['avg_number'];
//                    $this->publish_share_offers($shareOfferId,$shareNum,$share_avg_num);
//                }else{
              $this->Session->setFlash(__('The data has been saved'));

              $this->redirect(array('action' => 'share_offers'));

            }else {
              setFlashError($this->Session, __('The Data could not be saved. Please, try again.'));
            }
        }

         $this->set('store_offer',$store_offer);
         $this->set('op_cate','share_offers');
     }

    /**
     * 店铺红包编辑
     */

    public function edit_share_offers($id) {
       $this->checkAccess();
        $brand_id = $this->brand['Brand']['id'];
        $datainfo = $this->find_share_offers_by_id_and_brandid($id,$brand_id);

        $store_offer = $this->ShareOffer->find('first',array('conditions' =>array('id' => $id),'fields' => array('is_default')));
        $this->log('datainfo'.json_encode($datainfo));
        if (empty($datainfo)) {
            throw new ForbiddenException(__('You cannot edit this data'));
        }
        if (!empty($this->data)) {
            $this->autoRender = false;
            if ($this->ShareOffer->save($this->data)){
            $this->Session->setFlash(__('The data has been saved'));
            } else {
                setFlashError($this->Session, __('The Data could not be saved. Please, try again.'));
            }
            $this->redirect(array('action' =>'share_offers'));
        } else {
           $this->data = $datainfo;
//           $this->data['ShareOffer']['start']= date('Y-m-d',strtotime($datainfo['ShareOffer']['start']));
//
//             $this->log('time'.json_encode($this->data['ShareOffer']['start']));

        }
        $this->set('store_offer',$store_offer); $this->log('store_offer'.json_encode($store_offer));
        $this->set('opt','share_offers');
    }

    /**
     * 删除红包
     * @param $id
     * @throws ForbiddenException
     */

    public function delete_share_offers($id) {

       $this->checkAccess();
       $brand_id = $this->brand['Brand']['id'];
       $datainfo = $this->find_share_offers_by_id_and_brandid($id,$brand_id);

        if (empty($datainfo)) {
            throw new ForbiddenException(__('You cannot edit this data'));
        }
        $this->ShareOffer->updateAll(array('deleted' => DELETED_YES),array('id' => $id,'deleted' => DELETED_NO,'brand_id' => $brand_id));
        $this->Session->setFlash(__('删除成功'));
        $this->redirect(array('action' => 'share_offers'));

    }

    /*
     * 根据ID和brandId 查找红包信息
     * @param $id
     * @param $brand_id
     * @return mixed
     */
    private function find_share_offers_by_id_and_brandid ($id,$brand_id) {
        $this->loadModel('ShareOffer');
        return $this->ShareOffer->find('first',array('conditions' => array('id' =>$id,'brand_id' => $brand_id)));
    }


    /**
     * 产品排期
     * @param $action
     */
    public function consignment_dating($action) {
        $this->checkAccess();
        $this->pageTitle = '设置可选的发货日期';
        if($this->RequestHandler->isMobile()){
            $this->set('is_mobile',true);
        }
        $product_id = $_REQUEST['p_id'];
        $this->set('p_id',$product_id);
        $p = $this->Product->find('first',array(
            'conditions' => array(
                'id' => $product_id
            )
        ));
        $p_name = $p['Product']['name'];
        $this->set('p_name',$p_name);
        $this->loadModel('ConsignmentDate');
        if ("list" == $action) {
            $dates = $this->ConsignmentDate->find('all', array(
                'conditions' => array(
                    'product_id' => $product_id
                ),
                'order' => 'send_date desc'
            ));
            $this->set('dates', $dates);
            return;
        }

        if ("add" == $action) {
            $this->ConsignmentDate->id = null;
            $send_date = $_REQUEST['send_date'];
            $published = ($_REQUEST['published'] == PUBLISH_YES);
            if ($send_date) {
                $found = $this->ConsignmentDate->find('first', array(
                    'conditions' => array('send_date' => $send_date,'product_id' => $product_id)
                ));
                if (!empty($found)) {
                    setFlashError($this->Session, '发货日期已存在, 不能重复添加');
                } else {
                    $data = array();
                    $data['ConsignmentDate']['send_date'] = $send_date;
                    if($published == 1){
                        $num = $this->ConsignmentDate->find('count', array(
                            'conditions' => array('published' => PUBLISH_YES,'product_id' => $product_id)
                        ));
                        if($num>=5){
                            $data['ConsignmentDate']['published'] = 0;
                            setFlashError($this->Session, '在排期只能日期只能有五个,排期已经不允许排期');
                        }else{
                            $data['ConsignmentDate']['published'] = $published;
                        }
                    }
                    $data['ConsignmentDate']['product_id'] = $product_id;
                    $this->ConsignmentDate->save($data);
                }
            } else {
                setFlashError($this->Session, '发货日期不能为空');
            }
        } else if ("delete" == $action) {
            $id = intval($_REQUEST['id']);
            $this->ConsignmentDate->delete($id);
        } else if ("publish" == $action) {
            $num = $this->ConsignmentDate->find('count', array(
                'conditions' => array('published' => PUBLISH_YES,'product_id' => $product_id)
            ));
            if($num>=5){
                setFlashError($this->Session, '在排期只能日期只能有五个,排期已经不允许排期');
            }else{
                $id = intval($_REQUEST['id']);
                $data = array();
                $data['ConsignmentDate']['published'] = PUBLISH_YES;
                $data['ConsignmentDate']['id'] = $id;
                $this->ConsignmentDate->save($data);
            }

        } else if ("unpublish" == $action) {
            $id = intval($_REQUEST['id']);
            $data = array();
            $data['ConsignmentDate']['published'] = PUBLISH_NO;
            $data['ConsignmentDate']['id'] = $id;
            $this->ConsignmentDate->save($data);
        }
        $this->redirect('/stores/consignment_dating/list?p_id='.$product_id);
    }

    public function get_product_spec($pid){
        $this->loadModel('ProductSpec');
        $specs = $this->ProductSpec->find('all',array(
            'conditions'=>array(
                'product_id'=>$pid,
                'deleted'=>0
            )
        ));
        return $specs;
    }

    public function save_product_spec($pid,$isEdit=false){
        $this->loadModel('ProductSpec');
        if($isEdit){
            $this->ProductSpec->updateAll(
                array('deleted'=>1),
                array('product_id'=>$pid)
            );
        }
        $data = array();
        //todo product max spec is 3 move to bootstrap.php
        foreach(range(1,3) as $index){
            $p_attr = $_REQUEST['spec-'.$index];
            $p_tag=$_REQUEST['tags-'.$index];
            if(!empty($p_attr)&&!empty($p_tag)&&$p_attr!='0'){
                $tag_array = explode(',',$p_tag);
                foreach($tag_array as $tag){
                    if($isEdit){
                        if(!$this->spec_is_in_database($pid,$tag,$p_attr)){
                            $data[] = array('name'=>$tag,'product_id'=>$pid,'attr_id'=>$p_attr);
                        }
                    }else{
                        $data[] = array('name'=>$tag,'product_id'=>$pid,'attr_id'=>$p_attr);
                    }
                }
            }
        }
        $this->ProductSpec->saveAll($data);
    }
    //save spec group
    public function save_product_spec_gorup($pid,$isEdit=false){
        $this->loadModel('ProductSpecGroup');
        if($isEdit){
            $this->ProductSpecGroup->updateAll(
                array('deleted'=>1),
                array('product_id'=>$pid)
            );
        }
        App::uses('CakeNumber', 'Utility');
        $specGroup = json_decode($_REQUEST['spec_table'],true);
        $specs = $this->get_product_spec($pid);
        $specs = Hash::combine($specs,'{n}.ProductSpec.id','{n}.ProductSpec');
        $saveData = array();
        foreach($specGroup as $item){
            $tempSpecIds = array();
            $tempSpecNames = array();
            foreach($item as $key=>$value){
                if($key!='price'&&$key!='stock'){
                    $tempSpecIds[]=$this->extract_spec_id($key,$value,$specs);
                    $tempSpecNames[]=$value;
                }
            }
            $specIds = join(',',$tempSpecIds);
            $specNames = join(',',$tempSpecNames);
            $price = CakeNumber::precision($item['price'], 2);
            $stock = $item['stock'];
            if($isEdit){
                if(!$this->spec_group_is_in_database($pid,$specIds,$specNames,$price,$stock)){
                    $saveData[]=array('product_id'=>$pid,'price'=>$price,'stock'=>$stock,'spec_ids'=>$specIds,'spec_names'=>$specNames);
                }
            }else{
                $saveData[]=array('product_id'=>$pid,'price'=>$price,'stock'=>$stock,'spec_ids'=>$specIds,'spec_names'=>$specNames);
            }
        }
        $this->ProductSpecGroup->saveAll($saveData);
    }
    //约定同一个产品下面规格的名称是唯一的
    public function extract_spec_id($attrId,$name,$specs){
        foreach($specs as $key=>$item){
            if($item['attr_id']==$attrId&&$item['name']==$name){
                return $key;
            }

        }
    }

    public function spec_is_in_database($pid,$name,$atrrId){
        $this->loadModel('ProductSpec');
        $spec = $this->ProductSpec->find('first',array(
            'conditions'=>array(
                'name'=>$name,
                'attr_id'=>$atrrId,
                'product_id'=>$pid
            )
        ));
        //把重复的规格删除标记为0
        if(!empty($spec)){
            $spec['ProductSpec']['deleted']=0;
            $this->ProductSpec->save($spec['ProductSpec']);
            return true;
        }else{
            return false;
        }
    }

    public function spec_group_is_in_database($pid,$spec_ids,$spec_names,$price,$stock){
        $this->loadModel('ProductSpecGroup');
        $specGroup = $this->ProductSpecGroup->find('first',array(
            'conditions'=>array(
                'spec_ids'=>$spec_ids,
                'product_id'=>$pid,
                'spec_names'=>$spec_names
            )
        ));
        if(!empty($specGroup)){
            $specGroup['ProductSpecGroup']['deleted']=0;
            $specGroup['ProductSpecGroup']['price']=$price;
            $specGroup['ProductSpecGroup']['stock']=$stock;
            $this->ProductSpecGroup->save($specGroup['ProductSpecGroup']);
            return true;
        }else{
            return false;
        }
    }

    function send_track_log($trackId,$product_id,$is_first){
        $order_ids = $this->TrackOrderMap->find('all',array(
            'conditions' => array(
                'track_id' => $trackId
            ),
            'fields' => array(
                'order_id'
            )
        ));
        $track_log = $this->OrderTrackLog->find('first',array(
            'conditions' => array(
                'track_id' => $trackId
            ),
            'fields' => array(
                'log'
            ),
            'order' => 'date desc'
        ));
        $product = $this->Product->find('first',array(
            'conditions' => array(
                'id' => $product_id
            ),
            'fields' => array(
                'name'
            )
        ));
        $track_log = $track_log['OrderTrackLog']['log'];
        $order_ids = Hash::extract($order_ids,'{n}.TrackOrderMap.order_id');
        if($is_first){
            $this->Order->updateAll(array('status'=>ORDER_STATUS_SHIPPED), array('id'=>$order_ids,'status'=>ORDER_STATUS_PAID));
        }
        if(!empty($track_log)){
            $orders = $this->Order->find('all',array(
                'conditions' => array(
                    'id' => $order_ids
                ),
                'fields' => array(
                    'id','creator','consignee_mobilephone'
                )
            ));
            $product_name = $product['Product']['name'];
            $msg = $track_log;
            $track_log_msg = '您购买的('.$product_name.')最新状态,'.$track_log;
            foreach($orders as $item){
                $user_id = $item['Order']['creator'];
                $order_id = $item['Order']['id'];
                $order_mobile_phone = $item['Order']['consignee_mobilephone'];
                $cart = $this->Cart->find('first',array(
                    'conditions' => array(
                        'order_id' => $order_id,
                        'product_id' => $product_id
                    ),
                    'fields' => array(
                        'num'
                    )
                ));
                $user_info = $this->User->find('first',array('conditions' => array('id' => $user_id),'fields' => array('mobilephone')));
                $mobile_phone = empty($user_info['User']['mobilephone'])?$order_mobile_phone:$user_info['User']['mobilephone'];
                $num = $cart['Cart']['num'];
                message_send($msg,$mobile_phone);
                if(!$this->Weixin->send_tuan_track_log($user_id,$track_log_msg,$order_id,$product_name,$num)){
                    $this->log('send track msg (track id='.$trackId.' order_id='.$order_id.') fail ');
                }
            }
        }
    }

    function setConsignmentDate($pid){
        $this->loadModel('ConsignmentDate');
        $dates = $this->ConsignmentDate->find('all',array('conditions' => array(
            'product_id' => $pid
        )));
        $this->set('consignment_dates',$dates);
    }

}