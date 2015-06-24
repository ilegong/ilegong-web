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
        '883' => array(
            'id' => 883,
            'name' => '海南椰子冻',
            'group_price' => 10,
            'market_price' => 30,
            'closing_date' => '2015-06-24 18:00:00',
            'send_date' => '2015-06-26',
            'group_buy_num' => 5,
            'desc' => '满5人购买享受10元团购价'
        )
    );

    public function getGroupBuyProductInfo($pid){
        return $this->allGroupBuyProducts[$pid];
    }

}