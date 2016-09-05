<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 1/17/15
 * Time: 11:48 AM
 */

class Score extends AppModel {


    public function add_score_by_bought($userId, $orderId, $total_all_price) {
        $reason = SCORE_ORDER_DONE;
        $score_change = round($total_all_price, 0, PHP_ROUND_HALF_DOWN);
        if ($score_change > 0) {
            $desc = '订单 ' . $orderId . ' 交易完成获得 ' . $score_change . ' 个积分';

            $data = json_encode(array('order_id' => $orderId));
            return $this->save_score_log($userId, $score_change, $reason, $data, $desc, $orderId);
        } else {
            return 0;
        }
    }

    public function cancel_score_by_bought($userId, $orderId) {
        $found = $this->find('first', array(
            'conditions' => array('user_id' => $userId, 'order_id' => $orderId, 'reason' => SCORE_ORDER_SPENT),
        ));
        if (!empty($found)) {
            $desc = '订单 '. $orderId .' 退款取消积分';
            $this->save_score_log($userId, $found['Score']['score'], SCORE_ORDER_SPENT_CANCEL, json_encode(array('order_id' => $orderId,)), $desc, $orderId);
        }
    }

    public function add_score_by_refer_user_first_order($score_change, $referral, $referral_name, $from_uid){
        $desc = '您推荐的用户"'.$referral_name.'"首次完成下单获得'.$score_change.'个积分';
        return $this->save_score_log($from_uid, $score_change, SCORE_REFERRAL_FIRST_ORDER, json_encode(array('referral_id' => $referral)), $desc);
    }

    public function add_score_by_refer_bind($score_change, $referral, $referral_name, $from_uid) {
        $desc = '您推荐的用户"'.$referral_name.'"完成注册获得'.$score_change.'个积分';
        return $this->save_score_log($from_uid, $score_change, SCORE_REFERRAL_BIND_OK, json_encode(array('referral_id' => $referral)), $desc);
    }

    public function add_score_by_refer_bind_mobile($score_change, $referral, $uid){
        $desc = '您完成绑定手机号码获得'.$score_change.'个积分';
        return $this->save_score_log($referral, $score_change, SCORE_REFERRAL_BIND_OK_TO, json_encode(array('refer_user_id' => $uid)), $desc);
    }

    public function add_score_by_refer_accept_order($score_change, $referral, $referral_name, $from_uid) {
        $desc = '您推荐的用户"'.$referral_name.'"完成第一单，您获得'.$score_change.'个积分，可去"推荐有礼"页面查看最新状态';
        return $this->save_score_log($from_uid, $score_change, SCORE_REFERRAL_BIND_OK, json_encode(array('referral_id' => $referral)), $desc);
    }

    public function add_score_by_comment($userId, $score_change, $orderId, $order_comment_id, $award_extra_ids) {
        $desc = '评价订单 '.$orderId.' 获得 '.$score_change.' 个积分';

        if (!empty($award_extra_ids)) {
            $desc .='，包含'.count($award_extra_ids).'种商品(ID为'.implode('、', $award_extra_ids).')的抢先评论奖励';
        }
        $data = json_encode(array('order_id' => $orderId, 'order_comment_id' => $order_comment_id));
        return $this->save_score_log($userId, $score_change, SCORE_ORDER_COMMENT, $data, $desc, $orderId);
    }

    public function add_score_by_bind_mobile($userId, $score_change, $mobile){
        $desc = '绑定手机号码获得' . $score_change . ' 个积分';
        $data = json_encode(['mobile' => $mobile]);
        return $this->save_score_log($userId, $score_change, SCORE_BIND_MOBILE_OK, $data, $desc);
    }

    public function spent_score_by_order($userId, $spent, $order_id_to_scores) {
        $reason = SCORE_ORDER_SPENT;
        $data = json_encode($order_id_to_scores);
        $desc = '订单' . implode('、', array_keys($order_id_to_scores)) . '使用' . $spent . '积分';

        return $this->save_score_log($userId, -$spent, $reason, $data, $desc);
    }

    public function restore_score_by_undo_order($userId, $spent, $order_id) {
        if($spent > 0) {
            $reason = SCORE_ORDER_SPENT_UNDO;
            $desc = '取消订单' . $order_id . '返还' . $spent . '积分';
            return $this->save_score_log($userId, $spent, $reason, '', $desc);
        }  else return false;
    }

    public function find_user_score_logs($userId, $start = PHP_INT_MAX, $limit = 10) {
        return $this->find('all', array(
            'conditions' => array('user_id' => $userId, 'id' <= $start),
            'limit' => $limit,
            'order' => 'id desc',
        ));
    }

    public function find_user_score_logs_by_page($userId, $page, $limit = 10){
        return $this->find('all', [
            'conditions' => ['user_id' => $userId],
            'page' => $page,
            'limit' => $limit,
            'order' => ['id DESC']
        ]);
    }

    /**
     * @param $userId
     * @param $change
     * @param $reason
     * @param $data
     * @param $desc
     * @param null $orderId
     * @return mixed
     */
    protected function save_score_log($userId, $change, $reason, $data, $desc, $orderId = null) {
        $saved = $this->save(array(
            'user_id' => $userId,
            'reason' => $reason,
            'data' => $data,
            'desc' => $desc,
            'score' => $change,
            'order_id' => empty($orderId) ? 0 : $orderId,
        ));
        if ($saved) {
            $action = action_of_score_item($change, $reason);
            $this->send_score_change_message($userId, $desc, $action, $change);
        }
        return $saved;
    }


    public function send_score_change_message($user_id, $intro_desc, $action, $score_change, $click_desc = null)
    {

//        {{first.DATA}}
//
//        {{FieldName.DATA}}:{{Account.DATA}}
//        {{change.DATA}}积分:{{CreditChange.DATA}}
//        积分余额:{{CreditTotal.DATA}}
//        {{Remark.DATA}}

        if (empty($click_desc)) {
            try {
                $left_to_follow = (WX_STATUS_UNSUBSCRIBED == user_subscribed_pys($user_id));
                $left_to_follow_score = $left_to_follow ? 50 : 0;
                $orderM = ClassRegistry::init('Order');
                $left_to_comment = $orderM->count_to_comments($user_id);
                $score_to_comment = $left_to_comment * 100; //Should adjust
                list($left_receive_cnt, $left_receive_total) = $orderM->count_to_confirm_received($user_id);
                $left_got = $score_to_comment + $left_to_follow_score + $left_receive_total;
            } catch (Exception $e) {
                $left_got = 0;
                $this->log('error to calculate left to score:' . $e);
            }

            $click_desc = ($left_got > 0 ? '您有 ' . $left_got . ' 积分待领取' : '');
        }


        try {
            $userM = ClassRegistry::init('User');
            $totalScore = $userM->get_score($user_id);
            $totalScore += $score_change;

            $click_url = !empty($click_url) ? $click_url : 'http://' . WX_HOST . '/scores/more_score.html';

            $oauthBindModel = ClassRegistry::init('Oauthbind');
            $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
            if ($user_weixin != false) {
                $open_id = $user_weixin['oauth_openid'];
                $post_data = array(
                    "touser" => $open_id,
                    "template_id" => 'SpyG5LYbgkJrlgKNM7bWzCaqXdoUOOkO_G14Dxk0P5Y',
                    "url" => $click_url,
                    "topcolor" => "#FF0000",
                    "data" => array(
                        "first" => array("value" => $intro_desc),
                        "FieldName" => array("value" => "有 效 期 "),
                        "Account" => array("value" => '2015年12月31日前'),
                        "change" => array("value" => $action),
                        "CreditChange" => array("value" => abs($score_change)),
                        "CreditTotal" => array("value" => $totalScore),
                        "Remark" => array("value" => $click_desc, "color" => "#FF8800")
                    )
                );
                return send_weixin_message($post_data);
            }
            return false;
        }catch(Exception $e) {
            $this->log('error to send_score_change_message:(uid='.$user_id.', action='.$action.', intro_desc='.$intro_desc.'):'.$e);
            return false;
        }
    }
}