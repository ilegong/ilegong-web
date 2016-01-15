<?php

class ChatApiController extends AppController
{


    public $components = array('OAuth.OAuth', 'Session', 'HxChat');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $allow_action = array();
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }

    public function get_groups()
    {

    }

    public function get_group_users()
    {

    }
}