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
        $this->set('hideNav', true);
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

        $owner = $sharedOffer['SharedOffer']['uid'];
        $isOwner = $owner == $this->currentUser['id'];
        if ($isOwner) {
            if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_NEW){
                $this->SharedOffer->updateAll(array('status' => SHARED_OFFER_STATUS_GOING)
                    , array('id' => $shared_offer_id, 'status' => SHARED_OFFER_STATUS_NEW));
            }
        } else {
            if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_NEW) {
                $this-> __message('红包尚未开封，请等待红包所有人开封后发出邀请', '/');
                return;
            }

            if ($this->is_weixin() && notWeixinAuthUserInfo($uid, $this->currentUser['nickname'])) {
                $this->__message('为让朋友知道是谁领了她/他发的红包，请授权我们获取您的微信昵称', '/users/login.html?force_login=true&referer=' . urlencode($_SERVER['REQUEST_URI']));
            }

        }


        $expired = false;
        $addDays = $sharedOffer['ShareOffer']['valid_days'];
        if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_EXPIRED
            || is_past($sharedOffer['SharedOffer']['start'], $addDays)) {
            $expired = true;
        }

        $slices = $this->SharedSlice->find('all',
            array('conditions' => array('shared_offer_id' => $shared_offer_id), 'order' => 'SharedSlice.accept_time desc')
        );
        $accepted_users = Hash::extract($slices, '{n}.SharedSlice.accept_user');
        $nickNames = $this->User->findNicknamesMap(array_merge($accepted_users, array($uid, $owner)));

        $ownerName = $nickNames[$owner];
        if(wxDefaultName($ownerName)) {
            $ownerName = __('朋友说');
        }

        $brandId = $sharedOffer['ShareOffer']['brand_id'];
        $brandNames = $this->Brand->find('list', array(
            'conditions' => array('id' => $brandId),
            'fields' => array('id', 'name')
        ));
        $total_slice = count($slices);
        $valuesCounts = array_count_values($accepted_users);
        $left_slice = empty($valuesCounts[0])? 0 : $valuesCounts[0];
        $noMore = $left_slice == 0;

        $accepted =  (array_search($uid, $accepted_users) !== false);
        if (!$expired) {
            $just_accepted = 0;

            if (!$accepted && !$noMore) {
                foreach ($slices as &$slice) {
                    if (empty($slice['SharedSlice']['accept_user'])) {

                        $dt = new DateTime();
                        $now = $dt->format(FORMAT_DATETIME);
                        $dt->add(new DateInterval('P'.$addDays.'D'));
                        $valid_end = $dt->format(FORMAT_DATETIME);

                        $updated = $this->SharedSlice->updateAll(array('accept_user' => $uid, 'accept_time' => '\'' . addslashes($now) . '\''),
                            array('id' => $slice['SharedSlice']['id'], 'accept_user' => 0));
                        if($updated && $this->SharedSlice->getAffectedRows() == 1) {
                            if($sharedOffer['SharedOffer']['share_offer_id'] == 44){ //朋友说指定商品优惠券
                                $couponId = $this->CouponItem->add_coupon_type($brandNames[$brandId], 0, $now, $valid_end, $slice['SharedSlice']['number'], PUBLISH_YES,
                                    COUPON_TYPE_TYPE_SHARE_OFFER, $uid, COUPON_STATUS_VALID, 883);//指定商品id
                            }elseif($sharedOffer['SharedOffer']['share_offer_id'] == 45){//朋友说指定商品优惠券
                                $couponId = $this->CouponItem->add_coupon_type($brandNames[$brandId], 0, $now, $valid_end, $slice['SharedSlice']['number'], PUBLISH_YES,
                                    COUPON_TYPE_TYPE_SHARE_OFFER, $uid, COUPON_STATUS_VALID, 1020);//指定商品id
                            }else{
                                $couponId = $this->CouponItem->add_coupon_type($brandNames[$brandId], $brandId, $now, $valid_end, $slice['SharedSlice']['number'], PUBLISH_YES,
                                    COUPON_TYPE_TYPE_SHARE_OFFER, $uid, COUPON_STATUS_VALID);
                            }
                            if ($couponId) {
                                $this->CouponItem->addCoupon($uid, $couponId, $uid, 'shared_offer'.$shared_offer_id);
                                $couponItemId = $this->CouponItem->getLastInsertID();
                                $this->SharedSlice->updateAll(array('coupon_item_id' => $couponItemId),
                                    array('id' => $slice['SharedSlice']['id'], 'coupon_item_id' => 0));
                                $this->Weixin->send_coupon_received_message($uid, 1, "在".$sharedOffer['ShareOffer']['name']."店购买时有效","有效期至".friendlyDateFromStr($valid_end, 'full'));
                                App::uses('CakeNumber', 'Utility');
                                $this->Weixin->send_packet_be_got_message($sharedOffer['SharedOffer']['uid'], $nickNames[$uid], CakeNumber::precision($slice['SharedSlice']['number']/100, 2),  $sharedOffer['ShareOffer']['name']."红包");
                            }
                            $left_slice -= 1;
                            if ($left_slice == 0) {
                                $this->SharedOffer->updateAll(array('status' => SHARED_OFFER_STATUS_OUT)
                                    , array('id' => $shared_offer_id, 'status' => SHARED_OFFER_STATUS_GOING));
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
        } else {
            //Only ongoing status can go to expired
            if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_GOING) {
                $this->SharedOffer->updateAll(array('status' => SHARED_OFFER_STATUS_EXPIRED)
                    , array('id' => $shared_offer_id, 'status' => SHARED_OFFER_STATUS_GOING));
            }
        }
        $this->set(compact('slices', 'expired', 'accepted', 'just_accepted', 'noMore', 'nickNames', 'sharedOffer', 'uid', 'isOwner', 'total_slice', 'left_slice', 'ownerName'));
    }

}