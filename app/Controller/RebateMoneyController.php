<?php

class RebateMoneyController extends AppController{

    var $uses = ['RebateLog', 'Order'];

    public function beforeFilter()
    {
        parent::beforeFilter();
        if (empty($this->currentUser['id']) || ($this->is_weixin() && name_empty_or_weixin($this->currentUser['nickname']))) {
            $ref = Router::url($_SERVER['REQUEST_URI']);
            $this->redirect('/users/login.html?force_login=1&auto_weixin=' . $this->is_weixin() . '&referer=' . urlencode($ref));
        }
        $this->layout = 'weshare';
    }

    public function rules(){
    }

    public function detail(){
        $this->loadModel('User');
        $uid = $this->currentUser['id'];
        $totalRebateMoney = $this->User->get_rebate_money($uid, true);
        $this->set('totalRebateMoney', get_format_number($totalRebateMoney/100));
    }

    public function rebate_list($page){
        $limit = 10;
        $uid = $this->currentUser['id'];
        $logs = $this->RebateLog->find('all', [
            'conditions' => [
                'RebateLog.user_id' => $uid
            ],
            'joins' => [
                [
                    'table' => 'cake_orders',
                    'type' => 'left',
                    'alias' => 'Order',
                    'conditions' => 'Order.id = RebateLog.order_id'
                ],
                [
                    'table' => 'cake_users',
                    'type' => 'left',
                    'alias' => 'User',
                    'conditions' => 'User.id = Order.creator'
                ],
                [
                    'table' => 'cake_weshares',
                    'type' => 'left',
                    'alias' => 'Weshare',
                    'conditions' => 'Weshare.id = Order.member_id'
                ]
            ],
            'fields' => ['RebateLog.reason', 'RebateLog.money', 'RebateLog.description', 'Weshare.title', 'Weshare.default_image', 'User.nickname', 'Order.created', 'Weshare.id', 'Order.id'],
            'limit' => $limit,
            'page' => $page,
            'order' => 'RebateLog.id ASC'
        ]);
        $result = [];
        foreach ($logs as $logItem) {
            $item = array_merge([], $logItem['RebateLog'], $logItem['Weshare'], $logItem['User'], $logItem['Order']);
            $item['money'] = get_format_number($item['money']/100);
            $result[] = $item;
        }
        echo json_encode($result);
        exit;
    }

}