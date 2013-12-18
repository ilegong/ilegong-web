<?php
class Article extends AppModel { 
       var $name = 'Article';
       
       var $hasMany = array(
	       'Uploadfile' => array(
		       'className'     => 'Uploadfile',
		       'foreignKey'    => 'data_id',
		       'conditions'    => array('Uploadfile.modelclass' => 'Article','Uploadfile.trash' => '0'), 
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