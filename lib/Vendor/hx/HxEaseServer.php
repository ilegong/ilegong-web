<?php

require_once("HxCurl.php");

class HxEaseServer {
    protected $ch;
    protected $client_id;
    protected $client_secret;
    protected $token;
    protected $url = 'https://a1.easemob.com/ilegong-pys/';

    public function __construct($app_name, $client_id, $client_secret){
        $this->ch = new HxCurl();
        $this->url .= $app_name;
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->token = $this->getToken();
    }

    /**
     * 从ease服务器上获取access_oken
     */
    protected function getToken()
    {
        $token_cache_data = Cache::read(HX_TOKEN_CACHE_KEY);
        if (empty($token_cache_data)) {
            $token = $this->fetchHxToken();
            return $token;
        }
        $token_data = json_decode($token_cache_data, true);
        $expiration_date = $token_cache_data['expiration_date'];
        $now = time();
        if ($expiration_date - $now > 0) {
            return $token_data['token'];
        }
        $token = $this->fetchHxToken();
        return $token;
    }

    protected function fetchHxToken()
    {
        $data = array('grant_type' => 'client_credentials', 'client_id' => $this->client_id, 'client_secret' => $this->client_secret);
        $this->ch->createData($data);
        $content = $this->ch->execute($this->url . '/token', 'POST');
        $token_cache_data = array('token' => $content['access_token'], 'expiration_date' => strtotime('+5 day'));
        Cache::write(HX_TOKEN_CACHE_KEY, json_encode($token_cache_data));
        return $content['access_token'];
    }

    /**
     *
     */
    protected function putTokenOnCache(){
        Cache::write(HX_TOKEN_CACHE_KEY, $this->getToken());
    }

    protected function getTokenOnCache()
    {
        $token = Cache::read(HX_TOKEN_CACHE_KEY);
        if (empty($token)) {
            $token = $this->getToken();
            Cache::write(HX_TOKEN_CACHE_KEY, $token);
        }
        return $token;
    }

    /**
     * 将Token写入到文件中
     */
    protected function putTokenOnFile(){
        file_put_contents('./token.txt', $this->getToken());
    }

    /**
     * 获取文件中的Token
     * @return string
     */
    protected function getTokenOnFile(){
        return file_get_contents('./token.txt');
    }
}
?>