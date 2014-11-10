<?php

define('shichituan_cate', 126);

class ArticlesController extends AppController{
	public $name = 'Articles';

    public function shi_chi_tuan(){
        $this->loadModel('Category');
        $Category = $this->Category->find('first', array(
            'conditions' => array('id' => shichituan_cate)
        ));

        $cates = $this->Category->find('all', array(
            'conditions' => array(
                'parent_id' => shichituan_cate,
                'deleted' => 0,
                'published' => 1,
            ),
            'order' => 'id desc',
            'fields' => array('id', 'name')
        ));

        foreach($cates as &$cate) {
            $articles = $this->Article->find('all', array('conditions' => array(
                'cate_id' => $cate['Category']['id'],
                'published' => 1,
                'deleted' => 0,
            ),
                'order' => 'updated asc'
            ));

            $cate['articles'] = $articles;
        }

        // 设置页面SEO标题、关键字、内容描述
        if (!empty($Category['Category']['seotitle'])) {
            $this->pageTitle = $Category['Category']['seotitle'];
        } else {
            $this->pageTitle = $Category['Category']['name'];
        }
        if ($Category['Category']['seodescription']) {
            $this->set('seodescription', $Category['Category']['seodescription']);
        }
        if ($Category['Category']['seokeywords']) {
            $this->set('seokeywords', $Category['Category']['seokeywords']);
        }

        $navigations = $this->readOrLoadAndCacheNavigations(shichituan_cate, $this->Category);
        $this->set('navigations', $navigations);
        $this->set('Category', $Category);
        $this->set('cates', $cates);
        $this->set('op_cate', 'shichituan');
    }

    public function log_js_error() {
        $msg = $_GET['msg'];
        $url = $_GET['url'];
        $ln = $_GET['ln'];
        $uid = $this->currentUser['id']?$this->currentUser['id'] : 0;

        $this->log("$uid : $url : $ln msg=$msg");
        $this->autoRender = false;
    }

    public function log_trace() {
        $this->log('tracekit:'.var_export($_POST, true));
        echo "logged";
        $this->autoRender = false;
    }

    public function message_test() {
        $this->__message("发现错误", '', 1000);
    }
    
}