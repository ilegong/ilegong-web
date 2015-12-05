<?php

class SharePoolProduct extends AppModel {

    public $useTable = false;

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
                    'price' => 64,
                    'channel_price' => 45,
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
        )
    );

    //产品和试吃的对应关系
    var $product_foretaste_map = array();

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
}