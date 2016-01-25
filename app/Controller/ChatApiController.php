<?php

class ChatApiController extends AppController
{


    public $components = array('OAuth.OAuth', 'Session', 'HxChat');

    public $uses = array('ChatGroup', 'UserGroup', 'UserFriend', 'User');

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
        $postStr = file_get_contents('php://input');
        $postData = json_decode($postStr, true);
        $result = $this->HxChat->create_group($postData);
        if ($result) {
            echo json_encode(array('statusCode' => 1, 'data' => $result, 'statusMsg' => '创建成功'));
            return;
        }
        echo json_encode(array('statusCode' => -1, 'statusMsg' => '创建失败'));
        return;
    }

    public function update_group()
    {

    }

    public function get_groups()
    {

    }

    public function get_group_members($group_id)
    {
        $members = $this->UserGroup->find('all', array(
            'conditions' => array(
                'group_id' => $group_id,
                'deleted' => DELETED_NO
            ),
            'limit' => 500
        ));
        $member_ids = Hash::extract($members, '{n}.UserGroup.user_id');
        $data = $this->HxChat->get_users_info($member_ids);
        echo json_encode($data);
        return;
    }

    public function add_group_member()
    {

    }

    public function delete_group_member()
    {

    }

}