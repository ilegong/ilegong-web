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
            echo json_encode(array('success' => false,'reason' => 'user login required.'));
            exit();
        }
        $is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if (!$is_proxy) {
            echo json_encode(array('success' => false,'reason' => 'not a proxy user.'));
            exit();
        }

        $this->log('Proxy '.$uid.' tries to clone share from pool products '.$share_id , LOG_INFO);
        $result = $this->ShareUtil->cloneShare($share_id, $uid, SHARE_TYPE_POOL);
        if ($result['success']) {
            $this->log('Proxy '.$uid.' clones share '.$result['shareId'] . ' from pool products '.$share_id.' successfully', LOG_INFO);
        }
        else{
            $this->log('Proxy '.$uid.' failed to clone share '.$share_id . ' from pool products '.$share_id, LOG_ERR);
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
        $this->loadModel('SharePoolProduct');
        $res = $this->SharePoolProduct->get_pool_product_categories();
        echo json_encode($res);
        exit();
    }
}
