<?php

/**
 * Class ShareFavourableConfig
 * 分享打折处理
 */
class ShareFavourableConfig extends AppModel {

    public $useTable = false;

    /**
     * @var array
     *
     * ('discount' => 0)
     */
    var $favourableConfig = array(
//        71 => array(
//            'discount' => 0.8
//        )
        1216 => array(
            'discount' => 0.8
        )
    );

    public function get_favourable_config($share_id) {
        return $this->favourableConfig[$share_id];
    }


//    //检查是否有优惠
//$favourable_config = $this->ShareFavourableConfig->get_favourable_config($weshareId);
//$discountPrice = 0;
//if (!empty($favourable_config)) {
//    //优惠价格不计算邮费
//if ($favourable_config['discount']) {
//$discountPrice = $totalPrice - round($totalPrice * $favourable_config['discount']);
//}
//}
////产品价格的团长佣金
//$rebate_fee = $this->WeshareBuy->cal_proxy_rebate_fee($totalPrice - $discountPrice, $uid, $weshareId);
}