<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 15/3/8
 * Time: 下午5:40
 */
class Tuan extends AppModel {
    public function paid_done($memberId, $order_id) {
        $CartM = ClassRegistry::init('Cart');
        $cart_info = $CartM->find('first', array(
            'conditions' => array('order_id' => $order_id),
            'fields' => array('num')
        ));
        $sold_num = $cart_info['Cart']['num'];
        $this->updateAll(array('join_num' => 'join_num + 1', 'sold_num' => 'sold_num + ' . $sold_num), array('id' => $memberId));
        $tuan_info = $this->find('first', array(
            'conditions' => array('order_id' => $order_id),
            'fields' => array('sold_num')
        ));
        if($tuan_info['Cart']['sold_num'] >= 20){
            $this->updateAll(array('status' => 1), array('id' => $memberId));
        }
    }

}