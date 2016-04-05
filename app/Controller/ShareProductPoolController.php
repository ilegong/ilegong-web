<?php

/**
 * 产品池
 *
 */
class ShareProductPoolController extends AppController {


    var $name = 'share_product_pool';

    var $uses = array('SharePoolProduct', 'Weshare');

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
        $is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if (!$is_proxy) {
            $this->redirect('/weshares/index.html');
            exit();
        }
        //设置微信分享参数
        if (parent::is_weixin()) {
            $weixinJs = prepare_wx_share_log($uid, 'wsid', 0);
            $this->set($weixinJs);
        }
        $this->set('uid', $uid);
        $share_products = $this->SharePoolProduct->get_all_products();
        $this->set('share_products', $share_products);
    }

    /**
     * @param $share_id
     * 产品库详情页(朋友说用户分享的一个分享)
     */
    public function share_product_detail($share_id) {
        $uid = $this->currentUser['id'];
        $is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if (empty($uid) || !$is_proxy) {
            $this->redirect('/weshares/index.html');
            exit();
        }
        $this->set('weshare_id', $share_id);
    }

    /**
     * @param $share_id
     * 团长从产品库开启分享
     */
    public function clone_share($share_id) {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode(array('success' => false, 'reason' => 'not_login'));
            exit();
        }
        $is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if (!$is_proxy) {
            echo json_encode(array('success' => false, 'reason' => '不是团长'));
        }
        $result = $this->ShareUtil->cloneShare($share_id, $uid);
        if ($result['success']) {
            $this->init_share_authorize($result['shareId'], $share_id, $uid);
        }
        echo json_encode($result);
        exit();
    }

    /**
     * @param $share_id
     * ajax 获取产品池的详情
     */
    public function get_share_product_detail($share_id) {
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
    public function get_product_orders_and_comments($share_id, $page = 1) {

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
