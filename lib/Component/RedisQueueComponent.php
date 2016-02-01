<?php
class RedisQueueComponent extends Component
{

    public function add_curl_task()
    {
        App::import('Vendor', 'kue/Kue');
        // Connect "redis_server:6379" and select db to "1"
        $kue = Kue::createQueue(array('host' => '127.0.0.1', 'port' => 6379));
        $kue->create('email', array(
            'to' => 'hfcorriez@gmail.com',
            'subject' => 'Reset your password!',
            'body' => 'Your can reset your password in 5 minutes. Url: http://xxx/reset'
        ))->save();
    }

}