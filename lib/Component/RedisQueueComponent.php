<?php

class RedisQueueComponent extends Component
{
    var $curl_type = 'curl';

    public function add_curl_task($url, $postdata = null, $prior = false)
    {
        $kue = $this->get_kue();
        if (!empty($postdata)) {
            $postdata = base64_encode($postdata);
        }
        $kue->create('curl', array(
            'url' => $url,
            'form_data' => $postdata,
            'prior' => $prior
        ))->delay(120)->save()->attempts(1);
        return true;
    }

    public function batch_add_task($tasks)
    {
        foreach ($tasks as $task) {
            $url = $task['url'];
            $post_data = empty($task['postdata']) ? null : $task['postdata'];
            $prior = $task['prior'] ? false : true;
            $this->add_curl_task($url, $post_data, $prior);
        }
        return true;
    }

    public function get_kue()
    {
        App::import('Vendor', 'kue/Kue');
        // Connect "redis_server:6379" and select db to "1"
        $kue = Kue::createQueue(array('host' => REDIS_HOST, 'port' => 6379));
        return $kue;
    }

    public function add_tasks($queue_name, $tasks, $postdata = null, $prior = false)
    {
        if (is_string($tasks)) {
            if (check_sae()) {
                $queue = new SaeTaskQueue($queue_name);
                $queue->addTask($tasks, $postdata, $prior);
                $ret = $queue->push();
                return $ret;
            } else {
                return $this->add_curl_task($tasks, $postdata, $prior);
            }
        } elseif (is_array($tasks)) {
            if (check_sae()) {
                $queue = new SaeTaskQueue($queue_name);
                $queue->addTask($tasks);
                $ret = $queue->push();
                return $ret;
            } else {
                return $this->batch_add_task($tasks);
            }
        }
    }

}