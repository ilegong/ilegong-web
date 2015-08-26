<?php

App::uses('CakeEventListener', 'Event');
App::uses('CakeEvent', 'Event');
App::uses('ClassRegistry', 'Utility');
App::uses('CakeLog', 'Log');
class VoteListener implements CakeEventListener{

    public function implementedEvents() {
        return array('Vote.Candidate.created' => 'subSharer');
    }

    public function subSharer(CakeEvent $event) {
        $candidateEventData = $event->data;
        $candidateData = $candidateEventData['candidateData'];
        $eventId = $candidateData['eventId'];
        //sub
        $candidateId = $candidateEventData['id'];
        $creatorId = $candidateData['userId'];
        CakeLog::write(LOG_ERR, 'event save relation candidate event ' . json_encode($event) . ' candidate id ' . $candidateId . ' event id ' . $eventId . ' user id ' . $creatorId);
        if ($eventId == 6 && !empty($candidateId)) {
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
