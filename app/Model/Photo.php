<?php
class Photo extends AppModel { 
       var $name = 'Photo';
       
       var $hasMany = array(
	       'Uploadfile' => array(
		       'className'     => 'Uploadfile',
		       'foreignKey'    => 'data_id',
		       'conditions'    => array('Uploadfile.modelclass' => 'Photo','Uploadfile.trash' => '0'), 
		       'order'    => 'Uploadfile.sortorder asc,Uploadfile.created ASC',
		       'limit'        => '',
		       'dependent'=> true
	       )
       );  
       var $belongsTo=array(
        'Category' => array(
            'className' => 'Category',
            'foreignKey' => 'cate_id',
//             'conditions' => "Category.model='Article'",
        ),
    );
    var $validate = array(
    	'name' => array(
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