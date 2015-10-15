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
        446 => array(544307, 633345),
        542 => array(544307, 633345),
        22 => array(633345),
        441 => array(633345),
        484 => array(544307, 633345, 141),
        550 => array(544307, 633345, 141),
        586 => array(544307, 633345, 141, 870466),
        368 => array(544307, 633345),
        524 => array(544307, 633345),
        369 => array(544307, 633345),
        399 => array(544307, 633345),
        401 => array(544307, 633345),
        630 => array(544307, 633345),
        577 => array(633345),
        438 => array(633345),
        659 => array(68832, 633345),
        649 => array(633345),
        692 => array(841358,874821),
        664 => array(633345),
        744 => array(633345)
    );

    public function checkUserCanManageShare($share_id, $user_id) {
        $shareBindInfo = $this->shareBindData[$share_id];
        if (empty($shareBindInfo)) {
            return false;
        }
        return in_array($user_id, $shareBindInfo);
    }
}