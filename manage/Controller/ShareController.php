<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 7/20/15
 * Time: 17:11
 */

class ShareController extends AppController{

    var $name = 'Share';

    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User', 'OrderConsignees');

    public function beforeFilter(){
        parent::beforeFilter();
        $this->layout='bootstrap_layout';
    }

    public function admin_index(){

    }

    public function admin_all_shares() {
        $shares = $this->Weshare->find('all', array(
            'limit' => 200
        ));
        $shareIds = Hash::extract($shares, '{n}.Weshare.id');
        $products = $this->WeshareProduct->find('all',array(
            'conditions' => array(
                'weshare_id' => $shareIds
            )
        ));
        $share_product_map = array();
        foreach($products as $item){
            if(!isset($share_product_map[$item['WeshareProduct']['weshare_id']])){
                $share_product_map[$item['WeshareProduct']['weshare_id']] = array();
            }
            $share_product_map[$item['WeshareProduct']['weshare_id']][] = $item['WeshareProduct'];
        }
        $this->set('shares',$shares);
        $this->set('share_product_map',$share_product_map);
    }

    public function admin_share_order(){

    }

}