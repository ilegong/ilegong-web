<?php

class SharePushComponent extends Component
{

    var $components = ['JPush', 'WeshareBuy'];

    static $TEXT_MSG_TYPE = 'text';

    static $MSG_BUY_TYPE = 0;

    static $MSG_COMMENT_TYPE = 1;

    static $MSG_FAQ_TYPE = 2;

    static $MSG_NOTICE_ORDER_SHIPPED = 100;

    static $MSG_NOTICE_PICK_UP = 101;

    static $MSG_NOTICE_OFFERED = 102;

    static $MSG_SPREAD_SHARE = 202;

    static $MSG_SPREAD_WEB_URL = 201;


    public function push_buy_msg($buyOptLog, $share)
    {
        $user_id = $buyOptLog['user_id'];
        $sharer = $share['creator'];
        $content = $buyOptLog['reply_content'];
        $users = $this->get_users([$user_id, $sharer]);
        $title = $users[$user_id]['nickname'] . '购买了 : ' . $content;
        $this->JPush->push($sharer, $title, $content, self::$TEXT_MSG_TYPE, ['users' => json_encode($users, JSON_UNESCAPED_UNICODE), 'share_title' => $share['title'], 'type' => self::$MSG_BUY_TYPE]);
    }

    public function push_comment_msg($commentOptLog, $share)
    {
        $user_id = $commentOptLog['user_id'];
        $sharer = $share['creator'];
        $comment_content = $commentOptLog['reply_content'];
        $users = $this->get_users([$user_id, $sharer]);
        $title = $users[$user_id]['nickname'] . '评论 : ' . $comment_content;
        $this->JPush->push($sharer, $title, $comment_content, self::$TEXT_MSG_TYPE, ['users' => json_encode($users, JSON_UNESCAPED_UNICODE), 'share_title' => $share['title'], 'type' => self::$MSG_COMMENT_TYPE]);
    }


    public function push_faq_msg($faqData)
    {
        $msg = $faqData['msg'];
        $users = $this->get_users([$faqData['receiver'], $faqData['sender']]);
        $title = $users[$faqData['sender']]['nickname'] . ' : ' . $msg;
        $this->JPush->push(strval($faqData['receiver']), $title, $msg, self::$TEXT_MSG_TYPE, ['msg' => $msg, 'users' => json_encode($users, JSON_UNESCAPED_UNICODE), 'type' => self::$MSG_FAQ_TYPE]);
    }

    public function push_order_shipped_msg($user_id, $title, $msg, $order_id)
    {
        try {
            $this->JPush->push(strval($user_id), $title, $msg, self::$TEXT_MSG_TYPE, ['order_id' => $order_id, 'type' => self::$MSG_NOTICE_ORDER_SHIPPED]);
        } catch (Exception $e) {
            $this->log('push_order_shipped_msg jpush error ' . $e->getMessage());
        }
    }

    public function push_pick_up_msg($user_id, $title, $msg, $order_id)
    {
        try {
            $this->JPush->push(strval($user_id), $title, $msg, self::$TEXT_MSG_TYPE, ['order_id' => $order_id, 'type' => self::$MSG_NOTICE_PICK_UP]);
        } catch (Exception $e) {
            $this->log('push_pick_up_msg jpush error ' . $e->getMessage());
        }
    }

    public function push_share_offered_msg($user_id, $title, $msg, $weshare_id)
    {
//        try {
//            $this->JPush->push(strval($user_id), $title, $msg, self::$TEXT_MSG_TYPE, ['weshare_id' => $weshare_id, 'type' => self::$MSG_NOTICE_OFFERED]);
//        } catch (Exception $e) {
//            $this->log('push_share_offered_msg jpush error ' . $e->getMessage());
//        }
    }

    public function push_spread_msg($title, $msg, $data_type, $data_val, $uids = null)
    {
        try {
            if ($uids) {
                $this->JPush->push($uids, $title, $msg, self::$TEXT_MSG_TYPE, ['data' => $data_val, 'type' => $data_type]);
            } else {
                $this->JPush->push_all($title, $msg, self::$TEXT_MSG_TYPE, ['data' => $data_val, 'type' => $data_type]);
            }
        } catch (Exception $e) {
            $this->log('push_spread_msg jpush error ' . $e->getMessage());
        }
    }


    private function get_users($user_ids)
    {
        $userM = ClassRegistry::init('User');
        $users = $userM->get_users_simple_info($user_ids);
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        return $users;
    }

}