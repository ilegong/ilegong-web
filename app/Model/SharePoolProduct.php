<?php

class SharePoolProduct extends AppModel {

    public $useTable = false;

    //产品池所有产品
    var $products = array(
        '1268' => array(
            'share_id' => 1268,
            'foretaste_share_id' => 100,
            'share_name' => '麻阳冰糖橙（包邮预售）----不打药，不防腐，不上蜡，守护内心的“橙”实',
            'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/2f2ae8653ee_1123.jpg',
            'price' => '68',
            'commission_percent' => 10,
            'brand_img' => '',
            'brand_name' => '',
            'show_brand' => false,
            'brand_custom_service' => 633345,
            'published' => 1
        ),
    );

    //产品和试吃的对应关系
    var $product_foretaste_map = array();

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