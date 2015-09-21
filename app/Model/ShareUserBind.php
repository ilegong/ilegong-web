<?php

class ShareUserBind extends AppModel {

    public $useTable = false;

    /**
     * @var array
     *
     * $key => share_id
     * $val => array() user_ids
     */
    var $shareBindData = array(
        446 => array(544307),
        542 => array(544307)
    );

    public function checkUserCanManageShare($share_id, $user_id) {
        $shareBindInfo = $this->shareBindData[$share_id];
        return in_array($user_id, $shareBindInfo);
    }
}