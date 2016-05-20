<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/15/14
 * Time: 4:27 PM
 */

/**
 * Class ShareOffer
 */
class ShareOffer extends AppModel {

    public function findByBrandId($brandId, $limitDef = true, $shareOfferId = null) {
        return $this->_find_by_brandId($brandId, false, null, $limitDef, $shareOfferId);
    }

    public function findBySharerId($sharerId, $limitDef = true, $shareOfferId = null) {
        return $this->_find_by_sharerId($sharerId, false, null, $limitDef, $shareOfferId);
    }

    /**
     * @param $brandId
     * @param $actionTime
     * @param bool $limitDef
     * @return mixed
     */
    public function findValidByBrandId($brandId, $actionTime, $limitDef = true) {
        if ($actionTime == null) {
            return null;
        };
        return $this->_find_by_brandId($brandId, true, $actionTime, $limitDef);
    }

    public function find_all_def_valid_offer($brandId = null, $actionTime = null) {
        return $this->find_all_valid_offer($brandId, $actionTime, true);
    }

    public function find_all_valid_offer($brandId = null, $actionTime = null, $limitDef = false) {

        if (!$actionTime) {
            $actionTime = date(FORMAT_DATETIME);
        }

        $cond = array(
            'published' => 1,
            'deleted' => 0,
            'start <' => $actionTime,
            'end > ' => $actionTime
        );

        if ($brandId !== null) {
            $cond['brand_id'] = $brandId;
        }

        if ($limitDef) {
            $cond['is_default'] = 1;
        }

        return $this->find("all", array('conditions' => $cond));
    }

    /**
     * Get a share offer to sharing for current order && user
     * @param $order
     * @param $uid
     * @param $shareOfferId 指定优惠券
     * @param $commentId 评论
     * @return object ShareOffer object (name, number, id)
     */
    public function query_gen_offer($order, $uid, $shareOfferId = null, $commentId = null) {
        $status = $order['Order']['status'];
        $brandId = $order['Order']['brand_id'];
        $payTime = $order['Order']['pay_time'];
        $total_all_price = $order['Order']['total_all_price'];
        $orderType = $order['Order']['type'];
        $this->log('query_gen_offer order info' . json_encode($order), LOG_INFO);
        if (($status == ORDER_STATUS_DONE
                || $status == ORDER_STATUS_PAID
                || $status == ORDER_STATUS_RECEIVED
                || $status == ORDER_STATUS_SHIPPED)
            && !empty($payTime)
            && (!empty($brandId) || $orderType == ORDER_TYPE_WESHARE_BUY)
            && $total_all_price > 0
        ) {
            $soModel = ClassRegistry::init('ShareOffer');
            //check is share when is share gen type share offer
            //分享订单
            if ($orderType == ORDER_TYPE_WESHARE_BUY) {
                //获取分享ID
                $share_id = $order['Order']['member_id'];
                $weshareM = ClassRegistry::init('Weshare');
                //查找分享
                $weshare = $weshareM->find('first', array(
                    'conditions' => array(
                        'id' => $share_id
                    )
                ));
                //$this->log('offer weshare'.json_encode($weshare));
                if ($weshare) {
                    $share_creator = $weshare['Weshare']['creator'];
                    //根据分享者的配置生成红包
                    $so = $soModel->findBySharerId($share_creator, true, $shareOfferId);
                    $this->log('offer so ' . json_encode($so), LOG_INFO);
                }
            } else {
                //商城购买的逻辑
                $so = $soModel->findByBrandId($brandId, true, $shareOfferId);
            }
            $orderCreator = $order['Order']['creator'];
            if (!empty($so)) {
                //红包最后拆分结果
                $usModel = ClassRegistry::init('SharedOffer');
                $orderId = $order['Order']['id'];
                //根据评论和订单查询是否领取红包了
                if (!empty($commentId)) {
                    $userShared = $usModel->find_user_comment_shared_offer($uid, $orderId, $so['ShareOffer']['id'], $commentId);
                } else {
                    $userShared = $usModel->find_user_shared_offer($uid, $orderId, $so['ShareOffer']['id']);
                }
                //没有领取过
                if (empty($userShared)) {
                    if (!empty($commentId)) {
                        //ratio_percent 生成红包的返点数
                        //$toShareNum = round( (1 * $total_all_price * 100)/100, 0, PHP_ROUND_HALF_DOWN);
                        $toShareNum = COMMENT_SHARE_ORDER_COUPON_MONEY;
                    } else {
                        //ratio_percent 生成红包的返点数
                        $toShareNum = round(($so['ShareOffer']['ratio_percent'] * $total_all_price * 100) / 100, 0, PHP_ROUND_HALF_DOWN);
                    }
                    if ($toShareNum <= 0) {
                        return null;
                    }
                    //切割红包
                    return $this->genSharedSlices($orderCreator, $orderId, $usModel, $so, $toShareNum, $orderType == ORDER_TYPE_WESHARE_BUY, $commentId);
                } else {
                    //已经领取过直接返回数据
                    return array(
                        'name' => $so['ShareOffer']['name'],
                        'number' => $userShared['SharedOffer']['total_number'],
                        'id' => $userShared['SharedOffer']['id'],
                        'status' => $userShared['SharedOffer']['status']
                    );
                }
            }
        }

        return null;
    }

    /**
     * @param $sharerId
     * @param $onlyValid
     * @param $actionTime
     * @param null $limitDef
     * @param null $shareOfferId
     * @return array
     * 根据分享者查询红包配置
     */
    private function _find_by_sharerId($sharerId, $onlyValid, $actionTime, $limitDef = null, $shareOfferId = null) {
        //检查分享者是否激活红包 激活红包才进行红包的发放处理
        $cond = array(
            'sharer_id' => $sharerId,
            'published' => 1,
            'deleted' => 0,
            'sharer_active' => 1
        );
        //指定发放红包
        if ($shareOfferId !== null) {
            $cond['id'] = $shareOfferId;
        }
        if ($limitDef !== null) {
            $cond['is_default'] = $limitDef;
        }

        if ($onlyValid && $actionTime != null) {
            $cond['start <'] = $actionTime;
            $cond['end > '] = $actionTime;
        }

        return $this->find("first", array('conditions' => $cond));
    }

    private function _find_by_brandId($brandId, $onlyValid, $actionTime, $limitDef = null, $shareOfferId = null) {

        $cond = array(
            'brand_id' => $brandId,
            'published' => 1,
            'deleted' => 0,
        );
        //指定发放红包
        if ($shareOfferId !== null) {
            $cond['id'] = $shareOfferId;
        }
        if ($limitDef !== null) {
            $cond['is_default'] = $limitDef;
        }

        if ($onlyValid && $actionTime != null) {
            $cond['start <'] = $actionTime;
            $cond['end > '] = $actionTime;
        }

        return $this->find("first", array('conditions' => $cond));
    }

    /**
     * @param $uid
     * @param $shareOfferId
     * @param $toShareNum
     * @param $is_default_open = false
     * @return array|null
     * 商城商家添加红包
     */
    public function add_shared_slices($uid, $shareOfferId, $toShareNum, $is_default_open = false) {

        $soModel = ClassRegistry::init('ShareOffer');
        $so = $soModel->findById($shareOfferId);

        $sharedModel = ClassRegistry::init('SharedOffer');
        $results = $sharedModel->find('all', array('conditions' => array(
            'uid' => $uid,
//            'share_offer_id' => $shareOfferId,
        ),
            'fields' => array('order_id')
        ));

        $orderIds = Hash::extract($results, '{n}.SharedOffer.order_id');

        $orderId = 0;
        $i = 1;
        //这个逻辑 对于这个用户来说不重复的订单
        while (true) {
            if (array_search($i, $orderIds) === false) {
                $orderId = $i;
                break;
            }
            $i++;
        }

        return $this->genSharedSlices($uid, $orderId, $sharedModel, $so, $toShareNum, $is_default_open);
    }


    /**
     * @param $uid
     * @param $orderId
     * @param $userSharedModel
     * @param $shareOffer 红包配置
     * @param $toShareNum 一共多少钱
     * @param $is_default_open
     * @param $commentId
     * @internal param $order
     * @return array|null
     */
    private function genSharedSlices($uid, $orderId, $userSharedModel, $shareOffer, $toShareNum, $is_default_open = false, $commentId = null) {
        //订单Id和UID无法唯一！！！
        $userSharedModel->create();
        //保存sharedOffer数据
        $saveData = array(
            'share_offer_id' => $shareOffer['ShareOffer']['id'],
            'total_number' => $toShareNum,
            'start' => date(FORMAT_DATETIME, mktime()), //use db time
            'uid' => $uid,
            'order_id' => $orderId,
        );
        if (!empty($commentId)) {
            $saveData['comment_id'] = $commentId;
        }
        //初始化红包状态
        $shared_status = SHARED_OFFER_STATUS_NEW;
        //默认打开红包的话设置状态
        if ($is_default_open) {
            $shared_status = SHARED_OFFER_STATUS_GOING;
            $saveData['status'] = $shared_status;
        }
        //保存SharedOffer数据
        if ($userSharedModel->save($saveData)
        ) {
            //获取到平均数量
            $avg = $shareOffer['ShareOffer']['avg_number'];
            //计算拆分数量
            if (empty($commentId)) {
                //不是评论的就计算
                $split_num = round($toShareNum / $avg, 0, PHP_ROUND_HALF_UP);
            } else {
                //评论产生的数量默认是1
                $split_num = 1;
            }
            $amount_left = $toShareNum;
            //获取成功分享红包的ID
            $shared_id = $userSharedModel->id;
            $mSharedSlice = ClassRegistry::init('SharedSlice');
            //只有总体多于1毛，平均大于1分，才能分成多个红包
            if ($split_num > 1 && $avg >= 1) {
                for ($i = 0; $i < $split_num; $i++) {
                    if ($i < $split_num) {
                        //减一，避免只有两份的情况下一次全部分完
                        //HALF_UP, 避免0分钱红包
                        $num = round(rand($avg / 1.5, $avg * 1.25), 0, PHP_ROUND_HALF_UP);
                        if ($num > $amount_left) {
                            $num = $amount_left;
                        }
                        $amount_left -= $num;
                    } else {
                        $num = $amount_left;
                    }

                    if ($num <= 0) {
                        continue;
                    }

                    $mSharedSlice->create();
                    $mSharedSlice->save(array(
                        'shared_offer_id' => $shared_id,
                        'number' => $num,
                        'accept_time' => null,
                    ));
                }
            } else {
                //不进行拆分直接保存
                $mSharedSlice->create();
                $mSharedSlice->save(array(
                    'shared_offer_id' => $shared_id,
                    'number' => $amount_left,
                    'accept_time' => null,
                ));
            }
            //返回红包数据
            return array(
                'name' => $shareOffer['ShareOffer']['name'],
                'number' => $toShareNum,
                'id' => $shared_id,
                'status' => $shared_status,
            );

        } else {
            return null;
        }
    }
}