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
            'name' => '顺平水蜜桃-顺丰包邮 果汁四溢 绿色无农药 国家地理标志保护产品 4.5斤装',
            'group_price' => 76.6,
            'market_price' => 128,
            'spec' => 0,
            'closing_date' => '2015-06-25 18:00:00',
            'send_date' => '2015-06-26',
            'group_buy_num' => 5,
            'desc' => '满3人团购价76.6'
        )
    );

    public function getGroupBuyProductInfo($pid){
        return $this->allGroupBuyProducts[$pid];
    }

    public function getProductGroupBuyNum($pid){
        return $this->allGroupBuyProducts[$pid]['group_buy_num'];
    }

}