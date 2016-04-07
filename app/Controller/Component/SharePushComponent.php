<?php

class SharePushComponent
{

    var $components = ['JPush', 'WeshareBuy'];

    static $MSG_BUY_TYPE = 0;

    static $MSG_COMMENT_TYPE = 1;

    static $MSG_FAQ_TYPE = 2;

    public function push_buy_msg($buyOptLog, $share)
    {
        $user_id = $buyOptLog['user_id'];
        $share_thumb = $buyOptLog['thumbnail'];
        $title = $share['title'];
        $sharer = $share['creator'];
        $content = $buyOptLog['reply_content'];
        $users = $this->get_users([$user_id, $sharer]);
        $this->JPush->push($sharer, $title, $content, self::$MSG_BUY_TYPE, ['share_thumb' => $share_thumb, 'users' => json_encode($users)]);
    }

    public function push_comment_msg($commentOptLog, $share)
    {
        $user_id = $commentOptLog['user_id'];
        $share_thumb = $commentOptLog['thumbnail'];
        $title = $share['title'];
        $sharer = $share['creator'];
        $comment_content = $commentOptLog['reply_content'];
        $users = $this->get_users([$user_id, $sharer]);
        $this->JPush->push($sharer, $title, $comment_content, self::$MSG_COMMENT_TYPE, ['share_thumb' => $share_thumb, 'users' => json_encode($users)]);
    }

    public function push_faq_msg($faqData)
    {
        $msg = $faqData['msg'];
        $share_id = $faqData['share_id'];
        $share = $this->WeshareBuy->get_weshare_info($share_id);
        $title = $share['title'];
        $thumbnail = explode('|', $share['images']);
        $thumbnail = $thumbnail[0];
        $users = $this->get_users([$faqData['sender'], $faqData['receiver']]);
        $this->JPush->push($faqData['receiver'], $title, $msg, self::$MSG_FAQ_TYPE, ['share_thumb' => $thumbnail, 'users' => json_encode($users)]);
    }


    private function get_users($user_ids){
        $userM = ClassRegistry::init('User');
        $users = $userM->get_users_simple_info($user_ids);
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        return $users;
    }

}