<?php

class Category extends AppModel { 
       var $name = 'Category';
       var $actsAs = array(
       		'Tree'=> array('left'=>'left','right'=>'right'),
       );
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
?>