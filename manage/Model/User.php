<?php
/**
 * User
 *
 * PHP version 5
 *
 * @category Model
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class User extends AppModel {
/**
 * Model name
 *
 * @var string
 * @access public
 */
    var $name = 'User';    

/**
 * Model associations: belongsTo
 *
 * @var array
 * @access public
 */
    var $belongsTo = array('Role');
/**
 * Validation
 *
 * @var array
 * @access public
 */
    var $validate = array(
        'username' => array(
    		'unique'=>array(
	            'rule' => 'isUnique',
	            'message' => 'The username has already been taken.',
    		),
    		'notempty'=>array(
	            'rule' => 'notEmpty',
	            'message' => 'This field cannot be left blank.',
    		),
    		'minLength'=>array(
	            'rule' => array('minLength',3),
	            'message' => 'This field length must big than 3',
    		),
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
            'rule' => array('minLength', 6),
            'message' => 'Passwords must be at least 6 characters long.',
        ),
        'nickname' => array(
            'rule' => 'notEmpty',
            'message' => 'This field cannot be left blank.',
        ),
    );

	function userlist($page,$count) {
		$this->recursive = -1;
        $userlist = $this->find('all',array(
        	'conditions'=>array('sina_uid >0','status'=>1,'deleted'=>0 ),
        	'page'=>$page,
        	'limit'=>$count,
        	'order'=>'id desc'
        ));
        return $userlist;
    }
}
?>