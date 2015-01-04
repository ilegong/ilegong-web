<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 14/12/24
 * Time: 下午4:48
 */

class GrouponsController extends AppController{
    var $name = 'Groupon';

    var $uses = array('Team', 'GrouponMember', 'Groupon');

    public function beforeFilter(){
        parent::beforeFilter();
        if (array_search($this->request->params['action'], array('view', 'mobile_bind','join', 'pay_choices')) === false) {
            if($this->currentUser['id']){
                $this->loadModel('User');
                $user_info=$this->User->find('first', array(
                    'conditions' => array('id' => $this->currentUser['id']),
                    'fields' => array('mobilephone')
                ));
                if(empty($user_info['User']['mobilephone'])){
                    $this->redirect('/groupons/mobile_bind?referer='.urlencode($_SERVER['REQUEST_URI']));
                }
            }else{
                $this->redirect('/users/login?referer='.urlencode($_SERVER['REQUEST_URI']));
            }
        }
        $this->pageTitle = '团购杀价';
        $this->set('hideNav', true);
    }
    public function view($slug){
        if($slug){
            $team = $this->Team->find('first', array(
                'conditions' => array('slug' => $slug)
            ));
            if(empty($team) || $team['Team']['begin_time']> time() || $team['Team']['end_time']< time()){
                $this->Session->setFlash(__('团购项目不存在'));
                $this->redirect('/');
            }else{
                $this->set('team', $team['Team']);
            }
        }
    }
    public function organizing(){
        $team_slug =  $_POST['team']  ? $_POST['team']  :  $_GET['team'];

        if($this->request->is('post')){
            $this->autoRender=false;
            $current_uid = $this->currentUser['id'];
            $team = $this->Team->find('first', array(
                'conditions' => array('slug' => trim($this->data['team'])),
                //'fields' => array('id')
            ));
            if(empty($team) || $team['Team']['begin_time']> time() || $team['Team']['end_time']< time()){
                $res = array('success'=> false, 'msg'=>'团购项目不存在');
            }else{
                $this->loadModel('GrouponMember');
                if($this->GrouponMember->hasAny(array('user_id' => $current_uid, 'status' => STATUS_GROUP__PAID, 'team_id' => $team['Team']['id'] ))){
                    $res = array('success'=> false, 'msg'=>'您已经参加过该商品的一次团了');
                }else{
                    $info = array();
                    $info['Groupon']['name'] = $_POST['name'];
                    $info['Groupon']['mobile'] = $_POST['mobile'];
                    $info['Groupon']['address'] = $_POST['address'];
                    $info['Groupon']['team_id'] = $team['Team']['id'];
                    $info['Groupon']['user_id'] = $current_uid;

                    if($this->Groupon->save($info)){
                        $group_id = $this->Groupon->getLastInsertID();
                        $res = array('success'=> true, 'group_id'=>$group_id);
                    }else{
                        $res = array('success'=> false, 'msg'=>'保存失败，请重试');
                    }
                }
            }
            echo json_encode($res);
        }
        $this->data['team'] = $team_slug;

    }

    public function go_join($groupId) {
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            $this->redirect('/users/login?referer=' . urlencode($_SERVER['REQUEST_URI']));
            return;
        } else {
            $groupon = $this->Groupon->findById($groupId);
            if (empty($groupId)) {
                throw new NotFoundException();
            }

            $member = $this->GrouponMember->find_by_uid_and_groupon_id($groupId, $uid);
            if (empty($member)) {
                $member = $this->GrouponMember->save(array(
                    'groupon_id' => $groupId,
                    'user_id' => $uid,
                    'team_id' => $groupon['Groupon']['team_id'],
                ));
            }
            $this->redirect('/wxPay/jsApiPay/0?action=group_pay&memberId=' . $member['GrouponMember']['id']);
//            $this->redirect('/wxPay/group_pay/' . $member['GrouponMember']['id']);
        }
    }

    public function join($groupId){

        $groupon = $this->Groupon->findById($groupId);
        if (empty($groupon)) {
            $this->log("not found groupon for id:". $groupId);
            throw new NotFoundException();
        }

        $team_id = $groupon['Groupon']['team_id'];
        $team = $this->Team->findById($team_id);
        if (empty($team)) {
            $this->log("not found team for groupon:".$groupId." with team_id=".$team_id);
            throw new NotFoundException();
        }

        $uid = $this->currentUser['id'];
        if (!empty($uid)) {
            $groupon_member = $this->GrouponMember->find_by_uid_and_groupon_id($groupId, $uid);
            $this->set('member', $groupon_member);
        };
        $this->loadModel('GrouponMember');
        $join_users = $this->GrouponMember->find('list', array(
            'conditions' => array('groupon_id' => $groupId, 'status' => STATUS_GROUP__PAID),
            'fields' => array('user_id','created')
        ));
        $join_ids = array_keys($join_users);
        if(!empty($join_ids)){
            $this->loadModel('User');
            $nicknames = $this->User->find('list', array(
                'conditions' => array('id' => $join_ids),
                'fields' => array('id','nickname')
            ));
            $join_info = array();
            $k = 0;
            foreach($join_users as $key=>$value){
                $join_info[$k] = array('nickname' => $nicknames[$key], 'time' =>$value);
                $k++;
            }
        }

        if($uid === $groupon['Groupon']['user_id']){
            $this->set('is_organizer', true);
        }
        $this->set('team', $team);
        $this->set('groupon', $groupon);
        $this->set('join_info', $join_info);
    }

    public function test() {}

    public function mobile_bind(){
        $this->pageTitle = __('手机号绑定');
        $redirect = $_REQUEST['referer'];
        if(empty($this->currentUser['id'])){
            $this->redirect('/users/login?referer='.urlencode($_SERVER['REQUEST_URI']));
        }
        $this->data['referer'] = $redirect;
    }
    public function pay_choices($groupId){
        $groupon = $this->Groupon->findById($groupId);
        if (empty($groupon)) {
            $this->log("not found groupon for id:". $groupId);
            throw new NotFoundException();
        }
        $uid = $this->currentUser['id'];
        if($uid === $groupon['Groupon']['user_id']){
            $this->set('is_organizer', true);
        }
        $this->set('groupon_id', $groupId);
    }

}