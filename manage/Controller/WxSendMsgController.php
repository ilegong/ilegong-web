<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 5/15/15
 * Time: 20:06
 */

class WxSendMsgController extends AppController{

    var $name = 'WxSendMsg';

    var $uses = array('Order','Oauthbind');

    public function admin_to_send_wx_msg(){
        $this->getZitiOrderUserIds();
    }

    public function admin_send_wx_msg(){
        $this->autoRender=false;

    }

    private function getZitiOrderUserIds(){
        $now = date('Y-m-d');
        $startDate = date('Y-m-d',strtotime('-7 day'.$now));
        $users = $this->Order->query('SELECT DISTINCT(creator) FROM cake_orders WHERE ship_mark = "ziti" AND created > \''.$startDate.'\' AND created < \''.$now.'\'');
        $userIds = Hash::extract($users,'{n}.cake_orders.creator');
        $openIds = $this->Oauthbind->find('all',array(
            'conditions' => array(
                'user_id' => $userIds,
            ),
            'fields' => array(
                'oauth_openid'
            )
        ));
        $openIds = Hash::extract($openIds,'{n}.Oauthbind.oauth_openid');
        //loop send template msg
    }


}