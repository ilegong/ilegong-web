<?php
/**
 * 鐢熸垚Google,Baidu绛塻itemap
 * @author Administrator
 *
 */
class SitemapsController extends AppController{
    
        var $pagesize = 2;
	
	var $name = 'Sitemaps';
	//var $layout = 'schedules_layout';
	
	function google($modelClass,$page=0){
            
            $this->layout = 'ajax';
            if(!$page){
                // http://www.google.com/support/webmasters/bin/answer.py?hl=zh-Hans&answer=71453
		$totalpage = $this->_getModlePageList($modelClass);
                $this->set('totalpage', $totalpage);
            }
            else{
                $datalist = $this->_getModleData($modelClass,$page);
                print_r($datalist);
                $this->set('datalist', $datalist);
            }
            $this->set('modelClass', $modelClass);
            $this->set('page', $page);
            
            list($plugin, $modelname) = pluginSplit($modelClass, true);
            $modelname = Inflector::classify($modelname);
            $plugin = Inflector::camelize($plugin);
            echo $modelname;
            $this->set('modelname', $modelname);
            $this->set('controller_name', Inflector::tableize($modelname)); 
	}
	
	function baidu($modelClass,$page=1){
		$datalist = $this->_getModleData($modelClass,$page);
		
	}
	
        private function _getModlePageList($modelClass){
            
                list($plugin, $modelClass) = pluginSplit($modelClass, true);
                $modelClass = Inflector::classify($modelClass);
                $plugin = Inflector::camelize($plugin);
                
		$this->loadModel($plugin.$modelClass);
                
                $conditions = array('published'=>1,'deleted'=>0);
                $total = $this->{$modelClass}->find('count',array('conditions'=>$conditions));
                return ceil($total/$this->pagesize);
                
	}
        
	private function _getModleData($modelClass,$page=1){
            
                list($plugin, $modelClass) = pluginSplit($modelClass, true);
                $modelClass = Inflector::classify($modelClass);
                $plugin = Inflector::camelize($plugin);
                
		$this->loadModel($plugin.$modelClass);
                
                $conditions = array('published'=>1,'deleted'=>0);
                
                return $this->{$modelClass}->find('all',array(
                    'conditions'=>$conditions,
                    'page'=> $page,
                    'fields'=>array('id','name','created'),
                    'limit'=> $this->pagesize)
                );
	}
}

