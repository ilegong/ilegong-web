<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 5/8/15
 * Time: 16:26
 */

class ScheduleProductController extends AppController{

    var $name = 'ScheduleProduct';

    var $uses = array('Product','ConsignmentDate');

    public function admin_index(){
        $product_ids = $this->ConsignmentDate->query('SELECT distinct product_id FROM 51daifan.cake_consignment_dates');
        $product_ids = Hash::extract($product_ids,'{n}.cake_consignment_dates.product_id');
        $products = $this->Product->find('all',array(
            'conditions' => array(
                'id' => $product_ids
            ),
            'fields' => array('id','name')
        ));
        foreach($products as &$product){
            $product['consign_dates'] = $this->get_consign_dates_by_pid($product['id']);
        }
        $this->set('datas',$products);
    }

    private function get_consign_dates_by_pid($pid){
        $consign_dates = $this->ConsignmentDate->find('all',array(
            'conditions' => array(
                'product_id' => $pid,
                'published' => 1
            )
        ));
        $consign_dates = Hash::extract($consign_dates,'{n}.ConsignmentDate');
        return $consign_dates;
    }

}