<?php

class VoteSetting extends AppModel{

    public $useTable = false;

    public function getVoteConfig($eventId){
        return $this->voteConfigs[$eventId];
    }

    public function getVoteWxParams($eventId) {
        $configs = $this->getVoteConfig($eventId);
        return $configs['wx_params'];
    }

    public function getVoteCommonParams($eventId) {
        $configs = $this->getVoteConfig($eventId);
        return $configs['common_params'];
    }

    public function getVoteProductParams($eventId) {
        $configs = $this->getVoteConfig($eventId);
        return $configs['product_params'];
    }

    public function getVoteInitiator($eventId) {
        $config = $this->getVoteConfig($eventId);
        return $config['initiator'];
    }

    public function getSubVoteType($eventId) {
        $config = $this->getVoteConfig($eventId);
        return $config['sub_type'];
    }

    public function getSubVoteUrl($eventId) {
        $config = $this->getVoteConfig($eventId);
        return $config['sub_url'];
    }

    var $voteConfigs = array(
        6 => array(
            'id' => 6,
            'initiator' => '811917',
            'sub_type' => 'Vote7',
            'sub_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=209556231&idx=1&sn=2a60e7f060180c9ecd0792f89694defb#rd',
            'wx_params' => array(
                'time_line_title' => '晒萌宝小宝妈请吃海鲜啦！',
                'chat_title' => '晒萌宝小宝妈请吃海鲜啦！',
                'share_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/4f4998d2b68_0829.jpg',
                'desc' => '感谢亲们对小宝妈的支持，有你们在一起真好！让我们一起记录宝宝的美好瞬间。',
            ),
            'common_params' => array(
                'banner' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8bb3a470e66_0829.jpg',
                'prize_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/2a9c850ed64_0829.jpg',
                'title' => '晒萌宝小宝妈请吃海鲜啦',
                'server_reply_title' => '晒萌宝小宝妈请吃海鲜啦',
                'server_reply_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/2a9c850ed64_0829.jpg',
                'server_reply_url' => 'http://www.tongshijia.com/vote/vote_event_view/6',
            ),
            'product_params' => array(
                'recommend' => '小宝妈',
                'products' => array(
                    'url' => 'http://www.tongshijia.com/weshares/view/413',
                    'pic' => '/img/vote/xbm_recommend.jpg',
                    'name' => '澳柑'
                )
            )
        )
    );

}