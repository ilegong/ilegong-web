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
            array('id' => 8, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '搜狐网络大厦'),
            array('id' => 9, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '搜狐媒体大厦'),
            array('id' => 10, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '同方大厦'),
            array('id' => 11, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '银科大厦'),
            array('id' => 12, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '中国技术交易大厦'),
            array('id' => 13, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '希格玛大厦'),
            array('id' => 14, 'ship_price' => 0.0, 'price' => 49.90, 'time' => '11月5日', 'address' => '360大厦'),
        )
    ),
    '222' => array('limit_ship' => true,
        'limit_per_user' => 1,
        'items' => array(
            array('id' => 15, 'ship_price' => 0.0, 'price' => 0.10, 'time' => '', 'address' => '维亚大厦'),
            array('id' => 16, 'ship_price' => 0.0, 'price' => 0.10, 'time' => '', 'address' => '东升科技园'),
            array('id' => 17, 'ship_price' => 0.0, 'price' => 0.10, 'time' => '', 'address' => '神州数码'),
            array('id' => 18, 'ship_price' => 0.0, 'price' => 0.10, 'time' => '', 'address' => '西海国际'),
            array('id' => 19, 'ship_price' => 0.0, 'price' => 0.10, 'time' => '', 'address' => '电子大厦'),
        )
    ),
    PRODUCT_ID_CAKE => array('limit_ship' => true,
        'items' => array(
            array('id' => 21, 'ship_price' => 0.0, 'time' => '', 'address' => '自提点：海淀大街38号味多美门口， 包邮'),
            array('id' => 22, 'ship_price' => 0.0, 'time' => '', 'address' => '自提点：北四环盘古大观广场南门， 包邮'),
            array('id' => 23, 'ship_price' => 0.0, 'time' => '', 'address' => '自提点：五道口华清嘉园会所果臻棒， 包邮'),
            array('id' => 27, 'ship_price' => 0.0, 'time' => '', 'address' => '跑腿儿专递（限五环内，运送费到付：每个15元）', 'need_address_remark' => true),
            array('id' => 25, 'ship_price' => 0.0,  'time' => '', 'least_num' => 10,  'address' => '五环内满10个起送, 包邮', 'need_address_remark' => true),
            array('id' => 26, 'ship_price' => 5.0, 'time' => '', 'least_num' => 20,  'address' => '五环外满20个起送, 运送费：每个5元', 'need_address_remark' => true),
            array('id' => 28, 'ship_price' => 10.0, 'time' => '', 'address' => '昌平专场（超过20个发货), 运送费：每个10元'),
            array('id' => 29, 'ship_price' => 10.0, 'time' => '', 'address' => '顺义专场（超过20个发货), 运送费：每个10元'),
        )
    ),
    '240' => array('limit_ship' => true,
        'limit_per_user' => 1,
        'items' => array(
            array('id' => 28, 'ship_price' => 0.0, 'time' => '', 'address' => '腾讯公司希格玛B1交单处'),
        )
    )
    );

    private  $pro_num_limit = array(
        //total_limit(0 means none), brand_id, per_user_limit (0 means none)
        '228' => array(100, 13, 1),
        '229' => array(80, 13, 1),
        PRODUCT_ID_CAKE => array(288, 74, 0),
        '240' => array(306, 78, 1 )
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
        } else if ($pid == PRODUCT_ID_CAKE) {
            return $singleShipFee * $num;
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

    /**
     * @param $promotionId integer promotion id
     * @return mixed address and its properties
     */
    public function find_special_address_by_id($promotionId) {
        foreach($this->specialPromotions as $pid=>$promotions) {
            list($limit_per_user, $addressList) = $this->find_ship_promotion_limit($pid, $promotionId);
            if (!empty($addressList)) {
                return array($pid, $addressList);
            }
        }
        return null;
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