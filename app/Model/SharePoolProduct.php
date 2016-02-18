<?php

class SharePoolProduct extends AppModel {

    public $useTable = false;

    /**
     * @param $share_id
     * @return array
     */
    public function get_product_by_share_id($share_id) {
        $find_product = null;
        foreach ($this->products as $product) {
            if ($product['share_id'] == $share_id) {
                $find_product = $product;
                break;
            }
        }
        return $find_product;
    }

    /**
     * @return array
     * 获取产品池中所有产品
     */
    public function get_all_products() {
        $share_products = array_filter($this->products, function ($item) {
            return $item['published'] == PUBLISH_YES;
        });
        return $share_products;
    }

    /**
     * @param $share_id
     * @return mixed
     * 获取产品池中产品的试吃id
     */
    public function get_product_buy_config($share_id) {
        return $this->product_buy_map[$share_id];
    }

    /**
     * @param $share_id
     * @return mixed
     * 获取团长从产品池中分享出去所有的分享
     */
    public function get_fork_share_ids($share_id) {
        $weshareM = ClassRegistry::init('Weshare');
        $shares = $weshareM->find('all', array(
            'conditions' => array(
                'refer_share_id' => $share_id,
                'not' => array('type' => POOL_SHARE_TYPE)
            ),
            'fields' => array('id', 'creator', 'status', 'type'),
            'limit' => 100
        ));
        return $shares;
    }

    //产品和试吃的对应关系
    var $product_buy_map = array(
        '1411' => array(
            //'try' => 56,
            'buy' => 1463
        ),
        '1432' => array(
            'buy' => 1464
        ),
        '1433' => array(
            'buy' => 1466
        ),
        '1430' => array(
            'buy' => 1467
        ),
        '1438' => array(
            'buy' => 1468
        ),
        '1445' => array(
            'buy' => 1469
        ),
        '1447' => array(
            'buy' => 1471
        ),
//        '1448' => array(
//            'buy' => 1473
//        ),
        '1449' => array(
            'buy' => 1474
        ),
        '1450' => array(
            'buy' => 1475
        ),
//        '1703' => array(
//            'buy' => 1704
//        ),
        '1492' => array(
            'buy' => 1752
        ),
        '1783' => array(
            'buy' => 2018
        )
    );

    //产品池所有产品
    var $products = array(
        array(
            'share_id' => 2022,
            'share_name' => '现摘发货茂谷柑',
            'share_img' => 'http://static.tongshijia.com/images/1ce8a2d2-b91c-11e5-a8c5-00163e001f59.jpg',
            'brand_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_s/ef5896e5883_1204.jpg',
            'brand_name' => '娟子',
            'show_brand' => true,
            'brand_custom_service' => 801709,
            'published' => 1,
            'products' => array(
                '4252' => array(
                    'price' => 119,
                    'channel_price' => 110,
                )
            )
        ),
        array(
            'share_id' => 2098,
            'share_name' => '巨好吃的鲜8纯芝麻酱【全国包邮】',
            'share_img' => 'http://static.tongshijia.com/images/633dae9e-bb60-11e5-94c2-00163e001f59.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_b820324df8db0ad6606b6a613a925c98.jpg',
            'brand_name' => '酒香婷',
            'show_brand' => true,
            'brand_custom_service' => 892813,
            'published' => 0,
            'products' => array(
                '4397' => array(
                    'price' => 29,
                    'channel_price' => 25,
                ),
                '4398' => array(
                    'price' => 48,
                    'channel_price' => 43,
                )
            )
        ),
        array(
            'share_id' => 1437,
            'share_name' => '鲜活银耳【全国顺丰包邮】',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201601/thumb_m/a1455b6560a_0104.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_52d08a7c8bb6e9611307b6c4858ca38c.jpg',
            'brand_name' => '片片妈',
            'show_brand' => true,
            'brand_custom_service' => 12376,
            'published' => 1,
            'products' => array(
                '2916' => array(
                    'price' => 108,
                    'channel_price' => 98,
                )
            )
        ),
        array(
            'share_id' => 2050,
            'share_name' => '有机纯正红薯粉条 无任何添加剂',
            'share_img' => 'http://static.tongshijia.com/images/e9770c56-b9eb-11e5-a8c5-00163e001f59.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_52d08a7c8bb6e9611307b6c4858ca38c.jpg',
            'brand_name' => '片片妈',
            'show_brand' => true,
            'brand_custom_service' => 12376,
            'published' => 1,
            'products' => array(
                '4301' => array(
                    'price' => 80,
                    'channel_price' => 76,
                ),
                '4302' => array(
                    'price' => 95,
                    'channel_price' => 91,
                )
            )
        ),
        array(
            'share_id' => 1917,
            'share_name' => '宝宝的山楂条  添加胡萝卜和苹果',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201601/thumb_m/4aee3d3205f_0106.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_52d08a7c8bb6e9611307b6c4858ca38c.jpg',
            'brand_name' => '片片妈',
            'show_brand' => true,
            'brand_custom_service' => 878825,
            'published' => 1,
            'products' => array(
                '4072' => array(
                    'price' => 65,
                    'channel_price' => 55,
                ),
                '4073' => array(
                    'price' => 75,
                    'channel_price' => 65,
                ),
            )
        ),
        array(
            'share_id' => 1894,
            'share_name' => '宝宝和老人特别喜欢的花牛苹果',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201601/thumb_m/9fe70d2d40a_0104.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_52d08a7c8bb6e9611307b6c4858ca38c.jpg',
            'brand_name' => '片片妈',
            'show_brand' => true,
            'brand_custom_service' => 711480,
            'published' => 1,
            'products' => array(
                '4016' => array(
                    'price' => 68,
                    'channel_price' => 60,
                )
            )
        ),
        array(
            'share_id' => 1703,
            'share_name' => '永兴冰糖橙【限北京】',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201601/thumb_m/83862b90237_0104.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_f88cfd957b22b112058e340d508423a7.jpg',
            'brand_name' => '晓光',
            'show_brand' => true,
            'brand_custom_service' => 887026,
            'published' => 0,
            'products' => array(
                '3572' => array(
                    'price' => 85,
                    'channel_price' => 65,
                )
            )
        ),
        array(
            'share_id' => 1783,
            'share_name' => '忆味蕾，富平霜降柿饼重磅归来！',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/bf24186cec7_1229.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_f88cfd957b22b112058e340d508423a7.jpg',
            'brand_name' => '朱晓宇',
            'show_brand' => true,
            'brand_custom_service' => 433224,
            'published' => 0,
            'products' => array(
                '3758' => array(
                    'price' => 28,
                    'channel_price' => 21,
                ),
                '3759' => array(
                    'price' => 80,
                    'channel_price' => 63,
                ),
                '3760' => array(
                    'price' => 130,
                    'channel_price' => 105
                )
            )
        ),
        array(
            'share_id' => 1884,
            'share_name' => '富硒砂糖橘',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201601/thumb_m/1802770e8f3_0104.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_e92e61d892c2ad72c0e01ec1ac136e71.jpg',
            'brand_name' => '盛夏',
            'show_brand' => true,
            'brand_custom_service' => 708029,
            'published' => 0,
            'products' => array(
                '3998' => array(
                    'price' => 88,
                    'channel_price' => 70,
                )
            )
        ),
        array(
            'share_id' => 1411,
            'share_name' => '雾岭山楂条5袋装',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/83a26e7f545_1204.jpg',
            'brand_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_s/ef5896e5883_1204.jpg',
            'brand_name' => '天天踏歌',
            'show_brand' => true,
            'brand_custom_service' => 711503,
            'published' => 1,
            'products' => array(
                '2869' => array(
                    'price' => 12.8,
                    'channel_price' => 9,
                )
            )
        ),
        array(
            'share_id' => 1492,
            'share_name' => '小火团贡玉米 规格12棒/箱',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/b17c9f68199_1208.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_4c2cec11eabe3762984f808c5ceafdbd.jpg',
            'brand_name' => '李翊鸥',
            'show_brand' => true,
            'brand_custom_service' => 886456,
            'published' => 1,
            'products' => array(
                '3069' => array(
                    'price' => 68,
                    'channel_price' => 58,
                ),
                '4743' => array(
                    'price' => 83,
                    'channel_price' => 73,
                ),
            )
        ),
        array(
            'share_id' => 1600,
            'share_name' => '好吃的真空低温油浴果蔬套装（黄秋葵+香菇+什锦果蔬）',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/c9c10cbd197_1215.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_7db41874b00ec17af715b72adf87768a.jpg',
            'brand_name' => '赵宇',
            'show_brand' => true,
            'brand_custom_service' => 810688,
            'published' => 0,
            'products' => array(
                '3334' => array(
                    'price' => 59.9,
                    'channel_price' => 46,
                ),

            )
        ),
        array(
            'share_id' => 1607,
            'share_name' => '泉林本色 180g卷筒纸 5提共50卷套装',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/4eef34e2ed7_1216.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_bb0fe3108e30628b6766530390b7f1e8.jpg',
            'brand_name' => '馨聪',
            'show_brand' => true,
            'brand_custom_service' => 872024,
            'published' => 0,
            'products' => array(
                '3355' => array(
                    'price' => 150,
                    'channel_price' => 135,
                ),

            )
        ),
        array(
            'share_id' => 1449,
            'share_name' => '俄罗斯紫皮糖',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/50ee44b0bf0_1205.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_d742f4391e472ca6a24c58d96be17aca.jpg',
            'brand_name' => '微儿',
            'show_brand' => true,
            'brand_custom_service' => 23771,
            'published' => 1,
            'products' => array(
                '2963' => array(
                    'price' => 78,
                    'channel_price' => 70,
                ),

            )
        ),
        array(
            'share_id' => 1432,
            'share_name' => '越南黑虎虾仁 【纯野生虾仁】',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/8950ca1e7b1_1205.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg',
            'brand_name' => '小宝妈',
            'show_brand' => true,
            'brand_custom_service' => 811917,
            'published' => 1,
            'products' => array(
                '2903' => array(
                    'price' => 150,
                    'channel_price' => 130,
                )
            )
        ),
        array(
            'share_id' => 1430,
            'share_name' => '口口相传的艳艳山药',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/0a8c5657319_1205.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/123761447121370.png',
            'brand_name' => '张文艳',
            'show_brand' => true,
            'brand_custom_service' => 12376,
            'published' => 0,
            'products' => array(
                '2898' => array(
                    'price' => 85,
                    'channel_price' => 68,
                )
            )
        ),
        array(
            'share_id' => 1450,
            'share_name' => '超值新鲜正宗内蒙古中式羔羊排块，箱门爽口、口齿留香！',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/187724807dc_1205.jpg',
            'brand_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/9e077f58c06_1125.jpg',
            'brand_name' => '吃好网',
            'show_brand' => true,
            'brand_custom_service' => 884103,
            'published' => 0,
            'products' => array(
                '2964' => array(
                    'price' => 120,
                    'channel_price' => 110,
                ),

            )
        ),
        array(
            'share_id' => 1447,
            'share_name' => '第一抗癌食品窖藏红薯、紫薯【限北京】',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/206035c42ce_1205.jpg',
            'brand_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/319793d271b_0828.jpg',
            'brand_name' => '大美',
            'show_brand' => true,
            'brand_custom_service' => 842908,
            'published' => 0,
            'products' => array(
                '2950' => array(
                    'price' => 40,//红薯家庭装
                    'channel_price' => 37,
                ),
                '2953' => array(
                    'price' => 70,//紫薯家庭
                    'channel_price' => 65,
                ),
                '2955' => array(
                    'price' => 26,//迷你小红薯
                    'channel_price' => 24,
                ),
                '2956' => array(
                    'price' => 68, //精品红薯
                    'channel_price' => 63,
                ),
                '2957' => array(
                    'price' => 98, //精品紫薯
                    'channel_price' => 91,
                ),
            )
        ),
//        array(
//            'share_id' => 1415,
//            'share_name' => '测试商品',
//            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/83a26e7f545_1204.jpg',
//            'brand_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_s/ef5896e5883_1204.jpg',
//            'brand_name' => '天天踏歌',
//            'show_brand' => true,
//            'brand_custom_service' => 559795,
//            'published' => 0,
//            'products' => array(
//                '2880' => array(
//                    'price' => 1,
//                    'channel_price' => 0.5,
//                )
//            )
//        ),
        array(
            'share_id' => 1433,
            'share_name' => '德庆贡柑，专供陛下娘娘^_^',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/bdd78834f19_1205.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0e8ff635498de280dd3193826d837ee5.jpg',
            'brand_name' => '李明',
            'show_brand' => true,
            'brand_custom_service' => 6783,
            'published' => 0,
            'products' => array(
                '2904' => array(
                    'price' => 46,
                    'channel_price' => 40,
                )
            )
        ),
        array(
            'share_id' => 1438,
            'share_name' => '那那家五常稻花香米',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/15844587ff8_1205.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_db53c030cbe19145428f0d5ca58b9562.jpg',
            'brand_name' => '那那',
            'show_brand' => true,
            'brand_custom_service' => 812111,
            'published' => 1,
            'products' => array(
                '2920' => array(
                    'price' => 166,
                    'channel_price' => 150,
                )
            )
        ),
        array(
            'share_id' => 1445,
            'share_name' => '怀柔散养老杨家黑猪肉',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/a093ee98c6c_1205.jpg',
            'brand_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_s/ef5896e5883_1204.jpg',
            'brand_name' => '老杨',
            'show_brand' => true,
            'brand_custom_service' => 711503,
            'published' => 0,
            'products' => array(
                '2942' => array(
                    'price' => 36.1,//后尖
                    'channel_price' => 29,
                ),
                '2943' => array(
                    'price' => 36.1,//前尖
                    'channel_price' => 29,
                ),
                '2944' => array(
                    'price' => 71.2,//肋排
                    'channel_price' => 59,
                ),
                '2945' => array(
                    'price' => 36.1, //猪腔骨
                    'channel_price' => 29,
                ),
                '2946' => array(
                    'price' => 42.7, //五花肉
                    'channel_price' => 39,
                ),
                '2947' => array(
                    'price' => 71.2, //纯里脊
                    'channel_price' => 59,
                )
            )
        ),
        array(
            'share_id' => 1448,
            'share_name' => '有机翠香猕猴桃',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/c899b3bbcbc_1205.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_2d0cd7f75b366ae4ccb40cc380351574.jpg',
            'brand_name' => '赵静',
            'show_brand' => true,
            'brand_custom_service' => 867250,
            'published' => 0,
            'products' => array(
                '2958' => array(
                    'price' => 95,
                    'channel_price' => 88,
                ),
                '2959' => array(
                    'price' => 180,
                    'channel_price' => 166,
                ),
            )
        ),
        array(
            'share_id' => 1489,
            'share_name' => '黄山头茬野生冬笋',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/f88a0295cd9_1208.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_3d6ae69222c0363d52c96236175e1ec2.jpg',
            'brand_name' => '刘强',
            'show_brand' => true,
            'brand_custom_service' => 879158,
            'published' => 0,
            'products' => array(
                '3064' => array(
                    'price' => 158,
                    'channel_price' => 125,
                ),

            )
        ),
    );

}