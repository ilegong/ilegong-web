<?php

class HxChatComponent extends Component
{

    var $name = 'HxChat';

    public function reg_user($user_id){
        App::import('Vendor', 'hx/HxUser.class.php');
        $hxUser = new HxUser(HX_APP_NAME, HX_CLIENT_ID, HX_CLIENT_SECRET);
    }

}