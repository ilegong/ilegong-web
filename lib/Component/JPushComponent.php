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
    public function push($user_ids, $title, $content, $type, $extras = [])
    {
        if (!is_array($user_ids)) {
            $user_ids = [$user_ids];
        }
        $user_ids = $this->filter_user($user_ids);
        if (empty($user_ids)) {
            return false;
        }
        $type = strval($type);
        $client = $this->get_push_client();
        //$sendno=null, $time_to_live=null, $override_msg_id=null, $apns_production=null, $big_push_duration=null
        $result  = $client->push()
            ->setPlatform($this->all_platforms)
            ->addAlias($user_ids)
            ->setNotificationAlert($title)
            ->addIosNotification($title, 'iOS sound', '+1', true, 'iOS category', $extras)
            ->setMessage($content, $title, $type, $extras)
            ->setOptions(mt_rand(), $this->time_to_live, null, JPUSH_IS_PRODUCT, null)
            ->send();

        $this->log('push msg result ' . json_encode($result), LOG_DEBUG);

        return $result;
    }

    public function push_all($title, $content, $type, $extras = []){
        $type = strval($type);
        $client = $this->get_push_client();
        $result  = $client->push()
            ->setPlatform($this->all_platforms)
            ->addAllAudience()
            ->setNotificationAlert($title)
            ->addIosNotification($title, 'iOS sound', '+1', true, 'iOS category', $extras)
            ->setMessage($content, $title, $type, $extras)
            ->setOptions(mt_rand(), $this->time_to_live, null, JPUSH_IS_PRODUCT, null)
            ->send();

        $this->log('push msg result ' . json_encode($result), LOG_DEBUG);
    }

    public function filter_user($user_ids){
        $result = [];
//        $client = $this->get_push_client();
//        foreach ($user_ids as $uid) {
//            $u_device = $client->device()->getAliasDevices(strval($uid), ['ios']);
//            $this->log('jpush get user device result ' . $uid . ' result ' . json_encode($u_device->data->registration_ids));
//            if (!empty($u_device->data->registration_ids)) {
//                $result[] = $uid;
//            }
//        }
        if(empty($user_ids)){
            return $result;
        }
        $userM = ClassRegistry::init('User');
        $users = $userM->find('all', [
            'conditions' => [
                'id' => $user_ids,
                'is_install_app' => INSTALL_APP_MARK
            ]
        ]);
        $result = Hash::extract($users, '{n}.User.id');
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