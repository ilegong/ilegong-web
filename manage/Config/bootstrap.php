<?php
if(!defined('APP_PATH')){
	define('APP_PATH', ROOT . DS . 'manage' . DS);
}
if(!defined('APP_SUB_DIR')){
	define('APP_SUB_DIR', '');
}
if(php_sapi_name()==='cli' && empty($_GET)){
	$_SERVER['SERVER_ADDR']='127.0.0.1';
}

const WX_HOST = 'www.tongshijia.com';
const WX_JS_API_CALL_URL = 'http://www.tongshijia.com/wxPay/jsApiPay';
const WX_NOTIFY_URL = 'http://www.tongshijia.com/wxPay/notify.html';
const WX_SERVICE_ID_GOTO = 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=200757804&idx=1&sn=90b121983525298a4ac26ee8d6c0bc1c#rd';

const ALI_HOST = 'www.tongshijia.com';

const WX_API_PREFIX = 'https://api.weixin.qq.com';

const WX_OAUTH_USERINFO = 'snsapi_userinfo';
const WX_OAUTH_BASE = 'snsapi_base';

const ADD_SCORE_TUAN_LEADER=99;

const CART_ITEM_STATUS_NEW = -1;
const CART_ITEM_STATUS_BALANCED = 0;
const CART_ITEM_STATUS_PAID = 1;
const CART_ITEM_STATUS_SHIPPED = 3;

const ORDER_STATUS_WAITING_PAY=0; //待支付
const ORDER_STATUS_PAID=1; // 已支付
const ORDER_STATUS_SHIPPED=2; // 已发货
const ORDER_STATUS_RECEIVED=3; //已确认收货
const ORDER_STATUS_RETURN_MONEY=4; //已退款
const ORDER_STATUS_DONE=9; //已完成
const ORDER_STATUS_CANCEL=10; //已取消
const ORDER_STATUS_CONFIRMED=11; //已确认有效，不要再用
const ORDER_STATUS_TOUSU=12; //已投诉， 不要再用，投诉走其他流程
const ORDER_STATUS_COMMENT=16; //待评价
const ORDER_STATUS_RETURNING_MONEY=14;//退款中

define('ORDER_STATUS_PREPAID', 25); //预付款 待确认价格
define('ORDER_STATUS_PREPAID_TODO', 26);//预付款订单待处理
define('ORDER_STATUS_PREPAID_DONE', 27);//预付款处理成功
define('ORDER_STATUS_REFUND_TODO', 28);//付款 退款 处理
define('ORDER_STATUS_REFUND_DONE', 29);//预付款退款
define('ORDER_STATUS_REFUND', 30);//预付款比较大于实际金额 需要退款 生成退款订单 只作为标记 不进行实际操作


const REFUND_ADD_ORDER_TYPE = 1;

//分享缓存的key
const SHARE_USER_SUMMERY_CACHE_KEY = 'share_user_summery_cache_key';
const SHARER_FANS_DATA_CACHE_KEY = 'sharer_fans_data_cache_key';
const SHARER_FOCUS_DATA_CACHE_KEY = 'sharer_focus_data_cache_key';
const USER_SHARE_COMMENTS_DATA_CACHE_KEY = 'user_share_comments_data_cache_key';
const SHARER_ALL_COMMENT_DATA_CACHE_KEY = 'sharer_all_comment_data_cache_key';
const SHARE_COMMENT_DATA_CACHE_KEY = 'share_comment_data_cache_key';
const SHARE_ORDER_DATA_CACHE_KEY = 'share_order_data_cache_key';
const SHARE_DETAIL_DATA_CACHE_KEY = 'share_detail_data_cache_key';
const SHARER_CAN_USE_OFFLINE_STORE_CACHE_KEY = 'share_can_user_offline_store_cache_key';
const SHARE_SHIP_SETTINGS_CACHE_KEY = 'share_ship_setting_cache_key';
const USER_SHARE_INFO_CACHE_KEY = 'user_share_info_cache_key';
const SIMPLE_SHARE_INFO_CACHE_KEY = 'simple_share_info_cache_key';

const USER_SHARE_ORDER_INFO_CACHE_KEY = 'user_share_order_info_cache_key';//缓存对应用户对应分享的订单信息

const ORDER_STATUS_VIRTUAL = 21;

const ORDER_TUANGOU=5;

CONST DELETED_YES = 1;
CONST DELETED_NO = 0;


const OFFLINE_STORE_HAOLINJU=0;
const OFFLINE_STORE_PYS=1;

CONST PUBLISH_YES = 1;
CONST PUBLISH_NO = 0;


const PRODUCT_TUAN_TYPE = 0;
const PRODUCT_TRY_TYPE = 1;

const PYS_M_TUAN=34;

const SHIP_TYPE_ZITI = 1;
const SHIP_TYPE_SFDF = 5;
const SHIP_TYPE_KUAIDI = 6;
const ZITI_TAG = 'ziti';

const PYS_BRAND_ID = 92;

define('MSG_API_KEY', 'api:key-fdb14217a00065ca1a47b8fcb597de0d'); //发短信密钥


define('FORMAT_DATETIME', 'Y-m-d H:i:s');
define('FORMAT_DATE', 'Y-m-d');
define('FORMAT_DATE_YUE_RI_HAN', 'n月j日');
define('FORMAT_DATEH', 'Y-m-d H');
define('FORMAT_TIME', 'H:i:s');
// 变量混淆加密，不支持 global，而使用$GLOBALS
// 变量混淆加密，不支持extract方法，要使用数组方式来使用变量

$GLOBALS['hookvars']['navmenu'] = array();
$GLOBALS['hookvars']['submenu'] = array();
Configure::write('Hook.helpers.Miao','MiaoHook');
Configure::write('Hook.components.Miao','MiaoHook');

define('WX_APPID', 'wxca7838dcade4709c');
//ID for service account(DO NOT CHANGE)
define('WX_APPID_SOURCE', 'wxca78');
define('WX_SECRET', '79b787ec8f463eeb769540464c9277b2');
define('WX_SERVICE_ID_NAME', '朋友说');
define('WX_SERVICE_ID_NO', 'pyshuo2014');

define('COMMON_PATH', ROOT . DS . 'lib' . DS);

define('TUAN_TIP_MSG','tuan_tip_msg');
define('TUAN_COMPLETE_MSG','tuan_complete_msg');
define('TUAN_CANCEL_MSG','tuan_cancel_msg');
define('TUAN_CREATE_MSG','tuan_create_msg');
define('TUAN_STARTDELIVER_MSG','tuan_startdeliver_msg');
define('TUAN_NOTIFYDELIVER_MSG','tuan_notifydeliver_msg');

const SHARE_SHIP_PYS_ZITI_TAG = 'pys_ziti';
const SHARE_SHIP_SELF_ZITI_TAG = 'self_ziti';
const SHARE_SHIP_KUAIDI_TAG = 'kuai_di';
const SHARE_SHIP_GROUP_TAG = 'pin_tuan';

include_once COMMON_PATH.'bootstrap.php';

function oauth_wx_source() {
    return 'wx-' . WX_APPID_SOURCE;
}

function send_weixin_message($post_data, $logObj = null) {
    $tries = 2;
    $wxOauthM = ClassRegistry::init('WxOauth');
    $wx_curl_option_defaults = array(
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    );
    while ($tries-- > 0) {
        $access_token = $wxOauthM->get_base_access_token();
        if (!empty($access_token)) {
            $curl = curl_init();
            $options = array(
                CURLOPT_URL => 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token,
                CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS
            );
            if (!empty($post_data)) {
                $options[CURLOPT_POSTFIELDS] = json_encode($post_data);
            }
            curl_setopt_array($curl, ($options + $wx_curl_option_defaults));
            $json = curl_exec($curl);
            curl_close($curl);
            $output = json_decode($json, true);
            if (!empty($logObj)) {
                $logObj->log("post weixin api send template message output: " . json_encode($output), LOG_DEBUG);
            }
            if ($output['errcode'] == 0) {
                return true;
            } else {
                if (!$wxOauthM->should_retry_for_failed_token($output)) {
                    return false;
                };
            }
            return false;
        }
    }
    return false;
}

function convert_to_short_url($url){
    $tries = 2;
    $wxOauthM = ClassRegistry::init('WxOauth');
    $wx_curl_option_defaults = array(
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    );
    $post_data = [
        "action" => "long2short",
        "long_url" => $url
    ];
    while ($tries-- > 0) {
        $access_token = $wxOauthM->get_base_access_token();
        if (!empty($access_token)) {
            $curl = curl_init();
            $options = array(
                CURLOPT_URL => 'https://api.weixin.qq.com/cgi-bin/shorturl?access_token=' . $access_token,
                CURLOPT_CUSTOMREQUEST => 'POST', // GET POST PUT PATCH DELETE HEAD OPTIONS
            );
            if (!empty($post_data)) {
                $options[CURLOPT_POSTFIELDS] = json_encode($post_data);
            }
            curl_setopt_array($curl, ($options + $wx_curl_option_defaults));
            $json = curl_exec($curl);
            curl_close($curl);
            $output = json_decode($json, true);
            if (!empty($logObj)) {
                $logObj->log("post weixin api send template message output: " . json_encode($output), LOG_DEBUG);
            }
            if ($output['errcode'] == 0) {
                return $output['short_url'];
            } else {
                if (!$wxOauthM->should_retry_for_failed_token($output)) {
                    return false;
                };
            }
            return false;
        }
    }
    return false;
}


function get_tuan_msg_element($tuan_buy_id,$flag=true){
    $tuanBuyingM = ClassRegistry::init('TuanBuying');
    $tuanTeamM = ClassRegistry::init('TuanTeam');
    $productM = ClassRegistry::init('Product');
    $tuanMemberM = ClassRegistry::init('TuanMember');
    $tuanOrderM = ClassRegistry::init('Order');
    $tb = $tuanBuyingM->find('first',array(
        'conditions' => array(
            'id' => $tuan_buy_id,
        )
    ));
    if(!empty($tb)) {
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
        if($flag){                                        //flag is false, select all the tuan_members,otherwise we select tuan_members who have bought goods
        $tuanOrders = $tuanOrderM->find('all',array(
            'conditions' => array(
                'member_id' => $tuan_buy_id,
                'status' => ORDER_STATUS_PAID,
                'type' => ORDER_TUANGOU,
            )
        ));
         //cache it
         $uids = Hash::extract($tuanOrders,'{n}.Order.creator');
         $mobilephones = Hash::extract($tuanOrders,'{n}.Order.consignee_mobilephone');
        }else{
        $tuan_members = $tuanMemberM->find('all', array(
            'conditions' => array(
                'tuan_id' => $tuan_id
            )
        ));
         //cache it
         $uids = Hash::extract($tuan_members,'{n}.TuanMember.uid');
         $mobilephones = '';
        }
        $consign_time = $tb['TuanBuying']['consign_time'];
        $consign_time = friendlyDateFromStr($consign_time,FFDATE_CH_MD);

        $tuan_name = $tt['TuanTeam']['tuan_name'];
        $tuan_addr = $tt['TuanTeam']['tuan_addr'];
        $product_name = $p['Product']['name'];
        $tuan_leader = $tt['TuanTeam']['leader_name'];
        $target_num = $tb['TuanBuying']['target_num'];
        $sold_num = $tb['TuanBuying']['sold_num'];
        $tb_status = intval($tb['TuanBuying']['status']);
        return array(
            'consign_time' => $consign_time,
            'target_num' => $target_num,
            'sold_num' => $sold_num,
            'uids'=>$uids,
            'team_id' => $tt['TuanTeam']['id'],
            'tuan_name' => $tuan_name,
            'product_name' => $product_name,
            'tuan_leader' => $tuan_leader,
            'tuan_buy_status' => $tb_status,
            'consignee_mobilephones' => $mobilephones,
            'tuan_addr' => $tuan_addr
        );
    }else{
        return null;
    }
}

/**
 * 取全部团购商品
 * @return mixed
 */
function getTuanProductsAsJson(){
    $tuanProducts = Cache::read('tuan_products');
    if(empty($tuanProducts)){
        $tuanProductM = ClassRegistry::init('TuanProduct');
        $tuanProducts = $tuanProductM->find('all',array(
            'conditions' => array(
                'deleted' => DELETED_NO
            ),
            'order' => 'priority desc'
        ));
        $tuanProducts = json_encode($tuanProducts);
        Cache::write('tuan_products',$tuanProducts);

    }
    return $tuanProducts;
}

function getTuanProducts(){
    return json_decode(getTuanProductsAsJson(),true);
}

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

function get_address($tuan_team, $offline_store){
    if(empty($offline_store)){
        return $tuan_team['TuanTeam']['tuan_addr'];
    }

    return get_offline_store_address($offline_store);
}
function get_offline_store_address($offline_store){
    $tuan_address = $offline_store['OfflineStore']['name'];
    if(empty($offline_store['OfflineStore']['owner_name'])){
        if(!empty($offline_store['OfflineStore']['owner_phone'])){
            $tuan_address .= "(联系电话: ".$offline_store['OfflineStore']['owner_phone'].")";
        }
    }
    else{
        $tuan_address .= "(联系人: ".$offline_store['OfflineStore']['owner_name'];
        if(!empty($offline_store['OfflineStore']['owner_phone'])){
            $tuan_address .= " ".$offline_store['OfflineStore']['owner_phone'];
        }
        $tuan_address .= ")";
    }

    return $tuan_address;
}


class TuanShip{
    public static function get_all_tuan_ships(){
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

    public static function get_ship_name($id){
        $ships = TuanShip::get_all_tuan_ships();
        return $ships[$id]['name'];
    }

    public static function  get_ship_code($id){
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

    public function get_all_ship_info() {
        $ship_types = ShipAddress::ship_types();
        $ship_type_list = Hash::combine($ship_types, '{n}.company', '{n}.name', '{n}.id');
        return $ship_type_list;
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

/**
 * @param $open_id
 * @param $title
 * @param $product_name
 * @param $tuan_leader_wx
 * @param $remark
 * @param $deatil_url
 * @return bool
 * 团购提示信息
 */
function send_tuan_tip_msg($open_id,$title,$product_name,$tuan_leader_wx,$remark,$deatil_url){
    $post_data = array(
        "touser" => $open_id,
        "template_id" => 'P4iCqkiG7_s0SVwCSKyEuJ0NnLDgVNVCm2VQgSGdl-U',
        "url" =>$deatil_url,
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
 * @param $open_id
 * @param $deatil_url
 * @return bool
 *
 * 发送中奖消息
 */
function send_rice_prize_msg($open_id,$deatil_url){
    $post_data = array(
        "touser" => $open_id,
        "template_id" => 'vffIekz48NrxDRNbiGP5_xTvCqBHusA_W5pidHhGaHs',
        "url" =>$deatil_url,
        "topcolor" => "#FF0000",
        "data" => array(
            "first" => array("value" => "朋友说萌娃比赛领取奖品啦！"),
            "keyword1" => array("value" => "五常稻花香大米1元领"),
            "keyword2" => array("value" => "价值15元的五常稻花香大米1份"),
            "remark" => array("value" => "点击链接马上领取。", "color" => "#FF8800")
        )
    );
    return send_weixin_message($post_data);
}

/**
 * @param $pid
 * @param $defUri
 * @return string
 */
function product_link($pid, $defUri) {
    $linkInCache = Cache::read('link_pro_manage_' . $pid);
    if (!empty($linkInCache)) {
        return $linkInCache;
    }
    $pModel = ClassRegistry::init('Product');
    $p = $pModel->findById($pid);
    return product_link2($p, $defUri);
}

function product_link2($p, $defUri = '/') {
    if (!empty($p)) {
        $pp = empty($p['Product']) ? $p : $p['Product'];
        $link = WX_HOST."/products/" . date('Ymd', strtotime($pp['created'])) . "/" . $pp['slug'] . ".html";
        Cache::write('link_pro_manage_' . $pp['id'], $link);
        return $link;
    } else {
        return $defUri;
    }
}

function b2c_brands(){
    return array(18, 92, 180, 206);
}

function is_b2c($brand_id){
    // 18：老杨；92：朋友说；206：樱花；180：踏歌
    return in_array($brand_id, b2c_brands());
}

function share_self_ziti_order_filter($var){
    return $var['Order']['ship_mark'] == SHARE_SHIP_SELF_ZITI_TAG;
}

function share_kuai_di_order_filter($var){
    return $var['Order']['ship_mark'] == SHARE_SHIP_KUAIDI_TAG;
}

function share_pys_ziti_order_filter($var){
    return $var['Order']['ship_mark'] == SHARE_SHIP_PYS_ZITI_TAG;
}
function share_none_order_filter($var){
    return ($var['Order']['ship_mark'] == null)||(trim($var['Order']['ship_mark']) == '');
}
function ziti_order_filter($var){
    return $var['Order']['ship_mark'] == 'ziti' && is_b2c($var['Order']['brand_id']);
}
function sfby_order_filter($var){
    return $var['Order']['ship_mark'] == 'sfby' && is_b2c($var['Order']['brand_id']);
}
function sfdf_order_filter($var){
    return $var['Order']['ship_mark'] == 'sfdf' && is_b2c($var['Order']['brand_id']);
}
function kuaidi_order_filter($var){
    return $var['Order']['ship_mark'] == 'kuaidi' && is_b2c($var['Order']['brand_id']);
}
function none_order_filter($var){
    return (($var['Order']['ship_mark'] == null)||(trim($var['Order']['ship_mark']) == '')) && is_b2c($var['Order']['brand_id']);
}
function c2c_order_filter($var){
    return  !is_b2c($var['Order']['brand_id']);
}

function pys_ziti_filter($var){
    return $var['OfflineStore']['type']==1;
}

function hlj_ziti_filter($var){
    return $var['OfflineStore']['type']==0;
}

function has_permission_to_modify_order($user){
    return in_array($user, array('miaoyue', 'xiaoguang', 'xiaoqing', 'xinyu', 'jingge', 'huiyan', 'jiangwei'));
}

function dateRange( $first, $last, $step = '+1 day', $format = 'Y-m-d' ) {

    $dates = array();
    $current = strtotime( $first );
    $last = strtotime( $last );

    while( $current <= $last ) {

        $dates[] = date( $format, $current );
        $current = strtotime( $step, $current );
    }

    return $dates;
}


/**
 * @param $before_day
 * @param $week_days
 * @param $time
 * @return bool|string
 */
function get_send_date($deadline_day, $deadline_time, $week_days)
{
    $week_days = explode(',', $week_days);
    $interval_one_day = new DateInterval('P1D');

    $send_date = new DateTime();
    if(_is_after_deadline_time($send_date, $deadline_time)){
        $deadline_day = $deadline_day + 1;
    }
    $send_date->add(new DateInterval('P'.$deadline_day.'D'));

    while (!in_array($send_date->format('N'), $week_days)) {
        $send_date->add($interval_one_day);
    }

    return $send_date;
}

function _is_after_deadline_time($now, $deadline_time)
{
    $deadline_time = explode(':', $deadline_time);
    $limit_time = new DateTime('now');
    $limit_time->setTime($deadline_time[0], $deadline_time[1], $deadline_time[2]);

    return $now > $limit_time;
}

function getMonthRange($date, $returnFirstDay = true) {
    $timestamp = strtotime($date);
    if ($returnFirstDay) {
        $monthFirstDay = date('Y-m-1', $timestamp);
        return $monthFirstDay;
    } else {
        $mdays = date('t', $timestamp);
        $monthLastDay = date('Y-m-' . $mdays, $timestamp);
        return $monthLastDay;
    }
}
