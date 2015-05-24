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
            array('id' => self::BIN_BIN_SHIP_PROMO_ID, 'address' => '自提：海淀区上地十街辉煌国际大厦2号楼1706室 189-1105-8517'),
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
//            array('id' => 25, 'price' => 150.0,  'time' => '', 'least_num' => 10,  'address' => '四环内单次满10个可免费送到指定地点', 'need_address_remark' => true),
//            array('id' => 26, 'price' => 155.0, 'time' => '', 'least_num' => 20,  'address' => '四环外满20个起送，送到指定地点', 'need_address_remark' => true),
//            array('id' => 28, 'price' => 160.0, 'time' => '', 'address' => '昌平自提；地址: 昌平区商业街66号世纪联华超市（近商业街西口）13693336613'),
//            array('id' => 29, 'price' => 160.0, 'time' => '', 'address' => '顺义自提；地址: 顺义区顺白路望泉家园万科四季花城小区42号楼103室（万科四季花城南门旁）'),
//            array('id' => 38, 'price' => 160.0, 'time' => '', 'address' => '朝阳劲松自提；地址: 劲松二区208楼底商晨阳惠友商店（眉州东坡酒楼劲松店西侧小路向北50米）67720201'),
//            array('id' => 39, 'price' => 160.0, 'time' => '', 'address' => '丰台自提；地址: 六里桥桥南西行200米西局西路58号院，底商58-31，国烟国酒超市 63770644'),
//            array('id' => 40, 'price' => 160.0, 'time' => '', 'address' => '海淀自提；地址: 健德门桥东北200米京客隆超市 62059955'),
////            array('id' => 41, 'ship_price' => 10.0, 'time' => '', 'address' => '通州自提（超过20个发货), 运送费：每个10元。地址: 九棵树翠屏里20号楼23 81524153'),
//            array('id' => 42, 'price' => 160.0, 'time' => '', 'address' => '西城车公庄自提；地址: 西城区展览路12-5号，烟酒销售 88360166'),
////            array('id' => 43, 'price' => 160.0, 'time' => '', 'address' => '东城区朝阳门自提；地址: 朝阳门内大街97号底商，平价商店 15901495532'),
//            array('id' => 44, 'price' => 160.0, 'time' => '', 'address' => '崇文区崇文门自提；地址: 崇文门东大街8-2号，蟠桃宫便利店 18511077986'),
//            array('id' => 45, 'price' => 160.0, 'time' => '', 'address' => '石景山自提 地址：石景山万达广场，石景山区政达路2号1层110CRD 弘浩商贸 68647677'),
//            array('id' => 46, 'price' => 160.0, 'time' => '', 'address' => '大兴亦庄自提 地址：迪龙天天便利店 亦庄新康家园东门 67875215'),
//
//
//            array('id' => 157, 'price' => 160.0, 'address' => '通州区九棵树：九棵树翠屏里20号楼23蓝调沙龙超市 010-81524153'),
//            array('id' => 158, 'price' => 160.0, 'address' => '昌平回龙观：回龙观万润家园东门（回龙观医院西侧）好润佳超市（万润家园）010-62710969'),
//            array('id' => 159, 'price' => 160.0, 'address' => '昌平天通苑：昌平区立水桥龙德紫金大厦三层，龙德紫金超市010-62710969'),
//            array('id' => 160, 'price' => 160.0, 'address' => '朝阳望京：朝阳区花家地东路利景名门102室世纪华联万隆便利店（利景名门）18801371337'),
//            array('id' => 161, 'price' => 160.0, 'address' => '东城朝阳门：东城区朝阳门内大街97号底商，平价商店(朝内大街南京银行对面)15901495532'),
//            array('id' => 162, 'price' => 160.0, 'address' => '昌平清河小营：昌平区清河永泰园新地标小区17号楼（永泰园西门）世纪华联（新地标）13161840478'),
            array('id' => 25, 'price' => 150.0, 'time' => '', 'least_num' => 10,  'address' => '五环内单次满10个可免费送到指定地点', 'need_address_remark' => true),
            array('id' => 26, 'price' => 150.0, 'time' => '', 'least_num' => 20,  'address' => '五环外满20个起送，送到指定地点', 'need_address_remark' => true),
            array('id' => 28,  'time' => '', 'address' => '昌平自提；地址: 昌平区商业街66号世纪联华超市（近商业街西口）13693336613'),
            array('id' => 29,  'time' => '', 'address' => '顺义自提；地址: 顺义区顺白路望泉家园万科四季花城小区42号楼103室（万科四季花城南门旁）'),
            array('id' => 38,  'time' => '', 'address' => '朝阳劲松自提；地址: 劲松二区208楼底商晨阳惠友商店（眉州东坡酒楼劲松店西侧小路向北50米）67720201'),
            array('id' => 39,  'time' => '', 'address' => '丰台自提；地址: 六里桥桥南西行200米西局西路58号院，底商58-31，国烟国酒超市 63770644'),
            array('id' => 40,  'time' => '', 'address' => '海淀自提；地址: 健德门桥东北200米京客隆超市 62059955'),
//            array('id' => 41, 'ship_price' => 10.0, 'time' => '', 'address' => '通州自提（超过20个发货), 运送费：每个10元。地址: 九棵树翠屏里20号楼23 81524153'),
            array('id' => 42,  'time' => '', 'address' => '西城车公庄自提；地址: 西城区展览路12-5号，烟酒销售 88360166'),
//            array('id' => 43, 'price' => 160.0, 'time' => '', 'address' => '东城区朝阳门自提；地址: 朝阳门内大街97号底商，平价商店 15901495532'),
            array('id' => 44,  'time' => '', 'address' => '崇文区崇文门自提；地址: 崇文门东大街8-2号，蟠桃宫便利店 18511077986'),
            array('id' => 45,  'time' => '', 'address' => '石景山自提 地址：石景山万达广场，石景山区政达路2号1层110CRD 弘浩商贸 68647677'),
            array('id' => 46,  'time' => '', 'address' => '大兴亦庄自提 地址：迪龙天天便利店 亦庄新康家园东门 67875215'),


            array('id' => 157,  'address' => '通州区九棵树：九棵树翠屏里20号楼23蓝调沙龙超市 010-81524153'),
            array('id' => 158,  'address' => '昌平回龙观：回龙观万润家园东门（回龙观医院西侧）好润佳超市（万润家园）010-62710969'),
            array('id' => 159,  'address' => '昌平天通苑：昌平区立水桥龙德紫金大厦三层，龙德紫金超市010-84810352'),
            array('id' => 160,  'address' => '朝阳望京：朝阳区花家地东路利景名门102室世纪华联万隆便利店（利景名门）18801371337'),
            array('id' => 161,  'address' => '东城朝阳门：东城区朝阳门内大街97号底商，平价商店(朝内大街南京银行对面)15901495532'),
            array('id' => 162,  'address' => '昌平清河小营：昌平区清河永泰园新地标小区17号楼（永泰园西门）世纪华联（新地标）13161840478'),

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
                array('id' => 65, 'ship_price' => 0.0, 'time' => '', 'address' =>'东大桥8号SOHO南塔 1002 号'),
            )
        ),

        PRODUCT_ID_CAKE_FRUITS => array('limit_ship' => true,
            'items' => array(
//                array('id' => 66, 'price' => 258.0, 'time' => '', 'least_num' => 5,  'address' => '团购满5箱（含），送到指定地点', 'need_address_remark' => true),
//                array('id' => 67, 'price' => 248.0, 'time' => '', 'least_num' => 10,  'address' => '团购满10箱（含），送到指定地点', 'need_address_remark' => true),
//                array('id' => 68, 'price' => 268.0, 'time' => '', 'address' => '昌平自提 地址: 昌平区商业街66号世纪联华超市（近商业街西口）13693336613'),
//                array('id' => 69, 'price' => 268.0, 'time' => '', 'address' => '顺义自提 地址: 顺义区顺白路望泉家园万科四季花城小区42号楼103室（万科四季花城南门旁）'),
//                array('id' => 70, 'price' => 268.0, 'time' => '', 'address' => '朝阳劲松 地址: 劲松二区208楼底商晨阳惠友商店（眉州东坡酒楼劲松店西侧小路向北50米）67720201'),
//                array('id' => 71, 'price' => 268.0, 'time' => '', 'address' => '丰台自提 地址: 六里桥桥南西行200米西局西路58号院，底商58-31，国烟国酒超市 63770644'),
//                array('id' => 72, 'price' => 268.0, 'time' => '', 'address' => '海淀自提 地址: 健德门桥东北200米京客隆超市 62059955'),
//                array('id' => 73, 'price' => 268.0, 'time' => '', 'address' => '西城车公庄自提 地址: 西城区展览路12-5号，烟酒销售 88360166'),
////                array('id' => 74, 'price' => 268.0, 'time' => '', 'address' => '东城区朝阳门自提 地址: 朝阳门内大街97号底商，平价商店 15901495532'),
//                array('id' => 75, 'price' => 268.0, 'time' => '', 'address' => '崇文门蟠桃宫自提 地址: 崇文门东大街8-2号，蟠桃宫便利店 18511077986'),
//                array('id' => 114, 'price' => 268.0, 'time' => '', 'address' => '石景山自提 地址：石景山万达广场，石景山区政达路2号1层110CRD 弘浩商贸 68647677'),
//                array('id' => 117, 'price' => 268.0, 'time' => '', 'address' => '大兴亦庄自提 地址：迪龙天天便利店 亦庄新康家园东门 67875215'),
//
//                array('id' => 133, 'price' => 268.0, 'time' => '', 'address' => '通州区九棵树：九棵树翠屏里20号楼23蓝调沙龙超市 010-81524153'),
//                array('id' => 134, 'price' => 268.0, 'time' => '', 'address' => '昌平回龙观：回龙观万润家园东门（回龙观医院西侧）好润佳超市（万润家园）010-62710969'),
//                array('id' => 135, 'price' => 268.0, 'time' => '', 'address' => '昌平天通苑：昌平区立水桥龙德紫金大厦三层，龙德紫金超市010-62710969'),
//                array('id' => 136, 'price' => 268.0, 'time' => '', 'address' => '朝阳望京：朝阳区花家地东路利景名门102室世纪华联万隆便利店（利景名门）18801371337'),
//                array('id' => 137, 'price' => 268.0, 'time' => '', 'address' => '东城朝阳门：东城区朝阳门内大街97号底商，平价商店(朝内大街南京银行对面)15901495532'),
//                array('id' => 138, 'price' => 268.0, 'time' => '', 'address' => '昌平清河小营:：昌平区清河永泰园新地标小区17号楼（永泰园西门）世纪华联（新地标）13161840478'),
                array('id' => 66,  'time' => '', 'least_num' => 5,  'address' => '团购满5箱（含），送到指定地点', 'need_address_remark' => true),
                array('id' => 67,  'time' => '', 'least_num' => 10,  'address' => '团购满10箱（含），送到指定地点', 'need_address_remark' => true),
                array('id' => 68,  'time' => '', 'address' => '昌平自提 地址: 昌平区商业街66号世纪联华超市（近商业街西口）13693336613'),
                array('id' => 69,  'time' => '', 'address' => '顺义自提 地址: 顺义区顺白路望泉家园万科四季花城小区42号楼103室（万科四季花城南门旁）'),
                array('id' => 70,  'time' => '', 'address' => '朝阳劲松 地址: 劲松二区208楼底商晨阳惠友商店（眉州东坡酒楼劲松店西侧小路向北50米）67720201'),
                array('id' => 71,  'time' => '', 'address' => '丰台自提 地址: 六里桥桥南西行200米西局西路58号院，底商58-31，国烟国酒超市 63770644'),
                array('id' => 72,  'time' => '', 'address' => '海淀自提 地址: 健德门桥东北200米京客隆超市 62059955'),
                array('id' => 73,  'time' => '', 'address' => '西城车公庄自提 地址: 西城区展览路12-5号，烟酒销售 88360166'),
//                array('id' => 74, 'price' => 268.0, 'time' => '', 'address' => '东城区朝阳门自提 地址: 朝阳门内大街97号底商，平价商店 15901495532'),
                array('id' => 75,  'time' => '', 'address' => '崇文门蟠桃宫自提 地址: 崇文门东大街8-2号，蟠桃宫便利店 18511077986'),
                array('id' => 114,  'time' => '', 'address' => '石景山自提 地址：石景山万达广场，石景山区政达路2号1层110CRD 弘浩商贸 68647677'),
                array('id' => 117,  'time' => '', 'address' => '大兴亦庄自提 地址：迪龙天天便利店 亦庄新康家园东门 67875215'),

                array('id' => 133,  'time' => '', 'address' => '通州区九棵树：九棵树翠屏里20号楼23蓝调沙龙超市 010-81524153'),
                array('id' => 134,  'time' => '', 'address' => '昌平回龙观：回龙观万润家园东门（回龙观医院西侧）好润佳超市（万润家园）010-62710969'),
                array('id' => 135,  'time' => '', 'address' => '昌平天通苑：昌平区立水桥龙德紫金大厦三层，龙德紫金超市010-84810352'),
                array('id' => 136,  'time' => '', 'address' => '朝阳望京：朝阳区花家地东路利景名门102室世纪华联万隆便利店（利景名门）18801371337'),
                array('id' => 137,  'time' => '', 'address' => '东城朝阳门：东城区朝阳门内大街97号底商，平价商店(朝内大街南京银行对面)15901495532'),
                array('id' => 138,  'time' => '', 'address' => '昌平清河小营:：昌平区清河永泰园新地标小区17号楼（永泰园西门）世纪华联（新地标）13161840478'),
            )
        ),

        705 => array('limit_ship' => true,
            'items' => array(
//                array('id' => 76, 'time' => '', 'least_num' => 10, 'address' => '西二旗辉煌国际大厦B1层超市发门口  徐庆  18686504547'),
                array('id' => 77, 'time' => '', 'least_num' => 10, 'address' => '海淀西小口路中关村东升科技园果仕芙 张经理 57112013 (最低10盒)'),
                array('id' => 78, 'time' => '', 'least_num' => 10, 'address' => '海淀清河小营电科院旁边超市发 荣浩  18911692346 (最低10盒)'),
//                array('id' => 79, 'time' => '', 'least_num' => 10, 'address' => '海淀中关村银科大厦附近 (最低10盒)'),
//                array('id' => 80, 'time' => '', 'least_num' => 10, 'address' => '海淀五道口搜狐网络大厦附近 (最低10盒)'),
//                array('id' => 81, 'time' => '', 'least_num' => 10, 'address' => '海淀科学院南路搜狐媒体大厦附近 (最低10盒)'),
                array('id' => 86, 'time' => '', 'least_num' => 20,  'address' => '团购满20盒（含），送到指定地点(限北京城区)', 'need_address_remark' => true),
//                array('id' => 77, 'time' => '', 'address' => '西小口东升科技园（北京市海淀区西小口路66）'),
//                array('id' => 78, 'time' => '', 'address' => '微软大厦（中关村丹棱街附近）'),
            )
        ),

        877=>array(
            'limit_ship' => true, 'items' => array(
                array('id'=>79,'time' => '', 'least_num' => 5,  'address' => '购满5个蛋糕（含），送到指定地点(限北京城区)', 'need_address_remark' => true)
            )
        ),

        863 => array('limit_ship' => true,
            'items' => array(
                array('id' => 82, 'time' => '', 'least_num' => 20,  'address' => '团购满20盒（含），送到昌平县城指定地点', 'need_address_remark' => true),
//                array('id' => 77, 'time' => '', 'address' => '西小口东升科技园（北京市海淀区西小口路66）'),
//                array('id' => 78, 'time' => '', 'address' => '微软大厦（中关村丹棱街附近）'),
            )
        ),

        790=>array('limit_ship' => true,
            'items' => array(
                array('id' => 87, 'time' => '', 'address' => '海淀区万泉河路68号紫金大厦好邻居超市   '),
            )
        ),


        822 => array('limit_ship' => true,
            'items' => array(
                array('id' => 98, 'time' => '', 'address' => '昌平自提 地址: 昌平区商业街66号世纪联华超市（近商业街西口）13693336613'),
                array('id' => 99, 'time' => '', 'address' => '顺义自提 地址: 顺义区顺白路望泉家园万科四季花城小区42号楼103室（万科四季花城南门旁）'),
                array('id' => 100, 'time' => '', 'address' => '朝阳劲松 地址: 劲松二区208楼底商晨阳惠友商店（眉州东坡酒楼劲松店西侧小路向北50米）67720201'),
                array('id' => 101, 'time' => '', 'address' => '丰台自提 地址: 六里桥桥南西行200米西局西路58号院，底商58-31，国烟国酒超市 63770644'),
                array('id' => 102, 'time' => '', 'address' => '海淀自提 地址: 健德门桥东北200米京客隆超市 62059955'),
                array('id' => 103, 'time' => '', 'address' => '西城车公庄自提 地址: 西城区展览路12-5号，烟酒销售 88360166'),
//                array('id' => 104, 'time' => '', 'address' => '东城区朝阳门自提 地址: 朝阳门内大街97号底商，平价商店 15901495532'),
                array('id' => 105, 'time' => '', 'address' => '崇文门蟠桃宫自提 地址: 崇文门东大街8-2号，蟠桃宫便利店 18511077986'),
                array('id' => 115,  'time' => '', 'address' => '石景山自提 地址：石景山万达广场，石景山区政达路2号1层110CRD 弘浩商贸 68647677'),
                array('id' => 118,  'time' => '', 'address' => '大兴亦庄自提 地址：迪龙天天便利店 亦庄新康家园东门 67875215'),


                array('id' => 139, 'time' => '', 'address' => '通州区九棵树：九棵树翠屏里20号楼23蓝调沙龙超市 010-81524153'),
                array('id' => 140, 'time' => '', 'address' => '昌平回龙观：回龙观万润家园东门（回龙观医院西侧）好润佳超市（万润家园）010-62710969'),
                array('id' => 141, 'time' => '', 'address' => '昌平天通苑：昌平区立水桥龙德紫金大厦三层，龙德紫金超市010-84810352'),
                array('id' => 142, 'time' => '', 'address' => '朝阳望京：朝阳区花家地东路利景名门102室世纪华联万隆便利店（利景名门）18801371337'),
                array('id' => 143, 'time' => '', 'address' => '东城朝阳门：东城区朝阳门内大街97号底商，平价商店(朝内大街南京银行对面)15901495532'),
                array('id' => 144, 'time' => '', 'address' => '昌平清河小营：昌平区清河永泰园新地标小区17号楼（永泰园西门）世纪华联（新地标）13161840478'),

            )
        ),

        823 => array('limit_ship' => true,
            'items' => array(
                array('id' => 106, 'time' => '', 'address' => '昌平自提 地址: 昌平区商业街66号世纪联华超市（近商业街西口）13693336613'),
                array('id' => 107, 'time' => '', 'address' => '顺义自提 地址: 顺义区顺白路望泉家园万科四季花城小区42号楼103室（万科四季花城南门旁）'),
                array('id' => 108, 'time' => '', 'address' => '朝阳劲松 地址: 劲松二区208楼底商晨阳惠友商店（眉州东坡酒楼劲松店西侧小路向北50米）67720201'),
                array('id' => 109, 'time' => '', 'address' => '丰台自提 地址: 六里桥桥南西行200米西局西路58号院，底商58-31，国烟国酒超市 63770644'),
                array('id' => 110, 'time' => '', 'address' => '海淀自提 地址: 健德门桥东北200米京客隆超市 62059955'),
                array('id' => 111, 'time' => '', 'address' => '西城车公庄自提 地址: 西城区展览路12-5号，烟酒销售 88360166'),
//                array('id' => 112, 'time' => '', 'address' => '东城区朝阳门自提 地址: 朝阳门内大街97号底商，平价商店 15901495532'),
                array('id' => 113, 'time' => '', 'address' => '崇文门蟠桃宫自提 地址: 崇文门东大街8-2号，蟠桃宫便利店 18511077986'),
                array('id' => 116,  'time' => '', 'address' => '石景山自提 地址：石景山万达广场，石景山区政达路2号1层110CRD 弘浩商贸 68647677'),
                array('id' => 119,  'time' => '', 'address' => '大兴亦庄自提 地址：迪龙天天便利店 亦庄新康家园东门 67875215'),

                array('id' => 145, 'time' => '', 'address' => '通州区九棵树：九棵树翠屏里20号楼23蓝调沙龙超市 010-81524153'),
                array('id' => 146, 'time' => '', 'address' => '昌平回龙观：回龙观万润家园东门（回龙观医院西侧）好润佳超市（万润家园）010-62710969'),
                array('id' => 147, 'time' => '', 'address' => '昌平天通苑：昌平区立水桥龙德紫金大厦三层，龙德紫金超市010-84810352'),
                array('id' => 148, 'time' => '', 'address' => '朝阳望京：朝阳区花家地东路利景名门102室世纪华联万隆便利店（利景名门）18801371337'),
                array('id' => 149, 'time' => '', 'address' => '东城朝阳门：东城区朝阳门内大街97号底商，平价商店(朝内大街南京银行对面)15901495532'),
                array('id' => 150, 'time' => '', 'address' => '昌平清河小营：昌平区清河永泰园新地标小区17号楼（永泰园西门）世纪华联（新地标）13161840478'),
            )
        ),

        829 => array('limit_ship' => true,
            'items' => array(
                array('id' => 123, 'time' => '', 'address' => '昌平自提 地址: 昌平区商业街66号世纪联华超市（近商业街西口）13693336613'),
                array('id' => 124, 'time' => '', 'address' => '顺义自提 地址: 顺义区顺白路望泉家园万科四季花城小区42号楼103室（万科四季花城南门旁）'),
                array('id' => 125, 'time' => '', 'address' => '朝阳劲松 地址: 劲松二区208楼底商晨阳惠友商店（眉州东坡酒楼劲松店西侧小路向北50米）67720201'),
                array('id' => 126, 'time' => '', 'address' => '丰台自提 地址: 六里桥桥南西行200米西局西路58号院，底商58-31，国烟国酒超市 63770644'),
                array('id' => 127, 'time' => '', 'address' => '海淀自提 地址: 健德门桥东北200米京客隆超市 62059955'),
                array('id' => 128, 'time' => '', 'address' => '西城车公庄自提 地址: 西城区展览路12-5号，烟酒销售 88360166'),
//                array('id' => 129, 'time' => '', 'address' => '东城区朝阳门自提 地址: 朝阳门内大街97号底商，平价商店 15901495532'),
                array('id' => 130, 'time' => '', 'address' => '崇文门蟠桃宫自提 地址: 崇文门东大街8-2号，蟠桃宫便利店 18511077986'),
                array('id' => 131,  'time' => '', 'address' => '石景山自提 地址：石景山万达广场，石景山区政达路2号1层110CRD 弘浩商贸 68647677'),
                array('id' => 132,  'time' => '', 'address' => '大兴亦庄自提 地址：迪龙天天便利店 亦庄新康家园东门 67875215'),


                array('id' => 151, 'time' => '', 'address' => '通州区九棵树：九棵树翠屏里20号楼23蓝调沙龙超市 010-81524153'),
                array('id' => 152, 'time' => '', 'address' => '昌平回龙观：回龙观万润家园东门（回龙观医院西侧）好润佳超市（万润家园）010-62710969'),
                array('id' => 153, 'time' => '', 'address' => '昌平天通苑：昌平区立水桥龙德紫金大厦三层，龙德紫金超市010-84810352'),
                array('id' => 154, 'time' => '', 'address' => '朝阳望京：朝阳区花家地东路利景名门102室世纪华联万隆便利店（利景名门）18801371337'),
                array('id' => 155, 'time' => '', 'address' => '东城朝阳门：东城区朝阳门内大街97号底商，平价商店(朝内大街南京银行对面)15901495532'),
                array('id' => 156, 'time' => '', 'address' => '昌平清河小营：昌平区清河永泰园新地标小区17号楼（永泰园西门）世纪华联（新地标）13161840478'),
            )
        ),

        1012 => array(
            'limit_ship' => true,
            'items' => array(
                array('id' => 200,  'time' => '', 'least_num' => 10,  'address' => '购满10份（含），送到指定地点', 'need_address_remark' => true),
            )
        ),
    );

    private  $pro_num_limit = array(
        //total_limit(0 means none), brand_id, per_user_limit (0 means none)
        '228' => array(100, 13, 1),
        '229' => array(80, 13, 1),
//        PRODUCT_ID_CAKE => array(3084, BRAND_ID_CAKE, 0),
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
        '706' => array(100, 92, 1 ),
        '829' => array(334, BRAND_ID_CAKE, 0 ),
        //'161' => array(110, 46, 0 ),
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

        return $singleShipFee<0 ? 0:$singleShipFee;
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

    public function is_limit_ship($productId) {
        $promotions = $this->specialPromotions[$productId];
        return ($promotions && !empty($promotions) && $promotions['limit_ship'] === true);
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

    public function findShipPromotions($product_ids, $brand_ids = array()) {
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
