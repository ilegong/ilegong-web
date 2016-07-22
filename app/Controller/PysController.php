<?php

class PysController extends AppController{


    /**
     * pc首页
     */
    public function index(){
        if($this->RequestHandler->isMobile()){
            $this->redirect('/weshares/index');
        }
        $this->layout=null;
    }

    public function download_app(){
        $this->layout=null;
        add_logs_to_es(["index" => "event_view_banner", "type" => "ios_download"]);
    }

    public function invite_join_group($gid){
        $this->layout = null;
        $this->loadModel('ChatGroup');
        $group = $this->ChatGroup->find('first', [
            'conditions' => [
                'ChatGroup.id' => $gid,
            ],
            'joins' => [
                [
                    'table' => 'cake_users',
                    'alias' => 'User',
                    'type' => 'left',
                    'conditions' => 'User.id = ChatGroup.creator'
                ]
            ],
            'fields' => ['ChatGroup.*', 'User.nickname']
        ]);
        $this->set('group', $group);
        add_logs_to_es(["index" => "event_chat_group", "type" => "group_invite", 'group' => $group['Group']]);
    }

}