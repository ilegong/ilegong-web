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

    public function admin_all_shares(){

    }

    public function admin_share_order(){

    }

}