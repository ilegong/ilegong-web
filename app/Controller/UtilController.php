<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 8/23/15
 * Time: 20:16
 */

class UtilController extends AppController{

    public $name = 'util';

    public $uses = array('UserRelation', 'Order', 'Cart');

    public $components = array('ShareUtil');


    /**
     * @param $product_id
     * @param $user_id
     * 迁移粉丝数据
     */
    public function transferFansData($product_id, $user_id) {
        $this->autoRender = false;
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'product_id' => $product_id,
                'not' => array('order_id' => null, 'order_id' => 0, 'type' => ORDER_TYPE_WESHARE_BUY, 'creator' => 0, 'creator' => null),
            ),
            'group' => array('creator'),
            'limit' => 500,
            'order' => array('created DESC')
        ));
        $save_data = array();
        foreach ($carts as $cart_item) {
            $cart_creator = $cart_item['Cart']['creator'];
            if ($this->ShareUtil->check_user_relation($user_id, $cart_creator)) {
                $save_data[] = array('user_id' => $user_id, 'follow_id' => $cart_creator, 'type' => 'Transfer', 'created' => date('Y-m-d H:i:s'));
            }
        }
        $this->UserRelation->saveAll($save_data);
        echo json_encode(array('success' => true));
        return;
    }

    /**
     * @param $user_id
     * @param $page_num
     * @param $page_size
     * 从微信里面更新粉丝信息
     */
    public function updateFansProfile($user_id, $page_num, $page_size) {
        $this->autoRender = false;
        $tasks = array();
        foreach (range(0, $page_num) as $index) {
            $offset = $index * $page_size;
            $url = '/util/processUpdateFansProfile/' . $user_id . '/' . $page_size . '/' . $offset;
            $tasks[] = array('url' => $url);
        }
        $result = $this->addTaskQueue($tasks);
        echo json_encode($result);
        return;
    }

    /**
     * @param $user_id
     * @param $limit
     * @param $offset
     * task queue
     */
    public function processUpdateFansProfile($user_id, $limit, $offset){
        $user_relations = $this->UserRelation->find('all', array(
            'conditions' => array(
                'user_id' => $user_id
            ),
            'limit' => $limit,
            'offset' => $offset
        ));
        
    }

    /**
     * @param $tasks
     * @return array
     * 添加队列任务
     */
    private function addTaskQueue($tasks) {
        $queue = new SaeTaskQueue('share');
        $queue->addTask($tasks);
        //将任务推入队列
        $ret = $queue->push();
        //任务添加失败时输出错误码和错误信息
        if ($ret === false) {
            return array('success' => false, 'errno' => $queue->errno(), 'errmsg' => $queue->errmsg());
        }
        return array('success' => true, 'errno' => $queue->errno(), 'errmsg' => $queue->errmsg());
    }
}