<?php


class ArticlesController extends AppController{
	public $name = 'Articles';

    public function view($slug='/') {
        $this->layout=null;
        $modelClass = $this->modelClass;

        if (empty($slug)) {
            $slug = $this->_getParamVars('slug');
        }
        if (empty($id)) {
            $id = $this->_getParamVars('id');
            if (empty($id)) {
                $id = intval($slug);
            }
        }
        $this->{$modelClass}->recursive = 1; // 显示时，查询出相关联的数据。
        $cond = array($modelClass . '.deleted' => 0, );
        if ($modelClass != 'Product') {
            $cond[$modelClass . '.published'] = 1;
        }
        if (!empty($slug) && $slug != strval(intval($slug))) {
            $cond[$modelClass . '.slug'] = $slug;
        } elseif ($id) {
            $cond[$modelClass.'.id'] = $id;
        } else {
            $this->redirect(array('action' => 'lists'));
        }
        ${$modelClass} = $this->{$modelClass}->find('first', array(
            'conditions' => $cond,
        ));
        $this->viewdata = ${$modelClass};
        if (empty(${$modelClass})) {
            $url = $this->referer();
            if (empty($url))
                $url = '/';
            throw new NotFoundException();
        }

        $this->loadModel('Uploadfile');
        ${$modelClass}['Uploadfile'] = $this->Uploadfile->find('all',array(
            'conditions'=> array(
                'modelclass'=>$modelClass,
                'data_id'=>${$modelClass}[$modelClass]['id']
            ),
            'order'=> array('sortorder DESC')
        ));
        if(Configure::read($modelClass.'.view_nums')){// 记录访问次数
            $this->{$modelClass}->updateAll(
                array('views_count' => 'views_count+1'),
                array('id'=>${$modelClass}[$modelClass]['id'])
            );
        }
        // modelSplitOptions,modelSplitSchema 在ModelSplitBehavior->afterFind中的生成
        $this->set($modelClass . 'SplitOptions', $this->{$modelClass}->modelSplitOptions);
        $this->set($modelClass . 'SplitSchema', $this->{$modelClass}->modelSplitSchema);

//		print_r(${$modelClass});
        // 若同时发布到了多个栏目，导航默认只算第一个栏目的
        $current_cateid = ${$modelClass}[$modelClass]['cate_id'];

        $this->loadModel('Category');
        $path_cachekey = 'category_path_'.$current_cateid;
        $navigations = Cache::read($path_cachekey);
        if ($navigations === false) {
            $navigations = $this->Category->getPath($current_cateid);
            Cache::write($path_cachekey, $navigations);
        }
        // 去除站点类型的节点
        while($navigations[0]['Category']['model']=='website'){
            array_shift($navigations);
        }
        $this->set('top_category_id', $navigations[0]['Category']['id']);
        $this->set('top_category_name', $navigations[0]['Category']['name']);
        //seotitle  seodescription  seokeywords
        if (empty(${$modelClass}[$modelClass]['seotitle'])) {
            ${$modelClass}[$modelClass]['seotitle'] = ${$modelClass}[$modelClass]['title'];
        }
        if (empty(${$modelClass}[$modelClass]['seodescription'])) {
            ${$modelClass}[$modelClass]['seodescription'] = trim(${$modelClass}[$modelClass]['summary']);
        }
        //${$modelClass}[$modelClass]['content'] = $this->_lazyloadimg(${$modelClass}[$modelClass]['content']);
        if (${$modelClass}[$modelClass]['seotitle']) {
            $this->pageTitle = ${$modelClass}[$modelClass]['seotitle'];
        } else {
            $this->pageTitle = ${$modelClass}[$modelClass]['name'];
        }
        $this->set('seodescription', ${$modelClass}[$modelClass]['seodescription']);
        $this->set('seokeywords', ${$modelClass}[$modelClass]['seokeywords']);
        $this->set('current_cateid', $current_cateid);
        $this->set('use_stat', 'view'); // view action 使用统计，记录
        $this->current_data_id = ${$modelClass}[$modelClass]['id'];
        $this->set('current_data_id', ${$modelClass}[$modelClass]['id']);
        $this->set('current_model', $modelClass);
        $this->set('navigations', $navigations);
        $this->set($modelClass, ${$modelClass});
        $params = array($modelClass, ${$modelClass}[$modelClass]['id']);
        $this->Hook->call('viewItem', $params);
        $this->set('hide');
    }

    public function special(){
        $this->layout = null;
    }

    public function dzx(){
        $this->layout = null;
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