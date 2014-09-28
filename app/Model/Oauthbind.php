<?php

class Oauthbind extends AppModel {
    var $name = 'Oauthbind';

    /**
     * Find Uid by WX openid
     * @param $openid
     * @return user_id or 0
     */
    public function findUidByWx($openid) {
        global $oauth_wx_source;
        $oauth_record = $this->find('first', array(
            'conditions' => array('source' => $oauth_wx_source,
                'oauth_openid' => $openid
            ),
            'fields' => array('user_id')
        ));
        return ($oauth_record && $oauth_record['user_id'] > 0) ? $oauth_record['user_id'] : 0;
    }

} 