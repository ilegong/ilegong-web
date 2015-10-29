<?php

class ShareToolController extends AppController {


    var $name = 'share_tool';

    var $uses = array('WeshareProduct', 'Weshare', 'WeshareAddress', 'User',
        'WeshareShipSetting', 'OfflineStore', 'Oauthbind', 'SharerShipOption', 'ShareOperateSetting', 'WeshareProductTag');

    var $operateDataTypeNameMap = array(SHARE_ORDER_OPERATE_TYPE => '查看订单权限', SHARE_TAG_ORDER_OPERATE_TYPE => '查看分组订单权限');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'bootstrap_layout';
    }


    /**
     * 跳转到一个分享权限设置的页面
     */
    public function admin_share_operate_set_view() {
        $shareId = $_REQUEST['share_id'];
        if (!empty($shareId)) {
            $shareInfo = $this->Weshare->find('first', array(
                'conditions' => array(
                    'id' => $shareId
                )
            ));
            $share_creator = $shareInfo['Weshare']['creator'];
            $productTags = $this->WeshareProductTag->find('all', array(
                'conditions' => array(
                    'user_id' => $shareInfo['Weshare']['creator'],
                    'deleted' => DELETED_NO
                )
            ));
            $shareOperateSettings = $this->ShareOperateSetting->find('all', array(
                'conditions' => array(
                    'scope_id' => $shareId,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE
                ),
                'order' => array('user DESC')
            ));
            $shareOperateUserIds = Hash::extract($shareOperateSettings, '{n}.ShareOperateSetting.user');
            $shareOperateUserIds[] = $share_creator;
            $shareOperateUserIds = array_unique($shareOperateUserIds);
            $usersData = $this->User->find('all', array(
                'conditions' => array(
                    'id' => $shareOperateUserIds
                ),
                'fields' => array('nickname', 'id', 'mobilephone')
            ));
            $usersData = Hash::combine($usersData, '{n}.User.id', '{n}.User');
            $this->set('operate_settings', $shareOperateSettings);
            $this->set('user_data', $usersData);
            $this->set('operate_name_map', $this->operateDataTypeNameMap);
            $this->set('share_info', $shareInfo);
            $this->set('product_tags', $productTags);
        }
    }

    /**
     * 保存分享权限设置
     */
    public function admin_save_share_operate_setting() {

    }

    /**
     * 删除分享权限
     */
    public function admin_delete_share_operate_setting() {

    }

}