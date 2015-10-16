<?php

class ShareFaqController extends AppController {

    var $name = 'share_faq';

    var $uses = array('ShareFaq', 'Weshare', 'User');

    var $components = array('Weixin', 'WeshareBuy', 'ShareUtil', 'ShareFaqUtil');


    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'weshare_bootstrap';
    }

    //目前只有分享者可以看到
    public function faq_list($shareId, $shareCreator) {
        $faq_list = $this->ShareFaqUtil->share_faq_list($shareId, $shareCreator);
        $this->set($faq_list);
    }

    public function faq($shareId, $userId) {
        $share_info = $this->get_share_info($shareId);
        //todo check is login
        $current_user_id = $this->currentUser['id'];
        $share_creator = $share_info['Weshare']['creator'];
        $user_info = $this->User->find('all', array(
            'conditions' => array(
                'id' => array($userId, $share_creator)
            ),
            'fields' => array('id', 'nickname', 'image')
        ));
        $user_info = Hash::combine($user_info, '{n}.User.id', '{n}.User');
        $share_faqs = $this->ShareFaq->find('all', array(
            'conditions' => array(
                'share_id' => $shareId,
                'OR' => array(
                    'sender' => array($share_creator, $userId),
                    'receiver' => array($share_creator, $userId),
                )
            ),
            'order' => array('created ASC')
        ));
        $this->set('share_id', $shareId);
        $this->set('receiver', $userId);
        $this->set('current_user_id', $current_user_id);
        $this->set('share_info', $share_info);
        $this->set('user_info', $user_info);
        $this->set('share_faqs', $share_faqs);
    }

    public function create_faq() {
        $this->autoRender = false;
        //todo check login
        $sender = $this->currentUser['id'];
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
        $faq_data = $this->ShareFaq->save($faq_data);
        echo json_encode($faq_data);
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