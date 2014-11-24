<?php
class WeixinComponent extends Component
{

    public $wx_curl_option_defaults = array(
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    );

    public $wx_message_template_ids = array(
        "ORDER_PAID" => "UXmiPQNz46zZ2nZfDZVVd9xLIx28t66ZPNBoX1WhE8Q",
        "ORDER_SHIPPED" => "87uu4CmlZT-xlZGO45T_XTHiFYAWHQaLv94iGuH-Ke4",
        "ORDER_REBATE" => "DVuV9VC7qYa4H8oP1BaZosOViQ7RrU3v558VrjO7Cv0",
        "COUPON_RECEIVED" => "op8f7Ca1izIU1QVfrdrg7GBqa_KTXHlaGFjUO2EGG8I",
        "COUPON_TIMEOUT" => "KnpyIsYLe6W-8vKDFPVfd9_5WbvKBMn_wQiaIsc1-wE"
    );

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

    public function get_kuaidi_query_url($ship_type, $ship_code)
    {
        return $this->kuaidi100_url . '?type=' . $this->kuaidi100_ship_type[$ship_type] . '&postid=' . $ship_code;
    }

    public function get_order_query_url($order_no)
    {

        return WX_HOST . '/orders/detail/' . $order_no;
    }

    public function get_seller_order_query_url()
    {

        return WX_HOST . '/orders/tobe_shipped_orders.html';
    }

    public function get_order_rebate_url()
    {
        return WX_HOST . '/users/my_coupons.html';
    }

    public function get_rice_detail_url()
    {
        return WX_HOST . '/t/rice_product';
    }

    public function get_coupon_url()
    {
        return WX_HOST . '/users/my_coupons.html';
    }


    public function get_access_token()
    {
        return ClassRegistry::init('WxOauth')->get_base_access_token();
    }

    public function send_coupon_received_message($user_id, $count=1, $store="购买nana家大米时使用", $rule="有效期至2014年11月15日")
    {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->wx_message_template_ids["COUPON_RECEIVED"],
                "url" => $this->get_coupon_url(),
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => "亲，恭喜您获得".$count."张优惠券"),
                    "orderTicketStore" => array("value" => $store),
                    "orderTicketRule" => array("value" => $rule),
                    "remark" => array("value" => "点击详情，查询获得的优惠券。", "color" => "#FF8800")
                )
            );
            return $this->send_weixin_message($post_data);
        }
        return false;
    }

    public function send_coupon_timeout_message($user_id, $coupon_name, $timeout_time, $rule = "无金额限制")
    {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->wx_message_template_ids["COUPON_TIMEOUT"],
                "url" => $this->get_coupon_url(),
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => "亲，您有1张".$coupon_name."将于".$timeout_time."过期。"),
                    "orderTicketStore" => array("value" => ""),
                    "orderTicketRule" => array("value" => $rule),
                    "remark" => array("value" => "我不想离开您，点击详情，马上使用我。", "color" => "#FF8800")
                )
            );
            return $this->send_weixin_message($post_data);
        }
        return false;
    }

    public function send_order_paid_message($open_id, $price, $good_info, $ship_info, $order_no, $order = null)
    {
        $so = ClassRegistry::init('ShareOffer');
        $offer = $so->query_gen_offer($order, $order['Order']['creator']);

        $number = 0;
        $name = '';
        if(!empty($offer)) {
            $number = $offer['number'];
            $name = $offer['name'];
        }

        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->wx_message_template_ids["ORDER_PAID"],
            "url" => $this->get_order_query_url($order_no),
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => "亲，您的订单已完成付款，商家将即时为您发货。". (($number > 0 && !empty($name)) ? "同时恭喜您获得".$name."红包，点击领取。" : "")),
                "orderProductPrice" => array("value" => $price),
                "orderProductName" => array("value" => $good_info),
                "orderAddress" => array("value" => empty($ship_info)?'':$ship_info),
                "orderName" => array("value" => $order_no),
                "remark" => array("value" => "点击查看订单详情".($number > 0 ? "/领取红包":"")."。", "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    public function send_order_paid_message_for_seller($seller_open_id, $price, $good_info, $ship_info, $order_no)
    {
        $post_data = array(
            "touser" => $seller_open_id,
            "template_id" => $this->wx_message_template_ids["ORDER_PAID"],
            "url" => $this->get_seller_order_query_url(),
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => "亲，有用户刚刚购买了您家的商品，请及时发货。"),
                "orderProductPrice" => array("value" => $price),
                "orderProductName" => array("value" => $good_info),
                "orderAddress" => array("value" => empty($ship_info)?'':$ship_info),
                "orderName" => array("value" => $order_no),
                "remark" => array("value" => "点击详情，查看待发货订单", "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    public function send_rice_paid_message($open_id, $price, $good_info, $ship_info, $order_no)
    {
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->wx_message_template_ids["ORDER_PAID"],
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

    public function send_order_rebate_message($open_id, $buyer_name, $order_no, $price = '******', $paid_time = '刚刚')
    {
        $friend_name = empty($buyer_name) ? "神秘人" : $buyer_name;
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->wx_message_template_ids["ORDER_REBATE"],
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

    public function send_order_shipped_message($open_id, $ship_type, $ship_company, $ship_code, $good_info, $good_number)
    {
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->wx_message_template_ids["ORDER_SHIPPED"],
            "url" => $this->get_kuaidi_query_url($ship_type, $ship_code),
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => "亲，您的特产已经从家乡启程啦。"),
                "keyword1" => array("value" => $ship_company),
                "keyword2" => array("value" => $ship_code),
                "keyword3" => array("value" => $good_info),
                "keyword4" => array("value" => $good_number),
                "remark" => array("value" => "点击详情，查询快递状态。", "color" => "#FF8800")
            )
        );
        return $this->send_weixin_message($post_data);
    }

    public function send_weixin_message($post_data)
    {
        $tries = 2;
        while ($tries-- > 0) {
            $access_token = $this->get_access_token();
            $this->log("get weixin api access token: " . $access_token, LOG_DEBUG);
            if (!empty($access_token)) {
                $curl = curl_init();
                $options = array(
                    CURLOPT_URL => 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token,
                    CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS
                    CURLOPT_POSTFIELDS => json_encode($post_data)
                );

                curl_setopt_array($curl, ($options + $this->wx_curl_option_defaults));
                $json = curl_exec($curl);
                curl_close($curl);
                $output = json_decode($json, true);
                $this->log("post weixin api send template message output: " . $output, LOG_DEBUG);
                if ($output['errcode'] == 0) {
                    return true;
                } else {
                    if (!ClassRegistry::init('WxOauth')->should_retry_for_failed_token($output)) {
                        return false;
                    };
                }
                return false;
            }
        }
        return false;
    }

    public static function get_order_good_info($order_info){
        $good_info ='';
        $ship_info = $order_info['Order']['consignee_name'].','.$order_info['Order']['consignee_address'].','.$order_info['Order']['consignee_mobilephone'];
        $cartModel = ClassRegistry::init('Cart');
        $carts = $cartModel->find('all',array(
            'conditions'=>array('order_id' => $order_info['Order']['id'])));
        foreach($carts as $cart){
            $good_info = $good_info.$cart['Cart']['name'].' x '.$cart['Cart']['num'].';';
        }
        $pids = Hash::extract($carts, '{n}.Cart.product_id');

        $brandModel = ClassRegistry::init('Brand');
        $brand = $brandModel->find('first',array(
            'conditions'=>array(
                'id' => $order_info['Order']['brand_id']
            )
        ));
        return array("good_info"=>$good_info,"ship_info"=>$ship_info, 'pid_list' => $pids, 'brand_info' => $brand);
    }

    /**
     * @param $order
     */
    public function notifyPaidDone($order) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $user_weixin = $oauthBindModel->findWxServiceBindByUid($order['Order']['creator']);

        $good = self::get_order_good_info($order);
        $seller_weixin = $oauthBindModel->findWxServiceBindByUid($good['brand_info']['Brand']['creator']);

        $this->log("good info:" . $good['good_info'] . " ship info:" . $good['ship_info']);

        $price = $order['Order']['total_all_price'];
        $good_info = $good['good_info'];
        $ship_info = $good['ship_info'];
        $order_id = $order['Order']['id'];

        if ($user_weixin != false) {
            $open_id = $user_weixin['oauth_openid'];
            if ($this->hasRebates($good['pid_list'])) {
                $this->send_rice_paid_message($open_id, $price, $good_info, $ship_info, $order_id);

                $could_rebate_ids = array(PRODUCT_ID_RICE_10);
                foreach($could_rebate_ids as $pid) {
                    $be_recommend_uid = $order['Order']['creator'];
                    $recUserId = find_latest_clicked_from($be_recommend_uid, $pid);
                    $this->log("find_latest_clicked_from: $recUserId, be_recommend_uid $be_recommend_uid ");
                    if ($recUserId) {
                        $uModel = ClassRegistry::init('User');
                        $fromUser = $uModel->findById($recUserId);
                        if (!empty($fromUser)) {
                            $toUser = $uModel->findById($be_recommend_uid);
                            if (!empty($toUser)) {


                                try {
                                    $ciModel = ClassRegistry::init('CouponItem');
                                    $ciModel->addCoupon($recUserId, COUPON_TYPE_RICE_1KG, 'rebate_'.$pid.'_'.$order_id);
                                }catch(Exception $e) {
                                    $this->log("exception to add coupon:".$recUserId.", for used user:".$be_recommend_uid);
                                }

                                $buyer_name = $toUser['User']['nickname'];
                                $recOpenId = $oauthBindModel->findWxServiceBindByUid($recUserId);
                                if (!empty($recOpenId)) {
                                    $oauth_openid = $recOpenId['oauth_openid'];
                                    if ($oauth_openid) {
                                        $this->send_order_rebate_message($oauth_openid, $buyer_name, $order_id);
                                    }
                                }
                            }
                            }
                    }
                }


            }  else {
                $this->send_order_paid_message($open_id, $price, $good_info, $ship_info, $order_id, $order);
            }

        }

        if($seller_weixin != false){
            $this->send_order_paid_message_for_seller($seller_weixin['oauth_openid'], $price, $good_info, $ship_info, $order_id);
        }
    }

    /**
     * @param $pidList
     * @return bool
     */
    protected function hasRebates($pidList) {
        return !empty($pidList) && array_search(PRODUCT_ID_RICE_10, $pidList) !== false;
    }

}