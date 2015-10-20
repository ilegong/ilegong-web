<?php

/**
 * Class WeixinUtilComponent
 * 微信操作的工具类
 */
class WeixinUtilComponent extends Component {


    public function save_user_sub_reason($type, $url, $uid, $title, $data_id) {
        $UserSubReasonM = ClassRegistry::init('UserSubReason');
        $UserSubReasonM->save(array('type' => $type, 'url' => $url, 'user_id' => $uid, 'title' => $title, 'data_id' => $data_id, 'created' => date('Y-m-d H:i:s')));
    }

    /**
     * @param $user_id
     * @return mixed
     * query user sub reason
     */
    public function get_user_sub_reason($user_id) {
        $UserSubReasonM = ClassRegistry::init('UserSubReason');
        $subReason = $UserSubReasonM->find('first', array(
            'conditions' => array(
                'user_id' => $user_id,
                'used' => 0,
                'DATE(created)' => date('Y-m-d')
            ),
            'order' => array('id DESC')
        ));
        return $subReason;
    }

}