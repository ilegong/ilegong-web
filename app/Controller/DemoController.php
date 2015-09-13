<?php

class DemoController extends AppController {

    var $uses = array('DemoData');

    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'demo';
    }

    public function login() {

    }

    public function do_login() {
        return $this->redirect(array('action' => 'list_data'));
    }

    public function list_data() {
        $datas = $this->DemoData->find('all', array('conditions' => array(),
            'limit' => 100,
            'order' => array('created DESC')));
        $this->set('datas', $datas);
    }

    public function add() {
        $datetime = date('Y-m-d H:i:s');
        $this->set('datetime', $datetime);
    }

    public function do_add() {
        if ($this->request->is('post')) {
            $this->DemoData->create();
            if ($this->DemoData->save($this->request->data)) {
                return $this->redirect(array('action' => 'list_data'));
            }
        }
    }

    public function detail($id) {
        $detail = $this->DemoData->find('first', array(
            'conditions' => array(
                'id' => $id
            )
        ));
        $this->set('detail', $detail);
        $imageStr = $detail['DemoData']['images'];
        $images = explode('|', $imageStr);
        $this->set('images', $images);
    }

}