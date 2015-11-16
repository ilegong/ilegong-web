<?php


class ShareAuthorityComponent extends Component {


    /**
     * @param $share_id
     * @return array|mixed
     * 获取到授权用户的ID
     */
    public function get_share_manage_auth_users($share_id) {
        $key = SHARE_MANAGE_OPERATE_CACHE_KEY . '_' . $share_id;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $operateUserCollection = $this->get_spec_type_authority_users(SHARE_MANAGE_OPERATE_TYPE, $share_id);
            Cache::write($key, json_encode($operateUserCollection));
            return $operateUserCollection;
        }
        return json_decode($cacheData, true);
    }

    /**
     * @param $share_id
     * @return array|mixed
     */
    public function get_share_manage_auth_user_open_ids($share_id) {
        $key = SHARE_MANAGE_USER_OPEN_ID_DATA_CACHE_KEY . '_' . $share_id;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $uids = $this->get_share_manage_auth_users($share_id);
            $oauthBindM = ClassRegistry::init('Oauthbind');
            $uid_openid_map = $oauthBindM->findWxServiceBindMapsByUids($uids);
            Cache::write($key, json_encode($uid_openid_map));
            return $uid_openid_map;
        }
        return json_decode($cacheData, true);
    }

    /**
     * @param $uid
     * @param $shareId
     * @return bool
     * 检查用户能否编辑分享信息
     */
    public function user_can_edit_share_info($uid, $shareId) {
        $key = SHARE_INFO_OPERATE_CACHE_KEY . '_' . $shareId;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $operateUserCollection = $this->get_spec_type_authority_users(SHARE_INFO_OPERATE_TYPE, $shareId);
            Cache::write($key, json_encode($operateUserCollection));
        } else {
            $operateUserCollection = json_decode($cacheData, true);
        }
        return in_array($uid, $operateUserCollection);
    }

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
            $operateUserCollection = $this->get_spec_type_authority_users(SHARE_ORDER_OPERATE_TYPE, $shareId);
            Cache::write($key, json_encode($operateUserCollection));
        } else {
            $operateUserCollection = json_decode($cacheData, true);
        }
        return in_array($uid, $operateUserCollection);
    }

    private function get_spec_type_authority_users($type, $shareId) {
        $shareOperateSettingM = ClassRegistry::init('ShareOperateSetting');
        $operateSettings = $shareOperateSettingM->get_spec_type_authority($type, $shareId, SHARE_OPERATE_SCOPE_TYPE);
        $operateUserCollection = Hash::extract($operateSettings, '{n}.ShareOperateSetting.user');
        return $operateUserCollection;
    }

    /**
     * @param $tagId
     * @param $uid
     * @param $shareId
     * @return bool
     * 检查用户能否看到分组订单数据
     */
    public function user_can_view_share_order_tag_list($tagId, $uid, $shareId) {
        $key = SHARE_ORDER_TAG_OPERATE_CACHE_KEY . '_' . $shareId . '_' . $tagId;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $shareOperateSettingM = ClassRegistry::init('ShareOperateSetting');
            $operateSettings = $shareOperateSettingM->get_spec_data_authority($tagId, SHARE_TAG_ORDER_OPERATE_TYPE, $shareId, SHARE_OPERATE_SCOPE_TYPE);
            $operateUserCollection = Hash::extract($operateSettings, '{n}.OperateSetting.user');
            Cache::write($key, json_encode($operateUserCollection));
        } else {
            $operateUserCollection = json_decode($cacheData, true);
        }
        return in_array($uid, $operateUserCollection);
    }

    /**
     * @param $uid
     * @param $shareId
     * @return array
     * 获取用户可以看到的分组列表
     */
    public function get_user_can_view_order_tags($uid, $shareId) {
        $key = SHARE_ORDER_TAG_OPERATE_CACHE_KEY . '_' . $shareId;
        $cacheData = Cache::read($key);
        if (empty($cacheData)) {
            $shareOperateSettingM = ClassRegistry::init('ShareOperateSetting');
            $operateSettings = $shareOperateSettingM->get_user_spec_type_authority($uid, SHARE_TAG_ORDER_OPERATE_TYPE, $shareId, SHARE_OPERATE_SCOPE_TYPE);
            $operateTagCollection = Hash::extract($operateSettings, '{n}.ShareOperateSetting.data_id');
            Cache::write($key, json_encode($operateTagCollection));
        } else {
            $operateTagCollection = json_decode($cacheData, true);
        }
        return $operateTagCollection;
    }
}