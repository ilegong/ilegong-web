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

    public function find_by_uid_and_id($id, $uid) {
        return $this->find('first', array('conditions' => array(
            'id' => $id,
            'user_id' => $uid
        )));
    }

    public function paid_done($memberId, $uid, $order_type) {
        $gm = $this->find_by_uid_and_id($memberId, $uid);
        if (!empty($gm)) {
            if ($order_type == ORDER_TYPE_GROUP) {
                $this->updateAll(array('status' => STATUS_GROUP_MEM_PAID), array('id' => $memberId));
            }
            $grouponM = ClassRegistry::init('Groupon');
            $grouponM->paid_done($gm['GrouponMemeber']['groupon_id'], $order_type);
        }
    }

}