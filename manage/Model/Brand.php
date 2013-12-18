<?php
class Brand extends AppModel {
       var $name = 'Brand';
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
    var $validate = array(
    	'name' => array(
            'rule' => 'notEmpty',
            'message' => 'This field cannot be left blank.',
        ),
    );
    
}