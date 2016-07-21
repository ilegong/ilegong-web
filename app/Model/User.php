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
//        'mobilephone' => array(
//            'notempty' => array(
//                'rule' => 'notEmpty',
//                'message' => 'This field cannot be left blank.',
//            ),
//        ),
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
     * @return mixed
     * check user is proxy
     */
    function userIsProxy($uid){
        $proxyData = $this->find('first', array('conditions' => array('id' => $uid), 'fields' => array('is_proxy', 'id')));
        return $proxyData['User']['is_proxy'];
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

    function add_score($uid, $changed) {
        $old_score = $this->get_score($uid, true);
        $updated = $this->updateAll(array('score' => 'score + (' . $changed . ')'), array('score' => $old_score, 'id' => $uid));
        if ($updated) {
            Cache::delete($this->score_key($uid));
        }
        return $updated;
    }

    function add_rebate_money($uid, $changed){
        $old_money = $this->get_rebate_money($uid, true);
        $updated = $this->updateAll(['rebate_money' => 'rebate_money + (' . $changed . ')'], ['rebate_money' => $old_money, 'id' => $uid]);
        if($updated){
            Cache::delete($this->rebate_money_key($uid));
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

    function get_rebate_money($uid, $ignoreCache = false){
        $rebate_money_key = $this->rebate_money_key($uid);
        $rebate_money = $ignoreCache ? false : Cache::read($rebate_money_key);
        if ($rebate_money !== 0 && empty($rebate_money)) {
            $u = $this->find('first',['conditions' => ['id' => $uid], 'fields' => ['id', 'rebate_money']]);
            $rebate_money = $u['User']['rebate_money'];
            Cache::write($rebate_money_key, $rebate_money);

        }
        return $rebate_money;
    }

    /* 获取用户关注的团长ID */
    public function get_my_proxys($uid)
    {
        $userRelationM = ClassRegistry::init('UserRelation');
        $info = $userRelationM->find('all', [
            'conditions' => [
                'follow_id' => $uid,
                'deleted' => DELETED_NO,
            ],
            'fields' => ['user_id'],
        ]);
        $info = Hash::extract($info, '{n}.UserRelation.user_id');

        return $info;
    }

    public function get_users_simple_info($user_ids){
        if (!is_array($user_ids)) {
            $user_ids = [$user_ids];
        }
        $users = $this->find('all', ['conditions' => ['id' => $user_ids], 'fields' => ['id', 'image', 'avatar', 'nickname']]);
        $users = array_map('map_user_avatar2', $users);
        return $users;
    }

    /**
     * @param $uid
     * @return string
     */
    private function score_key($uid) {
        return '_u_score_' . $uid;
    }

    private function rebate_money_key($uid){
        return '_u_rebate_money_'.$uid;
    }

}
?>
