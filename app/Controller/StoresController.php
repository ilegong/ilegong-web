<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/28/14
 * Time: 12:45 PM
 */

class StoresController extends AppController {

    public $uses = array('Product', 'Brand', 'Order');

    public $components = array('Paginator');

    public $brand = null;

    private function checkAccess($refuse_redirect = true){

        if(empty($this->currentUser['id'])){
            if ($refuse_redirect) {
                $this->__message('您需要先登录才能操作', '/users/login?referer=' . urlencode($_SERVER['REQUEST_URI']));
                return false;
            }
        }

        $this->brand = $this->Brand->find('first',array('conditions'=>array(
            'creator'=>$this->currentUser['id'],
        )));
        if(empty($this->brand)){
            if ($refuse_redirect) {
                $this->__message('您没有权限访问相关页面', '/');
                return false;
            }
        }

        $this->set('brand', $this->brand);
        $this->pageTitle = '商家后台';

        return true;
    }

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'store';
    }


    public function profile() {
        $this->checkAccess();
        $this->set('brand', $this->brand);
        $this->setProfileInfo($this->brand['Brand']['weixin_id'],  $this->brand['Brand']['notice']);
        $this->set('op_cate', 'profile');
    }

    public function edit_profile() {
        $this->checkAccess();
        $this->set('brand', $this->brand);

        if ($this->request->method() == 'POST') {
            $brandId = $this->brand['Brand']['id'];
            $weixin_id = trim($_REQUEST['profile_weixin_id']);
            $notice = trim($_REQUEST['profile_notice']);
            if (empty($weixin_id) || mb_strlen($weixin_id) <= 3) {
                $this->Session->setFlash('微信Id不能为空，长度不能小于3个字符');
            } else if (mb_strlen($notice) > 30) {
                $this->Session->setFlash('公告信息不能超过30个汉字');
            } else {
                $this->Brand->updateAll(array('weixin_id' => '\''.$weixin_id.'\'', 'notice' => '\''.addslashes(htmlspecialchars($notice)).'\''), array('id' => $brandId));
                $this->Session->setFlash('保存成功');
                $this->redirect('/s/profile');
            }
            $this->setProfileInfo($weixin_id, $notice);
        } else {
            $this->setProfileInfo($this->brand['Brand']['weixin_id'], $this->brand['Brand']['notice']);
        }
        $this->set('op_cate', 'profile');
    }

    public function index() {
        if ($this->currentUser['id']) {
            $this->loadModel('Brand');
            $this->brand = $this->Brand->find('first',array('conditions'=>array(
                'creator'=>$this->currentUser['id'],
            )));
            if (!empty($this->brand)) {
                //
            } else {
                $this->redirect('/');
            }
        } else {
            $this->redirect('/users/login?referer=/s/index');
        }

    }

    public function add_product() {
        $this->checkAccess();

        if (!empty($this->data)) {
            $this->data['Product']['brand_id'] = $this->brand['Brand']['id'];
            foreach ($this->data['Product'] as &$item){
                if(is_array($item)){
                    $item = implode(',',$item); // 若提交的内容为数组，则使用逗号连接各项值保存到一个字段里
                }
            }
            if(!isset($this->data['Product']['published'])){
                $this->data['Product']['published'] = 1;
            }
            $this->data['Product']['deleted'] = 0;
            $this->data['Product']['creator'] = $this->currentUser['id'];

            $this->Product->create();
            if ($this->Product->save($this->data)) {
                $this->Session->setFlash(__('The Data has been saved'));
                $this->redirect(array('action' => 'products'));
            } else {
                $this->Session->setFlash(__('The Data could not be saved. Please, try again.'));
            }
        }
        $this->set('op_cate', 'products');
    }

    function product_sale_status($id, $on_sale) {
        $this->autoRender = false;
        $resp = array();
        $success = false;
        $message= 'ok';
        if ($this->checkAccess(false)){
            $datainfo = $this->findProductByIdAndBrandId($id, $this->brand['Brand']['id']);
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

    function edit_product($id) {
        $this->checkAccess();
        $datainfo = $this->findProductByIdAndBrandId($id, $this->brand['Brand']['id']);
        if (empty($datainfo)) {
            throw new ForbiddenException(__('You cannot edit this data'));
        }

        if (!empty($this->data)) { //有数据提交
            $this->autoRender = false;
            $this->data['Product']['creator'] = $this->currentUser['id'];

            if ($this->Product->save($this->data)) {
                $this->Session->setFlash(__('The Data has been saved'));
                //$this->redirect(array('action'=>'index'));
            } else {
                $this->Session->setFlash(__('The Data could not be saved. Please, try again.'));
            }
            $successinfo = array('success' => __('Edit success'), 'actions' => array('OK' => 'closedialog'));
            //echo json_encode($successinfo);
            //return ;
            $this->redirect(array('action' => 'edit_product',$id));
        }
        else{
            $this->data = $datainfo; //加载数据到表单中
        }
        $this->set('op_cate', 'products');
    }

    function del_product($id) {
        $this->checkAccess();

        $brandId = $this->brand['Brand']['id'];
        $datainfo = $this->findProductByIdAndBrandId($id, $brandId);
        if (empty($datainfo)) {
            throw new ForbiddenException(__('You cannot delete this data'));
        }

        $this->Product->updateAll(array('deleted' => DELETED_YES), array('id' => $id, 'deleted' => DELETED_NO, 'brand_id' => $brandId));
        $this->Session->setFlash('删除成功');

        $this->redirect(array('action' => 'products'));
    }

    public function products() {
        $this->checkAccess();

        $page = 1;
        $pagesize = intval(Configure::read('Product.pagesize'));
        if(!$pagesize){
            $pagesize = 15;
        }

        $total = $this->Product->find('count', array('conditions' => array('brand_id' => $this->brand['Brand']['id'])));
        $datalist = $this->Product->find('all', array(
            'conditions' => array('brand_id' => $this->brand['Brand']['id'], 'deleted' => DELETED_NO),
            'fields'=>array('id','name','price','published','coverimg', 'deleted', 'saled', 'storage', 'updated', 'slug'),
            'order' => 'updated desc'
        ));

        $page_navi = getPageLinks($total, $pagesize, '/products/mine', $page);
        $this->set('datalist',$datalist);
        $this->set('page_navi', $page_navi);
        $this->set('op_cate', 'products');
    }

    /**
     * @param $id
     * @param $brandId
     * @return mixed
     */
    private function findProductByIdAndBrandId($id, $brandId) {
        return $this->Product->find('first', array('conditions' => array('id' => $id, 'brand_id' => $brandId)));
    }


    public function orders(){
        $this->__business_orders(
            array(ORDER_STATUS_PAID,ORDER_STATUS_WAITING_PAY,ORDER_STATUS_SHIPPED,ORDER_STATUS_RECEIVED));
    }

    function wait_shipped_orders(){
        $this->__business_orders(array(ORDER_STATUS_PAID));
        $this -> render("orders");
    }

    function wait_paid_orders(){
        $this->__business_orders(array(ORDER_STATUS_WAITING_PAY));
        $this -> render("orders");
    }


    function shipped_orders(){
        $this->__business_orders(array(ORDER_STATUS_SHIPPED));
        $this -> render("orders");
    }


    function signed_orders(){
        $this->__business_orders(array(ORDER_STATUS_RECEIVED));
        $this -> render("orders");
    }

    public function orders_export(){
        $this->__business_orders_export(
            array(ORDER_STATUS_PAID,ORDER_STATUS_WAITING_PAY,ORDER_STATUS_SHIPPED,ORDER_STATUS_RECEIVED));
    }

    function wait_shipped_orders_export(){
        $this->__business_orders_export(array(ORDER_STATUS_PAID));
        $this -> render("orders_export");
    }

    function wait_paid_orders_export(){
        $this->__business_orders_export(array(ORDER_STATUS_WAITING_PAY));
        $this -> render("orders_export");
    }


    function shipped_orders_export(){
        $this->__business_orders_export(array(ORDER_STATUS_SHIPPED));
        $this -> render("orders_export");
    }


    function signed_orders_export(){
        $this->__business_orders_export(array(ORDER_STATUS_RECEIVED));
        $this -> render("orders_export");
    }

    protected function __business_orders($onlyStatus = array()) {
        $creator = $this->currentUser['id'];

        $this->loadModel('Brand');
        $brands = $this->Brand->find('list', array('conditions' => array(
            'creator' => $creator,
        )));

        if (!empty($brands)) {
            $brand_ids = array_keys($brands);
            $this->set('is_business', true);
        } else {
            $this->__message('只有合作商家才能查看商家订单，正在为您转向个人订单', '/orders/mine');
            return;
        }

        $cond = array('brand_id' => $brand_ids, 'NOT' => array(
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
        $this->set('ship_type', ShipAddress::$ship_type);
        $this->set('creator', $creator);
        if(sizeof($onlyStatus)>1){
            $this->set('status', -1);
        }else{
            $this->set('status', $onlyStatus[0]);
        }
        $this->set('op_cate', 'orders');
    }

    protected function __business_orders_export($onlyStatus = array()) {
        $creator = $this->currentUser['id'];

        $this->loadModel('Brand');
        $brands = $this->Brand->find('list', array('conditions' => array(
            'creator' => $creator,
        )));

        if (!empty($brands)) {
            $brand_ids = array_keys($brands);
            $this->set('is_business', true);
        } else {
            $this->__message('只有合作商家才能查看商家订单，正在为您转向个人订单', '/orders/mine');
            return;
        }

        $cond = array('brand_id' => $brand_ids, 'NOT' => array(
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
        $this->set('ship_type', ShipAddress::$ship_type);
        $this->set('creator', $creator);
    }

    private function setProfileInfo($weixinId, $notice) {
        $this->set('profile_weixin_id', $weixinId);
        $this->set('profile_notice', $notice);
    }
}