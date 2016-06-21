<?php

class BalanceApiController extends Controller
{

    public $components = array('OAuth.OAuth');

    public function beforeFilter()
    {
        $allow_action = array('test', 'order_export');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }

    

}