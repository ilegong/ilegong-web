<?php

/**
 * 分享动态
 */
class ShareOptController extends AppController {

    var $uses = array('OptLog', 'User', 'VisitLog', 'UserRelation');

    var $components = array('WeshareBuy', 'OptLogHelper', 'ShareUtil', 'NewOptLogList');

    /**
     * pys index view
     */
    public function index() {
        $this->layout = null;
        $uid = $this->currentUser['id'];
        if(!empty($uid)){
            $this->save_visit_log($uid);
        }
        $this->set('uid', $uid);
        if($_REQUEST['from'] == 'app'){
            $this->set('hide_footer', true);
        }
    }

    public function home()
    {
        $this->layout = null;
        $carousel = ClassRegistry::init('NewFind')->get_all_carousel();
        $top_rank = ClassRegistry::init('NewFind')->get_all_top_rank();
        $this->set('carousel', $carousel);
        $this->set('top_rank_first', $top_rank[0]);
        unset($top_rank[0]);
        $this->set('top_rank', $top_rank);
    }

    public function category_ajax($category)
    {
        $this->layout = null;
        $products = $this->ShareUtil->get_product_by_category($category);

        echo json_encode($products);
        exit();
    }

    public function category($category)
    {
        $this->layout = null;
        $products = $this->ShareUtil->get_product_by_category($category);

        $this->set('products', $products);
    }

    public function get_baoming_ajax() {
        $share = $_REQUEST['share'];
        $proxy = $_REQUEST['proxy'];
        $data = $this->NewOptLogList->get_all_baoming_data($share, $proxy);

        echo json_encode($data);
        exit();
    }

    /**
     * newfetch_opt_list_data 新版本的listdata.
     *
     * @access public
     * @return void
     */
    public function newfetch_opt_list_data() {
        $time = $_REQUEST['time'];
        $limit = $_REQUEST['limit'];
        $type = $_REQUEST['type'];
        $followed = $_REQUEST['followed'];

        $data = $this->fetch_opt_list_data_comman($time, $limit, $type, $followed, 1);
        echo json_encode($data);

        exit();
    }

    public function fetch_opt_list_data_comman($time, $limit, $type, $follow = false, $new = false)
    {
        $this->autoRender = false;
        if ($time == 0) {
            $time = time();
        }
        $oldest_timestamp = $this->OptLog->get_oldest_update_time();
        $last_timestamp = $this->OptLog->get_last_update_time();
        if ($time <= $oldest_timestamp) {
            $opt_logs = [];
            $combine_data = [];
        } else {
            if ($new) {
                $opt_logs = $this->NewOptLogList->get_all_logs($time, $limit, $type, $follow);
                // $opt_logs = $this->OptLogHelper->load_opt_log($time, $limit, $type, 1, $follow);
                $combine_data = [];
                if (!$opt_logs) {
                    return ['error' => 'get data failed.'];
                }
            } else {
                $opt_log_data = $this->OptLogHelper->load_opt_log($time, $limit, $type, 0, $follow);
                $opt_logs = $opt_log_data['opt_logs'];
                $combine_data = $opt_log_data['combine_data'];
            }
        }
        $data = [
            'oldest_timestamp' => $oldest_timestamp,
            'last_timestamp' => $last_timestamp,
            'opt_logs' => $opt_logs,
            'combine_data' => $combine_data
        ];


        return $data;
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
        exit();
    }

    /**
     * @param $uid
     * update user visit log
     */
    private function save_visit_log($uid) {
        $this->loadModel('VisitLog');
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
