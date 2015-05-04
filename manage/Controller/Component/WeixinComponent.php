<?php
class WeixinComponent extends Component
{

    public $wx_message_template_ids = array(
        "TUAN_TIP" => "BYtgM4U84etw2qbOyyZzR4FO8a-ddvjy8sgBiAQy64U",
        "JOIN_TUAN" => "P4iCqkiG7_s0SVwCSKyEuJ0NnLDgVNVCm2VQgSGdl-U",
        "REFUND_ORDER" => "j3mRemwa3yq5fjJCiNx5enCMC8C0YEXLehb2HGIiGkw"
    );


    /**
     * @param $user_id
     * @param $title
     * @param $product_name
     * @param $tuan_leader_wx
     * @param $remark
     * @param $deatil_url
     * @return bool
     * 加入一个团购
     */
    public function send_join_tuan_buy_msg($user_id,$title,$product_name,$tuan_leader_wx,$remark,$deatil_url){
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $r = $oauthBindModel->find('first', array('conditions' => array('user_id' => $user_id, 'source' => oauth_wx_source(),)));
        if(empty($r)){
            $user_weixin = false;
        }else{
            $user_weixin = $r['Oauthbind'];
        }
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->wx_message_template_ids["JOIN_TUAN"],
                "url" =>$deatil_url,
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => $title),
                    "Pingou_ProductName" => array("value" => $product_name),
                    "Weixin_ID" => array("value" => $tuan_leader_wx),
                    "Remark" => array("value" => $remark, "color" => "#FF8800")
                )
            );
            return $this->send_weixin_message($post_data);
        }
        return false;
    }

    /**
     * @param $user_id
     * @param $title
     * @param $product_name
     * @param $tuan_leader_wx
     * @param $remark
     * @param $deatil_url
     * @return bool
     * 团购提示信息
     */
    public function send_tuan_tip_msg($user_id,$title,$product_name,$tuan_leader_wx,$remark,$deatil_url){
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $r = $oauthBindModel->find('first', array('conditions' => array('user_id' => $user_id, 'source' => oauth_wx_source(),)));
        if(empty($r)){
            $user_weixin = false;
        }else{
            $user_weixin = $r['Oauthbind'];
        }
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->wx_message_template_ids["TUAN_TIP"],
                "url" =>$deatil_url,
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => $title),
                    "Pingou_ProductName" => array("value" => $product_name),
                    "Weixin_ID" => array("value" => $tuan_leader_wx),
                    "Remark" => array("value" => $remark, "color" => "#FF8800")
                )
            );
            return $this->send_weixin_message($post_data);
        }
        return false;

    }

    /**
     * @param $user_id
     * @param $title
     * @param $product_name
     * @param $refund_money
     * @param $detail_url
     * @param $order_id
     * @param $remark
     * @return bool
     * 退款成功通知
     */
    public function send_refund_order_notify($user_id,$title,$product_name,$refund_money,$detail_url,$order_id,$remark){
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $r = $oauthBindModel->find('first', array('conditions' => array('user_id' => $user_id, 'source' => oauth_wx_source(),)));
        if(empty($r)){
            $user_weixin = false;
        }else{
            $user_weixin = $r['Oauthbind'];
        }
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->wx_message_template_ids["REFUND_ORDER"],
                "url" =>$detail_url,
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => $title),
                    "orderProductPrice" => array("value" => $refund_money.'元'),
                    "orderProductName" => array("value" => $product_name),
                    "orderName" => array("value" => $order_id),
                    "Remark" => array("value" => $remark, "color" => "#FF8800")
                )
            );
            return $this->send_weixin_message($post_data);
        }
        return false;
    }

    public function send_weixin_message($post_data) {
        return send_weixin_message($post_data, $this);
    }
}