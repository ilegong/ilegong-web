<?php

class ShareManageComponent extends Component
{

    public $components = array('ShareUti', 'WeshareBuy');

    public function get_index_products($tag_id)
    {
        $indexProductM = ClassRegistry::init('IndexProduct');
        $indexProducts = $indexProductM->find('all', array(
            'conditions' => array(
                'deleted' => DELETED_NO,
                'tag_id' => $tag_id
            ),
            'order' => array('sort_val ASC')
        ));
        return $indexProducts;
    }

    public function get_order_products($order_ids)
    {
        $cartM = ClassRegistry::init('Cart');
        if (!$order_ids)
        {
            return false;
        }
        $sql = "SELECT name,product_id,price,sum(num) AS num FROM cake_carts WHERE order_id IN (".implode(',',$order_ids).") GROUP BY product_id";
        return $cartM->query($sql);
    }

    public function get_pool_product($id)
    {
        $indexProductM = ClassRegistry::init('PoolProduct');
        $indexProducts = $indexProductM->find('all', [
            'conditions' => [
                'PoolProduct.deleted' => DELETED_NO,
                'PoolProduct.id' => $id,
            ],
            'fields' => [
                'PoolProduct.*',
                'WeshareProducts.*',
                'Weshares.*',
                'User.*'
            ],
            'joins' => [
                [
                    'table' => 'weshare_products',
                    'alias' => 'WeshareProducts',
                    'conditions' => [
                        'PoolProduct.weshare_id = WeshareProducts.weshare_id',
                    ],
                ], [
                    'table' => 'weshares',
                    'alias' => 'Weshares',
                    'conditions' => [
                        'PoolProduct.weshare_id = Weshares.id',
                    ],
                ], [
                    'table' => 'users',
                    'alias' => 'User',
                    'conditions' => [
                        'Weshares.creator = User.id',
                    ],
                ],
            ],
            //'order' => array('weshare_id ASC')
        ]);

        return $this->rearrange_pool_product($indexProducts)[0];
    }

    public function get_pool_products($cond = [])
    {
        $indexProductM = ClassRegistry::init('PoolProduct');
        $q_cond = [
            'PoolProduct.deleted' => DELETED_NO,
        ];
        if (!empty($cond)) {
            if (!empty($cond['status'])) {
                $q_cond['PoolProduct.status'] = $cond['status'];
            }
            if (!empty($cond['name'])) {
                $q_cond['PoolProduct.share_name like '] = '%' . $cond['name'] . '%';
            }
        }
        $indexProducts = $indexProductM->find('all', [
            'conditions' => $q_cond,
            'fields' => [
                'PoolProduct.*',
                'WeshareProducts.*'
            ],
            'joins' => [
                [
                    'table' => 'weshare_products',
                    'alias' => 'WeshareProducts',
                    'conditions' => [
                        'PoolProduct.weshare_id = WeshareProducts.weshare_id',
                    ],
                ]
            ],
            'order' => ['PoolProduct.sort ASC', 'PoolProduct.id DESC'],
        ]);

        return $this->rearrange_pool_product($indexProducts);
    }

    private function rearrange_pool_product($data) {
        //print_r($data);
        if (count($data) == 0) {
            return [];
        }

        $oldid = 0;
        $ak = -1;
        foreach ($data as $k => $v) {
            $productid = $v['PoolProduct']['id'];
            if ($oldid == $productid) {
                $arr[$ak]['WeshareProducts'][] = $v['WeshareProducts'];
            } else {
                $ak++;
                $arr[$ak]['PoolProduct'] = $v['PoolProduct'];
                $arr[$ak]['Weshares'] = $v['Weshares'];
                $arr[$ak]['Weshares']['images_array'] = explode('|', $v['Weshares']['images']);
                $arr[$ak]['User'] = $v['User'];
                $arr[$ak]['WeshareProducts'][] = $v['WeshareProducts'];
                $oldid = $productid;
            }
        }

        return $arr;
    }

    public function delete_pool_product_category($id)
    {
        $data['id'] = $id;
        $data['deleted'] = 1;

        ClassRegistry::init('PoolProductCategory')->save($data);
    }

    public function pool_product_category_add($name)
    {
        $data['category_name'] = $name;
        $data['deleted'] = 0;
        $model = ClassRegistry::init('PoolProductCategory');

        $model->save($data);

        return true;
    }

    public function get_pool_product_categories()
    {
        $model = ClassRegistry::init('PoolProductCategory');
        $categories = $model->find('all', [
            'conditions' => [
                'PoolProductCategory.deleted' => DELETED_NO,
            ],
        ]);

        return $categories;
    }

    public function save_pool_product($data)
    {
        // 更新cake_pool_products表
        $poolProductM = ClassRegistry::init('PoolProduct');
        $wesharesM = ClassRegistry::init('Weshare');

        $sql = "SELECT id
FROM cake_weshares
WHERE root_share_id = {$data['Weshares']['id']}";

        $clone_weshares = $wesharesM->query($sql);

        $res = $poolProductM->save($data['PoolProduct']);
        // 更新cake_weshare_products表
        $weshareProductM = ClassRegistry::init('WeshareProduct');
        foreach ($data['WeshareProduct'] as $item) {
            if (!isset($item['id'])) {//add
                $item['id'] = null;
                $item['weshare_id'] = $data['Weshares']['id'];
                $item['price'] *= 100;
                $item['channel_price'] *= 100;
                $item['wholesale_price'] *= 100;
                $weshareProductM->save($item);
                $item['origin_product_id'] = $weshareProductM->id;

                foreach ($clone_weshares as $clone_weshare) {
                    $item['weshare_id'] = $clone_weshare['cake_weshares']['id'];
                    $weshareProductM->save($item);
                }
            }else{//update
                $item['price'] *= 100;
                $item['channel_price'] *= 100;
                $item['wholesale_price'] *= 100;
                $weshareProductM->save($item);

                $sql = "UPDATE `51daifan`.`cake_weshare_products`
SET `name` = '{$item['name']}', `price` = {$item['price']}, `channel_price` = {$item['channel_price']}, `wholesale_price` = {$item['wholesale_price']}
WHERE `51daifan`.`cake_weshare_products`.`origin_product_id` = '{$item['id']}'";
                $weshareProductM->query($sql);
            }

        }

        // 更新cake_weshares表
        $data['Weshares']['title'] = $data['PoolProduct']['share_name'];
        $wesharesM->save($data['Weshares']);

        return true;
    }

    public function save_index_product($data)
    {
        $indexProductM = ClassRegistry::init('IndexProduct');
        $share_id = $data['IndexProduct']['share_id'];
        $user_info = $this->get_user_info_by_share_id($share_id);
        $data['IndexProduct']['share_user_id'] = $user_info['id'];
        $data['IndexProduct']['share_user_img'] = get_user_avatar($user_info);
        $data['IndexProduct']['share_user_name'] = $user_info['nickname'];
        $result = $indexProductM->save($data);

        if(empty($data['IndexProduct']['id'])){
            $this->log('add index product '.$result['IndexProduct']['id'].': '.json_encode($result), LOG_INFO);
        }
        else{
            $this->log('update index product '.$result['IndexProduct']['id'].': '.json_encode($result), LOG_INFO);
        }

        $this->on_index_product_saved();
        return $result;
    }

    private function get_user_info_by_share_id($share_id)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $userM = ClassRegistry::init('User');
        $share_info = $weshareM->find('first', array(
            'conditions' => array('id' => $share_id),
            'fields' => array('id', 'creator')
        ));
        $creator_id = $share_info['Weshare']['creator'];
        $user_info = $userM->find('first', array(
            'conditions' => array('id' => $creator_id),
            'fields' => array('id', 'nickname', 'avatar', 'image')
        ));
        return $user_info['User'];
    }

    /**
     * @param $shareId
     * @return percent
     */
    public function get_weshare_rebate_setting($shareId)
    {
        $proxyRebatePercentM = ClassRegistry::init('ProxyRebatePercent');
        $proxyRebatePercent = $proxyRebatePercentM->find('first', array(
            'conditions' => array(
                'share_id' => $shareId
            )
        ));
        return $proxyRebatePercent;
    }

    /**
     * @param $shareId
     * @return array
     * 获取到分享的物流设置
     */
    public function get_weshare_ship_settings($shareId)
    {
        $weshareShipSettingM = ClassRegistry::init('WeshareShipSetting');
        $shipSettings = $weshareShipSettingM->find('all', array(
            'conditions' => array(
                'weshare_id' => $shareId
            )
        ));

        return $shipSettings;
    }

    /**
     * @param $shareId
     * @return mixed
     * 分享地址
     */
    public function get_weshare_addresses($shareId)
    {
        $weshareAddressM = ClassRegistry::init('WeshareAddress');
        $weshareAddresses = $weshareAddressM->find('all', array(
            'conditions' => array(
                'weshare_id' => $shareId
            )
        ));
        return $weshareAddresses;
    }

    public function get_weshare_products($shareId)
    {
        $weshareProductM = ClassRegistry::init('WeshareProduct');
        $weshare_products = $weshareProductM->find('all', array(
            'conditions' => array(
                'weshare_id' => $shareId,
                'deleted' => DELETED_NO
            )
        ));
        return $weshare_products;
    }

    public function get_share_product_tags($uid)
    {
        $weshareProductTagM = ClassRegistry::init('WeshareProductTag');
        $tags = $weshareProductTagM->find('all', array(
            'conditions' => array(
                'user_id' => $uid,
                'deleted' => DELETED_NO
            )
        ));
        return $tags;
    }

    /**
     * @param $share_id
     * @param int $only_paid
     * @param $start_date
     * @param $end_date
     * @return mixed
     * 获取分享的订单
     */
    public function get_share_orders($share_id, $only_paid = 0, $start_date = null, $end_date = null)
    {
        $OrderM = ClassRegistry::init('Order');
        $q_order_status = $only_paid == 1 ? [ORDER_STATUS_PAID] : [ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_COMMENT, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_COMMENT, ORDER_STATUS_DONE];
        $q_cond = [
            'type' => ORDER_TYPE_WESHARE_BUY,
            'member_id' => $share_id,
            'status' => $q_order_status,
        ];
        if(!empty($start_date)){
            $q_cond['created > '] = $start_date;
        }
        if(!empty($end_date)){
            $q_cond['created < '] = $end_date;
        }
        $orders = $OrderM->find('all', array(
            'conditions' => $q_cond,
            'limit' => 2000
        ));
        return $orders;
    }

    /**
     * @param $order_ids
     * @return array
     * 获取订单和购物车map
     */
    public function get_order_cart_map($order_ids){
        if(empty($order_ids)){
            return [];
        }
        $cartM = ClassRegistry::init('Cart');
        $carts = $cartM->find('all', [
            'conditions' => [
                'order_id' => $order_ids
            ],
            'fields' => ['id', 'order_id', 'num', 'name']
        ]);
        $result = [];
        foreach($carts as $cart_item){
            $order_id = $cart_item['Cart']['order_id'];
            if(!isset($result[$order_id])){
                $result[$order_id] = [];
            }
            $result[$order_id][] = $cart_item['Cart'];
        }
        return $result;
    }

    public function get_users_data($user_ids)
    {
        $UserM = ClassRegistry::init('User');
        $user_data = $UserM->find('all', array(
            'conditions' => array(
                'id' => $user_ids
            ),
            'fields' => array('id', 'nickname')
        ));
        return $user_data;
    }


    public function set_dashboard_collect_data($uid)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $orderM = ClassRegistry::init('Order');
        $userRelationM = ClassRegistry::init('UserRelation');
        $shareFaqM = ClassRegistry::init('ShareFaq');
        $share_count = $weshareM->find('count', array(
            'conditions' => array(
                'creator' => $uid
            )
        ));
        $last_300_shares = $weshareM->find('all', array(
            'conditions' => array(
                'creator' => $uid
            ),
            'order' => array('id' => 'desc'),
            'fields' => array('id'),
            'limit' => 300
        ));
        $last_300_share_ids = Hash::extract($last_300_shares, '{n}.Weshare.id');
        $order_count = $orderM->find('count', array(
            'conditions' => array(
                'member_id' => $last_300_share_ids,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED, ORDER_STATUS_RECEIVED, ORDER_STATUS_COMMENT, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_COMMENT, ORDER_STATUS_DONE),
            )
        ));
        $faq_count = $shareFaqM->find('count', array(
            'conditions' => array(
                'receiver' => $uid,
                'has_read' => 0
            )
        ));
        $fans_count = $userRelationM->find('count', array(
            'conditions' => array(
                'user_id' => $uid
            )
        ));
        return array('share_count' => $share_count, 'order_count' => $order_count, 'faq_count' => $faq_count, 'fans_count' => $fans_count);
    }

    public function on_index_product_saved(){
        $this->clear_cache_for_index_products();
    }

    public function on_index_product_deleted(){
        $this->clear_cache_for_index_products();
    }

    public function clear_cache_for_index_products(){
        $tags = get_index_tags();
        foreach($tags as $tag_item){
            Cache::write(INDEX_PRODUCTS_BY_TAG_CACHE_KEY . '_' . $tag_item['id'], '');
            Cache::write(INDEX_VIEW_PRODUCT_CACHE_KEY . '_' . $tag_item['id'], '');
        }
    }
}
