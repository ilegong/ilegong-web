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
        $cron_ids = Hash::extract($cron,'{n}.Cron.id');
        $this->loadModel('WxOauth');
        foreach ($cron as $rn){
            $this->WxOauth->send_kefu($rn['Cron']['content']);
        }
        $expires_time = time();
        $this->Cron->deleteAll(array('OR' => array(
            array('id' => $cron_ids),
            array('expires < ' => $expires_time)
        )));
    }
}