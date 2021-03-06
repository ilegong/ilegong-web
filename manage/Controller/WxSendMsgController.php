<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 5/15/15
 * Time: 20:06
 */

class WxSendMsgController extends AppController{

    var $name = 'WxSendMsg';

    var $uses = array('Order','Oauthbind','TuanTeam','TuanProduct','ProductTry','Weshare', 'WeshareAddress', 'User');

    public $components = array('Weixin');

    public function admin_to_send_wx_msg(){
        //$this->getZitiOrderUserIds();
    }


    public function admin_send_wx_msg_for_share($weshareId){
        $this->autoRender = false;
        $msg = '今天早上九点顶秀青溪西门取鸡蛋啦';
        $shareInfo = $this->Weshare->find('first',array(
            'conditions' => array(
                'id' => $weshareId
            )
        ));
        $share_id = $shareInfo['Weshare']['id'];
        $share_creator = $shareInfo['Weshare']['creator'];
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'type' => 9,
                'member_id' => $share_id,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED)
            ),
            'fields' => array(
                'id', 'consignee_name', 'consignee_address', 'creator'
            )
        ));
        $order_user_ids = Hash::extract($orders, '{n}.Order.creator');
        $order_user_ids[] = $share_creator;
        //$order_user_ids = array(633345);
        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $order_user_ids
            ),
            'fields' => array('id', 'nickname')
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        $userOauthBinds = $this->Oauthbind->find('all', array(
            'conditions' => array(
                'user_id' => $order_user_ids
            ),
            'fields' => array('user_id', 'oauth_openid')
        ));
        $userOauthBinds = Hash::combine($userOauthBinds, '{n}.Oauthbind.user_id','{n}.Oauthbind.oauth_openid');
        $desc = '感谢大家对'.$users[$share_creator]['nickname'].'的支持，分享快乐。';
        $detail_url = WX_HOST.'/weshares/view/'.$share_id;
        foreach($orders as $order){
            $order_id = $order['Order']['id'];
            $order_user_id = $order['Order']['creator'];
            $open_id = $userOauthBinds[$order_user_id];
            $order_user_name = $users[$order_user_id]['nickname'];
            $title = $order_user_name.'你好，'.$msg;
            $conginess_name = $order['Order']['consignee_name'];
            $conginess_address = $order['Order']['consignee_address'];
            $this->Weixin->send_share_product_arrival($open_id, $detail_url, $title, $order_id, $conginess_address, $conginess_name, $desc);
        }
        echo json_encode(array('result' => true));
        return;
    }

    public function admin_send_wx_msg_for_rice() {
        $this->autoRender = false;
        $query_sql = 'SELECT user_id,mobile_num FROM  cake_candidates WHERE id IN ( SELECT candidate_id FROM cake_candidate_events WHERE event_id=4) AND vote_num >=100 ORDER BY  vote_num DESC LIMIT 16,100';
        $query_data = $this->Order->query($query_sql);
        $user_ids = Hash::extract($query_data, '{n}.cake_candidates.user_id');
        //$user_ids = array(633345,544307);
        $openIds = $this->Oauthbind->find('all', array(
            'conditions' => array(
                'user_id' => $user_ids,
            ),
            'fields' => array(
                'oauth_openid', 'user_id'
            )
        ));
        $openIds = Hash::combine($openIds, '{n}.Oauthbind.user_id', '{n}.Oauthbind.oauth_openid');
        $detail_url = WX_HOST . '/tuan_buyings/detail/2869';
        foreach ($openIds as $uid => $openId) {
            $this->log('send rice prize open id '.$openId);
            send_rice_prize_msg($openId, $detail_url);
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