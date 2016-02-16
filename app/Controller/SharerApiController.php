<?php

class SharerApiController extends AppController
{


    public $components = array('OAuth.OAuth', 'Session', 'WeshareBuy', 'ShareUtil');


    public function beforeFilter()
    {
        $allow_action = array();
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }

    public function get_my_shares(){
        $uid = $this->currentUser['id'];


    }

}