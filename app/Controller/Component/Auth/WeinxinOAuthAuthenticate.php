<?php

App::uses('BaseAuthenticate', 'Controller/Component/Auth');

class WeinxinOAuthAuthenticate extends BaseAuthenticate {

    public function authenticate(CakeRequest $request, CakeResponse $response) {
        $oauth_wx_source = oauth_wx_source();
        $source = $_REQUEST['source'];
        $openid = $_REQUEST['openid'];
        if (empty($openid) || $source != $oauth_wx_source) {
            return false;
        }

        $oauth = ClassRegistry::init('Oauthbind')->find('first', array('conditions' => array('source' => $source,
            'oauth_openid' => $openid)));
        if (!empty($oauth)) {
            $oauth = $oauth['Oauthbind'];
        }
        if ($oauth['user_id'] > 0) {
            $wxOauth = ClassRegistry::init('WxOauth');
            $token = $wxOauth->find('all',
                array('method' => 'auth_token', 'token' => $oauth['oauth_token'], 'openid' => $oauth['oauth_openid'])
            );

            if (!empty($token)) {
                $token = $token['WxOauth'];
                if ($token['errcode'] == 0) {
                    return $this->_findUser(array('id' => $oauth['user_id']));
                }
            }
        }

        return false;
    }
}
