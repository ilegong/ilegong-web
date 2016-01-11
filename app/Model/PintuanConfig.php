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
    );

    var $conf_data = array(
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
            'published' => 1,
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
            'published' => 1,
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
            'published' => 1,
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
            'published' => 1,
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
            'published' => 1,
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
            'published' => 1,
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
            'published' => 1,
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
            'published' => 1,
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
            'published' => 1,
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
            'published' => 1,
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
            'published' => 1,
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
            'published' => 1,
            'product' => array(
                'id' => 3875,
                'normal_price' => 54,
                'pintuan_price' => 49,
                'name' => '越南红心火龙果 4个装'
            )
        ),
    );
}