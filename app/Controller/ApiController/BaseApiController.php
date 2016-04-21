<?php

class BaseApiController extends Controller
{
    public function beforeFilter()
    {
        $allow_action = array('test', 'check_mobile_available');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }
}