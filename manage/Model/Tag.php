<?php
class Tag extends AppModel { 
       var $name = 'Tag';
    var $validate = array(
    	'name' => array(
            'rule' => 'notEmpty',
            'message' => 'This field cannot be left blank.',
        ),
    	'cate_id' => array(
    			'rule' => 'notEmpty',
    			'message' => 'This field cannot be left blank.',
    	)
    );
    
} 