<?php
class ChatUtilComponent extends Component
{

    public function get_users_info($uids)
    {
        $userM = ClassRegistry::init('User');
        $userLevelM = ClassRegistry::init('UserLevel');
        $user_infos = $userM->find('all', array(
            'conditions' => array(
                'id' => $uids,
            ),
            'fields' => array('id', 'image', 'nickname', 'is_proxy', 'avatar')
        ));
        $user_levels = $userLevelM->find('all', array(
            'conditions' => array(
                'data_id' => $uids,
                'type' => 0
            ),
            'fields' => array(
                'data_id', 'data_value'
            )
        ));
        $user_infos = Hash::extract($user_infos, '{n}.User');
        $user_levels = Hash::combine($user_levels, '{n}.UserLevel.data_id', '{n}.UserLevel.data_value');
        foreach ($user_infos as &$user_item) {
            $uid = $user_item['id'];
            $level = -1;
            if ($user_levels[$uid]) {
                $level = $user_levels[$uid];
            }
            $level_text = get_user_level_text($level);
            $user_item['level'] = $level_text;
            $user_item['image'] = get_user_avatar($user_item);
            unset($user_item['avatar']);
        }
        return $user_infos;
    }

    public function reg_hx_user($user_id)
    {
        $hxUser = $this->get_hx_user();
        $hx_password = $this->get_password(12);
        if ($this->set_user_hx_password($user_id, $hx_password)) {
            $hx_user_data = array(
                array('username' => $user_id, 'password' => $hx_password)
            );
            $json_result = $hxUser->regUserOnAuth($hx_user_data);
            $this->log('reg hx user ' . json_encode($json_result));
            if (empty($json_result['error'])) {
                return array('statusCode' => 1, 'hx_user' => array('username' => $user_id, 'password' => $hx_password));
            }
            if ($json_result['error'] == 'duplicate_unique_property_exists') {
                return array('statusCode' => -2, 'statusMsg' => '已经注册');
            } else {
                return array('statusCode' => -3, 'statusMsg' => '注册IM用户失败');
            }
        }
        return array('statusCode' => -1, 'statusMsg' => '注册失败');
    }

    public function delete_friend($user_id, $friend_id)
    {
        $hxUser = $this->get_hx_user();
        $json_result = $hxUser->deleteFriendOnUser($user_id, $friend_id);
        $this->log('delete friend result ' . json_encode($json_result));
        if (empty($json_result['error'])) {
            return true;
        }
        return false;
    }

    public function add_friend($user_id, $friend_id)
    {
        $hxUser = $this->get_hx_user();
        $json_result = $hxUser->addFriendToUser($user_id, $friend_id);
        $this->log('add friend result ' . json_encode($json_result));
        $result = json_decode($json_result, true);
        if (empty($result['error'])) {
            return true;
        }
        return false;
    }

    public function update_hx_user()
    {

    }

    public function set_friend_block()
    {

    }

    public function get_user_status($username)
    {
        $hxUser = $this->get_hx_user();
        return $hxUser->getUserStatus($username);
    }

    public function create_group($data)
    {
        $hxGroup = $this->get_hx_group();
        $result = $hxGroup->createGroup($data);
        if ($result['error']) {
            $this->log('create group error' . json_encode($result));
            return false;
        }
        $hx_group_id = $result['data']['groupid'];
        return $hx_group_id;
    }


    public function update_group($data, $group_id)
    {
        $hxGroup = $this->get_hx_group();
        $result = $hxGroup->updateGroup($data, $group_id);
        if ($result['error']) {
            $this->log('update group error' . json_encode($result));
            return false;
        }
        return $group_id;
    }

    public function delete_group_member($user_id, $group_id)
    {
        $hxGroup = $this->get_hx_group();
        $result = $hxGroup->removeMember($user_id, $group_id);
        if ($result['error']) {
            $this->log('remove group member fail ' . json_encode($result));
            return false;
        }
        return true;
    }

    public function delete_group_members($user_ids, $group_id)
    {
        $hxGroup = $this->get_hx_group();
        $result = $hxGroup->removeMembers($user_ids, $group_id);
        if ($result['error']) {
            $this->log('remove group members fail ' . json_encode($result));
            return false;
        }
        return true;
    }

    public function add_group_members($user_ids, $group_id)
    {
        $hxGroup = $this->get_hx_group();
        $data = array('usernames' => $user_ids);
        $result = $hxGroup->addMembers($data, $group_id);
        if ($result['error']) {
            $this->log('add group members fail ' . json_encode($result));
            $this->log('add group member error for hx group id ' . $group_id . ' user ids ' . implode(',', $user_ids));
            return false;
        }
        return true;
    }

    public function add_group_member($user_id, $group_id)
    {
        $hxGroup = $this->get_hx_group();
        $result = $hxGroup->addMember($user_id, $group_id);
        if ($result['error']) {
            $this->log('add group member fail ' . json_encode($result));
            $this->log('add group member error for hx group id ' . $group_id . ' user id ' . $user_id);
            return false;
        }
        return true;
    }

    public function deactivate_user()
    {

    }

    public function active_user()
    {

    }

    public function force_user_disconnect()
    {

    }

    function set_user_hx_password($user_id, $password)
    {
        $userM = ClassRegistry::init('User');
        return $userM->update(array('hx_password' => "'" . $password . "'"), array('id' => $user_id));
    }

    function get_password($length = 8)
    {
        $str = substr(md5(time()), 0, $length);
        return $str;
    }


    function get_hx_group()
    {
        App::import('Vendor', 'hx/HxGroup');
        $hxGroup = new HxGroup(HX_APP_NAME, HX_CLIENT_ID, HX_CLIENT_SECRET);
        return $hxGroup;

    }

    function get_hx_user()
    {
        App::import('Vendor', 'hx/HxUser');
        $hxUser = new HxUser(HX_APP_NAME, HX_CLIENT_ID, HX_CLIENT_SECRET);
        return $hxUser;
    }

    function send_msg($type, $target, $msg, $from, $ext){
        $result = $this->get_hx_message()->sendMessage($type, $target, $msg, $from, $ext);
        return $result;
    }

    function get_hx_message(){
        App::import('Vendor', 'hx/HxMessage');
        $hxMessage = new HxMessage(HX_APP_NAME, HX_CLIENT_ID, HX_CLIENT_SECRET);
        return $hxMessage;
    }

    function get_user_info($id){
        $userM = ClassRegistry::init('User');
        $u = $userM->findById($id);
        return ['avatar' => get_user_avatar($u), 'userId' => $u['User']['id'], 'nickname' => $u['User']['nickname']];
    }

}