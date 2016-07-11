<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 7/29/15
 * Time: 15:09
 */
class RedPacketComponent extends Component
{
    public $components = array('Weixin');

    /**
     * @param $shared_offer_id
     * @param $uid
     * @param bool $is_weixin
     * @param $send_msg
     * @return array
     * 处理红包 逻辑
     */
    public function process_receive($shared_offer_id, $uid, $is_weixin = true, $send_msg = true)
    {
        //update sharing slices
        //add to coupon
        //display success (ajax)
        $this->SharedOffer = ClassRegistry::init('SharedOffer');
        $this->SharedSlice = ClassRegistry::init('SharedSlice');
        $this->User = ClassRegistry::init('User');
        $this->Brand = ClassRegistry::init('Brand');
        $this->CouponItem = ClassRegistry::init('CouponItem');
        $success = false;
        $sharedOffer = $this->SharedOffer->findById($shared_offer_id);
        if (empty($sharedOffer)) {
            return array('success' => $success, 'reason' => 'not_exist', 'msg' => '红包不存在', 'redirect_url' => '/');
        }
        $owner = $sharedOffer['SharedOffer']['uid'];
        $isOwner = $owner == $uid;
        if ($isOwner) {
            if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_NEW) {
                $this->SharedOffer->updateAll(array('status' => SHARED_OFFER_STATUS_GOING)
                    , array('id' => $shared_offer_id, 'status' => SHARED_OFFER_STATUS_NEW));
            }
        } else {
            if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_NEW) {
                return array('success' => $success, 'reason' => 'not_open', 'msg' => '红包尚未开封，请等待红包所有人开封后发出邀请', 'redirect_url' => '/');
            }
            if ($is_weixin && notWeixinAuthUserInfo($uid, $this->currentUser['nickname'])) {
                return array('success' => $success, 'reason' => 'not_auth', 'msg' => '为让朋友知道是谁领了她/他发的红包，请授权我们获取您的微信昵称', 'redirect_url' => '/users/login.html?force_login=true&referer=' . urlencode($_SERVER['REQUEST_URI']));
            }
        }
        $expired = false;
        $addDays = $sharedOffer['ShareOffer']['valid_days'];
        if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_EXPIRED
            || is_past($sharedOffer['SharedOffer']['start'], $addDays)
        ) {
            $expired = true;
        }
        $slices = $this->SharedSlice->find('all',
            array('conditions' => array('shared_offer_id' => $shared_offer_id), 'order' => 'SharedSlice.accept_time desc')
        );
        $accepted_users = Hash::extract($slices, '{n}.SharedSlice.accept_user');
        $packet_provider = $sharedOffer['ShareOffer']['sharer_id'];
        $nickNames = $this->User->findNicknamesMap(array_merge($accepted_users, array($uid, $owner, $packet_provider)));
        $ownerName = $nickNames[$owner];
        if (wxDefaultName($ownerName)) {
            $ownerName = __('朋友说');
        }
        $brandId = $sharedOffer['ShareOffer']['brand_id'];
        if ($brandId != -1 && $brandId != 0) {
            $brandNames = $this->Brand->find('list', array(
                'conditions' => array('id' => $brandId),
                'fields' => array('id', 'name')
            ));
        }
        $total_slice = count($slices);
        $valuesCounts = array_count_values($accepted_users);
        $left_slice = empty($valuesCounts[0]) ? 0 : $valuesCounts[0];
        $noMore = $left_slice == 0;
        $accepted = (array_search($uid, $accepted_users) !== false);
        $couponNum = 0;
        if (!$expired) {
            $just_accepted = 0;
            if (!$accepted && !$noMore) {
                foreach ($slices as &$slice) {
                    if (empty($slice['SharedSlice']['accept_user'])) {
                        $dt = new DateTime();
                        $now = $dt->format(FORMAT_DATETIME);
                        $dt->add(new DateInterval('P' . $addDays . 'D'));
                        $valid_end = $dt->format(FORMAT_DATETIME);
                        $updated = $this->SharedSlice->updateAll(array('accept_user' => $uid, 'accept_time' => '\'' . addslashes($now) . '\''),
                            array('id' => $slice['SharedSlice']['id'], 'accept_user' => 0));
                        if ($updated) {
                            if ($sharedOffer['SharedOffer']['share_offer_id'] == 44) { //朋友说指定商品优惠券
                                $couponId = $this->CouponItem->add_coupon_type($brandNames[$brandId], 0, $now, $valid_end, $slice['SharedSlice']['number'], PUBLISH_YES,
                                    COUPON_TYPE_TYPE_SHARE_OFFER, $uid, COUPON_STATUS_VALID, 883);//指定商品id
                            } elseif ($sharedOffer['SharedOffer']['share_offer_id'] == 45) {//朋友说指定商品优惠券
                                $couponId = $this->CouponItem->add_coupon_type($brandNames[$brandId], 0, $now, $valid_end, $slice['SharedSlice']['number'], PUBLISH_YES,
                                    COUPON_TYPE_TYPE_SHARE_OFFER, $uid, COUPON_STATUS_VALID, 1020);//指定商品id
                            } else {
                                //todo set name
                                $brandName = $brandNames[$brandId];
                                if ($brandId == 0) {
                                    $brandName = '朋友说';
                                }
                                if ($brandId == -1) {
                                    //set share name
                                    $brandName = '微分享红包';
                                }
                                $couponId = $this->CouponItem->add_coupon_type($brandName, $brandId, $now, $valid_end, $slice['SharedSlice']['number'], PUBLISH_YES,
                                    COUPON_TYPE_TYPE_SHARE_OFFER, $uid, COUPON_STATUS_VALID);

                            }
                            if ($couponId) {
                                $this->CouponItem->addCoupon($uid, $couponId, $uid, 'shared_offer' . $shared_offer_id);
                                $couponItemId = $this->CouponItem->getLastInsertID();
                                $this->SharedSlice->updateAll(array('coupon_item_id' => $couponItemId),
                                    array('id' => $slice['SharedSlice']['id'], 'coupon_item_id' => 0));
                                App::uses('CakeNumber', 'Utility');
                                $couponNum = CakeNumber::precision($slice['SharedSlice']['number'] / 100, 2);
                                //分享红包
                                if ($brandId == -1) {
                                    if ($send_msg) {
                                        //share red packet
                                        //send for user get packet url
                                        $packet_provider_nickname = $nickNames[$packet_provider];
                                        $title = '您已成功领取' . $nickNames[$sharedOffer['SharedOffer']['uid']] . '分享的' . $packet_provider_nickname . '红包';
                                        $keyword1 = $packet_provider_nickname . '心意一份';
                                        $desc = '谢谢你对' . $packet_provider_nickname . '支持';
                                        $detail_url = WX_HOST . '/weshares/user_share_info/' . $packet_provider;
                                        $this->Weixin->send_packet_received_message($uid, $couponNum, $sharedOffer['ShareOffer']['name'], $title, $detail_url, $keyword1, $desc);
                                        //send for sharer
                                        $this->Weixin->send_packet_be_got_message($sharedOffer['SharedOffer']['uid'], $nickNames[$uid], $couponNum, $packet_provider_nickname . "红包", $detail_url);
                                    }
                                } else {
                                    //normal red packet
                                    $this->Weixin->send_coupon_received_message($uid, 1, "在" . $sharedOffer['ShareOffer']['name'] . "店购买时有效", "有效期至" . friendlyDateFromStr($valid_end, 'full'));
                                    $this->Weixin->send_packet_be_got_message($sharedOffer['SharedOffer']['uid'], $nickNames[$uid], $couponNum, $sharedOffer['ShareOffer']['name'] . "红包");
                                }
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
        $success = true;
        return compact('slices', 'expired', 'accepted', 'just_accepted', 'noMore', 'nickNames', 'sharedOffer', 'uid', 'isOwner', 'total_slice', 'left_slice', 'ownerName', 'success', 'couponNum');
    }
}