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
            'name' => '顺平水蜜桃-顺丰包邮 果汁四溢 绿色无农药 国家地理标志保护产品',
            'group_price' => 88,
            'market_price' => 128,
            'spec' => 0,
            'spec_name' => '2.4kg 左右',
            'closing_date' => '2015-06-25 18:00:00',
            'send_date' => null,
            'group_buy_num' => 5,
            'product_alias' => '顺平水蜜桃',
            'desc' => '满5人享受团购价88！果汁四溢久久的挑逗你的口腔和舌尖'
        ),
    );

    public function getGroupBuyProductInfo($pid){
        return $this->allGroupBuyProducts[$pid];
    }

    public function getProductGroupBuyNum($pid){
        return $this->allGroupBuyProducts[$pid]['group_buy_num'];
    }

}