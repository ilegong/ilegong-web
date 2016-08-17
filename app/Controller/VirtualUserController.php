<?php

class VirtualUserController extends AppController
{


    public static $virtual_user = [926835, 928127, 928126, 929214, 929332, 929473, 930646];

    public function beforeFilter(){
        parent::beforeFilter();
        $this->layout = 'sharer';
    }

    public function virtual_dashboard()
    {
        $this->loadModel('ShareFaq');
        $this->loadModel('Comment');
        $this->loadModel('Order');

    }

    public function virtual_orders()
    {
        $this->loadModel('Order');
        $cond = ['Order.brand_id' => self::$virtual_user, 'Order.type' => ORDER_TYPE_WESHARE_BUY, 'Order.status' => ORDER_STATUS_PAID, 'Order.flag' => ORDER_FLAG_VIRTUAL_FLAG];
        if($_REQUEST['shareId']){
            $cond['Order.member_id'] = $_REQUEST['shareId'];
        }
        if($_REQUEST['keyword']){
            $cond['Weshare.title like '] = '%' . $_REQUEST['keyword'] . '%';
        }
        $joins = [
            [
                'table' => 'cake_weshares',
                'alias' => 'Weshare',
                'type' => 'inner',
                'conditions' => 'Weshare.id=Order.member_id'
            ]
        ];
        $fields = ['Order.id', 'Order.consignee_name', 'Weshare.title'];
        $orders = $this->Order->find('all', [
            'conditions' => $cond,
            'joins' => $joins,
            'fields' => $fields
        ]);
        $this->set('orders', $orders);
        $this->set('keyword', $_REQUEST['keyword']);
        $this->set('shareId', $_REQUEST['shareId']);
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