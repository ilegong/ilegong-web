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
}