<?php

/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 11/24/15
 * Time: 16:48
 */
class ShareCountlyController extends AppController {


    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'bootstrap_layout';
    }

    public function admin_order_statics() {
        $statisticsDataM = ClassRegistry::init('StatisticsData');
        $summeryData = $statisticsDataM->find('all', array(
            'order' => array('id desc'),
            'limit' => 60
        ));
        $summeryData = array_reverse($summeryData);
        $this->set('summeryData', $summeryData);
    }

    public function admin_cron_gen_day_data() {
        $this->autoRender = false;
        $date = $_REQUEST['date'];
        if (empty($date)) {
            $date = date('Y-m-d', strtotime("-1 day"));
        }
        $result = $this->gen_statics_data_by_date($date);
        echo json_encode(array('success' => true, 'data' => $result));
        return;
    }

    public function admin_gen_old_data() {
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

    public function admin_get_sharer_summary() {
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

    private function get_sharer_summery_data($sharer_id, $start_date, $end_date) {
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

    private function gen_statics_data_by_date($date) {
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

}