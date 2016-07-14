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
            $this->set('data', $group);
            $title = '更新';
        }
        $this->set('title', $title);
    }

    public function save_group()
    {
        $this->ChatUtil = $this->Components->load('ChatUtil');
        $this->loadModel('ChatGroup');
        $this->loadModel('UserGroup');
        $date_now = date('Y-m-d H:i:s');
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
                $data['ChatGroup']['group_code'] = make_union_code();
                $data['ChatGroup']['created'] = $date_now;
                $group = $this->ChatGroup->save($data);
                $this->UserGroup->save(['user_id' => $group['ChatGroup']['creator'], 'group_id' => $group['ChatGroup']['id'], 'created' => $date_now, 'updated' => $date_now]);
            }
        }
        $this->redirect('/chatManage/group_list.html');
    }

    public function send_msg()
    {

    }

}