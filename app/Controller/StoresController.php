<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/28/14
 * Time: 12:45 PM
 */
class StoresController extends AppController
{

    public $uses = array('Product', 'Brand', 'Order');

    public $components = array('Paginator');


    /* lower case */
    public $allowdPostProductFields = array('id', 'promote_name', 'name', 'coverimg', 'content', 'published', 'price', 'ship_fee', 'original_price');

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
//                $this->data['Product']['published'] = 1;
                $this->data['Product']['published'] = 2;
            }
            $this->data['Product']['deleted'] = DELETED_NO;
            $this->data['Product']['creator'] = $this->currentUser['id'];
            $this->data['Product']['coverimg'] = trim($this->data['coverimg']);

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
                if ($this->Product->save($this->data)) {
                    $this->Session->setFlash(__('The Data has been saved'));
                    $this->redirect(array('action' => 'products'));
                } else {
                    setFlashError($this->Session, __('The Data could not be saved. Please, try again.'));
                }
            }
        }
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
                if ($this->Product->save($this->data)) {
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
            $this->data = $datainfo; //加载数据到表单中
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

    public function products($product_status='')
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
            'fields' => array('id', 'name', 'price', 'published', 'coverimg', 'deleted', 'saled', 'storage', 'updated', 'slug', 'sort_in_store'),
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

        if (!empty($brand)) {
            $brand_id = $brand['Brand']['id'];
            $this->set('is_business', true);
        } else {
            $this->__message('只有合作商家才能查看商家订单，正在为您转向个人订单', '/orders/mine');
            return;
        }

        $cond = array('brand_id' => $brand_id, 'NOT' => array(
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

        $cond = array('brand_id' => $brand['Brand']['id'], 'NOT' => array(
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
}