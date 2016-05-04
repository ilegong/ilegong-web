<?php
class WeixinComponent extends Component
{

    public $wx_message_template_ids = array(
        "TUAN_TIP" => "BYtgM4U84etw2qbOyyZzR4FO8a-ddvjy8sgBiAQy64U",
        "JOIN_TUAN" => "P4iCqkiG7_s0SVwCSKyEuJ0NnLDgVNVCm2VQgSGdl-U",
        "REFUND_ORDER" => "j3mRemwa3yq5fjJCiNx5enCMC8C0YEXLehb2HGIiGkw",
        "REFUNDING_ORDER" => "0m3XwqqqiUSp0ls830LdL24GHTOVHwHd6hAYDx3xthk",
        "ORDER_LOGISTICS_INFO" => "3uA5ShDuM6amaaorl6899yMj9QvBmIiIAl7T9_JfR54"
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
    public function send_refunding_order_notify($user_id,$title,$product_name,$refund_money,$detail_url,$order_id,$remark){
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
                "template_id" => $this->wx_message_template_ids["REFUNDING_ORDER"],
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

    public function send_share_product_arrival($user_open_id, $detail_url, $title, $order_id, $address, $user_info, $desc) {
        $post_data = array(
            "touser" => $user_open_id,
            "template_id" => $this->wx_message_template_ids['ORDER_LOGISTICS_INFO'],
            "url" => $detail_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "keyword1" => array("value" => $order_id),
                "keyword2" => array("value" => $address),
                "keyword3" => array("value" => $user_info),
                "remark" => array("value" => $desc, "color" => "#FF8800")
            )
        );
        $this->send_weixin_message($post_data);
    }

    public function send_share_paid_msg($user_open_id, $detail_url, $title, $desc) {
        $post_data = array(
            "touser" => $user_open_id,
            "template_id" => 'xJMoewdihfWaopdnP5oSa1qQahuKRMOSMSImyfjVQBE',
            "url" => $detail_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => '分享打款通知'),
                "keyword1" => array("value" => $title),
                "keyword2" => array("value" => $desc),
                "remark" => array("value" => '点击查看详情', "color" => "#FF8800")
            )
        );
        $this->send_weixin_message($post_data);
    }
}