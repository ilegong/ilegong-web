<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 8/23/15
 * Time: 20:16
 */

class UtilController extends AppController{

    public $name = 'util';

    public $uses = array('UserRelation', 'Order', 'Cart', 'User', 'Oauthbind');

    public $components = array('ShareUtil');

    /**
     * 获取微信的token
     */
    public function get_base_token() {
        $this->autoRender = false;
        try {
            if ($this->is_admin($this->currentUser['id'])) {
                $this->loadModel('WxOauth');
                $o = $this->WxOauth->get_base_access_token();
                $log = "get_base_access_token:" . $o . ", in json:" . json_encode($o);
                $this->log($log);
                echo $log;
            }
        } catch (Exception $e) {
            echo "exception: $e";
        }
    }

    /**
     * @param $shareId
     * @param $user_id
     * 根据分享迁移数据
     */
    public function transferFansByShareId($shareId, $user_id){
        $this->autoRender = false;
        $orders = $this->Order->find('all', array(
            'conditions' => array(
                'member_id' => $shareId,
                'status' => array(ORDER_STATUS_PAID, ORDER_STATUS_DONE, ORDER_STATUS_SHIPPED, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY, ORDER_STATUS_RECEIVED),
                'type' => ORDER_TYPE_WESHARE_BUY
            ),
            'group' => array('creator'),
            'limit' => 1000
        ));
        $save_data = array();
        $temp_data = array();
        foreach($orders as $order){
            $order_creator = $order['Order']['creator'];
            if ($this->ShareUtil->check_user_relation($user_id, $order_creator)) {
                if(!in_array($order_creator, $temp_data)){
                    $temp_data[]=$order_creator;
                    $save_data[] = array('user_id' => $user_id, 'follow_id' => $order_creator, 'type' => 'Transfer', 'created' => date('Y-m-d H:i:s'));
                }
            }
        }
        $this->UserRelation->saveAll($save_data);
        echo json_encode(array('success' => true));
    }

    /**
     * @param $product_id
     * @param $user_id
     * @param $offset
     * 迁移粉丝数据
     */
    public function transferFansData($product_id, $user_id, $offset = 0) {
        $this->autoRender = false;
        $carts = $this->Cart->find('all', array(
            'conditions' => array(
                'product_id' => $product_id,
                'not' => array('order_id' => null, 'order_id' => 0, 'type' => ORDER_TYPE_WESHARE_BUY, 'creator' => 0, 'creator' => null),
            ),
            'group' => array('creator'),
            'limit' => 500,
            'offset' => $offset,
            'order' => array('created DESC')
        ));
        $save_data = array();
        $temp_data = array();
        foreach ($carts as $cart_item) {
            $cart_creator = $cart_item['Cart']['creator'];
            if ($this->ShareUtil->check_user_relation($user_id, $cart_creator)) {
                if (!in_array($cart_creator, $temp_data)) {
                    $temp_data[] = $cart_creator;
                    $save_data[] = array('user_id' => $user_id, 'follow_id' => $cart_creator, 'type' => 'Transfer', 'created' => date('Y-m-d H:i:s'));
                }
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
        echo json_encode(array('result' => $result, 'tasks' => $tasks));
        return;
    }

    /**
     * @param $user_id
     * @param $limit
     * @param $offset
     * task queue
     */
    public function processUpdateFansProfile($user_id, $limit, $offset){
        $this->autoRender = false;
        $user_relations = $this->UserRelation->find('all', array(
            'conditions' => array(
                'user_id' => $user_id,
                'type' => 'Transfer'
            ),
            'limit' => $limit,
            'offset' => $offset,
            'order' => array('created DESC')
        ));
        $follow_ids = Hash::extract($user_relations, '{n}.UserRelation.follow_id');
        $oauthBinds = $this->Oauthbind->find('all', array(
            'conditions' => array(
                'user_id' => $follow_ids
            ),
            'limit' => $limit
        ));
        if (!empty($oauthBinds)) {
            foreach ($oauthBinds as $item) {
                $follow_user_id = $item['Oauthbind']['user_id'];
                $open_id = $item['Oauthbind']['oauth_openid'];
                $wx_user = get_user_info_from_wx($open_id);
                $this->log('download wx user info ' . json_encode($wx_user));
                $photo = $wx_user['headimgurl'];
                $nickname = $wx_user['nickname'];
                if(empty($nickname)){
                    //user not have wexin info
                    $this->UserRelation->updateAll(array('deleted' => 1), array('user_id' => $user_id, 'follow_id' => $follow_user_id, 'type' => 'Transfer'));
                    continue;
                }
                //when user many oauth bind
                $this->UserRelation->updateAll(array('deleted' => 0), array('user_id' => $user_id, 'follow_id' => $follow_user_id, 'type' => 'Transfer'));
                //default header
                $download_url = 'http://51daifan.sinaapp.com/img/default_user_icon.jpg';
                if (!empty($photo)) {
                    $this->log('download wx user photo ' . $photo);
                    $download_url = download_photo_from_wx($photo);
                }
                $this->User->updateAll(array('nickname' => "'" . $nickname . "'", 'image' => "'" . $download_url . "'"), array('id' => $follow_user_id));
            }
        }
        //update status
        $this->UserRelation->updateAll(array('is_update' => 1), array('user_id' => $user_id, 'follow_id' => $follow_ids, 'type' => 'Transfer'));
        echo json_encode(array('success' => true, 'oauth-bind' => $oauthBinds, 'follow-id' => $follow_ids));
        return;
    }

    /**
     * @param $tasks
     * @return array
     * 添加队列任务
     */
    private function addTaskQueue($tasks) {
        $queue = new SaeTaskQueue('cqueue');
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