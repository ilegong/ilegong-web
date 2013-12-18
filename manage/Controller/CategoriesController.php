<?php
class CategoriesController extends AppController{
	
	var $name = 'Categories';
	var $actsAs = array('Tree'); 
	
	var $uses = array('Category','Idiom');
	
	function admin_index($parent_id=null){
		$this->pageTitle = __('Categories', true);
        $this->User->recursive = 0;
        //echo $parent_id;
        $modelClass = $this->modelClass;
        $datas = $this->{$modelClass}->find('all', array(
            'conditions' => array(
                $modelClass .'.parent_id' => $parent_id,
            ),
            'order' => $modelClass .'.id ASC',
            'fields' => array(
                'id','name','parent_id','created','updated'
            ),
        ));
        
        $this->set('Categories', $datas);
        
        $currentdata =  $this->{$modelClass}->findById($parent_id);
        $this->set('currentdata', $currentdata);
        $this->set('parent_id', $parent_id);
        
        
	}
	
	function admin_recover(){
		$this->Category->recover('parent');	
	}
}