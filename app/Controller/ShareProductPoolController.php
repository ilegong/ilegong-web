<?php

/**
 * 产品池
 *
 */
class ShareProductPoolController extends AppController
{


    var $name = 'share_product_pool';

    var $uses = array('SharePoolProduct', 'Weshare');

    var $components = array('ShareUtil', 'ShareAuthority');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = 'weshare';
    }

    /**
     *
     * 产品库首页
     */
    public function share_products_index($category = 0)
    {
        $this->layout = 'weshare';
        $uid = $this->currentUser['id'];
        $is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if (!$is_proxy) {
            $this->redirect('/weshares/index.html');
        }
        $categories = $this->SharePoolProduct->get_pool_product_categories();
        if ($category == 0) {
            $category = $categories[0]['id'];
        }
        $this->set('category', $category);
        $this->set('categories', $categories);
        $products = $this->SharePoolProduct->get_all_pool_products($category);
        $this->set('share_products', $products);
    }

    /**
     * @param $share_id
     * 产品库详情页(朋友说用户分享的一个分享)
     */
    public function share_product_detail($share_id)
    {
        $uid = $this->currentUser['id'];
        $is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if (empty($uid) || !$is_proxy) {
            $this->redirect('/weshares/index.html');
            exit();
        }
        $this->set('weshare_id', $share_id);
    }

    /**
     * 团长从产品库开启分享
     */
    public function clone_share($share_id)
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            exit();
        }
        $is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if (!$is_proxy) {
            echo json_encode(array('success' => false, 'reason' => '不是团长'));
            exit();
        }

        $this->log('Proxy ' . $uid . ' tries to clone share from pool products ' . $share_id, LOG_INFO);
        $result = $this->ShareUtil->cloneShare($share_id, $uid, SHARE_TYPE_POOL);
        if ($result['success']) {
            $this->log('Proxy ' . $uid . '  clones share ' . $result['shareId'] . ' from pool products ' . $share_id . ' successfully', LOG_INFO);
        } else {
            $this->log('Proxy ' . $uid . ' failed to clone share from pool products ' . $share_id, LOG_INFO);
        }
        echo json_encode($result);
        exit();
    }

    /**
     * @param $share_id
     * ajax 获取产品池的详情
     */
    public function get_share_product_detail($share_id)
    {
        $this->autoRender = false;
        $share_info = $this->ShareUtil->get_pool_product_info($share_id);
        echo json_encode($share_info);
        exit();
    }

    /**
     * @param $share_id
     * @param $page
     * 获取产品池中产品的订单和评论数据
     * 这个里面不进行交互,只显示数据
     */
    public function get_product_orders_and_comments($share_id, $page = 1)
    {

    }
}
