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
}
