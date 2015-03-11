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

    public function download_photo_from_wx() {
        $this->autoRender=false;
        $start = $_REQUEST['start'];
        $limit = $_REQUEST['limit'];
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $wxUsers = $oauthBindModel->find('all', array(
            'limit' => $limit,
            'offset' => $start
        ));
        $count = $this->process_download_wx_photo($wxUsers);
        echo $count;
    }

    public function send_ship_info(){
        $this->autoRender=false;
        $this->loadModel('Order');
        $this->loadModel('Cart');
        $start_date = date("Y-m-d",strtotime("-7 day"));
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
        if (!empty($oathBinds)) {
            foreach ($oathBinds as $item) {
                $bindId = $item['Oauthbind']['id'];
                $user_id = $item['Oauthbind']['user_id'];
                $open_id = $item['Oauthbind']['oauth_openid'];
                $extra_param =  $item['Oauthbind']['extra_param'];
                if(empty($extra_param)){
                    $extra_param = array();
                }else{
                    $extra_param = json_decode($extra_param,true);
                }
                $wx_user = get_user_info_from_wx($open_id);
                $this->log('download wx user info '.json_encode($wx_user));
                $photo = $wx_user['headimgurl'];
                if(!empty($photo)){
                    $download_url = download_photo_from_wx($photo);
                    if (!empty($download_url)) {
                        $this->User->id = $user_id;
                        if ($this->User->saveField('image', $download_url)) {
                            $resultCount++;
                            //todo add flag to oauthbind ?
                            $extra_param['is_downLoad_photo']=true;
                            $this->Oauthbind->id = $bindId;
                            $this->Oauthbind->saveField('extra_param',json_encode($extra_param));
                        }
                    }
                }
            }
        }
        return $resultCount;
    }
}