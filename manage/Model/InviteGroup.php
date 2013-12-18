<?php

class InviteGroup extends AppModel {

    var $name = 'InviteGroup';
    
    var $validate = array(
        'content' => array(
    		'unique'=>array(
	            'rule' => 'isUnique',
	            'message' => 'The has already saved.',
    		),
        ),
    );
    
	function getUserInviteGroup($uid){
		$cache_key = 'invite_group_list_'.$uid;
		$invite_group_list = Cache::read($cache_key); 
		if ($invite_group_list === false) 
		{
			$invite_group_list = $this->find('all',array(
				'conditions'=>array(
					'creator'=> $uid,
				),
				'fields'=>array('id','name','content'),
				'limit'=>15,
				'order'=>'InviteGroup.id desc'
			));
			
			Cache::write($cache_key,$invite_group_list);
		}
		return $invite_group_list;
    }
    
}
?>