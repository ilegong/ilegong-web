<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 浩
 * Date: 14-11-15
 * Time: 下午2:12
 * To change this template use File | Settings | File Templates.
 */
class CronController extends AppController
{

    public $name = 'Cron';

    public $uses = array('CouponItem');

    public $components = array('Weixin','WeshareBuy', 'ShareUtil', 'PintuanHelper', 'RedisQueue');


    function cron_send_pintuan_warn_msg(){
        $this->autoRender = false;
        $this->PintuanHelper->cron_send_warning_msg();
        echo json_encode(array('success' => true));
        return;
    }

    function cron_update_pintuan_tag_status(){
        $this->autoRender = false;
        $this->PintuanHelper->cron_change_tag_status();
        echo json_encode(array('success' => true));
        return;
    }

    function send_weshare_order_to_comment_msg($weshareId = null) {
        $this->autoRender = false;
        $this->WeshareBuy->send_to_comment_msg($weshareId);
        echo json_encode(array('success' => true));
    }

    function batch_update_weshare_order($weshareId){
        $this->autoRender = false;
        $this->WeshareBuy->batch_update_order_status($weshareId);
        echo json_encode(array('success' => true));
    }

    function change_share_order_status_and_send_msg($weshareId = null){
        $this->autoRender = false;
        $this->WeshareBuy->change_status_and_send_to_comment_msg($weshareId);
        echo json_encode(array('success' => true));
    }


    function cron_update_overtime_order_status() {
        $this->autoRender = false;
        $query_limit_time = date('Y-m-d H:i:s', strtotime('-2 hour'));
        $orderM = ClassRegistry::init('Order');
        $update_result = $orderM->updateAll(array('status' => ORDER_STATUS_CANCEL, 'business_remark' => 'concat(business_remark, "_canceled_by_sys")'), array('status' => ORDER_STATUS_WAITING_PAY, 'created < ' => $query_limit_time));
        echo json_encode(array('success' => true, 'result' => $update_result));
        return;
    }

    function process_sharer_fans(){
        $this->autoRender = false;
        $allShares = $this->ShareUtil->get_all_weshares();
        //批量添加任务
        $taskArray = array();
        foreach($allShares as $share){
            $share_id = $share['Weshare']['id'];
            $sharer_id = $share['Weshare']['creator'];
            $url = '/cron/load_fans_task/'.$share_id.'/'.$sharer_id;
            $taskArray[] = array('url'=>$url);
        }
        $this->RedisQueue->add_tasks('share', $taskArray);
        echo json_encode(array('success' => true));
    }

    function load_fans_task($share_id, $sharer_id){
        $this->autoRender = false;
        $this->ShareUtil->process_weshare_task($share_id, $sharer_id);
        echo json_encode(array('success' => true));
    }

    function send_coupon_timeout_message()
    {
        $this->autoRender = false;
        $result = array();

        $couponItems = $this->CouponItem->find_24hours_timeout_coupons();
        foreach ($couponItems as $couponItem) {
            $id = $couponItem['CouponItem']['id'];
            $user_id = $couponItem['CouponItem']['bind_user'];
            $coupon_name = $couponItem['Coupon']['name'];
            if($couponItem['Coupon']['brand_id'] > 0 && $couponItem['Coupon']['product_list'] == 0){
                $coupon_name = $coupon_name."的优惠券";
            }
            $timeout_time = $couponItem['Coupon']['valid_end'];
            $this->Weixin->send_coupon_timeout_message($user_id, $coupon_name, $timeout_time);
            $this->CouponItem->change_coupons_message_status_to_sent($id);
        }

        $result['result'] = "true";
        $result['count'] = count($couponItems);
        echo json_encode($result);
    }
    public function send_kefu_message(){
        $this->autoRender = false;
        $cron = $this->Cron->find('all', array('conditions' => array('type' =>0)
        ));
        $cron_ids = array();
        $this->loadModel('WxOauth');
        foreach ($cron as $rn){
            $this->WxOauth->send_kefu($rn['Cron']['content']);
            $cron_ids[] = $rn['Cron']['id'];
        }
        $this->Cron->deleteAll(array('OR' => array(
            array('id' => $cron_ids),
        )));

        echo count($cron_ids);
    }

    public function send_cake_coupon_msg(){
        $this->autoRender=false;
        $userId= $_REQUEST['user_id'];
        $getCouponUrl = WX_HOST.'/shortmessages/get_haohao_cake_coupon';
        if(empty($userId)){
            $this->loadModel('Order');
            $userIds = $this->Order->query('SELECT DISTINCT cos.creator FROM cake_orders cos, cake_carts ccs WHERE ccs.order_id = cos.id AND cos.status IN ( 1, 2, 3, 9 ) AND ccs.product_id IN (230,862,873) GROUP BY cos.consignee_mobilephone ORDER BY cos.created DESC LIMIT 0 , 200');
            $userIds = Hash::extract($userIds,'{n}.cos.creator');
            foreach($userIds as $uid){
                $this->Weixin->send_coupon_cake_msg($uid, $getCouponUrl);
            }
        }else{
            $this->Weixin->send_coupon_cake_msg($userId, $getCouponUrl);
        }
        echo json_encode(array('sucess'=>true));
    }

    public function download_photo_from_wx_for_comment(){
        $this->autoRender=false;
        $commentModel = ClassRegistry::init('Comment');
        $userIds = $commentModel->find('all',array(
            'fields'=>array('DISTINCT user_id')
        ));
        $userIds = Hash::extract($userIds,'{n}.Comment.user_id');
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $wxUsers = $oauthBindModel->find('all', array(
            'conditions'=>array(
                'user_id'=>$userIds
            )
        ));
        $result = $this->process_download_wx_photo($wxUsers);
        echo $result;
    }

    public function download_photo_from_wx_by_product_id(){
        $this->autoRender=false;
        $productId= $_REQUEST['id'];
        $commentModel = ClassRegistry::init('Comment');
        $userIds = $commentModel->find('all',array(
            'conditions'=>array(
                'data_id'=>$productId
            ),
            'fields'=>array('DISTINCT user_id')
        ));
        $userIds = Hash::extract($userIds,'{n}.Comment.user_id');
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $wxUsers = $oauthBindModel->find('all', array(
            'conditions'=>array(
                'user_id'=>$userIds
            )
        ));
        $count = $this->process_download_wx_photo($wxUsers);
        echo $count;
    }

    public function download_photo_from_wx_by_user_id(){
        $this->autoRender=false;
        $userIds = $_REQUEST['ids'];
        $userIds = explode(',',$userIds);
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $wxUsers = $oauthBindModel->find('all', array(
            'conditions'=>array(
                'user_id'=>$userIds
            )
        ));
        $count = $this->process_download_wx_photo($wxUsers);
        echo $count;
    }

    /**
     * 更新 用户微信授权的unionId
     */
    public function update_oauthbind_unionid(){
        $this->autoRender = false;
        $limit = $_REQUEST['limit'];
        if ($limit == null) {
            $limit = 20;
        }
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $wxUsers = $oauthBindModel->find('all', [
            'conditions' => [
                'oauth_token is not null and oauth_token!=\'\'',
                'unionId is null',
            ],
            'order' => ['id DESC'],
            'limit' => $limit
        ]);
        $count = $this->process_update_oathbind_unionid($wxUsers);
        echo $count;
        exit();
    }

    function process_update_oathbind_unionid($oathBinds){
        $this->loadModel('Oauthbind');
        $data = [];
        if (!empty($oathBinds)) {
            foreach ($oathBinds as $item) {
                $open_id = $item['Oauthbind']['oauth_openid'];
                $wx_user = get_user_info_from_wx($open_id);
                $unionid = $wx_user['unionid'];
                if(!empty($unionid)){
                    $data[] = ['id' => $item['Oauthbind']['id'], 'unionId' => $unionid];
                }
            }
            $this->Oauthbind->saveAll($data);
        }
        return count($data);
    }

    public function download_photo_from_wx() {
        $this->autoRender=false;
        $this->loadModel('DownloadLog');
        $downloadLog = $this->DownloadLog->find('first',array(
            'conditions'=>array(
                'name'=>'download_photo_from_wx'
            )
        ));
        $start = $downloadLog['DownloadLog']['tag'];
        $start = intval($start);
        $limit = $_REQUEST['limit'];
        if($limit==null){
            $limit=500;
        }
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $wxUsers = $oauthBindModel->find('all', array(
            'limit' => $limit,
            'offset' => $start
        ));
        $count = $this->process_download_wx_photo($wxUsers);
        $this->DownloadLog->id=$downloadLog['DownloadLog']['id'];
        $this->DownloadLog->saveField('tag',($start+$limit));
        echo $count;
    }

    public function send_ship_info(){
        $this->autoRender=false;
        $this->loadModel('Order');
        $this->loadModel('Cart');
        $start_date = date("Y-m-d",strtotime("-5 day"));
        $date = date('Y-m-d', time());
        $orders = $this->Order->find('all',array(
            'conditions'=>array(
                'created >='=>$start_date,
                'created <'=>$date,
                'status'=>ORDER_STATUS_SHIPPED,
                'published'=>1,
                'deleted'=>0,
                'not'=>array(
                    'ship_code'=>null,
                    'ship_type'=>null,
                ),
            )
        ));
        $from_url='www.kuaidi100.com';
        $ship_infos = ShipAddress::get_all_ship_info();
        foreach($orders as $order){
            $ship_type = $order['Order']['ship_type'];
            $consignee_address=$order['Order']['consignee_address'];
            $ship_code=$order['Order']['ship_code'];
            if(!preg_match("/([\x81-\xfe][\x40-\xfe])/", $ship_code, $match)&&!empty($ship_code)&&!empty($ship_type)&&!mb_strpos($consignee_address,'自提')){
                $com = key($ship_infos[$order['Order']['ship_type']]);
                $comName=current($ship_infos[$order['Order']['ship_type']]);
                //http://www.kuaidi100.com/query?id=1&type=quanfengkuaidi&postid=710023594269&valicode=&temp=0.018777450546622276
                $url = 'http://www.kuaidi100.com/query?id=&type='.trim($com).'&postid='.trim($order['Order']['ship_code']);
                $contents = gethtml($from_url,$url);
                $contentObject = json_decode($contents,true);
                $orderId = $order['Order']['id'];
                $userId = $order['Order']['creator'];
                //get ship info
                if(count($contentObject['data'])>0){
                    if(!empty($contentObject['state'])&&$contentObject['state']!='3'){
                        $currentShipInfo = $contentObject['data'][0];
                        $shipInfo = $currentShipInfo['time'].' '.$currentShipInfo['context'];
                        $products = $this->Cart->find('all',array(
                            'conditions'=>array(
                                'order_id'=>$orderId,
                                'creator'=>$userId,
                                'deleted'=>0,
                                'status'=>1
                            ),
                            'fields'=>array('name','num')
                        ));
                        $goodInfo='';
                        $goodNum=0;
                        foreach($products as $p){
                            $goodInfo.=$p['Cart']['name'].'X'.$p['Cart']['num'].' ';
                            $goodNum=$goodNum+$p['Cart']['num'];
                        }
                        if($this->Weixin->send_order_ship_info_msg($userId,$shipInfo,$orderId,$comName,$goodInfo,$goodNum)){
                            $this->log('push ship info '.$orderId.' wx send success on date '.$date.' curl fetch data '.$contents);
                        }else{
                            $this->log('push ship info '.$orderId.' wx send error on date '.$date.' curl fetch data '.$contents);
                        }
                    }
                }else{
                    $this->log('push ship info '.$orderId.' can not fetch ship info on date '.$date.' url is '.$url.' return content is '.$contents);
                }
            }
        }
        echo 'success';
    }


    function process_download_wx_photo($oathBinds) {
        $this->log('download avatar length : '.(count($oathBinds)));
        $resultCount = 0;
        $this->loadModel('User');
        $this->loadModel('Oauthbind');
        $this->loadModel('CronFaildInfo');
        if (!empty($oathBinds)) {
            foreach ($oathBinds as $item) {
                $user_id = $item['Oauthbind']['user_id'];
                $open_id = $item['Oauthbind']['oauth_openid'];
                $wx_user = get_user_info_from_wx($open_id);
                $this->log('download wx user info '.json_encode($wx_user));
                $photo = $wx_user['headimgurl'];
                if(!empty($photo)){
                    $this->log('download wx user photo '.$photo);
                    $download_url = download_photo_from_wx($photo);
                    $this->log('download wx user photo download url '.$download_url);
                    if (!empty($download_url)) {
                        $this->User->id = $user_id;
                        if ($this->User->saveField('image', $download_url)) {
                            $resultCount++;
                        }else{
                            $this->CronFaildInfo->save(array(
                                'info_id'=>$user_id,
                                'type'=>'download_photo_from_wx'
                            ));
                        }
                    }else{
                        $this->CronFaildInfo->save(array(
                            'info_id'=>$user_id,
                            'type'=>'download_photo_from_wx'
                        ));
                    }
                }else{
                    $this->CronFaildInfo->save(array(
                        'info_id'=>$user_id,
                        'type'=>'download_photo_from_wx'
                    ));
                }
            }
        }
        return $resultCount;
    }


    function delete_complete_curl_job(){
        $redisCli = createRedisCli();
        $it = NULL; /* Initialize our iterator to NULL */
        $redisCli->setOption(Redis::OPT_SCAN, Redis::SCAN_RETRY); /* retry when we get no keys back */
        $keys = $redisCli->scan($it, '*q:job:*', 20);
        $deletes = [];
        foreach ($keys as $key) {
            $item = $redisCli->hGetAll($key);
            if ($item['state'] == 'complete') {
                $deletes[] = $key;
                $data_keys = $redisCli->hKeys($key);
                foreach ($data_keys as $data_key_item) {
                    $redisCli->hDel($key, $data_key_item);
                }
            }
        }
        echo json_encode(['keys' => $deletes]);
        exit;
    }

}