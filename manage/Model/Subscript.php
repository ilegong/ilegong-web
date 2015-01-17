<?php
/**
 * Created by PhpStorm.
 * User: algdev
 * Date: 15/1/16
 * Time: 下午4:50
 */
class Subscript extends AppModel {
    var $name = 'Subscript';
    var $hasMany = array(
        'Uploadfile' => array(
            'className'     => 'Uploadfile',
            'foreignKey'    => 'data_id',
            'conditions'    => array('Uploadfile.trash' => '0'),
            'order'    => 'Uploadfile.created ASC',
            'limit'        => '',
            'dependent'=> true
        )
    );
    var $validate =array(

        'slug' => array(
            'rule' =>'notEmpty',
            'message' => 'This field cannot be left blank',
        ),
        'title' => array(
            'rule' => 'notEmpty',
            'message' => 'This title cannot be left blank',
        ),
        'priority' => array(
            'rule' => 'notEmpty',
            'message' => 'This priority cannot be left blank'
        ),
    );
}
