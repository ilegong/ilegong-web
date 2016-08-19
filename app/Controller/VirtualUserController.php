<?php

class VirtualUserController extends AppController
{


    public static $virtual_user = [926835, 928127, 928126, 929214, 929332, 929473, 930646];

    public function beforeFilter(){
        parent::beforeFilter();
        $this->layout = 'sharer';
    }

    public function virtual_user_list(){
        $this->loadModel('User');
        $users = $this->User->find('all', [
            'conditions' => [
                'id' => self::$virtual_user
            ],
            'fields' => ['id', 'nickname']
        ]);
        $this->set('users', $users);
    }

    public function virtual_dashboard($uid)
    {
        $this->loadModel('User');
        $this->loadModel('ShareFaq');
        $this->loadModel('Comment');
        $this->loadModel('Order');
        $this->loadModel('Weshare');
        $weshares = $this->Weshare->find('all', [
            'conditions' => [
                'creator' => $uid
            ],
            'fields' => ['id', 'title']
        ]);
        $weshare_ids = Hash::extract($weshares, '{n}.Weshare.id');
        $weshare_map = Hash::combine($weshares, '{n}.Weshare.id', '{n}.Weshare');
        $this->set('weshare_map', $weshare_map);
        $user = $this->User->find('first', [
            'conditions' => [
                'id' => $uid
            ],
            'fields' => ['id', 'nickname']
        ]);
        $this->set('user', $user);
        $faqs = $this->ShareFaq->find('all', [
            'conditions' => [
                'ShareFaq.receiver' => $uid
            ],
            'joins' => [
                [
                    'table' => 'cake_users',
                    'alias' => 'User',
                    'type' => 'inner',
                    'conditions' => 'User.id = ShareFaq.sender'
                ]
            ],
            'limit' => 10,
            'fields' => ['User.nickname', 'User.id', 'ShareFaq.msg', 'ShareFaq.share_id', 'ShareFaq.created'],
            'order' => 'ShareFaq.id DESC'
        ]);
        $this->set('faqs', $faqs);
        $comments = $this->Comment->find('all', [
            'conditions' => [
                'Comment.data_id' => $weshare_ids,
                'Comment.order_id > ' => 0,
                'Comment.parent_id' => 0,
                'Comment.type' => COMMENT_SHARE_TYPE
            ],
            'order' => 'Comment.id DESC',
            'limit' => 10,
            'fields' => ['Comment.data_id', 'Comment.order_id', 'Comment.body', 'Comment.username', 'Comment.created']
        ]);
        $this->set('Comments', $comments);
        $orders = $this->Order->find('all', [
            'conditions' => [
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => ORDER_STATUS_PAID,
                'not' => ['flag' => ORDER_FLAG_VIRTUAL_FLAG],
                'brand_id' => $uid
            ],
            'limit' => 50,
            'order' => 'id DESC'
        ]);
        $this->set('orders', $orders);
    }

    public function virtual_orders()
    {
        $this->loadModel('Order');
        $cond = ['Order.brand_id' => self::$virtual_user, 'Order.type' => ORDER_TYPE_WESHARE_BUY, 'Order.status' => ORDER_STATUS_PAID];
        if ($_REQUEST['shareId']) {
            $cond['Order.member_id'] = $_REQUEST['shareId'];
        }
        if ($_REQUEST['keyword']) {
            $cond['Weshare.title like '] = '%' . $_REQUEST['keyword'] . '%';
        }
        if ($_REQUEST['trueOrder']) {
            $cond['NOT'] = ['Order.flag' => ORDER_FLAG_VIRTUAL_FLAG];
        } else {
            $cond['Order.flag'] = ORDER_FLAG_VIRTUAL_FLAG;
        }
        $joins = [
            [
                'table' => 'cake_weshares',
                'alias' => 'Weshare',
                'type' => 'inner',
                'conditions' => 'Weshare.id=Order.member_id'
            ],
            [
                'table' => 'cake_users',
                'alias' => 'User',
                'type' => 'inner',
                'conditions' => 'User.id = Order.brand_id'
            ]
        ];
        $fields = ['Order.id', 'Order.consignee_name', 'Weshare.title', 'Order.created', 'Order.brand_id', 'User.nickname'];
        $orders = $this->Order->find('all', [
            'conditions' => $cond,
            'joins' => $joins,
            'fields' => $fields
        ]);
        $this->set('orders', $orders);
        $this->set('keyword', $_REQUEST['keyword']);
        $this->set('shareId', $_REQUEST['shareId']);
        $this->set('trueOrder', $_REQUEST['trueOrder']);
    }

    public function set_shipped(){
        $order_ids = $_POST['oids'];
        $order_ids = explode(',', $order_ids);
        $this->loadModel('Order');
        $this->Order->updateAll(['status' => ORDER_STATUS_SHIPPED], ['id' => $order_ids]);
        echo json_encode(['success' => true]);
        exit;
    }

}