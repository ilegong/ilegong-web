<?php


class ChatManageController extends AppController
{
    public $components = ['Auth'];

    public function beforeFilter()
    {
        $this->Auth->allowedActions = [];
        $this->layout = 'sharer';
        parent::beforeFilter();
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
                "approval" => (bool)$data['CatGroup']['approval'],
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

    public function group_user_list($gid)
    {
        $this->loadModel('ChatGroup');
        $group = $this->ChatGroup->findById($gid);
        $this->set('title', $group['ChatGroup']['group_name']);
        $this->loadModel('UserGroup');
        $result = $this->UserGroup->find('all', [
            'conditions' => [
                'UserGroup.group_id' => $gid
            ],
            'joins' => [
                [
                    'table' => 'cake_users',
                    'alias' => 'User',
                    'type' => 'left',
                    'conditions' => 'User.id = UserGroup.user_id'
                ]
            ],
            'fields' => ['UserGroup.*', 'User.nickname', 'User.id']
        ]);
        $this->set('result', $result);
        $this->set('group_id', $gid);
        $this->set('group', $group);
    }

    public function add_group_user($uid, $gid)
    {
        $this->autoRender = false;
        $this->loadModel('UserGroup');
        $this->loadModel('ChatGroup');
        $g = $this->ChatGroup->findById($gid);
        $this->ChatUtil = $this->Components->load('ChatUtil');
        $result = $this->ChatUtil->add_group_member($uid, $g['ChatGroup']['hx_group_id']);
        if ($result) {
            $date_now = date('Y-m-d H:i:s');
            $this->UserGroup->save(['user_id' => $uid, 'group_id' => $g['ChatGroup']['id'], 'created' => $date_now, 'updated' => $date_now]);
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false]);
        exit;
    }

    public function delete_group_user($id)
    {
        $this->autoRender = false;
        $this->loadModel('UserGroup');
        $this->loadModel('ChatGroup');
        $ug = $this->UserGroup->findById($id);
        $g = $this->ChatGroup->findById($ug['UserGroup']['group_id']);
        if ($g['ChatGroup']['creator'] != $ug['UserGroup']['user_id']) {
            $this->ChatUtil = $this->Components->load('ChatUtil');
            $result = $this->ChatUtil->delete_group_member($ug['UserGroup']['user_id'], $g['ChatGroup']['hx_group_id']);
            if ($result) {
                if ($this->UserGroup->delete($id)) {
                    echo json_encode(['success' => true]);
                    exit;
                }
            }
        }
        echo json_encode(['success' => false]);
        exit;
    }

    public function send_group_msg()
    {
        $this->autoRender = false;
        $gid = $_REQUEST['gid'];
        $msg = $_REQUEST['msg'];
        $this->loadModel('ChatGroup');
        $g = $this->ChatGroup->findById($gid);
        $this->ChatUtil = $this->Components->load('ChatUtil');
        $target_type = 'chatgroups';
        $target = $g['ChatGroup']['hx_group_id'];
        $from = $_REQUEST['from'];
        $ext = [];
        $from_info = $this->ChatUtil->get_user_info($from);
        $ext = array_merge($ext, $from_info);
        $result = $this->ChatUtil->send_msg($target_type, [$target], $msg, $from, $ext);
        if ($result['error']) {
            $this->log('send msg error ' . json_encode($result));
            echo json_encode(['success' => false]);
            exit;
        }
        echo json_encode(['success' => true]);
        exit;
    }

}