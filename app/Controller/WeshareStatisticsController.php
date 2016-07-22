<?php

class WeshareStatisticsController extends AppController{


    var $uses = ['WxShare', 'ShareTrackLog', 'Weshare'];

    static $SHARE_TYPE_WESHARE = 'wsid';

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    public function shareReadForwardSummary()
    {
        $uid = $this->currentUser['id'];
        $forwardCount = $this->WxShare->find('count', [
            'conditions' => [
                'WxShare.data_type' => self::$SHARE_TYPE_WESHARE,
                'Weshare.creator' => $uid,
                'Weshare.status' => WESHARE_STATUS_NORMAL
            ],
            'joins' => [
                [
                    'table' => 'cake_weshares',
                    'alias' => 'Weshare',
                    'conditions' => 'WxShare.data_id = Weshare.id',
                    'type' => 'inner'
                ]
            ]
        ]);
        $readCount = $this->ShareTrackLog->find('count', [
           'conditions' => [
               'conditions' => [
                   'ShareTrackLog.data_type' => self::$SHARE_TYPE_WESHARE,
                   
               ]
           ]
        ]);
    }

}