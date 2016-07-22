<?php

class WeshareStatisticsController extends AppController{


    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    public function shareReadForwardSummary()
    {
        $uid = $this->currentUser['id'];
    }

}