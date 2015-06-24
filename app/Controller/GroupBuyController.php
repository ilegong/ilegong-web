<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/23/15
 * Time: 20:49
 */
class GroupBuyController extends AppController{

    var $name = 'GroupBuy';

    var $uses = array('Product', 'GroupBuy','Cart','GroupBuyRecord','User');

    public function my_group_buy($pid){
        $this->check_login();
        $uid = $this->currentUser['id'];
        $record = $this->GroupBuyRecord->find('first',array('conditions' => array(
            'user_id' => $uid,
            'product_id' => $pid,
            'is_paid' => 1,
            'deleted' => DELETED_NO
        )));
        if(!empty($record)){
            $this->redirect('/group_buy/to_group_buy_detail/'.$pid.'/'.$uid.'/'.$record['GroupBuyRecord']['group_buy_tag']);
            return;
        }
        $this->redirect('/group_buy/to_group_buy_detail/'.$pid);
    }

    public function to_group_buy_detail($pid,$uid=null,$group_buy_tag=null){
        $this->check_login();
        $groupBuyInfo = $this->GroupBuy->getGroupBuyProductInfo($pid);
        $productInfo = $this->Product->find('first',array(
            'conditions' => array(
                'id' => $pid
            ),
            'fields' => Product::NO_VISIBLE_SIMPLE_FIELDS
        ));
        $consignment_date = parent::format_consignment_date($groupBuyInfo['send_date']);
        $this->set('consignment_date',$consignment_date);
        $this->pageTitle=$groupBuyInfo['name'];
        if($uid&&$group_buy_tag){
            $myid = $this->currentUser['id'];
            $this->set('invite',$myid==$uid);
            $this->set('group_tag',$group_buy_tag);
            $this->load_group_member($pid,$group_buy_tag);
        }
        $this->set('group_buy_info',$groupBuyInfo);
        $this->set('product_info',$productInfo);
        $this->set('hideFooter',true);
        $this->set('hideNav',true);
    }

    public function group_buy($pid,$group_buy_tag=null){
        $this->check_login();
        //add cart
        $buyingCom = $this->Components->load('Buying');
        $groupBuyInfo = $this->GroupBuy->getGroupBuyProductInfo($pid);
        $send_date = $groupBuyInfo['send_date'];
        $uid = $this->currentUser['id'];
        $sessionId = $this->Session->id();
        $cartM = $this->Cart;
        $returnInfo = $buyingCom->check_and_add($cartM, 1, 0, $uid, 1, $pid, 0, $sessionId);
        if (!empty($returnInfo) && $returnInfo['success']) {
            $cart_id = $returnInfo['id'];
            if ($cart_id) {
                $cartM->updateAll(array('send_date' => "'".$send_date."'"), array('id' => $cart_id));
            }
        }else{
            $this->redirect('/group_buy/to_group_buy_detail/'.$pid);
        }
        if(empty($group_buy_tag)){
            $group_buy_tag = md5(uniqid(rand(), true));
        }
        //to make order
        $this->redirect('/orders/info/?from=group&pid_list='.$cart_id.'&group_tag='.$group_buy_tag);
    }

    private function check_login(){
        //auto login
        if (empty($this->currentUser) && $this->is_weixin() && !in_array($this->request->params['controller'], array('users', 'check'))) {
            $this->redirect($this->login_link());
        }
    }

    private function load_group_member($pid,$group_tag){
        $group_records = $this->GroupBuyRecord->find('all',array(
            'conditions' => array(
                'product_id' => $pid,
                'group_buy_tag' => $group_tag,
                'is_paid' => 1,
                'deleted' => DELETED_NO
            )
        ));
        $uids = Hash::extract($group_records,'{n}.GroupBuyRecord.user_id');
        $userInfos  = $this->User->find('all', array(
            'conditions' => array('id' => $uids),
            'recursive' => 1, //int
            'fields' => array('id','username','image','nickname'),
        ));
        $this->set('group_members',$userInfos);
    }
}