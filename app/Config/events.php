<?php

App::uses('ClassRegistry', 'Utility');
App::uses('VoteListener', 'lib/Event');
App::uses('CakeEventManager', 'Event');

// Attach listeners.
$VoteListener = new VoteListener();
CakeEventManager::instance()->attach($VoteListener);