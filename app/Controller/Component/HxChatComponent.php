<?php

class HxChatComponent extends Component
{

    var $name = 'HxChat';

    public function reg_hx_user($user_id)
    {
        App::import('Vendor', 'hx/HxUser.class.php');
        $hxUser = new HxUser(HX_APP_NAME, HX_CLIENT_ID, HX_CLIENT_SECRET);
        $hx_password = $this->get_password(12);
        if ($this->set_user_hx_password($user_id, $hx_password)) {
            $hx_user_data = array(
                array('username' => $user_id, 'password' => $hx_password)
            );
            $json_result = $hxUser->regUserOnAuth($hx_user_data);
            $result = json_decode($json_result, true);
            if (empty($result['error'])) {
                return array('statusCode' => 1, 'hx_user' => array('username' => $user_id, 'password' => $hx_password));
            }
        }
        return array('statusCode' => -1, 'statusMsg' => '注册失败');
    }

    public function add_friend($user_id, $friend_id)
    {
        App::import('Vendor', 'hx/HxUser.class.php');
        $hxUser = new HxUser(HX_APP_NAME, HX_CLIENT_ID, HX_CLIENT_SECRET);
        $json_result = $hxUser->addFriendToUser($user_id, $friend_id);
        $result = json_decode($json_result, true);
        if (empty($result['error'])) {
            return true;
        }
        return false;
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