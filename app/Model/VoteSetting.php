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

    public function getVoteTitle($eventId) {
        $config = $this->getVoteConfig($eventId);
        return $config['title'];
    }

    public function getServerReplyPic($eventId) {
        $config = $this->getVoteConfig($eventId);
        return $config['common_params']['server_reply_img'];
    }


    var $voteConfigs = array(
        11 => array(
            'id' => 11,
            'initiator' => '701201',
            'sub_type' => 'Vote11',
            'title' => '亲子联盟新西兰之旅分享会',
            'sub_url' => 'https://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=404860412&idx=1&sn=c958e92faa424363d254a142ed4803c3',
            'wx_params' => array(
                'time_line_title' => '亲子联盟·新西兰之旅分享会',
                'chat_title' => '亲子联盟·新西兰之旅分享会',
                'share_pic' => 'http://static.tongshijia.com/static/img/vote/vote_11_banner.jpg?v2',
                'desc' => '记录下最美的一瞬间...如梦如幻的世界，带上你的宝贝，你也可以当一回摄影大师!',
            ),
            'common_params' => array(
                'banner' => 'http://static.tongshijia.com/static/img/vote/vote_11_banner.jpg?v2',
                'prize_pic' => 'http://static.tongshijia.com/images/index/8920d130-f58b-11e5-8c9e-00163e1600b6.jpg?v1',
                'server_reply_title' => '亲子联盟·新西兰之旅分享会',
                'server_reply_img' => 'http://static.tongshijia.com/static/img/vote/vote_9_banner.jpg?v1',
                'server_reply_url' => 'http://www.tongshijia.com/vote/vote_event_view/11',
                'reply_tip_info' => '请先关注朋友说微信公众号“pyshuo2014”如您已关注，请进入公众号回复“8888”即可！'
            ),
        ),
        9 => array(
            'id' => 9,
            'initiator' => '141',
            'sub_type' => 'Vote10',
            'title' => '新西兰梦幻之旅亲子摄影大赛开赛啦!',
            'sub_url' => 'https://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=404860412&idx=1&sn=c958e92faa424363d254a142ed4803c3',
            'wx_params' => array(
                'time_line_title' => '新西兰梦幻之旅亲子摄影大赛开赛啦！',
                'chat_title' => '新西兰梦幻之旅亲子摄影大赛开赛啦！',
                'share_pic' => 'http://static.tongshijia.com/static/img/vote/vote_9_banner.jpg?v1',
                'desc' => '记录下最美的一瞬间...如梦如幻的世界，带上你的宝贝，你也可以当一回摄影大师!',
            ),
            'common_params' => array(
                'banner' => 'http://static.tongshijia.com/static/img/vote/vote_9_banner.jpg?v1',
                'prize_pic' => 'http://static.tongshijia.com/images/index/8920d130-f58b-11e5-8c9e-00163e1600b6.jpg?v1',
                'server_reply_title' => '新西兰梦幻之旅亲子摄影大赛开赛啦！',
                'server_reply_img' => 'http://static.tongshijia.com/static/img/vote/vote_9_banner.jpg?v1',
                'server_reply_url' => 'http://www.tongshijia.com/vote/vote_event_view/9',
                'reply_tip_info' => '请先关注朋友说微信公众号“pyshuo2014”如您已关注，请进入公众号回复“8888”即可！'
            ),
        ),
        8 => array(
            'id' => 8,
            'initiator' => '141',
            'sub_type' => 'Vote9',
            'title' => '孩子们最开心的万圣节又来了!',
            'sub_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=400481575&idx=1&sn=6de5a3b7c60d050bd3be1223e06fe09d',
            'wx_params' => array(
                'time_line_title' => '万圣节最佳装扮评选投票啦！',
                'chat_title' => '万圣节最佳装扮评选投票啦！',
                'share_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/dada36a0363_1030.jpg',
                'desc' => '这一天的装扮是多么的“可爱”！看看大家的装扮，我们快来比一比，谁的装扮最Cool! 最棒？',
            ),
            'common_params' => array(
                'banner' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/dada36a0363_1030.jpg',
                'prize_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/6bbd4e0c490_1030.jpg',
                'server_reply_title' => '万圣节最佳装扮评选投票啦！',
                'server_reply_img' => 'http://51daifan-images.stor.sinaapp.com/files/201510/thumb_m/dada36a0363_1030.jpg',
                'server_reply_url' => 'http://www.tongshijia.com/vote/vote_event_view/8',
                'reply_tip_info' => '请先关注朋友说微信公众号“pyshuo2014”如您已关注，请进入公众号回复“万圣节”即可！'
            ),
//            'product_params' => array(
//                'recommend' => '樱花',
//                'products' => array(
//                    array(
//                        'url' => 'http://www.tongshijia.com/weshares/view/56',
//                        'pic' => '/img/imgstore/zao.jpg',
//                        'name' => '天山骏枣'
//                    )
//                )
//            )
        ),
        6 => array(
            'id' => 6,
            'initiator' => '811917',
            'sub_type' => 'Vote7',
            'title' => '晒萌宝小宝妈请吃海鲜啦',
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
                'server_reply_title' => '晒萌宝小宝妈请吃海鲜啦',
                'server_reply_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/2a9c850ed64_0829.jpg',
                'server_reply_url' => 'http://www.tongshijia.com/vote/vote_event_view/6',
                'reply_tip_info' => '请先关注朋友说微信公众号“pyshuo2014”如您已关注，请进入公众号回复“报名”即可！'
            ),
            'product_params' => array(
                'recommend' => '小宝妈',
                'products' => array(
                    array(
                        'url' => 'http://www.tongshijia.com/weshares/view/413',
                        'pic' => '/img/vote/xbm_recommend.jpg',
                        'name' => '澳柑'
                    )
                )
            )
        ),
        7 => array(
            'id' => 7,
            'initiator' => '810684',
            'sub_type' => 'Vote8',
            'title' => '晒萌宝樱花请客了!',
            'sub_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=210441366&idx=1&sn=35d156d5c3c9740cc8bbc894928bfed7#rd',
            'wx_params' => array(
                'time_line_title' => '晒萌宝樱花请客了!',
                'chat_title' => '晒萌宝樱花请客了!',
                'share_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/45ff4a8f0cb_0906.jpg',
                'desc' => '感谢亲们对樱花的支持，有你们在一起真好！让我们一起记录宝宝的美好瞬间。',
            ),
            'common_params' => array(
                'banner' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/7eb18fb79da_0906.jpg',
                'prize_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/77ba69da4f1_0906.jpg',
                'server_reply_title' => '晒萌宝樱花请客了!',
                'server_reply_img' => 'http://51daifan-images.stor.sinaapp.com/files/201509/thumb_m/77ba69da4f1_0906.jpg',
                'server_reply_url' => 'http://www.tongshijia.com/vote/vote_event_view/7',
                'reply_tip_info' => '请先关注朋友说微信公众号“pyshuo2014”如您已关注，请进入公众号回复“樱花”即可！'
            ),
            'product_params' => array(
                'recommend' => '樱花',
                'products' => array(
                    array(
                        'url' => 'http://www.tongshijia.com/weshares/view/56',
                        'pic' => '/img/imgstore/zao.jpg',
                        'name' => '天山骏枣'
                    )
                )
            )
        )
    );

}