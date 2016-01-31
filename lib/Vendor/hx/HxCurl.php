<?php

namespace HxModule;

class HxCurl
{
    private $ch;

    private $content;

    /**
     * 初始化
     * @param string $url
     * @param string $type 'GET'
     */
    public function __construct()
    {
        $this->ch = curl_init();
    }

    /**
     * 创建提交的数据
     * @param array $data
     */
    public function createData($data, $to = 'json')
    {
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    /**
     * 设置Header
     * @param string $str
     */
    public function createHeader($header)
    {
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
    }

    public function execute($url, $type)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        $this->content = curl_exec($this->ch);
        return obj2arr(json_decode($this->content));
    }

    public function __destruct()
    {
        if($this->ch != null) curl_close($this->ch);
    }
}