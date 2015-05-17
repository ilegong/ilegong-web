<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 1/27/15
 * Time: 4:10 PM
 */

class Refer extends AppModel {

    public function  be_referred_and_new($uid) {

        $ref = $this->find_first_refer_to_uid($uid);

        if (!empty($ref)) {
            $orderM = ClassRegistry::init('Order');
            $c = $orderM->count_paid_order($uid);
            if ($c <= 0) {
                //o2o,利用闲置资源
                return true;
            }
        }

        return false;
    }

    public function test() {
        $this->log("execute test");
    }

    public function update_referred_bind($uid, $nickname) {
        $ref = $this->find('first', array(
            'conditions' => array('to' => $uid, 'deleted' => DELETED_NO),
            'order' => 'created desc',
        ));

        if (!empty($ref)) {
            $result = $this->updateAll(array('bind_done' => 1, ), array('to' => $uid, 'id' => $ref['Refer']['id']));
            if ($result)  {
                $scoreM = ClassRegistry::init('Score');
                //默认给100积分
                if($scoreM->add_score_by_refer_bind(100, $uid, $nickname, $ref['Refer']['from'])) {
                    $userM = ClassRegistry::init('User');
                    $userM->add_score($ref['Refer']['from'], 100);
                    $this->log("add score: ".$ref['Refer']['from'].", 100, refer id".$ref['Refer']['id']);
                } else {
                    $this->log("failed to add score for refer bind mobile refer".$ref['Refer']['id']);
                }
                $scoreM->id=null;
                if($scoreM->add_score_by_refer_bind_mobile(1000, $uid, $ref['Refer']['from'])){
                    $userM = ClassRegistry::init('User');
                    $userM->add_score($uid, 1000);
                    $this->log("add score: ".$uid.", 100, refer id".$ref['Refer']['id']);
                }else{
                    $this->log("failed to add score for refer bind mobile referral".$ref['Refer']['id']);
                }

            } else {
                $this->log('error to update update_referred_bind');
            }
        }
    }

    public function update_referred_new_order($uid) {
        $ref = $this->find_first_refer_to_uid($uid);
        $this->log("debug: find_first_refer_to_uid ".json_encode($ref));
        if (!empty($ref) && $ref['Refer']['first_order_done'] != 1) {
            $this->log("debug: execute update all...");
            $result = $this->updateAll(array('first_order_done' => 1, ), array('first_order_done' => 0, 'to' => $uid, 'id' => $ref['Refer']['id']));
            $this->log("debug: execute update all...done:".$result);
            if ($result)  {
                try {
                    $userM = ClassRegistry::init('User');
                    $user = $userM->findById($uid);
                }catch (Exception $e) {
                    $this->log('error to update_referred_new_order for find user: uid='.$uid);
                }
                $scoreM = ClassRegistry::init('Score');
                //默认给10积分
                $scoreM->add_score_by_refer_accept_order(10, $uid, empty($user)?'':$user['User']['nickname'], $ref['Refer']['from']);
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
