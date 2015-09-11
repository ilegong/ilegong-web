<?php
/**
 * 分享动态
 */
class ShareOptController extends AppController{

    public function index(){
        $this->layout = null;
    }

    /**
     * @param $time
     * @param $limit
     * @param int $type
     * fetch opt log list
     */
    public function fetch_opt_list_data($time, $limit, $type = 0) {

    }
}
