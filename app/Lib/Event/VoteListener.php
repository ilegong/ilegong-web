<?php

App::uses('CakeEventListener', 'Event');
App::uses('CakeEvent', 'Event');
App::uses('ClassRegistry', 'Utility');
App::uses('CakeLog', 'Log');
class VoteListener implements CakeEventListener{

    public function implementedEvents() {
        return array('Vote.Candidate.created' => array(
            'callable' => 'subSharer',
            'passParams' => true
        ));
    }

    function subSharer(CakeEvent $event) {
        $candidateEventData = $event->data;
        $candidateData = $candidateEventData->candidateData;
        $eventId = $candidateData['eventId'];
        //sub
        $candidateId = $candidateEventData->id;
        CakeLog::write(LOG_ERR, 'event save relation candidate id ' . $candidateId . 'event id ' . $eventId);
        if ($eventId == 6 && !empty($candidateId)) {
            $creatorId = $candidateData->userId;
            $userRelationM = ClassRegistry::init('UserRelation');
            $saveData = array(
                'user_id' => '811917',
                'follow_id' => $creatorId,
                'type' => 'Vote',
                'created' => date('Y-m-d H:i:s')
            );
            $userRelation = $userRelationM->save($saveData);
            CakeLog::write(LOG_ERR, 'event save relation ' . json_encode($userRelation));
        }
    }
}
