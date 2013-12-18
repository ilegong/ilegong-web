<?php

class I18nfield extends AppModel {

    var $name = 'I18nfield';
    var $validate = array(
        'name' => array( // 这一层的索引为字段名，必须在表中出现，不能为name_model之类的。 $fieldName => $ruleSet
    		'name_model'=>array(
    			'rule' => array('isUniqueMulti', array('name', 'model')), 
	            'message' => 'The field has already been taken.',
    		),    		
        ),  
    	'type' => array(
    			'rule' => 'notEmpty',
    			'message' => 'This field cannot be left blank.',
    	),
    	'length' => array(
    			'rule' => 'notEmpty',
    			'message' => 'This field cannot be left blank.',
    	),
    	'translate' => array(
    			'rule' => 'notEmpty',
    			'message' => 'This field cannot be left blank.',
    	),
    );
}
?>