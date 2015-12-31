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

    public $components = array('Weixin','WeshareBuy', 'ShareUtil', 'PintuanHelper');

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

    function process_sharer_fans(){
        $this->autoRender = false;
        $allShares = $this->ShareUtil->get_all_weshares();
        $queue = new SaeTaskQueue('share');
        //批量添加任务
        $taskArray = array();
        foreach($allShares as $share){
            $share_id = $share['Weshare']['id'];
            $sharer_id = $share['Weshare']['creator'];
            $url = '/cron/load_fans_task/'.$share_id.'/'.$sharer_id;
            $taskArray[] = array('url'=>$url);
        }
        $queue->addTask($taskArray);
        //将任务推入队列
        $ret = $queue->push();
        //任务添加失败时输出错误码和错误信息
        if ($ret === false) {
            echo json_encode(array($queue->errno(), $queue->errmsg()));
            return;
        };
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


    function gen_user_refer_data(){
        $this->autoRender = false;
        if($_REQUEST['date']){
            $date = $_REQUEST['date'];
        }else{
            $date = date('Y-m-d');
        }
        $queue = new SaeTaskQueue('chaopeng');
        $queue->addTask("/cron/process_gen_refer_data/".$date);
        $ret = $queue->push();
        //任务添加失败时输出错误码和错误信息
        if ($ret === false){
            var_dump($queue->errno(), $queue->errmsg());
            $this->log('queue error '.$queue->errno().' queue error msg '.$queue->errmsg());
        }
        echo json_encode(array('success' => true,'date' => $date));
    }

    function process_gen_refer_data($date){
        $this->log('gen refer data date '.$date);
        $agency_uid = get_agency_uid();
        $this->gen_agency_refer_data($agency_uid,$date);
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

    private function gen_agency_refer_data($uids,$date){
        $this->loadModel('StatisticsReferData');
        $date = new DateTime($date);
        $start_date = $date->modify('first day of this month');
        $start_date = $start_date->format('Y-m-d');
        $this->StatisticsReferData->deleteAll(array('StatisticsReferData.start_date' => $start_date),false);
        $end_date = $date->modify('last day of this month');
        $end_date = $end_date->format('Y-m-d');
        if (strtotime(date('Y-m-d')) < strtotime($end_date)) {
            $end_date = date('Y-m-d');
        }
        $end_date = date('Y-m-d',strtotime($end_date.' + 1 day'));
        $saveData = array();
        foreach($uids as $uid){
            $saveData = array_merge($saveData,$this->gen_refer_statics_data($uid,$start_date,$end_date,true));
        }
        $this->StatisticsReferData->saveAll($saveData);
    }

    private function gen_refer_statics_data($uid,$start_date,$end_date,$call_back=false){
        $this->loadModel('Refer');
        $this->loadModel('Order');
        $saveData = array();
        $refers = $this->Refer->find('all',array('conditions' => array(
            'created >= ' => $start_date,
            'created < ' => $end_date,
            'first_order_done' => 1,
            'from' => $uid
        )));
        $refer_uids = Hash::extract($refers,'{n}.Refer.to');
        if(empty($refer_uids)){
            return $saveData;
        }
        $itemData = array();
        $itemData['recommend_user_count'] = count($refer_uids);
        $count_order_money = $this->Order->query('select sum(o.total_all_price) as ct from cake_orders o where o.status in (1,2,3,9) and o.creator in ('.implode(',',$refer_uids).') and Date(o.pay_time) >= "'.$start_date.'" and Date(o.pay_time) < "'.$end_date.'"');
        $all_money =  $count_order_money[0][0]['ct'];
        $itemData['sum_money'] = floatval($all_money)*100;
        $itemData['start_date'] = $start_date;
        $itemData['end_date'] = $end_date;
        $itemData['user_id'] = $uid;
        $saveData[] = $itemData;
        if($call_back){
            foreach($refer_uids as $uid){
                $saveData =array_merge($saveData,$this->gen_refer_statics_data($uid,$start_date,$end_date,false));
            }
        }
        return $saveData;
    }
}