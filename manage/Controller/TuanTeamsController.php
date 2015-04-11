<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 4/9/15
 * Time: 11:55
 */
class TuanTeamsController extends AppController{

    var $name = 'TuanTeams';

    var $uses = array('TuanTeam', 'TuanBuying');


    /**
     * show all tuan teams
     */
    public function admin_index(){
        $team_id = $_REQUEST['team_id'];
        $con = array();
        if(!empty($team_id)&&$team_id!='-1'){
            $con['id']=$team_id;
        }
        $this->log('con'.json_encode($con));
        if(!empty($con)){
            $tuan_teams = $this->TuanTeam->find('all',array(
                'conditions' => $con
            ));
        }else{
            $tuan_teams = $this->TuanTeam->find('all');
        }
        $tuan_buyings_count = $this->TuanBuying->query('select tuan_id as id, count(tuan_id) as c from cake_tuan_buyings group by tuan_id;');
        $tuan_buyings_count = Hash::combine($tuan_buyings_count, '{n}.cake_tuan_buyings.id', '{n}.0.c');
        foreach($tuan_teams as &$tuan_team){
            $tuan_buying_count = $tuan_buyings_count[$tuan_team['TuanTeam']['id']];
            if(!isset($tuan_buying_count) || is_null($tuan_buying_count)){
                $tuan_buying_count = 0;
            }

            $tuan_team['TuanTeam']['tuan_buying_count'] = $tuan_buying_count;
        }

        $this->set('tuan_teams',$tuan_teams);
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
        $teams = $this->TuanTeam->find('all');
        $teams = Hash::extract($teams,'{n}.TuanTeam');
        echo json_encode($teams);
    }

}