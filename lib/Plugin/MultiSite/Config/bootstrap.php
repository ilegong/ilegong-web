<?php

if(APP_DIR == 'manage'){
	$GLOBALS['model_behaviors']['*']['MultiSite.MultiSite'] = array();
}

Configure::write('Hook.components.MultiSite','MultiSite.MultiSiteHook');

