<?php

class PintuanHelperComponent extends Component {

    var $name = 'PintuanHelper';

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
        }
        //update or save pin tuan record
        $this->update_pintuan_record($order_id, $order_creator, $order_group_id);
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

    }

}