<?php

class MenusController extends AppController {

    var $name = 'Menus';

    function admin_index() {
        $this->pageTitle = __('Menus', true);

        $this->Menu->recursive = 0;
        $this->paginate['Menu']['order'] = "Menu.id ASC";
        $this->set('menus', $this->paginate());
    }
	/**
	 * 
	 * @param string/int $parent_id  if int,it's parentid; string slug.
	 */
    function admin_menu($parent_id=null) {
    	$parent_id = $this->Session->read('menuid');
    	if(empty($parent_id)) $parent_id='site';
    			
        $this->pageTitle = __('Menus', true);
        $modelClass = $this->modelClass;
        if ($parent_id) {
        	if(preg_match('/^\d+$/',$parent_id)){
            	$parent_info = $this->Menu->find('first',array(
        				'conditions'=>array('id'=>$parent_id),
        				'recursive'=>-1,
        		));
        	}
        	else{
        		$parent_info = $this->Menu->find('first',array(
        				'conditions'=>array('slug'=>$parent_id),
        				'recursive'=>-1,
        		));
        	}
            $left = $parent_info[$modelClass]['left'];
            $right = $parent_info[$modelClass]['right'];
            $menus = $this->Menu->find('threaded',
                            array(
                                'conditions' => array(
                                    $modelClass . '.left >' => $left,
                                    $modelClass . '.right <' => $right,
                                    $modelClass . '.deleted' => 0
                                ),
                                'order' => $modelClass . '.left',
                            	'recursive'=>-1,
                    ));
        }
//         else {
//             $menus = $this->Menu->find('threaded');
//         }
        $this->set('parent_info', $parent_info);
        $this->set('parent_id', $parent_id);
        $this->set('menus', $menus);
        $this->set('_serialize', 'menu');
        if($this->request->params['return']){ // in controller::requestAction;
        	//$this->renderElement('admin_menu');
        	return $this->render('admin_menu',false);
        }
    }
    
    function admin_contentmenu($cate_id=1){
    	$this->loadModel('Modelextend');
    	$contentmodels = $this->Modelextend->getContentModel('all',$cate_id);
    	$this->set('contentmodels', $contentmodels);
    }

}

?>