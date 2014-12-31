<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/31/14
 * Time: 4:56 PM
 */

class GrouponMember extends AppModel {

    public function find_by_uid_and_groupon_id($groupId, $uid) {
        return $this->find('first', array('conditions' => array(
            'groupon_id' => $groupId,
            'user_id' => $uid
        )));
    }

}