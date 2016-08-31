<?php
App::uses('Model', 'Model');
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 9/28/14
 * Time: 6:06 PM
 */
class WxOauth extends Model {
    public $useDbConfig = 'WxOauth';
    public $useTable = false;

    private $wx_curl_option_defaults = array(
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    );

    const MD_KEY_WX_BASE_ACCESS_TOKEN = "wx_base_access_token";
    const MD_KEY_JS_APITICKET = "wx_js_api_ticket";
    public function getUserInfo($openid, $token, $lang = 'zh_CN') {
        return $this->find('all', array(
            'method' => 'get_user_info',
            'token' => $token,
            'openid' => $openid,
            'lang' => $lang
        ));
    }

    public function cron_update_access_token(){
        $key = self::MD_KEY_WX_BASE_ACCESS_TOKEN;
        $rtn = $this->find('all', array('method' => 'get_base_access_token'));
        $token = $rtn['WxOauth']['access_token'];
        if (!empty($rtn) && $token) {
            Cache::write($key, array('token' => $token, 'expire' => mktime() + 3600));
            return $token;
        }
        return '';
    }

    public function get_base_access_token() {

        $key = self::MD_KEY_WX_BASE_ACCESS_TOKEN;
        $token_data = Cache::read($key);
        if (empty($token_data) || $token_data['expire'] < time() || empty($token_data['token'])) {
//        40014, 40001, 41001 access_token 有关的错误？
            $rtn = $this->find('all', array('method' => 'get_base_access_token'));
            $token = $rtn['WxOauth']['access_token'];
            if (!empty($rtn) && $token) {
                Cache::write($key, array('token' => $token, 'expire'=> mktime() + 3600));
                return $token;
            }
            return '';
        }  else {
            return $token_data['token'];
        }
    }
    private function get_js_api_ticket(){
        $key = self::MD_KEY_JS_APITICKET;
        $data = Cache::read($key);
        if (empty($data) || $data['expire'] < time() || empty($data['ticket'])) {
            $accessToken = $this->get_base_access_token();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = $this->do_curl($url);
            $ticket = $res['ticket'];
            if (!empty($ticket)) {
                Cache::write($key, array('ticket' => $ticket, 'expire'=> time() + 7000));
                return $ticket;
            }
        } else {
            $ticket = $data['ticket'];
        }
        return $ticket;
    }
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public function getSignPackage() {
        $jsapiTicket = $this->get_js_api_ticket();
        $protocol = "http://".WX_HOST;
        $url = "$protocol$_SERVER[REQUEST_URI]";
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
            "appId"     => WX_APPID,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
        );
        return $signPackage;
    }
    public function create_qrcode_by_sceneid($sceneId) {
        if (!empty($sceneId)) {
            $accessToken = $this->get_base_access_token();
            if (!empty($accessToken)) {
                $params = array('access_token' => $accessToken);
                $url = WX_API_PREFIX . "/cgi-bin/qrcode/create";
                return $this->do_curl_body($url, '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$sceneId.'}}}', $params, true);
            } else return false;
        }
        return false;
    }

    public function get_user_info_by_base_token($openid) {
        if (empty($openid)) {
            return null;
        }
        $accessToken = $this->get_base_access_token();
        if (!empty($accessToken)) {
            $rtn = $this->find('all', array('method' => 'get_user_info_by_base_token', 'base_token' => $accessToken, 'openid' => $openid));
            if (!empty($rtn) && $rtn['errcode'] == 0) {
                return $rtn['WxOauth'];
            } else {
                $errcode = $rtn['errcode'];
                $this->clearBaseTokenCacheIfRequired($errcode);
                return null;
            }
        }
    }

    public function should_retry_for_failed_token($output) {
        return ($this->clearBaseTokenCacheIfRequired($output['errcode']));
    }

    public function get_all_users($next_openid) {
        $accessToken = $this->get_base_access_token();
        if (!empty($accessToken)) {
            $params = array('access_token' => $accessToken);
            if ($next_openid) {
                $params['next_openid'] = $next_openid;
            }
            return $this->do_curl(WX_API_PREFIX . "/cgi-bin/user/get?", $params, true);
        } else return false;
    }

    /**
     * clear Base Token Cache If should.
     * @param $errcode
     * @return failed_by_base_token whether the code is cleared and should
     */
    protected function clearBaseTokenCacheIfRequired($errcode) {
        $failed_by_base_token = false;
        if ($errcode == 40014 || $errcode == 40001 || $errcode == 41001) {
            Cache::delete(self::MD_KEY_WX_BASE_ACCESS_TOKEN);
            $failed_by_base_token = true;
        }
        return $failed_by_base_token;
    }

    /**
     * @param $url
     * @param array|string $body
     * @param array $params
     * @param bool $used_base_token_and_check
     * @internal param bool $use_base_token
     * @return mixed
     */
    protected function do_curl_body($url, $body = '', $params = array(), $used_base_token_and_check = false) {
        if (strpos($url, '?') !== strlen($url) - 1  && !empty($params)) {
            $url .= '?';
        }
        foreach($params as $key=>$value) {
            $url .= "&$key=$value";
        }

        if (is_array($body)) {
            $body = json_encode($body);
        }

        $curl = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS
            CURLOPT_POSTFIELDS => $body,
        );
        curl_setopt_array($curl, ($options + $this->wx_curl_option_defaults));
        $this->log("WXOauth-curl:".$url, LOG_DEBUG);
        $time_start = mktime();
        $rtn = curl_exec($curl);

        if ($rtn === FALSE) {
            $error = curl_error($curl);
        }

        curl_close($curl);
        $this->log('resp ('.(mktime() - $time_start).'s) result:'. $rtn .", error=$error");

        $res = json_decode($rtn, true);
        if (is_null($res)) {
            $error = json_last_error();
            throw new CakeException($error);
        }

        if ($used_base_token_and_check) {
            if ($this->clearBaseTokenCacheIfRequired($res['errcode'])){
                return $this->do_curl_body($url, $body, $params, false);
            }
        }

        return $res;
    }


    /**
     * @param $url
     * @param array $params
     * @param bool $used_base_token_and_check
     * @internal param bool $use_base_token
     * @return mixed
     */
    protected function do_curl($url, $params = array(), $used_base_token_and_check = false) {
        if (strpos($url, '?') !== strlen($url) - 1  && !empty($params)) {
            $url .= '?';
        }
        foreach($params as $key=>$value) {
            $url = "&$key=$value";
        }
        $curl = curl_init();
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS
            CURLOPT_POSTFIELDS => '',
        );
        curl_setopt_array($curl, ($options + $this->wx_curl_option_defaults));
        $this->log("WXOauth-curl:".$url, LOG_DEBUG);
        $time_start = mktime();
        $rtn = curl_exec($curl);
        curl_close($curl);
        $this->log('resp ('.(mktime() - $time_start).'s) result:'. $rtn);

        $res = json_decode($rtn, true);
        if (is_null($res)) {
            $error = json_last_error();
            throw new CakeException($error);
        }

        if ($used_base_token_and_check) {
            if ($this->clearBaseTokenCacheIfRequired($res['errcode'])){
                return $this->do_curl($url, $params, false);
            }
        }

        return $res;
    }
    public function is_subscribe_wx_service($uid){
        $key = key_cache_sub($uid);
        $subscribe_status = Cache::read($key);
        if ($subscribe_status == WX_STATUS_SUBSCRIBED ) {
            return true;
        } elseif($subscribe_status == WX_STATUS_UNSUBSCRIBED){
            return false;
        } elseif(empty($subscribe_status)){
            $OauthbindM = ClassRegistry::init('Oauthbind');
            $oauth = $OauthbindM->findWxServiceBindByUid($uid);
            if (!empty($oauth)) {
                $uinfo = $this->get_user_info_by_base_token($oauth['oauth_openid']);
                if (!empty($uinfo)) {
                    $subscribe_status = ($uinfo['subscribe'] != 0 ? WX_STATUS_SUBSCRIBED : WX_STATUS_UNSUBSCRIBED);
                    Cache::write($key, $subscribe_status);
                }
                return $subscribe_status==1;
            }
        }
        return false;
    }
    public function send_kefu($body){
        $accessToken = $this->get_base_access_token();
        if (!empty($accessToken) && !empty($body)) {
            $params = array('access_token' => $accessToken);
            return $this->do_curl_body(WX_API_PREFIX . "/cgi-bin/message/custom/send",$body, $params);
        } else return false;
    }

}