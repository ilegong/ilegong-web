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

    public function findByBrandId($brandId) {
        return $this->_find_by_brandId($brandId, false, null);
    }

    /**
     * @param $brandId
     * @param $actionTime
     * @return mixed
     */
    public function findValidByBrandId($brandId, $actionTime) {
        if ($actionTime == null) {
            return null;
        };
        return $this->_find_by_brandId($brandId, true, $actionTime);
    }

    /**
     * Get a share offer to sharing for current order && user
     * @param $order
     * @param $uid
     * @return object ShareOffer object (name, number, id)
     */
    public function query_gen_offer($order, $uid) {
        $status = $order['Order']['status'];
        $brandId = $order['Order']['brand_id'];
        $payTime = $order['Order']['pay_time'];
        if ( ($status == ORDER_STATUS_DONE
                || $status == ORDER_STATUS_PAID
                || $status == ORDER_STATUS_RECEIVED
                || $status == ORDER_STATUS_SHIPPED)
            && !empty($payTime)
            && !empty($brandId)
            && $order['Order']['total_all_price'] > 0
        ) {
            $soModel = ClassRegistry::init('ShareOffer');
            $so = $soModel->findByBrandId($brandId, true, $payTime);
            if (!empty($so)) {
                $usModel = ClassRegistry::init('SharedOffer');
                $userShared = $usModel->find_user_shared_offer($uid, $order['Order']['id'], $so['ShareOffer']['id']);
                if (empty($userShared)){
                    $toShareNum = round( ($so['ShareOffer']['ratio_percent'] * $order['Order']['total_all_price'] * 100)/100, 0, PHP_ROUND_HALF_DOWN);
                    if ($toShareNum <= 0) {
                        return null;
                    }
                    return $this->genSharedSlices($order['Order']['creator'], $order['Order']['id'], $usModel, $so, $toShareNum);
                }
                else {
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


    private function _find_by_brandId($brandId, $onlyValid , $actionTime) {

        $cond = array(
            'brand_id' => $brandId,
            'published' => 1,
            'deleted' => 0,
        );

        if ($onlyValid && $actionTime != null) {
            $cond['start <'] = $actionTime;
            $cond['end > '] = $actionTime;
        }

        return $this->find("first", array('conditions' => $cond));
    }

    public function add_shared_slices($uid, $shareOfferId) {

//        (1, '2014-11-18 16:39:43', '2014-11-30 23:59:59', 7, 20, 4, '', 1, 0, 43, NULL, NULL, '山晋庄园'),
//        (2, '2014-11-18 16:38:12', '2014-11-30 23:59:59', 7, 20, 5, '', 1, 0, 37, NULL, NULL, '西域美农'),
//        (3, '2014-11-18 16:39:43', '2014-11-30 23:59:59', 7, 10, 8, '', 1, 0, 56, NULL, NULL, '河南荥阳河阴石榴'),
//        (4, '2014-11-18 16:39:43', '2014-11-30 23:59:59', 7, 10, 5, '', 1, 0, 76, NULL, NULL, '铁棍山药-艳艳'),
//        (5, '2014-11-18 16:39:43', '2014-11-30 23:59:59', 7, 10, 5, '', 1, 0, 13, NULL, NULL, '五常稻花香-那那'),
//        (6, '2014-11-18 16:39:43', '2014-11-30 23:59:59', 7, 20, 5, '', 1, 0, 88, NULL, NULL, '阿里巴巴—李瑞'),
//        (7, '2014-11-18 16:39:43', '2014-11-30 23:59:59', 7, 10, 5, '', 1, 0, 83, NULL, NULL, '陕西眉县猕猴桃');
        $prices = array(
            1 => 10,
            2 => 6,
            3 => 20,
            4 => 10,
            5 => 10,
            6 => 10,
            7 => 10
        );

        $toShareNum = round($prices[$shareOfferId] * 100, 0, PHP_ROUND_HALF_DOWN);
        if ($toShareNum <= 0) {
            return null;
        }

        $soModel = ClassRegistry::init('ShareOffer');
        $so = $soModel->findById($shareOfferId);

        return $this->genSharedSlices($uid, 0, ClassRegistry::init('SharedOffer'), $so, $toShareNum);
    }


    /**
     * @param $uid
     * @param $orderId
     * @param $userSharedModel
     * @param $shareOffer
     * @param $toShareNum
     * @internal param $order
     * @return array|null
     */
    private function genSharedSlices($uid, $orderId, $userSharedModel, $shareOffer, $toShareNum) {
        //订单Id和UID有唯一索引，避免用户重复点击
        $userSharedModel->create();
        if ($userSharedModel->save(array(
            'share_offer_id' => $shareOffer['ShareOffer']['id'],
            'total_number' => $toShareNum,
            'start' => date(FORMAT_DATETIME, mktime()), //use db time
            'uid' => $uid,
            'order_id' => $orderId,
        ))
        ) {

            $split_num = $shareOffer['ShareOffer']['split_num'];

            $avg = round($toShareNum / $split_num, 0, PHP_ROUND_HALF_UP);
            $amount_left = $toShareNum;

            $shared_id = $userSharedModel->getLastInsertID();
            $mSharedSlice = ClassRegistry::init('SharedSlice');

            //只有总体多于1毛，平均大于1分，才能分成多个红包
            if ($split_num > 1 && $avg >= 1) {
                for ($i = 0; $i < $split_num; $i++) {
                    if ($i < $split_num) {
                        //减一，避免只有两份的情况下一次全部分完
                        //HALF_UP, 避免0分钱红包
                        $num = round(rand($avg / 2, $avg * 2 - 1), 0, PHP_ROUND_HALF_UP);
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
                $mSharedSlice->create();
                $mSharedSlice->save(array(
                    'shared_offer_id' => $shared_id,
                    'number' => $amount_left,
                    'accept_time' => null,
                ));
            }

            return array(
                'name' => $shareOffer['ShareOffer']['name'],
                'number' => $toShareNum,
                'id' => $shared_id,
                'status' => SHARED_OFFER_STATUS_NEW,
            );

        } else {
            return null;
        }
    }
}