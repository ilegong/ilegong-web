<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 4/9/15
 * Time: 11:55
 */
class TuanTeamsController extends AppController{

    var $name = 'TuanTeams';

    var $uses = array('TuanTeam');


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
            ));}else{
            $tuan_teams = $this->TuanTeam->find('all');
        }
        $this->set('tuan_teams',$tuan_teams);
        $this->set('team_id',$team_id);
    }
    /**
     * create a new tuan_team
     */
    public function admin_tuan_team_create(){

        if(!empty($this->data)){
            if($this->TuanTeam->save($this->data)){
                $this->redirect(array('controller' => 'tuanTeams','action' => 'admin_tuan_teams'));
            }
        }
    }

    /**
     * edit a tuan_team
     */
    public function admin_tuan_team_edit($id){

        $data_Info = $this->TuanTeam->find('first',array('conditions' => array('id' => $id)));
        $this->log('data_info'.json_encode($data_Info));
        if (empty($data_Info)) {
            throw new ForbiddenException(__('该团队不存在！'));
        }
        if(!empty($this->data)){
            $this->data['TuanTeam']['id'] = $id;
            $this->autoRender = false;
            if($this->TuanTeam->save($this->data)){
                $this->redirect(array('controller' => 'tuanTeams','action' => 'admin_tuan_teams'));
            }
        }else{
            $this->data = $data_Info;
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