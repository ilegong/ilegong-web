<?php
App::uses('TaobaoAppModel','Taobao.Model');
class TaobaoCate extends TaobaoAppModel {	
	var $name = 'TaobaoCate';
	
	var $validate = array(
        'cid' => array(
    		'unique'=>array(
	            'rule' => 'isUnique',
	            'message' => 'The cid has already been taken.',
    		),    		
        ),        
    );

}
?>