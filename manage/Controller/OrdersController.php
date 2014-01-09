<?php

class OrdersController extends AppController{
	
	var $name = 'Orders';
	
	public function admin_view($id){
		$this->loadModel('Cart');
		$carts = $this->Cart->find('all',array(
			'conditions' => array( 'order_id' => $id ),		
		));
		$this->set('carts',$carts);
		parent::admin_view($id);				
	}
	
	public function admin_trash($ids){		
		if(is_array($_POST['ids'])&& !empty($_POST['ids'])){
			$ids = $_POST['ids'];
		}
		else{
			if (!$ids) {
				$this->redirect(array('action' => 'index'));
			}
			$ids = explode(',', $ids);
		}
		$error_flag = false;
		$this->loadModel('Cart');
		foreach ($ids as $id) {
			if (!intval($id))
				continue;		
			$data = array();
			$data['deleted'] = 1;		
			$this->Order->updateAll($data, array('id' => $id));
			//同时标记删除订单的商品
			$this->Cart->updateAll(array('deleted'=>1),array('order_id'=>$id));
		}
		
		$successinfo = array('success' => __('Trash success'));
		
		$this->set('successinfo', $successinfo);
		$this->set('_serialize', 'successinfo');
		
		if ($error_flag) {
			return false;
		}
		else{
			return true;
		}
	}
	
	/**
	 * 删除数据
	 * @param $id
	 */
	function admin_delete($ids = null) {
		@set_time_limit(0);
		if(is_array($_POST['ids'])&& !empty($_POST['ids'])){
			$ids = $_POST['ids'];
		}
		else{
			if (!$ids) {
				$this->redirect(array('action' => 'index'));
			}
			$ids = explode(',', $ids);
		}
		$istree = false;
		$delete_flag = $this->Order->deleteAll(array('id' => $ids, 'deleted' => 1), true, true);
		//删除相关的订单商品
		$this->loadModel('Cart');
		$this->Cart->deleteAll(array('order_id' => $ids, 'deleted' => 1), true, true);
		
		if ($delete_flag) {
			$successinfo = array('success' => __('Delete success'));
		} else {
			$successinfo = array('error' => __('Delete error'));
		}
		$this->set('successinfo', $successinfo);
		$this->set('_serialize', 'successinfo');
	
		if ($delete_flag) {
			return true;
		}
		else{
			return false;
		}
	}
	
	/**
	 * 恢复删除标记
	 * @param $id
	 */
	function admin_restore($ids = null) {
		if(is_array($_POST['ids'])&& !empty($_POST['ids'])){
			$ids = $_POST['ids'];
		}
		else{
			if (!$ids) {
				$this->redirect(array('action' => 'index'));
			}
			$ids = explode(',', $ids);
		}
		$this->loadModel('Cart');
		foreach ($ids as $id) {
			if (!intval($id))
				continue;
			$data = array();
			$data['id'] = $id;
			$data['deleted'] = 0;
	
			if ($this->Order->save($data)) {
				$this->Cart->updateAll(array('deleted'=>0),array('order_id'=>$id));
				$successinfo = array('success' => __('Restore success'));
			} else {
				$successinfo = array('error' => __('Restore error'));
			}
		}
		$this->set('successinfo', $successinfo);
		$this->set('_serialize', 'successinfo');
	}
	
	
}