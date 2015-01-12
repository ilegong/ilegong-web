<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/21/14
 * Time: 7:34 PM
 */

class ShipPromotion extends AppModel {
    const PID = 192;

    const BIN_BIN_BRAND_ID = 26;
    const BIN_BIN_SHIP_PROMO_ID = 2601;

    const QUNAR_PROMOTE_BRAND_ID = 61;
    /** @depreted */
    const QUNAR_PROMOTE_ID = 222;

    public $useTable = false;

    var $BIN_BIN_PROMO = array('limit_ship' => true,
        'items' => array(
            array('id' => self::BIN_BIN_SHIP_PROMO_ID, 'address' => '自提地址：海淀区上地十街辉煌国际大厦2号楼1706室 189-1105-8517'),
        )
    );

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
//            array('id' => 21, 'ship_price' => 0.0, 'time' => '', 'address' => '自提点：海淀大街38号味多美门口， 包邮'),
//            array('id' => 22, 'ship_price' => 0.0, 'time' => '', 'address' => '自提点：北四环盘古大观广场南门， 包邮'),
//            array('id' => 23, 'ship_price' => 0.0, 'time' => '', 'address' => '自提点：五道口华清嘉园会所果臻棒， 包邮'),
//            array('id' => 27, 'ship_price' => 0.0, 'time' => '', 'address' => '跑腿儿专递（限五环内，运送费到付：每个15元）', 'need_address_remark' => true),
            array('id' => 25, 'ship_price' => 0.0,  'time' => '', 'least_num' => 10,  'address' => '四环内单次满10个可免费送到指定地点', 'need_address_remark' => true),
            array('id' => 26, 'ship_price' => 5.0, 'time' => '', 'least_num' => 20,  'address' => '四环外满20个起送, 运送费：每个5元', 'need_address_remark' => true),
            array('id' => 28, 'ship_price' => 10.0, 'time' => '', 'address' => '昌平自提（超过20个发货), 运送费：每个10元。地址: 昌平区商业街66号世纪联华超市（近商业街西口）13693336613'),
            array('id' => 29, 'ship_price' => 10.0, 'time' => '', 'address' => '顺义自提（超过20个发货), 运送费：每个10元。地址: 顺义区顺白路望泉家园万科四季花城小区42号楼103室（万科四季花城南门旁）'),
            array('id' => 38, 'ship_price' => 10.0, 'time' => '', 'address' => '朝阳劲松自提（超过10个发货), 运送费：每个10元。地址: 劲松二区208楼底商晨阳惠友商店（眉州东坡酒楼劲松店西侧小路向北50米）67720201'),
            array('id' => 39, 'ship_price' => 10.0, 'time' => '', 'address' => '丰台自提（超过10个发货), 运送费：每个10元。地址: 六里桥桥南西行200米西局西路58号院，底商58-31，国烟国酒超市 63770644'),
            array('id' => 40, 'ship_price' => 10.0, 'time' => '', 'address' => '海淀自提（超过10个发货), 运送费：每个10元。地址: 健德门桥东北200米京客隆超市 62059955'),
//            array('id' => 41, 'ship_price' => 10.0, 'time' => '', 'address' => '通州自提（超过20个发货), 运送费：每个10元。地址: 九棵树翠屏里20号楼23 81524153'),
            array('id' => 42, 'ship_price' => 10.0, 'time' => '', 'address' => '西城车公庄自提（超过10个发货), 运送费：每个10元。地址: 西城区展览路12-5号，烟酒销售 88360166'),
            array('id' => 43, 'ship_price' => 10.0, 'time' => '', 'address' => '东城区朝阳门自提（超过10个发货), 运送费：每个10元。地址: 朝阳门内大街97号底商，平价商店 15901495532'),
            array('id' => 44, 'ship_price' => 10.0, 'time' => '', 'address' => '崇文区崇文门自提（超过10个发货), 运送费：每个10元。地址: 崇文门东大街8-2号，蟠桃宫便利店 18511077986'),
        )
    ),
    '240' => array('limit_ship' => true,
        'limit_per_user' => 1,
        'items' => array(
            array('id' => 31, 'ship_price' => 0.0, 'time' => '', 'address' => '腾讯公司希格玛B1交单处'),
        )
    ),

    '259' => array('limit_ship' => true,
        'limit_per_user' => 1,
        'items' => array(
            array('id' => 32, 'ship_price' => 0.0, 'time' => '', 'address' => '北京华宇东升科技园B2-5'),
        )
    ),

    '260' => array('limit_ship' => true,
        'limit_per_user' => 1,
        'items' => array(
            array('id' => 33, 'ship_price' => 0.0, 'time' => '', 'address' => 'ThoughtWorks公司'),
        )
    ),

    '264' => array('limit_ship' => true,
        'limit_per_user' => 1,
        'items' => array(
            array('id' => 34, 'ship_price' => 0.0, 'time' => '', 'address' => '華麗2層7層9-12'),
            array('id' => 35, 'ship_price' => 0.0, 'time' => '', 'address' => '金寶大廈19'),
        )
    ),

    '294' => array('limit_ship' => true,
        'limit_per_user' => 1,
        'items' => array(
            array('id' => 36, 'ship_price' => 0.0, 'time' => '', 'address' => '星光影视园新媒体大厦6层'),
        )
    ),

        '310' => array('limit_ship' => true,
            'limit_per_user' => 1,
            'items' => array(
//                array('id' => 37, 'ship_price' => 0.0, 'time' => '', 'address' =>'世贸天阶时尚大厦六层'),
                array('id' => 45, 'ship_price' => 0.0, 'time' => '', 'address' =>'东大桥8号SOHO南塔 1002 号'),
            )
        )

    );

    private  $pro_num_limit = array(
        //total_limit(0 means none), brand_id, per_user_limit (0 means none)
        '228' => array(100, 13, 1),
        '229' => array(80, 13, 1),
        PRODUCT_ID_CAKE => array(3084, BRAND_ID_CAKE, 0),
        '240' => array(306, 78, 1 ),
        '259' => array(303, 78, 1 ),
        '204' => array(135, 65, 0 ),
        '260' => array(51, 57, 1 ),
        '264' => array(103, 71, 1 ),
        '294' => array(103, 92, 1 ),
        '297' => array(10, 92, 1 ),
        '307' => array(18, 106, 1 ),
        '310' => array(103, 96, 1 ),
        '433' => array(1000, 92, 1 ),
    );


    static public $shi_liu_ids = array(201, 202, 233);

    /**
     *
     * //地区：省份限制或者不限制
     * //统一邮费 或者 按件数相乘
     * //满足多少件包邮
     * //全订单：满多少金额包邮
     *
     * @param $total_price int 订单商品总价
     * @param $singleShipFee
     * @param $num
     * @param $pss
     * @param $context
     * @return mixed
     */
    public static function calculateShipFee($total_price, $singleShipFee, $num, $pss, &$context) {

        $calculated = self::calculate_ship_fee($pss, $total_price, $singleShipFee, $num, $context);
        if ($calculated !== false) {
            return $calculated/100;
        }

//        if ($pid == PRODUCT_ID_RICE_10 && $num >= 2) {
//            return 0.0;
////        } else if ($pid == PRODUCT_ID_CAKE || array_search($pid, self::$shi_liu_ids) !== false) {
////            return $singleShipFee * $num;
//        } else if ($pid == 269 || $pid == 270) { //灰枣和骏枣
//            return ($num > 1) ? 0 : 15;     //check 15 problem:
//        } else if ($pid == 317) { //铁棍山药1斤装
//            return ($num >= 5 ? 0 : $num * $singleShipFee);
//        } else if ($pid == 406) { //呼伦贝尔牛肉干
//            return ($num >= 5 ? 0 : 8);
//        }

        return $singleShipFee;
    }

    public static function calculate_ship_fee($pss, $total_price, $singleShipFee, $num, &$context) {
        if (!empty($pss)) {
            $pss = Hash::combine($pss, '{n}.ShipSetting.type', '{n}.ShipSetting');
            $orderPricePss = $pss[TYPE_ORDER_PRICE];
            if (!empty($orderPricePss) && $total_price * 100 >= $orderPricePss['least_total_price']) {
                return $orderPricePss['ship_fee'];
            }

            $orderFixPss = $pss[TYPE_ORDER_FIXED];
            if (!empty($orderFixPss)) {
                if (empty($context['order_fix_calculated'])) {
                    $context['order_fix_calculated'] = 1;
                    return $orderFixPss['ship_fee'];
                } else return 0;
            }

            $byNumsPss = $pss[TYPE_REDUCE_BY_NUMS];
            if (!empty($byNumsPss) && $num >= $byNumsPss['least_num']) {
                return $byNumsPss['ship_fee'];
            }

            $mulNumsPss = $pss[TYPE_MUL_NUMS];
            if (!empty($mulNumsPss)) {
                return $num * $singleShipFee * 100;
            }
        }

        return false;
    }

//    public static function calculateShipFeeByOrder($shipFee, $brandId, $total_price) {
//        if ($brandId == 130 && $total_price * 100 > 4899) {
//            return 0.0;
//        }else if ($brandId == 126 && $total_price * 100 > 11999) {
//            return 0.0;
//        } else {
//            return $shipFee;
//        }
//    }

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

        if ($promotionId == self::BIN_BIN_SHIP_PROMO_ID) {
            return array(false, $this->BIN_BIN_PROMO['items'][0]);
        }

        foreach($this->specialPromotions as $pid=>$promotions) {
            list($limit_per_user, $addressList) = $this->find_ship_promotion_limit($pid, $promotionId);
            if (!empty($addressList)) {
                return array($pid, $addressList);
            }
        }
        return null;
    }

    public function find_ship_promotion_limit($productId, $promotionId) {

        if ($promotionId == self::BIN_BIN_SHIP_PROMO_ID) {
            return array(false, $this->BIN_BIN_PROMO['items'][0]);
        }

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

    public function findShipPromotions($product_ids, $brand_ids) {
        if (!empty($brand_ids)) {
            if(array_search(self::BIN_BIN_BRAND_ID, $brand_ids) !== false) {
                return $this->BIN_BIN_PROMO;
            }
        }

        foreach($product_ids as $pid) {
            $promotions = $this->specialPromotions[$pid];
            if ($promotions && !empty($promotions)) {
                return $promotions;
            }
        }
        return array();
    }
} 