<?php

class RolesController extends AppController {

    var $name = 'Roles';
	
    var $actsAs = array(
        'Acl' => array('type' => 'requester'),
    );
    
    function admin_index() {
        $this->pageTitle = __('Roles', true);

        $this->Role->recursive = 0;
        $this->paginate['Role']['order'] = "Role.id ASC";
        $this->set('roles', $this->paginate());
    }


}
?>