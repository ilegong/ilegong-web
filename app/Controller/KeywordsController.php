<?php
class KeywordsController extends AppController{
	
	var $name = 'Keywords';	
		
	function lists($model,$id){ //$page=1
		
		$model = Inflector::classify($model);
		
        $page= intval($_GET['page'])?intval($_GET['page']) :(intval($this->params['named']['page']) ? intval($this->params['named']['page']):1);
        
        $this->loadModel($model);
        $joins = array(
        		array(
        				'table'=> 'keyword_relateds',
        				'alias' => 'KeywordRelated',
        				'conditions' => array(
        						'KeywordRelated.relatedid = '.$model.'.id',
        						'KeywordRelated.relatedmodel' => $model,
        						'KeywordRelated.keyword_id' => $id,
        				),
        				'type' => 'inner',
        		),
        );
        $total  = $this->{$model}->find('count',array(
        	'conditions'=>array(),
        	'joins' => $joins,
        ));
        
        $datalist = $this->{$model}->find('all',array(
        		'conditions'=>array(),
        		'joins' => $joins,
        		'limit' => $limit,
        		'page' => $page
        ));
        
        $page_navi = getPageLinks($total, $limit, $this->request, $page);

        $this->set('datalist',$datalist);
        $this->set('total',$total);
        $this->set('target_model',$model);
        $this->set('target_controller',Inflector::tableize($model));
        $this->set('page',$page);
        $this->set('page_navi',$page_navi);
        
        $keyword = $this->Keyword->findById($id);
        $this->set('keyword',$keyword['Keyword']);
	}

}