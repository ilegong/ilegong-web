<?php

class ShortmessagesController extends AppController {

    var $name = 'Shortmessages';

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

}
?>