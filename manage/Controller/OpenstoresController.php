<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 14/12/6
 * Time: 下午1:24
 */

class OpenstoresController extends AppController{
    var $name = 'Openstores';
    protected function _list() {
        $this->autoRender = false;
        $modelClass = $this->modelClass;
        $control_name = Inflector::tableize($modelClass);
        $page = $_GET['page']; // get the requested page
        $limit = $_GET['rows']; // get how many rows we want to have into the grid
        $sidx = $_GET['sidx']; // get index row - i.e. user click to sort
        $sord = $_GET['sord']; // get the direction
        if(!$sidx) $sidx =1;
        $count = $this->{$modelClass}->find('count', array('conditions'=> array('status'=>'2')));
        if( $count >0 ) {
            $total_pages = ceil($count/$limit);
        } else {
            $total_pages = 0;
        }
        if ($page > $total_pages) $page=$total_pages;

        $results = $this->{$modelClass}->find('all', array(
            'conditions'=>array('status' => 2
            ),
            'fields' => array('id', 'creator', 'store_name', 'link_name', 'mobile', 'qq', 'person_id', 'person_name', 'person_id_pic'),
            'order' => $sidx.' ' .$sord,
            'limit' => $limit,
            'page' => $page
        ));
        $responce = new stdClass();
        $responce->page = $page;
        $responce->total = $total_pages;
        $responce->records = $count;
        $i=0;
        foreach($results as $item){
            /*******重要： action的li之间要一个紧连着一个 ，不要有换行或空格，否则会引起格式错乱。****** */
            $actions = '';

            if($this->AclFilter->check($this->name,'admin_edit')){
                $actions .= '<li class="ui-state-default grid-row-edit"><a title="通过" href="' . Router::url(array('controller' => $control_name, 'action' => 'add',  'admin' => true, $item[$modelClass]['id'])) . '"><span class="glyphicon glyphicon-ok"></span></a></li>';
                $actions .= '<li class="ui-state-default grid-row-edit"><a href="' . Router::url(array('controller' => $control_name, 'action' => 'edit', 'admin' => true, $item[$modelClass]['id'])) . '" title="' . __('Copy') . '"><span class="glyphicon glyphicon-file"></span></a></li>';
            }
            if (!isset($item[$modelClass]['deleted'])) { // 不包含deleted标记位的模块，直接删除
                $actions .= '<li class="ui-state-default grid-row-delete"><a href="#" data-confirm="'.__('Are you sure to delete').'" onclick="ajaxAction(\'' . Router::url(array('controller' => $control_name, 'action' => 'delete', 'plugin' => strtolower($plugin), 'admin' => true, $item[$modelClass]['id'],'ext'=>'json')) . '\',null,null,\'deleteGridRow\',this)" title="' . __('Delete') . '"><span class="glyphicon glyphicon-remove"></span></a></li>';
            } else {// 包含deleted标记位的模块，则删除到回收站
                $actions .= '<li class="ui-state-default grid-row-trash"><a href="#" data-confirm="'.__('Are you sure to trash').'" onclick="ajaxAction(\'' . Router::url(array('controller' => $control_name, 'action' => 'trash', 'plugin' => strtolower($plugin), 'admin' => true, $item[$modelClass]['id'],'ext'=>'json')) . '\',null,null,\'deleteGridRow\',this)" title="' . __('Trash') . '"><span class="glyphicon glyphicon-trash"></span></a></li>';
            }

            $actions .= '<li class="ui-state-default grid-row-view"><a href="' . Router::url(array('controller' => $control_name, 'action' => 'view', 'plugin' => strtolower($plugin), 'admin' => true, $item[$modelClass]['id'])) . '" title="' . __('View') . '"><span class="glyphicon glyphicon-info-sign"></span></a></li>';

            $actions .= $this->Hook->call('gridDataAction', array($modelClass, $item[$modelClass]));
            $item[$modelClass]['actions'] = $actions;


            $params = array($modelClass, &$item[$modelClass]);
            $this->Hook->call('gridList', $params);
            $item[$modelClass]['actions'] = '<ul class="ui-grid-actions">' . $item[$modelClass]['actions'] . '</ul>';

            $responce->rows[$i]['id']=$item[$modelClass]['id'];
            $responce->rows[$i]['cell']= $item[$modelClass];
            $responce->rows[$i]['cell']['person_id_pic']= '<img src="' . $responce->rows[$i]['cell']['person_id_pic'] . '" width="80"/>';
            $i++;
        }
        echo json_encode($responce);
    }
    public function admin_verify(){
        if($this->request->params['ext']=='json'){
            $this->_list();
            return;
        }
    }

    private function __loadFormStore($id = null){
        $modelClass = $this->modelClass;
        $result = $this->{$modelClass}->find('first', array('conditions' => array('id' => $id), 'fields' => array('store_name', 'creator')));
        $result[$modelClass]['slug'] = generate_slug($result[$modelClass]['store_name']);
        $this->request->data = $result;
        $this->set('id', $id);
    }
    public function admin_add($id = null) {
        $this->pageTitle = __("Add " . $this->modelClass, true);
        $modelClass = $this->modelClass;
        if (!empty($_POST)) {
            $this->autoRender = false;
            $brand= array();
            $brand['Brand']['name'] = $this->data[$modelClass]['store_name'];
            $brand['Brand']['slug'] = $this->data[$modelClass]['slug'];
            $brand['Brand']['creator'] = $this->data[$modelClass]['creator'];
            $brand['Brand']['cate_id'] = '121';
            $brand['Brand']['published'] = $this->data[$modelClass]['published'];
            $this->data = $brand;
            $this->loadModel('Brand');
            $this->loadModel('User');
            if($this->Brand->save($this->data)){
                $this->{$modelClass}->updateAll(array('status' => '4'), array('id' => $id));
                $this->User->updateAll(array('is_business' => 1), array('User.id' => $brand['Brand']['creator']));
                $successinfo = array(
                    'success' => __('Add success'),
                    'actions' => array(
                        'nexturl' => Router::url(array('action'=>'verify'))
                    ));
                echo json_encode($successinfo);
            }
        } else {
            // 无提交值，生成表单。加载选项
            $this->__loadFormStore($id);
        }
    }

}