<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 7/8/15
 * Time: 14:37
 */
class WeshareBuyComponent extends Component {
    //TODO 重构 weshare controller

    var $name = 'WeshareBuyComponent';

    var $uses = array('Order', 'Weshare', 'User');

    public $components = array('Session', 'Weixin');

    public function send_new_share_msg() {


    }

    public function load_fans_buy_sharer($sharerId) {
        $weshares = $this->Weshare->find('all', array(
            'conditions' => array(
                'creator' => $sharerId
            ),
            'fields' => array('id')
        ));
        $weshare_ids = Hash::extract($weshares, '{n}.Weshare.id');
        $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED);
        $follower = $this->Order->find('all', array(
            'conditions' => array(
                'member_id' => $weshare_ids,
                'status' => $order_status,
                'type' => ORDER_TYPE_WESHARE_BUY
            ),
            'fields' => array('DISTINCT creator')
        ));
        return $follower;
    }

}