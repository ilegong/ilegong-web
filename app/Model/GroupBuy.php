<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/23/15
 * Time: 20:43
 */

class GroupBuy extends AppModel{

    public $useTable = false;

    static $not_available_group_label = array('1069-1');

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
            'closing_date' => '2015-06-26 19:00:00',
            'send_date' => null,
            'group_buy_num' => 3,
            'product_alias' => '顺平水蜜桃',
            'desc' => '满5人享受团购价88！果汁四溢久久的挑逗你的口腔和舌尖',
            'group_buy_label' => '1069-2'
        ),
    );

    public function getGroupBuyProductInfo($pid){
        return $this->allGroupBuyProducts[$pid];
    }

    public function getProductGroupBuyNum($pid){
        return $this->allGroupBuyProducts[$pid]['group_buy_num'];
    }

    public static function group_buy_is_available($group_buy_label){
        return in_array($group_buy_label,self::$not_available_group_label);
    }
}