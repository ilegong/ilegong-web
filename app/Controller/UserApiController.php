<?php

class UserApiController extends AppController {

    public $components = array('OAuth.OAuth', 'Session');

    public function beforeFilter() {
        parent::beforeFilter();
        $allow_action = array('test', 'ping');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
    }

    public function profile() {
        $userM = ClassRegistry::init('User');
        $user_id = $this->currentUser['id'];
        $datainfo = $userM->find('first', array('recursive' => -1,
            'conditions' => array('id' => $user_id),
            'fields' => array('nickname', 'email', 'image', 'sex', 'companies', 'bio', 'mobilephone', 'email', 'username', 'id')));
        $this->set('my_profile', array('User' => $datainfo['User']));
        $this->set('_serialize', array('my_profile'));
    }

    public function test() {

    }

}