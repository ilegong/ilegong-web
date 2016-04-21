<?php

class BaseApiController extends Controller
{
    var $currentUser = [];

    public function beforeFilter()
    {
        $allow_action = array('test', 'check_mobile_available');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }


    protected function get_post_raw_data()
    {
        $postStr = file_get_contents('php://input');
        $postDataArray = json_decode($postStr, true);
        return $postDataArray;
    }

}