<?php

/**
 * 分享动态
 */
class ShareOptController extends AppController {

    var $uses = array('OptLog', 'User', 'VisitLog');

    /**
     * pys index view
     */
    public function index() {
        $this->layout = null;
        $uid = $this->currentUser['id'];
        $this->save_visit_log($uid);
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
            $datetime = date('Y-m-d H:i:s', $time);
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
     * check user is has unread opt log
     */
    public function check_opt_has_new() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $visitLog = $this->get_user_visit_log($uid);
        $has_new_result = array('has_new' => true, 'count' => '0');
        if (!empty($visitLog)) {
            $last_visit_time = $visitLog['VisitLog']['last_visit_time'];
            $unread_count = $this->OptLog->fetch_count_by_time($last_visit_time);
            if ($unread_count == 0) {
                $has_new_result['has_new'] = false;
            }
            $has_new_result['count'] = $unread_count;
        }
        echo json_encode($has_new_result);
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

    /**
     * @param $uid
     * update user visit log
     */
    private function save_visit_log($uid) {
        $visitLog = $this->VisitLog->find('first', array(
            'conditions' => array(
                'user_id' => $uid
            )
        ));
        $now = date('Y-m-d H:i:s');
        if (empty($visitLog)) {
            $saveVisitLog = array('user_id' => $uid, 'last_visit_time' => $now);
            $this->VisitLog->save($saveVisitLog);
        } else {
            $this->VisitLog->updateAll(array('last_visit_time' => '\'' . $now . '\''), array('id' => $visitLog['VisitLog']['id']));
        }
    }

    /**
     * @param $uid
     * @return mixed
     * get user visit log
     */
    private function get_user_visit_log($uid) {
        $visitLog = $this->VisitLog->find('first', array(
            'conditions' => array(
                'user_id' => $uid
            )
        ));
        return $visitLog;
    }
}
