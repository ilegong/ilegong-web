<?php

class ShortmessagesController extends AppController {

    var $name = 'Shortmessages';
    var $components  = array('Weixin');

    const coupon_30_10 = 24385;
    const coupon_50_20 = 24384;

	function admin_add()
	{
		if (!empty($this->data) ) {
			if($this->data['Shortmessage']['receiver'])
			{
				$this->loadModel('Staff');
				$this->Staff->recursive = -1;
				$receiver = $this->Staff->findByName($this->data['Shortmessage']['receiver']);
				if($receiver)
				{
					$this->data['Shortmessage']['receiverid'] = $receiver['Staff']['id'];
					$this->data['Shortmessage']['name'] = 'staff'; // 记录类型是staff的短信，还是user的短信
					$this->data['Shortmessage']['msgfromid'] = $this->Auth->user('id');
					$this->data['Shortmessage']['msgfrom'] = $this->Auth->user('name');
				}
				else
				{
					echo json_encode(array('receiver'=> 'receiver not exists'));
					exit;
				}
			}
			else
			{
				echo json_encode(array('receiver'=> 'receiver not exists'));
				exit;
			}
			
			
			//print_r($this->data);exit;
		}
		parent::admin_add();
	}

    public function get_haohao_cake_coupon(){
        $coupon_id = 19220;
        if (empty($this->currentUser['id']) && $this->is_weixin()) {
            $ref = Router::url($_SERVER['REQUEST_URI']);
            $this->redirect('/users/login.html?force_login=1&auto_weixin=' . $this->is_weixin() . '&referer=' . urlencode($ref));
            exit();
        }
        $this->loadModel('Coupon');
        $this->loadModel('CouponItem');
        $user_id = $this->currentUser['id'];
        $already_get = $this->CouponItem->hasAny(array('coupon_id' => $coupon_id, 'bind_user' => $user_id));
        $this->set('noFlash', false);
        if(!$already_get){
            $this->CouponItem->addCoupon($user_id, $coupon_id, $user_id, 'haohao_cake');
            $this->Session->setFlash(__('领取成功'));
            $this->redirect('/users/my_coupons');
        } else {
            $this->log("already got for ".$user_id." of ". $coupon_id);
            $this->Session->setFlash(__('你已经领过啦'));
            $this->redirect('/users/my_coupons');
        }
    }

    public function get_hongbao_by_id($id){
        $this->check_login();
        $cond = array('id' => $id,'deleted' => DELETED_NO);
        $this->loadModel('ShareOffer');
        $store_offer = $this->ShareOffer->find('first',array(
                'conditions' =>$cond,
                'order' =>'created desc',
                'fields' => array('id','name','introduct','deleted','start','end','valid_days','avg_number','is_default'))
        );
        $this->get_shared_offer($store_offer,8,400);
    }

    public function get_hongbao($type = null){
        $this->check_login();
        if($type == 'pyshuo'){
            $brand_id = PYS_BRAND_ID;
        }else{
            $brand_id = 193;
        }
        $cond = array('brand_id' => $brand_id,'deleted' => DELETED_NO);
        $this->loadModel('ShareOffer');
        $store_offer = $this->ShareOffer->find('first',array(
            'conditions' =>$cond,
            'order' =>'created desc',
            'fields' => array('id','name','introduct','deleted','start','end','valid_days','avg_number','is_default'))
        );
        $this->log('hongbao'. $store_offer['ShareOffer']['id']);
        $this->get_shared_offer($store_offer,8,100);
    }

    private function check_login(){
        if (empty($this->currentUser['id']) && $this->is_weixin()) {
            $ref = Router::url($_SERVER['REQUEST_URI']);
            $this->redirect('/users/login.html?force_login=1&auto_weixin=' . $this->is_weixin() . '&referer=' . urlencode($ref));
            exit();
        }
        $uid = $this->currentUser['id'];
        if(empty($uid)){
            $this->redirect('/users/login');
            exit();
        }
    }

    private function get_shared_offer($store_offer,$split_num,$money){
        if(empty($store_offer)){
            $this->redirect('/');
            exit();
        }
        $shareOfferId = $store_offer['ShareOffer']['id'];
        $toShareNum = $store_offer['ShareOffer']['avg_number'] * $split_num;
        $this->loadModel('SharedOffer');
        //separate red packet
        $uid = $this->currentUser['id'];
        if(!$this->SharedOffer->hasAny(array('uid' => $uid, 'share_offer_id'=>$shareOfferId))){
            $this->ShareOffer->add_shared_slices($uid,$shareOfferId,$toShareNum);
            $this->Weixin->send_packet_received_message($uid, $money, $store_offer['ShareOffer']['name']);
        }
        $this->redirect('/users/my_offers');
    }


    public function update_618_coupon_pids(){
        $this->autoRender=false;
        $this->loadModel('Coupon');
        $this->loadModel('ProductSpecial');
        $specialProduct = $this->ProductSpecial->find('all',array('conditions' => array('special_id' => 7,'published' => PUBLISH_YES)));
        $specialPids = Hash::extract($specialProduct,'{n}.ProductSpecial.product_id');
        $specialPidsStr = json_encode($specialPids);
        $specialPidsStr = str_replace('"','',$specialPidsStr);
        $this->Coupon->updateAll(array('product_list' => "'".$specialPidsStr."'"),array('id' => array(self::coupon_30_10,self::coupon_50_20)));
        echo json_encode(array('success' => true));
    }

    //24245 50-20
    //24246 30-10
    public function get_618_coupons($couponid){
        $this->check_login();
        if($couponid!=self::coupon_50_20&&$couponid!=self::coupon_30_10){
            //error
            $this->redirect('/');
        }
        if($couponid==self::coupon_50_20){
            $descs = "满50元减20";
        }
        if($couponid==self::coupon_30_10){
            $descs = " 满30元减10元";
        }
        $weixinC = $this->Components->load('Weixin');
        $uid = $this->currentUser['id'];
        add_coupon_for_618($uid, $weixinC, $couponid,$descs);
        $this->redirect('/users/my_coupons');
    }

    public function get_618_coupon_json($couponid){
        if (empty($this->currentUser['id']) && $this->is_weixin()) {
            echo json_encode(array('success' => false,'reason' => 'no_login'));
            return;
        }
        if($couponid==self::coupon_50_20){
            $descs = "满50元减20";
        }
        if($couponid==self::coupon_30_10){
            $descs = " 满30元减10元";
        }
        $weixinC = $this->Components->load('Weixin');
        $uid = $this->currentUser['id'];
        $result = add_coupon_for_618($uid, $weixinC, $couponid,$descs);
    }
}
?>