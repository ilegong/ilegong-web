<?php


class AppPushManageController extends AppController
{

    var $uses = ['PushMessage'];

    var $components = ['SharePush'];

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = 'sharer';
    }


    public function msg_list()
    {
        require_once(APPLIBS . 'MyPaginator.php');
        $cond = [];
        $count = $this->PushMessage->find('count', $cond);
        $page = intval($_REQUEST['page']) > 0 ? intval($_REQUEST['page']) : 1;
        $cond['limit'] = 50;
        $cond['page'] = $page;
        $cond['sort'] = 'id desc';
        $datas = $this->PushMessage->find('all', $cond);
        $url = "/app_push_manage/msg_list.html?page=(:num)";
        $pager = new MyPaginator($count, 30, $page, $url);
        $this->set('datas', $datas);
        $this->set('pager', $pager);
    }

    public function msg_form()
    {
        $id = $_REQUEST['id'];
        if ($id) {
            $data = $this->PushMessage->findById($id);
            $this->set('data', $data);
        }

    }

    public function save_msg()
    {
        $message = $this->request->data;
        if (empty($message['PushMessage']['id'])) {
            $message['PushMessage']['created'] = date('Y-m-d H:i:s');
        }
        $this->PushMessage->save($message);
        $this->redirect('/app_push_manage/msg_list.html');
    }

    public function preview()
    {
        $msg_id = $_REQUEST['msg_id'];
        $receivers = $_REQUEST['receivers'];
        $receivers = explode(',', $receivers);
        $msg = $this->PushMessage->findById($msg_id);
        $this->SharePush->push_spread_msg($msg['PushMessage']['title'], $msg['PushMessage']['description'], $msg['PushMessage']['type'], $msg['PushMessage']['data_val'], $receivers);
        echo json_encode(['success' => true]);
        exit;
    }

    public function push_all()
    {
        $msg_id = $_REQUEST['msg_id'];
        $msg = $this->PushMessage->findById($msg_id);
        $this->SharePush->push_spread_msg($msg['PushMessage']['title'], $msg['PushMessage']['description'], $msg['PushMessage']['type'], $msg['PushMessage']['data_val']);
        echo json_encode(['success' => true]);
        exit;
    }

}