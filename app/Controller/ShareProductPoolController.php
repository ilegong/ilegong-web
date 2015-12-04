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
        $is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if (!$is_proxy) {
            $this->redirect('/weshares/index.html');
            return;
        }
        //设置微信分享参数
        if (parent::is_weixin()) {
            $weixinJs = prepare_wx_share_log($uid, 'wsid', 0);
            $this->set($weixinJs);
        }
        $this->set('uid', $uid);
        $share_products = $this->SharePoolProduct->get_all_products();
        $this->set('share_products', $share_products);
        return;
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
            return;
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
            return;
        }
        $is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if (!$is_proxy) {
            echo json_encode(array('success' => false, 'reason' => '不是团长'));
        }
        $result = $this->ShareUtil->cloneShare($share_id, $uid);
        if ($result['success']) {
            $pool_product_config = $this->SharePoolProduct->get_product_by_share_id($share_id);
            $this->init_share_authorize($result['shareId'], $share_id, $pool_product_config['brand_custom_service']);
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
        $pool_product_config = $this->SharePoolProduct->get_product_by_share_id($share_id);
        $share_info['channel_price'] = $pool_product_config['channel_price'];
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
            $WeshareShipSettingM = ClassRegistry::init('WeshareShipSetting');
            $shipSettings = $WeshareShipSettingM->find('all', array('conditions' => array('weshare_id' => $share_id)));
            $ship_info = $this->get_pool_product_ship_info($shipSettings);
            $weshare_info['ship_info'] = $ship_info;
            Cache::write($key, json_encode($weshare_info));
            return $weshare_info;
        }
        return json_decode($cacheData, true);
    }

    /**
     * @param $shipSettings
     * @return string
     * 获取快递信息
     */
    private function get_pool_product_ship_info($shipSettings) {
        $ship_info = array();
        foreach ($shipSettings as $shipSettingItem) {
            if ($shipSettingItem['WeshareShipSetting']['tag'] == SHARE_SHIP_KUAIDI_TAG && $shipSettingItem['WeshareShipSetting']['status'] == 1) {
                $ship_fee = $shipSettingItem['WeshareShipSetting']['ship_fee'];
                if ($ship_fee == 0) {
                    $ship_info_item = '快递包邮';
                } else {
                    $ship_fee = $ship_fee / 100;
                    $ship_fee = number_format($ship_fee, 2);
                    $ship_info_item = '快递费用' . $ship_fee . '元';
                }
                $ship_info[] = $ship_info_item;
            }
            if ($shipSettingItem['WeshareShipSetting']['tag'] == SHARE_SHIP_PYS_ZITI_TAG && $shipSettingItem['WeshareShipSetting']['status'] == 1) {
                $ship_info[] = '好邻居自提';
            }
        }
        return implode(',', $ship_info);
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