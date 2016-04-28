<?php

class WeshareFaqComponent extends Component
{


    var $components = ['WeshareBuy', 'SharePush', 'ShareFaqUtil', 'ShareAuthority'];

    public function create_faq($faq_data)
    {
        $shareFaqM = ClassRegistry::init('ShareFaq');
        $faq_data = $shareFaqM->save($faq_data);
        $faq_data['success'] = true;
        $sender = $faq_data['sender'];
        $receiver = $faq_data['receiver'];
        $msg = $faq_data['msg'];
        $share_id = $faq_data['share_id'];
        $weshareInfo = $this->WeshareBuy->get_weshare_info($share_id);
        $share_title = $weshareInfo['title'];
        $this->ShareFaqUtil->send_notify_template_msg($sender, $receiver, $msg, $share_id, $share_title);
        //check receive msg user is share creator
        //发送给团长需要发送给助理
        try {
            $this->SharePush->push_faq_msg($faq_data['ShareFaq']);
        } catch (Exception $e) {
            $this->log('push faq msg error data ' . json_encode($faq_data['ShareFaq']) . 'msg ' . $e->getMessage());
        }
        return $faq_data;
    }
    
}