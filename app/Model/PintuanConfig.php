<?php

/**
 * 拼团数据的配置
 * User: shichaopeng
 * Date: 12/29/15
 * Time: 09:31
 */
class PintuanConfig extends AppModel {

    public $useTable = false;

    public function get_conf_data($share_id) {
        return $this->conf_data[$share_id];
    }

    public function get_product_data($conf_id) {
        return $this->pintuan_product_config[$conf_id];
    }

    var $pintuan_product_config = array(
        1 => array(
            'detail_img' => array('/static/pintuan/images/detail01.jpg', '/static/pintuan/images/detail02.jpg', '/static/pintuan/images/detail03.jpg'),
            'send_info' => '2016年1月5日24点截止报名，1月6日统一发货',
            'send_date' => '1月6日'
        ),
        2 => array(
            'detail_img' => array('/static/pintuan/images/cz_detail01.png', '/static/pintuan/images/cz_detail02.jpg', '/static/pintuan/images/cz_detail03.png', '/static/pintuan/images/cz_detail04.jpg', '/static/pintuan/images/cz_detail05.png', '/static/pintuan/images/cz_detail06.jpg'),
            'send_info' => '2016年1月11日24点截止报名，1月12日统一发货',
            'send_date' => '1月12日'
        ),
        3 => array(
            'detail_img' => array('/static/pintuan/images/xymn_detail01.jpg', '/static/pintuan/images/xymn_detail02.jpg', '/static/pintuan/images/xymn_detail03.jpg', '/static/pintuan/images/xymn_detail04.jpg', '/static/pintuan/images/xymn_detail05.jpg', '/static/pintuan/images/xymn_detail06.jpg', '/static/pintuan/images/xymn_detail07.jpg', '/static/pintuan/images/xymn_detail08.jpg', '/static/pintuan/images/xymn_detail09.jpg'),
            'send_info' => '2016年1月18日24点截止报名，1月19日统一发货',
            'send_date' => '1月19日'
        ),
        4 => array(
            'detail_img' => array('/static/pintuan/images/cm_detail01.jpg', '/static/pintuan/images/cm_detail02.gif', '/static/pintuan/images/cm_detail03.jpg', '/static/pintuan/images/cm_detail04.gif', '/static/pintuan/images/cm_detail05.gif'),
            'send_info' => '2016年1月18日24点截止报名，1月19日统一发货',
            'send_date' => '1月19日'
        ),
        5 => array(
            'detail_img' => array('/static/pintuan/images/cg_img01.gif', '/static/pintuan/images/cg_img02.jpg', '/static/pintuan/images/cg_img03.jpg', '/static/pintuan/images/cg_img04.jpg', '/static/pintuan/images/cg_img05.gif'),
            'send_info' => '3月31日20点停止拼团，支付后3天内发货',
            'send_date' => '支付后3天内发货'
        ),
        6 => array(
            'detail_img' => array('/static/pintuan/images/mg_img01.gif','/static/pintuan/images/mg_img02.jpg','/static/pintuan/images/mg_img03.gif','/static/pintuan/images/mg_img04.jpg','/static/pintuan/images/mg_img05.gif','/static/pintuan/images/mg_img06.jpg'),
            'send_info' => '3月29日（周二）截团, 3月30日（周三）发货',
            'send_date' => '3月30日（周三）发货'
        )
    );

    var $conf_data = array(
        //贵妃芒
        3161 => array(
            'share_id' => '3161',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/mg_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/mg_banner_small.jpg',
            'sharer_id' => '884183',
            'sharer_nickname' => '雪微',
            'sharer_avatar' => 'http://static.tongshijia.com/avatar/s/2015/12/31/wx_head_4220fe693ba5fd2ff5262053c29b3c01.jpg',
            'share_title' => '海南树上熟贵妃芒 现摘现发 5斤',
            'wx_title' => '［一起省4元］海南树上熟贵妃芒 5斤 84元，雪微邀你吃',
            'wx_desc' => '7、8成熟产地直采刚刚好的贵妃芒—［朋友说］',
            'promotions_title' => '购买了“［一起省6元］海南树上熟贵妃芒 5斤 84元”',
            'share_label' => '来自［雪微］的分享',
            'limit_time' => 24,
            'pid' => 6,
            'published' => 1,
            'product' => array(
                'id' => 7427,
                'normal_price' => 88,
                'pintuan_price' => 84,
                'name' => '海南树上熟贵妃芒 现摘现发 5斤'
            )
        ),
        3160 => array(
            'share_id' => '3160',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/mg_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/mg_banner_small.jpg',
            'sharer_id' => '810684',
            'sharer_nickname' => '李樱花',
            'sharer_avatar' => 'http://static.tongshijia.com/avatar/s/2015/12/31/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg',
            'share_title' => '海南树上熟贵妃芒 现摘现发 5斤',
            'wx_title' => '［一起省4元］海南树上熟贵妃芒 5斤 84元，李樱花邀你吃',
            'wx_desc' => '7、8成熟产地直采刚刚好的贵妃芒—［朋友说］',
            'promotions_title' => '购买了“［一起省6元］海南树上熟贵妃芒 5斤 84元”',
            'share_label' => '来自［李樱花］的分享',
            'limit_time' => 24,
            'pid' => 6,
            'published' => 1,
            'product' => array(
                'id' => 7426,
                'normal_price' => 88,
                'pintuan_price' => 84,
                'name' => '海南树上熟贵妃芒 现摘现发 5斤'
            )
        ),
        //盛夏不上火
        3147 => array(
            'share_id' => '3147',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cg_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cg_banner_small.jpg',
            'sharer_id' => '708029',
            'sharer_nickname' => '盛夏',
            'sharer_avatar' => 'http://static.tongshijia.com/avatar/s/2015/12/31/wx_head_e92e61d892c2ad72c0e01ec1ac136e71.jpg',
            'share_title' => '丹棱 [不知火] 无农药现摘现发5斤装',
            'wx_title' => '［一起省6元］丹棱无农药 [不知火] 5斤39.9元，盛夏邀你吃',
            'wx_desc' => '现摘现发，绿色无污染不知火丑柑—［朋友说］',
            'promotions_title' => '购买了“［一起省6元］丹棱 无农药[不知火] 5斤39.9元”',
            'share_label' => '来自［盛夏］的分享',
            'limit_time' => 24,
            'pid' => 5,
            'published' => 0,
            'product' => array(
                'id' => 7385,
                'normal_price' => 45.9,
                'pintuan_price' => 39.9,
                'name' => '丹棱 [不知火] 无农药现摘现发5斤装'
            )
        ),
        //草莓
        2074 => array(
            'share_id' => '2074',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cm_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cm_banner_small.jpg',
            'sharer_id' => '141',
            'sharer_nickname' => '杨晓光',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_f88cfd957b22b112058e340d508423a7.jpg',
            'share_title' => '昌平小汤山 现摘红颜草莓  4斤装',
            'wx_title' => '［一起省16元］昌平现摘红颜草莓4斤142元，杨晓光邀你吃',
            'wx_desc' => '吃过就会竖起大拇指的新鲜草莓—［朋友说］',
            'promotions_title' => '购买了“［一起省16元］昌平现摘红颜草莓4斤142元”',
            'share_label' => '来自［杨晓光］的分享',
            'limit_time' => 24,
            'pid' => 4,
            'published' => 1,
            'product' => array(
                'id' => 4354,
                'normal_price' => 158,
                'pintuan_price' => 142,
                'name' => '现摘红颜草莓  4斤装'
            )
        ),
        2073 => array(
            'share_id' => '2073',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cm_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cm_banner_small.jpg',
            'sharer_id' => '801447',
            'sharer_nickname' => '平凡的世界',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/8014471448421852.png',
            'share_title' => '昌平小汤山 现摘红颜草莓  4斤装',
            'wx_title' => '［一起省16元］昌平现摘红颜草莓4斤142元，平凡的世界邀你吃',
            'wx_desc' => '吃过就会竖起大拇指的新鲜草莓—［朋友说］',
            'promotions_title' => '购买了“［一起省16元］昌平现摘红颜草莓4斤142元”',
            'share_label' => '来自［平凡的世界］的分享',
            'limit_time' => 24,
            'pid' => 4,
            'published' => 1,
            'product' => array(
                'id' => 4352,
                'normal_price' => 158,
                'pintuan_price' => 142,
                'name' => '现摘红颜草莓  4斤装'
            )
        ),
        2062 => array(
            'share_id' => '2062',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cm_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cm_banner_small.jpg',
            'sharer_id' => '810684',
            'sharer_nickname' => '李樱花',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg',
            'share_title' => '昌平小汤山 现摘红颜草莓  4斤装',
            'wx_title' => '［一起省16元］昌平现摘红颜草莓4斤142元，李樱花邀你吃',
            'wx_desc' => '吃过就会竖起大拇指的新鲜草莓—［朋友说］',
            'promotions_title' => '购买了“［一起省16元］昌平现摘红颜草莓4斤142元”',
            'share_label' => '来自［李樱花］的分享',
            'limit_time' => 24,
            'pid' => 4,
            'published' => 1,
            'product' => array(
                'id' => 4321,
                'normal_price' => 158,
                'pintuan_price' => 142,
                'name' => '现摘红颜草莓  4斤装'
            )
        ),
        2061 => array(
            'share_id' => '2061',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cm_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cm_banner_small.jpg',
            'sharer_id' => '802852',
            'sharer_nickname' => '愣愣',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/6333451445516765.png',
            'share_title' => '昌平小汤山 现摘红颜草莓  4斤装',
            'wx_title' => '［一起省16元］昌平现摘红颜草莓4斤142元，愣愣邀你吃',
            'wx_desc' => '吃过就会竖起大拇指的新鲜草莓—［朋友说］',
            'promotions_title' => '购买了“［一起省16元］昌平现摘红颜草莓4斤142元”',
            'share_label' => '来自［愣愣］的分享',
            'limit_time' => 24,
            'pid' => 4,
            'published' => 1,
            'product' => array(
                'id' => 4320,
                'normal_price' => 158,
                'pintuan_price' => 142,
                'name' => '现摘红颜草莓  4斤装'
            )
        ),
        2051 => array(
            'share_id' => '2051',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/xymn_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/xymn_banner_small.jpg',
            'sharer_id' => '141',
            'sharer_nickname' => '杨晓光',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_f88cfd957b22b112058e340d508423a7.jpg',
            'share_title' => '［一起省13元］西域美农坚果礼包1800g165元，杨晓光邀你吃',
            'wx_title' => '［一起省13元］西域美农坚果礼包1800g165元',
            'wx_desc' => '让您个性十足，倍有面子的礼包—［朋友说］',
            'promotions_title' => '报名了“【一起省13元】西域美农 年货坚果大礼包8袋装1800g”',
            'share_label' => '来自［杨晓光］的分享',
            'limit_time' => 24,
            'pid' => 3,
            'published' => 1,
            'product' => array(
                'id' => 4303,
                'normal_price' => 178,
                'pintuan_price' => 165,
                'name' => '年货坚果大礼包8袋装1800g'
            )
        ),
        2047 => array(
            'share_id' => '2047',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/xymn_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/xymn_banner_small.jpg',
            'sharer_id' => '802852',
            'sharer_nickname' => '愣愣',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/6333451445516765.png',
            'share_title' => '西域美农 年货坚果大礼包8袋装1800g',
            'wx_title' => '［一起省13元］西域美农坚果礼包1800g165元，愣愣邀你吃',
            'wx_desc' => '品质棒棒嗒，好吃的要分享给大家一起吃--朋友说',
            'promotions_title' => '报名了“【一起省13元】西域美农 年货坚果大礼包8袋装1800g”',
            'share_label' => '来自［愣愣］的分享',
            'limit_time' => 24,
            'pid' => 3,
            'published' => 1,
            'product' => array(
                'id' => 4290,
                'normal_price' => 178,
                'pintuan_price' => 165,
                'name' => '年货坚果大礼包8袋装1800g'
            )
        ),
        //橙子
        1966 => array(
            'share_id' => '1966',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner_small.jpg',
            'sharer_id' => '884659',
            'sharer_nickname' => '微儿',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/237711451622653.png',
            'share_title' => '麻阳冰糖娃娃橙 12个装 1kg左右 包邮 限北京',
            'wx_title' => '［一起省4元］麻阳冰糖娃娃橙 12个12.8元包邮，微儿邀你吃',
            'wx_desc' => '虽然我很小，也很丑，但我是纯天然的—［朋友说］',
            'promotions_title' => '购买了“［一起省4元］麻阳冰糖娃娃橙12个12.8元包邮”',
            'share_label' => '来自［微儿］的分享',
            'published' => 0,
            'limit_time' => 24,
            'pid' => 2,
            'product' => array(
                'id' => 4146,
                'normal_price' => 16.8,
                'pintuan_price' => 12.8,
                'name' => '麻阳冰糖娃娃橙 12个装 1kg左右'
            )
        ),
        1965 => array(
            'share_id' => '1965',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner_small.jpg',
            'sharer_id' => '884659',
            'sharer_nickname' => '晚凉~文霞',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_223e3853f9605168c254c1256767f18f.jpg',
            'share_title' => '麻阳冰糖娃娃橙 12个装 1kg左右 包邮 限北京',
            'wx_title' => '［一起省4元］麻阳冰糖娃娃橙 12个12.8元包邮，晚凉~文霞邀你吃',
            'wx_desc' => '虽然我很小，也很丑，但我是纯天然的—［朋友说］',
            'promotions_title' => '购买了“［一起省4元］麻阳冰糖娃娃橙12个12.8元包邮”',
            'share_label' => '来自［晚凉~文霞］的分享',
            'limit_time' => 24,
            'pid' => 2,
            'published' => 0,
            'product' => array(
                'id' => 4145,
                'normal_price' => 16.8,
                'pintuan_price' => 12.8,
                'name' => '麻阳冰糖娃娃橙 12个装 1kg左右'
            )
        ),
        1951 => array(
            'share_id' => '1951',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner_small.jpg',
            'sharer_id' => '810684',
            'sharer_nickname' => '李樱花',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg',
            'share_title' => '麻阳冰糖娃娃橙 12个装 1kg左右 包邮 限北京',
            'wx_title' => '［一起省4元］麻阳冰糖娃娃橙 12个12.8元包邮，李樱花邀你吃',
            'wx_desc' => '虽然我很小，也很丑，但我是纯天然的—［朋友说］',
            'promotions_title' => '购买了“［一起省4元］麻阳冰糖娃娃橙12个12.8元包邮”',
            'share_label' => '来自［李樱花］的分享',
            'limit_time' => 24,
            'pid' => 2,
            'published' => 0,
            'product' => array(
                'id' => 4128,
                'normal_price' => 16.8,
                'pintuan_price' => 12.8,
                'name' => '麻阳冰糖娃娃橙 12个装 1kg左右'
            )
        ),
        1950 => array(
            'share_id' => '1950',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner_small.jpg',
            'sharer_id' => '878825',
            'sharer_nickname' => '片片妈',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_52d08a7c8bb6e9611307b6c4858ca38c.jpg',
            'share_title' => '麻阳冰糖娃娃橙 12个装 1kg左右 包邮 限北京',
            'wx_title' => '［一起省4元］麻阳冰糖娃娃橙 12个12.8元包邮，片片妈邀你吃',
            'wx_desc' => '虽然我很小，也很丑，但我是纯天然的—［朋友说］',
            'promotions_title' => '购买了“［一起省4元］麻阳冰糖娃娃橙12个12.8元包邮”',
            'share_label' => '来自［片片妈］的分享',
            'limit_time' => 24,
            'pid' => 2,
            'published' => 0,
            'product' => array(
                'id' => 4127,
                'normal_price' => 16.8,
                'pintuan_price' => 12.8,
                'name' => '麻阳冰糖娃娃橙 12个装 1kg左右'
            )
        ),
        1949 => array(
            'share_id' => '1949',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner_small.jpg',
            'sharer_id' => '811917',
            'sharer_nickname' => '小宝妈',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg',
            'share_title' => '麻阳冰糖娃娃橙 12个装 1kg左右 包邮 限北京',
            'wx_title' => '［一起省4元］麻阳冰糖娃娃橙 12个12.8元包邮，小宝妈邀你吃',
            'wx_desc' => '虽然我很小，也很丑，但我是纯天然的—［朋友说］',
            'promotions_title' => '购买了“［一起省4元］麻阳冰糖娃娃橙12个12.8元包邮”',
            'share_label' => '来自［小宝妈］的分享',
            'limit_time' => 24,
            'pid' => 2,
            'published' => 0,
            'product' => array(
                'id' => 4126,
                'normal_price' => 16.8,
                'pintuan_price' => 12.8,
                'name' => '麻阳冰糖娃娃橙 12个装 1kg左右'
            )
        ),
        1947 => array(
            'share_id' => '1947',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner_small.jpg',
            'sharer_id' => '141',
            'sharer_nickname' => '杨晓光',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_f88cfd957b22b112058e340d508423a7.jpg',
            'share_title' => '麻阳冰糖娃娃橙 12个装 1kg左右 包邮 限北京',
            'wx_title' => '［一起省4元］麻阳冰糖娃娃橙 12个12.8元包邮，杨晓光邀你吃',
            'wx_desc' => '虽然我很小，也很丑，但我是纯天然的—［朋友说］',
            'promotions_title' => '购买了“［一起省4元］麻阳冰糖娃娃橙12个12.8元包邮”',
            'share_label' => '来自［杨晓光］的分享',
            'limit_time' => 24,
            'pid' => 2,
            'published' => 0,
            'product' => array(
                'id' => 4115,
                'normal_price' => 16.8,
                'pintuan_price' => 12.8,
                'name' => '麻阳冰糖娃娃橙 12个装 1kg左右'
            )
        ),
        1941 => array(
            'share_id' => '1941',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/cz_banner_small.jpg',
            'sharer_id' => '802852',
            'sharer_nickname' => '愣愣',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/6333451445516765.png',
            'share_title' => '麻阳冰糖娃娃橙 12个装 1kg左右 包邮 限北京',
            'wx_title' => '［一起省4元］麻阳冰糖娃娃橙 12个12.8元包邮，愣愣邀你吃',
            'wx_desc' => '虽然我很小，也很丑，但我是纯天然的—［朋友说］',
            'promotions_title' => '购买了“［一起省4元］麻阳冰糖娃娃橙12个12.8元包邮”',
            'share_label' => '来自［愣愣］的分享',
            'limit_time' => 24,
            'pid' => 2,
            'published' => 0,
            'product' => array(
                'id' => 4115,
                'normal_price' => 0.02,
                'pintuan_price' => 0.01,
                'name' => '麻阳冰糖娃娃橙 12个装 1kg左右'
            )
        ),
        //火龙果
        79 => array(
            'share_id' => '1814',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner_small.jpg',
            'sharer_id' => '633345',
            'sharer_nickname' => '愣愣',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/6333451445516765.png',
            'share_title' => '越南红心火龙果 4个装 4.5斤左右',
            'wx_title' => '[和你一起立省5元] 越南红心火龙果 4个49元，愣愣邀你吃',
            'wx_desc' => '品质棒棒嗒，好吃的要分享给大家一起吃--朋友说',
            'promotions_title' => '报名了“【一起省5元】越南红心火龙果4个49元”',
            'share_label' => '来自［愣愣］的分享',
            'limit_time' => 24,
            'pid' => 1,
            'published' => 0,
            'product' => array(
                'id' => 3845,
                'normal_price' => 0.02,
                'pintuan_price' => 0.01,
                'name' => '越南红心火龙果 4个装'
            )
        ),
        1825 => array(
            'share_id' => '1825',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner_small.jpg',
            'sharer_id' => '141',
            'sharer_nickname' => '杨晓光',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_f88cfd957b22b112058e340d508423a7.jpg',
            'share_title' => '越南红心火龙果 4个装 4.5斤左右',
            'wx_title' => '【一起省5元】 越南红心火龙果 4个49元，杨晓光邀你吃',
            'wx_desc' => '品质棒棒嗒，好吃的要分享给大家一起吃--朋友说',
            'promotions_title' => '报名了“【一起省5元】越南红心火龙果4个49元”',
            'share_label' => '来自［杨晓光］的分享',
            'limit_time' => 24,
            'pid' => 1,
            'published' => 0,
            'product' => array(
                'id' => 3867,
                'normal_price' => 54,
                'pintuan_price' => 49,
                'name' => '越南红心火龙果 4个装'
            )
        ),
        1826 => array(
            'share_id' => '1826',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner_small.jpg',
            'sharer_id' => '806889',
            'sharer_nickname' => '鲲鲲',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_42fcd0bc876709a6fe3df32826b8d1fa.jpg',
            'share_title' => '越南红心火龙果 4个装 4.5斤左右',
            'wx_title' => '【一起省5元】 越南红心火龙果 4个49元，鲲鲲邀你吃',
            'wx_desc' => '品质棒棒嗒，好吃的要分享给大家一起吃--朋友说',
            'promotions_title' => '报名了“【一起省5元】越南红心火龙果4个49元”',
            'share_label' => '来自［鲲鲲］的分享',
            'limit_time' => 24,
            'pid' => 1,
            'published' => 0,
            'product' => array(
                'id' => 3868,
                'normal_price' => 54,
                'pintuan_price' => 49,
                'name' => '越南红心火龙果 4个装'
            )
        ),
        1827 => array(
            'share_id' => '1827',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner_small.jpg',
            'sharer_id' => '811917',
            'sharer_nickname' => '小宝妈',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg',
            'share_title' => '越南红心火龙果 4个装 4.5斤左右',
            'wx_title' => '【一起省5元】 越南红心火龙果 4个49元，小宝妈邀你吃',
            'wx_desc' => '品质棒棒嗒，好吃的要分享给大家一起吃--朋友说',
            'promotions_title' => '报名了“【一起省5元】越南红心火龙果4个49元”',
            'share_label' => '来自［小宝妈］的分享',
            'limit_time' => 24,
            'pid' => 1,
            'published' => 0,
            'product' => array(
                'id' => 3869,
                'normal_price' => 54,
                'pintuan_price' => 49,
                'name' => '越南红心火龙果 4个装'
            )
        ),
        1828 => array(
            'share_id' => '1828',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner_small.jpg',
            'sharer_id' => '810684',
            'sharer_nickname' => '李樱花',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg',
            'share_title' => '越南红心火龙果 4个装 4.5斤左右',
            'wx_title' => '[和你一起立省5元] 越南红心火龙果 4个49元，李樱花邀你吃',
            'wx_desc' => '品质棒棒嗒，好吃的要分享给大家一起吃--朋友说',
            'promotions_title' => '报名了“【一起省5元】越南红心火龙果4个49元”',
            'share_label' => '来自［李樱花］的分享',
            'limit_time' => 24,
            'pid' => 1,
            'published' => 0,
            'product' => array(
                'id' => 3870,
                'normal_price' => 54,
                'pintuan_price' => 49,
                'name' => '越南红心火龙果 4个装'
            )
        ),
        1831 => array(
            'share_id' => '1831',
            'banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner.jpg',
            'small_banner_img' => 'http://static.tongshijia.com/static/pintuan/images/banner_small.jpg',
            'sharer_id' => '878825',
            'sharer_nickname' => '片片妈',
            'sharer_avatar' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_52d08a7c8bb6e9611307b6c4858ca38c.jpg',
            'share_title' => '越南红心火龙果 4个装 4.5斤左右',
            'wx_title' => '[和你一起立省5元] 越南红心火龙果 4个49元，片片妈邀你吃',
            'wx_desc' => '品质棒棒嗒，好吃的要分享给大家一起吃--朋友说',
            'promotions_title' => '报名了“【一起省5元】越南红心火龙果4个49元”',
            'share_label' => '来自［片片妈］的分享',
            'limit_time' => 24,
            'pid' => 1,
            'published' => 0,
            'product' => array(
                'id' => 3875,
                'normal_price' => 54,
                'pintuan_price' => 49,
                'name' => '越南红心火龙果 4个装'
            )
        ),
    );
}