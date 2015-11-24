<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 11/24/15
 * Time: 16:48
 */

class ShareCountlyController extends AppController{


    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'bootstrap_layout';
    }

    public function admin_order_statics(){
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

    private function gen_statics_data_by_date($date){
        $orderM = ClassRegistry::init('Order');
        $statisticsDataM = ClassRegistry::init('StatisticsData');
        $date_summery_data = $orderM->query('SELECT count(id), sum(total_all_price) FROM cake_orders where status !=0 and date(created) = '.$date);
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