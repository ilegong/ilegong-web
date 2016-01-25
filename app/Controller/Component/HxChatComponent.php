<?php

class HxChatComponent extends Component
{

    var $name = 'HxChat';

    public function reg_hx_user($user_id)
    {
        App::import('Vendor', 'hx/HxUser');
        $hxUser = new HxUser(HX_APP_NAME, HX_CLIENT_ID, HX_CLIENT_SECRET);
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
        App::import('Vendor', 'hx/HxUser');
        $hxUser = new HxUser(HX_APP_NAME, HX_CLIENT_ID, HX_CLIENT_SECRET);
        $json_result = $hxUser->deleteFriendOnUser($user_id, $friend_id);
        $this->log('delete friend result ' . json_encode($json_result));
        if (empty($json_result['error'])) {
            return true;
        }
        return false;
    }

    public function add_friend($user_id, $friend_id)
    {
        App::import('Vendor', 'hx/HxUser');
        $hxUser = new HxUser(HX_APP_NAME, HX_CLIENT_ID, HX_CLIENT_SECRET);
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

    public function get_user_status()
    {

    }

    public function create_group()
    {

    }

    public function update_group()
    {

    }

    public function add_group_members()
    {

    }

    public function add_group_member()
    {

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


}