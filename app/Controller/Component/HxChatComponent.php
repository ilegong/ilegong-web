<?php

class HxChatComponent extends Component
{

    var $name = 'HxChat';

    public function reg_user($user_id)
    {
        App::import('Vendor', 'hx/HxUser.class.php');
        $hxUser = new HxUser(HX_APP_NAME, HX_CLIENT_ID, HX_CLIENT_SECRET);
        $hx_password = $this->get_password(12);
        if ($this->set_user_hx_password($user_id, $hx_password)) {
            $hx_user_data = array(
                array('username' => $user_id, 'password' => $hx_password)
            );
            $hxUser->regUserOnAuth($hx_user_data);
        }
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