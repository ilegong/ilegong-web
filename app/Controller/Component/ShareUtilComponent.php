<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 8/20/15
 * Time: 15:04
 */

class ShareUtilComponent extends Component{

    var $name = 'ShareUtil';

    public $uses = array('Weixin', 'WeshareBuy');

    public function process_weshare_task($weshareId, $sharer_id) {
        $userRelationM = ClassRegistry::init('UserRelation');
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'member_id' => $weshareId,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => array(ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY)
            ),
            'fields' => array('id', 'creator'),
            'limit' => 500
        ));
        $saveDatas = array();
        foreach ($orders as $order) {
            if ($this->check_user_relation($sharer_id, $order['Order']['creator'])) {
                $itemData = array('user_id' => $sharer_id, 'follow_id' => $order['Order']['creator'], 'type' => 'Buy', 'created' => date('Y-m-d H:i:s'));
                $saveDatas[] = $itemData;
            }
        }
        $userRelationM->saveAll($saveDatas);
    }

    public function get_all_weshares(){
        $weshareM = ClassRegistry::init('Weshare');
        $allWeshares = $weshareM -> find('all' , array(
            'limit' => 200
        ));
        return $allWeshares;
    }

    public function check_user_relation($user_id, $follow_id) {
        $userRelationM = ClassRegistry::init('UserRelation');
        $relation = $userRelationM->find('all', array(
            'conditions' => array(
                'user_id' => $user_id,
                'follow_id' => $follow_id
            )
        ));
        return empty($relation);
    }
}