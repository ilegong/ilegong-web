<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 11/17/14
 * Time: 12:59 AM
 */

class SharedOffer extends AppModel {
    var $recursive = 0;
    public $belongsTo = array(
        'ShareOffer' => array(
            'className' => 'ShareOffer',
            'foreignKey' => 'share_offer_id'
        ));


    public function find_user_shared_offer($userId, $orderId, $offerId) {
        return $this->find('first', array('conditions' => array('uid' => $userId, 'order_id' => $orderId, 'share_offer_id' => $offerId)));
    }

    public function find_shared_slices($shared_offer_id) {
        return $this->_find_shared_slices($shared_offer_id);
    }

    private function _find_shared_slices($shared_offer_id, $accepted = null) {
        $sharedSlicesM = ClassRegistry::init('SharedSlice');
        $cond = array(
            'shared_offer_id' => $shared_offer_id
        );

        if ($accepted != null) {
            if ($accepted)
                $cond['accept_user > '] = 0;
            else
                $cond['accept_user'] = 0;
        }

        return $sharedSlicesM->find('all', array(
            'conditions' => $cond,
        ));
    }
}