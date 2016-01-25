<?php

class ChatApiController extends AppController
{


    public $components = array('OAuth.OAuth', 'Session', 'HxChat');

    public $uses = array('ChatGroup', 'UserGroup', 'UserFriend');

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

    public function create_group()
    {
        $current_user_id = $this->currentUser['id'];

    }

    public function update_group()
    {

    }

    public function get_groups()
    {

    }

    public function get_group_members()
    {

    }

    public function add_group_member()
    {

    }

    public function delete_group_member()
    {

    }

}