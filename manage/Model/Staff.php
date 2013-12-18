<?php
class Staff extends AppModel {
    var $name = 'Staff';
    
    var $actsAs = array(
        'Acl' => array('type' => 'requester'),
    );
    
    var $validate = array(
    		'name' => array(
    				'unique'=>array(
    						'rule' => 'isUnique',
    						'message' => 'The username has already been taken.',
    				),
    				'notempty'=>array(
    						'rule' => 'notEmpty',
    						'message' => 'This field cannot be left blank.',
    				)
    		),
    		'email' => array(
    				'email' => array(
    						'rule' => 'email',
    						'message' => 'Please provide a valid email address.',
    				),
    				'isUnique' => array(
    						'rule' => 'isUnique',
    						'message' => 'Email address already in use.',
    				),
    		),
    		'password' => array(
    				'rule' => array('minLength', 8),
    				'message' => 'Passwords must be at least 8 characters long.',
    		),
    		'nickname' => array(
    				'rule' => 'notEmpty',
    				'message' => 'This field cannot be left blank.',
    		),
    );
    
    public $hasAndBelongsToMany = array(
					'Role' => array(
							'className'              => 'Role',
							'joinTable'              => 'staff_roles',
							'foreignKey'             => 'staff_id', // 外键对应本模块的id
							'associationForeignKey'  => 'role_id', // 外键对应关联模块的id
							'unique'                 => true,//'keepExisting'
							'dependent'            => true,
							'exclusive'            => true,
					)
			);
//    var $belongsTo = array('Role');

    function parentNode() {
	    if (!$this->id && empty($this->data)) {
	        return null;
	    }
	    $data = $this->data;
	    if (empty($this->data)) {
	    	$data = $this->read(); // 读取role_id字段值
	    }
        if (!isset($data['Role']) || empty($data['Role'])) {
            $roles = array_filter(explode(',',$data['Staff']['role_id']),'');
        	return array('Role' => array('id' => $roles));
        } else {
        	$roles = array();
        	foreach($data['Role'] as $role){
        		$roles[] = $role['role_id'];
        	}
        	//$roles = array_delete_value(explode(',',$data['Staff']['role_id']),'');
        	return array('Role' => array('id' => $roles));
        }
    }
}
?>