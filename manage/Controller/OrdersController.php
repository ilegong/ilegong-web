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

        $sent_by_our_self = array(
            35,//电科院-刘炎炎
            413,//腾讯-nancyqi
            41, //小牛村-王成宝
            92, //朋友说
            116, //中国移动-黄亮
            88, //阿里巴巴—李瑞
            36, //你好植物-张小伟
            72, //蓝靛果-王野
            34,//去哪儿-荣浩
        );

        $start_date= $this->get_day_start($_REQUEST['start_date']);
        $end_date = $this->get_day_end($_REQUEST['end_date']);
        $brand_id=empty($_REQUEST['brand_id'])?0:$_REQUEST['brand_id'];

        if(empty($brand_id)) {
            if ($_REQUEST['sent_by_us'] == 1) {
                $brand_id = $sent_by_our_self;
            }
        }

        $order_status=!isset($_REQUEST['order_status'])?-1:$_REQUEST['order_status'];
        $order_id=!isset($_REQUEST['order_id'])?"":$_REQUEST['order_id'];
        $consignee_name=!isset($_REQUEST['consignee_name'])?"":$_REQUEST['consignee_name'];
        $consignee_mobilephone=!isset($_REQUEST['consignee_mobilephone'])?"":$_REQUEST['consignee_mobilephone'];

        $this->loadModel('Brand');
        $brands = $this->Brand->find('all',array(
            'conditions'=>array(
                'deleted != 1',
                'published = 1'
            ),
            'order' => 'id desc'
        ));

        $this->loadModel('Order');
        $conditions = array(
            'Order.created >"' . date("Y-m-d\TH:i:s", $start_date) . '"',
            'Order.created <"' . date("Y-m-d\TH:i:s", $end_date) . '"',
            'Order.type' => array(ORDER_TYPE_DEF, ORDER_TYPE_GROUP_FILL)
        );
        if($_REQUEST['search_groupon'] === "1" && $_REQUEST['consignee_mobilephone']){
            $mobile_num = intval($_REQUEST['consignee_mobilephone']);
            $this->loadModel('Groupon');
            $groupons = $this->Groupon->find('all', array(
                'conditions' => array('mobile' => $mobile_num)
            ));
            if($groupons){
                $groupon_lists = Hash::extract($groupons, '{n}.Groupon.id');
                $organizer_ids = Hash::extract($groupons, '{n}.Groupon.user_id');
            }else{
                echo '<span style="font-size: large;color: #ff0000">\u8fd9\u662f\u4e00\u4e2a\u4f8b\u5b50</span>';
                return;
            }
            $this->loadModel('GrouponMember');
            $groupon_members = $this->GrouponMember->find('all', array(
                'conditions' => array('groupon_id'=>$groupon_lists ,'status' => 1),
                'fields' => array('id','groupon_id')
            ));
            $groupon_member_lists = Hash::extract($groupon_members, '{n}.GrouponMember.id');
            $order_groupon_link = Hash::combine($groupon_members, '{n}.GrouponMember.id', '{n}.GrouponMember.groupon_id');
            if($groupon_member_lists){
                $conditions[] = array('Order.member_id' => $groupon_member_lists);
                $conditions['Order.type'] = array(ORDER_TYPE_GROUP, ORDER_TYPE_GROUP_FILL);
                $consignee_mobilephone = null;
            }else{
                echo '<span style="font-size: large;color: #ff0000">\u56e2\u8d2d\u8ba2\u5355\u6682\u65f6\u65e0\u4eba\u53c2\u56e2</span>';
                return;
            }
            $find_order_conditions =array('member_id' => $groupon_member_lists, 'creator' => $organizer_ids, 'status'=>array(ORDER_STATUS_PAID,ORDER_STATUS_WAITING_PAY));
            if($this->Order->hasAny($find_order_conditions)){
                echo '<span style="font-size: large;color: #ff0000">\u56e2\u8d2d\u8ba2\u5355\u5df2\u53d1\u8d27\u6216\u5df2\u786e\u8ba4\u6536\u8d27</span>';
                return;
            }
            $this->set('order_groupon_link',$order_groupon_link);
        }

        if($brand_id !=0){
            $conditions['Order.brand_id'] = $brand_id;
        }
        if($order_status!=-1){
            array_push($conditions,'Order.status = '.$order_status);
        }
        if(!empty($order_id)){
            array_push($conditions,'Order.id = '.$order_id);
        }
        if(!empty($consignee_name)){
            array_push($conditions,'Order.consignee_name like "%'.$consignee_name.'%"');
        }
        if(!empty($consignee_mobilephone)){
            array_push($conditions,'Order.consignee_mobilephone = '.$consignee_mobilephone);
        }

        $payNotifyModel = ClassRegistry::init('PayNotify');
        $payNotifyModel->query("update cake_pay_notifies set order_id =  substring_index(substring_index(out_trade_no,'-',2),'-',-1) where status = 6");
        $join_conditions = array(
            array(
                'table' => 'pay_notifies',
                'alias' => 'Pay',
                'conditions' => array(
                    'Pay.order_id = Order.id'
                ),
                'type' => 'LEFT',
            )
        );

        $orders = $this->Order->find('all',array(
            'order' => 'id desc',
            'conditions'=> $conditions,
            'joins' => $join_conditions,
            'fields' => array('Order.*', 'Pay.trade_type'),
        ));

        $ids = array();
        $total_money = 0;
        foreach($orders as $o){
            $ids[] = $o['Order']['id'];
            $o_status = $o['Order']['status'];
            if($o_status == 1 || $o_status == 2 || $o_status == 3 ){
                $total_money = $total_money + $o['Order']['total_all_price'];
            }
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
        $this->set('total_money',$total_money);
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
        111=>'宅急送'
    );
}