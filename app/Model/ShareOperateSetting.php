<?php

class ShareOperateSetting extends AppModel {

    /**
     * @param $data_id
     * @param $data_type
     * @param $scope_id
     * @param $scope_type
     * @return array
     * 获取某个操作的特定权限
     */
    public function get_spec_data_authority($data_id, $data_type, $scope_id, $scope_type) {
        $shareOperateSetting = $this->find('first', array(
            'conditions' => array(
                'data_id' => $data_id,
                'data_type' => $data_type,
                'scope_id' => $scope_id,
                'scope_type' => $scope_type,
                'deleted' => DELETED_NO
            )
        ));
        return $shareOperateSetting;
    }

    /**
     * @param $data_type
     * @param $scope_id
     * @param $scope_type
     * @return array
     * 获取限定范围内的权限
     */
    public function get_spec_type_authority($data_type, $scope_id, $scope_type) {
        $shareOperateSettings = $this->find('first', array(
            'conditions' => array(
                'data_type' => $data_type,
                'scope_id' => $scope_id,
                'scope_type' => $scope_type,
                'deleted' => DELETED_NO
            )
        ));
        return $shareOperateSettings;
    }


}