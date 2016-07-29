<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 11/24/15
 * Time: 16:48
 */
App::uses('CakeNumber', 'Utility');
class ShareCountlyController extends AppController
{


    public $uses = array('SharerStaticsData', 'User');

    public $components = array('Paginator', 'RedisQueue');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = 'bootstrap_layout';
    }

    public function admin_app_statics(){
        $userM = ClassRegistry::init('User');
        $orderM = ClassRegistry::init('Order');
        $start_date = $_REQUEST['start_date'];
        if(empty($start_date)){
            $start_date = date('Y-m-d', strtotime('-1 day'));
        }
        $end_date = $_REQUEST['end_date'];
        if(empty($end_date)){
            $end_date = date('Y-m-d', strtotime('-1 day'));
        }
        $register_data = $userM->query("SELECT count(id) as l_count, date(created) as q_date FROM cake_users WHERE date(created) between '" . $start_date . "' and '" . $end_date . "' group by date(created)");
        //$login_data = $userM->query("SELECT count(id) as r_count, date(last_login) as q_date FROM cake_users where date(last_login) between '" . $start_date . "' and '" . $end_date . "' group by date(created)");
        $buy_data = $orderM->query("SELECT count(DISTINCT creator) as u_count, date(created) as q_date FROM cake_orders WHERE type=9 and status > 0 and status != 10 and date(created) between '" . $start_date . "' and '" . $end_date . "' group by date(created)");
        $new_user_buy_data = $orderM->query("SELECT count(id) as o_count,date(created) as q_date FROM cake_orders as t_o where type=9 and status > 0 and status !=10 and t_o.creator in (select t_u.id from cake_users as t_u where date(t_u.created)=date(t_o.created)) and date(t_o.created) between '" . $start_date . "' and '" . $end_date . "' group by date(t_o.created)");
        $register_data = Hash::combine($register_data, '{n}.0.q_date', '{n}.0.l_count');
        //$login_data = Hash::combine($login_data, '{n}.0.q_date', '{n}.0.r_count');
        $buy_data = Hash::combine($buy_data, '{n}.0.q_date', '{n}.0.u_count');
        $new_user_buy_data = Hash::combine($new_user_buy_data, '{n}.0.q_date', '{n}.0.o_count');
        $this->set('start_date', $start_date);
        $this->set('end_date', $end_date);
        $this->set('register_data', $register_data);
        //$this->set('login_data', $login_data);
        $this->set('buy_data', $buy_data);
        $this->set('new_user_buy_data', $new_user_buy_data);
        $this->set('date_ranges', $this->createDateRange($start_date, $end_date));
    }

    public function admin_hottest_good(){
        $startDate = $_REQUEST['startDate'];
        if(empty($startDate)){
            $startDate = date('Y-m-d', strtotime('-1 day'));
        }
        $endDate = $_REQUEST['endDate'];
        if(empty($endDate)){
            $endDate = date('Y-m-d');
        }
        $status = $_REQUEST['shareStatus'];
        $status = empty($status) ? '0,1' : $status;
        $orderM = ClassRegistry::init('Order');
        $weshareM = ClassRegistry::init('Weshare');
        $userM = ClassRegistry::init('User');
        $data = $orderM->query("SELECT count(co.id) as s_c, co.member_id as weshare_id FROM cake_orders as co JOIN cake_weshares as cw on (cw.id = co.member_id and cw.status in (" . $status . ")) where date(co.created)>='" . $startDate . "' AND date(co.created)<='" . $endDate . "' AND co.status > 0 and co.status != 10 and co.type=9 group by co.member_id order by s_c desc");
        $this->set('data', $data);
        $weshare_ids = array_unique(Hash::extract($data, '{n}.co.weshare_id'));
        $weshares = $weshareM->find('all', [
            'conditions' => [
                'id' => $weshare_ids
            ],
            'fields' => ['id', 'title', 'creator', 'status']
        ]);
        $share_creators = array_unique(Hash::extract($weshares, '{n}.Weshare.creator'));
        $weshares = Hash::combine($weshares, '{n}.Weshare.id', '{n}.Weshare');
        $users = $userM->find('all',[
            'conditions' => [
                'id' => $share_creators
            ],
            'fields' => ['id', 'nickname']
        ]);
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        $this->set('users', $users);
        $this->set('startDate', $startDate);
        $this->set('endDate', $endDate);
        $this->set('weshares', $weshares);
    }


    /**
     * Returns every date between two dates as an array
     * @param string $startDate the start of the date range
     * @param string $endDate the end of the date range
     * @param string $format DateTime format, default is Y-m-d
     * @return array returns every date between $startDate and $endDate, formatted as "Y-m-d"
     */
    function createDateRange($startDate, $endDate, $format = "Y-m-d")
    {
        $range = [];
        if ($startDate == $endDate) {
            $range[] = $startDate;
            return $range;
        }
        $begin = new DateTime($startDate);
        $end = new DateTime($endDate);
        $interval = new DateInterval('P1D'); // 1 Day
        $dateRange = new DatePeriod($begin, $interval, $end);
        foreach ($dateRange as $date) {
            $range[] = $date->format($format);
        }
        return $range;
    }

    public function admin_sharer_statics_detail()
    {
        $user_id = $_REQUEST['user_id'];
        if (!empty($user_id)) {
            $sharer_paginate = array(
                'SharerStaticsData' => array(
                    'conditions' => array(
                        'SharerStaticsData.sharer_id' => $user_id
                    ),
                    'limit' => 60,
                    'order' => array(
                        'SharerStaticsData.data_date' => 'DESC'
                    ))
            );
            $this->Paginator->settings = $sharer_paginate;
            $data = $this->Paginator->paginate('SharerStaticsData');
            $user = $this->User->find('first', array(
                'conditions' => array(
                    'id' => $user_id
                )
            ));
            $this->set('user', $user);
            $this->set('all_data', $data);
        }
    }

    public function admin_order_statics()
    {
        $statisticsDataM = ClassRegistry::init('StatisticsData');
        $summeryData = $statisticsDataM->find('all', array(
            'order' => array('id desc'),
            'limit' => 60
        ));
        $summeryData = array_reverse($summeryData);
        $this->set('summeryData', $summeryData);
    }

    public function admin_sharer_statics()
    {
        $start_date = date('Y-m-d', strtotime('-1 day'));
        $end_date = date('Y-m-d', strtotime('-1 day'));
        if (!empty($_REQUEST['start_date'])) {
            $start_date = $_REQUEST['start_date'];
        }
        if(!empty($_REQUEST['end_date'])){
            $end_date = $_REQUEST['end_date'];
        }
        $this->SharerStaticsData->virtualFields = array('sum_order_count' => 'SUM(order_count)', 'sum_trading_volume' => 'SUM(trading_volume)', 'sum_share_count' => 'SUM(share_count)', 'sum_fans_count' => 'SUM(fans_count)');
        $sharer_statics_paginate = array(
            'SharerStaticsData' => array(
                'conditions' => array(
                    'SharerStaticsData.data_date >=' => $start_date,
                    'SharerStaticsData.data_date <=' => $end_date,
                ),
                'limit' => 100,
                'order' => array(
                    'SharerStaticsData.sum_order_count' => 'desc'
                ),
                'group' => array('SharerStaticsData.sharer_id')
                )
        );
        $this->Paginator->settings = $sharer_statics_paginate;
        $allData = $this->Paginator->paginate('SharerStaticsData');
        $uids = Hash::extract($allData, '{n}.SharerStaticsData.sharer_id');
        $users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $uids
            ),
            'fields' => array('id', 'nickname', 'image')
        ));
        $userRelationM = ClassRegistry::init('UserRelation');
        $userRelationM->virtualFields = array('fans_count' => 'COUNT(id)');
        $userRelationData = $userRelationM->find('all', array(
            'conditions' => array(
                'UserRelation.user_id' => $uids
            ),
            'group' => array('UserRelation.user_id')
        ));
        $userRelationData = Hash::combine($userRelationData, '{n}.UserRelation.user_id', '{n}.UserRelation');
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        $this->set('users', $users);
        $this->set('user_relation_data', $userRelationData);
        $this->set('all_data', $allData);
        $this->set('start_date', $start_date);
        $this->set('end_date', $end_date);
    }

    /**
     * 定时任务生成团长前一天的统计数据
     */
    public function admin_cron_gen_proxy_data()
    {
        $this->autoRender = false;
        $date = $_REQUEST['date'];
        if (empty($date)) {
            $date = date('Y-m-d', strtotime("-1 day"));
        }
        $result = $this->gen_sharer_statics_data_by_date($date);
        echo json_encode($result);
        return;
    }

    public function admin_cron_gen_day_data()
    {
        $this->autoRender = false;
        $date = $_REQUEST['date'];
        if (empty($date)) {
            $date = date('Y-m-d', strtotime("-1 day"));
        }
        $result = $this->gen_statics_data_by_date($date);
        echo json_encode(array('success' => true, 'data' => $result));
        return;
    }

    public function admin_gen_old_data()
    {
        //SELECT count(id), sum(total_all_price), date(created) FROM 51daifan.cake_orders group by date(created);
        $this->autoRender = false;
        $orderM = ClassRegistry::init('Order');
        $statisticsDataM = ClassRegistry::init('StatisticsData');
        $date_summery_data = $orderM->query("SELECT count(id), sum(total_all_price), date(created) FROM cake_orders WHERE date(created) > '2015-08-31' and status !=0 group by date(created)");
        $saveData = array();
        foreach ($date_summery_data as $summery_item) {
            $saveData[] = array(
                'trading_volume' => $summery_item[0]['sum(total_all_price)'],
                'order_count' => $summery_item[0]['count(id)'],
                'created' => date('Y-m-d H:m:s'),
                'data_date' => $summery_item[0]['date(created)']
            );
        }
        $statisticsDataM->saveAll($saveData);
        echo json_encode(array('success' => true));
        return;
    }

    public function admin_get_sharer_summary()
    {
        $sharer_id = $_REQUEST['sharer'];
        if (!empty($sharer_id)) {
            $start_date = $_REQUEST['start_date'];
            $end_date = $_REQUEST['end_date'];
            if (empty($start_date)) {
                $start_date = '2015-09-01';
            }
            if (empty($end_date)) {
                $end_date = date('Y-m-d');
            }
            $this->set('sharer', $sharer_id);
            $this->set('start_date', $start_date);
            $this->set('end_date', $end_date);
            $sharer_summery = $this->get_sharer_summery_data($sharer_id, $start_date, $end_date);
            $this->set($sharer_summery);
        }
    }

    private function get_sharer_summery_data($sharer_id, $start_date, $end_date)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $shares = $weshareM->find('all', array(
            'conditions' => array(
                'date(created) >=' => $start_date,
                'date(created) <=' => $end_date,
                'creator' => $sharer_id
            ),
            'fields' => array('id', 'status')
        ));
        $share_count = count($shares);
        if ($share_count > 0) {
            $userM = ClassRegistry::init('User');
            $sharer_info = $userM->find('first', array(
                'conditions' => array('id' => $sharer_id),
                'fields' => array('id', 'nickname', 'mobilephone', 'is_proxy')
            ));
            $share_ids = Hash::extract($shares, '{n}.Weshare.id');
            $orderM = ClassRegistry::init('Order');
            $order_count = $orderM->find('count', array(
                'conditions' => array(
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'member_id' => $share_ids,
                    'not' => array('status' => 1)
                )
            ));
            $order_total_price = $orderM->find('all', array(
                'conditions' => array(
                    'type' => ORDER_TYPE_WESHARE_BUY,
                    'member_id' => $share_ids,
                    'not' => array('status' => 1)
                ),
                'fields' => array('sum(total_all_price) AS order_total_price')
            ));
            $userRelationM = ClassRegistry::init('UserRelation');
            $fans_count = $userRelationM->find('count', array(
                'conditions' => array(
                    'user_id' => $sharer_id
                )
            ));
            $commentM = ClassRegistry::init('Comment');
            $commentCount = $commentM->find('count', array(
                'conditions' => array(
                    'parent_id' => 0,
                    'data_id' => $share_ids,
                    'type' => 'Share',
                    'not' => array('order_id' => null, 'order_id' => 0)
                )
            ));
            return array('sharer_info' => $sharer_info, 'order_count' => $order_count, 'order_total_price' => $order_total_price, 'fans_count' => $fans_count, 'comment_count' => $commentCount, 'share_count' => $share_count);
        }
        return null;
    }

    private function gen_statics_data_by_date($date)
    {
        $orderM = ClassRegistry::init('Order');
        $statisticsDataM = ClassRegistry::init('StatisticsData');
        $date_summery_data = $orderM->query("SELECT count(id), sum(total_all_price) FROM cake_orders where status !=0 and date(created) = '" . $date . "'");
        $order_count = $date_summery_data[0][0]['count(id)'];
        $total_order_price = empty($date_summery_data[0][0]['sum(total_all_price)']) ? 0 : $date_summery_data[0][0]['sum(total_all_price)'];
        $saveData = array(
            'trading_volume' => $total_order_price,
            'order_count' => $order_count,
            'created' => date('Y-m-d H:m:s'),
            'data_date' => $date
        );
        $staticsData = $statisticsDataM->saveAll($saveData);
        return $staticsData;
    }

    private function gen_sharer_statics_data_by_date($date)
    {
        $userLevelM = ClassRegistry::init('UserLevel');
        $sharer_count = $userLevelM->find('count', array('conditions' => array('type' => 0)));
        $limit = 10;
        $page_count = ceil($sharer_count / $limit);
        //批量添加任务
        $array = array();
        foreach (range(1, $page_count) as $page) {
            $task_url = '/manage/admin/ShareCountly/gen_sharer_statics_data_task/' . $date . '/' . $limit . '/' . $page;
            $array[] = array('url' => $task_url);
        }
        $ret = $this->RedisQueue->add_tasks('cron_data', $array);
        return $ret;
    }


    public function admin_gen_sharer_statics_data_task($date, $limit, $page)
    {
        $this->autoRender =false;
        //生成用户统计数据的任务
        $userLevelM = ClassRegistry::init('UserLevel');
        $user_level_datas = $userLevelM->find('all', array(
            'conditions' => array(
                'type' => 0
            ),
            'limit' => $limit,
            'page' => $page,
            'order' => array('id ASC')
        ));
        $user_ids = Hash::extract($user_level_datas, '{n}.UserLevel.data_id');
        $save_data = array();
        foreach($user_ids as $uid){
            $save_data[] = $this->get_sharer_data($uid, $date);
        }
        if(!empty($save_data)){
            $sharerStaticsDataM = ClassRegistry::init('SharerStaticsData');
            $sharerStaticsDataM->saveAll($save_data);
        }
        echo json_encode(array('success' => true));
        return;
    }

    public function admin_save_sharer_data($user_id)
    {
        $this->autoRender = false;
        $sharerStaticsDataM = ClassRegistry::init('SharerStaticsData');
        $save_data = array();
        $date_ranges = dateRange('2016-01-01', '2016-01-19');
        foreach ($date_ranges as $date) {
            $save_data[] = $this->get_sharer_data($user_id, $date);
        }
        $sharerStaticsDataM->saveAll($save_data);
        echo json_encode(array('success' => true));
        return;
    }

    public function get_sharer_data($user_id, $date)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $orderM = ClassRegistry::init('Order');
        $userRelationM = ClassRegistry::init('UserRelation');
        $create_share_count = $weshareM->find('count', array(
            'conditions' => array(
                'DATE(created)' => $date,
                'creator' => $user_id
            )
        ));
        $fans_count = $userRelationM->find('count', array(
            'conditions' => array(
                'user_id' => $user_id,
                'DATE(created)' => $date
            )
        ));
        $share_view_count = $weshareM->query("select sum(view_count) from cake_weshares where creator = " . $user_id);
        $total_view_count = empty($share_view_count[0][0]['sum(view_count)']) ? 0 : $share_view_count[0][0]['sum(view_count)'];
        $runing_shares = $weshareM->find('all', array(
            'conditions' => array(
                'creator' => $user_id,
                'not' => array('status' => -1)
            ),
            'limit' => 100,
            'order' => array('id DESC')
        ));
        $runing_share_ids = Hash::extract($runing_shares, '{n}.Weshare.id');
        if (!empty($runing_share_ids)) {
            $this->log("gen proxy data sql " . "select count(id), sum(total_all_price) from cake_orders where type=9 and status!=0 and member_id in (" . implode(',', $runing_share_ids) . ") and DATE(created)='" . $date . "'", LOG_DEBUG);
            $order_summery = $orderM->query("select count(id), sum(total_all_price) from cake_orders where type=9 and status!=0 and member_id in (" . implode(',', $runing_share_ids) . ") and DATE(created)='" . $date . "'");
            $order_count = empty($order_summery[0][0]['count(id)']) ? 0 : $order_summery[0][0]['count(id)'];
            $trading_volume = empty($order_summery[0][0]['sum(total_all_price)']) ? 0 : $order_summery[0][0]['sum(total_all_price)'];
            $data = array('order_count' => $order_count, 'trading_volume' => $trading_volume, 'created' => date('Y-m-d H:i:s'), 'data_date' => $date, 'sharer_id' => $user_id, 'share_count' => $create_share_count, 'fans_count' => $fans_count, 'view_count' => $total_view_count);
        } else {
            $data = array('order_count' => 0, 'trading_volume' => 0, 'created' => date('Y-m-d H:i:s'), 'data_date' => $date, 'sharer_id' => $user_id, 'share_count' => $create_share_count, 'fans_count' => $fans_count, 'view_count' => $total_view_count);
        }
        return $data;
    }
}