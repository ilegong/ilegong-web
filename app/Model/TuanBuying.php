<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 15/3/11
 * Time: 下午6:40
 */
class TuanBuying extends AppModel {
    public function paid_done($memberId, $order_id) {
        $CartM = ClassRegistry::init('Cart');
        $cart_info = $CartM->find('first', array(
            'conditions' => array('order_id' => $order_id),
            'fields' => array('num','creator')
        ));
        $sold_num = $cart_info['Cart']['num'];
        $this->updateAll(array('join_num' => 'join_num + 1', 'sold_num' => 'sold_num + ' . $sold_num), array('id' => $memberId));
        $tuan_buying = $this->find('first', array(
            'conditions' => array('id' => $memberId),
            'fields' => array('tuan_id')
        ));
        $tuan_id = $tuan_buying['TuanBuying']['tuan_id'];
        $uid = $cart_info['Cart']['creator'];
        $TuanMemberM = ClassRegistry::init('TuanMember');
        $has_joined_tuan = $TuanMemberM->hasAny(array('tuan_id' => $tuan_id, 'uid' => $uid));
        if(!$has_joined_tuan){
            $data['tuan_id'] = $tuan_id;
            $data['uid'] = $uid;
            $data['join_time'] = date('Y-m-d H:i:s');
            $TuanMemberM->save($data);
        }
//        $tuan_info = $this->find('first', array(
//            'conditions' => array('id' => $memberId),
//            'fields' => array('sold_num')
//        ));
//        if($tuan_info['Cart']['sold_num'] >= 100){
//            $this->updateAll(array('status' => 1), array('id' => $memberId));
//        }
    }

}