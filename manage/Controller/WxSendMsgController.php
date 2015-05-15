<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 5/15/15
 * Time: 20:06
 */

class WxSendMsgController extends AppController{

    var $name = 'WxSendMsg';

    public function admin_to_send_wx_msg(){
        $wxOauthM = ClassRegistry::init('WxOauth');
        $access_token = $wxOauthM->get_base_access_token();
        $wx_curl_option_defaults = array(
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        );
        $media_id = $this->get_wx_new($access_token,$wx_curl_option_defaults);
        $this->set('media_id',$media_id);
    }

    public function admin_send_wx_msg(){
        $this->autoRender=false;
        $mediaId =$_POST['media_id'];
        $wxOauthM = ClassRegistry::init('WxOauth');
        $access_token = $wxOauthM->get_base_access_token();
        $postData = array('touser'=>array('orKydjpLB3ORedyURVnh8NOP52b0','orKydjoWcWp8vLpZ9Z9FoF2IPuKM','orKydjn-VDouoLz3XjG4cWEb7Tu4'),'mpnews'=>array('media_id'=>$mediaId),'msgtype'=>'mpnews');
        $wx_curl_option_defaults = array(
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        );
        if (!empty($access_token)) {
            $curl = curl_init();
            $options = array(
                CURLOPT_URL => 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=' . $access_token,
                CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS
            );
            if (!empty($postData)) {
                $options[CURLOPT_POSTFIELDS] = json_encode($postData);
            }
            curl_setopt_array($curl, ($options + $wx_curl_option_defaults));
            $json = curl_exec($curl);
            curl_close($curl);
            $output = json_decode($json, true);
            $this->log('send wx msg result'.$json);
            if ($output['errcode'] == 0) {
                echo json_encode(array('success'=>true));
                return;
            } else {
                if (!$wxOauthM->should_retry_for_failed_token($output)) {
                    echo json_encode(array('success'=>false));
                    return;
                };
            }
            echo json_encode(array('success'=>false));
            return;
        }
    }

    public function get_wx_new($access_token,$wx_curl_option_defaults){
        //https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=ACCESS_TOKEN
        $postData = array('type'=>'news','offset'=>1,'count'=>1);
        if (!empty($access_token)) {
            $curl = curl_init();
            $options = array(
                CURLOPT_URL => 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=' . $access_token,
                CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS
            );
            if (!empty($postData)) {
                $options[CURLOPT_POSTFIELDS] = json_encode($postData);
            }
            curl_setopt_array($curl, ($options + $wx_curl_option_defaults));
            $json = curl_exec($curl);
            curl_close($curl);
            $output = json_decode($json, true);
            if (!empty($logObj)) {
                $logObj->log("post weixin api send template message output: " . json_encode($output), LOG_DEBUG);
            }
            if(!empty($output['item'])){
                return $output['item'][0]['media_id'];
            }
        }
    }

}