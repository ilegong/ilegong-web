<?php

class ShareFaqController extends AppController {

    var $name = 'share_faq';

    var $uses = array('ShareFaq', 'Weshare', 'User');

    var $components = array('Weixin', 'WeshareBuy', 'ShareUtil', 'ShareFaqUtil', 'ShareAuthority', 'SharePush', 'WeshareFaq');


    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'weshare_bootstrap';
    }

    //目前只有分享者可以看到
    /**
     * @param $shareId
     * @param $receiver
     * 分享者和用户交互的列表
     */
    public function faq_list($shareId, $receiver) {
        $faq_list = $this->ShareFaqUtil->share_faq_list($shareId, $receiver);
        $this->set($faq_list);
    }

    /**
     * @param $shareId
     * @param $userId
     * 用户交流页面
     */
    public function faq($shareId, $userId) {
        $share_info = $this->get_share_info($shareId);
        $current_user_id = $this->currentUser['id'];
        //every one can chat
//        $share_creator = $share_info['Weshare']['creator'];
//        if ($current_user_id != $share_creator && $userId != $share_creator) {
//            $this->redirect('/weshares/view/' . $shareId);
//            return;
//        }
        $user_info = $this->User->find('all', array(
            'conditions' => array(
                'id' => array($userId, $current_user_id)
            ),
            'fields' => array('id', 'nickname', 'image', 'avatar')
        ));
        $user_info = Hash::combine($user_info, '{n}.User.id', '{n}.User');
        $user_info = array_map('map_user_avatar', $user_info);
        //查询交互的信息
        $share_faqs = $this->ShareFaq->find('all', array(
            'conditions' => array(
                'share_id' => $shareId,
                'OR' => array(
                    array('sender' => $userId,
                        'receiver' => $current_user_id),
                    array('sender' => $current_user_id,
                        'receiver' => $userId)
                ),
            ),
            'order' => array('created ASC')
        ));
        $this->set('hide_footer', true);
        $this->set('current_user', $current_user_id);
        $this->set('share_id', $shareId);
        $this->set('receiver', $userId);
        $this->set('current_user_id', $current_user_id);
        $this->set('share_info', $share_info);
        $this->set('user_info', $user_info);
        $this->set('share_faqs', $share_faqs);
    }

    /**
     * 发送消息
     */
    public function create_faq() {
        $this->autoRender = false;
        $sender = $this->currentUser['id'];
        if (is_blacklist_user($sender)) {
            echo json_encode(array('success' => false, 'reason' => 'user_bad'));
            return;
        }
        $msg = $_REQUEST['msg'];
        $receiver = $_REQUEST['receiver'];
        $shareId = $_REQUEST['share_id'];
        $faq_data = array(
            'sender' => $sender,
            'receiver' => $receiver,
            'created' => date('Y-m-d H:i:s'),
            'share_id' => $shareId,
            'msg' => $msg
        );
        $faq_data = $this->WeshareFaq->create_faq($faq_data);
        echo json_encode($faq_data);
        return;
    }

    public function update_faq_read($shareId, $userId) {
        $this->autoRender = false;
        $this->ShareFaqUtil->update_user_faq_has_read($shareId, $userId);
        echo json_encode(array('success' => true));
        return;
    }

    public function get_sharer_faq_list($shareId, $shareCreator) {
        $this->autoRender = false;
        $unread_count = $this->ShareUtil->sharer_has_unread_info($shareId, $shareCreator);
        echo json_encode(array('unread_count' => $unread_count));
        return;
    }

    /**
     * @param $shareId
     * @param $userId
     * @param $shareCreator
     * 获取用户未读信息
     */
    public function get_user_unread_info($shareId, $userId, $shareCreator) {
        $this->autoRender = false;
        $unread_count = $this->ShareFaqUtil->has_unread_info($shareId, $userId, $shareCreator);
        echo json_encode(array('unread_count' => $unread_count));
        return;
    }


    private function get_share_info($share_id) {
        $share_info = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $share_id
            )
        ));
        return $share_info;
    }

}