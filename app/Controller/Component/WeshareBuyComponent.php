<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 7/8/15
 * Time: 14:37
 */
class WeshareBuyComponent extends Component {

    var $components = array('Weixin');

    public function get_share_offer($shared_offer_id, $uid) {
        $sharedOfferM = ClassRegistry::init('SharedOffer');
        $sharedOffer = $sharedOfferM->findById($shared_offer_id);
        if (empty($sharedOffer)) {
            return array('success' => false, 'code' => 'shared_not_exist', 'msg' => '红包不存在');
        }
        $owner = $sharedOffer['SharedOffer']['uid'];
        $isOwner = $owner == $uid;
        if ($isOwner) {
            if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_NEW) {
                $sharedOfferM->updateAll(array('status' => SHARED_OFFER_STATUS_GOING)
                    , array('id' => $shared_offer_id, 'status' => SHARED_OFFER_STATUS_NEW));
            }
        } else {
            if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_NEW) {
                return array('success' => false, 'code' => 'not_open', 'msg' => '红包尚未开封，请等待红包所有人开封后发出邀请');
            }
        }
        $expired = false;
        $addDays = $sharedOffer['ShareOffer']['valid_days'];
        if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_EXPIRED
            || is_past($sharedOffer['SharedOffer']['start'], $addDays)
        ) {
            $expired = true;
        }
        $sharedSliceM = ClassRegistry::init('SharedSlice');
        $slices = $sharedSliceM->find('all',
            array('conditions' => array('shared_offer_id' => $shared_offer_id), 'order' => 'SharedSlice.accept_time desc')
        );
        $accepted_users = Hash::extract($slices, '{n}.SharedSlice.accept_user');
        $userM = ClassRegistry::init('User');
        $nickNames = $userM->findNicknamesMap(array_merge($accepted_users, array($uid, $owner)));
        $ownerName = $nickNames[$owner];
        if (wxDefaultName($ownerName)) {
            $ownerName = __('朋友说');
        }
        //brandId is pyshuo
        $brandId = $sharedOffer['ShareOffer']['brand_id'];
        $brandNames = $this->Brand->find('list', array(
            'conditions' => array('id' => $brandId),
            'fields' => array('id', 'name')
        ));
        $total_slice = count($slices);
        $valuesCounts = array_count_values($accepted_users);
        $left_slice = empty($valuesCounts[0]) ? 0 : $valuesCounts[0];
        $noMore = $left_slice == 0;
        $accepted = (array_search($uid, $accepted_users) !== false);
        if (!$expired) {
            $just_accepted = 0;
            $couponItemM = ClassRegistry::init('CouponItem');
            if (!$accepted && !$noMore) {
                foreach ($slices as &$slice) {
                    if (empty($slice['SharedSlice']['accept_user'])) {
                        $dt = new DateTime();
                        $now = $dt->format(FORMAT_DATETIME);
                        $dt->add(new DateInterval('P' . $addDays . 'D'));
                        $valid_end = $dt->format(FORMAT_DATETIME);
                        $updated = $sharedSliceM->updateAll(array('accept_user' => $uid, 'accept_time' => '\'' . addslashes($now) . '\''),
                            array('id' => $slice['SharedSlice']['id'], 'accept_user' => 0));
                        if ($updated && $sharedSliceM->getAffectedRows() == 1) {
                            $couponId = $couponItemM->add_coupon_type($brandNames[$brandId], $brandId, $now, $valid_end, $slice['SharedSlice']['number'], PUBLISH_YES,
                                COUPON_TYPE_TYPE_SHARE_OFFER, $uid, COUPON_STATUS_VALID);
                            if ($couponId) {
                                $couponItemM->addCoupon($uid, $couponId, $uid, 'shared_offer' . $shared_offer_id);
                                $couponItemId = $couponItemM->getLastInsertID();
                                $sharedSliceM->updateAll(array('coupon_item_id' => $couponItemId),
                                    array('id' => $slice['SharedSlice']['id'], 'coupon_item_id' => 0));
                                //TODO fix msg title and detail url
                                $this->Weixin->send_coupon_received_message($uid, 1, "在" . $sharedOffer['ShareOffer']['name'] . "购买时有效", "有效期至" . friendlyDateFromStr($valid_end, 'full'));
                                App::uses('CakeNumber', 'Utility');
                                //TODO fix msg title and detail url
                                $this->Weixin->send_packet_be_got_message($sharedOffer['SharedOffer']['uid'], $nickNames[$uid], CakeNumber::precision($slice['SharedSlice']['number'] / 100, 2), $sharedOffer['ShareOffer']['name'] . "红包");
                            }
                            $left_slice -= 1;
                            if ($left_slice == 0) {
                                $sharedOfferM->updateAll(array('status' => SHARED_OFFER_STATUS_OUT)
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
                $sharedOfferM->updateAll(array('status' => SHARED_OFFER_STATUS_EXPIRED)
                    , array('id' => $shared_offer_id, 'status' => SHARED_OFFER_STATUS_GOING));
            }
        }
        $success = true;
        return compact('success', 'slices', 'expired', 'accepted', 'just_accepted', 'noMore', 'nickNames', 'sharedOffer', 'uid', 'isOwner', 'total_slice', 'left_slice', 'ownerName');
    }
}