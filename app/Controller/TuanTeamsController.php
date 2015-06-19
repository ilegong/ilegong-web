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
        $area_id = $_REQUEST['area_id'];
        $current_user_id = $this->currentUser['id'];
        $this->loadModel('TuanMember');
        //user is login
        if(!empty($current_user_id)){
            $my_tuans = $this->TuanMember->find('all',array(
                'conditions' => array(
                    'uid' => $current_user_id
                ),
                'order' => 'join_time DESC'
            ));
            $my_tuan_ids = Hash::extract($my_tuans,'{n}.TuanMember.tuan_id');
        }
        $tuan_teams = $this->TuanTeam->find('all', array(
            'conditions' =>array('published'=> PUBLISH_YES, 'type' => 0),
            'order' => array('TuanTeam.priority DESC')
        ));
        $left_tuan_ids = Hash::extract($tuan_teams,'{n}.TuanTeam.id');
        if(!empty($my_tuan_ids)){
            $this->set('my_tuan_ids',$my_tuan_ids);
            $left_tuan_ids = array_diff($left_tuan_ids,$my_tuan_ids);
        }

        $offline_store_ids =  Hash::extract($tuan_teams,'{n}.TuanTeam.offline_store_id');
        $this->loadModel('OfflineStore');
        $offline_store = $this->OfflineStore->find('all',array('conditions' => array('id' => $offline_store_ids,'deleted' => DELETED_NO)));
        $offline_store = Hash::combine($offline_store,'{n}.OfflineStore.id','{n}.OfflineStore');
        $tuan_teams = Hash::combine($tuan_teams,'{n}.TuanTeam.id','{n}.TuanTeam');
        foreach ($tuan_teams as &$tuan_team){
            $offline_store_id = $tuan_team['offline_store_id'];
            $tuan_team['offline_store_location_long'] = $offline_store[$offline_store_id]['location_long'];
            $tuan_team['offline_store_location_lat'] =$offline_store[$offline_store_id]['location_lat'];
            $tuan_team['offline_store_name'] = $offline_store[$offline_store_id]['name'];
        }
        unset($tuan_team);
        $this->log('tuan_team'.json_encode($tuan_teams[1]));
        $this->set('left_tuan_ids',$left_tuan_ids);
        $this->set('tuan_teams',$tuan_teams);
        $this->set('op_cate','mei_shi_tuan');
        if(!empty($area_id)){
            $this->set('area_id',$area_id);
        }
    }

    public function info($tuan_id){

        if (empty($this->currentUser) && $this->is_weixin() && !in_array($this->request->params['controller'], array('users', 'check'))) {
            $this->redirect($this->login_link());
        }

        $tuan_team = $this->TuanTeam->find('first', array(
            'conditions' =>array(
                'id'=> $tuan_id,
                //'published' => PUBLISH_YES
            ),
        ));
        if(empty($tuan_team)){
            $message = '该团不存在';
            $url = '/tuan_teams/mei_shi_tuan';
            $this->__message($message, $url);
            return;
        }
        $this->pageTitle = $tuan_team['TuanTeam']['tuan_name'];
        $this->loadModel('TuanBuying');

        $this->loadModel('OfflineStore');
        $offline_store = $this->OfflineStore->findById($tuan_team['TuanTeam']['offline_store_id']);

        //get buying tuan
        $tuan_buyings = $this->TuanBuying->find('all', array(
            'conditions' => array('tuan_id' => $tuan_id,'status'=>0, 'published'=>1),
            'order' => array('TuanBuying.end_time DESC'),
        ));
        $pids = array_unique(Hash::extract($tuan_buyings, '{n}.TuanBuying.pid'));

        $this->loadModel('Product');
        if(!empty($pids)){
            $this->loadModel('TuanProduct');
            $tuan_product_infos = $this->TuanProduct->find('all',array(
                'conditions' => array(
                    'product_id' => $pids
                ),
                'order' => 'priority desc'
            ));
            $product_infos = $this->Product->find('all', array(
                'conditions' => array('id' => $pids),
                'fields' => array('id', 'name', 'coverimg', 'price', 'original_price')
            ));

            $product_infos = Hash::combine($product_infos, '{n}.Product.id', '{n}.Product');
            $tuan_product_infos = Hash::combine($tuan_product_infos,'{n}.TuanProduct.product_id','{n}.TuanProduct');
            $this->set('tuan_product_infos',$tuan_product_infos);
            $this->set('product_infos', $product_infos);
            $tb_product_map = Hash::combine($tuan_buyings,'{n}.TuanBuying.pid','{n}.TuanBuying');
            $this->set('tb_product_map',$tb_product_map);
        }else{
            $this->set('no_tuan_buy', true);
        }
        //add sec kill
        $this->loadModel('ProductTry');
        $ProductTuanTryM = ClassRegistry::init('ProductTuanTry');
        $tryings = $this->ProductTry->find_trying();
        if (!empty($tryings)) {
            $trying_result = array();
            $try_pids = Hash::extract($tryings, '{n}.ProductTry.product_id');
            $tryProducts = $this->Product->find_products_by_ids($try_pids, array(), false);
            if (!empty($tryProducts)) {
                foreach($tryings as &$trying) {
                    $try_id = $trying['ProductTry']['id'];
                    $global_show = $trying['ProductTry']['global_show'];
                    $pid = $trying['ProductTry']['product_id'];
                    $prod = $tryProducts[$pid];
                    if (!empty($prod)) {
                        $trying['Product'] = $prod;
                        if($global_show== '0'){
                            $product_tuan_try = $ProductTuanTryM->find('all',array('conditions' => array('try_id' => $try_id)));
                            $product_tuan_tryIds = Hash::extract($product_tuan_try,'{n}.ProductTuanTry.team_id');
                            if(!empty($product_tuan_tryIds)&&!in_array($tuan_id,$product_tuan_tryIds)){
                                continue;
                            }
                        }
                        $trying_result[] = $trying;
                    } else {
                        unset($trying);
                    }
                }
            }
            $tryings = $trying_result;
        }

        if($this->is_weixin()){
            $currUid = empty($this->currentUser) ? 0 : $this->currentUser['id'];
            $weixinJs = prepare_wx_share_log($currUid, 'tid', $tuan_id);
            $this->set($weixinJs);
            $this->set('jWeixinOn', true);
        }

        $current_user_id = $this->currentUser['id'];
        $this->loadModel('TuanMember');
        //user is login
        if(!empty($current_user_id)){
            $is_join = $this->TuanMember->find('all',array(
                'conditions' => array(
                    'uid' => $current_user_id,
                    'tuan_id' => $tuan_id
                )
            ));
        }
        $this->set('is_join',$is_join);
        $this->set('tryings',$tryings);
        $this->set('tuan_id', $tuan_id);
        $this->set('tuan_team', $tuan_team);
        $this->set('offline_store',$offline_store);
        $this->set('tuan_address', get_address($tuan_team, $offline_store));
        $this->set('tuan_buyings', $tuan_buyings);
        $this->set('hideNav',true);
    }

    public function join(){
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $tuan_id = $_POST['tuan_id'];
        if($tuan_id){
            if(empty($uid)){
                $is_oauth = false;
            }else{
                $is_oauth = true;
            }
            if($is_oauth){
                $this->loadModel('TuanMember');
                $is_member = $this->TuanMember->hasAny(array('uid' => $uid, 'tuan_id' => $tuan_id));
                if(!$is_member){
                    $data['tuan_id'] =  $tuan_id;
                    $data['uid'] = $uid;
                    $data['join_time'] = date('Y-m-d H:i:s');
                    if($this->TuanMember->save($data)){
                        $this->TuanTeam->update(array('member_num' => 'member_num + 1'), array('id' => $tuan_id));
                    }
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
            $teamInfo = $this->TuanTeam->find('first',array('conditions' => array('id' => $tuan_id, 'published' => PUBLISH_YES)));
            $this->loadModel('OfflineStore');
            $offline_store = $this->OfflineStore->find('first',array(
                'conditions' => array('id' => $teamInfo['TuanTeam']['offline_store_id'])
            ));
//            $location = $teamInfo['TuanTeam']['location_long'] . ',' . $teamInfo['TuanTeam']['location_lat'];
            $location = $offline_store['OfflineStore']['location_long'] . ',' . $offline_store['OfflineStore']['location_lat'];
            $this->set('tuan_id', $tuan_id);
            $this->set('name', $teamInfo['TuanTeam']['tuan_name']);
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
        $this->set('tuan_id',$tuan_id);
        $this->set('member_info', $member_info);
        $this->set('hideNav',true);
    }


    public function api_getArea(){
        $this->autoRender = false;
        $this->loadModel('Location');
        $areaId = $this->Location->find('all',array(
           'conditions' => array(
               'parent_id' => 110100
           ),
        ));
        $team_count_area_map = $this->Location->query('select county_id, count(*) t_count from cake_tuan_teams group by county_id');
        $count_result = array();
        foreach($team_count_area_map as $ta_map){
            $count_result[] = array('area_id' => $ta_map['cake_tuan_teams']['county_id'],'count'=>$ta_map['0']['t_count']);
        }
        $count_result = Set::sort($count_result,'{n}.count','DESC');
        $areaId = Hash::combine($areaId,'{n}.Location.id','{n}.Location');
        $json_result = array('areas' => $areaId,'count_result' => $count_result);
        echo json_encode($json_result);
    }


}

