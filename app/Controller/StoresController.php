<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/28/14
 * Time: 12:45 PM
 */

class StoresController extends AppController {

    public $uses = array('Product');

    public $brand = null;

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



            } else {
                $this->redirect('/');
            }
        } else {
            $this->redirect('/users/login?referer=/s/index');
        }

    }

    public function products($store = '') {
        $this->checkAccess();

        $page = 1;
        $pagesize = intval(Configure::read('Product.pagesize'));
        if(!$pagesize){
            $pagesize = 15;
        }

        $total = $this->Product->find('count', array('conditions' => array('brand_id' => $this->brand['Brand']['id'])));
        $datalist = $this->Product->find('all', array(
            'conditions' => array('brand_id' => $this->brand['Brand']['id']),
            'fields'=>array('id','name','price','published','coverimg'),
        ));

        $page_navi = getPageLinks($total, $pagesize, '/products/mine', $page);
        $this->set('datalist',$datalist);
        $this->set('page_navi', $page_navi);
    }
}