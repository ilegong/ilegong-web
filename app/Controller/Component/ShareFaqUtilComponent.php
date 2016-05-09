<?php

/**
 * Class ShareFaqUtilComponent
 * 主要是用来分享者和用户之间互动提问的
 */
class ShareFaqUtilComponent extends Component {

    var $name = 'ShareFaqUtil';


    var $components = array('Weixin', 'WeshareBuy');

    /**
     * @param $shareId
     * @param $userId
     * @param $shareCreator
     * 获取用户未读信息
     */
    public function has_unread_info($shareId, $userId, $shareCreator) {
        $shareFaqM = ClassRegistry::init('ShareFaq');
        $unreadCount = $shareFaqM->find('count', array(
            'conditions' => array(
                'sender' => $shareCreator,
                'receiver' => $userId,
                'share_id' => $shareId,
                'has_read' => SHARE_FAQ_UNREAD
            )
        ));
        return $unreadCount;
    }

    /**
     * @param $shareId
     * @param $shareCreator
     * 分享者是否有未读信息
     */
    public function sharer_has_unread_info($shareId, $shareCreator) {
        $shareFaqM = ClassRegistry::init('ShareFaq');
        $sharerUnreadCount = $shareFaqM->find('count', array(
            'conditions' => array(
                'receiver' => $shareCreator,
                'share_id' => $shareId,
                'has_read' => SHARE_FAQ_UNREAD
            )
        ));
        return $sharerUnreadCount;
    }

    /**
     * @param $queryCond
     * @return array
     * 获取到自己信息列表
     */
    public function faq_list($queryCond) {
        $shareFaqM = ClassRegistry::init('ShareFaq');
        $userM = ClassRegistry::init('User');
        $faqList = $shareFaqM->find('all', $queryCond);
        $faqUserIds = Hash::extract($faqList, '{n}.ShareFaq.sender');
        $faqUserInfo = $userM->find('all', array(
            'conditions' => array(
                'id' => $faqUserIds
            ),
            'fields' => array('id', 'nickname', 'image')
        ));
        $faqUserInfo = Hash::combine($faqUserInfo, '{n}.User.id', '{n}.User');
        $list_summery = array();
        //reduce data
        foreach ($faqList as $faqItem) {
            $faqSender = $faqItem['ShareFaq']['sender'];
            $faqItemHasRead = $faqItem['ShareFaq']['has_read'];
            $faqItemShareId = $faqItem['ShareFaq']['share_id'];
            if (!isset($list_summery[$faqSender])) {
                $list_summery[$faqSender] = array('all_count' => 0, 'unread_count' => 0, 'last_msg' => '', 'last_msg_created' => '', 'share_id' => 0);
                //sort by created desc
                $faqItemCreated = $faqItem['ShareFaq']['created'];
                $faqItemMsg = $faqItem['ShareFaq']['msg'];
                $list_summery[$faqSender]['last_msg_created'] = $faqItemCreated;
                $list_summery[$faqSender]['last_msg'] = $faqItemMsg;
            }
            $list_summery[$faqSender]['all_count'] = $list_summery[$faqSender]['all_count'] + 1;
            $list_summery[$faqSender]['share_id'] = $faqItemShareId;
            if ($faqItemHasRead == 0) {
                $list_summery[$faqSender]['unread_count'] = $list_summery[$faqSender]['unread_count'] + 1;
            }
        }
        return array('faq_list' => $list_summery, 'users' => $faqUserInfo);
    }

    /**
     * @param $userId
     * @return array
     * 获取购买者的信息列表
     */
    public function user_faq_list($userId) {
        $queryCond = array(
            'conditions' => array(
                'receiver' => $userId,
            ),
            'order' => array('id DESC'),
            'limit' => 500,
        );
        return $this->faq_list($queryCond);
    }

    /**
     * @param $shareId
     * @param $shareCreator
     * @return share faq list info
     * 获取分享者消息列表
     */
    public function share_faq_list($shareId, $shareCreator) {
        $queryCond = array(
            'conditions' => array(
                'share_id' => $shareId,
                'receiver' => $shareCreator,
            ),
            'order' => array('id DESC'),
            'limit' => 500,
        );
        return $this->faq_list($queryCond);
    }

    /**
     * @param $shareId
     * @param $userId
     * 把未读消息置为已读
     */
    public function update_user_faq_has_read($shareId, $userId) {
        $shareFaqM = ClassRegistry::init('ShareFaq');
        return $shareFaqM->updateAll(array('has_read' => SHARE_FAQ_READ), array('share_id' => $shareId, 'receiver' => $userId, 'has_read' => SHARE_FAQ_UNREAD));
    }

    /**
     * @param $sender
     * @param $receiver
     * @param $msg
     * @param $shareId
     * @param $share_title
     * send faq template msg
     */
    public function send_notify_template_msg($sender, $receiver, $msg, $shareId, $share_title) {
        $userNicknames = $this->WeshareBuy->get_users_nickname(array($sender, $receiver));
        $userOauthBinds = $this->WeshareBuy->get_open_ids(array($sender, $receiver));
        $title = $userNicknames[$receiver] . '你好' . '，' . $userNicknames[$sender] . '给你发送了一条消息。';
        $detail_url = WX_HOST . '/share_faq/faq/' . $shareId . '/' . $sender;
        $this->log('send notify template msg oauth binds '.json_encode($userOauthBinds), LOG_DEBUG);
        $this->Weixin->send_faq_notify_template_msg($userOauthBinds[$receiver], $detail_url, $title, $msg, $share_title);
    }
}