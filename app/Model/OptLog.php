<?php
/**
 * opt log modal
 */
class OptLog extends AppModel{


    /**
     * @param $format_date
     * @param $limit
     * @param $type
     * @return array
     */
    public function fetch_by_time_limit_type($format_date, $limit, $type) {
        $fetch_option = array(
            'conditions' => array(
                'created < ' => $format_date,
            ),
            'limit' => $limit,
            'order' => array('created DESC')
        );
        if ($type != 0) {
            $fetch_option['conditions']['obj_type'] = $type;
        }
        $opt_logs = $this->find('all', $fetch_option);
        return $opt_logs;
    }

    /**
     * @return int|mixed
     */
    public function get_oldest_update_time() {
        $timeStamp = Cache::read(OPT_LOG_OLDEST_TIME_CACHE_KEY, 0);
        if ($timeStamp == 0) {
            $oldestLog = $this->find('first', array(
                'order' => array('created ASC')
            ));
            $oldestDate = $oldestLog['OptLog']['created'];
            $timeStamp = strtotime($oldestDate);
            Cache::write(OPT_LOG_OLDEST_TIME_CACHE_KEY, $timeStamp);
        }
        return $timeStamp;
    }

    /**
     * @return int|mixed
     */
    public function get_last_update_time() {
        $timeStamp = Cache::read(OPT_LOG_LAST_TIME_CACHE_KEY, 0);
        if ($timeStamp == 0) {
            $lastLog = $this->find('first', array(
                'order' => array('created DESC')
            ));
            $lastDate = $lastLog['OptLog']['created'];
            $timeStamp = strtotime($lastDate);
            Cache::write(OPT_LOG_LAST_TIME_CACHE_KEY, $timeStamp);
        }
        return $timeStamp;
    }


}