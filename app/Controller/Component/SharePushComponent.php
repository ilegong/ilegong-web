<?php

class SharePushComponent extends Component
{

    var $components = ['JPush', 'WeshareBuy'];

    static $MSG_BUY_TYPE = 0;

    static $MSG_COMMENT_TYPE = 1;

    static $MSG_FAQ_TYPE = 2;

    public function push_buy_msg($buyOptLog, $share)
    {
        if (!$this->check_should_push()) {
            return false;
        }
        $user_id = $buyOptLog['user_id'];
        $sharer = $share['creator'];
        $content = $buyOptLog['reply_content'];
        $users = $this->get_users([$user_id, $sharer]);
        $title = $users[$user_id]['nickname'] . '购买了 : ' . $content;
        $this->JPush->push($sharer, $title, $content, self::$MSG_BUY_TYPE, ['users' => json_encode($users, JSON_UNESCAPED_UNICODE), 'share_title' => $share['title'], 'type' => self::$MSG_BUY_TYPE]);
    }

    public function push_comment_msg($commentOptLog, $share)
    {
        if (!$this->check_should_push()) {
            return false;
        }
        $user_id = $commentOptLog['user_id'];
        $sharer = $share['creator'];
        $comment_content = $commentOptLog['reply_content'];
        $users = $this->get_users([$user_id, $sharer]);
        $title = $users[$user_id]['nickname'] . '评论 : ' . $comment_content;
        $this->JPush->push($sharer, $title, $comment_content, self::$MSG_COMMENT_TYPE, ['users' => json_encode($users, JSON_UNESCAPED_UNICODE), 'share_title' => $share['title'], 'type' => self::$MSG_COMMENT_TYPE]);
    }

    public function push_faq_msg($faqData)
    {
        if (!$this->check_should_push()) {
            return false;
        }
        $msg = $faqData['msg'];
        $users = $this->get_users([$faqData['receiver'], $faqData['sender']]);
        $title = $users[$faqData['sender']]['nickname'] . ' : ' . $msg;
        $this->JPush->push(strval($faqData['receiver']), $title, $msg, self::$MSG_FAQ_TYPE, ['msg' => $msg, 'users' => json_encode($users, JSON_UNESCAPED_UNICODE), 'type' => self::$MSG_FAQ_TYPE]);
    }

    private function get_users($user_ids)
    {
        $userM = ClassRegistry::init('User');
        $users = $userM->get_users_simple_info($user_ids);
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        return $users;
    }

    private function check_should_push()
    {
        return WX_HOST == DEFAULT_SITE_HOST;
    }
}