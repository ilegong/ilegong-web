<?php

class KeywordsController extends AppController {
/**
 * Controller name
 *
 * @var string
 * @access public
 */
    var $name = 'Keywords';
    

    var $uses = array('Keyword','Misccate');
    
    /**
     * 后台表单中，选择标签内容
     
    function admin_select(){
    	$page = $_GET['page'] ? $_GET['page']:1;
    	$targetid = $_GET['targetid'];
    	$Model = $_GET['m'];
    	$conditions = array();
    	if($_GET['val']){
    		$conditions['value like'] = '%'.$_GET['val'].'%';
    	}
    	$pagesize = 60;
    	$total = $this->Keyword->find('count',array('conditions'=> $conditions));
    	$words = $this->Keyword->find('all',array(
    			'conditions'=> $conditions,
    			'limit' => $pagesize,
    			'page' => $page,
    			
    		));
    	$page_navi = getPageLinks($total, $pagesize, '/admin/keywords/select', $page);
    	$this->set('words',$words);
    	$this->set('targetid',$targetid);
    	$this->set('keywordModel',$Model);
    	$this->set('page_navi', $page_navi);
    }*/
/*
    function admin_add() {
        $this->pageTitle = __("Add Keyword", true);		
        if (!empty($this->data)) {            
            $keywords = explode("\n",$this->data['Keyword']['value']);
            foreach($keywords as $keyword)
            {
            	$keyword = trim($keyword);
            	if(empty($keyword)){
            		continue;
            	}
            	$this->Keyword->create();
            	$this->data['Keyword']['value'] = $keyword;
	            if ($this->Keyword->save($this->data)) {
	                $this->Session->setFlash(__('The Menu has been saved', true));
	                //$this->redirect(array('action'=>'index'));
	            } else {
	                $this->Session->setFlash(__('The Menu could not be saved. Please, try again.', true));
	            }
            }
            
            $successinfo = array('success'=>__('Add success',true),'actions'=>array('OK'=>'closedialog'));
            
            echo json_encode($successinfo);
            exit;
        }
    }*/
 
}
?>