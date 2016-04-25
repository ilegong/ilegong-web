<?php

class WeshareApiController extends Controller
{
    var $components = ['Weshares', 'UserConsignee', 'ShareUtil', 'OAuth.OAuth', 'WeshareBuy'];

    public function beforeFilter()
    {
        $allow_action = ['test', 'get_index_products'];
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }


    protected function get_post_raw_data()
    {
        $postStr = file_get_contents('php://input');
        $postDataArray = json_decode($postStr, true);
        return $postDataArray;
    }

    //获取首页的标签
    public function get_index_product_tags()
    {
        $tags = ['0' => '新品爆款', '1' => '水果蔬菜', '2' => '肉蛋粮油', '3' => '零食其他'];
        echo json_encode($tags);
        exit();
    }

    //获取首页产品
    public function get_index_products($tag)
    {
        $products = $this->ShareUtil->get_index_product($tag);
        $result = $this->combine_index_data($products);
        echo json_encode($result);
        exit();
    }

    //获取评论，只做展示使用
    public function get_share_comment($root_share_id, $weshare_id, $limit, $page)
    {
        $all_share_ids = $this->get_associate_share_ids($weshare_id, $root_share_id);
        $query_cond = [
            'conditions' => ['data_id' => $all_share_ids]
        ];
        $result = $this->WeshareBuy->query_comment2($query_cond);
        echo json_encode($result);
        exit();
    }

    //获取推荐，只做展示使用
    public function get_share_recommend($weshare_id)
    {
        $result = $this->WeshareBuy->load_share_recommend_data($weshare_id);
        echo json_encode($result);
        exit();
    }

    //获取分享的详情
    public function get_weshare_detail($weshare_id)
    {
        $uid = $this->currentUser['id'];
        $detail = $this->Weshares->get_app_weshare_detail($weshare_id);
        $products = $this->Weshares->get_weshare_products($weshare_id);
        $has_sub = true;
        if ($uid != $detail['creator']) {
            $has_sub = $this->WeshareBuy->check_user_subscribe($detail['creator'], $uid);
        }
        $userM = ClassRegistry::init('User');
        $users = $userM->get_users_simple_info([$uid, $detail['creator']]);
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        $sharer_level_data = $this->ShareUtil->get_user_level($detail['creator']);
        echo json_encode(['detail' => $detail, 'products' => $products, 'has_sub' => $has_sub, 'current_user' => $users[$uid], 'sharer' => $users[$detail['creator']], 'sharer_level' => $sharer_level_data]);
        exit();
    }

    //获取分享的汇总数据
    public function get_share_summery_data($weshare_id)
    {
        $uid = $this->currentUser['id'];
        $summery = $this->WeshareBuy->get_share_and_all_refer_share_summary($weshare_id, $uid);
        echo json_encode($summery);
        exit();
    }

    //获取某个分享自己的订单
    public function get_current_user_order($weshare_id)
    {
        $uid = $this->currentUser['id'];
        $ordersDetail = $this->WeshareBuy->get_current_user_share_order_data($weshare_id, $uid);
        echo json_encode($ordersDetail);
        exit();
    }

    //获取某个分享分页查询订单
    public function get_share_order_data($weshare_id, $page)
    {
        $uid = $this->currentUser['id'];
        $ordersDetail = $this->WeshareBuy->get_share_detail_view_orders($weshare_id, $page, $uid, 1);
        echo json_encode($ordersDetail);
        exit();
    }

    //获取用户的地址
    public function get_user_consignees()
    {
        $uid = $this->currentUser['id'];
        $result = $this->UserConsignee->get_consignee_list($uid);
        echo $result;
        exit();
    }

    //保存用户收货地址
    public function save_user_consignee()
    {
        $uid = $this->currentUser['id'];
        $post_data = $this->get_post_raw_data();
        $consignee = $this->UserConsignee->save_consignee($post_data, $uid);
        echo json_encode(['success' => true, 'consignee' => $consignee['OrderConsignee']]);
        exit();
    }

    //用户选择某个地址
    public function select_user_consignee($consignee_id)
    {
        $uid = $this->currentUser['id'];
        $this->UserConsignee->select_consignee($consignee_id, $uid);
        echo json_encode(['success' => true]);
        exit();
    }

    //组合首页数据
    private function combine_index_data($products)
    {
        $result = [];
        $uid = $this->currentUser['id'];
        $this->loadModel('User');
        $my_subs = $this->User->get_my_proxys($uid);
        foreach ($products as $product_item) {
            $result[] = [
                'share_id' => $product_item['IndexProduct']['share_id'],
                'share_img' => $product_item['IndexProduct']['share_img'],
                'share_price' => $product_item['IndexProduct']['share_price'],
                'share_desc' => $product_item['IndexProduct']['description'],
                'share_spec' => $product_item['IndexProduct']['specification'],
                'user_id' => $product_item['User']['id'],
                'user_avatar' => get_user_avatar($product_item['User']),
                'user_label' => $product_item['User']['label'],
                'user_level' => $product_item['UserLevel']['data_value'],
                'user_nickname' => $product_item['User']['nickname'],
                'is_sub' => in_array($product_item['User']['id'], $my_subs),
                'summery' => $this->ShareUtil->get_index_product_summary($product_item['IndexProduct']['share_id']),
            ];
        }
        return $result;
    }

    private function get_associate_share_ids($share_id, $root_share_id)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $root_share_id = $root_share_id > 0 ? $root_share_id : $share_id;
        $weshares = $weshareM->find('all', [
            'conditions' => [
                'OR' => ['id' => $root_share_id, 'root_share_id' => $root_share_id]
            ],
            'fields' => ['id']
        ]);
        return Hash::extract($weshares, '{n}.Weshare.id');
    }
}