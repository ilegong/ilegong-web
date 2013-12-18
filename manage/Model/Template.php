<?php

class Template extends AppModel {

    var $name = 'Template';
    var $validate = array(
        'name' => array( // 这一层的索引为字段名，必须在表中出现，不能为name_model之类的。 $fieldName => $ruleSet
    		'name_model'=>array(
    			'rule' => array('isUniqueMulti', array('relatepath', 'theme','appname')), 
	            'message' => 'The template has already been taken.',
    		),    		
        ),        
    );
}
?>