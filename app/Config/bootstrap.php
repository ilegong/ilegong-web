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

const COUPON_STATUS_VALID = 1;
const COUPONITEM_STATUS_TO_USE = 1;
const COUPONITEM_STATUS_USED = 2;
const COUPONITEM_MESSAGE_STATUS_TO_SEND = 0;
const COUPONITEM_MESSAGE_STATUS_SENT = 1;

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

const SHARED_OFFER_STATUS_EXPIRED = 1;

const COUPON_TYPE_TYPE_SHARE_OFFER = 2;


define('FORMAT_DATETIME', 'Y-m-d H:i:s');
define('FORMAT_DATE', 'Y-m-d');

define('ORDER_STATUS_WAITING_PAY', 0);   //待支付
define('ORDER_STATUS_PAID', 1);         //已支付
define('ORDER_STATUS_SHIPPED', 2);      //已发货
define('ORDER_STATUS_RECEIVED', 3);     //已确认收货
define('ORDER_STATUS_RETURN_MONEY', 4);  //已退款， 不要再用
define('ORDER_STATUS_DONE', 9);         //已完成
define('ORDER_STATUS_CANCEL', 10);      //已取消
define('ORDER_STATUS_CONFIRMED', 11);  //已确认有效，不要再用
define('ORDER_STATUS_TOUSU', 12);   //已投诉， 不要再用，投诉走其他流程


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
define('KEY_APPLE_201410',  'rice201411');
define('PROFILE_NICK_LEN', 16);
define('PROFILE_NICK_MIN_LEN', 2);


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
        default:
    }
    return "$host3g";
}


/**
 * @param $ref
 * @param string $scope
 * @param bool $not_require_info
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


function filter_invalid_name($name, $def = '神秘人') {
    if (!$name || $name == 'null') {
        $name = $def;
    } else if (strpos($name, '微信用户') === 0) {
        $name = mb_substr($name, 0, 8, 'UTF-8');
    }
    return $name;
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
 * @return array cartItemsByPid
 */
function mergeCartWithDb($uid, $cookieItems, &$cartsByPid, $poductModel, $cartModel) {
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

    $products = $poductModel->findPublishedProductsByIds($product_ids);
    foreach ($products as $p) {
        $pid = $p['Product']['id'];

        $newSpecId = empty($specs[$pid]) ? 0 : $specs[$pid];
        $cartItem =& $cartsByPid[$pid];
        if (empty($cartItem)) {
            $cartItem = array(
                'product_id' => $pid,
                'name' => product_name_with_spec($p['Product']['name'], $newSpecId, $p['Product']['specs']),
                'coverimg' => $p['Product']['coverimg'],
                'num' => $nums[$pid],
                'price' => $p['Product']['price'],
                'specId' => $newSpecId
            );
            $cartsByPid[$pid] =& $cartItem;
        } else {

            if ($newSpecId == $cartItem['specId']) {
                $cartItem['num'] += $nums[$pid];
                $cartItem['price'] = $p['Product']['price'];
                $cartItemId = $cartItem['id'];
            } else {
               //CONSIDER to add a new item in shopping cart!!
                $cartItem['num'] = $nums[$pid];
                $cartItem['price'] = $p['Product']['price'];
                $cartItem['name']  = product_name_with_spec($p['Product']['name'], $newSpecId, $p['Product']['specs']);
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

class ShipAddress {
    public static $ship_type = array(
        101 => '申通',
        102 => '圆通',
        103 => '韵达',
        104 => '顺丰',
        105 => 'EMS',
        106 => '邮政包裹',
        107 => '天天',
        108 => '汇通',
        109 => '中通',
        110 => '全一',
        111 => '宅急送',
        112 => '全峰',
    );
}


function game_uri($gameType, $defUri = '/') {

    if (TRACK_TYPE_PRODUCT_RICE == $gameType) {
        $pModel = ClassRegistry::init('Product');
        $riceProduct = $pModel->findById(PRODUCT_ID_RICE_10);
        if (!empty($riceProduct)) {
            return "/products/" . date('Ymd', strtotime($riceProduct['Product']['created'])) . "/" . $riceProduct['Product']['slug'] . ".html";
        } else {
            return $defUri;
        }
    }

    return "/t/ag/$gameType.html";
}


function notWeixinAuthUserInfo($uid, $userName) {
    return strpos($userName, '微信用户') === 0;
}

function filter_weixin_username($name) {
    return notWeixinAuthUserInfo(0, $name) ?  mb_substr($name, 4) : $name;
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

function brand_link($brand_id) {
    $brandM = ClassRegistry::init('Brand');
    $brand = $brandM->findById($brand_id);
    if(!empty($brand)) {
        return "/brands/" . date('Ymd', strtotime($brand['Brand']['created'])) . "/" . $brand['Brand']['slug'] . ".html";
    } else {
        return '/';
    }
}