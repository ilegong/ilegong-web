<?php

class SpreadSharerController extends AppController {

    var $uses = array('UserSubReason', 'User', 'SpreadConf');

    var $components = array('WeshareBuy');

    public function beforeFilter() {
        parent::beforeFilter();
    }

    public function scan_qrcode($sharer_id) {
        $uid = $this->currentUser['id'];
        $this->WeshareBuy->subscribe_sharer($sharer_id, $uid, SUB_SHARER_REASON_TYPE_FROM_SPREAD);
        if (user_subscribed_pys($uid) != WX_STATUS_SUBSCRIBED) {
            //没有关注朋友说
            $this->save_sub_reason($sharer_id, $uid);
            $sharer_conf = $this->SpreadConf->get_sharer_conf[$sharer_id];
            $this->redirect($sharer_conf['wx_introduce_url']);
            return;
        }
        //已经关注朋友说
        $this->redirect('/weshares/user_share_info/' . $sharer_id);
        return;
    }

    private function save_sub_reason($sharer_id, $uid) {
        $sharer_info = $this->User->find('first', array(
            'conditions' => array(
                'id' => $sharer_id
            ),
            'recursive' => 1,
            'fields' => array('id', 'nickname')
        ));
        $title = '我是' . $sharer_info['User']['nickname'] . '，感谢您关注我！';
        $this->UserSubReason->save(array('type' => SUB_SHARER_REASON_TYPE_FROM_SPREAD, 'url' => WX_HOST . '/weshares/user_share_info/' . $sharer_id, 'user_id' => $uid, 'title' => $title, 'data_id' => $sharer_id, 'created' => date('Y-m-d H:i:s')));
    }

}