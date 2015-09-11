<?php

/**
 * 分享动态
 */
class ShareOptController extends AppController {

    var $uses = array('OptLog', 'User');

    public function index() {
        $this->layout = null;
    }

    /**
     * fetch opt log list
     */
    public function fetch_opt_list_data() {
        $time = $_REQUEST['time'];
        $limit = $_REQUEST['limit'];
        $type = $_REQUEST['type'];
        $this->autoRender = false;
        if ($time == 0) {
            $time = time();
        }
        $oldest_timestamp = $this->OptLog->get_oldest_update_time();
        $last_timestamp = $this->OptLog->get_last_update_time();
        if ($time <= $oldest_timestamp) {
            $opt_logs = array();
            $combine_data = array();
        } else {
            $datetime = date('Y-m-d H:i:s');
            $opt_logs = $this->OptLog->fetch_by_time_limit_type($datetime, $limit, $type);
            $combine_data = $this->combine_opt_log_data($opt_logs);
            $opt_logs = Hash::extract($opt_logs, '{n}.OptLog');
            $opt_logs = array_map('map_opt_log_data', $opt_logs);
            usort($opt_logs, 'sort_data_by_id');
        }
        echo json_encode(array('oldest_timestamp' => $oldest_timestamp, 'last_timestamp' => $last_timestamp, 'opt_logs' => $opt_logs, 'combine_data' => $combine_data));
        return;
    }

    /**
     * @param $opt_logs
     * @return array
     */
    private function combine_opt_log_data($opt_logs) {
        $opt_user_ids = Hash::extract($opt_logs, '{n}.OptLog.user_id');
        $opt_user_ids = array_unique($opt_user_ids);
        $opt_users = $this->User->find('all', array(
            'conditions' => array(
                'id' => $opt_user_ids
            ),
            'fields' => array('id', 'nickname', 'image')
        ));
        $opt_users = Hash::combine($opt_users, '{n}.User.id', '{n}.User');
        return array('users' => $opt_users);
    }
}
