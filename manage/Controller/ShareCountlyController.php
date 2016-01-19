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

    public $components = array('Paginator');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = 'bootstrap_layout';
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
                        'SharerStaticsData.data_date' => 'ASC'
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
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
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
        $users = Hash::combine($users, '{n}.User.id', '{n}.User');
        $this->set('users', $users);
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
        $limit = 5;
        $page_count = ceil($sharer_count / $limit);
        $queue = new SaeTaskQueue('cron_data');
        //批量添加任务
        $array = array();
        foreach (range(0, $page_count) as $page) {
            $task_url = '/manage/admin/ShareCountly/gen_sharer_statics_data_task/' . $date . '/' . $limit . '/' . $page;
            $array[] = array('url' => $task_url);
        }
        $queue->addTask($array);
        //将任务推入队列
        $ret = $queue->push();
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
            'offset' => $limit * $page,
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
        $runing_shares = $weshareM->find('all', array(
            'conditions' => array(
                'creator' => $user_id,
                'not' => array('status' => -1)
            ),
            'limit' => 100,
            'order' => array('id DESC')
        ));
        $runing_share_ids = Hash::extract($runing_shares, '{n}.Weshare.id');
        $order_summery = $orderM->query("select count(id), sum(total_all_price) from cake_orders where type=9 and status!=0 and member_id in (".implode(',', $runing_share_ids).") and DATE(created)='".$date."'");
        $order_count = $order_summery[0][0]['count(id)'];
        $trading_volume = empty($order_summery[0][0]['sum(total_all_price)'])? 0 : $order_summery[0][0]['sum(total_all_price)'];
        $data = array('order_count' => $order_count, 'trading_volume' => $trading_volume, 'created' => date('Y-m-d H:i:s'), 'data_date' => $date, 'sharer_id' => $user_id, 'share_count' => $create_share_count, 'fans_count' => $fans_count);
        return $data;
    }
}