<?php
App::uses('Model', 'Model');
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 9/28/14
 * Time: 6:06 PM
 */
class WxOauth extends Model {
    public $useDbConfig = 'WxOauth';
    public $useTable = false;


    public function getUserInfo($openid, $token, $lang = 'zh_CN') {
        return $this->find('all', array(
            'method' => 'get_user_info',
            'token' => $token,
            'openid' => $openid,
            'lang' => $lang
        ));
    }
}