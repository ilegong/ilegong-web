<?php

class ShareFaqController extends AppController {

    var $name = 'share_faq';

    var $uses = array('ShareFaq', 'Weshare', 'User');

    var $components = array('Weixin', 'WeshareBuy', 'ShareUtil');


    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'weshare_bootstrap';
    }

    public function faq_list($shareId) {

    }

    public function faq($shareId, $userId) {
        $share_info = $this->get_share_info($shareId);
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
                'sender' => $userId,
                'receiver' => $share_creator,
                'share_id' => $shareId,
                'OR' => array(
                    'sender' => $share_creator,
                    'receiver' => $userId,
                    'share_id' => $shareId,
                )
            ),
            'order' => array('created DESC')
        ));
        $this->set('current_user_id', $current_user_id);
        $this->set('share_info', $share_info);
        $this->set('user_info', $user_info);
        $this->set('share_faqs', $share_faqs);
    }

    public function create_faq() {
        $this->autoRender = false;
        $msg = $_REQUEST['msg'];
        $sender = $_REQUEST['sender'];
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