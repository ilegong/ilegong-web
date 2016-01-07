<?php

class PintuanHelperComponent extends Component {

    var $name = 'PintuanHelper';

    var $components = array('ShareUtil', 'Weixin');

    /**
     * @param $conf_id
     * @return mixed
     * 获取拼团报名人数
     */
    public function get_pintuan_count($conf_id) {
        $dataCollectM = ClassRegistry::init('DataCollect');
        $data = $dataCollectM->find('first', array(
            'conditions' => array(
                'data_id' => $conf_id,
                'data_type' => COLLECT_DATA_PINTUAN_TYPE
            )
        ));
        $count = $data['DataCollect']['plus_count'] + $data['DataCollect']['count'];
        return $count;
    }

    /**
     * @param $share_id
     * 更新拼团的数量
     */
    private function update_pintuan_count($share_id) {
        $dataCollectM = ClassRegistry::init('DataCollect');
        $pintuanConfigM = ClassRegistry::init('PintuanConfig');
        $detail = $pintuanConfigM->get_conf_data($share_id);
        $pid = $detail['pid'];
        $dataCollectM->update(array('count' => 'count + 1'), array('data_id' => $pid, 'data_type' => COLLECT_DATA_PINTUAN_TYPE));
    }

    public function handle_order_paid($order) {
        $order_id = $order['Order']['id'];
        $order_creator = $order['Order']['creator'];
        $order_group_id = $order['Order']['group_id'];
        $tag = $this->get_pintuan_tag($order_group_id);
        $tag_creator = $tag['PintuanTag']['creator'];
        if ($tag_creator == $order_creator) {
            //start new pin tuan
            $expire_date = date('Y-m-d H:i:s', strtotime('+1 day'));
            $this->update_pintuan_tag(array('expire_date' => "'" . $expire_date . "'", 'order_id' => $order_id, 'status' => PIN_TUAN_TAG_PROGRESS_STATUS), array('id' => $order_group_id));
        } else {
            //save relation
            $this->ShareUtil->save_relation($tag_creator, $order['Order']['creator']);
        }
        //update or save pin tuan record
        $this->update_pintuan_record($order_id, $order_creator, $order_group_id);
        //update pintuan tag status and save opt log
        $this->update_pintuan_tag_status($order_group_id, $tag['PintuanTag']['num'], $order_creator, $order['Order']['member_id']);
        $this->update_pintuan_count($order['Order']['member_id']);
    }


    public function update_pintuan_tag($update_attr, $cond_attr) {
        $PintuanTagM = ClassRegistry::init('PintuanTag');
        $PintuanTagM->updateAll($update_attr, $cond_attr);
    }

    public function get_pintuan_tag($tag_id) {
        $PintuanTagM = ClassRegistry::init('PintuanTag');
        $tag = $PintuanTagM->find('first', array(
            'conditions' => array(
                'id' => $tag_id
            )
        ));
        return $tag;
    }

    public function check_and_return_pintuan_tag($tag_id) {
        $pinTuanTagM = ClassRegistry::init('PintuanTag');
        $tag = $pinTuanTagM->find('first', array(
            'conditions' => array('id' => $tag_id)
        ));
        $now = new DateTime();
        $expire_time = new DateTime($tag['PintuanTag']['expire_date']);
        if ($now >= $expire_time && $tag['PintuanTag']['status'] == PIN_TUAN_TAG_PROGRESS_STATUS) {
            //update tag status
            $pinTuanTagM->updateAll(array('status' => PIN_TUAN_TAG_EXPIRE_STATUS), array('id' => $tag_id, 'status' => PIN_TUAN_TAG_PROGRESS_STATUS));
            $tag['PintuanTag']['status'] = PIN_TUAN_TAG_EXPIRE_STATUS;
            $this->send_pintuan_fail_msg($tag['PintuanTag']['share_id'], $tag['PintuanTag']['id'], $tag['PintuanTag']['creator']);
        }
        return $tag;
    }

    public function update_pintuan_record($order_id, $user_id, $tag_id) {
        $PintuanRecordM = ClassRegistry::init('PintuanRecord');
        $record = $PintuanRecordM->find('first', array(
            'conditions' => array(
                'user_id' => $user_id,
                'order_id' => $order_id,
                'tag_id' => $tag_id
            )
        ));
        if (empty($record)) {
            $record = array('user_id' => $user_id, 'tag_id' => $tag_id, 'order_id' => $order_id, 'created' => date('Y-m-d H:i:s'), 'status' => PIN_TUAN_RECORD_PAID_STATUS);
            $PintuanRecordM->save($record);
        } else {
            $PintuanRecordM->updateAll(array('status' => PIN_TUAN_RECORD_PAID_STATUS), array('user_id' => $user_id, 'order_id' => $order_id, 'tag_id' => $tag_id));
        }

    }

    public function get_user_pintuan_data($uid) {
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'creator' => $uid,
                'type' => ORDER_TYPE_PIN_TUAN,
                'not' => array('status' => array(ORDER_STATUS_WAITING_PAY))
            ),
            'fields' => array('id', 'member_id', 'group_id', 'status'),
            'order' => array('id DESC'),
            'limit' => 100
        ));
        if (empty($orders)) {
            return array();
        }
        $share_ids = array_unique(Hash::extract($orders, '{n}.Order.member_id'));
        $group_ids = array_unique(Hash::extract($orders, '{n}.Order.group_id'));
        $shares = $this->get_pintuan_weshares($share_ids);
        $tags = $this->get_pintuan_tags($group_ids);
        return array('orders' => $orders, 'shares' => $shares, 'tags' => $tags);
    }

    public function get_pintuan_weshares($share_ids) {
        $WeshareM = ClassRegistry::init('Weshare');
        $weshares = $WeshareM->find('all', array(
            'conditions' => array(
                'id' => $share_ids
            ),
            'fields' => array('id', 'title', 'images', 'created')
        ));
        foreach ($weshares as &$share_item) {
            $images = $share_item['Weshare']['images'];
            if (!empty($images)) {
                $share_item['Weshare']['images'] = explode('|', $images);
            }
        }
        $weshares = Hash::combine($weshares, '{n}.Weshare.id', '{n}');
        return $weshares;
    }

    public function get_pintuan_tags($group_ids) {
        $PintuanTagM = ClassRegistry::init('PintuanTag');
        $tags = $PintuanTagM->find('all', array(
            'conditions' => array(
                'id' => $group_ids
            )
        ));
        $tags = Hash::combine($tags, '{n}.PintuanTag.id', '{n}');
        return $tags;
    }

    /**
     * cron task update pin tuan tag status
     */
    public function cron_change_tag_status() {
        $PintuanTagM = ClassRegistry::init('PintuanTag');
        $expire_date = date('Y-m-d H:i:s', strtotime('-1 day'));
        $expire_pintuans = $PintuanTagM->find('all', array(
            'conditions' => array(
                'expire_date <= ' => $expire_date,
                'status' => PIN_TUAN_TAG_PROGRESS_STATUS
            )
        ));
        if (!empty($expire_pintuans)) {
            $expire_pintuan_ids = Hash::extract($expire_pintuans, '{n}.PintuanTag.id');
            $PintuanTagM->updateAll(array('status' => PIN_TUAN_TAG_EXPIRE_STATUS), array('id' => $expire_pintuan_ids));
            foreach ($expire_pintuans as $pintuan_tag) {
                $share_id = $pintuan_tag['PintuanTag']['share_id'];
                $user_id = $pintuan_tag['PintuanTag']['creator'];
                $tag_id = $pintuan_tag['PintuanTag']['id'];
                $this->send_pintuan_fail_msg($share_id, $tag_id, $user_id);
            }
        }
    }

    public function save_pintuan_record($record) {
        $PintuanRecordM = ClassRegistry::init('PintuanRecord');
        $PintuanRecordM->save($record);
    }

    public function get_tag_id_by_uid($uid) {
        $PintuanRecordM = ClassRegistry::init('PintuanRecord');
        $last_record = $PintuanRecordM->find('first', array(
            'conditions' => array(
                'user_id' => $uid,
                'status' => PIN_TUAN_RECORD_PAID_STATUS,
                'deleted' => DELETED_NO
            ),
            'order' => array('id DESC')
        ));
        if (!empty($last_record)) {
            return $last_record['PintuanRecord']['tag_id'];
        }
        return null;
    }

    public function get_pintuan_records($tag_id) {
        $PintuanRecordM = ClassRegistry::init('PintuanRecord');
        $UserM = ClassRegistry::init('User');
        $records = $PintuanRecordM->find('all', array(
            'conditions' => array(
                'tag_id' => $tag_id,
                'status' => PIN_TUAN_RECORD_PAID_STATUS
            )
        ));
        $uids = Hash::extract($records, '{n}.PintuanRecord.user_id');
        $users = $UserM->find('all', array(
            'conditions' => array(
                'id' => $uids,
            ),
            'fields' => array('id', 'nickname', 'image')
        ));
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        foreach ($records as &$record_item) {
            $current_uid = $record_item['PintuanRecord']['user_id'];
            $record_item['PintuanRecord']['user_info'] = $users[$current_uid];
        }
        return $records;
    }

    public function get_pintuan_tag_order_count($tag_id) {
        $PintuanRecordM = ClassRegistry::init('PintuanRecord');
        $record_count = $PintuanRecordM->find('count', array(
            'conditions' => array(
                'tag_id' => $tag_id,
                'status' => PIN_TUAN_RECORD_PAID_STATUS
            )
        ));
        return $record_count;
    }

    private function update_pintuan_tag_status($tag_id, $tag_num, $user_id, $share_id) {
        $record_count = $this->get_pintuan_tag_order_count($tag_id);
        if ($record_count >= $tag_num) {
            //save pin tuan success opt log
            //check is test user don't save
            if($share_id!=1941){
                $this->ShareUtil->save_pintuan_success_opt_log($user_id, $share_id, $tag_id);
            }
            $this->update_pintuan_tag(array('status' => PIN_TUAN_TAG_SUCCESS_STATUS), array('id' => $tag_id));
            $this->send_pintuan_success_msg($share_id, $tag_id, $user_id);
        }
    }

    public function send_pintuan_success_msg($share_id, $tag_id, $uid) {
        $oauthBindM = ClassRegistry::init('Oauthbind');
        $user_open_id = $oauthBindM->findWxServiceBindByUid($uid);
        if ($user_open_id) {
            $pintuanConfigM = ClassRegistry::init('PintuanConfig');
            $conf_data = $pintuanConfigM->get_conf_data($share_id);
            $good_name = $conf_data['share_title'];
            $conf_id = $conf_data['pid'];
            $title = '恭喜您，您参加的团购已拼团成功，我们会尽快为您安排发货。';
            $leader_name = $conf_data['sharer_nickname'];
            $remark = '点击查看详情!邀请朋友来一起参加!';
            $url = WX_HOST . '/pintuan/detail/' . $share_id . '/' . $conf_id . '?tag_id=' . $tag_id;
            $this->Weixin->send_pintuan_success_msg($user_open_id, $title, $good_name, $leader_name, $remark, $url);
        }
    }

    public function send_pintuan_fail_msg($share_id, $tag_id, $uid) {
        $oauthBindM = ClassRegistry::init('Oauthbind');
        $user_open_id = $oauthBindM->findWxServiceBindByUid($uid);
        if ($user_open_id) {
            $pintuanConfigM = ClassRegistry::init('PintuanConfig');
            $conf_data = $pintuanConfigM->get_conf_data($share_id);
            $good_name = $conf_data['share_title'];
            $title = '您好，您发起的' . $good_name . '拼团失败！';
            $fail_reason = '24小时内没有人参团';
            $remark = '点击查看详情，重新发起拼团！';
            $conf_id = $conf_data['pid'];
            $url = WX_HOST . '/pintuan/detail/' . $share_id . '/' . $conf_id . '?tag_id=' . $tag_id;
            $this->Weixin->send_pintuan_fail_msg($user_open_id, $title, $good_name, $fail_reason, $remark, $url);
        }
    }

}