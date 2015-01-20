<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 1/17/15
 * Time: 11:48 AM
 */

class Score {

    var $name="ScoreLog";


    public function add_score_by_bought($userId, $orderId, $total_all_price) {

//        `reason` int(10) NOT NULL,
//  `score` int(10)  NOT NULL,
//  `desc` varchar(255) NOT NULL,

        $reason = SCORE_ORDER_COMMENT;


    }

    public function add_score_by_comment($userId, $orderId, $order_comment_id,$score) {
        $this->data['Score']['user_id']=$userId;
        $this->data['Score']['OrderId']=$orderId;
        $this->data['Score']['commentId']=$order_comment_id;
        $this->data['Score']['created']=date('Y-m-d H:i:s');
        $this->data['Score']['reason']=SCORE_ORDER_COMMENT;
        $this->data['Score']['desc']="对订单(".$orderId.")评价赢取".$score."积分";
        $this->data['Score']['score']=$score;
        return $this->save($this->data);
    }
}