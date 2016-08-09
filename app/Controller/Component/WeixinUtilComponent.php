<?php

/**
 * Class WeixinUtilComponent
 * 微信操作的工具类
 */
class WeixinUtilComponent extends Component {

    public $components = array('ShareUti', 'WeshareBuy');

    public function save_user_sub_reason($type, $url, $uid, $title, $data_id) {
        $UserSubReasonM = ClassRegistry::init('UserSubReason');
        $UserSubReasonM->save(array('type' => $type, 'url' => $url, 'user_id' => $uid, 'title' => $title, 'data_id' => $data_id, 'created' => date('Y-m-d H:i:s')));
    }

    /**
     * @param $user_id
     * @return mixed
     * query user sub reason
     */
    public function get_user_sub_reason($user_id) {
        $UserSubReasonM = ClassRegistry::init('UserSubReason');
        $subReason = $UserSubReasonM->find('first', array(
            'conditions' => array(
                'user_id' => $user_id,
                'used' => 0,
                'DATE(created)' => date('Y-m-d')
            ),
            'order' => array('id DESC')
        ));
        return $subReason;
    }

    public function update_user_sub_status($uid) {
        Cache::write(key_cache_sub($uid), WX_STATUS_SUBSCRIBED);
    }

    public function create_weixin_user($openId) {
        try {
            $oauth_wx_source = oauth_wx_source();
            $WxOauthM = ClassRegistry::init('WxOauth');
            $OauthbindM = ClassRegistry::init('Oauthbind');
            $uinfo = $WxOauthM->get_user_info_by_base_token($openId);
            $userId = createNewUserByWeixin($uinfo, $this->User);
            $oauth['Oauthbind']['oauth_openid'] = $openId;
            $oauth['Oauthbind']['created'] = date(FORMAT_DATETIME);
            $oauth['Oauthbind']['source'] = $oauth_wx_source;
            $oauth['Oauthbind']['domain'] = $oauth_wx_source;
            $oauth['Oauthbind']['user_id'] = $userId;
            $OauthbindM->save($oauth['Oauthbind']);
        } catch (Exception $e) {
            $this->log("error to save new user:" . $e);
        }
    }

    /**
     * @param $from
     * @param $uid
     * @param $openId
     * @return array
     */
    public function process_user_sub_weixin($from, $uid, $openId) {
        $replay_type = 1;
        $url = '';
        $userM = ClassRegistry::init('User');
        $sql = "SELECT nickname FROM cake_users WHERE deleted = 0 AND username = '{$openId}' LIMIT 1";

        $user = $userM->query($sql);
        $userName = $user[0]['cake_users']['nickname'];

        if ($from == FROM_WX_SERVICE) {
            /*$content = array(
                array('title' => '朋友说是什么？看完你就懂了！', 'description' => '',
                    'picUrl' => 'https://mmbiz.qlogo.cn/mmbiz/qpxHrxLKdR0A6F8hWz04wVpntT9Jiao8XZn7as5FuHch5zFzFnvibjUGYU3J4ibxRyLicytfdd9qDQoqV1ODOp3Rjg/0',
                    'url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=201694178&idx=1&sn=8dea494e02c96dc21e51931604771748#rd')
            );*/
            $content = "Hi，{$userName}\n终于等到您了，欢迎加入[ 朋友说]社区。\n[朋友说] 是一家为您提供精挑细选产品的平台，通过熟人间信任关系来为您提供优质的产品，节省您选择的时间，并通过这种方式来保证您买到产品的品质。\n".'<a href="http://www.tongshijia.com/weshares/index?from=weixin">'."进入逛逛>></a>\n如您有任何问题或建议，请加客服小妹微信咨询，微信号：pyshuo2015";
            $reason = $this->get_user_sub_reason($uid);
            if (!empty($uid) && !empty($reason)) {
                $url = $reason['UserSubReason']['url'];
                if (strpos($reason['UserSubReason']['type'], 'Vote') !== FALSE) {
                    $title = $reason['UserSubReason']['title'];
                    $event_id = $reason['UserSubReason']['data_id'];
                    $VoteSettingM = ClassRegistry::init('VoteSetting');
                    $picUrl = $VoteSettingM->getServerReplyPic($event_id);
                    $this->log("vote event id pic url:" . $event_id . ' ' . $picUrl);
                    $content = array(
                        array('title' => $title, 'description' => '快来支持我吧...',
                            'picUrl' => $picUrl,
                            'url' => $reason['UserSubReason']['url']),
                    );
                } else if ($reason['UserSubReason']['type'] == SUB_SHARER_REASON_TYPE_FROM_USER_CENTER || $reason['UserSubReason']['type'] == SUB_SHARER_REASON_TYPE_FROM_SHARE_INFO) {
                    //http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/d83967f3845_1020.jpg
                    if ($reason['UserSubReason']['type'] == SUB_SHARER_REASON_TYPE_FROM_USER_CENTER) {
                        $this->WeshareBuy->subscribe_sharer($reason['UserSubReason']['data_id'], $reason['UserSubReason']['user_id'], $type = 'SUB');
                    }
                    if ($reason['UserSubReason']['type'] == SUB_SHARER_REASON_TYPE_FROM_SHARE_INFO) {
                        $this->WeshareBuy->subscribe_sharer_by_share($reason['UserSubReason']['data_id'], $reason['UserSubReason']['user_id'], $type = 'SUB');
                    }
                    $content = array(array(
                        'title' => $reason['UserSubReason']['title'],
                        'description' => '点击查看详情....',
                        'picUrl' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/d83967f3845_1020.jpg',
                        'url' => $reason['UserSubReason']['url']
                    ));
                }elseif ($reason['UserSubReason']['type'] == SUB_SHARER_REASON_TYPE_FROM_SPREAD) {
                    $sharer_id = $reason['UserSubReason']['data_id'];
                    $SpreadConfM = ClassRegistry::init('SpreadConf');
                    $sharer_conf = $SpreadConfM->get_sharer_conf($sharer_id);
                    $content = array(
                        array(
                            'title' => $reason['UserSubReason']['title'],
                            'description' => '点击进入我的个人中心',
                            'picUrl' => $sharer_conf['wx_pic'],
                            'url' => $reason['UserSubReason']['url']
                        )
                    );
                }
                $UserSubReasonM = ClassRegistry::init('UserSubReason');
                $UserSubReasonM->updateAll(array('used' => 1), array('id' => $reason['UserSubReason']['id']));
            }
            if ($uid) {
                $this->update_user_sub_status($uid);
            } else {
                $this->create_weixin_user($openId);
            }
        } else {
            /*$content = array(
                array('title' => '朋友说是什么？看完你就懂了！', 'description' => '',
                    'picUrl' => 'https://mmbiz.qlogo.cn/mmbiz/qpxHrxLKdR0A6F8hWz04wVpntT9Jiao8XZn7as5FuHch5zFzFnvibjUGYU3J4ibxRyLicytfdd9qDQoqV1ODOp3Rjg/0',
                    'url' => 'http://mp.weixin.qq.com/s?__biz=MjM5NzQ3NTkxNA==&mid=203424483&idx=1&sn=e281fc56834fb0c2942f887d2edd8d48#rd')
            );*/
            $content = "Hi，{$userName}\n终于等到您了，欢迎加入[ 朋友说]社区。\n[朋友说] 是一家为您提供精挑细选产品的平台，通过熟人间信任关系来为您提供优质的产品，节省您选择的时间，并通过这种方式来保证您买到产品的品质。\n".'<a href="http://www.tongshijia.com/weshares/index?from=weixin">'."进入逛逛>></a>\n如您有任何问题或建议，请加客服小妹微信咨询，微信号：pyshuo2015";
        }
        return array('replay_type' => $replay_type, 'content' => $content, 'url' => $url);
    }

}