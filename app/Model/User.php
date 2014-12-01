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
     * Validation
     *
     * @var array
     * @access public
     */
    var $validate = array(
        'username' => array(
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'The username has already been taken.',
            ),
            'notempty' => array(
                'rule' => 'notEmpty',
                'message' => 'This field cannot be left blank.',
            ),
            'minLength' => array(
                'rule' => array('minLength', 3),
                'message' => 'This field length must big than 3',
            ),
        ),
        'mobilephone' => array(
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'The phone has already been taken.',
            ),
            'notempty' => array(
                'rule' => 'notEmpty',
                'message' => 'This field cannot be left blank.',
            ),
        ),
        'code' => array(
            'rule' => 'notEmpty'
        )
    );

    function userlist($page, $count) {
        $this->recursive = -1;
        $userlist = $this->find('all', array(
                    'conditions' => array('sina_uid >0', 'status' => 1, 'deleted' => 0),
                    'page' => $page,
                    'limit' => $count,
                    'order' => 'id desc'
                ));
        return $userlist;
    }

    /**
     * @param $uid
     * @return string  null if not found
     */
    function findNicknamesOfUid($uid) {
        $m = $this->findNicknamesMap(array($uid,));
        return !empty($m) ? $m[$uid] : null;
    }

    function findNicknamesMap($uids) {
        $names = $this->find('all', array('conditions' => array('id' => $uids), 'fields' => array('nickname', 'id')));
        $map = array();
        foreach($names as $name_id) {
            $map[$name_id['User']['id']] = $name_id['User']['nickname'];
        }
        return $map;
    }

}
?>