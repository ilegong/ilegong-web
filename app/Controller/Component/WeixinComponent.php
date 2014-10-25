<?php
class WeixinComponent extends Component
{

    public $wx_curl_option_defaults = array(
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    );

    public $wx_message_template_ids = array(
        "ORDER_SHIPPED" => "87uu4CmlZT-xlZGO45T_XTHiFYAWHQaLv94iGuH-Ke4"
    );

    public $kuaidi100_ship_type = array(
        101=>'shentong',
        102=>'yuantong',
        103=>'yunda',
        104=>'shunfeng',
        105=>'ems',
        106=>'youzhengguonei',
        107=>'tiantian',
        108=>'huitongkuaidi',
        109=>'zhongtong',
        110=>'quanyikuaidi',
        111=>'zhaijisong'
    );

    public $kuaidi100_url = "http://m.kuaidi100.com/index_all.html";

    public function get_kuaidi_query_url($ship_type,$ship_code)
    {
        return $this->kuaidi100_url.'?type='.$this->kuaidi100_ship_type[$ship_type].'&postid='.$ship_code;
    }

    public function get_access_token()
    {
       return ClassRegistry::init('WxOauth')->get_base_access_token();
    }

    public function send_order_shipped_message($open_id, $ship_type, $ship_company, $ship_code, $good_info, $good_number)
    {
        $tries = 1;
        while($tries -- > 0) {
        $access_token = $this->get_access_token();
        $this->log("get weixin api access token: ".$access_token,LOG_DEBUG);
        if (!empty($access_token)) {
            $curl = curl_init();
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