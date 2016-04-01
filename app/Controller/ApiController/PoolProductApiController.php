<?php

class PoolProductApiController extends AppController
{
    public $components = [
        'OAuth.OAuth',
        'Session',
        'WeshareBuy',
        'ShareUtil',
        'Weshares'
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

    public function get_pool_product_list()
    {
        $share_products = $this->SharePoolProduct->get_all_products();

        echo json_encode($share_products);
        exit();
    }

    public function clone_share($share_id)
    {
        $uid = $this->currentUser['id'];
        if (empty($uid)) {
            echo json_encode([
                'success' => false,
                'reason' => 'user login required.'
            ]);
            return;
        }
        $is_proxy = $this->ShareUtil->is_proxy_user($uid);
        if (!$is_proxy) {
            echo json_encode([
                'success' => false,
                'reason' => 'not a proxy user.'
            ]);
        }
        $result = $this->ShareUtil->cloneShare($share_id, $uid);
        if ($result['success']) {
            $this->init_share_authorize($result['shareId'], $share_id, $uid);
        }
        echo json_encode($result);
        return;
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
