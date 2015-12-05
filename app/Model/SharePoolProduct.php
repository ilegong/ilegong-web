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
    public function get_product_foretaste($share_id) {
        return $this->product_foretaste_map[$share_id];
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
                'refer_share_id' => $share_id
            ),
            'fields' => array('id', 'creator', 'status'),
            'limit' => 100
        ));
        return $shares;
    }

    //产品和试吃的对应关系
    var $product_foretaste_map = array();

    //产品池所有产品
    var $products = array(
        0 => array(
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
        1 => array(
            'share_id' => 1415,
            'share_name' => '测试商品',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/83a26e7f545_1204.jpg',
            'brand_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_s/ef5896e5883_1204.jpg',
            'brand_name' => '天天踏歌',
            'show_brand' => true,
            'brand_custom_service' => 559795,
            'published' => 0,
            'products' => array(
                '2880' => array(
                    'price' => 1,
                    'channel_price' => 0.5,
                )
            )
        ),
        2 => array(
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
        3 => array(
            'share_id' => 1433,
            'share_name' => '德庆贡柑，专供陛下娘娘^_^',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/bdd78834f19_1205.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0e8ff635498de280dd3193826d837ee5.jpg',
            'brand_name' => '李明',
            'show_brand' => true,
            'brand_custom_service' => 6783,
            'published' => 1,
            'products' => array(
                '2904' => array(
                    'price' => 46,
                    'channel_price' => 38,
                )
            )
        ),
        4 => array(
            'share_id' => 1430,
            'share_name' => '口口相传的艳艳山药',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/0a8c5657319_1205.jpg',
            'brand_img' => 'http://51daifan-avatar.stor.sinaapp.com/123761447121370.png',
            'brand_name' => '张文艳',
            'show_brand' => true,
            'brand_custom_service' => 12376,
            'published' => 1,
            'products' => array(
                '2898' => array(
                    'price' => 85,
                    'channel_price' => 68,
                )
            )
        ),
        5 => array(
            'share_id' => 1437,
            'share_name' => '鲜活银耳【全国顺丰包邮】',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/53d920db87f_1205.jpg',
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
        6 => array(
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
//        7 => array(
//            'share_id' => 22,
//            'share_name' => '雾岭山楂条5袋装',
//            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/83a26e7f545_1204.jpg',
//            'brand_img' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_s/ef5896e5883_1204.jpg',
//            'brand_name' => '天天踏歌',
//            'show_brand' => true,
//            'brand_custom_service' => 6333345,
//            'published' => 1,
//            'products' => array(
//                '31' => array(
//                    'price' => 12.8,
//                    'channel_price' => 9,
//                ),
//                '128' => array(
//                    'price' => 12.8,
//                    'channel_price' => 9,
//                ),
//            )
//        ),
    );

}