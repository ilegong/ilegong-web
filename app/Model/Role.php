<?php
/**
 * Role
 *
 * PHP version 5
 *
 * @category Model
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class Role extends AppModel {
/**
 * Model name
 *
 * @var string
 * @access public
 */
    var $name = 'Role';
/**
 * Behaviors used by the Model
 *
 * @var array
 * @access public
 */
    var $actsAs = array(
        'Acl' => array(
            'type' => 'requester',
        ),
    );
/**
 * Validation
 *
 * @var array
 * @access public
 */
    var $validate = array(
        'title' => array(
            'rule' => array('minLength', 1),
            'message' => 'Title cannot be empty.',
        ),
        'alias' => array(
            'isUnique' => array(
                'rule' => 'isUnique',
                'message' => 'This alias has already been taken.',
            ),
            'minLength' => array(
                'rule' => array('minLength', 1),
                'message' => 'Alias cannot be empty.',
            ),
        ),
    );

    function parentNode() {
        return null;
    }

}
?>