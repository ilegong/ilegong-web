<?php
/**
 * Language
 *
 * PHP version 5
 *
 * @category Model
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class Language extends AppModel {
/**
 * Model name
 *
 * @var string
 * @access public
 */
    var $name = 'Language';
/**
 * Behaviors used by the Model
 *
 * @var array
 * @access public
 */
    var $actsAs = array(
        'Ordered' => array('field' => 'weight', 'foreign_key' => null),
    );
/**
 * Validation
 *
 * @var array
 * @access public
 */
    var $validate = array(
        'name' => array(
            'rule' => array('minLength', 1),
            'message' => 'This field cannot be left blank.',
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

}
?>