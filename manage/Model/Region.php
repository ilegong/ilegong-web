<?php
class Region extends AppModel { 
       var $name = 'Region';
		
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
}