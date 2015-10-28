<?php


class ShareAuthorityComponent extends Component {

    /**
     * @param $shareId
     * @param $uid
     * @return bool
     * 检查用户能否看到分享的订单数据
     */
    public function user_can_view_share_order_list($uid, $shareId) {
        $key = SHARE_ORDER_OPERATE_CACHE_KEY . '_' . $shareId;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $shareOperateSettingM = ClassRegistry::init('ShareOperateSetting');
            $operateSettings = $shareOperateSettingM->get_spec_type_authority(SHARE_ORDER_OPERATE_TYPE, $shareId, SHARE_OPERATE_SCOPE_TYPE);
            $operateUserCollection = Hash::extract($operateSettings, '{n}.ShareOperateSetting.user');
            Cache::write($key, json_encode($operateUserCollection));
        }
        $operateUserCollection = json_decode($cacheData, true);
        return in_array($uid, $operateUserCollection);
    }

    /**
     * @param $tagId
     * @param $uid
     * @param $shareId
     * @return bool
     * 检查用户能否看到分组订单数据
     */
    public function user_can_view_share_order_tag_list($tagId, $uid, $shareId) {
        $key = SHARE_ORDER_TAG_OPERATE_CACHE_KEY . '_' . $shareId;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $shareOperateSettingM = ClassRegistry::init('ShareOperateSetting');
            $operateSettings = $shareOperateSettingM->get_spec_data_authority($tagId, SHARE_TAG_ORDER_OPERATE_TYPE, $shareId, SHARE_OPERATE_SCOPE_TYPE);
            $operateUserCollection = Hash::extract($operateSettings, '{n}.OperateSetting.user');
            Cache::write($key, json_encode($operateUserCollection));
        }
        $operateUserCollection = json_decode($cacheData, true);
        return in_array($uid, $operateUserCollection);
    }
}