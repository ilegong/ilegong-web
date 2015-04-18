<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 3/30/15
 * Time: 10:13
 */
class TuanMsgController extends AppController{

    var $name = "tuan_msg";

    var $uses = array('TuanBuyingMessages');

    public $components = array('Weixin');

    public function admin_send_tuan_buy_create_msg($tuanBuyId=null){
        $this->autoRender = false;
        $tuan_buy_id = $tuanBuyId?$tuanBuyId:$_REQUEST['tuan_buy_id'];
        $msg_element = get_tuan_msg_element($tuan_buy_id,false);
        if(empty($msg_element)) {
            echo json_encode(array('success' => false,'msg' => '该团购不存在,亲先创建..'));
            return;
        }

        if($msg_element['tuan_buy_status']!=0){
            echo json_encode(array('success' => false,'msg' => '只有进行中的团购才能推送该消息.'));
            return;
        }

        $consign_time = $msg_element['consign_time'];
        $uids = $msg_element['uids'];
        $tuan_name = $msg_element['tuan_name'];
        $title = '您参加的'.$tuan_name.',发起了一个新的团购。';
        $product_name = $msg_element['product_name'];
        $product_name = $product_name.', '.$consign_time.'发货';
        $tuan_leader = $msg_element['tuan_leader'];
        $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$tuan_buy_id;
        $remark = '点击详情，赶快和小伙伴一起团起来！';
        foreach($uids as $uid){
            $this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
            //TODO log fail user id
        }
        $this->save_msg_log(TUAN_CREATE_MSG,$tuan_buy_id);
        echo json_encode(array('success' => true,'msg' => '推送模板消息成功'));
    }

    public function admin_send_tuan_buy_complete_msg(){
        $this->autoRender = false;
        $tuan_buy_id = $_REQUEST['tuan_buy_id'];
        $msg_element = get_tuan_msg_element($tuan_buy_id);
        if(empty($msg_element)) {
            echo json_encode(array('success' => false,'msg' => '该团购不存在,亲先创建..'));
            return;
        }
        if($msg_element['tuan_buy_status']!=0&&$msg_element['tuan_buy_status']!=1){
            echo json_encode(array('success' => false,'msg' => '只有进行中或截单的团购才能推送该消息.'));
            return;
        }
        $consign_time = $msg_element['consign_time'];
        $uids = $msg_element['uids'];
        $tuan_name = $msg_element['tuan_name'];
        $target_num = $msg_element['target_num'];
        $product_name = $msg_element['product_name'];
        $title = '您参加的'.$tuan_name.'团购成功,目标'.$target_num.'份，已经成团，吼吼。';
        $tuan_leader = $msg_element['tuan_leader'];
        $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$tuan_buy_id;
        $remark = '我们将在'.$consign_time.'给你送货，请留意后续消息！';
        foreach($uids as $uid){
            $this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
        }
        $this->save_msg_log(TUAN_COMPLETE_MSG,$tuan_buy_id);
        echo json_encode(array('success' => true,'msg' => '推送模板消息成功'));
    }

    public function admin_send_tuan_buy_tip_by_id_msg(){
        $this->autoRender = false;
        $tuan_buy_id = $_REQUEST['tuan_buy_id'];
        $msg_element = get_tuan_msg_element($tuan_buy_id);
        if(empty($msg_element)) {
            echo json_encode(array('success' => false,'msg' => '该团购不存在,亲先创建..'));
            return;
        }
        if($msg_element['tuan_buy_status']!=0){
            echo json_encode(array('success' => false,'msg' => '只有进行中的团购才能推送该消息.'));
            return;
        }

        $uids = $msg_element['uids'];
        $tuan_name = $msg_element['tuan_name'];
        $target_num = intval($msg_element['target_num']);
        $sold_num = intval($msg_element['sold_num']);
        $product_name = $msg_element['product_name'];
        if($sold_num>=$target_num){
            return array('success' => false,'msg' => '该团已满');
        }
        $title = '您参加的'.$tuan_name.',目标'.$target_num.'份，现在'.$sold_num.'，还差'.($target_num-$sold_num).'份，加油，加油!';
        $tuan_leader = $msg_element['tuan_leader'];
        $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$tuan_buy_id;
        $remark = '点击详情，赶紧邀请小伙伴们加入，享受成团优惠价！';
        foreach($uids as $uid){
            $this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
            //TODO log fail user id
        }
        $this->save_msg_log(TUAN_TIP_MSG,$tuan_buy_id);
        echo json_encode(array('success' => true,'msg' => '推送模板消息成功'));
    }

    public function admin_send_tuan_buy_fail_msg(){
        $this->autoRender = false;
        $tuan_buy_id = $_REQUEST['tuan_buy_id'];
        $msg_element = get_tuan_msg_element($tuan_buy_id);
        if(empty($msg_element)) {
            echo json_encode(array('success' => false,'msg' => '该团购不存在,亲先创建..'));
            return;
        }
        if($msg_element['tuan_buy_status']!=2){
            echo json_encode(array('success' => false,'msg' => '只有已经取消的团购才能推送该消息.'));
            return;
        }

        $uids = $msg_element['uids'];
        $tuan_name = $msg_element['tuan_name'];
        $target_num = intval($msg_element['target_num']);
        $sold_num = intval($msg_element['sold_num']);
        $product_name = $msg_element['product_name'];
        $title = '呜呜,您参加的'.$tuan_name.'团购份数没有达到,目标'.$target_num.'份，现在只有'.$sold_num.'。';
        $tuan_leader = $msg_element['tuan_leader'];
        $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$tuan_buy_id;
        $remark = '我们将联系您退款或者延期，请留意后续消息！';
        foreach($uids as $uid){
            $this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
            //TODO log fail user id
        }
        $this->save_msg_log(TUAN_CANCEL_MSG,$tuan_buy_id);
        echo json_encode(array('success' => true,'msg' => '推送模板消息成功'));
    }

    public function admin_to_send_tuan_delay_msg($tuan_buy_id){
        $this->set('tuan_buy_id',$tuan_buy_id);
    }

    public function admin_send_tuan_delay_msg(){
        $this->autoRender=false;
        $tuan_buy_id = $_REQUEST['tuan_buy_id'];
        $tip_msg = $_REQUEST['msg'];
        $msg_element = get_tuan_msg_element($tuan_buy_id);
        if(empty($msg_element)) {
            echo json_encode(array('success' => false,'msg' => '该团购不存在,亲先创建..'));
            return;
        }
        if($msg_element['tuan_buy_status']!=0){
            echo json_encode(array('success' => false,'msg' => '只有进行中的团购才能推送该消息.'));
            return;
        }
        $uids = $msg_element['uids'];
        //$tuan_name = $msg_element['tuan_name'];
        $target_num = intval($msg_element['target_num']);
        $sold_num = intval($msg_element['sold_num']);
        $product_name = $msg_element['product_name'];
        if($sold_num>=$target_num){
            return array('success' => false,'msg' => '该团已满');
        }
        $title = $tip_msg;
        $tuan_leader = $msg_element['tuan_leader'];
        $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$tuan_buy_id;
        $remark = '点击详情，赶紧邀请小伙伴们加入，享受成团优惠价！';
        foreach($uids as $uid){
            $this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
            //TODO log fail user id
        }
        $this->save_msg_log(TUAN_TIP_MSG,$tuan_buy_id);
        echo json_encode(array('success' => true,'msg' => '推送模板消息成功'));
    }

    public function admin_send_tuan_buy_start_deliver_msg(){
        $this->autoRender = false;
        $tuan_buying_id = $_REQUEST['tuan_buying_id'];
        $tuan_deliver_msg = $_REQUEST['deliver_msg'];
        $msg_element = get_tuan_msg_element($tuan_buying_id);
        if(empty($tuan_deliver_msg)){
            echo json_encode(array('success' => false,'msg' => '模版消息推送失败'));
            return;
        }
        if(empty($msg_element)){
            echo json_encode(array('success' => false,'msg' => '该团购不存在,亲先创建..'));
            return;
        }
        if($msg_element['tuan_buy_status']!=1){
            echo json_encode(array('success' => false,'msg' => '只有已完成的团购才能推送该消息'));
            return;
        }
        $uids = $msg_element['uids'];
        $product_name = $msg_element['product_name'];
        $tuan_name = $msg_element['tuan_name'];
        $title = $tuan_deliver_msg;
        $tuan_leader = $msg_element['tuan_leader'];
        $remark = '点击查看团购详情';
        $detail_url = WX_HOST.'/tuan_buyings/detail/'.$tuan_buying_id;
        foreach($uids as $uid){
            $this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$detail_url);
        }
        $this->save_msg_log(TUAN_STARTDELIVER_MSG,$tuan_buying_id);
        echo json_encode(array('success' => false,'msg' => '推送模板消息成功'));
    }

    public function admin_send_tuan_buy_notify_deliver_msg(){
        $this->autoRender = false;
        $tuan_buying_id = $_REQUEST['tuan_buying_id'];
        $tuan_notify_msg = $_REQUEST['deliver_msg'];
        $msg_element = get_tuan_msg_element($tuan_buying_id);
        if(empty($tuan_notify_msg)){
            echo json_encode(array('success' => false,'msg' => '模版消息推送失败'));
            return;
        }
        if(empty($msg_element)){
            echo json_encode(array('success' => false,'msg' => '该团购不存在,亲先创建..'));
            return;
        }
        if($msg_element['tuan_buy_status']!=1){
            echo json_encode(array('success' => false,'msg' => '只有已完成的团购才能推送该消息'));
            return;
        }
        $uids = $msg_element['uids'];
        $product_name = $msg_element['product_name'];
        $title = $tuan_deliver_msg;
        $tuan_leader = $msg_element['tuan_leader'];
        $remark = '点击查看团购详情';
        $detail_url = WX_HOST.'/tuan_buyings/detail/'.$tuan_buying_id;
        foreach($uids as $uid){
            $this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$detail_url);
        }
        $this->save_msg_log(TUAN_NOTIFYDELIVER_MSG,$tuan_buying_id);
        echo json_encode(array('success' => true,'msg' => '推送模板消息成功'));
    }

    public function admin_send_message(){
        $this->autoRender = false;
        $tuanBuyingId = preg_split('/,/',$_REQUEST['tuanBuyingId']);
        $tuan_msg = $_REQUEST['msg'];
        foreach($tuanBuyingId as $tuanBuying_id){
            $msg_element = get_tuan_msg_element($tuanBuying_id);
            $product_name = $msg_element['product_name'];
            $tuan_name = $msg_element['tuan_name'];
            $tuan_addr = $msg_element['tuan_addr'];
            $title = '亲，您在'.$tuan_name.'团购的'.$product_name.'已经为您送到'.$tuan_addr.'已经发货啦，请您注意查收';
            $msg = $tuan_msg?$tuan_msg:$title;
            $consignee_phones = $msg_element['consignee_mobilephones'];
            foreach($consignee_phones as $consignee_phone){
                message_send($msg,$consignee_phone);
            }
        }
        echo json_encode(array('success' => true,'msg' => '短信发送成功'));
    }


    /**
     * 给所有团提示
     * @return array
     */
//    public function send_tuan_buy_tip_msg(){
//        $tuanBuyingM = ClassRegistry::init('TuanBuying');
//        $result = array();
//        $tb_ids = $tuanBuyingM->query('SELECT id FROM cake_tuan_buyings where sold_num >= target_num and status=0');
//        $tb_ids = Hash::extract($tb_ids,'{n}.cake_tuan_buyings.id');
//        foreach($tb_ids as $tb_id){
//            $msg_element = get_tuan_msg_element($tb_id);
//            if(!empty($msg_element)){
//                $uids = $msg_element['uids'];
//                $tuan_name = $msg_element['tuan_name'];
//                $target_num = intval($msg_element['target_num']);
//                $sold_num = intval($msg_element['sold_num']);
//                $product_name = $msg_element['product_name'];
//                $title = '您参加的'.$tuan_name.',目标'.$target_num.'份，现在'.$sold_num.'，还差'.($target_num-$sold_num).'份，加油，加油!';
//                $tuan_leader = $msg_element['tuan_leader'];
//                $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$tb_id;
//                $remark = '点击详情，赶紧邀请小伙伴们加入，享受成团优惠价！';
//                foreach($uids as $uid){
//                    $this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
//                    //TODO log fail user id
//                }
//                $result = array('success' => true,'msg' => '推送模板消息成功');
//            }else{
//                $result = array('success' => false,'msg' => '推送模板消息失败');
//            }
//        }
//        return $result;
//    }

    private function save_msg_log($type,$tb_id){
        $msg_log = array(
            'type' => $type,
            'flag' => $tb_id,
            'send_date' => date(FORMAT_DATETIME)
        );
        $this->TuanBuyingMessages->save($msg_log);
    }

}