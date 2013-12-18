<?php

class NotesController extends AppController {

	var $name = 'Note';
	
	var $uses = array('UserCate');
	
/**
     * 列表
     * @param $slug  为所在类别的slug
     */
    public function lists($cateid='') {
        $page = $this->_getParamVars('page',1);
        
        $rows = 5;
        $conditions = array($this->modelClass.'.deleted' => 0);
        if($cateid){
        	$conditions[$this->modelClass.'.cate_id'] = $cateid;
        }
        
        $user_cates = $this->UserCate->find('all',array('conditions'=>array('creator' => $this->currentUser['id'])));
        
        $datalist = $this->{$this->modelClass}->find('all', array(
                    'conditions' => $conditions,
                    'limit' => $rows,
                    'page' => $page,
                        )
        );

        $total = $this->{$this->modelClass}->find('count',
                        array(
                            'conditions' => $conditions,
                        )
        );
        $this->set('modelClass', $this->modelClass);
        $this->set('region_control_name', Inflector::tableize($this->modelClass));
        $this->set('datalist', $datalist);
        $this->set('user_cates', $user_cates);
        $this->set('active_cateid', $cateid);
        $this->set('total', $total);

        $page_navi = getPageLinks($total, $rows, $this->request, $page);
        $this->set('list_page_navi', $page_navi); // page_navi 在region调用中有使用，防止被覆盖，此处使用 list_page_navi
    }
    
    public function add(){
    	$user_cates = $this->UserCate->generateTreeList(array(
    			'creator'=> $this->currentUser['id'],
    			'model'=>'Note'
    	));
    	$this->set('cates', $user_cates);
    	parent::add();
    }
	


}
?>