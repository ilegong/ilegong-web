<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/31/14
 * Time: 6:45 PM
 */

class Groupon extends AppModel {

    public function paid_done($groupon_id) {
        $gm = $this->findById($groupon_id);
        if (!empty($gm)) {
            $toUpdate = array('pay_number' => 'pay_number + 1');
            $pay_number = $gm['Groupon']['pay_number'];

            $teamM = ClassRegistry::init('Team');
            $team = $teamM->findById($gm['Groupon']['team_id']);
            $new_paid_num = $pay_number + 1;
            if (!empty($team) && $new_paid_num >= $team['Team']['min_number']) {
                $toUpdate['status'] = STATUS_GROUP_REACHED;
            }
            $this->updateAll($toUpdate, array('id' => $groupon_id, 'pay_number' => $pay_number));
        }  else {
            $this->log('failed to handle paid_done: groupon_id='.$groupon_id.': not found');
        }
    }
}