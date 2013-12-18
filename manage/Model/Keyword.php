<?php
class Keyword extends AppModel { 
       var $name = 'Keyword';
       
       var $validate = array(
       		'value' => array(
       				'rule' => 'notEmpty',
       				'message' => 'This field cannot be left blank.',
       		),
       );
}