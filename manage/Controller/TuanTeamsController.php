<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 4/9/15
 * Time: 11:55
 */
class TuanTeamsController extends AppController{

    var $name = 'TuanTeams';

    var $uses = array('TuanTeam', 'TuanBuying', 'Location', 'TuanProduct', 'OfflineStore');


    /**
     * show all tuan teams
     */
    public function admin_index(){
        $team_id = $_REQUEST['team_id'];
        $con = array(
            'published' => PUBLISH_YES
        );
        if(!empty($team_id)&&$team_id!='-1'){
            $con['id']=$team_id;
        }
        if(!empty($con)){
            $tuan_teams = $this->TuanTeam->find('all',array(
                'conditions' => $con
            ));
        }else{
            $tuan_teams = $this->TuanTeam->find('all', array(
                'conditions' => $con
            ));
        }
        $tuan_buyings_count = $this->TuanBuying->query('select tuan_id as id, count(tuan_id) as c from cake_tuan_buyings WHERE STATUS IN ( 0, 1, 2 ) group by tuan_id;');
        $tuan_buyings_count = Hash::combine($tuan_buyings_count, '{n}.cake_tuan_buyings.id', '{n}.0.c');
        foreach($tuan_teams as &$tuan_team){
            $tuan_buying_count = $tuan_buyings_count[$tuan_team['TuanTeam']['id']];
            if(!isset($tuan_buying_count) || is_null($tuan_buying_count)){
                $tuan_buying_count = 0;
            }

            $tuan_team['TuanTeam']['tuan_buying_count'] = $tuan_buying_count;
        }

        $offline_stores = $this->OfflineStore->find('all',array(
            'conditions' => array(
                'deleted' => 0
            )
        ));
        $offline_stores = Hash::combine($offline_stores, '{n}.OfflineStore.id', '{n}');

        $this->set('tuan_teams', $tuan_teams);
        $this->set('offline_stores', $offline_stores);
        $this->set('team_id',$team_id);
    }

    public function admin_new(){
    }

    public function admin_create(){
        if($this->TuanTeam->save($this->data)){
            $this->redirect(array('controller' => 'tuan_teams','action' => 'index'));
        }
    }

    public function admin_edit($id){
        $data_Info = $this->TuanTeam->find('first',array('conditions' => array('id' => $id)));
        $this->log('data_info'.json_encode($data_Info));
        if (empty($data_Info)) {
            throw new ForbiddenException(__('该团队不存在！'));
        }
        $this->data = $data_Info;
        $this->set('id',$id);
    }

    public function admin_update($id){
        $this->log('update tuan team '.$id.': '.json_encode($this->data));
        $this->autoRender = false;
        if($this->TuanTeam->save($this->data)){
            $this->redirect(array('controller' => 'tuan_teams','action' => 'index'));
        }
        $this->set('id',$id);
    }

    public function admin_api_tuan_teams(){
        $this->autoRender=false;
        $tuan_teams = $this->TuanTeam->find('all');
        $tuan_teams = Hash::combine($tuan_teams, "{n}.TuanTeam.id", "{n}");

        $tuan_buyings = $this->TuanBuying->find('all');

        $product_ids = array_unique(Hash::extract($tuan_buyings, "{n}.TuanBuying.pid"));
        $tuan_products = $this->TuanProduct->find('all', array(
            'conditions' => array(
                'product_id' => $product_ids
            )
        ));
        $tuan_products = Hash::combine($tuan_products, "{n}.TuanProduct.product_id", "{n}.TuanProduct");

        foreach($tuan_buyings as &$tuan_buying){
            $tuan_buying['TuanProduct'] = $tuan_products[$tuan_buying['TuanBuying']['pid']];
            $team_id = $tuan_buying['TuanBuying']['tuan_id'];
            if(!empty($tuan_teams[$team_id])){
                $tuan_teams[$team_id]['TuanBuyings'][$tuan_buying['TuanBuying']['id']] = $tuan_buying;
            }
        }

        echo json_encode($tuan_teams);
    }

    public function admin_api_tuan_county(){
        $this->autoRender = false;
        $county_ids = $this->Location->find('all',array(
            'conditions' => array(
                'parent_id' => 110100
            )
        ));
        $county_ids = Hash::extract($county_ids,'{n}.Location');
        echo json_encode($county_ids);
    }
}