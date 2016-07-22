<?php

class WxShareStatisticsComponent extends Component
{


    static $SHARE_TYPE_WESHARE = 'wsid';
    static $LIMIT = 5;

    private function getSimpleWeshareData($weshareId){
        $weshare = $this->Weshare->find('first', [
            'conditions' => [
                'id' => $weshareId
            ],
            'fields' => ['Weshare.id', 'Weshare.title'],
        ]);
        return $weshare;
    }

    public function getWeshareForwardData($weshareId){
        $this->injectModel();
        $weshare = $this->getSimpleWeshareData($weshareId);
        $share_count = $this->WxShare->find('count', [
            'conditions' => [
                'data_type' => self::$SHARE_TYPE_WESHARE,
                'data_id' => $weshareId
            ]
        ]);

        return [$weshare, $share_count];

    }


    public function getWeshareReadData($weshareId){
        $this->injectModel();
        $weshare = $this->getSimpleWeshareData($weshareId);
        $read_count = $this->ShareTrackLog->find('count', [
            'conditions' => [
                'data_type' => self::$SHARE_TYPE_WESHARE,
                'data_id' => $weshareId
            ]
        ]);
        return [$weshare, $read_count];
    }

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

    public function getWeshareList($uid, $page)
    {
        $this->injectModel();

        $weshares = $this->Weshare->find('all', [
            'conditions' => [
                'creator' => $uid,
                'status' => WESHARE_STATUS_NORMAL,
            ],
            'limit' => self::$LIMIT,
            'page' => $page,
            'order' => ['Weshare.id DESC'],
            'fields' => ['Weshare.id', 'Weshare.title', 'Weshare.default_image', 'Weshare.creator','Weshare.created']
        ]);

        if ($weshares) {
            $weshare_id = [];
            foreach ($weshares as $weshare) {
                $weshare_id[] = $weshare['Weshare']['id'];
            }
            $weshare_id = implode($weshare_id, ',');

            $share_total_list = $this->WxShare->query("SELECT data_id ,count(1) AS total FROM cake_wx_shares WHERE data_type = 'wsid' AND data_id IN ({$weshare_id}) GROUP BY data_id");
            $read_total_list = $this->ShareTrackLog->query("SELECT data_id,count(1) AS total FROM cake_share_track_logs WHERE data_type = 'wsid' AND data_id IN ({$weshare_id}) GROUP BY data_id");
        }

        $read_tmp = [];
        $share_tmp = [];
        if ($share_total_list) {
            foreach ($share_total_list as $item) {
                $share_tmp[$item['cake_wx_shares']['data_id']] = $item[0]['total'];
            }
        }
        if ($read_total_list) {
            foreach ($read_total_list as $item) {
                $read_tmp[$item['cake_share_track_logs']['data_id']] = $item[0]['total'];
            }
        }
        foreach ($weshares as $index => $weshare) {
            $weshares[$index]['Weshare']['read_num'] = intval($read_tmp[$weshare['Weshare']['id']]);
            $weshares[$index]['Weshare']['share_num'] = intval($share_tmp[$weshare['Weshare']['id']]);
        }
        return $weshares;
    }


    private function injectModel()
    {
        $this->WxShare = ClassRegistry::init('WxShare');
        $this->Weshare = ClassRegistry::init('Weshare');
        $this->ShareTrackLog = ClassRegistry::init('ShareTrackLog');
    }

}