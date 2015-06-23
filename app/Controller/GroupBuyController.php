<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/23/15
 * Time: 20:49
 */
class GroupBuyController extends AppController{

    var $name = 'GroupBuy';

    var $uses = array('Product', 'GroupBuy');

    public function to_group_buy_detail($pid){
        $groupBuyInfo = $this->GroupBuy->getGroupBuyProductInfo($pid);
        $productInfo = $this->Product->find('first',array(
            'conditions' => array(
                'id' => $pid
            ),
            'fields' => Product::NO_VISIBLE_SIMPLE_FIELDS
        ));
        $this->set('group_buy_info',$groupBuyInfo);
        $this->set('product_info',$productInfo);
    }


}