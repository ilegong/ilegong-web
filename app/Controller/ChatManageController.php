<?php


class ChatManageController extends AppController
{
    public $components = ['Auth'];

    public function beforeFilter()
    {
        $this->Auth->allowedActions = [];
        $this->layout = 'sharer';
    }


    public function group_list()
    {
        $this->loadModel('ChatGroup');
        $groups = $this->ChatGroup->find('all', [
            'joins' => [
                [
                    'table' => 'cake_users',
                    'type' => 'left',
                    'alias' => 'User',
                    'conditions' => 'User.id = ChatGroup.creator'
                ],
            ],
            'fields' => ['ChatGroup.*', 'User.nickname']
        ]);
        $this->set('groups', $groups);
    }

    public function group_form()
    {
        $id = $_REQUEST['id'];
        if($id){
            $this->loadModel('ChatGroup');
            $group = $this->ChatGroup->findById($id);
            $this->set('group', $group);
        }
    }

    public function save_group()
    {

    }

    public function send_msg()
    {

    }

}