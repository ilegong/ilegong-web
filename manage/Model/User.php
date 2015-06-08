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
//        'username' => array(
//    		'unique'=>array(
//	            'rule' => 'isUnique',
//	            'message' => 'The username has already been taken.',
//    		),
//    		'notempty'=>array(
//	            'rule' => 'notEmpty',
//	            'message' => 'This field cannot be left blank.',
//    		),
//    		'minLength'=>array(
//	            'rule' => array('minLength',3),
//	            'message' => 'This field length must big than 3',
//    		),
//        ),
//        'email' => array(
//            'email' => array(
//                'rule' => 'email',
//                'message' => 'Please provide a valid email address.',
//            ),
//            'isUnique' => array(
//                'rule' => 'isUnique',
//                'message' => 'Email address already in use.',
//            ),
//        ),
        'password' => array(
            'rule' => array('minLength', 6),
            'message' => 'Passwords must be at least 6 characters long.',
        ),
        'nickname' => array(
            'rule' => 'notEmpty',
            'message' => 'This field cannot be left blank.',
        ),
    );

    public function beforeSave($options = array()) {
        $mobilePhone = $this->data['User']['mobilephone'];
        if (!empty($mobilePhone)) {
            $u = $this->find('first', array(
                'conditions' => array(
                    'OR' => array('mobilephone' => $mobilePhone, 'username' => $mobilePhone)
                )
            ));
            if ( !(empty($u) || $u['User']['id'] == $this->data['User']['id']) ) {
                throw new CakeException("duplicated mobile phone:".$mobilePhone, ERROR_CODE_USER_DUP_MOBILE);
            }
        }
        return parent::beforeSave($options);
    }

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

    function add_score($uid, $changed) {
        $old_score = $this->get_score($uid, true);
        $updated = $this->updateAll(array('score' => $old_score + $changed), array('User.score' => $old_score, 'User.id' => $uid));
        if ($updated) {
            Cache::delete($this->score_key($uid));
        }
        return $updated;
    }

    function get_score($uid, $ignoreCache = false) {
        $score_key = $this->score_key($uid);
        $score = $ignoreCache ? false : Cache::read($score_key);
        if ($score !== 0 && empty($score)) {
            $u = $this->findById($uid);
            $score = $u['User']['score'];
            Cache::write($score_key, $score);
        }
        return $score;
    }

    /**
     * @param $uid
     * @return string
     */
    private function score_key($uid) {
        return '_u_score_' . $uid;
    }
}
?>