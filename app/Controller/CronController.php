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

    public $components = array('Weixin');

    function send_coupon_timeout_message()
    {
        $this->autoRender = false;
        $result = array();

        $couponItems = $this->CouponItem->find_24hours_timeout_coupons();
        foreach ($couponItems as $couponItem) {
            $id = $couponItem['CouponItem']['id'];
            $user_id = $couponItem['CouponItem']['bind_user'];
            $coupon_name = $couponItem['Coupon']['name'];
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

    public function send_tuan_buy_create_msg(){
        $this->autoRender = false;
        $tuan_buy_id = $_REQUEST['tuan_buy_id'];
        $msg_element = get_tuan_msg_element($tuan_buy_id);
        if(!empty($msg_element)){
            $consign_time = $msg_element['consign_time'];
            $uids = $msg_element['uids'];
            $tuan_name = $msg_element['tuan_name'];
            $title = '您参加的'.$tuan_name.',发起了一个新的团购。';
            $product_name = $msg_element['product_name'];
            $product_name = $product_name.', '.$consign_time.'发货';
            $tuan_leader = $msg_element['tuan_leader'];
            $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$tuan_buy_id;
            $remark = '点击详情，赶快和小伙伴一起团起来！';
            foreach($uids as $uid){
                //$this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
                //TODO log fail user id
            }
            $result = array('success' => true,'msg' => '推送模板消息成功');
        }else{
            $result = array('success' => false,'msg' => '该团购不存在,亲先创建..');
        }
        echo json_encode($result);
    }

    public function send_tuan_buy_tip_msg(){
        $tuanBuyingM = ClassRegistry::init('TuanBuying');
        $result = array();
        $tb_ids = $tuanBuyingM->query('SELECT id FROM cake_tuan_buyings where sold_num >= target_num and status=0');
        $tb_ids = Hash::extract($tb_ids,'{n}.cake_tuan_buyings.id');
        foreach($tb_ids as $tb_id){
            $msg_element = get_tuan_msg_element($tb_id);
            if(!empty($msg_element)){
                $uids = $msg_element['uids'];
                $tuan_name = $msg_element['tuan_name'];
                $target_num = intval($msg_element['target_num']);
                $sold_num = intval($msg_element['sold_num']);
                $product_name = $msg_element['product_name'];
                $title = '您参加的'.$tuan_name.',目标'.$target_num.'份，现在'.$sold_num.'，还差'.($target_num-$sold_num).'份，加油，加油!';
                $tuan_leader = $msg_element['tuan_leader'];
                $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$tb_id;
                $remark = '点击详情，赶紧邀请小伙伴们加入，享受成团优惠价！';
                foreach($uids as $uid){
                    $this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
                    //TODO log fail user id
                }
                $result = array('success' => true,'msg' => '推送模板消息成功');
            }else{
                $result = array('success' => false,'msg' => '推送模板消息失败');
            }
        }
        return $result;
    }

    public function send_tuan_buy_fail_msg(){
        $this->autoRender = false;
        $tuan_buy_id = $_REQUEST['tuan_buy_id'];
        $msg_element = get_tuan_msg_element($tuan_buy_id);
        if(!empty($msg_element)){
            $uids = $msg_element['uids'];
            $tuan_name = $msg_element['tuan_name'];
            $target_num = intval($msg_element['target_num']);
            $sold_num = intval($msg_element['sold_num']);
            $product_name = $msg_element['product_name'];
            $title = '呜呜,您参加的'.$tuan_name.'团购份数没有达到,目标'.$target_num.'份，现在只有'.$sold_num.'。';
            $tuan_leader = $msg_element['tuan_leader'];
            $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$tuan_buy_id;
            $remark = '我们将联系您退款或者延期，请留意后续消息！';
            foreach($uids as $uid){
                $this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url);
                //TODO log fail user id
            }
            $result = array('success' => true,'msg' => '推送模板消息成功');
        }else{
            $result =  array('success' => false,'msg' => '该团购不存在,亲先创建..');
        }
        echo json_encode($result);
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
}