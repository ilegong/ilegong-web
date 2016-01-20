<?php

/**
 * 分享动态
 */
class ShareOptController extends AppController {

    var $uses = array('OptLog', 'User', 'VisitLog', 'UserRelation');

    var $components = array('WeshareBuy', 'OptLogHelper');

    /**
     * pys index view
     */
    public function index() {
        $this->layout = null;
        $uid = $this->currentUser['id'];
        if(!empty($uid)){
            $this->save_visit_log($uid);
        }
        if($_REQUEST['from'] == 'app'){
            $this->set('hide_footer', true);
        }
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
            $opt_log_data = $this->OptLogHelper->load_opt_log($time, $limit, $type);
            $opt_logs = $opt_log_data['opt_logs'];
            $combine_data = $opt_log_data['combine_data'];
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
