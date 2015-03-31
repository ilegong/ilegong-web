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
        $TuanTeamM = ClassRegistry::init('TuanTeam');
        $productM = ClassRegistry::init('Product');
        $has_joined_tuan = $TuanMemberM->hasAny(array('tuan_id' => $tuan_id, 'uid' => $uid));
        if(!$has_joined_tuan){
            $data['tuan_id'] = $tuan_id;
            $data['uid'] = $uid;
            $data['join_time'] = date('Y-m-d H:i:s');
            $TuanMemberM->save($data);
            $TuanTeamM->update(array('member_num' => 'member_num + 1'), array('id' => $tuan_id));
        }

        //send join tuan buy success msg
        $tuan = $TuanTeamM->find('first',array(
            'conditions' => array(
                'id' => $tuan_id
            )
        ));
        $product_id = $tuan_buying['TuanBuying']['pid'];
        $product = $productM->find('first',array(
            'conditions' => array(
                'id'=>$product_id
            )
        ));

        $product_name = $product['Product']['name'];
        $tuan_name = $tuan['TuanTeam']['tuan_name'];
        $title='您已参加'.$tuan_name.'发起的一个团购';
        $tuan_leader = $tuan['TuanTeam']['leader_weixin'];
        $consign_time = $tuan_buying['TuanBuying']['consign_time'];
        $consign_time = friendlyDateFromStr($consign_time,FFDATE_CH_MD);
        $product_name = $product_name.', '.$consign_time.'发货';
        $remark = '目标'.$tuan_buying['TuanBuying']['target_num'].'份，现在'.$tuan_buying['TuanBuying']['sold_num'].'份，点击详情，赶紧邀请小伙伴们加入，享受成团优惠价！';
        $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$memberId;;
        //$this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
        send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
        //send sold num complete msg
    }
}