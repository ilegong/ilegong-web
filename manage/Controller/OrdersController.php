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
        $start_date= $this->get_day_start($_REQUEST['start_date']);
        $end_date = $this->get_day_end($_REQUEST['end_date']);
        $brand_id=empty($_REQUEST['brand_id'])?0:$_REQUEST['brand_id'];
        $order_status=!isset($_REQUEST['order_status'])?-1:$_REQUEST['order_status'];
        $order_id=!isset($_REQUEST['order_id'])?"":$_REQUEST['order_id'];
        $consignee_name=!isset($_REQUEST['consignee_name'])?"":$_REQUEST['consignee_name'];
        $consignee_mobilephone=!isset($_REQUEST['consignee_mobilephone'])?"":$_REQUEST['consignee_mobilephone'];

        $this->loadModel('Brand');
        $brands = $this->Brand->find('all',array('order' => 'id desc'));

        $this->loadModel('Order');
        $conditions = array(
            'created >"' . date("Y-m-d\TH:i:s", $start_date) . '"',
            'created <"' . date("Y-m-d\TH:i:s", $end_date) . '"'
        );
        if($brand_id !=0){
            array_push($conditions,'brand_id = '.$brand_id);
        }
        if($order_status!=-1){
            array_push($conditions,'status = '.$order_status);
        }
        if(!empty($order_id)){
            array_push($conditions,'id = '.$order_id);
        }
        if(!empty($consignee_name)){
            array_push($conditions,'consignee_name like "%'.$consignee_name.'%"');
        }
        if(!empty($consignee_mobilephone)){
            array_push($conditions,'consignee_mobilephone = '.$consignee_mobilephone);
        }

        $orders = $this->Order->find('all',array(
            'order' => 'id desc',
            'conditions'=> $conditions));

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
            $c_order_id = $c['Cart']['order_id'];
            if (!isset($order_carts[$c_order_id])) {
                $order_carts[$c_order_id] = array();
            }
            $order_carts[$c_order_id][] = $c;
        }

        $this->set('orders',$orders);
        $this->set('order_carts',$order_carts);
        $this->set('ship_type',$this->ship_type);
        $this->set('brands',$brands);

        $this->set('start_date',date("Y-m-d",$start_date));
        $this->set('end_date',date("Y-m-d",$end_date));
        $this->set('brand_id',$brand_id);
        $this->set('order_status',$order_status);
        $this->set('order_id',$order_id);
        $this->set('consignee_name',$consignee_name);
        $this->set('consignee_mobilephone',$consignee_mobilephone);

    }

    private function get_day_start($start_day = ''){
        return $this->get_day_time($start_day);
    }

    private function get_day_end($end_day = ''){
        return $this->get_day_time($end_day,23,59,59);
    }

    private function get_day_time($day = '',$hour=0,$minute=0,$second =0){
        if(!empty($day)){
            $start_date=date_parse_from_format("Y-m-d",$day);
            $y=$start_date["year"];
            $m=$start_date["month"];
            $d=$start_date["day"];
        }else{
            $y=date("Y");
            $m=date("m");
            $d=date("d");
        }
        return mktime($hour,$minute,$second,$m,$d,$y);
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