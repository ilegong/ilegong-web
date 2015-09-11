<?php
/**
 * opt log modal
 */
class OptLog extends AppModel{


    /**
     * @param $time
     * @param $limit
     * @param $type
     * @return array
     */
    public function fetch_by_time_limit_type($time, $limit, $type) {
        $format_date = date('Y-m-d H:i:s', $time);
        $fetch_option = array(
            'conditions' => array(
                'created < ' => $format_date,
            ),
            'limit' => $limit
        );
        if ($type != 0) {
            $fetch_option['conditions']['obj_type'] = $type;
        }
        $opt_logs = $this->find('all', $fetch_option);
        return $opt_logs;
    }

    public function get_oldest_update_time(){

    }

    public function get_last_update_time(){

    }


}