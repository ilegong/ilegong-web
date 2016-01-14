<?php
class Chat extends HxEaseServer {

    /**
     * 上传文件
     * @param string $file
     * @return array
     */
    public function chatFiles($file){
        $data = "@" . $file;
        $header[] = 'Authorization: Bearer ' . $this->token;
        $header[] = 'restrict-access: true';
        $header[] = 'Content-Type: application/json';
        $this->ch->createHeader($header);
        $this->ch->createData($data);
        return $this->ch->execute($this->url . '/chatfiles', 'POST');
    }

    /**
     * 下载图片,语音文件
     */
    public function downloadFile(){}

    /**
     * 下载缩略图
     */
    public function downloadThumb(){}

    /**
     * 获取最新的聊天记录
     * @return array
     */
    public function getMessagesByNew($limit = 20){
        $header[] = 'Authorization: Bearer ' . $this->token;
        $url = $this->url . '/chatmessages?ql=' . urlencode('order+by+timestamp+desc') . '&limit=' . $limit;
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'GET');
    }

    /**
     * 获取某个时间段内的消息
     */
    public function getMessagesByTimes($start, $end){
        $ql = 'select * where timestamp>' . $start . ' and timestamp<' . $end . ' order by timestamp desc';
        $url = $this->url . '/chatmessages?ql=' . url_enc($ql);
        $header[] = 'Authorization: Bearer ' . $this->token;
        $header[] = 'Content-Type: application/json';
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'GET');
    }

    /**
     * 分页获取数据
     */
    public function getMessagesByPage($limit){
        $ql = 'select+*+order+by+timestamp+desc';
        $url = $this->url . '/chatmessages?ql=' . url_enc($ql) . '&limit=' . $limit;
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'GET');
    }
}