<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 15/3/16
 * Time: 下午6:29
 */
class TuanTeamsController extends AppController{
    public function mei_shi_tuan(){
        $this->pageTitle = '美食团';
        $tuan_teams = $this->TuanTeam->find('all', array(
            'conditions' =>array('status'=> 0),
            'order' => array('TuanTeam.priority DESC')
        ));
        $this->set('tuan_teams',$tuan_teams);
        $this->set('op_cate','mei_shi_tuan');
    }
    public function info($tuan_id){
        $tuan_team = $this->TuanTeam->find('first', array(
            'conditions' =>array('id'=> $tuan_id),
        ));
        if(empty($tuan_team)){
            $message = '该团不存在';
            $url = '/tuan_teams/mei_shi_tuan';
            $this->__message($message, $url);
            return;
        }
        $this->loadModel('TuanBuying');
        $tuan_buyings = $this->TuanBuying->find('all', array(
            'conditions' => array('tuan_id' => $tuan_id),
            'order' => array('TuanBuying.end_time DESC'),
        ));
        $pids = array_unique(Hash::extract($tuan_buyings, '{n}.TuanBuying.pid'));
        if(!empty($pids)){
            $this->loadModel('Product');
            $product_info = $this->Product->find('all', array(
                'conditions' => array('id' => $pids),
                'fields' => array('id',  'name', 'coverimg')
            ));
            $product_info = Hash::combine($product_info, '{n}.Product.id', '{n}.Product');
            $this->set('product_info', $product_info);
        }else{
            $this->set('no_tuan_buy', true);
        }
        if($this->is_weixin()){
            $currUid = empty($this->currentUser) ? 0 : $this->currentUser['id'];
            $this->prepare_wx_sharing($currUid, $tuan_id);
        }
        $referer = Router::url($_SERVER['REQUEST_URI']);
        if($_GET['has_joined'] == 'success'){
            $uid = $this->currentUser['id'];
            $this->loadModel('TuanMember');
            $is_member = $this->TuanMember->hasAny(array('uid' => $uid, 'tuan_id' => $tuan_id));
            if(!$is_member){
                $data['tuan_id'] =  $tuan_id;
                $data['uid'] = $uid;
                $data['join_time'] = date('Y-m-d H:i:s');
                $this->TuanMember->save($data);
            }
            $this->set('new_join', true);
        }
        if($this->currentUser['id']){
            $this->loadModel('TuanMember');
            $has_joined = $this->TuanMember->hasAny(array('tuan_id' => $tuan_id, 'uid' => $this->currentUser['id']));
            $this->set('has_joined', $has_joined);
        }
        $this->pageTitle = $tuan_team['TuanTeam']['tuan_name'];
        $this->set('tuan_id', $tuan_id);
        $this->set('tuan_team', $tuan_team);
        $this->set('tuan_buyings', $tuan_buyings);
        $this->set('hideNav',true);
        $this->set('referer', $referer);
    }
    protected function prepare_wx_sharing($currUid, $tid) {
        $currUid = empty($currUid) ? 0 : $currUid;
        $share_string = $currUid . '-' . time() . '-rebate-tid_' . $tid;
        $share_code = authcode($share_string, 'ENCODE', 'SHARE_TID');
        $oauthM = ClassRegistry::init('WxOauth');
        $signPackage = $oauthM->getSignPackage();
        $this->set('signPackage', $signPackage);
        $this->set('share_string', urlencode($share_code));
        $this->set('jWeixinOn', true);
    }

    public function join(){
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $tuan_id = $_POST['tuan_id'];
        if($tuan_id){
            if(empty($uid)){
                $is_oauth = false;
            }else{
                $is_oauth = false;
//                $this->loadModel('Oauthbinds');
//                $is_oauth = $this->Oauthbinds->hasAny(array('user_id' => $uid));
            }
            if($is_oauth){
                $this->loadModel('TuanMember');
                $is_member = $this->TuanMember->hanAny(array('uid' => $uid, 'tuan_id' => $tuan_id));
                if(!$is_member){
                    $data['tuan_id'] =  $tuan_id;
                    $data['uid'] = $uid;
                    $data['join_time'] = date('Y-m-d H:i:s');
                    $this->TuanMember->save($data);
                }
                $res = array('success'=> true);

            }else{
                $res = array('success'=> false, 'type' => 'not_login' );
            }
        }else{
            $res = array('success'=> false, 'type' =>  'error');
        }
        echo json_encode($res);
    }

    public function lbs_map($tuan_id= null){
        $this->pageTitle =__('自取点');
        if(empty($tuan_id)){
            $location = $_GET['location'];
            $name = $_GET['name'];
            $addr = $_GET['addr'];
            $this->set(compact('location', 'name', 'addr'));
        }else{
            $tuan_id = intval($tuan_id);
            $teamInfo = $this->TuanTeam->find('first',array('conditions' => array('id' => $tuan_id)));
            $location = $teamInfo['TuanTeam']['location_long'] . ',' . $teamInfo['TuanTeam']['location_lat'];
            $this->set('tuan_id',$tuan_id);
            $this->set('name',$teamInfo['TuanTeam']['tuan_name']);
            $this->set('location', $location);
            $this->set('addr',$teamInfo['TuanTeam']['tuan_addr']);
        }
        $this->set('hideNav',true);
    }

    public function create(){
        $this->pageTitle = '创建新团';
    }
    public function memberlist($tuan_id){
        $this->pageTitle = '美食团成员';
        $this->loadModel('TuanMember');
        $uids = $this->TuanMember->find('list', array(
            'conditions' => array('tuan_id' => $tuan_id),
            'fields' => array('uid')
        ));
        $leader = $this->TuanTeam->find('first', array(
            'conditions' => array('id' => $tuan_id),
            'fields' => array('leader_id')
        ));
        if($leader){
            $uids[] = $leader['TuanTeam']['leader_id'];
        }
        $this->loadModel('User');
        $member_info = $this->User->find('all', array(
            'conditions' => array('id' =>$uids),
            'fields' => array('nickname', 'image')
        ));
        $this->set('member_info', $member_info);
        $this->set('hideNav',true);
    }


}

