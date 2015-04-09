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
const ORDER_STATUS_PAID=1;


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
                'status' => ORDER_STATUS_PAID
            )
        ));
         $uids = Hash::extract($tuanOrders,'{n}.Order.creator');
        }else{
        $tuan_members = $tuanMemberM->find('all', array(
            'conditions' => array(
                'tuan_id' => $tuan_id
            )
        ));
         $uids = Hash::extract($tuan_members,'{n}.TuanMember.uid');
        }
        $consign_time = $tb['TuanBuying']['consign_time'];
        $consign_time = friendlyDateFromStr($consign_time,FFDATE_CH_MD);

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
            'uids'=>$uids,
            'tuan_name' => $tuan_name,
            'product_name' => $product_name,
            'tuan_leader' => $tuan_leader,
            'tuan_buy_status' => $tb_status
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
            )
        ));
        $tuanProducts = json_encode($tuanProducts);
        Cache::write('tuan_products',$tuanProducts);

    }
    return $tuanProducts;
}

function getTuanProducts(){
    return json_decode(getTuanProductsAsJson(),true);
}