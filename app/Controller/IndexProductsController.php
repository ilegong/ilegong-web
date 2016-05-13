<?php

class IndexProductsController extends Controller
{
    var $components = array('ShareUtil');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = null;
    }

    public function tutorial()
    {
        $this->layout = null;
    }

    /**
     * api
     */
    public function index_products($tag = 0)
    {
        $this->layout = null;
        $index_products = $this->ShareUtil->index_products($tag);

        echo json_encode($index_products);
        exit();
    }

    /**
     * api
     */
    public function summary($share_id)
    {
        $this->layout = null;

        $orders_and_creators = $this->ShareUtil->get_index_product_summary($share_id);

        echo json_encode($orders_and_creators);
        exit();
    }
}
