<?php

/**
 * opt log helper
 */
class OptLogHelperComponent extends Component {

    var $components = array('WeshareBuy', 'ShareUtil');

    public function load_opt_log($time, $limit, $type) {
        //check cache init cache
        $opt_logs = $this->load_opt_log_by_time($time);
        $combine_data = $this->combine_opt_log_data($opt_logs);
        $opt_logs = Hash::extract($opt_logs, '{n}.OptLog');
        $opt_logs = array_map('map_opt_log_data', $opt_logs);
        usort($opt_logs, 'sort_data_by_id');
        $opt_log_data = array('opt_logs' => $opt_logs, 'combine_data' => $combine_data);
        return $opt_log_data;
    }

    /**
     * @return mixed
     */
    private function load_last_opt_data() {
        $key = LAST_OPT_LOG_DATA_CACHE_KEY;
        $data = Cache::read($key);
        $this->log('get cache from ' . $key);
        if (empty($data)) {
            $optLogM = ClassRegistry::init('OptLog');
            $datetime = date('Y-m-d H:i:s');
            $opt_logs = $optLogM->fetch_by_time_limit_type($datetime, 100, 0);
            Cache::write($key, json_encode($opt_logs));
            return $opt_logs;
        }
        $this->log('get opt log use cache');
        $last_opt_logs = json_decode($data, true);
        return $last_opt_logs;
    }

    /**
     * @param $time
     * @return array
     * load opt_log
     */
    private function load_opt_log_by_time($time) {
        $last_opt_data = $this->load_last_opt_data();
        $first_log = $last_opt_data[0];
        $first_log_date = $first_log['OptLog']['created'];
        $first_log_time = strtotime($first_log_date);
        //check logic
        if ($time >= $first_log_time) {
            $log_data = array_slice($last_opt_data, 0, 10);
            return $log_data;
        }
        foreach ($last_opt_data as $index => $log_item) {
            $log_item_date = $log_item['OptLog']['created'];
            $log_item_time = strtotime($log_item_date);
            if ($log_item_time < $time) {
                $log_data = array_slice($last_opt_data, $index, 10);
                return $log_data;
            }
        }
        $optLogM = ClassRegistry::init('OptLog');
        $opt_logs = $optLogM->fetch_by_time_limit_type(date('Y-m-d H:i:s', $time), 10, 0);
        return $opt_logs;
    }

    /**
     * @param $opt_logs
     * @return array
     */
    private function combine_opt_log_data($opt_logs) {
        $key = OPT_LOG_COMBINE_DATA_CACHE_KEY;
        $start_id = $opt_logs[0]['OptLog']['id'];
        $end_id = $opt_logs[count($opt_logs) - 1]['OptLog']['id'];
        $key = $key . '_' . $start_id . '_' . $end_id;
        $combine_opt_log_data = Cache::read($key);
        if (empty($combine_opt_log_data)) {
            $opt_user_ids = Hash::extract($opt_logs, '{n}.OptLog.user_id');
            $opt_data_ids = Hash::extract($opt_logs, '{n}.OptLog.obj_id');
            $share_buy_user_info = $this->WeshareBuy->get_has_buy_user_map($opt_data_ids);
            $share_user_map = $share_buy_user_info['share_user_map'];
            $buy_user_ids = $share_buy_user_info['all_user_ids'];
            $opt_user_ids = array_merge($opt_user_ids, $buy_user_ids);
            $opt_user_ids = array_unique($opt_user_ids);
            $userM = ClassRegistry::init('User');
            $opt_users = $userM->find('all', array(
                'conditions' => array(
                    'id' => $opt_user_ids
                ),
                'fields' => array('id', 'nickname', 'image', 'is_proxy')
            ));
            $userRelationM = ClassRegistry::init('UserRelation');
            $opt_users_share_info = $userRelationM->find('all', array(
                'conditions' => array(
                    'user_id' => $opt_user_ids
                ),
                'group' => array('user_id'),
                'fields' => array(
                    'count(id) as fans_count', 'user_id'
                )
            ));
            $users_level_data = $this->ShareUtil->get_users_level($opt_user_ids);
            $opt_users_share_info = Hash::combine($opt_users_share_info, '{n}.UserRelation.user_id', '{n}.0.fans_count');
            $opt_users = Hash::combine($opt_users, '{n}.User.id', '{n}.User');
            $combine_opt_log_data = array('users' => $opt_users, 'users_level' => $users_level_data, 'user_fans_extra' => $opt_users_share_info, 'share_user_buy_map' => $share_user_map);
            Cache::write($key, json_encode($combine_opt_log_data));
            return $combine_opt_log_data;
        }
        $combine_opt_log_data = json_decode($combine_opt_log_data, true);
        return $combine_opt_log_data;
    }

}