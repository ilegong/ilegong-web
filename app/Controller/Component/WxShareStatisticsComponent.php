<?php

class WxShareStatisticsComponent extends Component
{


    static $SHARE_TYPE_WESHARE = 'wsid';

    public function getWeshareSummary($uid)
    {
        $this->injectModel();
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
                'ShareTrackLog.data_type' => self::$SHARE_TYPE_WESHARE,
                'Weshare.creator' => $uid,
                'Weshare.status' => WESHARE_STATUS_NORMAL
            ],
            'joins' => [
                [
                    'table' => 'cake_weshares',
                    'alias' => 'Weshare',
                    'conditions' => 'ShareTrackLog.data_id = Weshare.id',
                    'type' => 'inner'
                ]
            ]
        ]);
        return [$forwardCount, $readCount];
    }


    private function injectModel()
    {
        $this->WxShare = ClassRegistry::init('WxShare');
        $this->Weshare = ClassRegistry::init('Weshare');
        $this->ShareTrackLog = ClassRegistry::init('ShareTrackLog');
    }

}