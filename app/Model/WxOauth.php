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


    const MD_KEY_WX_BASE_ACCESS_TOKEN = "wx_base_access_token";
    public function getUserInfo($openid, $token, $lang = 'zh_CN') {
        return $this->find('all', array(
            'method' => 'get_user_info',
            'token' => $token,
            'openid' => $openid,
            'lang' => $lang
        ));
    }

    public function get_base_access_token() {

        $key = self::MD_KEY_WX_BASE_ACCESS_TOKEN;
        $token_data = Cache::read($key);
        if (empty($token_data) || $token_data['expire'] < time() || empty($token_data['token'])) {
//        40014, 40001, 41001 access_token 有关的错误？
            $rtn = $this->find('all', array('method' => 'get_base_access_token'));
            $token = $rtn['WxOauth']['access_token'];
            if (!empty($rtn) && $token) {
                Cache::write($key, array('token' => $token, 'expire'=> mktime() + 3600));
                return $token;
            }
            return '';
        }  else {
            return $token_data['token'];
        }
    }

    public function get_user_info_by_base_token($openid) {
        $accessToken = $this->get_base_access_token();
        if (!empty($accessToken) && !empty($openid)) {
            $rtn = $this->find('all', array('method' => 'get_user_info_by_base_token', 'base_token' => $accessToken, 'openid' => $openid));
            if (!empty($rtn) && $rtn['errcode'] == 0) {
                return $rtn['WxOauth'];
            } else {
                $errcode = $rtn['errcode'];
                $this->clearBaseTokenCacheIfRequired($errcode);
                return null;
            }
        }
    }

    /**
     * @param $errcode
     */
    protected function clearBaseTokenCacheIfRequired($errcode) {
        if ($errcode == 40014 || $errcode == 40001 || $errcode == 41001) {
            Cache::delete(self::MD_KEY_WX_BASE_ACCESS_TOKEN);
        }
    }
}