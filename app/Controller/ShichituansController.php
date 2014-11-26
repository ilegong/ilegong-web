<?php

/**
 * Created by PhpStorm.
 * User: lpy
 * Date: 2014/10/21
 * Time: 17:59
 */
class ShichituansController extends AppController{
    public $name = 'Shichituans';
    public $Helper = array('Html', 'Form', 'Paginator','Session');
    var $components = array(
        'Email', 'Session', 'Paginator'
    );
    var $user = array('Shichituan', 'User');
    var $paginate = array(
        'Shichituan' => array(
            'order' => 'Shichituan.shichi_id ASC',
            'limit' => 5,
        )
    );

    public function __construct($request = null, $response = null){
        $this->helpers[] = 'PhpExcel';
        parent::__construct($request, $response);
        $this->pageTitle = __('试吃团');
    }

    private function checkAccess(){ //检验是否登录
        if (empty($this->currentUser['id'])) {
            $this->__message('您需要先登录才能操作', '/users/login');
            //return $this->flash('您需要先登录才能操作','/users/login');
        }
    }

    public function apply(){ //试吃团申请
        $this->checkAccess();
        $result = $this->Shichituan->findByUser_id($this->currentUser['id'],array('Shichituan.shichi_id','Shichituan.period'),'Shichituan.shichi_id DESC');

        if ($result){
            if($result['Shichituan']['period']==(date('m', time()) - 8))
            {
            return $this->redirect(array('action' => 'shichi_view'));
            }
            $this->request->data = $this->Shichituan->read(array('Shichituan.wechat','Shichituan.name','Shichituan.company','Shichituan.telenum','Shichituan.email','Shichituan.comment'), $result['Shichituan']['shichi_id']);
        }
           return;
    }

    /**
     * 试吃团申请
     */
    public function shichituan(){
        $this->autoRender = false;
        $this->layout = 'ajax';
        if ($this->request->is('post')) {
            $this->data['Shichituan']['period'] = date('m', time()) - 8;
            $this->data['Shichituan']['user_id'] = $this->currentUser['id'];
            $this->data['Shichituan']['comment'] = htmlspecialchars($this->data['Shichituan']['comment']);
            if ($this->Shichituan->save($this->data)) {
                $successinfo = array('success' => __('谢谢您的申请，我们每月30号统一审核,请您耐心等待.', true));
                echo json_encode($successinfo);
            } else {
                echo json_encode($this->{$this->modelClass}->validationErrors);
            }
        }
    }

    /**
     * 导出试吃团申请表excel
     */
    function shichi_list(){
        $Shichituans = $this->Shichituan->find("all");
        $this->set("Shichituans", $Shichituans);
    }

    public function shichi_check($period = '') {
        $this->Paginator->settings = $this->paginate;
        $shichituans = $this->Paginator->paginate('Shichituan', array('Shichituan.period' => $period));
        $this->set('shichituans', $shichituans);

    }

    /**
     * 审核试吃团，并更新到数据库
     */

    public function shichi_save(){
        $this->autoRender = false;
        $this->layout = 'ajax';
        if ($this->request->is('post')) {
            $id = $_REQUEST['id'];
            $this->log('id' . json_encode($id));
            $count = $_REQUEST['count'];
            $val = $_REQUEST['val'];
            $res = array();
            foreach ($id as $re) {
                $this->Shichituan->id = null;
                //$r = $this->Shichituan->read($re);
                $this->log('r' . json_encode($re));
                $data = array('Shichituan' => array('shichi_id' => $re, 'status' => $val));
                if ($this->Shichituan->save($data)) {
                    $res [$re] = array('success' => __('申请状态修改成功.', true));

                } else {
                    $res [$re] = array('error' => $this->{$this->modelClass}->validationErrors);
                }
            }
            echo json_encode($res);

        }
    }

    /**
     * 试吃的信息
     */
    public function shichi_message(){
        if ($this->currentUser['id']) {
            $statusinfo = $this->Shichituan->find('first', array('recursive' => -1, 'conditions' => array('user_id' => $this->currentUser['id'])));
            if (empty($statusinfo)) {
                throw new ForbiddenException(__('You cannot view the apply status'));
            }
            $this->set('Mystatus', $statusinfo['Shichituan']);

        } else {
            $this->Session->setFlash(__('You are not authorized to access that location.', true));
            $this->redirect(array('controller' => 'Users', 'action' => 'login'));
        }

    }

    public function shichi_view(){
      
        $result = $this->Shichituan->findByUser_id($this->currentUser['id'],array('Shichituan.shichi_id','Shichituan.status','Shichituan.period'),'Shichituan.shichi_id DESC');
        $shichiId = $result['Shichituan']['shichi_id'];
        $status = $result['Shichituan']['status'];
        $this->set('result',$result);
        $this->request->data = $this->Shichituan->read(null, $shichiId);
        if($status == 0){
           $shichimessage=_('申请正在审核中,请耐心等待');
        } else if ($status == 1){
            $shichimessage=_('申请通过,恭喜您加入我们的试吃团');
        } else  $shichimessage=_('很遗憾,本期已满，请下次再申请');
        $this->set('message',$shichimessage);

        }

    public function shichi_status(){

        $result = $this->Shichituan->findByUser_id($this->currentUser['id'],array('Shichituan.shichi_id','Shichituan.status','Shichituan.period'),'Shichituan.shichi_id DESC');
        $shichiId = $result['Shichituan']['shichi_id'];
        $status = $result['Shichituan']['status'];
        $this->set('result',$result);
        $this->request->data = $this->Shichituan->read(null, $shichiId);
        if($status == 0){
           $shichimessage=_('申请正在审核中,请耐心等待');
        } else if ($status == 1){
            $shichimessage=_('申请通过,恭喜您加入我们的试吃团');
        } else  $shichimessage=_('很遗憾,本期已满，请下次再申请');
        $this->set('message',$shichimessage);

        }
}