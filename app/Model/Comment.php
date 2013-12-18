<?php

class Comment extends AppModel {
/**
 * Model name
 *
 * @var string
 * @access public
 */
    var $name = 'Comment';
/**
 * Behaviors used by the Model
 *
 * @var array
 * @access public
 */
    var $actsAs = array(
        'Tree',
    );
/**
 * Validation
 *
 * @var array
 * @access public
 */
    /**
    var $validate = array(
        'body' => array(
            'rule' => 'notEmpty',
            'message' => 'This field cannot be left blank.',
        ),
        'name' => array(
            'rule' => 'notEmpty',
            'message' => 'This field cannot be left blank.',
        ),
        'email' => array(
            'rule' => 'email',
            'required' => true,
            'message' => 'Please enter a valid email address.',
        ),
    );
 */
}
?>