<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/31/14
 * Time: 6:45 PM
 */

class Groupon extends AppModel {

    public function paid_done($groupon_id, $order_type) {
        $gm = $this->findById($groupon_id);
        if (!empty($gm)) {
            $pay_number = $gm['Groupon']['pay_number'];
            if ($order_type == ORDER_TYPE_GROUP) {
                $toUpdate = array('pay_number' => 'pay_number + 1');
            } else {
                $toUpdate = array();
            }

            $teamM = ClassRegistry::init('Team');
            $team = $teamM->findById($gm['Groupon']['team_id']);
            if (!empty($team) && ($this->is_all_paid($groupon_id, $team, $gm, true)) ) {
                $toUpdate['status'] = STATUS_GROUP_REACHED;
            }
            $this->updateAll($toUpdate, array('id' => $groupon_id, 'pay_number' => $pay_number));
        }  else {
            $this->log('failed to handle paid_done: groupon_id='.$groupon_id.': not found');
        }
    }

    public function set_paid_done($groupon_id) {
        $this->updateAll(array('status' => STATUS_GROUP_REACHED), array('id' => $groupon_id));
    }

    public function calculate_balance($groupon_id, $team, $groupon) {

        if ($groupon == null) {
            $groupon = $this->find('first', array(
                'conditions' => array('id' => $groupon_id),
                'fields' => array('pay_number', 'team_id')
            ));
        }

        if ($team == null) {
            $teamM = ClassRegistry::init('Team');
            $team = $teamM->find('first', array(
                'conditions' => array('id' => $groupon['Groupon']['team_id']),
                'fields' => array('market_price', 'unit_pay', 'unit_val')
            ));
        }
        if (!empty($team) && !empty($groupon)) {
            $pay_number = $groupon['Groupon']['pay_number'];
            return max($team['Team']['market_price'] - $pay_number * $team['Team']['unit_val'], 0);
        } else {
            throw new CakeException("failed to calculate balance: groupon_id=" . $groupon_id);
        }
    }

    /**
     * @param $groupon_id
     * @param $team
     * @param $groupon
     * @param bool $just_paid
     * @throws CakeException
     * @return bool order paid?
     */
    public function is_all_paid($groupon_id, $team, $groupon, $just_paid = false) {
        if ($groupon == null) {
            $groupon = $this->find('first', array(
                'conditions' => array('id' => $groupon_id),
                'fields' => array('pay_number', 'user_id', 'team_id')
            ));
        }

        if ($team == null) {
            $teamM = ClassRegistry::init('Team');
            $team = $teamM->find('first', array(
                'conditions' => array('id' => $groupon['Groupon']['team_id']),
                'fields' => array('market_price', 'unit_pay', 'unit_val')
            ));
        }

        if (!empty($team) && !empty($groupon)) {

            $pay_number = $groupon['Groupon']['pay_number'] + ($just_paid?1:0);
            $total_paid = $pay_number * $team['Team']['unit_val'];

            $creator = $groupon['Groupon']['user_id'];
            $orderM = ClassRegistry::init('Order');
            $foundFills = $orderM->find('all', array(
                'conditions' => array('creator' => $creator, 'type' => ORDER_TYPE_GROUP_FILL, 'status' => ORDER_STATUS_PAID)
            ));
            if(!empty($foundFills)) {
                //一个人只能发团一次所以直接sql查询了
                $gmM = ClassRegistry::init('GrouponMember');
                foreach($foundFills as $o) {
                    $mid = $o['Order']['member_id'];
                    $gm = $gmM->findById($mid);
                    if (!empty($gm) && $gm['GrouponMember']['user_id'] == $creator && $gm['GrouponMember']['groupon_id'] == $groupon_id) {
                        $total_paid += $o['Order']['total_all_price'] * 100;
                        break;
                    }
                }
            }

            return $team['Team']['market_price'] <= $total_paid;
        } else {
            throw new CakeException("failed to check all paid: groupon_id=" . $groupon_id);
        }
    }
}