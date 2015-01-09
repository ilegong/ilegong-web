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
        if (empty($this->currentUser['id'])) {
            $ref = Router::url($_SERVER['REQUEST_URI']);
            if ($this->is_weixin()) {
                $this->redirect(redirect_to_wx_oauth($ref, WX_OAUTH_BASE, true));
            } else {
                $this->redirect('/users/login.html?referer='.$ref);
            }
        }
        if (array_search($this->request->params['action'], array('mobile_bind','view', 'join')) === false){
            $this->loadModel('User');
            $user_info=$this->User->find('first', array(
                'conditions' => array('id' => $this->currentUser['id']),
                'fields' => array('mobilephone')
            ));
            if(empty($user_info['User']['mobilephone'])){
                $this->redirect('/groupons/mobile_bind?referer='.urlencode($_SERVER['REQUEST_URI']));
            }
        }
        $this->pageTitle = '组团一起吃，只要一块钱';
    }
    public function view($slug = null){
        if(empty($slug)){
            $team = $this->Team->find('first', array(
                'order' => 'id asc'
            ));
        }else{
            $team = $this->Team->find('first', array(
                'conditions' => array('slug' => $slug)
            ));
        }
        $uid = $this->currentUser['id'];

        if(empty($team) || $team['Team']['begin_time']> time() || $team['Team']['end_time']< time()){
            $this->Session->setFlash(__('团购项目不存在'));
            $this->redirect('/');
        }else{
            $this->set('team', $team);
            $this->loadModel('Groupon');
            $groupon = $this->Groupon->find('first', array(
                'conditions' =>array('user_id' => $uid, 'team_id' => $team['Team']['id']),
                'fields' => array('id')
            ));
            if($groupon){
                $this->redirect('/groupons/join/'.$groupon['Groupon']['id']);
            }

            $refUid = $_GET['fromid'];
            if (!empty($_GET['for']) && !empty($refUid) && $refUid != $uid ) {
                $this->loadModel('User');
                $nnMap = $this->User->findNicknamesMap(array($refUid));
                $refName = filter_invalid_name($nnMap[$refUid]);
                $this->set('show_found_new', $_GET['for']);
                $this->set('refer_name', $refName);
            }

            $this->loadModel('GrouponMember');
            $grouponMember = $this->GrouponMember->find('first', array(
                'conditions' =>array('user_id' => $uid, 'team_id' => $team['Team']['id'], 'status' => STATUS_GROUP_MEM_PAID),
                'fields' => array('id', 'groupon_id')
            ));
            if($grouponMember){
                $this->redirect('/groupons/join/'.$grouponMember['GrouponMember']['groupon_id']);
            }
        }

    }
    public function organizing(){
        $team_slug =   $this->data['team']  ?  $this->data['team']  :  $_GET['team'];
        $team = $this->Team->find('first', array(
            'conditions' => array('slug' => trim($team_slug)),
            'fields' => array('id','title','begin_time', 'end_time')
        ));
        if($this->request->is('post')){
            $this->autoRender=false;
            $current_uid = $this->currentUser['id'];
            if(empty($team) || $team['Team']['begin_time']> time() || $team['Team']['end_time']< time()){
                $res = array('success'=> false, 'msg'=>'团购项目不存在');
            }else{
                $count = $this->Groupon->find('count',array('conditions'=>array('user_id' => $current_uid)));
                if($count >= 1){
                    $res = array('success'=> false, 'msg'=>'您已经发起过该商品的一次团了');
                }else{
                    $info = array();
                    $info['Groupon']['name'] = $_POST['name'];
                    $info['Groupon']['mobile'] = $_POST['mobile'];
                    $info['Groupon']['address'] = $_POST['address'];
                    $info['Groupon']['team_id'] = $team['Team']['id'];
                    $info['Groupon']['province_id'] = intval($_POST['province_id']);
                    $info['Groupon']['city_id'] = intval($_POST['city_id']);
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
        $this->set('team', $team);
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
            $this->redirect('/wxPay/jsApiPay/0?action=group_pay&memberId=' . $member['GrouponMember']['id'] . '&type='.$_REQUEST['type']);
//            $this->redirect('/wxPay/group_pay/' . $member['GrouponMember']['id']);
        }
    }

    public function my_join($member_id) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (!empty($uid)) {
            $groupon_member = $this->GrouponMember->findById($member_id);
            $this->redirect('/groupons/join/'.$groupon_member['GrouponMember']['groupon_id']);
        }else {
            $this->redirect('/groupons/view');
        }
    }

    public function join($groupId, $for = '', $fromId = ''){

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
        $this->loadModel('GrouponMember');
        $join_users = $this->GrouponMember->find('list', array(
            'conditions' => array('groupon_id' => $groupId, 'status' => STATUS_GROUP_MEM_PAID),
            'fields' => array('user_id','created')
        ));
        $join_ids = array_keys($join_users);
        if(!empty($join_ids)){

            $this->loadModel('User');
//            $nicknames = $this->User->find('list', array(
//                'conditions' => array('id' => $join_ids),
//                'fields' => array('id','nickname')
//            ));
            $nicknames = $this->User->findNicknamesMap($join_ids);
            $join_info = array();
            $k = 0;
            foreach($join_users as $key=>$value){
                $join_info[$k] = array('nickname' => $nicknames[$key], 'time' =>$value);
                $k++;
            }
        }

        $balance = $this->calculate_balance($groupId, $team, $groupon);
        $this->set('balance', $balance);

        if($uid === $groupon['Groupon']['user_id']){
            $this->set('is_organizer', true);
            $is_paid = $this->Groupon->is_all_paid($groupId, $team, $groupon);
            if ($is_paid) {
                $this->Groupon->set_paid_done($groupId);
                //reloading
                $groupon = $this->Groupon->findById($groupId);
            }
            $my_join_id =$this->GrouponMember->find('first', array(
                'conditions'=>array('user_id'=> $uid, 'groupon_id !=' =>$groupId),
                'fields' => array('groupon_id')
            ));
            $this->set('my_join_id',$my_join_id);
            $this->set('closed', $groupon['Groupon']['status'] == STATUS_GROUP_REACHED);
        } else {
            $foundGroupon = $this->Groupon->find('first', array(
                'conditions' => array('user_id' => $uid)
            ));
            $joined = $this->GrouponMember->find('all', array(
                'conditions' => array('status' => STATUS_GROUP_MEM_PAID, 'user_id' => $uid, 'groupon_id !=' => $foundGroupon['Groupon']['id']),
            ));

            foreach($joined as $j) {
                if ($j['GrouponMember']['groupon_id'] == $groupId) {
                    $this->set('attend', true);
                    break;
                }
            }

            if(empty($foundGroupon) && !empty($for) && $fromId != $uid) {
                $this->redirect(array('action' => 'view', $team['Team']['slug'], '?' => array('for' => $for, 'fromid' => $fromId, )));
                exit();
            }

            $will_closed = $balance <= $team['Team']['unit_val'];
            $joinedCnt = count($joined);
            if ($joinedCnt >= 1) {
                $this->set('joined_exceed', true);
            }

            $this->set('share_alert_tuhao',  $for == "tuhao" && $uid == $fromId);
            $this->set('share_alert_leader',  $for == "leader" && $uid == $fromId);
            $this->set('has_organized', !empty($foundGroupon));
            $this->set('closed', $will_closed);
        }


        $this->loadModel('Product');
        $product = $this->Product->find('first', array(
            'conditions' => array('id' => $team['Team']['product_id']),
            'fields' => 'id, name, coverimg, price, created, slug'
        ));
        $this->set('product', $product);

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

    private function calculate_balance($groupId, $team = null, $groupon = null){
        $this->loadModel('Groupon');
        return $this->Groupon->calculate_balance($groupId, $team, $groupon);
    }

}