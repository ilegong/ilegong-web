<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/17/14
 * Time: 9:20 AM
 */

class OrderShichi extends AppModel {

    public function bought_by_curr_user($tryId, $currUid) {
        return $this->find('count', array(
            'conditions' => array('data_id' => $tryId, 'creator' => $currUid)
        ));
    }

} 