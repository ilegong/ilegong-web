<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 4/9/15
 * Time: 11:23
 */

class TuanBuyingsController extends AppController{

    var $name = 'TuanBuyings';

    var $uses = array('TuanTeam','TuanBuying','Order','Cart','TuanBuyingMessages','TuanProduct','TuanMsg', 'OfflineStore');
    public $components = array('Weixin');

    var $msg_uid = array();

    public function admin_api_tuan_buying_due(){
        $tuanBuyingId = $_REQUEST['id'];
        $this->log('update tuan buying ' + $tuanBuyingId + ' status to ' + 1);
        $this->TuanBuying->updateAll(array('status' => 1), array('id' => $tuanBuyingId));
    }
    public function admin_api_tuan_buying_canceled(){
        $tuanBuyingId = $_REQUEST['id'];
        $this->log('update tuan buying ' + $tuanBuyingId + ' status to ' + 2);
        $this->TuanBuying->updateAll(array('status' => 2), array('id' => $tuanBuyingId));
    }
    public function admin_api_tuan_buying_finished(){
        $tuanBuyingId = $_REQUEST['id'];
        $this->log('update tuan buying ' + $tuanBuyingId + ' status to ' + 11);
        $this->TuanBuying->updateAll(array('status' => 11), array('id' => $tuanBuyingId));
    }
    public function admin_api_tuan_buying_refunded(){
        $tuanBuyingId = $_REQUEST['id'];
        $this->log('update tuan buying ' + $tuanBuyingId + ' status to ' + 21);
        $this->TuanBuying->updateAll(array('status' => 21), array('id' => $tuanBuyingId));
    }
    public function admin_api_tuan_buyings($team_id){
        $this->autoRender = false;

        $tuan_buyings = $this->TuanBuying->find('all', array(
            'conditions' => array(
                'tuan_id' => $team_id
            )
        ));

        $product_ids = array_unique(Hash::extract($tuan_buyings, "{n}.TuanBuying.pid"));
        $tuan_products = $this->TuanProduct->find('all', array(
            'conditions' => array(
                'product_id' => $product_ids
            )
        ));
        $tuan_products = Hash::combine($tuan_products, "{n}.TuanProduct.product_id", "{n}.TuanProduct");
        $this->log($tuan_products);

        foreach($tuan_buyings as &$tuan_buying){
            $tuan_buying['Product'] = $tuan_products[$tuan_buying['TuanBuying']['pid']];
        }
        echo json_encode($tuan_buyings);
    }

    /**
     * show all tuan_buyings
     */
    public function admin_index(){
        $expired = isset($_REQUEST['expired']) ? $_REQUEST['status_type'] : false;
        $status_type = isset($_REQUEST['status_type']) ? $_REQUEST['status_type'] : -1;
        $team_id = isset($_REQUEST['team_id']) ? $_REQUEST['team_id'] : -1;
        $product_id = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : -1;
        $tuan_status = isset($_REQUEST['tuan_status']) ? $_REQUEST['tuan_status'] : -1;
        $cons_type = isset($_REQUEST['cons_type'])? $_REQUEST['cons_type']:-1;

        $con = array();
        if($expired){
            $con['end_time <= '] = date("Y-m-d H:i:s");
            $con['status'] = 0;
        }
        else{
            if($team_id != -1){
                $con['tuan_id']=$team_id;
            }
            if($product_id != -1){
                $con['pid'] = $product_id;
            }
            if($cons_type!=-1){
                $con['consignment_type'] = $cons_type;
            }
            if($status_type == -1){
                if($tuan_status == -1){
                    $con['status'] = array(0, 1, 2);
                }else{
                    $con['status'] = $tuan_status;
                }
            }
            else{
                $con['status'] = $status_type;
            }
        }

        $tuan_buyings = $this->TuanBuying->find('all',array(
            'conditions' => $con
        ));

        $tuan_ids = Hash::extract($tuan_buyings,'{n}.TuanBuying.tuan_id');
        $tuan_teams = $this->TuanTeam->find('all', array('conditions' => array('id' => $tuan_ids)));
        $tuan_teams = Hash::combine($tuan_teams, '{n}.TuanTeam.id', '{n}.TuanTeam');
        $tuan_products = getTuanProducts();
        $tuan_products = Hash::combine($tuan_products,'{n}.TuanProduct.product_id','{n}.TuanProduct');

        if(!empty($tuan_buyings)){
            $tuan_buying_id_strs = join(',', Hash::extract($tuan_buyings, "{n}.TuanBuying.id"));
            $paid_orders_count = $this->Order->query('select member_id as member_id, count(member_id) as c from cake_orders WHERE STATUS = 1 and type = 5 and member_id in ('.$tuan_buying_id_strs.') group by member_id;');
            $paid_orders_count = Hash::combine($paid_orders_count, '{n}.cake_orders.member_id', '{n}.0.c');
        }

        foreach($tuan_buyings as &$tuan_buying){
            $tuanBuying = $tuan_buying['TuanBuying'];
            $tb_id = $tuanBuying['id'];
            $tuan_id = $tuanBuying['tuan_id'];
            $tuan_buying['create_msg_status'] = $this->get_tb_msg_status(TUAN_CREATE_MSG,$tb_id);
            $tuan_buying['cancel_msg_status'] = $this->get_tb_msg_status(TUAN_CANCEL_MSG,$tb_id);
            $tuan_buying['complete_msg_status'] = $this->get_tb_msg_status(TUAN_COMPLETE_MSG,$tb_id);
            $tuan_buying['tip_msg_status'] = $this->get_tb_msg_status(TUAN_TIP_MSG,$tb_id);
            $tuan_buying['start_deliver_msg_status'] = $this->get_tb_msg_status(TUAN_STARTDELIVER_MSG,$tb_id);
            $tuan_buying['notify_deliver_msg_status'] = $this->get_tb_msg_status(TUAN_NOTIFYDELIVER_MSG,$tb_id);
            $tuan_buying['tuan_team'] = $tuan_teams[$tuan_id];
            $tuan_buying['tuan_product'] = $tuan_products[$tuanBuying['pid']];

            $tuan_buying['paid_orders_count'] = $paid_orders_count[$tb_id] ?: 0;
        }

        $this->set('cons_type',$cons_type);
        $this->set('tuan_buyings', $tuan_buyings);
        $this->set('team_id',$team_id);
        $this->set('product_id',$product_id);
        $this->set('status_type',$status_type);
        $this->set('tuan_status',$tuan_status);
    }

    /**
     * tuan msg has send
     * @param $type
     * @param $tb_id
     * @return string
     */
    private function get_tb_msg_status($type,$tb_id){
        $cond = array(
            'flag' => $tb_id,
            'type' => $type
        );
        if($type==TUAN_CREATE_MSG){
            $tml = $this->TuanBuyingMessages->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '已发建团消息';
            }else{
                return 'true';
            }
        }
        if($type==TUAN_CANCEL_MSG){
            $tml = $this->TuanBuyingMessages->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '已发团取消消息';
            }else{
                return 'true';
            }

        }
        if($type==TUAN_COMPLETE_MSG){
            $tml = $this->TuanBuyingMessages->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '已发团完成消息';
            }else{
                return 'true';
            }

        }
        if($type==TUAN_TIP_MSG){
            $cond['date(send_date)']=date(FORMAT_DATE);
            $tml = $this->TuanBuyingMessages->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '今天已发提示消息';
            }else{
                return 'true';
            }
        }
        if($type==TUAN_STARTDELIVER_MSG){
            $tml = $this->TuanBuyingMessages->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '已发配送消息';
            }else{
                return 'true';
            }
        }
        if($type==TUAN_NOTIFYDELIVER_MSG){
            $tml = $this->TuanBuyingMessages->find('first',array(
                'conditions' => $cond
            ));
            if(!empty($tml)){
                return '已发到货消息';
            }else{
                return 'true';
            }
        }
    }

    public function admin_batch_set_status(){
        $this->autoRender = false;
        $res = array();
        $tuan_buy_ids = $_REQUEST['tuan_buy_ids'];
        $status = intval($_REQUEST['status']);
        $tuan_buy_ids = explode(',',$tuan_buy_ids);
        if($this->TuanBuying->updateAll(array('status' => $status),array('id' => $tuan_buy_ids))){
            $res['success'] = true;
        }else{
            $res['success'] = false;
        }
        echo json_encode($res);
    }

    /**
     * set tuan_buying status
     */
    public function admin_tuan_buying_set(){
        $this->autoRender = false;
        if($this->request->is('post')){
            $id = $_REQUEST['id'];
            $val = $_REQUEST['val'];
            $res = array();
            $this->TuanBuying->updateAll(array('status' => $val),array('id' => $id));
            $this->log('status'.json_encode($res));
            echo json_encode($res);
        }
    }

    public function admin_edit($id){
        $data_info = $this->TuanBuying->find('first',array('conditions' => array('id' => $id)));
        $this->log('data_info'.json_encode($data_info));
        if (empty($data_info)) {
            throw new ForbiddenException(__('该团购不存在！'));
        }
        $this->data = $data_info;
        $this->set('id',$id);
    }

    public function admin_update($id){
        $this->log('update tuan buying '.$id.': '.json_encode($this->data));
        $this->autoRender = false;
        if($this->data['TuanBuying']['published'] == 1){
            $this->data['TuanBuying']['published'] = 1;
        }
        else{
            $this->data['TuanBuying']['published'] = 0;
        }
        if($this->TuanBuying->save($this->data)){
            $this->redirect(array('controller' => 'tuan_buyings','action' => 'index'));
        }
        $this->set('id',$id);
    }

    public function admin_new(){
    }

    public function admin_create(){
        $tuanTeamIds = $_REQUEST['team_ids'];
        $this->log('tuanTeamIds'.$tuanTeamIds);
        $tuanTeamIds = explode(',',$tuanTeamIds);
        $tuanProductIds = $_REQUEST['tuan_products'];
        $this->log('tuanProductIds'.$tuanProductIds);
        $tuanProductIds = explode(',',$tuanProductIds);
        App::import('Controller','TuanMsg');

        if(!empty($this->data)){
            if($this->data['TuanBuying']['published'] == 1){
                $this->data['TuanBuying']['published'] = 1;
            }
            else{
                $this->data['TuanBuying']['published'] = 0;
            }
            foreach($tuanTeamIds as $tuanTeamId){
                foreach($tuanProductIds as $tuanProductId){
                    $this->data['TuanBuying']['pid'] = $tuanProductId;
                    $this->data['TuanBuying']['tuan_id'] = $tuanTeamId;
                    $this->data['TuanBuying']['join_num'] = 0;
                    $this->data['TuanBuying']['sold_num'] = 0;
                    $this->data['TuanBuying']['stTuanBuyingMessagesatus'] = 0;
                    //todo created fields missing
                    $this->log("create tuan buying product .$tuanProductId.'for team ".$tuanTeamId.": ".json_encode($this->data));
                    $this->TuanBuying->create();
                    if($this->TuanBuying->save($this->data)){
                        $tuanBuyId = $this->TuanBuying->getLastInsertID();
                        //                $this->admin_send_tuan_buy_create_msg($tuanBuyId);
                    }
                }
            }
        }
        $this->redirect(array('controller' => 'tuan_buyings','action' => 'index'));
    }

    public function admin_set_status(){
        $this->autoRender = false;
        $tuan_orderIds = preg_split('/(,|\n)/',trim($_REQUEST['tuan_orderid']));
        $tuan_orderstatus = $_REQUEST['order_status'];
        $order_info = $this->Order->find('all',array('conditions' => array('id' => $tuan_orderIds,'status'=> 1)));
        $this->log('order_info'.json_encode($order_info));
        if(!empty($tuan_orderstatus)){
            if(!empty($order_info)){
                $this->Order->updateAll(array('status' => $tuan_orderstatus),array('id' => $tuan_orderIds));
                echo json_encode(array('success' => true,'msg' => '状态修改成功'));
            }else{
                echo json_encode(array('success' => false,'msg' => '订单不存在或订单状态已修改'));
            }
        }
    }

    public function admin_send_tuan_buy_create_msg($tuanBuyId){
        $this->autoRender = false;
        $msg_element = get_tuan_msg_element($tuanBuyId,false);
        if(empty($msg_element)) {
            echo json_encode(array('success' => false,'msg' => '该团购不存在,亲先创建..'));
            return;
        }
        if($msg_element['tuan_buy_status']!=0){
            echo json_encode(array('success' => false,'msg' => '只有进行中的团购才能推送该消息.'));
            return;
        }
        $consign_time = $msg_element['consign_time'];
        $uids = $msg_element['uids'];
        $tuan_name = $msg_element['tuan_name'];
        $title = '您参加的'.$tuan_name.',发起了一个新的团购。';
        $product_name = $msg_element['product_name'];
        $product_name = $product_name.', '.$consign_time.'发货';
        $tuan_leader = $msg_element['tuan_leader'];
        $deatil_url = WX_HOST.'/tuan_buyings/detail/'.$tuanBuyId;
        $remark = '点击详情，赶快和小伙伴一起团起来！';
        foreach($uids as $uid){
            //if user has receive msg
            if(!in_array($uid,$this->msg_uid)){
                //TODO log fail user id
                if($this->Weixin->send_tuan_tip_msg($uid,$title,$product_name,$tuan_leader,$remark,$deatil_url)){
                    $this->msg_uid[] = $uid;
                }
            }
        }
        $this->save_msg_log(TUAN_CREATE_MSG,$tuanBuyId);
    }

    private function save_msg_log($type,$tb_id){
        $msg_log = array(
            'type' => $type,
            'flag' => $tb_id,
            'send_date' => date(FORMAT_DATETIME)
        );
        $this->TuanBuyingMessages->create();
        $this->TuanBuyingMessages->save($msg_log);
    }

    public function admin_send_wx_fetch_msg($type = null){
        $this->autoRender = false;
        $data=$_POST;
        if(empty($data)){
            return false;
        }
        if($type == 'normal'){
            $ids = $data['ids'];
        }else{
            $ids = array_keys($data);
        }
        $orders = $this->Order->find('all', array(
            'conditions' => array('id'=> $ids, 'type'=>ORDER_TYPE_TUAN),
        ));
        $tuan_buy_ids = array_unique(Hash::extract($orders, '{n}.Order.member_id'));
        $consignee_ids = array_unique(Hash::extract($orders, '{n}.Order.consignee_id'));
        $products = $this->TuanBuying->find('all', array(
            'conditions' => array('TuanBuying.id'=>$tuan_buy_ids),
            'joins' =>array(
                array(
                    'table' => 'tuan_products',
                    'alias' => 'TuanProduct',
                    'type' => 'inner',
                    'conditions' => array(
                        'TuanProduct.product_id = TuanBuying.pid',
                    )
                )
            ),
            'fields' => array('TuanBuying.id', 'TuanProduct.alias')
        ));
        $alias = Hash::combine($products, '{n}.TuanBuying.id', '{n}.TuanProduct.alias');
        $offline_stores = $this->OfflineStore->find('all', array(
            'conditions' => array('id'=>$consignee_ids)
        ));
        $store_info = Hash::combine($offline_stores, '{n}.OfflineStore.id', '{n}.OfflineStore');
        $order_info = Hash::combine($orders, '{n}.Order.id', '{n}.Order');
        $uids = Hash::extract($orders, '{n}.Order.creator');
        $oauthBindModel = ClassRegistry::init('Oauthbind');
        $r = $oauthBindModel->find('list', array(
            'conditions' => array( 'user_id' => $uids, 'source' => oauth_wx_source()),
            'fields' => array('user_id','oauth_openid')
        ));
        $success = array();
        $fail = array();
        foreach($order_info as $key => $value){
            $order_id = $key;
            $tuan_buy_id =  $value['member_id'];
            $product_alias = $alias[$tuan_buy_id];
            $store= empty($value['consignee_id'])?null:$store_info[$value['consignee_id']];
            $store_alias = empty($store)?'':$store['alias'];
            $store_phone = empty($store)?'':'，自提点电话：'.$store['owner_phone'];
            $post_data = array(
                "touser" => $r[$value['creator']],
                "template_id" => '3uA5ShDuM6amaaorl6899yMj9QvBmIiIAl7T9_JfR54',
                "url" => WX_HOST . '/orders/detail/' . $order_id,
                "topcolor" => "#FF0000",
                "data" => array(
                    "first" => array("value" => "亲，您订购的".$product_alias."已经到达自提点，提货码：".$data[$order_id]."，生鲜娇贵，请尽快取货哈。"),
                    "keyword1" => array("value" => $order_id),
                    "keyword2" => array("value" => $store_alias),
                    "keyword3" => array("value" => $value['consignee_address']),
                    "remark" => array("value" => "感谢您的支持，现场提货遇到任何问题请拨打电话：4000-508-528", "color" => "#FF8800")
                )
            );
            if($type == 'normal'){
                $post_data['data'] =  array(
                    "first" => array("value" => "亲，您订购的".$product_alias."已经到达自提点，生鲜娇贵，请尽快取货哈。"),
                    "keyword1" => array("value" => $order_id),
                    "keyword2" => array("value" => $store_alias),
                    "keyword3" => array("value" => $value['consignee_address']),
                    "remark" => array("value" => "感谢您的支持".$store_phone, "color" => "#FF8800")
                );
            }
            if(send_weixin_message($post_data)){
                $success[]=$order_id;
            }else{
                $fail[] = $order_id;
            }
        }
        if(empty($fail)){
            echo json_encode(array('success' => true, 'res' => $success));
        }else{
            echo json_encode(array('success' => false, 'res' => $fail));
        }
    }
    public function admin_set_order_status(){

    }
}