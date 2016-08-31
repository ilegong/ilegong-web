<?php
include_once COMMON_PATH . 'Config' . DS . 'extend.php';

include_once COMMON_PATH . 'global_function.php';

error_reporting(E_ERROR | E_WARNING | E_PARSE);

const SHARE_TYPE_DEFAULT = 0; //默认团购
const SHARE_TYPE_GROUP = 1; //拼团团购
const SHARE_TYPE_POOL_SELF = 3; //产品池类型的分享 不能进行购买
const SHARE_TYPE_POOL_FOR_PROXY = 4; //产品池中购买的分享的链接
const SHARE_TYPE_PIN_TUAN = 5; //拼团的分享
const SHARE_TYPE_POOL = 6; //来自产品街的分享

const ORDER_TYPE_DEF = 1;
const ORDER_TYPE_GROUP = 2;
const ORDER_TYPE_GROUP_FILL = 4; //团购补充素有剩下的
const ORDER_TYPE_PARENT = 3;
const ORDER_TYPE_TUAN = 5; //团长团购

const ORDER_TYPE_SPLIT = 11; //订单已经被拆分
const ORDER_TYPE_WESHARE_BUY = 9; // 分享订单
const ORDER_TYPE_WESHARE_BUY_ADD = 10; //差价补充订单
const ORDER_TYPE_PIN_TUAN = 12; //拼团的订单类型

//change milk order to tuan sec kill
const ORDER_TYPE_TUAN_SEC = 6;

const ALI_PAY_TYPE_WAP = "wap";
const ALI_PAY_TYPE_WAPAPP = "wapapp";
const ALI_PAY_TYPE_PC = "pc";

const SHIPTYPE_ID_ZITI = 137;

const SCORE_ORDER_COMMENT = 1;
const SCORE_NEW_USER = 2;
const SCORE_ORDER_DONE = 3;
const SCORE_ORDER_SPENT = 4;

const SCORE_ORDER_SPENT_UNDO = 6; //取消/退款等，返回使用的积分

const SCORE_ORDER_SPENT_CANCEL = 5; //退款取消已经发放的积分

const SCORE_REFERRAL_BIND_OK = 7; //推荐人推荐某人绑定后得到的积分
const SCORE_REFERRAL_BIND_OK_TO = 8; //推荐人推荐某人某人绑定后，被推荐人得到积分
const SCORE_REFERRAL_FIRST_ORDER = 9; //推荐人推荐某人完成首单

const SPEICAL_LIST_DAILY_ID = 4;

const ASSET_SXA_DOMAIN = 's.tongshijia.com';

const TAG_ID_CHULIANG = 16;
const TAG_ID_ROUQIN_DANPIN = 17;
const TAG_ID_XINXIANSHUIGUO = 18;
const TAG_ID_XINPIN_SHICHI = 19;

const USER_IS_PROXY = 1;

const FFDATE_CH_MDW = 'chinese_m_d_w';
const FFDATE_CH_MD = 'chinese_m_d';

const SHARE_ORDER_OPERATE_TYPE = 'ShareOrder'; //用户看订单权限[查看订单]
const SHARE_INFO_OPERATE_TYPE = 'ShareInfo'; //分享编辑权限[编辑分享]
const SHARE_TAG_ORDER_OPERATE_TYPE = 'ShareTagOrder';//查看分组订单权限
const SHARE_MANAGE_OPERATE_TYPE = 'ShareManage';//分享管理权限[私信消息]
const SHARE_OPERATE_SCOPE_TYPE = 'Share'; //用户权限的配置范围

const SHARE_ORDER_OPERATE_CACHE_KEY = 'share_order_operate_data_cache_key'; // 分享订单管理
const SHARE_INFO_OPERATE_CACHE_KEY = 'share_info_operate_data_cache_key'; //分享详情的管理
const SHARE_MANAGE_OPERATE_CACHE_KEY = 'share_manage_operate_data_cache_key'; //分享管理
const SHARE_ORDER_TAG_OPERATE_CACHE_KEY = 'share_order_tag_operate_data_cache_key'; // 分享订单--分类订单管理

const GOOD_ORDER_PAY_TYPE = 0; //商品购买支付

const LOGISTICS_ORDER_PAY_TYPE = 1; //物流支付

$_display_tags_in_home = array(TAG_ID_CHULIANG, TAG_ID_ROUQIN_DANPIN, TAG_ID_XINPIN_SHICHI, TAG_ID_XINPIN_SHICHI);

$_coupon_could_distribute = array(18483 => '新用户50返10元券', 18482 => '新用户100返20元券');

const RR_LOGISTICS_CALLBACK = 'http://www.tongshijia.com/logistics/rr_logistics_callback.html';
const RR_LOGISTICS_USERNAME = '13693655401';
const RR_LOGISTICS_APP_KEY = 'c7ffbfecd5f706328e129865180388a2';
//const RR_LOGISTICS_APP_KEY = '5ad9d9600d21bdf6193eeff7a4ba9b99';
const RR_LOGISTICS_URL = 'http://openapi.rrkd.cn/v2';
//const RR_LOGISTICS_URL = 'http://code.rrkd.cn/v2';
const PYS_PROXY_NAME = '朋友说';

const RR_SINGLE_LOGISTICS_ORDER_TYPE = 0;//人人 单独订单类型
const RR_MULTI_LOGISTICS_ORDER_TYPE = 1;//人人 拼单类型

//物流订单status
const LOGISTICS_ORDER_WAIT_PAY_STATUS = 0;
const LOGISTICS_ORDER_PAID_STATUS = 1;
const LOGISTICS_ORDER_RECEIVE = 2;
const LOGISTICS_ORDER_TAKE = 3;
const LOGISTICS_ORDER_SIGN = 4;
const LOGISTICS_ORDER_CANCEL = 5;
const LOGISTICS_ORDER_INVALID = 6; //订单失效(退款操作)

//拼团的的状态
const PIN_TUAN_TAG_DEFAULT_STATUS = 0;
const PIN_TUAN_TAG_PROGRESS_STATUS = 1;
const PIN_TUAN_TAG_SUCCESS_STATUS = 2;
const PIN_TUAN_TAG_EXPIRE_STATUS = 3;

//拼团记录的状态
const PIN_TUAN_RECORD_DEFAULT_STATUS = 0;
const PIN_TUAN_RECORD_PAID_STATUS = 1;

const PROXY_USER_LEVEL_VALUE = 2;

function is_admin_uid($uid) {

    $_admin_uids = array(
        '632' // liu zhaoren
    , '8' // ronghao
    , '141' //yxg
    , '819' //高静静
    , '755' //高静静
    , '701166' //刘丹
    , '5081'   //张晓庆
    , '633345' //师超鹏
    );
    return $uid && false !== array_search($uid, $_admin_uids, true);
}

/**
 * @param $change
 * @param $reason
 * @return string
 */
function action_of_score_item($change, $reason) {
    if ($reason == SCORE_NEW_USER || $reason == SCORE_ORDER_COMMENT || $reason == SCORE_ORDER_DONE) {
        $action = '增加';
    } else if ($reason == SCORE_ORDER_SPENT) {
        $action = '消费';
    } else if ($reason == SCORE_ORDER_SPENT_UNDO) {
        $action = '返还';
    } else if ($reason == SCORE_ORDER_SPENT_CANCEL) {
        $action = '取消';
    } else {
        $action = $change > 0 ? '增加' : '减少';
    }
    return $action;
}

/**
 * @param $val
 * @return string
 */
function get_user_level_text($val) {
    $level_name_map = get_user_levels();
    return $level_name_map[$val];
}

function get_user_level_msg_count($val)
{
    $settings = array(
        0 => array(
            'limit' => 2,
        ),
        1 => array(
            'limit' => 2,
        ),
        2 => array(
            'limit' => 4,
        ),
        3 => array(
            'limit' => 4,
        ),
        4 => array(
            'limit' => 4,
        ),
        5 => array(
            'limit' => 4,
        ),
        6 => array(
            'limit' => 4,
        )
    );
    return $settings[$val];
}

function get_user_levels(){
    return array(
        0 => '分享达人',
        1 => '实习团长',
        2 => '正式团长',
        3 => '优秀团长',
        4 => '高级团长',
        5 => '资深团长',
        6 => '首席团长'
    );
}

App::build(array(
    'plugins' => array(COMMON_PATH . 'Plugin' . DS, APP_PATH . 'Plugin' . DS,),
    //'views' => array(COMMON_PATH.'View'.DS),
    'libs' => array(COMMON_PATH, APP_PATH . 'Lib' . DS),
    'vendors' => array(COMMON_PATH . 'Vendor' . DS,),
    'helpers' => array(COMMON_PATH . 'View' . DS . 'Helper' . DS,),
    'locales' => array(ROOT . DS . 'data' . DS . 'locale' . DS),
    'components' => array(COMMON_PATH . 'Component' . DS),
    'behaviors' => array(COMMON_PATH . 'Behavior' . DS),
));
if (defined('SAE_MYSQL_DB')) {
    App::build(array(
        'locales' => array(TMP . 'locale' . DS),
    ));

    // sae上禁止放在二级目录
    define('IN_SAE', true);
    define('SAE_STORAGE_UPLOAD_DOMAIN_NAME', 'images');
    define('SAE_STORAGE_UPLOAD_AVATAR_DOMAIN_NAME', 'avatar');
    define('SAE_STORAGE_UPLOAD_DOMAIN_URL', 'http://' . $_SERVER['HTTP_APPNAME'] . '-' . SAE_STORAGE_UPLOAD_DOMAIN_NAME . '.stor.sinaapp.com');
    define('UPLOAD_FILE_URL', 'http://' . $_SERVER['HTTP_APPNAME'] . '-' . SAE_STORAGE_UPLOAD_DOMAIN_NAME . '.stor.sinaapp.com');
    // SAE上传地址，S3,Storage等. 带 saestor://，可直接用于fwrite,copy,等函数读写文件
    // 结合UPLOAD_RELATIVE_PATH能获取到文件的地址
    define('UPLOAD_FILE_PATH', 'saestor://' . SAE_STORAGE_UPLOAD_DOMAIN_NAME . '/');

    define('WEB_VISIT_CACHE', 'saemc://cache/');
    define('WEB_VISIT_CACHE_URL', '/cache/');

    define('DATA_PATH', 'saemc://data/'); //data目录使用kvdb，其余stor的均使用upload_file_path
} else {
    /**
     * 数据库保存的文件路径从files开始，UPLOAD_FILE_URL不用包含files
     * 结合UPLOAD_RELATIVE_PATH能获取到文件的地址
     */
    define('UPLOAD_FILE_PATH', WWW_ROOT . DS); // 本地上传路径
    define('UPLOAD_FILE_URL', str_replace('\\', '/', APP_SUB_DIR)); //APP_SUB_DIR
    define('WEB_VISIT_CACHE', WWW_ROOT . DS . 'cache' . DS);
    define('WEB_VISIT_CACHE_URL', APP_SUB_DIR . '/cache/');
    define('DATA_PATH', ROOT . DS . 'data' . DS);
}
// 文件的相对地址,结合UPLOAD_FILE_PATH获取到本地的地址
define('UPLOAD_RELATIVE_PATH', '/files/');
// sae 队列接口，提供给外部调用。 类似于分布式任务。本地调用接口添加任务，sae队列中的任务一个一个去执行。
//define('SAE_TASK_QUEUE_URL', 'http://'.$_SERVER['HTTP_APPNAME'].'.sinaapp.com/api/sae/TaskQueue.php');
define('SAE_TASK_QUEUE_URL', 'http://www.miaomiaoxuan.com/api/sae/TaskQueue.php');

if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Etc/GMT-8');
}


define('HTTP_REQUEST_METHOD', 'HttpCurl'); // or HttpSocket

define('DEVELOP_MODE', '1'); // 显示隐藏的控制项，显示页面钩子

define('OPEN_INTERNATIONAL', 1);    // 站点是否开启多语言

Configure::write('Dispatcher.filters', array(
    'AssetDispatcher', // 处理js和css文件，压缩输出，作用不大。不能支持将内容汇总到一个文件输出
    //'CacheDispatcher', // 检查整个页面的缓存.php文件，有则直接包含输出。与cachehelp配合使用，缓存文件有cachehelp生成。
));

if (!defined('SAE_MYSQL_DB')) {
    /**
     * Configures default file logging options
     */
    App::uses('CakeLog', 'Log');
    CakeLog::config('debug', array(
        'engine' => 'FileLog',
        'types' => array('notice', 'info', 'debug'),
        'file' => 'debug',
    ));
    CakeLog::config('error', array(
        'engine' => 'FileLog',
        'types' => array('warning', 'error', 'critical', 'alert', 'emergency'),
        'file' => 'error',
    ));
}


/**
 * 加载扩展配置项中开启的各插件
 */
App::uses('PhpReader', 'Configure');
Configure::config('default', new PhpReader(DATA_PATH));
try {
    Configure::load('settings');
} catch (ConfigureException $e) {
    CakeLog::error('Configure load settings failed in lib/bootstrap.');
}

define('CLOUD_CRON_SECRET', Configure::read('Security.cloud_cron_secret'));

if (php_sapi_name() === 'cli' || (defined('IN_SAE') && isset($_GET['cron_secret']) && $_GET['cron_secret'] == CLOUD_CRON_SECRET)) {
    define('IN_CLI', true);
    unset($_GET['cron_secret'], $_REQUEST['cron_secret']);
}

$pluginBootstraps = Configure::read('Hook.bootstraps');
$plugins = array_filter(explode(',', $pluginBootstraps));

if (!empty($plugins)) {
    foreach ($plugins as $plugin) {
        $pluginName = Inflector::camelize($plugin);
        try {
            CakePlugin::load($pluginName, array(
                'bootstrap' => true,
                'routes' => true,
                'ignoreMissing' => true,
            ));
        } catch (Exception $e) {
            CakeLog::error('Plugin not found in lib/bootstrap: ' . $pluginName);
            continue;
        }
    }
}


function clear_tag_cache($tagId) {
    Cache::write('tag-products' . $tagId, '[]');
}

function check_sae(){
    return defined('SAE_MYSQL_DB');
}

/**
 * @param $payment
 * @return string
 */
function get_user_payment_info($payment){
    if (isJson($payment)) {
        $payment_data = json_decode($payment, true);
        $str = '';
        if($payment_data['payment']){
            $payment_data = $payment_data['payment'];
        }
        if (!empty($payment_data)) {
            if(is_array($payment_data) || is_object($payment_data)){
                while (list ($key, $val) = each($payment_data)) {
                    if ($key == 'type') {
                        if ($val == 0) {
                            $str .= '【支付宝】';
                        }
                        if ($val == 1) {
                            $str .= '【银行卡】';
                        }
                    }
                    if ($key == 'account') {
                        $str .= ' 账号: ' . $val;
                    }
                    if ($key == 'full_name') {
                        $str .= ' 姓名: ' . $val;
                    }
                }
                return $str;
            }
        }
    }
    return $payment;
}

function isJson($string){
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

function get_order_from_tag_by_flag($flag){
    if ($flag == 1) {
        return '朋友圈';
    }
    if ($flag == 2) {
        return '微信群';
    }
    if ($flag == 3) {
        return '模板消息';
    }
    if ($flag == 4) {
        return '首页';
    }
    if ($flag == 5) {
        return 'APP下单';
    }
    if ($flag == 6) {
        return '微信单聊';
    }
    if ($flag == 7) {
        return '发现页面';
    }
    if ($flag == 8) {
        return '个人中心';
    }
    if ($flag == 9) {
        return '系统推荐';
    }
    if ($flag == 10) {
        return '促销';
    }
    if ($flag == 11) {
        return '微信文章';
    }
    if ($flag == 12) {
        return 'banner';
    }
    if($flag == 13){
        return '服务号';
    }
    if ($flag == 19) {
        return '其他';
    }
    return '直接购买';
}