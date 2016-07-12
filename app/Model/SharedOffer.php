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

    public static function slicesSharedOut($id, $status) {
        if ($status == SHARED_OFFER_STATUS_OUT) return true;

        $soM = ClassRegistry::init('SharedOffer');
        $notAcceptedSlices = $soM->_find_shared_slices($id, false);
        $noLeft = empty($notAcceptedSlices);
        if ($noLeft) {
            $soM->updateAll(array('status' => SHARED_OFFER_STATUS_OUT)
                , array('id' => $id, 'status' => SHARED_OFFER_STATUS_GOING));
        }
        return $noLeft;
    }

    public function find_my_offers_by_weshare_creator($uid, $shareCreator) {
        return $this->find('all', array('conditions' => array('SharedOffer.uid' => $uid, 'ShareOffer.sharer_id' => $shareCreator, 'SharedOffer.status' => array(SHARED_OFFER_STATUS_NEW, SHARED_OFFER_STATUS_GOING)), 'order' => 'SharedOffer.created desc'));
    }

    public function find_new_offers_by_weshare_creator($uid, $shareCreator) {
        return $this->find('all', array('conditions' => array('SharedOffer.uid' => $uid, 'ShareOffer.sharer_id' => $shareCreator, 'SharedOffer.status' => array(SHARED_OFFER_STATUS_NEW, SHARED_OFFER_STATUS_GOING)), 'order' => 'SharedOffer.created desc'));
    }

    public function find_new_offers_by_order_id($uid, $orderId, $shareCreator){
        $sharedOffer = $this->find('first', ['conditions' => ['SharedOffer.uid' => $uid, 'ShareOffer.sharer_id' => $shareCreator, 'SharedOffer.status' => [SHARED_OFFER_STATUS_NEW, SHARED_OFFER_STATUS_GOING], 'SharedOffer.order_id' => $orderId]]);
        $sliceCount = 0;
        if (!empty($sharedOffer)) {
            $sliceCount = $this->find_shared_slices_count($sharedOffer['SharedOffer']['id']);
        }
        return [$sharedOffer, $sliceCount];
    }

    public function find_offers_by_weshare_creator($shareCreator) {
        return $this->find('all', array('conditions' => array('ShareOffer.sharer_id' => $shareCreator), 'order' => 'SharedOffer.created desc'));
    }

    public function find_my_all_offers($uid) {
        return $this->find('all', array('conditions' => array('SharedOffer.uid' => $uid), 'order' => 'SharedOffer.created desc'));
    }

    public function find_user_shared_offer($userId, $orderId, $offerId) {
        return $this->find('first', array('conditions' => array('uid' => $userId, 'order_id' => $orderId, 'share_offer_id' => $offerId)));
    }

    public function find_user_comment_shared_offer($userId, $orderId, $offerId, $commentId){
        return $this->find('first', array('conditions' => array('uid' => $userId, 'order_id' => $orderId, 'share_offer_id' => $offerId, 'comment_id' => $commentId)));
    }

    public function find_shared_slices_count($shared_offer_id){
        $sharedSlicesM = ClassRegistry::init('SharedSlice');
        return $sharedSlicesM->find('count',['conditions' => ['shared_offer_id' => $shared_offer_id]]);
    }

    public function find_shared_slices($shared_offer_id) {
        return $this->_find_shared_slices($shared_offer_id);
    }

    private function _find_shared_slices($shared_offer_id, $accepted = null) {
        $sharedSlicesM = ClassRegistry::init('SharedSlice');
        $cond = array(
            'shared_offer_id' => $shared_offer_id
        );

        if ($accepted !== null) {
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