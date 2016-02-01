<?php

class QueueComponent extends Component
{

    public function add_curl_task()
    {
        $kue = $this->get_kue();
        $kue->create('email', array(
            'to' => 'hfcorriez@gmail.com',
            'subject' => 'Reset your password!',
            'body' => 'Your can reset your password in 5 minutes. Url: http://xxx/reset'
        ))->save();
    }

    private function get_kue()
    {
        App::import('Vendor', 'kue/Kue');
        // Connect "redis_server:6379" and select db to "1"
        $kue = Kue::createQueue(array('host' => '127.0.0.1:6397', 'db' => 1));
        return $kue;
    }

}