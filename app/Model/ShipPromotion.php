<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/21/14
 * Time: 7:34 PM
 */

class ShipPromotion extends AppModel {
    const PID = 180;
    public $useTable = false;
    var $specialShipPromotion = array('lowest' => 49.90,
        'items' => array(
            array('id' => 1, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 海淀区 搜狐网络大厦'),
            array('id' => 2, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 海淀区 搜狐媒体大厦'),
            array('id' => 3, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 海淀区 同方大厦'),
            array('id' => 4, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 海淀区 银科大厦'),
            array('id' => 5, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 海淀区 中国技术交易大厦'),
            array('id' => 6, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 海淀区 希格玛大厦'),
        )
    );

    public function find_ship_promotion($productId, $promotionId) {
        if ($productId == self::PID) {
            $promotion = array_filter($this->specialShipPromotion['items'], function ($item) use ($promotionId) {
                    return ($item['id'] == $promotionId);
                });
            if(!empty($promotion)){ return (array_values($promotion)[0]);};
        }
        return null;
    }

    public function findShipPromotions($product_ids) {
        if (array_search(self::PID, $product_ids) !== false) {
            return $this->specialShipPromotion['items'];
        }
        return array();
    }
} 