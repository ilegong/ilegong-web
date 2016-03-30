<?php

class SharePushComponent
{

    var $components = ['JPush', 'WeshareBuy'];

    static $MSG_BUY_TYPE = 0;

    static $MSG_COMMENT_TYPE = 1;

    static $MSG_FAQ_TYPE = 2;

    public function push_buy_msg($optLog, $share)
    {
        $user_id = $optLog['user_id'];
        $share_thumb = $optLog['thumbnail'];
        $title = $share['title'];
        $sharer = $share['creator'];
        $content = $optLog['reply_content'];
        $userM = ClassRegistry::init('User');
        $users = $userM->get_users_simple_info([$user_id, $sharer]);
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        $this->JPush->push($sharer, $title, $content, self::$MSG_BUY_TYPE, ['share_thumb' => $share_thumb, 'users' => json_encode($users)]);
    }

    public function push_faq_msg($faqData)
    {
        $msg = $faqData['msg'];
        $share_id = $faqData['share_id'];
        $share = $this->WeshareBuy->get_weshare_info($share_id);
        $share_title = $share['title'];
        $thumbnail = explode('|', $share['images']);
        $thumbnail = $thumbnail[0];

    }

    public function push_comment_msg()
    {

    }

    private function get_users(){
        
    }

}