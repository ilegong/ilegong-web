<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 1/17/15
 * Time: 11:48 AM
 */

class Score extends AppModel {


    public function add_score_by_bought($userId, $orderId, $total_all_price) {

//        `reason` int(10) NOT NULL,
//  `score` int(10)  NOT NULL,
//  `desc` varchar(255) NOT NULL,

        $reason = SCORE_ORDER_COMMENT;


    }

    public function add_score_by_comment($userId, $score_change, $orderId, $order_comment_id, $award_extra_ids) {
        $desc = '评价订单 '.$orderId.' 获得 '.$score_change.' 个积分';

        if (!empty($award_extra_ids)) {
            $desc .='，包含'.count($award_extra_ids).'种商品(ID 为'.implode('、', $award_extra_ids).')的抢先评论奖励';
        }
        $data = json_encode(array('order_id' => $orderId, 'order_comment_id' => $order_comment_id));
        return $this->save_score_log($userId, $score_change, SCORE_ORDER_COMMENT, $data, $desc);
    }

    public function spent_score_by_order($userId, $spent, $order_id_to_scores) {
        $reason = SCORE_ORDER_SPENT;
        $data = json_encode($order_id_to_scores);
        $desc = '订单' . implode('、', array_keys($order_id_to_scores)) . '使用' . $spent . '积分';

        return $this->save_score_log($userId, -$spent, $reason, $data, $desc);

    }

    public function find_user_score_logs($userId, $start = PHP_INT_MAX, $limit = 10) {
        return $this->find('all', array(
            'conditions' => array('user_id' => $userId, 'id' <= $start),
            'limit' => $limit,
            'order' => 'id desc',
        ));
    }

    /**
     * @param $userId
     * @param $change
     * @param $reason
     * @param $data
     * @param $desc
     * @return mixed
     */
    protected function save_score_log($userId, $change, $reason, $data, $desc) {
        return $this->save(array(
            'user_id' => $userId,
            'reason' => $reason,
            'data' => $data,
            'desc' => $desc,
            'score' => $change,
        ));
    }
}