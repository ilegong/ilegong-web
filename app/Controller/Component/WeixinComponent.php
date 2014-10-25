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
        "ORDER_SHIPPED" => "87uu4CmlZT-xlZGO45T_XTHiFYAWHQaLv94iGuH-Ke4"
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
        111 => 'zhaijisong'
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

    public function get_access_token()
    {
        return ClassRegistry::init('WxOauth')->get_base_access_token();
    }

    public function send_order_paid_message($open_id, $price, $good_info, $ship_info, $order_no)
    {
        $post_data = array(
            "touser" => $open_id,
            "template_id" => $this->wx_message_template_ids["ORDER_PAID"],
            "url" => $this->get_order_query_url($order_no),
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => "亲，您的订单已完成付款，商家将即时为您发货。"),
                "orderProductPrice" => array("value" => $price),
                "orderProductName" => array("value" => $good_info),
                "orderAddress" => array("value" => $ship_info),
                "orderName" => array("value" => $order_no),
                "remark" => array("value" => "点击详情，查询订单。", "color" => "#FF8800")
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

}