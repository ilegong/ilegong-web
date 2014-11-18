<?php
class SharingController extends AppController{

    public $uses = array('SharedSlice', 'SharedOffer', 'User', 'Brand', 'CouponItem');

    public $components = array('Weixin');

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
    }

    public function send($shared_offer_id) {
        $uid = $this->currentUser['id'];
        $sharedOffer = $this->SharedOffer->findById($shared_offer_id);
        if (empty($sharedOffer)) {
            $this-> __message('红包不存在', '/');
        }
        if ($sharedOffer['SharedOffer']['uid'] != $this->currentUser['id']) {
            $this->redirect(array('action' => 'receive', $shared_offer_id));
        }

        $this->set(compact('sharedOffer'));
    }

    public function receive($shared_offer_id) {

        //update sharing slices
        //add to coupon
        //display success (ajax)

        $uid = $this->currentUser['id'];
        $sharedOffer = $this->SharedOffer->findById($shared_offer_id);
        if (empty($sharedOffer)) {
            $this-> __message('红包不存在', '/');
        }

        $expired = false;
        $addDays = $sharedOffer['ShareOffer']['valid_days'];
        if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_EXPIRED
            || is_past($sharedOffer['SharedOffer']['start'], $addDays)) {
            $expired = true;
        }

        $slices = $this->SharedSlice->find('all',
            array('conditions' => array('shared_offer_id' => $shared_offer_id),
            )
        );
        $accepted_users = Hash::extract($slices, '{n}.SharedSlice.accept_user');
        $nickNames = $this->User->findNicknamesMap(array_merge($accepted_users, (array)$uid));

        $brandId = $sharedOffer['ShareOffer']['brand_id'];
        $brandNames = $this->Brand->find('list', array(
            'conditions' => array('id' => $brandId),
            'fields' => array('id', 'name')
        ));

        if (!$expired) {

            $just_accepted = 0;
            $accepted =  (array_search($uid, $accepted_users) !== false);
            $noMore = (array_search(0, $accepted_users) === false);

            if (!$accepted && !$noMore) {
                foreach ($slices as &$slice) {
                    if (empty($slice['SharedSlice']['accept_user'])) {

                        $dt = new DateTime();
                        $now = $dt->format(FORMAT_DATETIME);
                        $dt->add(new DateInterval('P'.$addDays.'D'));
                        $valid_end = $dt->format(FORMAT_DATETIME);

                        if($this->SharedSlice->updateAll(array('accept_user' => $uid, 'accept_time' => '\''.addslashes($now).'\''),
                            array('id' => $slice['SharedSlice']['id'], 'accept_user' => 0))) {
                            $couponId = $this->CouponItem->add_coupon_type($brandNames[$brandId], $brandId, $now, $valid_end, $slice['SharedSlice']['number'], 1, COUPON_TYPE_TYPE_SHARE_OFFER, $uid, COUPON_STATUS_VALID);
//                            $recUserId, $couponType, $operator = -1, $source = 'unknown'
                            if ($couponId) {
                                $this->CouponItem->addCoupon($uid, $couponId, $uid, 'shared_offer'.$shared_offer_id);
                            }
                        }
                        $slice['SharedSlice']['accept_user'] = $uid;
                        $slice['SharedSlice']['accept_time'] = $now;
                        $accepted = true;
                        $just_accepted = $slice['SharedSlice']['number'];
                        break;
                    }
                }
            }
        }
        $this->set(compact('slices', 'expired', 'accepted', 'just_accepted', 'no_more', 'nickNames'));
    }

}