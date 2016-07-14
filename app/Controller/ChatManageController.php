<?php


class ChatManageController extends Controller{

    public function beforeFilter()
    {
        $this->Auth->allowedActions = [];
        $this->layout = 'sharer';
    }


    public function group_list(){
        $this->loadModel('ChatGroup');
        $groups = $this->ChatGroup->find('all', []);
        $this->set('groups', $groups);
    }

    public function group_form(){

    }

    public function save_group(){

    }

    public function send_msg(){

    }

}