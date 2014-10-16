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


    protected function _custom_list_option(&$searchoptions) {
        $filterType = $_REQUEST['filterType'];
        $filter = $_REQUEST['filter'];
        if ($filterType) {
            switch ($filterType) {
                case 'tag_id':
                    $tagId = intval($filter);
                    if ($tagId) {
                        if ($searchoptions['conditions']) {
                            $searchoptions['conditions']['Tag.tag_id'] = $tagId;
                        } else {
                            $searchoptions['conditions'] = array(
                                'Tag.tag_id' => $tagId
                            );
                        }
                        $searchoptions['joins'][] = array(
                            'table' => 'product_product_tags',
                            'alias' => 'Tag',
                            'type' => 'left',
                            'conditions' => array('Product.id=Tag.product_id'),
                        );
                        $this->set('filter_string', "Product.Tagid=" . $tagId);
                    }
                    break;
                case 'brand_id':
                    $brand_id = intval($filter);
                    if ($brand_id > 0) {
                        if ($searchoptions['conditions']) {
                            $searchoptions['conditions']['Order.brand_id'] = $brand_id;
                        } else {
                            $searchoptions['conditions'] = array(
                                'Order.brand_id' => $brand_id
                            );
                        }
//                        $searchoptions['joins'][] = array(
//                            'table' => 'products',
//                            'alias' => 'Product',
//                            'type' => 'left',
//                            'conditions' => array('Product.id=Order.brand_id'),
//                        );
                        $this->set('filter_string', "Product.BrandId=" . $brand_id);
                    }
            }
        }
        return $searchoptions;
    }


    public function admin_list_today() {
        $start_date= $this->get_current_day_start();
        $end_date = $this->get_current_day_end();

        $this->loadModel('Brand');
        $brands = $this->Brand->find('all');

        $this->loadModel('Order');
        $orders = $this->Order->find('all',array(
            'order' => 'id desc',
            'conditions'=>array(
                'created >"'.$start_date.'"',
                'created <"'.$end_date.'"'
            )));

        $ids = array();
        foreach($orders as $o){
            $ids[] = $o['Order']['id'];
        }
        $this->loadModel('Cart');
        $carts = $this->Cart->find('all',array(
            'conditions'=>array(
                'order_id' => $ids,
            )));

        $order_carts = array();
        foreach($carts as $c){
            $order_id = $c['Cart']['order_id'];
            if (!isset($order_carts[$order_id])) {
                $order_carts[$order_id] = array();
            }
            $order_carts[$order_id][] = $c;
        }

        $this->set('orders',$orders);
        $this->set('order_carts',$order_carts);
        $this->set('ship_type',$this->ship_type);
        $this->set('brands',$brands);

    }

    private function get_current_day_start(){
        $y=date("Y");
        $m=date("m");
        $d=date("d");
        $day_start=mktime(0,0,0,$m,$d,$y);
        return date("Y-m-d\TH:i:s",$day_start);
    }

    private function get_current_day_end(){
        $y=date("Y");
        $m=date("m");
        $d=date("d");
        $day_end=mktime(23,59,59,$m,$d,$y);
        return date("Y-m-d\TH:i:s",$day_end);
    }

    var $ship_type = array(
        101=>'申通',
        102=>'圆通',
        103=>'韵达',
        104=>'顺丰',
        105=>'EMS',
        106=>'邮政包裹',
        107=>'天天',
        108=>'汇通',
        109=>'中通',
        110=>'全一',
    );
}