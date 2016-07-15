<?php

class ChatApiController extends Controller
{


    public $components = array('OAuth.OAuth', 'Session', 'ChatUtil');

    public $uses = array('ChatGroup', 'UserGroup', 'UserFriend', 'User');


    protected function get_post_raw_data()
    {
        $postStr = file_get_contents('php://input');
        $postData = json_decode($postStr, true);
        return $postData;
    }

    public function beforeFilter()
    {
        $allow_action = array();
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }

    public function send_single_msg()
    {

    }

    public function send_group_msg()
    {
        $postData = $this->get_post_raw_data();
        $from = $postData['from'];
        $msg = $postData['msg'];
        $target = $postData['target'];
        $type = $postData['target_type'];
        $result = $this->ChatUtil->send_msg($type, $target, $msg, $from);
        echo json_encode($result);
        exit;
    }

    public function create_group()
    {
        $postData = $this->get_post_raw_data();
        $hx_group_id = $this->ChatUtil->create_group($postData);
        //save database
        $result = $this->save_group_data($postData, $hx_group_id);
        if ($result) {
            echo json_encode(array('statusCode' => 1, 'data' => $result, 'statusMsg' => '创建成功'));
            exit;
        }
        echo json_encode(array('statusCode' => -1, 'statusMsg' => '创建失败'));
        exit;
    }

    public function update_group($hx_group_id)
    {
        $postData = $this->get_post_raw_data();
        $result = $this->update_group_data($postData, $hx_group_id);
        if ($result) {
            echo json_encode(array('statusCode' => 1, 'data' => $result, 'statusMsg' => '更新成功成功'));
            exit;
        }
        echo json_encode(array('statusCode' => -1, 'statusMsg' => '更新失败'));
        exit;
    }

    function update_group_data($data, $hx_group_id)
    {
        $this->loadModel('ChatGroup');
        $groupname = $data['groupname'];
        $desc = $data['description'];
        $maxusers = $data['maxusers'];
        $result = false;
        if ($this->ChatGroup->updateAll(['maxusers' => $maxusers, 'description' => "'" . $desc . "'", 'group_name' => "'" . $groupname . "'"], ['hx_group_id' => $hx_group_id])) {
            $result = $this->ChatUtil->update_group($data, $hx_group_id);
        }
        return $result;
    }

    function save_group_data($data, $hx_group_id)
    {

        $chatGroupM = ClassRegistry::init('ChatGroup');
        $userGroupM = ClassRegistry::init('UserGroup');
        $date_now = date('Y-m-d H:i:s');
        $approval = 0;
        $public = 0;
        $creator = $data['owner'];
        $desc = $data['desc'];
        $groupname = $data['groupname'];
        $groupcode = make_union_code();
        $group_data = array('hx_group_id' => $hx_group_id, 'created' => $date_now, 'creator' => $creator, 'approval' => $approval, 'maxusers' => 500, 'is_public' => $public, 'description' => $desc, 'group_name' => $groupname, 'group_code' => $groupcode);
        $group_result = $chatGroupM->save($group_data);
        if ($group_result) {
            $group_id = $group_result['ChatGroup']['id'];
            $member_ids = $data['members'] ? $data['members'] : array();
            $member_ids[] = $data['owner'];
            $member_data = [];
            foreach ($member_ids as $m_id) {
                $member_data[] = array('user_id' => $m_id, 'group_id' => $group_id, 'created' => $date_now, 'updated' => $date_now);
            }
            $userGroupM->saveAll($member_data);
            return $group_result;
        }
        return false;
    }

    public function get_my_groups()
    {
        $uid = $this->currentUser['id'];
        $user_groups = $this->UserGroup->find('all', array(
            'conditions' => array(
                'deleted' => DELETED_NO,
                'user_id' => $uid
            ),
            'limit' => 500
        ));
        $group_ids = Hash::extract($user_groups, '{n}.UserGroup.group_id');
        $groups = $this->ChatGroup->find('all', array(
            'conditions' => array(
                'id' => $group_ids
            )
        ));
        $groups = Hash::extract($groups, '{n}.ChatGroup');
        echo json_encode(array('groups' => $groups));
        exit;
    }

    public function get_group_info($hx_group_id)
    {
        $chatGroupM = ClassRegistry::init('ChatGroup');
        $chatGroup = $chatGroupM->find('first', array(
            'conditions' => array(
                'hx_group_id' => $hx_group_id
            )
        ));
        $userGroupM = ClassRegistry::init('UserGroup');
        $group_id = $this->get_group_id($hx_group_id);
        $member_count = $userGroupM->find('count', array(
            'conditions' => array(
                'group_id' => $group_id,
                'deleted' => DELETED_NO
            )
        ));
        $groupInfo = $chatGroup['ChatGroup'];
        $groupInfo['member_count'] = $member_count;
        echo json_encode($groupInfo);
        exit;
    }

    public function get_group_members($hx_group_id)
    {
        $group_id = $this->get_group_id($hx_group_id);
        $members = $this->UserGroup->find('all', array(
            'conditions' => array(
                'group_id' => $group_id,
                'deleted' => DELETED_NO
            ),
            'limit' => 500
        ));
        $member_ids = Hash::extract($members, '{n}.UserGroup.user_id');
        $data = $this->ChatUtil->get_users_info($member_ids);
        echo json_encode($data);
        exit;
    }

    public function join_group_by_code($group_code)
    {
        $user = $this->currentUser  ['id'];
        list($group_id, $hx_group_id) = $this->get_group_by_code($group_code);
        $date_now = date('Y-m-d H:i:s');
        $save_result = true;
        if (!$this->UserGroup->hasAny(array('user_id' => $user, 'group_id' => $group_id))) {
            $save_data[] = array('user_id' => $user, 'group_id' => $group_id, 'created' => $date_now, 'updated' => $date_now);
            $save_result = $this->UserGroup->saveAll($save_data);
        }
        if ($save_result) {
            $this->ChatUtil->add_group_member($user, $hx_group_id);
            echo json_encode(array('statusCode' => 0, 'statusMsg' => '添加成功'));
            return;
        }
        echo json_encode(array('statusCode' => -2, 'statusMsg' => '添加失败'));
        exit;
    }

    public function add_group_members($hx_group_id)
    {
        $postData = $this->get_post_raw_data();
        $user_ids = $postData['usernames'];
        $save_data = array();
        $date_now = date('Y-m-d H:i:s');
        $group_id = $this->get_group_id($hx_group_id);
        foreach ($user_ids as $uid) {
            if (!$this->UserGroup->hasAny(array('user_id' => $uid, 'group_id' => $group_id))) {
                $save_data[] = array('user_id' => $uid, 'group_id' => $group_id, 'created' => $date_now, 'updated' => $date_now);
            }
        }
        $save_result = $this->UserGroup->saveAll($save_data);
        if ($save_result) {
            $this->ChatUtil->add_group_members($user_ids, $hx_group_id);
            echo json_encode(array('statusCode' => 0, 'statusMsg' => '添加成功'));
            return;
        }
        echo json_encode(array('statusCode' => -2, 'statusMsg' => '添加失败'));
        exit;
    }

    public function add_group_member($hx_group_id, $user_id)
    {
        $group_id = $this->get_group_id($hx_group_id);
        if ($this->UserGroup->hasAny(array('user_id' => $user_id, 'group_id' => $group_id, 'deleted' => DELETED_NO))) {
            echo json_encode(array('statusCode' => -1, 'statusMsg' => '用户已经在群里'));
            return;
        }
        $date_now = date('Y-m-d H:i:s');
        $save_data = array('user_id' => $user_id, 'group_id' => $group_id, 'created' => $date_now, 'updated' => $date_now);
        $user_group = $this->UserGroup->save($save_data);
        if ($user_group) {
            $this->ChatUtil->add_group_member($user_id, $hx_group_id);
            echo json_encode(array('statusCode' => 1, 'statusMsg' => '添加成功', 'data' => $user_group));
            exit;
        }
        echo json_encode(array('statusCode' => -1, 'statusMsg' => '添加失败'));
        exit;
    }

    public function delete_group_member($hx_group_id, $user_id)
    {
        $group_id = $this->get_group_id($hx_group_id);
        $update_result = $this->UserGroup->updateAll(array('deleted' => DELETED_YES), array('group_id' => $group_id, 'user_id' => $user_id, 'deleted' => DELETED_NO));
        if ($update_result) {
            $this->ChatUtil->delete_group_member($user_id, $hx_group_id);
            echo json_encode(array('statusCode' => 1, 'statusMsg' => '删除成功'));
            exit;
        }
        echo json_encode(array('statusCode' => -1, 'statusMsg' => '删除失败'));
        exit;
    }

    public function delete_group_members($hx_group_id)
    {
        $postData = parent::get_post_raw_data();
        $user_ids = $postData['usernames'];
        $group_id = $this->get_group_id($hx_group_id);
        $update_result = $this->UserGroup->updateAll(array('deleted' => DELETED_YES), array('group_id' => $group_id, 'user_id' => $user_ids));
        if ($update_result) {
            $hx_group_id = $this->get_hx_group_id($group_id);
            $this->ChatUtil->delete_group_members($user_ids, $hx_group_id);
            echo json_encode(array('statusCode' => 1, 'statusMsg' => '删除成功'));
            exit;
        }
        echo json_encode(array('statusCode' => -1, 'statusMsg' => '删除失败'));
        exit;
    }


    private function get_group_id($hx_group_id)
    {
        $chatGroup = $this->ChatGroup->find('first', array(
            'conditions' => array(
                'hx_group_id' => $hx_group_id
            )
        ));
        return $chatGroup['ChatGroup']['id'];
    }

    private function get_group_by_code($code)
    {
        $chatGroup = $this->ChatGroup->find('first', [
            'conditions' => [
                'group_code' => $code
            ]
        ]);
        return [$chatGroup['ChatGroup']['id'], $chatGroup['ChatGroup']['hx_group_id']];
    }

    private function get_hx_group_id($group_id)
    {
        $chatGroup = $this->ChatGroup->find('first', array(
            'conditions' => array(
                'id' => $group_id
            )
        ));
        return $chatGroup['ChatGroup']['hx_group_id'];
    }

    public function get_user_groups()
    {
        $uid = $this->currentUser['id'];
        $this->loadModel('ChatGroup');
        $this->loadModel('UserGroup');
        $this->loadModel('Weshare');
        $this->loadModel('Order');
        $ugs = $this->UserGroup->find('all', [
            'conditions' => [
                'user_id' => $uid
            ],
            'fields' => ['UserGroup.group_id', 'UserGroup.id']
        ]);

        $gids = Hash::extract($ugs, '{n}.UserGroup.group_id');
        $groups = $this->ChatGroup->find('all', [
            'conditions' => [
                'ChatGroup.id' => $gids
            ],
            'joins' => [
                [
                    'alias' => 'User',
                    'type' => 'INNER',
                    'table' => 'cake_users',
                    'conditions' => 'User.id = ChatGroup.creator'
                ]
            ],
            'fields' => ['ChatGroup.id', 'ChatGroup.group_name', 'ChatGroup.hx_group_id', 'User.nickname', 'ChatGroup.creator', 'User.image', 'User.avatar'],
            'limit' => 10
        ]);

        $result = [];
        $proxys = [];
        foreach ($groups as $g) {
            $proxys[] = $g['ChatGroup']['creator'];
            $result[] = ['id' => $g['ChatGroup']['id'], 'hx_group_id' => $g['ChatGroup']['hx_group_id'], 'group_name' => $g['ChatGroup']['group_name'], 'creator' => $g['ChatGroup']['creator'], 'creator_name' => $g['User']['nickname'], 'creator_image' => get_user_avatar($g['User'])];
        }

        $member_count = $this->UserGroup->find('all', [
            'conditions' => [
                'group_id' => $gids
            ],
            'group' => 'group_id',
            'fields' => ['group_id', 'COUNT(`id`) as `member_count`']
        ]);

        $member_count = Hash::combine($member_count, '{n}.UserGroup.group_id', '{n}.0.member_count');

        $order_summary = $this->Order->find('all', [
            'conditions' => [
                'type' => ORDER_TYPE_WESHARE_BUY,
                'not' => ['status' => ORDER_STATUS_WAITING_PAY],
                'brand_id' => $proxys
            ],
            'group' => 'brand_id',
            'fields' => ['brand_id', 'COUNT(`id`) as `order_count`']
        ]);

        $order_summary = Hash::combine($order_summary, '{n}.Order.brand_id', '{n}.0.order_count');

        $share_summary = $this->Weshare->find('all', [
            'conditions' => [
                'status' => WESHARE_STATUS_NORMAL,
                'creator' => $proxys,
            ],
            'group' => 'creator',
            'fields' => ['creator', 'COUNT(`id`) as `weshare_count`']
        ]);

        $share_summary = Hash::combine($share_summary, '{n}.Weshare.creator', '{n}.0.weshare_count');

        foreach($result as &$item){
            $item['member_count'] = $member_count[$item['id']];
            $item['share_count'] = $share_summary[$item['creator']];
            $item['order_count'] = $order_summary[$item['creator']];
        }

        echo json_encode($result);
        exit;
    }

}