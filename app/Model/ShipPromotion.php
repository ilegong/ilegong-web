<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/21/14
 * Time: 7:34 PM
 */

class ShipPromotion extends AppModel {
    const PID = 192;

    const QUNAR_PROMOTE_BRAND_ID = 61;
    const QUNAR_PROMOTE_ID = 222;

    public $useTable = false;

    var $specialPromotions = array(
    '192' => array('lowest' => 49.90,
        'limit_ship' => false,
        'items' => array(
            array('id' => 1, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 海淀区 搜狐网络大厦'),
            array('id' => 2, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 海淀区 搜狐媒体大厦'),
            array('id' => 3, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 海淀区 同方大厦'),
            array('id' => 4, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 海淀区 银科大厦'),
            array('id' => 5, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 海淀区 中国技术交易大厦'),
            array('id' => 6, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 海淀区 希格玛大厦'),
            array('id' => 7, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '北京市 360大厦'),
        )
    ),
    '221' => array('limit_ship' => true,
        'items' => array(
            array('id' => 1, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '搜狐网络大厦'),
            array('id' => 2, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '搜狐媒体大厦'),
            array('id' => 3, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '同方大厦'),
            array('id' => 4, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '银科大厦'),
            array('id' => 5, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '中国技术交易大厦'),
            array('id' => 6, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '希格玛大厦'),
            array('id' => 7, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '360大厦'),
        )
    ),
    '222' => array('limit_ship' => true,
        'limit_per_user' => 1,
        'items' => array(
            array('id' => 1, 'ship_price' => 0.0, 'price' => 0.10, 'time' => '', 'address' => '维亚大厦'),
            array('id' => 2, 'ship_price' => 0.0, 'price' => 0.10, 'time' => '', 'address' => '东升科技园'),
            array('id' => 3, 'ship_price' => 0.0, 'price' => 0.10, 'time' => '', 'address' => '神州数码'),
            array('id' => 4, 'ship_price' => 0.0, 'price' => 0.10, 'time' => '', 'address' => '西海国际'),
            array('id' => 5, 'ship_price' => 0.0, 'price' => 0.10, 'time' => '', 'address' => '电子大厦'),
        )
    )
    );

    private  $pro_num_limit = array(
        //total_limit(0 means none), brand_id, per_user_limit (0 means none)
        '228' => array(100, 13, 1),
        '229' => array(70, 13, 1),
        '230' => array(100, 74, 0),
    );

    /**
     * @param $pid
     * @param $singleShipFee
     * @param $num
     * @param $area
     * @return mixed
     */
    public static function calculateShipFee($pid, $singleShipFee, $num, $area) {
        if ($pid == 231 && $num >= 2) {
            return 0.0;
        }
        return $singleShipFee;
    }

    public function findNumberLimitedPromo($pid) {
        return $this->pro_num_limit[$pid];
    }

    public function find_ship_promotion($productId, $promotionId) {
        list($limit_ship, $promotion) = $this->find_ship_promotion_limit($productId, $promotionId);
        return $promotion;
    }

    public function find_ship_promotion_limit($productId, $promotionId) {
        $promotions = $this->specialPromotions[$productId];
        if ($promotions && !empty($promotions)) {
            $promotion = array_filter($promotions['items'], function ($item) use ($promotionId) {
                    return ($item['id'] == $promotionId);
                });
            if(!empty($promotion)){
                $values = array_values($promotion);
                return array($promotions['limit_per_user'], $values[0]);
            };
        }
        return null;
    }

    public function findShipPromotions($product_ids) {
        foreach($product_ids as $pid) {
            $promotions = $this->specialPromotions[$pid];
            if ($promotions && !empty($promotions)) {
                return $promotions;
            }
        }
        return array();
    }
} 