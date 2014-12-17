<?php
/**
 * Created by PhpStorm.
 * User: lpy
 * Date: 2014/10/26
 * Time: 21:38
 */
class Shichituan extends AppModel {
    var $name="Shichituan";
    var $primaryKey="shichi_id";
    public $validate = array(
        'name' => array(
            'rule' => 'notEmpty'
        ),
        'wechat' => array(
            'rule' => 'notEmpty'
        ),
        'telenum' => array(
            'rule' => 'numeric',
            'message' => 'Please enter a valid phone number.'
        ),
        'company' => array(
            'rule' => 'notEmpty'
        ),
        'comment' => array(
            'rule' => 'notEmpty'
        ),

    );

    public function find_in_period($uid, $period) {
        return $this->find('first', array(
            'conditions' => array('period' => $period, 'user_id'=>$uid, 'status' => SHICHI_STATUS_OK)
        ));
    }

}
