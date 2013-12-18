<?php

class KeywordRelated extends AppModel {

    var $name = 'KeywordRelated';
    
    var $belongsTo = array('Keyword');
    
    /**
     * 表中插入关键字与模块数据相关记录
     * @param $model
     * @param $id
     * @param $words
     */
	function addRelateds($model,$id,$words)
    {
    	$this->deleteAll(array('relatedid'=>$id,'relatedmodel'=>$model));
    	/*
    	 * $this->{$joinClass} = new AppModel(array(
						'name' => $joinClass,
						'table' => $this->{$type}[$assocKey]['joinTable'],
						'ds' => $this->useDbConfig
					));
    	 */
    	$words = explode(',',$words);
    	$keywords = $this->Keyword->find('all',array('conditions'=>array('value'=>$words)));
    	
    	foreach($keywords as $keyword)
    	{
    		$this->create();
    		$data['KeywordRelated']['relatedid'] = $id;
    		$data['KeywordRelated']['relatedmodel'] = $model;
    		$data['KeywordRelated']['keyword_id'] = $keyword['Keyword']['id'];
    		$this->save($data);
    	}
    }
}
?>