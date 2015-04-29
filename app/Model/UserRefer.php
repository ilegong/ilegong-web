<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 1/27/15
 * Time: 4:10 PM
 */

class UserRefer extends AppModel {

    public function  be_referred_and_new($uid) {

        $ref = $this->find_first_refer_to_uid($uid);

        if (!empty($ref)) {
            $orderM = ClassRegistry::init('Order');
            $c = $orderM->count_received_order($uid);
            if ($c <= 0) {
                //o2o,利用闲置资源
                return true;
            }
        }

        return false;
    }

    public function update_referred_bind($uid, $nickname) {
        $ref = $this->find('first', array(
            'conditions' => array('to' => $uid, 'deleted' => DELETED_NO),
            'order' => 'created desc',
        ));

        if (!empty($ref)) {
            $result = $this->updateAll(array('bind_done' => 1, ), array('to' => $uid, 'id' => $ref['UserRefer']['id']));
            if ($result)  {
                $scoreM = ClassRegistry::init('Score');
                //默认给100积分
                $scoreM->add_score_by_refer_bind(100, $uid, $nickname, $ref['UserRefer']['from']);
            } else {
                $this->log('error to update update_referred_bind');
            }
        }
    }

    public function update_referred_new_order($uid) {
        $ref = $this->find_first_refer_to_uid($uid);
        if (!empty($ref) && $ref['UserRefer']['first_order_done'] != 1) {
            $result = $this->updateAll(array('first_order_done' => 1, ), array('first_order_done' => 0, 'to' => $uid, 'id' => $ref['UserRefer']['id']));

            if ($result)  {
                $scoreM = ClassRegistry::init('Score');
                //默认给10积分
                $scoreM->add_score_by_refer_accept_order(10, $uid, '', $ref['UserRefer']['from']);
            } else {
                $this->log('error to update update_referred_new_order');
            }
        }
    }

    /**
     * @param $uid
     * @return array
     */
    private function find_first_refer_to_uid($uid) {
        $ref = $this->find('first', array(
            'conditions' => array('to' => $uid, 'deleted' => DELETED_NO),
            'order' => 'created desc',
        ));
        return $ref;
    }
}
