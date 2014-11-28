<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/28/14
 * Time: 12:45 PM
 */

class StoresController extends AppController {

    public $uses = array('Product', 'Brand');

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

        return true;
    }

    public function beforeFilter() {
        parent::beforeFilter();

        $this->layout = 'store';
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
            'conditions' => array('brand_id' => $this->brand['Brand']['id']),
            'fields'=>array('id','name','price','published','coverimg', 'deleted', 'saled', 'storage', 'updated', 'slug'),
            'order' => 'updated desc'
        ));

        $page_navi = getPageLinks($total, $pagesize, '/products/mine', $page);
        $this->set('datalist',$datalist);
        $this->set('page_navi', $page_navi);
    }

    /**
     * @param $id
     * @param $brandId
     * @return mixed
     */
    private function findProductByIdAndBrandId($id, $brandId) {
        return $this->Product->find('first', array('conditions' => array('id' => $id, 'brand_id' => $brandId)));
    }
}