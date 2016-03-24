<?php

class MessageApiController extends AppController
{

    public $components = array('OAuth.OAuth', 'Session');

    public function beforeFilter()
    {
        $allow_action = [];
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }




}