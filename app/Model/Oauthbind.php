<?php

class Oauthbind extends AppModel {
    var $name = 'Oauthbind';

    /**
     * Find Uid by WX openid
     * @param $openid
     * @return user_id or 0
     */
    public function findUidByWx($openid) {
        $oauth_wx_source = oauth_wx_source();
        $oauth_record = $this->find('first', array(
            'conditions' => array('source' => $oauth_wx_source,
                'oauth_openid' => $openid
            ),
            'fields' => array('user_id')
        ));
        return ($oauth_record && $oauth_record['Oauthbind']['user_id'] > 0) ? $oauth_record['Oauthbind']['user_id'] : 0;
    }

    public function findUidByUnionId($union_id){
        $oauth_record = $this->find('first', array(
            'conditions' => array(
                'unionId' => $union_id
            ),
            'fields' => array('user_id')
        ));
        return ($oauth_record && $oauth_record['Oauthbind']['user_id'] > 0) ? $oauth_record['Oauthbind']['user_id'] : 0;
    }


    public function findWxServiceBindByUid($uid) {
        $r = $this->find('first', array('conditions' => array('user_id' => $uid, 'source' => oauth_wx_source(),)));
        if (!empty($r)) {
            return $r['Oauthbind'];
        } else {
            return false;
        }
    }

    public function findWxServiceBindsByUids($uids){
        $r = $this->find('all', array('conditions' => array('user_id' => $uids)));
        if (!empty($r)) {
            $r = Hash::extract($r,'{n}.Oauthbind.oauth_openid');
            return $r;
        } else {
            return false;
        }
    }

    public function findWxServiceBindMapsByUids($uids) {
        $r = $this->find('all', array('conditions' => array('user_id' => $uids)));
        if (!empty($r)) {
            $r = Hash::combine($r, '{n}.Oauthbind.user_id', '{n}.Oauthbind.oauth_openid');
            return $r;
        } else {
            return false;
        }
    }

    public function update_wx_bind_uid($openid, $oldUid, $newUid) {
        if ($this->updateAll(array('user_id' => $newUid), array('user_id' => $oldUid, 'oauth_openid' => $openid, 'source' => oauth_wx_source(),))) {
            return $this->getAffectedRows();
        }
        return 0;
    }

}