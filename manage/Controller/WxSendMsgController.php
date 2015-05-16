<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 5/15/15
 * Time: 20:06
 */

class WxSendMsgController extends AppController{

    var $name = 'WxSendMsg';

    var $uses = array('Order','Oauthbind','TuanTeam','TuanProduct','ProductTry');

    public function admin_to_send_wx_msg(){
        //$this->getZitiOrderUserIds();
    }

    public function admin_send_wx_msg(){
        $this->autoRender=false;
        $data_id = $_POST['data_id'];
        $data_type = $_POST['data_type'];
        $result = $this->sendBuyTipMsg($data_id,$data_type);
        echo json_encode($result);
        return;
    }

    private function sendBuyTipMsg($data_id,$data_type){
        $msgData = $this->prepareMsgData($data_id,$data_type);
        if($msgData!=null){
            return array('success'=>false,'reason'=>'商品数据有问题');
        }
        $now = date('Y-m-d');
        $startDate = date('Y-m-d',strtotime('-7 day'.$now));
        $users = $this->Order->query('SELECT DISTINCT creator,consignee_id FROM cake_orders WHERE ship_mark = "ziti" AND created > \''.$startDate.'\' AND created < \''.$now.'\'');
        $userIds = Hash::extract($users,'{n}.cake_orders.creator');
        $consigneeIds = Hash::extract($users,'{n}.cake_orders.consignee_id');
        $userConigneeMap = Hash::combine($users,'{n}.cake_orders.creator','{n}.cake_orders.consignee_id');
        $tuanTeams = $this->TuanTeam->find('all',array(
            'conditions' => array(
                'offline_store_id' => $consigneeIds
            ),
            'fields' => array(
                'leader_name', 'leader_weixin', 'tuan_name', 'offline_store_id'
            )
        ));
        $tuanTeams = Hash::combine($tuanTeams,'{n}.TuanTeam.offline_store_id','{n}.TuanTeam');
        $openIds = $this->Oauthbind->find('all',array(
            'conditions' => array(
                'user_id' => $userIds,
            ),
            'fields' => array(
                'oauth_openid','user_id'
            )
        ));
        $openIds = Hash::combine($openIds,'{n}.Oauthbind.user_id','{n}.Oauthbind.oauth_openid');
        //loop send template msg
        foreach($openIds as $uid => $openId){
            $leader_name = $tuanTeams[$userConigneeMap[$uid]]['leader_name'].' 微信号:'.$tuanTeams[$userConigneeMap[$uid]]['leader_weixin'];
            send_tuan_tip_msg($openId,$msgData['title'],$msgData['productName'],$leader_name,$msgData['remark'],$msgData['detail_url']);
        }
        return array('success' => true);
    }

    private function prepareMsgData($data_id, $data_type){
        if($data_type==0){
            $product = $this->TuanProduct->find('first',array('conditions' => array('id' => $data_id)));
            $productName = $product['TuanProduct']['alias'];
            $title = '亲,'.$productName.'开始团购,快来团购吧！';
            $remark = '点击立即购买';
            $detail_url = product_link($product['TuanProduct']['product_id'],'/');
            return array('productName' => $productName,'title' => $title,'remark' => $remark,'detail_url' => $detail_url);
        }

        if($data_type==1){
            $product = $this->ProductTry->find('first',array('conditions' => array('id' => $data_id)));
            $productName = $product['ProductTry']['product_name'].$product['ProductTry']['spec'];
            $title = '亲,'.$productName.'开始秒杀,快来秒杀吧！';
            $remark = '点击立即秒杀';
            $detail_url = '/';
            return array('productName' => $productName,'title' => $title,'remark' => $remark,'detail_url' => $detail_url);
        }
        return null;
    }

}