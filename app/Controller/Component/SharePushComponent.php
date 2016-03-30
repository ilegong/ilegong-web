<?php

class SharePushComponent{

    var $components = ['JPush'];

    static $MSG_BUY_TYPE = 0;

    static $MSG_COMMENT_TYPE = 1;

    static $MSG_FAQ_TYPE = 2;

    public function push_buy_msg($optLog, $share){
        $user_id = $optLog['user_id'];
        $share_thumb = $optLog['thumbnail'];
        $title = $share['title'];
        $sharer = $share['creator'];

    }

    public function push_faq_msg(){

    }

    public function push_comment_msg(){

    }

}