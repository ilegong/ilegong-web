<?php

App::uses('CakeEventListener', 'Event');
App::uses('CakeLog', 'Log');
class VoteListener implements CakeEventListener{

    public function implementedEvents() {
        return array('Vote.Candidate.created' => 'subSharer');
    }

    function subSharer(CakeEvent $event) {
        $candidateEventData = $event['data'];
        $eventId = $candidateEventData['candidateEvent']['event_id'];
        //sub
        $candidateId = $event['id'];
        if ($eventId == 6 && !empty($candidateId)) {
            $creatorId = $candidateEventData['userId'];
            $userRelationM = ClassRegistry::init('UserRelation');
            $saveData = array(
                'user_id' => '811917',
                'follow_id' => $creatorId,
                'type' => 'Vote',
                'created' => date('Y-m-d H:i:s')
            );
            $userRelation = $userRelationM->saveAll($saveData);
            CakeLog::write(LOG_ERR, 'event save relation ' . json_encode($userRelation));
        }
    }
}
