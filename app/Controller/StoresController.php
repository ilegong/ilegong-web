<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/28/14
 * Time: 12:45 PM
 */
class StoresController extends AppController
{

    public $uses = array('Product', 'Brand', 'Order','OrderTrack','OrderTrackLog','TrackOrderMap','Cart');

    public $components = array('Paginator','ProductSpecGroup');


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
        $uid = $this->currentUser['id'];
        if ($uid) {
            $this->loadModel('Brand');
            $this->brand = $this->find_my_brand($uid);
            if (!empty($this->brand)) {
                //
                $this->set('brand', $this->brand);
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
        } else {
            $this->redirect('/users/login?referer=/s/index');
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
                    //save product spec
                    $this->save_product_spec($p['Product']['id']);
                    //get spec group
                    $this->save_product_spec_gorup($p['Product']['id']);
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

        $this->redirect(array('action' => 'products'));
    }

    public function get_product_orders_by_date(){
        $this->autoRender=false;
        $product_id = $_REQUEST['product_id'];
        $date = $_REQUEST['date'];
        $end_date = date("Y-m-d",strtotime(date("Y-m-d", strtotime($date)) . " +1 day"));
        $orders = $this->Order->find('all',array(
            'conditions'=>array(
                'status' => 1,
                'deleted' => 0,
                'created between ? and ?' => array($date,$end_date)
            ),
            'fields' => array(
                'id','consignee_name','consignee_mobilephone','consignee_address'
            )
        ));
        $result = array();
        foreach($orders as $item){
            $order_id = $item['Order']['id'];
            $cart_product = $this->Cart->find('first',array(
                'conditions'=>array(
                    'order_id' => $order_id,
                    'product_id' => $product_id
                )
            ));
            if(!empty($cart_product)){
                $result[] = $item;
            }
        }
        if(!empty($result)){
            $result = Hash::extract($result,'{n}.Order');
        }
        echo json_encode($result);
    }


    public function add_track_log(){
        $product_id = $_REQUEST['product_id'];
        $this->set('product_id',$product_id);
        $brand_id = $this->brand['Brand']['id'];
        $this->set('brand_id',$brand_id);
    }

    public function edit_track_log($id){

    }

    public function view_track($productId) {
        $page = 1;
        $pagesize = 30;
        $cond = array('brand_id' => $this->brand['Brand']['id'],'product_id'=>$productId, 'deleted' => DELETED_NO);
        $total = $this->OrderTrack->find('count', $cond);
        $datalist = $this->OrderTrack->find('all', array(
            'conditions' => $cond,
            'fields' => array('id', 'date'),
            'order' => 'date desc'
        ));
        foreach ($datalist as &$data) {
            $trackId = $data['OrderTrackLog']['id'];
            $orderCount = $this->TrackOrderMap->find('count', array(
                'conditions' => array('track_id' => $trackId)
            ));
            $data['order_count']=$orderCount;
            $lastLog = $this->OrderTrackLog->find('first', array(
                'conditions' => array('track_id' => $trackId),
                'order' => 'date desc'
            ));
            $data['last_log']=$lastLog['OrderTrackLog']['log'];
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
        $pagesize = intval(Configure::read('Product.pagesize'));
        if (!$pagesize) {
            $pagesize = 15;
        }

        $total = $this->Product->find('count', array('conditions' => array('brand_id' => $this->brand['Brand']['id'])));
        $cond = array('brand_id' => $this->brand['Brand']['id'], 'deleted' => DELETED_NO);
        $datalist = $this->Product->find('all', array(
            'conditions' => $cond,
            'fields' => array('id', 'name', 'price', 'published', 'coverimg', 'deleted', 'saled', 'storage', 'updated', 'slug','sort_in_store'),
            'order' => 'updated desc'
        ));

        $page_navi = getPageLinks($total, $pagesize, '/products/mine', $page);
        $this->set('datalist', $datalist);
        $this->set('page_navi', $page_navi);
        $this->set('op_cate', 'products');
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

        $this->loadModel('Brand');
        $brand = $this->find_my_brand($creator);
        $this->checkAccess();

        if (!empty($brand)) {
            $brand_id = $brand['Brand']['id'];
            $this->set('is_business', true);
        } else {
            $this->__message('只有合作商家才能查看商家订单，正在为您转向个人订单', '/orders/mine');
            return;
        }

        $cond = array('brand_id' => $brand_id,
            'type' => array(ORDER_TYPE_DEF, ORDER_TYPE_GROUP_FILL, ORDER_TYPE_TUAN),
            'NOT' => array(
            'status' => array(ORDER_STATUS_CANCEL)
        ));
        $cond['status'] = $onlyStatus;

        $wait_ship_cond = $cond;
        $wait_ship_cond['status'] = array(ORDER_STATUS_PAID);
        $total_wait_ship_count = $this->Order->find('count', array('conditions' => $wait_ship_cond));

        $this->Paginator->settings = array(
            'conditions' => $cond,
            'limit' => 15,
            'order' => array(
                'Order.id' => 'desc'
            )
        );

        $orders = $this->Paginator->paginate('Order');

        $total_count = $this->Order->find('count', array('conditions' => $cond));

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

        $this->loadModel('Brand');
        $brand = $this->find_my_brand($creator);

        if (!empty($brand)) {
            $this->set('is_business', true);
        } else {
            $this->__message('只有合作商家才能查看商家订单，正在为您转向个人订单', '/orders/mine');
            return;
        }

        $cond = array('brand_id' => $brand['Brand']['id'],
            'type' => array(ORDER_TYPE_DEF, ORDER_TYPE_GROUP_FILL, ORDER_TYPE_TUAN),
            'NOT' => array(
            'status' => array(ORDER_STATUS_CANCEL)
        ));

        $cond['status'] = $onlyStatus;

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




    public function cake_dating($action) {
        $this->checkAccess();
        $this->pageTitle = '设置可选的发货日期';
        if ($this->brand['Brand']['id'] == BRAND_ID_CAKE) {
            $this->loadModel('CakeDate');
            if ("list" == $action) {
                $dates = $this->CakeDate->find('all', array(
                    'order' => 'send_date desc'
                ));
                $this->set('dates', $dates);
                return;
            }

            if ("add" == $action) {
                $this->CakeDate->id = null;
                $send_date = $_REQUEST['send_date'];
                $published = ($_REQUEST['published'] == PUBLISH_YES);
                if ($send_date) {

                    $found = $this->CakeDate->find('first', array(
                        'conditions' => array('send_date' => $send_date)
                    ));
                    if (!empty($found)) {
                        setFlashError($this->Session, '发货日期已存在, 不能重复添加');
                    } else {
                        $data = array();
                        $data['CakeDate']['send_date'] = $send_date;
                        $data['CakeDate']['published'] = $published;
                        $this->CakeDate->save($data);
                    }
                } else {
                    setFlashError($this->Session, '发货日期不能为空');
                }
            } else if ("delete" == $action) {
                $id = intval($_REQUEST['id']);
                $this->CakeDate->delete($id);
            } else if ("publish" == $action) {
                $id = intval($_REQUEST['id']);
                $data = array();
                $data['CakeDate']['published'] = PUBLISH_YES;
                $data['CakeDate']['id'] = $id;
                $this->CakeDate->save($data);
            } else if ("unpublish" == $action) {
                $id = intval($_REQUEST['id']);
                $data = array();
                $data['CakeDate']['published'] = PUBLISH_NO;
                $data['CakeDate']['id'] = $id;
                $this->CakeDate->save($data);
            }
            $this->redirect('/stores/cake_dating/list');
        } else {
            $this->__message("您没有权限进行操作", '/stores/index');
        }
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

}