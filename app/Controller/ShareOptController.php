<?php

/**
 * 分享动态
 */
class ShareOptController extends AppController
{

    var $uses = array('OptLog', 'NewOptLog', 'User', 'VisitLog', 'UserRelation');

    var $components = array('WeshareBuy', 'ShareUtil', 'NewOptLogs');

    /**
     * pys index view
     */
    public function index()
    {
        $this->layout = null;
        $uid = $this->currentUser['id'];
        if (!empty($uid)) {
            $this->save_visit_log($uid);
        }
        $this->set('uid', $uid);
        if ($_REQUEST['from'] == 'app') {
            $this->set('hide_footer', true);
        }
    }

    /**
     * pys index view
     */
    public function newindex()
    {
        $this->layout = 'weshare';
        $uid = $this->currentUser['id'];
        if (!empty($uid)) {
            $this->save_visit_log($uid);
        }
        $this->set('uid', $uid);
        if ($_REQUEST['from'] == 'app') {
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

    public function get_baoming_ajax()
    {
        $share = $_REQUEST['share'];
        $proxy = $_REQUEST['proxy'];
        $data = $this->NewOptLogs->get_all_baoming_data($share, $proxy);

        echo json_encode($data);
        exit();
    }

    public function fetch_opt_list_data()
    {
        $this->autoRender = false;

        $time = $_REQUEST['time'];
        $limit = $_REQUEST['limit'];
        $type = $_REQUEST['type'];

        if ($time == 0) {
            $time = time();
        }
        $oldest_timestamp = $this->OptLog->get_oldest_update_time();
        $last_timestamp = $this->OptLog->get_last_update_time();
        $opt_logs = [];
        $this->log('Old time stamp: '.$oldest_timestamp.', time: '.$time);
        if ($time > $oldest_timestamp) {
            $opt_logs = $this->NewOptLog->get_all_logs($time, $limit, $type, false);
            if (!$opt_logs) {
                return ['error' => 'get data failed.'];
            }
            foreach ($opt_logs as &$opt_log) {
                if($opt_log['Weshare']['images']){
                    $opt_log['Weshare']['images'] = explode('|', $opt_log['Weshare']['images']);
                }
            }
        }

        $data = [
            'oldest_timestamp' => $oldest_timestamp,
            'last_timestamp' => $last_timestamp,
            'opt_logs' => array_values($opt_logs)
        ];

        echo json_encode($data);

        exit();
    }

    /**
     * newfetch_opt_list_data 新版本的listdata.
     *
     * @access public
     * @return void
     */
    public function newfetch_opt_list_data()
    {
        $this->autoRender = false;
        $time = $_REQUEST['time'];
        $limit = $_REQUEST['limit'];
        $type = $_REQUEST['type'];
        $followed = $_REQUEST['followed'];

        $data = $this->fetch_opt_list_data_comman($time, $limit, $type, $followed);
        echo json_encode($data);

        exit();
    }

    private function fetch_opt_list_data_comman($time, $limit, $type, $follow = false)
    {
        if ($time == 0) {
            $time = time();
        }
        $oldest_timestamp = $this->OptLog->get_oldest_update_time();
        $last_timestamp = $this->OptLog->get_last_update_time();
        if ($time <= $oldest_timestamp) {
            $opt_logs = [];
        } else {
            $opt_logs = $this->NewOptLogs->get_all_logs($time, $limit, $type, $follow);
            if (!$opt_logs) {
                return ['error' => 'get data failed.'];
            }
        }
        $data = [
            'oldest_timestamp' => $oldest_timestamp,
            'last_timestamp' => $last_timestamp,
            'opt_logs' => $opt_logs
        ];

        return $data;
    }

    /**
     * check user is has unread opt log
     */
    public function check_opt_has_new()
    {
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
    private function save_visit_log($uid)
    {
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
    private function get_user_visit_log($uid)
    {
        $visitLog = $this->VisitLog->find('first', array(
            'conditions' => array(
                'user_id' => $uid
            )
        ));
        return $visitLog;
    }
}
