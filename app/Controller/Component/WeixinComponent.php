<?php

class WeixinComponent extends Component {

    public $wx_curl_option_defaults = array(
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    );

    public $components = array('ShareUtil', 'WeshareBuy', 'ShareAuthority', 'PintuanHelper');

    public $wx_message_template_ids = array(
        "ORDER_PAID" => "UXmiPQNz46zZ2nZfDZVVd9xLIx28t66ZPNBoX1WhE8Q",
        "ORDER_SHIPPED" => "87uu4CmlZT-xlZGO45T_XTHiFYAWHQaLv94iGuH-Ke4",
        "ORDER_REBATE" => "DVuV9VC7qYa4H8oP1BaZosOViQ7RrU3v558VrjO7Cv0",
        "COUPON_RECEIVED" => "op8f7Ca1izIU1QVfrdrg7GBqa_KTXHlaGFjUO2EGG8I",
        "COUPON_TIMEOUT" => "KnpyIsYLe6W-8vKDFPVfd9_5WbvKBMn_wQiaIsc1-wE",
        "PACKET_BE_GOT" => "L2nw1khejEMFilcSGKxfm_2zK4cnrHhf4Gv5le0c204",
        "PACKET_RECEIVED" => "vffIekz48NrxDRNbiGP5_xTvCqBHusA_W5pidHhGaHs",
        "TUAN_TIP" => "BYtgM4U84etw2qbOyyZzR4FO8a-ddvjy8sgBiAQy64U",
        "JOIN_TUAN" => "P4iCqkiG7_s0SVwCSKyEuJ0NnLDgVNVCm2VQgSGdl-U",
        "REFUND_ORDER" => "j3mRemwa3yq5fjJCiNx5enCMC8C0YEXLehb2HGIiGkw",
        "REFUNDING_ORDER" => "0m3XwqqqiUSp0ls830LdL24GHTOVHwHd6hAYDx3xthk",
        "PIN_TUAN_FAIL" => "0MAsqVfLFyvlZ3LFdhavuq2PXuMxgi1i7rJcmTP31HU",
        "ORDER_LOGISTICS_INFO" => "3uA5ShDuM6amaaorl6899yMj9QvBmIiIAl7T9_JfR54",
        "RECOMMEND_TEMPLATE_MSG" => "XgB0hibK6F3RXkXrQxT5LilfjQOAYUhjiCQ-XPW2ccw",
        "FAQ_NOTIFY" => "xJMoewdihfWaopdnP5oSa1qQahuKRMOSMSImyfjVQBE",
        "REPAID_NOTIFY" => "CY69fWO3zw8S6dWiPQFs3W9LgVHRMDFXMl_8CeLLWmI",
        "USER_SUB_MSG" => "ee_aZdUrvl_G4F6qSUgZufwnt8oWs9LpG1K8hZ0l3Yg",
        "COMMENT_MSG" => "4P1UuxeCmpU4xVZxBWlkUQDsIGbviUQG6zSakNzvUK4"
    );

    public $sh_wx_message_template_ids = array(
        "ORDER_PAID" => "Ee82jWoDhX3jIzf9_RW88P03TGHe2xisfMwtwTdI7bk",
        "ORDER_SHIPPED" => "NCDgakoXPlPNHBJ1txoZKAt1S0_mYxxQb76qJ0kmN5s",
        "ORDER_REBATE" => "wlscn1lV4Ae59-4-dtBXwNDeO7JDznA5yipRDuBT-VM",
        //"COUPON_RECEIVED" => "op8f7Ca1izIU1QVfrdrg7GBqa_KTXHlaGFjUO2EGG8I",
        //"COUPON_TIMEOUT" => "KnpyIsYLe6W-8vKDFPVfd9_5WbvKBMn_wQiaIsc1-wE",
        //"PACKET_BE_GOT" => "L2nw1khejEMFilcSGKxfm_2zK4cnrHhf4Gv5le0c204",
        //"PACKET_RECEIVED" => "vffIekz48NrxDRNbiGP5_xTvCqBHusA_W5pidHhGaHs",
        "TUAN_TIP" => "BvIavL3GeWaN7ZryPdmQ-95-H2VqO1aMw15x3Jmenjs",
        "JOIN_TUAN" => "0BGy5Cqx0XBYEtSaVbR2ElyqYenejr_JxaWhgStcKXk",
        "REFUND_ORDER" => "Led-IXo06CPgYhnhHmVzgsXYsx5MhA0GFpSDFlO9PAo",
        "REFUNDING_ORDER" => "t7NWpMUucBg253o1apqfh5OndOL5jVpExAUGg4-qS0k",
        "PIN_TUAN_FAIL" => "z5CI4Tm3Z1LFF4r0kDzB-NjZjTPJnJEdWmJ6Tq8bSzU",
        "ORDER_LOGISTICS_INFO" => "_RCsqdUIJGTak2bGwkwdDYTbE_37QoBhIEH5cuWkIEI",
        "RECOMMEND_TEMPLATE_MSG" => "1xeAWXRCBMAqh2ENGyM1eAUzKkfmduQHXoAB-C6HnMg",
        "FAQ_NOTIFY" => "ujFns0Tiq5SAoI5ELiczt4JVdLnE-2Cw4ZNbFFMbyY0",
        //"REPAID_NOTIFY" => "CY69fWO3zw8S6dWiPQFs3W9LgVHRMDFXMl_8CeLLWmI"
        "USER_SUB_MSG" => "7YtirDng-QJ0xCJeVAt-ZInvKd4OtiWRtS3pzmmWD7c",
        "COMMENT_MSG" => "SB1u6e8lK7aBRWKvrjGD75Jh5WcAyj9C4OzxRSca7Gc"
    );

    private function get_template_msg_id($key){
        if(WX_HOST==SH_SITE_HOST){
            $templates = $this->sh_wx_message_template_ids;
        }else{
            $templates = $this->wx_message_template_ids;
        }
        return $templates[$key];
    }

    public $kuaidi100_ship_type = array(
        101 => 'shentong',
        102 => 'yuantong',
        103 => 'yunda',
        104 => 'shunfeng',
        105 => 'ems',
        106 => 'youzhengguonei',
        107 => 'tiantian',
        108 => 'huitongkuaidi',
        109 => 'zhongtong',
        110 => 'quanyikuaidi',
        111 => 'zhaijisong',
        112 => 'quanfengkuaidi',
    );

    public $kuaidi100_url = "http://m.kuaidi100.com/index_all.html";

    public function get_kuaidi_query_url($ship_type, $ship_code) {
        return $this->kuaidi100_url . '?type=' . $this->kuaidi100_ship_type[$ship_type] . '&postid=' . $ship_code;
    }

    public function get_weshare_buy_detail($weshare_id) {
        return WX_HOST . '/weshares/view/' . $weshare_id;
    }

    public function get_pintuan_detail($weshare_id, $group_id) {
        return WX_HOST . '/pintuan/detail/' . $weshare_id . '?tag_id=' . $group_id;
    }

    public function get_user_share_info_url($uid) {
        return WX_HOST . '/weshares/user_share_info/' . $uid;
    }

    public function get_order_query_url($order_no) {

        return WX_HOST . '/orders/detail/' . $order_no;
    }

    public function get_seller_order_query_url() {

        return WX_HOST . '/orders/wait_shipped_orders.html';
    }

    public function get_order_rebate_url() {
        return WX_HOST . '/users/my_coupons.html';
    }

    public function get_rice_detail_url() {
        return WX_HOST . '/t/rice_product';
    }

    public function get_coupon_url() {
        return WX_HOST . '/users/my_coupons.html';
    }

    public function get_packet_url() {
        return WX_HOST . '/users/my_offers.html';
    }

    public function get_weshare_packet_url($weshareId) {
        return WX_HOST . '/weshares/view/' . $weshareId . '?mark=template_msg';
    }


    public function get_access_token() {
        return ClassRegistry::init('WxOauth')->get_base_access_token();
    }

    public function send_coupon_cake_msg($user_id, $coupon_url, $count = 1, $store = "168元冷链到家的海南千层蛋糕大促，20元优惠券马上领   ", $rule = "有效期至2015年05月1日,不参与团购") {
        $first_intro = "亲，恭喜您获得" . $count . "张优惠券";
        $click_intro = "点击详情，获得的此优惠券。";
        return $this->send_coupon_message_on_received($user_id, $store, $rule, $coupon_url, $first_intro, $click_intro);
    }

    public function send_coupon_received_message($user_id, $count = 1, $store = "购买nana家大米时使用", $rule = "有效期至2014年11月15日") {
        $coupon_url = $this->get_coupon_url();
        $first_intro = "亲，恭喜您获得" . $count . "张优惠券";
        $click_intro = "点击详情，立即购买。";

        return $this->send_coupon_message_on_received($user_id, $store, $rule, $coupon_url, $first_intro, $click_intro);
    }

    public function send_coupon_timeout_message($user_id, $coupon_name, $timeout_time, $rule = "无金额限制") {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->get_template_msg_id("COUPON_TIMEOUT"),
                "url" => $this->get_coupon_url(),
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => "亲，您有1张" . $coupon_name . "将于" . $timeout_time . "过期。"),
                    "orderTicketStore" => array("value" => ""),
                    "orderTicketRule" => array("value" => $rule),
                    "remark" => array("value" => "我不想离开您，点击详情，马上使用我。", "color" => "#FF8800")
                )
            );
            return $this->send_weixin_message($post_data);
        }
        return false;
    }


    public function send_order_ship_info_msg($user_id, $msg, $order_id, $ship_company, $good_info, $good_number, $title = null, $product_num = null, $desc = null, $url = null) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            if (empty($title)) {
                $title = "亲，您的订单号为" . $order_id . "的最新物流信息: " . $msg . "如果你已经收货,请点击详情确认收货,可以获取积分(积分可以抵现)。";
            }
            if (empty($product_num)) {
                $product_num = '总数' . $good_number;
            }
            if (empty($desc)) {
                $desc = "点此查看详情";
            }
            if (empty($url)) {
                $url = $this->get_order_query_url($order_id);
            }
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->get_template_msg_id("ORDER_SHIPPED"),
//            "url" => $this->get_kuaidi_query_url($ship_type, $ship_code),
                "url" => $url,
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => $title),
                    "keyword1" => array("value" => $ship_company),
                    "keyword2" => array("value" => $order_id),
                    "keyword3" => array("value" => $good_info),
                    "keyword4" => array("value" => $product_num),
                    "remark" => array("value" => $desc, "color" => "#FF8800")
                )
            );
            return $this->send_weixin_message($post_data);
        }
        return false;
    }

    public function send_tuan_track_log($user_id, $msg, $order_id, $good_info, $good_number) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->get_template_msg_id("ORDER_SHIPPED"),
//            "url" => $this->get_kuaidi_query_url($ship_type, $ship_code),
                "url" => $this->get_order_query_url($order_id),
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => "亲，您的订单号为" . $order_id . "的最新信息: " . $msg . "。如果你已经收货,请点击详情确认收货,可以获取积分(积分可以抵现)。"),
                    "keyword1" => array("value" => '自提'),
                    "keyword2" => array("value" => '自提'),
                    "keyword3" => array("value" => $good_info),
                    "keyword4" => array("value" => '总数' . $good_number),
                    "remark" => array("value" => "点此查看详情", "color" => "#FF8800")
                )
            );
            return $this->send_weixin_message($post_data);
        }
        return false;
    }

    public function send_order_paid_message($open_id, $order, $good) {
        $so = ClassRegistry::init('ShareOffer');
        $offer = $so->query_gen_offer($order, $order['Order']['creator']);

        $number = 0;
        $name = '';
        if (!empty($offer)) {
            $number = $offer['number'];
            $name = $offer['name'];
        }
        $pys_msg = "您的订单支付成功，我们会按照订单中预计的时间为您发货";
        $org_msg = "亲，您的订单已完成付款，商家将即时为您发货。";
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->get_template_msg_id("ORDER_PAID"),
            "url" => $this->get_order_query_url($order['Order']['id']),
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => (($order['Order']['brand_id'] == PYS_BRAND_ID) ? $pys_msg : $org_msg) . (($number > 0 && !empty($name)) ? "同时恭喜您获得" . $name . "红包，点击领取。" : "")),
                "orderProductPrice" => array("value" => $order['Order']['total_all_price']),
                "orderProductName" => array("value" => $good['good_info']),
                "orderAddress" => array("value" => empty($good['ship_info']) ? '' : $good['ship_info']),
                "orderName" => array("value" => $order['Order']['id']),
                "remark" => array("value" => "点击查看订单详情" . ($number > 0 ? "/领取红包" : "") . "。", "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data) && $this->send_share_offer_msg($open_id, $order['Order']['id']);
    }

    public function send_groupon_paid_message($open_id, $price, $url, $order_no, $good_info, $isDone, $isSelf, $isOrganizer, $organizerName, $newMemberName, $leftPeople, $ship_info) {
        if ($isDone) {
            $msg = $isOrganizer ? "亲，您发起的团购已经成团，请等待收货。" : ($isSelf ? "亲，您参加 $organizerName 发起的团购成功，已经成团，请等待 $organizerName 收货。" : "亲，$newMemberName 刚刚加入了我们的团购，已经成团，请等待 $organizerName 收货。");
        } else {
            if ($leftPeople == 1) {
                $msg = $isSelf ? ("亲，您参加 $organizerName 发起的团购成功，等待他提交支付就可以发货啦。") : ($isOrganizer ? "亲，$newMemberName 刚刚加入了我们的团购，现在只需要您提交支付就可以发货啦" : "亲，$newMemberName 刚刚加入了我们的团购，现在只需要发起者提交支付就可以发货啦");
            } else {
                $msg = $isSelf ? ($isOrganizer ? "亲，您发起的团购成团啦，请等待收货" : "亲，您参加 $organizerName 发起的团购成功，现在只差 $leftPeople 人就可以成团啦。") : "亲，$newMemberName 刚刚加入了我们的团购，现在只差 $leftPeople 人就可以成团啦";
            }
        }
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->get_template_msg_id("ORDER_PAID"),
            "url" => $url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $msg),
                "orderProductPrice" => array("value" => $price),
                "orderProductName" => array("value" => $good_info),
                "orderAddress" => array("value" => empty($ship_info) ? '' : $ship_info),
                "orderName" => array("value" => $order_no),
                "remark" => array("value" => "点击查看详情", "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    public function send_order_paid_message_for_seller($seller_open_id, $price, $good_info, $ship_info, $order_no) {
        $post_data = array(
            "touser" => $seller_open_id,
            "template_id" => $this->get_template_msg_id("ORDER_PAID"),
            "url" => $this->get_seller_order_query_url(),
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => "亲，有用户刚刚购买了您家的商品，请及时发货。"),
                "orderProductPrice" => array("value" => $price),
                "orderProductName" => array("value" => $good_info),
                "orderAddress" => array("value" => empty($ship_info) ? '' : $ship_info),
                "orderName" => array("value" => $order_no),
                "remark" => array("value" => "点击详情，查看待发货订单", "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    public function send_rice_paid_message($open_id, $price, $good_info, $ship_info, $order_no) {
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->get_template_msg_id("ORDER_PAID"),
            "url" => $this->get_rice_detail_url(),
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => "亲，分享稻花香链接到朋友圈，朋友通过此链接购买大米成功，即可得大米1斤喔~"),
                "orderProductPrice" => array("value" => $price),
                "orderProductName" => array("value" => $good_info),
                "orderAddress" => array("value" => $ship_info),
                "orderName" => array("value" => $order_no),
                "remark" => array("value" => "点击详情马上分享到朋友圈得大米~", "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    public function send_order_rebate_message($open_id, $buyer_name, $order_no, $price = '******', $paid_time = '刚刚') {
        $friend_name = empty($buyer_name) ? "神秘人" : $buyer_name;
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->get_template_msg_id("ORDER_REBATE"),
            "url" => $this->get_order_rebate_url(),
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => "亲，" . $friend_name . "通过您分享的链接购买了稻花香大米，恭喜您获得优惠劵1张。"),
                "keyword1" => array("value" => $order_no),
                "keyword2" => array("value" => $price),
                "keyword3" => array("value" => date('Y-m-d H:i:s')),
                "keyword4" => array("value" => "粮票1斤"),
                "remark" => array("value" => "点击详情，查询获得的优惠劵。", "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    public function send_order_shipped_message($open_id, $ship_type, $ship_company, $ship_code, $good_info, $good_number, $order_id) {
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->get_template_msg_id("ORDER_SHIPPED"),
//            "url" => $this->get_kuaidi_query_url($ship_type, $ship_code),
            "url" => $this->get_order_query_url($order_id),
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => "亲，您的特产已经从家乡启程啦。"),
                "keyword1" => array("value" => $ship_company),
                "keyword2" => array("value" => $ship_code),
                "keyword3" => array("value" => $good_info),
                "keyword4" => array("value" => $good_number),
                "remark" => array("value" => "点击查看订单详情。", "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }


    //领取红包
    public function send_packet_received_message($user_id, $packet_money, $packet_name = "眉县有机猕猴桃红包", $title = null, $detail_url = null, $keyword1 = null, $desc = null) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            return $this->send_packet_received_message_by_openid($open_id, $packet_money, $packet_name, $title, $detail_url, $keyword1, $desc);
        }
        return false;
    }

    public function send_packet_received_message_by_openid($open_id, $packet_money, $packet_name, $title = null, $detail_url = null, $keyword1 = null, $desc = null) {
        $this->log('send msg title ' . $title . ' detail url ' . $detail_url, LOG_INFO);
        if (empty($detail_url)) {
            $detail_url = $this->get_packet_url();
        }
        if (empty($title)) {
            $title = '亲，恭喜您获得朋友说红包！';
        }
        if (empty($keyword1)) {
            $keyword1 = $packet_name . "红包";
        }
        if (empty($desc)) {
            $desc = '红包可以发送给朋友一起抢，点击详情，分享红包。';
        }
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->get_template_msg_id("PACKET_RECEIVED"),
            "url" => $detail_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "keyword1" => array("value" => $keyword1),
                "keyword2" => array("value" => $packet_money . "元"),
                "remark" => array("value" => $desc, "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }


    //红包被领取
    public function send_packet_be_got_message($user_id, $got_packet_user_name, $got_packet_money, $packet_name = "眉县有机猕猴桃红包", $detail_url = null) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
        if (empty($detail_url)) {
            $detail_url = $this->get_packet_url();
        }
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->get_template_msg_id("PACKET_BE_GOT"),
                "url" => $detail_url,
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => $got_packet_user_name . "领走了您分享的" . $packet_name . "！"),
                    "keyword1" => array("value" => $got_packet_money . "元"),
                    "keyword2" => array("value" => date('Y-m-d H:i:s')),
                    "remark" => array("value" => "点击详情，查看红包。", "color" => "#FF8800")
                )
            );
            return $this->send_weixin_message($post_data);
        }
        return false;
    }

    public function send_weixin_message($post_data) {
        return send_weixin_message($post_data, $this);
    }

    public static function get_order_weshare_product_info($order_info, $carts) {
        $good_info = '';
        $number = 0;
        $send_date = '';
        $ship_info = $order_info['Order']['consignee_name'];
        $weshareId = $order_info['Order']['member_id'];
        if (!empty($order_info['Order']['consignee_mobilephone'])) {
            $ship_info .= ' ' . $order_info['Order']['consignee_mobilephone'];
        }
        $ship_info .= ', ' . $order_info['Order']['consignee_address'];
        $order_id = $order_info['Order']['id'];
        foreach ($carts as $cart) {
            if ($cart['Cart']['order_id'] == $order_id) {
                $good_info = $good_info . $cart['Cart']['name'] . 'x' . $cart['Cart']['num'] . ';';
                $number += $cart['Cart']['num'];
            }
        }
        $weshareModel = ClassRegistry::init('Weshare');
        $weshare = $weshareModel->find('first', array(
            'conditions' => array(
                'id' => $weshareId
            )
        ));
        return array("good_info" => $good_info, "ship_info" => $ship_info, 'weshare_info' => $weshare, 'good_num' => $number, "send_date" => $send_date);
    }

    public static function get_order_good_info($order_info, $carts, $products) {
        $good_info = '';
        $number = 0;
        $send_date = '';
        $ship_info = $order_info['Order']['consignee_name'];
        if (!empty($order_info['Order']['consignee_mobilephone'])) {
            $ship_info .= ' ' . $order_info['Order']['consignee_mobilephone'];
        }
        $ship_info .= ', ' . $order_info['Order']['consignee_address'];
        $order_id = $order_info['Order']['id'];
        foreach ($carts as $cart) {
            if ($cart['Cart']['order_id'] == $order_id) {
                $product = $products[$cart['Cart']['product_id']];
                $name = empty($product['product_alias']) ? $product['name'] : $product['product_alias'];
                $good_info = $good_info . $name . 'x' . $cart['Cart']['num'] . ';';
                $number += $cart['Cart']['num'];
                if (!empty($cart['Cart']['send_date'])) {
                    $send_date = $cart['Cart']['send_date'];
                }
            }
        }
        $brandModel = ClassRegistry::init('Brand');
        $brand = $brandModel->find('first', array(
            'conditions' => array(
                'id' => $order_info['Order']['brand_id']
            ),
            'fields' => array('creator')
        ));
        return array("good_info" => $good_info, "ship_info" => $ship_info, 'brand_info' => $brand, 'good_num' => $number, "send_date" => $send_date);
    }

    /**
     * 订单支付成功后处理
     *
     * @param mixed $order You should write a brief comment about $order here.
     * @access public
     * @return void
     */
    public function notifyPaidDone($order)
    {
        try{
            if ($order['Order']['type'] == ORDER_TYPE_PIN_TUAN) {
                $this->pintuan_buy_order_paid($order);
                $this->PintuanHelper->handle_order_paid($order);
                return;
            }
            if ($order['Order']['type'] == ORDER_TYPE_WESHARE_BUY) {
                Cache::write(USER_SHARE_ORDER_INFO_CACHE_KEY . '_' . $order['Order']['member_id'] . '_' . $order['Order']['creator'], '');
                Cache::write(USER_SHARE_INFO_CACHE_KEY . '_' . $order['Order']['creator'], '');
                Cache::write(INDEX_PRODUCT_SUMMARY_CACHE_KEY . '_' . $order['Order']['member_id'], '');
                $this->weshare_buy_order_paid($order);
                return;
            }
            $this->on_order_status_change($order);
        }catch (Exception $e){
            $this->log('order notify paid done error exception msg '.$e->getMessage());
            return;
        }
    }

    /**
     * @param $pidList
     * @return bool
     */
    protected function hasRebates($pidList) {
        return !empty($pidList) && array_search(PRODUCT_ID_RICE_10, $pidList) !== false;
    }

    public function get_mihoutao_game_url() {
        return WX_HOST . '/t/ag/xirui1412.html?trid=' . urlencode('2bacv21iiFA4Fva6MF3bIUIAhQ4oYegjmWoxgJbIannTF70I8jpqkjcAMAdV');
    }

    //群发猕猴桃活动消息 5000个25元/100元/225元/400元
    public function send_mihoutao_game_message($user_id) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->get_template_msg_id("PACKET_RECEIVED"),
                "url" => $this->get_mihoutao_game_url(),
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => "圣诞节，朋友说与西瑞集团联合送福利啦！"),
                    "keyword1" => array("value" => "摇一摇，摇下20粒就有奖"),
                    "keyword2" => array("value" => "东北珍珠米2.5kg/谷物圈100g/超值优惠券"),
                    "remark" => array("value" => "点击详情，摇一摇。", "color" => "#FF8800")
                )
            );
            return $this->send_weixin_message($post_data);
        }
        return false;
    }

    /**
     * @param $user_id
     * @param $store
     * @param $rule
     * @param $coupon_url
     * @param $first_intro
     * @param $click_intro
     * @return bool
     */
    public function send_coupon_message_on_received($user_id, $store, $rule, $coupon_url, $first_intro, $click_intro) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->get_template_msg_id("COUPON_RECEIVED"),
                "url" => $coupon_url,
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => $first_intro),
                    "orderTicketStore" => array("value" => $store),
                    "orderTicketRule" => array("value" => $rule),
                    "remark" => array("value" => $click_intro, "color" => "#FF8800")
                )
            );
            return $this->send_weixin_message($post_data);
        }
        return false;
    }

    /**
     * @param $order_id
     * @param null $comment_id
     * @return mixed
     * 商场购买或者分享触发红包逻辑
     */
    private function gen_offer($order_id, $comment_id = null) {
        $so = ClassRegistry::init('ShareOffer');
        $orderM = ClassRegistry::init('Order');
        $orderInfo = $orderM->find('first', array(
            'conditions' => array(
                'id' => $order_id
            )
        ));
        //TODO set offer id
        //TODO check is spec product pengyoushuo brand send spec red packet
        //send spec packet
//        if($orderInfo['Order']['brand_id']==PYS_BRAND_ID){
//            $cartM = ClassRegistry::init('Cart');
//            $coconutCartInfo = $cartM->find('first',array('conditions' => array('order_id' => $order_id,'product_id' => 883)));
//            if(!empty($coconutCartInfo)){
//                $offer = $so->query_gen_offer($orderInfo, $orderInfo['Order']['creator'],44);
//                return $offer;
//            }
//            $cherryCartInfo = $cartM->find('first',array('conditions' => array('order_id'=>$order_id,'product_id'=>1020)));
//            if(!empty($cherryCartInfo)){
//                $offer = $so->query_gen_offer($orderInfo, $orderInfo['Order']['creator'],45);
//                return $offer;
//            }
//        }
        $this->log('gen offer order info' . json_encode($orderInfo), LOG_DEBUG);
        $offer = $so->query_gen_offer($orderInfo, $orderInfo['Order']['creator'], null, $comment_id);
        return $offer;
    }

    /**
     * @param $open_id
     * @param $order_id
     * @param null $title
     * @param null $detail_url
     * @param null $keyword1
     * @param null $desc
     * @param null $comment_id
     * @return bool
     * 分享红包处理逻辑
     * 可能是购买或者评论产生的红包
     */
    public function send_share_offer_msg($open_id, $order_id, $title = null, $detail_url = null, $keyword1 = null, $desc = null, $comment_id = null) {
        $offer = $this->gen_offer($order_id, $comment_id);
        $number = 0;
        $name = '';
        if (!empty($offer)) {
            $number = $offer['number'];
            $name = $offer['name'];
        }
        if ($number > 0) {
            if (empty($comment_id)) {
                return $this->send_packet_received_message_by_openid($open_id, $number / 100, $name, $title, $detail_url, $keyword1, $desc);
            } else {
                $this->send_packet_received_message_by_openid($open_id, $number / 100, $name, $title, $detail_url, $keyword1, $desc);
                return $offer;
            }
        }
        return false;
    }

    /**
     * @param $orderId
     */
    public function check_group_buy_complete($orderId) {
        $groupBuyRecordM = ClassRegistry::init('GroupBuyRecord');
        $groupBuyM = ClassRegistry::init('GroupBuy');
        $thisGroupRecord = $groupBuyRecordM->find('first', array(
            'conditions' => array(
                'order_id' => $orderId,
                'is_paid' => 1,
                'deleted' => DELETED_NO
            )
        ));
        if (!empty($thisGroupRecord)) {
            $group_buy_label = $thisGroupRecord['GroupBuyRecord']['group_buy_label'];
            //check group buy is available
            if (group_buy_is_available($group_buy_label)) {
                $product_id = $thisGroupRecord['GroupBuyRecord']['product_id'];
                $groupRecords = $groupBuyRecordM->find('all', array(
                    'conditions' => array(
                        'group_buy_tag' => $thisGroupRecord['GroupBuyRecord']['group_buy_tag'],
                        'is_paid' => 1,
                        'product_id' => $product_id
                    )
                ));
                $groupBuyInfo = $groupBuyM->getGroupBuyProductInfo($product_id);
                $group_buy_num = $groupBuyInfo['group_buy_num'];
                $send_record_id = array();
                if (count($groupRecords) >= $group_buy_num) {
                    $title = '您参加的' . $groupBuyInfo['product_alias'] . '团购成功';
                    $product_name = $groupBuyInfo['name'];
                    $remark = '点击查看详情';
                    $detailurl = WX_HOST . '/group_buy/my_group_buy/' . $groupBuyInfo['id'];
                    foreach ($groupRecords as $record) {
                        if ($record['GroupBuyRecord']['is_send_msg'] == 0) {
                            $user_id = $record['GroupBuyRecord']['user_id'];
                            $result = $this->send_group_buy_complete_msg($user_id, $title, $product_name, 'pyshuo@2015', $remark, $detailurl);
                            if ($result) {
                                $send_record_id[] = $record['GroupBuyRecord']['id'];
                            }
                        }
                    }
                }
                if (!empty($send_record_id)) {
                    $groupBuyRecordM->updateAll(array('is_send_msg' => 1), array('id' => $send_record_id));
                }
            }
        }
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
    public function send_group_buy_complete_msg($user_id, $title, $product_name, $tuan_leader_wx = 'pyshuo@2015', $remark, $deatil_url) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $r = $oauthBindModel->find('first', array('conditions' => array('user_id' => $user_id, 'source' => oauth_wx_source(),)));
        if (empty($r)) {
            $user_weixin = false;
        } else {
            $user_weixin = $r['Oauthbind'];
        }
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->get_template_msg_id("TUAN_TIP"),
                "url" => $deatil_url,
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
     * @param $open_id
     * @param $title
     * @param $product_name
     * @param $tuan_leader_name
     * @param $remark
     * @param $detail_url
     * @return bool
     * 分享购买提示信息
     */
    public function send_share_buy_complete_msg($open_id, $title, $product_name, $tuan_leader_name, $remark, $detail_url) {
        if (!empty($open_id)) {
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->get_template_msg_id("TUAN_TIP"),
                "url" => $detail_url,
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => $title),
                    "Pingou_ProductName" => array("value" => $product_name),
                    "Weixin_ID" => array("value" => $tuan_leader_name),
                    "Remark" => array("value" => $remark, "color" => "#FF8800")
                )
            );
            return $this->send_weixin_message($post_data);
        }
        return false;
    }


    public function send_tuan_paid_msg($open_id, $price, $good_info, $ship_info, $order_no, $order = null, $send_date) {
        $ship_way = $order['Order']['ship_mark'];
        if ($ship_way == 'sf') {
            $tail = '，发货时间是' . $send_date . '。';
        } else {
            $offlineStoreM = ClassRegistry::init('OfflineStore');
            $offline_store = $offlineStoreM->find('first', array(
                'conditions' => array('id' => $order['Order']['consignee_id'])
            ));
            $template = ",到货时间是" . $send_date . "，自提地点是" . $offline_store['OfflineStore']['alias'];
            if ($offline_store['OfflineStore']['type'] == 0) {
                $tail = $template . '，请留意当天到店取货收到的提货码提醒。';
            } else {
                $tail = $template . '，请留意当天到店取货提醒。';
            }
        }
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->get_template_msg_id("ORDER_PAID"),
            "url" => $this->get_order_query_url($order_no),
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => "亲，您的订单已完成付款，" . $tail),
                "orderProductPrice" => array("value" => $price),
                "orderProductName" => array("value" => $good_info),
                "orderAddress" => array("value" => empty($ship_info) ? '' : $ship_info),
                "orderName" => array("value" => $order_no),
                "remark" => array("value" => "点击查看订单详情", "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data) && $this->send_share_offer_msg($open_id, $order_no);
    }

    /**
     * @param $orders
     * 拼团支付成功
     */
    public function pintuan_buy_order_paid($orders) {
        if (count($orders) == 1) {
            $orders = array($orders);
        }
        $user_ids = Hash::extract($orders, '{n}.Order.creator');
        $order_ids = Hash::extract($orders, '{n}.Order.id');
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $cartModel = ClassRegistry::init('Cart');
        $userModel = ClassRegistry::init('User');
        $oauth_binds = $oauthBindModel->find('list', array(
            'conditions' => array('user_id' => $user_ids, 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));
        $users = $userModel->find('all', array(
            'conditions' => array('id' => $user_ids),
            'fields' => array('id', 'username')
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}');
        $carts = $cartModel->find('all', array(
            'conditions' => array('order_id' => $order_ids),
            'fields' => array('Cart.id', 'Cart.num', 'Cart.order_id', 'Cart.send_date', 'Cart.product_id', 'Cart.name'),
        ));
        foreach ($orders as $order) {
            $openid = $oauth_binds[$order['Order']['creator']];
            $good = self::get_order_weshare_product_info($order, $carts);
            $user = $users[$order['Order']['creator']];
            $this->send_pintuan_buy_wx_msg($openid, $order, $good, $user);
            //save pin tuan buy opt log
            //$this->ShareUtil->save_buy_opt_log($order['Order']['creator'], $order['Order']['member_id'], $order['Order']['id']);
        }
    }

    /**
     * @param $orders
     * 微分享支付成通知
     */
    public function weshare_buy_order_paid($orders) {
        if (count($orders) == 1) {
            $orders = array($orders);
        }
        $user_ids = Hash::extract($orders, '{n}.Order.creator');
        $order_ids = Hash::extract($orders, '{n}.Order.id');
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $cartModel = ClassRegistry::init('Cart');
        $userModel = ClassRegistry::init('User');
        $oauth_binds = $oauthBindModel->find('list', array(
            'conditions' => array('user_id' => $user_ids, 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));
        $users = $userModel->find('all', array(
            'conditions' => array('id' => $user_ids),
            'fields' => array('id', 'username')
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}');
        $carts = $cartModel->find('all', array(
            'conditions' => array('order_id' => $order_ids),
            'fields' => array('Cart.id', 'Cart.num', 'Cart.order_id', 'Cart.send_date', 'Cart.product_id', 'Cart.name'),
        ));
        foreach ($orders as $order) {
            $openid = $oauth_binds[$order['Order']['creator']];
            $good = self::get_order_weshare_product_info($order, $carts);
            $user = $users[$order['Order']['creator']];
            $this->send_weshare_buy_wx_msg($openid, $order, $good, $user);
            $cate_id = $order['Order']['cate_id'];
            //处理返利
            if ($cate_id != 0) {
                //check update rebate log add order id change paid status
                $this->ShareUtil->process_order_paid_rebate($cate_id, $order);
                //$this->ShareUtil->update_rebate_log($cate_id, $order);
            }
            //save buy opt log
            $this->ShareUtil->save_buy_opt_log($order['Order']['creator'], $order['Order']['member_id'], $order['Order']['id']);
        }
    }

    /**
     * @param $openid
     * @param $order
     * @param $good
     * @param $user
     * 通知购买者和分享者
     */
    public function send_weshare_buy_wx_msg($openid, $order, $good, $user) {
        if (empty($user) || substr($user['User']['username'], 0, 4) === "pys_") {
            return;
        }
        if ($order['Order']['status'] == ORDER_STATUS_PAID && !empty($openid)) {
            $this->send_weshare_buy_order_paid_msg($openid, $order, $good);
            $this->notify_weshare_buy_creator($order, $good);
        }
    }

    /**
     * @param $openid
     * @param $order
     * @param $good
     * @param $user
     * 拼团的通知
     */
    public function send_pintuan_buy_wx_msg($openid, $order, $good, $user) {
        if (empty($user) || substr($user['User']['username'], 0, 4) === "pys_") {
            return;
        }
        if ($order['Order']['status'] == ORDER_STATUS_PAID && !empty($openid)) {
            $this->send_pintuan_buy_order_paid_msg($openid, $order, $good);
            //$this->notify_weshare_buy_creator($order, $good);
        }
    }

    public function send_pintuan_buy_order_paid_msg($open_id, $order, $good) {
        $weshare_info = $good['weshare_info'];
        $title = $weshare_info['Weshare']['title'];
        $userM = ClassRegistry::init('User');
        $creatorInfo = $userM->findById($weshare_info['Weshare']['creator']);
        $creatorNickName = $creatorInfo['User']['nickname'];
        $org_msg = "亲，您报名了" . $creatorNickName . "分享的" . $title;
        //$org_msg = $org_msg . '，1月5日截止报名，1月6日统一发货。';//todo custom it
        $org_msg = $org_msg . $creatorNickName;
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->get_template_msg_id("ORDER_PAID"),
            "url" => $this->get_pintuan_detail($order['Order']['member_id'], $order['Order']['group_id']),
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $org_msg),
                "orderProductPrice" => array("value" => $order['Order']['total_all_price']),
                "orderProductName" => array("value" => $good['good_info']),
                "orderAddress" => array("value" => empty($good['ship_info']) ? '' : $good['ship_info']),
                "orderName" => array("value" => $order['Order']['id']),
                "remark" => array("value" => "分享，让生活更美。点击查看详情。", "color" => "#FF8800")
            )
        );
        //save relation
        $this->ShareUtil->save_relation($creatorInfo['User']['id'], $order['Order']['creator']);
        return $this->send_weixin_message($post_data);
    }

    public function send_weshare_buy_order_paid_msg($open_id, $order, $good) {
        $weshare_info = $good['weshare_info'];
        $title = $weshare_info['Weshare']['title'];
        $userM = ClassRegistry::init('User');
        $creatorInfo = $userM->findById($weshare_info['Weshare']['creator']);
        $creatorNickName = $creatorInfo['User']['nickname'];
        $org_msg = "亲，您报名了" . $creatorNickName . "分享的" . $title;
        if ($order['Order']['ship_mark'] == SHARE_SHIP_KUAIDI_TAG) {
            $org_msg = $org_msg . '，请留意后续的发货通知。';
        } else {
            $org_msg = $org_msg . '，请留意当天的取货提醒哈。';
        }
        $org_msg = $org_msg . $creatorNickName . '电话:' . $creatorInfo['User']['mobilephone'];
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->get_template_msg_id("ORDER_PAID"),
            "url" => $this->get_weshare_buy_detail($order['Order']['member_id']),
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $org_msg),
                "orderProductPrice" => array("value" => $order['Order']['total_all_price']),
                "orderProductName" => array("value" => $good['good_info']),
                "orderAddress" => array("value" => empty($good['ship_info']) ? '' : $good['ship_info']),
                "orderName" => array("value" => $order['Order']['id']),
                "remark" => array("value" => "分享，让生活更美。点击查看详情。", "color" => "#FF8800")
            )
        );
        $title = '亲，恭喜您获得' . $creatorNickName . '红包！';
        $detail_url = $this->get_weshare_packet_url($weshare_info['Weshare']['id']);
        //save relation
        $this->ShareUtil->save_relation_new($creatorInfo['User']['id'], $order['Order']['creator']);
        return $this->send_weixin_message($post_data) && $this->send_share_offer_msg($open_id, $order['Order']['id'], $title, $detail_url);
    }

    public function notify_weshare_buy_creator($order, $good) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $userModel = ClassRegistry::init('User');
        $weshare_info = $good['weshare_info'];
        $seller_weixin = $oauthBindModel->findWxServiceBindByUid($weshare_info['Weshare']['creator']);
        $price = $order['Order']['total_all_price'];
        $order_creator = $order['Order']['creator'];
        $order_ship_mark = $order['Order']['ship_mark'];
        $order_user = $userModel->find('first', array(
            'conditions' => array(
                'id' => $order_creator
            ),
            'fields' => array('id', 'nickname')
        ));
        $order_creator_name = $order_user['User']['nickname'];
        $good_info = $good['good_info'];
        $ship_info = $good['ship_info'];
        $order_id = $order['Order']['id'];
        $cate_id = $order['Order']['cate_id'];
        if ($seller_weixin != false) {
            $this->log('weshare paid send for creator ' . $seller_weixin['oauth_openid'] . ' order id ' . $order_id . ' weshare id ' . $weshare_info['Weshare']['id'], LOG_INFO);
            $this->send_weshare_buy_paid_msg_for_creator($seller_weixin['oauth_openid'], $price, $good_info, $ship_info, $order_id, $weshare_info, $order_creator_name, $order_ship_mark, $order_user['User']['id'], $cate_id);
            //send paid done msg to manage user
            $share_manage_user_open_ids = $this->ShareAuthority->get_share_manage_auth_user_open_ids($weshare_info['Weshare']['id']);
            if (!empty($share_manage_user_open_ids)) {
                foreach ($share_manage_user_open_ids as $open_id_item) {
                    if ($open_id_item != $seller_weixin['oauth_openid']) {
                        $this->send_weshare_buy_paid_msg_for_creator($open_id_item, $price, $good_info, $ship_info, $order_id, $weshare_info, $order_creator_name, $order_ship_mark, $order_user['User']['id'], $cate_id);
                    }
                }
            }
        }
    }

    /**
     * @param $seller_open_id
     * @param $price
     * @param $good_info
     * @param $ship_info
     * @param $order_no
     * @param $weshare_info
     * @param null $order_creator_name
     * @param string $shipType
     * @param $order_creator
     * @param int $cate_id
     * @return bool
     * 报名通知 发送给分享的创建者
     */
    public function send_weshare_buy_paid_msg_for_creator($seller_open_id, $price, $good_info, $ship_info, $order_no, $weshare_info, $order_creator_name = null, $shipType = '', $order_creator, $cate_id = 0) {
        $title = $weshare_info['Weshare']['title'];
        $detail_url = $this->get_user_share_info_url($order_creator);
        $userM = ClassRegistry::init('User');
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $rebateTrackLog = $rebateTrackLogM->find('first', array(
            'conditions' => array(
                'id' => $cate_id
            )
        ));
        $recommend_user = 0;
        $share_creator = $weshare_info['Weshare']['creator'];
        if (!empty($rebateTrackLog)) {
            $recommend_user = $rebateTrackLog['RebateTrackLog']['sharer'];
        }
        $user_names = $userM->findNicknamesMap(array($share_creator, $recommend_user));
        $show_tile = $user_names[$share_creator] . "，有人报名了您分享的" . $title . "。";
        if (!empty($order_creator_name)) {
            if (empty($rebateTrackLog)) {
                $show_tile = $user_names[$share_creator] . "，" . $order_creator_name . "报名了您分享的" . $title . "，";
            } else {
                $show_tile = $user_names[$share_creator] . "，" . $user_names[$recommend_user] . '推荐的' . $order_creator_name . '报名了您分享的' . $title . '，';
            }
        }
        if ($shipType == SHARE_SHIP_KUAIDI_TAG) {
            $show_tile = $show_tile . '需要快递。';
        }
        if ($shipType == SHARE_SHIP_SELF_ZITI_TAG) {
            $show_tile = $show_tile . '自提点自提。';
        }
        if ($shipType == SHARE_SHIP_PYS_ZITI_TAG) {
            $show_tile = $show_tile . '好邻居自提。';
        }
        $post_data = array(
            "touser" => $seller_open_id,
            "template_id" => $this->get_template_msg_id("ORDER_PAID"),
            "url" => $detail_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $show_tile),
                "orderProductPrice" => array("value" => $price),
                "orderProductName" => array("value" => $good_info),
                "orderAddress" => array("value" => empty($ship_info) ? '' : $ship_info),
                "orderName" => array("value" => $order_no),
                "remark" => array("value" => "分享，让生活更美。点击查看详情。", "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    public function on_order_status_change($orders) {
        if (count($orders) == 1) {
            $orders = array($orders);
        }
        $user_ids = Hash::extract($orders, '{n}.Order.creator');
        $order_ids = Hash::extract($orders, '{n}.Order.id');
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $cartModel = ClassRegistry::init('Cart');
        $productModel = ClassRegistry::init('Product');
        $userModel = ClassRegistry::init('User');
        $oauth_binds = $oauthBindModel->find('list', array(
            'conditions' => array('user_id' => $user_ids, 'source' => oauth_wx_source()),
            'fields' => array('user_id', 'oauth_openid')
        ));
        $users = $userModel->find('all', array(
            'conditions' => array('id' => $user_ids),
            'fields' => array('id', 'username')
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}');
        $carts = $cartModel->find('all', array(
            'conditions' => array('order_id' => $order_ids),
            'fields' => array('Cart.id', 'Cart.num', 'Cart.order_id', 'Cart.send_date', 'Cart.product_id'),
        ));
        $product_ids = Hash::extract($carts, '{n}.Cart.product_id');
        $products_info = $productModel->find('all', array(
            'conditions' => array('id' => $product_ids),
            'fields' => array('id', 'name', 'product_alias')
        ));
        $products = Hash::combine($products_info, '{n}.Product.id', '{n}.Product');
        foreach ($orders as $order) {
            $openid = $oauth_binds[$order['Order']['creator']];
            $good = self::get_order_good_info($order, $carts, $products);
            $user = $users[$order['Order']['creator']];
            $this->send_wx_msg_sms($openid, $order, $good, $user);
        }
    }

    public function send_wx_msg_sms($openid, $order, $good, $user) {
        if (empty($user) || substr($user['User']['username'], 0, 4) === "pys_") {
            return;
        }
        if ($order['Order']['status'] == ORDER_STATUS_PAID && !empty($openid)) {
            $this->send_order_paid_message($openid, $order, $good);
            if ($order['Order']['brand_id'] == PYS_BRAND_ID) {
                $this->send_pay_done_sms($order);
            }
            $this->notify_seller_after_paid($order, $good);
        } elseif ($order['Order']['status'] == ORDER_STATUS_SHIPPED) {
            //
        } else {
            $this->log('invalid order status change, order_id:' . $order['Order']['id'] . ',status:' . $order['Order']['status']);
        }
    }

    public function send_pay_done_sms($order) {
        $mobilephone = $order['Order']['consignee_mobilephone'];
        if ($order['Order']['ship_mark'] == "ziti") {
            $ziti_address = $this->get_ziti_info($order['Order']['consignee_id']);
            $msg = "您的订单付款成功，订单号" . $order['Order']['id'] . "，我们会按照订单中预计的时间将商品送达" . $ziti_address['alias'] . "自提点(" . $ziti_address['owner_phone'] . ")，商品到达自提点后，将再次通知您！";
        } elseif ($order['Order']['ship_mark'] == "kuaidi") {
            $msg = "您的订单付款成功，订单号" . $order['Order']['id'] . "，我们会按照订单中预计的时间为您发货！";
        } else {
            return false;
        }
        message_send($msg, $mobilephone);
    }

    private function get_ziti_info($id) {
        $offlineStoreM = ClassRegistry::init('OfflineStore');
        $ziti_address = $offlineStoreM->find('first', array(
            'conditions' => array('id' => $id)
        ));
        return $ziti_address['OfflineStore'];
    }

    public function notify_seller_after_paid($order, $good) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $seller_weixin = $oauthBindModel->findWxServiceBindByUid($good['brand_info']['Brand']['creator']);
        $price = $order['Order']['total_all_price'];
        $good_info = $good['good_info'];
        $ship_info = $good['ship_info'];
        $order_id = $order['Order']['id'];
        $good_num = $good['good_num'];
        $order_consinessname = $order['Order']['consignee_name'];
        if ($seller_weixin != false) {
            $this->send_order_paid_message_for_seller($seller_weixin['oauth_openid'], $price, $good_info, $ship_info, $order_id);
        }
        $User = ClassRegistry::init('User');
        $brand_creator = $good['brand_info'];
        $bussiness_info = $User->find('first', array('conditions' => array('id' => $brand_creator['Brand']['creator']), 'fields' => array('mobilephone')));
        $bussiness_mobilephone = $bussiness_info['User']['mobilephone'];
        $good_infomation = explode(';', $good_info);
        if ($good_num == 1) {
            $msg = '用户' . $order_consinessname . '刚刚购买了' . $good_infomation[0] . '共' . $good_num . '件商品，订单金额' . $price . '元，请您发货。订单号' . $order_id . '，关注服务号接收更详细信息。';
        } else {
            $msg = '用户' . $order_consinessname . '刚刚购买了' . $good_infomation[0] . '、' . $good_infomation[1] . '等' . $good_num . '件商品，订单金额' . $price . '元，请您发货。订单号' . $order_id . '，关注服务号接收更详细信息。';
        }
        $this->log('brand_creator:' . json_encode($brand_creator) . 'bussiness_info' . json_encode($bussiness_info) . 'bussiness_mobilephone' . json_encode($bussiness_mobilephone) . 'msg' . $msg, LOG_INFO);
        message_send($msg, $bussiness_mobilephone);
    }

    public function send_share_product_arrival($user_open_id, $detail_url, $title, $order_id, $address, $user_info, $desc) {
        $post_data = array(
            "touser" => $user_open_id,
            "template_id" => $this->get_template_msg_id('ORDER_LOGISTICS_INFO'),
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

    public function send_new_member_tip($open_id, $detail_url, $title, $member_name, $desc) {
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->get_template_msg_id("USER_SUB_MSG"),
            "url" => $detail_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "keyword1" => array("value" => $member_name),
                "keyword2" => array("value" => date('Y-m-d H:i:s')),
                "remark" => array("value" => $desc, "color" => "#FF8800")
            )
        );
        $this->send_weixin_message($post_data);
    }

    public function send_comment_template_msg($user_open_id, $detail_url, $title, $order_id, $order_date, $desc) {
        $post_data = array(
            "touser" => $user_open_id,
            "template_id" => $this->get_template_msg_id("COMMENT_MSG"),
            "url" => $detail_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "keyword1" => array("value" => $order_id),
                "keyword2" => array("value" => $order_date),
                "remark" => array("value" => $desc, "color" => "#FF8800")
            )
        );
        $this->send_weixin_message($post_data);
    }

    public function send_rebate_template_msg($user_open_id, $detail_url, $order_id, $order_money, $pay_time, $rebate_money, $title) {
        $post_data = array(
            "touser" => $user_open_id,
            "template_id" => $this->get_template_msg_id('ORDER_REBATE'),
            "url" => $detail_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "keyword1" => array("value" => $order_id),
                "keyword2" => array("value" => $order_money),
                "keyword3" => array("value" => $pay_time),
                "keyword4" => array("value" => $rebate_money),
                "remark" => array("value" => '好东西要一起分享，谢谢你的推荐。点击查看详情。', "color" => "#FF8800")
            )
        );
        $this->send_weixin_message($post_data);
    }

    public function send_recommend_template_msg($user_open_id, $detail_url, $remark, $title, $product_name, $sharer) {
        $post_data = array(
            "touser" => $user_open_id,
            "template_id" => $this->get_template_msg_id('JOIN_TUAN'),
            "url" => $detail_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "Pingou_Action" => array("value" => $title),
                "Pingou_ProductName" => array("value" => $product_name),
                "Weixin_ID" => array("value" => $sharer),
                "Remark" => array("value" => $remark, "color" => "#FF8800")
            )
        );
        $this->send_weixin_message($post_data);
    }

    /**
     * @param $user_open_id
     * @param $recommend_name
     * @param $title
     * @param $remark
     * @param $detail_url
     * @param string $basic_info
     * @param string $position
     * 推荐的模板消息
     */
    public function send_recommend_notify_template_msg($user_open_id, $recommend_name, $title, $remark, $detail_url, $basic_info = '团长', $position = '团长') {
        //删除 [推荐报告通知]
        $post_data = array(
            "touser" => $user_open_id,
            "template_id" => $this->get_template_msg_id('RECOMMEND_TEMPLATE_MSG'),
            "url" => $detail_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "keyword1" => array("value" => $recommend_name),
                "keyword2" => array("value" => $basic_info),
                "keyword3" => array("value" => $position),
                "remark" => array("value" => $remark, "color" => "#FF8800")
            )
        );
        $this->send_weixin_message($post_data);
    }

    /**
     * @param $user_open_id
     * @param $detail_url
     * @param $title
     * @param $msg
     * @param $share_title
     * 用户和分享者互动的模板消息
     */
    public function send_faq_notify_template_msg($user_open_id, $detail_url, $title, $msg, $share_title) {
        $post_data = array(
            "touser" => $user_open_id,
            "template_id" => $this->get_template_msg_id('FAQ_NOTIFY'),
            "url" => $detail_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "keyword1" => array("value" => $share_title),
                "keyword2" => array("value" => $msg),
                "remark" => array("value" => '点击详情，马上查看和回复。', "color" => "#FF8800")
            )
        );
        $this->send_weixin_message($post_data);
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
    public function send_refund_order_notify($user_id, $title, $product_name, $refund_money, $detail_url, $order_id, $remark) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $r = $oauthBindModel->find('first', array('conditions' => array('user_id' => $user_id, 'source' => oauth_wx_source(),)));
        if (empty($r)) {
            $user_weixin = false;
        } else {
            $user_weixin = $r['Oauthbind'];
        }
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->get_template_msg_id("REFUND_ORDER"),
                "url" => $detail_url,
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => $title),
                    "orderProductPrice" => array("value" => $refund_money . '元'),
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
    public function send_refunding_order_notify($user_id, $title, $product_name, $refund_money, $detail_url, $order_id, $remark) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $r = $oauthBindModel->find('first', array('conditions' => array('user_id' => $user_id, 'source' => oauth_wx_source())));
        if (empty($r)) {
            $user_weixin = false;
        } else {
            $user_weixin = $r['Oauthbind'];
        }
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->get_template_msg_id("REFUNDING_ORDER"),
                "url" => $detail_url,
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => $title),
                    "orderProductPrice" => array("value" => $refund_money . '元'),
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
     * @param $user_openid
     * @param $title
     * @param $detail_url
     * @param $price
     * @param $mobile
     * @param $remark
     * @return send result
     * 尾款提醒通知
     */
    public function send_remedial_order_msg($user_openid, $title, $detail_url, $price, $mobile, $remark) {
        //微信公众号 已经删除 [尾款提醒通知]
        $post_data = array(
            "touser" => $user_openid,
            "template_id" => $this->get_template_msg_id('REPAID_NOTIFY'),
            "url" => $detail_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "keyword1" => array("value" => $price . '元'),
                "keyword2" => array("value" => $mobile),
                "remark" => array("value" => $remark, "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    /**
     * @param $user_openid
     * @param $title
     * @param $order_price
     * @param $product_name
     * @param $consignee_address
     * @param $order_id
     * @param $remark
     * @param $detail_url
     * @return send result
     * 物流支付成功通知
     */
    public function send_logistics_order_paid_msg($user_openid, $title, $order_price, $product_name, $consignee_address, $order_id, $remark, $detail_url) {
        $post_data = array(
            "touser" => $user_openid,
            "template_id" => $this->get_template_msg_id('ORDER_PAID'),
            "url" => $detail_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "orderProductPrice" => array("value" => $order_price),
                "orderProductName" => array("value" => $product_name),
                "orderAddress" => array("value" => $consignee_address),
                "orderName" => array("value" => $order_id),
                "remark" => array("value" => $remark, "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    /**
     * @param $user_openid
     * @param $url
     * @param $title
     * @param $order_id
     * @param $start_address
     * @param $consignee_address
     * @param $remark
     * @return bool
     * 物流订单信息通知
     */
    public function send_logistics_order_notify_msg($user_openid, $url, $title, $order_id, $start_address, $consignee_address, $remark) {
        $post_data = array(
            "touser" => $user_openid,
            "template_id" => $this->get_template_msg_id('ORDER_LOGISTICS_INFO'),
            "url" => $url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "keyword1" => array("value" => $order_id),
                "keyword2" => array("value" => $start_address),
                "keyword3" => array("value" => $consignee_address),
                "remark" => array("value" => $remark, "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    /**
     * @param $user_open_id
     * @param $title
     * @param $good_name
     * @param $fail_reason
     * @param $remark
     * @param $url
     * @return bool
     * 拼团失败消息
     */
    public function send_pintuan_fail_msg($user_open_id, $title, $good_name, $fail_reason, $remark, $url) {
        $post_data = array(
            "touser" => $user_open_id,
            "template_id" => $this->get_template_msg_id('PIN_TUAN_FAIL'),
            "url" => $url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "keyword1" => array("value" => $good_name),
                "keyword2" => array("value" => $fail_reason),
                "remark" => array("value" => $remark, "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    /**
     * @param $user_open_id
     * @param $title
     * @param $good_name
     * @param $leader_name
     * @param $remark
     * @param $url
     * @return bool
     * 拼团成功提醒
     */
    public function send_pintuan_success_msg($user_open_id, $title, $good_name, $leader_name, $remark, $url) {
        $post_data = array(
            "touser" => $user_open_id,
            "template_id" => $this->get_template_msg_id('TUAN_TIP'),
            "url" => $url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "Pingou_ProductName" => array("value" => $good_name),
                "Weixin_ID" => array("value" => $leader_name),
                "Remark" => array("value" => $remark, "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    /**
     * @param $user_open_id
     * @param $title
     * @param $good_name
     * @param $leader_name
     * @param $remark
     * @param $url
     * @return bool
     * 发送拼团 预警通知
     */
    public function send_pintuan_warning_msg($user_open_id, $title, $good_name, $leader_name, $remark, $url)
    {
        $post_data = array(
            "touser" => $user_open_id,
            "template_id" => $this->get_template_msg_id('TUAN_TIP'),
            "url" => $url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "Pingou_ProductName" => array("value" => $good_name),
                "Weixin_ID" => array("value" => $leader_name),
                "Remark" => array("value" => $remark, "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }


    /**
     * @param $user_id
     * @param $title
     * @param $product_name
     * @param $tuan_leader_wx
     * @param $remark
     * @param $deatil_url
     * @param $open_id
     * @return bool
     * 加入一个团购
     */
    public function send_join_tuan_buy_msg($user_id, $title, $product_name, $tuan_leader_wx, $remark, $deatil_url, $open_id = null) {
        if (empty($open_id)) {
            $oauthBindModel = ClassRegistry::init('Oauthbind');
            $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
            if ($user_weixin == false) {
                return false;
            }
            $open_id = $user_weixin['oauth_openid'];
        }
        if (empty($open_id)) {
            return false;
        }
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->get_template_msg_id("JOIN_TUAN"),
            "url" => $deatil_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "Pingou_Action" => array("value" => $title),
                "Pingou_ProductName" => array("value" => $product_name),
                "Weixin_ID" => array("value" => $tuan_leader_wx),
                "Remark" => array("value" => $remark, "color" => "#FF8800")
            )
        );
        return send_weixin_message($post_data);
    }




}
