<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/27/15
 * Time: 09:46
 */

/**
 * Vote Model
 *
 * @property Voter $Voter
 */
class Vote extends AppModel {

    /**
     * Display field
     *
     * @var string
     */
    public $displayField = 'id';


    //The Associations below have been created with all possible keys, those that are not needed can be removed

    /**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = array(
        'User' => array(
            'className' => 'User',
            'foreignKey' => 'user_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        ),
        'Candidate' => array(
            'className' => 'Candidate',
            'foreignKey' => 'candidate_id',
            'conditions' => '',
            'fields' => '',
            'order' => ''
        )
    );
}