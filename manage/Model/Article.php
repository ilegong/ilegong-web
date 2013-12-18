<?php
class Article extends AppModel { 
       var $name = 'Article';
       var $hasMany = array(
	       'Uploadfile' => array(
		       'className'     => 'Uploadfile',
		       'foreignKey'    => 'data_id',
		       'conditions'    => array('Uploadfile.trash' => '0'), 
		       'order'    => 'Uploadfile.created ASC',
		       'limit'        => '',
		       'dependent'=> true
	       )
       ); 
       /* 
       var $belongsTo=array(
        'Category' => array(
            'className' => 'Category',
            'foreignKey' => 'cate_id',
        ),
    );
    */
    var $validate = array(
    	'name' => array(
            'rule' => 'notEmpty',
            'message' => 'This field cannot be left blank.',
        ),
    	'cate_id' => array(
    			'rule' => 'notEmpty',
    			'message' => 'This field cannot be left blank.',
    	),
        'slug' => array(
        	'notEmpty'=>array(
        		'rule' => 'notEmpty',
            	'message' => 'This field cannot be left blank.',
        	),
	        'isUnique'=>array(
	            'rule' => 'isUnique',
	            'message' => 'The value has already been taken.',
	        ),
        ),
    );
    
} 