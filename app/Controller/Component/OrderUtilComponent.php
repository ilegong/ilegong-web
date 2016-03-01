<?php

class OrderUtilComponent extends Component
{

    var $name = 'OrderUtil';

    public $components = array('Weixin');

    //
    public function on_order_created($uid, $share_id, $order_id)
    {
        $this->clearCacheForUser($uid);
        // 计算团长返利
        // 用户发送模板消息
        // 积分

    }

    private function clearCacheForUser($uid){
        $key = USER_SHARE_INFO_CACHE_KEY . '_' . $uid;
        Cache::write($key, '');
    }
}