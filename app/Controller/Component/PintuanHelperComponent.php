<?php

class PintuanHelperComponent extends Component {

    var $name = 'PintuanHelper';

    var $components = array('ShareUtil');

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
        //pintuan tag status
        $this->update_pintuan_tag_status($order_group_id, $tag['PintuanTag']['num']);
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


    public function save_pintuan_record($record) {
        $PintuanRecordM = ClassRegistry::init('PintuanRecord');
        $PintuanRecordM->save($record);
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

    private function update_pintuan_tag_status($tag_id, $tag_num) {
        $record_count = $this->get_pintuan_tag_order_count($tag_id);
        if ($record_count >= $tag_num) {
            $this->update_pintuan_tag(array('status' => PIN_TUAN_TAG_SUCCESS_STATUS), array('id' => $tag_id));
        }
    }

}