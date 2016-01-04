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
     * 初始化用户等级数据
     */
    public function admin_init_sharer_level() {
        $this->autoRender = false;
        $sharer_ids = $this->Weshare->find('all', array(
            'fields' => array('DISTINCT creator'),
            'limit' => 1500
        ));
        $userLevelM = ClassRegistry::init('UserLevel');
        $sharer_ids = Hash::extract($sharer_ids, '{n}.Weshare.creator');
        $datas = array();
        $date = date('Y-m-d H:i:s');
        foreach ($sharer_ids as $sharer_id) {
            $data_item = array(
                'data_value' => 0,
                'data_id' => $sharer_id,
                'created' => $date,
                'updated' => $date,
                'deleted' => 0,
                'type' => 0
            );
            $datas[] = $data_item;
        }
        $userLevelM->saveAll($datas);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $refer_share_id
     * @return array
     * 获取可能授权的用户
     */
    private function load_refer_share_authority_user($refer_share_id) {
        if(!empty($refer_share_id)){
            $shareOperateSettings = $this->ShareOperateSetting->find('all', array(
                'conditions' => array(
                    'scope_id' => $refer_share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE
                ),
                'order' => array('user DESC')
            ));
            $history_operate_setting_uids = Hash::extract($shareOperateSettings, '{n}.ShareOperateSetting.user');
            return $history_operate_setting_uids;
        }
        return array();
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
            $history_operate_setting_uids = $this->load_refer_share_authority_user($shareInfo['Weshare']['refer_share_id']);
            $shareOperateUserIds = Hash::extract($shareOperateSettings, '{n}.ShareOperateSetting.user');
            $shareOperateUserIds[] = $share_creator;
            $shareOperateUserIds = array_merge($shareOperateUserIds, $history_operate_setting_uids);
            $shareOperateUserIds = array_unique($shareOperateUserIds);
            $usersData = $this->User->find('all', array(
                'conditions' => array(
                    'id' => $shareOperateUserIds
                ),
                'fields' => array('nickname', 'id', 'mobilephone')
            ));
            $usersData = Hash::combine($usersData, '{n}.User.id', '{n}.User');
            $productTags = Hash::combine($productTags, '{n}.WeshareProductTag.id', '{n}.WeshareProductTag');
            $this->set('$history_operate_setting_uids', $history_operate_setting_uids);
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
        $user_id = $_REQUEST['user_id'];
        $share_id = $_REQUEST['share_id'];
        $tag_id = $_REQUEST['tag_id'];
        $this->save_share_operate($user_id, $share_id);
        $this->save_share_tag_operate($user_id, $tag_id, $share_id);
        $this->redirect(array('action' => 'admin_share_operate_set_view', '?' => array('share_id' => $share_id)));
    }

    public function admin_save_share_edit_operate_setting() {
        $user_id = $_REQUEST['user_id'];
        $share_id = $_REQUEST['share_id'];
        $this->save_share_operate_setting($user_id, $share_id, SHARE_INFO_OPERATE_TYPE);
        $this->redirect(array('action' => 'admin_share_operate_set_view', '?' => array('share_id' => $share_id)));
    }

    public function admin_save_share_manage_operate_setting() {
        $user_id = $_REQUEST['user_id'];
        $share_id = $_REQUEST['share_id'];
        $this->save_share_operate_setting($user_id, $share_id, SHARE_MANAGE_OPERATE_TYPE);
        $this->redirect(array('action' => 'admin_share_operate_set_view', '?' => array('share_id' => $share_id)));
    }

    //保存编辑分享的权限
    private function save_share_operate_setting($user_id, $share_id, $type) {
        if (!empty($user_id) && !empty($share_id) && !empty($type)) {
            $oldData = $this->ShareOperateSetting->find('first', array(
                'conditions' => array(
                    'user' => $user_id,
                    'data_type' => $type,
                    'data_id' => $share_id,
                    'scope_id' => $share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE
                )
            ));
            if (empty($oldData)) {
                $saveData = array('user' => $user_id,
                    'data_type' => $type,
                    'data_id' => $share_id,
                    'scope_id' => $share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE);
                $this->ShareOperateSetting->save($saveData);
                if ($type == SHARE_INFO_OPERATE_TYPE) {
                    Cache::write(SHARE_INFO_OPERATE_CACHE_KEY . '_' . $share_id, '');
                }
                if ($type == SHARE_MANAGE_OPERATE_TYPE) {
                    Cache::write(SHARE_MANAGE_OPERATE_CACHE_KEY . '_' . $share_id, '');
                }
            }
        }
    }


    private function save_share_tag_operate($user_id, $tag_id, $share_id) {
        if (!empty($user_id) && !empty($share_id) && !empty($tag_id)) {
            $oldData = $this->ShareOperateSetting->find('first', array(
                'conditions' => array(
                    'user' => $user_id,
                    'data_type' => SHARE_TAG_ORDER_OPERATE_TYPE,
                    'data_id' => $tag_id,
                    'scope_id' => $share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE
                )
            ));
            if (empty($oldData)) {
                $saveData = array('user' => $user_id,
                    'data_type' => SHARE_TAG_ORDER_OPERATE_TYPE,
                    'data_id' => $tag_id,
                    'scope_id' => $share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE);
                $this->ShareOperateSetting->save($saveData);
                Cache::write(SHARE_ORDER_TAG_OPERATE_CACHE_KEY . '_' . $share_id, '');
                Cache::write(SHARE_ORDER_TAG_OPERATE_CACHE_KEY . '_' . $share_id . '_' . $tag_id, '');
            }
        }
    }

    private function save_share_operate($user_id, $share_id) {
        if (!empty($user_id) && !empty($share_id)) {
            $oldData = $this->ShareOperateSetting->find('first', array(
                'conditions' => array(
                    'user' => $user_id,
                    'data_type' => SHARE_ORDER_OPERATE_TYPE,
                    'data_id' => $share_id,
                    'scope_id' => $share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE
                )
            ));
            if (empty($oldData)) {
                $saveData = array('user' => $user_id,
                    'data_type' => SHARE_ORDER_OPERATE_TYPE,
                    'data_id' => $share_id,
                    'scope_id' => $share_id,
                    'scope_type' => SHARE_OPERATE_SCOPE_TYPE);
                $this->ShareOperateSetting->save($saveData);
                Cache::write(SHARE_ORDER_OPERATE_CACHE_KEY . '_' . $share_id, '');
            }
        }
    }


    /**
     * @param $id
     * @param $share_id
     * @param $data_id
     * 删除分享权限
     */
    public function admin_delete_share_operate_setting($id, $share_id, $data_id) {
        $data = $this->ShareOperateSetting->find('first', array('conditions' => array('id' => $id)));
        if (!empty($data)) {
            $this->ShareOperateSetting->delete($id);
            if ($data['ShareOperateSetting']['data_type'] == SHARE_ORDER_OPERATE_TYPE) {
                Cache::write(SHARE_ORDER_OPERATE_CACHE_KEY . '_' . $share_id, '');
            } else {
                Cache::write(SHARE_ORDER_TAG_OPERATE_CACHE_KEY . '_' . $share_id, '');
                Cache::write(SHARE_ORDER_TAG_OPERATE_CACHE_KEY . '_' . $share_id . '_' . $data_id, '');
            }
        }
        $this->redirect(array('action' => 'admin_share_operate_set_view', '?' => array('share_id' => $share_id)));
    }

}