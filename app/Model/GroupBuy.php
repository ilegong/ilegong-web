<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 6/23/15
 * Time: 20:43
 */

class GroupBuy extends AppModel{

    public $useTable = false;


    var $allGroupBuyProducts = array(
        '883' => array(
            'id' => 883,
            'name' => '海南椰子冻',
            'group_price' => 10,
            'market_price' => 30,
            'closing_date' => '2015-06-25',
            'send_date' => '2015-06-26',
            'group_buy_num' => 5
        )
    );

    public function getGroupBuyProductInfo($pid){
        return $this->allGroupBuyProducts[$pid];
    }

}