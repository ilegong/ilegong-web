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


    private function injectModel(){
        $this->SharedOffer = ClassRegistry::init('SharedOffer');
        $this->SharedSlice = ClassRegistry::init('SharedSlice');
        $this->User = ClassRegistry::init('User');
        $this->Brand = ClassRegistry::init('Brand');
        $this->CouponItem = ClassRegistry::init('CouponItem');
    }

    private function validSharedOffer($sharedOffer, $uid, $shared_offer_id)
    {
        $success = false;
        $expired = false;
        if (empty($sharedOffer)) {
            return ['success' => $success, 'reason' => 'not_exist', 'msg' => '红包不存在', 'redirect_url' => '/'];
        }
        $owner = $sharedOffer['SharedOffer']['uid'];
        $isOwner = $owner == $uid;
        if ($isOwner) {
            if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_NEW) {
                $this->SharedOffer->updateAll(['status' => SHARED_OFFER_STATUS_GOING]
                    , ['id' => $shared_offer_id, 'status' => SHARED_OFFER_STATUS_NEW]);
            }
        } else {
            if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_NEW) {
                return ['success' => $success, 'reason' => 'not_open', 'msg' => '红包尚未开封，请等待红包所有人开封后发出邀请', 'redirect_url' => '/'];
            }
        }
        $addDays = $sharedOffer['ShareOffer']['valid_days'];
        if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_EXPIRED
            || is_past($sharedOffer['SharedOffer']['start'], $addDays)
        ) {
            //Only ongoing status can go to expired
            if ($sharedOffer['SharedOffer']['status'] == SHARED_OFFER_STATUS_GOING) {
                $this->SharedOffer->updateAll(['status' => SHARED_OFFER_STATUS_EXPIRED]
                    , ['id' => $shared_offer_id, 'status' => SHARED_OFFER_STATUS_GOING]);
            }
            $expired = true;
        }
        return ['success' => true, 'expired' => $expired];
    }

    private function send_dzx_coupon_msg($couponNum, $limitDate)
    {
        $title = '您已领取一张' . $couponNum . '元的阳澄湖大闸蟹红包，有效期截止' . $limitDate . '，请尽快使用！';
        $keyword1 = '朋友说思念红包';
        $desc = '[朋友说] 只为您提供精选品质产品！';
        $detail_url = WX_HOST . '/weshares/view/' . WESHARE_DZX_ID . '.html?from=_template_msg';
        return [$title, $keyword1, $desc, $detail_url];
    }

    /**
     * @param $share_id
     * @param $shared_offer_id
     * @param $uid
     * @param bool $send_msg
     * @return array
     * 添加记录并获取优惠券
     */
    public function gen_sliced_and_receive($share_id, $shared_offer_id, $uid, $send_msg = true)
    {
        $this->injectModel();
        $sharedOffer = $this->SharedOffer->findById($shared_offer_id);
        $validResult = $this->validSharedOffer($sharedOffer, $uid, $shared_offer_id);
        if (!$validResult['success']) {
            return $validResult;
        }
        $hasAccepet = $this->SharedSlice->hasAny(['shared_offer_id' => $shared_offer_id, 'accept_user' => $uid]);
        if ($hasAccepet) {
            return ['success' => false, 'reason' => 'has_accept', 'msg' => '已经领取过该红包'];
        }

        $expired = $validResult['expired'];
        if ($expired) {
            return ['success' => false, 'reason' => 'share_offer_expired', 'msg' => '活动已经过期'];
        }
        $owner = $sharedOffer['SharedOffer']['uid'];
        $addDays = $sharedOffer['ShareOffer']['valid_days'];
        $packet_provider = $sharedOffer['ShareOffer']['sharer_id'];
        $avgNumber = $sharedOffer['ShareOffer']['avg_number'];
        $nickNames = $this->User->findNicknamesMap(array_merge([], array($uid, $owner, $packet_provider)));
        $dt = new DateTime();
        $now = $dt->format(FORMAT_DATETIME);
        $dt->add(new DateInterval('P' . $addDays . 'D'));
        $valid_end = $dt->format(FORMAT_DATETIME);
        $valid_end_date = $dt->format(FORMAT_DATE);
        $insertSlice = $this->SharedSlice->save(['shared_offer_id' => $shared_offer_id,
            'number' => $avgNumber,
            'accept_time' => addslashes($now), 'accept_user' => $uid, 'modified' => addslashes($now), 'created' => addslashes($now)]);
        $brandId = $sharedOffer['ShareOffer']['brand_id'];
        $brandName = "微分享";
        if ($insertSlice) {
            $couponId = $this->CouponItem->add_coupon_type($brandName, $brandId, $now, $valid_end, $insertSlice['SharedSlice']['number'], PUBLISH_YES,
                COUPON_TYPE_TYPE_SHARE_OFFER, $uid, COUPON_STATUS_VALID, $share_id);
            if ($couponId) {
                $this->CouponItem->addCoupon($uid, $couponId, $uid, 'shared_offer' . $shared_offer_id);
                $couponItemId = $this->CouponItem->getLastInsertID();
                $this->SharedSlice->updateAll(array('coupon_item_id' => $couponItemId),
                    array('id' => $insertSlice['SharedSlice']['id'], 'coupon_item_id' => 0));
                App::uses('CakeNumber', 'Utility');
                $couponNum = CakeNumber::precision($insertSlice['SharedSlice']['number'] / 100, 2);
                if ($send_msg) {
                    if($share_id == WESHARE_DZX_ID){
                        $sendMsgParams = $this->send_dzx_coupon_msg($couponNum, $valid_end_date);
                        list($title, $keyword1, $desc, $detail_url) = $sendMsgParams;
                    }else{
                        $packet_provider_nickname = $nickNames[$packet_provider];
                        $title = '您已成功领取' . $nickNames[$sharedOffer['SharedOffer']['uid']] . '分享的' . $packet_provider_nickname . '红包';
                        $keyword1 = $packet_provider_nickname . '心意一份';
                        $desc = '谢谢你对' . $packet_provider_nickname . '支持';
                        $detail_url = WX_HOST . '/weshares/user_share_info/' . $packet_provider;
                        //send for sharer
                        $this->Weixin->send_packet_be_got_message($sharedOffer['SharedOffer']['uid'], $nickNames[$uid], $couponNum, $packet_provider_nickname . "红包", $detail_url);
                    }
                    $this->Weixin->send_packet_received_message($uid, $couponNum, $sharedOffer['ShareOffer']['name'], $title, $detail_url, $keyword1, $desc);
                }
                return ['success' => true, 'couponNum' => $couponNum];
            }
        }
        return ['success' => false];
    }


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
        $this->injectModel();
        $sharedOffer = $this->SharedOffer->findById($shared_offer_id);
        $validResult = $this->validSharedOffer($sharedOffer, $uid, $shared_offer_id);
        if (!$validResult['success']) {
            return $validResult;
        }

        $expired = $validResult['expired'];
        $owner = $sharedOffer['SharedOffer']['uid'];
        $addDays = $sharedOffer['ShareOffer']['valid_days'];
        $slices = $this->SharedSlice->find('all',
            ['conditions' => ['shared_offer_id' => $shared_offer_id], 'order' => 'SharedSlice.accept_time desc']
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