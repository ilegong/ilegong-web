<?php
//support utf8mb4
//mysqli_set_charset($handler, "utf8mb4");

if (!defined('COMMON_PATH')) {
    define('COMMON_PATH', ROOT . DS . 'lib' . DS);
}
include_once COMMON_PATH . 'bootstrap.php';

App::build(array(
    'Controller' => array(APP_PATH . 'Controller/', APP_PATH . 'Controller/ApiController/'),
));

//const WX_HOST = 'www.tongshijia.com';
const WX_JS_API_CALL_URL = 'http://www.tongshijia.com/wxPay/jsApiPay';
const WX_JS_LOGISTICS_API_CALL_URL = 'http://www.tongshijia.com/wxPay/logistics_order_pay';
const WX_NOTIFY_URL = 'http://www.tongshijia.com/wxPay/notify.html';
const WX_LOGISTICS_NOTIFY_URL = 'http://www.tongshijia.com/wxPay/logistics_notify.html';
const WX_SERVICE_ID_GOTO = 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=200757804&idx=1&sn=90b121983525298a4ac26ee8d6c0bc1c#rd';

const ALI_HOST = 'www.tongshijia.com';
const ALI_ACCOUNT = 'yxg@ilegong.com';

const ALI_CREATE_AVATAR_URL = 'http://images.tongshijia.com/download_avatar';

const TRADE_ALI_TYPE = 'ZFB';
const TRADE_WX_API_TYPE = 'JSAPI';
const TRADE_WX_NATIVE_API_TYPE = 'Native';

const WX_API_PREFIX = 'https://api.weixin.qq.com';

const WX_OAUTH_USERINFO = 'snsapi_userinfo';
const WX_OAUTH_BASE = 'snsapi_base';
const WX_STATUS_SUBSCRIBED = 1;
const WX_STATUS_UNSUBSCRIBED = 3;
const WX_STATUS_NO_WX = 4;
const WX_STATUS_UNKNOWN = 0;

const CART_ITEM_STATUS_NEW = -1;
const CART_ITEM_STATUS_BALANCED = 0;

const COUPON_STATUS_VALID = 1;
const COUPONITEM_STATUS_TO_USE = 1;
const COUPONITEM_STATUS_USED = 2;
const COUPONITEM_MESSAGE_STATUS_TO_SEND = 0;
const COUPONITEM_MESSAGE_STATUS_SENT = 1;

const WESHARE_DZX_ID = 6951;
const WESHARE_DZX_SHARED_OFFER_ID = 22628;

const SHARED_OFFER_STATUS_NEW = 0;
const SHARED_OFFER_STATUS_GOING = 3;
const SHARED_OFFER_STATUS_EXPIRED = 1;
const SHARED_OFFER_STATUS_OUT = 2;
//red packet same carts num
const COUPON_TYPE_TYPE_SHARE_OFFER = 2;
//least price global can use all
const COUPON_TYPE_TYPE_MAN_JIAN = 3;

const ERROR_CODE_USER_DUP_MOBILE = 801;

const VAL_PRODUCT_NAME_MAX_LEN = 60; //商品名称的最大长度

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
const CART_ITEM_TYPE_TUAN = 5; //团购加入购物车类型
const CART_ITEM_TYPE_TUAN_SEC = 6;

const SHICHI_STATUS_OK = 1;
const SHICHI_STATUS_APPLY = 0;

const CSAD_PHONE = '010-56245991';

const SPEC_PARAM_KEY_COMM = '_pys_add_comment';
const SPEC_PARAM_KEY_SHICHI_COMM = '_pys_add_shichi_comment';


const SCAN_TICKET_CAOMEI = 'gQHP8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzZIV1BfNWJsb1JyWUF1NTJDRmxvAAIEmH7AVAMEAAAAAA==';

const SCAN_TICKET_QRCODE_PAY = 'gQGN7zoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xLzczVnVuUXZsVUJvcC11a0E2VmxvAAIEbiMXVwMEAAAAAA==';

const PYS_OAUTH_CLIENT_ID = 'NTQ5NTE5MGViMTgzMDUw';

const INSTALL_APP_MARK = 1;
const UN_INSTALL_APP_MARK = 0;

const COMMENT_AWARD_BASE_PRICE = 100;
const COMMENT_EXTRA_LIMIT = 5;
const COMMENT_AWARD_SCORE = 100;
const COMMENT_EXTRA_SCORE = 100;
const COMMENT_SHOW_STATUS = 1;

const COMMENT_LIMIT_IN_PRODUCT_VIEW = 5;

const ORDER_COMMENTED = 1;

const TRY_GLOBAL_SHOW = 1;

const PYS_M_TUAN = 34;

const IS_BIG_TUAN = 1;

const RECOMMEND_TAG_ID = 23;

const TUAN_CONSIGNMENT_TYPE = 2;

const SHIP_TYPE_ZITI = 1;
const SHIP_TYPE_SFDF = 5;
const SHIP_TYPE_KUAIDI = 6;
const ZITI_TAG = 'ziti';
const KUAIDI_TAG = 'kuaidi';

const PYS_BRAND_ID = 92;
const PYS_BY_PRICE = 99;
const PYS_SHIP_FEE = 15;

const POOL_PRODUCT_PUBLISH = 1;
const POOL_PRODUCT_UN_PUBLISH = 0;
const POOL_PRODUCT_DRAFT = 2;

const WESHARE_STATUS_DELETED = -1;
const WESHARE_STATUS_NORMAL = 0;
const WESHARE_STATUS_STOP = 1;
const WESHARE_STATUS_DRAFT = 10;

const WESHARE_SETTLEMENT_NO = 0;
const WESHARE_SETTLEMENT_YES = 1;

const SEND_TEMPLATE_MSG_ACTIVE_STATUS = 1;

const COMMENT_SHARE_TYPE = 'Share'; //评论的类型

//weshare order status
const WESHARE_ORDER_STATUS_WAIT_SHIP = 0;
const WESHARE_ORDER_STATUS_SHIPPED = 1;

const SHARE_SHIP_OPTION_OFFLINE_STORE = 1;
const SHARE_SHIP_OPTION_OFFLINE_ADDRESS = 2;

const SUB_SHARER_REASON_TYPE_FROM_USER_CENTER = 'ShareUserInfo'; // 标示用户关注朋友说的原因 来向
const SUB_SHARER_REASON_TYPE_FROM_SHARE_INFO = 'ShareInfo'; // 标示用户关注朋友说的原因 来向
const SUB_SHARER_REASON_TYPE_FROM_SPREAD = 'Spread';

const SAE_IMAGES_FILE_PATH = 'http://images.tongshijia.com';
const ALIYUN_AVATAR_DOMAIN = 'http://static.tongshijia.com/avatar/';
const ALIYUN_AVATAR_PREFIX = 'http://static.tongshijia.com';

const ELK_REDIS_PORT = 33423;

const ORDER_FLAG_VIRTUAL_FLAG = 19;

define('FORMAT_DATETIME', 'Y-m-d H:i:s');
define('FORMAT_DATE', 'Y-m-d');
define('FORMAT_DATE_YUE_RI_HAN', 'n月j日');
define('FORMAT_DATEH', 'Y-m-d H');
define('FORMAT_TIME', 'H:i:s');

define('ORDER_STATUS_WAITING_PAY', 0); //待支付
define('ORDER_STATUS_PAID', 1); //已支付
define('ORDER_STATUS_SHIPPED', 2); //已发货
define('ORDER_STATUS_RECEIVED', 3); //已确认收货
define('ORDER_STATUS_RETURN_MONEY', 4); //已退款
define('ORDER_STATUS_DONE', 9); //已完成
define('ORDER_STATUS_CANCEL', 10); //已取消
define('ORDER_STATUS_CONFIRMED', 11); //已确认有效，不要再用
define('ORDER_STATUS_TOUSU', 12); //已投诉， 不要再用，投诉走其他流程

define('ORDER_STATUS_COMMENT', 16); //待评价
define('ORDER_STATUS_RETURNING_MONEY', 14); //退款中

define('ORDER_STATUS_PREPAID', 25); //预付款 待确认价格
define('ORDER_STATUS_PREPAID_TODO', 26);//预付款订单待处理
define('ORDER_STATUS_PREPAID_DONE', 27);//预付款处理成功
define('ORDER_STATUS_REFUND_TODO', 28);//付款 退款 处理
define('ORDER_STATUS_REFUND_DONE', 29);//预付款退款
define('ORDER_STATUS_REFUND', 30);//预付款比较大于实际金额 需要退款 生成退款订单 只作为标记 不进行实际操作

define('ORDER_STATUS_SPLIT', 20);
define('ORDER_STATUS_VIRTUAL', 21);

define('ON_SHELVE', PUBLISH_YES); //已上架
define('OFF_SHELVE', PUBLISH_NO); //下架
define('IN_CHECK', 2); //审查中
define('IN_SHICHI', 3); //试吃中
define('SHICHI_AND_NO_CHECK', 4); //试吃／审核不通过
//分享缓存的key
const SHARER_LEVEL_CACHE_KEY = 'sharer_level_cache_key';
const SHARE_USER_SUMMERY_CACHE_KEY = 'share_user_summery_cache_key'; //分享用户的统计
const SHARER_FANS_DATA_CACHE_KEY = 'sharer_fans_data_cache_key'; //分享用户粉丝的缓存
const SHARER_FOCUS_DATA_CACHE_KEY = 'sharer_focus_data_cache_key'; //用户关注信息的缓存
const USER_SHARE_COMMENTS_DATA_CACHE_KEY = 'user_share_comments_data_cache_key';//用户评论的缓存数据
const SHARER_ALL_COMMENT_DATA_CACHE_KEY = 'sharer_all_comment_data_cache_key';//分享者的所有评论
const SHARE_ORDER_DATA_CACHE_KEY = 'share_order_data_cache_key';
const SHARE_ORDER_PAGE_INFO_DATA_CACHE_KEY = 'share_order_page_info_data_cache_key'; //分享订单分页信息
const SHARE_DETAIL_VIEW_ORDER_DATA_CACHE_KEY = 'share_detail_view_order_data_cache_key';//分享详情页面订单管理
const SHARE_ORDER_COUNT_DATA_CACHE_KEY = 'share_order_count_data_cache_key';//分享订单数量缓存
const SHARER_COMMENT_COUNT_DATA_CACHE_KEY = 'sharer_comment_count_data_cache_key';//分享者的爱心评价数量
const SHARE_DETAIL_DATA_CACHE_KEY = 'share_detail_data_cache_key';//分享详情信息缓存
const SHARE_DETAIL_DATA_WITH_TAG_CACHE_KEY = 'share_detail_data_with_tag_cache_key';//分享详情信息缓存
const SHARER_CAN_USE_OFFLINE_STORE_CACHE_KEY = 'share_can_user_offline_store_cache_key';//能否使用好邻居自提点缓存
const SHARE_SHIP_SETTINGS_CACHE_KEY = 'share_ship_setting_cache_key';//分享的快递设置
const USER_SHARE_INFO_CACHE_KEY = 'user_share_info_cache_key';//分享用户中心的缓存
const SHARE_RECOMMEND_DATA_CACHE_KEY = 'share_recommend_data_key';//分享推荐缓存
const OPT_LOG_OLDEST_TIME_CACHE_KEY = 'opt_log_oldest_time_cache_key';//动态最新
const OPT_LOG_LAST_TIME_CACHE_KEY = 'opt_log_last_time_cache_key';
const LAST_OPT_LOG_DATA_CACHE_KEY = 'last_opt_log_data_cache_key';
const OPT_LOG_COMBINE_DATA_CACHE_KEY = 'opt_log_combine_data_cache_key';
const SHARER_MOBILE_PHONE_CACHE_KEY = 'sharer_mobile_phone_cache_key';
const SIMPLE_SHARE_INFO_CACHE_KEY = 'simple_share_info_cache_key';
const SHARER_TAGS_DATA_CACHE_KEY = 'sharer_tags_data_cache_key';
const SHARE_OFFLINE_ADDRESS_SUMMERY_DATA_CACHE_KEY = 'share_offline_address_summery_data_cache_key'; //分享线下自提点 购买汇总数据缓存
const SHARE_OFFLINE_ADDRESS_BUY_DATA_CACHE_KEY = 'share_offline_address_buy_data_cache_key';//分享线下自提点 购买信息
const GROUP_SHARE_ORDER_SUMMERY_DATA_CACHE_KEY = 'group_share_order_summery_data_cache_key'; //子分享汇总数据的缓存
const SHARE_MANAGE_USER_OPEN_ID_DATA_CACHE_KEY = 'share_manage_user_open_id_data_cache_key';//分享管理者的openId缓存
const USER_SHARE_PAGE_INFO_CACHE_KEY = 'user_share_page_info_cache_key'; //缓存对应用户对应分享的分页信息
const USER_SHARE_ORDER_INFO_CACHE_KEY = 'user_share_order_info_cache_key';//缓存对应用户对应分享的订单信息
const USER_SHARE_ORDER_INFO_FIRST_PAGE_CACHE_KEY = 'user_share_order_info_first_page_cache_key';//缓存对应用户对应分享的第一页订单信息
const USER_RECOMMEND_WESHARES_CACHE_KEY = 'user_recommend_weshares_cache_key';//团长推荐的分享
const SHARE_BUY_SUMMERY_INFO_CACHE_KEY = 'share_buy_summery_info_cache_key';//分享统计信息
const SHARE_REFER_SHARE_IDS_CACHE_KEY = 'share_refer_share_ids_cache_key';
const HX_TOKEN_CACHE_KEY = 'hx_token_cache_key';
const INDEX_VIEW_PRODUCT_CACHE_KEY = 'index_products_cache_key';
const INDEX_VIEW_BANNER_CACHE_KEY = 'index_view_banner_cache_key';
const INDEX_PRODUCTS_BY_TAG_CACHE_KEY = 'index_products_by_tag_cache_key';
const INDEX_PRODUCT_SUMMARY_CACHE_KEY = 'index_products_summary_cache_key';
const SHARE_ORDER_COUNT_SUM_CACHE_KEY = 'share_order_count_sum_cache_key';
const SHARE_COMMENT_COUNT_SUM_CACHE_KEY = 'share_comment_count_sum_cache_key';
const USER_CONSIGNEES_CACHE_KEY = 'user_consignees_cache_key';
//Product 表里设置是这个产品，不论多少都是同一邮费
const TYPE_ORDER_PRICE = 1; //订单总价满多少包邮
const TYPE_REDUCE_BY_NUMS = 2; //同一商品满几件包邮
const TYPE_ORDER_FIXED = 3; //同订单固定邮费
const TYPE_MUL_NUMS = 4; //每件相乘

const STATUS_GROUP_MEM_PAID = 1;
const STATUS_GROUP_REACHED = 1;

//团购地址地址
const STATUS_CONSIGNEES_TUAN = 2;
//自提地址
const STATUS_CONSIGNEES_TUAN_ZITI = 3;
//拼团地址
const STATUS_CONSIGNEES_PINTUAN = 5;

//分享地址
const TYPE_CONSIGNEES_SHARE = 0;
//分享自提
const TYPE_CONSIGNEES_SHARE_ZITI = 1;
//好邻居
const TYPE_CONSIGNEE_SHARE_OFFLINE_STORE = 2;


const SHARE_COUPON_OFFER_TYPE = -1;
const SHARE_SHIP_KUAIDI = 0;
const SHARE_SHIP_SELF_ZITI = 1;
const SHARE_SHIP_PYS_ZITI = 2;
const SHARE_SHIP_GROUP = 3;
const SHARE_SHIP_PYS_ZITI_TAG = 'pys_ziti';
const SHARE_SHIP_SELF_ZITI_TAG = 'self_ziti';
const SHARE_SHIP_KUAIDI_TAG = 'kuai_di';
const SHARE_SHIP_GROUP_TAG = 'pin_tuan';
const RECOMMEND_SHARE = 1;

const OPT_LOG_CREATE_SHARE = 1;
const OPT_LOG_SHARE_RECOMMEND = 2;
const OPT_LOG_SHARE_BUY = 3;
const OPT_LOG_SHARE_COMMENT = 4;
const OPT_LOG_START_GROUP_SHARE = 5;
const OPT_LOG_PINTUAN_SUCCESS = 6;


const ORDER_TRIGGER_GROUP_SHARE_TYPE = 1;
//同一个地址拼团
const SHARE_OFFLINE_ADDRESS_SHIP_FEE = 500;
const SHARE_OFFLINE_ADDRESS_SHIP_FEE_SIMPLE = 5;
const SHARE_GROUP_REBATE_MONEY = 500;

const COMMENT_SHARE_ORDER_COUPON_MONEY = 50;//评论分享订单默认领取5毛红包

//返利的类型
const DEFAULT_REBATE_TYPE = 0; //默认返利
const GROUP_SHARE_BUY_REBATE_TYPE = 1;//线下自提返利
const PROXY_USER_PAID_REBATE_TYPE = 2;//团长购买返利

const SHARE_FAQ_READ = 1;
const SHARE_FAQ_UNREAD = 0;


const USER_SUB_LOG_TYPE = 0;
const USER_UN_SUB_LOG_TYPE = 1;

//数据统计类型
const COLLECT_DATA_PINTUAN_TYPE = 0;

const DELIVERY_UNIT_COUNT_TYPE = 0; //按件数计算运费
const DELIVERY_UNIT_WEIGHT_TYPE = 1; //按重量计算运费

const HX_APP_NAME = 'pyjia';
const HX_CLIENT_ID = 'YXA6zAZWMLnYEeWBR61HoGXdXA';
const HX_CLIENT_SECRET = 'YXA6rKa-1NpmgnMyAcfJBqaVtVJjCtg';

const WX_CLICK_CUSTOM_SERVICE = 'V1001_C_S';

const HX_CHAT_GROUP_TARGET_TYPE = 'chatgroups';
const HX_CHAT_SINGLE_TARGET_TYPE = 'users';
const HX_CHAT_ROOM_TARGET_TYPE = 'chatrooms';

const USER_REBATE_MONEY_GOT = 1;

const USER_REBATE_MONEY_USE = 2;


const YLB_QR_CODE_SCENE_ID="y20160726"; //易拉宝二维码标示
const XCY_QR_CODE_SCENE_ID="x20160726"; //宣传页二维码标示

const PYS_CUSTOMER_SERVICE_ID = 711503;

define('CATEGORY_ID_TECHAN', 114);

define('PAYLOG_STATUS_NEW', 0);
define('PAYLOG_STATUS_FAIL', 1);
define('PAYLOG_STATUS_SUCCESS', 2);

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
define('KEY_APPLE_201410', 'normal1');
define('PROFILE_NICK_LEN', 16);
define('PROFILE_NICK_MIN_LEN', 2);
define('MSG_API_KEY', 'api:key-fdb14217a00065ca1a47b8fcb597de0d'); //发短信密钥
define('APP_REGISTER_MARK', 11); //APP注册用户标示
define('SERVICE_LINE_PHONE', '010-56245991');
define('SERVICE_LINE', '<a href="tel:01056245991">010-56245991</a>');

define('MSG_LOG_RECOMMEND_TYPE', 1);
define('MSG_LOG_NOTIFY_TYPE', 2);
define('MSG_LOG_PINTUAN_TYPE', 3);

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

$source_appid_map = array();

$order_after_paid_status = array(ORDER_STATUS_PAID, ORDER_STATUS_DONE, ORDER_STATUS_RECEIVED, ORDER_STATUS_SHIPPED);


function get_agency_uid() {
    return array(411402, 633345, 146, 6799, 805934, 660240, 810892);
}

function oauth_wx_source() {
    return 'wx-' . WX_APPID_SOURCE;
}

function oauth_wx_goto($refer_key, $host3g) {
    switch ($refer_key) {
        case "CLICK_URL_TECHAN":
            return "http://$host3g/weshares/index.html?_sl=wx.menu.h_redirect";
        case "CLICK_URL_ADD_SHARE":
            return "http://$host3g/weshares/add.html?_sl=wx.menu.h_redirect";
        case "MY_SHARE_INFO":
            return "http://$host3g/weshares/user_share_info.html?_sl=wx.menu.h_redirect";
        case "CLICK_URL_MINE":
            return "http://$host3g/weshares/user_share_info.html?_sl=wx.menu.h_redirect";
        case "CLICK_URL_SALE_AFTER_SAIL":
            return "http://$host3g/articles/view/377.html";
        case "CLICK_URL_SHICHITUAN":
            return "http://$host3g/weshares/index.html?_sl=wx.menu.h_redirect";
        case "CLICK_URL_COUPON":
            return "http://$host3g/users/my_coupons.html?_sl=wx.menu.coupon";
        case "CLICK_URL_SHICHI_APPLY":
            return "http://$host3g/weshares/index.html?_sl=wx.menu.h_redirect";
        case "CLICK_URL_REFER":
            return "http://$host3g/refer/index.html?_sl=wx.menu.refer";
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
function redirect_to_wx_oauth($ref, $scope = WX_OAUTH_USERINFO, $not_require_info = false) {
    $return_uri = 'http://' . WX_HOST . '/users/wx_auth?';
    if (!empty($ref)) {
        $return_uri .= '&referer=' . urlencode($ref);
    }
    if ($not_require_info) {
        $return_uri .= '&nru=' . $not_require_info;
    }

    $return_uri = urlencode($return_uri);
    return 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . WX_APPID . '&redirect_uri=' . $return_uri . "&response_type=code&scope=$scope&state=0#wechat_redirect";
}

/**
 * @param $time1
 * @param $time2
 * @return bool
 * 判断是否是同一天
 */
function same_day($time1, $time2) {
    $dt = new DateTime;
    $dt->setTimestamp($time1);
    $day1 = $dt->format(FORMAT_DATE);
    $dt->setTimestamp($time2);
    $day2 = $dt->format(FORMAT_DATE);
    return ($day1 == $day2);
}

/**
 * @param $timeStr1
 * @param null $timeStr2
 * @return bool
 * 时间是否早于某天
 */
function before_than($timeStr1, $timeStr2 = null) {
    if (!$timeStr1) {
        return false;
    }

    $dt1 = DateTime::createFromFormat(FORMAT_DATETIME, $timeStr1);
    if (!$dt1) {
        return false;
    }

    if ($timeStr2 != null) {
        $dt2 = DateTime::createFromFormat(FORMAT_DATETIME, $timeStr2);
        $ts2 = $dt2->getTimestamp();
    } else {
        $ts2 = time();
    }

    $ts1 = $dt1->getTimestamp();

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
    return ($isShichituan ? 99 : $priceInCent) / 100;
}

function special_cake_users($uid) {
    return /*$uid == 699919
    ||*/
        $uid == 708029 /*|| $uid == 632*/
        ; //Special user provided by Agnes(Li Hainan)
}


function promo_code_new_user($pids) {
    return ((is_array($pids) && count($pids) == 1 && $pids[0] == PRODUCT_ID_CAKE) || ($pids == PRODUCT_ID_CAKE));
}

function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * @param $pid
 * @param $price
 * @param $currUid
 * @param $num
 * @param int $cart_id
 * @param null $pp special price by ShipPromotion
 * @return array array of price && specialId
 */
function calculate_price($pid, $price, $currUid, $num, $cart_id = 0, $pp = null, $tuan_param = array()) {


    $tuan_buy_id = $tuan_param['tuan_buy_id'];
    //优先级 first
    if (!empty($tuan_buy_id)) {
        $tuanBuyM = ClassRegistry::init('TuanBuying');
        $tuanBuy = $tuanBuyM->find('first', array(
            'conditions' => array(
                'id' => $tuan_buy_id
            )
        ));
        $tuan_price = $tuanBuy['TuanBuying']['tuan_price'];
        $tuan_price = floatval($tuan_price);
        if ($tuan_price >= 0) {
            return array($tuan_price,);
        }
    }
    //优先级 second
    $tuan_product_id = $tuan_param['product_id'];
    if (!empty($tuan_product_id)) {
        $tuan_product_price = getTuanProductPrice($tuan_product_id);
        if ($tuan_product_price >= 0) {
            return array($tuan_product_price,);
        }
    }

    if (!empty($pp) && isset($pp['price'])) {
        if (PRODUCT_ID_CAKE == $pid && special_cake_users($currUid)) {
            return array($pp['price'] - 20,);
        } else {
            return array($pp['price'],);
        }
    }

    if (PRODUCT_ID_CAKE == $pid && special_cake_users($currUid)) {
        return array($price - 20,);
    }


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
        $special_rg = range_by_special($special);
        if ($special['special']['special_price'] >= 0) {
            list($afford_for_curr_user, $limit_per_user, $total_left) =
                calculate_afford($pid, $currUid, $special['special']['limit_total'], $special['special']['limit_per_user'], $special_rg);
            if ($afford_for_curr_user) {
                if (($special['special']['least_num'] <= 0 || $num >= $special['special']['least_num'])
                    && ($special['special']['limit_per_user'] <= 0 || $special['special']['limit_per_user'] >= $num)
                ) {
                    $price = $special['special']['special_price'] / 100;
                }
                return array($price, $special['special']['id']);
            }
        }

        //TODO: check time (current already checked)
        //CHECK time limit!!!!
        //CHECK AFFORD!
    }

    return array($price, null);
}


class ProductCartItem extends Object {
    public $cartId;
    public $pid;
    public $num;
    public $price;
    public $name;
    public $img;

    public function __construct($cartItem, $itemPrice, $num, $used_coupons, $pid, $published = true) {
        $this->cartId = $cartItem['id'];
        $this->pid = $cartItem['product_id'];
        $this->price = $itemPrice;
        $this->num = $num;
        $this->name = $cartItem['name'];
        $this->used_coupons = $used_coupons;
        $this->published = $published;
        $this->img = $cartItem['coverimg'];
        $this->specId = $cartItem['specId'];
        $this->consignment_date = $cartItem['consignment_date'];
        $this->send_date = $cartItem['send_date'];
    }

    public function total_price() {
        return $this->num * $this->price;
    }

    /**
     * @param ProductCartItem $other
     */
    public function merge($other) {
        if ($this->cartId != $other->cartId) {
            $msg = "not equals product id to merge a ProductCartItem:";
            $this->log($msg . ", src=" . json_encode($this) . ", other=" . json_encode($other));
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
        $proItem = $this->items[$item->cartId];
        if (empty($proItem)) {
            $this->items[$item->cartId] = $item;
        } else {
            $proItem->merge($item);
        }
    }

    public function total_price() {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->total_price();
        }
        return $total;
    }

    public function total_num() {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->num;
        }
        return $total;
    }

    public function coupon_applied($couponItemId) {
        return !empty($this->used_coupons) && array_search($couponItemId, $this->used_coupons) !== false;
    }

    public function apply_coupon($couponItemId, $reduce_price, $applying) {
        foreach ($this->items as $brandItem) {
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

    public function add_product_item($brand_id, $cartItem, $itemPrice, $num, $used_coupons, $published = true) {
        $brandItem = $this->brandItems[$brand_id];
        if (empty($brandItem)) {
            $brandItem = new BrandCartItem($brand_id);
            $this->brandItems[$brand_id] = $brandItem;
        }
        $brandItem->add_product_item(new ProductCartItem($cartItem, $itemPrice, $num, $used_coupons, $published));
    }

    public function find_product_item($cartId) {
        foreach ($this->brandItems as $bid => $brandItem) {
            foreach ($brandItem->items as $productItem) {
                if ($productItem->cartId == $cartId) {
                    return $productItem;
                }
            }
        }
        return null;
    }

    public function count_total_num($pid) {
        $num = 0;
        foreach ($this->brandItems as $bid => $brandItem) {
            foreach ($brandItem->items as $productItem) {
                if ($productItem->pid == $pid) {
                    $num += $productItem->num;
                }
            }
        }
        return $num;
    }

    public function list_cart_id() {
        $cart_ids = array();
        foreach ($this->brandItems as $bid => $brandItem) {
            foreach ($brandItem->items as $productItem) {
                $cart_ids[] = $productItem->cartId;
            }
        }
        return $cart_ids;
    }


    public function total_price() {
        $total = 0.0;
        foreach ($this->brandItems as $brandItem) {
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

        foreach ($this->brandItems as $bid => $brandItem) {
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
    } catch (Exception $e) {
        return false;
    }
}


/**
 * @param $uid
 * @param $cookieItems
 * @param $cartsDict  array: key=product_id-specId, value=Cart Object
 * @param $poductModel
 * @param $cartModel
 * @param $session_id
 * @return array cartItemsByPid
 */
function mergeCartWithDb($uid, $cookieItems, &$cartsDict, $poductModel, $cartModel, $session_id = null) {
    $product_ids = array();
    $nums = array();
    foreach ($cookieItems as $item) {
        list($id, $num, $newSpecId) = explode(':', $item);
        if ($id) {
            $product_ids[] = $id;
            $nums[$id] = $num;
            if (is_numeric($newSpecId)) {
                $specs[$id] = $newSpecId;
            }
        }
    }

    if (empty($product_ids)) {
        return array();
    }

    $products = $poductModel->find_published_products_by_ids($product_ids, array('specs'));
    foreach ($products as $p) {
        $pid = $p['id'];

        $newSpecId = empty($specs[$pid]) ? 0 : $specs[$pid];
        $cart_key = cart_dict_key($pid, $newSpecId);
        $cartItem =& $cartsDict[$cart_key];
        $pNum = $nums[$pid];
        if (empty($cartItem)) {
            list($price, $special_id) = calculate_price($p['id'], $p['price'], $uid, $pNum);
            $cartItem = array(
                'product_id' => $pid,
                'name' => product_name_with_spec($p['name'], $newSpecId, $p['specs']),
                'coverimg' => $p['Product']['coverimg'],
                'num' => $pNum,
                'price' => $price,
                'applied_special' => empty($special_id) ? 0 : $special_id,
                'specId' => $newSpecId,
                'session_id' => $session_id,
            );
            $cartsDict[$cart_key] =& $cartItem;
        } else {
            $cartItem['num'] = $pNum;
            $cartItem['price'] = $p['price'];
            $cartItemId = $cartItem['id'];
        }
        $cartItem['creator'] = $uid;

        if (isset($cartItemId) && $cartItemId) {
            $cartModel->id = $cartItemId;
        } else {
            $cartModel->create();
        }

        if ($cartModel->save(array('Cart' => $cartItem))) {
            $cartItem['id'] = $cartModel->id;
        }
    }
}

/**
 * @param $pid
 * @param $newSpecId
 * @return string
 */
function cart_dict_key($pid, $newSpecId) {
    return $pid . '-' . $newSpecId;
}

/**
 * @param $dbCartItems
 * @return array
 */
function dict_db_carts($dbCartItems) {
    $cartsDicts = array();
    if (!empty($dbCartItems)) {
        foreach ($dbCartItems as $ci) {
            $cartsDicts[cart_dict_key($ci['Cart']['product_id'], $ci['Cart']['specId'])] = $ci['Cart'];
        }
        return $cartsDicts;
    }
}

function find_latest_clicked_from($buyerId, $pid) {
    //CANNOT same with $newUserId
    if ($pid == PRODUCT_ID_RICE_10) {
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


class ProductCategory {

    public static function product_category_list() {
        $productCategoryListJson = Cache::read('_productcategorylist');
        if (empty($productCategoryListJson)) {
            $productCategoryModel = ClassRegistry::init('ProductTag');
            $productModel = ClassRegistry::init("Product");
            $productCategoryList = $productCategoryModel->find('all', array('conditions' => array(
                'show_in_home' => 1,
                'published' => 1
            ),
                'order' => 'priority desc'
            ));
            $conditions = array('Product' . '.deleted' => 0, 'Product' . '.published' => 1);
            $conditions['Product' . '.recommend >'] = 0;
            $orderBy = ' Product.recommend desc';
            foreach ($productCategoryList as &$tag) {
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
                        'limit' => ($tag['ProductTag']['size_in_home'] > 0 ? $tag['ProductTag']['size_in_home'] : 6),
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

class ProductSpeciality {

    //获取产品属性指标
    public static function get_product_attrs() {
        $allAttrs = Cache::read('all_product_attributes');
        if (!empty($allAttrs)) {
            $allAttrs = json_decode($allAttrs, true);
        }
        if (empty($allAttrs)) {
            $productAttribute = ClassRegistry::init('ProductAttribute');
            $allAttrs = $productAttribute->find('all', array(
                'conditions' => array(
                    'deleted' => 0
                )
            ));
            $allAttrs = Hash::extract($allAttrs, '{n}.ProductAttribute');
            $allAttrs = json_encode($allAttrs);
            Cache::write('all_product_attributes', $allAttrs);
        }

        return json_encode($allAttrs);
    }

}

class TuanShip {
    public static function get_all_tuan_ships() {
        $shipTypesJson = Cache::read('_tuanshiptypes');
        if (empty($shipTypesJson)) {
            $tuanShipTypeModel = ClassRegistry::init('TuanShipType');
            $tuanShipTypes = $tuanShipTypeModel->find('all', array(
                'conditions' => array(
                    'deleted' => DELETED_NO
                )
            ));
            $tuanShipTypes = Hash::combine($tuanShipTypes, '{n}.TuanShipType.id', '{n}.TuanShipType');
            $shipTypesJson = json_encode($tuanShipTypes);
            Cache::write('_tuanshiptypes', $shipTypesJson);
        }
        return json_decode($shipTypesJson, true);
    }

    public static function get_ship_name($id) {
        $ships = TuanShip::get_all_tuan_ships();
        return $ships[$id]['name'];
    }

    public static function  get_ship_code($id) {
        $ships = TuanShip::get_all_tuan_ships();
        return $ships[$id]['code'];
    }
}

class ShipAddress {
    /**
     * @return array keyed with ship type id, value is array of fields for the ship type
     */
    public static function ship_type_list() {
        $ship_types = ShipAddress::ship_types();
        if (is_array($ship_types)) {
            return Hash::combine($ship_types, '{n}.id', '{n}.name');
        } else {
            return false;
        }
    }

    public static function ship_type_name_id_map() {
        $ship_types = ShipAddress::ship_types();
        if (is_array($ship_types)) {
            return Hash::combine($ship_types, '{n}.name', '{n}.id');
        } else {
            return false;
        }
    }

    public function get_all_ship_info() {
        $ship_types = ShipAddress::ship_types();
        $ship_type_list = Hash::combine($ship_types, '{n}.company', '{n}.name', '{n}.id');
        return $ship_type_list;
    }

    /**
     * @param $orderInfo 订单信息
     * @return mixed
     */
    public static function get_ship_detail($orderInfo) {
        $ship_types = ShipAddress::ship_types();
        $ship_type_list = Hash::combine($ship_types, '{n}.company', '{n}.name', '{n}.id');
        $ship_type = $ship_type_list[$orderInfo['Order']['ship_type']];
        if (empty($ship_type)) {
            return null;
        }
        $com = key($ship_type);
        $nu = $orderInfo['Order']['ship_code'];
        $nu = str_replace('-', '', $nu);
        if ($nu == '无' || $nu == '' || $nu == '已发货') {
            return null;
        }
        $AppKey = Configure::read('kuaidi100_key');
        //http://api.kuaidi100.com/api?id=[]&com=[]&nu=[]&valicode=[]&show=[0|1|2|3]&muti=[0|1]&order=[desc|asc]
        //http://www.kuaidi100.com/applyurl?key=[]&com=[]&nu=[]
        $url = 'http://www.kuaidi100.com/applyurl?key=' . $AppKey . '&com=' . $com . '&nu=' . $nu;
        //优先使用curl模式发送数据
        if (function_exists('curl_init') == 1) {
//            $this->log("Curl can init...");
            $curl = curl_init();
            curl_setopt_array(
                $curl,
                array(
                    CURLOPT_URL => $url,
                    CURLOPT_HEADER => 0,
                    CURLOPT_RETURNTRANSFER => 1,
                    CURLOPT_TIMEOUT => 5
                )
            );
            $get_content = curl_exec($curl);
            curl_close($curl);
        } else {
//            $this->log("Curl can't init...");
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

/**
 * @param $uid
 * @param $weixinC
 * @param $coupon
 * @param string $descs
 * @return bool
 */
function add_coupon_for_618($uid, $weixinC, $coupon, $descs = "满50元减20， 满30元减10元") {
    $ci = ClassRegistry::init('CouponItem');
    //TODO limit get coupon times
    $ci->addCoupon($uid, $coupon, $uid, '618');
    $weixinC->send_coupon_received_message($uid, 1, "可购买满减商品", $descs);
    return true;
}

function add_coupon_for_new($uid, $weixinC, $coupons = array(18483, 18482), $descs = "满100元减20， 满50元减10元") {
    $ci = ClassRegistry::init('CouponItem');
    $new_user_coupons = $coupons;
    $found = $ci->find_coupon_item_by_type_no_join($uid, $new_user_coupons);
    if (empty($found)) {
        foreach ($new_user_coupons as $coupon_id) {
            $ci->addCoupon($uid, $coupon_id, $uid, 'new_register');
        }
        $weixinC->send_coupon_received_message($uid, count($coupons), "可购买全站商品", $descs);
        return true;
    }
    return false;
}

/**
 * @param $pid
 * @param $defUri
 * @return string
 */
function product_link($pid, $defUri) {
    $linkInCache = Cache::read('link_pro_' . $pid);
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
        Cache::write('link_pro_' . $pp['id'], $link);
        return $link;
    } else {
        return $defUri;
    }
}

function product_tuan_list_link($p, $defUri = '/') {
    if (!empty($p)) {
        $pp = empty($p['Product']) ? $p : $p['Product'];
        $link = "/tuans/lists/" . $pp['id'] . "/" . ".html";
        Cache::write('link_pro_tuan_' . $pp['id'], $link);
        return $link;
    } else {
        return $defUri;
    }
}

function url_append($url, $name, $value) {
    if (strpos($url, '?') !== false) {
        return $url . '&' . urlencode($name) . '=' . urlencode($value);
    } else {
        return $url . '?' . $name . '=' . urlencode($value);
    }
}

function url_colored($url, $value) {
    return url_append($url, '_sl', $value);
}

function wxDefaultName($name) {
    return notWeixinAuthUserInfo(0, $name);
}

function notWeixinAuthUserInfo($uid, $userName) {
    return strpos($userName, '微信用户') === 0;
}

function filter_weixin_username($name) {
    return notWeixinAuthUserInfo(0, $name) ? mb_substr($name, 4) : $name;
}

function date_days($timeStr, $addDays = 0) {
    $end = DateTime::createFromFormat(FORMAT_DATETIME, $timeStr);
    if ($addDays) {
        $end->add(new DateInterval('P' . $addDays . 'D'));
    }
    return $end;
}

function is_past($timeStr, $addDays = 0) {
    $end = DateTime::createFromFormat(FORMAT_DATETIME, $timeStr);
    if ($addDays) {
        $end->add(new DateInterval('P' . $addDays . 'D'));
    }
    return ($end->getTimestamp() < mktime());
}

function coupon_expired($coupon) {
    if (empty($coupon)) {
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
    return '/categories/special_list/' . $slug . '.html';
}

function brand_link($brand_id, $params = array()) {
    $brandM = ClassRegistry::init('Brand');
    $brand = $brandM->findById($brand_id);
    $url = (!empty($brand)) ? "/brands/" . date('Ymd', strtotime($brand['Brand']['created'])) . "/" . $brand['Brand']['slug'] . ".html" : '/';

    if (!empty($params) && is_array($params)) {
        $url .= '?';
        foreach ($params as $k => $v) {
            $url .= '&' . urlencode($k) . '=' . urlencode($v);
        }
    }

    return $url;
}

function category_link($category_id) {
    $productTagM = ClassRegistry::init('ProductTag');
    $tag = $productTagM->find('first', array(
        'conditions' => array($category_id),
        'fields' => array('slug')
    ));
    $url = (!empty($tag)) ? '/categories/tag/' . $tag['ProductTag']['slug'] . '.html' : '/';
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
        $r = preg_replace('/(.*files\/20\d+\/)(thumb_[ms]\/)?(\s*)/i', '${1}' . $type . '/${3}', $imgUrl);
        return ($r != null) ? $r : $imgUrl;
    }

    return $imgUrl;
}

function share_small_thumb_link($imgUrl){
    if (strpos($imgUrl, '/m/') !== false) {
        $imgUrl = str_replace('/m/', '/s/', $imgUrl);
        return $imgUrl;
    }
    if(strpos($imgUrl, '/avatar/') !== false){
        $imgUrl = str_replace('/avatar/', '/avatar/s/', $imgUrl);
    }
    if(strpos($imgUrl, '/images/') !== false){
        $imgUrl = str_replace('/images/', '/images/s/', $imgUrl);
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
        if ($total_limit != 0 && $soldCnt >= $total_limit) {
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

        if ($total_limit == 0) {
            $total_left = -1;
        } else {
            $total_left = $total_limit - $soldCnt;
            if ($total_left < 0) {
                $total_left = 0;
            }
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

    $range_key = $start . '_' . $end;

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

    if (!empty($data)) {
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


function message_send($msg = null, $mobilephone = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, MSG_API_KEY);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobilephone, 'message' => $msg . '【朋友说】'));
    $res = curl_exec($ch);
    //{"error":0,"msg":"ok"}
    curl_close($ch);
    return $res;
}

function consignment_send_date($p_id) {
    $consignmentDateM = ClassRegistry::init('ConsignmentDate');
    $send_dates = $consignmentDateM->find('all', array(
        'conditions' => array('published' => PUBLISH_YES, 'product_id' => $p_id),
        'order' => 'send_date',
        'field' => 'send_date',
        'limit' => 5,
    ));
    $rtn = array();
    foreach ($send_dates as $date) {
        $dt = DateTime::createFromFormat(FORMAT_DATE, $date['ConsignmentDate']['send_date']);
        $id = $date['ConsignmentDate']['id'];
        if (!empty($dt)) {
            $rtn[] = array('send_date' => $date['ConsignmentDate']['send_date'], 'date' => date(FORMAT_DATE_YUE_RI_HAN, $dt->getTimestamp()), 'id' => $id);
        }
    }
    return $rtn;
}

function remove_emoji($text) {
    if (empty($text)) {
        return "";
    }
    return preg_replace('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $text);
}


/**
 * @param $text
 * @return mixed|string
 */
function convertWxName($text) {
    $nickname = remove_emoji($text);
    return ($nickname == '' ? '用户_' . mt_rand(10, 1000) : $nickname);
}

function get_frid_from_request_uri(){
    $str = urldecode($_SERVER['REQUEST_URI']);
    preg_match("/frid=(\d*)/",$str , $res);
    return $res[1] ? $res[1] : 0;
}

/**
 * @param $userInfo
 * @param $userModel
 * @return int if created failed return 0
 */
function createNewUserByWeixin($userInfo, $userModel,$weshareBuyComponent = null) {
    $download_url = $userInfo['headimgurl'];
    $ali_avatar = '';

    $frid = get_frid_from_request_uri();
    if (!empty($userInfo['headimgurl'])) {
        $ali_avatar = create_avatar_in_aliyun($userInfo['headimgurl']);
        $download_url = $ali_avatar;
        if(empty($ali_avatar)){
            $download_url = download_photo_from_wx($userInfo['headimgurl']);
            if (empty($download_url)) {
                $download_url = $userInfo['headimgurl'];
            }
        }
    }
    if (empty($userModel)) {
        $userModel = ClassRegistry::init('User');
    }
    if (!$userModel->save(array(
        'nickname' => convertWxName($userInfo['nickname']),
        'sex' => $userInfo['sex'] == 1 ? 0 : ($userInfo['sex'] == 2 ? 1 : null),
        'image' => $download_url,
        'province' => $userInfo['province'],
        'city' => $userInfo['city'],
        'country' => $userInfo['country'],
        'language' => $userInfo['language'],
        'username' => $userInfo['openid'],
        'password' => '',
        'uc_id' => 0,
        'avatar' => $ali_avatar,
        'own_id' => $frid
    ))
    ) {
        return 0;
    }
    $insertId = $userModel->getLastInsertID();

    if($insertId && $frid)
    {
        $userRelationM = ClassRegistry::init('UserRelation');
        $userSubLog = ClassRegistry::init('UserSubLog');
        $has_relation = $userRelationM->hasAny(['user_id' => $frid, 'follow_id' => $insertId]);
        if (!$has_relation) {
            $userRelationM->saveAll(array('user_id' => $frid, 'follow_id' => $insertId, 'type' => 'Transfer', 'created' => date('Y-m-d H:i:s'),'is_own' => 1));
        } else {
            $userRelationM->updateAll(array('deleted' => DELETED_NO), array('user_id' => $frid, 'follow_id' => $insertId));
        }
        if($weshareBuyComponent){
            $weshareBuyComponent->subscribe_sharer($frid,$insertId);
        }
        $userSubLog->save(['user_id' => $frid, 'follow_id' => $insertId, 'type' => USER_SUB_LOG_TYPE, 'created' => date('Y-m-d H:i:s')]);
    }

    return $insertId;
}


/**
 * @param $range
 * @return bool
 */
function in_range($range) {
    return (empty($range['start']) || before_than($range['start']))
    && (empty($range['end']) || !before_than($range['end']));
}

/**
 * @param $special
 * @return array
 */
function range_by_special($special) {
    if ($special['special']['show_day'] != '0000-00-00') {
        $day_start = $special['special']['show_day'] . ' 00:00:00';
        $day_end = $special['special']['show_day'] . ' 23:59:59';
        $special_rg = array('start' => $day_start, 'end' => $day_end);
        return $special_rg;
    } else {
        $special_rg = array('start' => $special['start'], 'end' => $special['end']);
        return $special_rg;
    }
}

function accept_user_price_pid_num($pid, $num) {
    return false; // $pid == PRODUCT_ID_JD_HS_NZT && $num == 1;
}

function accept_user_price_pid($product_id) {
    return false; //$product_id == PRODUCT_ID_JD_HS_NZT;
}

function accept_user_price($product_id, $user_price) {
    return false; //($product_id == PRODUCT_ID_JD_HS_NZT) && !empty($user_price) && $user_price >= 1;
}

function cal_score_money($score, $total_price) {
    $score_money = $score / 100;
    if ($score_money > $total_price / 2) {
        return $total_price / 2;
    } else {
        return $score_money;
    }
}

function cal_rebate_money($rebate, $total_price){
    $rebate_money = $rebate / 100;
    if ($rebate_money > $total_price / 2) {
        return $total_price / 2;
    }
    return $rebate_money;
}

/**
 * @param $uid
 * @return int|mixed
 */
function user_subscribed_pys($uid) {
    $key = key_cache_sub($uid);
    $subscribe_status = Cache::read($key);
    if (empty($subscribe_status) || $subscribe_status == WX_STATUS_UNKNOWN) {
        $outhBindM = ClassRegistry::init('Oauthbind');
        $oauth = $outhBindM->findWxServiceBindByUid($uid);
        if (!empty($oauth)) {
            $wxOauthM = ClassRegistry::init('WxOauth');
            $uinfo = $wxOauthM->get_user_info_by_base_token($oauth['oauth_openid']);
            if (!empty($uinfo)) {
                $subscribe_status = ($uinfo['subscribe'] != 0 ? WX_STATUS_SUBSCRIBED : WX_STATUS_UNSUBSCRIBED);
                Cache::write($key, $subscribe_status);
            }
        } else {
            $subscribe_status = WX_STATUS_NO_WX;
            Cache::write($key, $subscribe_status);
        }
    }
    return $subscribe_status;
}

function send_weixin_message($post_data, $logObj = null) {

    add_template_msg_task($post_data);

//    $tries = 2;
//    $wxOauthM = ClassRegistry::init('WxOauth');
//
//    $wx_curl_option_defaults = array(
//        CURLOPT_HEADER => false,
//        CURLOPT_RETURNTRANSFER => true,
//        CURLOPT_TIMEOUT => 30
//    );
//    while ($tries-- > 0) {
//        $access_token = $wxOauthM->get_base_access_token();
//        if (!empty($access_token)) {
//            $curl = curl_init();
//            $options = array(
//                CURLOPT_URL => 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token,
//                CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS
//            );
//            if (!empty($post_data)) {
//                $options[CURLOPT_POSTFIELDS] = json_encode($post_data);
//            }
//
//            curl_setopt_array($curl, ($options + $wx_curl_option_defaults));
//            $json = curl_exec($curl);
//            curl_close($curl);
//            $output = json_decode($json, true);
//            if (!empty($logObj)) {
//                $logObj->log("post weixin api send template message output: " . json_encode($output), LOG_DEBUG);
//            }
//            if ($output['errcode'] == 0) {
//                return true;
//            } else {
//                if (!$wxOauthM->should_retry_for_failed_token($output)) {
//                    return false;
//                };
//            }
//            return false;
//        }
//    }
    return true;
}

function gethtml($from_url, $url) {
    $ch = curl_init();
    //设置 来路，这个很重要 ，表示这个访问 是从 $form_url 这个链接点过去的。
    curl_setopt($ch, CURLOPT_REFERER, $from_url);
    //获取 的url地址
    curl_setopt($ch, CURLOPT_URL, $url);
    //设置  返回原生的（Raw）输出
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //发送POST请求 CURLOPT_CUSTOMREQUEST
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    //模拟浏览器发送报文 ，这里模拟 IE6 浏览器访问
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)");
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function get_user_info_from_wx($open_id) {
    $wxOauthM = ClassRegistry::init('WxOauth');
    $access_token = $wxOauthM->get_base_access_token();
    $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $access_token . '&openid=' . $open_id;
    $content = gethtml(WX_HOST, $url);
    return json_decode($content, $content);
}

// Priority: small avatar, image
function get_user_avatar($user)
{
    if (empty($user)) {
        return "";
    }
    if ($user['User']) {
        $user = $user['User'];
    }
    if ($user['avatar']) {
        if(stripos($user['avatar'], ALIYUN_AVATAR_PREFIX) === 0)
        {
            return $user['avatar'];
        }
        return ALIYUN_AVATAR_PREFIX . '/' . str_replace('avatar/', 'avatar/s/', $user['avatar']);
    }
    return $user['image'];
}


function create_avatar_in_aliyun($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, ALI_CREATE_AVATAR_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'url='.$url);
    $response = curl_exec($ch);
    $ali_avatar = '';
    $result = json_decode($response, true);
    if($result['result']){
        $ali_avatar = $result['url'];
    }
    curl_close($ch);
    return $ali_avatar;
}

function download_photo_from_wx($url) {
    //use aliyun
    $ali_avatar = create_avatar_in_aliyun($url);
    if(!empty($ali_avatar)){
        return $ali_avatar;
    }
    App::uses('CurlDownloader', 'Lib');
    $dl = new CurlDownloader($url);
    $dl->isDownloadHeadImg(true);
    $dl->download();
    $download_url = '';
    if ($dl->getFileName() != 'remote.out') {
        if (defined('SAE_MYSQL_DB')) {
            $stor = new SaeStorage();
            $download_url = $stor->upload(SAE_STORAGE_UPLOAD_AVATAR_DOMAIN_NAME, $dl->getUploadFileName(), $dl->getFileName());
            if (!$download_url) {
                //retry
                $download_url = $stor->upload(SAE_STORAGE_UPLOAD_AVATAR_DOMAIN_NAME, $dl->getUploadFileName(), $dl->getFileName());
            }
        } else {
            copy($dl->getFileName(), WWW_ROOT . 'files/wx-download/' . $dl->getUploadFileName());
            $download_url = '/files/wx-download/' . $dl->getUploadFileName();
        }
        //delete temp file
        unlink($dl->getFileName());
    }
    return $download_url;
}


/**
 * param example array(array('pid'=>6,'specId'=>6,'defaultPrice'=>190).......)
 * specId default 0
 * result example array('pid-specId'=> array(price, 'spec_names'))
 *
 * @param $pidSidMap
 * @return array
 */
function get_spec_by_pid_and_sid($pidSidMap) {
    $productSpecGroup = ClassRegistry::init('ProductSpecGroup');
    //有特惠价格或者商品没有specId 使用默认价格
    $result = array();
    foreach ($pidSidMap as $item) {
        $sid = $item['specId'];
        $pid = $item['pid'];
        $price = $item['defaultPrice'];
        $spec_names = '';
        if ($sid != 0) {
            $specGroup = $productSpecGroup->find('first', array(
                'conditions' => array(
                    'id' => $sid
                )
            ));
            if (!empty($specGroup)) {
                //no published use default price
                $price = $specGroup['ProductSpecGroup']['price'];
                $spec_names = $specGroup['ProductSpecGroup']['spec_names'];
            }
        }
        $result[cart_dict_key($pid, $sid)] = array($price, $spec_names);
    }
    return $result;
}

function get_spec_name_by_pid_and_sid($pid, $sid) {
    $spec_name = Cache::read('product-' . $pid . '-' . $sid . '-spec');
    if (!empty($spec_name)) {
        return $spec_name;
    }
    $result = get_spec_by_pid_and_sid(array(
        array('pid' => $pid, 'specId' => $sid, 'defaultPrice' => 0),
    ));
    $spec_detail_arr = $result[cart_dict_key($pid, $sid)];
    $spec_name = empty($spec_detail_arr[1]) ? '' : $spec_detail_arr[1];
    Cache::write('product-' . $pid . '-' . $sid . '-spec', $spec_name);
    return $spec_name;
}

function get_spec_name_try($tryId) {
    $spec_name = Cache::read('try-' . $tryId . '-spec');
    if (!empty($spec_name)) {
        return $spec_name;
    }
    $ProductTryM = ClassRegistry::init('ProductTry');
    $try = $ProductTryM->find('first', array('conditions' => array('id' => $tryId)));
    $spec_name = $try['ProductTry']['spec'];
    Cache::write('try-' . $tryId . '-spec', $spec_name);
    return $spec_name;
}

function get_tuan_msg_element($tuan_buy_id) {
    $tuanBuyingM = ClassRegistry::init('TuanBuying');
    $tuanTeamM = ClassRegistry::init('TuanTeam');
    $productM = ClassRegistry::init('Product');
    $tuanMemberM = ClassRegistry::init('TuanMember');
    $tb = $tuanBuyingM->find('first', array(
        'conditions' => array(
            'id' => $tuan_buy_id,
        )
    ));
    if (!empty($tb)) {
        $tuan_id = $tb['TuanBuying']['tuan_id'];
        $product_id = $tb['TuanBuying']['pid'];
        $tt = $tuanTeamM->find('first', array(
            'conditions' => array(
                'id' => $tuan_id
            )
        ));
        $p = $productM->find('first', array(
            'conditions' => array(
                'id' => $product_id
            )
        ));
        $tuan_members = $tuanMemberM->find('all', array(
            'conditions' => array(
                'tuan_id' => $tuan_id
            )
        ));
        $consign_time = $tb['TuanBuying']['consign_time'];
        $consign_time = friendlyDateFromStr($consign_time, FFDATE_CH_MD);
        $uids = Hash::extract($tuan_members, '{n}.TuanMember.uid');
        $tuan_name = $tt['TuanTeam']['tuan_name'];
        $product_name = $p['Product']['name'];
        $tuan_leader = $tt['TuanTeam']['leader_name'];
        $target_num = $tb['TuanBuying']['target_num'];
        $sold_num = $tb['TuanBuying']['sold_num'];
        $tb_status = intval($tb['TuanBuying']['status']);
        return array(
            'consign_time' => $consign_time,
            'target_num' => $target_num,
            'sold_num' => $sold_num,
            'uids' => $uids,
            'tuan_name' => $tuan_name,
            'product_name' => $product_name,
            'tuan_leader' => $tuan_leader,
            'tuan_buy_status' => $tb_status
        );
    } else {
        return null;
    }
}

/**
 * @param $user_id
 * @param $title
 * @param $product_name
 * @param $tuan_leader_wx
 * @param $remark
 * @param $deatil_url
 * @param $open_id
 * @return bool
 * 加入一个团购
 */
function send_join_tuan_buy_msg($user_id, $title, $product_name, $tuan_leader_wx, $remark, $deatil_url, $open_id = null) {
    if (empty($open_id)) {
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
        if ($user_weixin == false) {
            return false;
        }
        $open_id = $user_weixin['oauth_openid'];
    }
    if (empty($open_id)) {
        return false;
    }
    $post_data = array(
        "touser" => $open_id,
        "template_id" => 'P4iCqkiG7_s0SVwCSKyEuJ0NnLDgVNVCm2VQgSGdl-U',
        "url" => $deatil_url,
        "topcolor" => "#FF0000",
        "data" => array(
            "Pingou_Action" => array("value" => $title),
            "Pingou_ProductName" => array("value" => $product_name),
            "Weixin_ID" => array("value" => $tuan_leader_wx),
            "Remark" => array("value" => $remark, "color" => "#FF8800")
        )
    );
    return send_weixin_message($post_data);
}

/**
 * @param $user_id
 * @param $title
 * @param $product_name
 * @param $tuan_leader_wx
 * @param $remark
 * @param $deatil_url
 * @return bool
 * 团购提示信息
 */
function send_tuan_tip_msg($user_id, $title, $product_name, $tuan_leader_wx, $remark, $deatil_url) {
    $oauthBindModel = ClassRegistry::init('Oauthbind');
    $user_weixin = $oauthBindModel->findWxServiceBindByUid($user_id);
    if ($user_weixin != false) {
        $open_id = $user_weixin['oauth_openid'];
        $post_data = array(
            "touser" => $open_id,
            "template_id" => 'BYtgM4U84etw2qbOyyZzR4FO8a-ddvjy8sgBiAQy64U',
            "url" => $deatil_url,
            "topcolor" => "#FF0000",
            "data" => array(
                "first" => array("value" => $title),
                "Pingou_ProductName" => array("value" => $product_name),
                "Weixin_ID" => array("value" => $tuan_leader_wx),
                "Remark" => array("value" => $remark, "color" => "#FF8800")
            )
        );
        return send_weixin_message($post_data);
    }
    return false;
}

//weixin 分享签名
function prepare_wx_share() {
    $oauthM = ClassRegistry::init('WxOauth');
    $signPackage = $oauthM->getSignPackage();
    return $signPackage;
}

//weixin 分享签名，记录用户分享信息
function prepare_wx_share_log($currUid, $date_type, $data_id) {
    $currUid = empty($currUid) ? 0 : $currUid;
    $share_string = $currUid . '-' . time() . '-rebate-' . $date_type . '_' . $data_id;
    $share_code = authcode($share_string, 'ENCODE', 'SHARE_TID');
    $signPackage = prepare_wx_share();
    return array('signPackage' => $signPackage, 'share_string' => urlencode($share_code));
}

function prepare_wx_share_string($currUid, $date_type, $data_id){
    $currUid = empty($currUid) ? 0 : $currUid;
    $share_string = $currUid . '-' . time() . '-rebate-' . $date_type . '_' . $data_id;
    $share_code = authcode($share_string, 'ENCODE', 'SHARE_TID');
    return urldecode($share_code);
}

function getTuanProductsAsJson() {
    $tuanProductM = ClassRegistry::init('TuanProduct');
    $tuanProducts = $tuanProductM->find('all', array(
        'conditions' => array(
            'deleted' => DELETED_NO
        ),
        'order' => 'priority desc'
    ));
    $tuanProducts = json_encode($tuanProducts);
    return $tuanProducts;
}

function getTuanProducts() {
    return json_decode(getTuanProductsAsJson(), true);
}

function getTuanProductPrice($pid) {
    $product_price_map = Hash::combine(getTuanProducts(), '{n}.TuanProduct.product_id', '{n}.TuanProduct.tuan_price');
    return floatval($product_price_map[$pid]);
}


function search_spec($spec_ids) {
    if (count($spec_ids) != 1 || !empty($spec_ids[0])) {
        $specM = ClassRegistry::init('ProductSpecGroup');
        $spec_groups = $specM->find('all', array(
            'conditions' => array(
                'id' => $spec_ids
            )
        ));
        return Hash::combine($spec_groups, '{n}.ProductSpecGroup.id', '{n}.ProductSpecGroup.spec_names');
    }
    return null;
}

function search_consignment_date($consign_ids) {
    if (count($consign_ids) != 1 || !empty($consign_ids[0])) {
        $consignM = ClassRegistry::init('ConsignmentDate');
        $consign_dates = $consignM->find('all', array(
            'conditions' => array(
                'id' => $consign_ids
            )
        ));
        return Hash::combine($consign_dates, '{n}.ConsignmentDate.id', '{n}.ConsignmentDate.send_date');
    }
    return null;
}

function get_address($tuan_team, $offline_store) {
    if (empty($offline_store)) {
        $tuan_address = $tuan_team['TuanTeam']['tuan_addr'];
    } else {
        $tuan_address = $offline_store['OfflineStore']['name'];
        if (empty($offline_store['OfflineStore']['owner_name'])) {
            if (!empty($offline_store['OfflineStore']['owner_phone'])) {
                $tuan_address .= "(联系电话: " . $offline_store['OfflineStore']['owner_phone'] . ")";
            }
        } else {
            $tuan_address .= "(联系人: " . $offline_store['OfflineStore']['owner_name'];
            if (!empty($offline_store['OfflineStore']['owner_phone'])) {
                $tuan_address .= " " . $offline_store['OfflineStore']['owner_phone'];
            }
            $tuan_address .= ")";
        }
    }

    return $tuan_address;
}

function sort_award($a, $b) {
    if ($a['order'] == $b['order']) return 0;
    return ($a['order'] > $b['order']) ? 1 : -1;
}

function day_of_week($date_string) {
    $day = date_format(date_create($date_string), 'N');
    $ret = '';
    switch ($day) {
        case 1:
            $ret = '一';
            break;
        case 2:
            $ret = '二';
            break;
        case 3:
            $ret = '三';
            break;
        case 4:
            $ret = '四';
            break;
        case 5:
            $ret = '五';
            break;
        case 6:
            $ret = '六';
            break;
        case 7:
            $ret = '日';
            break;
    }
    return '周' . $ret;
}

function is_pys_product($brandId) {
    return $brandId == PYS_BRAND_ID;
}

/**
 * @param $before_day
 * @param $week_days
 * @param $time
 * @return bool|string
 */
function get_send_date($deadline_day, $deadline_time, $week_days) {
    $week_days = explode(',', $week_days);
    $interval_one_day = new DateInterval('P1D');

    $send_date = new DateTime();
    if (_is_after_deadline_time($send_date, $deadline_time)) {
        $deadline_day = $deadline_day + 1;
    }
    $send_date->add(new DateInterval('P' . $deadline_day . 'D'));

    while (!in_array($send_date->format('N'), $week_days)) {
        $send_date->add($interval_one_day);
    }

    return $send_date;
}

function _is_after_deadline_time($now, $deadline_time) {
    $deadline_time = explode(':', $deadline_time);
    $limit_time = new DateTime('now');
    $limit_time->setTime($deadline_time[0], $deadline_time[1], $deadline_time[2]);

    return $now > $limit_time;
}


function get_pure_product_consignment_date($pid) {
    $ProductConsignmentDate = ClassRegistry::init('ProductConsignmentDate');
    $product_consignment_date = $ProductConsignmentDate->find('first', array('conditions' => array(
        'published' => PUBLISH_YES,
        'product_id' => $pid
    )));
    if (empty($product_consignment_date)) {
        return null;
    }
    $week_days = $product_consignment_date['ProductConsignmentDate']['week_days'];
    $deadline_day = $product_consignment_date['ProductConsignmentDate']['deadline_day'];
    $deadline_time = $product_consignment_date['ProductConsignmentDate']['deadline_time'];
    $consignment_date = get_send_date($deadline_day, $deadline_time, $week_days);
    if ($consignment_date == null) {
        return null;
    }
    return date_format($consignment_date, 'Y-m-d');
}

/**
 * @param $text
 * @param $len
 * @return string
 * 截取字符串多余 使用 ... 代替
 */
function get_limit_string($text, $len) {
    if (mb_strlen($text) > $len) {
        return mb_substr($text, 0, $len) . '...';
    }
    return $text;
}

function get_ship_mark_name($shipType) {
    if ($shipType == 'ziti') {
        return '自提';
    }
    if ($shipType == 'sfby') {
        return '顺丰包邮';
    }
    if ($shipType == 'sfdf') {
        return '顺丰到付';
    }
    if ($shipType == 'kuaidi') {
        return '快递';
    }
    if ($shipType == 'manbaoyou') {
        return '快递';
    }
    return null;
}

/**
 * @param $orderId
 * split pys order by cart
 */
function split_pys_order($orderId) {
    $orderM = ClassRegistry::init('Order');
    $cartM = ClassRegistry::init('Cart');
    $order = $orderM->find('first', array('conditions' => array('id' => $orderId)));
    if ($order['Order']['brand_id'] != PYS_BRAND_ID) {
        return;
    }
    if ($order['Order']['status'] != ORDER_STATUS_PAID) {
        return;
    }
    $carts = $cartM->find('all', array('conditions' => array('order_id' => $orderId)));
    $cartCount = count($carts);
    if ($cartCount <= 1) {
        return;
    }
    $score = $order['Order']['applied_score'];
    $all_order_total = $order['Order']['total_price'];
    $all_ship_fee = $order['Order']['ship_fee'];
    $avg_ship_fee = 0;
    if ($all_ship_fee > 0) {
        $avg_ship_fee = $all_ship_fee / $cartCount;
    }
    foreach ($carts as $item) {
        $tempOrder = clone $order;
        $total_price = $item['Cart']['num'] * $item['Cart']['price'];
        $tempOrder['Order']['id'] = null;
        $tempOrder['Order']['ship_fee'] = $avg_ship_fee;
        $tempOrder['Order']['total_price'] = $total_price;
        $tempOrder['Order']['total_all_price'] = $total_price + $avg_ship_fee;
        $spent_on_order = round($score * ($total_price / $all_order_total));
        $tempOrder['Order']['applied_score'] = $spent_on_order;
        $tempOrder['Order']['parent_order_id'] = $orderId;
        if ($orderM->save($tempOrder['Order'])) {
            $order_id = $orderM->getLastInsertID();
            $cartM->updateAll(array('order_id' => $order_id), array('id' => $item['Cart']['id']));
        }
    }
    $order['Order']['status'] = ORDER_STATUS_SPLIT;
    $orderM->save($order['Order']);
}

function get_special_pid_list($specialId) {
    $pids_str = Cache::read('special_' . $specialId . '_pids');
    if (!empty($pids_str)) {
        return json_decode($pids_str, true);
    }
    $ProductSpecial = ClassRegistry::init('ProductSpecial');
    $special_product = $ProductSpecial->find('all', array('conditions' => array('special_id' => $specialId, 'published' => PUBLISH_YES), 'fields' => array('product_id')));
    $pids = Hash::extract($special_product, '{n}.ProductSpecial.product_id');
    Cache::write('special_' . $specialId . '_pids', json_encode($pids));
    return $pids;
}

function pid_in_special($pid, $specialId) {
    $pids = get_special_pid_list($specialId);
    return in_array($pid, $pids);
}

function group_buy_is_available($group_buy_label) {
    $not_available_group_label = array('1069-1');
    return !in_array($group_buy_label, $not_available_group_label);
}

function get_pintuan_status_text($order_status, $tag_status) {
    if ($tag_status == PIN_TUAN_TAG_EXPIRE_STATUS) {
        return '报名失败，待退款';
    }
    if ($tag_status == PIN_TUAN_TAG_PROGRESS_STATUS) {
        return '报名进行中，待发货';
    }
    if($tag_status == PIN_TUAN_TAG_SUCCESS_STATUS){
        if($order_status == ORDER_STATUS_PAID){
            return '报名成功，待发货';
        }
    }
    return get_order_status_text_form_order_status($order_status);
}

function get_share_order_cart_display_name($carts) {
    $product_names = array();
    foreach ($carts as $item) {
        $product_names[] = $item['name'] . 'X' . $item['num'];
    }
    return implode(', ', $product_names);
}

function get_ship_text_from_order_ship_mark($order_ship_mark) {
    if ($order_ship_mark == SHARE_SHIP_PYS_ZITI_TAG) {
        return '好邻居自提';
    }
    if ($order_ship_mark == SHARE_SHIP_KUAIDI_TAG) {
        return '快递';
    }
    if ($order_ship_mark == SHARE_SHIP_GROUP_TAG) {
        return '拼团';
    }
    if ($order_ship_mark == SHARE_SHIP_SELF_ZITI_TAG) {
        return '自提';
    }
}

function get_order_status_text_form_order_status($order_status) {
    $order_status_text_map = array(
        ORDER_STATUS_WAITING_PAY => '待支付',
        ORDER_STATUS_PAID => '已支付',
        ORDER_STATUS_SHIPPED => '已发货',
        ORDER_STATUS_RECEIVED => '已收货',
        ORDER_STATUS_COMMENT => '待评价',
        ORDER_STATUS_RETURNING_MONEY => '退款中',
        ORDER_STATUS_RETURN_MONEY => '已退款',
        ORDER_STATUS_DONE => '已完成'
    );
    return $order_status_text_map[$order_status];
}

function get_share_order_tag_id($carts) {
    if (empty($carts)) {
        return 0;
    }
    $first_cart = $carts[0];
    return $first_cart['tag_id'];
}

function share_kuaidi_order_filter($var) {
    return empty($var['ship_mark']) || $var['ship_mark'] == SHARE_SHIP_KUAIDI_TAG;
}

function share_self_ziti_order_filter($var) {
    return $var['ship_mark'] == SHARE_SHIP_SELF_ZITI_TAG || $var['ship_mark'] == SHARE_SHIP_GROUP_TAG;
}

function share_pys_ziti_order_filter($var) {
    return $var['ship_mark'] == SHARE_SHIP_PYS_ZITI_TAG;
}

function order_comment_filter($var) {
    return $var['Comment']['parent_id'] == 0;
}

function order_reply_comment_filter($var) {
    return $var['Comment']['parent_id'] != 0;
}

function multi_array_sum($total, $next) {
    $total += $next['Order']['total_all_price'];
    return $total;
}

function map_share_type_text($type) {
    $type_text_map = array(0 => '正常分享', 1 => '自提点拼团', 3 => '产品池', 4 => '渠道价购买', 5 => '多人拼团分享', 6 => '指定开团');
    return $type_text_map[$type];
}

function map_user_avatar3($var){
    $var['avatar'] = get_user_avatar($var);
    return $var;
}

function map_user_avatar2($var){
    $var['User']['image'] = get_user_avatar($var);
    return $var;
}

function map_user_avatar($var){
    $avatar = get_user_avatar($var);
    if (empty($avatar)) {
        $avatar = STATIC_HOST . '/avatar/s/default.jpg';
    }
    $var['image'] = $avatar;
    return $var;
}

function get_index_tags()
{
    return [
        [
            'id' => 0,
            'name' => '新品爆款'
        ], [
            'id' => 1,
            'name' => '水果蔬菜'
        ], [
            'id' => 2,
            'name' => '肉蛋粮油'
        ], [
            'id' => 3,
            'name' => '零食其他'
        ], [
            'id' => 4,
            'name' => '粮油副食'
        ], [
            'id' => 5,
            'name' => '其他'
        ], [
            'id' => 6,
            'name' => '试吃',
            'banner' => 'tag-try-banner.jpg',
            'try' => true
        ], [
            'id' => 7,
            'name' => '尝鲜',
            'banner' => 'tag-taste-banner.jpg'
        ], [
            'id' => 8,
            'name' => '精选',
            'banner' => 'tag-chosen-banner.jpg'
        ]
    ];
}

function map_readable_date($time) {
    $now = time();
    $day = 24 * 60 * 60;
    $hour = 60 * 60;
    $minute = 60;
    $diff = $now - $time;

    if ($diff > $day) {
        $str = number_format($diff / $day, 0, '.', '') . "天前";
    } elseif ($diff > $hour) {
        $str = number_format($diff / $hour, 0, '.', '') . "小时前";
    } else {
        $min = number_format($diff / $minute, 0, '.', '');
        $str = $min ? $min . "分钟前" : "刚刚";
    }

    return $str;
}

function map_user_level($user_level) {
    $level_pool = [
        0 => '分享达人',
        1 => '实习团长',
        2 => '正式团长',
        3 => '优秀团长',
        4 => '高级团长',
        5 => '资深团长',
        6 => '首席团长'
    ];

    return $level_pool[$user_level];
}


function map_opt_log_data_type($type) {
    $str = "";

    switch ($type) {
        case OPT_LOG_CREATE_SHARE:
            $str = '分享了';
            break;
        case OPT_LOG_SHARE_BUY:
            $str = '报名了';
            break;
        case OPT_LOG_SHARE_COMMENT:
            $str = '评价了';
            break;
        case OPT_LOG_SHARE_RECOMMEND:
            $str = '推荐了';
            break;
        case OPT_LOG_START_GROUP_SHARE:
            $str = '发起拼团';
            break;
        case OPT_LOG_PINTUAN_SUCCESS:
            $str = '拼团成功';
            break;
    }

    return $str;
}

function map_opt_log_data($var) {
    $timeStamp = strtotime($var['created']);
    $var['timestamp'] = $timeStamp;
    $data_type = $var['obj_type'];
    $detail_url = '/weshares/view/' . $var['obj_id'];;
    $var['data_type_tag'] = map_opt_log_data_type($data_type);
    if ($data_type == OPT_LOG_PINTUAN_SUCCESS) {
        $detail_url = get_pintuan_opt_log_url($var['obj_id']);
    }
    $var['data_url'] = $detail_url;
    return $var;
}

function get_pintuan_opt_log_url($tag_id) {
    $PintuanTagM = ClassRegistry::init('PintuanTag');
    $tag = $PintuanTagM->find('first', array(
        'conditions' => array(
            'id' => $tag_id
        )
    ));
    $detail_url = '/pintuan/detail/' . $tag['PintuanTag']['share_id'] . '/' . $tag['PintuanTag']['pid'] . '?tag_id=' . $tag_id;
    return $detail_url;
}

/**
 * @param $a
 * @param $b
 * @return int
 * usort 函数
 */
function sort_data_by_id($a, $b) {
    return ($a['id'] < $b['id']) ? 1 : -1;
}

/**
 * @param $a
 * @param $b
 * @return int
 * usort 函数
 */
function sort_data_by_id_desc($a, $b) {
    return ($a['id'] > $b['id']) ? 1 : -1;
}

/**
 * @param $a
 * @param $b
 * @return int
 * usort 函数
 */
function sort_opt_log_data_by_id($a, $b) {
    return ($a['OptLog']['id'] < $b['OptLog']['id']) ? 1 : -1;
}

function replace_urls($string) {
    $host = "([a-z\d][-a-z\d]*[a-z\d]\.)+[a-z][-a-z\d]*[a-z]";
    $port = "(:\d{1,})?";
    $path = "(\/[^?<>\#\"\s]+)?";
    $query = "(\?[^<>\#\"\s]+)?";
    return preg_replace("#((ht|f)tps?:\/\/{$host}{$port}{$path}{$query})#i", "", $string);
}

/**
 * @param $uid
 * @return bool
 * 黑名单 乱发消息
 */
function is_blacklist_user($uid) {
    $blacklist = array(881026, 886291, 930585);
    return in_array($uid, $blacklist);
}

function is_pys_signed_user($uid){
    $pys_signed_user = [
        633345, 802852, 810684, 806889, 878825, 811917, 859965, 141
    ];
    return in_array($uid, $pys_signed_user);
}

/**
 * @param $uid
 * @return bool
 * 超级管理员
 */
function is_super_share_manager($uid) {
    $super_manager = [
        902185, 633345, 701166, 544307,
        141, 802852, 801447, 895096,
        711503, 897195, 897247, 5081,
        559795, 902131, 12282, 926835,
        908448
    ];
    return in_array($uid, $super_manager);
}

function is_product_manager($uid)
{
    $m = [801447, 559795, 633345];
    return in_array($uid, $m);
}

/**
 * @param $uid
 * @return bool
 * 测试用户
 */
function is_test_user($uid) {
    $test_users = [902185, 633345,802852,897195, 897247, 5081,559795, 902131];

    return in_array($uid, $test_users);
}

/**
 * @param $item
 * @return string
 * 组合分享的图片
 */
function map_share_img($item){
    if (!startsWith($item, 'http')) {
        return 'http://' . WX_HOST . $item;
    }
    return $item;
}

/**
 * @param $item
 * @return mixed
 * map分享的图片
 */
function map_share_images($item){
    $item_images = explode('|', $item['Weshare']['images']);
    $item_images = array_map('map_share_img',$item_images);
    $item['Weshare']['images'] = $item_images;
    return $item;
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}

//根据省市区获取地址
function get_address_location($locationData)
{
    $provinceId = $locationData['province_id'];
    $cityId = $locationData['city_id'];
    $countyId = $locationData['county_id'];
    $locationM = ClassRegistry::init('Location');
    $locationIds = array_filter(array($provinceId, $cityId, $countyId));
    $locations = $locationM->find('all', array(
        'conditions' => array(
            'id' => $locationIds
        ),
        'fields' => array('id', 'name')
    ));
    $locations = Hash::combine($locations, '{n}.Location.id', '{n}.Location.name');
    $location_address = '';
    foreach ($locationIds as $locationId) {
        $location_address = $location_address . $locations[$locationId];
    }
    return $location_address;
}

/**
 * 遍历对象转换为数组
 * @param object $obj
 * @return array
 */
function obj2arr($obj){
    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
    if(is_array($arr)){
        return array_map('obj2arr', $arr);
    }else{
        return $arr;
    }
}

/**
 * @param $ql
 * @return str
 * 转换url字符串
 */
function url_enc($ql){
    $str = urlencode($ql);
    return str_replace('%2A', '*', $str);
}



function check_weshare_detail_is_not_html($detail)
{
    return strrpos($detail, '<bdo id="mark-view-html"></bdo>') === false;
}

function get_format_number($val)
{
    return number_format(floatval($val), 2, '.', '');
}

function get_order_from_flag($from)
{
    if ($from == 'timeline') {
        return 1;
    }
    if ($from == 'groupmessage') {
        return 2;
    }
    if ($from == 'template_msg') {
        return 3;
    }
    if ($from == '_template_msg') {
        return 3;
    }
    if ($from == 'index') {
        return 4;
    }
    if ($from == 'app') {
        return 5;
    }
    if ($from == 'singlemessage') {
        return 6;
    }
    if ($from == 'opt') {
        return 7;
    }
    if ($from == 'userInfo') {
        return 8;
    }
    if ($from == 'recommend') {
        return 9;
    }
    if ($from == 'promotion') {
        return 10;
    }
    if ($from == 'wx_article') {
        return 11;
    }
    return 0;
}

function delete_redis_data_by_key($match){
    $redis = createRedisCli();
    $keys = $redis->keys('*'.$match.'*');
    $redis->delete($keys);
}

function createRedisCli() {
    $r = new Redis();
    $r->connect(REDIS_HOST);
    return $r;
}

//add logs
if(!function_exists("add_logs_to_es"))
{
    function add_logs_to_es($log)
    {
        foreach ($log as $index => $item) {
            if (is_numeric($item)) {
                $log[$index] = floatval($item);
            }
            if ($index == 'index') {
                $log['index'] = preg_replace("/\s|　/", "", strtolower($item));
            }
        }
        try {
            $redis = new Redis();
            $redis->connect(REDIS_HOST, ELK_REDIS_PORT);
            $log["session_id"] = $_COOKIE["PHPSESSID"];
            $redis->rPush("logstash-list", json_encode($log));
        } catch (Exception $e) {
            $logObj = ClassRegistry::init('User');
            $logObj->log('add es log error ' . $e->getMessage());
        }
    }
}

function add_template_msg_task($data){
    try {
        $r = createRedisCli();
        $r->rPush("wx_template_first_msg", json_encode($data));
    } catch (Exception $e) {

    }
}


/**
 * auto load spl lib
 */
// PHP5.3 namespace loader for Cake2.x
spl_autoload_register(function ($class) {
    foreach (App::path('Vendor') as $base) {
        $path = $base . str_replace('\\', DS, $class) . '.php';
        if (file_exists($path)) {
            include $path;
            return;
        }
    }
});

//add event listener
//require_once APP . 'Config' . DS . 'events.php';
