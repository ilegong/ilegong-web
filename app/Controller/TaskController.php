<?php

/**
 * Class TaskController
 *
 * for sae task queue
 */
class TaskController extends AppController {

    var $components = array('WeshareBuy', 'ShareUtil', 'Weixin');

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

    /**
     * @param $shareId
     * 批量退款，主要处理子分享订单
     */
    public function batch_refund_money($shareId) {
        $this->autoRender = false;
        $remark = $_REQUEST['remark'];
        $this->ShareUtil->batch_refund_order($shareId, $remark);
        echo json_encode(array('success' => true));
        return;
    }

    private function group_share_order_counts($share_ids) {
        $orderM = ClassRegistry::init('Order');
        $order_count_share_map = $orderM->find('all', array(
            'conditions' => array(
                'member_id' => $share_ids,
                'status' => array(ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE),
                'type' => ORDER_TYPE_WESHARE_BUY
            ),
            'fields' => array('COUNT(id), member_id'),
            'group' => array('member_id')
        ));
        $order_count_share_map = Hash::combine($order_count_share_map, '{n}.Order.member_id', '{n}.0.COUNT(id)');
        return $order_count_share_map;
    }

    private function share_group_limit_map($share_ids) {
        $shipSettingM = ClassRegistry::init('WeshareShipSetting');
        $groupShareShipSettings = $shipSettingM->find('all', array(
            'conditions' => array(
                'weshare_id' => $share_ids,
                'tag' => SHARE_SHIP_GROUP_TAG,
                'status' => PUBLISH_YES
            )
        ));
        $groupShareShipSettings = Hash::combine($groupShareShipSettings, '{n}.WeshareShipSetting.weshare_id', '{n}.WeshareShipSetting.limit');
        return $groupShareShipSettings;
    }

    public function notify_user_group_share_state() {
        $this->autoRender = false;
        $shares = $this->ShareUtil->get_recent_group_share();
        $share_ids = Hash::extract($shares, '{n}.Weshare.id');
        $refer_share_ids = Hash::extract($shares, '{n}.Weshare.refer_share_id');
        $share_order_count_map = $this->group_share_order_counts($share_ids);
        $share_group_limit_map = $this->share_group_limit_map($refer_share_ids);
        $queue = new SaeTaskQueue('tasks');
        //批量添加任务
        $tasks = array();
        //任务添加失败时输出错误码和错误信息
        foreach ($shares as $share_item) {
            $item_share_id = $share_item['Weshare']['id'];
            $item_refer_share_id = $share_item['Weshare']['refer_share_id'];
            if ($share_order_count_map[$item_share_id] < $share_group_limit_map[$item_refer_share_id]) {
                $tasks[] = array('url' => "/task/process_notify_group_share/" . $item_share_id . "/" . $share_order_count_map[$item_share_id] . "/" . $share_group_limit_map[$item_refer_share_id]);
            }
        }
        $queue->addTask($tasks);
        //将任务推入队列
        $ret = $queue->push();
        echo json_encode(array('success' => true, 'ret' => $ret, 'errno' => $queue->errno(), 'errmsg' => $queue->errmsg()));
        return;
    }

    public function process_notify_group_share($share_id, $now_count, $target_count) {
        $this->autoRender = false;
        $share_info = $this->WeshareBuy->get_weshare_info($share_id);
        $share_orders = $this->ShareUtil->get_share_orders($share_id);
        $share_order_user_ids = Hash::extract($share_orders, '{n}.Order.creator');
        $share_order_user_ids[] = $share_info['creator'];
        $share_order_user_ids = array_unique($share_order_user_ids);
        $share_title = $share_info['title'];
        $user_open_ids = $this->WeshareBuy->get_open_ids($share_order_user_ids);
        $user_nicknames = $this->WeshareBuy->get_users_nickname($share_order_user_ids);
        $tuan_leader_name = $user_nicknames[$share_info['creator']];
        $remark = '点击详情，赶紧邀请小伙伴们加入' . $tuan_leader_name . '的分享！';
        $detail_url = $this->WeshareBuy->get_weshares_detail_url($share_id);
        foreach ($user_open_ids as $user_id => $user_open_id) {
            $title = '你好，您报名的' . $share_title . '目标' . $target_count . '份，现在' . $now_count . '份，还差' . ($target_count - $now_count) . '份。加油，加油！';
            $this->Weixin->send_share_buy_complete_msg($user_open_id, $title, $share_title, $tuan_leader_name, $remark, $detail_url);
        }
        echo json_encode(array('success' => true, 'share-id' => $share_id));
        return;
    }

}