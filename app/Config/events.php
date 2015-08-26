<?php

App::uses('ClassRegistry', 'Utility');
App::uses('VoteListener', 'Event');
App::uses('CakeEventManager', 'Event');

// Attach listeners.
CakeEventManager::instance()->attach(new VoteListener());