<?php

/**
 * opt log helper
 */
class OptLogHelperComponent extends Component {

    var $components = array('WeshareBuy');

    public function load_opt_log($time, $limit, $type) {
        //check cache init cache
        $optLogM = ClassRegistry::init('OptLog');
        $datetime = date('Y-m-d H:i:s', $time);
        $opt_logs = $optLogM->fetch_by_time_limit_type($datetime, 100, 0);
        $combine_data = $this->combine_opt_log_data($opt_logs);
        $opt_logs = Hash::extract($opt_logs, '{n}.OptLog');
        $opt_logs = array_map('map_opt_log_data', $opt_logs);
        usort($opt_logs, 'sort_data_by_id');
        $opt_log_data = array('opt_logs' => $opt_logs, 'combine_data' => $combine_data);
        return $opt_log_data;
    }

    private function load_last_opt_data() {
        $key = LAST_OPT_LOG_DATA_CACHE_KEY;
        $data = Cache::read($key);
        if (empty($data)) {
           
        }
        $last_opt_logs = json_decode($data, true);
        return $last_opt_logs;
    }

    /**
     * @param $opt_logs
     * @return array
     */
    private function combine_opt_log_data($opt_logs) {
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
        $opt_users_share_info = Hash::combine($opt_users_share_info, '{n}.UserRelation.user_id', '{n}.0.fans_count');
        $opt_users = Hash::combine($opt_users, '{n}.User.id', '{n}.User');
        return array('users' => $opt_users, 'user_fans_extra' => $opt_users_share_info, 'share_user_buy_map' => $share_user_map);
    }

}