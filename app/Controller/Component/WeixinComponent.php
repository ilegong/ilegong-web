<?php
class WeixinComponent extends Component
{

    public $wx_curl_option_defaults = array(
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    );

    public $wx_message_template_ids = array(
        "ORDER_SHIPPED" => "gKc-mT_ck7NPt6yZEYs_N419Op8wf7n-ytewzqjRXDY"
    );

    public function get_access_token()
    {
        $curl = curl_init();
        $options = array(
            CURLOPT_URL => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . WX_APPID . '&secret=' . WX_SECRET ,
            CURLOPT_CUSTOMREQUEST => 'GET' // GET POST PUT PATCH DELETE HEAD OPTIONS
        );
        curl_setopt_array($curl, ($options + $this->wx_curl_option_defaults));
        $json = curl_exec($curl);
        curl_close($curl);
        $output = json_decode($json, true);
        $this->log("get weixin api access token output: ".$output,LOG_DEBUG);
        if (!empty($output['access_token'])) {
            return $output['access_token'];
        }
        return "";
    }

    public function send_order_shipped_message($open_id, $order_no, $ship_company, $ship_code)
    {
        $access_token = $this->get_access_token();
        $this->log("get weixin api access token: ".$access_token,LOG_DEBUG);
        if (!empty($access_token)) {
            $curl = curl_init();
            $post_data = array(
                "touser" => $open_id,
                "template_id" => $this->wx_message_template_ids["ORDER_SHIPPED"],
                "url" => "http://weixin.qq.com/download",
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => "亲，您的特产已经从家乡启程了，好想快点来到你身边", "color" => "#CCCCCC"),
                    "keyword1" => array("value" => $order_no, "color" => "#CCCCCC"),
                    "keyword2" => array("value" => $ship_company, "color" => "#CCCCCC"),
                    "keyword3" => array("value" => $ship_code, "color" => "#CCCCCC"),
                    "remark" => array("value" => "如有问题请致电18911692346", "color" => "#CCCCCC")
                )
            );
            $options = array(
                CURLOPT_URL => 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token,
                CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS
                CURLOPT_POSTFIELDS => json_encode($post_data)
            );

            curl_setopt_array($curl, ($options + $this->wx_curl_option_defaults));
            $json = curl_exec($curl);
            curl_close($curl);
            $output = json_decode($json, true);
            $this->log("post weixin api send template message output: ".$output,LOG_DEBUG);
            if ($output['errcode'] == 0) {
                return true;
            }
            return false;
        }
        return false;
    }

}