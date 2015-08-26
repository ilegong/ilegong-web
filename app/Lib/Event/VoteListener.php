<?php

App::uses('CakeEventListener', 'Event');

class VoteListener implements CakeEventListener{

    public function implementedEvents() {
        return ['Vote.Candidate.created' => 'subSharer'];
    }

    function subSharer(CakeEvent $event){

    }
}
