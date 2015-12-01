<?php

/**
 * 产品池
 *
 */
class ShareProductPoolController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    /**
     *
     * 产品库首页
     */
    public function share_products_index() {
        $this->layout = null;
        $share_products = $this->get_share_products();
        $this->set('share_products', $share_products);
    }

    /**
     * @param $share_id
     * 产品库详情页(朋友说用户分享的一个分享)
     */
    public function share_product_detail($share_id) {

    }

    /**
     * @param $share_id
     * ajax 获取产品池的详情
     */
    public function get_share_product_detail($share_id) {
        $this->autoRender = false;

    }


    private function get_share_product_info($share_id){

        $weshareM = ClassRegistry::init('Weshare');

    }


    /**
     * @return array
     */
    private function get_share_products() {
        $share_products = array(
            '1268' => array(
                'share_id' => 1268,
                'share_name' => '麻阳冰糖橙（包邮预售）----不打药，不防腐，不上蜡，守护内心的“橙”实',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/2f2ae8653ee_1123.jpg',
                'retail_price' => '68',
                'trade_price' => '78'
            ),
        );
        return $share_products;
    }


}