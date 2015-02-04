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

    public function send_ship_info(){
        $this->autoRender=false;
        $this->loadModel('Order');
        $start_date = date("Y-m-d H:i:s",strtotime("-7 day"));
        $date = date('m/d/Y h:i:s a', time());
        $orders = $this->Order->find('all',array(
            'conditions'=>array(
                'created >='=>$start_date,
                'status'=>ORDER_STATUS_SHIPPED,
                'published'=>1,
                'deleted'=>0,
                'not'=>array(
                    'ship_code'=>null,
                    'ship_type'=>null,
                )
            )
        ));
        $ship_infos = ShipAddress::get_all_ship_info();
        if (function_exists('curl_init') == 1) {
            $this->log("Curl can init...");
            $curl = curl_init();
        }else{
            $this->log("Curl can't init...");
        }
        foreach($orders as $order){
            $ship_type = $order['Order']['ship_type'];
            $consignee_address=$order['Order']['consignee_address'];
            $ship_code=$order['Order']['ship_code'];
            if(!preg_match("/([\x81-\xfe][\x40-\xfe])/", $ship_code, $match)&&!empty($ship_code)&&!empty($ship_type)&&!mb_strpos($consignee_address,'自提')){
                $com = key($ship_infos[$order['Order']['ship_type']]);
                //http://www.kuaidi100.com/query?id=1&type=quanfengkuaidi&postid=710023594269&valicode=&temp=0.018777450546622276
                $url = 'http://www.kuaidi100.com/query?id=1&type='.$com.'&postid='.$order['Order']['ship_code'].'&valicode=&temp='.(mt_rand()/mt_getrandmax());
                curl_setopt_array(
                    $curl,
                    array(
                        CURLOPT_URL=>$url,
                        CURLOPT_HEADER=>0,
                        CURLOPT_RETURNTRANSFER=>1,
                        CURLOPT_TIMEOUT=>5
                    )
                );
                $contents = curl_exec($curl);
                $contentObject = json_decode($contents,true);
                $orderId = $order['Order']['id'];
                //get ship info
                if(count($contentObject['data'])>0){
                    $currentShipInfo = $contentObject['data'][0];
                    $shipInfo = $currentShipInfo['time'].' '.$currentShipInfo['context'];
                    if(!$this->Weixin->send_order_ship_info_msg($order['Order']['creator'],$shipInfo,$orderId)){
                        $this->log('push ship info '.$orderId.' wx send error on date '.$date);
                    }
                }else{
                    $this->log('push ship info '.$orderId.' can not fetch ship info on date '.$date);
                }
            }
        }
        curl_close($curl);
        echo 'success';
    }
}