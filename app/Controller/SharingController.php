<?php
class SharingController extends AppController{

    public $uses = array('SharedSlice', 'SharedOffer', 'User', 'Brand', 'CouponItem');

    public $components = array('RedPacket');

    public function __construct($request = null, $response = null) {
        parent::__construct($request, $response);
        $this->pageTitle = __('红包');
    }

    function beforeFilter(){
        parent::beforeFilter();
        if (empty($this->currentUser['id'])) {
            $ref = Router::url($_SERVER['REQUEST_URI']);
            if ($this->is_weixin()) {
                $this->redirect(redirect_to_wx_oauth($ref, WX_OAUTH_BASE, true));
            } else {
                $this->redirect('/users/login.html?referer=' . $ref);
            }
        }
        $this->set('hideNav', true);
    }

    public function receive($shared_offer_id) {
        $uid = $this->currentUser['id'];
        $result = $this->RedPacket->process_receive($shared_offer_id,$uid,$this->is_weixin());
        if(!$result['success']){
            $this-> __message($result['msg'], $result['redirect_url']);
            return;
        }
        $this->set($result);
    }

}