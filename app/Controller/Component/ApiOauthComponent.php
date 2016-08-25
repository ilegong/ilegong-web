<?php

/**
 * Created by PhpStorm.
 * User: ellipsis
 * Date: 16/8/22
 * Time: 下午4:29
 */
class ApiOauthComponent extends Component
{
    public function baseAccessToken()
    {
        $url = OAUTH2_URL."/api/oauth/token";
        $data = [
            'grant_type' => 'client_credentials'
        ];
        $res = $this->curl($url,$data);
        $res = json_decode($res,true);

        return $res['access_token'];
    }

    public function getBaseAccessToken()
    {
        $redis = new Redis();
        $redis->connect(REDIS_HOST);
        $api_access_token = $redis->get('api_access_token');
        if(!$api_access_token)
        {
            $api_access_token = $this->baseAccessToken();
            $redis->setex('api_access_token',3500,$api_access_token);
        }
        return $api_access_token;
    }

    public function getCode($uid)
    {
        $url = OAUTH2_URL."/api/oauth/authorize?response_type=code&client_id=".OAUTH2_ID."&state=xyz&access_token=".$this->getBaseAccessToken()."&uid=".$uid;
        redirect($url);
    }

    public function authorize($uid)
    {
        if(!$_GET['code'])
        {
            $this->getCode($uid);
        }
        $url = OAUTH2_URL."/api/oauth/accesstoken";
        $data = [
            "grant_type" => "authorization_code",
            "code" => $_GET['code'],
            "uid" => $uid
        ];
        $res = $this->curl($url,$data);
        return json_decode($res,true);
    }


    public function refreshToken($refresh_token)
    {
        $url = OAUTH2_URL."/api/oauth/refreshToken";
        $data = [
            "grant_type" => "refresh_token",
            "refresh_token" => $refresh_token
        ];
        return json_decode($this->curl($url,$data),true);
    }

    protected function curl($url,$query)
    {
        $is_urlcode = true;

        if (is_array($query)) {
            foreach ($query as $key => $val) {
                if ($is_urlcode) {
                    $encode_key = urlencode($key);
                } else {
                    $encode_key = $key;
                }
                if ($encode_key != $key) {
                    unset($query[$key]);
                }
                if ($is_urlcode) {
                    $query[$encode_key] = urlencode($val);
                } else {
                    $query[$encode_key] = $val;
                }
            }
        }
        $headers = [
            "Authorization: Basic " . base64_encode(OAUTH2_ID.":".OAUTH2_SECRET)
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}