<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/16/14
 * Time: 7:10 PM
 */

class AwardInfo extends AppModel {

    /**
     * @param $uid
     * @param $type
     * @return mixed
     */
    public function getAwardInfoByUidAndType($uid, $type) {
        $awardTimes = $this->find('first', array(
            'conditions' => array('uid' => $uid, 'type' => $type),
            'fields' => array('times', 'got', 'id'),
        ));
        return $awardTimes ? $awardTimes['AwardInfo'] : false;
    }
} 