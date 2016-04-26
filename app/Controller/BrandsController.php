<?php

class BrandsController extends AppController {

    var $name = 'Brands';

    public function beforeFilter(){
        parent::beforeFilter();
        $this->redirect('/');
    }

    public function index($slug='/') {
        $uid = $this->currentUser['id'];
        if ($uid) {
            $this->loadModel('Brand');
            $this->brand = $this->Brand->find('first',array('conditions'=>array(
                'creator'=> $uid,
            )));
            if (!empty($this->brand)) {
                $this->redirect('/b/'.$this->brand['Brand']['slug']);
            } else {
                $this->redirect('/');
            }
        }
    }

    public function view($slug='/'){
    	$modelClass = $this->modelClass;
    	
    	parent::view($slug);
    	
    	$id = $this->viewdata[$modelClass]['id'];
    	$this->loadModel('Product');
    	$products = $this->Product->find('all',array(
    		'conditions' => array(
    			'brand_id' => $id,
    			'Product.published' => 1, 
    			'Product.deleted' => 0
    		),
    		'limit'=>'100',
            'order'=>'sort_in_store desc'
    	));
        $hide_icon_set = array('xi_rui_ji_tuan');
        if(!(array_search($slug, $hide_icon_set) === false)){
            $this->set('hide_icon', true);
        }
    	$this->set('products',$products);
        $this->set('op_cate', 'share');
        $this->set('hideNav', true);
        $this->set('slug', $slug);
        $this->set('is_owner', $this->currentUser['id'] == $this->viewdata[$modelClass]['creator']);
    }

    public function brands_admin() {
        $toLogin = '/users/login?referer=' .Router::url('/brands/brands_admin');
        if(empty($this->currentUser['id'])){
            $this->redirect($toLogin);
        }

        if ($this->is_admin($this->currentUser['id'])) {
            $brands = $this->Brand->find('all', array(
                'fields' => array('creator', 'name'),
                'order' =>  'id desc',
            ));
            $this->set('brands', $brands);
        } else {
            $this->Session->setFlash(__("You are not authorized to visit this page!"));
            $this->logoutCurrUser();
            $this->redirect($toLogin);
            exit;
        }
    }
    public function introduction($slug='/'){
        if (empty($id)) {
            $id = intval($slug);
        }
        if (!empty($slug) && $slug != strval(intval($slug))) {
            $Brand = $this->Brand->find('first', array(
                'conditions' => array('Brand.published' => 1, 'Brand.deleted' => 0, 'Brand.slug' => $slug),
            ));
        } elseif ($id) {
            $Brand  = $this->Brand ->find('first', array(
                'conditions' => array('Brand.published' => 1, 'Brand.deleted' => 0, 'Brand.id' => $id),
            ));
        }
        $hide_icon_set = array('xi_rui_ji_tuan');
        if(!(array_search($slug, $hide_icon_set) === false)){
            $this->set('hide_icon', true);
        }
        $this->set('Brand', $Brand);
        $this->set('Brand', $Brand);
        $this->set('hideNav', true);
        $this->set('myIntroduction', $_SERVER['REQUEST_URI']);
        $this->set('slug', $slug);
    }

}
?>