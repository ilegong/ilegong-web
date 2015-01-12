<?php
if (!defined('COMMON_PATH')) {
	define('COMMON_PATH', ROOT . DS . 'lib' . DS);
}
include_once COMMON_PATH.'bootstrap.php';

const WX_HOST = 'www.tongshijia.com';
const WX_JS_API_CALL_URL = 'http://www.tongshijia.com/wxPay/jsApiPay';
const WX_NOTIFY_URL = 'http://www.tongshijia.com/wxPay/notify.html';
const WX_SERVICE_ID_GOTO = 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=200757804&idx=1&sn=90b121983525298a4ac26ee8d6c0bc1c#rd';

const ALI_HOST = 'www.tongshijia.com';
const ALI_ACCOUNT = 'yxg@ilegong.com';

const TRADE_ALI_TYPE = 'ZFB';
const TRADE_WX_API_TYPE = 'JSAPI';

const WX_API_PREFIX  = 'https://api.weixin.qq.com';

const WX_OAUTH_USERINFO = 'snsapi_userinfo';
const WX_OAUTH_BASE = 'snsapi_base';
const WX_STATUS_SUBSCRIBED = 1;
const WX_STATUS_UNSUBSCRIBED = 3;
const WX_STATUS_UNKNOWN = 0;

const CART_ITEM_STATUS_NEW = 0;
const CART_ITEM_STATUS_BALANCED = 1;

const COUPON_STATUS_VALID = 1;
const COUPONITEM_STATUS_TO_USE = 1;
const COUPONITEM_STATUS_USED = 2;
const COUPONITEM_MESSAGE_STATUS_TO_SEND = 0;
const COUPONITEM_MESSAGE_STATUS_SENT = 1;

const BRAND_ID_CAKE = 74;
const PRODUCT_ID_CAKE = 230;
const PRODUCT_ID_RICE_10 = 231;
const TRACK_TYPE_PRODUCT_RICE = 'rebate_231';

const PRODUCT_ID_RICE_BRAND_10 = 13;

const COUPON_TYPE_RICE_1KG = 1;
const COUPON_TYPE_CHZ_30 = 2;
const COUPON_TYPE_CHZ_50 = 3;
const COUPON_TYPE_CHZ_100 = 4;
const COUPON_TYPE_CHZ_90 = 5;
const COUPON_TYPE_CHOUPG_100 = 6;
const COUPON_TYPE_CHOUPG_50 = 7;
const COUPON_TYPE_CHOUPG_30 = 8;

const SHARED_OFFER_STATUS_NEW = 0;
const SHARED_OFFER_STATUS_GOING = 3;
const SHARED_OFFER_STATUS_EXPIRED = 1;
const SHARED_OFFER_STATUS_OUT = 2;

const COUPON_TYPE_TYPE_SHARE_OFFER = 2;
const COUPON_TYPE_TYPE_MAN_JIAN = 3;

const ERROR_CODE_USER_DUP_MOBILE = 801;

const VAL_PRODUCT_NAME_MAX_LEN = 15;

CONST PUBLISH_YES = 1;
CONST PUBLISH_NO = 0;

const PRODUCT_STATUS_WAIT_AUDITING = 3;
const PRODUCT_STATUS_AUDIT_FAILED = 2;
const PRODUCT_STATUS_EXPR = 4;

const PRODUCT_TRY_ING = 1;
const PRODUCT_TRY_SOLD_OUT = 2;

CONST DELETED_YES = 1;
CONST DELETED_NO = 0;

const OP_CATE_HOME = 'home';
const OP_CATE_CATEGORIES = 'categories';

const PRO_TAG_HOTTEST = 1;

const CART_ITEM_TYPE_NORMAL = 1;
const CART_ITEM_TYPE_QUICK_ORDER = 2;
const CART_ITEM_TYPE_TRY = 3;
const CART_ITEM_TYPE_GROUPON_PROM = 4;

const SHICHI_STATUS_OK = 1;
const SHICHI_STATUS_APPLY = 0;


define('FORMAT_DATETIME', 'Y-m-d H:i:s');
define('FORMAT_DATE', 'Y-m-d');
define('FORMAT_DATE_YUE_RI_HAN', 'n月j日');
define('FORMAT_DATEH', 'Y-m-d H');
define('FORMAT_TIME', 'H:i:s');

define('ORDER_STATUS_WAITING_PAY', 0);   //待支付
define('ORDER_STATUS_PAID', 1);         //已支付
define('ORDER_STATUS_SHIPPED', 2);      //已发货
define('ORDER_STATUS_RECEIVED', 3);     //已确认收货
define('ORDER_STATUS_RETURN_MONEY', 4);  //已退款
define('ORDER_STATUS_DONE', 9);         //已完成
define('ORDER_STATUS_CANCEL', 10);      //已取消
define('ORDER_STATUS_CONFIRMED', 11);  //已确认有效，不要再用
define('ORDER_STATUS_TOUSU', 12);   //已投诉， 不要再用，投诉走其他流程

define('ON_SHELVE',PUBLISH_YES);   //已上架
define('OFF_SHELVE',PUBLISH_NO);   //下架
define('IN_CHECK',2);              //审查中
define('IN_SHICHI',3);             //试吃中
define('SHICHI_AND_NO_CHECK',4);   //试吃／审核不通过


//Product 表里设置是这个产品，不论多少都是同一邮费
const TYPE_ORDER_PRICE = 1;  //订单总价满多少包邮
const TYPE_REDUCE_BY_NUMS = 2; //同一商品满几件包邮
const TYPE_ORDER_FIXED = 3; //同订单固定邮费
const TYPE_MUL_NUMS = 4; //每件相乘

const STATUS_GROUP_MEM_PAID = 1;
const STATUS_GROUP_REACHED = 1;

define('CATEGORY_ID_TECHAN', 114);

define('PAYLOG_STATUS_NEW', 0);
define('PAYLOG_STATUS_SUCCESS', 1);
define('PAYLOG_STATUS_FAIL', 2);

define('PAYNOTIFY_STATUS_NEW', 0);
define('PAYNOTIFY_ERR_TRADENO', 1);
define('PAYNOTIFY_STATUS_PAYLOG_UPDATED', 2);
define('PAYNOTIFY_ERR_ORDER_NONE', 3);
define('PAYNOTIFY_ERR_ORDER_STATUS_ERR', 4);
define('PAYNOTIFY_ERR_ORDER_FEE', 5);
define('PAYNOTIFY_STATUS_ORDER_UPDATED', 6);
define('PAYNOTIFY_STATUS_CLOSED', 7);
define('PAYNOTIFY_STATUS_SKIPPED', 8);

define('PAID_DISPLAY_SUCCESS', 'success');
define('PAID_DISPLAY_PENDING', 'pending');

//define('KEY_APPLE_201410',  'apple201410');
//define('KEY_APPLE_201410',  'rice201411');
define('KEY_APPLE_201410',  'normal1');
define('PROFILE_NICK_LEN', 16);
define('PROFILE_NICK_MIN_LEN', 2);
define('MSG_API_KEY','api:key-fdb14217a00065ca1a47b8fcb597de0d'); //发短信密钥
define('APP_REGISTER_MARK', 11); //APP注册用户标示

global $page_style;
global $pages_tpl;
/*  分页样式    */
//style=1 共2991条 200页 当前第1页 [ 1 2 3 4 5 6 7 8 9 10 ... 200 ] 
//style=2 共118条 | 首页 | 上一页 | 下一页 | 尾页 | 65条/页 | 共2页  <select>第1页</select>
$page_style = 1;
$pages_tpl = array(
	'total' => '共%d条',
	'pages' => '共%d页',
	'current_page' => '当前第%d页',
	'first' => '首页',
	'last' => '尾页',
	'pagesize' => '%d条/页',
	'pre_page' => '上一页',
	'next_page' => '下一页',
	'template' => '{total} {pages} {current_page}'
);

$source_appid_map = array(

);

function oauth_wx_source() {
    return 'wx-'.WX_APPID_SOURCE;
}

function oauth_wx_goto($refer_key, $host3g) {
    switch ($refer_key) {
        case "CLICK_URL_TECHAN":
            return "http://$host3g/techan.html";
        case "CLICK_URL_MINE":
            return "http://$host3g/orders/mine.html";
        case "CLICK_URL_SALE_AFTER_SAIL":
            return "http://$host3g/articles/view/377.html";
        case "CLICK_URL_SHICHITUAN":
            return "http://$host3g/shichituan.html";
        case "CLICK_URL_OFFER":
            return "http://$host3g/users/my_offers.html";
        case "CLICK_URL_SHICHI_APPLY":
            return "http://$host3g/shichituans/apply.html";
        default:
    }
    return "$host3g";
}


/**
 * @param $ref
 * @param string $scope
 * @param bool $not_require_info
 * @return string url to Weixin oauth
 */
function redirect_to_wx_oauth($ref, $scope=WX_OAUTH_BASE, $not_require_info = false) {
    $return_uri = 'http://'.WX_HOST.'/users/wx_auth?';
    if (!empty($ref)) {
        $return_uri .= '&referer=' . urlencode($ref);
    }
    if ($not_require_info) {
        $return_uri .= '&nru='. $not_require_info;
    }

    $return_uri = urlencode($return_uri);
    return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . WX_APPID . '&redirect_uri=' . $return_uri . "&response_type=code&scope=$scope&state=0#wechat_redirect";
}


function same_day($time1, $time2) {
    $dt = new DateTime;
    $dt->setTimestamp($time1);
    $day1 = $dt->format(FORMAT_DATE);
    $dt->setTimestamp($time2);
    $day2 = $dt->format(FORMAT_DATE);
    return ($day1 == $day2);
}

function before_than($timeStr1, $timeStr2 = null) {
    $dt1 = DateTime::createFromFormat(FORMAT_DATETIME, $timeStr1);
    $ts1 = $dt1->getTimestamp();

    if ($timeStr2 != null) {
        $dt2 = DateTime::createFromFormat(FORMAT_DATETIME, $timeStr1);
        $ts2 = $dt2->getTimestamp();
    } else {
        $ts2 = time();
    }

    return ($ts1 < $ts2);
}

function filter_invalid_name($name, $def = '神秘人') {
    if (!$name || $name == 'null') {
        $name = $def;
    } else if (strpos($name, '微信用户') === 0) {
        $name = mb_substr($name, 0, 8, 'UTF-8');
    }
    return $name;
}

function calculate_try_price($priceInCent, $uid = 0, $shichituan = null) {
    if ($shichituan == null && $uid) {
        $sctM = ClassRegistry::init('Shichituan');
        $shichituan = $sctM->find_in_period($uid, get_shichituan_period());
    }
    $isShichituan = !empty($shichituan);
    return ($isShichituan ? 99 : $priceInCent)/100;
}

function calculate_price($pid, $price, $currUid) {
    $cr = ClassRegistry::init('SpecialList');
    $specialLists = $cr->has_special_list($pid);
    if (!empty($specialLists)) {
        foreach ($specialLists as $specialList) {
            if ($specialList['type'] == 1) {
                $special = $specialList;
                break;
            }
        }
    }

    if (!empty($special)) {
        $special_rg = array('start' => $special['start'], 'end' => $special['end']);
        if ($special['special']['special_price'] >= 0) {
            list($afford_for_curr_user, $limit_per_user, $total_left) =
                calculate_afford($pid, $currUid, $special['special']['limit_total'], $special['special']['limit_per_user'], $special_rg);
            if ($afford_for_curr_user) {
                $price = $special['special']['special_price'] / 100;
            }
        }

        //TODO: check time (current already checked)
        //CHECK time limit!!!!
        //CHECK AFFORD!
    }

    return $price;
}


class ProductCartItem extends Object {
    public $pid;
    public $num;
    public $price;
    public $name;

    public function __construct($pid, $itemPrice, $num, $used_coupons, $name) {
        $this->pid = $pid;
        $this->price = $itemPrice;
        $this->num = $num;
        $this->name = $name;
        $this->used_coupons = $used_coupons;
    }

    public function total_price() {
        return $this->num * $this->price;
    }

    /**
     * @param ProductCartItem $other
     */
    public function merge($other) {
        if ($this->pid != $other->pid) {
            $msg = "not equals product id to merge a ProductCartItem:";
            $this->log($msg.", src=".json_encode($this).", other=".json_encode($other));
            throw new CakeException($msg);
        }

        $this->num += $other->num;
    }
}

class BrandCartItem {
    public $id;

    /**
     * @var array ProductCartItem
     */
    public $items = array();
    public $used_coupons;

    public function __construct($brandId) {
        $this->id = $brandId;
    }

    public function add_product_item($item) {
        $proItem = $this->items[$item->pid];
        if (empty($proItem)) {
            $this->items[$item->pid] = $item;
        } else {
            $proItem->merge($item);
        }
    }

    public function total_price() {
        $total = 0;
        foreach($this->items as $item) {
            $total += $item->total_price();
        }
        return $total;
    }

    public function total_num() {
        $total = 0;
        foreach($this->items as $item) {
            $total += $item->num;
        }
        return $total;
    }

    public function coupon_applied($couponItemId) {
        return !empty($this->used_coupons) && array_search($couponItemId, $this->used_coupons) !== false;
    }

    public function apply_coupon($couponItemId, $reduce_price, $applying){
        foreach($this->items as $brandItem) {
           //TODO:
        }
    }
}

class OrderCartItem {
    public $order_id;
    public $user_id;
    public $is_try = false;

    /**
     * @var array BrandCartItem
     */
    public $brandItems = array();

    public function add_product_item($brand_id, $pid, $itemPrice, $num, $used_coupons, $name) {
        $brandItem = $this->brandItems[$brand_id];
        if (empty($brandItem)) {
            $brandItem = new BrandCartItem($brand_id);
            $this->brandItems[$brand_id] = $brandItem;
        }
        $brandItem->add_product_item(new ProductCartItem($pid, $itemPrice, $num, $used_coupons, $name));
    }

    public function find_product_item($pid) {
        foreach($this->brandItems as $bid=>$brandItem) {
            foreach($brandItem->items as $productItem) {
                if($productItem->pid == $pid) {
                    return $productItem;
                }
            }
        }
        return null;
    }


    public function total_price() {
        $total = 0.0;
        foreach($this->brandItems as $brandItem) {
            $total += $brandItem->total_price();
        }
        return $total;
    }

    public function apply_coupon($brandId, $coupon) {

        if (empty($coupon)) {
            return false;
        }

        $coup_brand_id = $coupon['Coupon']['brand_id'];
        if ($coup_brand_id && $brandId != $coup_brand_id) {
            return false;
        }

        //TODO: validate more, validate used!

        foreach($this->brandItems as $bid=>$brandItem) {
            if ($bid == $brandId) {
                $brandItem->apply_coupon($coup_brand_id, $coupon['Coupon']['price']);
            }
        }

        return array(true, $this->total_price());

    }
}

function product_name_with_spec($prodName, $specId, $specs) {
    if (!$specId || empty($specs)) {
        return $prodName;
    }

    $specsMap = product_spec_map($specs);
    if (!empty($specsMap) && !empty($specsMap['map'])) {
        $maps = $specsMap['map'][$specId];
        return sprintf("$prodName (%s)", empty($maps['name']) ? '' : $maps['name']);
    } else {
        return $prodName;
    }
}

/**
 * @param $specs
 * @return bool|mixed
 */
function product_spec_map($specs) {
    try {
        $specsMap = !empty($specs) ? json_decode($specs, true) : false;
        //if (!$specsMap) {
            //$error = ("json error: ".json_last_error_msg().": for $specs");
        //}
        return $specsMap;
    }catch (Exception $e) {
        return false;
    }
}


/**
 * @param $uid
 * @param $cookieItems
 * @param $cartsByPid
 * @param $poductModel
 * @param $cartModel
 * @param $session_id
 * @return array cartItemsByPid
 */
function mergeCartWithDb($uid, $cookieItems, &$cartsByPid, $poductModel, $cartModel, $session_id = null) {
    $product_ids = array();
    $nums = array();
    foreach ($cookieItems as $item) {
        list($id, $num, $newSpecId) = explode(':', $item);
        if ($id) {
            $product_ids[] = $id;
            $nums[$id] = $num;
            if(is_numeric($newSpecId)) {
                $specs[$id] = $newSpecId;
            }
        }
    }

    if (empty($product_ids)) { return array(); }

    $products = $poductModel->find_published_products_by_ids($product_ids, array('specs'));
    foreach ($products as $p) {
        $pid = $p['id'];

        $newSpecId = empty($specs[$pid]) ? 0 : $specs[$pid];
        $cartItem =& $cartsByPid[$pid];
        if (empty($cartItem)) {
            $cartItem = array(
                'product_id' => $pid,
                'name' => product_name_with_spec($p['name'], $newSpecId, $p['specs']),
                'coverimg' => $p['Product']['coverimg'],
                'num' => $nums[$pid],
                'price' => calculate_price($p['Product']['id'], $p['Product']['price'], $uid),
                'specId' => $newSpecId,
                'session_id' => $session_id,
            );
            $cartsByPid[$pid] =& $cartItem;
        } else {
            if ($newSpecId == $cartItem['specId']) {
                $cartItem['num'] = $nums[$pid];
                $cartItem['price'] = $p['price'];
                $cartItemId = $cartItem['id'];
            } else {
               //CONSIDER to add a new item in shopping cart!!
                $cartItem['num'] = $nums[$pid];
                $cartItem['price'] = calculate_price($p['id'], $p['price'], $uid);
                $cartItem['name']  = product_name_with_spec($p['name'], $newSpecId, $p['specs']);
                $cartItemId = $cartItem['id'];
                $cartItem['specId'] = $newSpecId;
            }
        }
        $cartItem['creator'] = $uid;

        if (isset($cartItemId) && $cartItemId) {
            $cartModel->id = $cartItemId;
        } else {
            $cartModel->create();
        }

        if($cartModel->save(array('Cart' => $cartItem))){
            $cartItem['id'] = $cartModel->id;
        }
    }
}

function find_latest_clicked_from($buyerId, $pid) {
    //CANNOT same with $newUserId
    if($pid == PRODUCT_ID_RICE_10) {
        $trackLogModel = ClassRegistry::init('TrackLog');
        $tr = $trackLogModel->find('first', array(
            'conditions' => array('from' => $buyerId, 'type' => 'rebate_' . $pid),
            'order' => 'latest_click_time desc'
        ));
        if (!empty($tr)) {
            return $tr['TrackLog']['to'];
        }
    }
    return 0;
}


class ProductCategory{

    public static function product_category_list(){
        $productCategoryListJson = Cache::read('_productcategorylist');
        if (empty($productCategoryListJson)) {
            $productCategoryModel = ClassRegistry::init('ProductTag');
            $productModel = ClassRegistry::init("Product");
            $productCategoryList = $productCategoryModel->find('all',  array('conditions' => array(
                'show_in_home' => 1,
                'published' => 1
            ),
                'order' => 'priority desc'
            ));
            $conditions = array('Product' .'.deleted'=>0, 'Product' .'.published'=>1);
            $conditions['Product' . '.recommend >'] = 0;
            $orderBy = ' Product.recommend desc';
            foreach($productCategoryList as &$tag) {
                //add class image
                $join_conditions = array(
                    array(
                        'table' => 'product_product_tags',
                        'alias' => 'Tag',
                        'conditions' => array(
                            'Tag.product_id = Product.id',
                            'Tag.tag_id' => $tag['ProductTag']['id']
                        ),
                        'type' => 'RIGHT',
                    )
                );
                $productsCount = $productModel->find('count', array(
                        'conditions' => $conditions,
                        'joins' => $join_conditions,
                        'order' => $orderBy,
                        'limit' => ($tag['ProductTag']['size_in_home']>0?$tag['ProductTag']['size_in_home']:6),
                        'page' => 1)
                );
                $tag['ProductCounts'] = $productsCount;
            }
            $productCategoryListJson = json_encode($productCategoryList);
            Cache::write('_productcategorylist', $productCategoryListJson);
        }
        return json_decode($productCategoryListJson, true);
    }

}

class ShipAddress {
//    public static $ship_type = array(
//        101 => '申通',
//        102 => '圆通',
//        103 => '韵达',
//        104 => '顺丰',
//        105 => 'EMS',
//        106 => '邮政包裹',
//        107 => '天天',
//        108 => '汇通',
//        109 => '中通',
//        110 => '全一',
//        111 => '宅急送',
//        112 => '全峰',
//        113 => '快捷',
//    );

    /**
     * @return array keyed with ship type id, value is array of fields for the ship type
     */
    public static function ship_type_list() {
        $ship_types = ShipAddress::ship_types();
        if (is_array($ship_types)) {
            return Hash::combine($ship_types,'{n}.id', '{n}.name');
        } else {
            return false;
        }
    }

    /**
     * @param $com 快递公司
     * @param $nu  快递单号
     */
    public function get_ship_detail($orderInfo){
        $ship_types = ShipAddress::ship_types();
        $ship_type_list = Hash::combine($ship_types,'{n}.company','{n}.name','{n}.id');
        $com = key($ship_type_list[$orderInfo['Order']['ship_type']]);
        $nu = $orderInfo['Order']['ship_code'];
        if($nu=='无'||$nu==''||$nu=='已发货'){
            return null;
        }
        $AppKey = Configure::read('kuaidi100_key');
        //http://api.kuaidi100.com/api?id=[]&com=[]&nu=[]&valicode=[]&show=[0|1|2|3]&muti=[0|1]&order=[desc|asc]
        //http://www.kuaidi100.com/applyurl?key=[]&com=[]&nu=[]
        $url = 'http://www.kuaidi100.com/applyurl?key='.$AppKey.'&com='.$com.'&nu='.$nu;
        //优先使用curl模式发送数据
        if (function_exists('curl_init') == 1) {
            $this->log("Curl can init...");
            $curl = curl_init();
            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL=>$url,
                    CURLOPT_HEADER=>0,
                    CURLOPT_RETURNTRANSFER=>1,
                    CURLOPT_TIMEOUT=>5
                )
            );
            $get_content = curl_exec($curl);
            curl_close($curl);
        }else{
            $this->log("Curl can't init...");
        }
        return $get_content;
    }

    /**
     * @return array keyed with ship type id, value is array of fields for the ship type
     */
    public static function ship_types() {
        $shipTypesJson = Cache::read('_shiptypes');
        if (empty($shipTypesJson)) {
            $shipTypeModel = ClassRegistry::init('ShipType');
            $shipTypes = $shipTypeModel->find('all', array(
                'conditions' => array(
                    'deleted' => 0
                )
            ));
            $shipTypes = Hash::combine($shipTypes, '{n}.ShipType.id', '{n}.ShipType');
            $shipTypesJson = json_encode($shipTypes);
            Cache::write('_shiptypes', $shipTypesJson);
        }
        return json_decode($shipTypesJson, true);
    }
}


function game_uri($gameType, $defUri = '/') {

    if (TRACK_TYPE_PRODUCT_RICE == $gameType) {
        return product_link(PRODUCT_ID_RICE_10, $defUri);
    }

    return "/t/ag/$gameType.html";
}


function add_coupon_for_new($uid) {
//    $ci = ClassRegistry::init('CouponItem') ;
//    $new_user_coupons = array(18483, 18482);
//    $found = $ci->find_coupon_item_by_type_no_join($uid, $new_user_coupons);
//    if (empty($found)) {
//        foreach($new_user_coupons as $coupon_id) {
//            $ci->addCoupon($uid, $coupon_id, $uid, 'new_register');
//        }
//        return false;
//    }
    return false;
}

/**
 * @param $pid
 * @param $defUri
 * @return string
 */
function product_link($pid, $defUri) {
    $linkInCache = Cache::read('link_pro_'.$pid);
    if (!empty($linkInCache)) {
        return $linkInCache;
    }
    $pModel = ClassRegistry::init('Product');
    $p = $pModel->findById($pid);
    return product_link2($p, $defUri);
}

/**
 * @param $p
 * @param $defUri
 * @return string
 */
function product_link2($p, $defUri = '/') {
    if (!empty($p)) {
        $pp = empty($p['Product']) ? $p : $p['Product'];
        $link = "/products/" . date('Ymd', strtotime($pp['created'])) . "/" . $pp['slug'] . ".html";
        Cache::write('link_pro_'.$pp['id'], $link);
        return $link;
    } else {
        return $defUri;
    }
}


function wxDefaultName($name) {
    return notWeixinAuthUserInfo(0, $name);
}

function notWeixinAuthUserInfo($uid, $userName) {
    return strpos($userName, '微信用户') === 0;
}

function filter_weixin_username($name) {
    return notWeixinAuthUserInfo(0, $name) ?  mb_substr($name, 4) : $name;
}

function date_days($timeStr, $addDays = 0) {
    $end = DateTime::createFromFormat(FORMAT_DATETIME, $timeStr);
    if ($addDays) {
        $end->add(new DateInterval('P'.$addDays.'D'));
    }
    return $end;
}

function is_past($timeStr, $addDays = 0) {
    $end = DateTime::createFromFormat(FORMAT_DATETIME, $timeStr);
    if ($addDays) {
        $end->add(new DateInterval('P'.$addDays.'D'));
    }
    return ($end->getTimestamp() < mktime());
}

function coupon_expired($coupon) {
    if(empty($coupon)) {
        return true;
    }
    $end = DateTime::createFromFormat(FORMAT_DATETIME, $coupon['Coupon']['valid_end']);
    if ($end->getTimestamp() <= mktime()) {
        return true;
    }

    $start = DateTime::createFromFormat(FORMAT_DATETIME, $coupon['Coupon']['valid_begin']);
    if ($start->getTimestamp() >= mktime()) {
        return true;
    }

    return false;
}

function special_link($slug) {
    return '/categories/special_list/'.$slug.'.html';
}

function brand_link($brand_id, $params = array()) {
    $brandM = ClassRegistry::init('Brand');
    $brand = $brandM->findById($brand_id);
    $url = (!empty($brand)) ? "/brands/" . date('Ymd', strtotime($brand['Brand']['created'])) . "/" . $brand['Brand']['slug'] . ".html" : '/';

    if(!empty($params) && is_array($params)) {
        $url .= '?';
        foreach($params as $k => $v) {
            $url .= '&'.urlencode($k) . '=' . urlencode($v);
        }
    }

    return $url;
}

function small_thumb_link($imgUrl) {
    return thumb_link($imgUrl, 'thumb_s');
}

function medium_thumb_link($imgUrl) {
    return thumb_link($imgUrl, 'thumb_m');
}

function thumb_link($imgUrl, $type = 'thumb_s') {
    if ($imgUrl && strpos($imgUrl, "/$type/") === false) {
        $r = preg_replace('/(.*files\/20\d+\/)(thumb_[ms]\/)?(\s*)/i', '${1}'.$type.'/${3}', $imgUrl);
        return ($r != null) ? $r : $imgUrl;
    }

    return $imgUrl;
}

/**
 * @param $session SessionComponent
 * @param $error
 */
function setFlashError($session, $error) {
    $session->setFlash($error, 'default', array('class' => 'alert alert-danger'));
}

/**
 * Calculate afford
 * @param $tryId
 * @param $currUid
 * @param null $prodTry
 * @throws CakeException
 * @return array whether afford to current user; limit for current user; total left for all users
 *  $afford_for_curr_user: whether current user can buy
 *  $limit_cur_user, -1 means no limit; 0 means no more; >1 means limit for curr user
 *  $total_left (-1 means no limit, 0 means sold out, more means left)
 */
function afford_product_try($tryId, $currUid, $prodTry = null) {
    if (!$prodTry) {
        $tryM = ClassRegistry::init('ProductTry');
        $prodTry = $tryM->findById($tryId);
    }
    if (!empty($prodTry)) {
        $total_limit = $prodTry['ProductTry']['limit_num'];
        $total_left = $total_limit - $prodTry['ProductTry']['sold_num'];
        $limit_cur_user = $total_left <= 0 ? 0 : 1 - bought_try_by_user($tryId, $currUid);
        return array($total_limit != 0 && $limit_cur_user != 0, $limit_cur_user, $total_left);
    } else {
        return array(false, 0, 0);
    }
}

/**
 * @param $tryId
 * @param $currUid
 * @return mixed
 */
function bought_try_by_user($tryId, $currUid) {
    $shichiM = ClassRegistry::init('OrderShichi');
    return $shichiM->bought_by_curr_user($tryId, $currUid);
}

/**
 * Calculate left for a user buying a product.
 *
 * FIXME: need review!
 * @param $pid
 * @param $currUid int 0 means no current uid.
 * @param $total_limit 0 means no limit
 * @param $limit_per_user 0 means no limit
 * @param array $range , time range
 * @return array limits results:
 *  $afford_for_curr_user: whether current user can buy
 *  $limit_cur_user, -1 means no limit; 0 means no more; >1 means limit for curr user
 *  $total_left (-1 means no limit, 0 means sold out, more means left)
 */
function calculate_afford($pid, $currUid, $total_limit, $limit_per_user, $range = array()) {
    $afford_for_curr_user = true;
    $total_left = -1;
    $left_curr_user = -1;

    $cartModel = ClassRegistry::init('Cart');
    if ($total_limit != 0 || $limit_per_user != 0) {
        $soldCnt = total_sold($pid, $range, $cartModel);
        if ($soldCnt > $total_limit) {
            $afford_for_curr_user = false;
        } else if ($limit_per_user > 0) {
            if ($currUid) {
                $ordersModel = ClassRegistry::init('Order');
                $orderCond = array('deleted' => 0,
                    'published' => 1,
                    'creator' => $currUid,
                    'not' => array('status' => array(ORDER_STATUS_CANCEL, ORDER_STATUS_WAITING_PAY))
                );
                if (!empty($range)) {
                    if (!empty($range['start'])) {
                        $orderCond['Order.pay_time > '] = $range['start'];
                    };
                    if (!empty($range['end'])) {
                        $orderCond['Order.pay_time < '] = $range['end'];
                    };
                }
                $order_ids = $ordersModel->find('list', array(
                    'conditions' => $orderCond,
                    'fields' => array('id', 'id')
                ));
                if (!empty($order_ids)) {
                    $rr = $cartModel->find('first', array(
                        'conditions' => array('order_id' => $order_ids, 'product_id' => $pid, 'deleted' => 0),
                        'fields' => array('sum(num) as total_num')
                    ));
                    $bought_by_curr_user = empty($rr) || empty($rr[0]['total_num']) ? 0 : $rr[0]['total_num'];
                    $left_curr_user = $limit_per_user - $bought_by_curr_user;
                    if ($left_curr_user <= 0) {
                        $afford_for_curr_user = false;
                    }
                } else {
                    $left_curr_user = $limit_per_user;
                }
            } else {
                $left_curr_user = $limit_per_user;
            }
        }
        $total_left = $total_limit - $soldCnt;
        if ($total_left < 0) {
            $total_left = 0;
        }
    }
    return array($afford_for_curr_user, $left_curr_user, $total_left);
}

function clean_total_sold($pid) {
    $cache_sold_key = total_sold_cache_key($pid);
    Cache::delete($cache_sold_key);
}

/**
 * @param $pid
 * @return string
 */
function total_sold_cache_key($pid) {
    $cache_sold_key = 'total_sold_pid_' . $pid;
    return $cache_sold_key;
}

/**
 * get the told sold of a pid
 * @param $pid
 * @param $range
 * @param $cartModel Object , default is null
 * @return array
 */
function total_sold($pid, $range, $cartModel = null) {

    $start = str2date($range['start']);
    $end = str2date($range['end']);

    $cache_sold_key = total_sold_cache_key($pid);
    $cache = Cache::read($cache_sold_key);

    $range_key = $start.'_'.$end;

    if (!empty($cache)) {
        $data = json_decode($cache, true);
        if (array_key_exists($range_key, $data)) {
            return $data[$range_key];
        }
    }

    $cartCond = array('Cart.order_id > 0', 'Cart.product_id' => $pid, 'Cart.deleted' => 0);
    if (!empty($range)) {
        if (!empty($range['start'])) {
            $cartCond['Order.pay_time > '] = $range['start'];
        };
        if (!empty($range['end'])) {
            $cartCond['Order.pay_time < '] = $range['end'];
        };
    }

    if ($cartModel == null) {
        $cartModel = ClassRegistry::init('Cart');
    }

    $rtn = $cartModel->find('first', array(
        'joins' => array(array(
            'table' => 'orders',
            'alias' => 'Order',
            'type' => 'inner',
            'conditions' => array('Order.id=Cart.order_id', 'Order.status != ' . ORDER_STATUS_CANCEL, 'Order.status != ' . ORDER_STATUS_WAITING_PAY),
        )),
        'fields' => 'SUM(Cart.num) as total_num',
        'conditions' => $cartCond));

    $total_sold = empty($rtn) || empty($rtn[0]['total_num']) ? 0 : $rtn[0]['total_num'];

    if(!empty($data)) {
        $data[$range_key] = $total_sold;
    } else {
        $data = array($range_key => $total_sold);
    }

    Cache::write($cache_sold_key, json_encode($data));

    return $total_sold;
}
CakePlugin::load(array(
    'OAuth' => array('routes' => true)
));


function message_send($msg=null, $mobilephone=null){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, MSG_API_KEY);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobilephone, 'message' => $msg.'【朋友说】'));
    $res = curl_exec($ch);
    //{"error":0,"msg":"ok"}
    curl_close($ch);
    return $res;
}

function cake_send_date() {
    $cakeDateM = ClassRegistry::init('CakeDate');
    $send_dates = $cakeDateM->find('all', array(
        'conditions' => array('published' => PUBLISH_YES),
        'order' => 'send_date',
        'field' => 'send_date',
        'limit' => 3,
    ));
    $rtn = array();
    foreach($send_dates as $date) {
        $dt = DateTime::createFromFormat(FORMAT_DATE, $date['CakeDate']['send_date']);
        if (!empty($dt)) {
            $rtn[] = date(FORMAT_DATE_YUE_RI_HAN, $dt->getTimestamp());
        }
    }
    return $rtn;
}

function remove_emoji($text){
    if (empty($text)) { return ""; }
    return preg_replace('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $text);
}