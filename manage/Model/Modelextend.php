<?php

class Modelextend extends AppModel {

    var $name = 'Modelextend';
    var $validate = array(
        'name' => array(
    		'unique'=>array(
	            'rule' => 'isUnique',
	            'message' => 'The name has already been taken.',
    		),    		
        ),        
    );
    
    public function getContentModel($type='list',$cate_id=1){
    	return $this->find($type,array('conditions'=>array('cate_id'=>$cate_id)));
    }
}
?>