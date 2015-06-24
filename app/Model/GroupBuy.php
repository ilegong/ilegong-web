<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/23/15
 * Time: 20:43
 */

class GroupBuy extends AppModel{

    public $useTable = false;

    /**
     * @var array
     *
     * custom group by product
     */
    var $allGroupBuyProducts = array(
        '1069' => array(
            'id' => 1069,
            'name' => '顺平水蜜桃-顺丰包邮 4.5斤装',
            'group_price' => 76.6,
            'market_price' => 128,
            'closing_date' => '2015-06-25 18:00:00',
            'send_date' => '2015-06-26',
            'group_buy_num' => 3,
            'desc' => '满3人团购价76.6'
        )
    );

    public function getGroupBuyProductInfo($pid){
        return $this->allGroupBuyProducts[$pid];
    }

}