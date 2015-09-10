<?php

/**
 * Class TaskController
 *
 * for sae task queue
 */
class TaskController extends AppController{

    var $components = array('WeshareBuy', 'ShareUtil');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = null;
    }

    /**
     * @param $share_id
     * @param $recommend_user
     * @param $pageCount
     * @param $pageSize
     * trigger send recommend msg
     */
    public function process_send_recommend_msg($share_id, $recommend_user, $pageCount, $pageSize) {
        $this->autoRender = false;
        $memo = $_REQUEST['memo'];
        $queue = new SaeTaskQueue('tasks');
        $tasks = array();
        foreach (range(0, $pageCount) as $page) {
            $offset = $page * $pageSize;
            $tasks[] = array('url' => "/task/send_recommend_msg_task/" . $share_id . "/" . $recommend_user . "/" . $pageSize . "/" . $offset, "postdata" => "memo=" . $memo);
        }
        $queue->addTask($tasks);
        $ret = $queue->push();
        echo json_encode(array('success' => true, 'ret' => $ret));
        return;
    }

    /**
     * @param $share_id
     * @param $recommend_user
     * @param $pageSize
     * @param $offset
     * child task
     */
    public function send_recommend_msg_task($share_id, $recommend_user, $pageSize, $offset) {
        $this->autoRender = false;
        $memo = $_REQUEST['memo'];
        $this->WeshareBuy->send_recommend_msg_task($share_id, $recommend_user, $memo, $pageSize, $offset);
        echo json_encode(array('success' => true));
        return;
    }

}