<?php

/**
 * 产品池
 *
 */
class ShareProductPoolController extends AppController {


    var $name = 'share_product_pool';

    var $uses = array('SharePoolProduct');

    var $components = array('ShareUtil', 'ShareAuthority');

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
        $uid = $this->currentUser['id'];
        $this->set('uid', $uid);
        $share_products = $this->SharePoolProduct->get_all_products();
        $this->set('share_products', $share_products);
    }

    /**
     * @param $share_id
     * 产品库详情页(朋友说用户分享的一个分享)
     */
    public function share_product_detail($share_id) {
        $this->set('weshare_id', $share_id);
    }

    /**
     * @param $share_id
     * 团长从产品库开启分享
     */
    public function clone_share($share_id) {
        $user_id = $this->currentUser['id'];
        if (empty($user_id)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            return;
        }
        $result = $this->ShareUtil->cloneShare($share_id, $user_id);
        if ($result['success']) {
            $this->init_share_authorize($result['shareId'], $share_id, $user_id);
        }
        echo json_encode($result);
        return;
    }

    /**
     * @param $share_id
     * ajax 获取产品池的详情
     */
    public function get_share_product_detail($share_id) {
        $this->autoRender = false;
        $share_info = $this->get_share_product_info($share_id);
        $share_info['foretaste_share_id'] = $this->SharePoolProduct->get_product_foretaste($share_id);
        echo json_encode($share_info);
        return;
    }

    /**
     * @param $share_id
     * @param $page
     * 获取产品池中产品的订单和评论数据
     * 这个里面不进行交互,只显示数据
     */
    public function get_product_orders_and_comments($share_id, $page = 1) {
        
    }

    private function get_share_product_info($share_id) {
        $key = 'pool_product_info_cache_key_' . $share_id;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $weshareM = ClassRegistry::init('Weshare');
            //get share basic info
            $weshare_info = $weshareM->find('first', array(
                'conditions' => array('id' => $share_id)
            ));
            $weshare_info = $weshare_info['Weshare'];
            $weshare_info['description'] = str_replace(array("\r\n", "\n", "\r"), '<br />', $weshare_info['description']);
            $weshare_info['images'] = array_filter(explode('|', $weshare_info['images']));
            $weshare_products = $this->ShareUtil->get_product_tag_map($share_id);
            $sharer_tags = $this->ShareUtil->get_tags($weshare_info['creator'], $weshare_info['refer_share_id']);
            $weshare_info['products'] = $weshare_products;
            $weshare_info['tags'] = $sharer_tags;
            Cache::write($key, json_encode($weshare_info));
            return $weshare_info;
        }
        return json_decode($cacheData, true);
    }

    private function init_share_authorize($share_id, $refer_share_id, $uid) {
        $weshareM = ClassRegistry::init('Weshare');
        $weshare_info = $weshareM->find('first', array(
            'conditions' => array(
                'id' => $refer_share_id
            ),
            'fields' => array('id', 'creator')
        ));
        $this->ShareAuthority->init_clone_share_from_pool_operate_config($share_id, $uid, $weshare_info['Weshare']['creator']);
    }
}