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
        $share_creator = $share_info['Weshare']['creator'];
        $user_infos = $this->User->find('all', array(
            'conditions' => array(
                'id' => array($userId, $share_creator)
            ),
            'fields' => array('id', 'nickname', 'image')
        ));

    }

    public function create_faq() {

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