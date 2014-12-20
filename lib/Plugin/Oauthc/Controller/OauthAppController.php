<?php


class OauthAppController extends AppController {
	
	/**
	 * 获取用户oauth绑定状态
	 * @param $user_id 用户id
	 * @param $login_token oauth登录状态access_token信息
	 */
	protected function _getOauthBinds($user_id,$login_token,$source='sina'){
    	
        $this->loadModel('Oauthbind');
        $oauth_bind = $this->Oauthbind->find('first', array(
                    'conditions' => array(
                        'oauth_uid' => $login_token['user_id'],
                        'user_id' => $user_id,
                        'source' => $source,
                    ),
                ));
        if (empty($oauth_bind)) {
            $oauth_bind = array(
                'user_id' => $user_id,
                'oauth_uid' => $login_token['user_id'],
                'oauth_token' => $login_token['oauth_token'],
                'oauth_token_secret' => $login_token['oauth_token_secret'],
                'source' => $source,
            );
            $this->Oauthbind->save($oauth_bind);
        } else {
            $oauth_bind['Oauthbind']['updated'] = date('Y-m-d H:i:s');
            $oauth_bind['Oauthbind']['oauth_token'] = $login_token['oauth_token'];
            $oauth_bind['Oauthbind']['oauth_token_secret'] = $login_token['oauth_token_secret'];
            $this->Oauthbind->save($oauth_bind);
        }

        $user_oauths = $this->Oauthbind->find('all', array(
                    'conditions' => array(
                        'user_id' => $user_id,
                    ),
        ));
        $this->Session->write('Auth.Oauthbind', $user_oauths);
    }
}
?>