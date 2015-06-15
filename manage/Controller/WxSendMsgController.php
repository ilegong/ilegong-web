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
            $title = '参加'.$productName.'的';
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