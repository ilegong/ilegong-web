<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 15/3/11
 * Time: 下午6:40
 */
class TuanBuying extends AppModel {

    public $components = array('Weixin');

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
            'conditions' => $product_id
        ));
        $product_name = $product['Product']['name'];
        $tuan_name = $tuan['TuanTeam']['tuan_name'];
        $title='您已参加'.$tuan_name.'发起的一个团购';
        $tuan_leader = $tuan['TuanTeam']['leader_weixin'];
        $consign_time = $tuan_buying['TuanBuying']['consign_time'];
        $consign_time = date(FORMAT_DATE,strtotime($consign_time));
        $product_name = $product_name.', '.$consign_time.'发货';
        $remark = '目标'.$tuan_buying['TuanBuying']['target_num'].'份，现在'.$tuan_buying['TuanBuying']['sold_num'].'份，点击详情，赶紧邀请小伙伴们加入，享受成团优惠价！';
        $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$memberId;;
        $this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
        //send sold num complete msg
        $target_num = intval($tuan_buying['TuanBuying']['target_num']);
        $sold_num = intval($tuan_buying['TuanBuying']['sold_num']);
        if($sold_num>=$target_num){
            $msg_element = get_tuan_msg_element($memberId);
            if(!empty($msg_element)){
                $consign_time = $msg_element['consign_time'];
                $uids = $msg_element['uids'];
                $tuan_name = $msg_element['tuan_name'];
                $target_num = $msg_element['target_num'];
                $product_name = $msg_element['product_name'];
                $title = '您参加的'.$tuan_name.'团购成功,目标'.$target_num.'份，已经成团，吼吼。';
                $tuan_leader = $msg_element['tuan_leader'];
                $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$memberId;
                $remark = '我们将在'.$consign_time.'给你送货，请留意后续消息！';
                foreach($uids as $uid){
                    $this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
                    //TODO log fail user id
                }
            }
        }
    }
}