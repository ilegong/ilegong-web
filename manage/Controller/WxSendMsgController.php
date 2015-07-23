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


    public function admin_send_wx_msg_for_rice() {
        $this->autoRender = false;
        $query_sql = 'SELECT user_id,mobile_num FROM  cake_candidates WHERE id IN ( SELECT candidate_id FROM cake_candidate_events WHERE event_id=4) AND vote_num >=100 ORDER BY  vote_num DESC LIMIT 0,100';
        $query_data = $this->Order->query($query_sql);
        $user_ids = Hash::extract($query_data,'{n}.cake_candidates.user_id');
        $openIds = $this->Oauthbind->find('all',array(
            'conditions' => array(
                'user_id' => $user_ids,
            ),
            'fields' => array(
                'oauth_openid','user_id'
            )
        ));
        $openIds = Hash::combine($openIds,'{n}.Oauthbind.user_id','{n}.Oauthbind.oauth_openid');
        foreach($openIds as $uid=>$openId){
            send_rice_prize_msg($openId,'');
        }
        echo json_encode(array('success' => true));
        return;
    }


    public function admin_send_wx_msg_for_tea(){
        $this->autoRender=false;
        $now = date('Y-m-d');
        $startDate = date('Y-m-d',strtotime('-50 day'.$now));
        $users = $this->Order->query('SELECT DISTINCT creator,consignee_id FROM cake_orders WHERE ship_mark = "ziti" AND created > \''.$startDate.'\' AND created < \''.$now.'\'');
        $userIds = Hash::extract($users,'{n}.cake_orders.creator');
        //$userIds = array(633345,544307);
        $openIds = $this->Oauthbind->find('all',array(
            'conditions' => array(
                'user_id' => $userIds,
            ),
            'fields' => array(
                'oauth_openid','user_id'
            )
        ));
        $openIds = Hash::combine($openIds,'{n}.Oauthbind.user_id','{n}.Oauthbind.oauth_openid');
        foreach($openIds as $uid => $openId){
            $leader_name = '朱晓宇';
            send_tuan_tip_msg($openId,'参加朋友说发起的蒲城塬上品酥梨29.9包邮的','蒲城塬上品酥梨',$leader_name,'点击查看详情','www.tongshijia.com/weshares/view/16');
        }
        echo json_encode(array('success' => true));
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
        if(empty($msgData)){
            return array('success'=>false,'reason'=>'商品数据有问题');
        }
        $now = date('Y-m-d');
        $startDate = date('Y-m-d',strtotime('-14 day'.$now));
        $users = $this->Order->query('SELECT DISTINCT creator,consignee_id FROM cake_orders WHERE ship_mark = "ziti" AND created > \''.$startDate.'\' AND created < \''.$now.'\'');
        $userIds = Hash::extract($users,'{n}.cake_orders.creator');
        //$userIds = array(633345);
        $openIds = $this->Oauthbind->find('all',array(
            'conditions' => array(
                'user_id' => $userIds,
            ),
            'fields' => array(
                'oauth_openid','user_id'
            )
        ));
        $openIds = Hash::combine($openIds,'{n}.Oauthbind.user_id','{n}.Oauthbind.oauth_openid');
        foreach($openIds as $uid => $openId){
            $leader_name = '朋友说小妹 微信号:pyshuo2015';
            send_tuan_tip_msg($openId,$msgData['title'],$msgData['productName'],$leader_name,$msgData['remark'],$msgData['detail_url']);
        }
        return array('success' => true);
    }

    private function prepareMsgData($data_id, $data_type){
        if($data_type==PRODUCT_TUAN_TYPE){
            $product = $this->TuanProduct->find('first',array('conditions' => array('id' => $data_id)));
            $productName = $product['TuanProduct']['alias'];
            $title = '参加'.$productName.'的团购';
            $remark = '点击立即购买';
            $detail_url = product_link($product['TuanProduct']['product_id'],WX_HOST);
            $detail_url = $detail_url.'?_sl=wx_tpl';
            return array('productName' => $productName,'title' => $title,'remark' => $remark,'detail_url' => $detail_url);
        }

        if($data_type==PRODUCT_TRY_TYPE){
            $product = $this->ProductTry->find('first',array('conditions' => array('id' => $data_id)));
            $pid = $product['ProductTry']['product_id'];
            $tuanProduct = $this->TuanProduct->find('first',array('conditions' => array('product_id' => $pid)));
            $price = ($product['ProductTry']['price'])/100;
            $productName = $product['ProductTry']['product_name'].$product['ProductTry']['spec'];
            $title = '参加'.$tuanProduct['TuanProduct']['alias'].$price.'元秒杀的';
            $remark = '点击立即秒杀';
            $detail_url = WX_HOST;
            return array('productName' => $productName,'title' => $title,'remark' => $remark,'detail_url' => $detail_url);
        }
        return null;
    }

    private function loadTeams($users){
        $consigneeIds = Hash::extract($users, '{n}.cake_orders.consignee_id');
        $tuanTeams = $this->TuanTeam->find('all', array(
            'conditions' => array(
                'offline_store_id' => $consigneeIds
            ),
            'fields' => array(
                'leader_name', 'leader_weixin', 'tuan_name', 'offline_store_id'
            )
        ));
        $tuanTeams = Hash::combine($tuanTeams, '{n}.TuanTeam.offline_store_id', '{n}.TuanTeam');
        return $tuanTeams;
    }

}