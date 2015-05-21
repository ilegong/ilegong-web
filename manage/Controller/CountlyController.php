<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 5/20/15
 * Time: 17:23
 */

class CountlyController extends AppController{

    var $name = 'Countly';

    var $uses = array('User','OfflineStore','Order','StatisticsZitiData','StatisticsOrderData');

    public function admin_index(){
        $this->set('active','gen-data');
    }

    public function admin_get_week_order(){
        $statisticsOrderDatas = $this->StatisticsOrderData->find('all',array(
            'limit'=>100,
            'order' => array('id DESC')
        ));
        $this->set('datas',$statisticsOrderDatas);
    }

    public function admin_get_area_week_order(){
        $areaId = $_REQUEST['area_id'];
        if($areaId){
            $this->set('area_id',$areaId);
        }
    }

    public function admin_get_store_week_order(){
        $storeId= $_REQUEST['store_id'];
        $start_date = $_REQUEST['start_date'];
        $end_date = $_REQUEST['end_date'];
        if($storeId){
            $statisticsZitiDatas = $this->StatisticsZitiData->find('all',array(
                'conditions' => array(
                    'offline_store_id' => $storeId
                ),
                'limit' => 100,
                'order' => array('id DESC')
            ));
            $this->set('datas',$statisticsZitiDatas);
            $this->set('store_id',$storeId);
        }elseif($start_date&&$end_date){
            $statisticsZitiDatas = $this->StatisticsZitiData->find('all',array(
                'conditions' => array(
                    'start_date' => $start_date,
                    'end_date' => $end_date
                ),
                'limit' => 400,
                'order' => array('id DESC')
            ));
            $offlineStoreIds = Hash::extract($statisticsZitiDatas,'{n}.StatisticsZitiData.offline_store_id');
            $statisticsZitiDatas = Hash::combine($statisticsZitiDatas,'{n}.StatisticsZitiData.offline_store_id','{n}');
            $offlineStores = $this->OfflineStore->find('all',array(
                'conditions' => array(
                    'id' => $offlineStoreIds
                )
            ));
            $pys_ziti_point = array_filter($offlineStores,'pys_ziti_filter');
            $hlj_ziti_point = array_filter($offlineStores,'hlj_ziti_filter');
            $this->set('pys_ziti',$pys_ziti_point);
            $this->set('hlj_ziti',$hlj_ziti_point);
            $this->set('datas',$statisticsZitiDatas);
            $this->set('store_list',true);
        }
    }

    /**
     * 自提点数据
     */
    public function admin_gen_data(){
        $this->autoRender = false;
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        if(!$start_date||!$end_date){
            $previous_week = strtotime("-1 week +1 day");
            $start_week = strtotime("last sunday midnight",$previous_week);
            $end_week = strtotime("next sunday",$start_week);
            $start_date = date("Y-m-d",$start_week);
            $end_date = date("Y-m-d",$end_week);
        }
        $this->gen_week_order_data($start_date,$end_date);
        $this->gen_week_ziti_data($start_date,$end_date);
        echo json_encode(array('success'=>true));
        return;

    }

    private function gen_week_ziti_data($start_date,$end_date){
        $orderCond = array(
            'created >=' =>$start_date,
            'created <' => $end_date,
            'status' => array(1,2,3),
            'type' => array(5,6),
            'ship_mark' => 'ziti'
        );
        $orders = $this->Order->find('all',array(
            'conditions' => $orderCond
        ));
        $offline_store_ids = Hash::extract($orders,'{n}.Order.consignee_id');
        $offline_store_ids = array_unique($offline_store_ids);
        $this->log('$offline_store_ids'.json_encode($offline_store_ids));
        foreach($offline_store_ids as $store_id){
            $this->gen_week_store_data($store_id,$start_date,$end_date);
        }
    }

    private function gen_week_store_data($store_id,$start_date,$end_date){

        $store = $this->OfflineStore->find('first',array(
            'conditions' => array(
                'id' => $store_id
            )
        ));

        $orderCond = array(
            'created >=' =>$start_date,
            'created <' => $end_date,
            'status' => array(1,2,3),
            'type' => array(5,6),
            'ship_mark' => 'ziti',
            'consignee_id' => $store_id
        );
        $weekAllOrderCount = $this->Order->find('count',array(
            'conditions' => $orderCond
        ));
        $orders = $this->Order->find('all',array(
            'conditions' => $orderCond
        ));
        $uids = Hash::extract($orders,'{n}.Order.creator');
        $uids = array_unique($uids);
        //$this->log('uids '.$uids);
        $new_user_buy_count = $this->ziti_new_buy_user_count($store_id,$uids,$start_date);
        //$this->log('new_user_buy_count '.$new_user_buy_count);
        $weekMaxOrderCount = $this->Order->query('select MAX(order_count),created from (select count(id) as order_count, date(created) as created from cake_orders where ship_mark=\'ziti\' and consignee_id='.$store_id.' and status in (1,2,3) and created BETWEEN \''.$start_date.'\' and \''.$end_date.'\' group by date(created)) as orders');
        //$this->log('$weekMaxOrderCount '.json_encode($weekMaxOrderCount));
        $repeat_buy_user_count = $this->repeat_ziti_user_count($store_id,$start_date,$end_date);
        //$this->log('$repeat_buy_user_count'.$repeat_buy_user_count);
        $all_new_user_count = $this->load_new_user_count($start_date,$end_date);
        //$this->log('$all_new_user_count'.$all_new_user_count);
        $weekData = array(
            'new_user_buy_count' => $new_user_buy_count,
            'repeat_buy_count' => $repeat_buy_user_count,
            'all_buy_user_count' => count($uids),
            'max_order_count' => $weekMaxOrderCount[0][0]['MAX(order_count)'],
            'all_order_count' => $weekAllOrderCount,
            'area_id' => $store['OfflineStore']['area_id']||0,
            'offline_store_id' => $store_id,
            'created' => date('Y-m-d h:i:s'),
            'all_new_user_count' => $all_new_user_count,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'max_order_date' => $weekMaxOrderCount[0]['orders']['created']
        );
        $this->StatisticsZitiData->id = null;
        $this->StatisticsZitiData->save($weekData);
    }

    private function gen_week_order_data($start_date,$end_date){
        $orderCond = array(
            'created >=' =>$start_date,
            'created <' => $end_date,
            'status' => array(1,2,3)
        );

        $weekAllOrderCount = $this->Order->find('count',array(
            'conditions' => $orderCond
        ));

        $orders = $this->Order->find('all',array(
            'conditions' => $orderCond
        ));

        $uids = Hash::extract($orders,'{n}.Order.creator');
        $uids = array_unique($uids);
        $weekMaxOrderCount = $this->Order->query('select MAX(order_count),created from (select count(id) as order_count, date(created) as created from cake_orders where status in (1,2,3) and created BETWEEN \''.$start_date.'\' and \''.$end_date.'\' group by date(created)) as orders');
        $orderCond['type']=array(5,6);
        $tuanOrderCount = $this->Order->find('count',array(
            'conditions' => $orderCond
        ));
        $orderCond['ship_mark'] = 'ziti';
        $zitiOrderCount = $this->Order->find('count',array(
            'conditions' => $orderCond
        ));
        $new_user_buy_count = $this->new_buy_user_count($uids,$start_date);
        $repead_buy_user_count = $this->repeat_buy_user_count($start_date,$end_date);
        $all_new_user_count = $this->load_new_user_count($start_date,$end_date);
        $weekData = array(
            'new_user_buy_count' => $new_user_buy_count,
            'all_order_count' => $weekAllOrderCount,
            'ziti_order_count' => $zitiOrderCount,
            'tuan_order_count' => $tuanOrderCount,
            'max_order_count' => $weekMaxOrderCount[0][0]['MAX(order_count)'],
            'all_new_user_count' => $new_user_buy_count,
            'max_order_date' => $weekMaxOrderCount[0]['orders']['created'],
            'start_date' => $start_date,
            'end_date' => $end_date,
            'repeat_buy_count' => $repead_buy_user_count,
            'all_buy_user_count' => count($uids),
            'created' => date('Y-m-d h:i:s'),
            'all_new_user_count' => $all_new_user_count
        );
        $this->StatisticsOrderData->save($weekData);
    }

    private function repeat_ziti_user_count($store_id,$start_date,$end_date){
        $result = $this->Order->query('select order_count from (select count(id) as order_count, creator from cake_orders where ship_mark=\'ziti\' and consignee_id='.$store_id.' and created BETWEEN \''.$start_date.'\' and \''.$end_date.'\' and status in (1,2,3) group by creator) as orders where order_count>1');
        return count($result);
    }

    private function repeat_buy_user_count($start_date,$end_date){
        $result = $this->Order->query('select order_count from (select count(id) as order_count, creator from cake_orders where created BETWEEN \''.$start_date.'\' and \''.$end_date.'\' and status in (1,2,3) group by creator) as orders where order_count>1');
        return count($result);
    }

    private function ziti_new_buy_user_count($store_id,$uids,$start_date){
        $allUserCount = count($uids);
        $uids = '('.implode(',',$uids).')';
        $result = $this->Order->query('select count(id) from cake_orders where ship_mark=\'ziti\' and consignee_id='.$store_id.' and creator in '.$uids.' and created < \''.$start_date.'\' group by creator');
        return $allUserCount-(count($result));
    }

    private function new_buy_user_count($uids,$start_date){
        $allUserCount = count($uids);
        $uids = '('.implode(',',$uids).')';
        $result = $this->Order->query('select count(id) from cake_orders where creator in '.$uids.' and created < \''.$start_date.'\' group by creator');
        return $allUserCount-(count($result));
    }

    private function load_new_user_count($start_date,$end_date){
        $all_user_count = $this->User->find('count',array(
            'conditions' => array(
                'created >=' => $start_date,
                'created <=' => $end_date
            ),
            'limit' => 2000
        ));
        return $all_user_count;
    }

}