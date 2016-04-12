<?php

class PoolProductApiController extends AppController
{
    public $components = [
        'OAuth.OAuth',
        'Session',
        'WeshareBuy',
        'ShareUtil',
        'Weshares',
        'ShareAuthority',
    ];

    public $uses = [
        'SharePoolProduct',
    ];

    public function beforeFilter()
    {
        $this->autoRender = false;
        $allow_action = ['test'];
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
    }

    public function get_pool_product_list($category)
    {
        $share_products = $this->SharePoolProduct->get_all_products($category);

        echo json_encode($share_products);
        exit();
    }

    // App上从产品街开团
    public function clone_share($share_id)
    {
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode([
                'success' => false,
                'reason' => 'user login required.'
            ]);
            exit();
        }
        $is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if (!$is_proxy) {
            echo json_encode([
                'success' => false,
                'reason' => 'not a proxy user.'
            ]);
            exit();
        }
        $result = $this->ShareUtil->cloneShare($share_id, $uid, FROM_POOL_SHARE_TYPE);
        if ($result['success']) {
            $this->init_share_authorize($result['shareId'], $share_id, $uid);
        }
        echo json_encode($result);
        exit();
    }

    /**
     * 获取产品街产品详情
     *
     * @param mixed $share_id 分享ID
     * @access public
     * @return void
     */
    public function get_detail($share_id){
        $result = $this->ShareUtil->get_pool_product_info($share_id);
        echo json_encode($result);
        exit();
    }

    /**
     * get_all_product_categories 获取产品街所有的分类ID
     *
     * @access public
     * @return void
     */
    public function get_all_product_categories()
    {
        $model = ClassRegistry::init('PoolProductCategory');

        $data = $model->find('all', [
            'conditions' => [
                'deleted' => DELETED_NO,
            ],
        ]);

        $res = [];
        foreach($data as $item) {
            $tmp = [];
            $tmp['id'] = $item['PoolProductCategory']['id'];
            $tmp['name'] = $item['PoolProductCategory']['category_name'];

            $res[] = $tmp;
        }

        echo json_encode($res);
        exit();
    }

    /**
     * init_share_authorize 此方法和ShareProductPoolController.php里面的方法重复
     * 是否考虑抽出到component里面?
     *
     * @param mixed $share_id
     * @param mixed $refer_share_id
     * @param mixed $uid
     * @access private
     * @return void
     */
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
