<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/15/14
 * Time: 5:24 PM
 */

class ShichiController extends AppController {

    var $uses = array('ProductTry', 'Product');

    public function list_pai(){
        $this->loadModel('ProductTry');
        $tryings = $this->ProductTry->find_trying(20);
        if (!empty($tryings)) {
            $tryProducts = $this->Product->find_products_by_ids(Hash::extract($tryings, '{n}.ProductTry.product_id'), array(), false);
            if (!empty($tryProducts)) {
                foreach($tryings as &$trying) {
                    $prod = $tryProducts[$trying['ProductTry']['product_id']];
                    if (!empty($prod)) {
                        $trying['Product'] = $prod;
                    } else {
                        unset($trying);
                    }
                }
            }
        }

        $this->set('tryings', $tryings);
        $this->pageTitle = '试吃秒杀';
    }
}