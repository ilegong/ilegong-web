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
        $this->loadModel('Cart');
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
                $contents = $this->gethtml($from_url,$url);
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

    function gethtml($from_url,$url){
        $ch = curl_init();
        //设置 来路，这个很重要 ，表示这个访问 是从 $form_url 这个链接点过去的。
        curl_setopt($ch,CURLOPT_REFERER,$from_url);
        //获取 的url地址
        curl_setopt ($ch,CURLOPT_URL,$url);
        //设置  返回原生的（Raw）输出
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        //发送POST请求 CURLOPT_CUSTOMREQUEST
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        //模拟浏览器发送报文 ，这里模拟 IE6 浏览器访问
        curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)");
        $res = curl_exec($ch);
        curl_close ($ch);
        return $res;
    }
}