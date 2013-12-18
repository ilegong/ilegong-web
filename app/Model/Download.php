<?php
class Download extends AppModel { 
       var $name = 'Download';
       
       var $hasMany = array(
	       'Uploadfile' => array(
		       'className'     => 'Uploadfile',
		       'foreignKey'    => 'data_id',
		       'conditions'    => array('Uploadfile.modelclass' => 'Download','Uploadfile.trash' => '0'), 
		       'order'    => 'Uploadfile.sortorder asc,Uploadfile.created ASC',
		       'limit'        => '',
		       'dependent'=> true
	       )
       ); 
       
    var $validate = array(
    	'title' => array(
            'rule' => 'notEmpty',
            'message' => 'This field cannot be left blank.',
        ),
        'slug' => array(
        	/*'notEmpty'=> array(
	            'rule' => 'notEmpty',
	            'message' => 'This field cannot be left blank.',
	        ),*/
	        'isUnique'=>array(
	            'rule' => 'isUnique',
	            'message' => 'The slug has already been taken.',
	        ),
        ),
    );
    
} 