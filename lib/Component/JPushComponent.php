<?php

class JPushComponent extends Component
{

    var $all_platforms = ['ios'];

    var $time_to_live = 86400;

    /**
     * @param $user_ids
     * @param $title
     * @param $content
     * @param $type
     * @param array $extras
     * @return array|object
     * 推送消息
     */
    public function push($user_ids, $title, $content, $type, $extras = array())
    {
        if (!is_array($user_ids)) {
            $user_ids = [$user_ids];
        }
        $client = $this->get_push_client();
        // 完整的推送示例,包含指定Platform,指定Alias,Tag,指定iOS,Android notification,指定Message等
        $result = $client->push()
            ->setPlatform($this->all_platforms)
            ->addAlias($user_ids)
            ->setNotificationAlert($title)
            ->addIosNotification($title, 'iOS sound', '+1', true, 'iOS category', $extras)
            ->setMessage($content, $title, $type, $extras)
            ->setOptions(mt_rand(), $this->time_to_live, null, false)
            ->send();
        return $result;
    }

    public function device()
    {

    }

    public function report()
    {

    }

    public function schedule()
    {

    }

    private function get_push_client()
    {
        // 初始化
        App::import('Vendor', 'JPush/JPush');
        $client = new JPush(JPUSH_APP_KEY, JPUSH_APP_SECRET);
        return $client;
    }


}