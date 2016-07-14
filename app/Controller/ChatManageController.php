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
        $title = '创建';
        if ($id) {
            $this->loadModel('ChatGroup');
            $group = $this->ChatGroup->findById($id);
            $this->set('group', $group);
            $title = '更新';
        }
        $this->set('title', $title);
    }

    public function save_group()
    {
        $this->Components->load('ChatUtil');
        $this->loadModel('ChatGroup');
        $data = $this->request->data;
        if ($data['ChatGroup']['id']) {
            //update
            $hxGroupData = [
                "groupname" => $data['ChatGroup']['group_name'],
                "desc" => str_replace(" ", "+", $data['ChatGroup']['description']),
                "maxusers" => $data['ChatGroup']['maxusers']
            ];
            $result = $this->ChatUtil->update_group($hxGroupData, $data['ChatGroup']['hx_group_id']);
            if ($result) {
                $this->ChatGroup->save($data);
            }
        } else {
            //save
            $hxGroupData = [
                "groupname" => $data['ChatGroup']['group_name'],
                "desc" => $data['ChatGroup']['description'],
                "public" => true,
                "maxusers" => $data['ChatGroup']['maxusers'],
                "approval" => boolval($data['CatGroup']['approval']),
                "owner" => $data['ChatGroup']['creator'],
                "members" => []
            ];
            $result = $this->ChatUtil->create_group($hxGroupData);
            if ($result) {
                $data['ChatGroup']['hx_group_id'] = $result;
                $data['ChatGroup']['is_public'] = 1;
                $this->ChatGroup->save($data);
            }
        }
        $this->redirect('/chatManage/group_list.html');
    }

    public function send_msg()
    {

    }

}