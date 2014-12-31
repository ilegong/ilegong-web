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
        if($this->currentUser['id']){
            $this->loadModel('User');
            $user_info=$this->User->find('first', array(
                'conditions' => array('id' => $this->currentUser['id']),
                'fields' => array('mobilephone')
            ));
            if(empty($user_info['User']['mobilephone'])){
                $this->redirect('/users/mobile_bind?referer='.urlencode($_SERVER['REQUEST_URI']));
            }
        }
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
       $team_slug =  $this->data['team']  ? $this->data['team']  :  $_GET['team'];

        if($this->request->is('post')){
            $current_uid = $this->currentUser['id'];
            $team = $this->Team->find('first', array(
                'conditions' => array('slug' => trim($this->data['team'])),
                //'fields' => array('id')
            ));
            if(empty($team) || $team['Team']['begin_time']> time() || $team['Team']['end_time']< time()){
                $this->Session->setFlash(__('团购项目不存在'));
                $this->redirect('/');
            }else{
                $this->loadModel('GrouponMember');
                if($this->GrouponMember->hasAny(array('user_id' => $current_uid, 'status' => STATUS_GROUP__PAID, 'team_id' => $team['Team']['id'] ))){
                    $this->Session->setFlash(__('您已经参加过该商品的一次团了'));
                    $this->redirect('/view/'. $this->data['team']);
                }else{
                    $info = array();
                    $info['Groupon']['name'] = $this->data['Groupon']['name'];
                    $info['Groupon']['mobile'] = $this->data['Groupon']['mobile'];
                    $info['Groupon']['address'] = $this->data['Groupon']['address'];
                    $info['Groupon']['team_id'] = $team['Team']['id'];
                    $info['Groupon']['user_id'] = $current_uid;

                    if($this->Groupon->save($info)){
                        $this->Session->setFlash(__('组团成功'));
                        $this->redirect('/groupons/join');
                    }else{
                        $this->Session->setFlash(__('提交失败'));
                        $this->redirect('/view/'. $this->data['team']);
                    }
                }

            }
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
            $this->redirect('/wx_pay/group_pay/' . $member['GrouponMember']['id']);
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
            $groupon = $this->GrouponMember->find_by_uid_and_groupon_id($groupId, $uid);
            $this->set('member', $groupon);
        };

        $this->set('team', $team);
        $this->set('groupon', $groupon);
    }

    public function test() {}


}