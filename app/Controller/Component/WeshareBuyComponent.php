<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 7/8/15
 * Time: 14:37
 */
class WeshareBuyComponent extends Component {
    //TODO 重构 weshare controller

    var $name = 'WeshareBuyComponent';

    public $components = array('Session', 'Weixin');

    public function __construct() {
        $this->Weshare = ClassRegistry::init('Weshare');
        $this->Order = ClassRegistry::init('Order');
        $this->User = ClassRegistry::init('User');
        $this->Oauthbind = ClassRegistry::init('Oauthbind');
        $this->WeshareProduct = ClassRegistry::init('WeshareProduct');
    }

    public function send_new_share_msg($weshareId) {
        $weshare = $this->Weshare->find('first', array(
            'conditions' => array(
                'id' => $weshareId
            )
        ));
        $wesahre_products = $this->WeshareProduct->find('all', array(
            'conditions' => array('weshare_id' => $weshareId)
        ));
        $weshare_product_names = Hash::extract($wesahre_products, '{n}.WeshareProduct.name');
        $sharer_user_info = $this->User->find('first', array(
            'conditions' => array(
                'id' => $weshare['Weshare']['creator']
            ),
            'fields' => array(
                'id', 'nickname'
            )
        ));
        $detail_url = WX_HOST.'/weshares/view/'.$weshareId;
        $sharer_name = $sharer_user_info['User']['nickname'];
        $product_name = implode(', ',$weshare_product_names);
        $title = '关注的'.$sharer_name.'发起了';
        $remark = '点击详情，赶快加入'.$sharer_name.'的分享！';
        $followers = $this->load_fans_buy_sharer($weshare['Weshare']['creator']);
        $openIds = $this->Oauthbind->findWxServiceBindsByUids($followers);
        foreach($openIds as $openId){
            $this->process_send_share_msg($openId,$title,$product_name,$detail_url,$sharer_name,$remark);
        }
    }

    public function load_fans_buy_sharer($sharerId) {
        $weshares = $this->Weshare->find('all', array(
            'conditions' => array(
                'creator' => $sharerId
            ),
            'fields' => array('id')
        ));
        $weshare_ids = Hash::extract($weshares, '{n}.Weshare.id');
        $order_status = array(ORDER_STATUS_PAID, ORDER_STATUS_SHIPPED);
        //query fans limit 1000
        $follower = $this->Order->find('all', array(
            'conditions' => array(
                'member_id' => $weshare_ids,
                'status' => $order_status,
                'type' => ORDER_TYPE_WESHARE_BUY
            ),
            'fields' => array('DISTINCT creator'),
            'limit' => 2000
        ));
        $follower_ids = Hash::extract($follower, '{n}.Order.creator');
        return $follower_ids;
    }

    public function process_send_share_msg($openId, $title, $productName, $detailUrl,$sharerName,$remark) {
        send_join_tuan_buy_msg(null,$title,$productName,$sharerName,$remark,$detailUrl,$openId);
    }
}